<?php


class Database extends DB
{
	protected static $initCmd=['SET NAMES UTF8'];
	protected static $initCmdSqlite=['PRAGMA SYNCHRONOUS=OFF','PRAGMA CACHE_SIZE=8000','PRAGMA TEMP_STORE=MEMORY'];

	public function __construct()
	{

	}

	final public static function find($where,$column='*')
	{
		$table=self::getCurrentTable();
		return is_numeric($where)?self::selectById($table,$where,$column):self::selectWhere($table,$where,null,$column);
	}

	final public static function insert(Array $data,$replace=false)
	{
		return self::insertData(self::getCurrentTable(),$data,$replace);
	}

	final public static function delete($where)
	{
		$table=self::getCurrentTable();
		return is_numeric($where)?self::deleteById($table,$where):self::deleteWhere($table,$where);
	}

	final public static function update($where,Array $data)
	{
		$table=self::getCurrentTable();
		return is_numeric($where)?self::updateById($table,$where,$data):self::updateWhere($table,$where,$data);
	}

	final private static function getCurrentTable()
	{
		return defined('static::table')?static::table:strtolower(get_called_class());
	}

	final private static function getCondition($where)
	{
		if($where)
		{
			if(is_array($where))
			{
				$k=[];
				foreach ($where as $key => $value)
				{
					if(is_int($key))
					{
						$k[]=$value;
					}
					else
					{
						$value=self::quote($value);
						$k[]='(`'.$key.'`='.$value.')';
					}
				}
				$where=implode(" AND ",$k);
			}
			return " WHERE ({$where}) ";
		}
		return ' ';
	}

	final public static function selectById($table,$id,$column='*')
	{
		$id=intval($id);
		$sql="SELECT {$column} FROM {$table} WHERE id={$id}";
		return self::getLine($sql);
	}

	final public static function deleteById($table,$id)
	{
		$id=intval($id);
		$sql="DELETE FROM {$table} WHERE id={$id}";
		return self::runSql($sql);
	}

	final public static function updateById($table,$id,Array $data)
	{
		$id=intval($id);
		$updateStr=[];
		foreach ($data as $key => $value)
		{
			$updateStr[]='`'.$key.'`='.self::quote($value);
		}
		$updateStr=implode(',',$updateStr);
		$sql="UPDATE {$table} SET {$updateStr} WHERE id ={$id} ";
		return self::runSql($sql);
	}

	final public static function insertData($table,Array $data,$replace=false)
	{
		$k=$v=[];
		foreach ($data as $key => $value)
		{
			$k[]='`'.$key.'`';
			$v[]=self::quote($value);
		}
		$strk=implode(',',$k);
		$strv=implode(',',$v);
		if($replace===true)
		{
			$sql="REPLACE INTO {$table} ({$strk}) VALUES ({$strv})";
		}
		else if($replace===false)
		{
			$sql="INSERT IGNORE INTO {$table} ({$strk}) VALUES ({$strv})";
		}
		else if(is_array($replace))
		{
			$updateStr=[];
			foreach($replace as $key => $value)
			{
				$updateStr[]='`'.$key.'`='.self::quote($value);
			}
			$updateStr=implode(',',$updateStr);
			$sql="INSERT INTO {$table} ({$strk}) VALUES ({$strv}) ON DUPLICATE KEY UPDATE {$updateStr}";
		}
		else
		{
			$sql="INSERT INTO {$table} ({$strk}) VALUES ({$strv})";
		}
		return self::runSql($sql)===false?false:self::lastId();
	}

	final public static function selectWhere($table,$where=null,$orderlimit=null,$column='*')
	{
		$sql="SELECT {$column} FROM {$table} ".self::getCondition($where).$orderlimit;
		return self::getData($sql);
	}

	final public static function deleteWhere($table,$where=null)
	{
		$sql="DELETE FROM {$table} ".self::getCondition($where);
		return self::runSql($sql);
	}

	final public static function updateWhere($table,$where,Array $data)
	{
		$strv=[];
		foreach ($data as $key => $value)
		{
			$strv[]='`'.$key.'`='.self::quote($value);
		}
		$strv=implode(',',$strv);
		return self::runSql("UPDATE {$table} SET {$strv} ".self::getCondition($where));
	}
	/***
	 *	$data=array(
	 *			array('name'=>'s1','pass'=>'p1','email'=>'123@qq.com'),
	 *			array('name'=>'s2','pass'=>'p2','email'=>'456@qq.com'),
	 *			array('name'=>'s3','pass'=>'p3','email'=>'789@qq.com')
	 *	);
	**/
	final public static function multInsert($table,Array $data,Closure $callback=null)
	{
		try
		{
			self::beginTransaction();
			$columns=array_keys(current($data));
			$columnStr=implode(',',$columns);
			$valueStr=implode(',:', $columns);
			$sql="INSERT INTO {$table} ({$columnStr}) VALUES (:{$valueStr})";
			$stmt=self::prepare($sql);
			foreach ($columns as $k)
			{
				$stmt->bindParam(":{$k}",$$k);
			}
			foreach ($data as $item)
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
	final public static function multUpdate($table,Array $data,Closure $callback=null)
	{
		try
		{
			self::beginTransaction();
			$columns=array_keys(current($data));
			$v=[];
			foreach ($columns as $k)
			{
				$v[]=$k.'='.":".$k."";
			}
			$strv=implode(',',$v);
			$sql="UPDATE {$table} SET {$strv} WHERE id=:id";
			$stmt=self::prepare($sql);
			foreach ($columns as $k)
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
		$str=is_array($inStr)?implode(',',$inStr):$inStr;
		$sql="DELETE FROM {$table} WHERE {$column} IN ({$str})";
		return self::runSql($sql);
	}

	final public static function multSelect($table,$inStr,$selectcolumn='*',$column='id')
	{
		$str=is_array($inStr)?implode(',',$inStr):$inStr;
		$sql="SELECT {$selectcolumn} FROM {$table} WHERE {$column} IN ({$str})";
		return self::getData($sql);
	}

	final public static function setKey($table,$id,$column,$value=true)
	{
		$id=intval($id);
		$value=$value===true?("{$column}+1"):($value===false?("{$column}-1"):"'{$value}'");
		$sql="UPDATE {$table} SET `{$column}`={$value} WHERE id={$id} ";
		return self::runSql($sql);
	}

	final public static function getList($table,$page=1,$where=null,$orderby=null,$pageSize=20,$selectcolumn='*')
	{
		$page=max(1,intval($page));
		$offset=max(0,($page-1)*$pageSize);
		$where=self::getCondition($where);
		$list=self::getData("SELECT {$selectcolumn} FROM {$table} {$where} ".($orderby?"ORDER BY {$orderby}":'')." LIMIT {$offset},{$pageSize}");
		$total=self::getVar("SELECT COUNT(1) FROM {$table} {$where}");
		$pages=ceil($total/$pageSize);
		return ['list'=>$list,'page'=>$pages,'total'=>$total,'current'=>$page,'prev'=>max(1,$page-1),'next'=>min($pages,$page+1)];
	}

	final public static function like($table,$column,$like,$selectcolumn='*',$num=50)
	{
		$sql="SELECT {$selectcolumn} FROM {$table} WHERE {$column} LIKE '%{$like}%' LIMIT {$num}";
		return self::getData($sql);
	}

	final public static function count($table,$where=null)
	{
		return self::getVar("SELECT COUNT(1) FROM {$table} ".self::getCondition($where));
	}
}

