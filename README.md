# webniu 网牛引擎
webniu 网牛引擎是一个基于workerman(异步PHP)+layui(html+js+css)开发的中后台管理系统，它有传统框架基本的功能，小巧轻便开箱即用，用户安装后可以通过自定义模块生成器创建自己的应用程序。

**功能内容** 
* 1、模块安装向导。
* 2、系统设置-基本设置-账户设置-附件设置-图片水印-页面样式DIY-接口配置。
* 3、灵活的多级菜单管理。
* 4、附件管理-图片管理-文件管理-视频管理-音频管理，支持远程：本地、FTP、七牛、阿里云、腾讯云存储扩展。
* 5、邮件发送、短信发送。
* 6、后端账户管理、权限管理、独立数据。
* 7、前端会员管理、权限管理，可扩展API。
* 8、应用插件，可快速配置生成打包出zip应用包，支持本地安装、升级、卸载。
* 9、应用权限，可灵活配置应用权限，独立登录。
----
最后一次更新
* 本次更新内容：v1.0.7
* 1、统一页面的加载特效；
* 2、修复了一些已知小问题；
* 3、调整了一些设计方案；
* 2025年5月24日
## 安装环境
```
PHP >= 8.1
MySQL >= 5.7
composer >= 2.5.8
```
```
PHP 需要安装扩展 redis、event、fileinfo
PHP 需要解除禁用函数找到配置文件 php.ini ，找到 disable_functions 并去掉以下函数前面的分号；
```
```
disable_functions = passthru,system,chroot,chgrp,chown,popen,pcntl_exec,ini_alter,ini_restore,dl,openlog,syslog,readlink,symlink,popepassthru,pcntl_waitpid,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,imap_open,apache_setenv
```
## 升级composer
```
composer self-update
```
## 安装 webman
```
composer create-project workerman/webman:~2.0
```
## 进入目录
```
cd webman
```
## 在安装 webniu
```
composer require webniucom/webniu
```
## 注意事项
```
1. 安装后给目录权限，否则启动容易报错！；
2. 宝塔异步项目运行需要检查运行权限账户；
3. 安装后默认webman的端口在根目录config/process.php查看
4. 默认端口是：8787
5. 启动命令，例如：php start.php start 开发者模式 php start.php start -d 守护进程；
5. 启动命令可以指定用户启动，例如：sudo -u www php start.php start  以www运行；
```
## 启动
```
php start.php start
```
## 访问
```
http://ip地址:8787/webniu
```
## 安装事项
```
1. 首次会弹出安装向导，按照提示安装即可；
2. 第二步会检查目录权限，可以忽略跳过不影响安装；
3. 如需重新安装可删除config/database.php、thinkorm.php文件，然后重新安装；
```
## 绑定域名访问
```
绑定域名，需要在配置文件中设置伪静态参数：
```
```
# 将请求转发到webman
  location ^~ / {
      proxy_set_header Host $http_host;
      proxy_set_header X-Forwarded-For $remote_addr;
      proxy_set_header X-Forwarded-Proto $scheme;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_http_version 1.1;
      proxy_set_header Connection "";
      if (!-f $request_filename){
          proxy_pass http://127.0.0.1:8787;
      }
  }

  # 拒绝访问所有以 .php 结尾的文件
  location ~ \.php$ {
      return 404;
  }

  # 允许访问 .well-known 目录
  location ~ ^/\.well-known/ {
    allow all;
  }

  # 拒绝访问所有以 . 开头的文件或目录
  location ~ /\. {
      return 404;
  }
```
## 使用文档
```
https://help.webniu.com 制作中未上线
```
## 更多插件
```
https://app.webniu.com 制作中未上线
```

 

