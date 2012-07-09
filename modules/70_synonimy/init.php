<?php
class bot_synonimy_init implements BotModuleInit {
	function register() {
		$handler = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_synonimy_module',
				'method' => 'handle',
			)
		);
		
		return array(
			'synonimy' => $handler,
			'synonim' => $handler,
			'syn' => $handler,
			's' => $handler,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>synonimy</b> <i>słowo</i><br />'."\n"
				. '   Synonimy słowa <i>słowo</i>.<br />'."\n");
		}
		else
		{
			return new BotMsg('<b>synonimy</b> <i>słowo</i> (aliasy: <b>s, syn, synonim</b>)<br />'."\n"
				. '   Podaje synonimy słowa <i>słowo</i> (w mianowniku liczby pojedynczej).<br />'."\n"
				. '<br />'."\n"
				. '<u>Przykład:</u><br />'."\n"
				. 'synonimy abecadło<br />'."\n"
				. 'synonimy wyspa');
		}
	}
}

return 'bot_synonimy_init';
?>