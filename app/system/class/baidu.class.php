<?php

/**
*  S('class/baidu')->init()->getLrcDataBr
*  S('class/baidu')->init()->getLrcData
*  S('class/baidu')->init()->getLrc
*  S('class/baidu')->init()->getSongInfo
*  S('class/baidu')->init()->getSongLink
*  S('class/baidu')->getSongById()
*/
class baidu
{

	private static $data;
	private static $callback;
	private static $playerid;

	function __construct($auto=false)
	{
		if($auto)
		{
			$this->AutoHandler();
		}
		
	}
	function init($name,$singer,$callback=null,$playerid=null)
	{
		self::$callback=$callback;
		self::$playerid=$playerid;
		$data=$this->initData($name,$singer,1);
		return $this;
	}
    
    function AutoHandler()
    {
    	$name=isset($_GET['name'])?$_GET['name']:null;
    	$singer=isset($_GET['singer'])?$_GET['singer']:null;
    	$callback=isset($_GET['callback'])?$_GET['callback']:null;
    	$playerid=isset($_GET['playerid'])?$_GET['playerid']:null;

    	$this->init($name,$singer,$callback,$playerid)->getLrcDataBr();
    	// echo $this->init($name,$singer,$callback,$playerid)->getSongInfo('file_link');
    }
    /**
     * with init param to get song info
     */
    public function getSongInfo($key=null)
    {
    	return self::fetchData(null,null,$key);
    }
    /**
     * with init param to get song play link
     */
    public function getSongLink($rate=128)
    {
    	$info=$this->getSongInfo();
    	$id=$info['song_list'][0]['song_id'];
    	return self::getSong($id,$rate);
    }
    /**
     * get by baidusong id with rate
     */
    public function getSongById($id,$rate=128)
    {
    	return self::getSong($id,$rate);
    }
    /**
     * return lrc data download link
     */
    public function getLrc()
    {
    	return self::fetchData(null,null,'lrclink');
    }
    /**
     * return origin lrc data 
     */
    public function getLrcData()
    {
    	$link=$this->getLrc();
    	$data=self::curl_get_contents($link);
    	return $data;
    }
    /**
     * echo lrc with <br>
     */
    public function getLrcDataBr()
    {
    	$data=$this->getLrcData();
        $data=str_replace(array("\r","\t","\n"),array("","    ","<br />"),$data);
        echo $data;
    }



    /**
     * step1
     */
    private static function initData($name,$singer,$pageSize=1)
    {
    	if($name&&$singer)
    	{
    		$json=self::curl_get_contents('http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.search.common&page_size={$pageSize}&format=json&query='.rawurlencode($name.' '.$singer));
    		if(!$json)
    		{
    			self::json(array('code'=>-2,'msg'=>'get lrc failed'));
    		}
    		$data=json_decode($json,true);
    		if(is_array($data['song_list']))
    		{
    			self::$data['songinfo']=$data;
    			return $data;
    		}
    		else
    		{
	    		self::json(array('code'=>-3,'msg'=>'no song list data'));
    		}
    	}
    	else
    	{
    		self::json(array('code'=>-1,'msg'=>'no name or singer input'));
    	}
    }
    /**
     * step2
     */
    private function getSong($songid,$rate=128,$key=null)
    {

        $data=self::curl_get_contents("http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.song.play&format=json&bit={$rate}&songid={$songid}");
        if($data)
        {
        	$data=json_decode($data,true);
        	self::$data['songdata']=$data;
        	if(isset($data['bitrate'][$key]))
        	{
        		return $data['bitrate'][$key];
        	}
        	else if(isset($data['songinfo'][$key]))
        	{
        		return $data['songinfo'][$key];
        	}
        	return $data;
        }
        else
        {
        	self::json(array('code'=>-4,'msg'=>'no song data'));
        }
    }
    /**
     * 获取数据
     * $key 
     */
    private function fetchData($name=null,$singer=null,$key=null)
    {
    	if(isset(self::$data['songinfo']))
    	{
    		$initData=self::$data['songinfo'];
    	}
    	else
    	{
    		$initData=self::initData($name,$singer);
    	}
		if($key)
		{
			if(isset($initData['song_list'][0][$key]))
			{
				$val=$initData['song_list'][0][$key];
				if($key=='lrclink')
				{
					$val='http://musicdata.baidu.com'.$val;
				}
				return $val;
			}
			else
			{
				$songid=$initData['song_list'][0]['song_id'];
				$rate=128;
				return self::getSong($songid,$rate,$key);
			}
		}
		else
		{
			return $initData;
		}
    }
  

     //封装 curl
   public static function curl_get_contents($url)
   {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 5); //超时五秒
	    $output = curl_exec($ch);
	    curl_close($ch);
	    if ($output===false)
	    {
	        return false;
	    }
	    return $output;
	}
	//递归处理数组
	public static function url_encode_array($val)
	{ 
        if (is_array($val))
        {
            foreach ($val as $k=>$v)
            {
                $val[$k]=url_encode_array($v);
            }
            return $val;
        }
        else
        {
            return urlencode($val);
        }
    }

    public static function json($data,$callback=null)
    {
		is_array($data)||parse_str($data,$data);
		$data=json_encode($data);
		if($callback&&(is_string($callback)||$callback=Request::get('jsoncallback')))
		{
		    exit($callback."(".$data.")");
		}
		exit($data);
    }
    
}