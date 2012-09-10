<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_union.php 25510 2011-11-14 02:22:26Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!$_G['inajax']) {
	cpheader();
	shownav('navcloud', 'cloud_stats');
}

$unionDomain = 'http://union.discuz.qq.com';
$utilService = Cloud::loadClass('Service_Util');
$signUrl = $utilService->generateSiteSignUrl();

$utilService->redirect($unionDomain . '/site/application/?' . $signUrl);