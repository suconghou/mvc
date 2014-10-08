<?php

/**
* 
*/
class youku 
{
	
	function __construct()
	{
		
	}
	function getUrl($url)
	{
		$id=self::getId($url);
		$meta=self::getMeta($id);
		self::m3u8($meta);
	} 

	private static function getId($url)
	{

		$regex='/[a-z0-9A-Z]{13}/';
		$id=null;
		if(preg_match_all($regex,$url,$matches))
		{
			$id=isset($matches[0][0])?$matches[0][0]:null;
		}
		return $id;

	} 

	private static function getMeta($id)
	{
		$url="http://v.youku.com/player/getPlayList/VideoIDS/{$id}/Pf/4/ctype/12/ev/1";
		$json=file_get_contents($url);
		$data=json_decode($json,true);
		return $data;
	}

	private static function m3u8($meta)
	{
		$oip=$meta['data'][0]['ip'];
		$vid=$meta['data'][0]['vidEncoded'];
		$ep=$meta['data'][0]['ep'];
		var_dump($meta,$oip,$vid,$ep);
		var_dump(json_decode($ep));
		$url="http://pl.youku.com/playlist/m3u8?ctype=12&ep={0}&ev=1&keyframe=1&oip={1}&sid={2}&token={3}&type={4}&vid={5}";

	}

}