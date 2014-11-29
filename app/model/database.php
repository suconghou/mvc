<?php
/**
*  数据库基础扩展,继承此类以获得灵活的数据操纵
*  提供数据库基本操作,但没有限定表名,字段名等
*  包含自动缓存系统,缓存系统需cache.class.php支持,也是三种缓存类型
*  四种基本数据访问
*  selectById($table,$id,$column='*')
*  deleteById($table,$id)
*  updateById($table,$id,$data)
*  insertData($table,$data) 
*  三种基本条件访问
*  selectWhere($table,$where=null)
*  deleteWhere($table,$where=null)
*  updateWhere($table,$where,$data) 
*  三种批量操作
*  multInsert($table,$dataArr)
*  multUpdate($table,$idArr)
*  multDelete($table,$idArr)
*  单字段自增,自减
*  incrById($table,$column,$id,$num=1)
*  decrById($table,$column,$id,$num=1)
*  搜索  
*  searchByColumn($table,$column,$search)
*  searchByTable($table,$cloumn,$search)
*  数据列表
*  getList($table,$column,$page=1,$order='desc',$per=20,$where=null)
* 
*  按条件计数
*  count($table,$where=array())
*
*  使用缓存 $db->cache('on',10)->selectById($table,$id); 允许得到缓存结果
*  $db->cache('off')->selectById($table,$id); 跳过缓存,直接使用数据库数据
*  $db->delCache($table,$id) 手动删除一个表的id缓存
*  $db->selectWhere() $db->getList() $db->count() 取得的缓存不能手动删除只能过期失效
*  $db->deleteById() $db->updateById() $db->deleteWhere() $db->updateWhere() 会自动判断删除缓存,保持数据一致
*  
*
*  该类只提供继承,不能直接实例化
*/
abstract class database extends db
{
	
	private static $cache; 
	private static $use=false;  
	private static $cacheTime=600;  //600秒缓存时间
	const cacheType='memcache';  //memcache,redis,file,三者其中之一

