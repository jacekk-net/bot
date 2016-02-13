<?php
class lotto implements module {
	static function register_cmd() {
		return array(
			'lotto' => 'cmd_lotto',
			'lootto' => 'cmd_lotto',
			'lotek' => 'cmd_lotto',
			'wyniki' => 'cmd_lotto',
			'l' => 'cmd_lotto',
			'duzy' => 'cmd_lotto',
			'dl' => 'cmd_lotto',
			
			'express' => 'cmd_lotto',
			'expres' => 'cmd_lotto',
			'ekspress' => 'cmd_lotto',
			'ekspress' => 'cmd_lotto',
			'exp' => 'cmd_lotto',
			'el' => 'cmd_lotto',
			'mini' => 'cmd_lotto',
			
			'ekstra' => 'cmd_lotto',
			'extra' => 'cmd_lotto',
			'pensja' => 'cmd_lotto',
			'ep' => 'cmd_lotto',
			'ex' => 'cmd_lotto',
			
			'multi' => 'cmd_lotto',
			'multimulti' => 'cmd_lotto',
			'multilotek' => 'cmd_lotto',
			'mm' => 'cmd_lotto',
			
			'ka' => 'cmd_lotto',
			'kaskada' => 'cmd_lotto',
		);
	}
	
	static function help($cmd=NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('lotto ', TRUE);
			GGapi::putRichText('[gra]', FALSE, TRUE);
			GGapi::putRichText("\n".'   Wyniki gry liczbowej TS'."\n");
		}
		else
		{
			GGapi::putRichText('lotto ', TRUE);
			GGapi::putRichText('[gra]', FALSE, TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('lotek, wyniki, l', TRUE);
			GGapi::putRichText(')'."\n".'   Podaje wyniki ostatniego losowania gry Totalizatora Sportowego ');
			GGapi::putRichText('[gra]', FALSE, TRUE);
			GGapi::putRichText(', gdzie gra to: lotto, mini (Mini Lotto), mm (Multi Multi), kaskada lub pensja (Ekstra Pensja).');
		}
	}
	
	static function cmd_lotto($name, $arg) {
		$skrot_nazwa = array(
			'dl' => 'Lotto',
			'dl2' => 'Lotto',
			'el' => 'Mini Lotto',
			'el2' => 'Mini Lotto',
			'ep' => 'Ekstra Pensja',
			'ep2' => 'Ekstra Pensji',
			'mm' => 'Multi Multi',
			'mm2' => 'Multi Multi',
			'mm14' => 'Multi Multi (14:00)',
			'mm142' => 'Multi Multi (14:00)',
			'mm22' => 'Multi Multi (22:00)',
			'mm222' => 'Multi Multi (22:00)',
			'ka' => 'Kaskada',
			'ka2' => 'Kaskady',
		);
		$arg_start = array(
			'dl' => 'dl', 'duzego' => 'dl', 'duzy' => 'dl', 'duzylotek' => 'dl',
			'el' => 'el', 'express' => 'el', 'ekspress' => 'el', 'expres' => 'el', 'ekspres' => 'el', 'minilotto' => 'el', 'm' => 'el', 'ml' => 'el', 'mlotto' => 'el', 'mini' => 'el',
			'ep' => 'ep', 'ekstra' => 'ep', 'pensja' => 'ep', 'extra' => 'ep', 'ekstrapensja' => 'ep', 'extrapensja' => 'ep', 'ex' => 'ep',
			'mm' => 'mm', 'multilotka' => 'mm', 'multi' => 'mm', 'multimulti' => 'mm',
			'ka' => 'ka', 'kaskada' => 'ka', 'k' => 'ka',
		);
		$arg = explode(' ', funcs::utfToAscii($arg));
		array_unshift($arg, $name);
		
		foreach($arg as $value) {
			if(empty($value))
				continue;
			$value = trim($value, "\t\n\r .,:;'\"");
			if(isset($arg_start[$value])) {
				$gra = $arg_start[$value];
				break;
			}
		}
		
		if(!isset($gra) || !$gra) {
			$gra = 'dl';
		}
		
		if($gra == 'mm') {
			$typy = array('14', '22');
		}
		else {
			$typy = array('');
		}
		
		$txt = '';
		foreach($typy as $addon) {
			$dane = unserialize(file_get_contents('./data/lotto/'.$gra.$addon.'.txt'));
			$txt .= 'Losowanie '.$skrot_nazwa[$gra.'2'].''.($addon ? ' '.$addon.':00' : '').' z dnia '.$dane['data']."\n";
			$gt = 1;
			if($gra == 'jk') {
				$txt .= $skrot_nazwa['jk'].': '.$dane[1]."\n".'Cztery liczby: '.$dane[2];
				$gt = 2;
			}
			else
			{
				$txt .= 'Liczby: '.$dane[1];
			}
			
			foreach($dane as $i => $l) {
				if(is_numeric($i) && $i>$gt) {
					$txt .= ', '.$l;
				}
			}
			
			if($gra == 'ml' || $gra == 'mm') {
				$txt .= "\n".'Plus: '.$dane['plus']."\n\n";
			}
		}
		
		GGapi::putText(trim($txt));
	}
}
?>