<?php
@date_default_timezone_set('Europe/Warsaw');

class bot_data_module implements BotModule {
	static $dni = array(
		'niedziela',
		'poniedziałek',
		'wtorek',
		'środa',
		'czwartek',
		'piątek',
		'sobota',
	);
	static $miesiace = array(
		1 => 'stycznia',
		'lutego',
		'marca',
		'kwietnia',
		'maja',
		'czerwca',
		'lipca',
		'sierpnia',
		'września',
		'października',
		'listopada',
		'grudnia',
	);
	
	function data($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		if(empty($arg)) {
			$data = time();
		} else {
			$data = calendar::parse_date($arg);
			if(!$data) {
				return new BotMsg('Podana data nie została rozpoznana<br />'."\n"
					. '<br />'."\n"
					. '<u>Przykłady:</u><br />'."\n"
					. 'data<br />'."\n"
					. 'data pojutrze<br />'."\n"
					. 'data 1.01.2009');
			}
		}
		
		if(date('d.m.Y') == date('d.m.Y', $data)) {
			$txt = 'Dziś jest ';
		} else {
			$txt = 'Wybrany dzień to ';
		}
		
		include('./data/data/data.php');
		
		$txt .= self::$dni[date('w', $data)].', '.date('j', $data).' '
			. self::$miesiace[date('n', $data)].' '.date('Y').' r., '
			. (date('z', $data)+1).' dzień roku.<br />'."\n"
			. '<br />'."\n";
		
		$msg->session->setClass('pogoda');
		if(!isset($msg->session->geo)) {
			$geo = array('lon' => '52.25', 'lat' => '21.0');
		} else {
			$geo = $msg->session->geo;
		}
		
		$txt .= 'Imieniny: '.$imieniny[date('n', $data)][date('j', $data)].'<br />'."\n"
			. 'Wschód Słońca: '.date_sunrise($data, SUNFUNCS_RET_STRING, $geo['lat'], $geo['lon'], 90.58, 1+date('I')).'<br />'."\n"
			. 'Zachód Słońca: '.date_sunset($data, SUNFUNCS_RET_STRING, $geo['lat'], $geo['lon'], 90.58, 1+date('I'));
		
		return new BotMsg($txt);
	}
	
	function imieniny($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		if(empty($arg)) {
			return new BotMsg('Nie podano imienia!<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykłady:</u><br />'."\n"
				. 'imieniny Adama<br />'."\n"
				. 'imieniny Ewy');
		}
		
		include('./data/data/imieniny.php');
		
		if(!isset($imiona[$arg])) {
			return new BotMsg('Nie znaleziono imienia w bazie. Pamiętaj, by podać imię w dopełniaczu liczby pojedynczej!<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykłady:</u><br />'."\n"
				. 'imieniny Adama<br />'."\n"
				. 'imieniny Ewy');
		}
		
		$txt = array();
		
		foreach($imiona[$arg] as $dzien) {
			$dzien = explode('.', $dzien);
			
			$txt[] = $dzien[0].' '.self::$miesiace[$dzien[1]];
		}
		
		return new BotMsg('Imieniny '.ucfirst($arg).' są '.implode(', ', $txt));
	}
}
?>