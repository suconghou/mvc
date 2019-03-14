<?php

/**
 * @author suconghou
 * @blog http://blog.suconghou.cn
 * @link https://github.com/suconghou/mvc
 * @version 1.9.15
 */

class app
{
    private static $global;

    public static function start(array $config)
    {
        try
        {
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'],$_SERVER['HTTP_IF_NONE_MATCH'])&&(count($param=explode('-',ltrim($_SERVER['HTTP_IF_NONE_MATCH'],'W/')))==2))
            {
                $last=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
                list($expired,$cacheTime)=$param;
                if($expired>$_SERVER['REQUEST_TIME']||($last+$cacheTime>$_SERVER['REQUEST_TIME']))
                {
                    header('Cache-Control: public, max-age='.($expired-$_SERVER['REQUEST_TIME']));
                    return header('Expires: '.gmdate('D, d M Y H:i:s',$expired).' GMT',true,304);
                }
			}
			$cli = defined('STDIN') && defined('STDOUT');
			if($cli)
			{
				$uri=implode('/',$GLOBALS['argv']);
			}
			else
			{
				list($uri)=explode('?',$_SERVER['REQUEST_URI'],2);
				if(stripos($uri,$_SERVER['SCRIPT_NAME'])===0)
				{
					$uri = substr($uri,strlen($_SERVER['SCRIPT_NAME']));
				}
			}

            $varPath = VAR_PATH;

            define('VAR_PATH_LOG',$varPath.'log'.DIRECTORY_SEPARATOR);
            define('VAR_PATH_HTML',$varPath.'html'.DIRECTORY_SEPARATOR);

			$file = self::file($uri);
            if(is_file($file))
            {
                $expire=filemtime($file);
                if($_SERVER['REQUEST_TIME']<$expire)
                {
                    header('Expires: '.gmdate('D, d M Y H:i:s',$expire).' GMT');
                    header('Cache-Control: public, max-age='.($expire-$_SERVER['REQUEST_TIME']));
                    header('X-Cache: Hit');
                    return isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304):(header('Last-Modified: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']).' GMT',true,200)||readfile($file));
                }
                unlink($file);
			}

			$request_method = $_SERVER['REQUEST_METHOD']??'GET';
            list($uri_match,$r,$matches,$fn,$notfound,$errfound)=route::run($uri,$request_method);
			
			try
			{
				spl_autoload_register(function($name)
				{
					if(is_file($file=CONTROLLER_PATH."{$name}.php")||is_file($file=MODEL_PATH."{$name}.php")||is_file($file=LIB_PATH."{$name}.php")||is_file($file=LIB_PATH."{$name}.phar.php"))
					{
						require_once $file;
					}
				});
				set_error_handler(function(int $errno,string $errstr,string $errfile,int $errline)
				{
					throw new Exception(sprintf('%s%s',$errstr,$errfile?(' in file '.$errfile.($errline?"({$errline})":'')):''),$errno);
				});
				if($uri_match) // 正则模式已匹配,此处处理闭包模式与插件模式
				{
					if($fn instanceof closure)
					{
						return call_user_func_array($fn,$matches);
					}
					else if(is_string($fn))
					{
						//插件模式,根据路由触发一个类
						return call_user_func_array([self,'load'],array_merge([$fn],$matches));
					}
					else if(is_array($fn))
					{
						return call_user_func_array([self,'run'],array_merge($fn,$matches));
					}
					throw new Exception("error route handler",101);
				}
				return self::run($r);
			}
			catch(Exception $e)
			{
				$errCode = $e->getCode();
				if($errCode==404 && !empty($notfound))
				{

				}
				else if($errCode==500 && !empty($errfound))
				{

				}
				else
				{
					throw $e;
				}
			}
			catch(Error $e)
			{
				throw $e;
			}
        }
        catch(Exception $e)
        {
            $err=$e->getTraceAsString();
            $errMsg = $e->getMessage();
            $errCode = $e->getCode();
            if ($cli)
            {
                echo $errCode,' ',$errMsg,PHP_EOL,$err,PHP_EOL;
            }
            else
            {
                $errs = str_replace(PHP_EOL,'</p><p>',$err);
                echo "<div style='margin:2% auto;width:80%;box-shadow:0 0 5px #f00;padding:1%;font:italic 14px/20px Georgia,Times New Roman;word-wrap:break-word;'><p>ERROR({$errCode}) {$errMsg}</p><p>{$errs}</p></div>";
            }
        }
        catch(Error $e)
        {
            $err=$e->getTraceAsString();
            $errMsg = $e->getMessage();
            $errCode = $e->getCode();
            if ($cli)
            {
                echo $errCode,' ',$errMsg,PHP_EOL,$err,PHP_EOL;
            }
            else
            {
                $errs = str_replace(PHP_EOL,'</p><p>',$err);
                echo "<div style='margin:2% auto;width:80%;box-shadow:0 0 5px #f00;padding:1%;font:italic 14px/20px Georgia,Times New Roman;word-wrap:break-word;'><p>ERROR({$errCode}) {$errMsg}</p><p>{$errs}</p></div>";
            }
        }
        finally
        {

        }
    }

    


	private static function cli($phar=false)
	{
		$router=$GLOBALS['argv'];
		$script=basename(array_shift($router));
		if($GLOBALS['argc']>1)
		{
			$phar||chdir(ROOT);
			$ret=self::regex('/'.implode('/',$router));
			return is_object($ret)?$ret:(($GLOBALS['app']['router']=$ret?$ret:$router)&&self::run($GLOBALS['app']['router']));
		}
		if($phar)
		{
			return self::run([DEFAULT_CONTROLLER,DEFAULT_ACTION]);
		}
		try
		{
			$pharName=rtrim($script,'php').'phar';
			$path=ROOT.$pharName;
			is_file($path)&&unlink($path);
			$phar=new Phar($path,FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::KEY_AS_FILENAME|FilesystemIterator::SKIP_DOTS,$pharName);
			$phar->startBuffering();
			$dirObj=new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT)),'/^[\w\/\-\\\.:]+\.php$/i');
			foreach($dirObj as $file)
			{
				$phar->addFromString(substr($file,strlen(ROOT)),php_strip_whitespace($file));
			}
			$phar->setStub((getenv('EXE')?"#!/usr/bin/env php".PHP_EOL:null)."<?php Phar::mapPhar('$pharName');require 'phar://{$pharName}/{$script}';__HALT_COMPILER();");
			$phar->stopBuffering();
			getenv('EXE')&&chmod($path,0700);
			echo "{$phar->count()} files stored in {$path}".PHP_EOL;
		}
		catch(Exception $e)
		{
			echo $e->getMessage().PHP_EOL;
		}
    }

    public static function load($fn,...$args)
    {
        var_dump('load');die;
    }


	//参数必须是数组,第一个为控制器,第二个为方法,后面的为参数
	public static function run(array $r)
	{
        if(empty($r[0]))
        {
            $r[0]='home';
        }
        else if(!preg_match('/^[a-z]\w{0,20}$/i',$r[0]))
        {
            throw new Exception(sprintf('request controller %s error',$r[0]),404);
        }
        if(empty($r[1]))
        {
            $r[1]='index';
        }
        else if(!preg_match('/^[a-z]\w{0,20}$/i',$r[1]))
        {
            throw new Exception(sprintf('request action %s:%s error',$r[0],$r[1]),404);
        }
        if(!method_exists($r[0],$r[1]))
        {
            throw new Exception(sprintf('request action %s:%s not exist',$r[0],$r[1]),404);
        }
        if(empty(self::$global['sys.'.$r[0]]))
        {
            self::$global['sys.'.$r[0]]=new $r[0];
        }
        if(!is_callable([self::$global['sys.'.$r[0]],$r[1]]))
        {
            throw new Exception(sprintf('request action %s:%s not callable',$r[0],$r[1]),404);
        }
        return call_user_func_array([self::$global['sys.'.$r[0]],$r[1]],array_slice($r,2));
	}
	public static function log($msg,$type='DEBUG',$file=null)
	{
		if(is_writable(VAR_PATH_LOG)&&(DEBUG||(($type=strtoupper($type))=='ERROR')))
		{
			$path=VAR_PATH_LOG.($file?$file:date('Y-m-d')).'.log';
			$msg=$type.'-'.date('Y-m-d H:i:s').' ==> '.(is_scalar($msg)?$msg:PHP_EOL.print_r($msg,true)).PHP_EOL;
			return error_log($msg,3,$path);
		}
	}

	public static function cost($type=null)
	{
		switch ($type)
		{
			case 'time': return round((microtime(true)-self::$global['sys-start-time']),4);
			case 'memory': return memory_get_usage()-self::$global['sys-start-memory'];
			case 'query': return db::$sqlCount?:0;
			default: return ['time'=>round((microtime(true)-self::$global['sys-start-time']),4),'memory'=>memory_get_usage()-self::$global['sys-start-memory'],'query'=>db::$sqlCount?:0];
		}
	}
	public static function file(string $r="",bool $delete=false)
	{
		$file=sprintf('%s%u.html',VAR_PATH_HTML,crc32(ROOT.strtolower($r)));
		return $delete?(is_file($file)&&unlink($file)):$file;
	}
	public static function cache(int $s=0)
	{
        header('Expires: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']+$s).' GMT');
        header("Cache-Control: public, max-age={$s}");
        header('Last-Modified: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']).' GMT');
        return header('ETag: W/'.($_SERVER['REQUEST_TIME']+$s).'-'.$s);
	}
	public static function get($key,$default=null)
	{
		return isset(self::$global[$key])?self::$global[$key]:$default;
	}
	public static function set($key,$value)
	{
		self::$global[$key]=$value;
		return self::$global;
	}
	public static function config($key=null,$default=null,$cfgfile='config.php')
	{
		$config=is_array($cfgfile)?$cfgfile:(isset(self::$global[$cfgfile])?self::$global[$cfgfile]:(self::$global[$cfgfile]=include $cfgfile));
		if($key=array_filter(explode('.',$key,9)))
		{
			foreach ($key as $item)
			{
				if(is_array($config)&&isset($config[$item]))
				{
					$config=$config[$item];
				}
				else
				{
					return $default;
				}
			}
		}
		return $config;
	}
	public static function on($event,closure $task)
	{
		return self::$global['event'][$event]=$task;
	}
	public static function off($event)
	{
		unset(self::$global['event'][$event]);
	}
	public static function emit($event,$args=[])
	{
		return empty(self::$global['event'][$event])?:call_user_func_array(self::$global['event'][$event],is_array($args)?$args:[$args]);
	}
	public static function __callStatic($fn,$args=[])
	{
		if(isset(self::$global['event'][$fn]))
		{
			return call_user_func_array(self::$global['event'][$fn],$args);
		}
		throw new Exception("call error static method {$fn}",500);
	}

}

