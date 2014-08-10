<?php


/**
* QQ机器人,发送消息, SID有效期为1个月
* 结合cache,实现机器人自动回复,聊天
* 流程 init-->sendmsg
*          -->自动回复
*/
class qqroubot
{
	private static $qq;
	private static $sid;	
	private static $login; //记录是否已经登录
	private static $cache=1; //是否开启自动回复,需要持续运行
	private static $noreplay=300; //超过5分钟不在回复

	function __construct()
	{
		date_default_timezone_set("PRC");
		if(self::$cache)
		{
			self::$cache=S('class/cache','file'); //文本方式cache
		}
	}
	/**
	 * 会返回是否成功登陆,只使用一次
	 */
	public static function init($qq,$sid)
	{
		self::$qq=$qq;
		self::$sid=$sid;
		if(self::$cache) //使用cache ,记录是否登录
		{
			$login=self::$cache->get('login');
			if($login) //原本已经就登录了
			{
				return true;
			}
			else ///登陆过期了,执行登陆
			{
				self::login();
			}
		}
		else //不使用持久记录,每次脚本运行都会登陆
		{
			if(!self::$login)
			{
				self::login();
			}
		}
		return self::$login;
	}
	/**
	 * 向一位好友发送消息,会返回是否发送成功
	 */
	public static function sendMsg($to,$msg)
	{
		$send_url='http://q16.3g.qq.com/g/s?sid='.self::$sid.'&aid=sendmsg&tfor=qq&referer=';
		$postData='msg='.$msg.'&u='.$to.'&saveURL=0&do=send';
		$ret=self::postData($send_url,$postData);
		if(strpos(strip_tags($ret), '消息发送成功')!==false) 
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	private static function postData($url,$post_string)
	{
	    $ch=curl_init();
	    curl_setopt_array($ch, array(CURLOPT_URL=>$url,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$post_string));
	    $result=curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}
	/**
	 * 获取对方的昵称
	 */
	public static function getUsername($html)
	{
		//echo $html;die;
		preg_match_all('/与.+聊天-3GQQ/', $html, $matches);
		//var_dump($matches);die;
		$name=substr($matches[0][0],0,-11);
		$name=substr($name,3);
		return $name;
	}
	/**
	 * 获取对方的QQ号
	 */
	private static function getUserqq($html)
	{
		preg_match_all('/aid=nqqChat&amp;u=[1-9]\d{4,12}&amp;on=1&amp;referer=/',$html, $matches);
		preg_match_all('/[1-9]\d{4,12}/', $matches[0][0], $qq);
		return $qq[0][0];
	}
	/**
	 * 获取对方发送的消息,我方发送三条,对方未回复,则不再进行
	 */
	private static function getUsermsg($html,$name)
	{

		preg_match_all('/发送短信给他[\s\S]+【QQ功能】/', $html, $matches);
		$arr=explode("\r\n",$matches[0][0]);  //3,6,9,为内容, 1,4,7为对话者
		if(strpos($arr[1], $name)!==false)
		{
			$msg=substr($arr[3], 0,-5);
		}
		else if(strpos($arr[4], $name)!==false)
		{
			$msg=substr($arr[6], 0,-5);

		}
		else if(strpos($arr[7], $name)!==false)
		{
			$msg=substr($arr[9], 0,-5);
		}
		else
		{
			$msg=null;
		}
		return $msg;
	}
	/**
	 * 获取这条消息的发送时间
	 */
	private static function getMsgtime($html)
	{
		
		$regex="((0|1|2)[0-9]?):[0-5][0-9]:[0-5][0-9]";
		if(preg_match("/".$regex."/", $html, $matches))
		{
			
			if(isset($matches[0]))
			{
				$h=$matches[1];
				$s=substr($matches[0],-2);
				if($h==0)
				{
					$m=substr($matches[0],2,2);
				}
				else
				{
					$m=substr($matches[0],3,2);
				}
				
				$time=mktime($h,$m,$s);
				return $time;
			}
		}
		return false;
	}
	/**
	 * 登陆QQ
	 */
	private static function login()
	{

		$url="http://pt5.3g.qq.com/s?aid=nLogin3gqqbysid&3gqqsid=".self::$sid; //上线QQ
		$ret=strip_tags(file_get_contents($url));
		if(strpos($ret, '在线|最近|离线|分组'))
		{
			self::$login=true;
			if(self::$cache)
			{
				self::$cache->set('login',time(),1200); //有效期20分钟
			}
		}
		else
		{
			self::$login=false;
		}
		return self::$login;

	}
	/**
	 * 机器人回复接口,问答
	 */
	private static function getAnswer($user_name,$msg)
	{
		$url="http://wenapi.sinaapp.com/api/Robot_api.php?userid=".urlencode($user_name)."&content=".$msg;
		$ret=file_get_contents($url);
		$ans=json_decode(substr($ret,3)); //去除BOM
		if(isset($ans->msg)&&$ans->msg)
		{
			return $ans->msg;
		}
		return '我都不知道说什么好了。。。';
	}
	/**
	 * 聊天界面,自动回复,
	 * 开启cache,机器人会记住那些回复了,哪些没有回复,可以轮询执行该方法自动回复
	 */
	public static function chat()
	{
		$url = "http://q32.3g.qq.com/g/s?sid=".self::$sid."&3G_UIN=".self::$qq."&saveURL=0&aid=nqqChat"; //聊天界面
		$html=self::postData($url,'act=chat');
	
		$user_msg_time=self::getMsgtime($html); //最后聊天的时间
		$time=time()-$user_msg_time;
		// var_dump($time,self::$noreplay);die;
		if($time>self::$noreplay) //超过10分钟的不再回复
		{
			self::$cache&&sleep(1);
			return 'Waiting..';
		}
		$user_qq=self::getUserqq($html);
		$user_name=self::getUsername($html);
		$user_msg=self::getUsermsg($html,$user_name); ///对方发来的消息
		if(is_null($user_msg))
		{
			self::$cache&&sleep(1);
			return 'No Caller...';
		}

		if(self::$cache) //记录哪些回复过
		{
			$ans=self::$cache->get($user_msg);
			if($ans) //已回复过
			{
				self::$cache&&sleep(1);
				return 'Already Answered'; //什么不做
			}
			else //没有回复过,执行回复并记录
			{
				$answer=self::getAnswer($user_name,$user_msg); //要回复的内容
				logMessage($user_qq.'=>'.$answer);
				if(self::sendMsg($user_qq,$answer)) //回复成功,记录已回复
				{
					self::$cache->set($user_msg,$answer,self::$noreplay/2); //记录是否回复过
					return 'Successfully Answered';
				}
				else
				{
					return 'Error Send Msg !';
				}

			}
		}
		else // 没有记忆
		{
			$answer=self::getAnswer($user_name,$user_msg); //要回复的内容
			return self::sendMsg($user_qq,$answer)?'Successfully Answered ':'Error Send Msg ';
		}		
		
	}
	function __call($name,$args)
	{
		Error('500','Call Error Method In Class '.__CLASS__);
	}
	/**
	 * 命令行循环执行接口,周期时间动态伸缩
	 */
	public function exec($t=30) //周期30秒
	{
		$s=$t;
		for ($i=0; $i < 999999999999; $i++)
		{ 
			if($i%20==0) // 每 20个周期 执行登陆
			{
				echo self::login()?'Online':'Offline';
				echo "\r\n";
			}
			$a=self::chat();
			if($a=='Successfully Answered')
			{
				$s=($s/2)>2?$s/2:2;
			}
			else
			{
				$s=($s*1.1)>$t?$t:($s*1.1);
			}
			echo $a."\r\n";
			sleep($s);

		}
	}

}
