<?php
class GGapi {
	const STATUS_DOSTEPNY = 'back';
	const STATUS_ZAJETY = 'away';
	const STATUS_NIEWIDOCZNY = 'invisible';
	
	protected static $color = '#000000';
	protected static $msg = NULL;
	protected static $len = 0;
	
	static function init() {
		if(!self::$msg) {
			self::$msg = new BotMsg;
		}
	}
	
	static function getLength() {
		return self::$len;
	}
	
	static function putImage($image) {
		self::init();
		
		if(!file_exists($image) OR !is_readable($image)) {
			return FALSE;
		}
		
		self::$msg->a('<img src="'.htmlspecialchars($image).'" />');
		
		return TRUE;
	}
	
	static function putText($text) {
		self::init();
		
		self::$msg->a(nl2br(htmlspecialchars($text)));
		self::$len += strlen($text);
		
		return TRUE;
	}
	
	static function putRichText($text, $bold=FALSE, $italic=FALSE, $underline=FALSE, $R=0, $G=0, $B=0) {
		self::init();
		
		self::$len += strlen($text);
		$text = nl2br(htmlspecialchars($text));
		
		if($bold) {
			$text = '<b>'.$text.'</b>';
		}
		if($italic) {
			$text = '<i>'.$text.'</i>';
		}
		if($underline) {
			$text = '<u>'.$text.'</u>';
		}
		
		$color = '#'.sprintf('%02x%02x%02x', $R%256, $G%256, $B%256);
		
		if($color != self::$color) {
			$text = '<span color="'.$color.'">'.$text.'</span>';
		}
		
		self::$msg->a($text);
		
		return TRUE;
	}
	
	static function getResponse() {
		return self::$msg;
	}
	
	static function setStatus($status=NULL, $desc='') {}
	static function getStatusResponse($status=NULL, $desc='') {}
	static function sendResponse() {}
	static function antiFlood($numer=NULL) {}
	
	static function getPublicData($number=NULL) {
		if($number === NULL) $number = $_GET['from'];
		if(!ctype_digit($number)) return FALSE;
		
		$data = @file_get_contents('http://api.gadu-gadu.pl/users/'.$number.'.xml');
		if(!$data) return FALSE;
		
		$data = @simplexml_load_string($data);
		if(!$data) return FALSE;
		
		return (array)$data->users->user;
	}
}
?>