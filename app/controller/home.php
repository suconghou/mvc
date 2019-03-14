<?php

/**
* 可以继承base类或者其他控制器类,也可以继承模型类,也可以什么都不继承
*/
class home extends base
{
	
	function __construct()
	{
		//可以添加权限控制,保护整个控制器
	}

	public function index($value='')
	{
		header('Content-Type','application/json');
		echo "console.info('hello')";

	}

	/**
	 * popen 异步任务
	 */
	static function async1($task)
	{

		$arg="hello world";
		pclose(popen("/data/data/cn.suconghou.hello/files/php /mnt/sdcard/external_sd/web/task.php '$arg' >/dev/null 2>&1 &", 'r'));


	}

	/**
	 *  写入异步任务并返回文件名
	 */
	static function async(closure $task)
	{
     	return template('mvc');
	}

	function phpinfo()
	{
		phpinfo();
	}

	function Error404($msg=null)
	{
		exit("404 ERROR FOUND:{$msg}");
	}

	function Error500($msg=null)
	{
		exit("500 ERROR FOUND:{$msg}");
	}

}

