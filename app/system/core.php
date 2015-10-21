<?php

/**
 * @author suconghou 
 * @blog http://blog.suconghou.cn
 * @link http://github.com/suconghou/mvc
 * @version 1.8.9
 */
/**
* APP 主要控制类
*/

class App
{
	private static $global;
	/**
	 * 启动入口
	 */
	public static function start()
	{
		self::set('sys-start-time',microtime(true));
		self::set('sys-start-memory',memory_get_usage());
		error_reporting(DEBUG?E_ALL:0);
		set_include_path(LIB_PATH);
		date_default_timezone_set('PRC');
		set_error_handler(array('app','Error'));
		set_exception_handler(array('app','Error'));
		register_shutdown_function(array('app','Shutdown'));
		defined('DEFAULT_ACTION')||define('DEFAULT_ACTION','index');
		defined('DEFAULT_CONTROLLER')||define('DEFAULT_CONTROLLER','home');
		defined('STDIN')||(defined('GZIP')?ob_start("ob_gzhandler"):ob_start());
		return defined('STDIN')?self::runCli():self::process(self::init());
	}
	/**
	 * CLI运行入口
	 */
	private static function runCli()
	{
		$script=array_shift($GLOBALS['argv']);
		$phar=substr(ROOT,0,7)=='phar://';
		if($GLOBALS['argc']>1)
		{
			$_SERVER['REQUEST_URI']=null;
			$phar||chdir(ROOT);
			$router=$GLOBALS['argv'];
			$ret=self::regexRouter('/'.implode('/',$router));
			return is_object($ret)?$ret:(($GLOBALS['APP']['router']=$ret?$ret:$router)&&self::run($GLOBALS['APP']['router']));
		}
		else
		{
			if($phar)
			{
				return self::run(array(DEFAULT_CONTROLLER,DEFAULT_ACTION));
			}
			else
			{
				try
				{
					$pharName=rtrim($script,'php').'phar';
					$path=ROOT.$pharName;
					is_file($path) && unlink($path);
					$phar=new Phar($path,FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,$pharName);
					$phar->startBuffering();
					$dirObj=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT),RecursiveIteratorIterator::SELF_FIRST);
					foreach ($dirObj as $file)
					{
						if(preg_match('/\\.php$/i',$file))
						{
							$phar->addFromString(substr($file,strlen(ROOT)),php_strip_whitespace($file));
						}
					}
					$stub="<?php Phar::mapPhar('$pharName');require 'phar://{$pharName}/{$script}';__HALT_COMPILER();";
					$phar->setStub($stub);
					$phar->stopBuffering();
					echo "{$phar->count()} Files Stored In ".$path.PHP_EOL;
				}
				catch(Exception $e)
				{
					echo $e->getMessage().PHP_EOL;
				}
			}
		}
	}
	/**
	 * 初始化相关
	 */
	private static function init()
	{
		(isset($_SERVER['REQUEST_URI'][defined('MAX_URL_LENGTH')?MAX_URL_LENGTH:80]))&&self::Error(414,'Request uri too long ! ');
		list($uri)=explode('?',$_SERVER['REQUEST_URI']);
		if(strpos($uri,$_SERVER['SCRIPT_NAME'])!==false)
		{
			$uri=str_replace($_SERVER['SCRIPT_NAME'],null,$uri);
		}
		$router=self::regexRouter($uri);
		if($router)
		{
			return $router;
		}
		else
		{
			$uri=explode('/',$uri);
			foreach ($uri as $segment)
			{
				if(!empty($segment))
				{
					$router[]=$segment;
				}
			}
		}
		if(empty($router[0]))
		{
			$router=array(DEFAULT_CONTROLLER,DEFAULT_ACTION);
		}
		else if(empty($router[1]))
		{
			if(preg_match('/^[a-z]\w{0,20}$/i',$router[0]))
			{
				$router=array(strtolower($router[0]),DEFAULT_ACTION);
			}
			else
			{
				return self::Error(404,'Request Controller '.$router[0].' Error ! ');
			}
		}
		else //控制器和动作全部需要过滤
		{
			if(!preg_match('/^[a-z]\w{0,20}$/i',$router[0]))
			{
				return self::Error(404,'Request Controller '.$router[0].' Error ! ');
			}
			if(!preg_match('/^[a-z]\w{0,20}$/i',$router[1]))
			{
				return self::Error(404,'Request Action '.$router[0].'=>'.$router[1].' Error ! ');
			}
			$router[0]=strtolower($router[0]);
		}
		return $router;
	}
	/**
	 *  缓存检测
	 */
	private static function process($router)
	{
		if(!is_object($router))
		{
			$GLOBALS['APP']['router']=$router;
			$file=is_object($router[1])?self::fileCache($router[0]):self::fileCache($router);
			if(is_file($file))
			{
				$expire=filemtime($file);
				$now=time();
				if($now<$expire)
				{
					header('Expires: '.gmdate('D, d M Y H:i:s',$expire).' GMT');
					header('Cache-Control: max-age='.($expire-$now));
					header('X-Cache: Hit',true);
					if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
					{
						return header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);	 
					}
					else
					{
						header('Last-Modified: '.gmdate('D, d M y H:i:s',$now).' GMT',true,200);	 
						return readfile($file);
					}
				}
				else
				{
					try
					{
						unlink($file);
					}
					catch(Exception $e)
					{
						app::log($e->getMessage(),'ERROR');
					}
					return self::run($router);
				}
			}
			else
			{
				return self::run($router);
			}
		}
	}
	/**
	 * 内部转向,转到其他控制器的方法执行,可传递参数,捕获返回
	 */
	public static function run($router)
	{
		//含有回调的,0为对应URL,1为回调函数
		$router=is_array($router)?$router:func_get_args();
		$router=isset($router[1])?$router:array($router[0],DEFAULT_ACTION);
		if(is_object($router[1]))
		{
			return call_user_func_array($router[1],array_slice($router,2));
		}
		if(is_file($path=CONTROLLER_PATH.$router[0].'.php'))
		{
			$controllerName=$router[0];
			$action=$router[1];
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
			return self::Error(404,'Request Controller '.$router[0].' Not Found ! ');
		}
		require_once $path;
		class_exists($controllerName)||self::Error(404,'Request Controller Class '.$controllerName.' Not Found ! ');
		method_exists($controllerName,$action)||self::Error(404,'Request Controller Class '.$controllerName.' Does Not Contain Method '.$action);
		$GLOBALS['APP']['controller'][$controllerName]=isset($GLOBALS['APP']['controller'][$controllerName])?$GLOBALS['APP']['controller'][$controllerName]:$controllerName;
		if(!$GLOBALS['APP']['controller'][$controllerName] instanceof $controllerName)
		{
			$GLOBALS['APP']['controller'][$controllerName]=new $controllerName($router);
		}
		return is_callable(array($GLOBALS['APP']['controller'][$controllerName],$action))?call_user_func_array(array($GLOBALS['APP']['controller'][$controllerName],$action),array_slice($router,$param)):self::Error(404,'Request Controller Class '.$controllerName.' Method '.$action.' Is Not Callable');
	}
	/**
	 * 正则路由,参数一正则,参数二数组形式的路由表或者回调函数
	 */
	public static function route($regex,$arr)
	{
		if($arr instanceof Closure)
		{
			$GLOBALS['APP']['regexRouter'][$regex]=$arr;
		}
		else
		{
			$GLOBALS['APP']['regexRouter'][]=array($regex,$arr);
		}
	}
	public static function log($msg,$type='DEBUG')
	{
		if(is_writable(VAR_PATH.'log'))
		{
			$path=VAR_PATH.'log'.DIRECTORY_SEPARATOR.date('Y-m-d').'.log';
			$msg=strtoupper($type).'-'.date('Y-m-d H:i:s').' ==> '.(is_scalar($msg)?$msg:PHP_EOL.print_r($msg,true)).PHP_EOL;
			//error消息和开发模式,测试模式全部记录
			if(DEBUG || strtoupper($type)=='ERROR')
			{
				error_log($msg,3,$path);
			}
		}
	}
	/**
	 * 正则路由分析器
	 */
	private static function regexRouter($uri)
	{
		if(!empty($GLOBALS['APP']['regexRouter']))
		{
			foreach ($GLOBALS['APP']['regexRouter'] as $regex=>$item)
			{
				$regex=is_array($item)?$item[0]:$regex;
				if(preg_match('/^'.$regex.'$/', $uri,$matches))
				{
					$url=$matches[0];
					unset($matches[0],$GLOBALS['APP']['regexRouter']);
					if(is_object($item)) 
					{
						//传入URL,作为闭包时的文件缓存依据
						return array_merge(array($url,$item),$matches);
					}
					else
					{
						if(is_string($item[1])) //plugin loader
						{
							return call_user_func_array('S',array_merge(array($item[1]),$matches));
						}
						return array_merge($item[1],$matches);
					}
				}
			}
			unset($GLOBALS['APP']['regexRouter']);
		}
		return array();
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
				return array('time'=>round((microtime(true)-self::get('sys-start-time',0)),4),'memory'=>byteFormat(memory_get_usage()-self::get('sys-start-memory',0)),'query'=>self::get('sys-sql-count',0));
		}
	}
	/**
	 * 计算缓存位置,或删除缓存,传入路由数组或路由字符串
	 */
	public static function fileCache($router=array(),$delete=false)
	{
		if(empty($router))
		{
			$router=DEFAULT_CONTROLLER.'/'.DEFAULT_ACTION;
		}
		else if(is_array($router))
		{
			$router=implode('/',$router);
		}
		$cacheFile=VAR_PATH.'html'.DIRECTORY_SEPARATOR.md5(baseUrl($router)).'.html';
		if($delete)
		{
			return is_file($cacheFile)&&unlink($cacheFile);
		}
		else
		{
			return $cacheFile;
		}
	}
	/**
	 * 全局变量获取设置
	 */
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
		$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT).'.config';
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			$data[$key]=$value;
		}
		else
		{
			$data=array($key=>$value);
		}
		return file_put_contents($file,serialize($data))?true:false;
	}
	public static function getItem($key,$default=null)
	{
		$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT).'.config';
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			return isset($data[$key])?$data[$key]:$default;
		}
		return $default;
	}
	public static function clearItem($key=null)
	{
		$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT).'.config';
		if(is_null($key))
		{
			return unlink($file);
		}
		else
		{
			if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
			{
				unset($data[$key]);
				return file_put_contents($file,serialize($data))?true:false;
			}
			return true;
		}
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
		$config=isset(self::$global[$configFile])?self::$global[$configFile]:(self::$global[$configFile]=include_once ROOT.$configFile);
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
	public static function emit($event,$arguments=array())
	{
		if(!empty(self::$global['event'][$event]))
		{
			return call_user_func_array(self::$global['event'][$event],is_array($arguments)?$arguments:array($arguments));
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
		return self::Error(500,'Call Error Static Method '.$method.' In Class '.get_called_class());
	}
	//异常处理 404 500等
	public static function Error($errno,$errstr=null,$errfile=null,$errline=null)
	{
		if((DEBUG<2)&&in_array($errno,array(E_NOTICE,E_WARNING)))
		{
			return;
		}
		else if($errno instanceof Exception)
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
		if(in_array($errno,array(400,403,404,414,500,502,503,504)))
		{
			$errormsg="ERROR({$errno}) {$errstr}";
			$code=$errno;
		}
		else
		{
			$errormsg="ERROR({$errno}) {$errstr} in {$errfile} on line {$errline} ";
			$code=500;
		}
		$errno==404 || app::log($errormsg,'ERROR');
		defined('STDIN')||(app::get('sys-error')&&exit('Error Found In Error Handler'))||(http_response_code($code)&&app::set('sys-error',true));
		if(DEBUG||defined('STDIN'))
		{
			$li=array();
			foreach($backtrace as $trace)
			{
				if(isset($trace['file'],$trace['type']))
				{
					$li[]=$trace['file'].'=>'.$trace['class'].$trace['type'].$trace['function'].'() on line '.$trace['line'];
				}
			}
			$li=implode(defined('STDIN')?PHP_EOL:'</p><p>',array_reverse($li));
			echo defined('STDIN')?($errfile?$errormsg.PHP_EOL.$li.PHP_EOL:exit($errormsg.PHP_EOL.$li.PHP_EOL)):exit("<div style='margin:2% auto;width:80%;box-shadow:0 0 5px #f00;padding:1%;'><p>{$errormsg}</p><p>{$li}</p></div>");
		}
		else
		{
			$errorController=(isset($GLOBALS['APP']['router'][0])&&is_file(CONTROLLER_PATH.$GLOBALS['APP']['router'][0].'.php'))?$GLOBALS['APP']['router'][0]:DEFAULT_CONTROLLER;
			$errorRouter=array($errorController,$errno==404?(defined('ERROR_PAGE_404')?ERROR_PAGE_404:'Error404'):(defined('ERROR_PAGE_500')?ERROR_PAGE_500:'Error500'),$errormsg);
			$errorPage="<title>Error..</title><center><span style='font-size:300px;color:gray;font-family:黑体'>{$code}...</span></center>";
			if(method_exists($errorController,$errorRouter[1]))//当前已加载的控制器或默认控制器中含有ERROR处理
			{
				$GLOBALS['APP']['controller'][$errorController]=isset($GLOBALS['APP']['controller'][$errorController])?$GLOBALS['APP']['controller'][$errorController]:$errorController;
				if(!$GLOBALS['APP']['controller'][$errorController] instanceof $errorController)
				{
					$GLOBALS['APP']['controller'][$errorController]=new $errorController($errorRouter);
				}
				$errorPage=is_callable(array($GLOBALS['APP']['controller'][$errorController],$errorRouter[1]))?call_user_func_array(array($GLOBALS['APP']['controller'][$errorController],$errorRouter[1]),array($errormsg)):$errorPage;
			}
			exit($errorPage);
		}
	}
	public static function Shutdown()
	{
		$lastError=error_get_last();
		if(!empty($lastError))
		{
			$errormsg="ERROR({$lastError['type']}) {$lastError['message']} in {$lastError['file']} on line {$lastError['line']} ";
			return app::log($errormsg,'ERROR');
		}
	}

}
// End of class app

