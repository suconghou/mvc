<?

///mvc 核心框架类库
//包含重要设置和核心类库
//update 2014.02.22
//修改了 base_url()可以没有参数

require 'config.php';

///路由,采用pathinfo模式
function uri_init()
{
	if(empty($_SERVER['PATH_INFO']))
	{
		$uri=array('c'=>'main','a'=>'index');
		return $uri;
	}
	$arr=explode('/',$_SERVER['PATH_INFO']);
	$uri['c']=empty($arr['1'])?'main':$arr['1'];///此处定义了默认控制器
	$uri['a']=empty($arr['2'])?'index':$arr['2'];//此处定义了默认动作
	for($i=3;$i<count($arr);$i++)
	{
		$uri[]=$arr[$i];
	}
	return $uri;
}
///核心进程
function process($arr_uri)
{
	$controller='c/'.$arr_uri['c'].'.php';///控制器文件

	if(is_file($controller))
	{
		require $controller;
		if(class_exists($arr_uri['c']))
		{
			$methods = get_class_methods($arr_uri['c']);
			(in_array($arr_uri['a'], $methods, true))||show_errorpage('已成功加载控制器文件并载入类 '.$arr_uri['c'],'但该类中不存在方法名 '.$arr_uri['a']);
			$arr_uri['c']=new $arr_uri['c']();//实例化控制器
			call_user_func_array(array($arr_uri['c'],$arr_uri['a']), array_slice($arr_uri,2));//传入参数
 			///控制器加载完成
		}
		else
		{
			$msg1='已成功加载控制器文件 '.$controller;
			$msg2='但未找到类 '.$arr_uri['c'];
			show_errorpage($msg1,$msg2);
		}
	}
	else
	{
		$msg1='尝试加载控制器时失败!';
		$msg2='不存在控制器文件 '.$controller;
		show_errorpage($msg1,$msg2);
	}
	
	

}

function show_errorpage($severity=null, $message=null, $filename=null, $lineno=null)
{
	$header="HTTP/1.1 500 Internal Server Error";
	$config=$GLOBALS['config'];
	if(!$config['debug'])///不显示错误消息
	{
		error_reporting(0);
		$severity='程序出现了一些小问题';
		$message='请联系管理员解决此问题';
		$filename=null;
		$lineno=null;
	}

	if($severity==404)
	{
		$message='你访问的地址有误!';
		$header="HTTP/1.1 404 Not Found";
	}
	$html='<div style="margin:2% auto;width:80%;box-shadow:0px 0px 8px #999;padding:2%;">';
	$html.='<h1>Something Error !</h1>';
	$html.="<h2>{$severity}</h2>";
	$html.="<h3>{$message}</h3>";
	if($filename&&$lineno)
	{
		$html.="<h4>{$filename} on line {$lineno}</h4>";
	}
	header('Content-Type:text/html;charset=utf-8');
	$html.="</div>";
	header($header);
	exit($html);

}




///control 层
class controller
{
	private $cache=null;//缓存单位分钟
	private $uri;
	function __construct()///没有执行
	{
		
	}
	///view 层
	function loadview($file,$data=null)
	{

		$this->uri=$GLOBALS['uri'];
		$file='v/'.$file.'.php';
		if(is_file($file))
		{
			is_array($data)||empty($data)||show_errorpage('向视图文件 '.$file.' 传入参数时出错','传入的参数不是数组格式!');
			empty($data)||extract($data);
			$times=round((microtime(true)-$GLOBALS['t']),4);//计时
			ob_start("ob_gzhandler");  
			require $file;
			if($this->cache)
			{
				$contents=ob_get_contents();	
				$cache_file='v/cache/'.implode('-',$this->uri).'.html';
				file_put_contents($cache_file,$contents);
				touch($cache_file,time()+($this->cache)*60);
						
			}
			else
			{

				ob_end_flush();
			}
			
		}
		else
		{
			show_errorpage('尝试加载视图时失败','不存在视图文件 '.$file);
		}
		die;////loadview终结整个进程
	}


	function cache($min=10)
	{
		$this->cache=$min;
	}

