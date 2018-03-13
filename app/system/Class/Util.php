<?php

/**
* 静态工具类,无需实例化,直接调用
*/
final class Util
{

	function __construct()
	{

	}

	public static function __callStatic($method,$args=null)
	{
		throw new Exception("Call Error Method {$method} In Class".get_called_class(),404);
	}

	public static function uuid($prefix='',$split='')
	{
		$str=md5(uniqid(mt_rand(),true));
		$uuid=substr($str,0,8).$split;
		$uuid.=substr($str,8,4).$split;
		$uuid.=substr($str,12,4).$split;
		$uuid.=substr($str,16,4).$split;
		$uuid.=substr($str,20,12);
		return $prefix.$uuid;
	}

	public static function arrayChange(Array $from,Array $to)
	{
		$delete=array_diff($from,$to);
		$add=array_diff($to,$from);
		return array('delete'=>$delete,'add'=>$add);
	}

	public static function arrayDelete($array,$item)
	{
		if(is_array($item))
		{
			return array_diff($array, $item);
		}
		else
		{
			if(($key=array_search($item, $array))!==false)
			{
				unset($array[$key]);
			}
			return $array;
		}
	}

	public static function isEmail($email)
	{
		return preg_match("/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i",$email);
	}

	public static function isPhone($tel)
	{
		return preg_match("/^1[3458][0-9]{9}$/",$tel);
	}

	public static function timeBefore($time)
	{
		$t=max(time()-$time,1);
		$f=array('31536000'=>'年','2592000'=>'个月','604800'=>'星期','86400'=>'天','3600'=>'小时','60'=>'分钟','1'=>'秒');
		foreach ($f as $k=>$v)
		{
			if (0!=$c=floor($t/(int)$k))
			{
				return $c.$v.'前';
			}
		}
	}

	public static function getOrderSN()
	{
		return (date('y')+date('m')+date('d')).str_pad((time()-strtotime(date('Y-m-d'))),5,0,STR_PAD_LEFT).substr(microtime(),2,6).sprintf('%03d',rand(0,999));
	}

	public static function convertStringEncoding(&$str,$set='UTF-8')
	{
		$charset=mb_detect_encoding($str);
		if($charset!=$set)
		{
			$str=iconv($charset,"{$set}//IGNORE",$str);
		}
		return $str;
	}

	public static function hex2rgb($c)
	{
		$r=hexdec(substr($c,0,2));
		$g=hexdec(substr($c,2,2));
		$b=hexdec(substr($c,-2));
		return array($r,$g,$b);
	}

	public static function rgb2hex($r,$g,$b)
	{
		return dechex($r).dechex($g).dechex($b);
	}

	public static function opt($key,$default=null)
	{
		$key="--{$key}=";
		foreach ($GLOBALS['argv'] as $item)
		{
			if(sizeof($arr=explode($key,$item))==2)
			{
				return end($arr);
			}
		}
		return $default;
	}

	public static function timer(closure $function,$exit=false,closure $callback=null)
	{
		while(true)
		{
			$data=$function();
			$break=($exit instanceof closure)?$exit($data):$exit;
			if($break)
			{
				return $callback?$callback($data):$data;
			}
		}
	}

	public static function encrypt($input,$key=null)
	{
		return str_replace(['+','/','='],['-','_',''],base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,md5($key),$input,MCRYPT_MODE_ECB,mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH,MCRYPT_MODE_ECB),MCRYPT_DEV_URANDOM))));
	}

	public static function decrypt($input,$key=null)
	{
		$input=str_replace(['-','_'],['+','/'],$input);
		if($mod=strlen($input)%4)
		{
			$input.=substr('====', $mod);
		}
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128,md5($key),base64_decode($input),MCRYPT_MODE_ECB,mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH,MCRYPT_MODE_ECB),MCRYPT_DEV_URANDOM)));
	}

	public static function async(closure $task=null,closure $callback=null)
	{
		function_exists('fastcgi_finish_request')&&fastcgi_finish_request();
		$data=$task();
		return $callback?$callback($data):$data;
	}

	public static function setItem($key,$value)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			$data[$key]=$value;
		}
		else
		{
			$data=[$key=>$value];
		}
		return file_put_contents($file,serialize($data));
	}
	public static function getItem($key,$default=null)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			return isset($data[$key])?$data[$key]:$default;
		}
		return $default;
	}
	public static function clearItem($key=null,&$file=null)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_null($key))
		{
			return is_file($file)&&unlink($file);
		}
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file)))&&isset($data[$key]))
		{
			unset($data[$key]);
			return file_put_contents($file,serialize($data));
		}
		return true;
	}

	public static function byteFormat($size,$dec=2)
	{
		$size=max($size,0);
		$unit=['B','KB','MB','GB','TB','PB','EB','ZB','YB'];
		return $size>=1024?round($size/pow(1024,($i=floor(log($size,1024)))),$dec).' '.$unit[$i]:$size.' B';
	}



}



