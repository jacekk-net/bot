<?php
class BotMessageGG extends BotMessage {
	function __construct() {
		if(!ctype_digit($_GET['from'])) {
			$_GET['from'] = 'invalid';
		}
		
		if(!ctype_digit($_GET['to'])) {
			$_GET['to'] = 'invalid';
		}
		
		$uid = 'Gadu-Gadu://'.$_GET['from'].'@gadu-gadu.pl';
		
		$this->userAlt = new BotUser($uid);
		$this->user = new BotUser($uid.'/'.$_GET['to']);
		$this->session = new BotSession($uid);
		$this->setText(file_get_contents('php://input'));
		
		if(isset($_GET['images'])) {
			$images = explode(',', $_GET['images']);
			foreach($images as $image) {
				if(strlen($image) == 16 && ctype_xdigit($image)) {
					$this->images[] = new BotImageGG($image);
				}
			}
		}
	}
}
?>