	function loadlibrary($file)//加载类库
	{
		$filepath='s/'.$file.'.class.php';
		is_file($filepath)||show_errorpage('尝试加载类库时出错','不存在类库文件 '.$filepath);
		require $filepath;
		class_exists($file)||show_errorpage('成功加载类库文件 '.$filepath,'但仍未找到类 '.$file);
		$this->$file=new $file();//对类库实例化

	}

	function loadmodel($file)//加载model
	{
		$filepath='m/'.$file.'.php';
		is_file($filepath)||show_errorpage('尝试加载模型文件时失败','不存在模型文件 '.$filepath);
		require $filepath;
		class_exists($file)||show_errorpage('成功加载模型文件'.$filepath,'但仍未找到类'.$file);
		$this->$file=new $file();///对模型实例化

	}
	///加载任意的PHP文件
	function load($file)
	{
		$filepath='s/'.$file.'.php';
		is_file($filepath)||show_errorpage('尝试加载函数库时出错','不存在函数库文件'.$filepath);
		require $filepath;
	}

	function uri($segment)
	{
		return $this->arr_uri[$segment]; 	
	}


}///end class controller


/**
* model 层
*/
class model 
{
	static $link;///单例模式
	static $debug=false;
	static $result=null;
	static $select_data;
	static $update_data;
	static $delete_data;
	static $insert_data;
	static $where_data;
	static $from_table;

	static $sum_data;
	static $avg_data;
	static $max_data;
	static $min_data;
	static $count_data;

