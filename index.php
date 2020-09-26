<?php

/*************************************系统配置区*************************************/
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);
require ROOT . 'app' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'core.php';

$envarr = explode('.', getenv('run_args'));
list($env, $debug) = count($envarr) == 2 ? $envarr : ['local', true];


$dbs = [
	'local' =>
	[
		'dsn' => 'mysql:host=172.168.1.3;port=13306;dbname=test;charset=utf8',
		'user' => 'work',
		'pass' => '123456',
	],
	'dev' =>
	[
		'dsn' => 'sqlite:/tmp/1.db;dbname=test;charset=utf8',
		'user' => 'work',
		'pass' => '123456',
	],
	'beta' =>
	[
		'dsn' => 'mysql:host=172.168.1.3;port=13306;dbname=test;charset=utf8',
		'user' => 'work',
		'pass' => '123456',
	],
	'release' =>
	[
		'dsn' => 'mysql:host=172.168.1.3;port=13306;dbname=test;charset=utf8',
		'user' => 'work',
		'pass' => '123456',
	],

];

$config =
	[
		'lib_path' => [ROOT . 'app' . DIRECTORY_SEPARATOR  . 'controller' . DIRECTORY_SEPARATOR, ROOT . 'app' . DIRECTORY_SEPARATOR  . 'model' . DIRECTORY_SEPARATOR],
		'view_path' => ROOT . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
		'var_path' => ROOT . 'var' . DIRECTORY_SEPARATOR,

		'debug' => $debug,
		'db' => $dbs[$env],
		'dbdev' => $dbs['dev'],
		// 可选的配置项
		'mail' =>
		[
			'server' => 'smtp.yeah.net',
			'user' => 'suconghou@yeah.net',
			'pass' => 'password',
			'name' => '消息通知',
			'port' => 25,
			'auth' => true,
		],
		'event' => [
			'json' => function ($e) {
				return json(['code' => $e->getCode(), 'msg' => $e->getMessage()]);
			}
		]
	];



/*************************************路由配置区*************************************/

route::get('\/video\/api\/v3\/(videos|search|channels|playlists|playlistItems)', ['youtube','index']);

/*************************************路由配置区*************************************/

//配置完,可以启动啦!
app::start($config);
