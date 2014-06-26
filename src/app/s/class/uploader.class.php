<?php

/**
* 文件上传
* 文件发送到SAE
* 七牛
* 酷盘
* 本地
* @author suconghou
* @blog http://blog.suconghou.cn
*/
class uploader
{
	private static $saeServer='http://2.suconghou.sinaapp.com/upload'; //SAE文件存储接口
	private static $uploadDir='static/upload/';  //本地存储目录，目录必须存在,前面不加/
	private static $kupanKeyServer='http://api.suconghou.cn/kupan/key'; //存储到酷盘时用到的密钥
	private static $allowType=array('jpg','gif','png','jpeg','mp4','swf','flv','rar','zip'); //允许上传的文件类型
	
	function __construct()
	{
		# code...
	}
	function init()
	{

	}
	/**
	 * 监视本地文件上传, $name为文件表单名，可发送单独POST['name']定义存储文件名
	 */
	function upload($name,$storName=null)
	{
		if(isset($_FILES[$name]))
		{
			if($_FILES[$name]['error']==0)
			{	
				$contents=file_get_contents($_FILES[$name]['tmp_name']);
				unlink($_FILES[$name]['tmp_name']);
				
				$filename=self::detectName($name,$storName);

				$destination=self::$uploadDir.date('Ymd');
				if(!is_readable($destination))
			    {
			        is_file($destination) or mkdir($destination,0700);
			    }
			    $destination=$destination.'/'.$filename;
				file_put_contents($destination,$contents);
				return baseUrl($destination);
			}
		}
		else
		{
			return false;
		}
	}
	/**
	 * 监视用户上传并转文件到SAE
	 */
	function uploadSae($name)
	{
		if(isset($_FILES[$name]))
		{
			if($_FILES[$name]['error']==0)
			{
				return self::sendToSae($_FILES[$name]['tmp_name']);
			}
		}
		return false;
	}
	function uploadKupan($name,$storName=null)
	{
		if(isset($_FILES[$name]))
		{
			if($_FILES[$name]['error']==0)
			{
				$filename=self::detectName($name,$storName);
				return self::sendToKupan($_FILES[$name]['tmp_name'],$filename);
			}
		}
		return false;
	}
	function uploadQiniu($name,$storName=null)
	{

	}
	function sendToSae($filepath)
	{
		$data = array("file"  => "@".realpath($filepath));
		$res=json_decode(self::postData(self::$saeServer,$data));
		if($res->code==0)
		{
			return 'http://2.suconghou.sinaapp.com/'.$res->msg;
		}
		else
		{
			return false;
		}
	}
	function sendToQiniu($path)
	{

	}
	function sendToKupan($filepath,$name)
	{
		$file=file_get_contents($filepath);
	    $path='/files/uploader/'.$name;
	    $token=file_get_contents(self::$kupanKeyServer);
	    $url="https://api-upload.kanbox.com/0/upload{$path}?bearer_token=".$token;
	    $res=self::postData($url,$file);
	    $downUrl='http://api.suconghou.cn'.$path;
	    return ($res==1)?$downUrl:false;
	}
	private function detectName($f,$storName=null)
	{
		if($storName)
		{
			$name=$storName;
		}
		else if(Request::post('name'))
		{
			$name=Request::post('name'); 
		}
		else if($_FILES[$f]['name']!='blob')
		{
			$name=$_FILES[$f]['name'];
		}
		else 
		{
			$contents=file_get_contents($_FILES[$f]['tmp_name']);
			$type=self::getType($contents,'jpg');
			$name=uniqid().$type;
		}
		$arr=explode('.',$name);
		if(in_array(end($arr),self::$allowType))
		{
			return $name;
		}
		else
		{
			return false;
		}
	}
	private function postData($url,$post_data)
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
     * 由文件内容获得扩展名
     */
    private static function getType($contents,$userType)
    {
        $bin=substr($contents, 0, 2);
        $strInfo =@unpack("c2chars", $bin);
        $typeCode=intval($strInfo['chars1'].$strInfo['chars2']);
        $types=array(
                    '8297'=>'rar',
                    '8075'=>'zip',
                    '55122'=>'7z',
                    '255216'=>'jpg',
                    '13780'=>'png',
                    '7173'=>'gif',
                    '6677'=>'bmp',
                    '7784'=>'midi',
                    '7790'=>'exe',
                    '7368'=>'mp3',
                    '7076'=>'flv',
                    '8381'=>'db',
                    '4838'=>'wmv',
                    '3780'=>'pdf',
                    '2669'=>'mkv' 
                    );
        //Fix
        if($strInfo['chars1']=='-1' && $strInfo['chars2']=='-40')
        {
            return 'jpg';
        }
        if($strInfo['chars1']=='-119' && $strInfo['chars2']=='80')
        {
            return 'png';
        }
        return isset($types[$typeCode])?$types[$typeCode]:$userType;
    }

}