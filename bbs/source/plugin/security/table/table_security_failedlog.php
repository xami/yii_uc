<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_security_failedlog.php 27666 2012-02-09 05:33:43Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_security_failedlog extends discuz_table {

	public function __construct() {
		$this->_table = 'security_failedlog';
		$this->_pk = 'id';

		parent::__construct();
	}

	public function deleteDirtyLog() {
		DB::delete($this->_table, 'lastfailtime = 0');
		DB::delete($this->_table, 'failcount >= 10');
		DB::delete($this->_table, 'pid = 0 AND tid = 0 AND uid = 0');
		return true;
	}

	public function fetch_by_pid($pid) {

		return DB::fetch_first('SELECT * FROM %t WHERE ' . DB::field('pid', $pid) . ' ' . DB::limit(0, 1), array($this->_table), $this->_pk);
	}

	public function fetch_by_uid($uid) {

		return DB::fetch_first('SELECT * FROM %t WHERE ' . DB::field('uid', $uid) . ' ' . DB::limit(0, 1), array($this->_table), $this->_pk);
	}

	public function range_by_pid($pid, $start = 0, $limit = 5) {

		return DB::fetch_all('SELECT * FROM %t WHERE ' . DB::field('pid', $pid) . ' ' . DB::limit($start, $limit), array($this->_table), $this->_pk);
	}

	public function range_by_uid($uid, $start = 0, $limit = 5) {

		return DB::fetch_all('SELECT * FROM %t WHERE ' . DB::field('uid', $uid) . ' ' . DB::limit($start, $limit), array($this->_table), $this->_pk);
	}

}