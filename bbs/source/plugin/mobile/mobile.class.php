<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile.class.php 28449 2012-03-01 04:12:13Z monkey $
 */

class mobile_core {

	function result($result) {
		global $_G;
		ob_end_clean();
		function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
		echo mobile_core::json($result);
		exit;
	}

	function json($encode) {
		if(!empty($_GET['debug']) && defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
			return debug($encode);
		}
		require_once 'source/plugin/mobile/json.class.php';
		return CJSON::encode($encode);
	}

	function getvalues($variables, $keys, $subkeys = array()) {
		$return = array();
		foreach($variables as $key => $value) {
			foreach($keys as $k) {
				if($k{0} == '/' && preg_match($k, $key) || $key == $k) {
					if($subkeys) {
						$return[$key] = mobile_core::getvalues($value, $subkeys);
					} else {
						if(!empty($value) || !empty($_GET['debug']) || (is_numeric($value) && intval($value) === 0 )) {
							$return[$key] = $value;
						}
					}
				}
			}
		}
		return $return;
	}

	function variable($variables = array()) {
		global $_G;
		$globals = array(
			'cookiepre' => $_G['config']['cookie']['cookiepre'],
			'auth' => $_G['cookie']['auth'],
			'saltkey' => $_G['cookie']['saltkey'],
			'member_uid' => $_G['member']['uid'],
			'member_username' => $_G['member']['username'],
			'groupid' => $_G['groupid'],
			'formhash' => FORMHASH,
			'ismoderator' => $_G['forum']['ismoderator'],
		);
		if(!empty($_GET['submodule']) == 'checkpost') {
			$apifile = 'source/plugin/mobile/api/'.$_GET['version'].'/sub_checkpost.php';
			if(file_exists($apifile)) {
				require_once $apifile;
				$globals = $globals + mobile_api_sub::getvariable();
			}
		}
		$xml = array(
			'Version' => '2',
			'Charset' => strtoupper($_G['charset']),
			'Variables' => array_merge($globals, $variables),
		);
		if(!empty($_G['messageparam'])) {
			$message_result = lang('plugin/mobile', $_G['messageparam'][0], $_G['messageparam'][2]);
			if($message_result == $_G['messageparam'][0]) {
				$vars = explode(':', $_G['messageparam'][0]);
				if (count($vars) == 2) {
					$message_result = lang('plugin/' . $vars[0], $vars[1], $_G['messageparam'][2]);
				} else {
					$message_result = lang('message', $_G['messageparam'][0], $_G['messageparam'][2]);
				}
			}
			$message_result = strip_tags($message_result);
			if($_G['messageparam'][4]) {
				$_G['messageparam'][0] = "custom";
			}
			if ($_G['messageparam'][3]['login'] && !$_G['uid']) {
				$_G['messageparam'][0] .= '//' . $_G['messageparam'][3]['login'];
			}
			$xml['Message'] = array("messageval" => $_G['messageparam'][0], "messagestr" => $message_result);
			if($_GET['mobilemessage']) {
				$return = mobile_core::json($xml);
				header("HTTP/1.1 301 Moved Permanently");
				header("Location:discuz://" . $_G['messageparam'][0] . "//" . rawurlencode(diconv($message_result, $_G['charset'], "utf-8")) . ($return ? "//" . rawurlencode($return) : '' ));
				exit;
			}
		}
		return $xml;
	}

}

class base_plugin_mobile {

	function common() {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		global $_G;
		if(!empty($_GET['tpp'])) {
			$_G['tpp'] = intval($_GET['tpp']);
		}
		if(!empty($_GET['ppp'])) {
			$_G['ppp'] = intval($_GET['ppp']);
		}
		$_G['setting']['msgforward'] = '';
		$_G['setting']['cacheindexlife'] = $_G['setting']['cachethreadlife'] = false;
		if(class_exists('mobile_api', 'common')) {
			mobile_api::common();
		}
	}

	function global_mobile() {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		if(class_exists('mobile_api', 'output')) {
			mobile_api::output();
		}
	}

}

class base_plugin_mobile_forum extends base_plugin_mobile {

	function post_mobile_message($param) {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		if(class_exists('mobile_api', 'post_mobile_message')) {
			list($message, $url_forward, $values, $extraparam, $custom) = $param['param'];
			mobile_api::post_mobile_message($message, $url_forward, $values, $extraparam, $custom);
		}
	}

}

class base_plugin_mobile_misc extends base_plugin_mobile {

	function mobile() {
		global $_G;
		if(empty($_GET['view']) && !defined('MOBILE_API_OUTPUT')) {
			$_G['setting']['pluginhooks'] = array();
			$qrfile = DISCUZ_ROOT.'./data/cache/mobile_siteqrcode.png';
			if(!file_exists($qrfile) || $_G['adminid'] == 1) {
				require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
				QRcode::png($_G['siteurl'], $qrfile);
			}
			define('MOBILE_API_OUTPUT', 1);
			$_G['disabledwidthauto'] = 1;
			include template('mobile:mobile');exit;
		}
	}

}

class plugin_mobile extends base_plugin_mobile {}
class plugin_mobile_forum extends base_plugin_mobile_forum {}
class plugin_mobile_misc extends base_plugin_mobile_misc {}
class mobileplugin_mobile extends base_plugin_mobile {
	function global_header_mobile() {
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'iphone') !== -1 || strpos($useragent, 'ios') !== -1) {
			return lang('plugin/mobile', 'mobile_tip_ios');
		} elseif(strpos($useragent, 'android') !== -1) {
			return lang('plugin/mobile', 'mobile_tip_android');
		} elseif(strpos($useragent, 'windows phone') !== -1) {
			return lang('plugin/mobile', 'mobile_tip_wp7');
		}
	}
}
class mobileplugin_mobile_forum extends base_plugin_mobile_forum {}
class mobileplugin_mobile_misc extends base_plugin_mobile_misc {}

class plugin_mobile_connect extends plugin_mobile {

	function login_mobile_output($param) {
	}

	function login_mobile_message($param) {
		if(substr($_GET['referer'], 0, 7) == 'Mobile_') {
			if($_GET['referer'] == 'Mobile_iOS' || $_GET['referer'] == 'Mobile_Android') {
				$_GET['mobilemessage'] = 1;
			}
			mobile_core::result(mobile_core::variable());
		}
	}

}

?>