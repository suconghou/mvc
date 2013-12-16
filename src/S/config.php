<?

//uri路由设置




//数据库配置

$config['db_host']='localhost';
$config['db_port']=3306;
$config['db_name']='data';
$config['db_user']='root';
$config['db_pass']='123456';


//smtp配置

$mail = Array (
	'sitename'=>'网站名称',
	'server' => 'smtp.126.com',
	'port' => 25,
	'auth' => 1,
	'username' => 'suconghou@126.com',
	'password' => '12330',
	'charset' => 'utf-8',
	'mailfrom' => 'suconghou@126.com'
	);

///发送短信配置,飞信方式
$sms=array('user'=>'18749667085',
			'pass'=>'123456'
			);
