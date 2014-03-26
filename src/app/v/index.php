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
        <h2>便捷快速的MVC开发框架</h2><span>update 20140325</span>
        <ul>
        	<li>只有两个核心文件入口文件index.php核心文件core.php,快速便捷,适用于各种项目</li>
        	<li>20140325修复apache和nginx状态码差异,正则路由有待完善</li>
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
			<li>byte_format 字节格式化</li>
			<li>redirect 外部重定向</li>
			<li>sendmail 发送邮件</li>
			<li>class model 数据库层</li>
			
        </ol>
   
        <h3>说明</h3>
        <ul>
        	<li>对于私有页面如要缓存的话,请使用http缓存,不要有文件缓存</li>
            <li>常量APP_TIME_SPEND保存着本次执行的消耗时间</li>
        	<li>常量APP_MEMORY_SPEND保存着本次执行的消耗内存</li>
            <li>变量APP存储着系统必须的全局变量</li>
            <li>S()方法可以加载.class.php的类库文件,并自动实例化然后返回,参数一为文件名,参数二可选,如果为false,则加载普通php文件,可用来加载函数库</li>
        	<li>控制器无需继承任何类,就可以直接使用,但是model至少要继承model父类,应为要连接数据库啊</li>
            <li>使用run()方法可以内部重定向,即移交到其他控制器处理,或者获取其他控制器的返回值,这比继承其他控制器更方便,也类似于多继承</li>
            <li>当然你也可以在控制器中继承其他控制器,在model中继承其他model,甚至在控制器中继承model,在model中继承控制器,最关键的是你无须提前加载它,需要的时候一切都会自动加载!</li>
            <li>最最最重要的是控制器类,model类,他们不能重名,控制器如果继承了这个model,就不要用M()方法加载这个model</li>    
            <li>不要对启用正则路由的页面使用文件缓存,缓存文件是根据控制器-方法-参数一-参数二..验证的.正则路由方法的参数不固定这将会产生大量缓存文件</li>
        </ul>

        <p align="center">执行时间 <?=APP_TIME_SPEND?>秒</p>
        <p align="center">消耗内存 <?=APP_MEMORY_SPEND?></p>
    </div>

</body>
</html>