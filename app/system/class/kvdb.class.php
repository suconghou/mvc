<?php

/**
*	基于文本的简易k->v数据库
* 	KVDB API 
* 	set($key,$value)
*	mset($arr)
*	get($key)
*	mget($arr)
*	del($key)
*	mdel($arr)
*	gets($key)
*	like($key)
*	flush()
*	select($db)
*	
*/
class kvdb 
{
	private static $dbfile='kvdb.db';///默认数据库
	private static $dbarr;

	function __construct($file=null)
	{
		self::init($file);
	}
	///从文本读入内存
	private static function read()
	{
		self::$dbarr=unserialize(file_get_contents(self::$dbfile));
	}
	//存入文本
	private static function write()
	{
		file_put_contents(self::$dbfile,serialize(self::$dbarr));
	}
	/**
	 * 加载一个数据库初始化
	 */
	private static function init($file=null)
	{
		if($file==null)//加载默认
		{
			self::$dbfile=APP_PATH.self::$dbfile;
		}
		else if($file=='tmp')
		{	
			self::$dbfile=sys_get_temp_dir().'/KVDB.DB'; 
		}
		else //指定文件名
		{
			self::$dbfile=APP_PATH.$file;
		}
		if(!file_exists(self::$dbfile))
		{
			self::$dbarr=array('init_time'=>time());
			self::write();
		}
		self::read();
	}
	/**
	 * 切换到或初始化一个数据库
	 */
	function select($db=null)
	{
		if($db==null)
		{
			self::$dbfile='kvdb.db';
		}
		self::write();
		self::init($db);
	}
	/**
	 * 设置
	 */
	function set($key,$value)
	{
		if(empty(self::$dbarr))
		{
			$this->flush();
		}
		self::$dbarr[$key]=$value;
		self::write();
	}
	/**
	 * 批量设置
	 */
	function mset($arr)
	{
		if(empty(self::$dbarr))
		{
			$this->flush();
		}
		foreach ($arr as $key => $value)
		{
			self::$dbarr[$key]=$value;
		}
		self::write();
	}

	/**
	 * 获取
	 */
	function get($key)
	{
		return isset(self::$dbarr[$key])?self::$dbarr[$key]:null;
	}
	/**
	 * 批量获取
	 */
	function mget($arr) 
	{
		if(empty(self::$dbarr))
		{
			$this->flush();
		}
		$out=array();
		foreach ($arr as $key)
		{
			$out[$key]=isset(self::$dbarr[$key])?self::$dbarr[$key]:null;
		}
		return $out;
	}
	/**
	 * 删除
	 */
	function del($key)
	{
		if(isset(self::$dbarr[$key]))
		{
			unset(self::$dbarr[$key]);	
			self::write();
		}
		return true;
		
	}
	/**
	 * 批量删除
	 */
	function mdel($arr)
	{
		if(empty(self::$dbarr))
		{
			$this->flush();
		}
		foreach ($arr as $key )
		{
			unset(self::$dbarr[$key]);
		}
		self::write();

	}
	/**
	 * 清空此数据库
	 */
	function flush()
	{
		self::$dbarr=array('init_time'=>time());
		self::write();
	}
	function gets($key=null) ///已XX开头的KEY, 为空则所有KEY
	{
		if(empty(self::$dbarr))
		{
			$this->flush();
		}
		foreach (self::$dbarr as $k=> $v)
		{
			if(substr($k,0,strlen($key))==$key)
			{
				$res[$k]=$v;
			}
		}
		return isset($res)?$res:null;
	}
	function like($key=null)///包含有XX字符的
	{
		if(empty(self::$dbarr))
		{
			$this->flush();
		}
		foreach (self::$dbarr as $k => $v)
		{
			if (stripos($k,$key)!==false)
			{
				$res[$k]=$v;
			}
		}
		return isset($res)?$res:null;
	}
	function __destruct()
	{
		self::write();
	}

}
