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
			self::$timeout=isset($cfg['timeout'])?$cfg['timeout']:Request::serverInfo('max_exectime')-10;
			
		}
		$this->init();
	}
	function init()
	{
		echo "init<br>";
	}
	function event($event,$function)
	{
		self::$events[$event]=$function;
		return $this;
	}
	function timer()
	{
		$i=0;
		while ( $i<= 10)
		{
			$fun=current(self::$events);
			$fun();
			sleep(1);
			$i++;
		}
	}
	function loop()
	{
		$this->timer();
	}
	function __destruct()
	{
		var_dump(self::$events);
	}
}