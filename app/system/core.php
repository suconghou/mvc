<?php

/**
 * @author suconghou
 * @blog http://blog.suconghou.cn
 * @link http://github.com/suconghou/mvc
 * @version 1.9.3
 */

final class App
{
	private static $global;
	public static function start()
	{
		self::set('sys-start-time',microtime(true));
		self::set('sys-start-memory',memory_get_usage());
		error_reporting(DEBUG?E_ALL:0);
		set_include_path(LIB_PATH);
		set_error_handler(['app','Error']);
		set_exception_handler(['app','Error']);
		register_shutdown_function(['app','Shutdown']);
		date_default_timezone_set(defined('TIMEZONE')?TIMEZONE:'PRC');
		defined('DEFAULT_ACTION')||define('DEFAULT_ACTION','index');
		defined('DEFAULT_CONTROLLER')||define('DEFAULT_CONTROLLER','home');
		defined('STDIN')||(defined('GZIP')?ob_start("ob_gzhandler"):ob_start());
		list($pharRun,$pharVar,$scriptName)=[substr(ROOT,0,7)=='phar://',substr(VAR_PATH,0,7)=='phar://','/'.trim($_SERVER['SCRIPT_NAME'],'./')];
		$varPath=$pharVar?str_ireplace(['phar://',$scriptName],null,VAR_PATH):VAR_PATH;
		define('VAR_PATH_LOG',$varPath.'log')&&define('VAR_PATH_HTML',$varPath.'html');
		return defined('STDIN')?self::runCli($pharRun):self::process(self::init($scriptName));
	}
	private static function runCli($phar=false)
	{
		$router=$GLOBALS['argv'];
		$script=basename(array_shift($router));
		if($GLOBALS['argc']>1)
		{
			$_SERVER['REQUEST_URI']=null;
			$phar||chdir(ROOT);
			$ret=self::regexRouter('/'.implode('/',$router));
			return is_object($ret)?$ret:(($GLOBALS['APP']['router']=$ret?$ret:$router)&&self::run($GLOBALS['APP']['router']));
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
			$phar->setStub((defined('EXE')?"#!/usr/bin/env php".PHP_EOL:null)."<?php Phar::mapPhar('$pharName');require 'phar://{$pharName}/{$script}';__HALT_COMPILER();");
			$phar->stopBuffering();
			defined('EXE')&&chmod($path,0700);
			echo "{$phar->count()} files stored in {$path}".PHP_EOL;
		}
		catch(Exception $e)
		{
			echo $e->getMessage().PHP_EOL;
		}
	}
	private static function init($script)
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
			foreach(explode('/',$uri) as $segment)
			{
				if(!empty($segment))
				{
					$router[]=$segment;
				}
			}
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
				return self::Error(404,"Request Controller {$router[0]} Error");
			}
		}
		else
		{
			if(!preg_match('/^[a-z]\w{0,20}$/i',$router[0]))
			{
				return self::Error(404,"Request Controller {$router[0]} Error");
			}
			if(!preg_match('/^[a-z]\w{0,20}$/i',$router[1]))
			{
				return self::Error(404,"Request Action {$router[0]}:{$router[1]} Error");
			}
		}
		return $router;
	}
	//Object为插件模式,router[1]为Object是闭包模式,0为对应URL,此处未执行闭包
	private static function process($router)
	{
		if(!is_object($router))
		{
			$GLOBALS['APP']['router']=$router;
			$file=is_object($router[1])?self::fileCache($router[0]):self::fileCache($router);
			if(is_file($file))
			{
				$expire=filemtime($file);
				$now=$_SERVER['REQUEST_TIME'];
				if($now<$expire)
				{
					header('Expires: '.gmdate('D, d M Y H:i:s',$expire).' GMT');
					header('Cache-Control: public, max-age='.($expire-$now));
					header('X-Cache: Hit',true);
					return isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304):(header('Last-Modified: '.gmdate('D, d M Y H:i:s',$now).' GMT',true,200)||readfile($file));
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
			$param=2;
		}
		else if(is_file($path=CONTROLLER_PATH.$router[0].DIRECTORY_SEPARATOR.$router[1].'.php'))
		{
			$controllerName=$router[1];
			$action=isset($router[2])?$router[2]:DEFAULT_ACTION;
			$param=3;
		}
		else
		{
			return self::Error(404,"Request Controller {$router[0]} Not Found");
		}
		require_once $path;
		class_exists($controllerName)||self::Error(404,"Request Controller Class {$controllerName} Not Found");
		method_exists($controllerName,$action)||self::Error(404,"Request Controller Class {$controllerName} Does Not Contain Method {$action}");
		$GLOBALS['APP']['controller'][$controllerName]=isset($GLOBALS['APP']['controller'][$controllerName])?$GLOBALS['APP']['controller'][$controllerName]:$controllerName;
		if(!$GLOBALS['APP']['controller'][$controllerName] instanceof $controllerName)
		{
			$GLOBALS['APP']['controller'][$controllerName]=new $controllerName($router);
		}
		return is_callable([$GLOBALS['APP']['controller'][$controllerName],$action])?call_user_func_array([$GLOBALS['APP']['controller'][$controllerName],$action],array_slice($router,$param)):self::Error(404,"Request Controller Class {$controllerName} Method {$action} Is Not Callable");
	}
	public static function route($regex,$arr)
	{
		$GLOBALS['APP']['regexRouter'][$regex]=$arr;
	}
	public static function log($msg,$type='DEBUG',$file=null)
	{
		if(is_writable(VAR_PATH_LOG)&&(DEBUG||strtoupper($type)=='ERROR'))
		{
			$path=VAR_PATH_LOG.DIRECTORY_SEPARATOR.($file?$file:date('Y-m-d')).'.log';
			$msg=strtoupper($type).'-'.date('Y-m-d H:i:s').' ==> '.(is_scalar($msg)?$msg:PHP_EOL.print_r($msg,true)).PHP_EOL;
			return error_log($msg,3,$path);
		}
	}
	private static function regexRouter($uri)
	{
		if(!empty($GLOBALS['APP']['regexRouter']))
		{
			foreach ($GLOBALS['APP']['regexRouter'] as $regex=>$item)
			{
				if(preg_match("/^{$regex}$/",$uri,$matches))
				{
					$url=$matches[0];
					unset($matches[0],$GLOBALS['APP']['regexRouter']);
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
						return call_user_func_array('S',array_merge([$item],$matches));
					}
				}
			}
			unset($GLOBALS['APP']['regexRouter']);
		}
		return [];
	}
	public static function async($router=null,Closure $callback=null)
	{
		function_exists('fastcgi_finish_request')&&fastcgi_finish_request();
		$data=($router instanceof Closure)?$router():self::run($router);
		return $callback?$callback($data):$data;
	}
	public static function cost($type=null)
	{
		switch ($type)
		{
			case 'time':
				return round((microtime(true)-self::get('sys-start-time',0)),4);
			case 'memory':
				return byteFormat(memory_get_usage()-self::get('sys-start-memory',0));
			case 'query':
				return self::get('sys-sql-count',0);
			default:
				return ['time'=>round((microtime(true)-self::get('sys-start-time',0)),4),'memory'=>byteFormat(memory_get_usage()-self::get('sys-start-memory',0)),'query'=>self::get('sys-sql-count',0)];
		}
	}
	public static function fileCache($router=[],$delete=false)
	{
		$router=$router?(is_array($router)?implode('/',$router):$router):DEFAULT_CONTROLLER.'/'.DEFAULT_ACTION;
		$cacheFile=VAR_PATH_HTML.DIRECTORY_SEPARATOR.sprintf('%u.html',crc32(ROOT.strtolower($router)));
		return $delete?(is_file($cacheFile)&&unlink($cacheFile)):$cacheFile;
	}
	public static function opt($key,$default=null)
	{
		$key="--{$key}=";
		foreach ($GLOBALS['argv'] as $item)
		{
			if(sizeof($arr=explode($key,$item))==2)
			{
				return end($arr);
			}
		}
		return $default;
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
		$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('%u.db',crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			$data[$key]=$value;
		}
		else
		{
			$data=[$key=>$value];
		}
		return file_put_contents($file,serialize($data))?true:false;
	}
	public static function getItem($key,$default=null)
	{
		$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('%u.db',crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			return isset($data[$key])?$data[$key]:$default;
		}
		return $default;
	}
	public static function clearItem($key=null,&$file=null)
	{
		$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('%u.db',crc32(ROOT));
		if(is_null($key))
		{
			return is_file($file)&&unlink($file);
		}
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file)))&&isset($data[$key]))
		{
			unset($data[$key]);
			return file_put_contents($file,serialize($data))?true:false;
		}
		return true;
	}
	public static function timer(Closure $function,$exit=false,Closure $callback=null)
	{
		while(true)
		{
			$data=$function();
			$break=($exit instanceof Closure)?$exit($data):$exit;
			if($break)
			{
				return $callback?$callback($data):$data;
			}
		}
	}
	public static function config($key=null,$default=null,$configFile='config.php')
	{
		$config=is_array($configFile)?$configFile:(isset(self::$global[$configFile])?self::$global[$configFile]:(self::$global[$configFile]=include $configFile));
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
		if(!empty(self::$global['event'][$event]))
		{
			return call_user_func_array(self::$global['event'][$event],is_array($arguments)?$arguments:[$arguments]);
		}
	}
	public static function method($method,Closure $function)
	{
		return self::$global['method'][$method]=$function;
	}
	public static function __callStatic($method,$args=null)
	{
		if(isset(self::$global['method'][$method]))
		{
			return call_user_func_array(self::$global['method'][$method],$args);
		}
		return self::Error(500,"Call Error Static Method {$method} In Class ".get_called_class());
	}
	public static function Error($errno,$errstr=null,$errfile=null,$errline=null)
	{
		if((DEBUG<2)&&in_array($errno,[E_NOTICE,E_WARNING]))
		{
			return false;
		}
		else if(is_object($errno))
		{
			$errstr=$errno->getMessage();
			$errfile=$errno->getFile();
			$errline=$errno->getLine();
			$backtrace=$errno->getTrace();
			$errno=$errno->getCode();
		}
		else
		{
			$backtrace=debug_backtrace();
		}
		if(in_array($errno,[400,403,404,414,500,502,503,504]))
		{
			$errormsg="ERROR({$errno}) {$errstr}";
			$code=$errno;
		}
		else
		{
			$errormsg="ERROR({$errno}) {$errstr} in {$errfile} on line {$errline}";
			$code=500;
		}
		$errno==404?app::log($errormsg,'DEBUG',$errno):app::log($errormsg,'ERROR');
		defined('STDIN')||(app::get('sys-error')&&exit("Error Found In Error Handler:{$errormsg}"))||(header("Error-At:{$errstr}",true,$code)||app::set('sys-error',true));
		if(DEBUG||defined('STDIN'))
		{
			$li=[];
			foreach($backtrace as $trace)
			{
				if(isset($trace['file']))
				{
					$li[]="{$trace['file']}:{$trace['line']}=>".(isset($trace['class'])?$trace['class']:null).(isset($trace['type'])?$trace['type']:null)."{$trace['function']}(".(DEBUG==1||empty($trace['args'])?null:implode(array_map(function($item){return strlen(print_r($item,true))>80?'...':(is_null($item)?'null':str_replace([PHP_EOL,'  '],null,print_r($item,true)));},$trace['args']),',')).")";
				}
			}
			$li=implode(defined('STDIN')?PHP_EOL:'</p><p>',array_reverse($li));
			echo defined('STDIN')?($errfile?$errormsg.PHP_EOL.$li.PHP_EOL:exit($errormsg.PHP_EOL.$li.PHP_EOL)):exit("<div style='margin:2% auto;width:80%;box-shadow:0 0 5px #f00;padding:1%;font:italic 14px/20px Georgia,Times New Roman;'><p>{$errormsg}</p><p>{$li}</p></div>");
		}
		else
		{
			$errorController=(isset($GLOBALS['APP']['router'][0])&&is_file(CONTROLLER_PATH.$GLOBALS['APP']['router'][0].'.php'))?$GLOBALS['APP']['router'][0]:DEFAULT_CONTROLLER;
			$errorRouter=[$errorController,$errno==404?(defined('ERROR_PAGE_404')?ERROR_PAGE_404:'Error404'):(defined('ERROR_PAGE_500')?ERROR_PAGE_500:'Error500'),$errormsg];
			$errorPage="<title>Error..</title><center><span style='font-size:300px;color:gray;font-family:黑体'>{$code}...</span></center>";
			if(method_exists($errorController,$errorRouter[1]))//当前已加载的控制器或默认控制器中含有ERROR处理
			{
				$GLOBALS['APP']['controller'][$errorController]=isset($GLOBALS['APP']['controller'][$errorController])?$GLOBALS['APP']['controller'][$errorController]:$errorController;
				if(!$GLOBALS['APP']['controller'][$errorController] instanceof $errorController)
				{
					$GLOBALS['APP']['controller'][$errorController]=new $errorController($errorRouter);
				}
				$errorPage=is_callable([$GLOBALS['APP']['controller'][$errorController],$errorRouter[1]])?call_user_func_array([$GLOBALS['APP']['controller'][$errorController],$errorRouter[1]],[$errormsg]):$errorPage;
			}
			exit($errorPage);
		}
	}
	public static function Shutdown()
	{
		if($lastError=error_get_last())
		{
			$errormsg="ERROR({$lastError['type']}) {$lastError['message']} in {$lastError['file']} on line {$lastError['line']}";
			headers_sent()||header('Error-At:'.(DEBUG?"{$lastError['file']}:{$lastError['line']}=>{$lastError['message']}":basename($lastError['file']).":{$lastError['line']}"),true,500);
			return app::log($errormsg,'ERROR');
		}
	}
}