class route
{
    private static $routes=[];
    private static $notfound;
    private static $errfound;

    static function u($path=null,$query=null,$host=null)
    {
        $prefix='';
        if($host===true)
        {
            $protocol=(isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off'))?"https":"http";
            $host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
            $prefix = "{$protocol}://{$host}";
        }
        else if($host)
        {
            $prefix = $host;
        }
        if(is_array($query))
        {
            $query=http_build_query($query);
        }
        if($query)
        {
            return "{$prefix}{$path}?{$query}";
        }
        return "{$prefix}{$path}";
    }

    static function to(string $url,int $timeout=0)
    {
        if(in_array($timeout,[0,301,302,303,307,308],true))
        {
            header("Location:{$url}",true,$timeout);
        }
        else
        {
            header("Refresh:{$timeout};url={$url}",true,302);
        }
        exit(header('Cache-Control:no-cache, no-store, max-age=0, must-revalidate'));
    }

    static function get(string $regex,$fn)
    {
        return self::add($regex,$fn,['GET']);
    }

    static function post(string $regex,$fn)
    {
        return self::add($regex,$fn,['POST']);
    }

    static function put(string $regex,$fn)
    {
        return self::add($regex,$fn,['PUT']);
    }

    static function delete(string $regex,$fn)
    {
        return self::add($regex,$fn,['DELETE']);
    }

    static function head(string $regex,$fn)
    {
        return self::add($regex,$fn,['HEAD']);
    }

    static function any(string $regex,$fn,array $methods=['GET','POST','PUT','DELETE','HEAD'])
    {
        return self::add($regex,$fn,$methods);
    }

    static function add(string $regex,$fn,array $methods)
    {
        self::$routes[$regex]=[$fn,$methods];
    }

    static function notfound($fn)
    {
        self::$notfound = $fn;
    }

    static function errfound($fn)
    {
        self::$errfound = $fn;
    }

    static function run(string $uri,string $m)
    {
        $r=array_values(array_filter(explode('/',$uri,9),'strlen'));
        $uri = '/'.implode('/',$r);
        $ret = self::match($uri,$m);
        if($ret)
        {
            list($url,$matches,$fn) = $ret;
            return [$url,$r,$matches,$fn,self::$notfound,self::$errfound];
        }
        return [false,$r,[],null,self::$notfound,self::$errfound];
    }

    private static function match(string $uri,string $m)
    {
        foreach(self::$routes as $regex => list($fn,$methods))
        {
            if(in_array($m,$methods,true) && preg_match("/^{$regex}$/",$uri,$matches))
            {
                $url=array_shift($matches);
                return [$url,$matches,$fn];
            }
        }
        return false;
    }

}

class request
{
	public static function post($key=null,$default=null,$clean=false)
	{
		return self::getVar($_POST,$key,$default,$clean);
	}
	public static function get($key=null,$default=null,$clean=false)
	{
		return self::getVar($_GET,$key,$default,$clean);
	}
	public static function param($key=null,$default=null,$clean=false)
	{
		return self::getVar($_REQUEST,$key,$default,$clean);
	}
	public static function server($key=null,$default=null,$clean=flase)
	{
		return self::getVar($_SERVER,$key,$default,$clean);
	}
	public static function cookie($key=null,$default=null,$clean=false)
	{
		return self::getVar($_COOKIE,$key,$default,$clean);
	}
	public static function session($key=null,$default=null,$clean=false)
	{
		isset($_SESSION)||session_start();
		return self::getVar($_SESSION,$key,$default,$clean);
	}
	public static function input($json=true,$key=null,$default=null)
	{
		$str=file_get_contents('php://input');
		$json?($data=json_decode($str,true)):parse_str($str,$data);
		return $key?(isset($data[$key])?$data[$key]:$default):$data;
	}
	public static function ip($default=null)
	{
		return isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$default;
	}
	public static function ua($default=null)
	{
		return isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:$default;
	}
	public static function refer($default=null)
	{
		return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$default;
	}
	public static function https()
	{
		return isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off')?:false;
	}
	public static function is($m=null,closure $callback=null)
	{
		$t=isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'GET';
		return $m?(($t===strtoupper($m))?($callback?$callback():true):false):$t;
	}
	public static function verify(array $rule,$callback=false,$post=true)
	{
		$keys=[];
		$data=$post===true?$_POST:(is_array($post)?$post:$_REQUEST);
		foreach ($rule as $key => $value)
		{
			$keys[]=is_int($key)?$value:$key;
		}
		foreach ($data as $key => $value)
		{
			if(!in_array($key,$keys))
			{
				unset($data[$key]);
			}
		}
		foreach ($keys as $key)
		{
			$data[$key]=isset($data[$key])?$data[$key]:null;
		}
		return validate::verify($rule,$data,$callback);
	}
	public static function getVar(&$origin,$var,$default=null,$clean=false)
	{
		if($var)
		{
			if(is_array($var))
			{
				$data=[];
				foreach ($var as $k)
				{
					$data[$k]=isset($origin[$k])?($clean?self::clean($origin[$k],$clean):$origin[$k]):$default;
				}
				return $data;
			}
			return isset($origin[$var])?($clean?self::clean($origin[$var],$clean):$origin[$var]):$default;
		}
		return $origin;
	}
	public static function clean($val,$type=null)
	{
		switch ($type)
		{
			case 'int': return intval($val);
			case 'float': return floatval($val);
			case 'string': return trim(strval($val));
			case 'xss': return filter_var(htmlspecialchars(strip_tags($val),ENT_QUOTES),FILTER_SANITIZE_STRING);
			case 'html': return trim(strip_tags($val));
			case 'en': return preg_replace('/[\x80-\xff]/','',$val);
			default: return $type?sprintf($type,$val):trim($val);
		}
	}
}

class validate
{
	public static function verify($rule,$data,$callback=false)
	{
		try
		{
			$switch=[];
			foreach($rule as $k=>&$item)
			{
				if(isset($data[$k]))//存在要验证的数据
				{
					foreach($item as $type=>$msg)
					{
						if($msg instanceof closure)
						{
							$data[$k]=$msg($data[$k],$k);
						}
						else if(is_array($msg))
						{
							if(!in_array($data[$k],$msg))
							{
								throw new Exception($type, 1);
							}
						}
						else if(is_int($type))
						{
							$switch[$k]=$msg;
						}
						else if(!self::check($data[$k],$type))
						{
							throw new Exception($msg,-11);
						}
					}
				}
				else if(isset($item['require'])) //标记为require,却不存在
				{
					throw new Exception($item['require'],-10);
				}
			}
		}
		catch(Exception $e)
		{
			if($callback===false)
			{
				throw $e;
			}
			$data=['code'=>$e->getCode(),'msg'=>$e->getMessage()];
			return $callback?(($callback instanceof closure)?$callback($data,$e):json($data)):false;
		}
		foreach($switch as $from=>$to)
		{
			$data[$to]=$data[$from];
			unset($data[$from]);
		}
		return $data; //数据全部校验通过
	}
	private static function check($item,$type)
	{
		if(strpos($type,'=')&&(list($key,$val)=explode('=',$type,2)))
		{
			switch ($key)
			{
				case 'minlength': return strlen($item)>=$val;
				case 'maxlength': return strlen($item)<=$val;
				case 'length': return strlen($item)==$val;
				case 'eq': return trim($item)==trim($val);
				case '!eq': return strtolower(trim($item))==strtolower(trim($val));
				default: return self::this($type,$item);
			}
		}
		else
		{
			switch ($type)
			{
				case 'need': return $item;
				case 'require': return $item===0 || $item;
				case 'email': return self::email($item);
				case 'username': return self::username($item);
				case 'password': return self::password($item);
				case 'phone': return self::phone($item);
				case 'url': return self::url($item);
				case 'ip': return self::ip($item);
				case 'idcard': return self::idcard($item);
				default: return self::this($type,$item);
			}
		}
	}
	public static function email($email)
	{
		return filter_var($email,FILTER_VALIDATE_EMAIL);
	}
	public static function phone($phone)
	{
		return preg_match("/^1[34578][0-9]{9}$/",$phone);
	}
	public static function url($url)
	{
		return filter_var($url,FILTER_VALIDATE_URL);
	}
	public static function ip($ip)
	{
		return filter_var($ip,FILTER_VALIDATE_IP);
	}
	//中国大陆身份证号(15位或18位)
	public static function idcard($id)
	{
		return preg_match('/^\d{15}(\d\d[0-9xX])?$/',$id);
	}
	//字母数字汉字,不能全是数字
	public static function username($username)
	{
		return is_numeric($username)?false:preg_match('/^[\w\x{4e00}-\x{9fa5}]{3,20}$/u',$username);
	}
	//数字/大写字母/小写字母/标点符号组成，四种都必有，8位以上
	public static function password($pass)
	{
		return preg_match('/^(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/',$pass);
	}
	public static function this($pattern,$subject)
	{
		return preg_match($pattern,$subject);
	}
}

class db
{
    private static $_instances=[];
    protected $_pdo;

