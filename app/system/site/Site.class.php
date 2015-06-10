<?php

/**
* Single File Site 模式
*/
class Site
{
	private static $db;

	function __construct()
	{
		// 以此兼容php5.3,或直接使用 php53overWriteRouter 也没问题
		if(version_compare(phpversion(),5.4,'>'))
		{
			$this->overWriteRouter();
		}
		else
		{
			$this->php53overWriteRouter();
		}
		self::$db=new SiteDb();
	}

	/**
	 * php5.4+ 可以直接这样使用
	 */
	function overWriteRouter()
	{
		app::route('\/?',function(){
			$this->index();
		});
		app::route('\/post\/(\d+)\.html',function($id){
			$this->post($id);
		});
	}

	/**
	 * php5.3+ 可以这样使用,php5.3闭包内不可使用$this,此法可以兼容使用$this
	 */
	function php53overWriteRouter()
	{
		$self=$this;
		app::route('\/?',function() use($self){
			$self->index();
		});
		app::route('\/post\/(\d+)\.html',function($id) use($self){
			$self->post($id);
		});
	}

	function index()
	{
		echo "Single File Site";
	}

	function post($id)
	{
		exit($id);
		return V('view_post',self::$db->getPost($id));
	}


}



/**
* 可以继承db 或databsae 或任意一个已存在的模型,但不能不继承
* db是最基础的数据库操作类,在框架核心内声明
* database继承于db,然后又添加通用方法,增强,存放于app/model/下
*/
class SiteDb extends Database
{
	const postTable='post';

	function __construct()
	{
		
	}
	function getPost($id=null)
	{
		return $this->selectById(self::postTable,$id);
	}
}
	