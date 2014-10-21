<?php

/**
* 酷我音乐调用
* search 搜索歌曲或歌手
* quicklink 歌曲名直接返回音乐地址
* $url=S('class/kuwoMusic')->quicklink('神武笑春风','mp3');
* $url=='404'||redirect($url);
*/

class kuwoMusic 
{
	static $q=null;//搜索词
	static $type='aac|mp3';///默认搜索的类型
	static $rn='10';//默认每页显示多少个
	//初始化
	function __construct()
	{
	}
	//获得指定页的搜索结果
	function search($q,$page=0)
	{
		$rn=self::$rn;
		$url="http://search.kuwo.cn/r.s?all={$q}&rformat=json&encoding=utf8&pn={$page}&rn={$rn}";
		$str=file_get_contents($url);
		$out=str_replace("'",'"',$str); //json数据不认识
		return $out;
	}
	///由ID获得真实地址
	function getlink($id,$type=null)
	{
		$t=$type?$type:self::$type;
		$url="http://antiserver.kuwo.cn/anti.s?response=url&type=convert_url&format={$t}&rid={$id}";
		return file_get_contents($url);

	}
	///信息的缩略和整合
	function getinfo($q,$page=0)
	{
		$res=$this->search($q,$page);
		$json=json_decode($res);
		foreach ($json->abslist  as $v)
		{
			$a['name']=$v->NAME;
			$a['artist']=$v->ARTIST;
			$a['album']=$v->ALBUM;
			$a['id']=$v->MUSICRID;
			$info[]=$a;
		}
		return isset($info)?$info:null;
	}
	function quicklink($q,$type=null,$i=0)
	{
		$res=$this->search($q);
		$json=json_decode($res);
		if(!$res||!$json||!isset($json->abslist))
		{
			return '404';
		}
		$ids=$json->abslist;
		$num=count($ids);
		$i=max(min(intval($i),$num-1),0);
		$id=$ids[$i]->MUSICRID;
		return $this->getlink($id,$type);
	}

}
