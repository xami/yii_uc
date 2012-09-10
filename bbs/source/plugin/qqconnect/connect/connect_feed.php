<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: connect_feed.php 28020 2012-02-21 02:13:11Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$params = $_GET;
$op = !empty($_GET['op']) ? $_GET['op'] : '';
if (!in_array($op, array('new'))) {
	$connectService->connectJsOutputMessage('', 'undefined_action', 1);
}

$tid = trim(intval($_GET['thread_id']));
if (empty($tid)) {
	$connectService->connectJsOutputMessage('', 'connect_thread_id_miss', 1);
}

if ($op == 'new') {

	$connectService->connectMergeMember();

	$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid, 0);
	$thread = C::t('forum_thread')->fetch_by_tid_displayorder($tid, 0);

	$f_type = trim(intval($_GET['type']));

	$html_content = $connectService->connectParseBbcode($post['message'], $thread['fid'], $post['pid'], $post['htmlon'], $attach_images);

	if($_G['setting']['rewritestatus'] && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
		$url = rewriteoutput('forum_viewthread', 1, $_G['siteurl'], $tid);
	} else {
		$url = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid;
	}
	$qzone_params = array(
		'title' => $thread['subject'],
		'url' => $url,
		'summary' => $html_content,
		'nswb' => '1',
	);

	$t_params = array(
		'content' => $thread['subject'].' '.$url,
	);

	if($attach_images && is_array($attach_images)) {
		$attach_image = array_shift($attach_images);
		$qzone_params['images'] = $attach_image['big'];
		$t_params['pic'] = $attach_image['path'];
		$t_params['remote'] = $attach_image['remote'];
	}

	$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
	if(getstatus($f_type, 1)) {
		try {
			$connectOAuthClient->connectAddShare($_G['member']['conopenid'], $_G['member']['conuin'], $_G['member']['conuinsecret'], $qzone_params);
			if(!getstatus($thread['status'], 7)) {
				C::t('forum_thread')->update($tid, array('status' => setstatus(7, 1, $thread['status'])));
			}
			$f_type = setstatus(1, 0, $f_type);
		} catch(Exception $e) {
			if($e->getCode()) {
				$f_type = setstatus(1, 0, $f_type);
				$shareErrorCode = $e->getCode();
			}
		}
	}
	if(getstatus($f_type, 2)) {
		try {
			if ($t_params['pic']) {
				$method = 'connectAddPicT';
			} else {
				$method = 'connectAddT';
			}

			$response = $connectOAuthClient->$method($_G['member']['conopenid'], $_G['member']['conuin'], $_G['member']['conuinsecret'], $t_params);
			if($response['data']['id']) {
				if($_G['setting']['connect']['t']['reply'] && $thread['tid'] && !$thread['closed'] && !getstatus($thread['status'], 3)) {
					$conopenid = DB::result_first("SELECT conopenid FROM ".DB::table('common_member_connect')." WHERE uid='".$thread['authorid']."'");
					C::t('#qqconnect#connect_tthreadlog')->insert(array(
						'twid' => $response['data']['id'],
						'tid' => $tid,
						'conopenid' => $conopenid,
						'pagetime' => 0,
						'lasttwid' => '0',
						'nexttime' => $_G['timestamp'] + 30 * 60,
						'updatetime' => 0,
						'dateline' => $_G['timestamp'],
					));
				}
			}
			if(!getstatus($thread['status'], 8)) {
				C::t('forum_thread')->update($tid, array('status' => setstatus(8, 1, $thread['status'])));
			}
			$f_type = setstatus(2, 0, $f_type);
		} catch(Exception $e) {
			if($e->getCode()) {
				$f_type = setstatus(2, 0, $f_type);
				$weiboErrorCode = $e->getCode();
			}
		}
	}

	if(!$shareErrorCode && !$weiboErrorCode) {
		$connectService->connectJsOutputMessage(lang('connect', 'feed_sync_success'), '', 0);
	} else {
		if($f_type > 0) {
			dsetcookie('connect_js_name', 'feed_resend');
			dsetcookie('connect_js_params', base64_encode(serialize(array('type' => $f_type, 'thread_id' => $tid, 'ts' => TIMESTAMP))), 86400);
		}
		$connectService->connectJsOutputMessage('', '', $shareErrorCode.'|'.$weiboErrorCode);
	}

}
?>