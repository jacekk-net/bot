<?php
class bot_wersja_module implements BotModule {
	function handle($msg, $params) {
		return new BotMsg('<img src="./data/version/jacekk.png" /><br />'."\n"
			. '<span color="#F70">Bot Gadu-Gadu</span> wersja '.main::VERSION.'<br />'."\n"
			. 'http://jacekk.info/botgg');
	}
}
?>