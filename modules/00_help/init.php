<?php
class bot_help_init implements BotModuleInit {
	function register() {
		$handler = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_help_module',
				'method' => 'handle',
			)
		);
		
		return array(
			'?' => $handler,
			'h' => $handler,
			'help' => $handler,
			'info' => $handler,
			'man' => $handler,
			'pomoc' => $handler,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>help</b> <i>[komenda]</i><br />'."\n"
				. '   Lista komend lub pomoc dla podanego polecenia <i>[komenda]</i><br />'."\n");
		}
		else
		{
			return new BotMsg('<b>help</b> <i>[komenda]</i> (aliasy: <b>h, pomoc, ?, info, man</b>)<br />'."\n"
				. '   Zwraca listę komend lub pomoc dotyczącą podanego polecenia <i>[komenda]</i>');
		}
	}
}

return 'bot_help_init';
?>