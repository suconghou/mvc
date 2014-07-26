<?
/**
 *  扩展函数配置区
 *  functions.php的函数配置区域
 *  配置函数的常量
 * 
 */
// function sendSms 配置
define('SMS_USER',18749667085);
define('SMS_PASS','11260sch');

// function sendMailByCloud 配置
define('MAIL_CLOUD_USER','postmaster@sumail.sendcloud.org');
define('MAIL_CLOUD_PASS','mK0A5iAS');
define('MAIL_CLOUD_FROM','admin@suconghou.cn');
define('MAIL_CLOUD_NAME','苏苏');

/*
 *应用配置区域,对整个应用的配置
 *针对自己的应用配置参数
 *设置常量等
 *
 */

define('LIST_PER_PAGE',25);///分页每页个数
//用户状态state 设置,
//登录时,获取state>=1的,并判断state是否为1,为1则提示冻结,不予登陆
//故2,3会直接登录,1会提示不能登陆,0会提示不存在用户,但是此用户名和邮箱不能再次使用,除非真正删除
define('USER_STATE_DELETE',0);//已删除
define('USER_STATE_FREEZE',1);//已冻结,不能登录,提示
define('USER_STATE_COMMON',2);//普通,未验证邮箱,可以登录
define('USER_STATE_VAILDE',3);//已验证邮箱










