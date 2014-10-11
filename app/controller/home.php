<?php

/**
* 可以继承base类或者其他控制器类,也可以继承模型类,也可以什么都不继承
*/
class home extends base
{
	
	function __construct()
	{
		//可以添加权限控制,保护整个控制器
		// session_get('LOGINED')||exit('login first');
	}
	/**
	 * home 是默认的控制器
	 * index 时默认的方法
	 */
	function index()
	{
		// C(10); //使用http缓存10分钟
     	V('mvc');//加载app/view里的mvc.php 视图
	}
	/**
	 * 若开启了自定义异常路由,则必须存在Error404和Error500方法
	 * 可以直接继承base类,不用再每个控制器中声明
	 * 也可以不继承base类,但需要自己声明
	 * 还可以继承base类,但是自己又重写方法覆盖原有的异常处理方式 
	 * 注意:使用闭包和Single File Site 模式时,若使用异常路由会使用home控制器里的异常路由
	 */
	function Error404($msg=null)
	{
		exit('404 ERROR FOUND:'.$msg);
	}
	/**
	 * 这里覆盖base类里的处理方式
	 */
	function Error500($msg=null)
	{
		exit('500 ERROR FOUND:'.$msg);
	}
	



}

