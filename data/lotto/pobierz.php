<?php
echo STAR.'Pobieranie danych...';

class lotto {
	// Zawartość strony http://lotto.pl/wyniki-gier
	// jako obiekt DOMDocument
	protected $strona = NULL;
	// Powiązany DOMXPath
	protected $xpath = NULL;
	
	// Specjalne zachowania dla gier
	const NONE = 0;
	const PLUS = 1;
	const LOTTOPLUS = 2;
	const EKSTRA = 3;
	
	// Lista gier.
	protected $gry = array(
		// nazwa
		'lotto', 'mini-lotto',
		'kaskada', 'multi-multi',
		'ekstra-pensja'
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
		
		$strona = '';
		$this->strona = new DOMDocument();
		
		if($cache == '' OR !$recent) {
			// Dane są nieaktualne, więc pobieramy je ponownie
			$strona = @file_get_contents('http://lotto.pl/wyniki-gier');
			if(!$strona) {
				throw new Exception('Nie udało się pobrać wyników.');
			}
			
			// Można zapisać do cache'a...
			if($cache != '') {
				// ...więc zapamiętujemy arkusz.
				file_put_contents($cache, $strona);
			}
			
			@$this->strona->loadHtml($strona);
		}
		else
		{
			// Dane w cache są aktualne, więc załaduj je.
			@$this->strona->loadHtmlFile($cache);
		}
		
		$this->xpath = new DOMXPath($this->strona);
	}
	
	// Znajduje odpowiedni element w pliku HTML lub zwraca błąd.
	protected function wytnij($zapytanie, $gdzie = NULL, $blad = 'Brak danych') {
		$rezultat = $this->xpath->query($zapytanie, $gdzie);
		if(!$rezultat OR $rezultat->length <= 0) {
			throw new Exception($blad);
		}
		return $rezultat;
	}
	
	// Zwraca wynik gry (domyślnie lotto).
	function wynik($gra = 'lotto') {
		$wyniki = $this->wyniki($gra, 1);
		if(!isset($wyniki[0])) {
			throw new Exception('Brak wyników dla gry.');
		}
		return $wyniki[0];
	}
	
	// Zwraca $liczba ostatnich wyników gry (domyślnie lotto),
	// jednak nie więcej niż 5 (tyle jest na stronie Totalizatora).
	function wyniki($gra = 'lotto', $zwroc = 1000) {
		if(!in_array($gra, $this->gry)) {
			throw new Exception('Podana gra liczbowa nie jest obsługiwana.');
		}
		
		$rezultaty = array();
		
		$dane = $this->wytnij('//div[@class="start-wyniki_'.$gra.'"]', NULL,
			'Nie znaleziono na stronie wyników dla gry '.$gra);
		$dane = $dane->item(0);
		
		
		$daty = $this->wytnij('div[contains(concat(" ", @class, " "), " wyniki_data ")]', $dane,
			'Nie znaleziono informacji o losowanu gry '.$gra);
		$wyniki = $this->wytnij('div[contains(concat(" ", @class, " "), " glowna_wyniki_'.$gra.' ")]', $dane,
			'Nie znaleziono wyników losowania gry '.$gra);
		
		$plusy = NULL;
		try {
			$plusy = $this->wytnij('div[@class="wynik_'.$gra.'_plus"]', $dane);
		} catch(Exception $e) {}
		
		$lottoplusy = NULL;
		try {
			$lottoplusy = $this->wytnij('//div[@class="glowna_wyniki_'.$gra.'plus"]', $dane);
		} catch(Exception $e) {}
		
		for($l = 0; $l < $daty->length && $l < $wyniki->length && $l < $zwroc; $l++) {
			$rezultat = array();
			
			// Znajdź $l-tą datę losowania i jego wynik
			$data = $daty->item($l);
			$wynik = $wyniki->item($l);
			
			try {
				// Znajdź datę i godzinę
				$data = $this->wytnij('strong', $data, 'Nie znaleziono '.$l.' daty losowania gry '.$gra);
				$rezultat['data'] = trim($data->item(0)->textContent);
				if($data->length > 1) {
					$rezultat['godzina'] = trim($data->item(1)->textContent);
				}
				
				// Znajdź poszczególne liczby w wyniku
				$liczby = $this->wytnij('div[@class="wynik_'.$gra.'"]', $wynik,
					'Nie znaleziono liczb w '.$l.' losowaniu gry '.$gra);
				$rezultat['liczby'] = array();
				foreach($liczby as $liczba) {
					$rezultat['liczby'][] = trim($liczba->textContent);
				}
			} catch(Exception $e) {
				break;
			}
			
			try {
				// Szukamy plusa
				if($plusy && $plusy->length > $l) {
					$rezultat['plus'] = trim($plusy->item($l)->textContent);
				}
			} catch(Exception $e) {}
			
			try {
				// Szukamy ekstra liczby
				$ekstra = $this->wytnij('div[@class="wynik_'.strtr($gra, '-', '_').'"]', $wynik,
					'Nie znaleziono ekstra w '.$l.' losowaniu gry '.$gra);
				$rezultat['ekstra'] = trim($ekstra->item(0)->textContent);
			} catch(Exception $e) {}
			
			try {
				// Szukamy lottoplusa
				if($lottoplusy && $lottoplusy->length > $l) {
					$liczby = $this->wytnij('div[@class="wynik_'.$gra.'plus"]', $lottoplusy->item($l),
						'Nie znaleziono liczb w '.$l.' losowaniu gry '.$gra.'plus');
					$rezultat['plus'] = array();
					foreach($liczby as $liczba) {
						$rezultat['plus'][] = trim($liczba->textContent);
					}
				}
			} catch(Exception $e) {}
			
			$rezultaty[] = $rezultat;
		}
		
		return $rezultaty;
	}
	
	protected $skroty = array(
		'lotto' => 'dl', 'mini-lotto' => 'el',
		'kaskada' => 'ka', 'multi-multi' => 'mm',
		'ekstra-pensja' => 'ep'
	);
	
	function pobierz_jeden($skrot, $wynik) {
		$last_data = @file_get_contents('./last_'.$skrot.'.txt');
		if($last_data != $wynik['data']) {
			foreach($wynik['liczby'] as $i => $liczba) {
				$wynik[$i+1] = $liczba;
			}
			file_put_contents('./last_'.$skrot.'.txt', $wynik['data']);
			file_put_contents('./'.$skrot.'.txt', serialize($wynik));
			file_put_contents('./archiwum/'.$skrot.'_'.date('j.m.Y', strtotime($wynik['data'])).'.txt', serialize($wynik));
			echo OK;
		} else {
			echo NOT;
		}
	}
	
	function pobierz() {
		foreach($this->gry as $gra) {
			echo STAR.'Wyniki gry '.$gra.'...';
			if($gra == 'multi-multi') {
				echo "\n";
				$wyniki = $this->wyniki($gra, 2);
				foreach($wyniki as $wynik) {
					$godzina = substr($wynik['godzina'], 0, 2);
					echo '   '.STAR.'godzina '.$godzina.'...';
					if($godzina == '21') {
						$godzina = '22';
					}
					$skrot = $this->skroty[$gra].$godzina;
					
					$this->pobierz_jeden($skrot, $wynik);
				}
			} else {
				$this->pobierz_jeden($this->skroty[$gra], $this->wynik($gra));
			}
		}
	}
}

$lotto = new lotto();
echo OK;

$lotto->pobierz();
?>
