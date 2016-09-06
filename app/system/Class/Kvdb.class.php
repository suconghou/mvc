<?php

/**
 * Kvdb 小数据持久化存储
 */

final class Kvdb
{
	const tCache='`kvdb`';
	private static $instance;

	final public static function ready($file='kvdb.db')
	{
		if(!self::$instance)
		{
			self::$instance=new SQLite3($file);
			self::$instance->exec('PRAGMA SYNCHRONOUS=OFF');
			self::$instance->exec('PRAGMA CACHE_SIZE =8000');
			self::$instance->exec('PRAGMA TEMP_STORE = MEMORY');
			self::$instance->exec('CREATE TABLE IF NOT EXISTS '.self::tCache.' ("k" text NOT NULL, "v" text NOT NULL, "t" integer NOT NULL, PRIMARY KEY ("k") )');
		}
		return self::$instance;
	}

	final public static function get($key,$default=null)
	{
		$value=self::ready()->querySingle('SELECT v FROM '.self::tCache." WHERE k='{$key}' and t > (SELECT strftime('%s', 'now')) ");
		return $value?json_decode($value,true):$default;
	}

	final public static function set($key,$value,$expired=86400)
	{
		$value=json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		return self::ready()->exec('REPLACE INTO '.self::tCache." (k,v,t) VALUES ('{$key}','{$value}',(SELECT strftime('%s', 'now')+{$expired}) ) ");
	}

	final public static function clear($key=null)
	{
		if($key)
		{
			$sql='DELETE FROM '.self::tCache.($key===true?null:(" WHERE `k` IN (".(is_array($key)?implode(',',$key):$key).")"));
		}
		else
		{
			$sql='DELETE FROM '.self::tCache." WHERE t < ".time();
		}
		return self::ready()->exec($sql);
	}
}
