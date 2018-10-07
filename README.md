# 简单易用的MVC开发框架

------

## 框架特色
> * 核心代码不足2000行,仅两个文件便可工作,极速加载
> * 单文件入口,不依赖`PathInfo`,入口文件即是配置文件,超级简洁
> * 文件夹随意移动,轻松多项目共享,入口文件随意命名,CLI模式轻松使用
> * `MYSQL/SQLITE`任意切换,注入过滤/ORM,文件缓存/HTTP缓存/数据库缓存,轻松安全
> * 异常捕获,`DEBUG`日志,自定义错误页,自定义异常路由一应俱全
> * 普通路由,正则路由,回调处理,百变URI随心所欲,插件模式,整站模式,即插即用
> * 文件加载自动完成,无需`Include`简单高效,丰富类库轻松调用



------

## 安装配置

- 框架的安装非常简单,仅需三个文件便可运行
- `index.php`入口文件即配置文件,`core.php`框架核心,外加一个处理请求的控制器文件
- 框架早期版本支持`PHP5.2`,先已全新迁移至`PHP5.4`,强烈推荐你使用`PHP5.6`或`PHP7`并开启`OPcache`
- 使用PDO连接数据库,支持`MySql`和`Sqlite`,需开启`PDO_MYSQL`
- 定义配置文件的程序路径(一般不需改变)和其他参数,例如SMTP,数据库,即可完美使用
- 需要URL REWRITE支持,否则链接中要添加`index.php`
- rewrite 即为一般的index.php rewrite写法

对于`nginx`

```nginx
try_files $uri $uri/ /index.php?$args;
```
加入`location / {}`里面

对于`apache`

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```
存入文件`.htaccess`

----
##开始使用

- `with()` 即可加载`app/system` 路径下的文件,或者下一级目录的文件 ,可加载普通php文件,也可加载.class.php文件,后者必须存在以文件名命名的类
- 所有加载过的类库和模型,都会记住是否已加载过,不会重复加载,也不会重复实例化


- 加载过的类库和模型全局可用
- Class以及controller和model目录下的类都可以直接通过类名调用其静态方法

-----
## CLI运行和打包


指定debug模式运行  `debug=2  php index.php em lists2016`

打包项目 `php -d phar.readonly=0 index.php`

打包后,项目所有php结尾的文件被包含到一个文件内,无需任何修改即可直接运行phar




```php

