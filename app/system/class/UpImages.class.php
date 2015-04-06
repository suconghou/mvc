<?php

/**
* UpImages
* using imagick
* 
*/
class UpImages
{
	const path='./uploads/';
	const maxSize=102400;

	private static $image;


	function __construct()
	{
		
	}

	// all run start
	function init($hash=null,$width=null,$height=null)
	{
		$this->loadImg($hash,$width,$height);
	}
	
	function upload($key='file',$width=120,$height=120)
	{
		if(isset($_FILES[$key])&&$_FILES[$key]['error']==0)
		{

		}
		else
		{
			$this->error(1);
		}
	}

	function loadImg($hash=null,$width=null,$height=null)
	{
		$path=self::path.substr($hash,0,2).'/'.$hash.'.jpg';
		if(is_file($path))
		{
			if($width&&$height)
			{
				$path=$this->resize($path,$width,$height);
			}
			$this->output($path,$hash);
		}
		else
		{
			$this->notfound($hash);
		}
	}
	function thumb($save=true)
	{

	}

	function resize($path,$width,$height)
	{
		self::$image=new Imagick($path);
		self::$image->resizeImage($width,$height,imagick::FILTER_LANCZOS, 0.9, true);
		self::$image->writeImage();
	}

	function output($path=null,$hash=null)
	{
		header("Content-Type: image/jpg");
		readfile($path);
	}

	function notfound($hash=null)
	{

	}

	private function error($flag=null)
	{
		$errors=array(

			);
		echo $errors[$flag];
	}

}