<?php
class data implements module {
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
	
	static function register_cmd() {
		return array(
			'data' => 'cmd_data',
			'dzien' => 'cmd_data',
			'd' => 'cmd_data',
			'imieniny' => 'cmd_imieniny',
			'im' => 'cmd_imieniny',
			'i' => 'cmd_imieniny',
		);
	}
	
	static function help($cmd = NULL) {
		switch($cmd) {
			case 'data':
			case 'd':
				GGapi::putRichText('data', TRUE);
				GGapi::putRichText(' [dzień]', FALSE, TRUE);
				GGapi::putRichText(' (alias: ');
				GGapi::putRichText('d', TRUE);
				GGapi::putRichText(')'."\n".'   Zwraca informacje (wschód/zachód słońca, imieniny) o dniu dzisiejszym lub podanym dniu ');
				GGapi::putRichText('[dzieĹ]', FALSE, TRUE);
				GGapi::putRichText("\n\n".'Przykłady:', FALSE, FALSE, TRUE);
				GGapi::putRichText("\n".'   data'."\n".'   data pojutrze'."\n".'   data 1.01.2009');
			break;
			case 'imieniny':
			case 'im':
			case 'i':
				GGapi::putRichText('imieniny', TRUE);
				GGapi::putRichText(' imie', FALSE, TRUE);
				GGapi::putRichText(' (alias: ');
				GGapi::putRichText('i', TRUE);
				GGapi::putRichText(')'."\n".'   Podaje dni, w których imieniny obchodzi osoba o imieniu ');
				GGapi::putRichText('imie', FALSE, TRUE);
				GGapi::putRichText("\n\n".'Przykłady:', FALSE, FALSE, TRUE);
				GGapi::putRichText("\n".'   imieniny Adama'."\n".'   imieniny Ewy');
			break;
			default:
				GGapi::putRichText('data', TRUE);
				GGapi::putRichText(' [dzien]', FALSE, TRUE);
				GGapi::putRichText("\n".'   Informacje o danym dniu'."\n");
				
				GGapi::putRichText('imieniny ', TRUE);
				GGapi::putRichText(' imie', FALSE, TRUE);
				GGapi::putRichText("\n".'   Kiedy ');
				GGapi::putRichText('imie', FALSE, TRUE);
				GGapi::putRichText(' obchodzi imieniny'."\n\n");
			break;
		}
	}
	
	static function cmd_data($name, $arg) {
		if(empty($arg)) {
			$data = time();
		}
		else
		{
			$data = calendar::parse_date($arg);
			if(!$data) {
				GGapi::putText('Podana data nie została rozpoznana'."\n\n");
				GGapi::putRichText('Przykłady:', FALSE, FALSE, TRUE);
				GGapi::putRichText("\n".'   data'."\n".'   data pojutrze'."\n".'   data 1.01.2009');
				
				return;
			}
		}
		
		if(date('d.m.Y') == date('d.m.Y', $data)) {
			$txt = 'Dziś jest ';
		}
		else
		{
			$txt = 'Wybrany dzień to ';
		}
		
		include('./data/data/data.php');
		
		$txt .= self::$dni[date('w', $data)].', '.date('j', $data).' '.self::$miesiace[date('n', $data)].' '.date('Y').' r., '.(date('z', $data)+1).' dzień roku.';
		
		$geo = database::get($_GET['from'], 'pogoda', 'geo');
		if(!$geo) {
			$geo = array('lon' => '52.25', 'lat' => '21.0');
		}
		
		$txt .= "\n\n".'Imieniny: '.$imieniny[date('n', $data)][date('j', $data)]."\n".'Wschód Słońca: '.date_sunrise($data, SUNFUNCS_RET_STRING, $geo['lat'], $geo['lon'], 90.58, 1+date('I'))."\n".'Zachód Słońca: '.date_sunset($data, SUNFUNCS_RET_STRING, $geo['lat'], $geo['lon'], 90.58, 1+date('I'));
		
		GGapi::putText($txt);
	}
	
	static function cmd_imieniny($name, $arg) {
		$arg = funcs::utfToAscii($arg);
		
		if(empty($arg)) {
			GGapi::putText('Nie podano imienia!'."\n\n");
			GGapi::putRichText('Przykłady:', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'   imieniny Adama'."\n".'   imieniny Ewy');
			
			return;
		}
		
		include('./data/data/imieniny.php');
		
		if(!isset($imiona[$arg])) {
			GGapi::putText('Nie znaleziono imienia w bazie. Pamiętaj, by podać imię w dopełniaczu liczby pojedynczej!'."\n\n");
			GGapi::putRichText('Przykłady:', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'   imieniny Adama'."\n".'   imieniny Ewy');
			
			return;
		}
		
		foreach($imiona[$arg] as $dzien) {
			$dzien = explode('.', $dzien);
			
			$txt[] = $dzien[0].' '.self::$miesiace[$dzien[1]];
		}
		
		GGapi::putText('Imieniny '.ucfirst($arg).' są '.implode(', ', $txt));
	}
}
?>