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
		else if($file=='tmp') //使用零时文件
		{	
			self::$dbfile=sys_get_temp_dir().'/KVDB.DB'; 
		}
		else if($file=='sqlite')//使用sqlite
		{
			self::$dbfile=db::getInstance(false);
			self::sqlite_init();
		}
		else //指定文件名
		{
			self::$dbfile=APP_PATH.$file;
		}
		if(!is_object(self::$dbfile))
		{
			if(!file_exists(self::$dbfile))
			{
				self::$dbarr=array('init_time'=>time());
				self::write();
			}			
			self::read();
		}
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
		if(is_object(self::$dbfile)) // sqlite
		{
			return self::sqlite_set($key,$value);
		}
		else
		{
			if(empty(self::$dbarr))
			{
				$this->flush();
			}
			self::$dbarr[$key]=$value;
			self::write();
		}

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
	function get($key,$default=null)
	{
		if(is_object(self::$dbfile))
		{
			return self::sqlite_get($key,$default);
		}
		else
		{
			return isset(self::$dbarr[$key])?self::$dbarr[$key]:$default;
		}
	}
	/**
	 * 批量获取
	 */
	function mget($arr,$default=null) 
	{
		if(empty(self::$dbarr))
		{
			$this->flush();
		}
		$out=array();
		foreach ($arr as $key)
		{
			$out[$key]=isset(self::$dbarr[$key])?self::$dbarr[$key]:$default;
		}
		return $out;
	}
	/**
	 * 删除
	 */
	function del($key)
	{
		if(is_object(self::$dbfile)) //sqlite
		{
			return self::sqlite_del($key);
		}
		else
		{

			if(isset(self::$dbarr[$key]))
			{
				unset(self::$dbarr[$key]);	
				self::write();
			}
			return true;

		}
	}
	/**
	 * 批量删除
	 */
	function mdel($arr)
	{
		if(is_object(self::$dbfile))
		{
			return self::sqlite_del($arr);
		}
		else
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
		

	}
	/**
	 * 清空此数据库
	 */
	function flush()
	{
		if(is_object(self::$dbfile))
		{
			return self::sqlite_flush();
		}
		else
		{
			self::$dbarr=array('init_time'=>time());
			self::write();
		}
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
	private static function sqlite_init()
	{
		$sql="CREATE TABLE IF NOT EXISTS `kvdb` (`k` text NOT NULL  PRIMARY KEY,`v` text NOT NULL)";
		return self::$dbfile->exec($sql);
	}
	private static function sqlite_set($key,$value)
	{
		$value=serialize($value);
		if(self::sqlite_get($key))
		{
			$sql="UPDATE `kvdb` SET v='{$value}' WHERE k='{$key}' ";
		}
		else
		{
			$sql="INSERT INTO `kvdb` (k,v) VALUES('{$key}','{$value}') ";
			
		}
		return self::$dbfile->exec($sql);

	}
	private static function sqlite_get($key,$default=null)
	{
		$sql="SELECT v FROM `kvdb` WHERE k='{$key}' ";
		$rs=self::$dbfile->query($sql);
		if(FALSE==$rs)return $default;
		return unserialize($rs->fetchColumn());

	}
	private static function sqlite_del($key)
	{
		$key=is_array($key)?$key:array($key);
		$keys='';
		foreach ($key as $k)
		{
			$keys.="'".$k."',";
		}
		$keys=rtrim($keys,',');
		$sql="DELETE FROM `kvdb` WHERE `k` IN ($keys)";
		return self::$dbfile->exec($sql);
	}
	private static function sqlite_flush()
	{
		$sql="DELETE FROM `kvdb`";
		return self::$dbfile->exec($sql);

	}
	function __destruct()
	{
		if(!is_object(self::$dbfile))
		{
			self::write();
		}
	}

}
