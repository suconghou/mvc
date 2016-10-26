<?php

/**
* Pthread 多线程,线程池
*/

class Pthread extends Thread
{
	public $payload;
	public static $wait;

	public function __construct($payload)
	{
		$this->payload=$payload;    
	}

	public function run()
	{
		$payload=$this->payload;
		if(is_callable($payload))
		{
			$this->payload=$payload($this->getThreadId());
		}
	}

	public static function setWait(Closure $wait)
	{
		self::$wait=$wait;
	}

	public static function multi()
	{
		$wait=self::$wait;
		$threads=$result=array();
		foreach (func_get_args() as $id => $fun)
		{
			$threads[$id]=new self($fun);
			$threads[$id]->start();
		}
		$wait&&$wait();
		foreach ($threads as $id => $thread)
		{
			$thread->join();
			$result[$id]=$thread->payload;
		}
		return $result;
	}

	public static function multiArray(Array $params,$pool=null,Closure $callback=null)
	{
		if($pool)
		{
			return self::pool($params,$pool,$callback);
		}
		else
		{
			return call_user_func_array('self::multi',$params);
		}
	}

	public static function pool(Array $task,$maxSize=10,Closure $callback=null)
	{
		$pool=new Pool($maxSize);
		foreach ($task as $item)
		{
			$pool->submit(new poolJob($item));
		}
		$pool->shutdown();
		$pool->collect(function($checkingTask) use($callback)
		{
			$callback&&$callback($checkingTask->payload);
			return $checkingTask->isGarbage();
		});
		return true;
	}

	public static function async(Closure $fun)
	{
		$thread=new self($fun);
		$thread->start();
		$thread->join();
	}

	
}

class poolJob extends Collectable
{
	
	public $payload;

	public function __construct($payload)
	{
		$this->payload=$payload;
	}
	public function run()
	{
		$payload=$this->payload;
		if(is_callable($payload))
		{
			$this->payload=$payload();
		}
		$this->setGarbage();
	}
}

