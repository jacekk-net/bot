<?php
class rejestracje implements module {
	static function register_cmd() {
		return array(
			'r' => 'cmd_rej',
			'rej' => 'cmd_rej',
			'rejestr' => 'cmd_rej',
			'rejestracja' => 'cmd_rej',
			'rejestracje' => 'cmd_rej',
		);
	}
	
	static function cmd_rej($name, $args) {
		if(empty($args)) {
			GGapi::putText('Nie podano numeru rejestracyjnego!'."\n\n");
			GGapi::putRichText('Przykłady', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'rej KRA'."\n".'rej WW 1111X');
			return FALSE;
		}
		include('./data/rejestracje/rej.php');
		$dane = rejestracje_data::find($args);
		GGapi::putRichText(array_shift($dane), TRUE);
		GGapi::putRichText("\n".'Typ: '.implode("\n", $dane));
	}
	
	static function help($cmd = NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('rej ', TRUE);
			GGapi::putRichText('rejestracja', FALSE, TRUE);
			GGapi::putRichText("\n".'   Informacje o polskiej tablicy rejestracyjnej'."\n");
		}
		else
		{
			GGapi::putRichText('rej ', TRUE);
			GGapi::putRichText('rejestracja', FALSE, TRUE);
			GGapi::putRichText("\n".'   Zwraca informacje o tablicy rejestracyjnej. Jak argument należy podać pełny, poprawny numer lub tylko wyróżnik (1-3 liter).');
		}
	}
}
?>