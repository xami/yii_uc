<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: feed_callback.inc.php 28108 2012-02-22 08:52:33Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$w = intval($_GET['w']) > 0 ? intval($_GET['w']) : 0;
$h = intval($_GET['h']) > 0 ? intval($_GET['h']) : 0;
$type = intval($_GET['type']) == 1 ? 1 : 2;

if ((!$w || !$h) && ($type != 1)) {
	showmessage('undefined_action');
}

include template('qqgroup:resize');