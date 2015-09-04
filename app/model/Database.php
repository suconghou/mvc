<?php
/**
* 基础数据库访问
* 
*/
class Database extends DB
{
	protected static $initCmd=array('SET NAMES UTF8');
	protected static $initCmdSqlite=array('PRAGMA SYNCHRONOUS=OFF','PRAGMA CACHE_SIZE=8000','PRAGMA TEMP_STORE=MEMORY');

	public function __construct()
	{
		
	}

	final public static function find($where,$column='*')
	{
		$table=isset(self::$table)?self::$table:get_called_class();
		return is_numeric($where)?self::selectById($table,$where,$column):self::selectWhere($table,$where,null,$column);
	}

	final public static function insert($data,$replace=false)
	{
		$table=isset(self::$table)?self::$table:get_called_class();
		return self::insertData($table,$data,$replace);
	}

	final public static function delete($where)
	{
		$table=isset(self::$table)?self::$table:get_called_class();
		return is_numeric($where)?self::deleteById($table,$where):self::deleteWhere($table,$where);
	}

	final public static function update($where,$data)
	{
		$table=isset(self::$table)?self::$table:get_called_class();
		return is_numeric($where)?self::updateById($table,$where,$data):self::updateWhere($table,$where,$data);
	}

	final public static function selectById($table=null,$id,$column='*')
	{
		$id=intval($id);
		$sql="SELECT {$column} FROM {$table} WHERE id={$id} ";
		return self::getLine($sql);
	}

	final public static function deleteById($table,$id)
	{
		$id=intval($id);
		$sql="DELETE FROM {$table} WHERE id={$id} ";
		return self::runSql($sql);
	}

	final public static function updateById($table,$id,$data)
	{
		$id=intval($id);
		$updateStr=array();
		foreach ($data as $key => $value)
		{
			$updateStr[]='`'.$key.'`='.self::quote($value);
		}
		$updateStr=implode(',',$updateStr);
		$sql="UPDATE {$table} SET {$updateStr} WHERE id ={$id} ";
		return self::runSql($sql);
	}

	final public static function insertData($table,$data,$replace=false)
	{
		$k=$v=array();
		foreach ($data as $key => $value)
		{
			$k[]='`'.$key.'`';
			$v[]=self::quote($value);
		}
		$strv=implode(',',$v);
		$strk=implode(',',$k);
		if($replace===false)
		{
			$sql="INSERT INTO {$table} ({$strk}) VALUES ({$strv})";
		}
		else if(is_array($replace))
		{
			$updateStr=array();
			foreach($replace as $key => $value)
			{
				$updateStr[]='`'.$key.'`='.self::quote($value);
			}
			$updateStr=implode(',',$updateStr);
			$sql="INSERT INTO {$table} ({$strk}) VALUES ({$strv}) ON DUPLICATE KEY UPDATE {$updateStr}";
		}
		else
		{
			$sql="REPLACE INTO {$table} ({$strk}) VALUES ({$strv})";
		}
		return self::runSql($sql)===false?false:self::lastId();
	}

	final public static function selectWhere($table,$where=null,$orderlimit=null,$column='*')
	{
		if($where)
		{
			if(is_array($where))
			{
				$k=array();
				foreach ($where as $key => $value) 
				{
					$value=self::quote($value);
					$k[]='(`'.$key.'`='.$value.')';
				}
				$strk=implode(" AND ",$k);
			}
			else
			{
				$strk=$where;
			}
			$sql="SELECT {$column} FROM {$table} WHERE ({$strk}) ";
		}
		else
		{
			$sql="SELECT {$column} FROM {$table} ";
		}
		if($orderlimit)
		{
			$sql.=$orderlimit;
		}
		return self::getData($sql);
	}

	final public static function deleteWhere($table,$where=null)
	{
		if($where)
		{
			if(is_array($where))
			{
				$k=array();
				foreach ($where as $key => $value) 
				{
					$value=self::quote($value);
					$k[]='(`'.$key.'`='.$value.')';
				}
				$strk=implode(" AND ",$k);
			}
			else
			{
				$strk=$where;
			}
			$sql="DELETE FROM {$table} WHERE ({$strk}) ";
		}
		else
		{
			$sql="DELETE FROM {$table} ";
		}
		return self::runSql($sql);
	}

