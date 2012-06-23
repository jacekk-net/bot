<?php
class czesc implements module {
	static function register_cmd() {
		return array(
			'czesc' => 'cmd_czesc',
			'witaj' => 'cmd_czesc',
			'witam' => 'cmd_czesc',
			'siema' => 'cmd_czesc',
			'hej' => 'cmd_czesc',
			'heeej' => 'cmd_czesc',
			'elo' => 'cmd_czesc',
			'haj' => 'cmd_czesc',
			'test' => 'cmd_czesc',
			'good' => 'cmd_hello',
			'hello' => 'cmd_hello',
			'hi' => 'cmd_hello',
			'sex' => 'cmd_zachcianki',
			'fiut' => 'cmd_zachcianki',
			'chuj' => 'cmd_zachcianki',
			'huj' => 'cmd_zachcianki',
			'seks' => 'cmd_zachcianki',
			'seksu' => 'cmd_zachcianki',
			'porno' => 'cmd_zachcianki',
			'ssij' => 'cmd_zachcianki',
			'obciagniesz' => 'cmd_zachcianki',
			'wal' => 'cmd_zachcianki',
			'kocham' => 'cmd_kocham',
			'lubie' => 'cmd_kocham',
			'lol' => 'cmd_odp',
			'do' => 'cmd_odp',
			'dzieki' => 'cmd_kocham',
			'dziekuje' => 'cmd_kocham',
		);
	}
	
	static function help($cmd=NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('czesc', TRUE);
			GGapi::putRichText("\n".'   Odpowiada na przywitanie'."\n\n");
		}
		else
		{
			GGapi::putRichText('czesc', TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('witam, witaj', TRUE);
			GGapi::putRichText(')'."\n".'   Bot przedstawia się i odpowiada na przywitanie');
		}
	}
	
	static function cmd_odp($cmd, $arg) {
		$txt = array(
			'lol' => array(
				'Co w tym takiego śmiesznego? :P',
				'Nie widzę w tym nic, co mogłoby sprawić, że tarzasz się po podłodze ;)',
				'LOL'
			),
			'do' => array(
				'Do... Du łot?!',
			),
		);
		
		funcs::antiFlood($_GET['numer']);
		
		if(is_array($txt[$cmd])) {
			GGapi::putText($txt[$cmd][array_rand($txt[$cmd])]);
		}
		else
		{
			GGapi::putText($txt[$cmd]);
		}
	}
	
	static function cmd_zachcianki($name, $arg) {
		funcs::antiFlood($_GET['numer']);
		$txt = array('Eh... Czego się tym ludziom zachciewa...', 'Znajdź sobie kogoś', 'CO?!');
		GGapi::putText($txt[array_rand($txt)]);
	}
	
	static function cmd_kocham($name, $arg) {
		$dozwolone = array('cie', 'ci', 'cie przyjacielu', 'cie kolego', 'ci bocie', 'cie bocie', 'cie :)', 'przyjacielu', 'kolego', 'bocie', '.', ':)', ':d', ':D');
		if(!in_array(funcs::utfToAscii(trim($arg)), $dozwolone) && !empty($arg)) {
			GGapi::putText('Funkcja nie przyjmuje epitetów!');
			return;
		}
		
		GGapi::putText('Miło mi!');
	}
	
	static function cmd_czesc($name, $arg) {
		$dozwolone = array('przyjacielu', 'kolego', 'bocie', ':)', '.', ':d', ':D');
		if(!in_array(funcs::utfToAscii(trim($arg)), $dozwolone) && !empty($arg)) {
			GGapi::putText('Funkcja ');
			GGapi::putRichText('czesc', TRUE);
			GGapi::putRichText(' nie przyjmuje epitetów!');
			return;
		}
		
		GGapi::putText('Witam,'."\n".'Jestem prostym botem Gadu-Gadu, który poda Ci kursy walut (wpisz: ');
		GGapi::putRichText('kursy', TRUE);
		GGapi::putRichText('), sprawdzi dane słowo w słowniku ortograficznym (wpisz: ');
		GGapi::putRichText('ort ', TRUE);
		GGapi::putRichText('słowo', TRUE, TRUE);
		GGapi::putRichText(') lub przywita się (tak jak teraz)!'."\n\n".'Informacje o wszystkich dostępnych poleceniach otrzymasz po wpisaniu ');
		GGapi::putRichText('help', TRUE);
	}
	
	static function cmd_hello($name, $arg) {
		$dozwolone = array('friend', 'buddy', 'bot', 'evening', 'afternoon', 'morning', '.', ':)', ':d', ':D');
		if(!in_array(funcs::utfToAscii(trim($arg)), $dozwolone) && !empty($arg)) {
			GGapi::putText('Function ');
			GGapi::putRichText('hello', TRUE);
			GGapi::putRichText(' does not have any arguments!');
			return;
		}
		
		GGapi::putText('Hello!'."\n\n".'I\'m simple bot for Gadu-Gadu communicator, which will give you exchange rates (send: kursy), check word in polish dictionary (send: ort word) or say hello (just like now)!'."\n\n".'Write help to me to get informations about all available commands');
	}
}
?>