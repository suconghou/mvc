<?php
/**
* 程序消耗分析器
*/
class faster
{
	private static $log;

	function __construct()
	{
		$this->init();
	}
	private function init()
	{
		$data=$this->getData();
		$data['i']=__method__;
		$data['n']='Faster Init';
		self::$log['list'][]=$data;

	}
	private function getData()
	{
		$data['t']=microtime(true);
		$data['m']=memory_get_usage();
		return $data;
	}
	function this($name=null)
	{
		$data=$this->getData();
		$data['i']=__method__;
		if($name)
		{
			$data['n']=$name;
		}
		self::$log['list'][]=$data;

	}
	function makeLog()
	{
		$this->this('Faster End');
		// var_dump(self::$log);
		$final=end(self::$log['list']);
		$size=count(self::$log['list']);
		$t=$final['t']-self::$log['list'][0]['t'];
		$m=$final['m']-self::$log['list'][0]['m'];
		var_dump($t,$m);
		$html_t=$html_m=null;
		foreach (self::$log['list'] as $k => $v)
		{
			$next=$k+1;
			if($next>=$size)break;
			$next=self::$log['list'][$next];
			$curr_t=$next['t']-$v['t'];
			$curr_t_per=($curr_t*100/$t).'%';
			$curr_m=$next['m']-$v['m'];
			if($curr_m<0)$curr_m=0;
			$curr_m_per=($curr_m*100/$m).'%';
			$html_t.= "<td style='width:".$curr_t_per.";border-right:1px solid #f66'>";
			$html_t.= $v['n'];
			$html_t.= '</td>';
			$html_m.="<td style='width:".$curr_m_per.";border-right:1px solid #f66'>";
			$html_m.=$v['n'];
			$html_m.='</td>';
		}
		
		$html="<table cellspadding=0 cellpadding=0 style='width:100%;height:30px;border:1px solid #f66'>";
		$html.='<tr>'.$html_t.'</tr>';
	
		$html.='</table>';
		$html.="<table cellspadding=0 cellpadding=0 style='width:100%;height:30px;border:1px solid #f66'>";
		$html.='<tr>'.$html_m.'</tr>';
	
		$html.='</table>';
		echo $html;

	}

}