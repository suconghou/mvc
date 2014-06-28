<?php

//扩展函数库,这里可以定义你自己的常用应用函数
//部分函数使用了常量,配置见app_config配置区域

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
//发送飞信
function sendSms($to,$msg)//成功返回true
{
    $user=SMS_USER;
    $pass=SMS_PASS;
    $url="http://quanapi.sinaapp.com/fetion.php?u={$user}&p={$pass}&to={$to}&m={$msg}";
    $ret=file_get_contents($url);
    $res=json_decode($ret);
    if ($res->result==0)///结果为零时成功
    {
       return true;
    }
    else
    {
        return false;
    }
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

function getImg($id,$page=1)
{
    $pattern='/http:\/\/image.hnol.net\/[a-z]\/201(2|3|4)-\d{2}\/\d{1,2}\/\d{1,2}\/\d{17,19}-\d{7}.jpg/';
    $url="http://bbs.voc.com.cn/viewthread.php?action=printable&tid={$id}&page={$page}";
    $subject=file_get_contents($url);
    if(preg_match_all($pattern, $subject, $matches))
    {
        return array_unique($matches[0]);
    }
    else
    {
        return false;
    }

}
function utf8Substr($str, $from, $len)
{   
   return mb_substr($str,$from,$len,'utf-8');
}