function M($model)
{
	$arguments=func_get_args();
	$arr=explode('/',array_shift($arguments));
	$m=end($arr);
	$GLOBALS['APP']['model'][$m]=isset($GLOBALS['APP']['model'][$m])?$GLOBALS['APP']['model'][$m]:$m;
	if($GLOBALS['APP']['model'][$m] instanceof $m)
	{
		return $GLOBALS['APP']['model'][$m];
	}
	else
	{
		$modelFile=MODEL_PATH.$model.'.php';
		(is_file($modelFile) && require_once $modelFile)||app::Error(500,"Load Model {$m} Failed , Mdoel File {$modelFile} Not Found");
		class_exists($m)||app::Error(500,"Model File {$modelFile} Does Not Contain Class {$m}");
		$class = new ReflectionClass($m);
		$GLOBALS['APP']['model'][$m]=$class->newInstanceArgs($arguments);
		return $GLOBALS['APP']['model'][$m];
	}
}

function S($lib)
{
	$arguments=func_get_args();
	$arr=explode('/',array_shift($arguments));
	$l=end($arr);
	$GLOBALS['APP']['lib'][$l]=isset($GLOBALS['APP']['lib'][$l])?$GLOBALS['APP']['lib'][$l]:$l;
	if($GLOBALS['APP']['lib'][$l] instanceof $l)
	{
		return $GLOBALS['APP']['lib'][$l];
	}
	else
	{
		if(is_file($classFile=LIB_PATH.$lib.'.class.php'))
		{
			require_once $classFile;
			class_exists($l)||app::Error(500,"Library File {$classFile} Does Not Contain Class {$l}");
			$class = new ReflectionClass($l);
			$GLOBALS['APP']['lib'][$l]=$class->newInstanceArgs($arguments);
			return $GLOBALS['APP']['lib'][$l];
		}
		else if(is_file($file=LIB_PATH.$lib.'.php'))
		{
			unset($GLOBALS['APP']['lib'][$l]);
			return require_once $file;
		}
		else
		{
			return app::Error(500,"Library File {$l}  Not Found");
		}
	}
}

