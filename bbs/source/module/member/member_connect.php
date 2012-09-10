<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_connect.php 27581 2012-02-07 02:03:31Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if(!$_G['setting']['connect']['allow']) {
	showmessage('qqconnect:qqconnect_closed');
}

if(defined('IN_MOBILE')) {
	showmessage("connect_register_mobile_bind_error", '', array("changeqqurl" => $_G['connect']['change_qq_url']));
}

if($_GET['action'] == 'login') { // debug 已有账号，绑定我的账号走此分支

	$ctl_obj = new logging_ctl();
	$ctl_obj->setting = $_G['setting'];
	$ctl_obj->setting['seccodestatus'] = 0;

	$ctl_obj->extrafile = libfile('member/connect_logging', 'module');
	$ctl_obj->template = 'member/login';
	$ctl_obj->on_login();

} else { // debug 完善我的资料，即添加个新的论坛账号走此分支

	$_G['qc']['connect_auth_hash'] = $_GET['con_auth_hash'];
	$auth_code = authcode($_G['qc']['connect_auth_hash']);
	$auth_code = explode('|', $auth_code);
	$conopenid = authcode($auth_code[2]);

	$ctl_obj = new register_ctl();
	$ctl_obj->setting = $_G['setting'];

	if($_G['setting']['regconnect']) {
		$ctl_obj->setting['regstatus'] = $ctl_obj->setting['regstatus'] ? $ctl_obj->setting['regstatus'] : 1;
	}

	$_G['setting']['regclosed'] = $_G['setting']['regconnect'] && !$_G['setting']['regstatus'];

	loadcache('connect_blacklist');
	if(in_array($conopenid, $_G['cache']['connect_blacklist'])) {
		showmessage('qqconnect:connect_uin_in_blacklist', $referer, array('changeqqurl' => $_G['connect']['discuz_change_qq_url']));
	}

	$_G['qc']['uinlimit'] = $_G['setting']['connect']['register_uinlimit'] && C::t('#qqconnect#connect_memberbindlog')->count_uid_by_openid_type($conopenid, '1') >= $_G['setting']['connect']['register_uinlimit'];
	if($_G['qc']['uinlimit']) {
		$_G['setting']['regconnect'] = false;
	}
	if(!$_G['setting']['regconnect']) {
		$ctl_obj->showregisterform = 0;
		$ctl_obj->setting['sitemessage']['register'] = array();
	}

	if($_G['qc']['uinlimit']) {
		$ctl_obj->showregisterform = 0;
		$ctl_obj->setting['sitemessage']['register'] = array();
		$ctl_obj->setting['regconnect'] = false;
	}

	if($_G['setting']['connect']['register_regverify']) {
		$ctl_obj->setting['regverify'] = 0;
	}
	$ctl_obj->setting['seccodestatus'] = 0;
	$ctl_obj->setting['secqaa']['status'] = 0;

	$ctl_obj->setting['sendregisterurl'] = false;

	loadcache(array('fields_connect_register', 'profilesetting'));
	foreach($_G['cache']['fields_connect_register'] as $field => $data) {
		unset($_G['cache']['fields_register'][$field]);
	}
	$_G['cache']['profilesetting']['gender']['unchangeable'] = 0;
	$_G['cache']['profilesetting']['birthyear']['unchangeable'] = 0;
	$_G['cache']['profilesetting']['birthmonth']['unchangeable'] = 0;
	$_G['cache']['profilesetting']['birthday']['unchangeable'] = 0;
	$_G['cache']['fields_register'] = array_merge($_G['cache']['fields_connect_register'], $_G['cache']['fields_register']);

	if($_G['setting']['connect']['register_invite']) {
		$ctl_obj->setting['regstatus'] = 1;
	}

	if(!$_G['setting']['connect']['register_regverify']) {
		$ctl_obj->setting['seccodestatus'] = $_G['setting']['seccodestatus'];
		$ctl_obj->setting['secqaa']['status'] = $_G['setting']['secqaa']['status'];
	}

	$ctl_obj->setting['ignorepassword'] = 1;
	$ctl_obj->setting['checkuinlimit'] = 1;
	$ctl_obj->setting['strongpw'] = 0;

	$ctl_obj->extrafile = libfile('member/connect_register', 'module');
	$ctl_obj->template = 'member/register';
	$ctl_obj->on_register();

}

?>