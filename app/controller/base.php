<?php
/**
* 基础控制器类,继承此类以获得请求拦截
*/
class base
{

	private static $version;

	private static $baseUrl;

	private static $pathMap=array('css'=>'/static/css/','style'=>'/static/style/','js'=>'/static/js/','img'=>'/static/img/');
	
	private function __construct()
	{
		$this->globalIndex(); //全局加载的
	}

	final private function globalIndex()
	{
		return $this->firewall();
	}

	final private function firewall($use=false)
	{
		if($use)
		{
			try
			{
				$ip=array('127.0.0.2');
				return $this->IpBlacklist($ip)->SpiderBlock()->BusyBlock([1,20]);
			}
			catch(Exception $e)
			{
				$msg=$e->getMessage();
				app::log($msg,'ERROR');
				return self::forbidden($msg);
			}
		}
		return $this; 
	}

	final private static function forbidden($msg=null)
	{
		echo $msg;
		exit(header('HTTP/1.1 403 Forbidden',true,403));
	}

	/**
	 * 开启跨域资源共享
	 */
	final public static function cors($allow=array())
	{
		$allow=is_array($allow)?$allow:array($allow);
		if($allow)
		{
			header('Access-Control-Allow-Origin: '.join(', ',$allow),true);
		}
		else
		{
			header('Access-Control-Allow-Origin: *',true);
		}
		return header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept',true);
	}
	
	final public static function onlyCli()
	{
		return Request::isCli()||self::forbidden();
	}

	final public static function onlyAjax()
	{
		return Request::isAjax()||self::forbidden();
	}

	final public static function onlyPost()
	{
		return Request::isPost()||self::forbidden();
	}

	final public function IpBlacklist(Array $ips,Closure $callback=null)
	{
		$ip=Request::ip();
		if(!$ip || in_array($ip,$ips))
		{
			if($callback)
			{
				return $callback($ip);
			}
			throw new Exception("IpBlacklist Block {$ip}",1);
		}
		return $this;
	}

	final public function SpiderBlock(Closure $callback=null)
	{
		if(Request::isSpider())
		{
			$ip=Request::ip();
			if($callback)
			{
				return $callback($ip);
			}
			throw new Exception("SpiderBlock Block {$ip}",2);
		}
		return $this;
	}
	
	final public function BusyBlock(array $hz=array(1,30),Closure $callback=null)
	{
		$ip=Request::ip();
		if($ip)
		{
			$key=md5($ip);
			$now=time();
			$seconds=intval($hz[0]*60);
			$times=intval($hz[1]);
			$dir=sys_get_temp_dir().DIRECTORY_SEPARATOR.'BusyBlock'.DIRECTORY_SEPARATOR.substr($key,0,2);
			is_dir($dir) or mkdir($dir,0777,true);
			$file=$dir.DIRECTORY_SEPARATOR.substr($key,-6);
			if(is_file($file))
			{
				$data=unserialize(file_get_contents($file));
				$data[$ip]=isset($data[$ip])?$data[$ip]:array(0,0);
				list($currentTimes,$lastAccess)=$data[$ip];
				$currentTimes=($now-$lastAccess)>$seconds?0:$currentTimes+1;
				$data[$ip]=array($currentTimes,$now);
				file_put_contents($file,serialize($data));
				if($currentTimes>$times)
				{
					if($callback)
					{
						return $callback($ip);
					}
					throw new Exception("BusyBlock Block {$ip}",4);
				}
				return $this;
			}
			else
			{
				$data[$ip]=array(0,0);
				file_put_contents($file,serialize($data));
				return $this;
			}
		}
		if($callback)
		{
			return $callback($ip);
		}
		throw new Exception("BusyBlock Block {$ip}",3);
	}

	public function Error404($msg=null)
	{
		echo $msg;

	}

	public function Error500($msg=null)
	{
		echo $msg;
	}

	/**********************************魔术方法********************************/

	final public function __call($method,$args=null)
	{
		return self::Error404("{$method} not found");
	}

	final public static function __callStatic($method,$args=null)
	{
		return self::Error404("{$method} not found");
	}

	final public function __set($key,$value)
	{
		$this->$key=$value;
	}

	final public function __get($key)
	{
		return isset($this->$key)?$this->$key:null;
	}

	final public function __isset($key)
	{
		return isset($this->$key);
	}

	/**********************************资源以及版本管理********************************/

	final public static function version($version)
	{
		self::$version=DEBUG?'?debug':'?ver='.md5($version);
		return self::$version;
	}

	final public static function url($url)
	{
		self::$baseUrl=$url;
		return self::$baseUrl;
	}

	final public static function setPath($type,$value)
	{
		self::$pathMap[$type]='/'.trim($value,'/').'/';
		return self::$pathMap;
	}

	final public static function css($css,$project=null)
	{
		return self::assets($css,'css',$project);
	}

	final public static function js($js,$project=null)
	{
		return self::assets($js,'js',$project);
	}

	final public static function img($src,$project=null)
	{
		return self::assets($src,'img',$project);
	}

	final private static function assets($asset,$type,$project=null)
	{
		$links=array();
		$version=self::$version;
		$asset=is_array($asset)?$asset:array($asset);
		$basePath=$project?rtrim(self::$baseUrl,'/').'/'.$project:rtrim(self::$baseUrl,'/');
		$basePath=$basePath.self::$pathMap[$type];
		switch ($type)
		{
			case 'css':
				foreach ($asset as $item)
				{
					$links[]="<link rel='stylesheet' href='{$basePath}{$item}{$version}'>";
				}
				break;
			case 'js':
				foreach ($asset as $item)
				{
					$links[]="<script src='{$basePath}{$item}{$version}'></script>";
				}
				break;
			case 'img':
				foreach ($asset as $item)
				{
					$links[]="<img src='{$basePath}{$item}{$version}'>";
				}
				break;
			default:
				break;
		}
		return implode('',$links);
	}

	final public static function lib($item,$load='main')
	{
		$version=self::$version;
		$script="<script src='{$item}{$version}' data-load='{$load}' data-ver='{$version}' id='js-main'></script>";
		return $script;
	}

	final public static function meta()
	{

	}

	/**********************************应用程序配置区**********************************/
	
	/**
	 *  检查用户是否登录
	 */
	final protected function isUserLogin($addr='/')
	{
		
	}

	/**
	 * 检查管理员是否登录
	 */
	final protected function isAdminLogin($addr='/')
	{
		
	}

	
}