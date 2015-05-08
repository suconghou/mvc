<?php

/**
* file search engine
*/
class Search
{
	private static $googleIp='64.233.167.163';
	
	function __construct()
	{
		
	}

	/**
	* 抓取谷歌搜索结果
	* @param string $kw 关键字
	* @param int $num 每页显示结果数
	* @param int $pn 页数
	* @param string $site 指定站点
	* @return array
	*/
	function searchGoogle($kw,$num=20,$pn=1,$site=null)
	{
		if(self::$googleIp)
		{
			$kw=urlencode($kw);
			$start = $pn*$num;
			$url="http://".self::$googleIp."/search?sa=N&newwindow=1&safe=off&q={$kw}&num={$num}&start={$start}";
			if($site)
			{
				$url.="&sitesearch={$site}";
			}
			$html = self::__curlGet($url);
			return self::__getCleanData($html);
		}
		else
		{
			$kw = urlencode(base64_encode($kw));
			$url = "http://google.cccyun.cn/api.php?ver=2&kw={$kw}&page={$pn}&num={$num}&site={$site}";
			$contents = self::__curlGet($url);
			$json = json_decode(base64_decode($contents),true);
			return $json;
		}
		
	}

	private static function __getCleanData($html)
	{
		$subject = mb_convert_encoding( $html, 'utf-8','gbk' );
		$pattern = "|<div id=\"universal\">(.*?)</div><div id=\"navbar\"|ims";
		if(preg_match($pattern, $subject, $matches))
		{
			$subject = $matches[1];
			$pattern = '|<div style=\"clear:both\">(.*?)</div></div></div>|ims';
			if(preg_match_all($pattern, $subject, $matches))
			{
				$li_contents = $matches[1];
				$i = 0;
				$all = array();
				foreach ($li_contents as $li)
				{
					$pattern = '!(u=|q=)(.*?)&amp;.*?>(.*?)</a></div><div>(.*?)<div/><div><span .*?>(.*?)</span>!ims';
					if(preg_match($pattern,$li,$matches))
					{
						$url = $matches[2];
						$op['title'] = $matches[3];
						$op['url'] = $url;
						$op['site'] = str_replace('&nbsp;<img src="//www.gstatic.com/m/images/phone.gif" width="7" height="14" alt=""/>','',$matches[5]);
						$op['description'] = $matches[4];
						if($url)
						{
							$all[] = $op;
						}
					}
				}
				return $all;
			}
		}
		return array();

	}

	private static function __curlGet($url,$refer='http://www.google.com.hk/')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, $refer);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MQQBrowser/Mini3.1 (Nokia3050/MIDP2.0)');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language:zh-cn,zh"));
		curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		$contents = curl_exec($ch);
		curl_close($ch);
		return $contents;
	}

}