<?php
class bot_synonimy_module implements BotModule {
	function handle($msg, $params) {
		$args = trim($msg->args);
		
		if(empty($args)) {
			return new BotMsg('Funkcja <b>synonimy</b> wymaga argumentu.<br />'
					. '<br />'."\n"
					. '<u>Przykład:</u><br />'."\n"
					. 'synonimy abecadło<br />'."\n"
					. 'synonimy wyspa');
		}
		
		$args = funcs::utfToAscii($args);
		
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
			$return .= '<br />'."\n".($i++).'. '.rtrim(substr(strstr($line, ';'), 1));
		}
		
		if(!empty($return)) {
			return new BotMsg('Znalezione synonimy:'.$return);
		}
		else
		{
			return new BotMsg('Nie znaleziono synonimów podanego słowa');
		}
	}
}
?>