<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Restful.php 28361 2012-02-28 07:12:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_RestfulException');

abstract class Cloud_Service_Client_Restful {

	protected $_cloudApiIp = '';

	protected $_sId = 0;

	protected $_sKey = '';

	protected $_url = 'http://api.discuz.qq.com/site.php';

	protected $_format = 'PHP';

	protected $_ts = 0;

	protected $_debug = false;

	public $errorCode = 0;

	public $errorMessage = '';

	public $my_status = false;
	public $cloud_status = false;

	public $siteName = '';
	public $uniqueId = '';
	public $siteUrl = '';
	public $charset = '';
	public $timeZone = 0;
	public $UCenterUrl = '';
	public $language = '';
	public $productType = '';
	public $productVersion = '';
	public $productRelease = '';
	public $apiVersion = '';
	public $siteUid = 0;

	protected static $_instance;

	public static function getInstance($debug = false) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($debug);
		}

		return self::$_instance;
	}

	public function __construct($debug = false) {

		$this->_debug = $debug;

		$this->_initSiteEnv();
	}

	protected function _initSiteEnv() {

		global $_G;

		require_once DISCUZ_ROOT.'./source/discuz_version.php';

		$this->my_status = !empty($_G['setting']['my_app_status']) ? $_G['setting']['my_app_status'] : '';
		$this->cloud_status = !empty($_G['setting']['cloud_status']) ? $_G['setting']['cloud_status'] : '';

		$this->_sId = !empty($_G['setting']['my_siteid']) ? $_G['setting']['my_siteid'] : '';
		$this->_sKey = !empty($_G['setting']['my_sitekey']) ? $_G['setting']['my_sitekey'] : '';
		$this->_ts = TIMESTAMP;

		$this->siteName = !empty($_G['setting']['bbname']) ? $_G['setting']['bbname'] : '';

		$this->uniqueId = $_G['setting']['siteuniqueid'];
		$this->siteUrl = $_G['siteurl'];
		$this->charset = CHARSET;
		$this->timeZone = !empty($_G['setting']['timeoffset']) ? $_G['setting']['timeoffset'] : '';
		$this->UCenterUrl = !empty($_G['setting']['ucenterurl']) ? $_G['setting']['ucenterurl'] : '';
		$this->language = $_G['config']['output']['language'] ? $_G['config']['output']['language'] : 'zh_CN';
		$this->productType = 'DISCUZX';
		$this->productVersion = defined('DISCUZ_VERSION') ? DISCUZ_VERSION : '';
		$this->productRelease = defined('DISCUZ_RELEASE') ? DISCUZ_RELEASE : '';

		$utilService = Cloud::loadClass('Service_Util');
		$this->apiVersion = $utilService->getApiVersion();

		$this->siteUid = $_G['uid'];

		if ($_G['setting']['cloud_api_ip']) {
			$this->setCloudApiIp($_G['setting']['cloud_api_ip']);
		}

	}

	protected function _callMethod($method, $args) {

		$this->errorCode = 0;
		$this->errorMessage = '';
		$url = $this->_url;

		$params = array();
		$params['sId'] = $this->_sId;
		$params['method'] = $method;
		$params['format'] = strtoupper($this->_format);

		$params['sig'] = $this->_generateSig($params, $method, $args);
		$params['ts'] = $this->_ts;

		$data = $this->_createPostString($params, $args, true);
		$result = $this->_postRequest($url, $data);
		if ($this->_debug) {
			$this->_message('receive data ' . htmlspecialchars($result) . "\n\n");
		}

		if ($result) {
			$result = @dunserialize($result);
			if(is_array($result) && array_key_exists('result', $result)) {
				if ($result['errCode']) {
					$this->errorCode = $result['errCode'];
					$this->errorMessage = $result['errMessage'];
					throw new Cloud_Service_Client_RestfulException($result['errMessage'], $result['errCode']);
				} else {
					return $result['result'];
				}
			} else {
				$this->_unknowErrorMessage();
			}
		} else {
			$this->_unknowErrorMessage();
		}
	}

	protected function _unknowErrorMessage() {
		$this->errorCode = 1;
		$this->errorMessage = 'An unknown error occurred. May be DNS Error. ';

		throw new Cloud_Service_Client_RestfulException($this->errorMessage, $this->errorCode);
	}

	protected function _generateSig($params, $method, $args) {

		$str = $this->_createPostString($params, $args, true);
		if ($this->_debug) {
			$this->_message('sig string: ' . $str . '|' . $this->_sKey . '|' . $this->_ts . "\n\n");
		}

		return md5(sprintf('%s|%s|%s', $str, $this->_sKey, $this->_ts));
	}

	protected function _createPostString($params, $args) {

		ksort($params);
		ksort($args);

		$params['args'] = $args;

		$utilService = Cloud::loadClass('Service_Util');
		$str = $utilService->httpBuildQuery($params, '', '&');

		return $str;
	}

	protected function _postRequest($url, $data, $ip = '') {
		if ($this->_debug) {
			$this->_message('post params: ' . $data. "\n\n");
		}

		$ip = $this->_cloudApiIp;

		$result = $this->_fsockopen($url, 4096, $data, '', false, $ip, 5);
		return $result;
	}

	function _fsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
		return dfsockopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
	}

	protected function _message($msg) {
		echo $msg;
	}

	public function setCloudApiIp($ip) {

		$this->_cloudApiIp = $ip;

		return true;
	}

	protected function getUserOpenId($uid) {
		$openId = '';
		$connectInfo = C::t('#qqconnect#common_member_connect')->fetch($uid);
		if($connectInfo) {
			$openId = $connectInfo['conopenid'];
		}
		return $openId;
	}

}