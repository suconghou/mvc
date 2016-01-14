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

if(!function_exists('array_replace'))
{
	function array_replace()
	{
		$args=func_get_args();
		$num_args=func_num_args();
		$res=array();
		for($i=0;$i<$num_args;$i++)
		{
			if(is_array($args[$i]))
			{
				foreach($args[$i] as $key => $val)
				{
					$res[$key] = $val;
				}
			}
			else
			{
				trigger_error(__FUNCTION__ .'(): Argument #'.($i+1).' is not an array',E_USER_WARNING);
				return null;
			}
		}
		return $res;
	}
}


if(!function_exists('json_last_error_msg'))
{
	function json_last_error_msg()
	{
		static $ERRORS = array
		(
			JSON_ERROR_NONE => 'No error',
			JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
			JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
			JSON_ERROR_SYNTAX => 'Syntax error',
			JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
		);
		$error = json_last_error();
		return isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error';
	}
}

