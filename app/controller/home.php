<?php

/**
* 可以继承base类或者其他控制器类,也可以继承模型类,也可以什么都不继承
*/
class home
{

	function __construct()
	{
		//可以添加权限控制,保护整个控制器
	}

	public function index($value='')
	{
		sleep(1);
		header('Content-Type','application/json');
		sleep(6);
		echo "console.info('hello')";

	}

	/**
	 * popen 异步任务
	 */
	static function async1($task)
	{

		$arg="hello world";
		pclose(popen("/data/data/cn.suconghou.hello/files/php /mnt/sdcard/external_sd/web/task.php '$arg' >/dev/null 2>&1 &", 'r'));


	}

	/**
	 *  写入异步任务并返回文件名
	 */
	static function async(closure $task)
	{
		ob_end_clean();
		ob_start();
		$ret=$task();
		pclose(popen('php -f /tmp/task.php  >/dev/null 2>&1 &','w'));
		header("Content-Length: ".ob_get_length());
		ob_end_flush();
		flush();
		ob_end_clean();
	}

	function index2($type=null)
	{
		include 'xtorrent.php';
		$data=file_get_contents('/data/tmp/1.torrent');
		include 'lightbenc.php';
		include 'Third/bencoded.phpa';
		$a=Lightbenc::bdecode_getinfo('/data/tmp/1.torrent');
		var_dump($a);
		// $a=new TorrentInfo($data);
		// var_dump($a->Info);
		// var_dump(BEncoded::Decode($data));
	}



	function sleep($s=1)
	{
		sleep($s);
		echo "hello";
	}

	static function listFile($dir='.')
	{
		if(is_dir($dir))
		{
			$dirObj=new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)),'/\.[0-9a-zA-Z]{2,6}$/i');
			$info=[];
			foreach ($dirObj as $key => $f)
			{
				if($f->isFile())
				{
					$path=$f->getRealPath();
					if($path)
					{
						$info[$path]=['size'=>$f->getSize(),'mtime'=>$f->getMTime()];
					}
				}
			}
			return $info;
		}
		else
		{
			throw new Exception("Dir {$dir} Not Exist",404);
		}
	}

	function index21($db=null,$table=null)
	{
		$mobile=18519196710;
		$msg="您的验证码是：123456。请不要把验证码泄露给其他人。";
		$url="http://106.ihuyi.cn/webservice/sms.php?method=Submit";
		$data="account=cf_bjwill&password=2014bjwill&mobile=".$mobile."&content=".rawurlencode($msg);
		$xmldata = self::curlPost($url,$data);
		$data = (array) simplexml_load_string($xmldata);
		var_dump($data);
	}

	private static function curlPost($to,$msg)
	{
		$data=json_encode(['src'=>'14153336666','dst'=>$to,'text'=>$msg]);
		$url="https://api.plivo.com/v1/Account/MAZMM0OWZMMWJMYZQXYZ/Message/";
		$header=['Content-Type: application/json'];
		$ch=curl_init($url);
		curl_setopt_array($ch,array(CURLOPT_HTTPAUTH=>CURLAUTH_BASIC,CURLOPT_USERPWD=>"MAZMM0OWZMMWJMYZQXYZ:ZTI2NDJlMDk1YWYwNGNhYjJmM2MzZWYwNzIxMmU3",CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>8,CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$data,CURLOPT_HTTPHEADER=>$header));
		$result=curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	function aa()
	{
		$num_values = 10000;

		$db = new pdo( 'sqlite::memory:' );

		$db->exec( 'CREATE TABLE data (binary BLOB(512));' );

		// generate plenty of troublesome, binary data
		for( $i = 0; $i < $num_values; $i++ )
		{
		    for( $val = null, $c = 0; $c < 512/16; $c++ )
		        $val .= md5( mt_rand(), true );
		    @$binary[] = $val;
		}

		// insert each value by prepared statement
		for( $i = 0; $i < $num_values; $i++ )
		    $db->prepare( 'INSERT INTO data VALUES (?);' )->execute( array($binary[$i]) );

		// fetch the entire row
		$data = $db->query( 'SELECT binary FROM data;' )->fetchAll( PDO::FETCH_COLUMN );

		// compare with original array, noting any mismatch
		for( $i = 0; $i < $num_values; $i++ )
		    if( $data[$i] != $binary[$i] ) echo "[$i] mismatch\n";

		$db = null;
	}

	function Error404()
	{
		exit('error404');
	}



}
