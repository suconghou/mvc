<?php
/**
* 
*/
class m_work extends model
{
	
	function __construct()
	{
		parent::__construct();
	}
	function add_work($url,$type,$time)
	{
		$sql="select * from work";
		$res=$this->getData($sql);
		var_dump($res);
		die;
	} 
	function del_work($id)
	{

	}
	function update_work()
	{

	}
	//type 1 
	function get_nores()
	{
		$sql="select url from work where type ='1'";
		$res=$this->getData($sql);
		return $res;
	}
	//type 2
	function get_header()
	{
		$sql="select id,url from work where type='2'";
		$res=$this->getData($sql);
		return $res;
	}
	//type 3
	function get_body()
	{
		$sql="select id,url from work where type='3'";
		$res=$this->getData($sql);
		return $res;
	}
	 //type 4
	function get_header_body()
	{
		$sql="select id,url from work where type='4'";
		$res=$this->getData($sql);
		return $res; 
	}
}