<?php

/**
*	set/get/del/flush
*	还可调用query/exec等继承方法
*/
class Kvdb extends SQLite3
{
	private static $path;

	function __construct($file=null)
	{
		if($file)
		{
			self::$path=defined(VAR_PATH)?VAR_PATH.$file:$file;
		}
		else if(is_writeable('/dev/shm/'))
		{
			self::$path='/dev/shm/kvdb.db';
		}
		else
		{
			self::$path=sys_get_temp_dir().DIRECTORY_SEPARATOR.'kvdb.db';
		}
		$this->open(self::$path);
		$this->exec('PRAGMA synchronous=OFF');
		$this->exec('PRAGMA cache_size =8000');
		$this->exec('PRAGMA temp_store = MEMORY');
		$sql="CREATE TABLE IF NOT EXISTS `kvdb` (`k` text NOT NULL  PRIMARY KEY,`v` text NOT NULL)";
		return $this->exec($sql);
	}

	function set($key,$value)
	{
		$value=serialize($value);
		$sql="REPLACE INTO `kvdb` (k,v) VALUES('{$key}','{$value}') ";
		return $this->exec($sql);
	}

	function get($key,$default=null)
	{
		$sql="SELECT v FROM `kvdb` WHERE k='{$key}' ";
		$rs=$this->query($sql);
		if(FALSE==$rs)return $default;
		$value=$rs->fetchArray(SQLITE3_ASSOC);
		$data=unserialize($value['v']);
		return $data?$data:null; 
	}

	function del($key)
	{
		$key=is_array($key)?$key:array($key);
		$keys=implode(',', $key);
		$sql="DELETE FROM `kvdb` WHERE `k` IN ($keys)";
		return $this->exec($sql);
	}

	function flush()
	{
		$sql="DELETE FROM `kvdb`";
		return $this->exec($sql);
	}
	
	function copy($path)
	{
		return copy(self::$path,$path);
	}

	function __destruct()
	{
		
	}

}
