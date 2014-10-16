<?php
/**
* long polling for ajax and event 
*/
class polling
{
	private static $events;
	private static $timeout;
	
	function __construct($cfg=null)
	{
		if(is_array($cfg))
		{
			self::$timeout['time']=isset($cfg['timeout'])?$cfg['timeout']:Request::serverInfo('max_exectime')/100;
		}
		else
		{
			self::$timeout['time']=8;
		}
		$this->init();
	}
	function init()
	{

	}
	/**
	 * 添加事件
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
	 * 超时处理
	 */
	function timeout($fun)
	{
		self::$timeout['fun']=$fun;
		$this->timer(1);
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
			session_set($key,1);
		}
		return $this;

	}
	/**
	 * 开始轮询
	 */
	function loop($time=1)
	{
		$this->timer($time);die;
		return $this;
	}

	private  function timer($time=1)
	{
		$i=0;
		$count=self::count();
		$t=intval($time*1000000/$count);
		while ($i<self::$timeout['time'])
		{
			foreach (self::$events['global'] as $event => $fun)
			{
				if($this->globalEvent($event))
				{
					exit($fun());
				}
				usleep($t);
			}
			foreach (self::$events['session'] as $event => $fun)
			{	
				if($this->sessionEvent($event))
				{
					exit($fun());
				}
				usleep($t);
			}
			$i=$i+$time;
		}
		return self::timeoutEvent();

	}
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
	
	private static function timeoutEvent()
	{
		$fun=self::$timeout['fun'];
		exit($fun());
	}
	private function sessionEvent($event)
	{
		$key='event-'.$event;
		if(Request::session($key))
		{
			session_set($key,0);
			return true;
		}
		return false;
	}
	private function globalEvent($event)
	{
		$key='event-'.$event;
		if(app::get($key))
		{
			app::set($key,0);
			return true;
		}
		return fasle;
	} 
	
	function __destruct()
	{
		
	}
}