<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_patch.php 28136 2012-02-23 03:26:19Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['action'] == 'checkpatch') {

	if($_G['uid'] && $_G['member']['allowadmincp'] == 1) {
		$discuz_patch = new discuz_patch();
		$discuz_patch->check_patch();
	}
	exit;

} elseif($_GET['action'] == 'patchnotice') {

	$patchlist = '';
	if($_G['member']['allowadmincp'] == 1) {
		$discuz_patch = new discuz_patch();
		$patchnotice = $discuz_patch->fetch_patch_notice();
		if(!empty($patchnotice['data'])) {
			include_once DISCUZ_ROOT.'./source/language/forum/lang_misc.php';
			$patchlist .= '<div class="bm'.($patchnotice['fixed'] ? ' allfixed' : '').'"><div class="bm_h cl"><a href="javascript:;" onclick="$(\'patch_notice\').style.display=\'none\'" class="y" title="'.$lang['patch_close'].'">'.$lang['patch_close'].'</a><h2>';
			if($patchnotice['fixed']) {
				$patchlist .= $lang['patch_site_have'].' '.count($patchnotice['data']).' '.$lang['patch_is_fixed'];
			} else {
				$patchlist .= $lang['patch_site_have'].' '.count($patchnotice['data']).' '.$lang['patch_need_fix'];
			}
			$patchlist .= '</h2></div><div class="bm_c"><table width="100%" class="mbm"><tr><th>'.$lang['patch_name'].'</th><th class="patchdate">'.$lang['patch_dateline'].'</th><th class="patchstat">'.$lang['patch_status'].'</th><tr>';
			foreach($patchnotice['data'] as $notice) {
				$patchlist .= '<tr><td>'.$notice['serial'].'</td><td>'.dgmdate($notice['dateline'], 'Y-m-d').'</td><td>';
				if($notice['status'] >= 1) {
					$patchlist .= '<span class="fixed">'.$lang['patch_fixed_status'].'<span>';
				} elseif($notice['status'] < 0) {
					$patchlist .= '<span class="unfixed">'.$lang['patch_fix_failed_status'].'</span>';
				} else {
					$patchlist .= '<span class="unfixed">'.$lang['patch_unfix_status'].'</span>';
				}
				$patchlist .= '</td></tr>';
			}
			$patchlist .= '</table><p class="cl"><a href="admin.php?action=patch" class="y pn"><strong>'.($patchnotice['fixed'] ? $lang['patch_view_fix_detail'] : $lang['patch_fix_right_now']).'</strong></a></p>';
			$patchlist .= '</div></div>';
		}
	}
	include template('common/header_ajax');
	echo $patchlist;
	include template('common/footer_ajax');
	exit;

}

?>