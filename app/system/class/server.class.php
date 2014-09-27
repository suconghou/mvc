<?php
/**
* http server
* socket server
* 
* run in cli
* 
* 
*/
class server
{

	private static $serverInfo;
	private static $debug;
	private static $msg;

	private static $socket;

	
	function __construct($cfg=array())
	{
		self::serverInfo();
		self::init();
	}

	public function debug($debug='debug')
	{
		self::$debug=$debug;
	}
	public function httpServer($host='127.0.0.1',$port=8088,$dir=null)
	{
		if(is_null($dir))
		{
			$dir=APP_PATH;
		}
		self::$serverInfo['httpServerDir']=$dir;	
		$this->_socket('http',$host,$port);
	}
	public function socketServer($host='127.0.0.1',$port=7272)
	{
		$this->_socket('socket',$host,$port);
	}
	public function ftpServer()
	{

	}
	public function smtpServer()
	{

	}
	/**
	 * 多个 server 同时运行
	 */
	public function server($servers=array())
	{
		foreach ($servers as $type => $v)
		{
			switch ($type)
			{
				case 'http':
					$this->httpServer($v['host'],$v['port'],$v['dir']);
					break;
				case 'socket':
					# code...
					break;
				case 'ftp':
					# code...
					break;
				case 'smtp':
					# code...
					break;
				default:
					# code...
					break;
			}
		}

	}

	private static function serverInfo()
	{

		Request::isCli()||self::log('error',"You may run in cli mode ");
		self::$serverInfo=Request::serverInfo();
	}

	private static function init()
	{


	}

	private static function a()
	{

	}

	private function _httpServer($socket)
	{
		//获取http请求头和正文
		$request=socket_read($socket, 8192);
		self::log('debug','received message'.$request);
		$http=self::_http($request);
		ob_start();
		self::_fileServer($http['http']['path']);
		$body=ob_get_contents();
		$header=array('HTTP/1.1 200 OK','Content-Type:text/html; charset=utf-8');
		$header=join($header,PHP_EOL).PHP_EOL.PHP_EOL;
		if (false === socket_write($socket, $header.$body, strlen($header.$body)))
		{
			self::log('error',"socket_write() failed : ".socket_strerror(socket_last_error(self::$socket['http'])),1);
		}
		else
		{
			self::log('debug','send response success',1);
		}

		
	}
	private function _socketServer($socket)
	{
		$request=socket_read($socket, 8192);
		self::log('debug','received message'.$request);
		
		$msg = "<font color='red'>server send:socket welcoe</font><br/>";
		
		socket_write($socket, $msg,strlen($msg));
		$buf='hello';

		if (false === socket_write($socket, $buf, strlen($buf)))
		{
			self::log('error',"socket_write() failed : ".socket_strerror(socket_last_error(self::$socket['http'])),1);
		}
		else
		{
			self::log('debug','send response success');
		}



	}
	private function _ftpServer()
	{

	}
	private function _smtpServer()
	{

	}
	private function _socket($type,$host,$port)
	{
		if( (self::$socket[$type]=socket_create(AF_INET, SOCK_STREAM, SOL_TCP))=== false)
		{
			self::log('error','socket_create() failed : '.socket_strerror(socket_last_error(self::$socket[$type])),1);
		}

		if(socket_bind(self::$socket[$type], $host, $port) === false)
		{
			self::log('error','socket_bind() failed : '.socket_strerror(socket_last_error(self::$socket[$type])),1);
		}
		if(socket_listen(self::$socket[$type], 5) === false)
		{
			self::log('error','socket_bind() failed : '.socket_strerror(socket_last_error(self::$socket[$type])),1);
		}
		self::log('debug','Begin to wait accept');
		do
		{
			if(($socket = socket_accept(self::$socket[$type])) !== false)
			{
				$server='_'.$type.'Server';
				self::log('debug',"method {$server} begin to handle the request ");
				$this->$server($socket);
				socket_close($socket);
			}
			else
			{
				self::log('error','socket_accept() failed : '.socket_strerror(socket_last_error(self::$socket[$type])),1);

			}
		}
		while(true);
		socket_close(self::$socket[$type]);
	}

	function __call($name,$args)
	{
		self::log('error','Call Error Method '.$name.' In Class '.__CLASS__ , 1);
	}
	public static function __callStatic($name,$args)
	{
		self::log('error','Call Error Static Method '.$name.' In Class '.__CLASS__ , 1);
	}
	public static function log($type,$msg,$show=false)
	{
		$level=self::$debug=='error'?array('error'):(self::$debug=='info'?array('error','info'):array('error','info','debug'));
		if(in_array($type, $level))
		{
			$msg.="\r\n";
			self::$msg=date('Y-m-d H:i:s')." ==> [ ".$type." ] ".$msg;
			if($show)
			{
				echo $msg;
			}
		}
	}
	 
	/**
	 * 解析http协议 
	 */
	private static function _http($request)
	{
		$request=explode("\n", $request);
		foreach ($request as  $value)
		{
			$arr=explode(':',$value);
			$len=count($arr);
			if($len==1)
			{
				$arr[0]=trim($arr[0]);
				if(empty($arr[0])) continue;
				$firstLine=explode(' ',$arr[0]);
				if(count($firstLine==3))
				{
					$data['http']['method']=$firstLine[0];
					$data['http']['path']=$firstLine[1];
					$data['http']['protocol']=$firstLine[2];
				}
				else
				{
					var_dump($firstLine);
				}
			}
			else if($len==2)
			{
				$data['http'][$arr[0]]=$arr[1];
				if($arr[0]=='Cookie') //解析cookie
				{
					$cookie=str_replace(';','&', $arr[1]);
					parse_str($cookie,$data['cookie']);
				}
			}
			else if($len==3)
			{
				$data['http'][$arr[0]]=$arr[1].':'.$arr[2];
			}
			else
			{
				$data['http'][$arr[0]]=$arr[1].':'.$arr[2].':'.$arr[3];
			}
		}
		return isset($data['http'])?$data:array();
		
		
	}

	/**
	 * 加载文件
	 */
	private static function _fileServer($path)
	{
		self::log('debug','_fileServer Init');
		$defaultIndex=array('index.html','index.php');
		$path=rtrim(self::$serverInfo['httpServerDir'],'/').$path;
		if(substr($path,'-1')=='/')
		{
			foreach ($defaultIndex as $index)
			{
				$newpath=$path.$index;
				if(is_file($newpath))
				{
					if(substr($newpath,'-4')=='.php')
					{
						include $newpath;
					}
					else
					{
						readfile($newpath);					
					}
					break;
					return;
				}
				else
				{
					self::log('error','file Not Found');
				}
			}
		}
		else
		{
			if(is_file($path))
			{
				if(substr($path,'-4')=='.php')
				{
					include $path;
				}
				else
				{
					readfile($path);
				}
				
			}
			else
			{
				echo $path,'Not Exists !';
			}

		}
	
	} 

	function __destruct()
	{
		echo self::$msg;
	}

}


function config($key,$value=null)
{
	static $config=array();
	if(!is_null($value))
	{
		$config[$key]=$value;
	}
	return isset($config[$key])?$config[$key]:null;
}