# 简单易用的 MVC 开发框架

---



## 框架特色
> * 核心代码不足1000行,仅两个文件便可工作,极速加载
> * 单文件入口,不依赖`PathInfo`,入口文件即是配置文件,超级简洁
> * 文件夹随意移动,轻松多项目共享,入口文件随意命名,CLI模式轻松使用
> * `MYSQL/SQLITE`任意切换,注入过滤/ORM,文件缓存/HTTP缓存/数据库缓存,轻松安全
> * 异常捕获,`DEBUG`日志,自定义错误页,自定义异常路由一应俱全
> * 普通路由,正则路由,回调处理,百变URI随心所欲,插件模式,即插即用
> * 文件加载自动完成,延迟按需加载、无需`Include`简单高效

---

## 安装配置

- `index.php`入口文件即配置文件,`core.php`框架核心,外加一个处理请求的控制器文件
- `PHP8.0`及以上
- 使用PDO连接数据库,支持`MySQL`和`Sqlite`,需开启`PDO_MYSQL`
- 定义配置文件的程序路径(一般不需改变)和其他参数,例如SMTP,数据库,即可完美使用
- 需要URL REWRITE支持,否则链接中要添加`index.php`
- rewrite 即为一般的index.php rewrite写法

对于`nginx`

```nginx
try_files $uri $uri/ /index.php$is_args$args;
```

加入`location / {}`里面

对于`apache`

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

Web程序可将目录结构调整为

```
├── app
│  ├── controller
│  │  └── home.php
│  ├── model
│  │  └── util.php
│  └── system
│     └── core.php
├── README.md
├── var
│  ├── html
│  └── log
└── www
   └── index.php
```

----

## 开始使用

---

入口文件`index.php`即为配置文件,主要配置控制器模型等地址

配置项`lib_path`为一个数组,配置控制器,模型,类库的自动查找地址

`view_path`为模板查找文件夹

`var_path`为缓存目录和日志目录

文件夹 controller model system 其实并无区别

系统并不是按照文件夹结构来区分 controller 和 model 而是按照类本身的特性

包含`__invoke`魔术方法的类将被视为控制器

在控制器抛出异常时,此方法将被调用用于处理异常.

`event`字段配置闭包函数可用于扩展`app`


- 所有加载过的控制器,都会记住是否已加载过,不会重复加载,也不会重复实例化
- 加载过的类库和模型全局可用
- 所有的同名文件类都可以直接通过类名调用其静态方法,或new实例化


对于低于8.1版本，需要实现`array_is_list`函数
```php
if (!function_exists('array_is_list'))
{
    function array_is_list(array $a)
    {
        return $a === [] || (array_keys($a) === range(0, count($a) - 1));
    }
}
```
对于低于8.0版本，需要实现`str_contains`函数
```php
if (!function_exists('str_contains'))
{
	function str_contains(string $haystack, string $needle)
	{
		return empty($needle) || strpos($haystack, $needle) !== false;
	}
}
```


## DEBUG 

配置文件`debug`字段用于开启debug模式,其值可为`0/1` 或者 `false/true`

框架判断其布尔值,`debug`模式下,记录debug级别log,捕获所有错误

无论是否debug,对于大于notice的错误，只要日志目录可写，都会记录相应的错误。




-----

---

## 路由

框架同时包含正则路由和普通路由.

普通路由按照目录结构路由,

正则路由按照URI匹配.


**_使用 route::get() 添加正则路由_**

正则路由优先级高于普通路由.


正则路由有多种写法

字符串函数

```php
route::get('\/print','print_r');
```

闭包
```php
route::get('\/dump',function(...$a){
	var_dump($a);
});

```

实例化控制器

控制器类将被自动实例化,然后执行

```php
route::get('\/hello',['home','hello'])
route::get('\/hello',['home','hello','world'])
```

静态化控制器

控制器类方法将被静态调用

```php

route::post('\/static','home::echo');

```

捕获参数
```php
route::get('\/userinfo\/(\d+)',['home','userinfo'])
```


