<?php

/**
 * @author suconghou 
 * @blog http://blog.suconghou.cn
 * @link http://github.com/suconghou/mvc
 * @version 1.8.7
 */
/**
* APP 主要控制类
*/
final class App
{
	private static $global;
	/**
	 * 启动入口
	 */
	public static function start()
	{
		defined('STDIN')||(GZIP?ob_start("ob_gzhandler"):ob_start());
		define('APP_START_TIME',microtime(true));
		define('APP_START_MEMORY',memory_get_usage());
		date_default_timezone_set('PRC');//设置时区
		set_include_path(LIB_PATH);//此路径下可直接include
		error_reporting(DEBUG?E_ALL:0);
		set_error_handler(array('app','Error'));///异常处理
		register_shutdown_function(array('app','Shutdown'));
		return defined('STDIN')?self::runCli():self::process(self::init());
	}
	/**
	 * CLI运行入口
	 */
	private static function runCli()
	{
		$script=array_shift($GLOBALS['argv']);
		$phar=substr($script,-4)=='phar';
		if($GLOBALS['argc']>1)
		{
			$_SERVER['REQUEST_URI']=null;
			$phar||chdir(ROOT);
			$router=$GLOBALS['argv'];
			$router[1]=isset($router[1])?$router[1]:DEFAULT_ACTION;
			$GLOBALS['APP']['router']=$router;
			return self::run($router);
		}
		else
		{
			if($phar)
			{
				return self::run(array(DEFAULT_CONTROLLER,DEFAULT_ACTION));
			}
			else
			{
				ini_get("phar.readonly") and exit('Please set phar.readonly Off in php.ini'.PHP_EOL);
				$path=ROOT.rtrim($script,'php').'phar';
				(is_file($path))&&unlink($path);
				$p=new Phar($path,FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,'app.phar');
				$p->startBuffering();
				$p->buildFromDirectory(ROOT,'/\.php$/');
				$p->stopBuffering();
				return ("Files:{$p->count()}".PHP_EOL."Stored in:".$path.PHP_EOL);
			}
		}
	}
	/**
	 * 初始化相关
	 */
	private static function init()
	{
		(isset($_SERVER['REQUEST_URI'][MAX_URL_LENGTH]))&&self::Error(414,'Request uri too long ! ');
		list($uri)=explode('?',$_SERVER['REQUEST_URI']);
		if(strpos($uri, $_SERVER['SCRIPT_NAME'])!==false)
		{
			$uri=str_replace($_SERVER['SCRIPT_NAME'], null, $uri);
		}
		$router=self::regexRouter($uri);
		if($router)
		{
			return $router;
		}
		else
		{
			$uri=explode('/', $uri);
			foreach ($uri as  $segment)
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
		$GLOBALS['APP']['router']=$router;
		$file=is_object($router[1])?self::fileCache($router[0]):self::fileCache($router); 
		if(is_file($file))//存在缓存文件
		{
			$expire=filemtime($file);
			$now=time();
			if($now<$expire) ///缓存未过期
			{
				header("Expires: ".gmdate("D, d M Y H:i:s", $expire)." GMT");
				header("Cache-Control: max-age=".($expire-$now));
				if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
				{
					header('Last-Modified: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE']);	 
					return http_response_code(304);
				}
				else
				{
					header('Last-Modified: ' . gmdate('D, d M y H:i:s',$now). ' GMT');	 
					return readfile($file);
				}
			}
			else //缓存已过期
			{
				try
				{
					unlink($file);  ///删除过期文件
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
	/**
	 * 内部转向,可以指定一个方法,控制器保持原有的
	 */
	public static function run($router)
	{
		//含有回调的,0为对应URL,1为回调函数
		if(is_object($router[1]))
		{
			return call_user_func_array($router[1],array_slice($router,2));
		}
		if(!is_array($router))
		{
			$router=func_get_args();
		}
		if(!isset($router[1]))
		{
			$router=array(DEFAULT_CONTROLLER,$router[0]);
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
			$GLOBALS['APP']['controller'][$controllerName]=new $controllerName($router);///实例化控制器	
		}
		return call_user_func_array(array($GLOBALS['APP']['controller'][$controllerName],$action), array_slice($router,$param));//传入参数

	}
	/**
	 * 正则路由,参数一正则,参数二数组形式的路由表或者回调函数
	 */
	public static function route($regex,$arr)
	{
		if(is_object($arr))
		{
			$GLOBALS['APP']['regexRouter'][$regex]=$arr;
		}
		else
		{
			$arr=is_array($arr)?$arr:array(strval($arr));			
			$GLOBALS['APP']['regexRouter'][]=array($regex,$arr);
		}
	}
	public static function log($msg,$type='DEBUG')
	{
		if(is_writable(VAR_PATH.'log'))
		{
			$path=VAR_PATH.'log'.DIRECTORY_SEPARATOR.date('Y-m-d').'.log';
			$msg=strtoupper($type).'-'.date('Y-m-d H:i:s').' ==> '.(is_array($msg)?var_export($msg,true):$msg).PHP_EOL;
			//error消息和开发模式,测试模式全部记录
			if(DEBUG or strtoupper($type)=='ERROR')
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
		if(!empty($GLOBALS['APP']['regexRouter']))//存在正则路由
		{
			foreach ($GLOBALS['APP']['regexRouter'] as $regex=>$item)
			{
				$regex=is_array($item)?$item[0]:$regex;
				if(preg_match('/^'.$regex.'$/', $uri,$matches)) //能够匹配正则路由
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
						if(count($item[1])==1)
						{
							$item[1][]=DEFAULT_ACTION;
						}
						return array_merge($item[1],$matches);
					}
				}
			}
		}
		return array();
	}
	/**
	 * 异步(非阻塞)运行一个路由
	 * php-fpm下运行回调,其他申明了回调使用curl,否则使用socket
	 */
	public static function async($router,$callback=false)
	{
		$router=is_array($router)?implode('/', $router):$router;
		$url=filter_var($router, FILTER_VALIDATE_URL)?$router:baseUrl($router);
		if(function_exists('fastcgi_finish_request'))
		{
			fastcgi_finish_request();
			return is_callable($callback)?$callback(file_get_contents($url),$url):file_get_contents($url);
		}
		if($callback===false)
		{
			$parts = parse_url($url);
			$parts['path']=isset($parts['path'])?$parts['path']:'/';
			$parts['port']=isset($parts['port']) ? $parts['port'] : 80;
			$parts['query']=isset($parts['query'])?$parts['path'].'?'.$parts['query']:$parts['path'];
			if(function_exists('stream_socket_client'))
			{
				$callback = stream_socket_client($parts['host'].':'.$parts['port'], $errno, $errstr,3);
			}
			else if(function_exists('fsockopen'))
			{
				$callback = fsockopen($parts['host'],$parts['port'],$errno, $errstr,3);
			}
			if($callback)
			{
				stream_set_blocking($callback,0);
				$out = 'GET '.$parts['query']." HTTP/1.1\r\nHost: ".$parts['host']."\r\nConnection: Close\r\n\r\n";
				fwrite($callback, $out);
				flush();
				fclose($callback);
				return true;
			}
			return false;
		}
		else
		{
			$ch = curl_init();
			$curlOpt = array(CURLOPT_URL=>$url,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_TIMEOUT_MS=>1,CURLOPT_NOSIGNAL=>1, CURLOPT_HEADER=>0,CURLOPT_NOBODY=>1,CURLOPT_RETURNTRANSFER=>1);
			curl_setopt_array($ch, $curlOpt);
			curl_exec($ch);
			curl_close($ch);
			return true;
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
		try
		{
			$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT);
			if(is_file($file))
			{
				$data=unserialize(file_get_contents($file));
			}
			$data[$key]=$value;
			return file_put_contents($file,serialize($data));
		}
		catch(Exception $e)
		{
			app::log($e->getMessage(),'ERROR');
			return false;
		}
	}
	public static function getItem($key,$default=null)
	{
		try
		{
			$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT);
			if(is_file($file))
			{
				$data=unserialize(file_get_contents($file));
				return isset($data[$key])?$data[$key]:$default;
			}
			return $default;
		}
		catch(Exception $e)
		{
			app::log($e->getMessage(),'ERROR');
			return false;
		}
	}
	public static function clearItem($key=null)
	{
		try
		{
			$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT);
			if(is_null($key))
			{
				return is_file($file)&&unlink($file);
			}
			else
			{
				if(is_file($file))
				{
					$data=unserialize(file_get_contents($file));
					unset($data[$key]);
					return file_put_contents($file, serialize($data))?true:false;
				}
				return true;
			}
		}
		catch(Exception $e)
		{
			app::log($e->getMessage(),'ERROR');
			return false;
		}

	}
	public static function timer($function,$exit=false,$callback=null)
	{
		while(true)
		{
			is_callable($function)&&$function();
			if(is_callable($exit)?$exit():$exit)
			{
				return is_callable($callback)?$callback():null;
			}
		}
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
	//异常处理 404 500等
	public static function Error($errno, $errstr, $errfile=null, $errline=null)
	{
		if((DEBUG<2)&&in_array($errno,array(E_NOTICE,E_WARNING)))
		{
			return;
		}
		else if(in_array($errno,array(400,403,404,414,500,502,503,504)))
		{
			$errormsg="ERROR({$errno}) {$errstr}";
			$code=$errno;
		}
		else
		{
			$errormsg="ERROR({$errno}) {$errstr} in {$errfile} on line {$errline} ";
			$code=500;
		}
		app::log($errormsg,'ERROR');
		defined('STDIN')||(app::get('sys-error')&&exit('Error Found In Error Handler'))||(http_response_code($code)&&app::set('sys-error',true));
		if(!DEBUG&&defined('ERROR_PAGE_404')&&defined('ERROR_PAGE_500')) //线上模式且自定义了404和500
		{
			if(isset($GLOBALS['APP']['router'][0])&&is_file(CONTROLLER_PATH.$GLOBALS['APP']['router'][0].'.php'))
			{
				$errorController=$GLOBALS['APP']['router'][0];
			}
			else
			{
				$errorController=DEFAULT_CONTROLLER;
			}
			$errorRouter=array($errorController,$errno==404?ERROR_PAGE_404:ERROR_PAGE_500,$errormsg);
			if(method_exists($errorController,$errorRouter[1]))//当前已加载的控制器或默认控制器中含有ERROR处理
			{
				$GLOBALS['APP']['controller'][$errorController]=isset($GLOBALS['APP']['controller'][$errorController])?$GLOBALS['APP']['controller'][$errorController]:$errorController;
				if(!$GLOBALS['APP']['controller'][$errorController] instanceof $errorController)
				{
					$GLOBALS['APP']['controller'][$errorController]=new $errorController($errorRouter);///实例化控制器	
				}
				exit(call_user_func_array(array($GLOBALS['APP']['controller'][$errorController],$errorRouter[1]), array($errormsg)));//传入参数
			}
			else
			{
				exit('No Error Handler Found In '.$errorController.'::'.$errorRouter[1]);
			}
		}
		else
		{
			$ln=defined('STDIN')?PHP_EOL:'</p><p>';
			$trace=debug_backtrace();
			$i=count($trace)-1;
			$li=null;
			while($i>=0)
			{
				if(!isset($trace[$i]['file']))
				{
					$i--;	continue;
				}
				$trace[$i]['class']=isset($trace[$i]['class'])?$trace[$i]['class']:null;
				$trace[$i]['type']=isset($trace[$i]['type'])?$trace[$i]['type']:null;
				$li.=$trace[$i]['file'].'=>'.$trace[$i]['class'].$trace[$i]['type'].$trace[$i]['function'].'() on line '.$trace[$i]['line'].$ln;
				$i--;
			}
			if(defined('STDIN'))
			{
				echo $errormsg,PHP_EOL,$li;
				$errfile||exit;
			}
			else
			{
				if(!DEBUG)
				{
					$errormsg="Oops ! Something Error,Error Code:{$errno}";
					$li="See the log for more information ! {$ln}";
				}
				exit("<div style='margin:2% auto;width:80%;box-shadow:0 0 5px #f00;padding:1%;'><p>{$errormsg}{$ln}{$li}</p></div>");
			}
		}

	}
	public static function Shutdown()
	{
		if(DEBUG)
		{
			$lastError=error_get_last();
			if($lastError)
			{
				$errormsg="ERROR({$lastError['type']}) {$lastError['message']} in {$lastError['file']} on line {$lastError['line']} ";
				return app::log($errormsg,'ERROR');
			}
		}
	}

}
// End of class app

//加载model
function M($model,$param=null)
{
	$arr=explode('/',$model);
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
		if(is_null($param))
		{
			$GLOBALS['APP']['model'][$m]=new $m();
		}
		else
		{
			$GLOBALS['APP']['model'][$m]=new $m($param);
		}
		return $GLOBALS['APP']['model'][$m];
	}
}
//加载类库
function S($lib,$param=null)
{
	$arr=explode('/',$lib);
	$l=end($arr);
	$GLOBALS['APP']['lib'][$l]=isset($GLOBALS['APP']['lib'][$l])?$GLOBALS['APP']['lib'][$l]:$l;
	if($GLOBALS['APP']['lib'][$l] instanceof $l)
	{
		return $GLOBALS['APP']['lib'][$l];
	}
	else
	{
		if(is_file($classFile=LIB_PATH.$lib.'.class.php'))///是类库文件
		{
			require_once $classFile;
			class_exists($l)||app::Error(500,'Library File '.$classFile .' Does Not Contain Class '.$l);
			if(is_null($param))
			{
				$GLOBALS['APP']['lib'][$l]=new $l();
			}
			else
			{
				$GLOBALS['APP']['lib'][$l]=new $l($param);
			}
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
function V($_v_,$_data_=array(),$fileCacheMinute=0)
{
	if(defined('APP_TIME_SPEND'))
	{
		return template($_v_,$_data_);
	}
	if((is_file($_v_=VIEW_PATH.$_v_.'.php'))||(is_file($_v_=VIEW_PATH.$_v_)))
	{
		if($fileCacheMinute||(is_int($_data_)&&($_data_>0)))
		{
			$cacheTime=$fileCacheMinute?$fileCacheMinute:$_data_;
			$GLOBALS['APP']['cache']['time']=intval($cacheTime*60);
			$GLOBALS['APP']['cache']['file']=true;
		}
		define('APP_TIME_SPEND',round((microtime(true)-APP_START_TIME),4));//耗时
		define('APP_MEMORY_SPEND',byteFormat(memory_get_usage()-APP_START_MEMORY));
		(is_array($_data_)&&!empty($_data_))&&extract($_data_);
		include $_v_;
		if(!empty($GLOBALS['APP']['cache']['file']))//启用了缓存,并且启用了文件缓存
		{
			$expire=intval(time()+$GLOBALS['APP']['cache']['time']);
			//生成文件缓存
			$contents=ob_get_contents();
			$router=$GLOBALS['APP']['router'];
			//与缓存检测时一致,闭包路由也可以使用文件缓存
			$file=is_object($router[1])?app::fileCache($router[0]):app::fileCache($router);
			file_put_contents($file,$contents);
			touch($file,$expire);
		}
		defined('STDIN')||(ob_end_flush()&&flush());
	}
	else
	{
		return app::Error(404,'View File '.$_v_.' Not Found ! ');
	}

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

function template($_v_,$_data_=array())///加载模版
{
	if((is_file($_v_=VIEW_PATH.$_v_.'.php'))||(is_file($_v_=VIEW_PATH.$_v_)))
	{
		(is_array($_data_)&&!empty($_data_))&&extract($_data_);
		return include $_v_;
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
	public static function ip()
	{
		if ($ip=self::getVar('server','HTTP_X_FORWARDED_FOR'))
			return $ip;
		else if ($ip=self::getVar('server','HTTP_CLIENT_IP'))
			return $ip;
		else if ($ip=self::getVar('server','REMOTE_ADDR'))
			return $ip;
		else if ($ip=getenv("HTTP_X_FORWARDED_FOR"))
			return $ip;
		else if ($ip=getenv("HTTP_CLIENT_IP"))
			return $ip;
		else if ($ip=getenv("REMOTE_ADDR"))
			return $ip;
		else return null;

	}
	public static function info($key=null,$default=null)
	{
		$data['ip']=self::ip();
		$data['ajax']=self::isAjax();
		$data['ua']=self::ua();
		$data['refer']=self::refer();
		$data['protocol'] = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? "https" : "http";
		if($key) {return isset($data[$key])?$data[$key]:$default;}
		return $data;
	}
	public static function serverInfo($key=null,$default=null)
	{
		$info['server_ip']=gethostbyname(self::getVar('server',"SERVER_NAME"));///服务器IP
		$info['max_exectime']=ini_get('max_execution_time');//最大执行时间
		$info['max_upload']=ini_get('file_uploads')?ini_get('upload_max_filesize'):0;///最大上传
		$info['php_vision']=PHP_VERSION;////php版本
		$info['os']=PHP_OS;///操作系统类型
		$info['run_mode']=php_sapi_name();//php 运行方式
		$info['post_max_size']=ini_get('post_max_size');
		if($key)
		{
			return isset($info[$key])?$info[$key]:$default;
		}
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
		return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'post';
	}
	public static function isRobot()
	{
		$agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
		if($agent)
		{
			return preg_match('/(spider|bot|slurp|crawler)/i', strtolower($agent));
		}
		return true;
	}
	public static function isMoblie()
	{
		$agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
		$regexMatch="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
		$regexMatch.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
		$regexMatch.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
		$regexMatch.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
		$regexMatch.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
		$regexMatch.=")/i";
		return preg_match($regexMatch, strtolower($agent));
	}
	public static function ua()
	{
		return self::getVar('server','HTTP_USER_AGENT');
	}
	public static function refer()
	{
		return self::getvar('server','HTTP_REFERER');
	}
	public static function filterPost($rule,$callback=null,$clean=false)
	{
		foreach ($rule as $key => $value)
		{
			$allowed[]=is_int($key)?$value:$key;
		}
		$post=self::cleanData($_POST,$allowed,$clean);
		return Validate::rule($rule,$post,$callback);
	}
	public static function filterGet($rule,$callback=null,$clean=false)
	{
		foreach ($rule as $key => $value)
		{
			$allowed[]=is_int($key)?$value:$key;
		}
		$get=self::cleanData($_GET,$allowed,$clean);
		return Validate::rule($rule,$get,$callback);
	}
	private static function cleanData($input,$allowed,$clean=false)
	{
		foreach(array_keys($input) as $key )
		{
			if ( !in_array($key,$allowed)||!$key )
			{
				 unset($input[$key]);
			}
			if($clean)
			{
				$input[$key]=self::clean($input[$key]);
			}
		}
		foreach ($allowed as $item)
		{
			if(!isset($input[$item]))
			{
				$input[$item]=null;
			}
		}
		return $input;
	}
	private static function getVar($type,$var,$default=null,$clean=false)
	{
		switch ($type)
		{
			case 'post':
				return isset($_POST[$var])?($clean?self::clean($_POST[$var]):$_POST[$var]):$default;
				break;
			case 'get':
				return isset($_GET[$var])?($clean?self::clean($_GET[$var]):$_GET[$var]):$default;
				break;
			case 'cookie':
				return isset($_COOKIE[$var])?($clean?self::clean($_COOKIE[$var]):$_COOKIE[$var]):$default;
				break;
			case 'server':
				return isset($_SERVER[$var])?$_SERVER[$var]:$default;
				break;
			case 'session': ///此处为获取session的方式
				return isset($_SESSION[$var])?$_SESSION[$var]:$default;
				break;
			default:
				return false;
				break;
		}
	}
	/**
	 * 默认普通过滤,去除html标签,去除空格
	 * $type null 默认 去除xss
	 * $type 1 去除中文
	 * $type 2 
	 * $type 3
	 */
	public static function clean($val,$type=null)
	{
		if(is_null($type))
		{
			return $val;
		}
		else
		{
			switch ($type)
			{
				case 1:
					$out=preg_replace('/[\x80-\xff]/','',$val);
					break;
				case 2:
					$out=preg_replace('','', $val);
					break;
				default:
					$out=strip_tags($val);
					break;
			}
			return trim($out);
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
	public static function rule($rule,$data,$callback=null)
	{
		try
		{
			foreach($rule as $k=>&$item)
			{
				if(isset($data[$k])&&$data[$k]!='')//存在要验证的数据
				{
					foreach($item as $type=>$msg)
					{
						if(is_object($msg)) //是一个过滤器
						{
							$data[$k]=$msg($data[$k]);
						}
						else if(is_int($type))
						{
							$sw[$k]=$msg;
						}
						else if(stripos($type,'='))
						{
							self::mixedChecker($data[$k],explode('=', $type),$msg);
						}
						else
						{
							self::typeChecker($data[$k],$type,$msg);
						}
					}
				}
				else if(isset($item['require'])) //标记为require,却不存在
				{
					throw new Exception($item['require'], -1);
				}
			}

		}
		catch(Exception $e)
		{
			$data=json_encode(array('code'=>$e->getCode(),'msg'=>$e->getMessage()));
			if(is_callable($callback))
			{
				$callback($data,$e);
			}
			else if($callback)
			{
				exit($data);
			}
			return false;
		}
		if(!empty($sw)&&is_array($sw))
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
					throw new Exception($msg, -1);
				}
				break;
			case 'email':
				if(!self::email($item))
				{
					throw new Exception($msg, -2);
				}
				break;
			case 'username':
				if(!self::username($item))
				{
					throw new Exception($msg, -3);
				}
				break;
			case 'password':
				if(!self::password($item))
				{
					throw new Exception($msg, -4);
				}
				break;
			case 'phone':
				if(!self::phone($item))
				{
					throw new Exception($msg, -5);
				}
				break;
			case 'url':
				if(!self::url($item))
				{
					throw new Exception($msg, -6);
				}
				break;
			case 'ip':
				if(!self::ip($item))
				{
					throw new Exception($msg, -7);
				}
				break;
			case 'idcard':
				if(!self::idcard($item))
				{
					throw new Exception($msg, -8);
				}
				break;
			default:
				throw new Exception("Error Type Rule {$type}", -404);
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
					throw new Exception($msg, -9);
				}
				break;
			case 'maxlength':
				if(strlen($item)>$mixed[1])
				{
					throw new Exception($msg, -10);
				}
				break;
			case 'eq':
				if($item!=$mixed[1])
				{
					throw new Exception($msg, -11);
				}
				break;
			default:
				throw new Exception("Error Mixed Rule {$mixed[0]}", -500);
				break;
		}
	}
	public static function email($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	public static function phone($phone)
	{
		return preg_match("/^1[3458][0-9]{9}$/",$phone);
	}
	public static function url($url)
	{
		return filter_var($url, FILTER_VALIDATE_URL);
	}
	public static function ip($ip)
	{
		if(function_exists('ip2long'))
		{
			return (ip2long($ip)!==false);  			
		}
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
class DB extends PDO 
{
	private  static $pdo;///单例模式

	function __construct($dbType=null)
	{
		self::init($dbType);
	}
	private static function init($dbType=null)
	{
		if(is_null($dbType))
		{
			$dbType=defined('DB')&&DB?true:false;
		}
		if($dbType)//使用sqlite
		{
			if(self::$pdo==null)
			{
				try
				{
					self::$pdo=new PDO("sqlite:".SQLITE);
					self::$pdo->exec('PRAGMA synchronous=OFF');
					self::$pdo->exec('PRAGMA cache_size =8000');
					self::$pdo->exec('PRAGMA temp_store = MEMORY');
				}
				catch (PDOException $e)
				{
					return app::Error(500,'Open Sqlite Database Error ! '.$e->getMessage());
				}
			}
		}
		else///使用mysql
		{
			if(self::$pdo==null)
			{
				$dsn='mysql:host='.DB_HOST.';dbname='.DB_NAME.';port='.DB_PORT.';charset=UTF8';
				try
				{
					self::$pdo= new PDO ($dsn,DB_USER,DB_PASS,array(PDO::ATTR_PERSISTENT=>TRUE,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
				}
				catch (PDOException $e)
				{
					try
					{
						self::$pdo= new PDO ($dsn,DB_USER,DB_PASS,array(PDO::ATTR_PERSISTENT=>TRUE,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
					}
					catch(PDOException $e)
					{
						return app::Error(500,'Connect Mysql Database Error ! '.$e->getMessage());
					}
				}
			}
		}
		return self::$pdo;

	}
	//运行Sql语句,不返回结果集,但会返回成功与否,不能用于select
	public static function runSql($sql)
	{
		try
		{
			$rs=self::ready()->exec($sql);
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			return $rs;
		}
		catch (PDOException $e)
		{
			return app::Error(500,'Run Sql [ '.$sql.' ] Error : '.$e->getMessage());
		}
	}
	////运行Sql,以多维数组方式返回结果集
	public static function getData($sql)
	{
		try
		{
			$rs=self::ready()->query($sql);
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			if(FALSE==$rs) {return array();}
			return $rs->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			return app::Error(500,'Run Sql [ '.$sql.' ] Error : '.$e->getMessage());
		}
	}
	//运行Sql,以数组方式返回结果集第一条记录
	public static function getLine($sql)
	{
		try
		{
			$rs=self::ready()->query($sql);
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			if(FALSE==$rs) {return array();}
			return $rs->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			return app::Error(500,'Run Sql [ '.$sql.' ] Error : '.$e->getMessage());
		}
	}
	//运行Sql,返回结果集第一条记录的第一个字段值
	public static function getVar($sql)
	{
		try
		{
			$rs=self::ready()->query($sql);
			app::set('sys-sql-count',app::get('sys-sql-count')+1);
			if(FALSE==$rs) {return null;}
			return $rs->fetchColumn();
		}
		catch (PDOException $e)
		{
			return app::Error(500,'Run Sql [ '.$sql.' ] Error : '.$e->getMessage());
		}
	}
	public static function lastId()
	{
		return self::ready()->lastInsertId();
	}
	//返回原生的PDO对象
	public static function getInstance($current=true)
	{
		if($current)
		{
			return self::ready();
		}
		else
		{
			$origin=self::$pdo;
			self::$pdo=null;
			$pdo=self::init(!(defined('DB')&&DB));//相反的
			self::$pdo=$origin;
			return $pdo;
		}
	}
	public  function quote($string, $paramtype = null)
	{
		return self::ready()->quote($string, $paramtype);
	}
	private static  function ready()
	{
		return self::$pdo?self::$pdo:self::init();
	}
	public function __call($method,$args=null)
	{
		return app::Error(500,'Call Error Method '.$method.' In Class '.get_called_class());
	}
	public static function __callStatic($method,$args=null)
	{
		$instance=M(get_called_class());
		if($method=='instance')
		{
			return $instance;
		}
		else
		{
			return call_user_func_array(array($instance,ltrim($method,'_')), $args);
		}
	}

}//end class db

if(!function_exists('http_response_code'))
{
	function http_response_code($code)
	{
		$header=(substr(php_sapi_name(),0,3)=='cgi')?'Status: ':'HTTP/1.1 ';
		static $headers=array(
							200	=> 'OK', 201	=> 'Created', 202	=> 'Accepted', 203	=> 'Non-Authoritative Information', 204	=> 'No Content', 205	=> 'Reset Content', 206	=> 'Partial Content',
							300	=> 'Multiple Choices', 301	=> 'Moved Permanently', 302	=> 'Found', 304	=> 'Not Modified', 305	=> 'Use Proxy', 307	=> 'Temporary Redirect',
							400	=> 'Bad Request', 401	=> 'Unauthorized', 403	=> 'Forbidden', 404	=> 'Not Found', 405	=> 'Method Not Allowed', 406	=> 'Not Acceptable', 407	=> 'Proxy Authentication Required', 408	=> 'Request Timeout', 409	=> 'Conflict', 410	=> 'Gone', 411	=> 'Length Required', 412	=> 'Precondition Failed', 413	=> 'Request Entity Too Large', 414	=> 'Request-URI Too Long', 415	=> 'Unsupported Media Type', 416	=> 'Requested Range Not Satisfiable', 417	=> 'Expectation Failed',
							500	=> 'Internal Server Error', 501	=> 'Not Implemented', 502	=> 'Bad Gateway', 503	=> 'Service Unavailable', 504	=> 'Gateway Timeout', 505	=> 'HTTP Version Not Supported'
							);
		if(isset($headers[$code]))
		{
			$text=$headers[$code];
			header("{$header} {$code} {$text}", TRUE, $code);
		}
	}
}
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
	else if(is_file($libFile=LIB_PATH.'class'.DIRECTORY_SEPARATOR.'{$class}.class.php'))
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
			return isset($_SESSION[$k])?$_SESSION[$k]:null;
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
function json($data,$callback=null)
{
	is_array($data)||parse_str($data,$data);
	$data=json_encode($data);
	if($callback&&(is_string($callback)||$callback=Request::get('jsoncallback')))
	{
		exit($callback."(".$data.")");
	}
	exit($data);
}
function byteFormat($size,$dec=2)
{
	$size=max(abs($size),1);
	$unit=array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
	return round($size/pow(1024,($i=floor(log($size,1024)))),$dec).' '.$unit[$i];
}
//外部重定向,会立即结束脚本以发送header,内部重定向app::run(array);
function redirect($url,$seconds=0,$code=302)
{
	http_response_code($code);
	exit(header("Refresh: {$seconds}; url={$url}"));
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
function csrf_token($check=false)
{
	isset($_SESSION)||session_start();
	$token=isset($_SESSION['csrf_token'])?$_SESSION['csrf_token']:null;
	if($check)
	{
		if(!(isset($_REQUEST['_token']) && $_REQUEST['_token'] === $token))
		{
			return app::Error(404,'Csrf Token Not Match ! ');
		}
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
		if(!$fp = fsockopen(MAIL_SERVER,MAIL_PORT, $errno, $errstr, 10))
		{
			throw new Exception("Unable to connect to the SMTP server", 1);
		}
		stream_set_blocking($fp, true);
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != '220')
		{
			throw new Exception("CONNECT - ".$lastmessage, 2);
		}
		fputs($fp, (MAIL_AUTH ? 'EHLO' : 'HELO')." befen\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250)
		{
			throw new Exception("HELO/EHLO - ".$lastmessage, 3);
		}
		while(1)
		{
			if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage))
			{
				break;
			}
			$lastmessage = fgets($fp, 512);
		}
		if(MAIL_AUTH)
		{
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

// end  of file core.php