<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_upgrade.php 26689 2011-12-20 05:05:58Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(0);
cpheader();
include_once 'source/discuz_version.php';
$discuz_upgrade = new discuz_upgrade();

$step = intval($_GET['step']);
$step = $step ? $step : 1;
$operation = $operation ? $operation : 'check';

if(!$_G['setting']['bbclosed'] && in_array($operation, array('cross', 'patch'))) {
	cpmsg('upgrade_close_site', '', 'error');
}

if($operation == 'patch' || $operation == 'cross') {

	$version = trim($_GET['version']);
	$release = trim($_GET['release']);
	$locale = trim($_GET['locale']);
	$charset = trim($_GET['charset']);
	$upgradeinfo = array();

	foreach($_G['setting']['upgrade'] as $type => $list) {
		if($type == $operation && $version == $list['latestversion'] && $release == $list['latestrelease']) {
			$discuz_upgrade->locale = $locale;
			$discuz_upgrade->charset = $charset;
			$upgradeinfo = $list;
			break;
		}
	}
	if(!$upgradeinfo) {
		cpmsg('upgrade_none');
	}

	shownav('tools', 'nav_founder_upgrade');
	showsubmenusteps('nav_founder_upgrade', array(
		array('founder_upgrade_updatelist', $step == 1),
		array('founder_upgrade_download', $step == 2),
		array('founder_upgrade_compare', $step == 3),
		array('founder_upgrade_upgrading', $step == 4),
		array('founder_upgrade_complete', $step == 5),
	));
	showtableheader();

	$updatefilelist = $discuz_upgrade->fetch_updatefile_list($upgradeinfo);
	$updatemd5filelist = $updatefilelist['md5'];
	$updatefilelist = $updatefilelist['file'];

	$theurl = 'upgrade&operation='.$operation.'&version='.$version.'&locale='.$locale.'&charset='.$charset.'&release='.$release;

	if($step == 1) {
		showtablerow('class="header"', '', $lang['founder_upgrade_preupdatelist']);
		foreach($updatefilelist as $file) {
			$file = '<em class="files bold">'.$file.'</em>';
			showtablerow('', '', array($file));
		}
		$linkurl = ADMINSCRIPT.'?action='.$theurl.'&step=2';
		showtablerow('', '', array($lang['founder_upgrade_store_directory'].'./data/update/Discuz! X'.$version.' Release['.$release.']'));
		showtablerow('', '', array('<input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_download'].'">'));
	} elseif($step == 2) {
		$fileseq = intval($_GET['fileseq']);
		$fileseq = $fileseq ? $fileseq : 1;
		if($fileseq > count($updatefilelist)) {
			if($upgradeinfo['isupdatedb']) {
				$discuz_upgrade->download_file($upgradeinfo, 'install/data/install.sql');
				$discuz_upgrade->download_file($upgradeinfo, 'install/data/install_data.sql');
				$discuz_upgrade->download_file($upgradeinfo, 'update.php', 'utility');
			}
			$linkurl = 'action='.$theurl.'&step=3';
			cpmsg('upgrade_download_complete_to_compare', $linkurl, 'loading');
		} else {
			$linkurl = 'action='.$theurl.'&step=2&fileseq='.($fileseq+1);
			if(!$discuz_upgrade->download_file($upgradeinfo, $updatefilelist[$fileseq-1], 'upload', $updatemd5filelist[$fileseq-1])) {
				cpmsg('upgrade_redownload', 'action='.$theurl.'&step=2&fileseq='.$fileseq, 'form', array('file' => $updatefilelist[$fileseq-1]));
			}
			cpmsg('upgrade_downloading_file', $linkurl, 'loading', array('file' => $updatefilelist[$fileseq-1]));
		}
	} elseif($step == 3) {
		list($modifylist, $showlist, $ignorelist) = $discuz_upgrade->compare_basefile($upgradeinfo, $updatefilelist);
		if(empty($modifylist) && empty($showlist) && empty($ignorelist)) {
			cpmsg('filecheck_nofound_md5file', '', 'error');
		}
		showtablerow('class="header"', 'colspan="2"', $lang['founder_upgrade_diff_show']);
		foreach($updatefilelist as $v) {
			if(isset($ignorelist[$v])) {
				continue;
			} elseif(isset($modifylist[$v])) {
				showtablerow('', array('class="td24" style="color:red;"', 'class="td24" style="color:red;"'), array('<em class="files bold">'.$v.'</em>', $lang['founder_upgrade_diff'].'<em class="edited">&nbsp;</em>'));
			} elseif(isset($showlist[$v])) {
				showtablerow('', array('class="td24"', 'class="td24"'), array('<em class="files bold">'.$v.'</em>', $lang['founder_upgrade_normal'].'<em class="fixed">&nbsp;</em>'));
			} else {
				showtablerow('', array('class="td24"', 'class="td24"'), array('<em class="files bold">'.$v.'</em>', $lang['founder_upgrade_new'].'<em class="unknown">&nbsp;</em>'));
			}
		}

		$linkurl = ADMINSCRIPT.'?action='.$theurl.'&step=4';
		showtablerow('', 'colspan="2"', $lang['founder_upgrade_backup_file'].' ./data/back/Discuz! '.DISCUZ_VERSION.' Release['.DISCUZ_RELEASE.']');
		showtablerow('', 'colspan="2"', '<input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.(!empty($modifylist) ? $lang['founder_upgrade_force'] : $lang['founder_upgrade_regular']).'" />');
	} elseif($step == 4) {

		$confirm = $_GET['confirm'];
		if(!$confirm) {
			if($_GET['siteftpsetting']) {
				$action = $theurl.'&step=4&confirm=ftp';
				siteftp_form($action);
				exit;
			}

			if($discuz_upgrade->check_folder_perm($updatefilelist)) {
				$confirm = 'file';
			} else {
				$linkurl = ADMINSCRIPT.'?action='.$theurl.'&step=4';
				$ftplinkurl = $linkurl.'&siteftpsetting=1';
				cpmsg('upgrade_cannot_access_file',
					'',
					'',
					array(),
					'<br /><input type="button" class="btn" onclick="window.location.href=\''.$ftplinkurl.'\'" value="'.$lang['founder_upgrade_set_ftp'].'" />'.
					' &nbsp; <input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_reset'].'" /><br /><br />'
				);
			}
		}

		$paraftp = '';
		if($_GET['siteftp']) {
			foreach($_GET['siteftp'] as $k => $v) {
				$paraftp .= '&siteftp['.$k.']='.$v;
			}
		}
		if(!$_GET['startupgrade']) {
			if(!$_GET['backfile']) {
				cpmsg('upgrade_backuping', 'action='.$theurl.'&step=4&backfile=1&confirm='.$confirm.$paraftp, 'loading', '', false);
			}
			foreach($updatefilelist as $updatefile) {
				$destfile = DISCUZ_ROOT.$updatefile;
				$backfile = DISCUZ_ROOT.'./data/back/Discuz! X'.substr(DISCUZ_VERSION, 1).' Release['.DISCUZ_RELEASE.']/'.$updatefile;
				if(is_file($destfile)) {
					if(!$discuz_upgrade->copy_file($destfile, $backfile, 'file')) {
						cpmsg('upgrade_backup_error', '', 'error');
					}
				}
			}
			cpmsg('upgrade_backup_complete', 'action='.$theurl.'&step=4&startupgrade=1&confirm='.$confirm.$paraftp, 'loading', '', false);
		}

		$linkurl = 'action='.$theurl.'&step=4&startupgrade=1&confirm='.$confirm.$paraftp;
		foreach($updatefilelist as $updatefile) {
			$srcfile = DISCUZ_ROOT.'./data/update/Discuz! X'.$version.' Release['.$release.']/'.$updatefile;
			if($confirm == 'ftp') {
				$destfile = $updatefile;
			} else {
				$destfile = DISCUZ_ROOT.$updatefile;
			}
			if(!$discuz_upgrade->copy_file($srcfile, $destfile, $confirm)) {
				cpmsg('upgrade_ftp_upload_error', $linkurl, 'form');
			}
		}
		if($upgradeinfo['isupdatedb']) {
			$dbupdatefilearr = array('update.php', 'install/data/install.sql','install/data/install_data.sql');
			foreach($dbupdatefilearr as $dbupdatefile) {
				$srcfile = DISCUZ_ROOT.'./data/update/Discuz! X'.$version.' Release['.$release.']/'.$dbupdatefile;
				$dbupdatefile = $dbupdatefile == 'update.php' ? 'install/update.php' : $dbupdatefile;
				if($confirm == 'ftp') {
					$destfile = $dbupdatefile;
				} else {
					$destfile = DISCUZ_ROOT.$dbupdatefile;
				}
				if(!$discuz_upgrade->copy_file($srcfile, $destfile, $confirm)) {
					cpmsg('upgrade_copy_error', $linkurl, 'form');
				}
			}
			cpmsg('upgrade_file_successful', $_G['siteurl'].'install/update.php?step=prepare&from='.rawurlencode($_G['siteurl'].ADMINSCRIPT.'?action=upgrade&operation='.$operation.'&version='.$version.'&release='.$release.'&step=5'));
		}
		dheader('Location: '.ADMINSCRIPT.'?action=upgrade&operation='.$operation.'&version='.$version.'&release='.$release.'&step=5');

	} elseif($step == 5) {
		$file = DISCUZ_ROOT.'./data/update/Discuz! X'.$upgradeinfo['latestversion'].' Release['.$upgradeinfo['latestrelease'].']/updatelist.tmp';
		@unlink($file);
		cpmsg('upgrade_successful', '', 'succeed', array('version' => $version, 'release' => $release), '<script type="text/javascript">if(parent.document.getElementById(\'notice\')) parent.document.getElementById(\'notice\').style.display = \'none\';</script>');
	}
	showtablefooter();

} elseif($operation == 'check') {
	if(!intval($_GET['checking'])) {
		cpmsg('upgrade_checking', 'action=upgrade&operation=check&checking=1', 'loading', '', false);
	}
	$discuz_upgrade->check_upgrade();
	dheader('Location: '.ADMINSCRIPT.'?action=upgrade&operation=showupgrade');

} elseif($operation == 'showupgrade') {
	shownav('tools', 'nav_founder_upgrade');
	showsubmenu('nav_founder_upgrade');
	showtableheader();
	if(!$_G['setting']['upgrade']) {
		cpmsg('upgrade_latest_version', '', 'succeed');
	} else {
		$upgraderow = $patchrow = array();
		$charset = str_replace('-', '', strtoupper($_G['config']['output']['charset']));
		$dbversion = helper_dbtool::dbversion();
		$locale = $charset == 'BIG5' ? 'TC' : 'SC';
		foreach($_G['setting']['upgrade'] as $type => $upgrade) {
			$unupgrade = 0;
			if(version_compare($upgrade['phpversion'], PHP_VERSION) > 0 || version_compare($upgrade['mysqlversion'], $dbversion) > 0) {
				$unupgrade = 1;
			}

			$linkurl = ADMINSCRIPT.'?action=upgrade&operation='.$type.'&version='.$upgrade['latestversion'].'&locale='.$locale.'&charset='.$charset.'&release='.$upgrade['latestrelease'];
			if($unupgrade) {
				$upgraderow[] = showtablerow('', '', array('Discuz! X'.$upgrade['latestversion'].'_'.$locale.'_'.$charset.$lang['version'].' [Release '.$upgrade['latestrelease'].']'.($type == 'patch' ? '('.$lang['founder_upgrade_newword'].'release)' : '').'', $lang['founder_upgrade_require_config'].' php v'.PHP_VERSION.'MYSQL v'.$dbversion, ''), TRUE);
			} else {
				$upgraderow[] = showtablerow('', '', array('Discuz! X'.$upgrade['latestversion'].'_'.$locale.'_'.$charset.$lang['version'].' [Release '.$upgrade['latestrelease'].']'.($type == 'patch' ? '('.$lang['founder_upgrade_newword'].'release)' : '').'', '<input type="button" class="btn" onclick="confirm(\''.$lang['founder_upgrade_backup_remind'].'\') ? window.location.href=\''.$linkurl.'\' : \'\';" value="'.$lang['founder_upgrade_automatically'].'">', '<a href="'.$upgrade['official'].'" target="_blank">'.$lang['founder_upgrade_manually'].'</a>'), TRUE);
			}
			if($charset == 'UTF8') {
				$locale = 'TC';
				$linkurl = ADMINSCRIPT.'?action=upgrade&operation='.$type.'&version='.$upgrade['latestversion'].'&locale='.$locale.'&charset='.$charset.'&release='.$upgrade['latestrelease'];
				if($unupgrade) {
					$upgraderow[] = showtablerow('', '', array('Discuz! X'.$upgrade['latestversion'].'_'.$locale.'_'.$charset.$lang['version'].' [Release '.$upgrade['latestrelease'].']'.($type == 'patch' ? '('.$lang['founder_upgrade_newword'].'release)' : '').'', $lang['founder_upgrade_require_config'].' php v'.PHP_VERSION.'MYSQL v'.$dbversion, ''), TRUE);
				} else {
					$upgraderow[] = showtablerow('', '', array('Discuz! X'.$upgrade['latestversion'].'_'.$locale.'_'.$charset.$lang['version'].' [Release '.$upgrade['latestrelease'].']'.($type == 'patch' ? '('.$lang['founder_upgrade_newword'].'release)' : '').'', '<input type="button" class="btn" onclick="confirm(\''.$lang['founder_upgrade_backup_remind'].'\') ? window.location.href=\''.$linkurl.'\' : \'\';" value="'.$lang['founder_upgrade_automatically'].'">', '<a href="'.$upgrade['official'].'" target="_blank">'.$lang['founder_upgrade_manually'].'</a>'), TRUE);
				}
			}
		}
		showtablerow('class="header"','', array($lang['founder_upgrade_select_version'], '', ''));
		if($upgraderow) {
			foreach($upgraderow as $row) {
				echo $row;
			}
		}
		if($patchrow) {
			foreach($patchrow as $row) {
				echo $row;
			}
		}
	}
	showtablefooter();
}
?>