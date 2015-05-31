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
*/
class Database extends DB
{
	private  $orm;
	private static $cache;
	private static $expire=600;  //600秒缓存时间
	private static $use=false;
	const type='memcache';  //memcache,redis,file,三者其中之一

	/**
	 * $cfg ,id or array ,where string
	 */
	function __construct($cfg=null,$column='*')
	{
		$this->orm['table'] = get_called_class();
		if(!is_null($cfg))
		{
			if(is_numeric($cfg))
			{
				$this->orm['instance'] = $this->selectById($this->orm['table'],$cfg,$column);
			}
			else
			{
				$this->orm['instance'] = $this->selectWhere($this->orm['table'],$cfg,null,$column);
			}
			if(!$this->orm['instance'])
			{
				$this->orm['instance']=false;
			}
		}
	}
	/**
	 *  可调用的缓存开关,第一次开启缓存时会初始化cacher
	 */	
	function cache($on,$expire=0)
	{
		if(is_null(self::$cache)&&$on)
		{
			self::$cache=S('class/cache',self::type);
		}
		if($expire)
		{
			self::$expire=$expire;
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
				self::$cache->set($key,$data,self::$expire);
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
			if(is_array($where))
			{
				$k=array();
				foreach ($where as $key => $value) 
				{
					$value=$this->quote($value);
					$k[]='(`'.$key.'`='.$value.')';
				}
				$strk=implode(" AND ",$k);
			}
			else
			{
				$strk=$where;
			}
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
					self::$cache->set($key,$data,self::$expire);
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
					self::$cache->set($key,$data,self::$expire);
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
			if(is_array($where))
			{
				$k=array();
				foreach ($where as $key => $value) 
				{
					$value=$this->quote($value);
					$k[]='(`'.$key.'`='.$value.')';
				}
				$strk=implode(" AND ",$k);
			}
			else
			{
				$strk=$where;
			}
			$sql="DELETE  FROM `{$table}` WHERE ({$strk}) ";
		}
		else
		{
			$sql="DELETE  FROM `{$table}` ";
		}
		if(is_array($where) and isset($where['id']))
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
		if(is_array($where))
		{
			foreach ($where as $key => $value) 
			{
				$value=$this->quote($value);
				$k[]='(`'.$key.'`='.$value.')';
			}
			$strk=implode(" AND ",$k);
		}
		else
		{
			$strk=$where;
		}
		foreach ($data as $key => $value) 
		{
			$v[]=$key.'='."'".$value."'";
		}
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
			 if(is_callable($callback))
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
	 *	批量更新   
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
			 if(is_callable($callback))
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
	function multDelete($table,$idArr,$cloumn='id')
	{
		if(is_array($idArr))
		{
			$str=implode(',', $idArr);
		}
		else
		{
			$str=$idArr;
		}
		$sql="DELETE FROM `{$table}` WHERE {$column} IN ({$str})";
		return $this->runSql($sql);
	}
	/**
	 * 批量map查找
	 */
	function multSelect($table,$idArr,$selectcolumn='*',$cloumn='id')
	{
		if(is_array($idArr))
		{
			$str=implode(',', $idArr);
		}
		else
		{
			$str=$idArr;
		}
		$sql="select {$selectcolumn} from `{$table}` where {$cloumn} in ({$str}) ";
		$ret=$this->getData($sql);
		$res=array();
		foreach ($ret as $item)
		{
			$id=$item[$cloumn];
			unset($item[$cloumn]);
			$res[$id]=count($item)==1?current($item):$item;
		}
		return $res;
	}
	//END multInsert multUpdate multDelete 四种批量操作

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
		if(is_array($where))
		{
			$k=array();
			foreach ($where as $key => $value) 
			{
				$value=$this->quote($value);
				$k[]='(`'.$key.'`='.$value.')';
			}
			$strk=implode(" AND ",$k);
		}
		else
		{
			$strk=$where;
		}
		$sql="SELECT {$select} FROM `{$table}` WHERE ({$strk})";
		$data=$this->getData($sql);
		return empty($data)?false:$data;
	}
	/**
	 * 按栏目搜索
	 */
	function searchByColumn($table,$column,$search,$selectcolumn='*',$num=50)
	{
		$sql="SELECT {$selectcolumn} FROM `{$table}` WHERE {$column} LIKE  '%{$search}%' LIMIT {$num}";
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
				self::$cache->set($key,$data,self::$expire);
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
	function getList($table,$page=1,$where=null,$column='id',$order='desc',$per=20,$selectcolumn='*')
	{
		$offset=max(0,($page-1)*$per);
		if($where)
		{
			$pdo=$this->getInstance();
			if(is_array($where))
			{
				$k=array();
				foreach ($where as $key => $value) 
				{
					$value=$pdo->quote($value);
					$k[]='(`'.$key.'`='.$value.')';
				}
				$strk=implode(" AND ",$k);
			}
			else
			{
				$strk=$where;
			}
			$l="SELECT {$selectcolumn} FROM `{$table}` WHERE  ({$strk})  ORDER BY {$column} {$order} LIMIT {$offset},{$per} ";
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
					self::$cache->set($key,$data,self::$expire);
					return $data;
				}
			}
			$list=$this->getData($l);
			$page=ceil($this->getVar($p)/$per);
			return array('list'=>$list,'page'=>$page);
		}
		else
		{
			$l="SELECT {$selectcolumn} FROM `{$table}` ORDER BY {$column} {$order} LIMIT {$offset},{$per} ";
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
					self::$cache->set($key,$data,self::$expire);
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
			if(is_array($where))
			{
				$k=array();
				foreach ($where as $key => $value) 
				{
					$k[]='(`'.$key.'`="'.$value.'")';
				}
				$strk=implode(" AND ",$k);
			}
			else
			{
				$strk=$where;
			}
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
				self::$cache->set($key,$data,self::$expire);
				return $data;
			}
		}
		return $this->getVar($sql);
		
	}

	///////////////////////ORM////////////////////////
	function __get($key)
	{
		return isset($this->orm['instance'][$key])?$this->orm['instance'][$key]:null;
	}
	function __set($key,$value)
	{
		if(empty($this->orm['instance']))
		{
			$this->orm['data'][$key]=$value;
		}
		else
		{
			if(array_key_exists($key, $this->orm['instance']) and ($this->orm['instance'][$key] !== $value) )
			{
				$this->orm['data'][$key]=$value;
				$this->orm['instance'][$key]=$value;
			}
		}
	}
	function __invoke($data=null)
	{
		if($data)
		{
			if(!isset($this->orm['instance']))
			{
				return $this->insertData($this->orm['table'],$data);
			}
			else
			{
				if(isset($this->orm['instance']['id']))
				{
					return $this->updateById($this->orm['table'],$data);
				}
				return false;
			}
		}
		else
		{
			return isset($this->orm['instance'])?$this->orm['instance']:null;
		}
		
	}
	function __toString()
	{
		return isset($this->orm['instance'])?var_export($this->orm['instance'],true):null;
	}
	function save($data=null)
	{
		var_dump($this->orm);
		if(!isset($this->orm['instance']))
		{
			return $this->insertData($this->orm['table'],$this->orm['data']);
		}
		else if(!empty($this->orm['data']))
		{
			if($this->orm['instance']===false)
			{
				return false;
			}
			else
			{
				return $this->updateById($this->orm['table'],$this->orm['instance']['id'],$this->orm['data']);
			}
		}
		else
		{
			return isset($this->orm['instance'][0])?false:true;
		}
	}
	function delete()
	{
		if(!empty($this->orm['instance']))
		{
			return $this->deleteById($this->orm['table'],$this->orm['instance']['id']);
		}
	}
	function __destruct()
	{
		
	}
}
// end class database

