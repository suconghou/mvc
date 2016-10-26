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
	private static $config=['memcached'=>['memcached','127.0.0.1',11211],'memcache'=>['memcache','127.0.0.1',11211],'redis'=>['redis','127.0.0.1',6379]];
	private static $instance=[];
	private static $method=[];
	private static $currentInstanceName;

	public static function ready($name=null,$type=null,$host=null,$port=null)
	{
		$config=&self::$config;
		$name=strtolower($name);
		if(isset($config[$name]))
		{
			$config[$name]=[$type?strtolower($type):$config[$name][0],$host?$host:$config[$name][1],$port?$port:$config[$name][2]];
		}
		else
		{
			$config[$name]=[$type?strtolower($type):'memcached',$host?$host:'127.0.0.1',$port?$port:11211];
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
			if(empty($instances[$instance]))
			{
				throw new Exception("Error Cache Instance {$instance}",1);
			}
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
				$instance->addServer($host,$port,true);
				break;
			case 'memcached':
				if(!class_exists('Memcached'))
				{
					throw new Exception("Class Memcached Not Found",1);
				}
				$instance=new Memcached('pool');
				$instance->addServer($host,$port);
				break;
			case 'redis':
				if(!class_exists('Redis'))
				{
					throw new Exception("Class Redis Not Found",1);
				}
				$instance=new Redis();
				$instance->pconnect($host,$port,1);
				break;
			default:
				throw new Exception("Error Cache Driver",1);
		}
		return $instance;
	}

	public static function method($method,Closure $function)
	{
		return self::$method[$method]=$function;
	}

	public static function getConfig()
	{
		return ['config'=>self::$config,'instance'=>self::$instance,'method'=>self::$method,'currentInstanceName'=>self::$currentInstanceName];
	}

	public function __call($method,$args)
	{
		return self::__callStatic($method,$args);
	}

	public static function __callStatic($method,$args)
	{
		if(isset(self::$method[$method]))
		{
			return call_user_func_array(self::$method[$method],$args);
		}
		$instance=self::instance();
		if(method_exists($instance,$method))
		{
			return call_user_func_array(array($instance,$method),$args);
		}
		else
		{
			throw new Exception("Call Error Method {$method} In Class ".get_called_class(),1);
		}
	}
}





