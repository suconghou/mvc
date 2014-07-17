<?

/**
 * @author suconghou 
 * @blog http://blog.suconghou.cn
 * @link http://github.com/suconghou/mvc
 * @version 1.29
 */

require 'config.php';
//正则路由分析器
function regexRouter($uri)
{
	global $APP;
	if(isset($APP['regex_router'][0]))
	{
		foreach ($APP['regex_router'] as $regex)
		{
			if(preg_match('/^'.$regex[0].'$/', $uri,$matches))
			{
				unset($matches[0]);
				$router=array_merge($regex[1],$matches);
				return $router;
			}
		}
	}
	return null;
}

//普通路由分析器
function commonRouter($uri)
{
	$uri_arr=explode('/', $uri);
	foreach ($uri_arr as  $v)
	{
		if(empty($v)) continue;
		$router[]=$v;
	}
	return isset($router)?$router:null;
}

//异常处理 404 500等
function showErrorpage($errno, $errstr, $errfile=null, $errline=null)
{
	$e = new Exception;
	$trace=$e->getTraceAsString();
	$trace_arr=preg_split('/#[0-9]/',$trace);
	$h2="ERROR({$errno}):  ".$errstr;
	$h3=null;
	foreach ($trace_arr as $key => $value)
	{
		if(!$value)continue;
		$h3.='<p>#'.$key.$value.'</p>';
	}
	if($errno==404)
	{
		$h1='404 - Request Page Not Found ! ';	
		http_response_code($errno);
		if(USER_ERROR_PAGE_404)
		{
			DEBUG&&logMessage($h1.$h2);
			include LIB_PATH.'error/'.USER_ERROR_PAGE_404;
			die;
		}
		else
		{	
			if(DEBUG)
			{
				logMessage($h1.$h2);
			}
			else
			{
				$h2='Please contact the administrator';
				$h3='';
			}	
		}
	}
	else if($errno==500)
	{
		$h1='500 - Internet Server Error ! ';
		http_response_code($errno);
		if(USER_ERROR_PAGE_500)
		{
			DEBUG&&logMessage($h1.$h2);
			include LIB_PATH.'error/'.USER_ERROR_PAGE_500;
			die;
		}
		else
		{	
			if(DEBUG)
			{
				logMessage($h1.$h2);
			}
			else
			{
				$h2='Please contact the administrator';
				$h3='';
			}
		}
	}
	else///系统抛出错误
	{	
		$h1='An error occurred at '.$errfile.' on line '.$errline.' ';	
		http_response_code('500');
		if(USER_ERROR_PAGE_500)
		{
			DEBUG&&logMessage($h1.$h2);
			include LIB_PATH.'error/'.USER_ERROR_PAGE_500;
			die;
		}
		else if(USER_ERROR_PAGE_404)
		{
			DEBUG&&logMessage($h1.$h2);
			include LIB_PATH.'error/'.USER_ERROR_PAGE_404;
			die;
		}
		else ////没有设置错误页,但是系统错误
		{		
			if(DEBUG)
			{	
				logMessage($h1.$h2);
			}
			else
			{
				$h1='Something Error ! ';
				$h2='Please contact the administrator';
				$h3='';
			}
		}
	}
	$html='<div style="margin:2% auto;width:80%;box-shadow:0px 0px 4px #888;padding:2%;font:12px \'Monaco\',Courier">';
	$html.='<h1>'.$h1.'</h1><h2>'.$h2.'</h2><h3>'.$h3.'</h3>';
	$html.="</div>";
	exit($html);

}


//添加路由规则,参数一正则,参数二数组
function route($regex,$arr)
{
	if(REGEX_ROUTER)//启用了正则路由
	{
		is_array($arr)||showErrorpage('500','Router need to be an array ! ');
		global $APP;
		$APP['regex_router'][]=array($regex,$arr);
	}
	
}