function V($v,$data=null,$fileCacheMinute=0)
{
	if($fileCacheMinute||(is_int($data)&&($data>0)))
	{
		$cacheTime=$fileCacheMinute?$fileCacheMinute:$data;
		$GLOBALS['APP']['cache']=['time'=>intval($cacheTime*60),'file'=>true];
	}
	$callback=$GLOBALS['APP']['cache']['file']?function(&$buffer)
	{
		$expire=intval($_SERVER['REQUEST_TIME']+$GLOBALS['APP']['cache']['time']);
		$router=$GLOBALS['APP']['router'];
		$file=is_object($router[1])?app::fileCache($router[0]):app::fileCache($router);
		is_writable(VAR_PATH_HTML)&&file_put_contents($file,$buffer)&&touch($file,$expire);
		return $file;
	}:null;
	return template($v,is_array($data)?$data:null,$callback);
}

function C($time,$file=false)
{
	$seconds=intval($time*60);
	$GLOBALS['APP']['cache']=['time'=>$seconds,'file'=>$file];
	$now=$_SERVER['REQUEST_TIME'];
	$lastExpire=isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']):0;
	if($lastExpire&&($lastExpire+$seconds-$now>0))
	{
		header('Expires: '.gmdate('D, d M Y H:i:s',$lastExpire+$seconds).' GMT');
		header('Cache-Control: public, max-age='.($lastExpire+$seconds-$now));
		exit(header('Last-Modified: '.gmdate('D, d M Y H:i:s',$lastExpire). ' GMT',true,304));
	}
	header('Expires: '.gmdate('D, d M Y H:i:s',$now+$seconds).' GMT');
	header("Cache-Control: public, max-age={$seconds}");
	header('Last-Modified: '.gmdate('D, d M Y H:i:s',$now).' GMT');
}

