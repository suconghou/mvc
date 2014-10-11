<?php

/**
* 文件上传,文件发送到SAE,七牛,酷盘,贴图库,本地
* upload 参数 表单名,存储名
* S('class/uploader')->upload()
* S('class/uploader')->uploadSae()
* S('class/uploader')->uploadQiniu()
* S('class/uploader')->uploadKupan()
* S('class/uploader')->uploadTietu()
* 
* S('class/uploader')->sendToSae()
* S('class/uploader')->sendToKupan()
* S('class/uploader')->sendToQiniu()
* S('class/uploader')->sendToTietu()
*
* 
* @author suconghou
* @blog http://blog.suconghou.cn
* 
*/
class uploader
{
	private static $saeServer='http://suconghou.sinaapp.com/'; //SAE文件存储接口基地址,上传接口upload ,详情见sae_storage.class.php
	private static $kupanKeyServer='http://api.suconghou.cn/kupan/key/susu'; //存储到酷盘时用到的密钥,见kupan.classs.php 私人存储,勿扰
	private static $uploadDir='static/upload/';  //本地存储目录,目录必须存在,前面不加/
	private static $allowType=array('jpg','gif','png','jpeg','mp4','swf','flv','rar','zip','pdf'); //本地存储允许上传的文件类型
	private static $allowSize=25; //最大允许上传的大小,单位 M


	private static $tietuAccesskey = '63f05140c64c80b40f87ee6e4d41c2635c2d1069';
	private static $tietuSecretkey = '2c332c8c4af922ae525183b671f7471d7b180126';
	private static $album=1548; //贴图库上传的相册

	private static $qiniuAccessKey = 'M96SigayyYQR0VdlFir96pGKmf3NZ5YPTmF0lzmv';
	private static $qiniuSecretKey = 'BlGJG8cH2Sgjqjrq3_YAvouxa-DRIAr9eMKWmbiN';
	private static $bucket='supic'; //七牛的bucket

	/**
	 * 传入参数可覆盖默认设置
	 */
	function __construct($cfg=null)
	{
		if(is_array($cfg))
		{
			foreach ($cfg as $k => $v)
			{
				if(isset(self::$$k))
				{
					self::$$k=$v;
				}
			}
		}
		
	}
	/**
	 * Init Uploader
	 */
	function init()
	{

	}
	/**
	 * 监视本地文件上传, $name为文件表单名，可发送单独POST['name']定义存储文件名
	 */
	function upload($name,$storName=null)
	{
		$ret=$this->commonCheck($name,$storName);
		if($ret['code']==0)
		{
			$destination=self::$uploadDir.date('Ymd');
			if(!is_readable($destination))
	    	{
	        	is_file($destination) or mkdir($destination,0700);
	    	}
	    	$destination=$destination.'/'.$ret['msg'];
			move_uploaded_file($_FILES[$name]['tmp_name'], $destination);
			$ret['msg']=baseUrl($destination);
			return $ret;
		}
		return $ret;
	}
	/**
	 * 监视用户上传并转文件到SAE
	 * 文件大小sae还有限定,无类型限制,见sae_storage.class.php
	 * 上传至sae的文件名都是hash,不固定
	 * $storName无效,但可用于类型检测
	 */
	function uploadSae($name,$storName=null)
	{
		$ret=$this->commonCheck($name,$storName);
		if($ret['code']==0)
		{
			return self::sendToSae($_FILES[$name]['tmp_name']);
		}
		return $ret;
			
	}
	function uploadKupan($name,$storName=null)
	{
		$ret=$this->commonCheck($name,$storName);
		if($ret['code']==0)
		{
			$filename=$ret['msg'];//存储时的文件名
			return self::sendToKupan($_FILES[$name]['tmp_name'],$filename);
		}
		return $ret;
	}
	function uploadQiniu($name,$storName=null)
	{
		$ret=$this->commonCheck($name,$storName);
		if($ret['code']==0)
		{
			$filename=$ret['msg'];
			return self::sendToQiniu($_FILES[$name]['tmp_name'],$filename);
		}
		return $ret;
	}
	function uploadTietu($name)
	{
		$ret=$this->commonCheck($name);
		if($ret['code']==0)
		{
			return self::sendToTietu($_FILES[$name]['tmp_name']);
		}
		return $ret;
		
	}
	/**
	 * 上传到其他url
	 */
	function uploadUrl($name,$url)
	{
		$ret=$this->commonCheck($name);
		if($ret['code']==0)
		{
			$data=array($name=>"@".realpath($_FILES[$name]['tmp_name']));
			return self::postData($url,$data);
		}
		return $ret;

	}
	/**
	 * 发送文件到sae,和文件类型,大小无关
	 */
	function sendToSae($filepath)
	{
		$data = array("file"  => "@".realpath($filepath)); //sae接收的字段也是file
		$res=json_decode(self::postData(self::$saeServer.'upload',$data)); 
		if($res->code==0)
		{
			return array('code'=>0 ,'msg'=>self::$saeServer.$res->msg);
		}
		return array('code'=>-1,'msg'=>$res->msg); //sae 也会返回错误消息
	}

