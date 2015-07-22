<?php

/**
* 框架命令行工具
*/
final class artisan extends base
{
	
	public function __construct()
	{
		$this->onlyCli();
	}

	public function index()
	{

	}

	public function release($update=true)
	{
		function_exists('opcache_reset')&&opcache_reset();
		if(substr(__FILE__,0,4)!='phar')
		{
			$update?$this->update(0,true):null;
			$script=$_SERVER['argv'][0];
			$subject=file_get_contents($script);
			$pattern='/base::version\((\d+)\)/';
			$data=preg_replace_callback($pattern,function($matches)
			{
				$version=$matches[1]+1;
				echo PHP_EOL."new version {$version}".PHP_EOL;
				return "base::version({$version})";
			},$subject);
			return $data==$subject?false:file_put_contents($script,$data);
		}
	}

	public function update($time=60,$once=false)
	{
		app::timer(function() use($time)
		{
			try
			{
				$this->gitpull('password');
			}
			catch(Exception $e)
			{
				echo $e->getMessage();
			}
			sleep($time);
		},$once);

	}

}