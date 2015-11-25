<?php

/**
* 微信公众号
*/
class Wechat
{
	const baseurl='https://api.weixin.qq.com/cgi-bin/';
	private static $token;
	private static $appid;
	private static $secret;

	private static $event;

	private static $msgdata;
	private static $response;
	
	function __construct()
	{
		
	}
	
	public static function ready($token=null,$appid=null,$secret=null)
	{
		self::$token=$token;
	    return self::signature($token,$appid,$secret);
	}
	
	public static function signature($token=null,$appid=null,$secret=null)
	{
		list($signature,$timestamp,$nonce,$echostr)=array_values(self::get(array('signature','timestamp','nonce','echostr')));
		$data=array($token,$timestamp,$nonce);
		sort($data,SORT_STRING);
		if(sha1(implode($data))===$signature)
		{
			self::$appid=$appid;
			self::$secret=$secret;
			return self::onMessage($token,$echostr);
		}
		else
		{
			return self::json(array('code'=>-100,'msg'=>'signature check failed'));
		}

	}
	
	public static function onMessage($token,$echostr)
	{
		$data=self::getMsgData();
		self::log('get msg data'.PHP_EOL.print_r($data,true));
		if($data)
		{
			self::$msgdata=$data;
			$event=&self::$event;
			$all=isset($event['*'])?$event['*']:false;
			if($all)
			{
				$all($data,$token);
			}
			if(isset($data['MsgType']))
			{
				$msgType=$data['MsgType'];
				if(isset($data['Event']))
				{
					$eve=strtolower($data['Event']);
					if(isset($event["event.{$eve}"]))
					{
						$evefun=$event["event.{$eve}"];
						$evefun($data,$token);
					}
				}
				if(isset($event[$msgType]))
				{
					$callback=$event[$msgType];
					$callback($data,$token);
				}
			}
		}
		else
		{
			return self::response($echostr);
		}
		
	}
	
	/**
	 * 普通消息类型text/image/voice/video/shortvideo/location/link
	 * 事件推送类型event
	 */
	public static function on($event,Closure $callback)
	{
		$event=strtolower($event);
		return self::$event[$event]=$callback;
	}
	
	/**
	 * 事件推送类型event包括subscribe/unsubscribe/scan/location/click/view
	 */
	public static function event($event,Closure $callback)
	{
		$event=strtolower($event);
		return self::$event["event.{$event}"]=$callback;	
	}
	
	/**
	 * 解除事件绑定,event须添加event.
	 */
	public static function off($event)
	{
		$event=&self::$event;
		unset($event[$event]);
		return $event;
	}
	
	public static function sendText($text,$toUser,$fromUser)
	{
		$now=time();
		$data=array('<xml>',"<ToUserName><![CDATA[{$toUser}]]></ToUserName>","<FromUserName><![CDATA[{$fromUser}]]></FromUserName>","<CreateTime>{$now}</CreateTime>",'<MsgType><![CDATA[text]]></MsgType>',"<Content><![CDATA[{$text}]]></Content>",'</xml>');
		return self::sendMsgData(implode(PHP_EOL,$data));
	}
	
	public static function sendImg($file,$toUser,$fromUser)
	{
		$now=time();
		$media=self::addMedia($file,'image');
		if(isset($media['media_id']))
		{
			$media_id=$media['media_id'];
			$data=array('<xml>',"<ToUserName><![CDATA[{$toUser}]]></ToUserName>","<FromUserName><![CDATA[{$fromUser}]]></FromUserName>","<CreateTime>{$now}</CreateTime>",'<MsgType><![CDATA[image]]></MsgType>','<Image>',"<MediaId><![CDATA[{$media_id}]]></MediaId>",'</Image>','</xml>');
			return self::sendMsgData(implode(PHP_EOL,$data));
		}
		return false;
	}
	
	public static function sendVoice($media_id,$toUser,$fromUser)
	{
		$now=time();
		$data=array('<xml>',"<ToUserName><![CDATA[{$toUser}]]></ToUserName>","<FromUserName><![CDATA[{$fromUser}]]></FromUserName>","<CreateTime>{$now}</CreateTime>",'<MsgType><![CDATA[voice]]></MsgType>','<Voice>',"<MediaId><![CDATA[{$media_id}]]></MediaId>",'</Voice>','</xml>');
		return self::sendMsgData(implode(PHP_EOL,$data));
	}
	
	public static function sendVideo($media_id,$toUser,$fromUser,$title=null,$description=null)
	{
		$now=time();
		$data=array('<xml>',"<ToUserName><![CDATA[{$toUser}]]></ToUserName>","<FromUserName><![CDATA[{$fromUser}]]></FromUserName>","<CreateTime>{$now}</CreateTime>",'<MsgType><![CDATA[video]]></MsgType>','<Video>',"<MediaId><![CDATA[{$media_id}]]></MediaId>","<Title><![CDATA[{$title}]]></Title>","<Description><![CDATA[{$description}]]></Description>",'</Video>','</xml>');
		return self::sendMsgData(implode(PHP_EOL,$data));
	}
	
