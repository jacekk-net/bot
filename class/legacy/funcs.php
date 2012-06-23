<?php
class BotLegacyEnd extends Exception {}

class funcs {
	static function end() {
		throw new BotLegacyEnd();
	}
	
	static function utfToAscii($utf) {
		$utf = trim(str_replace('  ', ' ', $utf));
		return strtolower(iconv('utf-8', 'ascii//TRANSLIT', trim($utf)));
	}
}
?>