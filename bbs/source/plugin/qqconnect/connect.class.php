<?php

/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect.class.php 28470 2012-03-01 07:24:49Z houdelei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_qqconnect_base {

	function init() {
		global $_G;
		include_once template('qqconnect:module');
		if(!$_G['setting']['connect']['allow'] || $_G['setting']['bbclosed']) {
			return;
		}
		$this->allow = true;
	}

	function common_base() {
		global $_G;

		if(!isset($_G['connect'])) {
			$_G['connect']['url'] = 'http://connect.discuz.qq.com';
			$_G['connect']['api_url'] = 'http://api.discuz.qq.com';
			$_G['connect']['avatar_url'] = 'http://avatar.connect.discuz.qq.com';

			$_G['connect']['qzone_public_share_url'] = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey';
			$_G['connect']['referer'] = !$_G['inajax'] && CURSCRIPT != 'member' ? $_G['basefilename'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '') : dreferer();
			$_G['connect']['weibo_public_appkey'] = 'ce7fb946290e4109bdc9175108b6db3a';

			$_G['connect']['login_url'] = $_G['siteurl'].'connect.php?mod=login&op=init&referer='.urlencode($_G['connect']['referer'] ? $_G['connect']['referer'] : 'index.php');
			$_G['connect']['callback_url'] = $_G['siteurl'].'connect.php?mod=login&op=callback';
			$_G['connect']['discuz_new_feed_url'] = $_G['siteurl'].'connect.php?mod=feed&op=new';
			$_G['connect']['discuz_new_share_url'] = $_G['siteurl'].'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=new';
			$_G['connect']['discuz_sync_tthread_url'] = $_G['siteurl'].'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=sync_tthread';
			$_G['connect']['discuz_change_qq_url'] = $_G['siteurl'].'connect.php?mod=login&op=change';
			$_G['connect']['auth_fields'] = array(
				'is_user_info' => 1,
				'is_feed' => 2,
			);

			if($_G['uid']) {
				dsetcookie('connect_is_bind', $_G['member']['conisbind'], 31536000);
				if(!$_G['member']['conisbind'] && $_G['cookie']['connect_login']) {
					$_G['cookie']['connect_login'] = 0;
					dsetcookie('connect_login');
				}
			}

			if($this->allow && !$_G['uid'] && !defined('IN_MOBILE')) {
				$_G['setting']['pluginhooks']['global_login_text'] = tpl_login_bar();
			}
		}
	}

}

class plugin_qqconnect extends plugin_qqconnect_base {

	var $allow = false;

	function plugin_qqconnect() {
		$this->init();
	}

	function common() {
		$this->common_base();
	}

	function discuzcode($param) {
		global $_G;
		if($param['caller'] == 'discuzcode') {
			$_G['discuzcodemessage'] = preg_replace('/\[wb=(.+?)\](.+?)\[\/wb\]/', '<a href="http://t.qq.com/\\1" target="_blank"><img src="\\2" /></a>', $_G['discuzcodemessage']);
		}
		if($param['caller'] == 'messagecutstr') {
			$_G['discuzcodemessage'] = preg_replace('/\[tthread=(.+?)\](.*?)\[\/tthread\]/', '', $_G['discuzcodemessage']);
		}
	}

	function avatar($param) {
		global $_G;
		if($this->allow) {
			if($_G['basescript'] == 'home' && CURMODULE == 'space' && (!$_GET['do'] || in_array($_GET['do'], array('profile', 'index')))) {
				$avataruid = $_GET['uid'];
			} elseif(CURMODULE == 'viewthread') {
				$avataruid = $_G['uid'];
			} else {
				return;
			}
			list($uid, $size, $returnsrc) = $param['param'];
			if($returnsrc || $size && $size != 'middle' || $uid != $avataruid) {
				return;
			}
			if(!$_G['member']['conopenid']) {
				$connectService = Cloud::loadClass('Service_Connect');
				$connectService->connectMergeMember();
			}
			if($_G['member']['conisqqshow'] && $_G['member']['conopenid']) {
				$_G['hookavatar'] = $this->_qqshow_img($_G['member']['conopenid']);
			}
		}
	}

