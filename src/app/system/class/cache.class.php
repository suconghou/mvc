<?php
/**
* memcache,redis,file 缓存,memcache支持sae
* set,get,del,flush
* mset,mdel,mget
* file 方式在高并发下可能存在性能问题
* @author suconghou
* @link http://blog.suconghou.cn
* @version V1.20
*/
class cache
{

	private static $memcacheServer='127.0.0.1';
	private static $memcachePort=11211;


	private static $redisServer='127.0.0.1';
	private static $redisPort=6379;

	private static $fileCache; //可以自定义路径或者使用系统默认
	private static $fileArr;

	private static $cache;
	private static $cacheType;
	
	function __construct($type='memcache')
	{
		switch ($type)
		{
			case 'memcache':
				self::initMemcache();
				break;
			case 'redis':
				self::initRedis();
				break;
			case 'file':
				self::initFile();
				break;
			default:
				self::halt(1);
				break;
		}	
	}

	private static  function initMemcache()
	{
		if(function_exists('memcache_init'))
		{
			self::$cache=memcache_init();
		}
		else
		{
			self::$cache= new Memcache();
			self::$cache->connect(self::$memcacheServer, self::$memcachePort);
		}
		self::$cacheType='memcache';
	}
	private static function initRedis()
	{
		self::$cache= new Redis();
		self::$cache->connect(self::$redisServer,self::$redisPort);
		self::$cacheType='redis';

	}
	private static function initFile()
	{
		
		self::$fileCache=empty(self::$fileCache)?sys_get_temp_dir().'/FILECACHE':self::$fileCache;
		if(!file_exists(self::$fileCache))
		{
			$init=array('FileCacheInit'=>time());
			file_put_contents(self::$fileCache,serialize($init));
		}
		self::$fileArr=unserialize(file_get_contents(self::$fileCache));
		self::$cacheType='file';

	}

	/**
	 * 键名,键值,
	 * 过期时间(默认为24小时),单位为秒
	 */
	function set($key,$value,$expire=86400)
	{
		switch (self::$cacheType)
		{
			case 'memcache':
				return self::memcacheSet($key,$value,$expire);
				break;
			case 'redis':
				return self::redisSet($key,$value,$expire);
				break;
			case 'file':
				return self::fileSet($key,$value,$expire);
				break;
			default:
				self::halt(2);
				break;
		}
	}
	/**
	 * 键名
	 */
	function get($key)
	{
		switch (self::$cacheType)
		{
			case 'memcache':
				return self::memcacheGet($key);
				break;
			case 'redis':
				return self::redisGet($key);
				break;
			case 'file':
				return self::fileGet($key);
				break;
			default:
				self::halt(2);
				break;
		}

	}
	function del($key)
	{
		switch (self::$cacheType)
		{
			case 'memcache':
				return self::memcacheDel($key);
				break;
			case 'redis':
				return self::redisDel($key);
				break;
			case 'file':
				return self::fileDel($key);
				break;	
			default:
				self::halt(2);
				break;
		}

	}
	/**
	 * 删除所有
	 */
	function flush()
	{
		switch (self::$cacheType)
		{
			case 'memcache':
				return self::memcacheFlush();
				break;
			case 'redis':
				return self::redisFlush();
				break;
			case 'file':
				return self::fileFlush();
				break;	
			default:
				self::halt(2);
				break;
		}

	}
	/**
	 * 批量设置
	 */
	function mset($arr,$expire=86400)
	{
		foreach ($arr as $key => $value)
		{
			self::set($key,$value,$expire);
		}
		return true;

	}
	/**
	 * 批量获取
	 */
	function mget($arr)
	{
		$res=array();
		foreach ($arr as $key)
		{
			$res[$key]=self::get($key);
		}
		return $res;
	}
	/**
	 * 批量删除
	 */
	function mdel($arr)
	{
		foreach ($arr as $value)
		{
			self::del($value);
		}
		return true;
	}
	function incr($key)
	{
		return self::set($key,abs(intval(self::get($key)))+1);
	}
	function decr($key)
	{
		return self::set($key,abs(intval(self::get($key)))-1);
	}
	function incrby($key,$num)
	{
		return self::set($key,abs(intval(self::get($key)))+$num);
	}
	function decrby($key,$num)
	{
		return self::set($key,abs(intval(self::get($key)))-$num);
	}
	
	private static function memcacheSet($key,$value,$expire=86400)
	{
		return self::$cache->set($key,$value,0,$expire);
	} 
	private static function memcacheGet($key)
	{
		return self::$cache->get($key);
	}
	private static function redisSet($key,$value,$expire=86400)
	{
		return self::$cache->setex($key,$expire,$value);
	}
	private static function redisGet($key)
	{
		return self::$cache->get($key);
	}
	private static function fileSet($key,$value,$expire=86400)
	{
		if(empty(self::$fileArr))
		{
			self::fileFlush();
		}
		$realVal['v']=$value;
		$realVal['e']=$expire+time();
		self::$fileArr[$key]=$realVal;
		self::storFile();
		return true;
	}
	private static function fileGet($key)
	{
		if(isset(self::$fileArr[$key]))
		{
			if(time()<=self::$fileArr[$key]['e']) //未过期
			{
				return self::$fileArr[$key]['v'];
			}
			else//检测到有KEY过期
			{
				self::fileDel($key);
			}
			 
		}
		return null;
		
	}
	private static function memcacheFlush()
	{
		return self::$cache->flush();
	}
	private static function redisFlush()
	{
		return self::$cache->flushdb();
	}
	private static function fileFlush()
	{
		$val['v']=time();
		$val['e']=2*time();
		self::$fileArr=array('FileCacheInit'=>$val);
		self::storFile();
	}
	private static function memcacheDel($key)
	{
		return self::$cache->delete($key,0);
	}
	private static function redisDel($key)
	{
		return self::$cache->del($key);
	}
	private static function fileDel($key)
	{
		 if(isset(self::$fileArr[$key]))
		 {
		 	unset(self::$fileArr[$key]);
		 }
		 self::storFile();
		 return true;
	}
	private static function halt($num)
	{
		switch ($num)
		{
			case 1:
				exit('Error Cache Driver ! ');
				break;
			
			case 2:
				exit('Error Cache Type ! ');
				break;
			
			default:
				exit('Error ! ');
				break;
		}
	}
	private static function storFile()
	{
		file_put_contents(self::$fileCache,serialize(self::$fileArr));	
	}
	/**
	 * 文本方式删除已过期的
	 */
	private static function delFileExpire()
	{
		foreach (self::$fileArr as $key => $value)
		{
			if(time()>$value['e']) //过期
			{
				unset(self::$fileArr[$key]);
			}
		}
	}

	function __destruct()
	{
		if(self::$cacheType=='file')
		{
			self::delFileExpire();
			file_put_contents(self::$fileCache,serialize(self::$fileArr));	
		}
	}

}