<?php

/**
* 框架命令行工具
*/
final class artisan extends base
{
	
	public function __construct()
	{
		self::onlyCli();
	}

	public function index()
	{

	}

	public function release($update=true)
	{
		function_exists('opcache_reset')&&opcache_reset();
		if(substr(ROOT,0,7)!='phar://')
		{
			$update&&$this->update(0,true);
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

	public function deploy()
	{
		$host='ftp://user:password@example.com/public_html';
		$script=array_shift($_SERVER['argv']);
		$pharName=rtrim($script,'php').'phar';
		$cmd="php {$script}";
		passthru($cmd);
		echo 'uploading...'.PHP_EOL;
		$ret=Curl::sendToFtp($host,$pharName,$script);
		echo ($ret?'upload success':'upload error').' cost time '.app::cost('time').'s'.PHP_EOL;
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