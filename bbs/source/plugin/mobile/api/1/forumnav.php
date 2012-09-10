<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumnav.php 27821 2012-02-15 05:26:17Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		$forums = array();
		$query = DB::query("SELECT f.fid, f.type, f.name, f.fup, f.status, ff.password, ff.redirect, ff.viewperm, ff.postperm, ff.threadtypes, ff.threadsorts FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid LEFT JOIN ".DB::table('forum_access')." a ON a.fid=f.fid AND a.allowview>'0' WHERE f.status='1' ORDER BY f.type, f.displayorder");
		while($forum = DB::fetch($query)) {
			if($forum['redirect'] || $forum['password']) {
				continue;
			}
			if(!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				if($forum['threadsorts']) {
					$forum['threadsorts'] = mobile_core::getvalues(unserialize($forum['threadsorts']), array('required', 'types'));
				}
				if($forum['threadtypes']) {
					$forum['threadtypes'] = mobile_core::getvalues(unserialize($forum['threadtypes']), array('required', 'types'));
				}
				$forums[] = mobile_core::getvalues($forum, array('fid', 'type', 'name', 'fup', 'viewperm', 'postperm', 'status', 'threadsorts', 'threadtypes'));
			}
		}
		$variable['forums'] = $forums;
		mobile_core::result(mobile_core::variable($variable));
	}

	function output() {}

}

?>