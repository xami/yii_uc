<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_connect_tthreadlog.php 27640 2012-02-08 09:48:47Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_connect_tthreadlog extends discuz_table {

	public function __construct() {
		$this->_table = 'connect_tthreadlog';
		$this->_pk = 'twid';

		parent::__construct();
	}

	public function fetch_max_updatetime_by_tid($tid) {
		return DB::result_first('SELECT updatetime FROM %t WHERE tid=%d ORDER BY updatetime DESC LIMIT 1', array($this->_table, $tid));
	}

	public function fetch_min_nexttime_by_tid($tid) {
		return DB::fetch_first('SELECT * FROM %t WHERE tid=%d ORDER BY nexttime ASC LIMIT 1', array($this->_table, $tid));
	}



}
?>