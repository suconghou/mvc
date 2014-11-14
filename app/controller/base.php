<?php
/**
* 基础控制器类,继承此类以获得请求拦截
* 该控制器不包含输出操作
* 包含条件过滤等
* IP黑名单
* REFER黑名单
* GET,POST,SESSION,频次过滤
* 非ajax过滤
* 非POST过滤
* 开启ajax跨域
* 自定义过滤条件
* 辅助函数
* 在其他控制器中调用 $this->ip()->refer()->session()->post()->get()->defender();
*/
class base 
{
	private static $ip;
	private static $refer; //限制refer,包含关系也会限制
	private static $frequency; //频次
	private static $get;
	private static $post;
	private static $session;
	private static $cookie;
	private static $ajax;

	private static $blockTime=10; //超出频次禁止时间

	
	function __construct()
	{
		if(baseUrl(0)==__CLASS__)die; //此控制器不能通过URl访问
		$this->auto()->defender(); //开启自动过滤
		$this->globalIndex(); //全局加载的
	}
	function index(){}
	function globalIndex()
	{
		  $menu=config('topmenu');
          if($user=isUserLogin())
          {
            array_pop($menu);
            array_pop($menu);
            $menu["/u/{$user['id']}"]=$user['name'];
          }
         config('topmenu',$menu); 
	}

