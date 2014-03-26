<?

//mvc 核心框架类库
//update 2014.03.25
//修复nginx 304

require 'config.php';

//正则路由分析器
function regex_router($uri)
{
	global $APP;
	foreach ($APP['regex_router'] as $regex)///遍历所有pattren
	{
		$ids = array();	
		$char = substr($regex[0], -1);
		$pattern=str_replace(array(')','*'), array(')?','.*?'),$regex[0]); //第一步替换
		$pattern=preg_replace_callback( '#@([\w]+)(:([^/\(\)]*))?#',
		function($matches) use (&$ids)
		{
            $ids[$matches[1]] = null;
            if (isset($matches[3]))
            {
                return '(?P<'.$matches[1].'>'.$matches[3].')';
            }
            return '(?P<'.$matches[1].'>[^/\?]+)';
        },$pattern);//第二步替换
		$pattern=($char==='/')?$pattern.'?':$pattern.'/?';//第三步 Fix trailing slash
		// Attempt to match route and named parameters
		if (preg_match('#^'.$pattern.'(?:\?.*)?$#i', $uri, $matches))///说明成功匹配
	 	{	
	 		foreach ($ids as $k => $v)
		 	{
	            $params[$k] = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;
	        }
	      	$APP['use_regex_router']=$regex;//标记将要使用的路由
	 		break;
	 	}

	}//end foreach

	if(isset($APP['use_regex_router']))
	{
		$router=array_merge($APP['use_regex_router'][1],$params);
		return $router;
	}
	else///遍历完了也没捕获到
	{
		return false;
	}

}


//普通路由分析器
function common_router($uri)
{

	$uri_arr=explode('/', $uri);
	foreach ($uri_arr as  $v)
	{
		$v&&($router[]=$v);
	}
	unset($uri_arr);
	return isset($router)?$router:null;
}

