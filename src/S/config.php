<?


date_default_timezone_set('PRC');//设置时区
set_error_handler('show_errorpage');



//数据库配置,有mysql和sqlite两种数据库,如果配置了sqlite,则优先使用sqlite
//$db['sqlite']="/db/data.db";


$db['db_host']='10.0.62.24';
$db['db_port']=3306;
$db['db_name']='d4529ded14db5447abec0d19416451043';
$db['db_user']='usHkUZwfH2iw0';
$db['db_pass']='pei2nLF8nqAxz';

//////////系统设置
$config['debug']=1;

//smtp配置

$mail = Array (
	'sitename'=>'网站名称',
	'server' => 'smtp.126.com',
	'port' => 25,
	'auth' => 1,
	'username' => 'suconghou@126.com',
	'password' => '11260sch45770',
	'charset' => 'utf-8',
	'mailfrom' => 'suconghou@126.com'
	);

///发送短信配置,飞信方式
$sms=array('user'=>'18749667085',
			'pass'=>'11260sch45770'
			);