    final public static function getVar(string $sql)
	{
		$rs=self::execute($sql,true);
		return $rs?$rs->fetchColumn():$rs;
    }

    final public static function getLine(string $sql,$type=PDO::FETCH_ASSOC)
	{
		$rs=self::execute($sql,true);
		return $rs?$rs->fetch($type):$rs;
    }

    final public static function getData(string $sql,$type=PDO::FETCH_ASSOC)
	{
		$rs=self::execute($sql,true);
		return $rs?$rs->fetchAll($type):$rs;
	}

	final public static function runSql(string $sql)
	{
		return self::execute($sql);
    }

    final public static function insert(array $data,$table=null,bool $ignore=false,bool $replace=false)
	{
		$sql=sprintf('%s %sINTO %s %s',$replace?'REPLACE':'INSERT',$ignore?'IGNORE ':'',$table?:self::getCurrentTable(),self::values($data));
		return self::exec($sql,$data);
	}

	final public static function replace(array $data,$table=null)
	{
		return self::insert($data,$table,false,true);
	}

	final public static function delete(array $where=[],$table=null)
	{
		$sql=sprintf('DELETE FROM %s%s',$table?:self::getCurrentTable(),self::condition($where));
		return self::exec($sql,$where);
	}

	final public static function find(array $where=[],$table=null,string $col='*',array $orderLimit=null,$fetch='fetchAll')
	{
		$sql=sprintf('SELECT %s FROM %s%s%s',$col,$table?:self::getCurrentTable(),self::condition($where),$orderLimit?self::orderLimit($orderLimit):null);
		return self::exec($sql,$where,$fetch);
	}

