<?php

/**
* 文本存储,静态类
* Store::get();
*/

final class Store
{
	private static $instance=array();
	private static $currentInstanceName='store';

	public static function ready($db=null)
	{
		$instance=&self::$instance;
		$db=$db?$db:self::$currentInstanceName;
		if(empty($instance[$db]))
		{
			$instance[$db]=sys_get_temp_dir().$db.DIRECTORY_SEPARATOR;
			is_dir($instance[$db])||mkdir($instance[$db],0777,true);
		}
		self::$currentInstanceName=$db;
		return $instance[$db];
	}

	public static function set($key,$value,$expire=86400)
	{
		$filepath=self::getKeyPath($key);
		file_put_contents($filepath,serialize($value));
		return touch($filepath,time()+intval($expire));
	}
	
	public static function get($key)
	{
		$filepath=self::getKeyPath($key);
		return self::isExpired($filepath)?null:self::getCacheData($filepath);
	}

	public static function del($key)
	{
		$filepath=self::getKeyPath($key);
		return is_file($filepath)&&unlink($filepath);
	}

	public static function flush()
	{
		self::clearExpiredData();
		return self::delTree(self::ready());
	}

	private static function getCacheData($filepath)
	{
		return unserialize(file_get_contents($filepath));
	}

	private static function delTree($dir) 
	{
		$files=array_diff(scandir($dir),array('.','..')); 
		foreach($files as $file)
		{
			(is_dir("$dir/$file"))?self::delTree("$dir/$file"):unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	private static function getKeyPath($key)
	{
		$key=md5($key);
		$dir=substr($key,0,2);
		$file=substr($key,-30);
		$path=self::ready().$dir;
		is_dir($path)||mkdir($path,0777,true);
		return $path.DIRECTORY_SEPARATOR.$file;
	}

	private static function isExpired($filepath)
	{
		if(is_file($filepath))
		{
			if(filemtime($filepath)<time())
			{
				unlink($filepath);
				return true;
			}
			return false;
		}
		return true;
	}

	private static function clearExpiredData()
	{
		foreach (array_diff(scandir(self::ready()),array('.','..')) as $dir)
		{
			foreach (array_diff(scandir(self::ready().$dir),array('.','..')) as $filename)
			{
				self::isExpired(self::ready().$dir.DIRECTORY_SEPARATOR.$filename);
			}
		}
	}

}