///流程导航器,第一个启动的
function process()
{
	(strlen($_SERVER['REQUEST_URI'])>MAX_URL_LENGTH)&&showErrorpage('500','Request url too long ! ');
	global $APP;///全局变量
	list($uri)=explode('?',$_SERVER['REQUEST_URI']);
	$uri=='/favicon.ico'&&die;
	if(substr($uri,0,10)=='/index.php')
	{
		$uri=substr($uri,10);
	}
	if(REGEX_ROUTER)
	{
		$router=regexRouter($uri);
		$router||($router=commonRouter($uri));
		unset($APP['regex_router']);
	}
	else
	{
		$router=commonRouter($uri);
	}
	if(empty($router[0])) // http://127.0.0.1 的情况
	{
		$router=array(DEFAULT_CONTROLLER,DEFAULT_ACTION);
	}
	else if(empty($router[1])) //  http://127.0.0.1/home 的情况
	{
		if(preg_match('/^\w+$/i',$router[0]))
		{
			$router=array($router[0],DEFAULT_ACTION);
		}
		else
		{
			showErrorpage('404','Error controller name ! ');
		}
	}
	else //控制器和动作全部需要过滤
	{
		if(!preg_match('/^\w+$/i',$router[0]))
		{
			showErrorpage('404','Error controller name ! ');
		}
		if(!preg_match('/^\w+$/i',$router[1]))
		{
			showErrorpage('404','Error action name ! ');
		}
	}
	$APP['router']=$router;
	return $router;
}
function run($router)
{	
	$controller=CONTROLLER_PATH.$router[0].'.php'; 
	$controllerDir=CONTROLLER_PATH.$router[0]; ///如果是二级目录
	if(is_file($controller))
	{
		$controllerFile=&$controller;
		$controllerName=&$router[0];
		$action=&$router[1];
		$param=2;
		require_once $controllerFile;
	}
	else if(is_dir($controllerDir))
	{
		$controllerFile=$controllerDir.'/'.$router[1].'.php';
		if(is_file($controllerFile))
		{			
			$controllerName=&$router[1];
			$action=isset($router[2])?$router[2]:DEFAULT_ACTION;
			$param=3;
			require_once $controllerFile;
		}
		else
		{
			showErrorpage('404','the controller file '.$controllerFile.' does not exists');
		}
		
	}
	else
	{
		showErrorpage('404','the controller file '.$controller.' does not exists');
	}
	if(class_exists($controllerName))
	{
		$methods=get_class_methods($controllerName);
		in_array($action, $methods)||showErrorpage('404','class '.$controllerName.' does not contain method '.$action);
		$controllerName=new $controllerName();///实例化控制器	
		return call_user_func_array(array($controllerName,$action), array_slice($router,$param));//传入参数
	}
	else
	{
		showErrorpage('404','the contoller file '.$controllerFile.' does not contain the class '.$controllerName);
	}



}