	function global_login_extra() {
		if(!$this->allow) {
			return;
		}
		return tpl_global_login_extra();
	}

	function global_usernav_extra1() {
		global $_G;
		if(!$this->allow || !$_G['uid']) {
			return;
		}
		if(!$_G['member']['conisbind']) {
			return tpl_global_usernav_extra1();
		}
		return;
	}

	function global_footer() {
		global $_G;

		if(!$this->allow || !empty($_G['inshowmessage'])) {
			return;
		}

		$loadJs = array();

		$connectService = Cloud::loadClass('Service_Connect');

		if(defined('CURSCRIPT') && CURSCRIPT == 'forum' && defined('CURMODULE') && CURMODULE == 'viewthread'
			&& $_G['setting']['connect']['allow'] && $_G['setting']['connect']['qshare_allow']) {

			$appkey = $_G['setting']['connect']['qshare_appkey'] ? $_G['setting']['connect']['qshare_appkey'] : $_G['connect']['weibo_public_appkey'];

			$qsharejsurl = $_G['siteurl'] . 'static/js/qshare.js';
			$sitename = isset($_G['setting']['bbname']) ? $_G['setting']['bbname'] : '';
			$loadJs['qsharejs'] = array('jsurl' => $qsharejsurl, 'appkey' => $appkey, 'sitename' => $sitename, 'func' => '$C');
		}

		if(!empty($_G['cookie']['connect_js_name'])) {
			if($_G['cookie']['connect_js_name'] == 'user_bind') {
				$params = array('openid' => $_G['cookie']['connect_uin']);
				$jsurl = $connectService->connectUserBindJs($params);
				$loadJs['feedjs'] = array('jsurl' => $jsurl);
			} elseif($_G['cookie']['connect_js_name'] == 'feed_resend') {
				$jsurl = $connectService->connectFeedResendJs();
				$loadJs['feedjs'] = array('jsurl' => $jsurl);
			}
			dsetcookie('connect_js_name');
			dsetcookie('connect_js_params');
		}

		$connectService->connectMergeMember();
		if(!$_G['cookie']['connect_check_token'] && $_G['member']['conuinsecret']) {
			$request_url = $_G['siteurl'] . 'connect.php?mod=check&op=token&_r=' . rand(1, 10000);
			$loadJs['checktokenjs'] = array('jsurl' => $request_url);
		}

		if($_G['member']['conuinsecret'] && ($_G['cookie']['connect_last_report_time'] != date('Y-m-d') || $_G['cookie']['connect_report_times'] <= 4)) {
			$ajaxUrl = 'connect.php?mod=check&op=cookie';
			$loadJs['cookieloginjs'] = array('jsurl' => $ajaxUrl);
		}

		return tpl_global_footer($loadJs);
	}

