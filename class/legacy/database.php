<?php
class database {
	static function init($numer, $modul) {
		if(ctype_digit($numer)) {
			$user = 'Gadu-Gadu://'.$numer.'@gadu-gadu.pl';
		}
		else
		{
			$user = 'IMified://'.strtr($numer, array('@' => '\\@')).'@userkey.imified.com';
		}
		
		$data = new BotSession($user);
		$data->setClass($modul);
		return $data;
	}
	
	static function add($numer, $modul, $name, $value) {
		$data = self::init($numer, $modul);
		
		return $data->__set($name, $value);
	}
	
	static function del($numer, $modul, $name) {
		$data = self::init($numer, $modul);
		
		return $data->__unset($name);
	}
	
	static function delAll($numer, $modul) {
		$data = self::init($numer, $modul);
		
		return $data->truncate($name, $value);
	}
	
	static function addArray($numer, $modul, $name_value) {
		$data = self::init($numer, $modul);
		
		return $data->push($name_value);
	}
	
	static function get($numer, $modul, $name) {
		$data = self::init($numer, $modul);
		
		return $data->__get($name);
	}
	
	static function getArray($numer, $modul) {
		$data = self::init($numer, $modul);
		
		return $data->pull();
	}
}
?>