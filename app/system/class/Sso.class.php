<?php

/**
* 单点登录
* Single Sign On
* Server and Client
* 
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

	/**
	 * server 是否工作于服务模式
	 */
	function init($server=false)
	{
		app::route('\/auth(\?.*)?',function()
		{
			return $server?$this->__initServer():$this->__initClient();
		});
	}
	
	private function __initServer()
	{
		//sae 缓存驱动
		if(function_exists('memcache_init'))
		{
			self::$cache=memcache_init();
		}
		else
		{
			self::$cache=S('class/cache');
		}
		if(isset($_REQUEST['account'],$_REQUEST['password']))
		{
			//登录模式
			$account=$_REQUEST['account'];
			$password=$_REQUEST['password'];
			return $this->login($account,$password);
		}
		else if(!empty($_POST['secret_key']))
		{
			//内部授权模式
			if($_POST['secret_key']===self::secret_key)
			{
				$token=isset($_POST['token'])?$_POST['token']:null;
				return $this->auth($token);
			}
			else
			{
				return self::out(array('code'=>-1,'msg'=>'auth failed,secret key error'));
			}
		}
		else
		{
			//其他
		}

	}

	private function __initClient()
	{

		app::route('\/setcookie(\?*)?',function()
		{
			$this->setcookie();
		});

	}

	/**
	 * 若发送了redirect则地址跳转否则json或者jsonp响应.
	 */
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
				return header("Refresh: 0; url={$url}");
			}
			else
			{
				return self::out(array('code'=>0,'msg'=>'success','token'=>$token,'domain'=>self::$domain));
			}
		}
		else
		{
			if($redirect)
			{
				$url=$redirect."?msg=".urlencode("邮箱与密码不匹配");
				return header("Refresh: 0; url={$url}");
			}
			else
			{
				return self::out(array('code'=>-100,'msg'=>'邮箱与密码不匹配'));
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
	 * 客户端回调处理,登陆成功或失败重定向地址.
	 * 须客户端主动调用,返回数据须渲染到页面.
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
			return $msg;
		}
	}

	/**
	 * 跨域之setcookie请求,最好不要有输出.可以用script,img等请求
	 */
	function setcookie()
	{
		$token=isset($_GET['token'])?$_GET['token']:null;
		if($token and preg_match('/[a-z0-9]/', $token))
		{
			$expire=time()+86400*30;
			setcookie('token',$token,$expire,'/',$_SERVER['HTTP_HOST'],0,true);
			header('X-Setcookie:success');
		}
		else
		{
			header('X-Setcookie:error');
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
			//填写自己需要的字段
			$sql="SELECT uid,email,username,phone FROM `users` ";
			$user=DB::getLine($sql);
			if($user)
			{
				return self::out(array('code'=>0,'msg'=>'success','data'=>$user));
			}
		}
		return self::out(array('code'=>-10,'msg'=>'auth failed,token error'));

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