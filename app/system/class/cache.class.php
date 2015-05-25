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

	public static function set($key,$value,$expire=86400)
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
	private static $path;

	function __construct()
	{
		if(is_writeable('/dev/shm'))
		{
			self::$path='/dev/shm/cache/';
		}
		else
		{
			self::$path=sys_get_temp_dir().DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
		}
		if(!is_dir(self::$path))
		{
			mkdir(self::$path,0777,true);
		}
	}
	
	function set($key,$value,$expire=86400)
	{
		$path=null;
		$filepath=self::__filepath($key,$path);
		if(!is_dir($path))
		{
			mkdir($path,0777,true);
		}
		$data=serialize($value);
		file_put_contents($filepath,$data);
		return touch($filepath,time()+$expire);
	}
	
	function get($key)
	{
		$filepath=self::__filepath($key);
		if(self::__expired($filepath))
		{
			return null;
		}
		else
		{
			return self::__getData($filepath);
		}

	}

	function del($key)
	{
		$filepath=self::__filepath($key);
		return is_file($filepath)&&unlink($filepath);
	}

	function flush()
	{
		return self::delTree(self::$path);
	}
	
	public static function delTree($dir) 
	{
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file)
		{
			(is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file"); 
		}
		return rmdir($dir);
	}

	private static function __filepath($key,&$path=null)
	{
		$key=md5($key);
		$dir=substr($key,0,2);
		$file=substr($key,-30);
		$path=self::$path.$dir;
		return $path.DIRECTORY_SEPARATOR.$file;
	}

	private static function __expired($filepath)
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

	private static function __getData($filepath)
	{
		return unserialize(file_get_contents($filepath));
	}

	//todo delete tidy data
	function __destruct()
	{

	}
}





