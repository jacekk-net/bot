<?php
class bot_wersja_init implements BotModuleInit {
	function register() {
		$handler = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_wersja_module',
				'method' => 'handle',
			)
		);
		
		return array(
			'version' => $handler,
			'wersja' => $handler,
			'wersia' => $handler,
			'v' => $handler,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>wersja</b><br />'."\n"
				. '   Wersja bota.<br />'."\n");
		}
		else
		{
			return new BotMsg('<b>wersja</b> (aliasy: <b>v, version</b>)<br />'."\n"
				. '   Zwraca wersję bota.');
		}
	}
}

return 'bot_wersja_init';
?>