<?php
class synonimy implements module {
	static function register_cmd() {
		return array(
			'synonimy' => 'cmd_synonimy',
			'synonim' => 'cmd_synonimy',
			'syn' => 'cmd_synonimy',
			's' => 'cmd_synonimy',
		);
	}
	
	static function help($cmd = NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('synonimy ', TRUE);
			GGapi::putRichText('słowo', FALSE, TRUE);
			GGapi::putRichText("\n".'   Synonimy słowa ');
			GGapi::putRichText('słowo'."\n", FALSE, TRUE);
		}
		else
		{
			GGapi::putRichText('synonimy ', TRUE);
			GGapi::putRichText('słowo', FALSE, TRUE);
			GGapi::putRichText(' (aliasy: ');
			GGapi::putRichText('s, syn, synonim', TRUE);
			GGapi::putRichText(')'."\n".'   Podaje synonimy słowa ');
			GGapi::putRichText('słowo'."\n\n", FALSE, TRUE);
			GGapi::putRichText('Przykład', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'synonimy abecadło'."\n".'synonimy wyspa');
		}
	}
	
	static function cmd_synonimy($nazwa, $args) {
		if(!$args) {
			GGapi::putText('Funkcja ');
			GGapi::putRichText('synonimy', TRUE);
			GGapi::putRichText(' wymaga argumentu ');
			GGapi::putRichText('słowo'."\n\n", FALSE, TRUE);
			GGapi::putRichText('Przykład', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'synonimy abecadło'."\n".'synonimy wyspa');
			
			return;
		}
		
		$args = funcs::utfToAscii(trim($args));
		
		$fp = fopen('./data/synonimy/thesaurus.res', 'r');
		list($rows, $len) = explode('x', trim(fgets($fp, 50)));
		$start = ftell($fp);
		
		$l = 0;
		$p = $rows-1;
		
		while($l<$p) {
			//echo 'L: '.$l.' - P: '.$p."\n";
			$s = floor(($l+$p)/2);
			
			$poz = $start + ($s*$len);
			fseek($fp, $poz);
			
			$line = fread($fp, $len);
			
			$word = strtok($line, ';');
			$cmp = strcmp($word, $args);
			
			if($cmp >= 0) {
				//echo 'Right'."\n";
				$p = $s;
			}
			else
			{
				//echo 'Left'."\n";
				$l = $s+1;
			}
		}
		
		$poz = $start + ($l*$len);
		fseek($fp, $poz);
		
		$i = 1;
		$return = '';
		while(TRUE) {
			$line = fread($fp, $len);
			$word = strtok($line, ';');
			
			if($word != $args) break;
			$return .= "\n".($i++).'. '.rtrim(substr(strstr($line, ';'), 1));
		}
		
		if(!empty($return)) {
			GGapi::putText('Znalezione synonimy:'.$return);
		}
		else
		{
			GGapi::putText('Nie znaleziono synonimów podanego słowa');
		}
	}
}
?>