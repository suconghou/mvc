<?php

/**
* Static Provider
*
*/
class StaticProvider
{
	const route='\/(((?:([\w\-]{2,12})\/)?static\/)[a-z]{2,8}\/)([\w\-]{2,20})(\.min)?\.(css|js)';

	public function __construct($fullPath,$staticPath,$project,$filename,$compress,$ext)
	{
		$ver=isset($_GET['ver'])?$_GET['ver']:null;
		$config=$staticPath.'static.json';
		is_file($config)&&($config=json_decode(file_get_contents($config),true));
		if($ext=='js')
		{
			if($config&&isset($config['static']['js'][$filename]))
			{
				$files=array_unique(array_values($config['static']['js'][$filename]));
				$files=array_map(function($item)use($fullPath){return $fullPath.$item;},$files);
			}
			else
			{
				$files=array_map(function($item)use($fullPath,$ext){return $fullPath.$item.'.'.$ext;},array_unique(explode('-',$filename)));
			}
			$this->outPutJs($files,$project,$ver,$compress,$staticPath);
		}
		else if($ext=='css')
		{
			if($config&&isset($config['static']['css'][$filename]))
			{
				$files=array_unique(array_values($config['static']['css'][$filename]));
				$files=array_map(function($item)use($fullPath){return $fullPath.$item;},$files);
			}
			else
			{
				$files=array_map(function($item)use($fullPath,$ext){return $fullPath.$item.'.less';},array_unique(explode('-',$filename)));
			}
			$this->outPutCss($files,$project,$ver,$compress,$staticPath);
		}
		else
		{
			self::clearItem($staticPath);
		}
	}

	private function outPutCss($files,$project,$ver,$compress,$staticPath)
	{
		try
		{
			$debug=!($compress||$ver);
			$key=$content=$staticPath;
			$maxTime=$this->fileExist($files,$key,$content);
			if(isset($_SERVER['HTTP_IF_NONE_MATCH'])&&$_SERVER['HTTP_IF_NONE_MATCH']=="W/{$maxTime}")
			{
				return header('Content-Type: text/css',true,304);
			}
			if($content)
			{
				header('X-Cache: Hit',true);
			}
			else
			{
				header('X-Cache: Miss',true);
				$content=Minifier::parse($files,$debug,$ver,$project);
				$item=['update'=>$maxTime,'content'=>&$content];
				self::setItem($key,$item,$staticPath);
			}
			if(!$debug)
			{
				header("Expires: ".gmdate("D, d M Y H:i:s",$maxTime+86400)." GMT");
				header("Cache-Control: max-age=86400");
			}
			header("Etag: W/{$maxTime}");
			header('Content-Type: text/css');
			echo $content;
		}
		catch(Exception $e)
		{
			$msg=$e->getMessage();
			$code=$e->getCode();
			header("Error-At:{$msg}",true,$code==404?404:500);
			header('Content-Type: text/plain');
			return printf('Error %u : %s',$code,$msg);
		}
	}

	private function outPutJs($files,$project,$ver,$compress,$staticPath)
	{
		try
		{
			$debug=!($compress||$ver);
			$key=$content=$staticPath;
			$maxTime=$this->fileExist($files,$key,$content);
			if(isset($_SERVER['HTTP_IF_NONE_MATCH'])&&$_SERVER['HTTP_IF_NONE_MATCH']=="W/{$maxTime}")
			{
				return header('Content-type: text/javascript',true,304);
			}
			if($content)
			{
				header('X-Cache: Hit',true);
			}
			else
			{
				header('X-Cache: Miss',true);
				$content=Minifier::minify($files,$debug,$ver,$project);
				$item=['update'=>$maxTime,'content'=>&$content];
				self::setItem($key,$item,$staticPath);
			}
			if(!$debug)
			{
				header("Expires: ".gmdate("D, d M Y H:i:s",$maxTime+86400)." GMT");
				header("Cache-Control: max-age=86400");
			}
			header("Etag: W/{$maxTime}");
			header('Content-type: text/javascript');
			echo $content;
		}
		catch(Exception $e)
		{
			$msg=$e->getMessage();
			$code=$e->getCode();
			header("Error-At:{$msg}",true,$code==404?404:500);
			header('Content-Type: text/plain');
			return printf('Error %u : %s',$code,$msg);
		}
	}

	private function fileExist(Array $files,&$staticPath,&$content=null)
	{
		$time=[];
		foreach ($files as $file)
		{
			if(is_file($file))
			{
				$time[]=filemtime($file);
			}
			else
			{
				throw new Exception("{$file} not found",404);
			}
		}
		$time=max($time);
		if($content)
		{
			$key=sprintf('%x',crc32(implode('',$files)));
			$item=self::getItem($key,$staticPath);
			$staticPath=$key;
			if($item&&$item['update']>=$time)
			{
				$content=$item['content'];
			}
			else
			{
				$content=null;
			}
		}
		return $time;
	}

	private static function setItem($key,Array $content,$staticPath,$expired=864000)
	{
		$file=$staticPath.'static.db';
		$data=is_file($file)?unserialize(file_get_contents($file)):[];
		$content['expired']=time()+$expired;
		$data[$key]=$content;
		return file_put_contents($file,serialize($data));
	}

	private static function getItem($key,$staticPath)
	{
		$file=$staticPath.'static.db';
		$data=is_file($file)?unserialize(file_get_contents($file)):[];
		$item=isset($data[$key])?$data[$key]:null;
		if(isset($item['expired'])&&$item['expired']<time())
		{
			unset($data[$key]);
			file_put_contents($file,serialize($data));
			return null;
		}
		return $item;
	}

	private static function clearItem($staticPath,$key=null)
	{
		$file=$staticPath.'static.db';
		if($key)
		{
			if($key===true)
			{
				return is_file($file)&&unlink($file);
			}
			else
			{
				$data=is_file($file)?unserialize(file_get_contents($file)):[];
				unset($data[$key]);
				return file_put_contents($file,serialize($data));
			}
		}
		else
		{
			$data=is_file($file)?unserialize(file_get_contents($file)):[];
			$now=time();
			$ok=true;
			foreach ($data as $key => $item)
			{
				if(!isset($item['expired'])||$item['expired']<$now)
				{
					$ok=false;
					unset($data[$key]);
				}
			}
			return $ok?:file_put_contents($file,serialize($data));
		}
	}
}

