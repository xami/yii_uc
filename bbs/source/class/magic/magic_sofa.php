<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_sofa.php 26749 2011-12-22 07:38:37Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_sofa {

	var $version = '1.0';
	var $name = 'sofa_name';
	var $description = 'sofa_desc';
	var $price = '10';
	var $weight = '10';
	var $targetgroupperm = true;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'sofa_forum',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache('forums');
		$settings['fids']['value'][] = array(0, '&nbsp;');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		$magic['fids'] = explode("\t", $magic['forum']);

		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		global $_G;
		$magicnew['forum'] = is_array($parameters['fids']) && !empty($parameters['fids']) ? implode("\t",$parameters['fids']) : '';
	}

	function usesubmit() {
		global $_G;
		if(empty($_GET['tid'])) {
			showmessage(lang('magic/sofa', 'sofa_info_nonexistence'));
		}

		$thread = getpostinfo($_GET['tid'], 'tid', array('fid', 'authorid', 'dateline', 'subject'));
		$this->_check($thread);

		$firstsofa = C::t('forum_threadmod')->count_by_tid_magicid($_GET['tid'], $this->magic['magicid']);
		if($firstsofa >= 1) {
			showmessage(lang('magic/sofa', 'sofa_info_sofaexistence'), '', array(), array('login' => 1));
		}

		$sofamessage = lang('magic/sofa', 'sofa_text', array('actor' => $_G['member']['username'], 'time' => dgmdate(TIMESTAMP), 'magicname' => $this->magic['name']));
		$dateline = $thread['dateline'] + 1;
		require_once libfile('function/forum');

		insertpost(array(
			'fid' => $thread['fid'],
			'tid' => $_GET['tid'],
			'first' => '0',
			'author' => $_G['username'],
			'authorid' => $_G['uid'],
			'dateline' => $dateline,
			'message' => $sofamessage,
			'useip' => $_G['clientip'],
			'usesig' => '1',
		));

		C::t('forum_thread')->increase($_GET['tid'], array('replies' => 1, 'moderated' => array(1)));
		C::t('forum_forum')->update_forum_counter($post['fid'], 0, 1, 1);

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_GET['tid']);
		updatemagicthreadlog($_GET['tid'], $this->magic['magicid']);

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'magic', lang('magic/sofa', 'sofa_notification'), array('tid' => $_GET['tid'], 'subject' => $thread['subject'], 'magicname' => $this->magic['name']));
		}

		showmessage(lang('magic/sofa', 'sofa_succeed'), dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$tid = !empty($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
		if($tid) {
			$thread = getpostinfo($_GET['id'], 'tid', array('fid', 'authorid'));
			$this->_check($thread);
		}
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 24;
		magicshowtype('top');
		magicshowsetting(lang('magic/sofa', 'sofa_info', array('expiration' => $this->parameters['expiration'])), 'tid', $tid, 'text');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_GET['id'])) {
			$thread = getpostinfo($_GET['id'], 'tid', array('fid', 'authorid'));
			$this->_check($thread);
		}
	}

	function _check($thread) {
		if(!checkmagicperm($this->parameters['forum'], $thread['fid'])) {
			showmessage(lang('magic/sofa', 'sofa_info_noperm'));
		}
		$member = getuserbyuid($thread['authorid']);
		if(!checkmagicperm($this->parameters['targetgroups'], $member['groupid'])) {
			showmessage(lang('magic/sofa', 'sofa_info_user_noperm'));
		}
	}

}

?>