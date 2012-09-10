<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_connect.php 27998 2012-02-20 09:33:38Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(

	'feed_sync_success' => '同步发 Feed 成功',
	'deletethread_sync_success' => '删除主题同步成功',
	'deletethread_sync_failed' => '删除主题同步失败',
	'server_busy' => '抱歉，当前存在网络问题或服务器繁忙，请您稍候再试。谢谢。',
    'share_token_outofdate' => '为了您的账号安全，请使用QQ帐号重新登录，将为您升级帐号安全机制<br/><br/>点击<a href={login_url}><img src=static/image/common/qq_login.gif class=vm alt=QQ登录 /></a>页面将发生跳转',
	'share_success' => '分享成功',
	'broadcast_success' => '转播成功',

	'qzone_title' => '标题',
	'qzone_reason' => '理由',
	'qzone_picture' => '图片',
	'qzone_shareto' => '分享到QQ空间',
	'qzone_to_friend' => '分享给好友',
	'qzone_reason_default' => '可以在这里输入分享原因或详细内容',
	'qzone_subject_is_empty' => '分享标题不能为空',
	'qzone_subject_is_long' => '分享标题超过了长度限制',
	'qzone_reason_is_long' => '分享理由超过了长度限制',
    'qzone_share_same_url' => '该帖子您已经分享过，不需要重复分享',

	'weibo_title' => '分享到我的微博，顺便说点什么吧',
	'weibo_input' => '还能输入<strong id=checklen></strong>字',
	'weibo_select_picture' => '请选择分享图片',
	'weibo_share' => '转播',
	'weibo_share_to' => '转播到腾讯微博',
	'weibo_reason_is_long' => '微博内容超过了长度限制',
    'weibo_same_content' => '该帖子您已经转播过，不需要重复转播',
	'weibo_account_not_signup' => '抱歉，您还未开通微博账号，无法分享内容，<a href=http://t.qq.com/reg/index.php target=_blank style=color:#336699>点击这里马上开通</a>',
	'user_unauthorized' => '抱歉，您未授权分享主题到QQ空间、腾讯微博和腾讯朋友，点击<a href={login_url}><img src=static/image/common/qq_login.gif class=vm alt=QQ登录 /></a>，马上完善授权',

	'connect_errlog_server_no_response' => '服务器无响应',
	'connect_errlog_access_token_incomplete' => '接口返回的AccessToken数据不完整',
	'connect_errlog_request_token_not_authorized' => '用户TmpToken未授权或返回的数据不完整',
	'connect_errlog_sig_incorrect' => 'URL签名不正确',

	'connect_tthread_broadcast' => '转播微博',
	'connect_tthread_message' => '<br><br><img class="vm" src="static/image/common/weibo.png">&nbsp;<a href="http://t.qq.com/{username}" target="_blank">来自 {nick} 的腾讯微博</a>',
	'connect_tthread_comment' => '微博评论',
);

?>