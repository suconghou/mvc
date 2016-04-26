<?php

/***
* MySql缓存中心

CREATE TABLE IF NOT EXISTS `cache` (`k` varchar(200) NOT NULL, `v` varchar(21600) NOT NULL, `t` int(11) NOT NULL, PRIMARY KEY (`k`) ) ENGINE=MEMORY DEFAULT CHARSET=utf8;

*/

final class M extends DB
{

	const tCache='cache';

	final public static function set($key,$value,$expired=86400)
	{
		$stm=self::prepare('INSERT INTO `'.self::tCache.'` (k,v,t) VALUES (:k,:v,:t) ON DUPLICATE KEY UPDATE v=:v,t=:t');
		return $stm->execute([':k'=>$key,':v'=>json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),':t'=>$expired>864000?$expired:time()+$expired]);
	}

	final public static function mset(Array $set,$expired=86400)
	{
		$insert=[];
		$t=$expired>864000?$expired:time()+$expired;
		$values=implode(',',array_map(function($item){ return '(?,?,?)'; },$set));
		foreach ($set as $k => $v)
		{
			array_push($insert,$k,$v,$t);
		}
		unset($set,$t,$expired);
		$stm=self::prepare('INSERT INTO `'.self::tCache.'` (k,v,t) VALUES '.$values.' ON DUPLICATE KEY UPDATE v=VALUES(v),t=VALUES(t)');
		return $stm->execute($insert);
	}

	final public static function get($key,$default=null)
	{
		$stm=self::prepare('SELECT v FROM `'.self::tCache.'` where k=:k and t > UNIX_TIMESTAMP()');
		$rs=$stm->execute([':k'=>$key]);
		return $rs===false?$default:json_decode($stm->fetchColumn(),true);
	}

	final public static function mget(Array $keys=null)
	{
		if($keys)
		{
			$in='('.implode(',',array_fill(0,count($keys),'?')).')';
			$stm=self::prepare('SELECT k,v FROM `'.self::tCache.'` where k in '.$in.' and t > UNIX_TIMESTAMP()');
			$stm->execute($keys);
			$result=[];
			foreach ($stm->fetchAll() as $item)
			{
				$result[$item['k']]=json_decode($item['v'],true);
			}
			foreach (array_diff($keys,array_keys($result)) as $k)
			{
				$result[$k]=null;
			}
			return $result;
		}
		else
		{
			$stm=self::prepare('SELECT k,v FROM `'.self::tCache.'` where t > UNIX_TIMESTAMP()');
			$stm->execute();
			$result=[];
			foreach ($stm->fetchAll() as $item)
			{
				$result[$item['k']]=json_decode($item['v'],true);
			}
			return $result;
		}
	}

	final public static function ex($key,$expired=86400)
	{
		$stm=self::prepare('UPDATE `'.self::tCache.'` SET t=:t where k=:k ');
		return $stm->execute([':k'=>$key,':t'=>$expired>864000?$expired:time()+$expired]);
	}

	final public static function clear($key=null)
	{
		if($key)
		{
			return self::prepare('DELETE FROM `'.self::tCache.'`'.($key===true?null:(" WHERE k IN (".(is_array($key)?implode(',',$key):$key).") ")))->execute();
		}
		return self::prepare("DELETE FROM `".self::tCache."` WHERE t < UNIX_TIMESTAMP()")->execute();
	}

}
