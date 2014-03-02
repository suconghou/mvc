<?
//date 20140218
//mvc 入口文件
$t=microtime(true);//计时开始
require 's/core.php';//载入核心
$uri=uri_init();//获得路由信息
$hash='v/cache/'.implode('-',$uri).'.html';///缓存hash

if (is_file($hash))//存在缓存文件
{

	if(time()<filemtime($hash)) ///未过期
	{		
		 exit(file_get_contents($hash));
	}
	else ///已过期
	{
		unlink($hash);  ///删除过期文件
		process($uri);
	}
}
else
{
	process($uri);
}

//end of file index.php