	final public static function findOne(array $where=[],$table=null,string $col='*',array $orderLimit=[1],$fetch='fetch')
	{
		return self::find($where,$table,$col,$orderLimit,$fetch);
	}

	final public static function findVar(array $where=[],$table=null,string $col='COUNT(1)',array $orderLimit=[1])
	{
		return self::find($where,$table,$col,$orderLimit,'fetchColumn');
	}

	final public static function findPage(array $where=[],$table=null,string $col='*',int $page=1,int $limit=20,array $order=[])
	{
		$total=intval(self::findVar($where,$table));
		$pages=ceil($total/$limit);
		$list=self::find($where,$table,$col,[($page-1)*$limit=>intval($limit)]+$order);
		return ['list'=>$list,'pages'=>$pages,'total'=>$total,'current'=>$page,'prev'=>min($pages,max(1,$page-1)),'next'=>min($pages,$page+1)];
	}

	final public static function update(array $where,array $data,$table=null)
	{
		$sql=sprintf('UPDATE %s SET %s%s',$table?:self::getCurrentTable(),self::values($data,true),self::condition($where));
		$intersect=array_keys(array_intersect_key($data,$where));
		$sql=$intersect?preg_replace(array_map(function($x)use(&$data){$data["{$x}_"]=$data[$x];unset($data[$x]);return sprintf('/:%s/',$x);},$intersect),'\0_',$sql,1):$sql;
		return self::exec($sql,$data+$where);
	}

