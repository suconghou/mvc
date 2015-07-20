<?php

/**
* UpImages
* 缩略图服务,图片中转,密匙验证上传.
* 
*/
class UpImages
{
	//上传的存储路径
	const path='./';
	//最大允许上传大小,单位M
	const maxSize=2;


	function __construct($auto=false)
	{
		if($auto)
		{
			$this->__autoHandler();
		}
	}

	/**
	 * 路由自动注册，检测
	 */
	private function __autoHandler()
	{
		app::route('\/([a-z0-9]{32})\/(\d{1,4})x(\d{1,4})\.(jpe?g|png|gif)',function($origin,$width,$height)
		{
			return $this->init($origin,$width,$height);
		});
	}
	
	// all run start
	function init($origin=null,$width=null,$height=null)
	{
		$filename=self::path.$origin.'.jpg';
		if(is_file($filename))
		{
			return $this->loadImg($origin,intval($width),intval($height));
		}
		else
		{
			return self::error($filename." not found !");
		}
	}

	/**
	 * 手动调用上传接口,监视文件上传和URL上传
	 */
	function uploadHandler($f='file')
	{
		$width=isset($_GET['w'])?intval($_GET['w']):null;
		$height=isset($_GET['h'])?intval($_GET['h']):null;
		if(!empty($_GET['url']))
		{
			$data=$this->uploadByUrl($_GET['url'],$width,$height);
		}
		else
		{
			$data=$this->upload($f,$width,$height);
		}
		return self::json($data);
	}

	
	//若指定宽或者高,则触发缩放
	function upload($f='file',$width=null,$height=null)
	{
		if(isset($_FILES[$f])&&$_FILES[$f]['error']==0)
		{
			$tmpname=$_FILES[$f]['tmp_name'];
			if($_FILES[$f]['size']<self::maxSize*1024*1024)
			{
				if($imgInfo=getimagesize($tmpname))
				{
					$hash=md5_file($tmpname);
					$dir=self::path.$hash;
					is_dir($dir) or mkdir($dir,0777,true);
					$destination=$dir.".jpg";
					move_uploaded_file($tmpname, $destination);
					if($width||$height)
					{
						$newpath=self::path.$hash."/{$width}x{$height}.jpg";
						self::resize($destination,$width,$height,$newpath);
					}
					return array(
							'hash'=>$hash,
							'path'=>$destination,
							'size'=>$_FILES[$f]['size'],
							'width'=>$imgInfo[0],
							'height'=>$imgInfo[1],
							'type'=>$imgInfo[2]
						);
				}
				else
				{
					unlink($tmpname);
					return self::error('only images file allowed');
				}
			}
			else
			{
				unlink($tmpname);
				return self::error('file is too large');
			}
			
		}
		else
		{
			return self::error('no file upload');
		}
	}

	function uploadByUrl($url,$width=null,$height=null)
	{
		if(!filter_var($url,FILTER_VALIDATE_URL))
		{
			return self::error('error url'.$url);
		}
		$data=file_get_contents($url);
		if(!$data)
		{
			return self::error('url upload failed '.$url);
		}
		$size=strlen($data);
		if($size>self::maxSize*1024*1024)
		{
			return self::error('file is too large');
		}
		$hash=md5($data);
		$dir=self::path.$hash;
		$destination=$dir.".jpg";
		file_put_contents($destination, $data);
		if($imgInfo=getimagesize($destination))
		{
			is_dir($dir) or mkdir($dir,0777,true);
			if($width||$height)
			{
				$newpath=self::path.$hash."/{$width}x{$height}.jpg";
				self::resize($destination,$width,$height,$newpath);
			}
			return array(
					'hash'=>$hash,
					'path'=>$destination,
					'size'=>$size,
					'width'=>$imgInfo[0],
					'height'=>$imgInfo[1],
					'type'=>$imgInfo[2]
				);
		}
		else
		{
			unlink($destination);
			return self::error('only images file allowed');
		}
	}
	/**
	 * 加载一个已存在的图像
	 */
	function loadImg($hash=null,$width=null,$height=null)
	{
		$path=self::path.$hash.'.jpg';
		$newpath=self::path.$hash."/{$width}x{$height}.jpg";
		if(!is_file($newpath))
		{
			if($width||$height)
			{
				$newpath=self::resize($path,$width,$height,$newpath);
			}
			else
			{
				$newpath=$path;
			}
		}
		return self::output($newpath,$hash);
	
	}


	/**
	 * 兼容imagick 与gd2 的 图像缩放
	 */
	private static function resize($path,$width,$height,$newpath)
	{
		if(class_exists('Imagick'))
		{
			return self::imagickResize($path,$width,$height,$newpath);
		}
		else
		{
			return self::gdResize($path,$width,$height,$newpath);
		}
		
	}

	private static function imagickResize($path,$width,$height,$newpath)
	{
		$image=new Imagick($path);
		$image->resizeImage($width,$height,imagick::FILTER_LANCZOS, 0.9, true);
		$image->writeImage($newpath);
		return $newpath;
	}

	private static function gdResize($path,$width,$height,$newpath)
	{
		list($w,$h,$type)=getimagesize($path);
		if($width&&!$height)
		{
			$height=$h*($width/$w);
		}
		else if(!$width&&$height)
		{
			$width=$w*($height/$h);
		}
		$newimg=imagecreatetruecolor($width,$height);
		switch ($type)
		{
			case 1: //gif
			$fun=array('imagecreatefromgif','imagegif');
			break;
			case 2: //jpg
			$fun=array('imagecreatefromjpeg','imagejpeg');
			break;
			default: //png
			$fun=array('imagecreatefrompng','imagepng');
			break;
		}
		$srcimg=$fun[0]($path);
		imagecopyresampled($newimg,$srcimg,0,0,0,0,$width,$height,$w,$h);
		$fun[1]($newimg,$newpath);
		imagedestroy($newimg);
		imagedestroy($srcimg);
		return $newpath;
	}

	private static function output($path=null,$hash=null)
	{
		header("Content-Type: image/jpg");
		return readfile($path);
	}

	private static function error($msg=null)
	{
		return self::json(array('msg'=>$msg,'code'=>-1));
	}

	private static function json($arr)
	{
		exit(json_encode($arr));
	}

}