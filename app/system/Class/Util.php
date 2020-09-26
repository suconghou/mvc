<?php

/**
* 静态工具类,无需实例化,直接调用
*/
final class Util
{

	function __construct()
	{

	}

	public static function __callStatic($method,$args=null)
	{
		throw new Exception("Call Error Method {$method} In Class".get_called_class(),404);
	}

	public static function uuid($prefix='',$split='')
	{
		$str=md5(uniqid(mt_rand(),true));
		$uuid=substr($str,0,8).$split;
		$uuid.=substr($str,8,4).$split;
		$uuid.=substr($str,12,4).$split;
		$uuid.=substr($str,16,4).$split;
		$uuid.=substr($str,20,12);
		return $prefix.$uuid;
	}

	public static function arrayChange(Array $from,Array $to)
	{
		$delete=array_diff($from,$to);
		$add=array_diff($to,$from);
		return array('delete'=>$delete,'add'=>$add);
	}

	public static function arrayDelete($array,$item)
	{
		if(is_array($item))
		{
			return array_diff($array, $item);
		}
		else
		{
			if(($key=array_search($item, $array))!==false)
			{
				unset($array[$key]);
			}
			return $array;
		}
	}

	public static function isEmail($email)
	{
		return preg_match("/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i",$email);
	}

	public static function isPhone($tel)
	{
		return preg_match("/^1[3458][0-9]{9}$/",$tel);
	}

	public static function timeBefore($time)
	{
		$t=max(time()-$time,1);
		$f=array('31536000'=>'年','2592000'=>'个月','604800'=>'星期','86400'=>'天','3600'=>'小时','60'=>'分钟','1'=>'秒');
		foreach ($f as $k=>$v)
		{
			if (0!=$c=floor($t/(int)$k))
			{
				return $c.$v.'前';
			}
		}
	}

	public static function getOrderSN()
	{
		return (date('y')+date('m')+date('d')).str_pad((time()-strtotime(date('Y-m-d'))),5,0,STR_PAD_LEFT).substr(microtime(),2,6).sprintf('%03d',rand(0,999));
	}

	public static function convertStringEncoding(&$str,$set='UTF-8')
	{
		$charset=mb_detect_encoding($str);
		if($charset!=$set)
		{
			$str=iconv($charset,"{$set}//IGNORE",$str);
		}
		return $str;
	}

	public static function hex2rgb($c)
	{
		$r=hexdec(substr($c,0,2));
		$g=hexdec(substr($c,2,2));
		$b=hexdec(substr($c,-2));
		return array($r,$g,$b);
	}

	public static function rgb2hex($r,$g,$b)
	{
		return dechex($r).dechex($g).dechex($b);
	}

	public static function opt($key,$default=null)
	{
		$key="--{$key}=";
		foreach ($GLOBALS['argv'] as $item)
		{
			if(sizeof($arr=explode($key,$item))==2)
			{
				return end($arr);
			}
		}
		return $default;
	}

	public static function timer(closure $function,$exit=false,closure $callback=null)
	{
		while(true)
		{
			$data=$function();
			$break=($exit instanceof closure)?$exit($data):$exit;
			if($break)
			{
				return $callback?$callback($data):$data;
			}
		}
	}