    final public static function query(array $v)
	{
		return array_map(function($v){return call_user_func_array('self::exec',$v);},func_get_args());
	}

    final public static function init(string $dsn,string $user,string $pass)
	{
		$options=[PDO::ATTR_PERSISTENT=>true,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_TIMEOUT=>3,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_STRINGIFY_FETCHES=>false];
		return new PDO($dsn,$user,$pass,$options);
	}
    // isQuery false:预处理,true:查询,其他'',null:执行SQL
	final public static function execute(string $sql,$isQuery=null)
	{
        $pdo = self::ready();
        return $isQuery===false?($pdo->prepare($sql)):($isQuery?($pdo->query($sql)):($pdo->exec($sql)));
    }

	final public static function lastId()
	{
		return self::ready()->lastInsertId();
    }

    final public static function ready()
    {
        if(empty(static::$_pdo))
        {
            list('dsn'=>$dsn,'user'=>$user,'pass'=>$pass) = app::get('db');
            static::$_pdo = self::init($dsn,$user,$pass);
        }
        return static::$_pdo;
    }

	final public static function getInstance(array $dbConfig)
	{
        list('dsn'=>$dsn,'user'=>$user,'pass'=>$pass) = $dbConfig;
        $instanceKey = $user.$pass.$dsn;
        if(empty(self::$_instances[$instanceKey]))
        {
            $pdoKey = $dsn.$user.$pass;
            if(empty(self::$_instances[$pdoKey]))
            {
                self::$_instances[$pdoKey] = self::init($dsn,$user,$pass);
            }
            self::$_instances[$instanceKey] = new class(self::$_instances[$pdoKey]) extends db {function __construct ($pdo){self::$_pdo=$pdo;}};
        }
        return self::$_instances[$instanceKey];
	}

