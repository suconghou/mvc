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
*  
*  数据列表
*  getList($table,$column,$page=1,$order='desc',$per=20,$where=null)
* 
*  使用缓存会缓存
*  缓存存储的键为md5($table.$id)
*  $db->cache(1)->查询操作, 开启缓存,可能带来数据不一致
*  $db->cache(0)->查询操作, 跳过缓存,取真实数据(默认)
*  $db->cache(0/1,60); 设置缓存有效期, 
*
*  该类只提供继承,不能直接实例化
*/
abstract class database extends db
{
	
	private static $cache; 
	private static $use=false;  
	private static $cacheTime=600;  //600秒缓存时间
	const cacheType='memcache';  //memcache,redis,file,三者其中之一

	private $data;
	private $table;
	private $id;
	private $update;

	/**
	 * 带有参数的实例化,会生成数据模型
	 */
	function __construct($table=null,$id=null)
	{
		if($table&&$id) //手动实例化,生成数据模型
		{
			$this->data[$table]=$this->selectById($table,$id);
			$this->table=$table;
			$this->id=$id;
		}
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
		self::$use=(bool)$on;
		return $this;
	}
	/**
	 * 根据ID获得某个表的一行数据
	 */
	function selectById($table,$id,$column='*')
	{
		$sql="SELECT {$column} FROM `{$table}` WHERE id='{$id}' ";
		if(self::$use)
		{
			$key=md5($table.$id);
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
		$sql="DELETE FROM `{$table}` WHERE id={$id} ";
		if(self::$use)
		{
			$key=md5($table.$id);
			self::$cache->del($key);
		}
		return $this->runSql($sql);
	}
	/**
	 * 根据ID更新某个表
	 */
	function updateById($table,$id,$data)
	{
		$v=array();
		foreach ($data as $key => $value)
		{
			 $v[]=$key.'='."'".$value."'";
		}
		$strv=implode(',',$v);  
		$sql="UPDATE `{$table}` SET {$strv} WHERE id ='{$id}' ";
		if(self::$use)
		{
			$key=md5($table.$id);
			self::$cache->del($key);
		}
		return $this->runSql($sql);
	}
	/**
	 * 返回自增ID
	 */
	function insertData($table,$data)
	{
		foreach ($data as $key => $value)
		{
			  $k[]='`'.$key.'`';
			  $v[]='"'.$value.'"';
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
	// end 四种基本类型
	///缓存结果不能更新直到过期
	function selectWhere($table,$where=null,$column='*')
	{	
		if($where)
		{
			foreach ($where as $key => $value) 
			{
				$k[]='(`'.$key.'`="'.$value.'")';
			}
			$strk=null;
			$strk.=implode(" AND ",$k);
			$sql="SELECT {$column} FROM `{$table}` WHERE ({$strk}) ";
			if(self::$use)
			{
				$key=md5($table,$strk);
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
			foreach ($where as $key => $value) 
			{

				$k[]='(`'.$key.'`="'.$value.'")';

			}
			$strk.=implode(" AND ",$k);
			$sql="DELETE  FROM `{$table}` WHERE ({$strk}) ";
		}
		else
		{
			$sql="DELETE  FROM `{$table}` ";
		}
		if(isset($where['id'])&&self::$use)
		{
			$key=md5($table.$where['id']);
			self::$cache->del($key);
		}
		return $this->runSql($sql);

	}
	/**
	 * 若缺少id字段,会使缓存无法更新,直到过期时间
	 */
	function updateWhere($table,$where,$data)
	{
		foreach ($where as $key => $value) 
		{

			$k[]='(`'.$key.'`="'.$value.'")';

		}
		foreach ($data as $key => $value) 
		{

			$v[]=$key.'='."'".$value."'";

		}
		if(isset($where['id'])&&self::$use)
		{
			$key=md5($table.$where['id']);
			self::$cache->del($key);
		}
		$strk.=implode(" AND ",$k);
		$strv.=implode(' , ',$v);
		$sql="UPDATE `{$table}` SET {$strv} WHERE ({$strk})";
		return $this->runSql($sql);
	}
	// end 三种基本条件

	function multInsert($table,$dataArr)
	{

	}
	function multUpdate($table,$idArr)
	{

	}
	function multDelete($table,$idArr)
	{

	}
	/**
	 * 将某个表的某个字段自增1
	 */
	function incrById($table,$column,$id,$num=1)
	{
		$sql="UPDATE `{$table}` SET {$column}={$column}+{$num} WHERE id={$id} ";
		if(self::$use)
		{
			$key=md5($table.$id);
			self::$cache->del($key);
		}
		return $this->runSql($sql);
	}

	/**
	 * 将某个表的某个字段减去1
	 */
	function decrById($table,$column,$id,$num=1)
	{
		$sql="UPDATE `{$table}` SET {$column}={$column}-{$num} WHERE id={$id} ";
		if(self::$use)
		{
			$key=md5($table.$id);
			self::$cache->del($key);
		}
		return $this->runSql($sql);
	}
	/**
	 * 获得某个表的按某字段排序的分页内容以及总页数,不缓存
	 */
	function getList($table,$page=1,$where=null,$column='id',$order='desc',$per=20,$selectCloumn='*')
	{
		$offset=($page-1)*$per;
		if(is_array($where))
		{
			foreach ($where as $key => $value) 
			{

				$k[]='(`'.$key.'`="'.$value.'")';

			}
			$strk=null;
			$strk.=implode(" AND ",$k);
			$sql="SELECT {$selectCloumn} FROM `{$table}` WHERE  ({$strk})  ORDER BY {$column} {$order} LIMIT {$offset},{$per} ";
			$list=$this->getData($sql);
			$sql="SELECT COUNT(1) FROM `{$table}` WHERE  ({$strk})  ";
			$page=ceil($this->getVar($sql)/$per);
		}
		else
		{
			$sql="SELECT {$selectCloumn} FROM `{$table}` ORDER BY {$column} {$order} LIMIT {$offset},{$per} ";
			$list=$this->getData($sql);
			$sql="SELECT COUNT(1) FROM `{$table}` ";
			$page=ceil($this->getVar($sql)/$per);
		}
		return array('list'=>$list,'page'=>$page);
	}
	

}
// end class database

