<?php


/**
* 
*/
class yinyuetai
{
	private static $content;

	function __construct()
	{
		
	}
	function init()
	{

	}
	function getInfo($url)
	{
		$content = file_get_contents($url);
		$time=$_SERVER['REQUEST_TIME'];
		preg_match('/property="og:title"[\s]+content="([^"]*)".*?>/i',$content,$title);
		//获取封面
		preg_match('/property="og:image" content="([^"]*)".*?>/',$content,$images);
		//获取MV的ID
		preg_match('/[\d]+/',$url,$song_id);
		$title=$title[1];
		$images=$images[1];
		$song_id=$song_id[0];
		//解析json
		$songurl = "http://www.yinyuetai.com/api/info/get-video-urls?callback=callback&videoId=".$song_id."&_=".$time;
		$data = file_get_contents($songurl);
		if (strpos($data, "callback") !== false)
		{
		    $lpos = strpos($data, "(");
		    $rpos = strrpos($data, ")");
		    $data  = substr($data, $lpos + 1, $rpos - $lpos -1);
		}
		$json= json_decode($data,true);
		$info=array('title'=>$title,'image'=>$images)+$json;
		return $info;

	}
	
}