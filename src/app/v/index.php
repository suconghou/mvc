<!doctype html> 
<html> 
<head> 
    <meta charset="utf-8"> 
    <title></title> 
    <style>
    body{font: 16px Monaco,Bitstream Vera Sans Mono, Microsoft YaHei, Arial, sans-serif;} 
    #wrapper{width: 80%; margin: 2% auto; box-shadow: 0 0 4px #999; line-height:30px; padding: 4%; } 
    ::-webkit-scrollbar-track-piece {width:6px; background-color: #fdfdfd; } 
    ::-webkit-scrollbar-thumb {height: 50px; background-color: rgba(0,0,0,.7); 
    -webkit-border-radius: 2px; } ::-webkit-scrollbar {width:6px; height: 6px; } 
    ::selection {background: #FFF200; text-shadow: none; } 
    </style> </head> <body>
    <div id='wrapper'>
        <h2>便捷快速的MVC开发框架</h2><span>update 20140407</span>
        <ul>
        	<li>只有两个核心文件入口文件index.php核心文件core.php,快速便捷,适用于各种项目</li>
        	<li>20140325修复apache和nginx状态码差异</li>
            <li>完善了正则路由,新的路由机制已经支持php5.2</li>
            <li>完善了默认控制器,以及路径包含问题</li>
            <li>完善了GET,POST的内容过滤</li>
            <li>更新http缓存，http缓存可以不依靠加载view</li>
            <li>更新run方法,run方法,能够返回数据了</li>
            <li>更新run方法,解决了不能多次执行的bug</li>
        </ul>
        <h3>系统特色</h3>
        <ul>
        	<li>1.单入口启动,不依赖pathinfo</li>
        	<li>2.普通路由分析,正则路由分析,给你随心所欲的URI</li>
        	<li>3.文件缓存,HTTP请求缓存,想用哪个用哪个</li>
        	<li>4.SQLITE/MYSQL支持,简易高效的数据库层</li>
        	<li>5.异常捕获,DEBUG日志,堆栈分析,自定义错误页一应俱全</li>
        	<li>6.简易的加载方式,WRITE LESS,DO MORE!</li>
        </ul>
        <h3>core.php 文件分析</h3>
        <ol>
			<li>regex_router 正则路由分析器</li>
			<li>common_router 普通路由分析器</li>
			<li>show_errorpage 异常捕获</li>
			<li>route 添加正则路由</li>
			<li>process 流程导航器</li>
			<li>run 内部重定向</li>
			<li>log_message 记录错误日志</li>
			<li>M model加载器</li>
			<li>S lib加载器</li>
			<li>V view加载器</li>
			<li>C 缓存处理器</li>
            <li>http_response_code nginx自定义状态码</li>
            <li>__autoload 自动装载器</li>
            <li>template 模板加载器</li>
            <li>userInfo 来访信息</li>
            <li>POST POST消息过滤器</li>
            <li>GET GET消息过滤器</li>
            <li>COOKIE COOKIE消息过滤器</li>
            <li>SERVER SERVER消息过滤器</li>
			<li>byte_format 字节格式化</li>
			<li>redirect 外部重定向</li>
            <li>base_url 路径组合器</li>
            <li>async 异步执行器</li>
			<li>sendmail 发送邮件</li>
			<li>class model 数据库层</li>
			
        </ol>
   
        <h3>说明</h3>
        <ul>
        	<li>对于私有页面如要缓存的话,请使用http缓存,不要有文件缓存,因为文件缓存最优先,在控制器加载之前,可能就会命中缓存并输出</li>
            <li>常量APP_TIME_SPEND保存着本次执行的消耗时间</li>
        	<li>常量APP_MEMORY_SPEND保存着本次执行的消耗内存</li>
            <li>变量APP存储着系统必须的全局变量</li>
            <li>S()方法可以加载.class.php的类库文件,并自动实例化然后返回,参数一为文件名,参数二可选,如果为false,则加载普通php文件,可用来加载函数库</li>
        	<li>控制器无需继承任何类,就可以直接使用,但是model至少要继承model父类,因为要连接数据库啊</li>
            <li>使用run()方法可以内部重定向,即移交到其他控制器处理,或者获取其他控制器的返回值,这比继承其他控制器更方便,也类似于多继承</li>
            <li>当然你也可以在控制器中继承其他控制器,在model中继承其他model,甚至在控制器中继承model,在model中继承控制器,最关键的是你无须提前加载它,需要的时候一切都会自动加载!</li>
            <li>最最最重要的是控制器类,model类,他们不能重名,控制器如果继承了这个model,就不要用M()方法加载这个model</li>    
            <li>不要对启用正则路由的页面使用文件缓存,缓存文件是根据控制器-方法-参数一-参数二..验证的.正则路由方法的参数不固定这将会产生大量缓存文件</li>
            <li>对于app/s目录下的文件,你可以直接include,不用加路径哦</li>
            <li>在控制器中,不要多次使用V方法,建议其他试图在view里调用template()</li>
            <li>base_url(),当参数为数字,则返回路由信息,0为控制器,1为方法,以此类推,否则返回组合路径</li>
            <li>async 异步触发一个路由,或者无阻塞触发外界网址,个别服务器会失效,请使用async($router,1),采用curl方式触发</li>
            <li>开启DEBUG,会记录错误日志,否则不记录;自定义错误页,始终不会输出错误信息;没有自定义错误页,若开启debug,会输出详细错误信息并记录,不开启debug,只提示发生某种错误,不记录日志</li>
        </ul>
        <h3>开发建议</h3>
        <ul>
            <li>程序仅需index.php core.php config.php 便可运行,但是建议你为自己的项目组建更适宜的环境.</li>
            <li>应在s中创建app_config.php 和functions.php</li>
            <li>在m中创建 base_model ,系统并未包装复杂的数据库操作,就是希望自己去包装更适宜的基础model</li>
            <li>在c中创建base_controller,提供一些基础的操作,以便可以使用run方法调用他</li>
        </ul>
       
        <p align="center">执行时间 <?=APP_TIME_SPEND?>秒</p>
        <p align="center">消耗内存 <?=APP_MEMORY_SPEND?></p>
    </div>

</body>
</html>