//加载model
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
		(is_file($modelFile) && require_once $modelFile)||app::Error(500,'Load Model '.$m.' Failed , Mdoel File '.$modelFile.' Not Found ! ');
		class_exists($m)||app::Error(500,'Model File '.$modelFile .' Does Not Contain Class '.$m);
		$class = new ReflectionClass($m);
		$GLOBALS['APP']['model'][$m]=$class->newInstanceArgs($arguments);
		return $GLOBALS['APP']['model'][$m];
	}
}
//加载类库
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
			class_exists($l)||app::Error(500,'Library File '.$classFile .' Does Not Contain Class '.$l);
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
			return app::Error(500,'Library File '.$l.'  Not Found ! ');
		}
	}
}
//加载视图,传递参数,设置缓存
function V($v,$data=null,$fileCacheMinute=0)
{
	if($fileCacheMinute||(is_int($data)&&($data>0)))
	{
		$cacheTime=$fileCacheMinute?$fileCacheMinute:$data;
		$GLOBALS['APP']['cache']['time']=intval($cacheTime*60);
		$GLOBALS['APP']['cache']['file']=true;
	}
	if(!empty($GLOBALS['APP']['cache']['file']))
	{
		$callback=function($buffer)
		{
			$expire=intval(time()+$GLOBALS['APP']['cache']['time']);
			$router=$GLOBALS['APP']['router'];
			//与缓存检测时一致,闭包路由也可以使用文件缓存
			$file=is_object($router[1])?app::fileCache($router[0]):app::fileCache($router);
			file_put_contents($file,$buffer);
			touch($file,$expire);
			defined('STDIN')||(ob_end_flush()&&flush());
		};
	}
	else
	{
		$callback=null;
	}
	return template($v,is_array($data)?$data:null,$callback);
}
//缓存,第一个参数为缓存时间,第二个为是否文件缓存
function C($time,$file=false)
{
	$seconds=intval($time*60);
	$GLOBALS['APP']['cache']['time']=$seconds;
	$GLOBALS['APP']['cache']['file']=$file;
	///使用了http缓存,在此处捕获缓存
	$now=time();
	$expiresTime=time()+$seconds;
	$lastExpire = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?$_SERVER['HTTP_IF_MODIFIED_SINCE']:0;
	if($lastExpire&&((strtotime($lastExpire)+$seconds-$now)>0))
	{
		$lastExpire=strtotime($lastExpire);
		header("Expires: ".gmdate("D, d M Y H:i:s",$lastExpire+$seconds)." GMT");
		header("Cache-Control: max-age=".(($lastExpire+$seconds)-$now));
		header('Last-Modified: ' . gmdate('D, d M y H:i:s',$lastExpire). ' GMT');
		exit(http_response_code(304));
	}
	else
	{
		header("Expires: ".gmdate("D, d M Y H:i:s", $expiresTime)." GMT");
		header("Cache-Control: max-age=".$seconds);
		header('Last-Modified: ' . gmdate('D, d M y H:i:s',$now). ' GMT'); 
	}
}