function template($v,Array $_data_=null,Closure $callback=null)
{
	if((is_file($_v_=VIEW_PATH.$v.'.php'))||(is_file($_v_=VIEW_PATH.$v)))
	{
		(is_array($_data_)&&!empty($_data_))&&extract($_data_);
		if($callback)
		{
			ob_start()&&include $_v_;
			$contents=ob_get_contents();
			ob_end_clean();
			return $callback($contents);
		}
		return include $_v_;
	}
	return app::Error(404,"Template File {$_v_} Not Found");
}

class Request
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
	public static function isPost()
	{
		return isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']==='POST';
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
	public static function ua($default=null)
	{
		return isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:$default;
	}
	public static function refer($default=null)
	{
		return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$default;
	}
	public static function form(Array $rule,$callback=true,$post=true)
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
		return Validate::rule($rule,$data,$callback);
	}
	private static function getVar($origin,$var,$default=null,$clean=false)
	{
		if(is_array($var)&&$var)
		{
			$data=[];
			foreach ($var as $k)
			{
				$data[$k]=isset($origin[$k])?($clean?self::clean($origin[$k],$clean):trim($origin[$k])):$default;
			}
			return $data;
		}
		return isset($origin[$var])?($clean?self::clean($origin[$var],$clean):trim($origin[$var])):$default;
	}
	public static function clean($val,$type=null)
	{
		switch ($type)
		{
			case 'int':
				return intval($val);
			case 'float':
				return floatval($val);
			case 'string':
				return trim(strval($val));
			case 'xss':
				return filter_var(htmlspecialchars(strip_tags($val),ENT_QUOTES),FILTER_SANITIZE_STRING);
			case 'html':
				return trim(strip_tags($val));
			case 'en':
				return preg_replace('/[\x80-\xff]/','',$val);
			default:
				return $type?sprintf($type,$val):trim($val);
		}
	}
	public static function __callStatic($method,$args)
	{
		return app::Error(500,"Call Error Static Method {$method} In Class ".get_called_class());
	}
}

