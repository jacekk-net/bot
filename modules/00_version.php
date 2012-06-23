<?php
class version implements module {
	static function register_cmd() {
		return array(
			'version' => 'cmd_version',
			'wersja' => 'cmd_version',
			'wersia' => 'cmd_version',
			'v' => 'cmd_version',
		);
	}
	
	static function cmd_version($name, $args) {
		GGapi::putImage('./data/version/jacekk.png');
		GGapi::putRichText("\n".'Bot Gadu-Gadu', TRUE, FALSE, FALSE, 255, 108, 0);
		GGapi::putRichText(' wersja '.main::VERSION."\n".'http://jacekk.info/botgg');
	}
	
	static function help($cmd = NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('wersja', TRUE);
			GGapi::putRichText("\n".'   Wersja bota'."\n");
		}
		else
		{
			GGapi::putRichText('wersja', TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('v', TRUE);
			GGapi::putRichText(')'."\n".'   Zwraca wersję bota');
		}
	}
}
?>