	/**
	 * 可以设置,自动过滤的内容
	 */
	private function auto($use=false)
	{
		if($use)
		{
			$ip=array('127.0.0.10'); //设定自动过滤IP
			$refer=array('http://127.0.0.1'); //设定自动过滤refer
			$this->frequency(5,1)->refer($refer)->ip($ip);

		}
	
		
		return $this; 
	}
	/**
	 * 开启跨域资源共享
	 */
	function cors($domain=null)
	{
		if($domain)
		{
			header("Access-Control-Allow-Origin:{$domain}");
		}
		else
		{
			header('Access-Control-Allow-Origin:*');
		}
	}
	/**
	 * 是否已登录用户,登陆返回其中信息,否则返回json或跳转
	 */
	function isLogin($user='USERID',$addr='/')
	{
		$ret=$this->session($user)->defender(true); //没有此SESSION则返回错误信息而不是直接过滤
		if($ret) //没有此SESSION
		{
			if(Request::isAjax())
			{
				exit(json_encode(array('code'=>-1,'msg'=>'please login !')));
			}
			else
			{
				redirect($addr);
			}
		}
		return session($user);
	}
	/**
	 * 阻断非CLI请求
	 */
	function cli()
	{
		Request::isCli()||exit;
		return $this;
	}
	function get($arr)
	{
		self::$get=is_array($arr)?$arr:array($arr);
		return $this;
	}
	function post($arr)
	{
		self::$post=is_array($arr)?$arr:array($arr);

		return $this;
	}
	function session($arr)
	{
		self::$session=is_array($arr)?$arr:array($arr);
		return $this;
	}
	function cookie($arr)
	{
		self::$cookie=is_array($arr)?$arr:array($arr);
		return $this;
	}
	function ip($arr)
	{
		self::$ip=is_array($arr)?$arr:array($arr);
		return $this;
	}
	function refer($arr)
	{
		self::$refer=is_array($arr)?$arr:array($arr);
		return $this;
	}
	function ajax($a=true)
	{
		self::$ajax=$a;
		return $this;
	}
	/**
	 * 关联数组,或者一个整数 $f['5']=20; 5秒20次 , 次数不宜设置太大
	 * $ip 限定此IP的频次还是此浏览器(session)频次,依据IP可以防止抓取
	 */
	function frequency($arr=15,$ip=false)
	{
		self::$frequency=is_array($arr)?$arr:array('5'=>$arr);
		$this->frequencyIp=$ip;
		return $this;
	}
	function defender($ret=false) //过滤动作
	{
		$info=Request::info();

		if(self::$ip)
		{
			if(in_array($info['ip'],self::$ip))
			{
				$hit['ip']=&$info['ip'];
				goto block;
			}
		
		}
		if(self::$ajax)
		{
			if(!$info['ajax'])
			{
				$hit['ajax']=&$info['ajax'];
				goto block;
			}
		}
		if(self::$refer)
		{
			while(list($k,$v)=each(self::$refer))
			{
				if(stripos($info['refer'],$v)!==false)
				{
					$hit['refer']=&$info['refer'];
					goto block;
				}
			}

		}
		if(self::$session)
		{
			$session=Request::session();
			while(list($k,$v)=each(self::$session))
			{
				if(!isset($session[$v]))
				{
					$hit['session']=$v;
					goto block;
				}
			}
		}
		if(self::$cookie)
		{
			$cookie=Request::cookie();
			while(list($k,$v)=each(self::$cookie))
			{
				if(!isset($cookie[$v]))
				{
					$hit['cookie']=$v;
					goto block;
				}
			}	
		}
		if(self::$post)
		{
			$post=Request::post();
			while(list($k,$v)=each(self::$post))
			{
				if(!isset($post[$v]))
				{
					$hit['post']=$v;
					goto block;
				}
			}	

		}
		if(self::$get)
		{
			$get=Request::get();
			while(list($k,$v)=each(self::$get))
			{
				if(!isset($get[$v]))
				{
					$hit['get']=$v;
					goto block;
				}
			}	

		}
		if(self::$frequency)
		{
			session_start();
			if($this->frequencyIp) //依据IP
			{
				$this->current_id=session_id();
	    		session_write_close();
				$host=md5(Request::ip());
	    		session_id($host);
			}
			$ssid='frequency';
			$data=json_decode(session($ssid),1);
			$data[$ssid][]=APP_START_TIME;
			list($k,$v)=each(self::$frequency);
			$size=count($data[$ssid]);
			if($size>$v)
			{
				$sec=APP_START_TIME-$data[$ssid][$size-$v];
				if($sec<$k)
				{
					$data['block']=APP_START_TIME+self::$blockTime; ///超出限制
				}
				if($size>$k*$v+20)
				{
					unset($data[$ssid]);
				}
			}
			if(isset($data['block'])) //阻挡此请求
			{
				if($data['block']<APP_START_TIME)
				{
					unset($data['block']);
				}
				else
				{
					session($ssid,$data);
					$t=intval($data['block']-APP_START_TIME);
					if($this->frequencyIp)
					{
						session_write_close();
						session_id($this->current_id); //还原
						session_start();
					}
					$this->frequencyBlock($t); //执行拦截
					exit;
				}
			}
			session($ssid,$data); //未达到拦截要求,记录数据
			if($this->frequencyIp)
			{
				session_write_close();
				session_id($this->current_id); //还原
				session_start();
			}
			
		}
		block:		
		if(isset($hit))
		{
			if($ret)
			{
				return $hit;
			}
			else
			{
				$this->block($hit);					
			}
		}
		else
		{
			return null;
		}
	}
	/**
	 * 命中预定设置
	 * @param $hit , 因何原因,是一个数组
	 */
	private function block($hit)
	{
		list($k,$v)=each($hit);
		http_response_code(403);
		exit('禁止'.$k.$v);	
	}
	/**
	 * 超过最高频次设置
	 * @param $t 还有多少秒后恢复正常访问
	 */
	private function frequencyBlock($t)
	{
		http_response_code(403);
		exit('禁止'.$t);
	}

	function Error404($msg)
	{
		echo $msg;
	}
	function Error500($msg)
	{
		echo $msg;
	}

	#################################用户自定义扩展############################################
	/**
	 *  检查用户是否登录
	 */
	function isUserLogin($addr='/')
	{
		return $this->isLogin('USERID',$addr);
	}

	/**
	 * 检查管理员是否登录
	 */
	function isAdminLogin($addr='/')
	{
		return $this->isLogin('ADMINID',$addr);
	}

	
}