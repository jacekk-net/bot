<?php
class bot_czesc_module implements BotModule {
	function czesc($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		$dozwolone = array('przyjacielu', 'kolego', 'bocie', ':)', '.', ':d', ':D');
		if(!in_array($arg, $dozwolone) && !empty($arg)) {
			return new BotMsg('Funkcja <b>czesc</b> nie przyjmuje argumentów!');
		}
		
		return new BotMsg('<p>Witam!</p>'."\n"
			. '<p>Jestem prostym botem Gadu-Gadu, który poda Ci kursy walut (wpisz: <b>kursy</b>), sprawdzi dane s³owo w s³owniku ortograficznym (wpisz: <b>ort s³owo</b>) lub przywita siê (tak jak teraz)!</p>'."\n"
			. '<p>Informacje o wszystkich dostêpnych poleceniach otrzymasz po wys³aniu do mnie s³owa <b>pomoc</b>.</p>');
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
		$txt = array('Ech... Czego siê tym ludziom zachciewa...', 'Znajd¼ sobie kogo¶.', 'CO?!');
		return new BotMsg($txt[array_rand($txt)]);
	}
	
	function kocham($msg, $params) {
		$arg = funcs::utfToAscii($msg->args);
		
		$dozwolone = array('cie', 'ci', 'cie przyjacielu', 'cie kolego', 'ci bocie', 'cie bocie', 'cie :)', 'przyjacielu', 'kolego', 'bocie', '.', ':)', ':d', ':D');
		if(!in_array($arg, $dozwolone) && !empty($arg)) {
			return new BotMsg('Funkcja <b>kocham</b> nie przyjmuje epitetów!');
		}
		
		return new BotMsg('Mi³o mi!');
	}
	
	function odp($msg, $params) {
		$txt = array(
			'lol' => array(
				'Co w tym takiego ¶miesznego? :P',
				'Nie widzê w tym nic, co mog³oby sprawiæ, ¿e tarzasz siê po pod³odze ;)',
				'LOL'
			),
			'do' => array(
				'Do... Du ³ot?!',
			),
		);
		
		$cmd = $msg->command;
		return new BotMsg($txt[$cmd][array_rand($txt[$cmd])]);
	}
}
?>