	final public static function exec($sql,array $bind=null,$fetch=null)
	{
		$stm=self::execute($sql,false);
	    $rs=$stm->execute($bind);
	    return is_string($fetch)?$stm->$fetch():($fetch?$stm:$rs);
	}

	final public static function getCurrentTable()
	{
		return defined('static::table')?static::table:static::class;
	}

	final public static function condition(array &$where,$prefix='WHERE')
	{
		$keys=array_keys($where);
		$condition=$keys?implode(sprintf(' %s ',isset($where[0])?$where[0]:'AND'),array_map(function($v)use(&$where){$x=array_values(array_filter(explode(' ',$v)));$n=null;$k=trim(ltrim($x[0],'!'));if(is_array($where[$v])){$marks=[];$i=0;array_map(function($t)use(&$marks,&$where,&$i){$q='_'.$i++;$marks[]=":{$q}";$where[$q]=$t;},$where[$v]);}else{if($k!=$x[0]){$n=$where[$v];}elseif($x[0]!=$v){$where[$x[0]]=$where[$v];}}$str=sprintf('`%s` %s %s',$k,isset($x[1])?(isset($x[2])?"{$x[1]} {$x[2]}":$x[1]):(is_array($where[$v])?'IN':'='),is_array($where[$v])?sprintf('(%s)',implode(',',$marks)):($n?$n:":{$k}"));if(is_array($where[$v])||$n||$x[0]!=$v){unset($where[$v]);}return $str;},array_filter($keys))):null;
		unset($where[0],$keys);
		return $condition?sprintf('%s(%s)',$prefix?" {$prefix} ":null,$condition):null;
	}