	function __construct()
	{
		
	}
	/**
	 *  可调用的缓存开关,第一次开启缓存时会初始化cacher
	 */	
	function cache($on,$cacheTime=null)
	{
		if(is_null(self::$cache)&&$on)
		{
			self::$cache=S('class/cache',self::cacheType);
		}
		if($cacheTime)
		{
			self::$cacheTime=$cacheTime;
		}
		if($on=='off'||!$on)
		{
			self::$use=false;
		}
		else
		{
			self::$use=true;
		}
		return $this;
	}
	/**
	 * 清除某个cache值
	 */
	function delCache($table,$id)
	{
		if(self::$cache&&self::$use)
		{
			$key=$table.intval($id);
			return self::$cache->del($key);
		}
		return false;
	}
	/**
	 * 根据ID获得某个表的一行数据
	 */
	function selectById($table,$id,$column='*')
	{
		$id=intval($id);
		$sql="SELECT {$column} FROM `{$table}` WHERE id='{$id}' ";
		if(self::$use)
		{
			$key=$table.$id;
			$data=self::$cache->get($key);
			if($data)
			{
				return $data;
			}
			else
			{
				$data=$this->getLine($sql);
				self::$cache->set($key,$data,self::$cacheTime);
				return $data;
			}
		}
		else
		{
			return $this->getLine($sql);
		}
	}
	/**
	 * 根据ID删除某个表的一行
	 */
	function deleteById($table,$id)
	{
		$id=intval($id);
		$sql="DELETE FROM `{$table}` WHERE id={$id} ";
		$this->delCache($table,$id);
		return $this->runSql($sql);
	}
	/**
	 * 根据ID更新某个表
	 */
	function updateById($table,$id,$data)
	{
		$id=intval($id);
		$v=array();
		foreach ($data as $key => $value)
		{
			$value=$this->quote($value);
			$v[]=$key.'='.$value;
		}
		$strv=implode(',',$v);  
		$sql="UPDATE `{$table}` SET {$strv} WHERE id ='{$id}' ";
		$this->delCache($table,$id);
		return $this->runSql($sql);
	}
	/**
	 * 返回自增ID
	 */
	function insertData($table,$data)
	{
		$k=$v=array();
		foreach ($data as $key => $value)
		{
			$k[]='`'.$key.'`';
			$v[]=$this->quote($value);
		}
		$strv=implode(',',$v);    
		$strk=implode(",",$k);
		$sql="INSERT INTO `{$table}` ({$strk}) VALUES ({$strv})";
		if($this->runSql($sql))
		{
			return $this->lastId();
		}
		return false;
	}
	// END selectById, updateById, deleteById, insertData 四种基本类型
	///缓存结果不能更新直到过期
	function selectWhere($table,$where=null,$orderlimit=null,$column='*')
	{	
		if($where)
		{
			$k=array();
			foreach ($where as $key => $value) 
			{
				$value=$this->quote($value);
				$k[]='(`'.$key.'`='.$value.')';
			}
			$strk=implode(" AND ",$k);
			$sql="SELECT {$column} FROM `{$table}` WHERE ({$strk}) ";
			if($orderlimit)
			{
				$sql.=$orderlimit;
			}
			if(self::$use)
			{
				$key=md5($sql);
				$data=self::$cache->get($key);
				if($data)
				{
					return $data;
				}
				else
				{
					$data=$this->getData($sql);
					self::$cache->set($key,$data,self::$cacheTime);
					return $data;
				}
			}
			else
			{
				return $this->getData($sql);
			}
		}
		else
		{
			$sql="SELECT {$column} FROM `{$table}` ";
			if($orderlimit)
			{
				$sql.=$orderlimit;
			}
			if(self::$use)
			{
				$key=md5($sql);
				$data=self::$cache->get($key);
				if($data)
				{
					return $data;
				}
				else
				{
					$data=$this->getData($sql);
					self::$cache->set($key,$data,self::$cacheTime);
					return $data;
				}
			}
			return $this->getData($sql);
		}
		
	}
	/**
	 * 若缺少id字段,会使缓存无法更新,直到过期时间
	 */
	function deleteWhere($table,$where=null)
	{
		if($where)
		{
			$k=array();
			foreach ($where as $key => $value) 
			{
				$value=$this->quote($value);
				$k[]='(`'.$key.'`='.$value.')';
			}
			$strk=implode(" AND ",$k);
			$sql="DELETE  FROM `{$table}` WHERE ({$strk}) ";
		}
		else
		{
			$sql="DELETE  FROM `{$table}` ";
		}
		if(isset($where['id']))
		{
			$this->delCache($table,$where['id']);
		}
		return $this->runSql($sql);

	}
	/**
	 * 若缺少id字段,会使缓存无法更新,直到过期时间
	 */
	function updateWhere($table,$where,$data)
	{
		$k=$v=array();
		foreach ($where as $key => $value) 
		{
			$value=$this->quote($value);
			$k[]='(`'.$key.'`='.$value.')';
		}
		foreach ($data as $key => $value) 
		{
			$v[]=$key.'='."'".$value."'";
		}
		$strk=implode(" AND ",$k);
		$strv=implode(' , ',$v);
		$sql="UPDATE `{$table}` SET {$strv} WHERE ({$strk})";
		if(isset($where['id']))
		{
			$this->delCache($table,$where['id']);
		}
		return $this->runSql($sql);
	}
	// END selectWhere deleteWhere updateWhere 三种基本条件

