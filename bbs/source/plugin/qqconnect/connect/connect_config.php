<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_config.php 27941 2012-02-17 03:25:15Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['uid'])) {
	showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
}

$op = !empty($_GET['op']) ? $_GET['op'] : '';
$referer = dreferer();

if(submitcheck('connectsubmit')) {

	if($op == 'config') { // debug 修改QQ绑定设置

		$ispublishfeed = !empty($_GET['ispublishfeed']) ? 1 : 0;
		$ispublisht = !empty($_GET['ispublisht']) ? 1 : 0;
		DB::query("UPDATE ".DB::table('common_member_connect')." SET conispublishfeed='$ispublishfeed', conispublisht='$ispublisht' WHERE uid='$_G[uid]'");
		if (!$ispublishfeed || !$ispublisht) {
			dsetcookie('connect_synpost_tip');
		}
		showmessage('qqconnect:connect_config_success', $referer);

	} elseif($op == 'unbind') {

		$connectService->connectMergeMember();

		$connect_member = DB::fetch_first("SELECT * FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");

		if ($connect_member['conuinsecret']) {

			if($_G['member']['conisregister']) {
				if($_G['setting']['strongpw']) {
					$strongpw_str = array();
					if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['newpassword1'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_1');
					}
					if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['newpassword1'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_2');
					}
					if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['newpassword1'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_3');
					}
					if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['newpassword1'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_4');
					}
					if($strongpw_str) {
						showmessage(lang('member/template', 'password_weak').implode(',', $strongpw_str));
					}
				}
				if($_GET['newpassword1'] !== $_GET['newpassword2']) {
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}

			$connectService->connectUserUnbind();

		} else { // debug 因为老用户access token等信息，所以没法通知connect，所以直接在本地解绑就行了，不fopen connect

			if($_G['member']['conisregister']) {
				if($_GET['newpassword1'] !== $_GET['newpassword2']) {
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}
		}

		C::t('#qqconnect#common_member_connect')->delete($_G['uid']);

		C::t('common_member')->update($_G['uid'], array('conisbind' => 0));
		DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$_G[uid]', '{$_G[member][conopenid]}', '2', '$_G[timestamp]')");

		if($_G['member']['conisregister']) {
			loaducenter();
			uc_user_edit(addslashes($_G['member']['username']), null, $_GET['newpassword1'], null, 1);
		}

		foreach($_G['cookie'] as $k => $v) {
			dsetcookie($k);
		}

		$_G['uid'] = $_G['adminid'] = 0;
		$_G['username'] = $_G['member']['password'] = '';

		showmessage('qqconnect:connect_config_unbind_success', 'member.php?mod=logging&action=login');
	}

} else {

	if($_G[inajax] && $op == 'synconfig') {
		DB::query("UPDATE ".DB::table('common_member_connect')." SET conispublishfeed='0', conispublisht='0' WHERE uid='$_G[uid]'");
		dsetcookie('connect_synpost_tip');

	} else {
		dheader('location: home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp');
	}
}
?>