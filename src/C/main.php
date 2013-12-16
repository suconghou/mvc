<?

/**
* 
*/
class main extends C
{
	
	function index()
	{
		$this->model('user');

		$s=$this->user->insert();
		//$this->user->sql2();
		//var_dump($s);
	}

	function hh()
	{
		$this->cache(5);
		$data=array('aa'=>'fg');

		$this->view('1',$data);

	}

	function ss()
	{
		$this->view('1');
	}

	function test()
	{
		$this->view();
		$this->view();
		$this->load();
		$this->cache();

		$this->uri();
		$this->clean();

		base_url();
		redirect();
	}
}