<?php

/**
* 单点登录
* Single Sign On
* Server and Client
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


	function __construct($server=false)
	{
		$this->init($server);
	}

	function init($server)
	{
		app::route('\/auth(\?.*)?',function()
		{
			if(isset($_REQUEST['account']) and isset($_REQUEST['password']))
			{
				$account=$_REQUEST['account'];
				$password=$_REQUEST['password'];
				return $this->login($account,$password);
			}
			else if(!empty($_POST['secret_key']))
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
			else
			{
				$this->redirect();
			}
		});
		app::route('\/setcookie(\?*)?',function()
		{
			$this->setcookie();
		});
		//sae 缓存驱动
		if(function_exists('memcache_init'))
		{
			self::$cache=memcache_init();
		}
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
				$url=$redirect."?msg=".urlencode("邮箱与密码不匹配");
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
	 * 客户端回调处理
	 */
	function redirect()
	{
		$token=isset($_GET['token'])?$_GET['token']:null;
		$domain=isset($_GET['domain'])?$_GET['domain']:null;
		$msg=isset($_GET['msg'])?$_GET['msg']:null;
		if($token and preg_match('/[a-z0-9]/', $token))
		{
			//success
			$expire=time()+86400*30;
			setcookie('token',$token,$expire,'/',$_SERVER['HTTP_HOST'],0,true);
			$script=array();
			$domain=json_decode(base64_decode($domain),true);
			foreach ($domain as $url)
			{
				$script[]="<script src=\"//{$url}/setcookie?token={$token}\"></script>";
			}
			//show welcome page , and get those script
			return implode("\r\n", $script);
		}
		else
		{
			//error,display error page
			echo $msg;
		}
	}

	/**
	 * 跨域之setcookie请求
	 */
	function setcookie()
	{
		$token=isset($_GET['token'])?$_GET['token']:null;
		if($token and preg_match('/[a-z0-9]/', $token))
		{
			$expire=time()+86400*30;
			setcookie('token',$token,$expire,'/',$_SERVER['HTTP_HOST'],0,true);
			header('X-SETCOOKIE:SUCCESS');
		}
		else
		{
			header('X-SETCOOKIE:ERROR');
		}
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