带命名空间的静态调用,可调度到子文件夹,自己再次分发路由
```php
route::get('\/admin','\admin\form::index')
```



闭包模式
```php
route::get('\/about',function(){echo 'about';})
```


**URL 拼接**
```php
route::u('/home/hello',['act'=>'hi'])
```

**重定向**
```php
route::to('/login')
```

## 控制器

默认的控制器为`home`,默认的action为`index`

控制器被实例化时,将传入触发实例化时的路由参数给构造函数

控制器实现了单例模式,一个控制器只会实例化一次

`app::run(array $r)` 可以内部转移控制器,交由其他控制器执行

## 自定义异常处理器

除了控制器的`__invoke`方法能处理异常外,全局异常可配置自定义处理

在配置中添加`notfound`和`errfound`并赋值一个闭包,开启自定义错误处理

```php
'notfound' => function ($e, $cli) {
	echo '404 page not found';
},
'errfound' => function ($e, $cli) {
	echo 'user hand this error : ', $e->getMessage();
},

```

自定义异常处理后,框架提供的404,500等错误详情,页面将不再展现,只能通过日志查看




## 使用缓存

框架內建2种缓存,可同时工作

### HTTP 缓存

HTTP 缓存使用 `app::cache(int $second)`开启

需要在控制器输出其他内容前调用

客户端下次请求将会`200(from cache)`

有客户端发起协商缓存时,框架也会自动验证,命中时返回`304`

可用于缓存接口,页面等

### 页面缓存

模板函数`template(string $v, array $data = [], callable|int $callback = 0, string $path = '')`实现了页面缓存

设置`$callback`为一个大于1的秒数,即可开启页面自动缓存

同时必须确保配置项`var_path`是可写的,缓存文件存储在其`html`子文件夹

页面缓存的缓存键是当前请求的`URI`,不包含query部分

可用于缓存公共页面,不可缓存带有个人信息的页面

请求来临时同样鉴别对应的`URI`是否已缓存

命中时释放缓存,缓存失效时删除缓存

配置`$callback`为1，则返回模板渲染的数据而不是直接输出，配置为闭包可以获取渲染结果，手动处理后续逻辑


## 验证器

`request::verify(array $rule, array|bool $post = true, bool|callable $callback = false)`

`$post`为要验证的数据,若非数组，则true代表$_POST,false代表$_REQUEST

`$rule`

例:
```php
$r =
	[
		'q' => ['maxlength=50' => 'q最大50字符'],
		'type' => ['set=video' => 'type不合法'],
		'order' => ['set=viewCount' => 'order不合法'],
		'channelId' => ['/^[\w\-]{20,40}$/' => 'channelId为20-40位字母'],
		'pageToken' => ['/^[\w\-]{4,14}$/' => 'pageToken为4-14位字母'],
		'relatedToVideoId' => ['/^[\w\-]{4,14}$/' => 'relatedToVideoId为4-14位字母'],
		'maxResults' => ['number=1,50' => 'maxResults不合法'],
		'regionCode' => ['set=HK,TW,US,KR,JP' => 'regionCode不合法'],
		'part' => 'id,snippet',
	];

```
注意 `maxResults` 的配置项为一个数组,元素可为闭包和其他规则,
如果直接写一个闭包而不是数组,代表使用闭包的返回值,而不是对输入值校验.


內建的验证类型有

类型限定
```
'array', 'bool', 'float', 'int', 'null', 'numeric', 'object', 'scalar', 'string'
```
ctype类型
```
'alnum', 'alpha', 'cntrl', 'digit', 'graph', 'lower', 'print', 'punct', 'space', 'upper', 'xdigit'
```
其他 `require` `required` `default` `number` `json` `url` `ip` `email` `username` `password` `phone`

动态比较的类型有 `minlength` `maxlength` `length` `eq` `eqs` `set` `int` `number` `numeric`

> required 数字0,字符串0,空数组,空字符串等被认为校验不通过,其他true值,被认为通过校验

> require 在required的基础上允许数字0和字符串0校验通过
> 注意:空数组,空字符串,被认为校验不通过, 与`required`的区别在于数字0和字符串0,`required`更加严格

