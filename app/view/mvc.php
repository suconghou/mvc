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
					<li><a href="">开始使用</a></li>
					<li><a href="">SMVC</a></li>
					<li><a href="">关于debug</a></li>
					<li><a href="">控制器模型增强</a></li>
					<li><a href="">增强的控制器</a></li>
					<li><a href="">S加载和M加载</a></li>
					<li><a href="">缓存</a></li>
					<li><a href="">任务转向</a></li>
					<li><a href="">异步</a></li>
					<li><a href="">CLI模式</a></li>
					<li><a href="">Request请求</a></li>
					<li><a href="">Validate静态类</a></li>
					<li><a href="">session处理</a></li>
					<li><a href="">注意</a></li>
				</ul>
			</nav>
		</aside>
	</header>
    <div id='wrapper'>
        
        <section>
        	<h1>开始使用</h1>
        	<p>快捷的MVC开发框架</p>
        	<blockquote>
        		<ul>
        			<li>代码简洁加载极速,核心代码不足2000行</li>
        			<li>单入口启动,不依赖pathinfo,入口文件即是配置文件,一看就懂,配置无忧</li>
        			<li>文件夹随意移动,轻松多项目共享</li>
        			<li>MYSQL/SQLITE双数据库支持</li>
        			<li>文件缓存/HTTP缓存就是这么简单</li>
        			<li>异常捕获,DEBUG日志,自定义错误页,自定义异常路由一应俱全</li>
        			<li>普通路由,正则路由,百变URI随心所欲</li>
        			<li>文件加载自动完成,简洁的加载方式,简易高效</li>
        		</ul>
        	</blockquote>
        	<p>安装配置</p>
        	<blockquote>
        		<ol>
        			<li>框架只需三个文件,即可运行.</li>
        			<li>index.php入口文件即配置文件,core.php框架核心,外加一个处理请求的控制器文件</li>
        			<li>框架仅需PHP5.2+环境即可运行,但是部分自带类库需PHP5.3+版本</li>
        			<li>支持Mysql和Sqlite,但PHP须支持PDO</li>
        			<li>定义配置文件的程序路径(一般不需改变)和其他参数,例如SMTP,数据库,即可完美使用</li>
        			<li>需要URL REWRITE支持</li>
        		</ol>
        	</blockquote>
        	<p>rewrite 即为一般的index.php rewrite写法</p>
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
        	<p>对于apache类型为</p>
        	<blockquote>
        		<p>
        			RewriteEngine On <br>
					RewriteCond %{REQUEST_FILENAME} !-f <br>
					RewriteCond %{REQUEST_FILENAME} !-d <br>
					RewriteRule ^(.*)$ index.php [QSA,L] <br>
        		</p>
        	</blockquote>	
        	<p>
        		提示:如果正则路由中需要匹配js,css等类型的url,需要在nginx的rewrite中排除这些文件类型
        	</p>
        </section>
        <section>
        	<h1>SMVC</h1>
        	<p>S() 即可加载app/system 路径下的文件,或者下一级目录的文件 ,可加载普通php文件,也可加载.class.php文件,后者必须存在以文件名命名的类</p>
        	<p>M() 可加载app/model 目录下的文件,该文件必须是以文件名命名的类文件,作为模型</p>
        	<p>V() 为加载视图,目录为app/view 下,一个方法内只能使用一次,如需多次使用,可用template替换</p>
        	<p>C() 为缓存控制函数,可控制文件缓存和HTTP缓存,下面会详细介绍</p>
        	<p>函数都全局可用,也可用在系统类库和第三方类库中</p>
        	<p>加载不存在的文件,会抛出异常</p>
        </section>
        <section>
        	<h1>关于debug</h1>
        	<p>开启参数debug,即可启用debug模式 </p>
        	<p>debug模式对异常敏感,不建议在生产环境使用</p>
        	<p>开启debug模式即会记录错误日志,但是输不输出错误信息取决于是否定义异常路由</p>
        	<p>定义了异常路由,便不会将错误信息发送到浏览器</p>
        	<p>开发环境最好不要定义异常路由,以便查看debug输出的跟踪信息</p>
        	<p>若定义异常路由,请确保该路由确实存在</p>
        </section>
		<section>
			<h1>控制器模型增强</h1>
			<p>虽然系统仅需三个文件便可运行</p>
			<p>但是任然建议使用系统自带的增强工具</p>
			<p>编写自己的模型时可以直接继承db类,db类存在于core.php中,是基本的数据库操作方式</p>
			<p>但是建议你继承database类,该类存放于app/model文件夹内,可自由改写</p>
			<p>是的,你不需提前include它,应为一起都已自动完成</p>
			<p>继承database类不但可以获取db类的所有功能,并且还额外拥有了database类带来的强大用法</p>
			<p>同理,编写自已的控制器时可以继承base控制器,以获得增强的过滤功能</p>
			<p>是的,你可能注意到模型类没有添加构造函数,别急,加载模型时就连接数据库还为时尚早</p>
			<p>模型类可以不做任何操作仅继承db和database类就好了</p>
			<p>需要连接数据库时,都会自动完成</p>
			<p>同理,控制器类可以简单粗暴的不继承任何类</p>
			<p>但是他也可以随意的继承,控制器可以继承其他控制器也可以继承模型类</p>
			<p>模型类可以继承其他模型,也可以继承控制器类</p>
			<p>但是仅限于继承一级目录内的控制器和模型</p>
			<p>注意:模型可以随意继承,但不要丢失与db或database的联系</p>
		</section>
		<section>
			<h1>增强的控制器</h1>
			<p>继承base控制器可获得增强的过滤和其他辅助功能</p>
			<p>可以再base里的构造函数内添加全局的自动过滤</p>
			<p>也可以在单独的控制器类进行使用过滤</p>
			<p>base控制器已设定不能通过路由访问,只做继承用途</p>
			<p>可根据IP过滤,refer过滤,post,get,参数过滤请求等</p>
			<p>详细使用,不一一叙述</p>
		</section>
		<section>
			<h1>增强的模型</h1>
			<p>继承database可获得增强的数据库操作方式</p>
			<p>可以使用缓存</p>
			<p>详细用法,见代码自懂</p>
		</section>
		<section>
			<h1>S加载和M加载</h1>
			<p>S若加载的是类,可以直接使用 <code>S('类名')->方法</code> 也可以<code>$a=S('类名');$a->方法</code></p>
			<p>当然前提是类和方法都存在</p>
			<p>加载普通的php文件,可以直接<code>S('路径')</code></p>
			<p>多次使用同一个类,多次<code>S('类名')</code>有问题吗</p>
			<p>放心,系统已记住了该类是否已经加载,重复使用该方法,系统会放回上次加载的类</p>
			<p>对于普通文件也是只加载一次,如是,同一个类只会实例化一次</p>
			<p>注意:因为只会实例化一次,故实例化时的参数<code>S('类名','参数')</code>只有第一次有效</p>
			<p>同理,对于<code>M('模型')</code>加载也是,多次加载只会实例化一次模型</p>
			<p>完全避免了资源的浪费</p>
			<p>为什么V在一个方法内只需用一次</p>
			<p>V方法实现一个路由的缓存和GZIP任务,同时加载模板渲染,传送数据,结束计时</p>
			<p>V大多实现的任务即为终结任务,不可二次使用</p>
			<p>当然,系统提供了template方法可供使用</p>
			<p>建议使用V加载视图,在视图内部用template加载其他视图文件</p>
			<p>template可随意使用,但是不处理缓存,没有计时功能</p>
		</section>
		<section>
			<h1>缓存</h1>
			<p>有两种方式使用缓存</p>
			<p><code>C(60)</code>代表使用http缓存60分钟</p>
			<p><code>C(60,true)</code>代表使用文件缓存60分钟</p>
			<p><code>C()</code>方法使用的前后有影响吗</p>
			<p>对于使用htpp缓存的,略有影响,<code>C(60)</code>既是http缓存的发起者也是http缓存的捕获者</p>
			<p>在方法内越早使用,就越早进行捕获,进而命中缓存,改变原有执行线路</p>
			<p>若是较晚使用<code>C(60)</code>命中缓存时已经做了大量逻辑,造成资源浪费</p>
			<p>对于文件缓存方式<code>C(60,true)</code>会下达缓存任务,任务有<code>V()</code>执行</p>
			<p>文件缓存的检测在实例化控制器之前,所以对文件缓存的影响不大</p>
			<p>因此,建议将C()代码放在所有逻辑处理之前,以获得最佳缓存体验</p>
			<p>如上,无论C在何位置,都必须在V之前执行</p>
			<p>并且http缓存可以在没有V的情况下使用,而文件缓存必须在有视图加载的情况下使用</p>
		</section>
		<section>
			<h1>任务转向</h1>
			<p>使用<code>app:run(Array)</code>即可内部转到其他控制器里的方法执行,而不带来URL上的变化</p>
			<p>使用<code>app::run('方法名')</code>即转到当前控制器的方法内执行</p>
			<p>这样,相当于使用<code>$this->方法名</code>但是,不能执行私有方法</p>
			<p><code>app::run()</code>可以返回来自其他控制器方法内返回的数据</p>
			<p><code>app::run()</code>可以多次使用,与重定向完全不同,其后的代码任会正常实行</p>
			<p>如此,可用来权限检测,例如未登录的用户转到登陆的控制器,已登录则执行另一个逻辑</p>
			<p>此方式,可带来奇妙的url变化</p>
		</section>
		<section>
			<h1>异步</h1>
			<p>这是一种伪异步方式</p>
			<p>使用<code>app::async()</code>即可,参数可以是数组或者一个完整URL</p>
			<p>参数为数组是异步执行系统中的一个控制器中的方法</p>
			<p>参数为URl是异步触发此URL</p>
			<p>此方式只是在极短的时间内触发一个内部或者外部URL</p>
			<p>不能得到任何返回数据</p>
			<p>参数二可以强制使用CURL方式</p>
			<p>参数三为如果可以,放弃与浏览器的链接</p>
			<p>即<code>fastcgi_finish_request</code>,此函数仅在FastCGI模式下可用</p>
			<p>函数执行即把所有数据发送到浏览器并断开,以后的执行与浏览器无关也不会输出</p>
		</section>
		<section>
			<h1>CLI模式</h1>
			<p>系统支持CLI模式,开启<code>CLI</code>选项即可使用</p>
			<p>开启CLI并不会丢失原有的所有特性</p>
			<p>只不过系统多走了一段CLI环境的检测</p>
			<p>CLI模式直接执行<code>php index.php 控制器 方法 (参数1,参数2...)</code>即可</p>
			<p>启动CLI运行没有默认控制器和方法,需在index.php后输入 控制器 方法</p>
		</section>
		<section>
			<h1>Request请求</h1>
			<p>静态类Request可以获得各种系统环境数据和超全局变量数据</p>
			<p>Request::post()</p>
			<p>Request::get()</p>
			<p>Request::session()</p>
			<p>Request::cookie()</p>
			<p>Request::server()</p>
			<p>Request::info()</p>

		</section>
		<section>
			<h1>Validate静态类</h1>
			<p>提供对数据的基本验证</p>
		</section>
		<section>
			<h1>session处理</h1>
			<p>系统封装的session函数,对于session的处理很有帮助</p>
			<p>session_set()</p>
			<p>session_get()</p>
			<p>session_del()</p>
			<p>从此再也不需要考虑<code>session_start</code>了</p>
		</section>
		<section>
			<h1>注意</h1>
			<blockquote>
			<ol>
				<li>在继承一个类时,如果同时存在以此命名的控制器和模型,则会继承模型,建议不要有重名</li>
				<li>对于私有页面如要缓存的话,请使用http缓存,不要有文件缓存,因为文件缓存最优先,在控制器加载之前,可能就会命中缓存并输出</li>
				<li>谨慎对启用正则路由的页面使用文件缓存,缓存文件是根据控制器-方法-参数一-参数二..验证的</li>
				<li><code>app::async</code>异步触发一个路由,或者无阻塞触发外界网址,个别服务器会失效,请使用app::async($router,1),采用curl方式触发</li>
				<li></li>
				<li></li>
			</ol>
			</blockquote>
	
		</section>
	<div style="height:600px;"></div>
    </div>
    <footer>
    	
    </footer>
<script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
</body>
</html>