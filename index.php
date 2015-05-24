<?php

define('ROOT',dirname(__FILE__).DIRECTORY_SEPARATOR);//根路径
define('APP_PATH',ROOT.'app'.DIRECTORY_SEPARATOR);//APP路径
define('VAR_PATH',ROOT.'var'.DIRECTORY_SEPARATOR);//缓存路径
define('LIB_PATH',APP_PATH.'system'.DIRECTORY_SEPARATOR);//系统路径
define('MODEL_PATH',APP_PATH.'model'.DIRECTORY_SEPARATOR);//模型路径
define('VIEW_PATH',APP_PATH.'view'.DIRECTORY_SEPARATOR);//视图路径
define('CONTROLLER_PATH',APP_PATH.'controller'.DIRECTORY_SEPARATOR); //控制器路径
require LIB_PATH.'core.php';//载入核心

define('GZIP',1);  //是否开启GZIP,在SAE若出错请关闭
define('MAX_URL_LENGTH',100); //URL最大长度限制
define('REGEX_ROUTER',1);  //是否启用正则路由

//自定义404,500路由,若设定请确保必须存在,系统定义Error404,Error500
define('ERROR_PAGE_404','Error404');
define('ERROR_PAGE_500','Error500');

define('DEFAULT_CONTROLLER','home'); //默认的控制器
define('DEFAULT_ACTION','index'); ///默认的动作

//smtp配置
define('MAIL_SERVER','smtp.126.com');
define('MAIL_PORT',25);
define("MAIL_AUTH",true);
define('MAIL_USERNAME','suconghou@126.com');
define('MAIL_PASSWORD','123456');
define('MAIL_NAME',baseUrl());

///添加一个正则路由,数组第一个为控制器,第二个为方法,前面捕获的将作为该方法的第一个实参,以此类推
app::route('\/page\/(\d{1,9})\.html',array('page','id'));
app::route('\/read\/(\d{1,9})\.html',array('read','id'));
app::route('\/(\d{1,9})\.html',array('home','id'));
app::route('\/fm\/(\d{1,9}).fm',array('fm','id'));
app::route('\/about',array('home','about'));


//0自动记录错误日志(自定义的error和自动捕获的程序错误),不显示错误详情,忽略notice,显示404,500错误页(若已定义).建议上线使用
//1自动记录全部日志(error ,debug 和自动捕获的程序错误),显示错误详情,忽略notice,不使用404,500错误页.建议测试时使用
//2自动记录全部日志(error ,debug 和自动捕获的程序错误),显示错误详情,捕获所有,不使用404,500错误页.建议开发时使用
S('functions');//加载通用扩展函数库

//线上模式与本地模式配置差异,更改数据库,Redis,Memcache等
//加载应用程序配置,可以将数据库,正则,smtp等配置信息移入应用设置文件
if(isset($_ENV['DEBUG']))
{
	//线上模式配置
	// S('server_config');
	define('DEBUG',$_ENV['DEBUG']);

}
else
{
	//开发模式配置
	// S('local_config');
	define('DEBUG',2);
	//mysql数据库配置
	define('DB_HOST','127.0.0.1');
	define('DB_PORT',3306);
	define('DB_NAME','blog');
	define('DB_USER','root');
	define('DB_PASS',123456);
}


//sqlite 数据库配置
define('SQLITE',APP_PATH.'data.db');
//配置使用何种数据库,0为mysql,1为sqlite
define('DB',0);

// S('site/site1'); //Single File Site 模式
// S('site/site2'); //Single File Site 模式

//配置完,可以启动啦!
//带不带echo都没关系,echo在cli下会输出部分提示信息!
echo app::start();