	function sendToKupan($filepath,$name)
	{
		$file=file_get_contents($filepath);
	    $path='/files/uploader/'.$name;
	    $token=file_get_contents(self::$kupanKeyServer);  //酷盘密匙每小时更新
	    $url="https://api-upload.kanbox.com/0/upload{$path}?bearer_token=".$token;
	    $res=self::postData($url,$file);
	    $downUrl='http://api.suconghou.cn'.$path;
	    if($res==1)
	    {
	    	return array('code'=>0,'msg'=>$downUrl);
	    }
	    return array('code'=>-2,'msg'=>'send to kupan error');
	}
	/**
	 * $filename 七牛可以手动填写路径
	 */
	function sendToQiniu($path,$filename)
	{
		Qiniu_setKeys(self::$qiniuAccessKey, self::$qiniuSecretKey);
		$qiniu = new Qiniu_MacHttpClient(null);

		$putPolicy = new Qiniu_RS_PutPolicy(self::$bucket);

		$upToken = $putPolicy->Token(null);
		$putExtra = new Qiniu_PutExtra();
		$putExtra->Crc32 = 1;
		list($ret, $err) = Qiniu_PutFile($upToken,$filename,$path, $putExtra);
		if($err)
		{
			return array('code'=>-2,'msg'=>$err);
		}
		else
		{
			$file=$ret['key'];
			$domain = self::$bucket.'.qiniudn.com';
			$baseUrl = Qiniu_RS_MakeBaseUrl($domain,$file);
			$private =false;
			if($private) //是否是私有文件
			{
				$getPolicy = new Qiniu_RS_GetPolicy();
				$baseUrl = $getPolicy->MakeRequest($baseUrl, null);
			}
			return array('code'=>0,'msg'=>$baseUrl);
		}
		
	}
	/**
	 * 贴图库返回有多个可用信息,直接返回上层处理
	 */
	function sendToTietu($path)
	{

		$tietu = new TieTuKuToken(self::$tietuAccesskey,self::$tietuSecretkey);
		$url='http://up.tietuku.com/';
		$param['deadline'] = time()+60;
		$param['aid'] = self::$album;
		$param['from']='file';
		$token=$tietu->Dealparam($param)->createToken();
	    $postData = array( "Token"=>$token, "file"=>'@'.realpath($path));
		$ret = json_decode(self::postData($url,$postData));
		if(isset($ret->linkurl))
		{
			return array('code'=>0,'msg'=>(array)$ret);
		}
		return array('code'=>-2,'msg'=>'send to tietu error ');

	}
	/**
	 *  @param $f 表单名,
	 *  @param $storName 存储名
	 * 文件类型不合法,则返回false
	 * ajax切割上传没有发来文件名的会采取自动命名
	 * 部分文件类型不能检测,需要随请求发送来
	 */
	private function detectName($f,$storName=null)
	{
		$contents=file_get_contents($_FILES[$f]['tmp_name']);
		if($storName)
		{
			$default=$storName;
		}
		else if(Request::post('name'))
		{
			$default=Request::post('name'); 
		}
		else if($_FILES[$f]['name']!='blob')
		{
			$default=$_FILES[$f]['name'];
		}
		else
		{
			$default=uniqid().'.unknow';
		}
		$default=preg_replace('/[^\w\.]/',chr(mt_rand(65,90)),$default);//文件名清除
		$arr=explode('.',$default);
		$defaultType=end($arr); ///综合得出默认文件类型
		$type=self::getType($contents,$defaultType);

		if(in_array($type,self::$allowType)) //合法的文件类型
		{
			$filename=$arr[0].'.'.$type;
			return $filename;
		}
		return false;

	}
	/**
	 * 是否存在上传和文件大小与类型检测
	 * @param $f 表单名
	 * @param $storName 存储时的文件名
	 */
	private function commonCheck($f,$storName=null)
	{	
		if(!empty($_FILES)&&is_string($f)&&isset($_FILES[$f]))
		{
			if($_FILES[$f]['error']==0)
			{
				if($_FILES[$f]['size']<self::$allowSize*1024*1024)
				{
					$filename=$this->detectName($f,$storName);
					if($filename)
					{
						return array('code'=>0,'msg'=>$filename); //存储时的文件名
					}
					return array('code'=>-2,'msg'=>'file type not allow');
				}
				return array('code'=>-3,'msg'=>'exceeds limit size');
			}
			return array('code'=>-4,'msg'=>'upload error');
		}

		return array('code'=>-5,'msg'=>'no file upload');


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
//end class uploader




// 贴图库class
class TieTuKuToken{
	public $accesskey;
	public $secretkey;
	private $base64param;
	function __construct($accesskey,$secretkey){
		if($accesskey == ''||$secretkey =='')
			return false;
		$this->accesskey = $accesskey;
		$this->secretkey = $secretkey;
	}
	function dealParam($param){
		$this->base64param = $this->URLSafeBase64Encode(json_encode($param));
		return $this;
	}
	function createToken(){
		if(empty($this->base64param)) return false;
		$sign = $this->signEncode($this->base64param,$this->secretkey);
		return $this->accesskey.':'.$this->URLSafeBase64Encode($sign).':'.$this->base64param;
	}
	function signEncode($str, $key){
		$hmac_sha1_str = "";
		if (function_exists('hash_hmac')){
			$hmac_sha1_str = hash_hmac("sha1", $str, $key, true);
		} else {
			$blocksize = 64;
			$hashfunc  = 'sha1';
			if (strlen($key) > $blocksize){
				$key = pack('H*', $hashfunc($key));
			}
			$key       		= str_pad($key, $blocksize, chr(0x00));
			$ipad      		= str_repeat(chr(0x36), $blocksize);
			$opad      		= str_repeat(chr(0x5c), $blocksize);
			$hmac_sha1_str	= pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $str))));
		}
		return $hmac_sha1_str;
	}
	function URLSafeBase64Encode($str){
		$find = array('+', '/');
		$replace = array('-', '_');
		return str_replace($find, $replace, base64_encode($str));
	}
}
//end class TieTuKuToken

