<?php
/**
* long polling for ajax and event 
*/
class polling
{
	private static $events;
	private static $timeout;
	
	function __construct($timeout=null)
	{
		$max_exectime=Request::serverInfo('max_exectime');
		self::$timeout['time']=is_null($timeout)?$max_exectime/2:min($timeout,$max_exectime);
		$this->init();
	}
	function init()
	{

	}
	/**
	 * 添加事件,可注册多个事件
	 */
	function event($event,$function,$global=false)
	{
		if($global)
		{
			self::$events['global'][$event]=$function;
		}
		else
		{
			self::$events['session'][$event]=$function;
		}
		return $this;
	}
	/**
	 * 超时处理,只有一个事件
	 */
	function timeout($fun)
	{
		self::$timeout['fun']=$fun;
		return $this;
	}
	/**
	 * 触发一个事件
	 */
	function on($event,$global=false)
	{
		$key='event-'.$event;
		if($global)
		{
			app::set($key,1);
		}
		else
		{
			session($key,1);
		}
		return $this;

	}
	/**
	 * 开始轮询
	 */
	function loop($time=1)
	{
		$this->timer($time);
		return $this;
	}
	/**
	 * 执行一段轮询
	 */
	private  function timer($time=1)
	{
		$maxTime=self::$timeout['time']*1000000; //最大时间,微秒
		$circle=$time*1000000; //一个大周期
		$count=self::count(); //事件数
		$t=intval($circle/$count);
		$i=0;
		while ($i<$maxTime)
		{
			foreach (self::$events['session'] as $event => $fun)
			{
				if($this->sessionEvent($event))
				{
					exit($fun());
				}
				else if($i>=$maxTime)
				{
					break;
				}
				usleep($t);
				$i=$i+$t;
			}
			foreach (self::$events['global'] as $event => $fun)
			{	
				if($this->globalEvent($event))
				{
					exit($fun());
				}
				else if($i>=$maxTime)
				{
					break;
				}
				usleep($t);
				$i=$i+$t;
			}
			if($i>=$maxTime)
			{
				break;
			}
		}
		return self::timeoutEvent();

	}
	/**
	 * 返回所有事件数
	 */
	private static function count()
	{
		if(!isset(self::$events['global']))
		{
			self::$events['global']=array();
		}
		if(!isset(self::$events['session']))
		{
			self::$events['session']=array();
		}
		$count=count(self::$events['global'])+count(self::$events['session']);
		return $count;
	}
	/**
	 * 超时后执行
	 */
	private static function timeoutEvent()
	{
		$fun=self::$timeout['fun'];
		exit($fun());
	}
	/**
	 * 检测一个局部事件是否到来
	 */
	private function sessionEvent($event)
	{
		$key='event-'.$event;
		if(Request::session($key))
		{
			session($key,0);
			return true;
		}
		return false;
	}
	/**
	 * 检测一个全局事件是否到来
	 */
	private function globalEvent($event)
	{
		$key='event-'.$event;
		if(app::get($key))
		{
			app::set($key,0);
			return true;
		}
		return false;
	} 
	
	function __destruct()
	{
		
	}
}