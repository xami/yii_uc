<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: member_connect_register.php 26543 2011-12-15 02:17:57Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$from_connect = $_G['setting']['connect']['allow'] ? 1 : 0;
$regname = 'connect';

if(empty($_POST)) {

	$_G['qc']['connect_auth_hash'] = $_GET['con_auth_hash'];
	$_G['qc']['dreferer'] = dreferer();

	$auth_code = authcode($_G['qc']['connect_auth_hash']);
	if (empty($auth_code)) {
		showmessage('qqconnect:connect_get_request_token_failed', $referer);
	}
	$auth_code = explode('|', $auth_code);
	$conopenid = authcode($auth_code[2]);

	$_G['qc']['connect_is_feed'] = true;

	$_G['qc']['connect_app_id'] = $_G['setting']['connectappid'];
	$_G['qc']['connect_openid'] = $conopenid;
	unset($auth_code, $conopenid);

	$_G['qc']['connect_is_notify'] = true;

	foreach($_G['cache']['fields_register'] as $field) {
		$fieldid = $field['fieldid'];
		$html = profile_setting($fieldid, $connectdefault);
		if($html) {
			$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
			$htmls[$fieldid] = $html;
		}
	}

} else {

	if(!empty($_G['setting']['checkuinlimit']) && !empty($_GET['uin'])) {
		if($_G['qc']['uinlimit']) {
			showmessage('qqconnect:connect_register_uinlimit', '', array('limit' => $this->setting['connect']['register_uinlimit']));
		}
		if(!$_G['setting']['regconnect']) {
			showmessage('qqconnect:connect_register_closed');
		}
	}

	if(empty($_GET['auth_hash'])) {
		$_GET['auth_hash'] = $_G['cookie']['con_auth_hash'];
	}
	$auth_code = authcode($_GET['auth_hash']);
	$auth_code = explode('|', authcode($_GET['auth_hash']));
	$conuin = authcode($auth_code[0]);
	$conuinsecret = authcode($auth_code[1]);
	$conopenid = authcode($auth_code[2]);
	$user_auth_fields = authcode($auth_code[3]);

	$cookie_expires = 2592000;
	dsetcookie('client_created', TIMESTAMP, $cookie_expires);
	dsetcookie('client_token', 1, $cookie_expires);

	if (!$conuin || !$conuinsecret || !$conopenid) {
		showmessage('qqconnect:connect_get_request_token_failed');
	}

	if(C::t('#qqconnect#common_member_connect')->fetch_fields_by_openid($conopenid, 'uid')) {
		showmessage('qqconnect:connect_register_bind_uin_already');
	}

	$conispublishfeed = $conispublisht = 0;
	if ($_GET['is_feed']) {
		$conispublishfeed = $conispublisht = 1;
	}
	$is_qzone_avatar = !empty($_GET['use_qzone_avatar']) ? 1 : 0;
	$is_use_qqshow = !empty($_GET['use_qqshow']) ? 1 : 0;
	$userdata = array();
	$userdata['avatarstatus'] = $is_qzone_avatar;
	$userdata['conisbind'] = 1;

	C::t('#qqconnect#common_member_connect')->insert(array(
		'uid' => $uid,
		'conuin' => $conuin,
		'conuinsecret' => $conuinsecret,
		'conopenid' => $conopenid,
		'conispublishfeed' => $conispublishfeed,
		'conispublisht' => $conispublisht,
		'conisregister' => '1',
		'conisqzoneavatar' => $is_qzone_avatar,
		'conisfeed' => $user_auth_fields,
		'conisqqshow' => $is_use_qqshow,
	));


	dsetcookie('connect_js_name', 'user_bind', 86400);
	dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'register'))), 86400);
	dsetcookie('connect_login', 1, 31536000);
	dsetcookie('connect_is_bind', '1', 31536000);
	dsetcookie('connect_uin', $conopenid, 31536000);
	dsetcookie('stats_qc_reg', 1, 86400);
	if ($_GET['is_feed']) {
		dsetcookie('connect_synpost_tip', 1, 31536000);
	}

	C::t('#qqconnect#connect_memberbindlog')->insert(array('uid' => $uid, 'uin' => $conopenid, 'type' => '1', 'dateline' => $_G['timestamp']));
	dsetcookie('con_auth_hash');

	if($_G['setting']['connect']['register_groupid']) {
		$userdata['groupid'] = $groupinfo['groupid'] = $_G['setting']['connect']['register_groupid'];
	}
	C::t('common_member')->update($uid, $userdata);

	if($_G['setting']['connect']['register_addcredit']) {
		$addcredit = array('extcredits'.$_G['setting']['connect']['register_rewardcredit'] => $_G['setting']['connect']['register_addcredit']);
	}
	C::t('common_member_count')->increase($uid, $addcredit);
}

function connect_filter_username($username) {
	$username = str_replace(' ', '_', trim($username));
	return cutstr($username, 15, '');
}

?>