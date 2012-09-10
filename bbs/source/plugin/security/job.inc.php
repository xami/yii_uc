<?php
/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: job.inc.php 26819 2011-12-23 09:17:49Z songlixin $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if ($_POST['formhash'] != formhash()) {
    exit('Access Denied');
}

$securityService = Cloud::loadClass('Service_Security');
$securityService->retryReportData('3');



?>