//记录日志的函数
function logMessage($msg)
{
	$path=LIB_PATH.'error/'.date('Y-m-d',APP_START_TIME).'.log';
	$msg=date('Y-m-d H:i:s',time()).' ==> '.$msg."\r\n";
	if(is_writable(LIB_PATH.'error'))
	{
		error_log($msg,3,$path);
	}
	if(!function_exists('error_log'))
	{
		function error_log($msg,$type=3,$path)
		{
			file_put_contents($path,$msg,FILE_APPEND); 
		}
	}
}
//加载model
function M($model,$param=null)
{
	$arr=explode('/',$model);
	$m=end($arr);
	global $APP;
	$APP['model'][$m]=isset($APP['model'][$m])?$APP['model'][$m]:$m;
	if($APP['model'][$m] instanceof $m)
	{
		return $APP['model'][$m];
	}
	else
	{
		$model_file=MODEL_PATH.$model.'.php';
		is_file($model_file)||showErrorpage('500','load model '.$m.' failed , mdoel file '.$model_file.' does not exists ');
		require $model_file;
		class_exists($m)||showErrorpage('500','model file '.$model_file .' does not contain class '.$m);
		$APP['model'][$m]=new $m($param);///对模型实例化
		return $APP['model'][$m];
	}
}
//加载类库
function S($lib,$param=null)
{
	$arr=explode('/',$lib);
	$l=end($arr);
	global $APP;
	$APP['lib'][$l]=isset($APP['lib'][$l])?$APP['lib'][$l]:$l;
	if($APP['lib'][$l] instanceof $l)
	{
		return $APP['lib'][$l];
	}
	else
	{
		$file=LIB_PATH.$lib.'.php';
		$class_file=LIB_PATH.$lib.'.class.php';
		if(is_file($class_file))///是类库文件
		{
			require $class_file;
			class_exists($l)||showErrorpage('500','library file '.$class_file .' does not contain class '.$l);
			$APP['lib'][$l]=new $l($param);///对模型实例化
			return $APP['lib'][$l];

		}
		else if(is_file($file))
		{
			unset($APP['lib'][$l]);
			return require_once $file;
		}
		else
		{
			showErrorpage('500','load  library '.$l.' failed ,file '.$file.' or '.$class_file.' does not exists ');
		}

	}

}
//加载视图
function V($view,$data=null)
{
	if(defined('APP_TIME_SPEND'))
	{
		showErrorpage('500','You have already loaded a view,function V can not used twice in a method !');
	}
	$view_file=VIEW_PATH.$view.'.php';
	if(is_file($view_file))
	{
		is_array($data)||empty($data)||showErrorpage('500','param to view '.$view_file.' show be an array');
		empty($data)||extract($data);
		GZIP?ob_start("ob_gzhandler"):ob_start();
		define('APP_TIME_SPEND',round((microtime(true)-APP_START_TIME),4));//耗时
		define('APP_MEMORY_SPEND',byteFormat(memory_get_usage()-APP_START_MEMORY));
		require $view_file;
		global $APP;
		if(isset($APP['cache']))//启用了缓存
		{
			$expires_time=time()+$APP['cache']['time'];
			if($APP['cache']['file'])//生成文件缓存
			{
				$contents=ob_get_contents();
				$cache_file=ROOT.'static/cache/'.md5(implode('-',$APP['router'])).'.html';
				file_put_contents($cache_file,$contents);
				touch($cache_file,$expires_time);
				header("Expires: ".gmdate("D, d M Y H:i:s", $expires_time)." GMT");
				header("Cache-Control: max-age=".$APP['cache']['time']);
				header('Last-Modified: ' . gmdate('D, d M y H:i:s',time()). ' GMT');   
				flush();
				ob_end_flush();
			}
			else//使用的是http缓存
			{
				header("Expires: ".gmdate("D, d M Y H:i:s", $expires_time)." GMT");
				header("Cache-Control: max-age=".$APP['cache']['time']);
				header('Last-Modified: ' . gmdate('D, d M y H:i:s',time()). ' GMT'); 
				flush();
				ob_end_flush();
			}
			
		}
		else
		{

			flush();
			ob_end_flush();
		}
		
			
	}
	else
	{
		showErrorpage('404','view file '.$view_file.' does not exists ');
	}

}
//缓存,第一个参数为缓存时间,第二个为是否文件缓存
function C($time,$file=null)
{
	global $APP;
	$cache['time']=$time*60;
	$cache['file']=$file;
	$APP['cache']=&$cache;
	if(!$file)///使用了http缓存,在此处捕获缓存
	{
		$expires_time=time()+$APP['cache']['time'];
		$last_expire = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : 0;
		if($last_expire)
		{	
			if((strtotime($last_expire)+$cache['time'])>time()) //命中缓存
			{
				http_response_code(304);	
				die;  
			}
		}
		else
		{
			header("Expires: ".gmdate("D, d M Y H:i:s", $expires_time)." GMT");
			header("Cache-Control: max-age=".$APP['cache']['time']);
			header('Last-Modified: ' . gmdate('D, d M y H:i:s',time()). ' GMT'); 

		}

	}
}

if(!function_exists('http_response_code'))
{
	function http_response_code($code)
	{
		$nginx=(php_sapi_name()=='cgi-fcgi')?true:false;
		switch ($code)
		{
			case '301':
			$nginx?header('status: 301 Moved Permanently'):header('HTTP/1.1 301 Moved Permanently');
			break;
			case '302':
			$nginx?header('status: 302 Moved Temporarily'):header('HTTP/1.1 302 Moved Temporarily');
			break;
			case '304':
			$nginx?header('status: 304 Not Modified'):header('HTTP/1.1 304 Not Modified');
			break;
			case '403':
			$nginx?header('status: 403 Forbidden'):header('HTTP/1.1 403 Forbidden');
			break;			
			case '404':
			$nginx?header('status: 404 Not Found'):header('HTTP/1.1 404 Not Found');
			break;
			case '500':
			$nginx?header('status: 500 Internal Server Error'):header('HTTP/1.1 500 Internal Server Error');	
			break;
		}
	}
}
function __autoload($class)
{
	$controller_file=CONTROLLER_PATH.$class.'.php';
	$model_file=MODEL_PATH.$class.'.php';	
	if(is_file($model_file))
	{
		require $model_file;
		class_exists($class)||showErrorpage('500','Autoload file '.$model_file.' successfully,but not found class '.$class);
	}
	else if(is_file($controller_file))
	{
		require $controller_file;
		class_exists($class)||showErrorpage('500','Autoload file '.$controller_file.' successfully,but not found class '.$class);
	}
	else
	{
		showErrorpage('500','Can not autoload class file '.$class.'.php');
	}
}
function template($file)///加载模版
{
	$file=VIEW_PATH.$file.'.php';
	if(is_file($file))
	{
		include $file;
	}
	else
	{
		showErrorpage('404','template file '.$file.' not exists !');
	}
}

