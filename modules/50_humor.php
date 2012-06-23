<?php
class humor implements module {
	static function register_cmd() {
		return array(
			'humor' => 'cmd_humor',
			'kawal' => 'cmd_humor',
			'zart' => 'cmd_humor',
			'smieszne' => 'cmd_humor',
			'dowcip' => 'cmd_humor',
		);
	}
	
	static function cmd_humor($name, $args) {
		if(file_exists('./data/humor/humor.txt')) {
			GGapi::putText(file_get_contents('./data/humor/humor.txt'));
		}
		elseif(file_exists('./data/humor/humor.jpg')) {
			GGapi::putImage('./data/humor/humor.jpg');
		}
		else
		{
			$last = './data/humor/archiwum/'.date('d.m.Y', strtotime('-1 day'));
			if(file_exists($last.'.txt')) {
				GGapi::putText(file_get_contents($last.'.txt'));
			}
			elseif(file_exists($last.'.jpg')) {
				GGapi::putImage($last.'.jpg');
			}
			else
			{
				GGapi::putText('Dziś nie udało się pobrać danych - przepraszamy.');
			}
		}
	}
	
	static function help($cmd = NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('humor', TRUE);
			GGapi::putRichText("\n".'   Coś humorystycznego'."\n");
		}
		else
		{
			GGapi::putRichText('humor', TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('s, smieszne', TRUE);
			GGapi::putRichText(')'."\n".'   Coś humorystycznego na dziś');
		}
	}
}
?>