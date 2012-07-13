<?php
class bot_kino_init implements BotModuleInit {
	function register() {
		$handler_ustaw = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_kino_module',
				'method' => 'ustaw',
			)
		);
		$handler_kino = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_kino_module',
				'method' => 'handle',
			)
		);
		
		return array(
			'kino' => $handler_kino,
			'kina' => $handler_kino,
			'k' => $handler_kino,
			'rep' => $handler_kino,
			'repertuar' => $handler_kino,
			'u' => $handler_ustaw,
			'ustaw' => $handler_ustaw,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>kino</b> <i>miasto nazwa [kiedy]</i><br />'."\n"
				. '   Repertuar kina.<br />'."\n"
				. '<b>ustaw</b> <i>miasto nazwa</i><br />'."\n"
				. '   Ustawia domyślne kino<br />'."\n"
				. '<br />'."\n");
		}
		elseif(substr($params, 0, 1)=='u') {
			return new BotMsg('<b>ustaw</b> <i>[miasto] [nazwa]</i> (alias: <b>u</b>)<br />'."\n"
				. '   Ustawia domyślne kino/miasto dla komendy kino. W przypadku niepodania argumentów kasuje uprzednio ustawione informacje. Komenda nie sprawdza, czy dane miasto jest obsługiwane - po ustawieniu danych należy wykonać komendę kino.<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykłady:</u><br />'."\n"
				. 'ustaw Kraków<br />'."\n"
				. 'ustaw Kraków Multikino');
		}
		else
		{
			return new BotMsg('<b>kino</b> <i>miasto nazwa [kiedy]</i> (aliasy: <b>k, repertuar, rep</b>)<br />'."\n"
				. '   Podaje repertuar kina <i>nazwa</i> w mieście <i>miasto</i> na <i>kiedy</i> (dziś, jutro, pojutrze). W przypadku wpisania nieznanego miasta, zwracana jest lista obsługiwanych miast. Pominięcie nazwy skutkuje wylistowaniem kin w danym mieście. Domyślnie podawany jest repertuar na dziś. Jeśli brakuje któregoś argumentu, podejmowana jest próba zastąpienia go danymi zapisanymi za pomocą komendy <b>ustaw</b>.<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykłady:</u><br />'."\n"
				. 'kino<br />'."\n"
				. 'kino Kraków<br />'."\n"
				. 'kino Kraków Multikino');
		}
	}
}

return 'bot_kino_init';
?>