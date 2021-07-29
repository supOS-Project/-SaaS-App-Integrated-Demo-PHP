supOS saas3.0 版本的php demo

**===============**

该demo采用ThinkPHP 5.0 框架，采用MVC模式开发。

\> ThinkPHP5的运行环境要求PHP5.4以上。

详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

本demo只是一个简单的OAuth的对接，租户管理的对接。

主要文件说明：
application\index\controller\Base.php    	 #基类，其中包含构造判断是否有通过supOS的OAuth
application\index\controller\Bluetron.php    #实现了supOS租户的订阅消息，有开通租户、续期租户、查询租户状态、停用租户
application\index\controller\Index.php       #业务实现的示例，其中查询了租户下的所有用户列表

application\index\model   					 #数据库关联实体model

application\index\view						 #前端展示

common.php									 #公共的函数
config.php									 #项目配置
database.php								 #数据库配置
route.php									 #路由映射，租户相关的映射在其中有配置
index.php									 #入口文件