	/***
	 * 批量添加
	 *
		$data=array(
				array('name'=>'s10','pass'=>'p1','email'=>'121200'),
				array('name'=>'s20','pass'=>'p2','email'=>'1200'),
				array('name'=>'s3','pass'=>'p3','email'=>'12774')
				);

	**/
	function multInsert($table,$dataArr,$callback=null)
	{
		$pdo=$this->getInstance();
		$pdo->beginTransaction();
		try
		{
			$columns=array_keys($dataArr[0]);
			$columnStr=implode(',',$columns);
			$valueStr=implode(',:', $columns);
			$sql="INSERT INTO `{$table}` ({$columnStr}) VALUES (:{$valueStr})";
			$stmt = $pdo->prepare($sql);
			foreach ($columns as $k)
			{
				$stmt->bindParam(":{$k}", $$k);
			}
			foreach ($dataArr as $i=>$item)
			{
				foreach ($item as $k => $v)
				{
					$$k=$v;
				}
				$stmt->execute();
				if($stmt->rowCount()<1)
				{
					throw new PDOException;
				}
			}
			$pdo->commit();
		}
		catch(PDOException $e)
		{
			 $pdo->rollback();
			 $error=$e->getMessage();
			 if($callback)
			 {
			 	$callback($error,$e);
			 }
			 else
			 {
				 app::log($error);
			 }
			 return false;
		}
		return true;

	}
	/***
	 * 批量更新   
		$updata=array(
					'18'=>array('name'=>'name18','pass'=>'11'),
					'19'=>array('name'=>'name19','pass'=>'22')
				);
	 **/
	function multUpdate($table,$idArr,$callback=null)
	{
		$pdo=$this->getInstance();
		$pdo->beginTransaction();
		try
		{
			$keys=array_keys(current($idArr));
			$v=array();
			foreach ($keys as $k) 
			{
				$v[]=$k.'='.":".$k."";
			}
			$strv=implode(',', $v);
			$sql="UPDATE `{$table}` SET {$strv} WHERE id=:id";
			$stmt = $pdo->prepare($sql);
			foreach ($keys as $k)
			{
				$stmt->bindParam(":{$k}", $$k);
			}
			$stmt->bindParam(":id", $id);
			foreach ($idArr as $id => $item)
			{
				foreach ($item as $k => $v)
				{
					$$k=$v;
				}
				$stmt->execute();
			}
			$pdo->commit();
		}
		catch(PDOException $e)
		{
			 $pdo->rollback();
			 $error=$e->getMessage();
			 if($callback)
			 {
			 	$callback($error,$e);
			 }
			 else
			 {
			 	app::log($error);
			 }
			 return false;
		}
		return true;

	}
	/**
	 * 批量删除
	 */
	function multDelete($table,$idArr)
	{
		$str='';
		foreach ($idArr as $id)
		{
			$str.="'{$id}',";
		}
		$str=rtrim($str,',');
		$sql="DELETE FROM `{$table}` WHERE id IN ({$str})";
		return $this->runSql($sql);
	}
	//END multInsert multUpdate multDelete 三种批量操作

	/**
	 * 将某个表的某个字段自增1
	 */
	function incrById($table,$column,$id,$num=1)
	{
		$id=intval($id);
		$sql="UPDATE `{$table}` SET {$column}={$column}+{$num} WHERE id={$id} ";
		$this->delCache($table,$id);
		return $this->runSql($sql);
	}