	function _allowconnectfeed() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		return $_G['uid'] && $_G['setting']['connect']['allow'] && $_G['setting']['connect']['feed']['allow'] && ($_G['forum']['status'] == 3 && $_G['setting']['connect']['feed']['group'] || $_G['forum']['status'] != 3 && (!$_G['setting']['connect']['feed']['fids'] || in_array($_G['fid'], $_G['setting']['connect']['feed']['fids'])));
	}

	function _allowconnectt() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		return $_G['uid'] && $_G['setting']['connect']['allow'] && $_G['setting']['connect']['t']['allow'] && ($_G['forum']['status'] == 3 && $_G['setting']['connect']['t']['group'] || $_G['forum']['status'] != 3 && (!$_G['setting']['connect']['t']['fids'] || in_array($_G['fid'], $_G['setting']['connect']['t']['fids'])));
	}

	function _forumdisplay_fastpost_sync_method_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$allowconnectfeed = $this->_allowconnectfeed();
		$allowconnectt = $this->_allowconnectt();
		if($GLOBALS['fastpost'] && ($allowconnectfeed || $allowconnectt)) {
			$connectService = Cloud::loadClass('Service_Connect');
			$connectService->connectMergeMember();
			if ($_G['member']['is_feed']) {
				return tpl_sync_method($allowconnectfeed, $allowconnectt);
			}
		}
	}

	function _post_sync_method_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$allowconnectfeed = $this->_allowconnectfeed();
		$allowconnectt = $this->_allowconnectt();
		if(!$_G['inajax'] && ($allowconnectfeed || $allowconnectt) && ($_GET['action'] == 'newthread' || $_GET['action'] == 'edit' && $GLOBALS['isfirstpost'] && $GLOBALS['thread']['displayorder'] == -4)) {
			$connectService = Cloud::loadClass('Service_Connect');
			$connectService->connectMergeMember();
			if ($_G['member']['is_feed']) {
				return tpl_sync_method($allowconnectfeed, $allowconnectt);
			}
		}
	}

	function _post_infloat_btn_extra_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$allowconnectfeed = $this->_allowconnectfeed();
		$allowconnectt = $this->_allowconnectt();
		if($_G['inajax'] && ($allowconnectfeed || $allowconnectt) && $_GET['action'] == 'newthread') {
			$connectService = Cloud::loadClass('Service_Connect');
			$connectService->connectMergeMember();
			if ($_G['member']['is_feed']) {
				return tpl_infloat_sync_method($allowconnectfeed, $allowconnectt, ' z');
			}
		}
	}

	function _post_feedlog_message($param) {
		if(!$this->allow) {
			return;
		}
		global $_G;
		if(empty($_GET['connect_publish_feed']) || $_GET['action'] == 'reply' || substr($param['param'][0], -8) != '_succeed' || $_GET['action'] == 'edit' && !$GLOBALS['isfirstpost'] || !$this->_allowconnectfeed()) {
			return;
		}

		$tid = $param['param'][2]['tid'];
		DB::query("REPLACE INTO ".DB::table('connect_feedlog')." (tid, uid, lastpublished, dateline, status) VALUES ('$tid', '$_G[uid]', '0', '$_G[timestamp]', '1')");
	}

	function _post_tlog_message($param) {
		if(!$this->allow) {
			return;
		}
		global $_G;
		if(empty($_GET['connect_publish_t']) || $_GET['action'] == 'reply' || substr($param['param'][0], -8) != '_succeed' || $_GET['action'] == 'edit' && !$GLOBALS['isfirstpost'] || !$this->_allowconnectt()) {
			return;
		}

		$tid = $param['param'][2]['tid'];
		DB::query("REPLACE INTO ".DB::table('connect_tlog')." (tid, uid, lastpublished, dateline, status) VALUES ('$tid', '$_G[uid]', '0', '$_G[timestamp]', '1')");
	}

	function _viewthread_share_method_output() {
		global $_G;

		$connectService = Cloud::loadClass('Service_Connect');

		if($GLOBALS['page'] == 1 && $_G['forum_firstpid'] && $GLOBALS['postlist'][$_G['forum_firstpid']]['invisible'] == 0) {
			$_G['connect']['feed_js'] = $_G['connect']['t_js'] = $feedlogstatus = $tlogstatus = false;
			if($_G['member']['conisbind'] && $_G['uid'] == $_G['forum_thread']['authorid'] && $_G['forum_thread']['displayorder'] >= 0 && !getstatus($_G['forum_thread']['status'], 7)) {
				$_G['connect']['feed_log'] = DB::fetch_first("SELECT * FROM ".DB::table('connect_feedlog')." WHERE tid='$_G[tid]'");
				if($_G['connect']['feed_log']) {
					$_G['connect']['feed_interval'] = 300;
					$_G['connect']['feed_publish_max'] = 1000;
					if($_G['connect']['feed_log']['status'] == 1 || ($_G['connect']['feed_log']['status'] == 2
						&& TIMESTAMP - $_G['connect']['feed_log']['lastpublished'] > $_G['connect']['feed_interval']
						&& $_G['connect']['feed_log']['publishtimes'] < $_G['connect']['feed_publish_max'])) {
						DB::query("UPDATE ".DB::table('connect_feedlog')." SET status='2', lastpublished='$_G[timestamp]', publishtimes=publishtimes+1 WHERE tid='$_G[tid]' AND status!=4");
						$_G['connect']['feed_js'] = $feedlogstatus = true;
					}
				}
			}

			if($_G['member']['conisbind'] && $_G['uid'] == $_G['forum_thread']['authorid'] && $_G['forum_thread']['displayorder'] >= 0 && !getstatus($_G['forum_thread']['status'], 8)) {
				$_G['connect']['t_log'] = DB::fetch_first("SELECT * FROM ".DB::table('connect_tlog')." WHERE tid='$_G[tid]'");
				if($_G['connect']['t_log']) {
					$_G['connect']['t_interval'] = 300;
					$_G['connect']['t_publish_max'] = 1000;
					if($_G['connect']['t_log']['status'] == 1 || ($_G['connect']['t_log']['status'] == 2
						&& TIMESTAMP - $_G['connect']['t_log']['lastpublished'] > $_G['connect']['t_interval']
						&& $_G['connect']['t_log']['publishtimes'] < $_G['connect']['t_publish_max'])) {
						DB::query("UPDATE ".DB::table('connect_tlog')." SET status='2', lastpublished='$_G[timestamp]', publishtimes=publishtimes+1 WHERE tid='$_G[tid]' AND status!=4");
						$_G['connect']['t_js'] = $tlogstatus = true;
					}
				}
			}

			if($feedlogstatus || $tlogstatus){
				$newstatus = $_G['forum_thread']['status'];
				$newstatus = $feedlogstatus ? setstatus(7, 1, $newstatus) : $newstatus;
				$newstatus = $tlogstatus ? setstatus(8, 1, $newstatus) : $newstatus;
				C::t('forum_thread')->update($_G['tid'], array('status' => $newstatus));
			}

			$_G['connect']['thread_url'] = $_G['siteurl'].$GLOBALS['canonical'];

			$_G['connect']['qzone_share_url'] = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=share&sh_type=1&thread_id=' . $_G['tid'];
			$_G['connect']['weibo_share_url'] = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=share&sh_type=2&thread_id=' . $_G['tid'];
			$_G['connect']['pengyou_share_url'] = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=share&sh_type=3&thread_id=' . $_G['tid'];

			$_G['connect']['qzone_share_api'] = $_G['connect']['qzone_public_share_url'].'?url='.urlencode($_G['connect']['thread_url']);
			$_G['connect']['pengyou_share_api'] = $_G['connect']['qzone_public_share_url'].'?to=pengyou&url='.urlencode($_G['connect']['thread_url']);
			$params = array('oauth_consumer_key' => $_G['setting']['connectappid'], 'title' => $GLOBALS['postlist'][$_G['forum_firstpid']]['subject'], 'url' => $_G['connect']['thread_url']);
			$params['sig'] = $connectService->connectGetSig($params, $connectService->connectGetSigKey());
			$utilService = Cloud::loadClass('Service_Util');
			$_G['connect']['t_share_api'] =	$_G['connect']['url'].'/mblog/redirect?'.$utilService->httpBuildQuery($params, '', '&');

			$_G['connect']['first_post'] = daddslashes($GLOBALS['postlist'][$_G['forum_firstpid']]);
			$_GET['connect_autoshare'] = !empty($_GET['connect_autoshare']) ? 1 : 0;

			$_G['connect']['weibo_appkey'] = $_G['connect']['weibo_public_appkey'];
			if($this->allow && $_G['setting']['connect']['qshare_appkey']) {
				$_G['connect']['weibo_appkey'] = $_G['setting']['connect']['qshare_appkey'];
			}

			$jsurl = '';
			if($_G['connect']['feed_js'] || $_G['connect']['t_js']) {
				$params = array();
				$params['thread_id'] = $_G['tid'];
				$params['ts'] = TIMESTAMP;
				$params['type'] = bindec(($_G['connect']['t_js'] ? '1' : '0').($_G['connect']['feed_js'] ? '1' : '0'));
				$params['sig'] = $connectService->connectGetSig($params, $connectService->connectGetSigKey());

				$utilService = Cloud::loadClass('Service_Util');
				$jsurl = $_G['connect']['discuz_new_feed_url'].'&'.$utilService->httpBuildQuery($params, '', '&');
			}

			if (!$_G['member']['conisbind'] && $_G['group']['allowgetimage'] && $_G['thread']['price'] == 0) {
				if ($_G['connect']['first_post']['message']) {
					$connectService = Cloud::loadClass('Service_Connect');
					$post['html_content'] = $connectService->connectParseBbcode($_G['connect']['first_post']['message'], $_G['connect']['first_post']['fid'], $_G['connect']['first_post']['pid'], $_G['connect']['first_post']['htmlon'], $attach_images);
					if($attach_images && is_array($attach_images)) {
						$attach_images = array_slice($attach_images, 0, 3);
						$share_images = array();
						foreach ($attach_images as $attach_image) {
							$share_images[] = urlencode($attach_image['big']);
						}
						$_G['connect']['share_images'] = implode('|', $share_images);
						unset($share_images);
					}
				}
			}
			$connectService->connectMergeMember();
			return tpl_viewthread_share_method($jsurl);
		}
	}

	function _viewthread_bottom_output() {
		if(!$this->allow) {
			return;
		}
		global $_G, $thread, $rushreply;
		$uids = $openids = array();
		foreach($GLOBALS['postlist'] as $pid => $post) {
			if($post['anonymous']) {
				continue;
			}
			if($post['authorid']) {
				$uids[$post['authorid']] = $post['authorid'];
			}
		}
		foreach(C::t('#qqconnect#common_member_connect')->fetch_all($uids) as $connect) {
			if($connect['conisqqshow'] && $connect['conopenid']) {
				$openids[$connect['uid']] = $connect['conopenid'];
			}
		}
		foreach($GLOBALS['postlist'] as $pid => $post) {
			if(getstatus($post['status'], 5)) {
				$matches = array();
				preg_match('/\[tthread=(.+?),(.+?)\](.*?)\[\/tthread\]/', $post['message'], $matches);
				if($matches[1] && $matches[2]) {
					$post['message'] = preg_replace('/\[tthread=(.+?)\](.*?)\[\/tthread\]/', lang('connect', 'connect_tthread_message', array('username' => $matches[1], 'nick' => $matches[2])), $post['message']);
				}
				$post['authorid'] = 0;
				$post['author'] = lang('connect', 'connect_tthread_comment');
				$post['avatar'] = $matches[3] ? '<img src="'.$matches[3].'/120'.'">' : '<img src="'.$_G['siteurl'].'/static/image/common/tavatar.gif">';
				$post['groupid'] = '7';
				$GLOBALS['postlist'][$pid] = $post;
				continue;
			}
			if($post['anonymous']) {
				continue;
			}
			if($openids[$post['authorid']]) {
				$GLOBALS['postlist'][$pid]['avatar'] = $this->_qqshow_img($openids[$post['authorid']]);
			}
		}
		if($GLOBALS['page'] == 1 && $GLOBALS['postlist'][$_G['forum_firstpid']]['invisible'] == 0) {
			$jsurl = '';
			if($_G['uid'] && $_G['setting']['connect']['t']['reply'] && !$thread['closed'] && !$rushreply && getstatus($_G['forum_thread']['status'], 8)) {
				$jsurl = $_G['connect']['discuz_sync_tthread_url'].'&tid='.$thread['tid'];
			}
			return tpl_viewthread_bottom($jsurl);
		}
	}

	function _qqshow_img($openid) {
		global $_G;
		return '<img width="120" src="http://open.show.qq.com/cgi-bin/qs_open_snapshot?appid='.$_G['setting']['connectappid'].'&openid='.$openid.'" />';
	}

}

