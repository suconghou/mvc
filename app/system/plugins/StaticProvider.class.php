<?php

/**
* Static Provider
* 
*/
class StaticProvider
{
	const route='\/static\/(css|style|js)(?:\/(\w+))';

	private static $debug=false;


	function __construct($type=null,$name=null)
	{
		if($type or $name)
		{
			return $this->init($type,$name);
		}
		else
		{
			return $this->overWriteRouter();
		}
	}

	function overWriteRouter()
	{
		app::route(self::route,function($type,$name=null)
		{
			return $this->init($type,$name);
		});
	}

	function init($type,$name=null)
	{
		self::$debug=isset($_GET['debug']);
		$filePath='static'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$name;
		if($type=='js')
		{
			$filePath.='.js';
			if(is_file($filePath))
			{
				return self::compileJs($filePath);
			}
		}
		else
		{
			$filePath.='.less';
			if(is_file($filePath))
			{
				return self::compileLess($filePath);
			}
		}
	}

	public static function compileLess($filePath)
	{
		$cacheObject=self::getItem($filePath);
		$input=$cacheObject?$cacheObject:$filePath;
		$less = new Lessc;
		self::$debug?null:$less->setFormatter("compressed");
		try
		{
			$ret=$less->cachedCompile($input);
			ob_end_clean() and ob_start("ob_gzhandler");
			header('Content-Type: text/css');
			header("Etag: W/{$ret['updated']}");
			if(!self::$debug)
			{
				header("Expires: ".gmdate("D, d M Y H:i:s",$ret['updated']+86400)." GMT");
				header("Cache-Control: max-age=86400");
			}
			if(!is_array($input) or $ret['updated']>$input['updated'])
			{
				self::setItem($filePath,$ret);
				header('X-Cache: Miss',true,200);
				echo $ret['compiled'];
			}
			else
			{
				$code=isset($_SERVER['HTTP_IF_NONE_MATCH'])&&$_SERVER['HTTP_IF_NONE_MATCH']=="W/{$ret['updated']}"?304:200;
				header('X-Cache: Hit',true,$code);
				echo $code==304?null:$ret['compiled'];
			}
			ob_end_flush() and flush();
		}
		catch(Exception $e)
		{
			echo $e->getMessage();			
		}
	}

	public static function compileJs($filePath)
	{

	}

	private static function setItem($key,$data)
	{
		$filename=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5($key);
		return file_put_contents($filename,serialize($data));
	}

	private static function getItem($key)
	{
		$filename=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5($key);
		if(is_file($filename))
		{
			return unserialize(file_get_contents($filename));
		}
	}

	private static function getMapData()
	{

	}

	private static function getCssPath($key)
	{
		
	}

	private static function getJsPath($key)
	{

	}

}