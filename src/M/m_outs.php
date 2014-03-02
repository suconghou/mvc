<?php

/**
* 
*/
class m_outs extends model
{
	
	function __construct()
	{
		parent::__construct();
	}
	function give_log($workid,$text)
	{
		$time=time();
		$sql="INSERT INTO outs (`workid`,`text`,`time`) VALUES ('{$workid}','{$text}','{$time}')";
		$res=$this->runSql($sql);
	}
}