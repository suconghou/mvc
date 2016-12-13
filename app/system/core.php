<?php

/**
 * @author suconghou
 * @blog http://blog.suconghou.cn
 * @link https://github.com/suconghou/mvc
 * @version 1.9.13
 */

final class app
{
	private static $global;
	public static function start()
	{
		self::set('sys-start-time',microtime(true));
		self::set('sys-start-memory',memory_get_usage());
		error_reporting(DEBUG?E_ALL:0);
		set_include_path(LIB_PATH);
		set_error_handler('app::error');
		set_exception_handler('app::error');
		register_shutdown_function(function()
		{
			if($error=error_get_last())
			{
				$errormsg="ERROR({$error['type']}) {$error['message']} in {$error['file']} on line {$error['line']}";
				headers_sent()||header('Error-At:'.preg_replace('/\s+/',' ',DEBUG?"{$error['file']}:{$error['line']}=>{$error['message']}":(basename($error['file']).":{$error['line']}")),true,500);
				return app::log($errormsg,'ERROR');
			}
		});
		date_default_timezone_set(defined('TIMEZONE')?TIMEZONE:'PRC');
		defined('DEFAULT_ACTION')||define('DEFAULT_ACTION','index');
		defined('DEFAULT_CONTROLLER')||define('DEFAULT_CONTROLLER','home');
		defined('STDIN')||(defined('GZIP')?ob_start("ob_gzhandler"):ob_start());
		list($pharRun,$pharVar,$scriptName)=[substr(ROOT,0,7)=='phar://',substr(VAR_PATH,0,7)=='phar://','/'.trim($_SERVER['SCRIPT_NAME'],'./')];
		$varPath=$pharVar?str_ireplace(['phar://',$scriptName],null,VAR_PATH):VAR_PATH;
		define('VAR_PATH_LOG',$varPath.'log'.DIRECTORY_SEPARATOR)&&define('VAR_PATH_HTML',$varPath.'html'.DIRECTORY_SEPARATOR);
		return defined('STDIN')?self::cli($pharRun):self::process(self::init($scriptName));
	}
	private static function cli($phar=false)
	{
		$router=$GLOBALS['argv'];
		$script=basename(array_shift($router));
		if($GLOBALS['argc']>1)
		{
			$_SERVER['REQUEST_URI']=null;
			$phar||chdir(ROOT);
			$ret=self::regexRouter('/'.implode('/',$router));
			return is_object($ret)?$ret:(($GLOBALS['app']['router']=$ret?$ret:$router)&&self::run($GLOBALS['app']['router']));
		}
		if($phar)
		{
			return self::run([DEFAULT_CONTROLLER,DEFAULT_ACTION]);
		}
		try
		{
			$pharName=rtrim($script,'php').'phar';
			$path=ROOT.$pharName;
			is_file($path)&&unlink($path);
			$phar=new Phar($path,FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::KEY_AS_FILENAME|FilesystemIterator::SKIP_DOTS,$pharName);
			$phar->startBuffering();
			$dirObj=new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT)),'/^[\w\/\-\\\.:]+\.php$/i');
			foreach($dirObj as $file)
			{
				$phar->addFromString(substr($file,strlen(ROOT)),php_strip_whitespace($file));
			}
			$phar->setStub((getenv('EXE')?"#!/usr/bin/env php".PHP_EOL:null)."<?php Phar::mapPhar('$pharName');require 'phar://{$pharName}/{$script}';__HALT_COMPILER();");
			$phar->stopBuffering();
			getenv('EXE')&&chmod($path,0700);
			echo "{$phar->count()} files stored in {$path}".PHP_EOL;
		}
		catch(Exception $e)
		{
			echo $e->getMessage().PHP_EOL;
		}
	}
	private static function init($script)
	{
		if(!self::httpCache())
		{
			list($uri)=explode('?',$_SERVER['REQUEST_URI']);
			$uri=strpos($uri,$script)===false?$uri:str_ireplace($script,null,$uri);
			$router=self::regexRouter($uri);
			if($router)
			{
				return $router;
			}
			else
			{
				$router=array_values(array_filter(explode('/',$uri)));
			}
			if(empty($router[0]))
			{
				$router=[DEFAULT_CONTROLLER,DEFAULT_ACTION];
			}
			else if(empty($router[1]))
			{
				if(preg_match('/^[a-z]\w{0,20}$/i',$router[0]))
				{
					$router=[$router[0],DEFAULT_ACTION];
				}
				else
				{
					return self::error(404,"Request Controller {$router[0]} Error");
				}
			}
			else
			{
				if(!preg_match('/^[a-z]\w{0,20}$/i',$router[0]))
				{
					return self::error(404,"Request Controller {$router[0]} Error");
				}
				if(!preg_match('/^[a-z]\w{0,20}$/i',$router[1]))
				{
					return self::error(404,"Request Action {$router[0]}:{$router[1]} Error");
				}
			}
			return $router;
		}
	}
	//Object为插件模式,router[1]为Object是闭包模式,0为对应URL,此处未执行闭包
	private static function process($router)
	{
		if($router&&!is_object($router))
		{
			$GLOBALS['app']['router']=$router;
			$file=is_object($router[1])?self::fileCache($router[0]):self::fileCache($router);
			if(is_file($file))
			{
				$expire=filemtime($file);
				if($_SERVER['REQUEST_TIME']<$expire)
				{
					header('Expires: '.gmdate('D, d M Y H:i:s',$expire).' GMT');
					header('Cache-Control: public, max-age='.($expire-$_SERVER['REQUEST_TIME']));
					header('X-Cache: Hit');
					return isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304):(header('Last-Modified: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']).' GMT',true,200)||readfile($file));
				}
				unlink($file);
			}
			return self::run($router);
		}
	}
	//参数可以多个,也可以数组,router[1]为Object是闭包
	public static function run($router)
	{
		$router=is_array($router)?$router:func_get_args();
		$router=isset($router[1])?$router:[$router[0],DEFAULT_ACTION];
		if(is_object($router[1]))
		{
			return call_user_func_array($router[1],array_slice($router,2));
		}
		if(is_file($path=CONTROLLER_PATH.$router[0].'.php'))
		{
			list($controllerName,$action)=$router;
			$index=2;
		}
		else if(is_file($path=CONTROLLER_PATH.$router[0].DIRECTORY_SEPARATOR.$router[1].'.php'))
		{
			$controllerName=$router[1];
			$action=isset($router[2])?$router[2]:DEFAULT_ACTION;
			$index=3;
		}
		else
		{
			return self::error(404,"Request Controller {$router[0]} Not Found");
		}
		require_once $path;
		class_exists($controllerName)||self::error(404,"Request Controller Class {$controllerName} Not Found");
		method_exists($controllerName,$action)||self::error(404,"Request Controller Class {$controllerName} Does Not Contain Method {$action}");
		$GLOBALS['app']['ctl'][$controllerName]=isset($GLOBALS['app']['ctl'][$controllerName])?$GLOBALS['app']['ctl'][$controllerName]:$controllerName;
		if((!$GLOBALS['app']['ctl'][$controllerName] instanceof $controllerName)&&($class=new ReflectionClass($controllerName))&&($class->isInstantiable()))
		{
			if(($constructor=$class->getConstructor())&&($params=$constructor->getParameters()))
			{
				foreach ($params as &$param)
				{
					$param=(($di=$param->getClass())&&($m=$di->name))?($GLOBALS['app']['lib'][$m]=isset($GLOBALS['app']['lib'][$m])?$GLOBALS['app']['lib'][$m]:(new $m())):$router;
				}
				$GLOBALS['app']['ctl'][$controllerName]=$class->newInstanceArgs($params);
			}
			else
			{
				$GLOBALS['app']['ctl'][$controllerName]=new $controllerName($router);
			}
		}
		return is_callable([$GLOBALS['app']['ctl'][$controllerName],$action])?call_user_func_array([$GLOBALS['app']['ctl'][$controllerName],$action],array_slice($router,$index)):self::error(404,"Request Controller Class {$controllerName} Method {$action} Is Not Callable");
	}
	public static function route($regex,$arr)
	{
		$GLOBALS['app']['regexRouter'][$regex]=$arr;
	}
	public static function log($msg,$type='DEBUG',$file=null)
	{
		if(is_writable(VAR_PATH_LOG)&&((($type=strtoupper($type))=='ERROR')||DEBUG))
		{
			$path=VAR_PATH_LOG.($file?$file:date('Y-m-d')).'.log';
			$msg=$type.'-'.date('Y-m-d H:i:s').' ==> '.(is_scalar($msg)?$msg:PHP_EOL.print_r($msg,true)).PHP_EOL;
			return error_log($msg,3,$path);
		}
	}
	private static function regexRouter($uri)
	{
		if(!empty($GLOBALS['app']['regexRouter']))
		{
			foreach ($GLOBALS['app']['regexRouter'] as $regex=>$item)
			{
				if(preg_match("/^{$regex}$/",$uri,$matches))
				{
					$url=$matches[0];
					unset($matches[0],$GLOBALS['app']['regexRouter']);
					if(is_array($item))
					{
						return array_merge($item,$matches);
					}
					else if(is_object($item))
					{
						//传入URL,作为闭包时的文件缓存依据
						return array_merge([$url,$item],$matches);
					}
					else
					{
						//插件模式,根据路由触发一个类
						return call_user_func_array('with',array_merge([$item],$matches));
					}
				}
			}
			unset($GLOBALS['app']['regexRouter']);
		}
		return [];
	}
	public static function async($router=null,closure $callback=null)
	{
		function_exists('fastcgi_finish_request')&&fastcgi_finish_request();
		$data=($router instanceof closure)?$router():self::run($router);
		return $callback?$callback($data):$data;
	}
	public static function cost($type=null)
	{
		switch ($type)
		{
			case 'time': return round((microtime(true)-self::get('sys-start-time',0)),4);
			case 'memory': return byteFormat(memory_get_usage()-self::get('sys-start-memory',0));
			case 'query': return db::$sqlCount?:0;
			default: return ['time'=>round((microtime(true)-self::get('sys-start-time',0)),4),'memory'=>byteFormat(memory_get_usage()-self::get('sys-start-memory',0)),'query'=>db::$sqlCount?:0];
		}
	}
	public static function fileCache($router=[],$delete=false)
	{
		$cacheFile=sprintf('%s%u.html',VAR_PATH_HTML,crc32(ROOT.strtolower(trim($router?(is_array($router)?implode('/',$router):$router):DEFAULT_CONTROLLER.'/'.DEFAULT_ACTION ,'/'))));
		return $delete?(is_file($cacheFile)&&unlink($cacheFile)):$cacheFile;
	}
	public static function httpCache($min=0)
	{
		if($min=$min*60)
		{
			header('Expires: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']+$min).' GMT');
			header("Cache-Control: public, max-age={$min}");
			header('Last-Modified: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']).' GMT');
			return !header('ETag: W/'.($_SERVER['REQUEST_TIME']+$min).'-'.$min);
		}
		else if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'],$_SERVER['HTTP_IF_NONE_MATCH'])&&(count($param=explode('-',ltrim($_SERVER['HTTP_IF_NONE_MATCH'],'W/')))==2))
		{
			$last=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			list($expired,$cacheTime)=$param;
			if($expired>$_SERVER['REQUEST_TIME']||($last+$cacheTime>$_SERVER['REQUEST_TIME']))
			{
				header('Cache-Control: public, max-age='.($expired-$_SERVER['REQUEST_TIME']));
				return !header('Expires: '.gmdate('D, d M Y H:i:s',$expired).' GMT',true,304);
			}
		}
	}
	public static function get($key,$default=null)
	{
		return isset(self::$global[$key])?self::$global[$key]:$default;
	}
	public static function set($key,$value)
	{
		self::$global[$key]=$value;
		return self::$global;
	}
	public static function setItem($key,$value)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			$data[$key]=$value;
		}
		else
		{
			$data=[$key=>$value];
		}
		return file_put_contents($file,serialize($data));
	}
	public static function getItem($key,$default=null)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			return isset($data[$key])?$data[$key]:$default;
		}
		return $default;
	}
	public static function clearItem($key=null,&$file=null)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_null($key))
		{
			return is_file($file)&&unlink($file);
		}
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file)))&&isset($data[$key]))
		{
			unset($data[$key]);
			return file_put_contents($file,serialize($data));
		}
		return true;
	}
	public static function config($key=null,$default=null,$cfgfile='config.php')
	{
		$config=is_array($cfgfile)?$cfgfile:(isset(self::$global[$cfgfile])?self::$global[$cfgfile]:(self::$global[$cfgfile]=include $cfgfile));
		if($key=array_filter(explode('.',$key),function($item){return $item;}))
		{
			foreach ($key as $item)
			{
				if(is_array($config)&&isset($config[$item]))
				{
					$config=$config[$item];
				}
				else
				{
					return $default;
				}
			}
		}
		return $config;
	}
	public static function on($event,$function)
	{
		return self::$global['event'][$event]=$function;
	}
	public static function off($event)
	{
		unset(self::$global['event'][$event]);
	}
	public static function emit($event,$arguments=[])
	{
		return empty(self::$global['event'][$event])?:call_user_func_array(self::$global['event'][$event],is_array($arguments)?$arguments:[$arguments]);
	}
	public static function method($method,closure $function)
	{
		return self::$global['method'][$method]=$function;
	}
	public static function __callStatic($method,$args=null)
	{
		return isset(self::$global['method'][$method])?call_user_func_array(self::$global['method'][$method],$args):self::error(500,"Call Error Static Method {$method} In Class ".get_called_class());
	}
	public static function error($errno,$errstr=null,$errfile=null,$errline=null,array $errcontext=[])
	{
		if($errno instanceof Exception or $errno instanceof Error)
		{
			$errstr=$errno->getMessage();
			$errfile=$errno->getFile();
			$errline=$errno->getLine();
			$backtrace=$errno->getTrace();
			$errno=$errno->getCode();
		}
		else if(in_array($errno,[E_NOTICE,E_WARNING],true)&&(substr($errstr,0,3)==='PDO'||DEBUG<2))
		{
			return;
		}
		else
		{
			$backtrace=debug_backtrace();
		}
		$errstr=substr($errstr,0,999);
		$errormsg=sprintf('ERROR(%d) %s%s%s',$errno,$errstr,$errfile?" in {$errfile}":null,$errline?" on line {$errline}":null);
		$code=in_array($errno,[400,403,404,414,500,502,503,504])?$errno:500;
		$errno==404?app::log($errormsg,'DEBUG',$errno):app::log($errormsg,'ERROR');
		defined('STDIN')||(app::get('sys-error')&&exit("Error Found In Error Handler:{$errormsg}"))||(header('Error-At:'.preg_replace('/\s+/',' ',$errstr),true,$code)||app::set('sys-error',true));
		if(DEBUG||getenv('EXE'))
		{
			$li=[];
			foreach($backtrace as $trace)
			{
				if(isset($trace['file']))
				{
					$li[]="{$trace['file']}:{$trace['line']}=>".(isset($trace['class'])?$trace['class']:null).(isset($trace['type'])?$trace['type']:null)."{$trace['function']}(".((empty($trace['args'])||(!defined('STDIN')&&DEBUG<2))?null:(implode(array_map(function($item){return strlen(print_r($item,true))>90?'...':(is_null($item)?'null':preg_replace('/\s+/',null,print_r($item,true)));},$trace['args']),','))).")";
				}
			}
			$li=implode(defined('STDIN')?PHP_EOL:'</p><p>',array_reverse($li));
			echo defined('STDIN')?($errfile?$errormsg.PHP_EOL.$li.PHP_EOL:exit($errormsg.PHP_EOL.$li.PHP_EOL)):exit("<div style='margin:2% auto;width:80%;box-shadow:0 0 5px #f00;padding:1%;font:italic 14px/20px Georgia,Times New Roman;word-wrap:break-word;'><p>{$errormsg}</p><p>{$li}</p></div>");
		}
		else
		{
			$errorController=(isset($GLOBALS['app']['router'][0])&&is_file(CONTROLLER_PATH.$GLOBALS['app']['router'][0].'.php'))?$GLOBALS['app']['router'][0]:DEFAULT_CONTROLLER;
			$errorRouter=[$errorController,$errno==404?(defined('ERROR_PAGE_404')?ERROR_PAGE_404:'Error404'):(defined('ERROR_PAGE_500')?ERROR_PAGE_500:'Error500'),$errormsg];
			$errorPage="<title>Error..</title><center><span style='font-size:300px;color:gray;font-family:黑体'>{$code}...</span></center>";
			if(method_exists($errorController,$errorRouter[1]))//当前已加载的控制器或默认控制器中含有ERROR处理
			{
				$GLOBALS['app']['ctl'][$errorController]=isset($GLOBALS['app']['ctl'][$errorController])?$GLOBALS['app']['ctl'][$errorController]:$errorController;
				if((!$GLOBALS['app']['ctl'][$errorController] instanceof $errorController)&&($class=new ReflectionClass($errorController))&&($class->isInstantiable()))
				{
					if(($constructor=$class->getConstructor())&&($params=$constructor->getParameters()))
					{
						foreach ($params as &$param)
						{
							$param=(($di=$param->getClass())&&($m=$di->name))?($GLOBALS['app']['lib'][$m]=isset($GLOBALS['app']['lib'][$m])?$GLOBALS['app']['lib'][$m]:(new $m())):(isset($GLOBALS['app']['router'])?$GLOBALS['app']['router']:null);
						}
						$GLOBALS['app']['ctl'][$errorController]=$class->newInstanceArgs($params);
					}
					else
					{
						$GLOBALS['app']['ctl'][$errorController]=new $errorController($errorRouter);
					}
				}
				$errorPage=is_callable([$GLOBALS['app']['ctl'][$errorController],$errorRouter[1]])?call_user_func_array([$GLOBALS['app']['ctl'][$errorController],$errorRouter[1]],[$errormsg]):$errorPage;
			}
			exit($errorPage);
		}
	}
}

