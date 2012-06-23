<?php
class bot_lang_init implements BotModuleInit {
	private $languages = array(
		'pol' => 'pl',
		'pl' => 'pl',
		'p' => 'pl',
		
		'ang' => 'en',
		'a' => 'en',
		'eng' => 'en',
		'en' => 'en',
		'e' => 'en',
		
		'niem' => 'de',
		'nie' => 'de',
		'ni' => 'de',
		'n' => 'de',
		'de' => 'de',
		'd' => 'de',
		
		'wlo' => 'it',
		'wl' => 'it',
		'w' => 'it',
		'ita' => 'it',
		'it' => 'it',
		'i' => 'it',

		'esp' => 'es',
		'es' => 'es',
		'hiszp' => 'es',
		'hisz' => 'es',
		'his' => 'es',
		'hi' => 'es',
		'h' => 'es',
		
		'fra' => 'fr',
		'fr' => 'fr',
		'f' => 'fr',
	);
	
	function register() {
		$handler = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_lang_module',
				'method' => 'handle',
			)
		);
		
		$return = array();
		foreach($this->languages as $c1 => $l1) {
			foreach($this->languages as $c2 => $l2) {
				if($l1 == $l2) continue;
				
				$handler[0]['params'] = array($l1, $l2);
				$return[$c1.$c2] = $handler;
			}
		}
		
		$handler[0]['method'] = 'typo';
		unset($handler[0]['params']);
		
		foreach($this->languages as $c1 => $l1) {
			if(!isset($return[$c1])) {
				$return[$c1] = $handler;
			}
		}
		
		return $return;
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>[J1][J2]</b> <i>zdanie</i><br />'."\n"
				. '   Tłumaczy zdanie lub słowo z języka [J1] na język [J2] (pol, ang, nie, his, wlo, fra)<br /><br />'."\n");
		}
		else
		{
			return new BotMsg('<b>[J1][J2]</b> <i>zdanie</i><br />'."\n"
				. '   Tłumaczy zdanie lub słowo podane w parametrze z języka [J1] na język [J2]<br /><br />'."\n\n"
				
				. 'Dostępne języki: pol, ang, nie, his, wlo, fra<br /><br />'."\n\n"
				
				. '<u>Przykłady:</u><br />'."\n"
				. 'polang Dzień dobry<br />'."\n"
				. 'angnie Good morning');
		}
	}
}

return 'bot_lang_init';
?>