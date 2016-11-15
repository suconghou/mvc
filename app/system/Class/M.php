<?php

/***
*
MySQL 缓存中心

CREATE TABLE IF NOT EXISTS `cache` (`k` varchar(200) NOT NULL, `v` varchar(21600) NOT NULL, `t` int(11) NOT NULL, PRIMARY KEY (`k`) ) ENGINE=MEMORY DEFAULT CHARSET=utf8;

*/

final class M extends db
{

	const tCache='`cache`';

	final public static function set($key,$value,$expired=86400)
	{
		return self::prepare('INSERT INTO '.self::tCache.' (k,v,t) VALUES (:k,:v,:t) ON DUPLICATE KEY UPDATE v=:v,t=:t')->execute([':k'=>$key,':v'=>json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),':t'=>$expired>2592000?$expired:time()+$expired]);
	}

	final public static function mset(array $set,$expired=86400)
	{
		$t=$expired>2592000?$expired:time()+$expired;
		$holders=implode(',',array_map(function($k)use($t){return "('{$k}',?,{$t})";},array_keys($set)));
		return self::prepare('INSERT INTO '.self::tCache.' (k,v,t) VALUES '.$holders.' ON DUPLICATE KEY UPDATE v=VALUES(v),t=VALUES(t)')->execute(array_map(function($v){return json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);},array_values($set)));
	}

	final public static function get($key,$default=null)
	{
		$stm=self::prepare('SELECT v FROM '.self::tCache.' WHERE k=:k AND t > UNIX_TIMESTAMP()');
		$rs=$stm->execute([':k'=>$key]);
		return ($rs&&$ret=$stm->fetchColumn())?json_decode($ret,true):$default;
	}

	final public static function mget(array $keys=null)
	{
		if($keys)
		{
			$stm=self::prepare('SELECT k,v FROM '.self::tCache.' WHERE k IN ('.implode(',',array_fill(0,count($keys),'?')).') AND t > UNIX_TIMESTAMP()');
			$stm->execute($keys);
			$result=[];
			foreach ($stm->fetchAll(PDO::FETCH_UNIQUE) as $k => $item)
			{
				$result[$k]=json_decode($item['v'],true);
			}
			foreach (array_diff($keys,array_keys($result)) as $k)
			{
				$result[$k]=null;
			}
			return $result;
		}
		else
		{
			$stm=self::prepare('SELECT k,v FROM '.self::tCache.' WHERE t > UNIX_TIMESTAMP()');
			$stm->execute();
			$result=[];
			foreach ($stm->fetchAll(PDO::FETCH_UNIQUE) as $k => $item)
			{
				$result[$k]=json_decode($item['v'],true);
			}
			return $result;
		}
	}

	final public static function ex($key,$expired=86400)
	{
		return self::prepare('UPDATE '.self::tCache.' SET t=:t WHERE k=:k ')->execute([':k'=>$key,':t'=>$expired>2592000?$expired:time()+$expired]);
	}

	final public static function clear($key=[])
	{
		if($key)
		{
			return self::prepare('DELETE FROM '.self::tCache.(is_array($key)?(" WHERE k IN (".implode(',',array_fill(0,count($key),'?')).") "):null))->execute(is_array($key)?$key:null);
		}
		return self::prepare('DELETE FROM '.self::tCache.' WHERE t < UNIX_TIMESTAMP()')->execute();
	}

}
