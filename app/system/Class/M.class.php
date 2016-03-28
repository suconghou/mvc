<?php

/***
* MySql缓存中心

CREATE TABLE IF NOT EXISTS `cache` (`k` varchar(200) NOT NULL, `v` varchar(21500) NOT NULL, `t` int(11) NOT NULL, PRIMARY KEY (`k`) ) ENGINE=MEMORY DEFAULT CHARSET=utf8;

*/

final class M extends DB
{

	const tCache='cache';

	final public static function set($key,$value,$expired=86400)
	{
		$value=json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		$t=$expired>864000?$expired:time()+$expired;
		return self::exec('INSERT INTO '.self::tCache." (k,v,t) VALUES ('{$key}','{$value}',{$t}) ON DUPLICATE KEY UPDATE v='{$value}',t={$t} ");
	}

	final public static function get($key,$default=null)
	{
		$rs=self::query('SELECT v FROM '.self::tCache." where k='{$key}' and t > UNIX_TIMESTAMP() ");
		return $rs===false?$default:json_decode($rs->fetchColumn(),true);
	}

	final public static function ex($key,$expired=86400)
	{
		$t=$expired>864000?$expired:time()+$expired;
		return self::exec('UPDATE '.self::tCache." SET t={$t} where k='{$key}' ");
	}

	final public static function clear($key=null)
	{
		if($key)
		{
			$sql='DELETE FROM '.self::tCache.($key===true?null:(" WHERE k IN (".(is_array($key)?implode(',',$key):$key).") "));
		}
		else
		{
			$sql="DELETE FROM ".self::tCache." WHERE t < UNIX_TIMESTAMP() ";
		}
		return self::exec($sql);
	}

}
