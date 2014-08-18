<?php
/**
* Mysql & Datas backup
* $backup->init()->start(); //自动备份
* $backup->dbBackup();  //返回数据库备份的内容
* $backup->dataBackup(); //返回备份好的压缩包文件所在位置
*/
class backup
{
	private static $dbConfig; //数据库设置,包含DB_HOST,DB_PORT,DB_NAME,DB_USER,DB_PASS
	private static $dataConfig; //备份名称,备份的目录,文件备份模式(1仅本地存储,2仅云端存储,3同时存储)

	private static $log; //备份日志记录
	private static $mysql;
	
	function __construct()
	{
		set_time_limit(300); //超时时间5分钟
	}
	/**
	 * 都没有参数,则使用系统定义的mySql数据库,全站备份
	 * 只设置dataConfig则只备份数据
	 * 只设置dbConfig 则只备份数据库
	 * 同时设置,则都备份
	 */
	function init($dataConfig=null,$dbConfig=null)
	{
		if(is_null($dataConfig)&&is_null($dbConfig)) //全站备份
		{
			self::$dbConfig=array(DB_HOST,DB_PORT,DB_NAME,DB_USER,DB_PASS); //继承系统设置
			self::$dataConfig=array('backup','.',2); //默认仅云端存储
		}
		else
		{
			self::$dbConfig=is_array($dbConfig)?$dbConfig:null;
			self::$dataConfig=is_array($dataConfig)?$dataConfig:null;
			if(!(self::$dbConfig&&self::$dataConfig))
			{
				self::log("Init Error");
			}
		}
		return $this;		
	}

