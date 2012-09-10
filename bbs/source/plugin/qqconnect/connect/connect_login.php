<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_login.php 28346 2012-02-28 05:56:51Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = !empty($_GET['op']) ? $_GET['op'] : '';
if(!in_array($op, array('init', 'callback', 'change'))) {
	showmessage('undefined_action');
}

$referer = dreferer();

try {
	$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
} catch(Exception $e) {
	showmessage('qqconnect:connect_app_invalid');
}
if($op == 'init') {

	if($_G['member']['conisbind'] && $_GET['reauthorize']) {
		if($_GET['formhash'] == FORMHASH) {
			$connectService->connectMergeMember();
			$connectService->connectUserUnbind();
		} else {
			showmessage('submit_invalid');
		}
	}

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	try {
		$response = $connectOAuthClient->connectGetRequestToken();
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed', $referer);
	}

	$request_token = $response['oauth_token'];
	$request_token_secret = $response['oauth_token_secret'];

	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']) . (!empty($_GET['isqqshow']) ? '&isqqshow=yes' : '');
	$redirect = $connectOAuthClient->getOAuthAuthorizeURL($request_token, $callback);;
	dheader('Location:' . $redirect);

} elseif($op == 'callback') {

	$params = $_GET;

	if(!isset($params['receive'])) {
		$utilService = Cloud::loadClass('Service_Util');
		echo '<script type="text/javascript">setTimeout("window.location.href=\'connect.php?receive=yes&'.str_replace("'", "\'", $utilService->httpBuildQuery($_GET, '', '&')).'\'", 1)</script>';
		exit;
	}

	try {
		$response = $connectOAuthClient->connectGetAccessToken($params, $_G['cookie']['con_request_token_secret']);
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_access_token_failed', $referer);
	}

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	$conuin = $response['oauth_token'];
	$conuinsecret = $response['oauth_token_secret'];
	$conopenid = strtoupper($response['openid']);
	if(!$conuin || !$conuinsecret || !$conopenid) {
		showmessage('qqconnect:connect_get_access_token_failed', $referer);
	}

	loadcache('connect_blacklist');
	if(in_array($conopenid, array_map('strtoupper', $_G['cache']['connect_blacklist']))) {
		$change_qq_url = $_G['connect']['discuz_change_qq_url'];
		showmessage('qqconnect:connect_uin_in_blacklist', $referer, array('changeqqurl' => $change_qq_url));
	}

	$referer = $referer && (strpos($referer, 'logging') === false) && (strpos($referer, 'mod=login') === false) ? $referer : 'index.php';

	if($params['uin']) {
		$old_conuin = $params['uin'];
	}

	$is_notify = true;

	$conispublishfeed = $conispublisht = 1;

	$is_user_info = 1;
	$is_feed = 1;

	$user_auth_fields = 1;

	$cookie_expires = 2592000;
	dsetcookie('client_created', TIMESTAMP, $cookie_expires);
	dsetcookie('client_token', $conopenid, $cookie_expires);

	$connect_member = array();
	if($old_conuin) {
		$connect_member = DB::fetch_first("SELECT uid, conuin, conuinsecret, conopenid FROM ".DB::table('common_member_connect')." WHERE conuin='$old_conuin'");
	}
	if(empty($connect_member)) {
		$connect_member = DB::fetch_first("SELECT uid, conuin, conuinsecret, conopenid FROM ".DB::table('common_member_connect')." WHERE conopenid='$conopenid'");
	}
	if($connect_member) {
		$member = getuserbyuid($connect_member['uid']);
		if($member) {
			if(!$member['conisbind']) {
				DB::delete('common_member_connect', array('uid' => $connect_member['uid']));
				unset($connect_member);
			} else {
				$connect_member['conisbind'] = $member['conisbind'];
			}
		} else {
			DB::delete('common_member_connect', array('uid' => $connect_member['uid']));
			unset($connect_member);
		}
	}

	$connect_is_unbind = $params['is_unbind'] == 1 ? 1 : 0;
	if($connect_is_unbind && $connect_member && !$_G['uid'] && $is_notify) {
		dsetcookie('connect_js_name', 'user_bind', 86400);
		dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'registerbind'))), 86400);
	}

	if($_G['uid']) {

		if($connect_member && $connect_member['uid'] != $_G['uid']) {
			showmessage('qqconnect:connect_register_bind_uin_already', $referer, array('username' => $_G['member']['username']));
		}

		$isqqshow = !empty($_GET['isqqshow']) ? 1 : 0;

		$current_connect_member = DB::fetch_first("SELECT conopenid FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");
		if($_G['member']['conisbind'] && $current_connect_member['conopenid']) {
			if(strtoupper($current_connect_member['conopenid']) != $conopenid) {
				showmessage('qqconnect:connect_register_bind_already', $referer);
			}
			DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conisregister='0', conisfeed='1', conisqqshow='$isqqshow' WHERE uid='$_G[uid]'");

		} else { // debug 当前登录的论坛账号并没有绑定任何QQ号，则可以绑定当前的这个QQ号
			if(empty($current_connect_member)) {
				DB::query("INSERT INTO ".DB::table('common_member_connect')." (uid, conuin, conuinsecret, conopenid, conispublishfeed, conispublisht, conisregister, conisfeed, conisqqshow) VALUES ('$_G[uid]', '$conuin', '$conuinsecret', '$conopenid', '1', '1', '0', '1', '$isqqshow')");
			} else {
				DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conispublishfeed='1', conispublisht='1', conisregister='0', conisfeed='1', conisqqshow='$isqqshow' WHERE uid='$_G[uid]'");
			}
			C::t('common_member')->update($_G['uid'], array('conisbind' => '1'));
		}

		if($is_notify) {
			dsetcookie('connect_js_name', 'user_bind', 86400);
			dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'loginbind'))), 86400);
		}
		dsetcookie('connect_login', 1, 31536000);
		dsetcookie('connect_is_bind', '1', 31536000);
		dsetcookie('connect_uin', $conopenid, 31536000);
		dsetcookie('stats_qc_reg', 3, 86400);
		if($is_feed) {
			dsetcookie('connect_synpost_tip', 1, 31536000);
		}

		DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$_G[uid]', '$conopenid', '1', '$_G[timestamp]')");

		showmessage('qqconnect:connect_register_bind_success', $referer);

	} else {

		if($connect_member) { // debug 此分支是用户直接点击QQ登录，并且这个QQ号已经绑好一个论坛账号了，将直接登进论坛了
			DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conisfeed='1' WHERE uid='$connect_member[uid]'");

			$params['mod'] = 'login';
			connect_login($connect_member);

			loadcache('usergroups');
			$usergroups = $_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
			$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle']);

			C::t('common_member_status')->update($connect_member['uid'], array('lastip'=>$_G['clientip'], 'lastvisit'=>TIMESTAMP, 'lastactivity' => TIMESTAMP));
			$ucsynlogin = '';
			if($_G['setting']['allowsynlogin']) {
				loaducenter();
				$ucsynlogin = uc_user_synlogin($_G['uid']);
			}

			dsetcookie('stats_qc_login', 3, 86400);
			showmessage('login_succeed', $referer, $param, array('extrajs' => $ucsynlogin));

		} else { // debug 此分支是用户直接点击QQ登录，并且这个QQ号还未绑定任何论坛账号，将将跳转到一个新页引导用户注册个新论坛账号或绑一个已有的论坛账号

			$encode[] = authcode($conuin, 'ENCODE');
			$encode[] = authcode($conuinsecret, 'ENCODE');
			$encode[] = authcode($conopenid, 'ENCODE');
			$encode[] = authcode($user_auth_fields, 'ENCODE');
			$auth_hash = authcode(implode('|', $encode), 'ENCODE');
			dsetcookie('con_auth_hash', $auth_hash);

			unset($params['op']);
			$params['mod'] = 'register';
			$params['referer'] = $referer;
			$params['con_auth_hash'] = $auth_hash;

			$utilService = Cloud::loadClass('Service_Util');
			$redirect = 'connect.php?'.$utilService->httpBuildQuery($params, '', '&');
			$utilService->redirect($redirect);
		}
	}

} elseif($op == 'change') {
	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	try {
		$response = $connectOAuthClient->connectGetRequestToken();
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed', $referer);
	}

	$request_token = $response['oauth_token'];
	$request_token_secret = $response['oauth_token_secret'];

	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']);
	$redirect = $connectOAuthClient->getOAuthAuthorizeURL($request_token, $callback);;
	dheader('Location:' . $redirect);
}

function connect_login($connect_member) {
	global $_G;

	if(!($member = getuserbyuid($connect_member['uid'], 1))) {
		return false;
	} else {
		if(isset($member['_inarchive'])) {
			C::t('common_member_archive')->move_to_master($member['uid']);
		}
	}

	require_once libfile('function/member');
	$cookietime = 1296000;
	setloginstatus($member, $cookietime);

	dsetcookie('connect_login', 1, $cookietime);
	dsetcookie('connect_is_bind', '1', 31536000);
	dsetcookie('connect_uin', $connect_member['conopenid'], 31536000);
	return true;
}

?>