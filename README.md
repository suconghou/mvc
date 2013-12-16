
<html>
<head>
	<h1>最简单实用的MVC框架</h1>
</head>
<body>
<h2>框架特色</h2>
<ul>
<li>框架小巧,目录简洁,整个框架大小不足100Kb.</li>
<li>高兼容性,采用<code>pathinfo</code>模式,伪静态效果,你还可以用rewrite去除index</li>
<li>功能完备,URI路由,缓存静态化,数据库类,email类,一应俱全</li>
<li>高可靠性,GET,POST自动全局过滤,XSS,SQL注入,一键过滤</li>
</ul>

<h2>分层简介</h2>
<b>控制器层</b>
<ul>
<li>$this->view($file) 加载view,$file文件都不带后缀.php</li>
<li>$this->model($file) 加载model,model加载后会自动实例化,你可以直接使用$this->model名称->model里的方法名</li>
<li>$this->load($file) 加载类库,类库存放于S,同$this->model(),也会自动实例化</li>
<li>$this->uri($segment) 返回URI指定的段,$segment为a时返回当前的controller,为a返回当前的action,为0返回参数一,为1返回参数二,以此类推</li>
<li>$this->cache($min) 为视图缓存$min分钟,可在action中随时调用</li>
</ul>

<b>数据库层</b>
<ul>

<li>$this->query()->fetch()</li>
<li>$this->query()->fetchall()获取所有结果集</li>
<li>$this->fetch() 获取一行</li>
<li>$this-></li>


</ul>

<h2>全局函数</h2>
<ul>
<li>base_url($filepath) 自动计算出文件的绝对地址并返回</li>
<li>redirect($url,$seconds=0) 延时$seconds后重定向,$seconds不指定则立即重定向</li>
<li>sendmail($to,$subject,$html) 使用smtp发送邮件,需在config中配置smtp信息</li>
<li>sendsms($to,$msg),使用飞信方式发送短信,要在config中配置手机号和飞信密码,可以发给自己活自己好友,发送多人,可以用,号隔开</li>
</ul>
</body>
</html>