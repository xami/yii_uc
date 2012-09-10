<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanfeed.php 24854 2011-10-13 02:43:23Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['setting']['feedday'] < 3) $_G['setting']['feedday'] = 3;
$deltime = $_G['timestamp'] - $_G['setting']['feedday']*3600*24;
$f_deltime = $_G['timestamp'] - 3*3600*24;

C::t('home_feed')->delete_by_dateline($deltime);
C::t('home_feed_app')->delete_by_dateline($f_deltime);
C::t('home_feed')->optimize_table();
C::t('home_feed_app')->optimize_table();

?>