class plugin_qqconnect_member extends plugin_qqconnect {

	function logging_method() {
		if(!$this->allow) {
			return;
		}
		return tpl_login_bar();
	}

	function register_logging_method() {
		if(!$this->allow) {
			return;
		}
		return tpl_login_bar();
	}

	function connect_input_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$_G['setting']['pluginhooks']['register_input'] = tpl_register_input();
	}

	function connect_bottom_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$_G['setting']['pluginhooks']['register_bottom'] = tpl_register_bottom();
	}

}

class plugin_qqconnect_forum extends plugin_qqconnect {

	function index_status_extra() {
		global $_G;
		if(!$this->allow) {
			return;
		}
		if($_G['setting']['connect']['like_allow'] && $_G['setting']['connect']['like_url'] || $_G['setting']['connect']['turl_allow'] && $_G['setting']['connect']['turl_code']) {
			return tpl_index_status_extra();
		}
	}

	function forumdisplay_fastpost_sync_method_output() {
		return $this->_forumdisplay_fastpost_sync_method_output();
	}

	function post_sync_method_output() {
		return $this->_post_sync_method_output();
	}

	function post_infloat_btn_extra_output() {
		return $this->_post_infloat_btn_extra_output();
	}

	function post_feedlog_message($param) {
		return $this->_post_feedlog_message($param);
	}

