<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: check.php 28418 2012-02-29 07:31:47Z monkey $
 */

chdir('../../../');

define('APPTYPEID', 127);
define('CURSCRIPT', 'plugin');

$_GET['mobile'] = 'no';

require './source/class/class_core.php';
require './source/plugin/mobile/mobile.class.php';
if(!defined('DISCUZ_VERSION')) {
    require './source/discuz_version.php';
}

$discuz = C::app();

$discuz->init();

$array = in_array('mobile', $_G['setting']['plugins']['available']) ? array(
	'discuzversion' => DISCUZ_VERSION,
	'charset' => CHARSET,
	'regname' => $_G['setting']['regname'],
	'qqconnect' => in_array('qqconnect', $_G['setting']['plugins']['available']) ? 1 : 0,
	'sitename' => $_G['setting']['bbname'],
	'mysiteid' => $_G['setting']['my_siteid'],
	'ucenterurl' => $_G['setting']['ucenterurl'],
) : array();
mobile_core::result($array);

?>