// qiniu API class
function Qiniu_Encode($str) // URLSafeBase64Encode
{
    $find = array('+', '/');
    $replace = array('-', '_');
    return str_replace($find, $replace, base64_encode($str));
}


function Qiniu_Decode($str)
{
    $find = array('-', '_');
    $replace = array('+', '/');
    return base64_decode(str_replace($find, $replace, $str));
}

global $QINIU_UP_HOST;
global $QINIU_RS_HOST;
global $QINIU_RSF_HOST;
 
global $QINIU_ACCESS_KEY;
global $QINIU_SECRET_KEY;

$QINIU_UP_HOST  = 'http://up.qiniu.com';
$QINIU_RS_HOST  = 'http://rs.qbox.me';
$QINIU_RSF_HOST = 'http://rsf.qbox.me';

$QINIU_ACCESS_KEY   = '<Please apply your access key>';
$QINIU_SECRET_KEY   = '<Dont send your secret key to anyone>';


// ----------------------------------------------------------

class Qiniu_Mac {

    public $AccessKey;
    public $SecretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->AccessKey = $accessKey;
        $this->SecretKey = $secretKey;
    }

    public function Sign($data) // => $token
    {
        $sign = hash_hmac('sha1', $data, $this->SecretKey, true);
        return $this->AccessKey . ':' . Qiniu_Encode($sign);
    }

    public function SignWithData($data) // => $token
    {
        $data = Qiniu_Encode($data);
        return $this->Sign($data) . ':' . $data;
    }

    public function SignRequest($req, $incbody) // => ($token, $error)
    {
        $url = $req->URL;
        $url = parse_url($url['path']);
        $data = '';
        if (isset($url['path'])) {
            $data = $url['path'];
        }
        if (isset($url['query'])) {
            $data .= '?' . $url['query'];
        }
        $data .= "\n";

        if ($incbody) {
            $data .= $req->Body;
        }
        return $this->Sign($data);
    }
}

