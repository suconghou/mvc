<?php


/**
* 酷盘api
*/
class Kupan 
{

  static $client_id='4ca2c199a24ec9a3415f586c77dc04d*';
  static $client_secret='44edbeca692b0818623476a9b232290*';
  static $backurl='http://www.baidu.com'; 

  static $code="52779143445274131ff75249632b56b*";//这个是手动获取的,根据用户连接的网盘
  static $refresh_token="567c0c9b711f6dbbb145dae9b38d3b6*";//重要
  static $token='c68819a76069f05368cf36916710e1d*';///每60分钟变化
  
  function __construct()
  {
	
  }
  function post($url,$post_string)
  {
	$ch=curl_init();
	curl_setopt_array($ch, array(CURLOPT_URL=>$url,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$post_string));
	$result=curl_exec($ch);
	curl_close($ch);
	return $result;
  }
	///第一步 获得code
  function get_userlink()
  {
	$url="https://auth.kanbox.com/0/auth?response_type=code&client_id=".self::$client_id."&platform=web&redirect_uri=".self::$backurl."&user_language=ZH";
	echo $url;
  }

  ///第二部获得token,会得出一枚ref_token
  function get_token()
  {
		$post_string="grant_type=authorization_code&client_id=".self::$client_id."&client_secret=".self::$client_secret."&code=".self::$code."&redirect_uri=".self::$backurl;
		$token_url='http://auth.kanbox.com/0/token';
		return json_decode($this->post($token_url,$post_string),true);
  }
  ///以后可以采用ref_token, 最常使用
  function get_token_byref()
  {
	$post_string="grant_type=refresh_token&client_id=".self::$client_id."&client_secret=".self::$client_secret."&refresh_token=".self::$refresh_token;
	$ref_url="https://auth.kanbox.com/0/token";
	return json_decode($this->post($ref_url,$post_string),true);
  }
  //进一步的封装
  function token()
  {
	$ret=$this->get_token_byref();
	$access_token=isset($ret['access_token'])?$ret['access_token']:implode(' ',$ret);
	return $access_token;
  }
  //下面操作函数
  function info()
  {
	$url="https://api.kanbox.com/0/info?bearer_token=".self::$token;
	return json_decode(file_get_contents($url),true);
  }


  function lists($path=null)
  {
	$url="https://api.kanbox.com/0/list{$path}?bearer_token=".self::$token;
	return json_decode(file_get_contents($url),true);
  }

  function download($path,$full=null)
  {
	$url="https://api.kanbox.com/0/download{$path}?bearer_token=".self::$token;
	if(!function_exists('get_headers2'))
	{
		function get_headers($url,$format=false)
		{
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL,            $url); 
			curl_setopt($ch, CURLOPT_HEADER,         true); 
			curl_setopt($ch, CURLOPT_NOBODY,         true); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_TIMEOUT,        5); 
			$r = curl_exec($ch); 
			$r = explode("\n", $r);
			array_pop($r); 
			array_pop($r); 
			if($format)
			{
			foreach ($r as $key => $value)
			{
			  $arr=explode(':', $value,2);
			  if(sizeof($arr)==2)
			  {
				$r[$arr[0]]=$arr[1];
				unset($r[$key]);
			  }
			}
			}
			return $r; 
		}

	}
	$head=get_headers($url,1);
	if(strpos($head[0],'404'))
	{
	  return '404';
	}
	if($full)return $head;
	else return $head['Location'];

  }

  function upload($path,$file)//有问题
  {
	
  $content="file={$file}&bearer_token=".self::$token;
  $url="https://api-upload.kanbox.com/0/upload{$path}?bearer_token=".self::$token;
  $res=$this->post($url,$content);
  return $res;
  }

  function delete($path)
  {
	$url="https://api.kanbox.com/0/delete{$path}?bearer_token=".self::$token;
	return json_decode(file_get_contents($url),true);

  }

  function move($path,$new_path)//有问题
  {
	$url="https://api.kanbox.com/0/move{$path}?destination_path={$new_path}&bearer_token=".self::$token;
	return json_decode(file_get_contents($url),true);
  }
  function copys($path,$new_path)
  {
	$url="https://api.kanbox.com/0/copy{$path}?destination_path={$new_path}&bearer_token=".self::$token;
	return json_decode(file_get_contents($url),true);
  }

  function create_folder($path)
  {
	$url="https://api.kanbox.com/0/create_folder{$path}?bearer_token=".self::$token;
	return json_decode(file_get_contents($url),true);

  }
  static function set_token($token)
  {
	self::$token=$token;
  } 


}
//end class kupan
