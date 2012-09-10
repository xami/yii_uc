<?php
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

// change the following paths if necessary
$yii=dirname(__FILE__).'/../../yii/framework/yii.php';
$config=dirname(__FILE__).'/../protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',0);
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
require_once($yii);

require(dirname(__FILE__).'/../protected/components/UcApplication.php');
Yii::createApplication('UcApplication', $config)->run();