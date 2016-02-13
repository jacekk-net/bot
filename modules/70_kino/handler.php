<?php
class bot_kino_module implements BotModule {
	function cache($url) {
		$down = new DownloadHelper($url);
		$dane = $down->exec();
		
		libxml_use_internal_errors(TRUE);
		
		$dom = new DOMDocument();
		if(!$dom->loadHTML($dane)) {
			libxml_use_internal_errors(FALSE);
			$down->cacheFor(1800);
			return FALSE;
		}
		
		$down->cacheUntil(strtotime('tomorrow midnight'));
		
		return $dom;
	}
	
	function getMiasta() {
		$xml = $this->cache('http://film.interia.pl/repertuar-kin');
		if(!$xml) return FALSE;
		
		$xpath = new DOMXPath($xml);
		$dane = $xpath->query('//a[contains(@class, "showtimes-city")]');
		$return = array();
		
		foreach($dane as $miasto) {
			$href = $miasto->getAttribute('href');
			$data = trim($miasto->textContent);
			$return[$data] = substr($href, strrpos($href, ',')+1);
		}
		
		return $return;
	}
	
	function getKina($miasto, $kiedy='') {
		$xml = $this->cache('http://film.interia.pl/repertuar-kin/miasto-a,cId,'.$miasto.($kiedy ? ',when,'.$kiedy : ''));
		if(!$xml) return FALSE;
		
		$xpath = new DOMXPath($xml);
		$dane = $xpath->query('//div[@id="content"]//div[@class="showtimes-accordion-heading"]//p[@class="showtimes-cinema-name"]');
		$return = array();
		
		foreach($dane as $id => $kino) {
			$name = trim($kino->textContent);
			$return[$name] = $id;
		}
		
		return $return;
	}
	
	function getKino($miasto, $kino, $kiedy='') {
		$xml = $this->cache('http://film.interia.pl/repertuar-kin/miasto-a,cId,'.$miasto.($kiedy ? ',when,'.$kiedy : ''));
		if(!$xml) return FALSE;
		
		$xpath = new DOMXPath($xml);
		$dane = $xpath->query('//div[@id=\'content\']//div[@class=\'showtimes-accordion-body\']');
		$return = array();
		
		$dane = $xpath->query('.//div[@class=\'showtimes-cinema-movie\']', $dane[$kino]);
		
		foreach($dane as $film) {
			$title = $xpath->query('.//span[@class=\'showtimes-cinema-movie-title\']', $film);
			$hours = $xpath->query('.//span[@data-time]', $film);
			
			$hours_ret = array();
			foreach($hours as $hour) {
				$sub = array();
				if($xpath->query('.//span[@showtimes-cinema-movie-dubbing]', $hour)) {
					$sub[] = 'DUB';
				}
				if($xpath->query('.//span[@showtimes-cinema-movie-3d]', $hour)) {
					$sub[] = '3D';
				}
				
				$hour = $hour->getAttribute('data-time');
				
				$hours_ret[] = array(substr($hour, 0, -2).':'.substr($hour, -2), $sub);
			}
			
			$return[] = array(
				trim($title->item(0)->textContent),
				$hours_ret
			);
		}
		
		return $return;
	}
	
	function ustaw($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		$msg->session->setClass('kino');
		
		if(empty($arg)) {
			unset($msg->session->kino);
			return new BotMsg('Ustawienie domyślnego kino zostało usunięte. Aby ponownie je ustawić, wpisz:<br />'."\n"
				. 'ustaw <i>miasto kino</i>');
		}
		else
		{
			$msg->session->kino = $arg;
			return new BotMsg('Podane miasto/kino zostało zapisane jako domyślne. Sprawdź, czy jest poprawne wysyłając komendę <b>kino</b> bez argumentów.');
		}
	}
	
