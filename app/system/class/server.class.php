<?php
/**
* http server
* socket server
* ftp server
* smtp server 
* Run In Cli Mode
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
		self::debug();
	}

	public static function debug($debug='debug')
	{
		self::$debug=$debug;
	}

	// 四大Server
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
	public function servers($servers=array())
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

	/**
	 * 处理http请求并返回
	 */
	private function _httpServer($socket)
	{
		//获取http请求头和正文
		$request=socket_read($socket, 8192);
		self::log('debug',"received message".PHP_EOL.$request,1);
		$http=self::_http($request);
		ob_start();
		self::_fileServer(isset($http['http']['path'])?$http['http']['path']:null);
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
		self::log('debug','Received message : '.PHP_EOL.$request,1);
		$data=self::_webSocket($request);
		$header=self::computeSec($data['Sec-WebSocket-Key']);
		

		if (false === socket_write($socket, $header, strlen($header)))
		{
			self::log('error',"socket_write() failed : ".socket_strerror(socket_last_error(self::$socket['socket'])),1);
		}
		else
		{
			self::log('debug','send response success'.PHP_EOL.$header,1);
		}



	}
	private function _ftpServer()
	{

	}
	private function _smtpServer()
	{

	}
	/**
	 * 通用底层套接字处理
	 */
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
				self::log('debug',"method {$server} begin to handle the request ",1);
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
		$request=explode(PHP_EOL, $request);
		foreach ($request as  $value)
		{
			$arr=explode(':',$value);
			$len=count($arr);
			if($len==1) //没有包含: ,可能为请求行
			{
				$arr[0]=trim($arr[0]);
				if(empty($arr[0])) continue;
				$firstLine=explode(' ',$arr[0]);
				if(count($firstLine==3)) //是请求行
				{
					$data['http']['method']=$firstLine[0];
					$data['http']['path']=$firstLine[1];
					$data['http']['protocol']=$firstLine[2];
				}
				else //未包含: 并且也没有两个空格
				{
					var_dump($firstLine);
				}
			}
			else if($len==2) //普通的键值对方式,请求header
			{
				$data['http'][$arr[0]]=$arr[1];
				if($arr[0]=='Cookie') //解析cookie
				{
					$cookie=str_replace(';','&', $arr[1]);
					parse_str($cookie,$data['cookie']);
				}
			}
			else if($len==3) //包含两个:,未知
			{
				$data['http'][$arr[0]]=$arr[1].':'.$arr[2];
			}
			else //包含3个或以上
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

	/**
	 * 解析websocket协议
	 */
	public static function _webSocket($request)
	{
		$web=array();
		$arr=explode(PHP_EOL,$request);
		foreach ($arr as  $line)
		{
			if(empty($line))continue;
			$arr_header=explode(':', $line);
			$size=count($arr_header);
			// var_dump($arr_header);
			if($size==1) //不含有:
			{
				$firstLine=explode(' ', $line);
				if(count($firstLine)==3)
				{
					$web['method']=$firstLine[0];
					$web['path']=$firstLine[1];
					$web['protocol']=$firstLine[2];
				}
				else
				{
					var_dump('not contain : but contain not 2 space',$firstLine);
				}
			}
			else if($size==2)
			{
				$web[$arr_header[0]]=$arr_header[1];
			}
			else if($size==3)
			{
				$web[$arr_header[0]]=$arr_header[1].':'.$arr_header[2];
			}
			else
			{
				var_dump('contain : unkunw num',$line);
			}
		}
		return $web;

	}
	/**
	 * websocket 握手
	 */
	private static function checkHandshake($sec)
	{
		$key = base64_encode(sha1($sec."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
		// 握手返回的数据
		$message = "HTTP/1.1 101 Switching Protocols\r\n";
		$message .= "Upgrade: websocket\r\n";
		$message .= "Sec-WebSocket-Version: 13\r\n";
		$message .= "Connection: Upgrade\r\n";
		$message .= "Sec-WebSocket-Accept: " . $key . "\r\n\r\n";
		// 发送数据包到客户端 完成握手
		self::sendToCurrentClient($message);
	}
	public static function sendToCurrentClient($data)
	{
		
	} 
	public static function sendToAll($data,$client)
	{

	}
	public static function sendToClient($data,$client)
	{

	}

	function __destruct()
	{
		echo self::$msg;
	}

}


function _config($key,$value=null)
{
	static $config=array();
	if(!is_null($value))
	{
		$config[$key]=$value;
	}
	return isset($config[$key])?$config[$key]:null;
}