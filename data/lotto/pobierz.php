<?php
echo STAR.'Pobieranie danych...';

class lotto {
	// Zawartość strony http://lotto.pl/wyniki-gier
	protected $strona = NULL;
	
	// Lista gier.
	protected $gry = array(
		// nazwa => array(ilość liczb, plus?, lottoplus?)
		'lotto' => array(6, FALSE, 6),
		'mini-lotto' => array(5, FALSE, 0),
		'kaskada' => array(12, FALSE, 0),
		'multi-multi' => array(20, TRUE, 0),
		'joker' => array(5, FALSE, 0)
	);
	
	// Spróbuj pobrać stronę http://lotto.pl/wyniki-gier
	// do zmiennej $this->strona z użyciem pliku cache.
	function __construct() {
		$cache = 'lotto_cache.txt';
		
		// Sprawdź, czy da się skorzystać z pliku cache.
		if( ( !file_exists($cache) AND !is_writable(dirname($cache)) )
			OR ( file_exists($cache) AND !(is_writable($cache)) ) ) {
			// Nie, nie da się.
			$cache = '';
		}
		else
		{
			// Tak, da się.
			// Sprawdź, czy dane są aktualne.
			if(@filemtime($cache)<strtotime('yesterday 22:45') && time()<=strtotime('14:30')) {
				$recent = FALSE;
			}
			elseif( ( time()>=strtotime('14:30') && @filemtime($cache)<strtotime('14:30') )
				OR ( time()>=strtotime('22:45') && @filemtime($cache)<strtotime('22:45') ) ) {
				$recent = FALSE;
			}
			else
			{
				$recent = TRUE;
			}
		}
		
		// Dane są nieaktualne, więc pobieramy je ponownie
		if($cache == '' OR !$recent) {
			$this->strona = @file_get_contents('http://lotto.pl/wyniki-gier');
			if(!$this->strona) {
				throw new Exception('Nie udało się pobrać wyników.');
			}
			
			// Można zapisać do cache'a...
			if($cache != '') {
				// ...więc zapamiętujemy arkusz.
				file_put_contents($cache, $this->strona);
			}
		}
		else
		{
			// Dane w cache są aktualne, więc załaduj je.
			$this->strona = file_get_contents($cache);
		}
	}
	
	// Znajduje w $gdzie ciągi $od i $do, po czym zwraca
	// treść znajdującą się pomiędzy tymi wartościami.
	protected function wytnij(&$gdzie, $od, $do, $blad = NULL, &$pozycja = NULL) {
		if($blad === NULL) {
			$blad = 'Nie znaleziono wymaganego elementu';
		}
		
		$start = strpos($gdzie, $od, $pozycja);
		if($start === FALSE) {
			throw new Exception($blad);
		}
		$start += strlen($od);
		
		$stop = strpos($gdzie, $do, $start);
		if($stop === FALSE) {
			throw new Exception($blad);
		}
		
		if($pozycja !== NULL) {
			$pozycja = $stop + strlen($do);
		}
		
		return trim(substr($gdzie, $start, $stop-$start));
	}
	
	// Zwraca wynik gry (domyślnie lotto).
	function wynik($gra = 'lotto') {
		$wyniki = $this->wyniki($gra, 1);
		return $wyniki[0];
	}
	
