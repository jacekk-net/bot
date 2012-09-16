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
		$xml = $this->cache('http://film.interia.pl/kino/repertuar');
		if(!$xml) return FALSE;
		
		$xpath = new DOMXPath($xml);
		$dane = $xpath->query('//div[@id=\'cities\']//a');
		$return = array();
		
		foreach($dane as $miasto) {
			$href = $miasto->getAttribute('href');
			$data = trim($miasto->textContent);
			$return[$data] = substr($href, strpos($href, ',')+1);
		}
		
		return $return;
	}
	
	function getKina($miasto, $kiedy='') {
		$xml = $this->cache('http://film.interia.pl/kino/repertuar//kina,'.$miasto.($kiedy ? ','.$kiedy : ''));
		if(!$xml) return FALSE;
		
		$xpath = new DOMXPath($xml);
		$dane = $xpath->query('//div[@id=\'mainContent\']/table//th[@class=\'theatre\']/a[1]');
		$return = array();
		
		foreach($dane as $kino) {
			$name = trim($kino->textContent);
			$return[$name] = $kino->getAttribute('href');
		}
		
		return $return;
	}
	
	function getKino($miasto, $kino, $kiedy='') {
		$xml = $this->cache('http://film.interia.pl/kino/repertuar//kina,'.$miasto.($kiedy ? ','.$kiedy : ''));
		if(!$xml) return FALSE;
		
		$xpath = new DOMXPath($xml);
		$dane = $xpath->query('//div[@id=\'mainContent\']/table//a[@href=\''.$kino.'\']/../../following-sibling::tr');
		$return = array();
		
		foreach($dane as $film) {
			if(!$film->firstChild) {
				break;
			}
			if($film->firstChild->nodeName == 'th') {
				break;
			}
			if($film->firstChild->nodeName != 'td') {
				break;
			}
			
			$tds = $xpath->query('td', $film);
			$name = $xpath->query('a[1]', $tds->item(0));
			
			$more = array();
			$more_desc = array(
				's3d-movie' => '3D',
				'dubbing-movie' => 'dubbing',
			);
			$more_xml = $xpath->query('span[@class=\'reper\']/div', $tds->item(0));
			foreach($more_xml as $more_x) {
				$more_x = $more_x->getAttribute('class');
				if(isset($more_desc[$more_x])) {
					$more[] = $more_desc[$more_x];
				}
			}
			
			$return[] = array(
				trim($tds->item(1)->textContent),
				trim($name->item(0)->textContent),
				implode(', ', $more),
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
		$found = FALSE;
		$miasto_num = $miasto_nazw = '';
		
		if(!$miasta) {
			return new BotMsg('Przepraszamy, wystąpił bład przy pobieraniu listy miejscowości.');
		}
		
		foreach($miasta as $miasto => $numer) {
			$szukaj = funcs::utfToAscii($miasto);
			if(($pos = strpos($arg, $szukaj)) !== FALSE) {
				$found = TRUE;
				$miasto_nazw = htmlspecialchars($miasto);
				$miasto_num = $numer;
				
				$arg = trim(str_replace('  ', ' ', substr($arg, 0, $pos).substr($arg, $pos+strlen($szukaj))));
				break;
			}
		}
		
		if($found===FALSE && !empty($arg2)) {
			foreach($miasta as $miasto => $numer) {
				$szukaj = funcs::utfToAscii($miasto);
				if(($pos = strpos($arg2, $szukaj)) !== FALSE) {
					$found = TRUE;
					$miasto_nazw = htmlspecialchars($miasto);
					$miasto_num = $numer;
					
					$arg2 = trim(str_replace('  ', ' ', substr($arg2, 0, $pos).substr($arg2, $pos+strlen($szukaj))));
					break;
				}
			}
		}
		
		if($found === FALSE) {
			$txt = 'Wybrane miasto nie został odnalezione. Obsługiwane miejscowości:';
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
		$kina = self::getKina($miasto_num, $czas);
		$found = FALSE;
		$kino_num = $kino_nazw = '';
		
		if(!$kina) {
			return new BotMsg('Przepraszamy, wystąpił bład przy pobieraniu listy kin.');
		}
		
		if(empty($kina)) {
			return new BotMsg(($czas == '1' ? 'Jutro' : ($czas == '2' ? 'Pojutrze' : 'Dziś')).' żadne filmy nie są wyświetlane w podanym mieście.<br />'."\n"
				. '<br />'."\n"
				. '<u>Spróbuj też:</u><br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($arg).' '.($czas != '1' ? 'jutro' : ($czas != '2' ? 'pojutrze' : 'dziś')).'<br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($arg).' '.($czas != '' ? 'dziś' : ($czas != '2' ? 'pojutrze' : 'dziś')));
		}
		
		if(!empty($arg)) {
			foreach($kina as $kino => $kino_id) {
				if(levenshtein(funcs::utfToAscii($kino), $arg, 1, 1, 0) < 2) {
					$found = TRUE;
					$kino_num = $kino_id;
					$kino_nazw = htmlspecialchars($kino);
					break;
				}
			}
		}
		
		if($found===FALSE && !empty($arg2)) {
			foreach($kina as $kino => $kino_id) {
				if(levenshtein(funcs::utfToAscii($kino), $arg2, 1, 1, 0) < 2) {
					$found = TRUE;
					$kino_num = $kino_id;
					$kino_nazw = htmlspecialchars($kino);
					break;
				}
			}
		}
		
		if($found === FALSE) {
			$txt = (!empty($arg) ? 'Podany obiekt nie został znaleziony. ' : '').'Dostępne kina w pasujących miastach:';
			foreach($kina as $kino => $num) {
				$txt .= '<br />'."\n".$miasto_nazw.' '.htmlspecialchars($kino);
			}
			
			return new BotMsg($txt.'<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykład:</u><br />'."\n"
				. 'kino '.$miasto_nazw.' '.htmlspecialchars($kino).' '.($czas == '1' ? 'jutro' : ($czas == '2' ? 'pojutrze' : 'dziś')));
		}
		
		/*
			REPERTUAR
		*/
		$filmy = self::getKino($miasto_num, $kino_id, $czas);
		
		if(!$filmy) {
			return new BotMsg('Przepraszamy, wystąpił bład przy pobieraniu listy wyświelanych filmów.');
		}
		
		$txt = '<b>Repertuar dla kina '.$kino_nazw.' ('.$miasto_nazw.') na '.($czas == '1' ? 'jutro' : ($czas == '2' ? 'pojutrze' : 'dziś')).':</b>';
		if(empty($filmy)) {
			$txt .= '<br />'."\n".'Brak projekcji.';
		}
		else
		{
			foreach($filmy as $film) {
				$txt .= '<br />'."\n".htmlspecialchars($film[0]).' '.htmlspecialchars($film[1]).($film[2]!='' ? ' ('.htmlspecialchars($film[2]).')' : '');
			}
		}
		
		return new BotMsg($txt);
	}
}
?>