function Qiniu_SetKeys($accessKey, $secretKey)
{
    global $QINIU_ACCESS_KEY;
    global $QINIU_SECRET_KEY;

    $QINIU_ACCESS_KEY = $accessKey;
    $QINIU_SECRET_KEY = $secretKey;
}

function Qiniu_RequireMac($mac) // => $mac
{
    if (isset($mac)) {
        return $mac;
    }

    global $QINIU_ACCESS_KEY;
    global $QINIU_SECRET_KEY;

    return new Qiniu_Mac($QINIU_ACCESS_KEY, $QINIU_SECRET_KEY);
}

function Qiniu_Sign($mac, $data) // => $token
{
    return Qiniu_RequireMac($mac)->Sign($data);
}

function Qiniu_SignWithData($mac, $data) // => $token
{
    return Qiniu_RequireMac($mac)->SignWithData($data);
}

// ----------------------------------------------------------


// --------------------------------------------------------------------------------
// class Qiniu_Error

class Qiniu_Error
{
    public $Err;     // string
    public $Reqid;   // string
    public $Details; // []string
    public $Code;    // int

    public function __construct($code, $err)
    {
        $this->Code = $code;
        $this->Err = $err;
    }
}

// --------------------------------------------------------------------------------
// class Qiniu_Request

class Qiniu_Request
{
    public $URL;
    public $Header;
    public $Body;

    public function __construct($url, $body)
    {
        $this->URL = $url;
        $this->Header = array();
        $this->Body = $body;
    }
}

// --------------------------------------------------------------------------------
// class Qiniu_Response

class Qiniu_Response
{
    public $StatusCode;
    public $Header;
    public $ContentLength;
    public $Body;

    public function __construct($code, $body)
    {
        $this->StatusCode = $code;
        $this->Header = array();
        $this->Body = $body;
        $this->ContentLength = strlen($body);
    }
}

// --------------------------------------------------------------------------------
// class Qiniu_Header

function Qiniu_Header_Get($header, $key) // => $val
{
    $val = isset($header[$key])?$header[$key]:NULL;

    if (isset($val)) {
        if (is_array($val)) {
            return $val[0];
        }
        return $val;
    } else {
        return '';
    }
}

function Qiniu_ResponseError($resp) // => $error
{
    $header = $resp->Header;
    $details = Qiniu_Header_Get($header, 'X-Log');
    $reqId = Qiniu_Header_Get($header, 'X-Reqid');
    $err = new Qiniu_Error($resp->StatusCode, null);

    if ($err->Code > 299) {
        if ($resp->ContentLength !== 0) {
            if (Qiniu_Header_Get($header, 'Content-Type') === 'application/json') {
                $ret = json_decode($resp->Body, true);
                $err->Err = $ret['error'];
            }
        }
    }
    return $err;
}

// --------------------------------------------------------------------------------
// class Qiniu_Client

function Qiniu_Client_incBody($req) // => $incbody
{
    $body = $req->Body;
    if (!isset($body)) {
        return false;
    }

    $ct = Qiniu_Header_Get($req->Header, 'Content-Type');
    if ($ct === 'application/x-www-form-urlencoded') {
        return true;
    }
    return false;
}