> default 如果值不存在则使用此默认值并忽略规则校验,如果有值,则规则将会生效

> int,float等为强类型规则, number可以既接受int型也接受字符串格式的整数

> numeric可以既接受float型也接受字符串格式的小数,也可以既接受int型也接受字符串格式的整数

> int,number,numeric,length 可以使用区间限定,`int=1,50`表示int型1和50之间,`length=10,20`表示string类型长度在10至20

> `minlength=8` 表示string长度最小8

> set 规则只能针对值是string类型的值做判断,值为int类型的,一律判断不通过

> 直接使用数组语法来判断更加复杂的是否在集合中, 此比较方法是强类型的比较

> eqs为不区分大小写,eq为区分大小写

> 键可扩展为一种静态方法模式校验规则,例 r::domain 表示使用r类中的静态方法domain来校验，实现复杂校验

定义`callback`值,用于检验不通过时的动作

> false 抛出异常,此为默认

> 闭包函数, 异常将传递给此闭包函数处理

> true 立即响应json并中断




## 多数据库链接

某个Model要链接其他数据库,只需要重写ready方法即可,然后返回新的PDO实例
```php
public static function ready(): PDO
{
	static $_pdo;
	$_pdo ??= self::init(app::get('dbdev'));
	return $_pdo;
}
```


## 轻量级的 ORM 操作

无过度封装,简单直接,轻松完成大部分数据库操作.


PDO参数
```php
$options = [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_TIMEOUT => 3, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_STRINGIFY_FETCHES => false];
```

`ATTR_EMULATE_PREPARES`若是开启的话,数据库中的`int`和`float`取回来时,在PHP中被转化为`string`,造成类型不一致,故需要
```
ATTR_EMULATE_PREPARES => false
ATTR_STRINGIFY_FETCHES => false
```

`ATTR_EMULATE_PREPARES`配置为`false`时,预编译中的同名命名参数(`:name`这样的形式)只能使用一次.
见https://www.php.net/manual/en/pdo.prepare.php

命名参数与`?`占位符也不可混用

此框架都已自动处理.


### 增加

```php
orm::insert(array $data,string $table='',bool $ignore=false,bool $replace=false)
orm::replace(array $data,string $table='')
```

`replace`也是通过`insert`方法,只是参数不同.

`$ignore`设置为`true`可以使用`INSERT IGNORE`模式

> _`insert`方法没有完成`ON DUPLICATE KEY UPDATE`,若想使用,见下面说明_

> _`insert`方法没有完成`INSERT DELAYED INTO`,若想使用,见下面说明_

> _对于批量插入,参见下面说明_

### 删除

```php
orm::delete(array $where=[],string $table='')
```

将`$where`设置为空数组即可删除全表数据

详细的`$where`使用见*WHERE 构造器*

### 查询

```php
orm::find(array $where=[],string $table='',string $col='*',array $orderLimit=[],$fetch='fetchAll')
orm::findOne(array $where=[],string $table='',string $col='*',array $orderLimit=[1],$fetch='fetch')
orm::findVar(array $where=[],string $table='',string $col='COUNT(1)',array $orderLimit=[1])
orm::findPage(array $where=[],string $table='',string $col='*',int $page=1,int $limit=20,array $order=[])
```

`findOne`,`findVar`,`findPage`均是借助于`find`方法,只不过传递参数不同.

`findOne`默认`LIMIT 1`,只返回一行数据

`findVar`在`findOne`的基础上仅返回一个字段,并且默认是`COUNT(1)`即计算行数,可以修改参数三返回希望的字段.

`findPage`为分页,返回指定页的数据和上一页,下一页等,可以添加排序规则,详细见*ORDERLIMIT 构造器*

详细的`$where`使用见*WHERE 构造器*

#### 大量查询

如果你的一条 SQL 要查询大量数据,结果集往往超过几十万条,一次读取结果集会使得内存溢出,脚本终止.

其实`find`的第五个参数可以帮助你.

