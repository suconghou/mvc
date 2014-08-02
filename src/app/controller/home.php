<?php

/**
* 
*/
class home 
{
	
	function __construct()
	{
		
	}
	function index()
	{
       
     var_dump($GLOBALS['APP']);
       
	}
	function test()
	{
		dump(Request::server());
	}

}

