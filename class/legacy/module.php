<?php
interface module {
	static function register_cmd();
	// Zwraca:
	// array(
	//   'KOMENDA1' => 'METODA_OBSLUGUJACA_KOMENDE1',
	//   'KOMENDA2' => 'METODA_OBSLUGUJACA_KOMENDE2',
	//   ...
	// )
	
	static function help($cmd=NULL);
	// Zwraca pomoc dotyczącą komendy z użyciem funkcji GGapi::put*()
	// Jeśli $cmd === NULL, zwraca skróconą listę poleceń modułu
	
	// static function METODA_OBSLUGUJACA_KOMENDE(NAZWA_KOMENDY, ARGUMENTY)
	//    ARGUMENTY to wszystko poza nazwą komendy, przekazane w taki sposób,
	//    w jaki zostały otrzymane od użytkownika
}
?>