	public static function encrypt($input,$key=null)
	{
		return str_replace(['+','/','='],['-','_',''],base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,md5($key),$input,MCRYPT_MODE_ECB,mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH,MCRYPT_MODE_ECB),MCRYPT_DEV_URANDOM))));
	}

	public static function decrypt($input,$key=null)
	{
		$input=str_replace(['-','_'],['+','/'],$input);
		if($mod=strlen($input)%4)
		{
			$input.=substr('====', $mod);
		}
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128,md5($key),base64_decode($input),MCRYPT_MODE_ECB,mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH,MCRYPT_MODE_ECB),MCRYPT_DEV_URANDOM)));
	}

	public static function async(closure $task=null,closure $callback=null)
	{
		function_exists('fastcgi_finish_request')&&fastcgi_finish_request();
		$data=$task();
		return $callback?$callback($data):$data;
	}

	public static function setItem($key,$value)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			$data[$key]=$value;
		}
		else
		{
			$data=[$key=>$value];
		}
		return file_put_contents($file,serialize($data));
	}
	public static function getItem($key,$default=null)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file))))
		{
			return isset($data[$key])?$data[$key]:$default;
		}
		return $default;
	}
	public static function clearItem($key=null,&$file=null)
	{
		$file=sprintf('%s%s%u.db',sys_get_temp_dir(),DIRECTORY_SEPARATOR,crc32(ROOT));
		if(is_null($key))
		{
			return is_file($file)&&unlink($file);
		}
		if(is_file($file)&&is_array($data=unserialize(file_get_contents($file)))&&isset($data[$key]))
		{
			unset($data[$key]);
			return file_put_contents($file,serialize($data));
		}
		return true;
	}

	public static function byteFormat($size,$dec=2)
	{
		$size=max($size,0);
		$unit=['B','KB','MB','GB','TB','PB','EB','ZB','YB'];
		return $size>=1024?round($size/pow(1024,($i=floor(log($size,1024)))),$dec).' '.$unit[$i]:$size.' B';
    }
    
    public static function info($key=null,$default=null)
    {
        $data['ip']=self::ip();
        $data['ajax']=self::isAjax();
        $data['ua']=self::ua();
        $data['refer']=self::refer();
        $data['protocol'] = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? "https" : "http";
        if($key) return isset($data[$key])?$data[$key]:$default;
        return $data;
    }

	public static function serverInfo($key=null,$default=null)
	{
		$info=['php_os'=>PHP_OS,'php_sapi'=>PHP_SAPI,'php_vision'=>PHP_VERSION,'post_max_size'=>ini_get('post_max_size'),'max_execution_time'=>ini_get('max_execution_time'),'server_ip'=>gethostbyname($_SERVER['SERVER_NAME']),'upload_max_filesize'=>ini_get('file_uploads')?ini_get('upload_max_filesize'):0];
		return $key?(isset($info[$key])?$info[$key]:$default):$info;
	}
	public static function isCli()
	{
		return defined('STDIN')&&defined('STDOUT');
	}
	public static function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&$_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}
	public static function isPjax()
	{
		return isset($_SERVER['HTTP_X_PJAX'])&&$_SERVER['HTTP_X_PJAX'];
	}
	public static function isSpider()
	{
		$agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
		return $agent?preg_match('/(spider|bot|slurp|crawler)/i',$agent):true;
	}
	public static function isMobile()
	{
		$agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
		$regexMatch="/(nokia|iphone|android|motorola|ktouch|samsung|symbian|blackberry|CoolPad|huawei|hosin|htc|smartphone)/i";
		return $agent?preg_match($regexMatch,$agent):true;
	}

    //发送邮件,用来替代原生mail,多个接受者用分号隔开
    public static function sendMail($mailTo,$mailSubject,$mailMessage=null)
    {
        try
        {
            $mail=app::get('mail',[]);
            if(!$fp=fsockopen($mail['server'],empty($mail['port'])?25:$mail['port'],$errno,$errstr,3))
            {
                throw new Exception($errstr,$errno);
            }
            $lastmessage=fgets($fp,128);
            if(substr($lastmessage,0,3)!='220')
            {
                throw new Exception("CONNECT ERROR - {$lastmessage}",2);
            }
            fputs($fp,"EHLO mail\r\n");
            $lastmessage=fgets($fp,128);
            if(!in_array(substr($lastmessage,0,3),[220,250]))
            {
                throw new Exception("HELO/EHLO - {$lastmessage}",3);
            }
            while(true)
            {
                if(substr($lastmessage,3,1)!='-'||empty($lastmessage))
                {
                    break;
                }
                $lastmessage=fgets($fp,128);
            }
            if(!empty($mail['auth'])&&$mail['auth'])
            {
                fputs($fp,"AUTH LOGIN\r\n");
                $lastmessage=fgets($fp,128);
                if(substr($lastmessage,0,3)!=334)
                {
                    throw new Exception("AUTH LOGIN - {$lastmessage}",4);
                }
                fputs($fp,base64_encode($mail['user'])."\r\n");
                $lastmessage=fgets($fp,128);
                if(substr($lastmessage,0,3)!=334)
                {
                    throw new Exception("AUTH LOGIN - {$lastmessage}",5);
                }
                fputs($fp,base64_encode($mail['pass'])."\r\n");
                $lastmessage=fgets($fp,128);
                if(substr($lastmessage,0,3)!=235)
                {
                    throw new Exception("AUTH LOGIN - {$lastmessage}",6);
                }
            }
            fputs($fp,"MAIL FROM: <{$mail['user']}>\r\n");
            $lastmessage=fgets($fp,128);
            if(substr($lastmessage,0,3)!=250)
            {
                fputs($fp,"MAIL FROM: <{$mail['user']}>\r\n");
                $lastmessage=fgets($fp,128);
                if(substr($lastmessage,0,3)!=250)
                {
                    throw new Exception("MAIL FROM - {$lastmessage}",7);
                }
            }
            foreach(explode(';',$mailTo) as $touser)
            {
                if($touser=trim($touser))
                {
                    fputs($fp,"RCPT TO: <{$touser}>\r\n");
                    $lastmessage=fgets($fp,128);
                    if(substr($lastmessage,0,3)!=250)
                    {
                        throw new Exception("RCPT TO - {$lastmessage}",8);
                    }
                }
            }
            fputs($fp,"DATA\r\n");
            $lastmessage=fgets($fp,128);
            if(substr($lastmessage,0,3)!=354)
            {
                throw new Exception("DATA - {$lastmessage}",9);
            }
            $headers="MIME-Version:1.0\r\nContent-type:text/html\r\nContent-Transfer-Encoding: base64\r\nFrom: {$mail['name']}<{$mail['user']}>\r\nDate: ".date("r")."\r\nMessage-ID: <".uniqid(rand(1,999)).">\r\n";
            $mailSubject='=?utf-8?B?'.base64_encode($mailSubject).'?=';
            $mailMessage=chunk_split(base64_encode(preg_replace("/(^|(\r\n))(\.)/","\1.\3",$mailMessage)));
            fputs($fp,$headers);
            fputs($fp,"To: {$mailTo}\r\n");
            fputs($fp,"Subject: {$mailSubject}\r\n");
            fputs($fp,"\r\n\r\n");
            fputs($fp,"{$mailMessage}\r\n.\r\n");
            $lastmessage=fgets($fp,128);
            if(substr($lastmessage,0,3)!=250)
            {
                throw new Exception("END - {$lastmessage}",10);
            }
            fputs($fp,"QUIT\r\n");
            return true;
        }
        catch(Exception $e)
        {
            app::log($e->getMessage(),'ERROR');
            throw $e;
        }
    }


    public static function csrf_token($check=false,$name='_token',closure $callback=null)
    {
        isset($_SESSION)||session_start();
        $token=isset($_SESSION['csrf_token'])?$_SESSION['csrf_token']:null;
        if($check)
        {
            if(!(isset($_REQUEST[$name])&&$_REQUEST[$name]===$token))
            {
                return $callback?$callback($token):app::error(403,'csrf token not match');
            }
            return true;
        }
        else if(!$token)
        {
            $token=md5(uniqid());
            $_SESSION['csrf_token']=$token;
        }
        return $token;
    }
    
    

}



