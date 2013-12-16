<?
/**
* 
*/
class user extends M
{
	
	function __construct()
	{
		parent::__construct();
	}

	function inser()
	{
		$sql="select * from info";

		$where=array('id'=>0002);
		$s=$this->select($sql)->fetch();
		var_dump($s);
		
	}
}