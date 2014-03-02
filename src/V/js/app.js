// 为核心的phonecat 服务创建模块  

var pservice = angular.module('phonecat', ['ngRoute']);  
// 在URL、模板和控制器之间建立映射关系  
function RouteConfig($routeProvider) {  
$routeProvider.  
when('/', {  
controller: ListController,  
templateUrl: 'tpl/index'  
}).  
// 注意，为了创建详情视图，我们在id 前面加了一个冒号，从而指定了一个参数化的URL 组件  
when('/post/:id', {  
controller: DetailController,  
templateUrl: 'tpl/detail/'
}).
when('/addpost/newpost', {  
controller: AddController,  
templateUrl: 'tpl/newpost/'
}).
when('/user/login', {  
controller: LoginController,  
templateUrl: 'tpl/login/'
}).

otherwise({  
redirectTo: '/'  
});  
}  

// 配置我们的路由，以便AMail 服务能够找到它  
pservice.config(RouteConfig);