<?php
class BotMessageHTTP extends BotMessage {
	function __construct() {
		session_start();
		
		$uid = 'HTTP://'.session_id().'@localhost';
		
		$this->user = new BotUser($uid);
		$this->userAlt = $this->user;
		$this->session = new BotSession($uid);
		$this->setText($_GET['msg']);
	}
}
?>