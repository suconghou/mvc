<?php

class Database extends db
{
	protected static $initCmd=['SET NAMES UTF8'];
	protected static $initCmdSqlite=['PRAGMA SYNCHRONOUS=OFF','PRAGMA CACHE_SIZE=8000','PRAGMA TEMP_STORE=MEMORY'];

	final public static function insert(array $data,$table=null,$ignore=false,$replace=false)
	{
		$sql=sprintf('%s %sINTO `%s` %s',$replace?'REPLACE':'INSERT',$ignore&&!$replace?'IGNORE ':null,$table?:self::getCurrentTable(),self::values($data));
		return self::exec($sql,$data);
	}

	final public static function replace(array $data,$table=null)
	{
		return self::insert($data,$table,false,true);
	}

	final public static function delete(array $where=[],$table=null)
	{
		$sql=sprintf('DELETE FROM `%s`%s',$table?:self::getCurrentTable(),self::condition($where));
		return self::exec($sql,$where);
	}

	final public static function find(array $where=[],$table=null,$col='*',array $orderLimit=null,$fetch='fetchAll')
	{
		$sql=sprintf('SELECT %s FROM `%s`%s%s',$col,$table?:self::getCurrentTable(),self::condition($where),$orderLimit?self::orderLimit($orderLimit):null);
		return self::exec($sql,$where,$fetch);
	}

	final public static function findOne(array $where=[],$table=null,$col='*',array $orderLimit=[1],$fetch='fetch')
	{
		return self::find($where,$table,$col,$orderLimit,$fetch);
	}

	final public static function findVar(array $where=[],$table=null,$method='COUNT(1)',array $orderLimit=[1])
	{
		return self::find($where,$table,$method,$orderLimit,'fetchColumn');
	}

	final public static function findPage(array $where=[],$table=null,$col='*',$page=1,$limit=20,array $order=[])
	{
		$page=$page<1?1:intval($page);
		$total=intval(self::findVar($where,$table));
		$pages=ceil($total/$limit);
		$list=self::find($where,$table,$col,[($page-1)*$limit=>intval($limit)]+$order);
		return ['list'=>$list,'pages'=>$pages,'total'=>$total,'current'=>$page,'prev'=>min($pages,max(1,$page-1)),'next'=>min($pages,$page+1)];
	}

	final public static function update(array $where,array $data,$table=null)
	{
		$sql=sprintf('UPDATE `%s` SET %s%s',$table?:self::getCurrentTable(),self::values($data,true),self::condition($where));
		$intersect=array_keys(array_intersect_key($data,$where));
		$sql=$intersect?preg_replace(array_map(function($x)use(&$data){$data["{$x}_"]=$data[$x];unset($data[$x]);return sprintf('/:%s/',$x);},$intersect),'\0_',$sql,1):$sql;
		return self::exec($sql,$data+$where);
	}

	final public static function exec($sql,array $bind=null,$fetch=null)
	{
		$stm=self::execute($sql,false);
	    $rs=$stm->execute($bind);
	    return is_string($fetch)?$stm->$fetch():($fetch?$stm:$rs);
	}

	final public static function query(array $v)
	{
		return array_map(function($v){return call_user_func_array('self::exec',$v);},func_get_args());
	}

	final protected static function getCurrentTable()
	{
		return defined('static::table')?static::table:get_called_class();
	}

	final protected static function condition(array &$where,$prefix='WHERE')
	{
		$keys=array_keys($where);
		$condition=$keys?implode(sprintf(' %s ',isset($where[0])?$where[0]:'AND'),array_map(function($v)use(&$where){$x=array_values(array_filter(explode(' ',$v)));$n=null;$k=trim(ltrim($x[0],'!'));if(is_array($where[$v])){$marks=[];$i=0;array_map(function($t)use(&$marks,&$where,&$i){$q='_'.$i++;$marks[]=":{$q}";$where[$q]=$t;},$where[$v]);}else{if($k!=$x[0]){$n=$where[$v];}elseif($x[0]!=$v){$where[$x[0]]=$where[$v];}}$str=sprintf('`%s` %s %s',$k,isset($x[1])?(isset($x[2])?"{$x[1]} {$x[2]}":$x[1]):(is_array($where[$v])?'IN':'='),is_array($where[$v])?sprintf('(%s)',implode(',',$marks)):($n?$n:":{$k}"));if(is_array($where[$v])||$n||$x[0]!=$v){unset($where[$v]);}return $str;},array_filter($keys))):null;
		unset($where[0],$keys);
		return $condition?sprintf('%s(%s)',$prefix?" {$prefix} ":null,$condition):null;
	}

	final protected static function values(array &$data,$set=false,$table=null)
	{
		$keys=array_keys($data);
		return $set?implode(',',array_map(function($x)use(&$data){$k=trim($x);if($k!=$x){$data[$k]=$data[$x];unset($data[$x]);}$n=ltrim($k,'!');if($n!=$k){$n=$data[$k];unset($data[$k]);}return sprintf('`%s` = %s',trim(ltrim($k,'!')),$n==$k?":{$k}":$n);},$keys)):sprintf('%s(%s) VALUES (%s)',$table?" `{$table}` ":null,implode(',',array_map(function($x){return sprintf('`%s`',trim(ltrim(trim($x),'!')));},$keys)),implode(',',array_map(function($x)use(&$data){$k=trim($x);$n=trim(ltrim($k,'!'));if($n!=$k){$n=$data[$x];unset($data[$x]);}elseif($k!=$x){$data[$k]=$data[$x];unset($data[$x]);}return $n==$k?":{$k}":$n;},$keys)));
	}

	final protected static function orderLimit(array $orderLimit,$limit=[])
	{
		$orderLimit=array_filter($orderLimit,function($x)use($orderLimit,&$limit){if(preg_match('/^\d+$/',$x)){$k=array_search($x,$orderLimit,true);$limit=[$k,$x];return false;}else{return true;}});
		$limit=$limit?" LIMIT ".implode(',',$limit):null;
		$orderLimit?(array_walk($orderLimit,function(&$v,$k){$v=sprintf('%s %s',$k,is_string($v)?$v:($v?'ASC':'DESC'));})):null;
		return sprintf('%s%s',$orderLimit?' ORDER BY '.implode(',',$orderLimit):null,$limit);
	}

}
