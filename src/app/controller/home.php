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
        C(5);
       	V('index');
       
	}
	function test()
	{
		dump(Request::server());
	}

}

