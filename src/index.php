<?

//mvc 入口文件

$t1=microtime(true);//计时开始
require 'S/core.php';//载入核心

$arr_uri=(new uri())->init();

$hash='V/cache/'.implode('-',$arr_uri).'.html';
if (is_file($hash))//存在缓存文件
{
	
	if(time()<file_get_contents($hash,null,null,4,10)) ///未过期
	{

		echo file_get_contents($hash);
	}
	else ///已过期
	{
		unlink($hash);  ///删除过期文件
		new process($arr_uri);

	}


}
else
{
	new process($arr_uri);
}

//var_dump($arr_uri);
