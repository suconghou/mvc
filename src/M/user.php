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

	function insert()
	{

		$where=array('car_id'=>'0002');
		$s=$this->select('*')->where($where)->from('info')->debug(0)->fetchs();
		
		while($b=$s->fetch())
		{
		var_dump($b);


		}
		
		
		
		
	}

	function sql2()
	{
		$sql="select *  from users";
		$s=$this->query($sql)->fetchall();
		var_dump($s);

	}
}