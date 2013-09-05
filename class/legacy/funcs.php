<?php
class BotLegacyEnd extends Exception {}

class funcs {
	/**
	 * Przerywa dalsze wykonywanie modułu i wysyła odpowiedź do klienta
	 * @deprecated Przestarzałe wraz z wprowadzeniem nowego API.
	 *  Metoda może zostać usunięta bez ostrzeżenia!
	 */
	static function end() {
		throw new BotLegacyEnd();
	}
	
	/**
	 * Funkcja usuwa "ogonki" (transliteracja), podwójne spacje i odstępy
	 * z podanego w parametrze ciągu znaków oraz zamienia wszystkie litery na małe
	 * @param string $utf Ciąg znaków w UTF-8
	 * @returns string Ciąg po przetworzeniu
	 */
	static function utfToAscii($utf) {
		$utf = trim(str_replace('  ', ' ', $utf));
		return strtolower(iconv('utf-8', 'ascii//TRANSLIT', $utf));
	}
}
?>