	function post_tlog_message($param) {
		return $this->_post_tlog_message($param);
	}

	function viewthread_share_method_output() {
		return $this->_viewthread_share_method_output();
	}

	function viewthread_bottom_output() {
		return $this->_viewthread_bottom_output();
	}

}

class plugin_qqconnect_group extends plugin_qqconnect {

	function forumdisplay_fastpost_sync_method_output() {
		return $this->_forumdisplay_fastpost_sync_method_output();
	}

	function post_sync_method_output() {
		return $this->_post_sync_method_output();
	}

	function post_infloat_btn_extra_output() {
		return $this->_post_infloat_btn_extra_output();
	}

	function post_feedlog_message($param) {
		return $this->_post_feedlog_message($param);
	}

	function post_tlog_message($param) {
		return $this->_post_tlog_message($param);
	}

	function viewthread_share_method_output() {
		return $this->_viewthread_share_method_output();
	}

	function viewthread_bottom_output() {
		return $this->_viewthread_bottom_output();
	}
}

class plugin_qqconnect_home extends plugin_qqconnect {

	function spacecp_profile_bottom() {
		global $_G;

		if(submitcheck('profilesubmit')) {
			$_G['group']['maxsigsize'] = $_G['group']['maxsigsize'] < 200 ? 200 : $_G['group']['maxsigsize'];
			return;
		}
		if($_G['uid'] && $_G['setting']['connect']['allow']) {

			$connectService = Cloud::loadClass('Service_Connect');
			$connectService->connectMergeMember();

			if($_G['member']['conuin'] && $_G['member']['conuinsecret']) {

				$arr = array();
				$arr['oauth_consumer_key'] = $_G['setting']['connectappid'];
				$arr['oauth_nonce'] = mt_rand();
				$arr['oauth_timestamp'] = TIMESTAMP;
				$arr['oauth_signature_method'] = 'HMAC_SHA1';
				$arr['oauth_token'] = $_G['member']['conuin'];
				ksort($arr);
				$arr['oauth_signature'] = $connectService->connectGetOauthSignature('http://api.discuz.qq.com/connect/getSignature', $arr, 'GET', $_G['member']['conuinsecret']);

				$arr['version'] = 'qzone1.0';

				$utilService = Cloud::loadClass('Service_Util');
				$result = $connectService->connectOutputPhp('http://api.discuz.qq.com/connect/getSignature?' . $utilService->httpBuildQuery($arr, '', '&'));
				if($result['status'] == 0) {
					$js = 'a.onclick = function () { seditor_insertunit(\'sightml\', \'[wb='.$result['result']['username'].']'.$result['result']['signature_url'].'[/wb]\'); };';
				} else {
					$js = 'a.onclick = function () { showDialog(\''.lang('plugin/qqconnect', 'connect_wbsign_no_account').'\'); };';
				}
			} else {
				$js = 'a.onclick = function () { showDialog(\''.lang('plugin/qqconnect', 'connect_wbsign_no_bind').'\'); };';
			}
			return '<script type="text/javascript">if($(\'sightmlsml\')) {'.
				'var a = document.createElement(\'a\');a.href = \'javascript:;\';a.style.background = \'url(\' + STATICURL + \'image/common/weibo.png) no-repeat 0 2px\';'.
				'a.onmouseover = function () { showTip(this); };a.setAttribute(\'tip\', \''.lang('plugin/qqconnect', 'connect_wbsign_tip').'\');'.
				$js.
				'$(\'sightmlsml\').parentNode.appendChild(a);'.
				'}</script>';

		}

	}
}

?>