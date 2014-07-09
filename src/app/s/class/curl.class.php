<?

/**
* php curl 并发 多线程
* @author suconghou <suconghou@126.com>
* @version v1.0
* @blog http://blog.suconghou.cn
* @date 2013.12.25
* example 
* $a=$curl->quick_exec($url);
* add 第一个参数网址数组，第二个header，第三个nobody，第四个超时时间
* 默认返回正文，超时10秒
* $a=$curl->add($url_arr,1,0)->add($url_arr2)->exec();
* $a=$curl->add($url)->exec();
* $b=$curl->add($url)->fetch('img');
*/
class curl 
{
    private $mh;
    private $ch;

    function __construct()
    {
         $this->mh=curl_multi_init();//创建批处理cURL句柄
    }
    //增加一个/组请求
    //url为array
    function add($url,$header=0,$no_body=0,$timeout=10)
    {
        if(is_array($url))
        {
            $url_array=&$url;
        }
        else
        {
            $url_array=array($url);
        }
        foreach ($url_array as  $value)
        {
            $this->ch[$value]=curl_init();           
            curl_setopt_array($this->ch[$value], array(CURLOPT_URL=>$value,CURLOPT_HEADER=>$header,CURLOPT_TIMEOUT=>$timeout,CURLOPT_NOBODY=>$no_body,CURLOPT_RETURNTRANSFER=>1));
            curl_multi_add_handle($this->mh,$this->ch[$value]);
        }
        return $this;
    }

    ///执行所有请求
    function exec($one=null)
    {
        if(!($this->mh&&$this->ch))return false;
        $running=null;
        do
        {
            curl_multi_exec($this->mh, $running);
            curl_multi_select($this->mh);
        }
        while ($running > 0);
        foreach ($this->ch as $key => $value)
        {
           $result[$key]=curl_multi_getcontent($value);
           curl_multi_remove_handle($this->mh,$value);
           curl_close($value);
        }
        curl_multi_close($this->mh);
        if($one)
        {
            $out=null;
            foreach ($result as $key => $value)
            {
                $out.=$value;
            }
            return $out;
        }
        else
        {
            return $result;             
        }
    }

    //快速发起忽略返回值的并行请求
    function quick_exec($url)
    {

        if(is_array($url))
        {
            $url_array=&$url;
        }
        else
        {
            $url_array=array($url);
        }
        foreach ($url_array as $key => $value)
        {
            $this->ch[$key]=curl_init();           
            curl_setopt_array($this->ch[$key], array(CURLOPT_URL=>$value,CURLOPT_HEADER=>0,CURLOPT_TIMEOUT=>1,CURLOPT_NOBODY=>1));
            curl_multi_add_handle($this->mh,$this->ch[$key]);
        }
        $running=null;
        do
        {
            curl_multi_exec($this->mh,$running);
        }
        while($running > 0);
        return true;

    }

    function fetch($type=null,$regex=null)
    {
        $res=$this->exec(true);
        if($type)
        {
            switch ($type)
            {
                case 'img':
                    $regex='//';
                    $index=null;
                    break;
                case 'url':
                    
                    $regex='';
                    $index=1;
                    break;
                case 'pic':
                    
                    $regex='';
                    $index=1;
                    break;
                default:
                    return $res;
                    break;
            }
            return $this->filter($res,$regex,$index);
        }   
        else if($regex)
        {
           return $this->filter($res,$regex);
        } 
        else
        {
            return strip_tags($res);
        }
    
    }
    private function filter($html,$regex,$index=null)
    {
        if(preg_match_all($regex,$html,$matches))
        {
            if(is_null($index))
            {
                return $matches;
            }
            return $matches[$index];
        }
        return null;

    }
}