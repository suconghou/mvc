<?php

/**
 *  常用PHP函数扩充和兼容性处理
 */

if(!function_exists('http_response_code'))
{
	function http_response_code($code)
	{
		$header=(substr(php_sapi_name(),0,3)=='cgi')?'Status: ':'HTTP/1.1 ';
		static $headers=array(
							200	=> 'OK', 201	=> 'Created', 202	=> 'Accepted', 203	=> 'Non-Authoritative Information', 204	=> 'No Content', 205	=> 'Reset Content', 206	=> 'Partial Content',
							300	=> 'Multiple Choices', 301	=> 'Moved Permanently', 302	=> 'Found', 304	=> 'Not Modified', 305	=> 'Use Proxy', 307	=> 'Temporary Redirect',
							400	=> 'Bad Request', 401	=> 'Unauthorized', 403	=> 'Forbidden', 404	=> 'Not Found', 405	=> 'Method Not Allowed', 406	=> 'Not Acceptable', 407	=> 'Proxy Authentication Required', 408	=> 'Request Timeout', 409	=> 'Conflict', 410	=> 'Gone', 411	=> 'Length Required', 412	=> 'Precondition Failed', 413	=> 'Request Entity Too Large', 414	=> 'Request-URI Too Long', 415	=> 'Unsupported Media Type', 416	=> 'Requested Range Not Satisfiable', 417	=> 'Expectation Failed',
							500	=> 'Internal Server Error', 501	=> 'Not Implemented', 502	=> 'Bad Gateway', 503	=> 'Service Unavailable', 504	=> 'Gateway Timeout', 505	=> 'HTTP Version Not Supported'
							);
		if(isset($headers[$code]))
		{
			$text=$headers[$code];
			header("{$header} {$code} {$text}",true,$code);
		}
	}
}

/**
 * 自然下标、制成二维map、化为一维数组,array_column兼容
 */

if(!function_exists('array_column'))
{
	function array_column($input,$columnKey=null,$indexKey=null)
	{
		if($columnKey&&!$indexKey)
		{
			return array_map(function($item){ return $item[$columnKey]; },$input);
		}
		else
		{
			$ret=array();
			foreach ($input as &$item)
			{
				if($indexKey)
				{
					$ret[$item[$indexKey]]=$columnKey?$item[$columnKey]:$item;
				}
				else
				{
					$ret[]=$columnKey?$item[$columnKey]:$item;
				}
			}
			return $ret;
		}
	}
}


function array_delete($array,$item)
{
	if(is_array($item))
	{
		return array_diff($array, $item);
	}
	else
	{
		if(($key = array_search($item, $array)) !== false)
		{
			unset($array[$key]);
		}
		return $array;
	}
}

function object2array(&$object)
{
	$object=json_decode(json_encode($object),true);
	return $object;
}


function timeBefore($time)
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

function createUuid($prefix='',$split='')
{
	$str=md5(uniqid(mt_rand(),true));
	$uuid=substr($str,0,8).$split;
	$uuid.=substr($str,8,4).$split;
	$uuid.=substr($str,12,4).$split;
	$uuid.=substr($str,16,4).$split;
	$uuid.=substr($str,20,12);
	return$prefix.$uuid;
}

function isEmail($email)
{
	return preg_match("/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i",$email);
}

function isPhone($tel)
{
	return preg_match("/^1[3458][0-9]{9}$/",$tel);
}

function getOrderSN()
{
	return (date('y')+date('m')+date('d')).str_pad((time()-strtotime(date('Y-m-d'))),5,0,STR_PAD_LEFT).substr(microtime(),2,6).sprintf('%03d',rand(0,999));
}

function convertStringEncoding(&$str,$set='UTF-8')
{
	$charset=mb_detect_encoding($str);
	if($charset!=$set)
	{
		$str=iconv($charset,"{$set}//IGNORE",$str);
	}
	return $str;
}
