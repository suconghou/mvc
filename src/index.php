<?

define('APP_START_TIME',microtime(true));//计时开始
define('APP_START_MEMORY',memory_get_usage());//初始内存大小
define('ROOT',dirname(__FILE__).'/');//根路径
define('APP_PATH',ROOT.'app/');//APP路径
define('LIB_PATH',APP_PATH.'s/');
define('MODEL_PATH',APP_PATH.'m/');
define('VIEW_PATH',APP_PATH.'v/');
define('CONTROLLER_PATH',APP_PATH.'c/');
require LIB_PATH.'core.php';//载入核心
CLI&&runCli();
if(!isset($GLOBALS['APP']['CLI']))
{
	$router=process();//获得路由信息
	$hash=ROOT.'static/cache/'.md5(implode('-',$router)).'.html';///缓存hash
	if (is_file($hash))//存在缓存文件
	{
		$expires_time=filemtime($hash);
		if(time()<$expires_time) ///缓存未过期
		{		 
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			{
				http_response_code(304);
				exit();  
			}
			else
			{	
				header('Last-Modified: ' . gmdate('D, d M y H:i:s',time()). ' GMT');   
				exit(file_get_contents($hash));
			}
		}
		else ///已过期
		{
			unlink($hash);  ///删除过期文件
			run($router);
		}
	}
	else
	{
		run($router);
	}

}

//end of file index.php