// with(['code'=>0,'msg'=>'hello'])->out(20);//json
// with(['user'=>123456,'password'=>'hello222'])->out(['code'=>0,'msg'=>'ok'],1);//json without cache
// with($data)->out(30);//json http cache 30
// with($data)->out([],30);//json http cache 30
// with($data)->out(30,[]);//jsonp http cache 30
// with($data)->out([],30,'jsonp');//jsonp http cache 30
// with($data)->out(30,[],'jsonp');//jsonp http cache 30
// with($data)->out('login',40);//view http cache 40
// with($data)->out('login',40,true);//view file cache 40
// var_dump(M::get('a'),M::get('ab'),app::cost());
```

## 路由与RESTful

### 普通路由

普通路由模式和其他大部分框架如`codeigniter`一致,控制器文件内包含一个同名Class.

URI为`/api/userinfo/1`则对用于api.php里的userinfo方法,1为此方法的第一个实参

### 正则路由

***使用 app::route() 添加正则路由***

- 映射路由到普通路由上,访问/hello 等同于访问/home/hello

```php
app::route('\/hello',['home','hello'])
```

- 传递参数,访问`/userinfo/1` ,则参数1将会传递给home中的userinfo方法为第一个实参

```php
app::route('\/userinfo\/(\d+)',['home','userinfo'])
```

- 插件模式,访问/upload 会激发Plugins目录下的Upload类
```php
app::route('\/upload','Plugins/Upload')
```

- 闭包模式,直接对应一个闭包.
```php
app::route('\/about',function(){echo 'about';})
```

>  正则路由的优先级大于普通路由

## 控制器



### 依赖注入





## 同时连接多个数据库

默认的`db::getData` `db::runSql`等使用默认的数据库配置,默认的数据库配置为`config`中的`db`键,形式如
```
'db'=>
[
	'dsn'=>'mysql:host=172.168.1.3;port=13306;dbname=dbname;charset=utf8',
	'user'=>'work',
	'pass'=>'123456',
]
```
使用PDO链接,支持mysql,sqlite,pgsql等



内部根据$cfg做缓存

$instance=db::getInstance($cfg)


传入$cfg获取指定的实例

```php
$instance->insert
```

### 数据库切换



## 轻量级的ORM操作

无过度封装,简单直接,轻松完成大部分数据库操作.

### 增加

```php
orm::insert(array $data,$table=null,$ignore=false,$replace=false)
orm::replace(array $data,$table=null)
```

`replace`也是通过`insert`方法,只是参数不同.

`$ignore`设置为`true`可以使用`INSERT IGNORE`模式

> *`insert`方法没有完成`ON DUPLICATE KEY UPDATE`,若想使用,见下面说明*

> *`insert`方法没有完成`INSERT DELAYED INTO`,若想使用,见下面说明*

> *对于批量插入,参见下面说明*

### 删除

```php
orm::delete(array $where=[],$table=null)
```
将`$where`设置为空数组即可删除全表数据

详细的`$where`使用见*WHERE构造器*

### 查询

```php
orm::find(array $where=[],$table=null,$col='*',array $orderLimit=null,$fetch='fetchAll')
orm::findOne(array $where=[],$table=null,$col='*',array $orderLimit=[1],$fetch='fetch')
orm::findVar(array $where=[],$table=null,$method='COUNT(1)',array $orderLimit=[1])
orm::findPage(array $where=[],$table=null,$col='*',$page=1,$limit=20,array $order=[])
```

`findOne`,`findVar`,`findPage`均是借助于`find`方法,只不过传递参数不同.

`findOne`默认`LIMIT 1`,只返回一行数据

`findVar`在`findOne`的基础上仅返回一个字段,并且默认是`COUNT(1)`即计算行数,可以修改参数三返回希望的字段.

`findPage`为分页,返回指定页的数据和上一页,下一页等,可以添加排序规则,详细见*ORDERLIMIT构造器*

详细的`$where`使用见*WHERE构造器*

#### 大量查询

如果你的一条SQL要查询大量数据,结果集往往超过几十万条,一次读取结果集会使得内存溢出,脚本终止.

其实`find`和`findOne`的第五个参数可以帮助你.

该参数为获取结果集的方法,`find`方法默认是一次性全部获取为数组,你可以传入参数`true`交由自己主动获取.

使用参数`true`后将返回一个`PDOStatement`,你将可以自由进行后续操作.

```php
$stm=User::find(['id >'=>1],'userTable','*',['id'=>'ASC'],true);
while($row=$stm->fetch())
{
}
```

你可以修改方法`fetch`为`fetchObject`

他们二者的不同是以数组还是对象的方式返回.

即使循环获取,数据也是从MySQL服务器发送到了PHP进程中保存,若数据实在太大,可以设置数据任然保存在MySQL服务器,循环的过程中现场取

在查询之前,给PDO实例设置
```php
self::setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
```
然后再循环获取,内存使用会显著下降

> *因PDO使用长连接,该设置会影响一定时段内的所有SQL查询,你也可以查询完设置回`true`避免影响其他查询*
> *自PHP5.5起,可以使用yield,大数据量下可以显著帮你节省内存*

#### 子查询

使用原始值可以实现子查询
```php
$where=['!id IN'=>'(SELECT `id` FROM `user` WHERE fid=1)','age >'=>18]
```

### 更新

```php
orm::update(array $where,array $data,$table=null)
```

`$where`的具体形式见***WHERE构造器***

`$data`的具体形式见***SET构造器***

### WHERE构造器

```php
orm::condition(array &$where,$prefix='WHERE')
```

在查询和删除,更新等场景下,传入一个数组作为条件

`$where`是一个数组变量,一般为一维数组,某些需要使用`IN`操作时为二维数组

`$where`为一个引用,执行过后会清理`$where`中的数据,因此必须传入一个变量名,执行后的`$where`变量将有后续用途

例如 `$where=['username'=>'name1','age'=>18];`

这样会筛选`username`为`name1`并且`age`为18的用户

> 默认的结合条件是`AND`,你可以在数组的最后面添加一个指定的结合符

> 改用`OR`连接 `$where=['username'=>'name1','age'=>18,'OR'];`

同时对于键(例如`username`)还可以添加一些修饰符和操作符.

键可以由三部分组成,以空格隔开

第一段对应于数据库中的字段,第二段是修饰符,往往是`NOT`或空,第三段是操作符,可以是`>` , `<` , `>=` , `<=` , `!=` , `IN` , `LIKE` , `REGEXP`

例如`$where=['age >'=>18,'name LIKE'=>'user%'];`

同理,也可以使用`NOT LIKE`

如果你需要使用`IN`操作符,也是可以的,给它的键值为一个数组

`$where=['id'=>[1,3,5,7]];` 数组作为参数默认就置为`IN`操作符

将会等到 `where id in (1,3,5,7)`,如果要使用`NOT IN`需要显式指明

`$where=['id NOT IN'=>[1,3,5,7]]`

***使用字段的引用和内置函数***

`$where`数组中的键值都会进行预处理操作,因此不能使用字段的引用和内置函数.

若要使用,可以在键的第一段,即数据库字段前加`!`定义符,代表要使用原始值.

`$where=['!time < '=>'UNIX_TIMESTAMP()']`

使用`!`定义符后对应的键值须为定值,对于用户发送来的数据,使用`!`定义符前需要仔细过滤,仅能信任使用`intval`过滤后的值.


> *构造器一次不能生成包含`AND`和`OR`相互嵌套的复杂条件,若想使用,见下面说明*

### SET构造器

```php
orm::values(array &$data,$set=false,$table=null)
```

`$data`使用关联数组表示,默认生成`VALUES()`语句用于`INSERT`,将`$set`设置为`true`生成用于`update`的语句

`['name'=>'name1','pass'=>123]`

数组的键也有一个前置定义符`!`,表示原始值,使用此定义符可以调用函数,引用字段等,插入原始值等.

如 `['v'=>time(),'!t'=>'UNIX_TIMESTAMP()']` 添加了!则存储的是时间戳,不加!则是存储此字符串

`['!count'=>'count+1']` 使`count`的值加一

`['!count'=>'count+age']` 引用其他字段,`count`设置为`count+age`的和

除非你要调用函数或引用字段,否则不建议你使用原始值,

原始值没有引号包裹,也不是预处理字段,随意使用将会带来安全隐患.

### ORDERLIMIT构造器

```php
orm::orderLimit(array $orderLimit,$limit=[])
```
只需一个参数,`$limit`参数无需设置

`$orderLimit`使用关联数组,键为数据库字段,键值为排序规则,`ASC`或`DESC`,也可以使用布尔值代替,`true`为`ASC`,`false`为`DESC`

如`$orderLimit=['id'=>'DESC','name'=>'ASC']`

还可以使用`LIMIT`,添加一个整形的键值对

`$orderLimit=['id'=>'DESC','name'=>true,35=>20]`

代表`LIMIT 35,20`

直接使用`$orderLimit=['id'=>'DESC','name'=>'ASC',5]`

代表`LIMIT 0,5`

### 使用 ON DUPLICATE KEY UPDATE

例如`$data=['id'=>123,'name'=>'456'];` 其中`id`为主键

```php
$sql=sprintf('INSERT INTO%s ON DUPLICATE KEY UPDATE id=:id,name=:name',self::values($data,false,static::table));
return self::exec($sql,$data);
```

如果`$data`里的数据全部需要覆盖更新,可以直接使用`self::values($data,true)`
```php
$sql=sprintf('INSERT INTO%s ON DUPLICATE KEY UPDATE %s',self::values($data,false,static::table),self::values($data,true));
return self::exec($sql,$data);
```


### 使用 INSERT DELAYED

> DELAYED仅适用于MyISAM, MEMORY和ARCHIVE表

可采用如下方式构造
```php
$sql=sprintf('REPLACE DELAYED INTO `%s` %s',static::table,self::values($data));
$sql=sprintf('INSERT DELAYED INTO `%s` %s',static::table,self::values($data));
$sql=sprintf('INSERT DELAYED IGNORE INTO `%s` %s',static::table,self::values($data));
```

### 使用 CASE WHEN


### 批量插入

可以使用`prepare`绑定数据循环.

如果数据表是`InnoDB`而不是`MyISAM`,还可以开启事务,进一步提升速度.

因为`InnoDB`默认是`auto-commit mode`,每条SQL都会当做一个事务自动提交,会带来额外开销.

数据源
```php
$data=
[
	['id'=>11,'name'=>'name11']
];
```

`INSERT INTO`也可以使用`IGNORE`,`REPLACE`,`ON DUPLICATE KEY UPDATE`
```php
try
{
	$example=reset($data);
	self::beginTransaction();
	$sql=sprintf('INSERT INTO `%s` %s',static::table,self::values($example));
	$stm=DB::execute($sql,false);
	foreach($data as $row)
	{
		$stm->bindParam(':id',$row['id']);
		$stm->bindParam(':name',$row['name']);
		$stm->execute();
	}
	return self::commit();
}
catch(PDOExecption $e)
{
	self::rollBack();
	return false;
}
```

如果字段与数组的键相同,还可以简化变量绑定
```php
foreach ($row as $column => $value)
{
	$stm->bindParam(":{$column}",$value);
}
$stm->execute();
```


### 更快的批量插入

使用单条SQL代替循环插入速度将会更快

数据源
```php
$data=
[
	6=>['id'=>11,'name'=>'name1','age'=>22],
	8=>['id'=>12,'name'=>'name2','age'=>23],
	9=>['id'=>13,'name'=>'name3','age'=>24],
	3=>['id'=>14,'name'=>'name4','age'=>25],
];
```

设置`$values=[]`
```php
array_map(function($v)use(&$values){array_push($values,...array_values($v));},$data);
$holders=substr(str_repeat('(?'.str_repeat(',?',count(reset($data))-1).'),',count($data)),0,-1);
$sql=sprintf('INSERT INTO%s %s',sprintf(' `%s` (%s) VALUES',self::table,implode(',',array_map(function($k){return "`{$k}`";},array_keys(reset($data))))),substr(str_repeat('(?'.str_repeat(',?',count(reset($data))-1).'),',count($data)),0,-1));
```

如果你的PHP版本小于PHP5.6

将第一行替换为
```php
array_map(function($v)use(&$values){array_map(function($i)use(&$values){array_push($values,$i);},$v);},$data);
```

*批量插入中使用`ON DUPLICATE KEY UPDATE`*

在最后面添加一行
```php
$sql.=' ON DUPLICATE KEY UPDATE '.implode(',',array_map(function($v){return "`{$v}`=VALUES({$v})";},array_keys(reset($data))));
```
完成以后最好`unset($data,$holders);`释放内存,
然后`self::exec($sql,$values);`

如果`$data`太大,超过1W个元素,或者字段太大,建议分块插入

2000个一批,速度并不会有明显影响,内存会较为节省
```Php
foreach(array_chunk($data,2e3) as $item)
{
	self::dobatchinsert($item);
}
```


### 嵌套的`AND`和`OR`


```php
$where1=['age >'=>18,'sex'=>1];
$where2=['id >'=>20,'!id <'=>40];
$sql=sprintf('SELECT id FROM `%s`%s%s',static::table,self::condition($where1),self::condition($where2,'OR'));
return self::exec($sql,$where1+$where2);
```

> *如果你的条件中包含多个相同的字段,重复的需要使用原始值,否则绑定会引起混乱*

### 高级查询


```php
$where=['age >'=>1];
$sql=sprintf('SELECT id FROM `%s` m%s',static::table,self::condition($where,'LEFT JOIN `user` u ON u.id=m.id WHERE'));
return self::exec($sql,$where);
```
如果你需要非常复杂的SQL查询,可能不能一次就利用方法完成,需要多次操作

或者自己进行`prepare`并绑定.

使用`orm::query`可以一次完成多个SQL操作,它是`orm::exec`的批处理.

```php

$sql1="SELECT 1";
$sql2="SELECT 2";
$sql3="SELECT 3";
$data1=$data2=$data3=[];
list($res1,$res2,$res3)=self::query([$sql1,$data1,'fetchAll'],[$sql2,$data2,'fetch'],[$sql3,$data3,true]);
```

每个参数都是数组

数组内部,第一个元素要批处理的$sql语句,第二个参数绑定的参数,第三个参数获取方式.

所有的SQL执行最终都会指向`orm::exec($sql,array $bind=null,$fetch=null)`



## 视图

### 输出 JSON

### 视图缓存

### HTTP缓存





## 缓存

M.class.php

Kvdb.class.php

Cache.class.php

Store.class.php



引入命名空间

配置全局可读

数据库多实例同时连接.


spl_auto_load 代替 __autoload

with make


app::get() 获取和修改config

app::set()

app::load()


不能这样 , 普通路由有问题, 可考虑插件形式
route::get('get')

route::post()

route::put()

route::delete()






