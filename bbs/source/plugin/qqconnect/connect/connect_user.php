<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_user.php 26679 2011-12-19 12:55:05Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

$op = !empty($_GET['op']) ? trim($_GET['op'], '/') : '';
if(!in_array($op, array('get'))) {
	showmessage('undefined_action');
}

if($_GET['hash'] != formhash()) {
	showmessage('submit_invalid');
}

if($op == 'get') {
	$auth_code = authcode($_G['cookie']['con_auth_hash']);
	$auth_code = explode('|', authcode($_G['cookie']['con_auth_hash']));
	$conuin = authcode($auth_code[0]);
	$conuinsecret = authcode($auth_code[1]);
	$conopenid = authcode($auth_code[2]);

	if($conuin && $conuinsecret && $conopenid) {
		try {
			$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
			$connect_user_info = $connectOAuthClient->connectGetUserInfo($conopenid, $conuin, $conuinsecret);
		} catch(Exception $e) {
			exit;
		}
		if ($connect_user_info['nickname']) {
			$qq_nick = $connect_user_info['nickname'];
			$connect_nickname = $connectService->connectFilterUsername($qq_nick);
		}

		loaducenter();
		$ucresult = uc_user_checkname($connect_nickname);
		$first_available_username = '';
		if($ucresult >= 0) {
			$first_available_username = $connect_nickname;
		}
		echo "<span>".$qq_nick."\t".$first_available_username."</span>";
	}
}

?>