	final public static function updateWhere($table,$where,$data)
	{
		$k=$v=array();
		if(is_array($where))
		{
			foreach ($where as $key => $value) 
			{
				$value=self::quote($value);
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
		$sql="UPDATE {$table} SET {$strv} WHERE ({$strk})";
		return self::runSql($sql);
	}

	/***
	 *	$data=array(
	 *			array('name'=>'s1','pass'=>'p1','email'=>'123@qq.com'),
	 *			array('name'=>'s2','pass'=>'p2','email'=>'456@qq.com'),
	 *			array('name'=>'s3','pass'=>'p3','email'=>'789@qq.com')
	 *	);
	**/
	final public static function multInsert($table,$data,Closure $callback=null)
	{
		try
		{
			self::beginTransaction();
			$columns=array_keys($data[0]);
			$columnStr=implode(',',$columns);
			$valueStr=implode(',:', $columns);
			$sql="INSERT INTO {$table} ({$columnStr}) VALUES (:{$valueStr})";
			$stmt=self::prepare($sql);
			foreach ($columns as $k)
			{
				$stmt->bindParam(":{$k}",$$k);
			}
			foreach ($data as $i=>$item)
			{
				foreach ($item as $k => $v)
				{
					$$k=$v;
				}
				$stmt->execute();
			}
			return self::commit();
		}
		catch(PDOException $e)
		{
			self::rollback();
			return $callback?$callback($e):false;
		}

	}
	/***
	 *	批量更新   
	 *	$data=array(
	 *			'18'=>array('name'=>'name18','pass'=>'11'),
	 *			'19'=>array('name'=>'name19','pass'=>'22')
	 *	);
	 **/
	final public static function multUpdate($table,$data,Closure $callback=null)
	{
		try
		{
			self::beginTransaction();
			$keys=array_keys(current($data));
			$v=array();
			foreach ($keys as $k) 
			{
				$v[]=$k.'='.":".$k."";
			}
			$strv=implode(',', $v);
			$sql="UPDATE {$table} SET {$strv} WHERE id=:id";
			$stmt=self::prepare($sql);
			foreach ($keys as $k)
			{
				$stmt->bindParam(":{$k}", $$k);
			}
			$stmt->bindParam(":id", $id);
			foreach ($data as $id => $item)
			{
				foreach ($item as $k => $v)
				{
					$$k=$v;
				}
				$stmt->execute();
			}
			return self::commit();
		}
		catch(PDOException $e)
		{
			 self::rollback();
			 return $callback?$callback($e):false;
		}
	}

	final public static function multDelete($table,$inStr,$column='id')
	{
		$str=is_array($inStr)?implode(',', $inStr):$inStr;
		$sql="DELETE FROM {$table} WHERE {$column} IN ({$str})";
		return self::runSql($sql);
	}

	final public static function multSelect($table,$inStr,$selectcolumn='*',$column='id')
	{
		$str=is_array($inStr)?implode(',', $inStr):$inStr;
		$sql="SELECT {$selectcolumn} FROM {$table} WHERE {$column} IN ({$str}) ";
		$ret=self::getData($sql);
		$res=array();
		foreach ($ret as $item)
		{
			$id=$item[$column];
			unset($item[$column]);
			$res[$id]=count($item)==1?current($item):$item;
		}
		return $res;
	}

	final public static function incrById($table,$column,$id,$num=1)
	{
		$id=intval($id);
		$sql="UPDATE {$table} SET {$column}={$column}+{$num} WHERE id={$id} ";
		return self::runSql($sql);
	}

	final public static function decrById($table,$column,$id,$num=1)
	{
		$id=intval($id);
		$sql="UPDATE {$table} SET {$column}={$column}-{$num} WHERE id={$id} ";
		return self::runSql($sql);
	}

	final public static function getList($table,$page=1,$where=null,$orderby='id desc',$pageSize=20,$selectcolumn='*')
	{
		$offset=max(0,($page-1)*$pageSize);
		if($where)
		{
			if(is_array($where))
			{
				$k=array();
				foreach ($where as $key => $value) 
				{
					$value=self::quote($value);
					$k[]='(`'.$key.'`='.$value.')';
				}
				$strk=implode(" AND ",$k);
			}
			else
			{
				$strk=$where;
			}
			$list="SELECT {$selectcolumn} FROM {$table} WHERE  ({$strk})  ORDER BY {$orderby} LIMIT {$offset},{$pageSize} ";
			$pages="SELECT COUNT(1) FROM {$table} WHERE ({$strk}) ";
		}
		else
		{
			$list="SELECT {$selectcolumn} FROM {$table} ORDER BY {$orderby} LIMIT {$offset},{$pageSize} ";
			$pages="SELECT COUNT(1) FROM {$table} ";
		}
		$list=self::getData($list);
		$total=self::getVar($pages);
		$pages=ceil($total/$pageSize);
		return array('list'=>$list,'page'=>$pages,'total'=>$total,'current'=>$page,'prev'=>max(1,$page-1),'next'=>min($pages,$page+1));
	}

	final public static function like($table,$column,$like,$selectcolumn='*',$num=50)
	{
		$sql="SELECT {$selectcolumn} FROM {$table} WHERE {$column} LIKE '%{$like}%' LIMIT {$num}";
		return self::getData($sql);
	}

	final public static function count($table,$where=null)
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
		$sql="SELECT COUNT(1) FROM {$table} {$where} ";
		return self::getVar($sql);
		
	}

	
}
// end class database