class Validate
{
	public static function rule($rule,$data,$callback=true)
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
						if($msg instanceof Closure)
						{
							$data[$k]=$msg($data[$k],$k);
						}
						else if(is_int($type))
						{
							$switch[$k]=$msg;
						}
						else if(!self::check($data[$k],$type))
						{
							self::check($data[$k],$type,$msg);
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
			return $callback?(($callback instanceof Closure)?$callback($data,$e):json($data)):false;
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
				case 'eq': return $item==$val;
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

class DB
{
	private static $pdo;
	final private static function init($dbDsn,$dbUser,$dbPass)
	{
		if(!self::$pdo)
		{
			$options=[PDO::ATTR_PERSISTENT=>TRUE,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_TIMEOUT=>1];
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
					return app::Error(500,$e->getMessage());
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
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			app::set('sys-sql-last',$sql);
			return self::ready()->exec($sql);
		}
		catch (PDOException $e)
		{
			return app::Error(500,"Run Sql [ {$sql} ] Error : ".$e->getMessage());
		}
	}
	final public static function getData($sql)
	{
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			app::set('sys-sql-last',$sql);
			$rs=self::ready()->query($sql);
			return $rs===false?[]:$rs->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			return app::Error(500,"Run Sql [ {$sql} ] Error : ".$e->getMessage());
		}
	}
	final public static function getLine($sql)
	{
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			app::set('sys-sql-last',$sql);
			$rs=self::ready()->query($sql);
			return $rs===false?[]:$rs->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			return app::Error(500,"Run Sql [ {$sql} ] Error : ".$e->getMessage());
		}
	}
	final public static function getVar($sql)
	{
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			app::set('sys-sql-last',$sql);
			$rs=self::ready()->query($sql);
			return $rs===false?null:$rs->fetchColumn();
		}
		catch (PDOException $e)
		{
			return app::Error(500,"Run Sql [ {$sql} ] Error : ".$e->getMessage());
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
		return app::Error(500,"Call Error Method {$method} In Class ".get_called_class());
	}
}

