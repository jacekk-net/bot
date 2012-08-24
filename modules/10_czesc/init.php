<?php
class bot_czesc_init implements BotModuleInit {
	function register() {
		$handler_czesc = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_czesc_module',
				'method' => 'czesc',
			)
		);
		$handler_hello = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_czesc_module',
				'method' => 'hello',
			)
		);
		$handler_zachcianki = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_czesc_module',
				'method' => 'zachcianki',
			)
		);
		$handler_kocham = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_czesc_module',
				'method' => 'kocham',
			)
		);
		$handler_odp = array(
			array(
				'file' => 'handler.php',
				'class' => 'bot_czesc_module',
				'method' => 'odp',
			)
		);
		
		return array(
			'czesc' => $handler_czesc,
			'witaj' => $handler_czesc,
			'witam' => $handler_czesc,
			'siema' => $handler_czesc,
			'hej' => $handler_czesc,
			'heeej' => $handler_czesc,
			'elo' => $handler_czesc,
			'haj' => $handler_czesc,
			'test' => $handler_czesc,
			'good' => $handler_hello,
			'hello' => $handler_hello,
			'hi' => $handler_hello,
			'sex' => $handler_zachcianki,
			'fiut' => $handler_zachcianki,
			'chuj' => $handler_zachcianki,
			'huj' => $handler_zachcianki,
			'seks' => $handler_zachcianki,
			'seksu' => $handler_zachcianki,
			'porno' => $handler_zachcianki,
			'ssij' => $handler_zachcianki,
			'obciagniesz' => $handler_zachcianki,
			'wal' => $handler_zachcianki,
			'kocham' => $handler_kocham,
			'lubie' => $handler_kocham,
			'dzieki' => $handler_kocham,
			'dziekuje' => $handler_kocham,
			'lol' => $handler_odp,
			'do' => $handler_odp,
		);
	}
	
	function help($params = NULL) {
		if($params === NULL) {
			return new BotMsg('<b>czesc</b><br />'."\n"
				. '   Odpowiada na przywitanie.<br />'."\n");
		}
		else
		{
			return new BotMsg('<b>czesc</b> (aliasy: <b>witam, witaj, hej</b>)<br />'."\n"
				. '   Bot przedstawia się i odpowiada na przywitanie.');
		}
	}
}

return 'bot_czesc_init';
?>