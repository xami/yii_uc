<?php

define('UC_CONNECT', 'mysql');              // 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen()
// mysql 是直接连接的数据库, 为了效率, 建议采用 mysql
//数据库相关 (mysql 连接时, 并且没有设置 UC_DBLINK 时, 需要配置以下变量)
define('UC_DBHOST', 'localhost');           // UCenter 数据库主机
define('UC_DBUSER', 'root');                // UCenter 数据库用户名
define('UC_DBPW', '');                  // UCenter 数据库密码
define('UC_DBNAME', 'ucenter');                // UCenter 数据库名称
define('UC_DBCHARSET', 'utf8');             // UCenter 数据库字符集
define('UC_DBTABLEPRE', 'ucenter.uc_');            // UCenter 数据库表前缀

//通信相关
define('UC_KEY', 'ebR4GhhpZB7e9MHVJHbd&^*YHJRRWE');               // 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', 'http://api.orzero.com');  // UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'utf8');               // UCenter 的字符集
define('UC_IP', '');                    // UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', 4);                  // 当前应用的 ID

//include dirname(__FILE__).'/uc.php';
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
//        'application.modules.user.models.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>false,
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
	),

	// application components
	'components'=>array(
        'user'=>array(
            'class'=>'WebUser',
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
            'loginUrl' => array('/site/login'),
        ),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
        'db'=>array(
            'connectionString' => 'mysql:host=localhost;dbname=yii_uc',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            // prior to yum0.8rc7 tablePrefix is not necessary anymore, but it can not hurt
            'tablePrefix' => 'uc_',
        ),

        'cache' => array('class' => 'system.caching.CDummyCache'),
		// uncomment the following to use a MySQL database
		/*
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=testdrive',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),
		*/
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);