	function handle($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		$msg->session->setClass('kino');
		
		if(empty($arg)) {
			$arg = $msg->session->kino;
			if(empty($arg)) {
				return new BotMsg('Podaj nazwę miejscowości i kina.<br />'."\n"
					. '<br />'."\n"
					. '<u>Przykłady:</u><br />'."\n"
					. 'kino Kraków<br />'."\n"
					. 'kino Kraków Multikino');
			}
		}
		else
		{
			$arg2 = $msg->session->kino;
		}
		
		/*
			MIASTO
		*/
		$miasta = self::getMiasta();
		$miasto_num = $miasto_nazw = '';
		
		if(!$miasta) {
			return new BotMsg('Przepraszamy, wystąpił bład przy pobieraniu listy miejscowości.');
		}
		
		foreach($miasta as $miasto => $numer) {
			$szukaj = funcs::utfToAscii($miasto);
			if(($pos = strpos($arg, $szukaj)) !== FALSE) {
				$miasto_nazw = htmlspecialchars($miasto);
				$miasto_num = $numer;
				
				$arg = trim(str_replace('  ', ' ', substr($arg, 0, $pos).substr($arg, $pos+strlen($szukaj))));
				break;
			}
		}
		
		if($miasto_num === '' && !empty($arg2)) {
			foreach($miasta as $miasto => $numer) {
				$szukaj = funcs::utfToAscii($miasto);
				if(($pos = strpos($arg2, $szukaj)) !== FALSE) {
					$miasto_nazw = htmlspecialchars($miasto);
					$miasto_num = $numer;
					
					$arg2 = trim(str_replace('  ', ' ', substr($arg2, 0, $pos).substr($arg2, $pos+strlen($szukaj))));
					break;
				}
			}
		}
		
		if($miasto_num === '') {
			$txt = 'Wybrane miasto nie został odnalezione. Obsługiwane miejscowości:';
			$miasto = 'Warszawa';
			foreach($miasta as $miasto => $num) {
				$txt .= '<br />'."\n".htmlspecialchars($miasto);
			}
			$txt .= '<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykład:</u><br />'."\n"
				. 'kino '.htmlspecialchars($miasto);
			return new BotMsg($txt);
		}
		
		
		/*
			KIEDY
		*/
		$tydzien = array('niedziela', 'poniedzialek', 'wtorek', 'sroda', 'czwartek', 'piatek', 'sobota');
		$data = array(
			'dzis' => '',
			'teraz' => '',
			'jutro' => 'jutro',
			'pojutrze' => 'pojutrze',
			'po jutrze' => 'pojutrze',
		);
		$data[date('d.m')] = '';
		$data[date('j.m')] = '';
		$data[$tydzien[date('w')]] = '';
		$data[date('d.m', strtotime('+1 day'))] = 'jutro';
		$data[date('j.m', strtotime('+1 day'))] = 'jutro';
		$data[$tydzien[date('w', strtotime('+1 day'))]] = 'jutro';
		$data[date('d.m', strtotime('+2 day'))] = 'pojutrze';
		$data[date('j.m', strtotime('+2 day'))] = 'pojutrze';
		$data[$tydzien[date('w', strtotime('+2 day'))]] = 'pojutrze';
		
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
		$kina = self::getKina($miasto_num, $czas);
		$kino_num = $kino_nazw = '';
		
		if(!$kina) {
			$txt = 'Brak seansów w tym mieście w wybranym dniu.';
			$txt .= '<br />'."\n"
				. '<br />'."\n"
				. '<u>Spróbuj też:</u><br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($arg).' '.($czas != 'dzis' ? 'jutro' : ($czas != '2' ? 'pojutrze' : 'dziś')).'<br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($arg).' '.($czas != '' ? 'dziś' : ($czas != '2' ? 'pojutrze' : 'dziś'));
			return new BotMsg($txt);
		}
		
		if(empty($kina)) {
			return new BotMsg(($czas == '' ? 'Dziś' : ucfirst($czas)).' żadne filmy nie są wyświetlane w podanym mieście.<br />'."\n"
				. '<br />'."\n"
				. '<u>Spróbuj też:</u><br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($arg).' '.($czas != 'dzis' ? 'jutro' : ($czas != '2' ? 'pojutrze' : 'dziś')).'<br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($arg).' '.($czas != '' ? 'dziś' : ($czas != '2' ? 'pojutrze' : 'dziś')));
		}
		
		if(!empty($arg)) {
			foreach($kina as $kino => $kino_id) {
				if(levenshtein(funcs::utfToAscii($kino), $arg, 1, 1, 0) < 2) {
					$kino_num = $kino_id;
					$kino_nazw = htmlspecialchars($kino);
					break;
				}
			}
		}
		
		if($kino_num === '' && !empty($arg2)) {
			foreach($kina as $kino => $kino_id) {
				if(levenshtein(funcs::utfToAscii($kino), $arg2, 1, 1, 0) < 2) {
					$kino_num = $kino_id;
					$kino_nazw = htmlspecialchars($kino);
					break;
				}
			}
		}
		
		if($kino_num === '') {
			$txt = (!empty($arg) ? 'Podany obiekt nie został znaleziony. ' : '').'Dostępne kina w pasujących miastach:';
			$kino = '';
			foreach($kina as $kino => $num) {
				$txt .= '<br />'."\n".$miasto_nazw.' '.htmlspecialchars($kino);
			}
			
			return new BotMsg($txt.'<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykład:</u><br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($kino).' '.($czas == '' ? 'dziś' : $czas));
		}
		
		/*
			REPERTUAR
		*/
		$filmy = self::getKino($miasto_num, $kino_num, $czas);
		
		if(!$filmy) {
			return new BotMsg('Przepraszamy, wystąpił bład przy pobieraniu listy wyświelanych filmów.');
		}
		
		$txt = '<b>Repertuar dla kina '.$kino_nazw.' ('.$miasto_nazw.') na '.($czas == '' ? 'dziś' : $czas).':</b><br />'."\n";
		if(empty($filmy)) {
			$txt .= '<br />'."\n".'Brak projekcji.';
		}
		else
		{
			foreach($filmy as $film) {
				$txt .= '<br />'."\n".htmlspecialchars($film[0]).'<br />'."\n";
				$info = array();
				foreach($film[1] as $dane) {
					$info[] = '<b>'.$dane[0].'</b>'.($dane[1] ? ' ('.implode(', ', $dane[1]).')' : '');
				}
				$txt .= implode(', ', $info)."\n".'<br />';
			}
		}
		
		return new BotMsg($txt);
	}
}
?>