class response
{
	private $data;
	public function __construct(array &$data)
	{
		$this->data=&$data;
	}
	public function out($template=null,$min=0,$file=false)
	{
		if(is_string($template))
		{
			return $this->view($template,$min,$file);
		}
		else
		{
			return $this->json(is_array($template)?$template:(is_array($min)?$min:[]),is_int($template)?$template:(is_int($min)?$min:0),$file);
		}
	}
	public function view($template,$min=0,$file=false)
	{
		$min&&app::httpCache($min);
		$callback=($file&&$min)?function(&$buffer)use($min)
		{
			if(is_writable(VAR_PATH_HTML))
			{
				$router=&$GLOBALS['app']['router'];
				$file=is_object($router[1])?app::fileCache($router[0]):app::fileCache($router);
				file_put_contents($file,$buffer)&&touch($file,$_SERVER['REQUEST_TIME']+($min*60));
			}
			echo $buffer;
		}:null;
		return template($template,$this->data,$callback);
	}
	public function json(array $msg,$min=0,$callback=null)
	{
		$min&&app::httpCache($min);
		if($msg)
		{
			$msg['data']=&$this->data;
		}
		else
		{
			$msg=&$this->data;
		}
		return json($msg,$callback);
	}
}

function with($class)
{
	if(is_string($class))
	{
		$arguments=func_get_args();
		$arr=explode('/',array_shift($arguments));
		$m=end($arr);
		$GLOBALS['app']['lib'][$m]=isset($GLOBALS['app']['lib'][$m])?$GLOBALS['app']['lib'][$m]:$m;
		if($GLOBALS['app']['lib'][$m] instanceof $m)
		{
			return $GLOBALS['app']['lib'][$m];
		}
		if(is_file($file=MODEL_PATH."{$class}.php")||is_file($file=CONTROLLER_PATH."{$class}.php")||is_file($file=LIB_PATH.'Class'.DIRECTORY_SEPARATOR."{$class}.php")||is_file($file=LIB_PATH."{$class}.php")||is_file($file=LIB_PATH."{$class}.phar"))
		{
			$ret=require_once $file;
			if(class_exists($m))
			{
				$class=new ReflectionClass($m);
				$GLOBALS['app']['lib'][$m]=$class->newInstanceArgs($arguments);
				return $GLOBALS['app']['lib'][$m];
			}
			unset($GLOBALS['app']['lib'][$m]);
			return $ret;
		}
		return app::error(404,"Can not load {$class}");
	}
	return new response($class);
}

