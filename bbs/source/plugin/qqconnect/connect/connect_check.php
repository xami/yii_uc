<?php

/**
 *	  [Discuz!] (C)2001-2009 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: connect_check.php 26208 2011-12-05 12:18:58Z houdelei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$utilService = Cloud::loadClass('Service_Util');

$op = !empty($_GET['op']) ? $_GET['op'] : '';
if (!in_array($op, array('token', 'cookie'))) {
	$connectService->connectAjaxOuputMessage('0', '1');
}

if ($op == 'token') {

	$connectService->connectMergeMember();

	if($_G['setting']['connect']['allow'] && !$_G['cookie']['connect_check_token'] && $_G['member']['conuinsecret']) {

		dsetcookie('connect_check_token', '1', 86400);

		$api_url = $_G['connect']['api_url'] . '/connect/discuz/validateToken';

		$extra = array(
			'oauth_token' => $_G['member']['conuin'],
		);
		$sig_params = $connectService->connectGetOauthSignatureParams($extra);
		$oauth_token_secret = $_G['member']['conuinsecret'];
		$sig_params['oauth_signature'] = $connectService->connectGetOauthSignature($api_url, $sig_params, 'POST', $oauth_token_secret);

		$params = array(
			'client_ip' => $_G['clientip'],
			'response_type' => 'PHP',
			'version' => 'qzone1.0',
		);

		$params = array_merge($sig_params, $params);
		$response = $connectService->connectOutputPhp($api_url . '?', $utilService->httpBuildQuery($params, '', '&'));
		if(isset($response['status']) && ($response['status'] === 2024 || $response['status'] === 2025)) {

			DB::query('UPDATE '.DB::table('common_member_connect')." SET conuinsecret='' WHERE conopenid='".$_G['member']['conopenid']."'");

			$connectService->connectAjaxOuputMessage('2', '0');
		}
	}

	$connectService->connectAjaxOuputMessage('0', '2');

} elseif ($op == 'cookie') {

	$now = time();
	$life = 86400;
	$response = '';
	$api_url = $_G['connect']['api_url'].'/connect/discuz/cookieReport';
	$params = $connectService->connectCookieLoginParams();

	if($params) {
		$last_report_time = getcookie('connect_last_report_time');
        $current_date = date('Y-m-d');

        if(getcookie('connect_report_times')) {
            $retry = intval(getcookie('connect_report_times'));
        } else {
            $retry = 1;
        }

        if($last_report_time == $current_date) {
            if($retry <= 4) {
                $response = $connectService->connectOutputPhp($api_url.'?', $utilService->httpBuildQuery($params, '', '&'));
                $retry++;
            }
        } else {
            $response = $connectService->connectOutputPhp($api_url.'?', $utilService->httpBuildQuery($params, '', '&'));
            dsetcookie('connect_last_report_time', $current_date, $life);
            $retry = 1;
        }

        if($response['status'] === 0) {
        	$retry = 5;
        }

		dsetcookie('connect_report_times', $retry, $life);
	}
}
?>