	function __construct()
	{
		$cfg=$GLOBALS['db'];
		if (isset($cfg['sqlite'])&&!empty($cfg['sqlite']))
		{
			try
			{
				self::$link=new PDO("sqlite:".$cfg['sqlite']);
			}
			catch ( Exception $e )
			{
            	show_errorpage('尝试连接SQLITE数据库时出错','数据库连接失败: '.$e->getMessage());
        	}
		}
		else
		{
			try
			{			
				$cfg['db_host']=isset($cfg['db_host'])?$cfg['db_host']:'localhost';
				$cfg['db_port']=isset($cfg['db_port'])?$cfg['db_port']:'3306';
				$dsn="mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};port={$cfg['db_port']}";
				self::$link= new PDO ($dsn,$cfg['db_user'],$cfg['db_pass'],array (PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				self::$link->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			}
			catch ( Exception $e )
			{
           	 	show_errorpage('尝试连接MYSQL数据库时出错','数据库连接失败: '.$e->getMessage());
        	}
		}
		

	}

	function debug($debug=true)
	{
		self::$debug=$debug?true:false;
		return $this;		
	}

	//运行Sql语句,不返回结果集,但会返回成功与否.仅支持exec,不能用于select
	function runSql($sql)
	{
		try
		{
			$rs=self::$link->exec($sql);
			return $rs;
		}
		catch (PDOException $e)
		{
			show_errorpage('执行SQL语句 '.$sql.' 时出错 ',$e->getMessage());
		}
		
		
	}

	////运行Sql,以多维数组方式返回结果集
	function getData($sql)
	{
		try
		{
			$rs=self::$link->query($sql);
			return $rs->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			show_errorpage('执行SQL语句 '.$sql.' 时出错 ',$e->getMessage());
		}


	}
	//运行Sql,以数组方式返回结果集第一条记录
	function getLine($sql)
	{
		try
		{
			$rs=self::$link->query($sql);
			return $rs->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			show_errorpage('执行SQL语句 '.$sql.' 时出错 ',$e->getMessage());
		}

	}
	//运行Sql,返回结果集第一条记录的第一个字段值
	function getVar($sql)
	{
		try
		{
			$rs=self::$link->query($sql);
			return $rs->fetchColumn();
		}
		catch (PDOException $e)
		{
			show_errorpage('执行SQL语句 '.$sql.' 时出错 ',$e->getMessage());
		}


	}

	//智能查询,但自己不会返回结果,他供其他函数调用,但也可直接使用,支持debug模式
	function query($sql)
	{
		if(self::$debug)
		{
			show_errorpage('调试模式','将要执行SQL语句 '.$sql);
		}
		else
		{
			try
			{	
				if (stristr($sql, 'select'))
				{
					
					self::$result=self::$link->query($sql);
					return $this;
					
				}
				else
				{	
					self::$result=self::$link->exec($sql);
					return $this;
				}
			}
			catch (PDOException $e)
			{
				show_errorpage('执行SQL语句 '.$sql.' 时出错 ',$e->getMessage());
			}
			
		}
	}
	function escape($str)
	{
		return mysql_real_escape_string($str);
	}
	function insert($table,$insert_data)
	{
		is_array($insert_data)||exit('insert的数据应是数组格式!');
		foreach ($insert_data as $key => $value) 
		{	
			$k[]='`'.$key.'`';
			$v[]='"'.$value.'"';
		}
		$strv=null;$strk=null;
		$strv.=implode(',',$v);     
   		$strk.=implode(",",$k);
		self::$insert_data="INSERT INTO {$table} ({$strk}) VALUES ({$strv})";
		return $this;
	}
	function delete($table)
	{
		self::$delete_data="DELETE FROM {$table} ";
		return $this;
	}
	function update($table,$update_data)
	{
		is_array($update_data)||exit('update的数据应是数组格式!');
		foreach ($update_data as $key => $value) 
		{
			$v[]=$key.'='."'".$value."'";
		}
		$strv.=implode(',',$v);   
		self::$update_data="UPDATE {$table} SET ".$strv;
		return $this;

	}
	function select($select)
	{
		self::$select_data=$select;
		return $this;
	}
	function from($table)
	{
		self::$from_table=$table;
		return $this;
	}
	function where($where)
	{
		is_array($where)||exit('where的条件应为数组格式!');
		foreach ($where as $key => $value) 
		{		
			$k[]='(`'.$key.'`="'.$value.'")';
			
		}
   		$strk.=implode("and",$k);

		self::$where_data=' WHERE '."({$strk})";
		return $this;
	}
	function order_by($type)
	{

	}
	function max($column)
	{
		self::$max_data="SELECT MAX({$column}) ";
		return $this;
	}
	function min($column)
	{		
		self::$min_data="SELECT MIN({$column}) ";
		return $this;

	}
	function avg($column)
	{
		self::$avg_data="SELECT AVG({$column}) ";
		return $this;
	}
	function count($column)
	{
		self::$count_data="SELECT COUNT({$column}) ";
		return $this;
	}
	function sum($column)
	{
		self::$sum_data="SELECT SUM({$column}) ";
		return $this;
	}

	function lastid()
	{
		return self::$link->lastInsertId();
	}
	function commitTransaction($sqlarr)
	{	
		is_array($sqlarr)||exit('提交的事务应是一个SQL语句数组');
		try
		{
			self::$link->beginTransaction();
			foreach ($sqlarr as $sql)
			{
				self::$link->exec($sql);
			}
			self::$link->commit();
		}
		catch(Exception $e)
		{ 
			self::$link->rollBack(); 
			exit("Failed: " . $e->getMessage()); 
		} 
	}

	function getResult()
	{	
		if(self::$result===null)///之前串的都是select() update() 等,先要query()
		{
			//组合sql,并query
			$select=isset(self::$select_data)?"SELECT ".self::$select_data." ":null;
			$from=isset(self::$from_table)?" FROM ".self::$from_table." ":null;
			$where=isset(self::$where_data)?self::$where_data:null;
			$update=isset(self::$update_data)?self::$update_data:null;
			$delete=isset(self::$delete_data)?self::$delete_data:null;
			$insert=isset(self::$insert_data)?self::$insert_data:null;
			
			$sum=isset(self::$sum_data)?self::$sum_data:null;
			$avg=isset(self::$avg_data)?self::$avg_data:null;
			$max=isset(self::$max_data)?self::$max_data:null;
			$min=isset(self::$min_data)?self::$min_data:null;
			$count=isset(self::$count_data)?self::$count_data:null;
			if($update)
			{
				$sql=$update.$where;
			}
			else if($delete)
			{
				$sql=$delete.$where;
			}
			else if($insert)
			{
				$sql=$insert;
			}
			else if($select)
			{
				$sql=$select.$from.$where;
			}
			else if($sum)
			{
				$sql=$sum.$from.$where;
			}
			else if($avg)
			{
				$sql=$avg.$from.$where;
			}
			else if($max)
			{
				$sql=$max.$from.$where;
			}
			else if($min)
			{
				$sql=$min.$from.$where;
			}
			else if($count)
			{
				$sql=$count.$from.$where;
			}

			
			

			$this->query($sql);
			if(self::$result===0||self::$result===1)///exec的后代
			{

				return self::$result;
			}
			else
			{   
				return $this;
			}
		}
		else if(self::$result===0||self::$result===1)//如果之前串的是query(),则直接返回结果就好了
		{
			return self::$result;
		}
		else///result是结果集了,需要fetch()
		{
			return $this;
		}
		
	}

	function fetchColumn($mode=PDO::FETCH_ASSOC)
	{	
		return self::$result->fetchColumn($mode);
	}
	function fetch($mode=PDO::FETCH_ASSOC)
	{
		return self::$result->fetch($mode);
	}
	function fetchAll($mode=PDO::FETCH_ASSOC)
	{
		return self::$result->fetchAll($mode);
	}
	function __destruct()
	{
		self::$link=null;
	}
}//end class model



/////////some functions blow


function redirect($url,$seconds=0)
{

	header("Refresh: {$seconds}; url={$url}");
	exit();

}


function base_url($path=null)
{
	return('http://'.$_SERVER['SERVER_NAME'].'/'.$path);

}







function sendmail($mail_to, $mail_subject, $mail_message) {
	global $mail;
	$mail_subject = '=?'.$mail['charset'].'?B?'.base64_encode($mail_subject).'?=';
	$mail_message = chunk_split(base64_encode(preg_replace("/(^|(\r\n))(\.)/", "\1.\3", $mail_message)));

	$headers .= "";
	$headers .= "MIME-Version:1.0\r\n";
	$headers .= "Content-type:text/html\r\n";
	$headers .= "Content-Transfer-Encoding: base64\r\n";
	$headers .= "From: ".$mail['sitename']."<".$mail['mailfrom'].">\r\n";
	$headers .= "Date: ".date("r")."\r\n";
	list($msec, $sec) = explode(" ", microtime());
	$headers .= "Message-ID: <".date("YmdHis", $sec).".".($msec * 1000000).".".$mail['mailfrom'].">\r\n";

	if(!$fp = fsockopen($mail['server'], $mail['port'], $errno, $errstr, 30)) {
		exit("CONNECT - Unable to connect to the SMTP server");
	}

	stream_set_blocking($fp, true);

	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != '220') {
		exit("CONNECT - ".$lastmessage);
	}

	fputs($fp, ($mail['auth'] ? 'EHLO' : 'HELO')." befen\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
		exit("HELO/EHLO - ".$lastmessage);
	}

	while(1) {
		if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
 			break;
 		}
 		$lastmessage = fgets($fp, 512);
	}

