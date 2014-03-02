<?php
/**
* 
* 四种类型的获取
*/
class work extends controller
{
	
	function __construct()
	{
		$this->loadlibrary('curl');
		$this->loadmodel('m_outs');
	}
	///每隔1分钟触发的地址
	//启动后按类型启动四种线程
	function timer()
	{
		
		$urls=array('http://work.suconghou.cn/work/take_nores',
					'http://work.suconghou.cn/work/take_header',
					'http://work.suconghou.cn/work/take_body',
					'http://work.suconghou.cn/work/take_header_body');
		$this->curl->quick_exec($urls);
		exit('done');
	}

	function take_nores()
	{
		$this->loadmodel('m_work');
		$res=$this->m_work->get_nores();
		$urls=array();
		foreach ($res as $key => $value)
		{
			$urls[]=$value['url'];
		}
		$num=count($urls);
		$this->curl->quick_exec($urls);
		$this->logs('num',$num);///记录日志
		exit('nores'.$num);//输出个数并退出
	}
	function take_header()
	{
		$this->loadmodel('m_work');
		$res=$this->m_work->get_header();
		$res||exit('header0');
		foreach ($res as $key => $value)
		{
			$urls[$value['id']]=$value['url'];
		}
		$swurls=array();
		$swurls=array_flip($urls);
		$res=$this->curl->add($urls,1,1,5)->exec();
		
		foreach ($res as $key => $value)//结果,$key为一个网址,$value为返回的结果
		{

			$this->logs('out',array('workid'=>$swurls[$key],'text'=>$value));
		}
		exit('header'.count($urls));//本次执行的个数
	}
	function take_body()
	{
		$this->loadmodel('m_work');
		$res=$this->m_work->get_body();
		$res||exit('body0');
		foreach($res as $key=>$value)
		{
			$urls[$value['id']]=$value['url'];
		}
		$swurls=array();
		$swurls=array_flip($urls);
		$res=$this->curl->add($urls,0,0,5)->exec();
		foreach ($res as $key => $value)//结果,$key为一个网址,$value为返回的结果
		{
			$this->logs('out',array('workid'=>$swurls[$key],'text'=>$value));
		}
		exit('body'.count($urls));//本次执行的个数

	} 
	function take_header_body()
	{
		$this->loadmodel('m_work');
		$res=$this->m_work->get_header_body();
		$res||exit('headerbody0');
		foreach($res as $key=>$value)
		{
			$urls[$value['id']]=$value['url'];
		}
		$swurls=array();
		$swurls=array_flip($urls);
		$res=$this->curl->add($urls,1,0,8)->exec();
		foreach ($res as $key => $value)//结果,$key为一个网址,$value为返回的结果
		{
			$this->logs('out',array('workid'=>$swurls[$key],'text'=>$value));
		}
		exit('headerbody'.count($urls));//本次执行的个数

	}
	//记录日志
	private function logs($type,$arr)
	{
		//$this->loadmodel('m_log');
		if($type=='num')
		{

		}
		else if($type=='out')//记录数据
		{

			$this->m_outs->give_log($arr['workid'],$arr['text']);
		}
		
	}

}