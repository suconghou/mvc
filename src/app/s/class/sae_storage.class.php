<?php
/**
* SAE KV存储,文件存储中心
* 规则,小于4M存入KV,否则存入STORAGE
* 取出,首先在kv里查找,没有的话,到storage里查找,也没有则输出默认
* 关于后缀名,STOR必须带上
*/
class sae_storage
{
	private static $kv;
	private static $stor;

	function __construct()
	{
		self::$kv = new SaeKV();
   		self::$kv->init();
   		self::$stor=new SaeStorage();
	}
	public function get($key)
	{
		return self::$kv->get($key);
	}

	public function set($key,$value)
	{
		return self::$kv->set($key,$value);
	}

	private function kv_push($key,$gzdata)
	{
		return $this->set($key,$gzdata);
	}
	private function stor_push($key,$orgindata)
	{
		$dir=substr($key,0,1);
		$attr = array('encoding'=>'gzip');
		return self::$stor->write('storage',$dir.'/'.$key,$orgindata,-1,$attr,true);
	}

	private function stor_dump($key)
	{
		$dir=substr($key,0,1);
		if(self::$stor->fileExists('storage',$dir.'/'.$key))
		{
			$url=self::$stor->getUrl('storage',$dir.'/'.$key);
			redirect($url);
		}
		else //都没有找到,默认吧
		{
			return '404';
		}

	}
	private function kv_delete($key)
	{
		 return self::$kv->delete($key);
	}
	private function stor_delete($key)
	{
		$dir=substr($key,0,1);
		if(self::$stor->fileExists('storage',$dir.'/'.$key))
		{
			return self::$stor->delete('storage',$dir.'/'.$key);	
		}
	}

	//对外接口,存入
	public function push($bindata)
	{
		 $gz=gzcompress($bindata);
		 $key=md5($gz);
		 if(strlen($gz)>=4194304)
		 {
		 	return $this->stor_push($key,$bindata)?$key:false;
		 }
		 else//采用KV 存储
		 {
		 	return $this->kv_push($key,$gz)?$key:false;
		 }

	}
	//取出,在KV的直接输出,在Stor的重定向
	public function dump($key)
	{
		$gz=$this->get($key);
		if($gz)//在KV中命中
		{
			$bindata=$gz?gzuncompress($gz):null;
	    	return $bindata;
		}
		else///在STOR中查找
		{
			return $this->stor_dump($key);
		}


	}
	//删除
	public function delete($key)
	{
		$this->kv_delete($key);
		$this->stor_delete($key);
	}
}