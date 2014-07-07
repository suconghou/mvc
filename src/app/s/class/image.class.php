<?php

/**
* image 图像处理
* 图像缩放,加水印,旋转,裁剪,缩略,验证码
*/
class image 
{
	const dict='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; //产生验证码的字典
	
	function __construct()
	{
		
	}
	private static function random($num=4)
	{
		$len=strlen(self::dict);
		$dict=self::dict;
		$code=null;
		while($num--)
		{
			$code.=$dict[mt_rand(0,$len-1)];
		}
		return $code;
	}
	function  vcode($num=4)
	{
		
		Header("Content-type: image/PNG");
		$code=self::random($num);
		$width=10*$num+10;
		$im = imagecreate($width,30); // 画一张指定宽高的图片
		$back = ImageColorAllocate($im, 255,255,255); // 定义背景颜色
		for ($i=0; $i <$num ; $i++)
		{ 
			$randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255)); // 生成随机颜色
			imagestring($im,6, 5+$i*10, 10, $code[$i], $randcolor);
		}
		for ($j=0,$len=$width*2; $j <$len ; $j++)
		{ 
			$randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
    		imagesetpixel($im, rand(1,$width) , rand(1,30), $randcolor); // 画像素点函数
		}
		ImagePNG($im);
     	ImageDestroy($im);
     	if(!isset($_SESSION))
     	{
     		session_start();
     	}
     	$_SESSION['VCODE']=$code;

	} 
}