<?php

/**
*  S('class/baidu')->init()->getSongInfo
*  S('class/baidu')->init()->getSongData
*  S('class/baidu')->init()->getSongInfo
*  S('class/baidu')->init()->getSongInfo
*  S('class/baidu')->init()->getSongInfo
*/
class baidu
{

	private static $data;
	private static $callback;
	private static $playerid;

	function __construct()
	{
		
	}
	function init($name,$singer,$callback=null,$playerid=null)
	{
		self::$callback=$callback;
		self::$playerid=$playerid;
		$data=$this->initData($name,$singer,1);
		return $this;
	}
    
    function handler()
    {
    	$name=isset($_GET['name'])?$_GET['name']:null;
    	$singer=isset($_GET['singer'])?$_GET['singer']:null;
    	$callback=isset($_GET['callback'])?$_GET['callback']:null;
    	$playerid=isset($_GET['playerid'])?$_GET['playerid']:null;
    	$this->init($name,$singer,$callback,$playerid);
    	$this->getLrcDataBr();
    }

    public function getSongInfo($key=null)
    {
    	return self::fetchData(null,null,$key);
    }
    public function getSongLink($rate=128)
    {
    	$info=$this->getSongInfo();
    	$id=$info['song_list'][0]['song_id'];
    	return self::getSong($id,$rate);
    }
    public function getSongById($id,$rate=128)
    {
    	return self::getSong($id,$rate);
    }
    public function getLrc()
    {
    	return self::fetchData(null,null,'lrclink');
    }
    public function getLrcData()
    {
    	$link=$this->getLrc();
    	$data=self::curl_get_contents($link);
    	return $data;
    }
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

    public static function output($result_array)
    { //输出函数
        if ($_GET['playerid'] && $result_array['status']=='success')
        {
            $playerid=htmlspecialchars($_GET['playerid']);
        }
        echo ($_GET['callback']?htmlspecialchars($_GET['callback']).'(':'').urldecode(json_encode(url_encode_array($result_array))).($_GET['callback']?(($playerid?(',\''.(get_magic_quotes_gpc()?$playerid:addslashes($playerid)).'\''):'').');'):'');
        exit();
    }
    public static function json($data,$callback=null)
    {
    	is_array($data)||parse_str($data,$data);
    
		exit(json_encode($data));
    }
    
}