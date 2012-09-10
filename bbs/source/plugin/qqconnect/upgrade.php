<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: upgrade.php 26543 2011-12-15 02:17:57Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = '';

if($_GET['fromversion'] <= '1.04') {

	$connect = $_G['setting']['connect'];
	$connect['t']['reply'] = 1;
	$connect['t']['reply_showauthor'] = 1;
	$connect = serialize($connect);

	$sql .= <<<EOF

	CREATE TABLE IF NOT EXISTS pre_connect_tthreadlog (
	  twid char(16) NOT NULL,
	  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
	  conopenid char(32) NOT NULL,
	  pagetime int(10) unsigned DEFAULT '0',
	  lasttwid char(16) DEFAULT NULL,
	  nexttime int(10) unsigned DEFAULT '0',
	  updatetime int(10) unsigned DEFAULT '0',
	  dateline int(10) unsigned DEFAULT '0',
	  PRIMARY KEY (twid),
	  KEY nexttime (tid,nexttime),
	  KEY updatetime (tid,updatetime)
	) TYPE=MyISAM;

	REPLACE INTO pre_common_setting VALUES ('connect', '{$connect}');

	ALTER TABLE pre_common_member_connect ADD COLUMN `conisqqshow` tinyint(1) unsigned NOT NULL default '0';

EOF;

}

if($sql) {
	runquery($sql);
}

$finish = true;

?>