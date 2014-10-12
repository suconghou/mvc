<!doctype html> 
<html> 
<head> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--[if lt IE 9]>
        <script src="http://cdn.bootcss.com/html5shiv/3.7.0/html5shiv.min.js"></script>
        <script src="http://cdn.bootcss.com/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="/static/css/frame.css">
    <link rel="stylesheet" href="/static/css/mvc.css">
    <title></title> 
</head>
<body>
	<header>
		<aside>
			<nav>
				<ul>
					<li><a href="#begin">开始使用</a></li>
					<li><a href="#smvc">SMVC</a></li>
					<li><a href="#debug">关于debug</a></li>
					<li><a href="#en">控制器模型增强</a></li>
					<li><a href="#enc">增强的控制器</a></li>
					<li><a href="#enm">S加载和M加载</a></li>
					<li><a href="#cache">缓存</a></li>
					<li><a href="#redirect">任务转向</a></li>
					<li><a href="#async">异步</a></li>
					<li><a href="#cli">CLI模式</a></li>
					<li><a href="#request">Request请求</a></li>
					<li><a href="#validate">Validate静态类</a></li>
					<li><a href="#session">session处理</a></li>
					<li><a href="#attention">注意</a></li>
				</ul>
			</nav>
		</aside>
	</header>
    <div id='wrapper'>
        
        <section>
        	<h1 class="danger" id="begin">开始使用</h1>
        	<p class="info">快捷的MVC开发框架,媲美原生的使用方式</p>
        	<blockquote>
        		<ul>
        			<li>代码简洁加载极速,核心代码不足2000行</li>
        			<li>单入口启动,不依赖pathinfo,入口文件即是配置文件,一看就懂,配置无忧</li>
        			<li>文件夹随意移动,轻松多项目共享核心,共享类库</li>
        			<li>PDO模式,MySql/Sqlite多数据库支持</li>
        			<li>文件缓存/HTTP缓存就是这么简单易用</li>
        			<li>异常捕获,Debug日志,自定义异常路由,自定义错误页一应俱全</li>
        			<li>普通路由,正则路由,百变URI随心所欲</li>
        			<li>文件加载自动完成,简洁的加载方式,简易高效</li>
        		</ul>
        	</blockquote>
        	<p>以上还都是单个核心文件完成的功能哦,更多辅助功能见下面类库介绍</p>
        	<p class="info">安装配置</p>
        	<blockquote>
        		<ol>
        			<li>框架只需三个文件,即可运行:入口文件,核心文件,控制器文件</li>
        			<li>index.php为入口文件即配置文件,core.php为框架核心文件,外加一个处理请求的控制器文件</li>
        			<li>框架核心仅需PHP5.2+环境即可运行,但是部分自带类库需PHP5.3+版本,推荐PHP5.5+环境</li>
        			<li>PHP须支持PDO,index.php中可配置数据库,GZIP,SMTP,正则路由等参数</li>
        			<li>可以配置文件夹迁移,共享等,可以加载自定义配置文件,可以自动装载类库等</li>
        			<li>需要URL REWRITE支持,否则需要添加index.php/</li>
        		</ol>
        	</blockquote>
        	<p class="info">rewrite 即为一般的index.php rewrite写法</p>
        	<p>对于nginx类型为</p>
        	<blockquote>
        		<p>
        			if (-f $request_filename) { <br>
				           break;<br>
				   }<br>
				    if ($request_filename ~* "\.(js|ico|gif|jpe?g|bmp|png|css)$") { <br>
				       break; <br>
				   } <br>
				   if (!-e $request_filename) { <br>
				       rewrite . /index.php last; <br>
				   }<br>
        		</p>
        	</blockquote>
        	<p class="info">对于apache为</p>
        	<blockquote>
        		<p>
        			RewriteEngine On <br>
					RewriteCond %{REQUEST_FILENAME} !-f <br>
					RewriteCond %{REQUEST_FILENAME} !-d <br>
					RewriteRule ^(.*)$ index.php [QSA,L] <br>
        		</p>
        	</blockquote>	
        	<p class="warning">
        		提示:如果正则路由中需要匹配js,css等类型的url,需要在nginx的rewrite中排除这些文件类型,否则这些请求直接被nginx处理了.
        	</p>
        </section>
        <section>
        	<h1 class="danger" id="smvc">SMVC</h1>
        	<p>S() 即可加载app/system 路径下的文件,或者下一级目录的文件 ,可加载普通php文件,也可加载.class.php文件,后者必须存在以文件名命名的类</p>
        	<p>M() 可加载app/model 目录下的文件,该文件必须是以文件名命名的类文件,作为模型</p>
        	<p>V() 为加载视图,目录为app/view 下,一个方法内只能使用一次,如需多次使用,可用template替换</p>
        	<p>C() 为缓存控制函数,可控制文件缓存和HTTP缓存,下面会详细介绍</p>
        	<p>函数都全局可用,也可用在系统类库和第三方类库中</p>
        	<p>加载不存在的文件,会抛出异常</p>
        </section>
        <section>
        	<h1 class="danger" id="debug">关于debug</h1>
        	<p>开启参数debug,即可启用debug模式 </p>
        	<p>DEBUG三个等级0,1,2</p>
        	<p>0不自动记录错误日志,非敏感模式,不显示错误详情,建议上线后稳定时使用</p>
        	<p>1自动记录错误日志,非敏感模式,不显示错误详情,建议测试时或线上DEBUG使用</p>
        	<p>2自动记录错误日志,敏感模式,显示错误详情,开发时使用</p>
        	<p>敏感模式,未声明即使用变量的notice也会捕获,不建议在生产环境使用</p>
        	<p>定义了异常路由,错误消息会传递到异常路由的第一个参数</p>
        	<p>异常路由显不显示错误消息,自由决定,但是异常路由的错误消息没有跟踪信息</p>
        	<p>开发环境最好不要定义异常路由,以便查看debug输出的跟踪信息</p>
        	<p>若定义异常路由,请确保该路由确实存在,也可以继承base获得,也可以自定义或者重写继承过来的</p>
        </section>
		<section>
			<h1 class="danger" id="en">控制器模型增强</h1>
			<p>虽然系统仅需三个文件便可运行</p>
			<p>但是任然建议使用系统自带的增强工具</p>
			<p>编写自己的模型时可以直接继承db类,db类存在于core.php中,是基本的数据库操作方式</p>
			<p>但是建议你继承database类,该类存放于app/model文件夹内,可自由改写</p>
			<p>是的,你不需提前include它,应为一起都已自动完成</p>
			<p>继承database类不但可以获取db类的所有功能,并且还额外拥有了database类带来的强大用法</p>
			<p>同理,编写自已的控制器时可以继承base控制器,以获得增强的过滤功能</p>
			<p>是的,你可能注意到模型类没有添加构造函数,别急,加载模型时就连接数据库还为时尚早</p>
			<p>模型类可以不做任何操作仅继承db或database类就好了</p>
			<p>需要连接数据库时,都会自动完成</p>
			<p>同理,控制器类可以简单粗暴的不继承任何类</p>
			<p>但是他也可以随意的继承,控制器可以继承其他控制器也可以继承模型类</p>
			<p>模型类可以继承其他模型,也可以继承控制器类</p>
			<p>但是这都仅限于继承一级目录内的控制器和模型,继承其他目录文件需要自首先引入</p>
			<p class="danger">注意:模型可以随意继承,但不要丢失与db或database的联系</p>
		</section>
		<section>
			<h1 class="danger" id="enc">增强的控制器</h1>
			<p>继承base控制器可获得增强的过滤和其他辅助功能</p>
			<p>可以再base里的构造函数内添加全局的自动过滤</p>
			<p>也可以在单独的控制器类进行使用过滤</p>
			<p>base控制器已设定不能通过路由访问,只做继承用途</p>
			<p>可根据IP过滤,refer过滤,post,get,参数过滤请求等</p>
			<p>详细使用,不一一叙述</p>
		</section>
		<section>
			<h1 class="danger" id="enm">增强的模型</h1>
			<p>继承database可获得增强的数据库操作方式</p>
			<p>可以使用数据库缓存,可以使用memcache方式,redis方式,并且带自动缓存系统,可选择使用缓存数据或不经过缓存</p>
			<p>返璞归真,原生的类库使用方式</p>
			<p>详细用法,见代码自懂</p>
		</section>
		<section>
			<h1 class="danger" id="sm">S加载和M加载</h1>
			<p>S加载的基路径为app/sysyem/</p>
			<p>S若加载的是类,可以直接使用 <code>S('类名')->方法</code> 也可以<code>$a=S('类名');$a->方法</code></p>
			<p>当然前提是类和方法都存在</p>
			<p>加载普通的php文件,可以直接<code>S('路径')</code></p>
			<p>多次使用同一个类,多次<code>S('类名')</code>有问题吗?</p>
			<p>放心,系统已记住了该类是否已经加载,重复使用该方法,系统会放回上次加载的类</p>
			<p>对于普通文件也是只加载一次,如是,同一个类只会实例化一次</p>
			<p class="warning">注意:因为只会实例化一次,故实例化时的参数<code>S('类名','参数')</code>只有第一次有效</p>
			<p>同理,对于<code>M('模型')</code>加载也是,多次加载只会实例化一次模型</p>
			<p>完全避免了资源的浪费</p>
			<p>为什么V在一个方法内只需用一次</p>
			<p>V方法实现一个路由的缓存和GZIP任务,同时加载模板渲染,传送数据,结束计时</p>
			<p>V大多实现的任务即为终结任务,不可二次使用</p>
			<p>当然,系统提供了template方法可供使用</p>
			<p>建议使用V加载视图,在视图内部用template加载其他视图文件</p>
			<p>template可随意使用,但是不处理缓存,不处理计时功能</p>
		</section>
		<section>
			<h1 class="danger" id="regex">使用正则路由</h1>
			<p>在index.php入口文件内可以添加正则路由,形式为</p>
			<p>使用正则路由,请在配置文件(入口文件index.php)内启用正则路由</p>
			<p>app::route('\/post\/(\d+)\.html',array('home','post'));</p>
			<p>如此将会捕获例如 /post/123.html 形式的url,取得分组内的数据 `123` ,然后将此数据当做 home控制器post方法的第一个参数,并执行</p>
			<p>如果你的PHP版本在PHP5.3+ 可以使用闭包形式,如</p>
			<p>
							app::route('\/about\/?',function(){
					<br>	&nbsp;&nbsp;	   echo "about us";
					<br>	&nbsp;&nbsp;	// 或者 V('about');
					<br>	&nbsp;&nbsp;	// 或者 V('about', M('m_page')->getAbout(), 10);
					<br> 		});
			</p>

		</section>
		<section>
			<h1 class="danger" id="sfs">Single File Site 模式 </h1>
			<p>可能你不想要将代码如此分散,不想使用众多控制器与模型,只想简简单单</p>
			<p>可能你有代码洁癖,想要随意切换网站,随时增删模块</p>
			<p>那么 <code>Single File Site</code>模式来帮你</p>
			<p>Single File Site 模式,可以将网站所有功能写成一个文件,采用正则路由形式注入</p>
			<p>使用也极其方便,在app/system/目录下创建site目录,存放所有Single File Site模式的网站 </p>
			<p>在site目录下新建site.class.php ,并编写site类和方法,并在构造函数中添加正则路由,然后使用闭包形式</p>
			<p>只需在index.php中加载此文件就OK啦</p>
			<p>详情见代码 site.class.php</p>

		</section>
		<section>
			<h1 class="danger" id="cache">缓存</h1>
			<p>有两种方式使用缓存</p>
			<p><code>C(60)</code>代表使用http缓存60分钟</p>
			<p><code>C(60,true)</code>代表使用文件缓存60分钟</p>
			<p><code>C()</code>方法使用的前后有影响吗?</p>
			<p>对于使用htpp缓存的,略有影响,<code>C(60)</code>既是http缓存的发起者也是http缓存的捕获者</p>
			<p>在方法内越早使用,就越早进行捕获,进而命中缓存,改变原有执行线路</p>
			<p>若是较晚使用<code>C(60)</code>命中缓存时已经做了大量逻辑,造成资源浪费</p>
			<p>对于文件缓存方式<code>C(60,true)</code>会下达缓存任务,任务有<code>V()</code>执行</p>
			<p>文件缓存的检测在实例化控制器之前,所以对文件缓存的影响不大</p>
			<p>因此,无论如何,建议将C()代码放在所有逻辑处理之前,以获得最佳缓存体验</p>
			<p>但是,无论C在何位置,都必须在V之前执行</p>
			<p>并且http缓存可以在没有V的情况下使用,而文件缓存必须在有视图加载的情况下使用</p>
			<p class="info">另外:方法V()也可以直接调用文件缓存,第三个参数为使用文件的失效时间<br>若填写则启用文件缓存,否则不启用</p>
			<p class="success">如下,表示从session中取出用户机构ID,若没有此session值,则默认为1,然后从m_user模型中按机构ID取出用户列表,交由userlist视图渲染,并文件缓存10分钟</p>
			<div class="alert success">
				<p>
					<code class='danger'>V('userlist',M('m_user')->userListByFid(session_get('FACILITY_ID',1)),10);</code>
				</p>
			</div>
				
			
		</section>
		<section>
			<h1 class="danger" id="redirect">任务转向</h1>
			<p>使用<code>app:run(Array)</code>即可内部转到其他控制器里的方法执行,而不带来URL上的变化</p>
			<p>使用<code>app::run('方法名')</code>即转到当前控制器的方法内执行</p>
			<p>这样,相当于使用<code>$this->方法名</code>但是,不能执行私有方法</p>
			<p><code>app::run()</code>可以返回来自其他控制器方法内返回的数据</p>
			<p><code>app::run()</code>可以多次使用,与重定向完全不同,其后的代码仍会正常执行</p>
			<p>如此,可用来权限检测,例如未登录的用户转到登陆的控制器,已登录则执行另一个逻辑</p>
			<p>此方式,可带来奇妙的url变化</p>
			<p>若要实现http重定向,则采用redirect($url,$delay=null,$code=301)</p>
			<p>参数二为延时,参数三为永久重定向或临时</p>
		</section>
		<section>
			<h1 class="danger" id="async">异步</h1>
			<p>这是一种伪异步方式</p>
			<p>使用<code>app::async()</code>即可,参数可以是数组或者一个完整URL</p>
			<p>参数为数组是异步执行系统中的一个控制器中的方法</p>
			<p>参数为URl是异步触发此URL</p>
			<p>此方式只是在极短的时间内触发一个内部或者外部URL</p>
			<p>不能得到任何返回数据</p>
			<p>参数二可以强制使用CURL方式</p>
			<p>参数三为如果运行环境支持,放弃与浏览器的连接</p>
			<p>即<code>fastcgi_finish_request</code>,此函数仅当PHP运行在FastCGI模式时可用</p>
			<p>此函数执行即把所有数据发送到浏览器并断开,以后的执行与浏览器无关也不会输出到浏览器</p>
		</section>
		<section>
			<h1 class="danger" id="cli">CLI模式</h1>
			<p>系统支持CLI模式,无需任何操作便可运行于CLI模式下</p>
			<p>CLI模式直接执行<code>php index.php 控制器 方法 (参数1,参数2...)</code>即可</p>
			<p>注意:CLI模式下没有默认的方法,需在index.php后输入 控制器 方法</p>
		</section>
		<section>
			<h1 class="danger" id="request">Request请求处理类</h1>
			<p>静态类Request可以获得各种系统环境数据和超全局变量数据</p>
			<p>Request::post() 获取所有post数据,键值对数组返回</p>
			<p>Request::post('username',$default) 获取$_POST['username']值若没有,则返回$default</p>
			<p>Request::get() 获取所有get数据</p>
			<p>Request::get('id',$default) 获取$_GET['id']值若没有,则返回$default</p>
			<p>Request::session() 获取所有session值,无需关心session_start</p>
			<p>Request::cookie() 获取所有cookie值</p>
			<p>Request::server() 获取所有$_SERVER值</p>
			<p>Request::info($key) 获取用户ip,是否为ajax,用户的ua,refer,或其中之一值</p>
			<p>Request::serverInfo($key) 返回服务器IP,操作系统,php运行方式,最大上传文件,最大执行时间等</p>
			<p>Request::input($key,$default) 返回使用json格式传递来的$key值,若没有该值,返回$default </p>
			<p>Request::input() 返回所有使用json格式传递的数据即file_get_contents('php://input'),以数组形式返回</p>
			<p>Request::input($key,$default,false) 返回以a=b&c=d 形式传递来的http正文数据里的$key值,没有则返回$default</p>
			<p>Request::isAjax() 返回是否是ajax请求</p>
			<p>Request::isCli() 返回是否是运行于CLI模式</p>
			<p>Request::isPjax() 是否是pjax请求,及判断$_SERVER是否存在HTTP_X_PJAX</p>

		</section>
		<section>
			<h1 class="danger" id="validate">Validate数据验证类</h1>
			<p>提供对数据的基本验证</p>
			<blockquote>
				<p class="success">
					数据验证规则分三部分 <br>
					第一部分为类型: <code class='info'>email</code> <code class='info'>phone</code> <code class='info'>url</code> <code class='info'>username</code> <code class='info'>password</code>  <code class="info">idcard</code><br>
					第二部分为可变参数 <code class='danger'>min-length=6</code> <code class='danger'>max-length=20</code> <code class='danger'>eq=123456</code><br>
					第三部分为正则验证 可以添加自定义正则验证
				</p>
				<p>如: <code>Validate::addRule('text','输入的内容不合法','/^\w+$/')</code></p>
				<p class="danger">注意正则规则以/开头,以/结尾,中间不要含有|,否则会识别为多个规则了</p>
				<ul>
					<li>只需简单两步 <code>Validate::addRule('inputurl','不正确的网址格式|网址最小8位','url|min-length=8')</code>
					再然后<code>$ret=Validate::check($data)</code>
					</li>
					<li>验证的结果即存放在$ret中,$ret是一个数组,全部验证通过$ret['code']=0 <br>
					否则$ret返回错误代号$ret['code']非0和错误消息$ret['msg']</li>
					<li>多个错误消息用|隔开,多个验证规则用|隔开,并且错误消息和规则一一对应</li>
					<li>所有参数都会自动进行存在性检测,所有不用添加require规则,也没有该规则</li>
					<li>可以省略错误消息,也可以省略规则,这样仅执行存在性检测<br>或者仅省略规则,这样默认的存在性错误消息,将会被自定义的错误消息取代</li>
					<li>如: <code>Validate::addRule('name','用户名必须存在')</code> ,不填写第二个参数,验证不通过时默认会返回 '字段name必须存在'	</li>
					<li>多个规则也想要自定义必须性检测的错误消息怎么办?</li>
					<li>这样,将错误消息的个数比规则个数多出一个,这样必须性检测不通过是会返回第一个错误消息</li>
					<li>如: <code>Validate::addRule('inputemail','邮箱必须填写|邮箱格式不正确','email')</code></li>

				</ul>
			</blockquote>
			<p class="info">Request和Validate结合可以大大简化表单操作</p>
			<div class="alert success">
				<p class="danger"> Validate::addRule('name','用户名必须填写'); </p>
				<p class="danger"> Validate::addRule('email','邮箱必须填写|邮箱格式不正确','email'); </p>
				<p class="danger"> Validate::addRule('pass','密码必须填写'); </p>
				<p class="danger"> $info=Request::post();</p>
				<p class="danger"> $ret=Validate::check($info); </p>
				<p class="danger"> if($ret['code']!=0)exit(json_encode($ret)); //验证不通过</p>
				<p class="danger"> $userid=M('m_user')->addNewUser($info);</p>
			</div>
		</section>
		<section>
			<h1 class="danger" id="session">session处理</h1>
			<p class="info">系统封装的session函数,对于session的处理很有帮助</p>
			<blockquote>
				<p><code>session_set($key,$value)</code> 设置session,$value可以为array,这样会自动进行json_encode操作,
				但是获取时需要自己进行json_decode<br>
				同时$key也可以为键值对这样可以批量设置session,批量设置中也会检测value是否为array,若是则json_encode操作</p>
				<p><code>session_get($key,$default)</code>获取session, $key可以为array,若是则批量获取session以数组形式返回<br>
				$default为没有设置该session时的默认值,默认为null</p>
				<p><code>session_del($key)</code>删除一个session,若$key为null或不传递参数,则执行session_destroy操作<br>
				$key可以为数组,则执行批量删除操作</p>
				<p>从此再也不需要考虑<code>session_start</code>了,所有函数直接使用,自动检测session_start</p>	
			</blockquote>
			
		</section>
		<section>
			<h1 class="danger" id="attention">注意</h1>
			<blockquote>
			<ol>
				<li>在继承一个类时,如果同时存在以此命名的控制器和模型,则会继承模型,建议控制器和模型不要有重名</li>
				<li>对于私有页面如要缓存的话,请使用http缓存,不要有文件缓存,因为文件缓存最优先,在控制器加载之前,可能就会命中缓存并输出,造成安全隐患</li>
				<li>谨慎对启用正则路由的页面使用文件缓存,缓存文件是根据控制器-方法-参数一-参数二..验证的</li>
				<li><code>app::async</code>异步触发一个路由,或者无阻塞触发外界网址,个别服务器会失效,请使用app::async($router,1),采用curl方式触发</li>
				<li>框架近乎原生的使用方式,最大的自由开发使用,随意的继承转向,但是自己需拥有良好的开发准则</li>
				<li></li>
			</ol>
			</blockquote>
	
		</section>
		<p class="danger">以下为类库扩展使用帮助</p>
		<section>
			<h1 class="info">Curl类</h1>
			<blockquote>

				<p><code>S('class/curl')</code>即可加载,主要可用于简单的采集和url触发</p>
				<p>例:S('class/curl')->fetch('img',$url) 获取$url下的所有完整图片全地址</p>
				<p>例:S('class/curl')->fetch('src',$url) 获取$url下的所有img src的地址</p>
				<p>例:S('class/curl')->fetch('href',$url) 获取$url下的所有a href 的地址</p>
				<p>例:S('class/curl')->fetch('url',$url) 获取$url下的所有符合 http://...的全网址 地址</p>
				<p>当然,也可以添加多个url ,将$url改为一个数组值即可,程序会自动判断,取得其中多有网址内容</p>
				<p>CURl还有异步触发功能,S('class/curl')->quickExec($url); $url为单个地址或数组包含多个地址,此时会多线程异步触发此地址</p>
				<p>还可以用CURl传送POST数据,发送文件等</p>

				
			</blockquote>	
		</section>

		<section>
			<h1 class="info">KVDB类</h1>
			<blockquote>
				<p><code>$db=S('class/kvdb')</code>即可加载,可用于存储少量的结构化数据</p>
				<p>简易的API,如 $db->set('a',$b); $b=$db->get('a'); $b也可以是数组,这样取出来的也是数组</p>
				<p>$db->mset($arr)批量设置, $db->mget($arr) 批量获取 $db->del($key) 删除 $db->mdel($arr) 批量删除</p>
				<p>$db->flush() 清空所有</p>
				<p>$db->gets($key) 以$key开头的, 为空则返回所有</p>
				<p>$db->like($key) 包含有$key的</p>
				<p>S('class/kvdb',$param) 参数二可以指定KVDB文件,省略则加载默认的,tmp则使用临时文件,其他则创建KVDB文件</p>
				<p>$db->select($db) 可以切换到其他DB文件上</p>
			</blockquote>

		</section>

		<section>
			<h1 class="info">Uploader类</h1>
			<blockquote>
				<p><code>$up=S('class/uploader',$cfg)</code>即可加载,用于上传文件到七牛,酷盘,SAE,上传到本地,或其他服务器</p>
				<p>$cfg参数可以设定参数和密匙等所有数据替代类库里的默认值</p>
				<p>$up->upload($name,$fileName) 监视表单名为$name的文件上传表单,符合设定要求则上传到本地,并返回相关上传数据,$fileName可以强制存储时的文件名</p>
				<p>$up->uploadSae($name) 上传文件到SAE,当然sae服务端也有操作存储文件并返回相关信息</p>
				<p>$up->uploadQiniu($name) 上传到七牛,必须设定正确的密匙</p>
				<p>$up->uploadTietu($name) 上传到贴出库,必须设定正确的密匙</p>
				<p>$up->uploadKuaipan($name) 发送到快盘, 也是需要开发者密匙(密匙不断变化,需自己维护,见kuaipan.class.php)</p>
				<p>$up->uploadUrl() 发送到其他地址</p>
				<p>$up->sendToSae() 发送指定文件到SAE</p>
				<p>$up->sendToQiniu() 发送指定文件到七牛</p>
				<p>$up->sendToTietu() 发送指定文件到贴图库</p>
				<p>$up->sendToKuaipan() 发送指定文件到快盘</p>
			</blockquote>
		</section>

		<section>
			<h1 class="info">Cache 类</h1>
			<p><code>$cache=S('class/cache',$type)</code>即可加载,可以使用memcache,redis,file等缓存</p>
			<p>对于不同的缓存类型封装了一致的操作接口</p>
			<p>$cache->set()</p>
			<p>$cache->get()</p>
			<p>$cache->select()</p>
			<p>$cache->mset()</p>
			<p>$cache->mget()</p>
			<p>$cache->del()</p>
			<p>$cache->mdel()</p>
			<p>$cache->incr()</p>
			<p>$cache->desc()</p>
			<p>$cache->incrby()</p>
			<p>$cache->descby()</p>
			<p>$cache->flush()</p>
		</section>

		<section>
			<h1 class="info">Kuaipan类</h1>
		</section>
		
		<section>
			<h1 class="info">KuwoMusic类</h1>
		</section>
		
		<section>
			<h1 class="info">SaeStorage类</h1>
		</section>

		<section>
			<h1 class="info">Image类</h1>
		</section>

		<section>
			<h1 class="info">Backup类</h1>
		</section>

		<section>
			<h1 class="info">QqRoubot类</h1>
		</section>

		<section>
			<h1 class="info">Layout类</h1>
		</section>
		
	<div style="height:600px;"></div>
    </div>
    <footer>
    	
    </footer>
<script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
<script>
	

</script>
</body>
</html>