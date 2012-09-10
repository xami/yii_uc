<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Connect.php 27709 2012-02-13 03:13:04Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Connect {

	protected static $_instance;

	public $state = '';

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct($siteId = '', $siteKey = '') {
	}

	public function connectMergeMember() {
		global $_G;
		static $merged;
		if($merged) {
			return;
		}

		$connect_member = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
		if ($connect_member) {
			$_G['member'] = array_merge($_G['member'], $connect_member);
			$user_auth_fields = $connect_member['conisfeed'];
			if ($user_auth_fields == 0) {
				$_G['member']['is_user_info'] = 0;
				$_G['member']['is_feed'] = 0;
			} elseif ($user_auth_fields == 1) {
				$_G['member']['is_user_info'] = 1;
				$_G['member']['is_feed'] = 1;
			} elseif ($user_auth_fields == 2) {
				$_G['member']['is_user_info'] = 1;
				$_G['member']['is_feed'] = 0;
			} elseif ($user_auth_fields == 3) {
				$_G['member']['is_user_info'] = 0;
				$_G['member']['is_feed'] = 1;
			}
			unset($connect_member, $_G['member']['conisfeed']);
		}
		$merged = true;
	}

	public function connectUserBindJs($params) {
		global $_G;

		$jsname = $_G['cookie']['connect_js_name'];
		if($jsname != 'user_bind') {
			return false;
		}

		$jsparams = dunserialize(base64_decode($_G['cookie']['connect_js_params']));
		$jsurl = $_G['connect']['url'].'/notify/user/bind';

		if($jsparams) {
			$params = array_merge($params, $jsparams);
		}

		$func = 'connect'.'UserBind'.'Params';
		$other_params = $this->$func();
		$params = array_merge($other_params, $params);
		$params['sig'] = $this->connectGetSig($params, $this->connectGetSigKey());

		$utilService = Cloud::loadClass('Service_Util');
		$jsurl .= '?' . $utilService->httpBuildQuery($params, '', '&');

		return $jsurl;
	}

	function connectUserBindParams() {
		global $_G;

		$this->connectMergeMember();
		getuserprofile('birthyear');
		getuserprofile('birthmonth');
		getuserprofile('birthday');
		switch ($_G['member']['gender']) {
			case 1 :
				$sex = 'male';
				break;
			case 2 :
				$sex = 'female';
				break;
			default :
				$sex = 'unknown';
		}

		$is_public_email = 2;
		$is_use_qq_avatar = $_G['member']['conisqzoneavatar'] == 1 ? 1 : 2;
		$birthday = sprintf('%04d', $_G['member']['birthyear']).'-'.sprintf('%02d', $_G['member']['birthmonth']).'-'.sprintf('%02d', $_G['member']['birthday']);

		$agent = md5(time().rand().uniqid());
		$inputArray = array (
			'uid' => $_G['uid'],
			'agent' => $agent,
			'time' => TIMESTAMP
		);
		require_once DISCUZ_ROOT.'./config/config_ucenter.php';
		$input = 'uid='.$_G['uid'].'&agent='.$agent.'&time='.TIMESTAMP;
		$avatar_input = authcode($input, 'ENCODE', UC_KEY);

		$params = array (
			'oauth_consumer_key' => $_G['setting']['connectappid'],
			'u_id' => $_G['uid'],
			'username' => $_G['member']['username'],
			'email' => $_G['member']['email'],
			'birthday' => $birthday,
			'sex' => $sex,
			'is_public_email' => $is_public_email,
			'is_use_qq_avatar' => $is_use_qq_avatar,
			's_id' => null,
			'avatar_input' => $avatar_input,
			'avatar_agent' => $agent,
			'site_ucenter_id' => UC_APPID,
			'source' => 'qzone',
		);

		return $params;
	}

	function connectFeedResendJs() {
		global $_G;

		$jsname = $_G['cookie']['connect_js_name'];
		if($jsname != 'feed_resend') {
			return false;
		}

		$params = dunserialize(base64_decode($_G['cookie']['connect_js_params']));
		$params['sig'] = $this->connectGetSig($params, $this->connectGetSigKey());

		$jsurl = $_G['connect']['discuz_new_feed_url'];
		$utilService = Cloud::loadClass('Service_Util');
		$jsurl .= '?' . $utilService->httpBuildQuery($params, '', '&');

		return $jsurl;
	}


	function connectGetSigKey() {
		global $_G;

		return $_G['setting']['connectappid'] . '|' . $_G['setting']['connectappkey'];
	}


	function connectGetSig($params, $app_key) {
		ksort($params);
		$base_string = '';
		foreach($params as $key => $value) {
			$base_string .= $key.'='.$value;
		}
		$base_string .= $app_key;
		return md5($base_string);
	}

	function connectParseBbcode($bbcode, $fId, $pId, $isHtml, &$attachImages) {
		include_once libfile('function/discuzcode');

		$result = preg_replace('/\[hide(=\d+)?\].+?\[\/hide\](\r\n|\n|\r)/i', '', $bbcode);
		$result = preg_replace('/\[payto(=\d+)?\].+?\[\/payto\](\r\n|\n|\r)/i', '', $result);
		$result = discuzcode($result, 0, 0, $isHtml, 1, 2, 1, 0, 0, 0, 0, 1, 0);
		$result = strip_tags($result, '<img><a>');
		$result = preg_replace('/<img src="images\//i', "<img src=\"".$_G['siteurl']."images/", $result);
		$result = $this->connectParseAttach($result, $fId, $pId, $attachImages, $attachImageThumb);
		return $result;
	}

	function connectParseAttach($content, $fId, $pId, &$attachImages) {
		global $_G;

		$attachIds = array();
		$attachImages = array ();
		$query = DB :: query("SELECT aid, remote, attachment, filename, isimage, readperm, price FROM ".DB :: table(getattachtablebypid($pId))." WHERE pid='$pId'");
		while ($attach = DB :: fetch($query)) {
			$aid = $attach['aid'];
			if($attach['isimage'] == 0 || $attach['price'] > 0 || $attach['readperm'] > 0 || in_array($attach['aid'], $attachIds)) {
				continue;
			}

			$imageItem = array ();
			$thumbWidth = '100';
			$thumbHeight = '100';
			$bigWidth = '400';
			$bigHeight = '400';
			$key = md5($aid.'|'.$thumbWidth.'|'.$thumbHeight);
			$thumbImageURL = $_G['siteurl'] . 'forum.php?mod=image&aid='.$aid.'&size='.$thumbWidth.'x'.$thumbHeight.'&key='.rawurlencode($key).'&type=fixwr&nocache=1';
			$key = md5($aid.'|'.$bigWidth.'|'.$bigHeight);
			$bigImageURL = $_G['siteurl'] . 'forum.php?mod=image&aid='.$aid.'&size='.$bigWidth.'x'.$bigHeight.'&key='.rawurlencode($key).'&type=fixnone&nocache=1';
			$imageItem['aid'] = $aid;
			$imageItem['thumb'] = $thumbImageURL;
			$imageItem['big'] = $bigImageURL;
			if($attach['remote']) {
				$imageItem['path'] = $_G['setting']['ftp']['attachurl'].'forum/'.$attach['attachment'];
				$imageItem['remote'] = true;
			} else {
				$imageItem['path'] = $_G['setting']['attachdir'].'forum/'.$attach['attachment'];
			}

			$attachIds[] = $aid;
			$attachImages[] = $imageItem;
		}
		$content = preg_replace('/\[attach\](\d+)\[\/attach\]/ie', '$this->connectParseAttachTag(\\1, $attachNames)', $content);
		return $content;
	}

	function connectParseAttachTag($attachId, $attachNames) {
		include_once libfile('function/discuzcode');
		if(array_key_exists($attachId, $attachNames)) {
			return '<span class="attach"><a href="'.$_G['siteurl'].'/attachment.php?aid='.aidencode($attachId).'">'.$attachNames[$attachId].'</a></span>';
		}
		return '';
	}

	function connectOutputPhp($url, $postData = '') {
		global $_G;

		$response = dfsockopen($url, 0, $postData, '', false, $_G['setting']['cloud_api_ip']);
		$result = (array) dunserialize($response);
		return $result;
	}

	function connectJsOutputMessage($msg = '', $errMsg = '', $errCode = '') {
		$result = array (
			'result' => $msg,
			'errMessage' => $errMsg,
			'errCode' => $errCode
		);
		echo sprintf('con_handle_response(%s);', json_encode($this->_connectUrlencode($result)));
		exit;
	}

	function _connectUrlencode($value) {

		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$value[$k] = $this->_connectUrlencode($v);
			}
		} else if (is_string($value)) {
			$value = urlencode(str_replace(array("\r\n", "\r", "\n", "\"", "\/", "\t"), array('\\n', '\\n', '\\n', '\\"', '\\/', '\\t'), $value));
		}

		return $value;
	}

	function connectCookieLoginParams() {
		global $_G;

		$this->connectMergeMember();
		$oauthToken = $_G['member']['conuin'];
		$api_url = $_G['connect']['api_url'].'/connect/discuz/cookieReport';

		if($oauthToken) {
			$extra = array (
				'oauth_token' => $oauthToken
			);

			$sig_params = $this->connectGetOauthSignatureParams($extra);

			$oauth_token_secret = $_G['member']['conuinsecret'];
			$sig_params['oauth_signature'] = $this->connectGetOauthSignature($api_url, $sig_params, 'POST', $oauth_token_secret);
			$params = array (
				'client_ip' => $_G['clientip'],
				'u_id' => $_G['uid'],
				'version' => 'qzone1.0',
			);

			$params = array_merge($sig_params, $params);
			$params['response_type'] = 'php';

			return $params;
		} else {
			return false;
		}
	}

	function connectAjaxOuputMessage($msg = '', $errCode = '') {

		@header("Content-type: text/html; charset=".CHARSET);

		echo "errCode=$errCode&result=$msg";
		exit;
	}

	function connectUserUnbind($uin, $secet, $client_ip) {
		global $_G;

		$api_url = $_G['connect']['api_url'].'/connect/user/unbind';

		$params = array (
			'oauth_consumer_key' => $_G['setting']['connectappid'],
			'client_ip' => $_G['clientip'],
			'response_type' => 'php',
			'openid' => $_G['member']['conopenid'],
			'source' => 'qzone',
		);

		$params['sig'] = $this->connectGetSig($params, $this->connectGetSigKey());
		$arr['version'] = 'qzone1.0';

		$utilService = Cloud::loadClass('Service_Util');
		$response = $this->connectOutputPhp($api_url.'?', $utilService->httpBuildQuery($params, '', '&'));
		return $response;
	}


	function connectGetOauthSignature($url, $params, $method = 'POST', $oauth_token_secret = '') {

		global $_G;

		$method = strtoupper($method);
		if(!in_array($method, array ('GET', 'POST'))) {
			return FALSE;
		}

		$url = urlencode($url);

		$utilService = Cloud::loadClass('Service_Util');
		$param_str = urlencode($utilService->httpBuildQuery($params, '', '&'));

		$base_string = $method.'&'.$url.'&'.$param_str;

		$key = $_G['setting']['connectappkey'].'&'.$oauth_token_secret;

		$signature = $utilService->_hash_hmac('sha1', $base_string, $key);

		return $signature;
	}

	function connectGetOauthSignatureParams($extra = array ()) {
		global $_G;

		$params = array (
			'oauth_consumer_key' => $_G['setting']['connectappid'],
			'oauth_nonce' => $this->_connectGetNonce(),
			'oauth_signature_method' => 'HMAC_SHA1',
			'oauth_timestamp' => TIMESTAMP
		);
		if($extra) {
			$params = array_merge($params, $extra);
		}
		ksort($params);

		return $params;
	}

	function _connectCustomHmac($algo, $data, $key, $raw_output = false) {
		$algo = strtolower($algo);
		$pack = 'H'.strlen($algo ('test'));
		$size = 64;
		$opad = str_repeat(chr(0x5C), $size);
		$ipad = str_repeat(chr(0x36), $size);

		if(strlen($key) > $size) {
			$key = str_pad(pack($pack, $algo ($key)), $size, chr(0x00));
		} else {
			$key = str_pad($key, $size, chr(0x00));
		}

		for ($i = 0; $i < strlen($key) - 1; $i++) {
			$opad[$i] = $opad[$i] ^ $key[$i];
			$ipad[$i] = $ipad[$i] ^ $key[$i];
		}

		$output = $algo ($opad.pack($pack, $algo ($ipad.$data)));

		return ($raw_output) ? pack($pack, $output) : $output;
	}

	function _connectGetNonce() {
		$mt = microtime();
		$rand = mt_rand();

		return md5($mt.$rand);
	}

	function connectParseXml($contents, $getAttributes = true, $priority = 'tag') {
		if (!$contents) {
			return array();
		}

		if (!function_exists('xml_parser_create')) {
			return array();
		}

		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xmlValues);
		xml_parser_free($parser);

		if (!$xmlValues) {
			return;
		}

		$xmlArray = $parent = array();

		$current = &$xmlArray;
		$repeatedTagIndex = array();

		foreach($xmlValues as $data) {
			unset($attributes, $value);
			extract($data);

			$result = $attributesData = array();

			if (isset($value)) {
				if ($priority == 'tag') {
					$result = $value;
				} else {
					$result['value'] = $value;
				}
			}

			if (isset($attributes) && $getAttributes) {
				foreach ($attributes as $attr => $val) {
					if ($priority == 'tag') {
						$attributesData[$attr] = $val;
					} else {
						$result['attr'][$attr] = $val;
					}
				}
			}

			if ($type == 'open') {
				$parent[$level - 1] = &$current;
				if (!is_array($current) || (!in_array($tag, array_keys($current)))) {
					$current[$tag] = $result;
					if ($attributesData) {
						$current[$tag . '_attr'] = $attributesData;
					}
					$repeatedTagIndex[$tag . '_' . $level] = 1;
					$current = &$current[$tag];
				} else {
					if (isset($current[$tag][0])) {
						$current[$tag][$repeatedTagIndex[$tag . '_' . $level]] = $result;
						$repeatedTagIndex[$tag . '_' . $level] ++;
					} else {
						$current[$tag] = array($current[$tag], $result);
						$repeatedTagIndex[$tag . '_' . $level] = 2;
						if (isset($current[$tag . '_attr'])) {
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset($current[$tag . '_attr']);
						}
					}
					$lastItemIndex = $repeatedTagIndex[$tag . '_' . $level] - 1;
					$current = &$current[$tag][$lastItemIndex];
				}
			} elseif($type == 'complete') {
				if (!isset($current[$tag])) {
					$current[$tag] = $result;
					$repeatedTagIndex[$tag . '_' . $level] = 1;
					if ($priority == 'tag' && $attributesData) {
						$current[$tag . '_attr'] = $attributesData;
					}
				} else {
					if (isset($current[$tag][0]) && is_array($current[$tag])) {
						$current[$tag][$repeatedTagIndex[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' && $getAttributes && $attributesData) {
							$current[$tag][$repeatedTagIndex[$tag . '_' . $level] . '_attr'] = $attributesData;
						}
						$repeatedTagIndex[$tag . '_' . $level] ++;
					} else {
						$current[$tag] = array($current[$tag], $result);
						$repeatedTagIndex[$tag . '_' . $level] = 1;
						if ($priority == 'tag' && $getAttributes) {
							if (isset($current[$tag . '_attr'])) {
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset($current[$tag . '_attr']);
							}
							if ($attributesData) {
								$current[$tag][$repeatedTagIndex[$tag . '_' . $level] . '_attr'] = $attributesData;
							}
						}
						$repeatedTagIndex[$tag . '_' . $level] ++;
					}
				}
			} elseif($type == 'close') {
				$current = &$parent[$level - 1];
			}
		}

		return $xmlArray[key($parent[0])] ? $xmlArray[key($parent[0])] : $xmlArray;
	}


	function connectFilterUsername($username) {
		$username = str_replace(' ', '_', trim($username));
		return cutstr($username, 15, '');
	}

	function connectErrlog($errno, $error) {
		global $_G;
		writelog('errorlog', $_G['timestamp']."\t[QQConnect]".$errno." ".$error);
	}
}

?>