/**
* Request 用户来访信息,使用静态访问
*/
class Request
{
	
	public static function post($key=null,$default=null)
	{
		if($key)
		{
			return self::getVar('post',$key,$default);
		}
		else
		{
			$data=array();
			foreach ($_POST as $key => $value)
			{
				$data[$key]=self::getVar('post',$key);
			}
			return $data;
		}

	}
	public static function get($key=null,$default=null)
	{
		if($key)
		{
			return self::getVar('get',$key,$default);
		}
		else
		{
			$data=array();
			foreach ($_GET as $key => $value)
			{
				$data[$key]=self::getVar('get',$key);
			}
			return $data;
		}


	}
	public static function cookie($key=null,$default=null)
	{
		if($key)
		{
			return self::getVar('cookie',$key,$default);
		}
		else
		{
			$data=array();
			foreach ($_COOKIE as $key => $value)
			{
				$data[$key]=self::getVar('cookie',$key);
			}
			return $data;
		}

	}
	public static function session($key=null,$default=null)
	{
		if(!isset($_SESSION))session_start();
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
	public static function info()
	{
		$data['ip']=self::getIp();
		$data['ajax']=self::isAjax();
		$data['ua']=self::ua();
		$data['refer']=self::refer();
		return $data;
	}
	/**
	 * 默认去除html标签,去除空格
	 * $type='1' 去除中文
	 * $type=''
	 * $type=''
	 * $type=''
	 */
	public static function clean($val,$type=null,$all=null)
	{
		if(is_null($type))
		{
			return trim(htmlentities(strip_tags($val)));
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
					$out=$val;
					break;
			}
			return $all?trim(htmlentities(strip_tags($out))):$out;
		}

	}
	private static function getIp()
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
	private static function isAjax()
	{
		return self::getVar('server','HTTP_X_REQUESTED_WITH')=='XMLHttpRequest';
	}
	private static function ua()
	{
		return self::getVar('server','HTTP_USER_AGENT');
	}
	private static function refer()
	{
		return self::getvar('server','HTTP_REFERER');
	}
	private static function getVar($type,$var,$default=null)
	{
		switch ($type)
		{
			case 'post':
				return isset($_POST[$var])?self::clean($_POST[$var]):$default;
				break;
			case 'get':
				return isset($_GET[$var])?self::clean($_GET[$var]):$default;
				break;
			case 'cookie':
				return isset($_COOKIE[$var])?self::clean($_COOKIE[$var]):$default;
				break;
			case 'server':
				return isset($_SERVER[$var])?self::clean($_SERVER[$var]):$default;
				break;
			case 'session': ///此处为获取session的方式
				return isset($_SESSION[$var])?$_SESSION[$var]:$default;
				break;
			default:
				return false;
				break;
		}

	}
}

/**
* 验证类,使用静态方法
*/
class Validate
{
	
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
	//数字/大写字母/小写字母/标点符号组成，四种都必有，8位以上
	public static function pass($pass)
	{
		return preg_match('/^(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/',$pass);
	}
	//自定义正则验证
	public static function this($pattern,$subject)
	{
		return preg_match('/^'.$pattern.'$/', $subject);
	}

}

/**
 * 异步(非阻塞)运行一个路由
 * $curl 强制使用curl方式,但此方式至少阻塞1秒
 * $lose 如果可以,断开客户端连接,脚本后台运行,以后输出不能发送到浏览器
 */
