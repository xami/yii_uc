<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_trade.php 25387 2011-11-08 08:07:16Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$orderby = in_array($orderby, array('dateline', 'price', 'expiration')) ? $orderby : 'dateline';
$ascdesc = isset($ascdesc) && $ascdesc == 'asc' ? 'asc' : 'desc';

if(!empty($searchid)) {

	$page = max(1, intval($_GET['page']));
	$start_limit = ($page - 1) * $_G['tpp'];

	$index = C::t('common_searchindex')->fetch($searchid);
	if(!$index) {
		showmessage('search_id_invalid');
	}
	$index['keywords'] = rawurlencode($index['keywords']);
	$index['searchtype'] = preg_replace("/^([a-z]+)\|.*/", "\\1", $index['searchstring']);

	$threadlist = $tradelist = array();

	$query = C::t('forum_trade')->fetch_goods(0, explode(',', $index['tids']), $orderby, $ascdesc, $start_limit, $_G['tpp']);
	foreach($query as $tradethread) {
		$tradethread['lastupdate'] = dgmdate($tradethread['lastupdate'], 'u');
		$tradethread['lastbuyer'] = rawurlencode($tradethread['lastbuyer']);
		if($tradethread['expiration']) {
			$tradethread['expiration'] = ($tradethread['expiration'] - TIMESTAMP) / 86400;
			if($tradethread['expiration'] > 0) {
				$tradethread['expirationhour'] = floor(($tradethread['expiration'] - floor($tradethread['expiration'])) * 24);
				$tradethread['expiration'] = floor($tradethread['expiration']);
			} else {
				$tradethread['expiration'] = -1;
			}
		}
		$tradelist[] = $tradethread;
	}

	$multipage = multi($index['threads'], $_G['tpp'], $page, "forum.php?mod=search&searchid=$searchid".($orderby ? "&orderby=$orderby" : '')."&srchtype=trade&searchsubmit=yes");

	$url_forward = 'forum.php?mod=search&'.$_SERVER['QUERY_STRING'];

	include template('forum/search_trade');

} else {

	!($_G['group']['exempt'] & 2) && checklowerlimit('search');

	$srchtxt = isset($srchtxt) ? trim($srchtxt) : '';
	$srchuname = isset($srchuname) ? trim($srchuname) : '';

	$forumsarray = array();
	if(!empty($srchfid)) {
		foreach((is_array($srchfid) ? $srchfid : explode('_', $srchfid)) as $forum) {
			if($forum = intval(trim($forum))) {
				$forumsarray[] = $forum;
			}
		}
	}

	$fids = $comma = '';
	foreach($_G['cache']['forums'] as $fid => $forum) {
		if($forum['type'] != 'group' && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
			if(!$forumsarray || in_array($fid, $forumsarray)) {
				$fids .= "$comma'$fid'";
				$comma = ',';
			}
		}
	}

	$srchfilter = in_array($srchfilter, array('all', 'digest', 'top')) ? $srchfilter : 'all';

	$searchstring = 'trade|'.addslashes($srchtxt).'|'.intval($srchtypeid).'|'.intval($srchuid).'|'.$srchuname.'|'.addslashes($fids).'|'.intval($srchfrom).'|'.intval($before).'|'.$srchfilter;
	$searchindex = array('id' => 0, 'dateline' => '0');

	foreach(C::t('common_searchindex')->fetch_all_search($_G['setting']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring) as $index) {
		if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
			$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
			break;
		} elseif($index['flood']) {
			showmessage('search_ctrl', 'forum.php?mod=search', array('searchctrl' => $_G['setting']['searchctrl']));
		}
	}

	if($searchindex['id']) {

		$searchid = $searchindex['id'];

	} else {

		if(!$srchtxt && !$srchtypeid && !$srchuid && !$srchuname && !$srchfrom && !in_array($srchfilter, array('digest', 'top'))) {
			showmessage('search_invalid', 'forum.php?mod=search');
		} elseif(isset($srchfid) && $srchfid != 'all' && !(is_array($srchfid) && in_array('all', $srchfid)) && empty($forumsarray)) {
			showmessage('search_forum_invalid', 'forum.php?mod=search');
		} elseif(!$fids) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}

		if($_G['setting']['maxspm']) {
			if(C::t('common_searchindex')->count_by_dateline($_G['timestamp']) >= $_G['setting']['maxspm']) {
				showmessage('search_toomany', 'forum.php?mod=search', array('maxspm' => $_G['setting']['maxspm']));
			}
		}

		$digestltd = $srchfilter == 'digest' ? "t.digest>'0' AND" : '';
		$topltd = $srchfilter == 'top' ? "AND t.displayorder>'0'" : "AND t.displayorder>='0'";

		if(!empty($srchfrom) && empty($srchtxt) && empty($srchtypeid) && empty($srchuid) && empty($srchuname)) {

			$searchfrom = $before ? '<=' : '>=';
			$searchfrom .= TIMESTAMP - $srchfrom;
			$sqlsrch = " tr.dateline$searchfrom";
			$expiration = TIMESTAMP + $cachelife_time;
			$keywords = '';

		} else {

			$sqlsrch = ' 1 ';

			if($srchuname) {
				$srchuid = array_keys(C::t('common_member')->fetch_all_by_like_username($srchuname, 0, 50));
				if(!$srchuid) {
					$sqlsrch .= ' AND 0';
				}
			}/* elseif($srchuid) {
				$srchuid = "'$srchuid'";
			}*/

			if($srchtypeid) {
				$srchtypeid = intval($srchtypeid);
				$sqlsrch .= " AND tr.typeid='$srchtypeid'";
			}

			if($srchtxt) {
				require_once libfile('function/search');
				$srcharr = searchkey($srchtxt, "tr.subject LIKE '%{text}%'", true);
				$srchtxt = $srcharr[0];
				$sqlsrch .= $srcharr[1];
			}

			if($srchuid) {
				$sqlsrch .= ' AND tr.sellerid IN ('.dimplode((array)$srchuid).')';
			}

			if(!empty($srchfrom)) {
				$searchfrom = ($before ? '<=' : '>=').(TIMESTAMP - $srchfrom);
				$sqlsrch .= " AND tr.dateline$searchfrom";
			}


			$keywords = str_replace('%', '+', $srchtxt).(trim($srchuname) ? '+'.str_replace('%', '+', $srchuname) : '');
			$expiration = TIMESTAMP + $cachelife_text;

		}

		$threads = $tids = 0;
		$query = C::t('forum_trade')->fetch_all_for_search($digestltd, $fids, $topltd, $sqlsrch, 0, $_G['setting']['maxsearchresults']);
		foreach($query as $post) {
			if($thread['closed'] <= 1) {
				$tids .= ','.$post['pid'];
				$threads++;
			}
		}

		$searchid = C::t('common_searchindex')->insert(array(
			'keywords' => $keywords,
			'searchstring' => $searchstring,
			'useip' => $_G['clientip'],
			'uid' => $_G['uid'],
			'dateline' => $_G['timestamp'],
			'expiration' => $expiration,
			'threads' => $threads,
			'threadsortid' => $_GET['selectsortid'],
			'tids' => $tids
		), true);

		!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');

	}

	showmessage('search_redirect', "forum.php?mod=search&searchid=$searchid&srchtype=trade&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

}

?>