function Qiniu_Client_do($req) // => ($resp, $error)
{
    $ch = curl_init();
    $url = $req->URL;
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_URL => $url['path']
    );
    $httpHeader = $req->Header;
    if (!empty($httpHeader))
    {
        $header = array();
        foreach($httpHeader as $key => $parsedUrlValue) {
            $header[] = "$key: $parsedUrlValue";
        }
        $options[CURLOPT_HTTPHEADER] = $header;
    }
    $body = $req->Body;
    if (!empty($body)) {
        $options[CURLOPT_POSTFIELDS] = $body;
    }
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    $ret = curl_errno($ch);
    if ($ret !== 0) {
        $err = new Qiniu_Error(0, curl_error($ch));
        curl_close($ch);
        return array(null, $err);
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    $resp = new Qiniu_Response($code, $result);
    $resp->Header['Content-Type'] = $contentType;
    return array($resp, null);
}

class Qiniu_HttpClient
{
    public function RoundTrip($req) // => ($resp, $error)
    {
        return Qiniu_Client_do($req);
    }
}

class Qiniu_MacHttpClient
{
    public $Mac;

    public function __construct($mac)
    {
        $this->Mac = Qiniu_RequireMac($mac);
    }

    public function RoundTrip($req) // => ($resp, $error)
    {
        $incbody = Qiniu_Client_incBody($req);
        $token = $this->Mac->SignRequest($req, $incbody);
        $req->Header['Authorization'] = "QBox $token";
        return Qiniu_Client_do($req);
    }
}

// --------------------------------------------------------------------------------

function Qiniu_Client_ret($resp) // => ($data, $error)
{
    $code = $resp->StatusCode;
    $data = null;
    if ($code >= 200 && $code <= 299) {
        if ($resp->ContentLength !== 0) {
            $data = json_decode($resp->Body, true);
            if ($data === null) {
                $err_msg = function_exists('json_last_error_msg') ? json_last_error_msg() : "error with content:" . $resp->Body;
                $err = new Qiniu_Error(0, $err_msg);
                return array(null, $err);
            }
        }
        if ($code === 200) {
            return array($data, null);
        }
    }
    return array($data, Qiniu_ResponseError($resp));
}

function Qiniu_Client_Call($self, $url) // => ($data, $error)
{
    $u = array('path' => $url);
    $req = new Qiniu_Request($u, null);
    list($resp, $err) = $self->RoundTrip($req);
    if ($err !== null) {
        return array(null, $err);
    }
    return Qiniu_Client_ret($resp);
}

function Qiniu_Client_CallNoRet($self, $url) // => $error
{
    $u = array('path' => $url);
    $req = new Qiniu_Request($u, null);
    list($resp, $err) = $self->RoundTrip($req);
    if ($err !== null) {
        return array(null, $err);
    }
    if ($resp->StatusCode === 200) {
        return null;
    }
    return Qiniu_ResponseError($resp);
}

function Qiniu_Client_CallWithForm(
    $self, $url, $params, $contentType = 'application/x-www-form-urlencoded') // => ($data, $error)
{
    $u = array('path' => $url);
    if ($contentType === 'application/x-www-form-urlencoded') {
        if (is_array($params)) {
            $params = http_build_query($params);
        }
    }
    $req = new Qiniu_Request($u, $params);
    if ($contentType !== 'multipart/form-data') {
        $req->Header['Content-Type'] = $contentType;
    }
    list($resp, $err) = $self->RoundTrip($req);
    if ($err !== null) {
        return array(null, $err);
    }
    return Qiniu_Client_ret($resp);
}

// --------------------------------------------------------------------------------

function Qiniu_Client_CallWithMultipartForm($self, $url, $fields, $files)
{
    list($contentType, $body) = Qiniu_Build_MultipartForm($fields, $files);
    return Qiniu_Client_CallWithForm($self, $url, $body, $contentType);
}

