<?php
/**
* 布局模板,数据排版
* 提供静态方法
* 几大组件
* css ,js, title, ul ,ol 
* 配置数据库映射
* layout::easyList(1,3) 加载第一页,每页3个
* layout::easyPost(1) 显示第一篇文章
*
*/
class layout
{
	// 数据库 字段映射,一个表一个数组,数组第一个表名,以后为各个字段与数组的映射,修改键值以匹配数据库
	private static $post=array('wp_posts','id'=>'id','title'=>'post_title','content'=>'post_content','views'=>'comment_count','time'=>'time'); 
	private static $cat=array();
		
	/**
	 * 参数合并
	 * @param array $cfg [传入合并的参数]
	 */
	function __construct($cfg=array())
	{
		self::init($cfg);
	}
	function __call($method,$args)
	{
		Error(500,'Call Error Method '.$method.' In Class '.__CLASS__.' ! ');
	}
	static function __callStatic($method,$args)
	{
		Error(500,'Call Error Static Method '.$method.' In Class '.__CLASS__.'!');
	}
	static function init($cfg=array())
	{
		if($cfg)
		{
			foreach ($cfg as $key => $arr)
			{
				if(isset(self::$$key))
				{
					self::$$key=$arr;
				}
			}
		}
	}
	static function data($method,$param_arr=array())
	{
		return call_user_func(array('db',$method), $param_arr);
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
	static function pager($link='?p=',$total=5,$current=1,$step=1,$class='pager',$id=null)
	{
		$class=$class?" class=\"{$class}\" ":null;
		$id=$id?" id=\"{$id}\" ":null;
		$html="<div{$class}{$id}><ul>";
		$start=1; 
		while($start <=$total)
		{ 
			$aclass=$start==$current?"active":null;
			$pageText=$start;
			if($pageText==1) $pageText='首页';
			if($pageText==$total) $pageText='尾页 ';
			$html.="<li>".anchor($link.$start,$pageText,$aclass)."</li>";	
			$start=$start+$step;
		}
		$html.="</ul></div>";
		return $html;

		
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
	/**
	 * 遍历二维数组,形成ul>li模式或ul>li>a模式
	 */
	static function lists($list,$class=null,$id=null,$liclass=null)
	{
		$class=$class?" class=\"{$class}\" ":null;
		$id=$id?" id=\"{$id}\" ":null;
		$liclass=$liclass?" class=\"{$liclass}\" ":null;
		$html="<ul {$class}{$id}>";
		foreach ($list as $key => $v)
		{
			$link="/bbs/t/{$v['id']}";
			$title=$v['title'];
			$li="<li{$liclass}><a href=\"{$link}\">{$title}</a></li>";
			$html.=$li;
		}
		$html.="</ul>";
		return $html;
	}
	/**
	 * 加载其他布局文件
	 */
	static function load($file,$data=array())
	{
		template('layout/'.$file,$data);
	}

	static function easyList($page,$num=15,$container='.post-list',$orderby=' id desc ')
	{

		$offset=max(intval(($page-1)*$num),0);
		$data=self::data('getData',"select * from ".self::$post[0]." order by {$orderby} limit {$offset},$num");
		$data=$data?$data:array();
		$con=substr($container,0,1)=='#'?" id='".substr($container,1)."' ":" class='".substr($container,1)."' ";
		$html="<div {$con}><ul>";
		foreach ($data as $key => $item)
		{
			$html.="<li>".$item[self::$post['title']]."<span class='list-meta'>".$item[self::$post['views']]." | ".date('Y-m-d',$item[self::$post['time']])."</span></li>";
		}
		$html.="</ul></div><div class='list-pages'><ul class='pager'>";
		$pages=self::data('getVar',"select count(1) from ".self::$post[0]);
		$pages=$pages?ceil($pages/$num):0;
		for ($i=1; $i <=$pages ; $i++)
		{
			$html.="<li><a href='?p={$i}'>{$i}</a></li>";
		}
		echo $html,'</ul></div>';

		
	}
	static function easyPost($id,$container='.post-container')
	{

		$data=self::data('getLine',"select * from ".self::$post[0]." where ".self::$post['id']."={$id} ");
		$data=$data?$data:null;
		$con=substr($container,0,1)=='#'?" id='".substr($container,1)."' ":" class='".substr($container,1)."' ";
		$html=" <div {$con}>";
		$html.="<div class='post-title'>";
		$html.="<h2>".$data[self::$post['title']]."</h2>";
		$html.="<p class='post-meta'>阅读:".$data[self::$post['views']].",发布于:".date('Y-m-d H:i',$data[self::$post['time']])."</p></div>";
		$html.="<div class='post-content'>".$data[self::$post['content']]."</div>";
		echo $html,'</div>';
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