	/**
	 * 将某个表的某个字段减去1
	 */
	function decrById($table,$column,$id,$num=1)
	{
		$id=intval($id);
		$sql="UPDATE `{$table}` SET {$column}={$column}-{$num} WHERE id={$id} ";
		$this->delCache($table,$id);
		return $this->runSql($sql);
	}
	function existId($table,$id,$select='*')
	{
		$sql="SELECT {$select} FROM `{$table}` WHERE id='{$id}' ";
		$data=$this->getLine($sql);
		return empty($data)?false:$data;
	}
	function existWhere($table,$where,$select='*')
	{
		$k=array();
		foreach ($where as $key => $value) 
		{
			$value=$this->quote($value);
			$k[]='(`'.$key.'`='.$value.')';
		}
		$strk=implode(" AND ",$k);
		$sql="SELECT {$select} FROM `{$table}` WHERE ({$strk})";
		$data=$this->getData($sql);
		return empty($data)?false:$data;

	}
	/**
	 * 按栏目搜索
	 */
	function searchByColumn($table,$column,$search,$selectCloumn='*',$num=50)
	{
		$sql="SELECT {$selectCloumn} FROM `{$table}` WHERE {$column} LIKE  '%{$search}%' LIMIT {$num}";
		if(self::$use)
		{
			$key=md5($sql);
			$data=self::$cache->get($key);
			if($data)
			{
				return $data;
			}
			else
			{
				$data=$this->getData($sql);
				self::$cache->set($key,$data,self::$cacheTime);
				return $data;
			}
		}
		else
		{
			return $this->getData($sql);
			
		}
	}

	/**
	 * 获得某个表的某条件下按某字段排序的指定页的SELECT内容以及该条件下的总页数,缓存只能过期自动删除
	 */
	function getList($table,$page=1,$where=null,$column='id',$order='desc',$per=20,$selectCloumn='*')
	{
		$offset=max(0,($page-1)*$per);
		if(is_array($where))
		{
			$pdo=$this->getInstance();
			$k=array();
			foreach ($where as $key => $value) 
			{
				$value=$pdo->quote($value);
				$k[]='(`'.$key.'`='.$value.')';
			}
			$strk=null;
			$strk.=implode(" AND ",$k);
			$l="SELECT {$selectCloumn} FROM `{$table}` WHERE  ({$strk})  ORDER BY {$column} {$order} LIMIT {$offset},{$per} ";
			$p="SELECT COUNT(1) FROM `{$table}` WHERE  ({$strk})  ";
			if(self::$use)
			{
				$key=md5($l);
				$data=self::$cache->get($key);
				if($data)
				{
					return $data;
				}
				else
				{
					$list=$this->getData($l);
					$page=ceil($this->getVar($p)/$per);
					$data=array('list'=>$list,'page'=>$page);
					self::$cache->set($key,$data,self::$cacheTime);
					return $data;
				}
			}
			$list=$this->getData($l);
			$page=ceil($this->getVar($p)/$per);
			$data=array('list'=>$list,'page'=>$page);
			return $data;
		}
		else
		{
			$l="SELECT {$selectCloumn} FROM `{$table}` ORDER BY {$column} {$order} LIMIT {$offset},{$per} ";
			$p="SELECT COUNT(1) FROM `{$table}` ";
			if(self::$use)
			{
				$key=md5($l);
				$data=self::$cache->get($key);
				if($data)
				{
					return $data;
				}
				else
				{
					$list=$this->getData($l);
					$page=ceil($this->getVar($p)/$per);
					$data=array('list'=>$list,'page'=>$page);
					self::$cache->set($key,$data,self::$cacheTime);
					return $data;
				}
			}
			$list=$this->getData($l);
			$page=ceil($this->getVar($p)/$per);
			return array('list'=>$list,'page'=>$page);
		}
	}
	/**
	 * 对某条件计数
	 */
	function count($table,$where=null)
	{
		if($where)
		{
			foreach ($where as $key => $value) 
			{
				$k[]='(`'.$key.'`="'.$value.'")';
			}
			$strk=null;
			$strk.=implode(" AND ",$k);
			$where=" WHERE ({$strk}) ";
		}
		$sql="SELECT COUNT(1) FROM `".$table."` {$where} ";
		if(self::$use)
		{
			$key=md5($sql);
			$data=self::$cache->get($key);
			if($data)
			{
				return $data;
			}
			else
			{
				$data=$this->getVar($sql);
				self::$cache->set($key,$data,$cacheTime);
				return $data;
			}
		}
		return $this->getVar($sql);
		
	}
	

}
// end class database

