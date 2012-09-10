<?php
/**
 * Created by JetBrains PhpStorm.
 * User: admin
 * Date: 12-9-6
 * Time: 上午11:28
 * To change this template use File | Settings | File Templates.
 */

define('UC_CLIENT_VERSION', '1.6.0');
define('UC_CLIENT_RELEASE', '20110501');

define('API_DELETEUSER', 1);        //note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);        //note 用户改名 API 接口开关
define('API_GETTAG', 1);        //note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);      //note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);     //note 同步登出 API 接口开关
define('API_UPDATEPW', 1);      //note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);    //note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);       //note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);        //note 更新应用列表 开关
define('API_UPDATECLIENT', 1);      //note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1);      //note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 1); //note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 1);     //note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 1);  //note 更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');


define('UC_CONNECT', 'mysql');              // 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen()
// mysql 是直接连接的数据库, 为了效率, 建议采用 mysql
//数据库相关 (mysql 连接时, 并且没有设置 UC_DBLINK 时, 需要配置以下变量)
define('UC_DBHOST', 'localhost');           // UCenter 数据库主机
define('UC_DBUSER', 'root');                // UCenter 数据库用户名
define('UC_DBPW', '');                  // UCenter 数据库密码
define('UC_DBNAME', 'yii_uc');                // UCenter 数据库名称
define('UC_DBCHARSET', 'utf8');             // UCenter 数据库字符集
define('UC_DBTABLEPRE', 'yii_uc.uc_');            // UCenter 数据库表前缀

//通信相关
define('UC_KEY', 'ebR4GhhpZB7e9MHVJHbd&^*YHJRRWE');               // 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', 'http://uc.orzero.com');  // UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'utf8');               // UCenter 的字符集
define('UC_IP', '127.0.0.1');                    // UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', 1);                  // 当前应用的 ID