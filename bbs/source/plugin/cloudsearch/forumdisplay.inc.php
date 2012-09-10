<?php

/**
 *	  [Discuz! X] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: forumdisplay.inc.php 27241 2012-01-12 03:13:37Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = $_G['page'];

if($_GET['cloudop'] == 'relatedthread') {

	list($threadlist, $cloudData) = getCloudRelateThreads('tao', $_GET['keyword'], $page, $_G['tpp'], '', false);

	include_once template('cloudsearch:forumdisplay');

	$enKeyword = urlencode($_GET['keyword']);
	$threaddiv = str_replace(array("\r", "\n"), "", tpl_cloudsearch_forumdisplay_relatedlist($threadlist, $enKeyword));
	$multipage = str_replace(array("\r", "\n"), "", multi($cloudData['result']['total'], $_G['tpp'], $page, 'switchPage(', 0, 10, false, false, ');'));

	$_GET['handlekey'] = 'getRelatedData';
	showmessage('get_data_succ', dreferer(), array('threaddiv' => $threaddiv, 'page'=> $multipage), array('closetime' => '2', 'showmsg' => '1'));

} elseif($_GET['cloudop'] == 'relatelist') {

	list($threadlist, $cloudData) = getCloudRelateThreads(($_GET['fid'] ? 'forum' : 'tao'), $_GET['keyword'], $_G['page'], ($_GET['fid'] ? $_G['tpp'] : 5), ($_GET['fid'] ? $_GET['fid'] : ''), false);

	if ($_GET['fid']) {
		loadcache('forums');
	}

	include template('common/header_ajax');
	echo tpl_cloudsearch_relate_threadlist_js_output($threadlist, $_GET['fid'] ? urlencode(strip_tags($_G['cache']['forums'][$_GET['fid']]['name'])) : urlencode(strip_tags($_GET['keyword'])));
	include template('common/footer_ajax');
}

dexit();

function getCloudRelateThreads($api = 'tao', $keyword, $page, $tpp = 20, $excludeForumIds = '', $cache = false) {
	global $_G;

	$threadlist = array();
	$searchHelper = Cloud::loadClass('Service_SearchHelper');
	if($api != 'tao') {
		$cloudData = $searchHelper->getRelatedThreads($excludeForumIds);
	} else {
		$cloudData = $searchHelper->getRelatedThreadsTao($keyword, $page, $tpp, $disAd, $of, $ot, $cache, $excludeForumIds);
	}
	if($cloudData['result']['data']) {
		foreach ($cloudData['result']['ad']['content'] as $sAdv) {
			$threadlist[] = array('icon' => (string)$cloudData['result']['ad']['icon']) + $sAdv;
		}
		foreach ($cloudData['result']['data'] as $sPost) {
			$threadlist[] = $sPost;
		}
	} else {
		return null;
	}
	loadcache('forums');

	foreach($threadlist as $curtid=>&$curvalue) {
		$curvalue['pForumName'] = $_G['cache']['forums'][$curvalue['pForumId']]['name'];
		$curvalue['istoday'] = strtotime($curvalue['pPosted']) > $todaytime ? 1 : 0;
		$curvalue['dateline'] = $curvalue['pPosted'];
	}

	return array($threadlist, $cloudData);
}
?>