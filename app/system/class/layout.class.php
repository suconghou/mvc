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
	/**
	 * 生成加载css地址
	 */
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
					$css_link.='<link rel="stylesheet" href="/static/css/'.$v.'.css">';
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
					$js_link.='<script src="/static/js/'.$v.'.js"></script>';
				}
			}
			return $js_link;
		}
		return null;
	}
	static function title($title=null,$default=null)
	{
		if($title||$default)
		{
			return '<title>'.$title?$title:$default.'</title>';
		}
		return null;
	}
	static function h($data,$class=null,$id=null)
	{
		$data=is_array($data)?$data:array($data);
		$h=null;
		$class=$class?" class=\"{$class}\" ":null;
		$id=$id?" id=\"{$id}\" ":null;
		foreach ($data as $key => $value)
		{
			$key++;
			$h.="<h{$key}{$class}{$id}>{$value}</h{$key}>";
		}
		return $h;

	}
	static function pager($total,$per=10,$class=null)
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
	static function menu($data,$current=null,$class=null,$liclass='iblock',$aclass='block',$id=null)
	{
		return '<nav>'.ul($data,$current,$class,$liclass,$aclass,$id).'</nav>';
	}
	
	static function sidebar()
	{
		

	}
	static function lists()
	{
		

	}

}

// 几个布局函数

/**
 * <a href=''>$title</a>
 */
function anchor($link,$title=null,$class=null,$id=null,$new=null)
{
	$title=$title?$title:$link;
	$new=$new?" target=\"_blank\" ":null;
	$tit=strip_tags($title)?" title=\"".strip_tags($title)."\" ":null;
	$class=$class?" class=\"{$class}\" ":null;
	$id=$id?" id=\"{$id}\" ":null;
	return "<a href=\"{$link}\"{$tit}{$class}{$id}{$new}>".$title."</a>";
}
/**
 * img 生成函数
 */
function img($src,$alt=null,$class=null,$id=null)
{
	$alt=$alt?" alt=\"{$alt}\" ":null;
	$class=$class?" class=\"{$class}\" ":null;
	$id=$id?" id=\"{$id}\" ":null;
	return "<img src=\"{$src}\"{$alt}{$class}{$id}>";
}
/**
 * ul生成函数
 */
function ul($data,$current=null,$class=null,$liclass=null,$aclass=null,$id=null)
{
	$class=$class?" class=\"{$class}\" ":null;
	$id=$id?" id=\"{$id}\" ":null;
	$liclass=$liclass?" class=\"{$liclass}\" ":null;
	$html="<ul {$class}{$id}>";
	foreach ($data as $key => $value)
	{
		$active=$current==$value?" class=\"active\" ":null;
		if(is_numeric($key))
		{
			$html.="<li{$liclass}{$active}>{$value}</li>";
		}
		else
		{	
			$current=$current==$value?' active':null;
			$aaclass=$aclass?" class=\"{$aclass}{$current}\" ":$active;
			$html.="<li{$liclass}><a href=\"{$key}\"{$aaclass}>{$value}</a></li>";
		}
	}
	$html.="</ul>";
	return $html;
}


/**
 * select 生成函数
 */
function select($data,$current=null,$name=null,$class=null,$id=null)
{
	$name=$name?" name=\"{$name}\" ":null;
	$class=$class?" class=\"{$class}\" ":null;
	$id=$id?" id=\"{$id}\" ":null;
	$html="<select {$name}{$class}{$id} >";
	foreach ($data as $key => $value)
	{
		$current=$key==$current?" selected":null;
		$html.="<option value=\"{$key}\"{$current}>{$value}</option>";
	}
	$html.="</select>";
	return $html;
}

