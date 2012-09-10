<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: viewthread.php 28022 2012-02-21 03:27:54Z congyushuai $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'viewthread';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$variable = array(
			'thread' => mobile_core::getvalues($_G['thread'], array('tid', 'author', 'authorid', 'subject', 'views', 'replies', 'attachment', 'price', 'freemessage')),
			'fid' => $_G['fid'],
			'postlist' => array_values(mobile_core::getvalues($GLOBALS['postlist'], array('/^\d+$/'), array('pid', 'tid', 'author', 'first', 'dbdateline', 'dateline', 'useip', 'username', 'adminid', 'email', 'memberstatus', 'authorid', 'username', 'groupid', 'memberstatus', 'status', 'message', 'number', 'memberstatus', 'groupid', 'attachment', 'attachments', 'attachlist', 'imagelist'))),
			'ppp' => $_G['ppp'],
			'setting_rewriterule' => $_G['setting']['rewriterule'],
			'setting_rewritestatus' => $_G['setting']['rewritestatus'],
			'forum_threadpay' => $_G['forum_threadpay'],
			'cache_custominfo_postno' => $_G['cache']['custominfo']['postno'],
		);
		foreach($variable['$postlist'] as $k => $v) {
			$variable['$postlist'][$k]['attachments'] = array_values(mobile_core::getvalues($v['attachments'], array('/^\d+$/'), array('aid', 'tid', 'uid', 'dbdateline', 'dateline', 'filename', 'filesize', 'url', 'attachment', 'remote', 'description', 'readperm', 'price', 'width', 'thumb', 'picid', 'ext', 'imgalt', 'attachsize', 'payed', 'downloads')));
		}

		$variable['forum']['password'] = $variable['forum']['password'] ? 1 : 0;
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>