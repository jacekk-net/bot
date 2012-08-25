<?php
class bot_data_module implements BotModule {
	static $dni = array(
		'niedziela',
		'poniedzia³ek',
		'wtorek',
		'¶roda',
		'czwartek',
		'pi±tek',
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
		'wrze¶nia',
		'pa¼dziernika',
		'listopada',
		'grudnia',
	);
	
	function data($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		if(empty($arg)) {
			$data = time();
		}
		else
		{
			$data = calendar::parse_date($arg);
			if(!$data) {
				return new BotMsg('Podana data nie zosta³a rozpoznana<br />'."\n"
					. '<br />'."\n"
					. '<u>Przyk³ady:</u><br />'."\n"
					. 'data<br />'."\n"
					. 'data pojutrze<br />'."\n"
					. 'data 1.01.2009');
			}
		}
		
		if(date('d.m.Y') == date('d.m.Y', $data)) {
			$txt = 'Dzi¶ jest ';
		}
		else
		{
			$txt = 'Wybrany dzieñ to ';
		}
		
		include('./data/data/data.php');
		
		$txt .= self::$dni[date('w', $data)].', '.date('j', $data).' '.self::$miesiace[date('n', $data)].' '.date('Y').' r., '.(date('z', $data)+1).' dzieñ roku.<br />'."\n"
			. '<br />'."\n";
		
		$msg->session->setClass('pogoda');
		if(!isset($msg->session->geo)) {
			$geo = array('lon' => '52.25', 'lat' => '21.0');
		}
		else
		{
			$geo = $msg->session->geo;
		}
		
		$txt .= 'Imieniny: '.$imieniny[date('n', $data)][date('j', $data)].'<br />'."\n"
			. 'Wschód S³oñca: '.date_sunrise($data, SUNFUNCS_RET_STRING, $geo['lat'], $geo['lon'], 90.58, 1+date('I')).'<br />'."\n"
			. 'Zachód S³oñca: '.date_sunset($data, SUNFUNCS_RET_STRING, $geo['lat'], $geo['lon'], 90.58, 1+date('I'));
		
		return new BotMsg($txt);
	}
	
	function imieniny($msg, $params) {
		$arg = funcs::utfToAscii($arg);
		
		if(empty($arg)) {
			return new BotMsg('Nie podano imienia!<br />'."\n"
				. '<br />'."\n"
				. '<u>Przyk³ady:</u><br />'."\n"
				. 'imieniny Adama<br />'."\n"
				. 'imieniny Ewy');
		}
		
		include('./data/data/imieniny.php');
		
		if(!isset($imiona[$arg])) {
			return new BotMsg('Nie znaleziono imienia w bazie. Pamiêtaj, by podaæ imiê w dope³niaczu liczby pojedynczej!<br />'."\n"
				. '<br />'."\n"
				. '<u>Przyk³ady:</u><br />'."\n"
				. 'imieniny Adama<br />'."\n"
				. 'imieniny Ewy');
		}
		
		foreach($imiona[$arg] as $dzien) {
			$dzien = explode('.', $dzien);
			
			$txt[] = $dzien[0].' '.self::$miesiace[$dzien[1]];
		}
		
		return new BotMsg('Imieniny '.ucfirst($arg).' s± '.implode(', ', $txt));
	}
}
?>