function Qiniu_Build_MultipartForm($fields, $files) // => ($contentType, $body)
{
    $data = array();
    $mimeBoundary = md5(microtime());

    foreach ($fields as $name => $val) {
        array_push($data, '--' . $mimeBoundary);
        array_push($data, "Content-Disposition: form-data; name=\"$name\"");
        array_push($data, '');
        array_push($data, $val);
    }

    foreach ($files as $file) {
        array_push($data, '--' . $mimeBoundary);
        list($name, $fileName, $fileBody, $mimeType) = $file;
        $mimeType = empty($mimeType) ? 'application/octet-stream' : $mimeType;
        $fileName = Qiniu_escapeQuotes($fileName);
        array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$fileName\"");
        array_push($data, "Content-Type: $mimeType");
        array_push($data, '');
        array_push($data, $fileBody);
    }

    array_push($data, '--' . $mimeBoundary . '--');
    array_push($data, '');

    $body = implode("\r\n", $data);
    $contentType = 'multipart/form-data; boundary=' . $mimeBoundary;
    return array($contentType, $body);
}

function Qiniu_escapeQuotes($str)
{
    $find = array("\\", "\"");
    $replace = array("\\\\", "\\\"");
    return str_replace($find, $replace, $str);
}

// --------------------------------------------------------------------------------



// ----------------------------------------------------------
// class Qiniu_PutExtra

class Qiniu_PutExtra
{
    public $Params = null;
    public $MimeType = null;
    public $Crc32 = 0;
    public $CheckCrc = 0;
}

function Qiniu_Put($upToken, $key, $body, $putExtra) // => ($putRet, $err)
{
    global $QINIU_UP_HOST;

    if ($putExtra === null) {
        $putExtra = new Qiniu_PutExtra;
    }

    $fields = array('token' => $upToken);
    if ($key === null) {
        $fname = '?';
    } else {
        $fname = $key;
        $fields['key'] = $key;
    }
    if ($putExtra->CheckCrc) {
        $fields['crc32'] = $putExtra->Crc32;
    }

    $files = array(array('file', $fname, $body, $putExtra->MimeType));

    $client = new Qiniu_HttpClient;
    return Qiniu_Client_CallWithMultipartForm($client, $QINIU_UP_HOST, $fields, $files);
}

function Qiniu_PutFile($upToken, $key, $localFile, $putExtra) // => ($putRet, $err)
{
    global $QINIU_UP_HOST;

    if ($putExtra === null) {
        $putExtra = new Qiniu_PutExtra;
    }

    if (!empty($putExtra->MimeType)) {
        $localFile .= ';type=' . $putExtra->MimeType;
    }

    $fields = array('token' => $upToken, 'file' => '@' . $localFile);
    if ($key === null) {
        $fname = '?';
    } else {
        $fname = $key;
        $fields['key'] = $key;
    }
    if ($putExtra->CheckCrc) {
        if ($putExtra->CheckCrc === 1) {
            $hash = hash_file('crc32b', $localFile);
            $array = unpack('N', pack('H*', $hash));
            $putExtra->Crc32 = $array[1];
        }
        $fields['crc32'] = sprintf('%u', $putExtra->Crc32);
    }

    $client = new Qiniu_HttpClient;
    return Qiniu_Client_CallWithForm($client, $QINIU_UP_HOST, $fields, 'multipart/form-data');
}

// ----------------------------------------------------------


// ----------------------------------------------------------
// class Qiniu_RS_GetPolicy

class Qiniu_RS_GetPolicy
{
    public $Expires;

    public function MakeRequest($baseUrl, $mac) // => $privateUrl
    {
        $deadline = $this->Expires;
        if ($deadline == 0) {
            $deadline = 3600;
        }
        $deadline += time();

        $pos = strpos($baseUrl, '?');
        if ($pos !== false) {
            $baseUrl .= '&e=';
        } else {
            $baseUrl .= '?e=';
        }
        $baseUrl .= $deadline;

        $token = Qiniu_Sign($mac, $baseUrl);
        return "$baseUrl&token=$token";
    }
}

function Qiniu_RS_MakeBaseUrl($domain, $key) // => $baseUrl
{
    $keyEsc = rawurlencode($key);
    return "http://$domain/$keyEsc";
}

// --------------------------------------------------------------------------------
// class Qiniu_RS_PutPolicy

