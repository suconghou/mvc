<?php
/**
* 程序消耗分析器
*/
class Faster
{

	private static $event;

	function __construct()
	{
	
	}
	function add($fun)
	{
		$id=uniqid().rand(100,999);
		self::$event[$id]=$fun;
		return $this;
	}
	function run($times=1)
	{
		$log=array();
		foreach(self::$event as $id=>$fun)
		{
			$begin=array(microtime(true),memory_get_usage());
			for($i=0;$i<$times;$i++)
			{
				$fun($i);
			}
			$end=array(microtime(true),memory_get_usage());
			$log[$id]=array($begin,$end);
		}
		$this->makeLog($log);
	}


	private function makeLog($log)
	{
		if(empty($log))
		{
			return false;
		}
		foreach($log as $id=>$result)
		{
			list($begin,$end)=$result;
			$timeSpent=$end[0]-$begin[0];
			$memorySpent=$end[1]-$begin[1];
			echo "{$id}:Time Spent {$timeSpent} , Memory Spent {$memorySpent}",PHP_EOL;
		}

	}

}