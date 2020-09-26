<?php

/**
 * 可以继承base类或者其他控制器类,也可以继承模型类,也可以什么都不继承
 */
class home
{

	function __construct()
	{
		//可以添加权限控制,保护整个控制器
	}

	public function index($value = '')
	{
		echo $value;
	}

	function __invoke($e)
	{
		app::json($e);
	}

	function phpinfo()
	{
		phpinfo();
	}
}
