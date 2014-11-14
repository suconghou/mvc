<?php

/**
* 
*/
class imger 
{
	
	function __construct()
	{
		header('Access-Control-Allow-Origin:*');
		$this->overWriteRouter();
	}

	function overWriteRouter()
	{
		app::route('\/imger\/?',function(){
			$this->receiveImg();
		});

		app::route('\/imgout',function(){
			$this->imgout();
		});
	}

	function receiveImg()
	{
		$img=Request::post('img');
		if($img)
		{
			$img=explode(';',$img);
			$db=S('class/kvdb','img');
			$db->set('img',$img);
		}
	}

	function imgout()
	{
		$db=S('class/kvdb','img');
		$a=$db->get('img');
		var_dump($a);

	}

}


/**
* 
*/
class imger_db extends db
{
	
	function __construct()
	{
		
	}


}