	if($mail['auth']) {
		fputs($fp, "AUTH LOGIN\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {
			exit($lastmessage);
		}

		fputs($fp, base64_encode($mail['username'])."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {
			exit("AUTH LOGIN - ".$lastmessage);
		}

		fputs($fp, base64_encode($mail['password'])."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 235) {
			exit("AUTH LOGIN - ".$lastmessage);
		}

		$email_from = $mail['mailfrom'];
	}

	fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 250) {
		fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250) {
			exit("MAIL FROM - ".$lastmessage);
		}
	}

	foreach(explode(',', $mail_to) as $touser) {
		$touser = trim($touser);
		if($touser) {
			fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 250) {
				fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
				$lastmessage = fgets($fp, 512);
				exit("RCPT TO - ".$lastmessage);
			}
		}
	}

	fputs($fp, "DATA\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 354) {
		exit("DATA - ".$lastmessage);
	}

	fputs($fp, $headers);
	fputs($fp, "To: ".$mail_to."\r\n");
	fputs($fp, "Subject: $mail_subject\r\n");
	fputs($fp, "\r\n\r\n");
	fputs($fp, "$mail_message\r\n.\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 250) {
		exit("END - ".$lastmessage);
	}

	fputs($fp, "QUIT\r\n");

}

function sendsms($to,$msg)//成功返回true
{
	global $sms;
    $user=$sms['user'];
    $pass=$sms['pass'];
    $url="http://quanapi.sinaapp.com/fetion.php?u={$user}&p={$pass}&to={$to}&m={$msg}";
    $ret=file_get_contents($url);
    $res=json_decode($ret,true);
    if ($res->result==0)///结果为零时成功
    {
       return true;
    }
    else
    {
        return false;
    }
}
