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
* 在其他控制器中调用 $this->ip()->refer()->session()->post()->get()->defender();
*/
abstract class base 
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
		$this->auto()->defender(); //开启自动过滤
	}

	/**
	 * 可以设置,自动过滤的内容
	 */
	private function auto()
	{
		if(baseUrl(0)==__CLASS__)die;

		$ip=array('188.0.0.1');
		$refer=array('http://127.0.0.1');
		$this->frequency(8)->refer($refer);
		
		return $this; 
	}
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
	 */
	function frequency($arr=15)
	{
		self::$frequency=is_array($arr)?$arr:array('5'=>$arr);
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
			var_dump($info);
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
		
			$ssid='frequency';
			$data=json_decode(session_get($ssid),1);
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
				if($size>($k+1)*($v+1))
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
					session_set($ssid,$data);
					$t=intval($data['block']-APP_START_TIME);
					$this->frequencyBlock($t);
					exit;
				}
			}
			session_set($ssid,$data);
		
			
			
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
	 * @param $hit , 因何原因
	 */
	private function block($hit)
	{
		var_dump('禁止',$hit);
		list($k,$v)=each($hit);
		http_response_code(403);
		exit;	
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
	
}