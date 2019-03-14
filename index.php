<?php
/*************************************系统配置区*************************************/
define('DEBUG',getenv('debug'));
define('ROOT',__DIR__.DIRECTORY_SEPARATOR);
define('APP_PATH',ROOT.'app'.DIRECTORY_SEPARATOR);
define('VAR_PATH',ROOT.'var'.DIRECTORY_SEPARATOR);
define('LIB_PATH',APP_PATH.'system'.DIRECTORY_SEPARATOR);
define('VIEW_PATH',APP_PATH.'view'.DIRECTORY_SEPARATOR);
define('MODEL_PATH',APP_PATH.'model'.DIRECTORY_SEPARATOR);
define('CONTROLLER_PATH',APP_PATH.'controller'.DIRECTORY_SEPARATOR);
require LIB_PATH.'core.php';


$config=
[
	'db'=>
	[
		'dsn'=>'mysql:host=172.168.1.3;port=13306;dbname=test;charset=utf8',
		'user'=>'work',
		'pass'=>'123456',
	],
	'db2'=>
	[
		'dsn'=>'mysql:host=172.168.1.3;port=13306;dbname=21text_new;charset=utf8',
		'user'=>'work',
		'pass'=>'123456',
	],
	// 可选的配置项
	'mail'=>
	[
		'server'=>'smtp.yeah.net',
		'user'=>'suconghou@yeah.net',
		'pass'=>'password',
		'name'=>'消息通知',
		'port'=>25,
		'auth'=>true,
	],
	'timezone'=>'prc',
];



/*************************************应用程序配置区*************************************/

route::get('\/admin\/(\w+)(?:\/(\w+))?','Site/Site');

/*************************************应用程序配置区*************************************/

//配置完,可以启动啦!
app::start($config);

