<?

/**
* php curl 并发 多线程
* @author suconghou <suconghou@126.com>
* @version v1.3 <2014.7.19>
* @link http://blog.suconghou.cn
* $a=$curl->quick_exec($url);
* add 第一个参数网址数组，第二个header，第三个nobody，第四个超时时间
* 默认返回正文，超时10秒
* $a=$curl->add($url_arr,1,0)->add($url_arr2)->exec();
* $a=$curl->add($url)->exec();
* $curl->post($url,$post_data);
* $b=$curl->add($url)->fetch('img');img/src/url/href/或者自定义正则
* 
* 
* http请求,GET,POST,PUT,DELETE
* 发送文件,多线程并发抓取
* 
* 
*/
class Curl 
{
	private $mh;
	private $ch=array();
	
	private static $headers=array(
								'Referer'=>'http://www.baidu.com',
								'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
								'Accept'=>'*/*'
							);

	
	public static function get($url,$timeout=3)
	{
		$ch=self::initCurl($url,$timeout);
		if($ch)
		{
			$result=curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		return self::http_get_contents($url,$timeout);
	}
	
	public static function post($url,$data,$timeout=10)
	{
		$ch=self::initCurl($url,$timeout);
		if($ch)
		{
			curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>is_array($data)?http_build_query($data):$data));
			$result=curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		return self::http_post_contents($url,$data,$timeout);
	}
	
	public static function put($url,$data=array(),$timeout=10)
	{
		$ch=self::initCurl($url,$timeout);
		if($ch)
		{
			curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>is_array($data)?http_build_query($data):$data,CURLOPT_CUSTOMREQUEST=>'put'));
			$result=curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		return false;
		
	}
	
	public static function delete($url,$data,$timeout=10)
	{
		$ch=self::initCurl($url,$timeout);
		if($ch)
		{
			curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>is_array($data)?http_build_query($data):$data,CURLOPT_CUSTOMREQUEST=>'delete'));
			$result=curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		return false;
	}
	
	public static function head($url,$timeout=3)
	{
		$ch=self::initCurl($url,$timeout);
		if($ch)
		{
			curl_setopt_array($ch,array(CURLOPT_NOBODY=>true,CURLOPT_CUSTOMREQUEST=>'head'));
			$result=curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		return false;
	}
	/**
	 * CURL 发送文件
	 * $data = array("username" => $username,"password"  => $password,"file"  => "@".realpath("1.jpg") );
	 */
	public static function sendFile($url,$data,$timeout=10)
	{
		return self::post($url,$data,$timeout);
	}
	
	
	public static function http_get_contents($url,$timeout=3)
	{
		$header='';
		foreach(self::$headers as $key=>$val)
		{
			$header.=$key.':'.$val.PHP_EOL;
		}
		$options = array('http' => array('method'=>'GET','timeout'=>$timeout,'header'=>$header));
		$context = stream_context_create($options);
		$result  = file_get_contents($url, false, $context);
		return $result;
	}
	
	public static function http_post_contents($url,$data,$timeout=10)
	{
		$header='';
		foreach(self::$headers as $key=>$val)
		{
			$header.=$key.':'.$val.PHP_EOL;
		}
		$data=is_array($data)?http_build_query($data):$data;
		$options = array('http' => array('method'=>'POST','timeout'=>$timeout,'header'=>$header,'content' => $data));
		$context = stream_context_create($options);
		$result  = file_get_contents($url, false, $context);
		return $result;
	}
	
	public static function setHeader($header)
	{
		self::$headers=array_merge(self::$headers,$header);
		return self::$headers;
	}
	
	private static function initCurl($url,$timeout=3)
	{
		if(extension_loaded('curl'))
		{
			$ch=curl_init($url);
			curl_setopt_array($ch, array(CURLOPT_HTTPHEADER=>self::$headers,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>$timeout,CURLOPT_CONNECTTIMEOUT=>$timeout));
			return $ch;
		}
		return false;
	}
	
	
	///////////////////////////// 多线程请求 /////////////////////////
	
	//增加一个/组请求
	//url为array
	function add($url,$header=0,$nobody=0,$timeout=10)
	{
		$this->mh=$this->mh?$this->mh:curl_multi_init();
		$url=is_array($url)?$url:array($url);
		foreach($url as $k=>$u)
		{
			$ch=curl_init($u);
			curl_setopt_array($ch,array(CURLOPT_NOBODY=>$nobody,CURLOPT_HEADER=>$header,CURLOPT_HTTPHEADER=>self::$headers,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>$timeout,CURLOPT_CONNECTTIMEOUT=>$timeout));
			curl_multi_add_handle($this->mh,$ch);
			$this->ch[$k]=$ch;
		}
		return $this;
	}

	///执行所有请求
	function exec($url=null)
	{
		$this->mh=$this->mh?$this->mh:curl_multi_init();
		if($url)
		{
			$this->add($url);
		}
		$running=null;
		do
		{
			curl_multi_exec($this->mh, $running);
			curl_multi_select($this->mh);
		}
		while ($running>0);
		foreach ($this->ch as $k=>$v)
		{
		   $this->ch[$k]=curl_multi_getcontent($v);
		   curl_multi_remove_handle($this->mh,$v);
		   curl_close($v);
		}
		curl_multi_close($this->mh);
		$result=implode('',$this->ch);
		$this->mh=null;
		$this->ch=array();
		return $result;
	}

	/**
	 * 内部规则 
	 * img  提取 http://xxxx.jpg  图片全地址
	 * src  提取 <img src=''  所有能够自己显示的图片
	 * href 提取 <a href=''  所有连接
	 * url  提取 http://    符合http:// 的地址
	 */
	function fetch($type,$url=null,$index=null)
	{
		$res=$this->exec($url);
		$regex['img']='/https?:\/\/[a-z0-9_-]+(\.[a-z0-9_-]+){1,5}(\/[a-z0-9_-]+){1,9}\.(jpg|jpeg|png|gif|bmp)/i';
		$regex['src']='/<img.+?src=(\"|\')(.{5,}?)(\"|\').+?\/?>/i';
		$regex['url']='/https?:\/\/[a-z0-9_-]+(\.[a-z0-9_-]+){1,5}(\/[a-z0-9_-]+){0,9}(\.\w+)?/i';
		$regex['href']='/<a.+?href=(\"|\')(.+?)(\"|\').+?>.+?<\/a>/i';
		switch ($type)
		{
			case 'img':
				$index=0;
				return $this->filter($res,$regex['img'],$index);
			case 'src':
				$index=2;
				return $this->filter($res,$regex['src'],$index);
			case 'url':
				$index=0;
				return $this->filter($res,$regex['url'],$index);
			case 'href':
				$index=2;
				return $this->filter($res,$regex['href'],$index);
			case 'all':
				$ret['img']=$this->filter($res,$regex['img'],0);
				$ret['src']=$this->filter($res,$regex['src'],2);
				$ret['url']=$this->filter($res,$regex['url'],0);
				$ret['href']=$this->filter($res,$regex['href'],2);
				return $ret;
			default:
				if(!preg_match('/^\/.+\/$/',$type)) {return $res;}//不是正则规则
				return $this->filter($res,$type,$index);
		}
	}
	private function filter($html,$regex,$index=null)
	{
		if(preg_match_all($regex,$html,$matches))
		{
			if($index and isset($matches[$index]))
			{
				return array_unique($matches[$index]);
			}
			return $matches;
		}
		return array();

	}
}