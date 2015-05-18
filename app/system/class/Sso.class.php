<?php

/**
* 单点登录
* Single Sign On
*/
class Sso
{
	//缓存驱动
	private static $cache;

	//服务器通信秘钥
	const secret_key = 'hello';

	//授权域名
	private static $domain=array(
								'www.youxia123.com',
								'blog.suconghou.cn',
								'wenyibox.com'
							);


	function __construct()
	{
		$this->init();
	}

	function init()
	{
		app::route('\/auth(\?.*)?',function()
		{
			if(empty($_POST['secret_key']))
			{
				$account=isset($_REQUEST['account'])?$_REQUEST['account']:null;
				$password=isset($_REQUEST['password'])?$_REQUEST['password']:null;
				return $this->login($account,$password);
			}
			else
			{
				if($_POST['secret_key']===self::secret_key)
				{
					$token=isset($_POST['token'])?$_POST['token']:null;
					return $this->auth($token);
				}
				else
				{
					self::out(array('code'=>-1,'msg'=>'auth failed,secret key error'));
				}
			}
		});
		//sae 缓存驱动
		// self::$cache=memcache_init();
	}
	
	function login($account,$password)
	{
		$redirect=isset($_REQUEST['redirect'])?$_REQUEST['redirect']:null;
		if($user=$this->loginByEmail($account,$password))
		{
			$uid=$user['uid'];
			$token=$this->loginOk($account,$uid);
			if($redirect)
			{
				$url=$redirect."?token={$token}&domain=".base64_encode(json_encode(self::$domain));
				exit(header("Refresh: 0; url={$url}"));
			}
			else
			{
				self::out(array('code'=>0,'msg'=>'success','token'=>$token,'domain'=>self::$domain));
			}
		}
		else
		{	
			if($redirect)
			{
				$url=$redirect."?token={$token}&domain=".base64_encode(json_encode(self::$domain));
				exit(header("Refresh: 0; url={$url}"));
			}
			else
			{
				self::out(array('code'=>-100,'msg'=>'邮箱与密码不匹配'));
			}
		}
	}

	function loginByEmail($email,$password)
	{
		if(filter_var($email,FILTER_VALIDATE_EMAIL))
		{
			$sql="SELECT uid,email,username,password FROM `users` ";
			$user=DB::getLine($sql);
			if($user)
			{
				if($password===$user['password'])
				{
					return $user;
				}
			}
		}
		return false;
	}

	function loginOk($account,$uid)
	{
		$token=md5($account.uniqid());
		self::set($token,$uid);
		return $token;
	}

	/**
	 * 服务器通信验证接口
	 */
	function auth($token)
	{
		$uid=self::get($token);
		if($uid)
		{
			$sql="SELECT uid,email,username,phone FROM `users` ";
			$user=DB::getLine($sql);
			if($user)
			{
				self::out(array('code'=>0,'msg'=>'success','data'=>$user));
			}
		}
		self::out(array('code'=>-10,'msg'=>'auth failed,token error'));

	}

	private static function out($array)
	{
		$data=json_encode($array);
		if(empty($_REQUEST['callback']))
		{
			exit($data);
		}
		else
		{
			$callback=$_REQUEST['callback'];
			exit($callback."(".$data.")");
		}
	}

	private static function get($key)
	{
		return self::$cache->get($key);
	}

	private static function set($key,$value)
	{
		return self::$cache->set($key,$value);
	}

}