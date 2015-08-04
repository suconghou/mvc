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

	function index()
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

