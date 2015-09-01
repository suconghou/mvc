<?php
/**
* memcache,redis,file 缓存,memcache支持sae
* 配置可事先定义常亮到配置文件. 
* file 方式在高并发下可能存在性能问题
* @author suconghou
* @link http://blog.suconghou.cn
* @version V1.21
*/
class Cache
{
	const memcacheServer='127.0.0.1';
	const memcachePort=11211;

	const redisServer='127.0.0.1';
	const redisPort=6379;

	private static $cache;

	/**
	 * true 自动判断,传入redis/memcache/file三者之一
	 */
	public function __construct($type=true)
	{
		self::init($type);
	}

	public static function init($type=true)
	{
		if(function_exists('memcache_init'))
		{
			self::$cache = memcache_init();
		}
		else if(extension_loaded('Redis') && ($type === true || $type === 'redis') )
		{
			self::$cache = new Redis();
			self::$cache->connect(self::redisServer,self::redisPort);
		}
		else if(extension_loaded('Memcached') && ($type === true || $type ==='memcache') )
		{
			self::$cache = new Memcached('persistent');
			self::$cache->addServer(self::memcacheServer, self::memcachePort);
		}
		else if(extension_loaded('Memcache') && ($type === true || $type ==='memcache') )
		{
			self::$cache = new Memcache();
			self::$cache->addServer(self::memcacheServer, self::memcachePort);
		}
		else
		{
			self::$cache = new FileCache();
		}
		return self::$cache;
	}

	public static function get($key)
	{
		return unserialize(self::getInstance()->get($key));
	}

	public static function set($key,$value,$expire=86400)
	{
		return self::getInstance()->set($key,serialize($value));
	}

	public function __call($name,$args)
	{
		return call_user_func_array(array(self::getInstance(),$name), $args);
	}

	public static function __callStatic($name,$args)
	{
		return call_user_func_array(array(self::getInstance(),$name), $args);
	}
	
	function __set($key,$value)
	{
		return self::getInstance()->set($key,serialize($value));
	}

	function __get($key)
	{
		return unserialize(self::getInstance()->get($key));
	}

	function __isset($key)
	{
		return self::getInstance()->get($key);
	}

	public static function getInstance()
	{
		return self::$cache?self::$cache:self::init();
	}


}

/**
 * file cache
 */
final class FileCache
{
	private static $path;

	public function __construct()
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
	
	public function set($key,$value,$expire=86400)
	{
		$path=null;
		$filepath=$this->getKeyPath($key,$path);
		if(!is_dir($path))
		{
			mkdir($path,0777,true);
		}
		$data=serialize($value);
		file_put_contents($filepath,$data);
		return touch($filepath,time()+$expire);
	}
	
	public function get($key)
	{
		$filepath=$this->getKeyPath($key);
		if($this->isExpired($filepath))
		{
			return null;
		}
		else
		{
			return $this->getCacheData($filepath);
		}

	}

	public function del($key)
	{
		$filepath=$this->getKeyPath($key);
		return is_file($filepath)&&unlink($filepath);
	}

	public function flush()
	{
		return self::delTree(self::$path);
	}
	
	public static function delTree($dir) 
	{
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file)
		{
			(is_dir("$dir/$file"))?self::delTree("$dir/$file"):unlink("$dir/$file"); 
		}
		return rmdir($dir);
	}

	private function getKeyPath($key,&$path=null)
	{
		$key=md5($key);
		$dir=substr($key,0,2);
		$file=substr($key,-30);
		$path=self::$path.$dir;
		return $path.DIRECTORY_SEPARATOR.$file;
	}

	private function isExpired($filepath)
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

	private  function getCacheData($filepath)
	{
		return unserialize(file_get_contents($filepath));
	}

	function __destruct()
	{
		if(!rand(0,999))
		{
			foreach (array_diff(scandir(self::$path),array('.','..')) as $dir)
			{
				foreach (array_diff(scandir(self::$path.$dir),array('.','..')) as $filename)
				{
					$this->isExpired(self::$path.$dir.DIRECTORY_SEPARATOR.$filename);
				}
			}
		}
	}
}





