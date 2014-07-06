<?


date_default_timezone_set('PRC');//设置时区
set_include_path('./app/s/');//此路径下可直接include

///DEBUG模式,0,禁用debug隐藏警告消息,同时也不记录日志,
//1记录错误日志,显示错误的堆栈信息(如果未自定义错误页)
define('DEBUG',1);

//设计你自己的错误页面,存放在./app/s/error下的html或php文件,没有请保持为空
//自定义错误页不会向用户输出详细错误消息
define('USER_ERROR_PAGE_404','');
define('USER_ERROR_PAGE_500','');

//是否开启GZIP
define('GZIP',0);
///URL最大长度限制
define('MAX_URL_LENGTH',200);

//默认的控制器
define('DEFAULT_CONTROLLER','home');
///默认的动作
define('DEFAULT_ACTION','index');


//是否启用正则路由
define('REGEX_ROUTER',1);
///自定义正则路由
///添加一个正则路由,数组第一个为控制器,第二个为方法,前面的将作为该方法的第一个实参,以此类推

/*route('\/files\/(\w+)\/(\w+\.\w{2,4})',array('regex','storage'));
route('\/upload\/?',array('regex','upload'));
route('\/page\/(\d{1,9})\.html',array('page','id'));
route('\/music\/(.+)\.(aac|mp3)',array('music','link'));
*/



//mysql数据库配置
define('DB_HOST','localhost');
define('DB_PORT',3306);
define('DB_NAME','blog');
define('DB_USER','root');
define('DB_PASS','123456');


//sqlite 数据库配置
define('SQLITE','./app/s/data.db');
//配置使用何种数据库,0为mysql,1为sqlite
define('DB',0);

///smtp配置
define('MAIL_SERVER','smtp.126.com');
define('MAIL_PORT',25);
define("MAIL_AUTH",true);
define('MAIL_USERNAME','suconghou@126.com');
define('MAIL_PASSWORD','11260sch45770');
define('MAIL_NAME','系统邮件');


// 用户应用设置,是否自动加载应用设置,以及应用函数库
define('APP_CONFIG','./app/s/app_config.php');
define('APP_FUN','./app/s/functions.php');
APP_CONFIG&&include APP_CONFIG;
APP_FUN&&include APP_FUN;




// end file of config
