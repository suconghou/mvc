<?php

/**
* Static Provider
* 
*/
class StaticProvider
{
	const route='\/(?:([\w-]+)\/)static\/(css|style|js)(?:\/([\w-\.]+))';

	private static $debug=false;

	private static $project;

	function __construct($project=null,$type=null,$name=null)
	{
		self::$project=$project;
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
		if($type=='js')
		{
			$files=array();
			$basePath='static'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR;
			if(self::$project)
			{
				$basePath=self::$project.DIRECTORY_SEPARATOR.$basePath;
			}
			foreach(explode('-',rtrim($name,'.js')) as $file)
			{
				if($file and $filePath=$basePath.$file.'.js')
				{
					if(!in_array($filePath,$files))
					{
						if(is_file($filePath))
						{
							$files[]=$filePath;
						}
						else
						{
							return false;
						}
					}
				}
			}
			if($files)
			{
				return self::compileJs($files);
			}
		}
		else
		{
			$filePath=rtrim('static'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$name,'.css').'.less';
			if(self::$project)
			{
				$filePath=self::$project.DIRECTORY_SEPARATOR.$filePath;
			}
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

	public static function compileJs($files)
	{
		$key=implode('-',$files);
		$cacheObject=self::getItem($key);
		$cacheObject=$cacheObject?$cacheObject:array('updated'=>0,'compiled'=>'','files'=>array());
		$lastUpdate=&$cacheObject['updated'];
		$fileList=&$cacheObject['files'];
		$fileTimes=array();
		foreach($files as $file)
		{
			$ftime=filemtime($file);
			$fileTimes[]=$ftime;
			if(!(isset($cacheObject['ftime-'.$file]) and $cacheObject['ftime-'.$file]>=$ftime))
			{
				$fileList[$file]=file_get_contents($file);
				$cacheObject['ftime-'.$file]=$ftime;
			}
		}
		ob_end_clean() and ob_start('ob_gzhandler');
		header('Content-type: text/javascript');
		self::$debug or header("Cache-Control: max-age=86400");
		if($lastUpdate>=max($fileTimes))
		{
			$contents=$cacheObject['compiled'];
			$code=isset($_SERVER['HTTP_IF_NONE_MATCH'])&&$_SERVER['HTTP_IF_NONE_MATCH']=="W/{$lastUpdate}"?304:200;
			header("Etag: W/{$lastUpdate}");
			header('X-Cache: Hit',true,$code);
			self::$debug or header("Expires: ".gmdate("D, d M Y H:i:s",$lastUpdate+86400)." GMT");
			echo $code==304?null:$contents;
		}
		else
		{
			$contents=self::compressJs(implode(PHP_EOL,$fileList));
			$cacheObject['compiled']=$contents;
			$lastUpdate=time();
			self::setItem($key,$cacheObject);
			header("Etag: W/{$lastUpdate}");
			header('X-Cache: Miss',true,200);
			self::$debug or header("Expires: ".gmdate("D, d M Y H:i:s",$lastUpdate+86400)." GMT");
			echo $contents;	
		}

	}

	public static function compressJs($data)
	{
		if(self::$debug)
		{
			return $data;
		}
		$packer=new JSqueeze;
		return $packer->squeeze($data,true,false);
	}

	private static function setItem($key,$data)
	{
		$filename=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT.$key);
		return file_put_contents($filename,serialize($data));
	}

	private static function getItem($key)
	{
		$filename=sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(ROOT.$key);
		if(is_file($filename))
		{
			return unserialize(file_get_contents($filename));
		}
	}


}