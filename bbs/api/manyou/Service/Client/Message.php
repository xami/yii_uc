<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Message.php 28251 2012-02-27 01:40:15Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Message extends Cloud_Service_Client_Restful {

	protected static $_instance;

	public static function getInstance($debug = false) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($debug);
		}

		return self::$_instance;
	}

	public function __construct($debug = false) {

		return parent::__construct($debug);
	}

	public function add($siteUid, $authorId, $author, $dateline) {
		$toUids = array();
		if($siteUid) {
			foreach(C::t('#qqconnect#common_member_connect')->fetch_all((array)$siteUid) as $user) {
				$toUids[$user['conopenid']] = $user['uid'];
			}
			if($toUids) {
				$_params = array(
						'openidData' => $toUids,
						'authorId' => $authorId,
						'author' => $author,
						'dateline' => $dateline
					);
				return $this->_callMethod('connect.discuz.message.add', $_params);
			}
		}
		return false;
	}
	public function setMsgFlag($siteUid, $dateline) {
		$openId = $this->getUserOpenId($siteUid);
		if($openId) {
			$_params = array(
					'openid' => $openId,
					'sSiteUid' => $siteUid,
					'dateline' => $dateline
			);
			return $this->_callMethod('connect.discuz.message.read', $_params);
		}
		return false;
	}

	protected function _callMethod($method, $args) {
		try {
			return parent::_callMethod($method, $args);
		} catch (Exception $e) {

		}
	}
}

?>