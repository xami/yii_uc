<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_upgrade.php 27678 2012-02-09 08:11:21Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_upgrade {

	var $upgradeurl = 'http://upgrade.discuz.com/DiscuzX/';
	var $locale = 'SC';
	var $charset = 'GBK';

	public function fetch_updatefile_list($upgradeinfo) {

		$file = DISCUZ_ROOT.'./data/update/Discuz! X'.$upgradeinfo['latestversion'].' Release['.$upgradeinfo['latestrelease'].']/updatelist.tmp';
		$upgradedata = @file_get_contents($file);
		if(!$upgradedata) {
			$upgradedata = dfsockopen($this->upgradeurl.$upgradeinfo['upgradelist']);
			$this->mkdirs(dirname($file));
			$fp = fopen($file, 'w');
			if(!$fp) {
				return array();
			}
			fwrite($fp, $upgradedata);
		}

		$return = array();
		$upgradedata = explode("\r\n", $upgradedata);
		foreach($upgradedata as $k => $v) {
			if(!$v) {
				continue;
			}
			$return['file'][$k] = trim(substr($v, 34));
			$return['md5'][$k] = substr($v, 0, 32);

		}

		return $return;
	}

	public function compare_basefile($upgradeinfo, $upgradefilelist) {
		if(!$discuzfiles = @file('./source/admincp/discuzfiles.md5')) {
			return array();
		}

		$newupgradefilelist = array();
		foreach($upgradefilelist as $v) {
			$newupgradefilelist[$v] = md5_file(DISCUZ_ROOT.'./'.$v);
		}

		$modifylist = $showlist = $searchlist = array();
		foreach($discuzfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if(isset($newupgradefilelist[$file])) {
				if($md5datanew[$file] != $newupgradefilelist[$file]) {
					if(!$upgradeinfo['isupdatetemplate'] && preg_match('/\.htm$/i', $file)) {
						$ignorelist[$file] = $file;
						$searchlist[] = "\r\n".$file;
						continue;
					}
					if($this->compare_file_content(DISCUZ_ROOT.$file, $this->upgradeurl.$this->versionpath().'/'.DISCUZ_RELEASE.'/'.$this->locale.'_'.$this->charset.'/upload/'.$file.'sc')) {
						$showlist[$file] = $file;
					} else {
						$modifylist[$file] = $file;
					}
				} else {
					$showlist[$file] = $file;
				}
			}
		}
		if($searchlist) {
			$file = DISCUZ_ROOT.'./data/update/Discuz! X'.$upgradeinfo['latestversion'].' Release['.$upgradeinfo['latestrelease'].']/updatelist.tmp';
			$upgradedata = file_get_contents($file);
			$upgradedata = str_replace($searchlist, '', $upgradedata);
			$fp = fopen($file, 'w');
			if($fp) {
				fwrite($fp, $upgradedata);
			}
		}

		return array($modifylist, $showlist, $ignorelist);
	}

	public function compare_file_content($file, $remotefile) {
		if(!preg_match('/\.php$|\.htm$/i', $file)) {
			return false;
		}
		$content = preg_replace('/\s/', '', file_get_contents($file));
		$remotecontent = preg_replace('/\s/', '', file_get_contents($remotefile));
		if(strcmp($content, $remotecontent)) {
			return false;
		} else {
			return true;
		}
	}

	public function check_upgrade() {

		include_once libfile('class/xml');
		include_once libfile('function/cache');

		$upgradefile = $this->upgradeurl.$this->versionpath().'/'.DISCUZ_RELEASE.'/upgrade.xml';
		$response = xml2array(dfsockopen($upgradefile));
		if(isset($response['cross']) || isset($response['patch'])) {
			C::t('common_setting')->update('upgrade', $response);
		} else {
			C::t('common_setting')->update('upgrade', '');
		}
		updatecache('setting');
		return true;
	}

	public function check_folder_perm($updatefilelist) {
		foreach($updatefilelist as $file) {
			if(!file_exists(DISCUZ_ROOT.$file)) {
				if(!$this->test_writable(dirname(DISCUZ_ROOT.$file))) {
					return false;
				}
			} else {
				if(!is_writable(DISCUZ_ROOT.$file)) {
					return false;
				}
			}
		}
		return true;
	}

	public function test_writable($dir) {
		$writeable = 0;
		$this->mkdirs($dir);
		if(is_dir($dir)) {
			if($fp = @fopen("$dir/test.txt", 'w')) {
				@fclose($fp);
				@unlink("$dir/test.txt");
				$writeable = 1;
			} else {
				$writeable = 0;
			}
		}
		return $writeable;
	}

	public function download_file($upgradeinfo, $file, $folder = 'upload', $md5 = '') {
		$dir = DISCUZ_ROOT.'./data/update/Discuz! X'.$upgradeinfo['latestversion'].' Release['.$upgradeinfo['latestrelease'].']/';
		$this->mkdirs(dirname($dir.$file));
		$fp = fopen($dir.$file, 'w');
		if(!$fp) {
			return false;
		}
		$response = dfsockopen($this->upgradeurl.$upgradeinfo['latestversion'].'/'.$upgradeinfo['latestrelease'].'/'.$this->locale.'_'.$this->charset.'/'.$folder.'/'.$file.'sc');
		if($response) {
			fwrite($fp, $response);
		}
		fclose($fp);

		if(md5_file($dir.$file) == $md5) {
			return true;
		} else {
			return false;
		}
	}

	public function mkdirs($dir) {
		if(!is_dir($dir)) {
			if(!self::mkdirs(dirname($dir))) {
				return false;
			}
			if(!@mkdir($dir)) {
				return false;
			}
		}
		return true;
	}

	public function copy_file($srcfile, $desfile, $type) {
		global $_G;

		if(!is_file($srcfile)) {
			return false;
		}
		if($type == 'file') {
			$this->mkdirs(dirname($desfile));
			copy($srcfile, $desfile);
		} elseif($type == 'ftp') {
			$siteftp = $_GET['siteftp'];
			$siteftp['on'] = 1;
			$siteftp['password'] = authcode($siteftp['password'], 'ENCODE', md5($_G['config']['security']['authkey']));
			$ftp = & discuz_ftp::instance($siteftp);
			$ftp->connect();
			$ftp->upload($srcfile, $desfile);
			if($ftp->error()) {
				return false;
			}
		}
		return true;
	}

	public function versionpath() {
		$versionpath = '';
		foreach(explode(' ', substr(DISCUZ_VERSION, 1)) as $unit) {
			$versionpath = $unit;
			break;
		}
		return $versionpath;
	}
}
?>