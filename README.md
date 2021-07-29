supOS saas3.0 版本的php sdk
===============
该sdk采用ThinkPHP 5.0 框架
> ThinkPHP5的运行环境要求PHP5.4以上。
详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

## 目录结构

初始的目录结构如下：

 WEB部署目录（或者子目录）
├─application           应用目录
│  ├─common             公共模块目录（可以更改）
│  ├─module_name        模块目录
│  │  ├─config.php      模块配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  │  ├─Base.php     公共控制器类，主要用于判断蓝卓的Auth认证
│  │  │  ├─Bluetron.php 蓝卓租户和auth回调放在这里
│  │  │  ├─Index.php    业务程序主入口
│  │  ├─model           模型目录
│  │  │  ├─Tenant.php   租户的数据库对应
│  │  │  ├─User.php     用户的对应
│  │  ├─view            视图目录
│  │
│  ├─command.php        命令行工具配置文件
│  ├─common.php         公共函数文件，内有重要函数需要注意查看
│  ├─config.php         公共配置文件
│  ├─route.php          路由配置文件，内有open-api的路由配置注意查看
│  ├─tags.php           应用行为扩展定义文件
│  └─database.php       数据库配置文件
│
├─thinkphp              框架系统目录
│  ├─lang               语言文件目录
│  ├─library            框架类库目录
│  │  ├─think           Think类库包目录
│  │  └─traits          系统Trait目录
│  │
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─console.php        控制台入口文件
│  ├─convention.php     框架惯例配置文件
│  ├─helper.php         助手函数文件
│  ├─phpunit.xml        phpunit配置文件
│  └─start.php          框架入口文件
│
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─build.php             自动生成定义文件（参考）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
├─index.php             入口文件
├─.htaccess             用于apache的重写