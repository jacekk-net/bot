<?php
class bot_ort_init implements BotModuleInit {
	function register() {
		$handler = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_ort_module',
				'method' => 'handle',
			)
		);
		
		return array(
			'ort' => $handler,
			'o' => $handler,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>ort</b> <i>słowo</i><br />'."\n"
				. '   Słownik ortograficzny.<br />'."\n");
		}
		else
		{
			return new BotMsg('<b>ort</b> <i>słowo</i> (alias: <b>o</b>)<br />'."\n"
				. '   Sprawdza <i>słowo</i> w słowniku ortograficznym. W przypadku jego nie odnalezienia zwraca propozycje poprawnej pisowni.<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykład:</u><br />'."\n"
				. 'ort grzegżółka<br />'."\n"
				. 'ort warsawa');
		}
	}
}

return 'bot_ort_init';
?>