	public static function sendMusic($MUSIC_Url,$toUser,$fromUser,$TITLE=null,$DESCRIPTION=null,$HQ_MUSIC_Url=null,$media_id=null)
	{
		$now=time();
		$data=array('<xml>',"<ToUserName><![CDATA[{$toUser}]]></ToUserName>","<FromUserName><![CDATA[{$fromUser}]]></FromUserName>","<CreateTime>{$now}</CreateTime>",'<MsgType><![CDATA[music]]></MsgType>','<Music>',"<Title><![CDATA[{$TITLE}]]></Title>","<Description><![CDATA[{$DESCRIPTION}]]></Description>","<MusicUrl><![CDATA[{$MUSIC_Url}]]></MusicUrl>","<HQMusicUrl><![CDATA[{$HQ_MUSIC_Url}]]></HQMusicUrl>","<ThumbMediaId><![CDATA[{$media_id}]]></ThumbMediaId>",'</Music>','</xml>');
		return self::sendMsgData(implode(PHP_EOL,$data));
	}
	
	public static function sendNews(Array $news,$toUser,$fromUser)
	{
		$now=time();
		$count=count($news);
		$count=$count>10?10:$count;
		$newsList=array();
		foreach ($news as $item)
		{
			$newsList[]=implode(PHP_EOL,array('<item>',"<Title><![CDATA[{$item['title']}]]></Title>","<Description><![CDATA[{$item['description']}]]></Description>","<PicUrl><![CDATA[{$item['picurl']}]]></PicUrl>","<Url><![CDATA[{$item['url']}]]></Url>",'</item>'));
			if(count($newsList)>=$count)
			{
				break;
			}
		}
		$items=implode(PHP_EOL,$newsList);
		$data=array('<xml>',"<ToUserName><![CDATA[{$toUser}]]></ToUserName>","<FromUserName><![CDATA[{$fromUser}]]></FromUserName>","<CreateTime>{$now}</CreateTime>",'<MsgType><![CDATA[news]]></MsgType>',"<ArticleCount>{$count}</ArticleCount>",'<Articles>',$items,'</Articles>','</xml>');
		return self::sendMsgData(implode(PHP_EOL,$data));
	}
	
	public static function sendTextToCurrent($text)
	{
		$msgdata=&self::$msgdata;
		$me=$msgdata['ToUserName'];
		$client=$msgdata['FromUserName'];
		return self::sendText($text,$client,$me);
	}

	public static function sendImgToCurrent($file)
	{
		$msgdata=&self::$msgdata;
		$me=$msgdata['ToUserName'];
		$client=$msgdata['FromUserName'];
		return self::sendImg($file,$client,$me);
	}

	public static function sendVoiceToCurrent($media)
	{
		$msgdata=&self::$msgdata;
		$me=$msgdata['ToUserName'];
		$client=$msgdata['FromUserName'];
		return self::sendVoice($media,$client,$me);
	}

	public static function sendVideoToCurrent($media,$title=null,$description=null)
	{
		$msgdata=&self::$msgdata;
		$me=$msgdata['ToUserName'];
		$client=$msgdata['FromUserName'];
		return self::sendVideo($text,$client,$me,$title,$description);
	}

	public static function sendMusicToCurrent($url,$hqurl,$title,$description,$media)
	{
		$msgdata=&self::$msgdata;
		$me=$msgdata['ToUserName'];
		$client=$msgdata['FromUserName'];
		return self::sendMusic($url,$client,$me,$title,$description,$hqurl,$media);
	}

	public static function sendNewsToCurrent($news)
	{
		$msgdata=&self::$msgdata;
		$me=$msgdata['ToUserName'];
		$client=$msgdata['FromUserName'];
		return self::sendNews($news,$client,$me);
	}

	/**
	 * 添加临时素材
	 * type可为image/voice/video/thumb
	 */
	public static function addMedia($file,$type='image')
	{
		if(is_file($file))
		{
			$token=self::token();
			$data=array('media'=>'@'.realpath($file));
			return json_decode(self::url("/media/upload?access_token={$token}&type={$type}",$data,true),true);
		}
		self::log("not found file {$file}");
		return false;
	}

	/**
	 * 获取临时素材
	 */
	public static function getMedia($media_id)
	{
		$token=self::token();
		return self::url("/media/get?access_token={$token}&media_id={$media_id}");
	}
	