	function dbBackup($dbConfig)
	{
		self::$dbConfig=$dbConfig;
		self::mysqlInit($dbConfig);
		$data=self::data2sql();
		return $data;
	}
	function dataBackup($dataConfig)
	{
		self::$dataConfig=$dataConfig;
		try
		{
			$zip= new ZipArchive(); 
			$filename=$dataConfig[0].'-'.date("YmdHis").".zip";
		    $filepath = APP_PATH.$filename;  //框架根目录下创建备份文件
		    if ($zip->open($filepath, ZIPARCHIVE::CREATE)!==TRUE)
		    {
		      self::log("ERROR: ".$filepath."不可写。创建文件失败，请检查目录权限。"); 
		    }
    		$files = self::listDir($dataConfig[1]);
    		
    		if(isset(self::$dbConfig['dbPath'])) // 设定了dbPath,说明备份了数据库
		    {
		      	array_push($files,self::$dbConfig['dbPath']);   //将数据库文件添加入要压缩的列表 
		    }
		    foreach($files as $path)
		    {
		      	$zip->addFile($path,str_replace("./","",str_replace("\\","/",$path))); 
		    }
		    $ret['filenum']=$zip->numFiles;
		    $zip->close();
		    unlink(self::$dbConfig['dbPath']);  //是否要删除生成的SQL文件
		    $ret['filepath']=$filepath; 
		    return $ret;
		}
		catch(Exception $e)
		{
			 self::log('Error: '.$e->getMessage());
		}

	}
	/**
	 * 数据云端同步
	 */
	private function sync($path)
	{
		self::log('Begin Upoad File '.$path);
		var_dump($path);

	}
	/**
	 * 自动备份, 程序入口
	 */
	function start()
	{
		if(self::$dbConfig) //开启了数据库备份
		{
			$data=self::dbBackup(self::$dbConfig);
			$path=APP_PATH.self::$dbConfig[2].date('YmdHis').'.sql';
			file_put_contents($path,$data);
			self::$dbConfig['dbPath']=$path;
			self::log('DB store in '.$path);
			if(!self::$dataConfig) //仅备份数据库,可以上传了
			{
				self::sync($path);
			}
		}
		if(self::$dataConfig) //自定义文件备份选项 ,要备份文件了
		{
			$path=self::dataBackup(self::$dataConfig); //备份的文件地址
			self::log("Backup Files End , Stored In ".$path['filepath'].',Total Files '.$path['filenum']);
			self::sync($path['filepath']);
		}
		

	}
	private static function mysqlInit($dbConfig)
	{
		try
	    {	

	         self::$mysql=mysql_connect($dbConfig[0].':'.$dbConfig[1],$dbConfig[3],$dbConfig[4]);
	         mysql_select_db($dbConfig[2]);
	         mysql_query("set names utf8");
	         self::log("MySql Init Success !");
	    } 
	    catch (Exception $e) 
	    {
	        self::log('Error: '.$e->getMessage());
	    }

	}
	private static function runSql($sql)
  	{
	    try
	    {
	 
	      $result=mysql_query($sql);
	      return $result;  
	    }
	    catch (Exception $e)
	    {
	        self::log("ERROR: ".$e->getMessage());
	    }
  	}
  	private static function getTables()
  	{
	    $sql="SHOW TABLES";
	    $result=self::runSql($sql);
	    while($row=mysql_fetch_row($result))
	    {
	      $tables[]=$row[0];
	    }
	    return $tables;
    }
    //获得所有表结构
	private static function table2sql()
	{
		$tables=self::getTables();
		$return="-- ".date('Y-m-d H:i:s')."\r\n";
		foreach ($tables as $table)
		{
		  $result=self::runSql("select * from ".$table);
		  $num_fields = mysql_num_fields($result);   
		  $return.= 'DROP TABLE IF EXISTS `'.$table.'` ;';
		  
		  $create = mysql_fetch_row(self::runSql('SHOW CREATE TABLE '.$table));
		  $return.= "\n\n".$create[1].";\n\n";
		  
		}
		return  $return;
	}
	/**
	 * 获取表结构和数据
	 */
	private static function data2sql()
	{
		$tables=self::getTables();
		$return="-- ".date('Y-m-d H:i:s')."\r\n";
		foreach ($tables as $table)
		{
		  $result=self::runSql("SELECT * FROM `".$table."`");
		  $num_fields = mysql_num_fields($result);   
		  $return.= 'DROP TABLE IF EXISTS `'.$table.'` ;';
		  $create = mysql_fetch_row(self::runSql('SHOW CREATE TABLE `'.$table.'`'));
		  $return.= "\n\n".$create[1].";\n\n";
		  
		  for ($i=0; $i < $num_fields ; $i++)
		  { 
		      while($row = mysql_fetch_row($result))
		      {
		           $return.= 'INSERT INTO `'.$table.'` VALUES(';
		           for($j=0; $j<$num_fields; $j++) 
		           {
		              $row[$j] = addslashes($row[$j]);
		              if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; }
		              else { $return.= '""'; }
		              if ($j<($num_fields-1)) { $return.= ','; }
		           }
		          $return.= ");\n";
		      }
		  }  
		  $return.="\n\n\n";

		}
		return $return;
	}
	/**
	 * 记录log 日志
	 */
	private static function log($msg)
	{
		self::$log.=date('Y-m-d H:i:s').$msg."\r\n";
		return self::$log;
	}
	private static function listDir($start_dir=null)
	{
		$start_dir=is_null($start_dir)?self::$dataConfig[1]:$start_dir;
	    $files = array();
	    if (is_dir($start_dir)) {
	      $fh = opendir($start_dir);
	      while (($file = readdir($fh)) !== false) {
	        if(strcmp($file, '.')==0 || strcmp($file, '..')==0){
	          continue;
	        }
	        $filepath = $start_dir . '/' . $file;
	        if(is_dir($filepath)){
	          $files = array_merge($files, self::listDir($filepath));
	        }else{
	          array_push($files, $filepath);
	        }
	      }
	      closedir($fh);
	    }else{
	      $files = array();
	    }
	    return $files;

	}

	private static function postData($url,$post_string)
	{
	  $ch=curl_init();
	  curl_setopt_array($ch, array(CURLOPT_URL=>$url,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$post_string));
	  $result=curl_exec($ch);
	  curl_close($ch);
	  return $result;
	}
	function __call($name,$args)
	{
		Error('500','Call Error Method '.$name.' In Class '.__CLASS__);
	}
	function __destruct()
	{
		echo(self::$log);
	}
}