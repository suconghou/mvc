<?


set_error_handler('show_errorpage');///异常处理
date_default_timezone_set('PRC');//设置时区
set_include_path('app/s/');

//默认的控制器
define('DEFAULT_CONTROLLER','home');
///默认的动作
define('DEFAULT_ACTION','index');
//是否开启GZIP
define('GZIP',0);
///URL最大长度限制
define('MAX_URL_LENGTH',200);
///DEBUG模式,0,禁用debug隐藏警告消息,1显示错误的堆栈信息
define('DEBUG',0);
//设计你自己的错误页面,存放在s/error下的html或php文件,自定义错误页会自动禁用debug
define('USER_ERROR_PAGE_404','404.html');
define('USER_ERROR_PAGE_500','500.html');
//是否启用正则路由
define('REGEX_ROUTER',1);
///自定义正则路由
///添加一个正则路由,数组第一个为控制器,第二个为方法,前面的将作为该方法的第一个实参,以此类推

route('/music/@name:.+\.\w{2,4}',array('regex','kuwomusic_quicklink'));
route('/musicinfo/@name:.+\.\w{2,4}',array('regex','kuwomusic_musicinfo'));
route('/musiclink/@name:.+\.\w{2,4}',array('regex','kuwomusic_musiclink'));
route('/files/@type:\w{3}/@name:.+\.\w{2,4}',array('regex','kupan'));




//mysql数据库配置
define('DB_HOST','localhost');
define('DB_PORT',3306);
define('DB_NAME','app_173aft');
define('DB_USER','root');
define('DB_PASS','123456');
//sqlite 数据库配置
define('SQLITE','app/s/data.db');
//配置使用何种数据库,0为mysql,1为sqlite
define('DB',1);

///smtp配置
define('MAIL_SERVER','smtp.126.com');
define('MAIL_PORT',25);
define("MAIL_AUTH",true);
define('MAIL_USERNAME','suconghou@126.com');
define('MAIL_PASSWORD','11260sch45770');
define('MAIL_NAME','系统邮件');



// end file of config