//异常处理 404 500等
function show_errorpage($errno, $errstr, $errfile=null, $errline=null)
{
	$e = new Exception;
	$trace=$e->getTraceAsString();
	$trace_arr=preg_split('/#[0-9]/',$trace);
	$h2=$errstr;
	$h3=null;
	foreach ($trace_arr as $key => $value)
	{
		if(!$value)continue;
		$h3.='<p>#'.$key.$value.'</p>';
	}
	if($errno==404)
	{
		http_response_code($errno);
		if(USER_ERROR_PAGE_404)
		{
			require 'app/s/error/'.USER_ERROR_PAGE_404;
			die;
		}
		else
		{	
			$h1='404 - Request Page Not Found !';	
			log_message($h1.$h2);
			if(!DEBUG)
			{
				$h2='Please contact the administrator';
				$h3='';
			}

		}
	}
	else if($errno==500)
	{
		http_response_code($errno);
		if(USER_ERROR_PAGE_500)
		{
			require 'app/s/error/'.USER_ERROR_PAGE_500;
			die;
		}
		else
		{	
			$h1='500 - Internet Server Error !';
			log_message($h1.$h2);
			if(!DEBUG)
			{
				$h2='Please contact the administrator';
				$h3='';
			}

		}
	}
	else///系统抛出错误
	{	
		http_response_code($errno);
		if(USER_ERROR_PAGE_500)
		{
			require 'app/s/error/'.USER_ERROR_PAGE_500;
			die;
		}
		else if(USER_ERROR_PAGE_404)
		{
			require 'app/s/error/'.USER_ERROR_PAGE_404;
			die;
		}
		else ////没有设置错误页,但是系统错误
		{
			$h1='An error occurred at '.$errfile.' on line '.$errline.' ';		
			log_message($h1.$h2);
			if(!DEBUG)
			{
				$h1='Something Error !';
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
		global $APP;
		$APP['regex_router'][]=array($regex,$arr);
	}
	
}

///流程导航器,第一个启动的

function process()
{
	(strlen($_SERVER['REQUEST_URI'])>MAX_URL_LENGTH)&&show_errorpage('404','The url too long !');
	global $APP;///全局变量
	if(REGEX_ROUTER)
	{
		$router=regex_router($_SERVER['REQUEST_URI']);
		$router||($router=common_router($_SERVER['REQUEST_URI']));
	}
	else
	{
		$router=common_router($_SERVER['REQUEST_URI']);
	}

	$router[0]=empty($router[0])||$router[0]=='index.php'?DEFAULT_CONTROLLER:$router[0];
	$router[1]=empty($router[1])?DEFAULT_ACTION:$router[1];
	$APP['router']=$router;

	return $router;

}
function run($router)
{	

	$controller='app/c/'.$router[0].'.php';
	if(is_file($controller))
	{
		require $controller;
		if(class_exists($router[0]))
		{
			$methods=get_class_methods($router[0]);
			in_array($router[1], $methods)||show_errorpage('404','class '.$router[0].' does not contain method '.$router[1]);
			$router[0]=new $router[0]();///实例化控制器
			unset($controller);
			call_user_func_array(array($router[0],$router[1]), array_slice($router,2));//传入参数
		}
		else
		{
			show_errorpage('404','the contoller file '.$controller.' does not contain the router class '.$router[0]);
		}
	}
	else
	{
		show_errorpage('404','the controller file '.$controller.' does not exists');
	}
}

//记录日志的函数
function log_message($msg)
{
	$path='app/s/error/'.date('Y-m-d',APP_START_TIME).'.log';
	$msg=date('Y-m-d H:i:s',time()).' ==> '.$msg."\r\n";
	error_log($msg,3,$path);
	unset($path);
	unset($mag);
}

function M($model)
{
	global $APP;
	$APP['model'][$model]=isset($APP['model'][$model])?$APP['model'][$model]:$model;
	if($APP['model'][$model] instanceof $model)
	{
		return $APP['model'][$model];
	}
	else
	{
		$model_file='app/m/'.$model.'.php';
		is_file($model_file)||show_errorpage('500','load model '.$model.' failed , mdoel file '.$model_file.' does not exists ');
		require $model_file;
		class_exists($model)||show_errorpage('500','model file '.$model_file .' does not contain class '.$model);
		$APP['model'][$model]=new $model();///对模型实例化
		return $APP['model'][$model];
	}
}
//加载类库
function S($lib,$isclass=true)
{
	if(!$isclass)//要加载的不是类
	{
		$fun_file='app/s/'.$lib.'.php';
		is_file($fun_file)||show_errorpage('500','load common library '.$lib.' failed , library file '.$fun_file.' does not exists ');
		return require $fun_file;
	}
	global $APP;
	$APP['lib'][$lib]=isset($APP['lib'][$lib])?$APP['lib'][$lib]:$lib;
	if($APP['lib'][$lib] instanceof $lib)
	{
		return $APP['lib'][$lib];
	}
	else
	{
		$lib_file='app/s/'.$lib.'.class.php';
		is_file($lib_file)||show_errorpage('500','load class library '.$lib.' failed , library file '.$lib_file.' does not exists ');
		require $lib_file;
		class_exists($lib)||show_errorpage('500','library file '.$lib_file .' does not contain class '.$lib);
		$APP['lib'][$lib]=new $lib();///对模型实例化
		return $APP['lib'][$lib];
	}
}
//加载视图
function V($view,$data=null)
{
	$view_file='app/v/'.$view.'.php';
	if(is_file($view_file))
	{
		is_array($data)||empty($data)||show_errorpage('500','param to view '.$view_file.' show be an array');
		empty($data)||extract($data);
		GZIP?ob_start("ob_gzhandler"):ob_start();		
		define('APP_TIME_SPEND',round((microtime(true)-APP_START_TIME),4));//耗时
		define('APP_MEMORY_SPEND',byte_format(memory_get_usage()-APP_START_MEMORY));
		require $view_file;
		global $APP;
		if(isset($APP['cache']))//启用了缓存
		{
			$expires_time=time()+$APP['cache']['time'];
			if($APP['cache']['file'])//生成文件缓存
			{
				$contents=ob_get_contents();
				$cache_file='static/cache/'.md5(implode('-',$APP['router'])).'.html';
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
		show_errorpage('404','view file '.$view_file.' does not exists ');
	}
		
}
//缓存,第一个参数为缓存时间,第二个为是否文件缓存
function C($time,$file=null)
{
	global $APP;
	$cache['time']=$time*60;
	$cache['file']=$file;
	$APP['cache']=$cache;
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
	$controller_file='app/c/'.$class.'.php';
	$model_file='app/m/'.$class.'.php';	
	if(is_file($model_file))
	{
		require $model_file;
		class_exists($class)||show_errorpage('500','Autoload file '.$model_file.' successfully,but not found class '.$class);
	}
	else if(is_file($controller_file))
	{
		require $controller_file;
		class_exists($class)||show_errorpage('500','Autoload file '.$controller_file.' successfully,but not found class '.$class);
	}
	else
	{
		show_errorpage('500','Can not autoload class file '.$class.'.php');
	}
	unset($controller_file);
	unset($model_file);
}
//来访信息
function userInfo()
{
	function getVar($var,$default=null)
	{
		return isset($_SERVER[$var])?$_SERVER[$var] : $default;
	}
	$info['ip']=getVar('REMOTE_ADDR');
	$info['ip']||$info['ip']=getVar('HTTP_X_FORWARDED_FOR');
	$info['ip']||$info['ip']=getVar('HTTP_CLIENT_IP');
	$info['ajax']=getVar('HTTP_X_REQUESTED_WITH')=='XMLHttpRequest';
	$info['os']=getVar('HTTP_USER_AGENT');

	$info['methond']='';
	$info['broswer']='';
	$info['ispc']='';

}

/**
* model 层
*/
class model 
{
	static $link;///单例模式

	function __construct()
	{

		if(DB)//使用sqlite
		{
			try
			{
				self::$link=new PDO("sqlite:".SQLITE);
			}
			catch ( Exception $e )
			{
            	show_errorpage('500','connect sqlite database error ! '.$e->getMessage());
        	}

		}
		else///使用mysql
		{
			try
			{			

				$dsn="mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT;
				self::$link= new PDO ($dsn,DB_USER,DB_PASS,array (PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				self::$link->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			}
			catch ( Exception $e )
			{
           	 	show_errorpage('500','connect mysql database error ! '.$e->getMessage());
        	}
		}
		

	}
	//运行Sql语句,不返回结果集,但会返回成功与否,不能用于select
	function runSql($sql)
	{
		try
		{
			$rs=self::$link->exec($sql);
			return $rs;
		}
		catch (PDOException $e)
		{
			show_errorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}
		
		
	}

	////运行Sql,以多维数组方式返回结果集
	function getData($sql)
	{
		try
		{
			$rs=self::$link->query($sql);
			return $rs->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			show_errorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}


	}
	//运行Sql,以数组方式返回结果集第一条记录
	function getLine($sql)
	{
		try
		{
			$rs=self::$link->query($sql);
			return $rs->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			show_errorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}

	}
	//运行Sql,返回结果集第一条记录的第一个字段值
	function getVar($sql)
	{
		try
		{
			$rs=self::$link->query($sql);
			return $rs->fetchColumn();
		}
		catch (PDOException $e)
		{
			show_errorpage('500','run sql [ '.$sql.' ] error :<br> '.$e->getMessage());
		}


	}
	function lastId()
	{
		return self::$link->lastInsertId();
	}
	//返回原生的PDO对象
	function getInstance()
	{
		return self::$link;
	}
	function escape($str)
	{
		return mysql_real_escape_string($str);
	}
	function commitTransaction($sqlarr)
	{	
		is_array($sqlarr)||show_errorpage('500','提交的事务应是一个SQL语句数组');
		try
		{
			self::$link->beginTransaction();
			foreach ($sqlarr as $sql)
			{
				self::$link->exec($sql);
			}
			self::$link->commit();
		}
		catch(Exception $e)
		{ 
			self::$link->rollBack(); 
			show_errorpage('500',"Failed: " . $e->getMessage()); 
		} 
	}

	function __destruct()
	{
		self::$link=null;
	}
}//end class model



/////////some functions blow
function byte_format($size,$dec=2)
{
    $unit=array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
    return round($size/pow(1024,($i=floor(log($size,1024)))),$dec).' '.$unit[$i];
}

//外部重定向//内部重定向run(array);
function redirect($url,$seconds=0)
{
	header("Refresh: {$seconds}; url={$url}");
	exit();
}


//发送邮件,用来替代原生mail
function sendmail($mail_to, $mail_subject, $mail_message)
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
	foreach(explode(',', $mail_to) as $touser) {
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