function async($router,$curl=false,$lose=false)
{
	if(is_array($router))
	{
		$url='http://'.$_SERVER['HTTP_HOST'].'/'.implode('/',$router);
	}
	else
	{
		$url=$router;
	}
	if($curl)
	{
		if(function_exists('fastcgi_finish_request')&&$lose)
		{
			fastcgi_finish_request();
			return file_get_contents($url);			
		}
		$ch = curl_init(); 
		$curl_opt = array(CURLOPT_URL=>$url,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_TIMEOUT=>1,
							CURLOPT_HEADER=>0,CURLOPT_NOBODY=>1,CURLOPT_RETURNTRANSFER=>1);
		curl_setopt_array($ch, $curl_opt);
		curl_exec($ch);
		curl_close($ch);
		return true;
	}
	else
	{ 
		if(function_exists('fastcgi_finish_request')&&$lose)
		{
			fastcgi_finish_request();
			return file_get_contents($url);			
		}
		else if (function_exists('fsockopen')||function_exists('stream_socket_client'))
		{
			$parts = parse_url($url);
			$parts['port']=isset($parts['port']) ? $parts['port'] : 80;
			$parts['query']=isset($parts['query'])?$parts['path'].'?'.$parts['query']:$parts['path'];
			if(function_exists('stream_socket_client'))
			{
				$fp = stream_socket_client($parts['host'].':'.$parts['port'], $errno, $errstr,3);
			}
			else
			{
				$fp = fsockopen($parts['host'],$parts['port'],$errno, $errstr,3);
			}	
	    	$fp||showErrorpage($errno,$errstr);
	    	stream_set_blocking($fp,0);
	    	$out = 'GET '.$parts['query']." HTTP/1.1\r\nHost: ".$parts['host']."\r\nConnection: Close\r\n\r\n";
	    	fwrite($fp, $out);
	    	ob_flush();
	    	flush();
	    	fclose($fp);
	    	return true;
		}
		return false;
	}

}
/**
* model 层
*/
class model 
{
	private  static $pdo;///单例模式

