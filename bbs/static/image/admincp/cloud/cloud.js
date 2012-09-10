function submitForm() {

	if (dialogHtml == '') {
		dialogHtml = $('siteInfo').innerHTML;
		$('siteInfo').innerHTML = '';
	}

	showWindow('open_cloud', dialogHtml, 'html');

	$('fwin_open_cloud').style.top = '80px';
	$('cloud_api_ip').value = cloudApiIp;

	return false;
}

function dealHandle(msg) {

	getMsg = true;

	if (msg['status'] == 'error') {
		$('loadinginner').innerHTML = '<font color="red">' + msg['content'] + '</font>';
		return;
	}

	$('loading').style.display = 'none';
	$('mainArea').style.display = '';

	if(cloudStatus == 'upgrade') {
		$('title').innerHTML = msg['cloudIntroduction']['upgrade_title'];
		$('msg').innerHTML = msg['cloudIntroduction']['upgrade_content'];
	} else {
		$('title').innerHTML = msg['cloudIntroduction']['open_title'];
		$('msg').innerHTML = msg['cloudIntroduction']['open_content'];
	}

	if (msg['navSteps']) {
		$('nav_steps').innerHTML = msg['navSteps'];
	}

	if (msg['protocalUrl']) {
		$('protocal_url').href = msg['protocalUrl'];
	}

	if (msg['cloudApiIp']) {
		cloudApiIp = msg['cloudApiIp'];
	}

	if (msg['manyouUpdateTips']) {
		$('manyou_update_tips').innerHTML = msg['manyouUpdateTips'];
	}
}

function expiration() {

	if(!getMsg) {
		$('loadinginner').innerHTML = '<font color="red">' + expirationText + '</font>';
		clearTimeout(expirationTimeout);
	}
}

function apiCallback(apiIps) {

	if (typeof apiIps == 'undefined' || typeof apiIps == 'null' || !apiIps) {
		return false;
	}

	if (apiIps.errorCode) {
		return false;
	}

	if (!apiIps.result || !apiIps.result.cloud_api_ip || !apiIps.result.manyou_api_ip || !apiIps.result.qzone_api_ip) {
		return false;
	}

	if (!$('cloud_tbody_api_test') || !$('cloud_tbody_manyou_test') || !$('cloud_tbody_qzone_test')) {
		return false;
	}

	var cloudAPIIPs = apiIps.result.cloud_api_ip;
	var manyouAPIIPs = apiIps.result.manyou_api_ip;
	var QzoneAPIIPs = apiIps.result.qzone_api_ip;

	ajaxShowAPIStatus(1, cloudAPIIPs);

	ajaxShowAPIStatus(2, manyouAPIIPs);

	ajaxShowAPIStatus(3, QzoneAPIIPs);

}

function ajaxShowAPIStatus(apiType, ips) {

	var apiType = parseInt(apiType);

	for(i in ips) {
		var apiIp = ips[i].ip;
		var apiDescription = ips[i].description;
		var apiTr = document.createElement('tr');

		var apiTdFirst = document.createElement('td');
		apiTdFirst.className = 'td24';
		if (!apiType || apiType == 1) {
			apiTdFirst.innerHTML = '<strong>云平台其他接口测试</strong>';
		} else if (apiType == 2) {
			apiTdFirst.innerHTML = '<strong>漫游其他接口测试</strong>';
		} else if (apiType == 3) {
			apiTdFirst.innerHTML = '<strong>QQ互联接口测试</strong>';
		}

		var apiTdSecond = document.createElement('td');
		apiTdSecond.innerHTML = '<div id="_doctor_apitest_' + apiType + '_' + apiIp + '">&nbsp;</div>';

		apiTr.appendChild(apiTdFirst);
		apiTr.appendChild(apiTdSecond);

		if (!apiType || apiType == 1) {
			$('cloud_tbody_api_test').appendChild(apiTr);
		} else if (apiType == 2) {
			$('cloud_tbody_manyou_test').appendChild(apiTr);
		} else if (apiType == 3) {
			$('cloud_tbody_qzone_test').appendChild(apiTr);
		}
	}

	for(i in ips) {
		var apiIp = ips[i].ip;
		var apiDescription = ips[i].description;
		ajaxget('admin.php?action=cloud&operation=doctor&op=apitest&api_type=' + apiType + '&api_ip=' + encodeURI(apiIp) + '&api_description=' + encodeURI(apiDescription), '_doctor_apitest_' + apiType + '_' + apiIp);
	}

}

function siteTestApiCallback(returnInfo, siteTestPosition) {

	if (typeof siteTestPosition == 'undefined') {
		var siteTestPosition = 'doctor';
	}

	if (!$('cloud_doctor_site_test_result_div')) {
		return false;
	}

	if (typeof returnInfo == 'undefined' || !returnInfo) {
		$('cloud_doctor_site_test_result_div').innerHTML = '<img align="absmiddle" src="static/image/admincp/cloud/wrong.gif" /> 服务器繁忙，请稍后再试';
		return false;
	}

	if (returnInfo.errorCode) {
		var errorCode = parseInt(returnInfo.errorCode);
		var errorMessage = returnInfo.errorMessage;
		$('cloud_doctor_site_test_result_div').innerHTML = '<img align="absmiddle" src="static/image/admincp/cloud/wrong.gif" /> ' + errorMessage;
		return false;
	}

	$('cloud_doctor_site_test_result_div').innerHTML = '<img align="absmiddle" src="static/image/admincp/cloud/right.gif" /> 测试成功，耗时 ' + returnInfo.result.timeUsed + ' 秒';
	if (siteTestPosition == 'open') {
		$('submit_submit').style.color = '#000';
		$('submit_submit').disabled = false;
	}

	return true;
}