	final public static function values(array &$data,bool $set=false,string $table=null)
	{
		$keys=array_keys($data);
		return $set?implode(',',array_map(function($x)use(&$data){$k=trim($x);if($k!=$x){$data[$k]=$data[$x];unset($data[$x]);}$n=ltrim($k,'!');if($n!=$k){$n=$data[$k];unset($data[$k]);}return sprintf('`%s` = %s',trim(ltrim($k,'!')),$n==$k?":{$k}":$n);},$keys)):sprintf('%s(%s) VALUES (%s)',$table?" `{$table}` ":null,implode(',',array_map(function($x){return sprintf('`%s`',trim(ltrim(trim($x),'!')));},$keys)),implode(',',array_map(function($x)use(&$data){$k=trim($x);$n=trim(ltrim($k,'!'));if($n!=$k){$n=$data[$x];unset($data[$x]);}elseif($k!=$x){$data[$k]=$data[$x];unset($data[$x]);}return $n==$k?":{$k}":$n;},$keys)));
	}

	final public static function orderLimit(array $orderLimit,$limit=[])
	{
		$orderLimit=array_filter($orderLimit,function($x)use($orderLimit,&$limit){if(preg_match('/^\d+$/',$x)){$k=array_search($x,$orderLimit,true);$limit=[$k,$x];return false;}else{return true;}});
		$limit=$limit?" LIMIT ".implode(',',$limit):null;
		$orderLimit?(array_walk($orderLimit,function(&$v,$k){$v=sprintf('%s %s',$k,is_string($v)?$v:($v?'ASC':'DESC'));})):null;
		return sprintf('%s%s',$orderLimit?' ORDER BY '.implode(',',$orderLimit):null,$limit);
	}

