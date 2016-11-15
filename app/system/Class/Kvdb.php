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
			foreach (['PRAGMA SYNCHRONOUS=OFF','PRAGMA CACHE_SIZE=8000','PRAGMA TEMP_STORE=MEMORY','CREATE TABLE IF NOT EXISTS '.self::tCache.' ("k" text NOT NULL, "v" text NOT NULL, "t" integer NOT NULL, PRIMARY KEY ("k") )'] as $sql)
			{
				self::$instance->exec($sql);
			}
		}
		return self::$instance;
	}

	final public static function set($key,$value,$expired=86400)
	{
		$t=$expired>2592000?$expired:time()+$expired;
		$stm=self::ready()->prepare('REPLACE INTO '.self::tCache." (k,v,t) VALUES ('$key',:v,$t)");
		$stm->bindValue(':v',json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		return (bool)$stm->execute();
	}


	final public static function mset(array $set,$expired=86400,$i=0)
	{
		$t=$expired>2592000?$expired:time()+$expired;
		$holders=array_map(function($k)use($t){return "('{$k}',?,{$t})";},array_keys($set));
		$stm=self::ready()->prepare('REPLACE INTO '.self::tCache.' (k,v,t) VALUES '.implode(',',$holders));
		array_walk($set,function($v)use(&$stm,&$i){$stm->bindValue(++$i,json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));});
		return (bool)$stm->execute();
	}

	final public static function get($key,$default=null)
	{
		$value=self::ready()->querySingle('SELECT v FROM '.self::tCache." WHERE k='{$key}' and t > (SELECT strftime('%s', 'now')) ");
		return $value?json_decode($value,true):$default;
	}

	final public static function mget(array $keys=null,$i=0)
	{
		if($keys)
		{
			$stm=self::ready()->prepare('SELECT k,v FROM '.self::tCache.' WHERE k IN ('.implode(',',array_fill(0,count($keys),'?')).") AND t > (SELECT strftime('%s', 'now')) ");
			array_walk($keys,function($v)use(&$stm,&$i){$stm->bindValue(++$i,$v);});
			$res=$stm->execute();
			$result=[];
			while($tmp=$res->fetchArray(SQLITE3_ASSOC))
			{
				$result[$tmp['k']]=json_decode($tmp['v'],true);
			}
			foreach (array_diff($keys,array_keys($result)) as $k)
			{
				$result[$k]=null;
			}
			return $result;
		}
		else
		{
			$res=self::ready()->query('SELECT k,v FROM '.self::tCache." WHERE t > (SELECT strftime('%s', 'now')) ");
			$result=[];
			while($tmp=$res->fetchArray(SQLITE3_ASSOC))
			{
				$result[$tmp['k']]=json_decode($tmp['v'],true);
			}
			return $result;
		}
	}

	final public static function ex($key,$expired=86400)
	{
		$t=$expired>2592000?$expired:time()+$expired;
		$stm=self::ready()->prepare('UPDATE '.self::tCache." SET t={$t} WHERE k=:k");
		$stm->bindValue(':k',$key);
		return (bool)$stm->execute();
	}

	final public static function clear($key=[],$i=0)
	{
		if($key)
		{
			$stm=self::ready()->prepare('DELETE FROM '.self::tCache.(is_array($key)?(" WHERE k IN (".implode(',',array_fill(0,count($key),'?')).") "):null));
			is_array($key)&&array_walk($key,function($v)use(&$stm,&$i){$stm->bindValue(++$i,$v);});
			return (bool)$stm->execute();
		}
		return self::ready()->exec('DELETE FROM '.self::tCache." WHERE t < (SELECT strftime('%s', 'now'))");
	}
}