class Qiniu_RS_PutPolicy
{
    public $Scope;                  //必填
    public $Expires;                //默认为3600s
    public $CallbackUrl;
    public $CallbackBody;
    public $ReturnUrl;
    public $ReturnBody;
    public $AsyncOps;
    public $EndUser;
    public $InsertOnly;             //若非0，则任何情况下无法覆盖上传
    public $DetectMime;             //若非0，则服务端根据内容自动确定MimeType
    public $FsizeLimit;
    public $SaveKey;
    public $PersistentOps;
    public $PersistentNotifyUrl;
    public $Transform;
    public $FopTimeout;

    public function __construct($scope)
    {
        $this->Scope = $scope;
    }

    public function Token($mac) // => $token
    {
        $deadline = $this->Expires;
        if ($deadline == 0) {
            $deadline = 3600;
        }
        $deadline += time();

        $policy = array('scope' => $this->Scope, 'deadline' => $deadline);
        if (!empty($this->CallbackUrl)) {
            $policy['callbackUrl'] = $this->CallbackUrl;
        }
        if (!empty($this->CallbackBody)) {
            $policy['callbackBody'] = $this->CallbackBody;
        }
        if (!empty($this->ReturnUrl)) {
            $policy['returnUrl'] = $this->ReturnUrl;
        }
        if (!empty($this->ReturnBody)) {
            $policy['returnBody'] = $this->ReturnBody;
        }
        if (!empty($this->AsyncOps)) {
            $policy['asyncOps'] = $this->AsyncOps;
        }
        if (!empty($this->EndUser)) {
            $policy['endUser'] = $this->EndUser;
        }
        if (!empty($this->InsertOnly)) {
            $policy['exclusive'] = $this->InsertOnly;
        }
        if (!empty($this->DetectMime)) {
            $policy['detectMime'] = $this->DetectMime;
        }
        if (!empty($this->FsizeLimit)) {
            $policy['fsizeLimit'] = $this->FsizeLimit;
        }
        if (!empty($this->SaveKey)) {
            $policy['saveKey'] = $this->SaveKey;
        }
        if (!empty($this->PersistentOps)) {
            $policy['persistentOps'] = $this->PersistentOps;
        }
        if (!empty($this->PersistentNotifyUrl)) {
            $policy['persistentNotifyUrl'] = $this->PersistentNotifyUrl;
        }
        if (!empty($this->Transform)) {
            $policy['transform'] = $this->Transform;
        }
        if (!empty($this->FopTimeout)) {
            $policy['fopTimeout'] = $this->FopTimeout;
        }

        $b = json_encode($policy);
        return Qiniu_SignWithData($mac, $b);
    }
}

// ----------------------------------------------------------
// class Qiniu_RS_EntryPath

class Qiniu_RS_EntryPath
{
    public $bucket;
    public $key;

    public function __construct($bucket, $key)
    {
        $this->bucket = $bucket;
        $this->key = $key;
    }
}

// ----------------------------------------------------------
// class Qiniu_RS_EntryPathPair

class Qiniu_RS_EntryPathPair
{
    public $src;
    public $dest;

    public function __construct($src, $dest)
    {
        $this->src = $src;
        $this->dest = $dest;
    }
}

// ----------------------------------------------------------

function Qiniu_RS_URIStat($bucket, $key)
{
    return '/stat/' . Qiniu_Encode("$bucket:$key");
}

function Qiniu_RS_URIDelete($bucket, $key)
{
    return '/delete/' . Qiniu_Encode("$bucket:$key");
}

function Qiniu_RS_URICopy($bucketSrc, $keySrc, $bucketDest, $keyDest)
{
    return '/copy/' . Qiniu_Encode("$bucketSrc:$keySrc") . '/' . Qiniu_Encode("$bucketDest:$keyDest");
}

function Qiniu_RS_URIMove($bucketSrc, $keySrc, $bucketDest, $keyDest)
{
    return '/move/' . Qiniu_Encode("$bucketSrc:$keySrc") . '/' . Qiniu_Encode("$bucketDest:$keyDest");
}

// ----------------------------------------------------------

function Qiniu_RS_Stat($self, $bucket, $key) // => ($statRet, $error)
{
    global $QINIU_RS_HOST;
    $uri = Qiniu_RS_URIStat($bucket, $key);
    return Qiniu_Client_Call($self, $QINIU_RS_HOST . $uri);
}

