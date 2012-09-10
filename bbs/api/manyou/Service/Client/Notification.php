<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Notification.php 28251 2012-02-27 01:40:15Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Notification extends Cloud_Service_Client_Restful {

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
	public function add($siteUid, $pkId, $type, $authorId, $author, $fromId, $fromIdType, $note, $fromNum, $dateline) {
		$openId = $this->getUserOpenId($siteUid);
		if($openId) {
			$_params = array(
					'openid' => $openId,
					'sSiteUid' => $siteUid,
					'pkId' => $pkId,
					'type' => $type,
					'authorId' => $authorId,
					'author' => $author,
					'fromId' => $fromId,
					'fromIdType' => $fromIdType,
					'fromNum' => $fromNum,
					'content' => $note,
					'dateline' => $dateline
			);
            return $this->_callMethod('connect.discuz.notification.add', $_params);
		}
		return false;
    }

    public function update($siteUid, $pkId, $fromNum, $dateline) {
    	$openId = $this->getUserOpenId($siteUid);
    	if($openId) {
    		$_params = array(
    				'openid' => $openId,
    				'sSiteUid' => $siteUid,
    				'pkId' => $pkId,
    				'fromNum' => $fromNum,
    				'dateline' => $dateline
    		);
    		return $this->_callMethod('connect.discuz.notification.update', $_params);
    	}
    	return false;
    }

	public function setNoticeFlag($siteUid, $dateline) {
		$openId = $this->getUserOpenId($siteUid);
		if($openId) {
			$_params = array(
					'openid' => $openId,
					'sSiteUid' => $siteUid,
					'dateline' => $dateline
			);
            return $this->_callMethod('connect.discuz.notification.read', $_params);
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