该参数为获取结果集的方法,`find`方法默认是一次性全部获取为数组,你可以传入参数`true`交由自己主动获取.

使用参数`true`后将返回一个`PDOStatement`,你将可以自由进行后续操作.

```php
$stm=User::find(['id >'=>1],'userTable','*',['id'=>'ASC'],true);
while($row=$stm->fetch())
{
}
```

> 注意: pdo->exec() 返回的是一个int值,代表被影响的行数, 例如runSql()函数
>
> stm->execute() 返回的是一个布尔值,代表是否操作成功,
> 使用 stm->rowCount() 才能取到执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。

你可以修改方法`fetch`为`fetchObject`

他们二者的不同是以数组还是对象的方式返回.

即使循环获取,数据也是从 MySQL 服务器发送到了 PHP 进程中保存,若数据实在太大,可以设置数据任然保存在 MySQL 服务器,循环的过程中现场取

在查询之前,给 PDO 实例设置

```php
self::setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
```

然后再循环获取,内存使用会显著下降

> _因 PDO 使用长连接,该设置会影响一定时段内的所有 SQL 查询,你也可以查询完设置回`true`避免影响其他查询_ 
> _自 PHP5.5 起,可以使用 yield,大数据量下可以显著帮你节省内存_

#### 子查询

使用原始值可以实现子查询

```php
$where=['!id IN'=>'(SELECT `id` FROM `user` WHERE fid=1)','age >'=>18]
```

### 更新

```php
orm::update(array $where,array $data,string $table='')
```

`$where`的具体形式见*WHERE 构造器*

`$data`的具体形式见*SET 构造器*

### WHERE 构造器

```php
orm::condition(array &$where,string $prefix='WHERE')
```

在查询和删除,更新等场景下,传入一个数组作为条件

`$where`是一个数组变量,一般为一维数组,某些需要使用`IN`操作时为二维数组

`$where`为一个引用,执行过后会清理`$where`中的数据,因此必须传入一个变量名,执行后的`$where`变量将有后续用途

例如 `$where=['username'=>'name1','age'=>18];`

这样会筛选`username`为`name1`并且`age`为 18 的用户

> 默认的结合条件是`AND`,你可以在数组的最后面添加一个指定的结合符

> 改用`OR`连接 `$where=['username'=>'name1','age'=>18,'OR'];`

同时对于键(例如`username`)还可以添加一些修饰符和操作符.

键可以由三部分组成,以空格隔开

第一段对应于数据库中的字段,第二段是修饰符,往往是`NOT`或空,第三段是操作符,可以是`>` , `<` , `>=` , `<=` , `!=` , `<>` , `IN` , `LIKE` , `REGEXP`

例如`$where=['age >'=>18,'name LIKE'=>'user%'];`

同理,也可以使用`NOT LIKE`

如果你需要使用`IN`操作符,也是可以的,给它的键值为一个数组

`$where=['id'=>[1,3,5,7]];` 数组作为参数默认就置为`IN`操作符

将会等到 `where id in (1,3,5,7)`,如果要使用`NOT IN`需要显式指明

`$where=['id NOT IN'=>[1,3,5,7]]`

**_使用字段的引用和内置函数_**

`$where`数组中的键值都会进行预处理操作,因此不能使用字段的引用和内置函数.

若要使用,可以在键的第一段,即数据库字段前加`!`定义符,代表要使用原始值.

`$where=['!time < '=>'UNIX_TIMESTAMP()']`

使用`!`定义符后对应的键值须为定值,对于用户发送来的数据,使用`!`定义符前需要仔细过滤,仅能信任使用`intval`过滤后的值.

`!time` 或者 `! time` `! time <` `!time <` 等都是合法的,

但`!time<` `! time<`是非法的,字段和操作符之间必须使用空格隔开

**NULL处理**

当条件的值为`null`时,自动构造为`IS NULL`语句.

例如 `['name'=>null]` 转化为
```sql
`name` IS NULL
```

如果需要`IS NOT NULL`需要显示申明
`['name IS NOT'=>null]`

此时,使用原始值前缀`!`对此构造无任何影响

