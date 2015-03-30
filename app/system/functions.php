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
function gbk2utf8($str)
{ 
	$charset = mb_detect_encoding($str,array('UTF-8','GBK','GB2312')); 
	$charset = strtolower($charset); 
	if('cp936' == $charset)
	{ 
		$charset='GBK'; 
	} 
	if("utf-8" != $charset)
	{ 
		$str = iconv($charset,"UTF-8//IGNORE",$str); 
	} 
	return $str; 
}
function strToUtf8 ($str)
{ 
	if (mb_detect_encoding($str, 'UTF-8', true) === false)
	{ 
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
function dump($var, $echo=true, $label=null, $strict=true)
{
	$label = ($label === null) ? '' : rtrim($label) . ' ';
	if (!$strict)
	{
		if (ini_get('html_errors'))
		{
			$output = print_r($var, true);
			$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
		}
		else
		{
			$output = $label . print_r($var, true);
		}
	}
	else
	{
		ob_start();
		var_dump($var);
		$output = ob_get_clean();
		if (!extension_loaded('xdebug'))
		{
			$output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
			$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
		}
	}
	if ($echo)
	{
		echo($output);
		return null;
	}
	else
	{
		return $output;
	}
}
//可以指定前缀
function createUuid($prefix ='',$split='')
{ 
	$str = md5(uniqid(mt_rand(), true));
	$uuid = substr($str, 0, 8).$split ;
	$uuid .= substr($str, 8, 4).$split;
	$uuid .= substr($str, 12, 4).$split ;
	$uuid .= substr($str, 16, 4).$split ;
	$uuid .= substr($str, 20, 12);
	return $prefix . $uuid;
}
/** 
 * 10进制转为62进制 
 *  
 * @param integer $n 10进制数值 
 * @return string 62进制 
 */ 
function dec62($n)
{  
	$base = 62;  
	$index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';  
	$ret = '';  
	for($t = floor(log10($n) / log10($base)); $t >= 0; $t --)
	{  
		$a = floor($n / pow($base, $t));  
		$ret .= substr($index, $a, 1);  
		$n -= $a * pow($base, $t);  
	}  
	return $ret;  
}
/** 
 * 62进制转为10进制 
 * 
 * @param integer $n 62进制 
 * @return string 10进制 
 */ 
function dec10($s)
{  
	$base = 62;  
	$index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';  
	$ret = 0;  
	$len = strlen($s) - 1;  
	for($t = 0; $t <= $len; $t ++)
	{  
		$ret += strpos($index, substr($s, $t, 1)) * pow($base, $len - $t);  
	}  
	return $ret;
}

/**
* 生成16位纯数字订单号
* 最大支持时间到 2056-12-31 23:59:59
*
* @access public
* @return string
*/
function getOrderSN()
{
	return (date('y') + date('m') + date('d')) . str_pad((time() - strtotime(date('Y-m-d'))), 5, 0, STR_PAD_LEFT) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
}

function array_delete($array,$item)
{
	if(is_array($item))
	{
		return array_diff($array, $item);
	}
	else
	{
		if(($key = array_search($item, $array)) !== false)
		{
			unset($array[$key]);
		}
		return $array;
	}

}
/**
 * 自然下标、制成map、新键数组
 */
function array_keymap($array,$index=null,$key=null)
{
	$ret=array();
	foreach ($array as &$item)
	{
		if($index)
		{
			$ret[$item[$index]]=$key?$item[$key]:$item;
		}
		else
		{
			$ret[]=$key?$item[$key]:$item;
		}
	}
	return $ret;
}

function mcrypt($string,$operation,$key='')
{ 
	$key=md5($key); 
	$key_length=strlen($key); 
	$string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
	$string_length=strlen($string); 
	$rndkey=$box=array(); 
	$result=''; 
	for($i=0;$i<=255;$i++)
	{ 
		$rndkey[$i]=ord($key[$i%$key_length]); 
		$box[$i]=$i; 
	} 
	for($j=$i=0;$i<256;$i++)
	{ 
		$j=($j+$box[$i]+$rndkey[$i])%256; 
		$tmp=$box[$i]; 
		$box[$i]=$box[$j]; 
		$box[$j]=$tmp; 
	} 
	for($a=$j=$i=0;$i<$string_length;$i++)
	{ 
		$a=($a+1)%256; 
		$j=($j+$box[$a])%256; 
		$tmp=$box[$a]; 
		$box[$a]=$box[$j]; 
		$box[$j]=$tmp; 
		$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256])); 
	} 
	if($operation=='D')
	{ 
		if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8))
		{ 
			return substr($result,8); 
		}
		else
		{ 
			return''; 
		} 
	}
	else
	{ 
		return str_replace('=','',base64_encode($result)); 
	} 
} 
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{   
	// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙   
	$ckey_length = 4;   
	// 密匙   
	$key = md5($key);   
	// 密匙a会参与加解密   
	$keya = md5(substr($key, 0, 16));   
	// 密匙b会用来做数据完整性验证   
	$keyb = md5(substr($key, 16, 16));   
	// 密匙c用于变化生成的密文   
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';   
	// 参与运算的密匙   
	$cryptkey = $keya.md5($keya.$keyc);   
	$key_length = strlen($cryptkey);   
	// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)， 
	//解密时会通过这个密匙验证数据完整性   
	// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确   
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :  sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;   
	$string_length = strlen($string);   
	$result = '';   
	$box = range(0, 255);   
	$rndkey = array();   
	// 产生密匙簿   
	for($i = 0; $i <= 255; $i++)
	{   
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);   
	}   
	// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度   
	for($j = $i = 0; $i < 256; $i++)
	{   
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;   
		$tmp = $box[$i];   
		$box[$i] = $box[$j];   
		$box[$j] = $tmp;   
	}   
	// 核心加解密部分   
	for($a = $j = $i = 0; $i < $string_length; $i++)
	{   
		$a = ($a + 1) % 256;   
		$j = ($j + $box[$a]) % 256;   
		$tmp = $box[$a];   
		$box[$a] = $box[$j];   
		$box[$j] = $tmp;   
		// 从密匙簿得出密匙进行异或，再转成字符   
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));   
	}   
	if($operation == 'DECODE')
	{  
		// 验证数据有效性，请看未加密明文的格式   
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
		{   
			return substr($result, 26);   
		}
		else
		{   
			return '';   
		}   
	}
	else
	{   
		// 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因   
		// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码   
		return $keyc.str_replace('=', '', base64_encode($result));   
	}
}
