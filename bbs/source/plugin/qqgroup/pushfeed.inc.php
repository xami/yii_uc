<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: pushfeed.inc.php 28445 2012-03-01 03:08:33Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$service = Cloud::loadClass('Service_QQGroup');
$tid = intval($_GET['tid']);
$title = trim(daddslashes($_GET['title']));
$content = trim(daddslashes($_GET['content']));
if (!$tid) {
	showmessage('undefined_action');
}
$iframeUrl = $service->iframeUrl($tid, $title, $content);
include template('qqgroup:pushfeed');