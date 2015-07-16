<?php
/**
* 基础控制器类,继承此类以获得请求拦截
*/
class base
{

	private static $version;

	private static $baseUrl;

	private static $pathMap=array('css'=>'/static/css/','style'=>'/static/style/','js'=>'/static/js/','img'=>'/static/img/');

	
	function __construct()
	{
		$this->globalIndex(); //全局加载的
	}

	private final function globalIndex()
	{
		return $this->firewall();
	}

	/**
	 * 可以设置,自动过滤的内容
	 */
	private final function firewall($use=false)
	{
		if($use)
		{
			try
			{
				$ip=array('127.0.0.1');
				return $this->IpBlacklist($ip)->SpiderBlock()->BusyBlock();
			}
			catch(Exception $e)
			{
				header('HTTP/1.1 403 Forbidden',true,403);
				app::log($e->getMessage(),'ERROR');
			}

		}
		return $this; 
	}
	/**
	 * 开启跨域资源共享
	 */
	public static final function cors($allow=array())
	{
		$allow=is_array($allow)?$allow:array($allow);
		if($allow)
		{
			header('Access-Control-Allow-Origin: '.join(', ',$allow));
		}
		else
		{
			header('Access-Control-Allow-Origin: *');
		}
		return header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	}
	
	public static final function onlyCli()
	{
		return Request::isCli()||exit;
	}

	public static final function onlyAjax()
	{
		return Request::isAjax()||exit;
	}

	public static final function onlyPost()
	{
		return Request::isPost()||exit;
	}


	public final function IpBlacklist(Array $ips,Clouse $callback=null)
	{
		$ip=Request::ip();
		if(!$ip || in_array($ips,$ip))
		{
			if($callback)
			{
				return $callback($ip);
			}
			throw new Exception("block the ip {$ip}",1);
		}
		return $this;
	}

	public final function SpiderBlock(Clouse $callback=null)
	{
		if(Request::isSpider())
		{
			if($callback)
			{
				return $callback();
			}
			throw new Exception("block spider",2);
		}
		return $this;
	}
	
	public final function BusyBlock($times=30,Clouse $callback=null)
	{
		list($k,$v)=is_array($times)?$times:array(1=>$times);
		$ip=Request::ip();
		if($ip)
		{
			$key=md5($ip);
			$dir=sys_get_temp_dir().DIRECTORY_SEPARATOR.'BusyBlock'.DIRECTORY_SEPARATOR.substr($key,0,2);
			is_dir($dir) or mkdir($dir,0777,true);
			$file=$dir.DIRECTORY_SEPARATOR.substr($key,-6);
			$now=time();
			if(is_file($file))
			{
				$data=unserialize(file_get_contents($file));
				$data[$ip]=isset($data[$ip])?$data[$ip]:array($now);
				$size=count($data[$ip]);
				if($size>$v)
				{
					$ltime=$data[$ip][$size-$v];
				}
				else
				{
					$data[$ip][]=$now;
					return $this;
				}
			}
			else
			{
				$data[$ip][]=$now;
				file_put_contents($file,serialize($data));
				return $this;
			}
		}
		throw new Exception("busy block ip {$ip}",3);
	}

	function Error404($msg=null)
	{
		echo $msg;
	}
	function Error500($msg=null)
	{
		echo $msg;
	}

	/**********************************魔术方法********************************/


	public static final function __callStatic($method,$args=null)
	{
		echo "__callStatic";
	}

	public  final function __invoke()
	{
		echo "__invoke";
	}

	public final function __set()
	{

	}

	public final function __get()
	{


	}

	/**********************************资源以及版本管理********************************/

	public static final function version($version)
	{
		self::$version='?ver='.md5($version);
		return self::$version;
	}

	public static final function url($url)
	{
		self::$baseUrl=$url;
		return self::$baseUrl;
	}

	public static final function setPath($type,$value)
	{
		self::$pathMap[$type]='/'.trim($value,'/').'/';
		return self::$pathMap;
	}

	public static final function css($css,$project=null)
	{
		$links=array();
		$version=DEBUG?'?debug':self::$version;
		$css=is_array($css)?$css:array($css);
		$basePath=$project?rtrim(self::$baseUrl,'/').'/'.$project:rtrim(self::$baseUrl,'/');
		$basePath=$basePath.self::$pathMap['css'];
		foreach ($css as $item)
		{
			$links[]="<link rel='stylesheet' href='{$basePath}{$item}{$version}'>";
		}
		return implode('',$links);
	}

	public static final function js($js,$project=null)
	{
		$links=array();
		$version=DEBUG?'?debug':self::$version;
		$js=is_array($js)?$js:array($js);
		$basePath=$project?rtrim(self::$baseUrl,'/').'/'.$project:rtrim(self::$baseUrl,'/');
		$basePath=$basePath.self::$pathMap['js'];
		foreach ($js as $item)
		{
			$links[]="<script src='{$basePath}{$item}{$version}'></script>";
		}
		return implode('',$links);
	}

	/**********************************应用程序配置区**********************************/
	
	/**
	 *  检查用户是否登录
	 */
	function isUserLogin($addr='/')
	{
		
	}

	/**
	 * 检查管理员是否登录
	 */
	function isAdminLogin($addr='/')
	{
		
	}

	
}