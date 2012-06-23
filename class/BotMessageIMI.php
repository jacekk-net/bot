<?php
class BotMessageIMI extends BotMessage {
	function __construct() {
		if(!ctype_print($_POST['userkey'])) {
			$_POST['userkey'] = 'invalid';
		}
		
		if(!ctype_print($_POST['user'])) {
			$_POST['user'] = 'invalid';
		}
		
		if(!ctype_alnum($_POST['network'])) {
			$_POST['network'] = 'invalid';
		}
		
		if(!ctype_print($_POST['botkey'])) {
			$_POST['botkey'] = 'invalid';
		}
		
		if($_POST['channel'] != 'public') {
			$_POST['channel'] = 'private';
		}
		
		$uid = 'IMified://'.$_POST['userkey'].'@userkey.imified.com';
		
		$this->userAlt = new BotUser($uid);
		$this->user = new BotUser('IMified://'.strtr($_POST['user'], array('@' => '\\@')).'@'.strtolower($_POST['network']).'.imified.com/'.$_POST['botkey'].'?'.$_POST['channel']);
		$this->session = new BotSession($uid);
		$this->setText($_POST['msg']);
	}
}
?>