<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Connect.php 26480 2011-12-13 12:07:38Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Connect extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onConnectSetConfig($data) {
		global $_G;

		$settingFields = array('connectappid', 'connectappkey');
		if (!$data) {
			return false;
		}

		$connectData = $_G['setting']['connect'];
		if (!is_array($connectData)) {
			$connectData = array();
		}

		$settings = array();
		foreach($data as $k => $v) {
			if (in_array($k, $settingFields)) {
				$settings[$k] = $v;
			} else {
				$connectData[$k] = $v;
			}
		}
		if ($connectData) {
			$settings['connect'] = $connectData;
		}

		if ($settings) {
			C::t('common_setting')->update_batch($settings);
			return true;
		}
		return false;
	}

}