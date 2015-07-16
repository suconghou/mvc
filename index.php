<?php
/*************************************系统配置区*************************************/
define('GZIP',0);
define('DEBUG',get_cfg_var('debug'));
define('MAX_URL_LENGTH',100);
define('ROOT',__DIR__.DIRECTORY_SEPARATOR);
define('APP_PATH',ROOT.'app'.DIRECTORY_SEPARATOR);
define('VAR_PATH',ROOT.'var'.DIRECTORY_SEPARATOR);
define('LIB_PATH',APP_PATH.'system'.DIRECTORY_SEPARATOR);
define('VIEW_PATH',APP_PATH.'view'.DIRECTORY_SEPARATOR);
define('MODEL_PATH',APP_PATH.'model'.DIRECTORY_SEPARATOR);
define('CONTROLLER_PATH',APP_PATH.'controller'.DIRECTORY_SEPARATOR);
// 自定义404,500路由,线上模式会有用
define('ERROR_PAGE_404','Error404');
define('ERROR_PAGE_500','Error500');
// 默认的控制器和动作
define('DEFAULT_CONTROLLER','home');
define('DEFAULT_ACTION','index');
require LIB_PATH.'core.php';
if(DEBUG)
{
	define('DB_HOST','127.0.0.1');
	define('DB_PORT',3306);
	define('DB_NAME','blog');
	define('DB_USER','root');
	define('DB_PASS',123456);
}
else
{

}

// 开启如下配置使用SQLITE数据库
// define('SQLITE',VAR_PATH.'project.db');
// define('DB',1);
// 开启如下配置可使用sendMail
// define('MAIL_SERVER','smtp.126.com');
// define('MAIL_USERNAME','suconghou@126.com');
// define('MAIL_PASSWORD','123456');
// define('MAIL_NAME','发送者名称');


/*************************************应用程序配置区*************************************/

app::route('\/(?:([\w\-]+)\/)?static\/(css|style|js)(?:\/([\w\-\.]+))','plugins/StaticProvider');

base::version(1234);

/*************************************应用程序配置区*************************************/

//配置完,可以启动啦!
app::start();

