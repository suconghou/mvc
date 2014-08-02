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
     // var_dump(db::getData('select * from post'));
     $data=Request::post();
     Validate::addRule('name','');
     Validate::addRule('pass','');
     Validate::addRule('email','');
     $ret=Validate::check($data);
       var_dump($ret);
	}
	function test()
	{
		dump(Request::server());
	}

}

