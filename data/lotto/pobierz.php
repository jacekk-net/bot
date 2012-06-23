<?php
echo STAR.'Pobieranie danych...';

class lotto {
	protected $strona = NULL;
	
	protected $gry = array(
		'lotto' => array(6, FALSE, 'dl'),
		'mini-lotto' => array(5, FALSE, 'el'),
		'kaskada' => array(12, FALSE, 'ka'),
		'multi-multi' => array(20, TRUE, 'mm'),
		'joker' => array(5, FALSE, 'jk')
	);
	
	function __construct() {
		$cache = 'lotto_cache.txt';
		
		if( ( !file_exists($cache) AND !is_writable(dirname($cache)) )
			OR ( file_exists($cache) AND !(is_writable($cache)) ) ) {
			$cache = '';
		}
		else
		{
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
		
		if($cache == '' OR !$recent) {
			$this->strona = @file_get_contents('http://lotto.pl/wyniki-gier');
			if(!$this->strona) {
				throw new Exception('Nie udało się pobrać wyników.');
			}
			
			if($cache != '') {
				file_put_contents($cache, $this->strona);
			}
		}
		else
		{
			$this->strona = file_get_contents($cache);
		}
	}
	
	protected function szukaj_poczatku($tresc, $poczatek = 0) {
		return strpos($this->strona, $tresc, $poczatek);
	}
	
	protected function szukaj_konca($tresc, $poczatek = 0) {
		$pozycja = strpos($this->strona, $tresc, $poczatek);
		
		if($pozycja !== FALSE) {
			$pozycja += strlen($tresc);
		}
		
		return $pozycja;
	}
	
	function wynik($gra = 'lotto') {
		$wyniki = $this->wyniki($gra, 1);
		return $wyniki[0];
	}
	
	function wyniki($gra = 'lotto', $liczba = 100) {
		if(!isset($this->gry[$gra])) {
			throw new Exception('Podana gra liczbowa nie jest obsługiwana.');
		}
		
		$wyniki = array();
		$poczatek = $this->szukaj_konca('<div class="start-wyniki_'.$gra.'">');
		if($poczatek === FALSE) {
			throw new Exception('Nie znaleziono wyników dla gry '.$gra.' na stronie.');
		}
		
		for($l = 0; $l < $liczba; $l++) {
			$poczatek = $this->szukaj_konca('<div class="wyniki_data">', $poczatek);
			if($poczatek === FALSE) {
				break;
			}
			
			$wynik = array();
			
			$poczatek = $this->szukaj_konca('<strong>', $poczatek);
			if($poczatek === FALSE) {
				break;
			}
			$koniec = $this->szukaj_poczatku('</strong>', $poczatek);
			$wynik['data'] = substr($this->strona, $poczatek, $koniec-$poczatek);
			if($poczatek === FALSE) {
				break;
			}
			$poczatek = $this->szukaj_konca('<strong>', $poczatek);
			$koniec = $this->szukaj_poczatku('</strong>', $poczatek);
			$wynik['godzina'] = substr($this->strona, $poczatek, $koniec-$poczatek);
			
			$poczatek = $this->szukaj_konca('<div class="glowna_wyniki_'.$gra.'">', $poczatek);
			if($poczatek === FALSE) {
				break;
			}
			
			$wynik['liczby'] = array();
			for($i = 0; $i < $this->gry[$gra][0]; $i++) {
				$poczatek = $this->szukaj_konca('<div class="wynik_'.$gra.'">', $poczatek);
				$koniec = $this->szukaj_poczatku('</div>', $poczatek);
				$wynik['liczby'][] = substr($this->strona, $poczatek, $koniec-$poczatek);
			}
			
			if($this->gry[$gra][1]) {
				$poczatek = $this->szukaj_konca('<div class="wynik_'.$gra.'_plus">', $poczatek);
				$koniec = $this->szukaj_poczatku('</div>', $poczatek);
				$wynik['plus'] = substr($this->strona, $poczatek, $koniec-$poczatek);
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
