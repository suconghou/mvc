// 把我们的邮件发布给邮件列表模板  
function ListController($scope,$http) {
$http.get('/post/newpost').success(function(data){
	$scope.posts = data;
	$scope.load_hide='hide';  
});  

}  
// 从路由信息（从URL 中解析出来的）中获取邮件id，然后用它找到正确的邮件对象  
function DetailController($scope, $routeParams,$http) {  
var id=$routeParams.id;
$http.get('/post/detail/'+id).success(function(data){
	$scope.postinfo= data;  
	$http.get('/comment/all/'+$scope.postinfo.id).success(function(data){
	$scope.comments=data;
		$scope.load_hide='hide';  
});
}); 
//拉去回复 



//提交回复
$scope.comment=function(){
	$http.post('/comment/add/'+$scope.postinfo.id,{comment:$scope.user_comment}).success(function(data){
		if(data==1)
		{
			DetailController($scope, $routeParams,$http);
			$scope.user_comment='';
		}
		else
		{
			alert(data);
		}
	});
}
///哈哈，为提交回复定义一个快捷键把

$scope.hotkey=function(){
	if (event.keyCode == 13 && event.ctrlKey)
	 {
	 	
	  	if($scope.user_comment)
	  	{

	  		$scope.comment();
	  	}
	 }
}
onkeydown=$scope.hotkey;
}

function AddController($scope,$http)
{
	$scope.addnewpost=function(){
		$http.post('/post/addnewpost',{title:$scope.newpost_title,text:$scope.newpost_text}).success(function(data){
			if(data==1)
			{
					location.href='/#/';
			}
			else
			{
				alert(data);
			}
		});
	}
} 

function LoginController($scope,$http)
{
	$scope.login=function(){
		$http.post('/login/login',{name:$scope.login_name,pass:$scope.login_pass}).success(function(data){
			if(data=='success')
			{
				
				location.href='/#/';
			}
			else
			{
					
				alert('登录失败!请检查你的用户名和密码');
				

			}
		});
	}
	$scope.register=function(){
	
		$http.post('/login/reg',{name:$scope.reg_name,pass:$scope.reg_pass,mail:$scope.reg_mail}).success(function(data){
			if(data=='success')
			{
				$scope.reg_name=null;
				$scope.reg_pass=null;
				$scope.reg_mail=null;
				location.href='/#/';
			}
			else
			{
				alert(data);
			}
		});
	}
}