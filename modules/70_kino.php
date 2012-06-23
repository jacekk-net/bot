<?php
class kino implements module {
	static function register_cmd() {
		return array(
			'kino' => 'cmd_kino',
			'kina' => 'cmd_kino',
			'k' => 'cmd_kino',
			'rep' => 'cmd_kino',
			'repertuar' => 'cmd_kino',
			'u' => 'cmd_ustaw',
			'ustaw' => 'cmd_ustaw',
		);
	}
	
	static function help($cmd = NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('kino ', TRUE);
			GGapi::putRichText('miasto nazwa [kiedy]', FALSE, TRUE);
			GGapi::putRichText("\n".'   Repertuar kina ');
			GGapi::putRichText('nazwa', FALSE, TRUE);
			GGapi::putRichText("\n");
			GGapi::putRichText('ustaw ', TRUE);
			GGapi::putRichText('miasto nazwa', FALSE, TRUE);
			GGapi::putRichText("\n".'   Ustawia domyślne kino '."\n\n");
		}
		elseif(substr($cmd, 0, 1)=='u') {
			GGapi::putRichText('ustaw ', TRUE);
			GGapi::putRichText('miasto nazwa', FALSE, TRUE);
			GGapi::putRichText("\n".'   Ustawia domyślną lokalizację dla komendy kino na ');
			GGapi::putRichText('[nazwa]', FALSE, TRUE);
			GGapi::putRichText(' w mieście ');
			GGapi::putRichText('[miasto]', FALSE, TRUE);
		}
		else
		{
			GGapi::putRichText('kino ', TRUE);
			GGapi::putRichText('miasto nazwa [kiedy]', FALSE, TRUE);
			GGapi::putRichText("\n".'   Repertuar kina ');
			GGapi::putRichText('nazwa', FALSE, TRUE);
			GGapi::putRichText(' w mieście ');
			GGapi::putRichText('miasto', FALSE, TRUE);
			GGapi::putRichText(' na ');
			GGapi::putRichText('[kiedy]', FALSE, TRUE);
			GGapi::putRichText(' (dziś, jutro, pojutrze)');
		}
	}
	
	static function cmd_ustaw($cmd, $arg) {
		$arg = funcs::utfToAscii($arg);
		
		database::add($_GET['from'], 'kino', 'kino', $arg);
		
		if(empty($arg)) {
			GGapi::putText('Usunięto domyślne kino. Aby otrzymać listę dostępnych obiektów wpisz:'."\n");
			GGapi::putRichText('kino', TRUE);
			GGapi::putRichText(' Miasto', FALSE, TRUE);
		}
		else
		{
			GGapi::putText('Podane kino zostało zapisane jako domyślne. Sprawdź, czy jest poprawne wpisując:'."\n");
			GGapi::putRichText('kino', TRUE);
		}
	}
	
	static function cmd_kino($cmd, $arg) {
		$arg = funcs::utfToAscii($arg);
		if(empty($arg)) {
			$arg = database::get($_GET['from'], 'kino', 'kino');
			if(empty($arg)) {
				GGapi::putText('Podaj nazwę miejscowości i kina.'."\n\n");
				GGapi::putRichText('Przykłady', FALSE, FALSE, TRUE);
				GGapi::putRichText("\n".'kino Kraków'."\n".'kino Kraków Multikino');
				return FALSE;
			}
		}
		else
		{
			$arg2 = database::get($_GET['from'], 'kino', 'kino');
		}
		
		/*
			MIASTO
		*/
		$miasta = self::getMiasta(); $found = FALSE;
		
		if(!$miasta) {
			GGapi::putText('Przepraszamy, wystąpił bład przy pobieraniu listy miejscowości.');
			return FALSE;
		}
		
		foreach($miasta as $miasto => $miasto_num) {
			if(($pos = strpos($arg, funcs::utfToAscii($miasto))) !== FALSE) {
				$found = $miasto_num;
				$arg = trim(str_replace('  ', ' ', substr($arg, 0, $pos).substr($arg, $pos+strlen(funcs::utfToAscii($miasto)))));
				break;
			}
		}
		
		if($found===FALSE && !empty($arg2)) {
			foreach($miasta as $miasto => $miasto_num) {
				if(($pos = strpos($arg2, funcs::utfToAscii($miasto))) !== FALSE) {
					$found = $miasto_num;
					$arg2 = trim(str_replace('  ', ' ', substr($arg2, 0, $pos).substr($arg2, $pos+strlen(funcs::utfToAscii($miasto)))));
					break;
				}
			}
		}
		
		if($found === FALSE) {
			$txt = 'Wybrane miasto nie został odnalezione. Dostępne miejscowości:';
			foreach($miasta as $miasto => $num) {
				$txt .= "\n".$miasto;
			}
			GGapi::putText($txt);
			return FALSE;
		}
		
		
		/*
			KIEDY
		*/
		$tydzien = array('niedziela', 'poniedzialek', 'wtorek', 'sroda', 'czwartek', 'piatek', 'sobota');
		$data = array(
			'dzis' => '',
			'teraz' => '',
			'jutro' => '1',
			'pojutrze' => '2',
			'po jutrze' => '2',
		);
		for($i=0; $i<3; $i++) {
			$data[date('d.m', strtotime('+'.$i.' day'))] = ($i ? $i : '');
			$data[date('j.m', strtotime('+'.$i.' day'))] = ($i ? $i : '');
		}
		
		$czas = '';
		foreach($data as $known => $d) {
			if(($pos = strpos($arg, $known))!==FALSE) {
				$czas = $d;
				$arg = trim(str_replace('  ', ' ', substr($arg, 0, $pos).substr($arg, $pos+strlen($known))));
				break;
			}
		}
		
		/*
			KINO
		*/
		$kina = self::getKina($miasto_num, $czas); $found = FALSE;
		
		if(!$kina) {
			GGapi::putText('Przepraszamy, wystąpił bład przy pobieraniu listy kin.');
			return FALSE;
		}
		
		if(empty($kina)) {
			GGapi::putText(($czas == '1' ? 'Jutro' : ($czas == '2' ? 'Pojutrze' : 'Dziś')).' żadne filmy nie są wyświetlane w podanym mieście.'."\n\n");
			GGapi::putRichText('Spróbuj też:', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'kino '.$miasto.' '.$kino.' '.($czas != '1' ? 'jutro' : ($czas != '2' ? 'pojutrze' : 'dziś')).
					"\n".'kino '.$miasto.' '.$kino.' '.($czas != '' ? 'dziś' : ($czas != '2' ? 'pojutrze' : 'dziś')));
			return FALSE;
		}
		
		if(!empty($arg)) {
			foreach($kina as $kino => $kino_id) {
				if(levenshtein(funcs::utfToAscii($kino), $arg, 1, 1, 0) < 2) {
					$found = $kino_id;
					break;
				}
			}
		}
		
		if($found===FALSE && !empty($arg2)) {
			foreach($kina as $kino => $kino_id) {
				if(levenshtein(funcs::utfToAscii($kino), $arg2, 1, 1, 0) < 2) {
					$found = $kino_id;
					break;
				}
			}
		}
		
		if($found === FALSE) {
			$txt = (!empty($arg) ? 'Podany obiekt nie został znaleziony. ' : '').'Dostępne kina w pasujących miastach:';
			foreach($kina as $kino => $num) {
				$txt .= "\n".$miasto.' '.$kino;
			}
			GGapi::putText($txt."\n\n");
			GGapi::putRichText('Przykład:', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'kino '.$miasto.' '.$kino.' '.($czas == '1' ? 'jutro' : ($czas == '2' ? 'pojutrze' : 'dziś')));
			return FALSE;
		}
		
		/*
			REPERTUAR
		*/
		$filmy = self::getKino($miasto_num, $kino_id, $czas);
		
		if(!$filmy) {
			GGapi::putText('Przepraszamy, wystąpił bład przy pobieraniu listy wyświelanych filmów.');
			return FALSE;
		}
		
		GGapi::putRichText('Repertuar dla kina '.$kino.' ('.$miasto.') na '.($czas == '1' ? 'jutro' : ($czas == '2' ? 'pojutrze' : 'dziś')).':', TRUE);
		if(empty($filmy)) {
			GGapi::putRichText("\n".'Brak repertuaru');
		}
		else
		{
			foreach($filmy as $film) {
				$txt .= "\n".$film[0].' '.$film[1];
			}
		}
		GGapi::putRichText($txt);
	}
	
	static function tidy($code) {
		$tidy = new tidy;
		$tidy->parseString($code, array(
			'add-xml-decl'	=> true,
			'output-xml'	=> true,
		), 'raw');
		$tidy->CleanRepair();
		return str_replace(array('&nbsp;', 'margin:="'), array(' ', 'margin="'), (string)$tidy);
	}
	
	static function cache($url) {
		$time = '+2 hour'; $dir = './data/kino/cache/';
		
		if(file_exists($dir.md5($url))) {
			$mtime = @filemtime($dir.md5($url));
		}
		if($mtime && $mtime > strtotime('today '.$time) && $mtime < strtotime('tomorrow '.$time)) {
			return file_get_contents($dir.md5($url));
		}
		else
		{
			$dane = @file_get_contents($url);
			if(!$dane) {
				trigger_error('Nie udało się pobrać repertuaru kina, przepraszamy.', E_USER_ERROR);
			}
			$dane = self::tidy($dane);
			file_put_contents($dir.md5($url), $dane);
			return $dane;
		}
	}
	
	static function getMiasta() {
		$return = array();
		$dane = @simplexml_load_string(self::cache('http://film.interia.pl/kino/repertuar'));
		if(!$dane) return FALSE;
		$dane = $dane->xpath('//div[@id=\'cities\']//a');
		foreach($dane as $miasto) {
			$miasto['href'] = (string)$miasto['href'];
			$return[str_replace(array("\r\n", "\r", "\n"), array(' ', ' ', ' '), (string)$miasto)] = substr($miasto['href'], strpos($miasto['href'], ',')+1);
		}
		
		return $return;
	}
	
	static function getKina($miasto, $kiedy='') {
		$return = array();
		$dane = @simplexml_load_string(self::cache('http://film.interia.pl/kino/repertuar//kina,'.$miasto.($kiedy ? ','.$kiedy : '')));
		if(!$dane) return FALSE;
		$dane = $dane->xpath('//div[@id=\'mainContent\']/table//th[@class=\'theatre\']/a[1]');
		if(!empty($dane)) {
			foreach($dane as $kino) {
				$return[str_replace(array("\r\n", "\r", "\n"), array(' ', ' ', ' '), (string)$kino)] = (string)$kino['name'];
			}
		}
		return $return;
	}
	
	static function getKino($miasto, $kino, $kiedy='') {
		$return = array();
		$dane = @simplexml_load_string(self::cache('http://film.interia.pl/kino/repertuar//kina,'.$miasto.($kiedy ? ','.$kiedy : '')));
		if(!$dane) return FALSE;
		$dane = $dane->xpath('//div[@id=\'mainContent\']/table//a[@name=\''.$kino.'\']/../../following-sibling::tr');
		if(!empty($dane)) {
			foreach($dane as $film) {
				if($film->th) break;
				$return[] = array((string)$film->td[1], str_replace(array("\r\n", "\r", "\n"), array(' ', ' ', ' '), (string)$film->td[0]->a));
			}
		}
		return $return;
	}
}
?>