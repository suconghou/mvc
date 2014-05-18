<?

//这里,你应用的设置
//发送飞信设置
define('SMS_USER',18749667085);
define('SMS_PASS','11260sch');

define('LIST_PER_PAGE',25);///分页每页个数
//用户状态state 设置,
//登录时,获取state>=1的,并判断state是否为1,为1则提示冻结,不予登陆
//故2,3会直接登录,1会提示不能登陆,0会提示不存在用户,但是此用户名和邮箱不能再次使用,除非真正删除
define('USER_STATE_DELETE',0);//已删除
define('USER_STATE_FREEZE',1);//已冻结,不能登录,提示
define('USER_STATE_COMMON',2);//普通,未验证邮箱,可以登录
define('USER_STATE_VAILDE',3);//已验证邮箱










