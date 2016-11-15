<?php

/**
* 文本存储,静态类
* Store::get();
* Store::set();
* Store::clear();
* Store::flush();
*/

final class Store
{
	private static $instance=[];
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

	public static function set($key,$value,$expired=86400)
	{
		$filepath=self::getPath($key);
		file_put_contents($filepath,serialize($value));
		return touch($filepath,$expired>2592000?$expired:time()+$expired);
	}

	public static function get($key,$default=null)
	{
		$filepath=self::getPath($key);
		return self::isExpired($filepath)?$default:self::getCacheData($filepath);
	}

	public static function clear($key=null)
	{
		if($key)
		{
			$filepath=self::getPath($key);
			return is_file($filepath)&&unlink($filepath);
		}
		return self::clearExpiredData();
	}

	public static function flush()
	{
		return self::delete(self::ready());
	}

	private static function getCacheData($filepath)
	{
		return unserialize(file_get_contents($filepath));
	}

	private static function delete($dir)
	{
		$files=array_diff(scandir($dir),array('.','..'));
		foreach($files as $file)
		{
			(is_dir("$dir/$file"))?self::delete("$dir/$file"):unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	private static function getPath($key)
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
		$db=self::ready();
		foreach (array_diff(scandir($db),array('.','..')) as $dir)
		{
			foreach (array_diff(scandir($db.$dir),array('.','..')) as $filename)
			{
				self::isExpired($db.$dir.DIRECTORY_SEPARATOR.$filename);
			}
		}
		return $db;
	}

}
