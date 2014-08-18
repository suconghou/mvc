<?php
/**
* 监控服务器是否能够访问
* 每当状态切换时发送提醒邮件
* 
*/
class server
{
	
	private static $log;
	private static $db;
	public static $receiver='1126045770@qq.com';

	function __construct()
	{
		self::$db=S('class/kvdb','tmp');
	}
	/**
	 * 添加要监控的服务器可访问地址
	 */
	function serverOk($url)
	{
		$urls=is_array($url)?$url:array($url);
		foreach ($urls as  $url)
		{
			$key=md5($url);
			$code=self::httpCode($url);
			$lastCode=self::$db->get($key);
			var_dump($code,$lastCode);
			if(is_null($lastCode))
			{
				self::$db->set($key,'200');
			}
			else if($lastCode!=$code) //状态不一致
			{
				self::$db->set($key,$code);
				$state=$code?' 可以访问':' 不能访问';
				self::$log.="检测到地址".$url.$state."<br>";
			}

		}
		echo self::$log;
		self::msg();
	}

	private static function httpInfo($url)
	{
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 1); 
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,3); //超时时长，单位秒    
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_exec($ch);   
		$res=curl_getinfo($ch);
		curl_close($ch);  
		return $res;
	}
	private static function httpCode($url)
	{
		$res=self::httpInfo($url);
		return $res['http_code'];
	}
	private static function msg()
	{
		if(self::$log)
		{
			$title='监控提醒邮件';
			sendMail(self::$receiver,$title,self::$log);
		}
	}

}