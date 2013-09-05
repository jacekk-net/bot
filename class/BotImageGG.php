<?php
class BotImageGG extends BotImage {
	protected $hash;
	
	function __construct($hash) {
		$this->hash = $hash;
	}
	
	function getHash() {
		return $this->hash;
	}
	
	function getImage() {
		return imagecreatefromstring($this->getImageData());
	}
	
	function getImageData() {
		if($this->data === NULL) {
			$push = new BotAPIGG();
			$this->data = $push->getImage($this->hash);
		}
		
		return $this->data;
	}
}
?>