function template($v,Array $_data_=null,Closure $callback=null)
{
	if((is_file($_v_=VIEW_PATH.$v.'.php'))||(is_file($_v_=VIEW_PATH.$v)))
	{
		header('X-Xss-Protection:1; mode=block',true);
		header('X-Frame-Options:DENY',true);
		(is_array($_data_)&&!empty($_data_))&&extract($_data_);
		return $callback?((include $_v_)&&$callback(ob_get_contents())):include $_v_;
	}
	else
	{
		return app::Error(404,'Template File '.$_v_.' Not Found !');
	}
}

/**
* Request 用户来访信息,使用静态访问
*/
class Request
{
	public static function post($key=null,$default=null,$clean=false)
	{
		if($key)
		{
			return self::getVar('post',$key,$default,$clean);
		}
		else
		{
			$data=array();
			foreach ($_POST as $key => $value)
			{
				$data[$key]=self::getVar('post',$key,$default,$clean);
			}
			return $data;
		}

	}
	public static function get($key=null,$default=null,$clean=false)
	{
		if($key)
		{
			return self::getVar('get',$key,$default,$clean);
		}
		else
		{
			$data=array();
			foreach ($_GET as $key => $value)
			{
				$data[$key]=self::getVar('get',$key,$default,$clean);
			}
			return $data;
		}

	}
	public static function cookie($key=null,$default=null,$clean=false)
	{
		if($key)
		{
			return self::getVar('cookie',$key,$default,$clean);
		}
		else
		{
			$data=array();
			foreach ($_COOKIE as $key => $value)
			{
				$data[$key]=self::getVar('cookie',$key,$default,$clean);
			}
			return $data;
		}

	}
	public static function session($key=null,$default=null)
	{
		isset($_SESSION)||session_start();
		if($key)
		{
			return self::getVar('session',$key,$default);
		}
		else
		{
			$data=array();
			foreach ($_SESSION as $key => $value)
			{
				$data[$key]=self::getVar('session',$key);
			}
			return $data;
		}

	}
	public static function server($key=null,$default=null)
	{
		if($key)
		{
			return self::getVar('server',$key,$default);
		}
		else
		{
			$data=array();
			foreach ($_SERVER as $key => $value)
			{
				$data[$key]=self::getVar('server',$key);
			}
			return $data;
		}
	}
	//获取http请求正文,默认当做json处理
	public static function input($key=null,$default=null,$json=true)
	{
		$str=file_get_contents('php://input');
		if($json)
		{
			$data=json_decode($str,true);
		}
		else
		{
			parse_str($str,$data);
		}
		if($key)
		{
			return isset($data[$key])?$data[$key]:$default;
		}
		return $data;
	}
	public static function ip($default=null)
	{
		$ip=getenv('REMOTE_ADDR');
		return $ip?$ip:(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$default);
	}
	public static function info($key=null,$default=null)
	{
		$data=array('ip'=>self::ip(),'ajax'=>self::isAjax(),'ua'=>self::ua(),'refer'=>self::refer(),'protocol'=>(isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS']) != 'off'))?"https":"http");
		if($key) {return isset($data[$key])?$data[$key]:$default;}
		return $data;
	}
	public static function serverInfo($key=null,$default=null)
	{
		$info=array('php_os'=>PHP_OS,'php_sapi'=>PHP_SAPI,'php_vision'=>PHP_VERSION,'post_max_size'=>ini_get('post_max_size'),'max_execution_time'=>ini_get('max_execution_time'),'server_ip'=>gethostbyname($_SERVER['SERVER_NAME']),'upload_max_filesize'=>ini_get('file_uploads')?ini_get('upload_max_filesize'):0);
		if($key) {return isset($info[$key])?$info[$key]:$default;}
		return $info;
	}
	public static function isCli()
	{
		return defined('STDIN')&&defined('STDOUT');
	}
	public static function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}
	public static function isPjax()
	{
		return isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'];
	}
	public static function isPost()
	{
		return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
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
	public static function filterPost(Array $rule,Closure $callback=null,$clean=false)
	{
		$allowed=array();
		foreach ($rule as $key => $value)
		{
			$allowed[]=is_int($key)?$value:$key;
		}
		$post=self::cleanData($_POST,$allowed,$clean);
		return Validate::rule($rule,$post,$callback);
	}
	public static function filterGet(Array $rule,Closure $callback=null,$clean=false)
	{
		$allowed=array();
		foreach ($rule as $key => $value)
		{
			$allowed[]=is_int($key)?$value:$key;
		}
		$get=self::cleanData($_GET,$allowed,$clean);
		return Validate::rule($rule,$get,$callback);
	}
	private static function cleanData(Array $input,Array $allowed,$clean=false)
	{
		foreach ($input as $key => $value)
		{
			if(!in_array($key,$allowed))
			{
				unset($input[$key]);
			}
		}
		foreach ($allowed as $item)
		{
			if(!isset($input[$item]))
			{
				$input[$item]=null;
			}
			else if($clean)
			{
				$input[$item]=self::clean($input[$item],$clean);
			}
		}
		return $input;
	}
	private static function getVar($type,$var,$default=null,$clean=false)
	{
		switch ($type)
		{
			case 'post':
				return isset($_POST[$var])?($clean?self::clean($_POST[$var],$clean):$_POST[$var]):$default;
			case 'get':
				return isset($_GET[$var])?($clean?self::clean($_GET[$var],$clean):$_GET[$var]):$default;
			case 'cookie':
				return isset($_COOKIE[$var])?($clean?self::clean($_COOKIE[$var],$clean):$_COOKIE[$var]):$default;
			case 'server':
				return isset($_SERVER[$var])?$_SERVER[$var]:$default;
			case 'session':
				return isset($_SESSION[$var])?$_SESSION[$var]:$default;
			default:
				return false;
		}
	}
	public static function clean($val,$type=null)
	{
		switch ($type)
		{
			case 'int':
				return intval($val);
			case 'float':
				return floatval($val);
			case 'xss':
				return filter_var(htmlspecialchars(strip_tags($val),ENT_QUOTES),FILTER_SANITIZE_STRING);
			case 'html':
				return strip_tags($val);
			case 'en':
				return preg_replace('/[\x80-\xff]/','',$val);
			default:
				return $type?sprintf($type,$val):trim($val);
		}
	}
	public static function __callStatic($method,$args)
	{
		return app::Error(500,'Call Error Static Method '.$method.' In Class '.get_called_class());
	}
}

/**
* 验证类,使用静态方法
*/
class Validate
{
	public static function rule($rule,$data,Closure $callback=null)
	{
		try
		{
			foreach($rule as $k=>&$item)
			{
				if(isset($data[$k])&&$data[$k])//存在要验证的数据
				{
					foreach($item as $type=>$msg)
					{
						if($msg instanceof Closure)
						{
							$ret=$msg($data[$k],$k);
							if(!$ret)
							{
								throw new Exception($k,-120);
							}
							else if($ret!==true)
							{
								$data[$k]=$ret;
							}
						}
						else if(is_int($type))
						{
							$sw[$k]=$msg;
						}
						else if(stripos($type,'='))
						{
							self::mixedChecker($data[$k],explode('=',$type),$msg);
						}
						else
						{
							self::typeChecker($data[$k],$type,$msg);
						}
					}
				}
				else if(isset($item['require'])) //标记为require,却不存在
				{
					throw new Exception($item['require'],-100);
				}
			}

		}
		catch(Exception $e)
		{
			$data=array('code'=>$e->getCode(),'msg'=>$e->getMessage());
			return $callback?$callback(json_encode($data),$data):false;
		}
		if(!empty($sw))
		{
			foreach($sw as $from=>$to)
			{
				$data[$to]=$data[$from];
				unset($data[$from]);
			}
		}
		return $data; //数据全部校验通过
	}
	private static function typeChecker($item,$type,$msg)
	{
		switch ($type)
		{
			case 'require':
				if(empty($item))
				{
					throw new Exception($msg, -101);
				}
				break;
			case 'email':
				if(!self::email($item))
				{
					throw new Exception($msg, -102);
				}
				break;
			case 'username':
				if(!self::username($item))
				{
					throw new Exception($msg, -103);
				}
				break;
			case 'password':
				if(!self::password($item))
				{
					throw new Exception($msg, -104);
				}
				break;
			case 'phone':
				if(!self::phone($item))
				{
					throw new Exception($msg, -105);
				}
				break;
			case 'url':
				if(!self::url($item))
				{
					throw new Exception($msg, -106);
				}
				break;
			case 'ip':
				if(!self::ip($item))
				{
					throw new Exception($msg, -107);
				}
				break;
			case 'idcard':
				if(!self::idcard($item))
				{
					throw new Exception($msg, -108);
				}
				break;
			default:
				if(!self::this($type,$item))
				{
					throw new Exception($msg, -109);
				}
				break;
		}
	}
	private static function mixedChecker($item,$mixed,$msg)
	{
		switch ($mixed[0])
		{
			case 'minlength':
				if(strlen($item)<$mixed[1])
				{
					throw new Exception($msg, -201);
				}
				break;
			case 'maxlength':
				if(strlen($item)>$mixed[1])
				{
					throw new Exception($msg, -202);
				}
				break;
			case 'eq':
				if($item!=$mixed[1])
				{
					throw new Exception($msg, -203);
				}
				break;
			default:
				throw new Exception("Error Mixed Rule {$mixed[0]}", -500);
		}
	}
	public static function email($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	public static function phone($phone)
	{
		return preg_match("/^1[34578][0-9]{9}$/",$phone);
	}
	public static function url($url)
	{
		return filter_var($url, FILTER_VALIDATE_URL);
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
		if(is_numeric($username)) {return false;}
		return preg_match('/^[\w\x{4e00}-\x{9fa5}]{3,20}$/u', $username);
	}
	//数字/大写字母/小写字母/标点符号组成，四种都必有，8位以上
	public static function password($pass)
	{
		return preg_match('/^(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/',$pass);
	}
	//自定义正则验证
	public static function this($pattern,$subject)
	{
		return preg_match($pattern, $subject);
	}
}
/**
* model 层,可以静态方式使用
*/
class DB
{
	private static $pdo;

	final private static function init($dbDsn,$dbUser,$dbPass)
	{
		if(!self::$pdo)
		{
			$options=array(PDO::ATTR_PERSISTENT=>TRUE,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_TIMEOUT=>1);
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
			if(!empty(static::$initCmd) && is_array(static::$initCmd))
			{
				foreach (static::$initCmd as $cmd)
				{
					self::$pdo->exec($cmd);
				}
			}
		}
		return self::$pdo;
	}
	//运行Sql语句,不返回结果集,但会返回成功与否,不能用于select
	final public static function runSql($sql)
	{
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			return self::ready()->exec($sql);
		}
		catch (PDOException $e)
		{
			return app::Error(500,"Run Sql [ {$sql} ] Error : ".$e->getMessage());
		}
	}
	//运行Sql,以多维数组方式返回结果集
	final public static function getData($sql)
	{
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			$rs=self::ready()->query($sql);
			return $rs===false?array():$rs->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			return app::Error(500,"Run Sql [ {$sql} ] Error : ".$e->getMessage());
		}
	}
	//运行Sql,以数组方式返回结果集第一条记录
	final public static function getLine($sql)
	{
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			$rs=self::ready()->query($sql);
			return $rs===false?array():$rs->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			return app::Error(500,"Run Sql [ {$sql} ] Error : ".$e->getMessage());
		}
	}
	//运行Sql,返回结果集第一条记录的第一个字段值
	final public static function getVar($sql)
	{
		try
		{
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
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
			return call_user_func_array(array(self::$pdo,$method),$args);
		}
		return app::Error(500,'Call Error Method '.$method.' In Class '.get_called_class());
	}

}//end class db

