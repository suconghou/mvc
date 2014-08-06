<?php

/**
* 
*/
class home extends base
{
	
	function __construct()
	{
		
	}
	function index()
	{
      
      
       echo app::run('hello22');
	}
	function test()
	{
		dump(Request::server());
	}
	function hello2()
	{

    	 var_dump($GLOBALS['APP']);

		return '0022';
	}



}