`['!name'=>null]`

`['!name IS NOT'=>null]`

都是合法的.

**WHERE NOT EXISTS**

使用 `db::condition($where, 'WHERE NOT EXISTS')` 可以构造`WHERE NOT EXISTS`语句

可以参考内部的`find` `delete`构造方法,修改其`condition`语句派生出更多构造函数.

**FIND_IN_SET**

例如：查找ids字段中包含3的数据

当且仅当`Where`条件已有两条约束时，我们可以使用where数组构造

```php
$where = ['enable' => 1, 'id >' => 0, "AND FIND_IN_SET('3',ids) AND"];
var_dump(db::find($where, 'iplog'));
```
构造出
```sql
SELECT * FROM iplog WHERE (`enable` = :enable_1 AND FIND_IN_SET('3',ids) AND `id` > :id_2)
```
数组的第三个元素是作为连接符连接多个条件的

或者更通用的，我们可以单独写个函数处理

```php
final public static function findInSet(array $where = [], string $table = '', string $col = '*', array $orderLimit = [], $fetch = 'fetchAll')
{
	$sql = sprintf('SELECT %s FROM %s%s%s', $col, $table ?: static::table(), self::condition($where, "WHERE FIND_IN_SET('3',ids) AND"), $orderLimit ? self::orderLimit($orderLimit) : '');
	return self::exec($sql, $where, $fetch);
}
```
构造出
```sql
SELECT * FROM iplog WHERE FIND_IN_SET('3',ids) AND (`enable` = :enable_1)
```



**HAVING**

简单的`HAVING`语句可以同上,修改condition后的参数,包含`GROUP BY`等复杂语句需要自己拼接

```php
final public static function findHaving(array $where = [], string $table = '', string $col = '*', array $orderLimit = [], $fetch = 'fetchAll')
{
	$sql = sprintf('SELECT %s FROM %s%s%s', $col, $table ?: static::table(), self::condition($where, "HAVING"), $orderLimit ? self::orderLimit($orderLimit) : '');
	return self::exec($sql, $where, $fetch);
}
```

> _构造器一次不能生成包含`AND`和`OR`相互嵌套的复杂条件,若想使用,见下面说明_

### SET 构造器

```php
orm::values(array &$data,bool $set=false,string $table='')
```

`$data`使用关联数组表示,默认生成`VALUES()`语句用于`INSERT`,将`$set`设置为`true`生成用于`update`的语句

`['name'=>'name1','pass'=>123]`

数组的键也有一个前置定义符`!`,表示原始值,使用此定义符可以调用函数,引用字段等,插入原始值等.

如 `['v'=>time(),'!t'=>'UNIX_TIMESTAMP()']` 添加了!则存储的是时间戳,不加!则是存储此字符串

`['!count'=>'count+1']` 使`count`的值加一

`['!count'=>'count+age']` 引用其他字段,`count`设置为`count+age`的和

除非你要调用函数或引用字段,否则不建议你使用原始值,

原始值没有引号包裹,也不是预处理字段,随意使用将会带来安全隐患.


**注意**

同一个变量不可传入`values()`或`condition()`两次,因为这些方法会修改传入值,第二次执行时,用到的值已经被第一次修改了

可以使用`$update=$insert`,两个变量各自修改不会影响另一个


### ORDERLIMIT 构造器

```php
orm::orderLimit(array $orderLimit)
```

`$orderLimit`使用关联数组,键为数据库字段,键值为排序规则,`ASC`或`DESC`,也可以使用布尔值代替,`true`为`ASC`,`false`为`DESC`

如`$orderLimit=['id'=>'DESC','name'=>'ASC']`

还可以使用`LIMIT`,添加一个整形的键值对

`$orderLimit=['id'=>'DESC','name'=>true,35=>20]`

代表`LIMIT 35,20`

直接使用`$orderLimit=['id'=>'DESC','name'=>'ASC',5]`

代表`LIMIT 0,5`

### 使用 ON DUPLICATE KEY UPDATE

