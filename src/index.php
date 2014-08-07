<?php

define('ROOT',dirname(__FILE__).'/');//根路径
define('APP_PATH',ROOT.'app/');//APP路径
define('LIB_PATH',APP_PATH.'system/');//系统路径
define('MODEL_PATH',APP_PATH.'model/');//模型路径
define('VIEW_PATH',APP_PATH.'view/');//视图路径
define('CONTROLLER_PATH',APP_PATH.'controller/'); //控制器路径
require LIB_PATH.'core.php';//载入核心

define('CLI',1);//在CLI模式下也能运行

define('MAX_URL_LENGTH',200); //URL最大长度限制
define('REGEX_ROUTER',1);  //是否启用正则路由

define('DEFAULT_CONTROLLER','home'); //默认的控制器
define('DEFAULT_ACTION','index'); ///默认的动作

define('GZIP',0);  //是否开启GZIP
//0 不使用,也不记录错误日志,1使用记录错误日志,捕获警告消息
define('DEBUG',1);

//自定义404,500路由,若设定请确保必须存在,系统定义Error404,Error500
define('ERROR_PAGE_404',''); //Error404
define('ERROR_PAGE_500','');//Error500

//mysql数据库配置
define('DB_HOST','127.0.0.1');
define('DB_PORT',3306);
define('DB_NAME','blog');
define('DB_USER','root');
define('DB_PASS',123456);

//sqlite 数据库配置
define('SQLITE',APP_PATH.'data.db');
//配置使用何种数据库,0为mysql,1为sqlite
define('DB',0);

//smtp配置
define('MAIL_SERVER','smtp.126.com');
define('MAIL_PORT',25);
define("MAIL_AUTH",true);
define('MAIL_USERNAME','suconghou@126.com');
define('MAIL_PASSWORD','11260sch45770');
define('MAIL_NAME','系统邮件');

///添加一个正则路由,数组第一个为控制器,第二个为方法,前面的将作为该方法的第一个实参,以此类推

app::route('\/([A-Z0-9]{40})\.torrent',array('home','a'));
app::route('\/page\/(\d{1,9})\.html',array('page','id'));
app::route('\/read\/(\d{1,9})\.html',array('read','id'));
app::route('\/(\d{1,9})\.html',array('home','id'));
app::route('\/fm\/(\d{1,9}).fm',array('fm','id'));
app::route('\/about',array('home','about'));

//也可以添加自动加载,或者加载程序设置
S('functions');
S('class/curl');
//配置完,可以启动啦
app::start();