function Qiniu_RS_Delete($self, $bucket, $key) // => $error
{
    global $QINIU_RS_HOST;
    $uri = Qiniu_RS_URIDelete($bucket, $key);
    return Qiniu_Client_CallNoRet($self, $QINIU_RS_HOST . $uri);
}

function Qiniu_RS_Move($self, $bucketSrc, $keySrc, $bucketDest, $keyDest) // => $error
{
    global $QINIU_RS_HOST;
    $uri = Qiniu_RS_URIMove($bucketSrc, $keySrc, $bucketDest, $keyDest);
    return Qiniu_Client_CallNoRet($self, $QINIU_RS_HOST . $uri);
}

function Qiniu_RS_Copy($self, $bucketSrc, $keySrc, $bucketDest, $keyDest) // => $error
{
    global $QINIU_RS_HOST;
    $uri = Qiniu_RS_URICopy($bucketSrc, $keySrc, $bucketDest, $keyDest);
    return Qiniu_Client_CallNoRet($self, $QINIU_RS_HOST . $uri);
}

// ----------------------------------------------------------
// batch

function Qiniu_RS_Batch($self, $ops) // => ($data, $error)
{
    global $QINIU_RS_HOST;
    $url = $QINIU_RS_HOST . '/batch';
    $params = 'op=' . implode('&op=', $ops);
    return Qiniu_Client_CallWithForm($self, $url, $params);
}

function Qiniu_RS_BatchStat($self, $entryPaths)
{
    $params = array();
    foreach ($entryPaths as $entryPath) {
        $params[] = Qiniu_RS_URIStat($entryPath->bucket, $entryPath->key);
    }
    return Qiniu_RS_Batch($self,$params);
}

function Qiniu_RS_BatchDelete($self, $entryPaths)
{
    $params = array();
    foreach ($entryPaths as $entryPath) {
        $params[] = Qiniu_RS_URIDelete($entryPath->bucket, $entryPath->key);
    }
    return Qiniu_RS_Batch($self, $params);
}

function Qiniu_RS_BatchMove($self, $entryPairs)
{
    $params = array();
    foreach ($entryPairs as $entryPair) {
        $src = $entryPair->src;
        $dest = $entryPair->dest;
        $params[] = Qiniu_RS_URIMove($src->bucket, $src->key, $dest->bucket, $dest->key);
    }
    return Qiniu_RS_Batch($self, $params);
}

function Qiniu_RS_BatchCopy($self, $entryPairs)
{
    $params = array();
    foreach ($entryPairs as $entryPair) {
        $src = $entryPair->src;
        $dest = $entryPair->dest;
        $params[] = Qiniu_RS_URICopy($src->bucket, $src->key, $dest->bucket, $dest->key);
    }
    return Qiniu_RS_Batch($self, $params);
}

// ----------------------------------------------------------



// --------------------------------------------------------------------------------
// class Qiniu_ImageView

class Qiniu_ImageView {
	public $Mode;
    public $Width;
    public $Height;
    public $Quality;
    public $Format;

    public function MakeRequest($url)
    {
    	$ops = array($this->Mode);

    	if (!empty($this->Width)) {
    		$ops[] = 'w/' . $this->Width;
    	}
    	if (!empty($this->Height)) {
    		$ops[] = 'h/' . $this->Height;
    	}
    	if (!empty($this->Quality)) {
    		$ops[] = 'q/' . $this->Quality;
    	}
    	if (!empty($this->Format)) {
    		$ops[] = 'format/' . $this->Format;
    	}

    	return $url . "?imageView/" . implode('/', $ops);
    }
}

// --------------------------------------------------------------------------------
// class Qiniu_Exif

class Qiniu_Exif {

	public function MakeRequest($url)
	{
		return $url . "?exif";
	}

}

// --------------------------------------------------------------------------------
// class Qiniu_ImageInfo

class Qiniu_ImageInfo {

	public function MakeRequest($url)
	{
		return $url . "?imageInfo";
	}

}
// end qiniu API class

