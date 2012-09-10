<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ftn_upload.inc.php 27316 2012-01-16 03:08:11Z songlixin $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$storageService = Cloud::loadClass('Service_Storage');

$formhash = formhash();
if($_GET['formhash'] == $formhash && $_GET['inajax']){
	$ftnFormhash = $storageService->ftnFormhash();
	$iframeurl = $storageService->makeIframeUrl($ftnFormhash);
} else {
	$iframeurl = '';
}

include template('xf_storage:upload');

?>