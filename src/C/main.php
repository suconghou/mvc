<?php
/**
* 
*/
class main extends controller
{
	
	function __construct()
	{
		# code...
	}
	function index()
	{
		$this->cache(10);
		$this->loadview('index');
	}

}