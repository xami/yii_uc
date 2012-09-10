<?php
if(!defined('IN_UCHOME')) exit('Access Denied');
$_SGLOBAL['app']=Array
	(
	1 => Array
		(
		'name' => 'Yii_Uc',
		'url' => 'http://api.orzero.com/api/uc.php',
		'type' => 'DISCUZX',
		'open' => 1,
		'icon' => 'discuzx'
		),
	2 => Array
		(
		'name' => 'Yii_BBS',
		'url' => 'http://bbs.orzero.com',
		'type' => 'DISCUZX',
		'open' => 1,
		'icon' => 'discuzx'
		),
	3 => Array
		(
		'name' => '个人家园',
		'url' => 'http://home.orzero.com',
		'type' => 'UCHOME',
		'open' => '0',
		'icon' => 'uchome'
		)
	)
?>