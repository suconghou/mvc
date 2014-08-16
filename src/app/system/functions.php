<?php

/**
 *  扩展函数配置区
 *  functions.php的函数配置区域
 *  配置函数的常量
 * 
 */


// function sendMailByCloud 配置
define('MAIL_CLOUD_USER','postmaster@sumail.sendcloud.org');
define('MAIL_CLOUD_PASS','123456');
define('MAIL_CLOUD_FROM','admin@suconghou.cn');
define('MAIL_CLOUD_NAME','苏苏');

/*
 *应用配置区域,对整个应用的配置
 *针对自己的应用配置参数
 *设置常量等
 *
 */

define('LIST_PER_PAGE',25);///分页每页个数
//用户状态state 设置,
//登录时,获取state>=1的,并判断state是否为1,为1则提示冻结,不予登陆
//故2,3会直接登录,1会提示不能登陆,0会提示不存在用户,但是此用户名和邮箱不能再次使用,除非真正删除
define('USER_STATE_DELETE',0);//已删除
define('USER_STATE_FREEZE',1);//已冻结,不能登录,提示
define('USER_STATE_COMMON',2);//普通,未验证邮箱,可以登录
define('USER_STATE_VAILDE',3);//已验证邮箱




//采用CURL方式POST数据,数据为拼接好的或者数组
function postData($url,$post_string)
{
	$ch=curl_init();
	curl_setopt_array($ch, array(CURLOPT_URL=>$url,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>is_array($post_string)?http_build_query($post_string):$post_string));
	$result=curl_exec($ch);
	curl_close($ch);
	return $result;
}
//采用file_get_content 发送POST数据	
function postDataByStream($url,$post_string)
{
	$data=is_array($post_string)?http_build_query($post_string):$post_string;
	$options = array('http' => array('method'  => 'POST','header'=>'Content-type: application/x-www-form-urlencoded','content' => $data));
    $context = stream_context_create($options);
    $result  = file_get_contents($url, false, $context);
    return $result;
}
/**
* CURL模拟上传文件和发送表单
* 使用@和realpath选择要发送的文件
* $url = "http://127.0.0.1/upload.php";
* $data = array("username" => $username,"password"  => $password,"file"  => "@".realpath("1.jpg") );
*/
function sendFile($url,$post_data)
{
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_POST, 1 );
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt($curl, CURLOPT_POSTFIELDS,$post_data);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	$error = curl_error($curl);
	return $error ? $error : $result;
}

/**
 * CURl发送get请求
 */
function curlGet($url)
{
    $ch=curl_init($url);
    curl_setopt_array($ch, array(CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>3));
    $result=curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 返回解析好的http信息头
 */
function httpInfo($url)
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


///探测http状态码
function httpCode($url)
{
	$res=httpInfo($url);
	return $res['http_code'];
}

//探测网址是否存在
function urlExists($url)   
{   
	$res=httpInfo($url);
	return $res['header_size']?true:false;
}
//获得要下载的文件大小
function getSize($url)
{
	$res=httpInfo($url);
	return $res['download_content_length'];
}

// 采用SendCloud 发送邮件，需配置好账户密码等
function sendMailByCloud($to,$subject,$html) 
{
    ///首先设置账户密码,发送地址,api地址
    $api_user=MAIL_CLOUD_USER; 
    $api_key=MAIL_CLOUD_PASS;
    $from=MAIL_CLOUD_FROM;  ///域的邮件地址
    $fromname=MAIL_CLOUD_NAME;
    $url = 'http://sendcloud.sohu.com/webapi/mail.send.json';
    $param = array( 'api_user' => $api_user,
                    'api_key' => $api_key,
                    'from' => $from,
                    'fromname' => $fromname,
                    'to' => $to,
                    'subject' => $subject,
                    'html' => $html
                    );
    $res=postDataByStream($url,$param);
   return $res; 
 }
//验证
function isEmail($email)
{
	return (preg_match("/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i",$email ));
}
function isTel($tel)
{
	return (preg_match("/^1[3458][0-9]{9}$/",$tel));
}

function utf8Substr($str, $from, $len)
{   
   return mb_substr($str,$from,$len,'utf-8');
}
function gbk2utf8($str){ 
    $charset = mb_detect_encoding($str,array('UTF-8','GBK','GB2312')); 
    $charset = strtolower($charset); 
    if('cp936' == $charset){ 
        $charset='GBK'; 
    } 
    if("utf-8" != $charset){ 
        $str = iconv($charset,"UTF-8//IGNORE",$str); 
    } 
    return $str; 
}
function str_to_utf8 ($str) { 
    
    if (mb_detect_encoding($str, 'UTF-8', true) === false) { 
    $str = utf8_encode($str); 
    }

    return $str;
}

/**
 * 颜色值转换
 */
function hex2rgb($c)
{
    $r=hexdec(substr($c,0,2));
    $g=hexdec(substr($c,2,2));
    $b=hexdec(substr($c,-2));
    return array($r,$g,$b);
}
function rgb2hex($r,$g,$b)
{
    return dechex($r).dechex($g).dechex($b);
}