function __autoload($class)
{
	if(is_file($modelFile=MODEL_PATH.$class.'.php'))
	{
		require_once $modelFile;
		return class_exists($class)||app::Error(500,'Load File '.$modelFile.' Succeed,But Not Found Class '.$class);
	}
	else if(is_file($controllerFile=CONTROLLER_PATH.$class.'.php'))
	{
		require_once $controllerFile;
		return class_exists($class)||app::Error(500,'Load File '.$controllerFile.' Succeed,But Not Found Class '.$class);
	}
	else if(is_file($libFile=LIB_PATH.'Class'.DIRECTORY_SEPARATOR."{$class}.class.php"))
	{
		require_once $libFile;
		return class_exists($class)||app::Error(500,'Load File '.$libFile.' Succeed,But Not Found Class '.$class);
	}
	else
	{
		return false;
	}
}
function session($key,$val=null,$delete=false)
{
	isset($_SESSION)||session_start();
	if(is_null($val))
	{
		if(is_array($key))
		{
			$res=array();
			foreach ($key as  $k)
			{
				$res[$k]=isset($_SESSION[$k])?$_SESSION[$k]:null;
			}
			return $res;
		}
		else if($delete)
		{
			unset($_SESSION[$key]);
		}
		else
		{
			return isset($_SESSION[$key])?$_SESSION[$key]:null;
		}
	}
	else
	{
		$_SESSION[$key]=is_array($val)?json_encode($val):$val;
	}

}
function cookie($key,$val=null,$expire=0)
{
	if(is_null($val))
	{
		if(is_array($key))
		{
			$res=array();
			foreach ($key as $k)
			{
				$res[$k]=isset($_COOKIE[$key])?$_COOKIE[$key]:null;
			}
			return $res;
		}
		else
		{
			return isset($_COOKIE[$key])?$_COOKIE[$key]:null;
		}
	}
	else
	{
		setcookie($key,is_array($val)?json_encode($val):$val,$expire);
	}

}
function json(Array $data,$callback=null)
{
	$data=json_encode($data,JSON_UNESCAPED_UNICODE);
	$callback=$callback===true?(empty($_GET['callback'])?null:$_GET['callback']):$callback;
	$data=$callback?$callback."(".$data.")":$data;
	header('Content-Type: text/'.($callback?'javascript':'json'),true,200);
	exit($data);
}
function byteFormat($size,$dec=2)
{
	$size=max($size,0);
	$unit=array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
	return $size>=1024?round($size/pow(1024,($i=floor(log($size,1024)))),$dec).' '.$unit[$i]:$size.' B';
}
//外部重定向,会立即结束脚本以发送header,内部重定向app::run(array);
function redirect($url,$timeout=0)
{
	$timeout=intval($timeout);
	if(in_array($timeout,array(0,301,302,307)))
	{
		header("Location: {$url}",true,$timeout);
	}
	else
	{
		header("Refresh: {$timeout}; url={$url}");
	}
	exit(header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate",true));
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
		$protocol=(isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? "https" : "http";
		$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		$path=is_null($path)?null:(is_bool($path)?($path?$_SERVER['REQUEST_URI']:'/'.implode('/',$GLOBALS['APP']['router'])):'/'.ltrim($path,'/'));
		return "{$protocol}://{$host}{$path}";
	}
}
function encrypt($input,$key=null)
{
	return str_replace(array('+','/','='),array('-','_',''),base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,md5($key),$input,MCRYPT_MODE_ECB,mcrypt_create_iv(16))));
}
function decrypt($input,$key=null)
{
	$input=str_replace(array('-','_'), array('+','/'), $input);
	if($mod=strlen($input)%4)
	{
		$input.=substr('====', $mod);
	}
	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128,md5($key),base64_decode($input),MCRYPT_MODE_ECB,mcrypt_create_iv(16)));
}
function csrf_token($check=false,$name='_token',Closure $callback=null)
{
	isset($_SESSION)||session_start();
	$token=isset($_SESSION['csrf_token'])?$_SESSION['csrf_token']:null;
	if($check)
	{
		if(!(isset($_REQUEST[$name]) && $_REQUEST[$name] === $token))
		{
			return $callback?$callback():app::Error(403,'Csrf Token Not Match ! ');
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
function sendMail($mailTo, $mailSubject, $mailMessage)
{
	try
	{
		$mailSubject = '=?utf-8?B?'.base64_encode($mailSubject).'?=';
		$mailMessage = chunk_split(base64_encode(preg_replace("/(^|(\r\n))(\.)/", "\1.\3", $mailMessage)));
		$headers  = "";
		$headers .= "MIME-Version:1.0\r\n";
		$headers .= "Content-type:text/html\r\n";
		$headers .= "Content-Transfer-Encoding: base64\r\n";
		$headers .= "From: ".MAIL_NAME."<".MAIL_USERNAME.">\r\n";
		$headers .= "Date: ".date("r")."\r\n";
		list($msec, $sec) = explode(" ", microtime());
		$headers .= "Message-ID: <".date("YmdHis", $sec).".".($msec * 1000000).".".MAIL_USERNAME.">\r\n";
		if(!$fp = fsockopen(MAIL_SERVER,defined('MAIL_PORT')?MAIL_PORT:25, $errno, $errstr, 3))
		{
			throw new Exception("Unable to connect to the SMTP server", 1);
		}
		stream_set_blocking($fp, true);
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != '220')
		{
			throw new Exception("CONNECT - ".$lastmessage, 2);
		}
		fputs($fp,"EHLO befen\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250)
		{
			throw new Exception("HELO/EHLO - ".$lastmessage, 3);
		}
		while(true)
		{
			if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage))
			{
				break;
			}
			$lastmessage = fgets($fp, 512);
		}
		fputs($fp, "AUTH LOGIN\r\n");$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334)
		{
			throw new Exception($lastmessage, 4);
		}
		fputs($fp, base64_encode(MAIL_USERNAME)."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334)
		{
			throw new Exception("AUTH LOGIN - ".$lastmessage, 5);
		}
		fputs($fp, base64_encode(MAIL_PASSWORD)."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 235)
		{
			throw new Exception("AUTH LOGIN - ".$lastmessage, 6);
		}
		fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", MAIL_USERNAME).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250)
		{
			fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", MAIL_USERNAME).">\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 250)
			{
				throw new Exception("MAIL FROM - ".$lastmessage,7);
			}
		}
		foreach(explode(';', $mailTo) as $touser)
		{
			$touser = trim($touser);
			if($touser)
			{
				fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
				$lastmessage = fgets($fp, 512);
				if(substr($lastmessage, 0, 3) != 250)
				{
					fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
					$lastmessage = fgets($fp, 512);
					throw new Exception("RCPT TO - ".$lastmessage,8);
				}
			}
		}
		fputs($fp, "DATA\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 354) 
		{
			throw new Exception("DATA - ".$lastmessage,9);
		}
		fputs($fp, $headers);
		fputs($fp, "To: ".$mailTo."\r\n");
		fputs($fp, "Subject: $mailSubject\r\n");
		fputs($fp, "\r\n\r\n");
		fputs($fp, "$mailMessage\r\n.\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250)
		{
			throw new Exception("END - ".$lastmessage,10);
		}
		fputs($fp, "QUIT\r\n");
		return true;
	}
	catch(Exception $e)
	{
		app::log($e->getMessage(),'ERROR');
		return false;
	}
}

// end of file core.php