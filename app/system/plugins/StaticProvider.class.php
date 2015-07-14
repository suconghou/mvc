<?php

/**
* Static Provider
*/
class StaticProvider
{
	
	function __construct()
	{
		$this->overWriteRouter();
	}

	function overWriteRouter()
	{
		app::route('\/static\/(css|js)(?:\/(\w+))?',function($type,$name=null)
		{
			return $this->init($type,$name);
		});
	}

	function init($type,$name)
	{
		
	}


}