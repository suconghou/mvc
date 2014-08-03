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
     Validate::addRule('name','require|min-length:15');
     Validate::addRule('pass','require|min-length:15');
     Validate::addRule('email','require|min-length:105');
     $ret=Validate::check($data);
       var_dump($ret);
	}
	function test()
	{
		dump(Request::server());
	}

}

