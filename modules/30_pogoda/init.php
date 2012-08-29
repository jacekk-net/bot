<?php
class bot_pogoda_init implements BotModuleInit {
	function register() {
		$handler_pogoda = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_pogoda_module',
				'method' => 'pogoda',
			)
		);
		$handler_miasto = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_pogoda_module',
				'method' => 'miasto',
			)
		);
		
		return array(
			'pogoda' => $handler_pogoda,
			'p' => $handler_pogoda,
			'weather' => $handler_pogoda,
			'temperatura' => $handler_pogoda,
			'temp' => $handler_pogoda,
			'miasto' => $handler_miasto,
			'm' => $handler_miasto,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>pogoda</b> <i>[miasto]</i><br />'."\n"
				. '   Pogoda dla miasta.<br />'."\n"
				. '<b>miasto</b> <i>miasto</i><br />'."\n"
				. '   Ustawia domyślne miasto dla funkcji pogoda.<br />'."\n"
				. '<br />'."\n");
		}
		elseif(substr($params, 0, 1) == 'm') {
			return new BotMsg('<b>miasto</b> <i>miasto</i> (alias: <b>m</b>)<br />'."\n"
				. '   Ustawia domyślne miasto dla komendy <b>pogoda</b>. Dane o lokalizacji są również wykorzystywane do wyliczania godziny wschodu i zachodu słońca w funkcji <b>data</b>.<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykład:</u><br />'."\n"
				. 'miasto Warszawa');
		}
		else
		{
			return new BotMsg('<b>pogoda</b> <i>[miasto]</i> (aliasy: <b>p, weather, temp</b>)<br />'."\n"
				. '   Podaje pogodę dla danego miasta na najbliższe dni. Domyślne miasto można ustawić komendą <b>miasto</p>.<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykład:</u><br />'."\n"
				. 'pogoda Warszawa');
		}
	}
}

return 'bot_pogoda_init';
?>