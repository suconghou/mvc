<?

///mvc 核心框架类库
//包含重要设置和核心类库


require 'config.php';
date_default_timezone_set('Asia/Chongqing');///更正时间

class uri
{

	private $uri;

	function __construct()
	{
		$this-> uri= $_SERVER['PATH_INFO'];
	}

	function init()
	{
		$arr=explode('/', $this-> uri);
		$arr_uri['c']=empty($arr['1'])?'main':$arr['1'];
		$arr_uri['a']=empty($arr['2'])?'index':$arr['2'];
		for ($i=3; $i <count($arr) ; $i++)
		{ 

			$arr[$i]==null||array_push($arr_uri,$arr[$i]);
			
		}
		return($arr_uri);
	}

	
}

/**
 * 关键进程
 */
 class process 
 {

 	function __construct($arr_uri)
 	{
 		$file='C/'.$arr_uri['c'].'.php';

 		is_file($file)||exit('控制器文件'.$file.'不存在');
 		require $file;
 		class_exists($arr_uri['c'])||exit('文件'.$file.'中不存在类'.$arr_uri['c']);
 		$methods = get_class_methods($arr_uri['c']);			// 获取类中的方法名 
		(in_array($arr_uri['a'], $methods, true))|| exit('在类'.$arr_uri['c'].'中不存在方法名'.$arr_uri['a']);

		
		$arr_uri['c']=new $arr_uri['c']();//实例化控制器
		call_user_func_array(array($arr_uri['c'],$arr_uri['a']), array_slice($arr_uri,2));//传入参数
 	}


 	


 }


///control 层
class C
{
	private $cache=null;//缓存单位分钟
	private $arr_uri;
	function __construct()
	{
		global $arr_uri;
		$this-> arr_uri=$arr_uri;

	}
	///view 层
	function view($file,$data=null)
	{
		$file='V/'.$file.'.php';
		is_file($file)||exit('不存在视图文件'.$file);
		is_array($data)||empty($data)||exit('向'.$file.'传入的参数不是数组格式');
		empty($data)||extract($data);
		global $t1;
		$t2=microtime(true); ///计时结束
		$spend_time=round(($t2-$t1),4);
		unset($t1);
		unset($t2);
		if($this-> cache)//设置了缓存时间
		{

			$cache_file='V/cache/'.implode('-',$this-> arr_uri).'.html';
			$time=time()+(int)($this-> cache)*60;
			ob_start();
			include $file;
			$contents=ob_get_contents();
			file_put_contents($cache_file,"<!--{$time}-->".$contents);
			ob_end_clean();
			echo $contents;


		}
		else //不要缓存
		{
			include $file;

		}
		


	}


	function cache($min)
	{

		$this-> cache=$min;
	}

	function load($file)//加载类库
	{
		$filepath='S/'.$file.'.php';
		is_file($filepath)||exit('不存在类库文件'.$filepath);
		require $filepath;
		class_exists($file)||exit('文件'.$filepath.'中不存在类'.$file);
		$this->$file=new $file();

	}

	function model($file)//加载model
	{
		$filepath='M/'.$file.'.php';
		is_file($filepath)||exit('不存在模型文件'.$filepath);
		require $filepath;
		class_exists($file)||exit('文件'.$filepath.'中不存在类'.$file);
		$this->$file=new $file();


	}

	function uri($segment)
	{
		return $this-> arr_uri[$segment]; 
		
	}




}


/**
* model 层
*/
class M
{
	private $config;
	static $link;///单例模式
	public $debug=false;///调试模式

	public  $select=array();



	function __construct()
	{
		global $config;
		$this-> config=$config;
		$dsn="mysql:host={$config['db_host']};dbname={$config['db_name']};port={$config['db_port']}";
		try
		{
			self::$link= new PDO ($dsn,$config['db_user'],$config['db_pass'],array (PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true ));
		}
		catch ( Exception $e )
		{
            exit('数据库连接失败:'.$e->getMessage());
        }

	}

	function query($sql)
	{
		if ($this-> debug)
		{
			exit('调试模式:'.$sql);
		}
		else
		{
			try
			{	
				if (stristr($sql, 'SELECT'))
				{
					
					return self::$link->query($sql);
					
				}
				else
				{
					return self::$link->exec($sql);
				}
			}
			catch(Exception $e)
			{
				exit('执行SQL语句时出错:'.$e->getMessage());
			}
			
		}

	}

	function select()
	{
		echo 'select';
		return $this;
	}
	function select_max()
	{

	}
	function select_min()
	{

	}
	function select_avg()
	{

	}
	function select_sum($select = '', $alias = '')
	{
		return $this->_max_min_avg_sum($select, $alias, 'SUM');

	}
	function _max_min_avg_sum($select = '', $alias = '', $type = 'MAX')
	{
		if ( ! is_string($select) OR $select == '')
		{
			exit('db_invalid_query');
		}

		$type = strtoupper($type);

		if ( ! in_array($type, array('MAX', 'MIN', 'AVG', 'SUM')))
		{
			exit('Invalid function type: '.$type);
		}

		$sql = $type.'('.$this->_protect_identifiers(trim($select)).') AS '.$alias;

		$this->select[] = $sql;

		return $this;

	}
	function from($table)
	{

		return $this;

	}
	function where()
	{
		echo 'where';

		return $this;
	}
	function order_by()
	{
		echo 'order';
		return $this;
	}
	function update()
	{
		echo 'update';
		return $this;
	}
	function delete()
	{
		echo 'delete';

		return $this;
	}
	function insert()
	{
		echo 'insert';
		return $this;
	}
	function debug($debug)
	{

		if ($debug)
		{
			$this-> debug=true;
		}
		else
		{
			$this-> debug=false;
		}
		return $this;
	}
}


/////////some functions blow


function redirect($url,$seconds=0)
{

	header("Refresh: {$seconds}; url={$url}");
	exit();

}


function base_url($path)
{
	return('http://'.$_SERVER['SERVER_NAME'].'/'.$path);

}