function template($v,array $_data_=null,$callback=null)
{
	if((is_file($_v_=VIEW_PATH.$v.'.php'))||(is_file($_v_=VIEW_PATH.$v)))
	{
		(is_array($_data_)&&!empty($_data_))&&extract($_data_);
		if($callback)
		{
			ob_start()&&include $_v_;
			$contents=ob_get_contents();
			return (ob_end_clean()&&($callback instanceof closure))?$callback($contents):$contents;
		}
		return include $_v_;
	}
	return app::error(404,"File {$_v_} Not Found");
}

class request
{
	public static function post($key=null,$default=null,$clean=false)
	{
		return self::getVar($_POST,$key,$default,$clean);
	}
	public static function get($key=null,$default=null,$clean=false)
	{
		return self::getVar($_GET,$key,$default,$clean);
	}
	public static function param($key=null,$default=null,$clean=false)
	{
		return self::getVar($_REQUEST,$key,$default,$clean);
	}
	public static function cookie($key=null,$default=null,$clean=false)
	{
		return self::getVar($_COOKIE,$key,$default,$clean);
	}
	public static function session($key=null,$default=null,$clean=false)
	{
		isset($_SESSION)||session_start();
		return self::getVar($_SESSION,$key,$default,$clean);
	}
	public static function server($key=null,$default=null,$clean=flase)
	{
		return self::getVar($_SERVER,$key,$default,$clean);
	}
	public static function input($key=null,$default=null,$json=true)
	{
		$str=file_get_contents('php://input');
		$json?($data=json_decode($str,true)):parse_str($str,$data);
		return $key?(isset($data[$key])?$data[$key]:$default):$data;
	}
	public static function ip($default=null)
	{
		$ip=getenv('REMOTE_ADDR');
		return $ip?$ip:(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$default);
	}
	public static function info($key=null,$default=null)
	{
		$data=['ip'=>self::ip(),'ajax'=>self::isAjax(),'ua'=>self::ua(),'refer'=>self::refer(),'protocol'=>(isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off'))?"https":"http"];
		return $key?(isset($data[$key])?$data[$key]:$default):$data;
	}
	public static function serverInfo($key=null,$default=null)
	{
		$info=['php_os'=>PHP_OS,'php_sapi'=>PHP_SAPI,'php_vision'=>PHP_VERSION,'post_max_size'=>ini_get('post_max_size'),'max_execution_time'=>ini_get('max_execution_time'),'server_ip'=>gethostbyname($_SERVER['SERVER_NAME']),'upload_max_filesize'=>ini_get('file_uploads')?ini_get('upload_max_filesize'):0];
		return $key?(isset($info[$key])?$info[$key]:$default):$info;
	}
	public static function isCli()
	{
		return defined('STDIN')&&defined('STDOUT');
	}
	public static function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&$_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}
	public static function isPjax()
	{
		return isset($_SERVER['HTTP_X_PJAX'])&&$_SERVER['HTTP_X_PJAX'];
	}
	public static function isSpider()
	{
		$agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
		return $agent?preg_match('/(spider|bot|slurp|crawler)/i',$agent):true;
	}
	public static function isMobile()
	{
		$agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
		$regexMatch="/(nokia|iphone|android|motorola|ktouch|samsung|symbian|blackberry|CoolPad|huawei|hosin|htc|smartphone)/i";
		return $agent?preg_match($regexMatch,$agent):true;
	}
	public static function method($method=null,closure $callback=null)
	{
		$type=isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'GET';
		return $method?(($type===strtoupper($method))?($callback?$callback():true):false):$type;
	}
	public static function ua($default=null)
	{
		return isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:$default;
	}
	public static function refer($default=null)
	{
		return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$default;
	}
	public static function verify(array $rule,$callback=true,$post=true)
	{
		$keys=[];
		$data=$post===true?$_POST:(is_array($post)?$post:$_REQUEST);
		foreach ($rule as $key => $value)
		{
			$keys[]=is_int($key)?$value:$key;
		}
		foreach ($data as $key => $value)
		{
			if(!in_array($key,$keys))
			{
				unset($data[$key]);
			}
		}
		foreach ($keys as $key)
		{
			$data[$key]=isset($data[$key])?$data[$key]:null;
		}
		return validate::verify($rule,$data,$callback);
	}
	public static function getVar(&$origin,$var,$default=null,$clean=false)
	{
		if($var)
		{
			if(is_array($var))
			{
				$data=[];
				foreach ($var as $k)
				{
					$data[$k]=isset($origin[$k])?($clean?self::clean($origin[$k],$clean):$origin[$k]):$default;
				}
				return $data;
			}
			return isset($origin[$var])?($clean?self::clean($origin[$var],$clean):$origin[$var]):$default;
		}
		return $origin;
	}
	public static function clean($val,$type=null)
	{
		switch ($type)
		{
			case 'int': return intval($val);
			case 'float': return floatval($val);
			case 'string': return trim(strval($val));
			case 'xss': return filter_var(htmlspecialchars(strip_tags($val),ENT_QUOTES),FILTER_SANITIZE_STRING);
			case 'html': return trim(strip_tags($val));
			case 'en': return preg_replace('/[\x80-\xff]/','',$val);
			default: return $type?sprintf($type,$val):trim($val);
		}
	}
	public static function __callStatic($method,$args)
	{
		return app::error(500,"Call Error Static Method {$method} In Class ".get_called_class());
	}
}

class validate
{
	public static function verify($rule,$data,$callback=true)
	{
		try
		{
			$switch=[];
			foreach($rule as $k=>&$item)
			{
				if(isset($data[$k])&&$data[$k])//存在要验证的数据
				{
					foreach($item as $type=>$msg)
					{
						if($msg instanceof closure)
						{
							$data[$k]=$msg($data[$k],$k);
						}
						else if(is_int($type))
						{
							$switch[$k]=$msg;
						}
						else if(!self::check($data[$k],$type))
						{
							throw new Exception($msg,-11);
						}
					}
				}
				else if(isset($item['require'])) //标记为require,却不存在
				{
					throw new Exception($item['require'],-10);
				}
			}
		}
		catch(Exception $e)
		{
			$data=['code'=>$e->getCode(),'msg'=>$e->getMessage()];
			return $callback?(($callback instanceof closure)?$callback($data,$e):json($data)):false;
		}
		foreach($switch as $from=>$to)
		{
			$data[$to]=$data[$from];
			unset($data[$from]);
		}
		return $data; //数据全部校验通过
	}
	private static function check($item,$type)
	{
		if(strpos($type,'=')&&(list($key,$val)=explode('=',$type)))
		{
			switch ($key)
			{
				case 'minlength': return strlen($item)>=$val;
				case 'maxlength': return strlen($item)<=$val;
				case 'length': return strlen($item)==$val;
				case 'eq': return trim($item)==trim($val);
				case '!eq': return strtolower(trim($item))==strtolower(trim($val));
				default: return self::this($type,$item);
			}
		}
		else
		{
			switch ($type)
			{
				case 'require': return $item;
				case 'email': return self::email($item);
				case 'username': return self::username($item);
				case 'password': return self::password($item);
				case 'phone': return self::phone($item);
				case 'url': return self::url($item);
				case 'ip': return self::ip($item);
				case 'idcard': return self::idcard($item);
				default: return self::this($type,$item);
			}
		}
	}
	public static function email($email)
	{
		return filter_var($email,FILTER_VALIDATE_EMAIL);
	}
	public static function phone($phone)
	{
		return preg_match("/^1[34578][0-9]{9}$/",$phone);
	}
	public static function url($url)
	{
		return filter_var($url,FILTER_VALIDATE_URL);
	}
	public static function ip($ip)
	{
		return filter_var($ip,FILTER_VALIDATE_IP);
	}
	//中国大陆身份证号(15位或18位)
	public static function idcard($id)
	{
		return preg_match('/^\d{15}(\d\d[0-9xX])?$/',$id);
	}
	//字母数字汉字,不能全是数字
	public static function username($username)
	{
		return is_numeric($username)?false:preg_match('/^[\w\x{4e00}-\x{9fa5}]{3,20}$/u',$username);
	}
	//数字/大写字母/小写字母/标点符号组成，四种都必有，8位以上
	public static function password($pass)
	{
		return preg_match('/^(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/',$pass);
	}
	public static function this($pattern,$subject)
	{
		return preg_match($pattern,$subject);
	}
}

class db
{
	private static $pdo;
	public static $lastSql;
	public static $sqlCount;
	final private static function init($dbDsn,$dbUser,$dbPass)
	{
		if(!self::$pdo)
		{
			$options=[PDO::ATTR_PERSISTENT=>true,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_TIMEOUT=>3];
			try
			{
				self::$pdo=new PDO($dbDsn,$dbUser,$dbPass,$options);
			}
			catch (PDOException $e)
			{
				try
				{
					self::$pdo=new PDO($dbDsn,$dbUser,$dbPass,$options);
				}
				catch(PDOException $e)
				{
					return app::error(500,$e->getMessage());
				}
			}
			if(!empty(static::$initCmd)&&is_array(static::$initCmd))
			{
				foreach (static::$initCmd as $cmd)
				{
					self::$pdo->exec($cmd);
				}
			}
		}
		return self::$pdo;
	}
	final public static function runSql($sql)
	{
		return self::execute($sql);
	}
	final public static function getData($sql)
	{
		$rs=self::execute($sql,true);
		return $rs===false?:$rs->fetchAll(PDO::FETCH_ASSOC);
	}
	final public static function getLine($sql)
	{
		$rs=self::execute($sql,true);
		return $rs===false?:$rs->fetch(PDO::FETCH_ASSOC);
	}
	final public static function getVar($sql)
	{
		$rs=self::execute($sql,true);
		return $rs===false?:$rs->fetchColumn();
	}
	final public static function execute($sql,$isQuery=null)
	{
		try
		{
			(self::$lastSql=$sql)&&self::$sqlCount++;
			return $isQuery===false?(self::ready()->prepare($sql)):($isQuery?(self::ready()->query($sql)):(self::ready()->exec($sql)));
		}
		catch (PDOException $e)
		{
			return app::error($e->getCode(),$e->getMessage());
		}
	}
	final public static function lastId()
	{
		return self::ready()->lastInsertId();
	}
	final public static function getInstance()
	{
		return self::ready();
	}
	final private static  function ready()
	{
		return self::$pdo?self::$pdo:self::init(DB_DSN,defined('DB_USER')?DB_USER:null,defined('DB_PASS')?DB_PASS:null);
	}
	final public function __call($method,$args=null)
	{
		return self::__callStatic($method,$args);
	}
	final public static function __callStatic($method,$args=null)
	{
		if(method_exists(self::ready(),$method))
		{
			return call_user_func_array([self::$pdo,$method],$args);
		}
		return app::error(500,"Call Error Method {$method} In Class ".get_called_class());
	}
}

function __autoload($class)
{
	if(is_file($file=MODEL_PATH."{$class}.php")||is_file($file=CONTROLLER_PATH."{$class}.php")||is_file($file=LIB_PATH.'Class'.DIRECTORY_SEPARATOR."{$class}.php")||is_file($file=LIB_PATH."{$class}.php"))
	{
		require_once $file;
		return class_exists($class)||app::error(500,"File {$file} Does Not Contain Class {$class}");
	}
	return false;
}
function session($key,$val=null,$delete=false)
{
	isset($_SESSION)||session_start();
	if(is_null($val))
	{
		return $delete?(bool)array_map(function($k){unset($_SESSION[$k]);},is_array($key)?$key:[$key]):request::session($key,null,false);
	}
	return $_SESSION[$key]=$val;
}
function cookie($key,$val=null,$expire=0)
{
	if(is_null($val))
	{
		return request::cookie($key,null,false);
	}
	return call_user_func_array('setcookie',func_get_args());
}
function json(array $data,$callback=null)
{
	$data=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	$callback=$callback===true?(empty($_GET['callback'])?null:$_GET['callback']):$callback;
	$data=$callback?$callback."(".$data.")":$data;
	header('Content-Type: text/'.($callback?'javascript':'json').';charset=utf-8',true,200);
	exit($data);
}
function byteFormat($size,$dec=2)
{
	$size=max($size,0);
	$unit=['B','KB','MB','GB','TB','PB','EB','ZB','YB'];
	return $size>=1024?round($size/pow(1024,($i=floor(log($size,1024)))),$dec).' '.$unit[$i]:$size.' B';
}
function redirect($url,$timeout=0)
{
	$timeout=intval($timeout);
	if(in_array($timeout,[0,301,302,303,307]))
	{
		header("Location:{$url}",true,$timeout);
	}
	else
	{
		header("Refresh:{$timeout};url={$url}",true,302);
	}
	exit(header('Cache-Control:no-cache, no-store, max-age=0, must-revalidate'));
}
function baseUrl($path=null)
{
	if(is_int($path))
	{
		$router=&$GLOBALS['app']['router'];
		return isset($router[$path])?$router[$path]:null;
	}
	$protocol=(isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off'))?"https":"http";
	$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
	$path=is_null($path)?null:(is_bool($path)?($path?$_SERVER['REQUEST_URI']:'/'.implode('/',$GLOBALS['app']['router'])):'/'.ltrim($path,'/'));
	return "{$protocol}://{$host}{$path}";
}
function encrypt($input,$key=null)
{
	return str_replace(['+','/','='],['-','_',''],base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,md5($key),$input,MCRYPT_MODE_ECB,mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH,MCRYPT_MODE_ECB),MCRYPT_DEV_URANDOM))));
}
function decrypt($input,$key=null)
{
	$input=str_replace(['-','_'],['+','/'],$input);
	if($mod=strlen($input)%4)
	{
		$input.=substr('====', $mod);
	}
	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128,md5($key),base64_decode($input),MCRYPT_MODE_ECB,mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH,MCRYPT_MODE_ECB),MCRYPT_DEV_URANDOM)));
}
function csrf_token($check=false,$name='_token',closure $callback=null)
{
	isset($_SESSION)||session_start();
	$token=isset($_SESSION['csrf_token'])?$_SESSION['csrf_token']:null;
	if($check)
	{
		if(!(isset($_REQUEST[$name])&&$_REQUEST[$name]===$token))
		{
			return $callback?$callback($token):app::error(403,'Csrf Token Not Match');
		}
		return true;
	}
	else if(!$token)
	{
		$token=md5(uniqid());
		$_SESSION['csrf_token']=$token;
	}
	return $token;
}
//发送邮件,用来替代原生mail,多个接受者用分号隔开
function sendMail($mailTo,$mailSubject,$mailMessage=null)
{
	try
	{
		if(!$fp=fsockopen(MAIL_SERVER,defined('MAIL_PORT')?MAIL_PORT:25,$errno,$errstr,3))
		{
			throw new Exception($errstr,$errno);
		}
		$lastmessage=fgets($fp,128);
		if(substr($lastmessage,0,3)!='220')
		{
			throw new Exception("CONNECT ERROR - {$lastmessage}",2);
		}
		fputs($fp,"EHLO mail\r\n");
		$lastmessage=fgets($fp,128);
		if(!in_array(substr($lastmessage,0,3),[220,250]))
		{
			throw new Exception("HELO/EHLO - {$lastmessage}",3);
		}
		while(true)
		{
			if(substr($lastmessage,3,1)!='-'||empty($lastmessage))
			{
				break;
			}
			$lastmessage=fgets($fp,128);
		}
		if(!defined('MAIL_AUTH')||defined('MAIL_AUTH')&&MAIL_AUTH)
		{
			fputs($fp,"AUTH LOGIN\r\n");
			$lastmessage=fgets($fp,128);
			if(substr($lastmessage,0,3)!=334)
			{
				throw new Exception("AUTH LOGIN - {$lastmessage}",4);
			}
			fputs($fp,base64_encode(MAIL_USERNAME)."\r\n");
			$lastmessage=fgets($fp,128);
			if(substr($lastmessage,0,3)!=334)
			{
				throw new Exception("AUTH LOGIN - {$lastmessage}",5);
			}
			fputs($fp,base64_encode(MAIL_PASSWORD)."\r\n");
			$lastmessage=fgets($fp,128);
			if(substr($lastmessage,0,3)!=235)
			{
				throw new Exception("AUTH LOGIN - {$lastmessage}",6);
			}
		}
		fputs($fp,"MAIL FROM: <".MAIL_USERNAME.">\r\n");
		$lastmessage=fgets($fp,128);
		if(substr($lastmessage,0,3)!=250)
		{
			fputs($fp,"MAIL FROM: <".MAIL_USERNAME.">\r\n");
			$lastmessage=fgets($fp,128);
			if(substr($lastmessage,0,3)!=250)
			{
				throw new Exception("MAIL FROM - {$lastmessage}",7);
			}
		}
		foreach(explode(';',$mailTo) as $touser)
		{
			if($touser=trim($touser))
			{
				fputs($fp,"RCPT TO: <{$touser}>\r\n");
				$lastmessage=fgets($fp,128);
				if(substr($lastmessage,0,3)!=250)
				{
					throw new Exception("RCPT TO - {$lastmessage}",8);
				}
			}
		}
		fputs($fp,"DATA\r\n");
		$lastmessage=fgets($fp,128);
		if(substr($lastmessage,0,3)!=354)
		{
			throw new Exception("DATA - {$lastmessage}",9);
		}
		$headers="MIME-Version:1.0\r\nContent-type:text/html\r\nContent-Transfer-Encoding: base64\r\nFrom: ".MAIL_NAME."<".MAIL_USERNAME.">\r\nDate: ".date("r")."\r\nMessage-ID: <".uniqid(rand(1,999)).">\r\n";
		$mailSubject='=?utf-8?B?'.base64_encode($mailSubject).'?=';
		$mailMessage=chunk_split(base64_encode(preg_replace("/(^|(\r\n))(\.)/","\1.\3",$mailMessage)));
		fputs($fp,$headers);
		fputs($fp,"To: {$mailTo}\r\n");
		fputs($fp,"Subject: {$mailSubject}\r\n");
		fputs($fp,"\r\n\r\n");
		fputs($fp,"{$mailMessage}\r\n.\r\n");
		$lastmessage=fgets($fp,128);
		if(substr($lastmessage,0,3)!=250)
		{
			throw new Exception("END - {$lastmessage}",10);
		}
		fputs($fp,"QUIT\r\n");
		return true;
	}
	catch(Exception $e)
	{
		app::log($e->getMessage(),'ERROR');
		throw $e;
	}
}

