<?php
include('./data/tv/xmltv_parse.php');

class tv extends xmltv_parse implements module {
	static function register_cmd() {
		return array(
			't' => 'cmd_tv',
			'tv' => 'cmd_tv',
			'program' => 'cmd_tv',
			'tvp' => 'cmd_tv',
			'tvp1' => 'cmd_tv',
			'tvp2' => 'cmd_tv',
			'tvn' => 'cmd_tv',
			'axn' => 'cmd_tv',
			'polsat' => 'cmd_tv',
			'polsta' => 'cmd_tv',
			'l' => 'cmd_list',
			'list' => 'cmd_list',
			'lita' => 'cmd_list',
			'lista' => 'cmd_list',
		);
	}
	
	static function parse_date(&$txt) {
		$known = array(
			'teraz' => 'now',
			'dzis' => 'today',
			'dzisiaj' => 'today',
			'jutro' => 'tomorrow',
			'pojutrze' => 'tomorrow +1 day',
			'po jutrze' => 'tommorow +1 day',
			'poniedzialek' => 'Monday',
			'wtorek' => 'Tuesday',
			'sroda' => 'Wednesday',
			'czwartek' => 'Thursday',
			'piatek' => 'Friday',
			'sobota' => 'Saturday',
			'niedziela' => 'Sunday',
		);
		for($i=0; $i<3; $i++) {
			$known[date('d.m', strtotime('+'.$i.' day'))] = 'today +'.$i.' day';
			$known[date('j.m', strtotime('+'.$i.' day'))] = 'today +'.$i.' day';
		}
		foreach($known as $test => $time) {
			if(substr($txt, -strlen($test))==$test) {
				$txt = trim(substr($txt, 0, -strlen($test)));
				return strtotime($time);
			}
			elseif(substr($txt, 0, strlen($test))==$test) {
				$txt = trim(substr($txt, strlen($test)));
				return strtotime($time);
			}
		}
		
		return time();
	}
	
	static function help($cmd=NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('tv ', TRUE);
			GGapi::putRichText('kanał [kiedy]', FALSE, TRUE);
			GGapi::putRichText("\n".'   Program telewizyjny dla stacji ');
			GGapi::putRichText('kanał'."\n", FALSE, TRUE);
			GGapi::putRichText('lista ', TRUE);
			GGapi::putRichText("\n".'   Lista dostępnych stacji telewizyjnych'."\n\n");
		}
		elseif(substr($cmd, 0, 1) == 'l') {
			GGapi::putRichText('lista ', TRUE);
			GGapi::putRichText("\n".'   Podaje listę dostępnych w komendzie ');
			GGapi::putRichText('tv', TRUE);
			GGapi::putRichText(' stacji telewizyjnych'."\n\n");
		}
		else
		{
			GGapi::putRichText('tv ', TRUE);
			GGapi::putRichText('kanał [kiedy]', FALSE, TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('program', TRUE);
			GGapi::putRichText(')'."\n".'   Podaje program  dla stacji ');
			GGapi::putRichText('kanał', FALSE, TRUE);
			GGapi::putRichText(' na ');
			GGapi::putRichText('[kiedy]'."\n\n", FALSE, TRUE);
			GGapi::putRichText('Przykład', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'tv TVP 1'."\n".'tv TVP 1 sobota');
		}
	}
	
	static function cmd_list($name, $args) {
		self::$aliases = './data/tv/aliases';
		
		GGapi::putRichText('Dostępne stacje telewizyjne:', TRUE);
		GGapi::putRichText("\n".implode("\n", self::channels()));
	}
	
	static function cmd_tv($name, $args) {
		self::$file = './data/tv/xmltv-utf.xml';
		self::$aliases = './data/tv/aliases';
		
		$time = self::parse_date($args);
		
		if(empty($args)) {
			$args = $name;
		}
		
		$tv = self::aliases($args);
		
		if(!$tv) {
			GGapi::putText('Nieznana stacja telewizyjna. Spróbuj:'."\n".'tv TVP 1'."\n".'tv Discovery'."\n\n".'lub wpisz ');
			GGapi::putRichText('lista', TRUE);
			GGapi::putRichText(' by uzyskać listę dostępnych stacji telewizyjnych');
			return;
		}
		
		GGapi::putRichText('Program stacji '.$tv."\n", TRUE, FALSE, TRUE);
		self::schedule($tv, $time);
	}
}
?>