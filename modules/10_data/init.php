<?php
class bot_data_init implements BotModuleInit {
	function register() {
		$handler_data = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_data_module',
				'method' => 'data',
			)
		);
		$handler_imieniny = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_data_module',
				'method' => 'imieniny',
			)
		);
		
		return array(
			'data' => $handler_data,
			'dzien' => $handler_data,
			'd' => $handler_data,
			'imieniny' => $handler_imieniny,
			'im' => $handler_imieniny,
			'i' => $handler_imieniny,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>data</b> <i>[dzień]</i><br />'."\n"
				. '   Informacje o danym dniu.<br />'."\n"
				. '<b>imieniny</b> <i>imię</i><br />'."\n"
				. '   Kiedy <i>imię</i> obchodzi imieniny.');
		}
		elseif(substr($params, 0, 1) == 'd') {
			return new BotMsg('<b>data</b> <i>[dzień]</i> (aliasy: <b>d, dzień</b>)<br />'."\n"
				. '   Zwraca informacje (wschód/zachód słońca, imieniny) o dniu podanym w argumencie <i>[dzień]</i> lub o dniu dzisiejszym - gdy nie uda się określić dnia lub nie podano argumentu.<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykłady:</u><br />'."\n"
				. 'data<br />'."\n"
				. 'data pojutrze<br />'."\n"
				. 'data 1.01.2009');
		}
		else
		{
			return new BotMsg('<b>imieniny</b> <i>imię</i> (alias: <b>i</b>)<br />'."\n"
				. '   Podaje dni, w których <i>imię</i> obchodzi imieniny. Parametr <i>imię</i> winien być podany w dopełniaczu liczby pojedynczej.<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykłady:</u><br />'."\n"
				. 'imieniny Adama<br />'."\n"
				. 'imieniny Ewy');
		}
	}
}

return 'bot_data_init';
?>