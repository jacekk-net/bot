<?php
class kurs implements module {
	static $name2iso = array(
		'dolar' => 'USD',
		'dolara' => 'USD',
		'euro' => 'EUR',
		'rubel' => 'RUB',
		'rubla' => 'RUB',
		'GPB' => 'GBP',
		'funt' => 'GBP',
		'funta' => 'GBP',
		'funt szterling' => 'GBP',
		'funta szterlinga' => 'GBP',
		'frank' => 'CHF',
		'franka' => 'CHF',
		'frank szwajcarski' => 'CHF',
		'franka szwajcarskiego' => 'CHF',
		'jen' => 'JPY',
		'jena' => 'JPY',
		'forint' => 'HUF',
		'forinta' => 'HUF',
		'hrywna' => 'UAH',
		'hrywny' => 'UAH',
		'hrywien' => 'UAH',
		'kuna' => 'HRK',
		'kuny' => 'HRK',
		'kun' => 'HRK',
		'lej' => 'RON',
		'lei' => 'RON',
		'lejow' => 'RON',
		'lew' => 'BGN',
		'lewy' => 'BGN',
		'lewow' => 'BGN',
		'peso' => 'MXN',
		'rupia' => 'IDR',
		'rupii' => 'IDR',
		'SDR' => 'XDR',
	);
	
	static function register_cmd() {
		return array(
			'kursy' => 'cmd_kurs',
			'kurs' => 'cmd_kurs',
			'k' => 'cmd_kurs',
			'waluta' => 'cmd_kurs',
			'waluty' => 'cmd_kurs',
			'euro' => 'cmd_rewrite',
			'dolar' => 'cmd_rewrite',
			'dolara' => 'cmd_rewrite',
		);
	}
	
	static function help($cmd = NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('kurs ', TRUE);
			GGapi::putRichText('waluta', FALSE, TRUE);
			GGapi::putRichText("\n".'   Kurs danej waluty wg. NBP'."\n\n");
		}
		else
		{
			GGapi::putRichText('kurs ', TRUE);
			GGapi::putRichText('[waluta]', FALSE, TRUE);
			GGapi::putRichText(' (aliasy: ');
			GGapi::putRichText('kursy, k, waluta', TRUE);
			GGapi::putRichText(')'."\n".'   Zwraca aktualne kursy (średnie, a także kupna i sprzedaży - jeśli są dostępne) waluty ');
			GGapi::putRichText('[waluta]', FALSE, TRUE);
			GGapi::putRichText('.'."\n".'   Argument powinien być kodem waluty zgodnym z ISO 4217 lub jedną z popularnych nazw (np. dolar, euro).'."\n\n".'   Tabela A jest aktualizowana codziennie po godzinie 12:00, tabela B w środy po 12:00, tabela C codziennie po 8:00'."\n\n");
			GGapi::putRichText('Przykłady', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'kurs USD'."\n".'kurs EUR');
		}
	}
	
	static function cmd_rewrite($nazwa, $argument) {
		self::cmd_kurs('rewrite', self::$name2iso[$nazwa].' '.$argument);
	}
	
	static function cmd_kurs($nazwa, $argument) {
		$argument = funcs::utfToAscii(trim($argument));
		
		if(isset(self::$name2iso[$argument])) {
			$argument = self::$name2iso[$argument];
		}
		else
		{
			$argument = strtoupper($argument);
		}
		
		$waluty_A = unserialize(file_get_contents('./data/kurs/A_kursy.txt'));
		
		if(empty($argument)) {
			$waluty_C = unserialize(file_get_contents('./data/kurs/C_kursy.txt'));
			
			$txt = 'Kursy średnie walut obcych z dnia '.$waluty_A['i_n_f_o']['data'].' (tabela NBP nr '.$waluty_A['i_n_f_o']['tabela'].') oraz kursy kupna i sprzedaży z dnia '.$waluty_C['i_n_f_o']['data'].' (tabela NBP nr '.$waluty_C['i_n_f_o']['tabela'].')'."\n";
			foreach($waluty_C as $kod => $dane) {
				if($kod == 'i_n_f_o') continue;
				$txt .= "\n".$dane['ilosc'].' '.$kod.' => '.$waluty_A[$kod]['kurs'].' PLN';
				$txt .= ' (kupno: '.$dane['kupno'].' PLN, sprzedaż: '.$dane['sprzedaz'].' PLN)';
			}
			
			GGapi::putText($txt);
			return TRUE;
		}
		
		if(isset($waluty_A[$argument])) {
			$txt = 'Kursy średnie walut obcych z dnia '.$waluty_A['i_n_f_o']['data'].' (tabela NBP nr '.$waluty_A['i_n_f_o']['tabela'].')';
			$waluty_C = unserialize(file_get_contents('./data/kurs/C_kursy.txt'));
			if(isset($waluty_C[$argument])) {
				 $txt .= ' oraz kursy kupna i sprzedaży z dnia '.$waluty_C['i_n_f_o']['data'].' (tabela NBP nr '.$waluty_C['i_n_f_o']['tabela'].')';
			}
			
			$txt .= "\n\n".$waluty_A[$argument]['ilosc'].' '.$argument.' => '.$waluty_A[$argument]['kurs'].' PLN';
			if(isset($waluty_C[$argument])) {
				$txt .= ' (kupno: '.$waluty_C[$argument]['kupno'].' PLN, sprzedaż: '.$waluty_C[$argument]['sprzedaz'].' PLN)';
			}
			
			GGapi::putText($txt);
			return TRUE;
		}
		else
		{
			$waluty_B = unserialize(file_get_contents('./data/kurs/B_kursy.txt'));
			if(!isset($waluty_B[$argument])) {
				GGapi::putText('Nie znaleziono żądanej waluty. Sprawdź, czy kod waluty jest zgodny z ISO 4217.'."\n\n");
				GGapi::putRichText('Przykłady', FALSE, FALSE, TRUE);
				GGapi::putRichText("\n".'kurs USD'."\n".'kurs EUR');
				
				return FALSE;
			}
			
			GGapi::putText('Kursy średnie walut obcych z dnia '.$waluty_B['i_n_f_o']['data'].' (tabela NBP nr '.$waluty_B['i_n_f_o']['tabela'].')'."\n\n".$waluty_B[$argument]['ilosc'].' '.$argument.' => '.$waluty_B[$argument]['kurs'].' PLN');
			
			return TRUE;
		}
	}
}
?>