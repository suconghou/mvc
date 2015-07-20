<?php

/**
* image 图像处理
* 图像缩放,加水印,旋转,裁剪,缩略,验证码
* 生成验证码 $image->vcode($num,$rgb)
* 生成占位符 $image->placeholder($w,$h,$rgb)
* $image->thumb($path,$w,$h) //缩放图片,参数0-1缩略,1-2放大,$path可以是本地或者远程
* $cache true 检测缓存,false 不使用缓存 其他不使用缓存并且删除以前的缓存
* 建议开启http缓存使用 C(60); 缓存1个小时
*/
class Image 
{
	const dict='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; //产生验证码的字典
	private static $cachePath; //缓存路径

	function __construct($auto=false)
	{
		self::$cachePath=sys_get_temp_dir(); //缓存路径
		if($auto)//缩略图自动模式
		{
			$self=$this;
			app::route('\/images\/([\w=\+\/]+)\.(jpg|png|gif)',function($key=null) use($self){
				$self->AutoHandler($key);
			});//注册一个路由
			// 或者
			$this->AutoHandler();
		}

	}
	/**
	* 生成验证码随机数
	*/
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
	/**
	* 生成验证码,不能有其他输出
	*/
	public function  vcode($num=4,$font=null,$gb=null)
	{
		$size=20;
		$fontSize=20;
		$font='./static/font/MONACO.TTF';
		$this->vcode=self::random($num);
		$width=$size*$num;
		$height=($size+$fontSize)/1.2;
		if(is_null($font))
		{
			$font=6;//system font
		}
		$im = imagecreate($width,$height); // 画一张指定宽高的图片
		$bg = ImageColorAllocate($im, isset($gb[0])?$gb[0]:rand(220,255),isset($gb[1])?$gb[1]:rand(220,255),isset($gb[2])?$gb[2]:rand(220,255)); // 定义背景颜色
		for ($i=0; $i <$num ; $i++)
		{ 
			$a=rand(1,255);
			$randcolor = ImageColorAllocate($im,$a,255-$a,rand(1,255)); // 生成随机颜色
			$offset=$i==0?(mt_rand(1,$size/2)):($i*$size);
			if(is_numeric($font))
			{
				imagestring($im,$font,$offset,mt_rand(1,$size), $this->vcode[$i], $randcolor);
			}
			else
			{
				imagettftext($im,$fontSize,mt_rand(-$size/2,$size/2),$offset,mt_rand(min($height,$fontSize),max($height,$fontSize)),$randcolor,$font,$this->vcode[$i]);  
			}
		}
		//draw some other 
		for($i=0,$len=$height+$width; $i <$len ; $i++)
		{
			$randcolor = ImageColorAllocate($im,rand(50,255),rand(50,255),rand(50,255));
			if($i%57==0)
			{
				imageline($im,rand(1,$width),rand(1,$height),rand(1,$width),rand(1,$height),$randcolor);
			}
			else if($i%17==0)
			{
				imagesetpixel($im, rand(1,$width) , rand(1,$height), $randcolor); // 画像素点函数
			}
			else if(!is_numeric($font)&&$i%17==0)
			{
				imagestring($im,1,rand(1,$width) , rand(1,$height),chr(rand(1,127)), $randcolor);
			}
		}
		Header("Content-type: image/gif");
		ImageGif($im);
		ImageDestroy($im);
		return  $this->getVcode();
	}
	public function getVcode()
	{
		return isset($this->vcode)?$this->vcode:null;
	}
	/**
	* 生成图片占位符,需提供宽高,背景颜色
	*/
	function placeholder($w,$h,$gb=null)
	{
		Header("Content-type: image/PNG");
		$im=imagecreate($w,$h);
		$bg=ImageColorAllocate($im, isset($gb[0])?$gb[0]:200,isset($gb[1])?$gb[1]:200,isset($gb[2])?$gb[2]:200); // 定义背景颜色
		$randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255)); // 生成随机颜色
		$text=$w.'X'.$h;
		$font=ROOT.'static/font/monaco.ttf';
		$size=$w/5>$h/5?$h/5:$w/5;
		$size=$size>sqrt($w*$h)?sqrt($w*$h):$size;
		$arr=ImageTTFBBox($size, 0, $font,$text);
		$text_w=$arr[2]-$arr[0];
		$text_h=$arr[6]-$arr[7];
		$x=($w-$text_w)/2;
		$y=($h+$text_h)/2;
		ImageTTFText($im, $size,0,$x,$y,$randcolor,$font,$text);
		ImagePNG($im);
		ImageDestroy($im);

	} 
	/**
	* 图片采集与缩放
	* @param  $path 本地或远程地址
	* @param  $set_w 输出图片宽度 比例或者固定数值
	* @param  $set_h 高度
	* @param  $cache 是否启用缓存, true启用缓存,false不启用,其他值不启用缓存并清理缓存
	*/
	function thumb($path,$set_w=null,$set_h=null,$cache=false)
	{
		$hash=self::$cachePath.'/'.md5($path.$set_w.$set_h).'.thumb'; //缩略图的缓存地址
		if(!is_bool($cache)) //清除缓存
		{
			self::delCache($hash);
			$cache=false;
		}
		$cache&&self::readCache($hash); ///读取缩略图缓存
		if(preg_match('/http(s)?:\/\/([a-z0-9]+\.)+([a-z0-9]+\/)+.*/i',$path,$matches)) //远程地址
		{
			$origin=self::$cachePath.'/'.md5($path).'.origin';
			error_reporting(0);
			is_file($origin)?null:file_put_contents($origin,file_get_contents($path));    
			$path=$origin;
		}
		$arr=getimagesize($path); //原始图像大小 $type 1gif 2jpg 3png
		if(!$arr)
		{
			self::delCache($origin);
			return false;
		}
		$w=&$arr[0];
		$h=&$arr[1];
		$type=&$arr[2];
		$mime=&$arr['mime'];
		if(is_null($set_w)&&$set_h)
		{
			$set_w=$set_h<=2?$set_h:$set_h/$h;
		}
		else
		{
			$set_w=is_null($set_w)?1:$set_w; //默认宽度原图
			$set_h=is_null($set_h)?($set_w<=2?$set_w:$set_w/$w):$set_h; //没有设定,若宽度设定了百分比则继承否则计算出百分比
		}
		$real_w=intval($set_w<=2?$w*$set_w:$set_w);
		$real_h=intval($set_h<=2?$h*$set_h:$set_h);

		$new_img=imagecreatetruecolor($real_w,$real_h);

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
		header('Content-Type: '.$mime);
		$src_image=$fun[0]($path);
		imagecopyresampled($new_img,$src_image,0,0,0,0,$real_w,$real_h,$w,$h);
		$cache?($fun[1]($new_img,$hash)&&self::readCache($hash)):$fun[1]($new_img);
		imagedestroy($new_img);
		imagedestroy($src_image);
		return $this;
	}
	/**
	* 尝试读取缓存,命中直接输出
	*/
	private static function readCache($hash)
	{
		if(is_file($hash))
		{
			$arr=getimagesize($hash); //原始图像大小 $type 1gif 2jpg 3png
			$mime=&$arr['mime'];
			header('Content-Type: '.$mime);
			exit(readfile($hash));
		}
	}
	/**
	* 删除一个缓存
	*/
	private static function delCache($hash)
	{
		try
		{
			return is_file($hash)?unlink($hash):false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	/**
	 * 旋转图像
	 */
	function rotate($filepath,$degrees=90)
	{
		if(is_file($filepath))
		{
			$arr=getimagesize($filepath);
			$mime=&$arr['mime'];
			$type=&$arr[2];
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
			$source=$fun[0]($filepath);
			$rotate=imagerotate($source, $degrees, 16777215);///未覆盖的区域白色ffffff
			header('Content-Type: '.$mime);
			$fun[1]($rotate);

		}

	}
	/**
	 * 自动提供API
	 */
	function AutoHandler($key=null)
	{

		$src=Request::get('src');
		//自动识别URI模式
		if($key)
		{
			$str=base64_decode($key);
			parse_str($str,$data);
			$src=isset($data['src'])?$data['src']:null;
			$width=isset($data['width'])?$data['width']:null;
			$height=isset($data['height'])?$data['height']:null;
			if($src)
			{
				$this->thumb($src,$width,$height,true); //启用缓存
			}
		}
		else if($src)
		{
			$width=Request::get('width');
			$height=Request::get('height');
			$this->thumb($src,$width,$height,true); //启用缓存
		}

	}


}