yii_uc ：orzero.com 整理出品
======

项目描述：

yii 整合 uc 用户接口，可以基于此做二次开发和部署。

整合Yii, ucenter, discuz bbs, discuz home, 实现同步登陆。


实现：
1、Yii同步登陆，即在Yii登陆或者登出后，其他模块（bbs，uchome也同步登陆或登出）;

2、Yii单独注册用户（单独的注册功能，同时调用uc的api入库ucenter用户表）;

3、Yii找回密码功能;


其他程序注册的用户可以直接登录，并自动同步到Yii的单独的用户表：uc_user里面，Yii注册的用户同步入库到uc对应的用户表里面，其中 uc_user.password 密码字段为冗余字段，不具备验证功能，验证直接通过uc的api实现。


目录结构：

/yii_uc 为Yii的主程序,对应域名 api.orzero.com 

/yii_uc/bbs 论坛程序根目录，对应域名 bbs.orzero.com 

/yii_uc/uc    ucenter程序，对应域名  uc.orzero.com 

/yii_uc/home  uchome程序, 对应域名   home.orzero.com


数据库备份：

/data/uc_bbs_home_yii.mysql 可以直接导入数据库yii_uc恢复，所有程序均采用admin : admin 作为默认账号,ucenter单独安装，康盛的整套服务下载于:20120910 utf-8简体中文的最新版本


配置安装说明：

请自己把Yii框架代码放置到相对目录：/yii_uc/../yii/framework

Yii配置文件详见这几个文件，强烈建议修改默认值，并持跟uc的设置一致： /yii_uc/protected/config/main.php /yii_uc/protected/config/uc.php /yii_uc/api/uc.php

Yii跟uc通信接口：/yii_uc/api/uc.php  对应链接 http://api.orzero.com/api/uc.php


要用于自己的服务请自行修改,本地测试可以直接添加hosts（ 添加下面的内容到 ：C:\Windows\System32\drivers\etc\hosts ），并配置好apache虚拟主机直接运行

127.0.0.1       api.orzero.com
127.0.0.1       bbs.orzero.com
127.0.0.1       home.orzero.com
127.0.0.1       uc.orzero.com



虚拟机配置实例，比如我本地代码放置在 G:/github/yii_uc 目录下面，修改apache对应的虚拟机文件 httpd-vhosts.conf,添加如下内容：

NameVirtualHost *:80

<VirtualHost *:80>
    DocumentRoot "G:/github/yii_uc"
    ServerName api.orzero.com
<Directory "G:/github/yii_uc">
Options Indexes FollowSymLinks
AllowOverride FileInfo
Order allow,deny
Allow from all
</Directory>
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "G:/github/yii_uc/uc"
    ServerName uc.orzero.com
<Directory "G:/github/yii_uc/uc">
Options Indexes FollowSymLinks
AllowOverride FileInfo
Order allow,deny
Allow from all
</Directory>
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "G:/github/yii_uc/home"
    ServerName home.orzero.com
<Directory "G:/github/yii_uc/home">
Options Indexes FollowSymLinks
AllowOverride FileInfo
Order allow,deny
Allow from all
</Directory>
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "G:/github/yii_uc/bbs"
    ServerName bbs.orzero.com
<Directory "G:/github/yii_uc/bbs">
Options Indexes FollowSymLinks
AllowOverride FileInfo
Order allow,deny
Allow from all
</Directory>
</VirtualHost>


主要实现的三项功能：
1、登陆
http://api.orzero.com/index.php?r=site/login

2、注册
http://api.orzero.com/index.php?r=site/register

3、找回密码
http://api.orzero.com/index.php?r=site/recovery