	public static function uploadImg($file)
	{
		if(is_file($file))
		{
			$token=self::token();
			$finfo=finfo_open(FILEINFO_MIME);
			$mimetype=finfo_file($finfo,$file);
			finfo_close($finfo);
			$length=filesize($file);
			$data=array('filename'=>$file,'content-type'=>$mimetype,'filelength'=>$length);
			return json_decode(self::url("/media/uploadimg?access_token={$token}",$data,true),true);
		}
		return false;

	}

	public static function addMaterial($file)
	{
		$token=self::token();
		$data=array('title'=>$title,'thumb_media_id'=>$thumb_media_id,'author'=>$author,'digest'=>$digest,'show_cover_pic'=>$show_cover_pic,'content'=>$content,'content_source_url'=>$content_source_url);
		return json_decode(self::url("/material/add_news?access_token={$token}",$data,true),true);
	}

	public static function menuCreate($data)
	{
		return json_decode(self::url('/menu/create?access_token='.self::token(),$data,true),true);
	}

	public static function menuGet()
	{
		return json_decode(self::url('/menu/get?access_token='.self::token()),true);
	}

	public static function menuDelete()
	{
		return json_decode(self::url('/menu/delete?access_token='.self::token()),true);
	}

	public static function getMenuInfo()
	{
		return json_decode(self::url('/get_current_selfmenu_info?access_token='.self::token()),true);
	}



	/**
	 * 解析微信消息XML
	 */
	public static function getMsgData()
	{
		return json_decode(json_encode(simplexml_load_string(file_get_contents('php://input'),null,LIBXML_NOCDATA)),true);
	}
	
	/**
	 * 所有消息回复中枢
	 */
	public static function sendMsgData($data)
	{
		self::response($data);
	}

	public static function url($uri,$data=array(),$post=false)
	{
		$url=self::baseurl.trim($uri,'/?');
		if(!$post&&$data)
		{
			$url=$url.(stripos($url,'?')?'&':'?').(is_array($data)?http_build_query($data):$data);
		}
		$ch=curl_init($url);
		$headers=array('Referer'=>'http://www.baidu.com','User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36','Accept'=>'*/*');
		$timeout=8;
		curl_setopt_array($ch,array(CURLOPT_HTTPHEADER=>$headers,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>$timeout,CURLOPT_CONNECTTIMEOUT=>$timeout));
		if($post)
		{
			curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$data));
		}
		$result=curl_exec($ch);
		curl_close($ch);
		self::log(($post?'post':'get')." http data {$url}".PHP_EOL.$result);
		return $result;
	}

	/**
	 * 获取access_token
	 */
	public static function token()
	{
		$tokenInfo=self::session('tokenInfo');
		if(!empty($tokenInfo['expires_in'])&&($tokenInfo['expires_in']>time()))
		{
			return $tokenInfo['token'];
		}
		$json=json_decode(self::url('/token',array('grant_type'=>'client_credential','appid'=>self::$appid,'secret'=>self::$secret)),true);
		if(isset($json['access_token']))
		{
			$json['expires_in']+=time();
			self::session('tokenInfo',$json);
			return $json['access_token'];
		}
		return self::log('get access_token failed,'.print_r($json));

	}

	/**
	 * 本地存储会话数据
	 */
	public static function session($key,$value=null)
	{
		$file=sys_get_temp_dir().DIRECTORY_SEPARATOR."wechat.data";
		is_file($file)||touch($file);
		$data=unserialize(file_get_contents($file));
		$data=is_array($data)?$data:array();
		if($value)
		{
			$data[$key]=serialize($value);
			return file_put_contents($file,serialize($data));
		}
		$value=isset($data[$key])?$data[$key]:null;
		return is_null($value)?$value:unserialize($value);
	}

	private static function getRequestVar($origin,$key,$default=null)
	{
		if($key&&is_array($key))
		{
			$res=array();
			foreach ($key as $k)
			{
				$res[$k]=isset($origin[$k])?$origin[$k]:$default;
			}
			return $res;
		}
		return isset($origin[$key])?$origin[$key]:$default;
	}

	private static function get($key,$default=null)
	{
		return self::getRequestVar($_GET,$key,$default);
	}

	private static function post($key,$default=null)
	{
		return self::getRequestVar($_POST,$key,$default);
	}

	private static function json($data)
	{
		header('Content-Type: text/json',true,200);
		self::response(json_encode($data,JSON_UNESCAPED_UNICODE));
	}
	
	private static function response($text)
	{
		if(!self::isEnd())
		{
			self::log("response to ".PHP_EOL.$text);
			echo $text;
			return self::$response=$text;
		}
		self::log('already send response');
		return false;
	}

	/**
	 * 是否已回应微信服务器
	 */
	public static function isEnd()
	{
		return self::$response;
	}

	public static function log($msg)
	{
		return app::log($msg);
	}
}