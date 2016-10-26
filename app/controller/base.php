<?php
/**
* 基础控制器类,继承此类以获得请求拦截
* 此类中无可访问的public方法,url不能访问此路由
*/
class base
{

	private static $version;
	private static $baseUrl;
	private static $project;
	private static $pathMap=['css'=>'/static/css/','style'=>'/static/style/','js'=>'/static/js/','img'=>'/static/img/'];

	public function __construct()
	{
		$this->globalIndex(); //全局加载的
	}

	final private function globalIndex($use=false)
	{
		if($use)
		{
			try
			{
				$this->busyBlock();
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
		header('HTTP/1.1 403 Forbidden',true,403)||exit($msg);
	}

	final private static function cors($host=null)
	{
		if(empty($host))
		{
			$host='*';
			if(isset($_SERVER['HTTP_REFERER'])||isset($_SERVER['HTTP_ORIGIN']))
			{
				$parts=parse_url($_SERVER['HTTP_REFERER']?:$_SERVER['HTTP_ORIGIN']);
				$host=sprintf('%s://%s%s',$parts['scheme'],$parts['host'],isset($parts['port'])?":{$parts['port']}":null);
			}
		}
		header('Access-Control-Allow-Origin: '.$host);
		header('Access-Control-Allow-Credentials:true');
		header('Access-Control-Allow-Methods:GET, POST, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept');
		return header('Access-Control-Max-Age:3600');
	}

	final private static function onlyCli()
	{
		return Request::isCli()||self::forbidden();
	}

	final private static function onlyAjax()
	{
		return Request::isAjax()||self::forbidden();
	}

	final private static function onlyPost()
	{
		return Request::isPost()||self::forbidden();
	}

	final protected function ipBlacklist(Array $ips,Closure $callback=null)
	{
		$ip=Request::ip();
		if(!$ip || in_array($ip,$ips))
		{
			if($callback)
			{
				return $callback($ip);
			}
			throw new Exception("ipBlacklist {$ip}",1);
		}
		return $this;
	}

	final protected function spiderBlock(Closure $callback=null)
	{
		if(Request::isSpider())
		{
			$ip=Request::ip();
			if($callback)
			{
				return $callback($ip);
			}
			throw new Exception("spiderBlock {$ip}",2);
		}
		return $this;
	}

	final protected function busyBlock($minute=1,$times=30,Closure $callback=null)
	{
		$ip=Request::ip();
		if($ip)
		{
			$now=time();
			$seconds=intval($minute*60);
			$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('%x.kv',crc32($ip));
			if(is_file($file))
			{
				$data=unserialize(file_get_contents($file));
				$data[$ip]=isset($data[$ip])?$data[$ip]:[0,0];
				list($currentTimes,$lastAccess)=$data[$ip];
				$currentTimes=($now-$lastAccess)>$seconds?0:$currentTimes+1;
				$data[$ip]=[$currentTimes,$now];
				file_put_contents($file,serialize($data));
				if($currentTimes>$times)
				{
					if($callback)
					{
						return $callback($ip);
					}
					throw new Exception("busyBlock {$ip}",4);
				}
				return $this;
			}
			else
			{
				$data=[$ip=>[0,0]];
				file_put_contents($file,serialize($data));
				return $this;
			}
		}
		if($callback)
		{
			return $callback($ip);
		}
		throw new Exception("busyBlock {$ip}",3);
	}


	/**********************************魔术方法********************************/

	final public function __call($method,$args=null)
	{
		return app::Error(404,"Call Error Method {$method} In Class ".get_called_class());
	}

	final public static function __callStatic($method,$args=null)
	{
		if(method_exists(__CLASS__,$method))
		{
			return call_user_func_array("self::{$method}",$args);
		}
		return app::Error(404,"Call Error Static Method {$method} In Class ".get_called_class());
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

	final private static function version($version=null,$debug=false)
	{
		return $version?(self::$version=$debug?'?debug':('?ver='.sprintf('%x',crc32($version)))):self::$version;
	}

	final private static function url($url=null)
	{
		return $url?(self::$baseUrl=$url):self::$baseUrl;
	}

	final private static function project($project=null)
	{
		return $project?(self::$project=$project):self::$project;
	}

	final private static function setPath($key,$value)
	{
		self::$pathMap[$key]='/'.trim($value,'/').'/';
		return self::$pathMap;
	}

	final private static function css($css,$project=null)
	{
		return self::assets($css,'css',$project?$project:self::$project);
	}

	final private static function js($js,$project=null)
	{
		return self::assets($js,'js',$project?$project:self::$project);
	}

	final private static function img($src,$project=null)
	{
		return self::assets($src,'img',$project?$project:self::$project);
	}

	final private static function assets($asset,$type,$project=null)
	{
		$links=[];
		$version=self::$version;
		$asset=is_array($asset)?$asset:[$asset];
		$basePath=($project?rtrim(self::$baseUrl,'/')."/{$project}":rtrim(self::$baseUrl,'/')).self::$pathMap[$type];
		$ext=strlen($version)<8?".{$type}{$version}":".min.{$type}{$version}";
		switch ($type)
		{
			case 'css':
				foreach ($asset as $item)
				{
					$links[]="<link rel='stylesheet' href='{$basePath}{$item}{$ext}'>";
				}
				return implode('',$links);
			case 'js':
				foreach ($asset as $item)
				{
					$links[]="<script src='{$basePath}{$item}{$ext}' defer></script>";
				}
				return implode('',$links);
			case 'img':
				foreach ($asset as $item)
				{
					$links[]="<img src='{$basePath}{$item}{$version}'>";
				}
				return implode('',$links);
			default:
				return null;
		}
	}

	final private static function lib($item,$main='main',$ext=[])
	{
		$version=self::$version;
		$base=['main'=>$main,'ver'=>$version];
		$ext=array_merge($base,$ext);
		foreach ($ext as $key => $value)
		{
			$ext[$key]="data-{$key}='{$value}'";
		}
		$script="<script src='{$item}{$version}' ".implode(' ',$ext)." defer></script>";
		return $script;
	}

	final private static function meta($title=null,$description=null,$keywords=null,$ext=[],$ie=false)
	{
		$meta=["<meta charset='UTF-8'>","<title>{$title}</title>","<meta http-equiv=X-UA-Compatible content='IE=edge,chrome=1'>"];
		$base=['csrf-token'=>csrf_token(),'renderer'=>'webkit','viewport'=>'width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no','description'=>$description,'keywords'=>$keywords];
		$ext=array_merge($base,$ext);
		foreach ($ext as $name => $content)
		{
			if($name&&$content)
			{
				$meta[]="<meta name='{$name}' content='{$content}'>";
			}
		}
		$ie=$ie?"<!--[if lt IE 9]><script src='//cdn.bootcss.com/html5shiv/r29/html5.min.js'></script><script src='//cdn.bootcss.com/respond.js/1.4.2/respond.min.js'></script><![endif]-->":null;
		return implode('',$meta).$ie;
	}

	/*******************应用程序配置区,定义为protected保证继承而又不会通过url触发******************/

	protected function Error404($msg=null)
	{
		echo $msg;
	}

	protected function Error500($msg=null)
	{
		echo $msg;
	}

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
