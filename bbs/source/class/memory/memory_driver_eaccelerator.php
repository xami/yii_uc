<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_driver_eaccelerator.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class memory_driver_eaccelerator
{

	public function init($config) {

	}

	public function get($key) {
		return eaccelerator_get($key);
	}

	public function set($key, $value, $ttl = 0) {
		return eaccelerator_put($key, $value, $ttl);
	}

	public function rm($key) {
		return eaccelerator_rm($key);
	}


}

?>