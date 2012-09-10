<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 28218 2012-02-24 07:18:37Z yangli $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

REPLACE INTO pre_common_setting VALUES ('my_search_data', 'a:8:{s:6:"status";i:0;s:15:"allow_hot_topic";i:1;s:20:"allow_thread_related";i:1;s:21:"allow_forum_recommend";i:0;s:19:"allow_forum_related";i:0;s:24:"allow_collection_related";i:1;s:10:"cp_version";i:1;s:17:"recwords_lifetime";i:21600;}');

EOF;

runquery($sql);

$finish = true;

?>