	// Zwraca $liczba ostatnich wyników gry (domyślnie lotto),
	// jednak nie więcej niż 5 (tyle jest na stronie Totalizatora).
	function wyniki($gra = 'lotto', $liczba = 1000) {
		if(!isset($this->gry[$gra])) {
			throw new Exception('Podana gra liczbowa nie jest obsługiwana.');
		}
		
		$wyniki = array();
		$dane = $this->wytnij($this->strona, '<div class="start-wyniki_'.$gra,
			'<div class="start-wyniki_', 'Nie znaleziono na stronie wyników dla gry '.$gra);
		
		$poz_dane = 0;
		for($l = 1; $l <= $liczba; $l++) {
			$data = $this->wytnij($dane, '<div class="wyniki_data', '</div>',
				'Nie znaleziono '.$l.'-ej informacji o losowanu gry '.$gra, $poz_dane);
			
			$pozycja = 0;
			$wynik['data'] = $this->wytnij($data, '<strong>', '</strong>',
				'Nie znaleziono '.$l.'-ej daty losowania gry '.$gra, $pozycja);
			$wynik['godzina'] = $this->wytnij($data, '<strong>', '</strong>',
				'Nie znaleziono '.$l.'-ej godziny losowania gry '.$gra, $pozycja);
			
			try {
				$liczby = $this->wytnij($dane, '<div class="glowna_wyniki_'.$gra, "\t".'</div>',
					'Nie znaleziono na stronie '.$l.'-ch wyników dla gry '.$gra, $poz_dane);
			}
			catch(Exception $e) {
				break;
			}
			
			// Pobierz kolejne liczy zawarte pomiędzy <div class="wynik_NAZWAGRY"> a </div>
			$wynik['liczby'] = array();
			$pozycja = 0;
			for($i = 0; $i < $this->gry[$gra][0]; $i++) {
				$wynik['liczby'][] = $this->wytnij($liczby, '<div class="wynik_'.$gra.'">',
					'</div>', NULL, $pozycja);
			}
			
			// Szukamy plusa
			if($this->gry[$gra][1]) {
				$wynik['plus'] = $this->wytnij($dane, '<div class="wynik_'.$gra.'_plus">',
					'</div>', NULL, $poz_dane);
			}
			
			// Szukamy lottoplusa
			if($this->gry[$gra][2] > 0) {
				try {
					$liczby = $this->wytnij($dane, '<div class="glowna_wyniki_'.$gra.'plus">', "\t".'</div>',
						'Nie znaleziono na stronie '.$l.'-ch wyników dla gry '.$gra.'plus', $poz_dane);
				}
				catch(Exception $e) {
					continue;
				}
				
				$pozycja = 0;
				$wynik['plus'] = array();
				for($i = 0; $i < $this->gry[$gra][2]; $i++) {
					$wynik['plus'][] = $this->wytnij($liczby, '<div class="wynik_'.$gra.'plus">',
						'</div>', 'Nie znaleziono wyników losowania '.$gra.'plus', $pozycja);
				}
			}
			
			$wyniki[] = $wynik;
		}
		
		return $wyniki;
	}
	
	function pobierz() {
		foreach($this->gry as $gra => $data) {
			echo STAR.'Wyniki gry '.$gra.'...';
			if($gra == 'multi-multi') {
				$wyniki = $this->wyniki($gra, 2);
				$wynik = $wyniki[0];
				$skrot = $data[2].substr($wynik['godzina'], 0, 2);
				$last_data = @file_get_contents('./last_'.$skrot.'.txt');
				if($last_data != $wynik['data']) {
					$output = array();
					$output['data'] = $wynik['data'];
					for($i = 0; $i < $data[0]; $i++) {
						$output[$i+1] = $wynik['liczby'][$i];
					}
					if($data[1]) {
						$output['plus'] = $wynik['plus'];
					}
					file_put_contents('./last_'.$skrot.'.txt', $output['data']);
					file_put_contents('./'.$skrot.'.txt', serialize($output));
					file_put_contents('./archiwum/'.$skrot.'_'.date('j.m.Y', strtotime($output['data'])).'.txt', serialize($output));
				}
				
				$wynik = $wyniki[1];
				$skrot = $data[2].substr($wynik['godzina'], 0, 2);
				$last_data = @file_get_contents('./last_'.$skrot.'.txt');
				if($last_data != $wynik['data']) {
					$output = array();
					$output['data'] = $wynik['data'];
					for($i = 0; $i < $data[0]; $i++) {
						$output[$i+1] = $wynik['liczby'][$i];
					}
					if($data[1]) {
						$output['plus'] = $wynik['plus'];
					}
					file_put_contents('./last_'.$skrot.'.txt', $output['data']);
					file_put_contents('./'.$skrot.'.txt', serialize($output));
					file_put_contents('./archiwum/'.$skrot.'_'.date('j.m.Y', strtotime($output['data'])).'.txt', serialize($output));
				}
			}
			else
			{
				$wynik = $this->wynik($gra);
				$skrot = $data[2];
				$last_data = @file_get_contents('./last_'.$skrot.'.txt');
				if($last_data != $wynik['data']) {
					$output = array();
					$output['data'] = $wynik['data'];
					for($i = 0; $i < $data[0]; $i++) {
						$output[$i+1] = $wynik['liczby'][$i];
					}
					if($data[1]) {
						$output['plus'] = $wynik['plus'];
					}
					if(($data[2] > 0) && isset($wynik['plus'])) {
						$output['plus'] = $wynik['plus'];
					}
					file_put_contents('./last_'.$skrot.'.txt', $output['data']);
					file_put_contents('./'.$skrot.'.txt', serialize($output));
					file_put_contents('./archiwum/'.$skrot.'_'.date('j.m.Y', strtotime($output['data'])).'.txt', serialize($output));
				}
			}
			echo OK;
		}
	}
}

$lotto = new lotto();
echo OK;

$lotto->pobierz();
?>
