<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_membersplit.php 28308 2012-02-28 01:51:52Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();



loadcache('membersplitdata');
if(!empty($_G['cache']['membersplitstep'])) {
	cpmsg('membersplit_split_in_backstage', 'action=membersplit&operation=manage', 'loadingform');
}
if($operation == 'manage') {
	shownav('founder', 'nav_membersplit');
	if(!submitcheck('membersplit_split_submit', 1)) {
		showsubmenu('membersplit');
		showtips('membersplit_tips');
		showformheader('membersplit&operation=manage');
		showtableheader('membersplit_table_orig');

		if($_G['cache']['membersplitdata'] && $_G['cache']['membersplitdata']['dateline'] > TIMESTAMP - 86400) {
			$membercount = $_G['cache']['membersplitdata']['membercount'];
			$zombiecount = $_G['cache']['membersplitdata']['zombiecount'];
		} else {
			$membercount = C::t('common_member')->count();
			$zombiecount = C::t('common_member')->count_zombie();
			savecache('membersplitdata', array('membercount' => $membercount, 'zombiecount' => $zombiecount, 'dateline' => TIMESTAMP));
		}
		$percentage = round($zombiecount/$membercount, 4)*100;

		showsubtitle(array('','','membersplit_count', 'membersplit_combie_count'));
		$color = $percentage > 0 ? 'red' : 'green';
		if($percentage == 0) {
			$msg = $lang['membersplit_message0'];
		} else if($percentage < 10) {
			$msg = $lang['membersplit_message10'];
		} else {
			$msg = $lang['membersplit_message100'];
		}
		showtablerow('', '', array('','', number_format($membercount), '<span style="color:'.$color.'">'.number_format($zombiecount).'('.$percentage.'%) '.$msg.'</span>'));

		if($percentage > 0) {
			showsubmit('membersplit_split_submit', 'membersplit_archive');
		}
		showtablefooter();
		showformfooter();

	} else {
		if(!$_G['setting']['bbclosed']) {
			cpmsg('membersplit_split_must_be_closed', 'action=membersplit&operation=manage', 'error');
		}
		$step = intval($_GET['step'])+1;
		$splitnum = 1000;
		if(!$_GET['nocheck'] && $step == 1 && !C::t('common_member_archive')->check_table()) {
			cpmsg('membersplit_split_check_table', 'action=membersplit&operation=rebuildtable', 'loadingform', array());
			cpmsg('', 'action=membersplit&operation=manage', 'error');
		}
		if(!C::t('common_member')->split($splitnum)) {
			cpmsg('membersplit_split_succeed', 'action=membersplit&operation=manage', 'succeed');
		}
		cpmsg('membersplit_split_doing', 'action=membersplit&operation=manage&membersplit_split_submit=1&step='.$step, 'loadingform', array('num' => $step*$splitnum));
	}
} else if($operation == 'rebuildtable') {
	if(!$_G['setting']['bbclosed']) {
		cpmsg('membersplit_split_must_be_closed', 'action=membersplit&operation=manage', 'error');
	}
	$step = intval($_GET['step']);
	$ret = C::t('common_member_archive')->rebuild_table($step);
	if($ret === false) {
		cpmsg('membersplit_split_check_table_done', 'action=membersplit&operation=manage&membersplit_split_submit=1&nocheck=1', 'loadingform');
	} else if($ret === true) {
		cpmsg('membersplit_split_checking_table', 'action=membersplit&operation=rebuildtable&step='.($step+1), 'loadingform', array('step' => $step+1));
	} else {
		cpmsg('membersplit_split_check_table_fail', 'action=membersplit&operation=manage', 'error', array('tablename' => $ret));
	}
}

?>