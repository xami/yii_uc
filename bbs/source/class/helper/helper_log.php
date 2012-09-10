<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_log.php 27957 2012-02-17 08:48:07Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_log {

	public static function runlog($file, $message, $halt=0) {
		global $_G;

		$nowurl = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
		$log = dgmdate($_G['timestamp'], 'Y-m-d H:i:s')."\t".$_G['clientip']."\t$_G[uid]\t{$nowurl}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($message))."\n";
		helper_log::writelog($file, $log);
		if($halt) {
			exit();
		}
	}


	public static function writelog($file, $log) {
		global $_G;
		$yearmonth = dgmdate(TIMESTAMP, 'Ym', $_G['setting']['timeoffset']);
		$logdir = DISCUZ_ROOT.'./data/log/';
		$logfile = $logdir.$yearmonth.'_'.$file.'.php';
		if(@filesize($logfile) > 2048000) {
			$dir = opendir($logdir);
			$length = strlen($file);
			$maxid = $id = 0;
			while($entry = readdir($dir)) {
				if(strpos($entry, $yearmonth.'_'.$file) !== false) {
					$id = intval(substr($entry, $length + 8, -4));
					$id > $maxid && $maxid = $id;
				}
			}
			closedir($dir);

			$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
			@rename($logfile, $logfilebak);
		}
		if($fp = @fopen($logfile, 'a')) {
			@flock($fp, 2);
			$log = is_array($log) ? $log : array($log);
			foreach($log as $tmp) {
				fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
			}
			fclose($fp);
		}
	}


	public static function useractionlog($uid, $action) {
		$uid = intval($uid);
		if(empty($uid) || empty($action)) {
			return false;
		}
		$action = getuseraction($action);
		C::t('common_member_action_log')->insert(array('uid' => $uid, 'action' => $action, 'dateline' => TIMESTAMP));
		return true;
	}

	public static function getuseraction($var) {
		$value = false;
		$ops = array('tid', 'pid', 'blogid', 'picid', 'doid', 'sid', 'aid', 'uid_cid', 'blogid_cid', 'sid_cid', 'picid_cid', 'aid_cid', 'topicid_cid', 'pmid');
		if(is_numeric($var)) {
			$value = isset($ops[$var]) ? $ops[$var] : false;
		} else {
			$value = array_search($var, $ops);
		}
		return $value;
	}

	public static function coredebuglog($errno, $errstr, $errfile, $errline) {
		$log = "Errno:[$errno]\n\rErrstr:[$errstr]\n\rFile:[".str_replace(DISCUZ_ROOT, '', $errfile)."]\n\rLine:[$errline]\n\r";
		$show = "Stack trace:\n\r";
		$debug_backtrace = debug_backtrace();
		array_pop($debug_backtrace);
		krsort($debug_backtrace);
		foreach ($debug_backtrace as $k => $error) {
			$file = str_replace(DISCUZ_ROOT, '', $error['file']);
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';
			$error['line'] = sprintf('%04d', $error['line']);

			$show .= "[Line: $error[line]]".$file."($func)\n";
		}
		helper_log::runlog('discuz_core_debug', $log.$show);
		if($errno == E_ERROR || $errno == E_CORE_ERROR || $errno == E_COMPILE_ERROR || $errno == E_PARSE) {
			helper_sysmessage::show(nl2br($log.$show), 'System Error');
		}
		return false;
	}

}

?>