	function __construct()
	{

		if(DB)//使用sqlite
		{
			try
			{
				if(self::$pdo==null)
				{
					self::$pdo=new PDO("sqlite:".SQLITE);
					self::$pdo->exec('PRAGMA synchronous=OFF');
					self::$pdo->exec('PRAGMA cache_size =8000');
					self::$pdo->exec('PRAGMA temp_store = MEMORY');
				}
			}
			catch ( Exception $e )
			{
            	showErrorpage('500','connect sqlite database error ! '.$e->getMessage());
        	}

		}
		else///使用mysql
		{
			try
			{		
				if(self::$pdo==null)
				{
					$dsn="mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT;
					self::$pdo= new PDO ($dsn,DB_USER,DB_PASS,array (PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
					self::$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				}	
			
			}
			catch ( Exception $e )
			{
           	 	showErrorpage('500','connect mysql database error ! '.$e->getMessage());
        	}
		}
		

	}
	//运行Sql语句,不返回结果集,但会返回成功与否,不能用于select
	function runSql($sql)
	{
		try
		{
			self::ready();
			$rs=self::$pdo->exec($sql);
			return $rs;
		}
		catch (PDOException $e)
		{
			showErrorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}
		
		
	}
	////运行Sql,以多维数组方式返回结果集
	function getData($sql)
	{
		try
		{
			self::ready();
			$rs=self::$pdo->query($sql);
			return $rs->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			showErrorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}


	}
	//运行Sql,以数组方式返回结果集第一条记录
	function getLine($sql)
	{
		try
		{
			self::ready();
			$rs=self::$pdo->query($sql);
			return $rs->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			showErrorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}

	}
	//运行Sql,返回结果集第一条记录的第一个字段值
	function getVar($sql)
	{
		try
		{
			self::ready();
			$rs=self::$pdo->query($sql);
			return $rs->fetchColumn();
		}
		catch (PDOException $e)
		{
			showErrorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}

	}
	function lastId()
	{
		self::ready();
		return self::$pdo->lastInsertId();
	}
	//返回原生的PDO对象
	function getInstance()
	{
		self::ready();
		return self::$pdo;
	}
	private function ready()
	{
		self::$pdo||showErrorpage('500','PDO not init , have you construct ? ');
	}
	function __call($name,$args)
	{
		showErrorpage('500','Call Error Method '.$name);
	}
	function __destruct()
	{
		self::$pdo=null;
	}
}//end class model

/////////some functions blow
// session 系列函数
function session_get($key=null,$default=null)
{
	if(is_array($key))
	{
		$res=array();
		foreach ($key as  $k)
		{
			$res[$k]=Request::session($k,$default);
		}
		return $res;
	}
	else
	{
		return Request::session($key,$default);
	}
}
//此处为设置session的方式,重写可将session迁移至redis等
function session_set($key,$value=null)
{
	if(!isset($_SESSION))session_start();
	if(is_array($key))
	{
		foreach ($key as $k => $v)
		{
			$_SESSION[$k]=$v;
		}
		return true;
	}
	else
	{ 
		return $_SESSION[$key]=is_array($value)?json_encode($value):$value;
	}
}
function session_del($key=null)
{
	if(!isset($_SESSION))session_start();
	if(is_array($key))
	{
		while(list($k,$v)=each($key))
		{
			unset($_SESSION[$v]);
		}
		return true;
	}
	else if($key)
	{
		unset($_SESSION[$key]);
	}
	else
	{
		return session_destroy();
	}
}
function session_echo($key,$default=null)
{
	e(Request::session($key,$default));
}
function e($var)
{
	if(isset($var))
	{
		if(is_array($var))
		{
			print_r($var);
		}
		else if(is_object($var))
		{
			var_dump($var);
		}
		else
		{
			echo $var;
		}

	}
}
function dump($data)
{
	echo '<pre>';
	e($data);
	echo '</pre>';
}

function byteFormat($size,$dec=2)
{
    $unit=array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
    return round($size/pow(1024,($i=floor(log($size,1024)))),$dec).' '.$unit[$i];
}
function dateFormat($time)
{
	$t=time()-$time;
	if($t<1)return false;
	$f=array(
	'31536000'=>'年',
	'2592000'=>'个月',
	'604800'=>'星期',
	'86400'=>'天',
	'3600'=>'小时',
	'60'=>'分钟',
	'1'=>'秒'
	);
	foreach ($f as $k=>$v)
	{
        if (0 !=$c=floor($t/(int)$k))
        {
            return $c.$v.'前';
        }
    }

}


//外部重定向,会立即结束脚本以发送header
//内部重定向run(array);
function redirect($url,$seconds=0)
{
	header("Refresh: {$seconds}; url={$url}");
	exit();
}

function baseUrl($path=null)
{
	if(is_string($path))
	{
		return('http://'.$_SERVER['SERVER_NAME'].'/'.$path);
	}
	else
	{
		global $APP;
		$router=&$APP['router'];
		return isset($router[$path])?$router[$path]:null;
	}

}


//发送邮件,用来替代原生mail,多个接受者用分号隔开
function sendMail($mail_to, $mail_subject, $mail_message)
{
	$mail_subject = '=?utf-8?B?'.base64_encode($mail_subject).'?=';
	$mail_message = chunk_split(base64_encode(preg_replace("/(^|(\r\n))(\.)/", "\1.\3", $mail_message)));
	$headers  = "";
	$headers .= "MIME-Version:1.0\r\n";
	$headers .= "Content-type:text/html\r\n";
	$headers .= "Content-Transfer-Encoding: base64\r\n";
	$headers .= "From: ".MAIL_NAME."<".MAIL_USERNAME.">\r\n";
	$headers .= "Date: ".date("r")."\r\n";
	list($msec, $sec) = explode(" ", microtime());
	$headers .= "Message-ID: <".date("YmdHis", $sec).".".($msec * 1000000).".".MAIL_USERNAME.">\r\n";
	if(!$fp = fsockopen(MAIL_SERVER,MAIL_PORT, $errno, $errstr, 30)) {exit("CONNECT - Unable to connect to the SMTP server");	}
	stream_set_blocking($fp, true);
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != '220') {exit("CONNECT - ".$lastmessage);}
	fputs($fp, (MAIL_AUTH ? 'EHLO' : 'HELO')." befen\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {exit("HELO/EHLO - ".$lastmessage);}
	while(1) {if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {break;}$lastmessage = fgets($fp, 512);}
	if(MAIL_AUTH) {
		fputs($fp, "AUTH LOGIN\r\n");$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {	exit($lastmessage);	}
		fputs($fp, base64_encode(MAIL_USERNAME)."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {	exit("AUTH LOGIN - ".$lastmessage);}
		fputs($fp, base64_encode(MAIL_PASSWORD)."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 235) {exit("AUTH LOGIN - ".$lastmessage);}

	}
	fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", MAIL_USERNAME).">\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 250) {
		fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", MAIL_USERNAME).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250) {exit("MAIL FROM - ".$lastmessage);}
	}
	foreach(explode(';', $mail_to) as $touser) {
		$touser = trim($touser);
		if($touser) {
			fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 250) {
				fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
				$lastmessage = fgets($fp, 512);
				exit("RCPT TO - ".$lastmessage);
			}
		}
	}
	fputs($fp, "DATA\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 354) 
	{
		exit("DATA - ".$lastmessage);
	}
	fputs($fp, $headers);
	fputs($fp, "To: ".$mail_to."\r\n");
	fputs($fp, "Subject: $mail_subject\r\n");
	fputs($fp, "\r\n\r\n");
	fputs($fp, "$mail_message\r\n.\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 250) {
		exit("END - ".$lastmessage);
	}
	fputs($fp, "QUIT\r\n");

}




// end file of core.php
