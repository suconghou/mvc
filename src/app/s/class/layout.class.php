<?php
/**
* 布局模板,数据排版
* 提供静态方法
* 几大组件
* css ,js, title, ul ,ol 
*
*/
class layout
{

		
	function __construct()
	{
		// var_dump($GLOBALS['APP']);
	}
	function init()
	{

	}
	function __call($method,$x)
	{
		exit('Error Method '.$method.'  Called ! ');
	}
	static function css($css=null)
	{
		if($css)
		{
			$css_link=null;
			$css=is_array($css)?$css:array($css);
			foreach ($css as $v)
			{
				if(substr($v,0,4)=='http')
				{
					$css_link.='<link rel="stylesheet" href="'.$v.'">';
				}
				else
				{
					$css_link.='<link rel="stylesheet" href="/static/css/'.$v.'">';
				}
			}
			return $css_link;
		}
		return null;
	} 
	static function js($js=null)
	{
		if($js)
		{
			$js_link=null;
			$js=is_array($js)?$js:array($js);
			foreach ($js as $v)
			{
				if(substr($v,0,4)=='http')
				{
					$js_link.='<script src="'.$v.'"></script>';
				}
				else
				{
					$js_link.='<script src="/static/js/'.$v.'"></script>';
				}
			}
			return $js_link;
		}
		return null;
	}
	static function title($title=null)
	{
		if($title)
		{
			return '<title>'.$title.'</title>';
		}
		return null;
	}
	private static function ul_ol($data,$i_class=null)
	{
		$data=is_array($data)?$data:array($data);
		$ul_ol=null;
		$i_class=$i_class?" class='{$i_class}' ":null;
		foreach ($data as $key => $value)
		{
			$ul_ol.='<li'.$i_class.'>'.$value.'</li>';
		}
		return $ul_ol;

	}
	static function ul($data,$ul_class=null,$li_class=null)
	{
		$ul=self::ul_ol($data,$li_class);
		$ul_class=$ul_class?" class='{$ul_class}' ":null;
		return '<ul'.$ul_class.'>'.$ul.'</ul>';

	}
	static function ol($data,$ol_class=null,$li_class=null)
	{
		$ol=self::ul_ol($data,$li_class);
		$ol_class=$ol_class?" class='{$ol_class}' ":null;
		return '<ol'.$ol_class.'>'.$ol.'</ol>';
	}
	static function pager($total,$per=10,$class=null)
	{
		$page
	}

	private static function img()
	{

	}
	private static function a()
	{

	}
	static function header($css=null,$js=null,$title=null)
	{
		

	}
	static function footer($css=null,$js=null)
	{
		$js=self::js($js);

		$footer='</body></html>';
		return $footer;

	}
	static function menu()
	{
		

	}
	
	static function sidebar()
	{
		

	}
	static function lists()
	{
		

	}

}