function __autoload($class)
{
	if(is_file($file=MODEL_PATH."{$class}.php")||is_file($file=CONTROLLER_PATH."{$class}.php")||is_file($file=LIB_PATH.'Class'.DIRECTORY_SEPARATOR."{$class}.class.php")||is_file($file=LIB_PATH."{$class}.class.php"))
	{
		require_once $file;
		return class_exists($class)||app::Error(500,"File {$file} Does Not Contain Class {$class}");
	}
	return false;
}
function session($key,$val=null,$delete=false)
{
	isset($_SESSION)||session_start();
	if(is_null($val))
	{
		if($delete)
		{
			foreach(is_array($key)?$key:[$key] as $k)
			{
				unset($_SESSION[$k]);
			}
			return $_SESSION;
		}
		return Request::session($key,null,false);
	}
	return $_SESSION[$key]=is_array($val)?json_encode($val,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):$val;
}
function cookie($key,$val=null,$expire=0)
{
	if(is_null($val))
	{
		return Request::cookie($key,null,false);
	}
	return call_user_func_array('setcookie',func_get_args());
}
function json(Array $data,$callback=null)
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
	exit(header('Cache-Control:no-cache, no-store, max-age=0, must-revalidate',true));
}
function baseUrl($path=null)
{
	if(is_int($path))
	{
		$router=$GLOBALS['APP']['router'];
		return isset($router[$path])?$router[$path]:null;
	}
	else
	{
		$protocol=(isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off'))?"https":"http";
		$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		$path=is_null($path)?null:(is_bool($path)?($path?$_SERVER['REQUEST_URI']:'/'.implode('/',$GLOBALS['APP']['router'])):'/'.ltrim($path,'/'));
		return "{$protocol}://{$host}{$path}";
	}
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
function csrf_token($check=false,$name='_token',Closure $callback=null)
{
	isset($_SESSION)||session_start();
	$token=isset($_SESSION['csrf_token'])?$_SESSION['csrf_token']:null;
	if($check)
	{
		if(!(isset($_REQUEST[$name])&&$_REQUEST[$name]===$token))
		{
			return $callback?$callback($token):app::Error(403,'Csrf Token Not Match');
		}
		return true;
	}
	else
	{
		if(!$token)
		{
			$token=md5(uniqid());
			$_SESSION['csrf_token']=$token;
		}
		return $token;
	}
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
			if(substr($lastmessage,3,1)!='-' || empty($lastmessage))
			{
				break;
			}
			$lastmessage=fgets($fp,128);
		}
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
		return false;
	}
}

