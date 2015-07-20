# 简单易用的MVC开发框架

------

##框架特色
> * 核心代码不足2000行,仅两个文件便可工作,极速加载
> * 单文件入口,不依赖`PathInfo`,入口文件即是配置文件,超级简洁
> * 文件夹随意移动,轻松多项目共享,入口文件随意命名,CLI模式轻松使用
> * `MYSQL/SQLITE`任意切换,注入过滤/ORM,文件缓存/HTTP缓存/数据库缓存,轻松安全
> * 项目打包,`LESS`解析压缩,`JAVASCRIPT`打包压缩,动静分离部署,全栈开发,轻松搞定
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

对于`nginx`类型为

```
try_files $uri $uri/ /index.php?$args;
```

对于`apache`

```
Options -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```
----
##开始使用
- `S()` 即可加载`app/system` 路径下的文件,或者下一级目录的文件 ,可加载普通php文件,也可加载.class.php文件,后者必须存在以文件名命名的类
- `M()` 可加载`app/model`目录下的文件,该文件必须是以文件名命名的类文件,作为模型
- `V()` 为加载视图,目录为`app/view`下,一个方法内只能使用一次,如需多次使用,可用`template`替换
- `C()` 为缓存控制函数,可控制文件缓存和HTTP缓存
- 方法`V()`也可以直接调用文件缓存,第三个参数为使用文件的失效时间,若填写则启用文件缓存,否则不启用

```
//如下,表示按机构获取用户列表渲染视图,并使用文件缓存,每次缓存60分钟
V('userlist',M('m_user')->userListByFid($fid),60);
```

更多类库方便使用

```
//加载一个类库就是这么简单
//取得指定url里的所有href连接
$hrefs=S('class/Curl')->fetch('href',$url);
//甚至你还可以直接调用他的静态方法
$result=Curl::post($url,$data)
```

- 所有加载过的类库和模型,都会记住是否已加载过,不会重复加载,也不会重复实例化
- 加载过的类库和模型全局可用
- class以及controller和model目录下的类都可以直接通过类名调用静态方法

-----
##其他

###获取更多详细用法,见[使用说明](http://www.suconghou.cn/phpframe)
-----
2014年9月12日
