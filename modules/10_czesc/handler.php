<?php
class bot_czesc_module implements BotModule {
	function czesc($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		$dozwolone = array('przyjacielu', 'kolego', 'bocie', ':)', '.', ':d', ':D');
		if(!in_array($arg, $dozwolone) && !empty($arg)) {
			return new BotMsg('Funkcja <b>czesc</b> nie przyjmuje argument�w!');
		}
		
		return new BotMsg('<p>Witam!</p>'."\n"
			. '<p>Jestem prostym botem Gadu-Gadu, kt�ry poda Ci kursy walut (wpisz: <b>kursy</b>), sprawdzi dane s�owo w s�owniku ortograficznym (wpisz: <b>ort s�owo</b>) lub przywita si� (tak jak teraz)!</p>'."\n"
			. '<p>Informacje o wszystkich dost�pnych poleceniach otrzymasz po wys�aniu do mnie s�owa <b>pomoc</b>.</p>');
	}
	
	function hello($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		$dozwolone = array('friend', 'buddy', 'bot', 'evening', 'afternoon', 'morning', '.', ':)', ':d', ':D');
		if(!in_array($arg, $dozwolone) && !empty($arg)) {
			return new BotMsg('Function <b>hello</b> does not have any arguments!');
		}
		
		return new BotMsg('<p>Hello!</p>'."\n"
			. '<p>I am simple bot for Gadu-Gadu communicator, which will give you exchange rates (send: <b>kursy</b>), check word in polish dictionary (send: <b>ort word</b>) or say hello (just like now)!</p>'."\n"
			. '<p>Write <b>help</b> to me to get information about all available commands.</p>');
	}
	
	function zachcianki($msg, $params) {
		$txt = array('Ech... Czego si� tym ludziom zachciewa...', 'Znajd� sobie kogo�.', 'CO?!');
		return new BotMsg($txt[array_rand($txt)]);
	}
	
	function kocham($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		$dozwolone = array('cie', 'ci', 'cie przyjacielu', 'cie kolego', 'ci bocie', 'cie bocie', 'cie :)', 'przyjacielu', 'kolego', 'bocie', '.', ':)', ':d', ':D');
		if(!in_array($arg, $dozwolone) && !empty($arg)) {
			return new BotMsg('Funkcja <b>kocham</b> nie przyjmuje epitet�w!');
		}
		
		return new BotMsg('Mi�o mi!');
	}
	
	function odp($msg, $params) {
		$txt = array(
			'lol' => array(
				'Co w tym takiego �miesznego? :P',
				'Nie widz� w tym nic, co mog�oby sprawi�, �e tarzasz si� po pod�odze ;)',
				'LOL'
			),
			'do' => array(
				'Do... Du �ot?!',
			),
		);
		
		$cmd = $msg->command;
		return new BotMsg($txt[$cmd][array_rand($txt[$cmd])]);
	}
}
?>