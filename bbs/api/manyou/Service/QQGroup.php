<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: QQGroup.php 28369 2012-02-28 07:54:27Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_QQGroup {
	protected static $util;
	protected static $siteId;
	protected static $siteKey;

	protected static $_instance;

	public static function getInstance() {
		global $_G;

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
			self::$siteId = $_G['setting']['my_siteid'];
			self::$siteKey = $_G['setting']['my_sitekey'];
			self::$util = Cloud::loadClass('Service_Util');
		}

		return self::$_instance;
	}

	public function iframeUrl($tid, $title, $content) {
		global $_G;
		if (!$_G['adminid']) {
			return false;
        }
        $title = rawurlencode($title);
        $content = rawurlencode($content);

		$url = 'http://qun.discuz.qq.com/feed/push?';
		$params = array(
			's_id' => self::$siteId,
            't_id' => $tid,
            's_url' => $_G['siteurl'],
        );
        $signUrl = self::$util->generateSiteSignUrl($params);

		return $url . 'title=' . $title . '&content=' . $content . '&' . $signUrl;
	}
}