```php
final public static function insert_or_update(string $table, array $insert, array $update)
{
	$sql = sprintf('INSERT INTO%s ON DUPLICATE KEY UPDATE %s', self::values($insert, false, $table), self::values($update, true));
	return self::exec($sql, $insert + $update);
}
```
自己拼接的话
```php
$sql = sprintf('INSERT INTO%s ON DUPLICATE KEY UPDATE id=:id,name=:name', self::values($insert, false, static::table));
return self::exec($sql, $insert + $update);
```
需要`$update=['id'=>1,'name'=>2];`与自己写的SQL对应


### 使用 INSERT DELAYED

> DELAYED 仅适用于 MyISAM, MEMORY 和 ARCHIVE 表

可采用如下方式构造

```php
$sql=sprintf('REPLACE DELAYED INTO `%s` %s',static::table,self::values($data));
$sql=sprintf('INSERT DELAYED INTO `%s` %s',static::table,self::values($data));
$sql=sprintf('INSERT DELAYED IGNORE INTO `%s` %s',static::table,self::values($data));
```

### 使用`CASE WHEN`

> 请自己拼接SQL,然后预处理执行
...

### 批量插入

可以使用`prepare`绑定数据循环.

如果数据表是`InnoDB`而不是`MyISAM`,还可以开启事务,进一步提升速度.

因为`InnoDB`默认是`auto-commit mode`,每条 SQL 都会当做一个事务自动提交,会带来额外开销.


`INSERT INTO`也可以使用`IGNORE`,`REPLACE`,`ON DUPLICATE KEY UPDATE`


数据源

```php
$column = ['title', 'text', 'ids', 'enable'];
$data = [
	['title1', 'text1', 'a,b', 1],
	['title2', 'text2', 'a,b', 1],
	['title3', 'text3', 'b,c', 0],
	['title4', 'text4', 'b,c', 0]
];
```

```php

class test extends db
{

	final public static function insert_many(string $table, array $column, array $data)
	{
		$column = array_combine($column, $column);
		$pdo = self::ready();
		$pdo->beginTransaction();
		try {
			$sql = sprintf('INSERT INTO `%s` %s', $table, self::values($column));
			$stm = $pdo->prepare($sql);
			$key_names = array_keys($column);
			foreach ($data as $row) {
				foreach ($row as $i => $value) {
					$stm->bindValue(":{$key_names[$i]}", $value);
				}
				$stm->execute();
			}
			return $pdo->commit();
		} catch (Throwable $e) {
			$pdo->rollBack();
			throw $e;
		}
	}
}
```


### 更快的批量插入

使用单条 SQL 代替循环插入速度将会更快

数据源和参数同上


```php

class test extends db
{

	final public static function insert_many(string $table, array $column, array $data)
	{
		$column = array_combine($column, $column);
		$pdo = self::ready();
		$pdo->beginTransaction();
		try {
			$sql = sprintf('INSERT INTO `%s` %s', $table, self::values($column));
			$stm = $pdo->prepare($sql);
			$key_names = array_keys($column);
			foreach ($data as $row) {
				foreach ($row as $i => $value) {
					$stm->bindValue(":{$key_names[$i]}", $value);
				}
				$stm->execute();
			}
			return $pdo->commit();
		} catch (Throwable $e) {
			$pdo->rollBack();
			throw $e;
		}
	}

	// 返回受影响的行数
	final public static function insert_once_many(string $table, array $column, array $data, array $duplicateKeyUpdate = [])
	{
		$values = array_merge(...$data);
		$holders = substr(str_repeat('(?' . str_repeat(',?', count(reset($data)) - 1) . '),', count($data)), 0, -1);
		$sql = sprintf('INSERT INTO %s (%s) VALUES %s', str_contains($table, '.') ? $table : "`$table`", implode(',', array_map(static fn (string $k) => "`$k`", $column)), $holders);
		if ($duplicateKeyUpdate) {
			$sql .= ' ON DUPLICATE KEY UPDATE ' . implode(',', array_map(static fn (string $v) => "`$v`=VALUES($v)", $duplicateKeyUpdate));
		}
		return self::exec($sql, $values, 'rowCount');
	}
}
```


