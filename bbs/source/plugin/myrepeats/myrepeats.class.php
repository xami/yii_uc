<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myrepeats.class.php 23901 2011-08-15 10:08:59Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_myrepeats {

	var $value = array();

	function plugin_myrepeats() {
		global $_G;
		if(!$_G['uid']) {
			return;
		}

		$this->value['global_usernav_extra1'] = '<script>'.
			'function showmyrepeats() {if(!$(\'myrepeats_menu\')) {'.
			'menu=document.createElement(\'div\');menu.id=\'myrepeats_menu\';menu.style.display=\'none\';menu.className=\'p_pop\';'.
			'$(\'append_parent\').appendChild(menu);'.
			'ajaxget(\'plugin.php?id=myrepeats:switch&list=yes\',\'myrepeats_menu\',\'ajaxwaitid\');}'.
			'showMenu({\'ctrlid\':\'myrepeats\',\'duration\':2});}'.
			'</script>'.
			'<span class="pipe">|</span><a id="myrepeats" href="home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp" class="showmenu cur1" onmouseover="delayShow(this, showmyrepeats)">'.lang('plugin/myrepeats', 'switch').'</a>'."\n";
	}

	function global_usernav_extra1() {
		return $this->value['global_usernav_extra1'];
	}

}

?>