	final public function __call($fn,$args=null)
	{
		return self::__callStatic($fn,$args);
	}
	final public static function __callStatic($fn,$args=null)
	{
		if(method_exists(self::ready(),$fn))
		{
			return call_user_func_array([self::$pdo,$fn],$args);
		}
		throw new Exception("method {$fn} not found in class ".static::class,500);
	}
}


function with($class)
{
	if(is_string($class))
	{
		$args=func_get_args();
		$arr=explode('/',array_shift($args),3);
		$m=end($arr);
		$GLOBALS['app']['lib'][$m]=isset($GLOBALS['app']['lib'][$m])?$GLOBALS['app']['lib'][$m]:$m;
		if($GLOBALS['app']['lib'][$m] instanceof $m)
		{
			return $GLOBALS['app']['lib'][$m];
		}
		if(is_file($file=MODEL_PATH."{$class}.php")||is_file($file=CONTROLLER_PATH."{$class}.php")||is_file($file=LIB_PATH.'Class'.DIRECTORY_SEPARATOR."{$class}.php")||is_file($file=LIB_PATH."{$class}.php")||is_file($file=LIB_PATH."{$class}.phar"))
		{
			$ret=require_once $file;
			if(class_exists($m))
			{
				$class=new ReflectionClass($m);
				$GLOBALS['app']['lib'][$m]=$class->newInstanceArgs($args);
				return $GLOBALS['app']['lib'][$m];
			}
			unset($GLOBALS['app']['lib'][$m]);
			return $ret;
		}
		return app::error(404,"can not load {$class}");
	}
	return new response($class);
}

function template($v,array $_data_=null,$callback=null)
{
	if((is_file($_v_=VIEW_PATH.$v.'.php'))||(is_file($_v_=VIEW_PATH.$v)))
	{
		(is_array($_data_)&&!empty($_data_))&&extract($_data_);
		if($callback)
		{
			ob_start()&&include $_v_;
			$contents=ob_get_contents();
			return (ob_end_clean()&&($callback instanceof closure))?$callback($contents):$contents;
		}
		return include $_v_;
	}
	return app::error(404,"file {$_v_} not found");
}

function session($key,$val=null,$delete=false)
{
	isset($_SESSION)||session_start();
	if(is_null($val))
	{
		return $delete?(bool)array_map(function($k){unset($_SESSION[$k]);},is_array($key)?$key:[$key]):request::session($key,null,false);
	}
	return $_SESSION[$key]=$val;
}
function cookie($key,$val=null,$expire=0)
{
	if(is_null($val))
	{
		return request::cookie($key,null,false);
	}
	return call_user_func_array('setcookie',func_get_args());
}
function json(array $data,$callback=null)
{
	$data=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	$callback=$callback===true?(empty($_GET['callback'])?null:$_GET['callback']):$callback;
	$data=$callback?$callback."(".$data.")":$data;
	header('Content-Type: text/'.($callback?'javascript':'json').';charset=utf-8',true,200);
	exit($data);
}
