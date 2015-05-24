<?php
/**
* memcache,redis,file 缓存,memcache支持sae
* 配置可事先定义常亮到配置文件. 
* file 方式在高并发下可能存在性能问题
* @author suconghou
* @link http://blog.suconghou.cn
* @version V1.21
*/
class cache
{

	const memcacheServer='127.0.0.1';
	const memcachePort=11211;

	const redisServer='127.0.0.1';
	const redisPort=6379;

	private static $cache;
	/**
	 * true 自动判断
	 * 或者传入redis/memcache/file/sqlite四者之一
	 */
	function __construct($type=true)
	{
		if(function_exists('memcache_init'))
		{
			self::$cache = memcache_init();
		}
		else if(extension_loaded('Redis') and ($type === true or $type === 'redis') )
		{
			self::$cache = new Redis();
			self::$cache->connect(self::redisServer,self::redisPort);
		}
		else if(extension_loaded('Memcached') and ($type === true or $type ==='memcache') )
		{
			self::$cache = new Memcached();
			self::$cache->addServer(self::memcacheServer, self::memcachePort);
		}
		else if(extension_loaded('Memcache') and ($type === true or $type ==='memcache') )
		{
			self::$cache = new Memcache();
			self::$cache->addServer(self::memcacheServer, self::memcachePort);
		}
		else
		{
			self::$cache = new FileCache();
		}
	}

	public static function get($key)
	{
		return unserialize(self::$cache->get($key));
	}

	public static function set($key,$value)
	{
		return self::$cache->set($key,serialize($value));
	}

	function __call($name,$args)
	{
		return call_user_func_array(array(self::$cache,$name), $args);
	}

	public static function __callStatic($name,$args)
	{
		return call_user_func_array(array(self::$cache,$name), $args);
	}
	
	function __set($key,$value)
	{
		return self::$cache->set($key,serialize($value));
	}

	function __get($key)
	{
		return unserialize(self::$cache->get($key));
	}

	function __isset($key)
	{
		return self::$cache->get($key);
	}

	public static function getInstance()
	{
		return self::$cache;
	}


}

/**
 * file cache
 */
final class FileCache
{
	private static $temp;

	function __construct()
	{
		if(is_writeable('/dev/shm'))
		{
			self::$temp='/dev/shm/'.date('Ym');
		}
		else
		{
			self::$temp=sys_get_temp_dir().DIRECTORY_SEPARATOR.date('Ym');
		}
		if(!file_exists(self::$temp))
		{
			touch(self::$temp);
		}
	}
	
	//每个键最大存储100Kb
	function set($key,$value)
	{
		$value=serialize($value);
		if(strlen($value)>102400)
		{
			return false;
		}
		$key=str_pad(trim($key),60);

		$fp=fopen(self::$temp,'a+');
		while (! feof($fp))
		{
			$str=fgets($fp,204800);
			if($key == substr($str,0,60))
			{
				//delet this line
			}
		}
		$data=$key.$value.PHP_EOL;
		fwrite($fp,$data);
		return fclose($fp);

	}
	
	function get($key)
	{
		$fp=fopen(self::$temp,'r');
		if(!$fp)
		{
			return null;
		}
		$key=str_pad(trim($key),60);
		while (! feof($fp))
		{
			$str=fgets($fp,204800);
			if($key == substr($str,0,60))
			{
				return unserialize(ltrim($str,$key));
			}
		}
		return null;

	}

	//todo delete tidy data
	function __destruct()
	{

	}
}





