<?
//VERSION 1.23
//update 20140622
//mvc 入口文件
define('APP_START_TIME',microtime(true));//计时开始
define('APP_START_MEMORY',memory_get_usage());//初始内存大小

require 'app/s/core.php';//载入核心

$router=process();//获得路由信息
$hash='static/cache/'.md5(implode('-',$router)).'.html';///缓存hash

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

//end of file index.php