_批量插入中使用`ON DUPLICATE KEY UPDATE`仅需配置第四个参数_

如果数据量巨大，可能造成SQL语句太长，可以使用`array_chunk`切割`$data`分批调用


### 嵌套的`AND`和`OR`

对WHERE 构造器传入二维数组，可以构造嵌套的`AND`或`OR`


```php
$where1=['age >'=>18,'sex'=>1];
$where2=['id >'=>20,'id <'=>40];
db::find([$where1, $where2, 'OR'], 'users');
```
构造出如下SQL
```sql
SELECT * FROM users WHERE ((`age` > :age_1 AND `sex` = :sex_2) OR (`id` > :id_3 AND `id` < :id_4))
```




### 高级查询

```php
$where=['age >'=>1];
$sql=sprintf('SELECT id FROM `%s` m%s',static::table,self::condition($where,'LEFT JOIN `user` u ON u.id=m.id WHERE'));
return self::exec($sql,$where,'fetchAll');
```

如果你需要非常复杂的 SQL 查询,可能不能一次就利用方法完成,需要多次操作

或者自己进行`prepare`并绑定.

使用`orm::query`可以一次完成多个 SQL 操作,它是`orm::exec`的批处理.

```php
$sql1="SELECT 1";
$sql2="SELECT 2";
$sql3="SELECT 3";
$data1=$data2=$data3=[];
[$res1,$res2,$res3]=self::query([$sql1,$data1,'fetchAll'],[$sql2,$data2,'fetch'],[$sql3,$data3,true]);
```

每个参数都是数组

数组内部,第一个元素要批处理的$sql 语句,第二个参数绑定的参数,第三个参数获取方式.

所有的 SQL 执行最终都会指向`orm::exec($sql,array $params=[],$fetch='')`


第三个参数`fetch`

如果为空,代表非查询语句,返回的是影响的行数(无预处理`exec()`)或者是否成功(有预处理`stm->execute()`)

如果是个`string`,返回结果集(例如`fetchAll`,无论是否走了预处理)

如果是`true`(仅当是查询语句,或者是预处理操作,才能置为true),返回一个`PDOStatement`(无论是否走了预处理),后续按何种方式获取结果集,自己任意操作.

>
> 注意: params 为空则代表没有预处理参数,底层会直接调用 pdo->exec() 或 pdo->query()
> 如果不为空,则会先预处理,然后stm->execute()
>
> pdo->exec() 返回的是一个int值,代表被影响的行数, 例如runSql()函数
>
> stm->execute() 返回的是一个布尔值,代表是否操作成功,
> 使用 stm->rowCount() 才能取到执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。
>
> 如果你关心受影响的行数,需要自己实现这一细节.

```php

class testdb extends db
{

	final public static function getUpdate(array $where, array $data, string $table = '')
	{
		$sql = sprintf('UPDATE %s SET %s%s', $table ?: self::table(), self::values($data, true), self::condition($where));
		$params = $data + $where;
		if (empty($params)) {
			return self::exec($sql);
		}
		return self::exec($sql, $params, 'rowCount');
	}

	final public static function getDelete(array $where = [], string $table = '')
	{
		$sql = sprintf('DELETE FROM %s%s', $table ?: self::table(), self::condition($where));
		if (empty($where)) {
			return self::exec($sql);
		}
		return self::exec($sql, $where, 'rowCount');
	}
}

```
### SQLITE 差异

insert ignore 和 replace 与MySQL的语法存在差异

insert or replace：如果不存在就插入，存在就更新
insert or ignore：如果不存在就插入，存在就忽略

insert函数可以新增如下对SQLITE的改写版

```php
final public static function sqliteInsert(array $data, string $table = '', bool $ignore = false, bool $replace = false)
{
	$sql = sprintf('%s %sINTO %s %s', $replace ? 'INSERT OR REPLACE' : 'INSERT', $ignore ? 'OR IGNORE ' : '', $table ?: static::table(), self::values($data));
	return self::exec($sql, $data);
}
```
