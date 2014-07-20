<?php
 
/**
* ajax proxy
* ajax跨域解决办法
* @author  suconghou <suconghou@126.com>
* @version v1.1
* @blog http://blog.suconghou.cn
* @update 2014.2.26
* @ 修正了get时忽略了端口号的问题
* 实例化,传送url为真实的ajax请求地址即可
*/
class ajax 
{
  
    private $url; ///真实的ajax地址
    private $get;
    private $post;
    private $get_string;
    private $post_string;
    private $result;
 
    function __construct()
    {
        isset($_REQUEST['url'])||exit('no available url');
        $this->url=$_REQUEST['url'];
        foreach ($_GET as $key => $value)
        {
            if($key=='url')continue;
            $this->get[$key]=$value;
        }
        foreach ($_POST as $key => $value)
        {
            if($key=='url')continue;        
            $this->post[$key]=$value;
        }
        if(!empty($this->get))
        {
            $this->get_string=$this->implode_with_key($this->get);
        }
        if(!empty($this->post))
        {
             $this->post_string=$this->implode_with_key($this->post);
        }
        //$this->debug();
        $this->ajax();
       
    }
 
    function debug($debug=1)
    {
         
        var_dump($this->get);
        var_dump($this->post);
        var_dump($this->get_string);
      
    }
    function ajax()
    {
 
        if (empty($this->post))///没有post数据,但可能有get
        {
            $this->get();
        }
        else //可能有post,有get
        {
            $this->post();
        }
        echo $this->result;
 
    }
 
    ///三种版本的post,get,优先使用curl
    function post()
    {
        if (extension_loaded('curl'))
        {   
            $url=$this->query_string();
            $ch=curl_init();
            curl_setopt_array($ch, array(CURLOPT_URL=>$url,CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$this->post_string));
            $this->result=curl_exec($ch);
            curl_close($ch);
            
 
        }
        else if(function_exists('fsockopen'))
        {
            $parts = parse_url($this->url);
            $fp= fsockopen($parts['host'],isset($parts['port']) ? $parts['port'] : 80,$errno, $errstr,10);
            if (!$fp) die("$errstr($errno)");
            $url=$this->query_string(1);
            $out='POST '.$url."\r\nContent-type: application/x-www-form-urlencoded\r\n"."Content-length: " . strlen($this->post_string) . "\r\nConnection: close\r\n\r\n".$this->post_string;
            //exit($out);
            fwrite($fp,$out);
 
            while ($str = trim(fgets($fp, 4096)))
            {
                 $header .= $str;
            }
            while (!feof($fp))
            {
               $data.=fgets($fp, 4096);
            }
 
            $this->result=$data;
 
             
        }
        else
        {
            $context = array(
            'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded\r\n' .                   
                        'Content-length:' . strlen($this->post_string)+8,
            'content' =>$this->post_string)
            );
            $stream_context = stream_context_create($context);
            $data = file_get_contents($this->query_string(), false, $stream_context);
            $this->result=$data;
 
        }
 
    }
    function get()
    {
 
        if (extension_loaded('curl'))//已修正端口号问题
        {
            $ch=curl_init();           
            $url=$this->query_string();
            
            curl_setopt_array($ch, array(CURLOPT_URL=>$url,CURLOPT_RETURNTRANSFER=>1));
            $this->result=curl_exec($ch);
            curl_close($ch);
           
        }
        else if(function_exists('fsockopen'))
        {
            $parts = parse_url($this->url);
            $fp= fsockopen($parts['host'],isset($parts['port']) ? $parts['port'] : 80,$errno, $errstr,10);
            if (!$fp) die("$errstr($errno)"); 
            $url=$this->query_string(1);
            $out = 'GET ' . $url . "\r\nConnection: Close\r\n\r\n";
            fwrite($fp, $out);
            while ($str = trim(fgets($fp, 4096)))
            {
                 $header .= $str;
            }
            while (!feof($fp))
            {
               $data.=fgets($fp, 4096);
            }
            $this->result=$data;
  
        }
        else
        {
 
            $url=$this->query_string();        
            $this->result=file_get_contents($url);
 
            
        }
 
 
    }
 
    function implode_with_key($assoc, $inglue = '=', $outglue = '&')
    {
        $return = null;
        foreach ($assoc as $tk => $tv) $return .= $outglue.$tk.$inglue.$tv;
        return substr($return,1);
    }
 
    function query_string($type=0)
    {
        $parts = parse_url($this->url);
        $host_port=$parts['host'];
        if($parts['port'])
        {
            $host_port.=':'.$parts['port'];
        }
       
        if (empty($parts['query']))
        {
           $parts['query']=$this->get_string;
        }
        else
        {   
            if(!empty($this->get_string))
            {
                $parts['query'].='&'.$this->get_string;
            }
             
        }         
        if($type)
        {
            $url=$parts['path'].'?'.$parts['query']." HTTP/1.1\r\nHost: " . $host_port ; 
        }
        else
        {
            $url=$parts['scheme'].'://'.$host_port.$parts['path'].'?'.$parts['query'];   
        }
       //exit($url);
       return $url;
 
    }
 
 
}
 
// end class ajax