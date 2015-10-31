<?php
/**
* memcache,memcached,redis 缓存
* Usage
* Cache::ready('memcache')
* 
* @author suconghou
* @link http://blog.suconghou.cn
* @version V1.23
*/
final class Cache
{
	private static $config=array('memcache'=>array('memcache','127.0.0.1',11211),'memcached'=>array('memcached','127.0.0.1',11211),'redis'=>array('redis','127.0.0.1',6379));
	private static $instance=array();
	private static $currentInstanceName;

	public static function ready($name,$type=null,$host=null,$port=null)
	{
		$config=&self::$config;
		$name=strtolower($name);
		if(isset($config[$name]))
		{
			$config[$name]=array($type?strtolower($type):$config[$name][0],$host?$host:$config[$name][1],$port?$port:$config[$name][2]);
		}
		else
		{
			$config[$name]=array($type?strtolower($type):'memcache',$host?$host:'127.0.0.1',$port?$port:11211);
		}
		self::$currentInstanceName=$name?$name:key($config);
		return $config;
	}

	public static function instance($instance=null)
	{
		$instances=&self::$instance;
		if(!$instance)
		{
			if(!self::$currentInstanceName)
			{
				self::$currentInstanceName=key(self::$config);
			}
			$instance=self::$currentInstanceName;
		}
		$instance=strtolower($instance);
		if(empty($instances[$instance]))
		{
			if(isset(self::$config[$instance]))
			{
				list($type,$host,$port)=self::$config[$instance];
				$instances[$instance]=self::getInstance($type,$host,$port);
			}
			return false;
		}
		return $instances[$instance];
	}

	private static function getInstance($type,$host,$port)
	{
		$instance=false;
		switch ($type)
		{
			case 'memcache':
				if(!class_exists('Memcache'))
				{
					throw new Exception("Class Memcache Not Found",1);
				}
				$instance=new Memcache();
				$instance->addServer($host,$port);
				break;
			case 'memcached':
				if(!class_exists('Memcached'))
				{
					throw new Exception("Class Memcached Not Found",1);
				}
				$instance=new Memcached('persistent');
				$instance->addServer($host,$port);
				break;
			case 'redis':
				if(!class_exists('Redis'))
				{
					throw new Exception("Class Redis Not Found",1);
				}
				$instance=new Redis();
				$instance->connect($host,$port);
				break;
			default:
				if(function_exists('memcache_init'))
				{
					$instance=memcache_init();
				}
				else
				{
					throw new Exception("Error Cache Type",1);
				}
				break;
		}
		return $instance;
	}
	
	public function __call($name,$args)
	{
		return call_user_func_array(array(self::instance(),$name), $args);
	}

	public static function __callStatic($name,$args)
	{
		return call_user_func_array(array(self::instance(),$name), $args);
	}
	
	function __set($key,$value)
	{
		return self::instance()->set($key,$value);
	}

	function __get($key)
	{
		return unserialize(self::instance()->get($key));
	}

	function __isset($key)
	{
		return self::instance()->get($key);
	}

}





