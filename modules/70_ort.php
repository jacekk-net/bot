<?php
class ort implements module {
	static function register_cmd() {
		return array(
			'o' => 'cmd_ort',
			'ort' => 'cmd_ort',
		);
	}
	
	static function help($cmd=NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('ort ', TRUE);
			GGapi::putRichText('słowo', FALSE, TRUE);
			GGapi::putRichText("\n".'   Słownik ortograficzny'."\n");
		}
		else
		{
			GGapi::putRichText('ort ', TRUE);
			GGapi::putRichText('słowo', FALSE, TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('o', TRUE);
			GGapi::putRichText(')'."\n".'   Sprawdza w słowniku ortograficznym ');
			GGapi::putRichText('słowo', FALSE, TRUE);
		}
	}
	
	static function cmd_ort($name, $arg) {
		if(empty($arg)) {
			GGapi::putText('Funkcja ');
			GGapi::putRichText('ort', TRUE);
			GGapi::putRichText(' wymaga argumentu ');
			GGapi::putRichText('slowo'."\n\n", FALSE, TRUE);
			GGapi::putRichText('Przykłady', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'ort grzegżółka'."\n".'ort warsawa');
			
			return;
		}
		
		$proc = proc_open('aspell --lang=pl --encoding=utf-8 --ignore-case=true pipe', array(array('pipe', 'r'), array('pipe', 'w'), array('file', '/dev/null', 'w')), $pipe);
		
		fwrite($pipe[0], trim($arg)."\n");
		fclose($pipe[0]);
		
		$status = proc_get_status($proc);
		while($status['running']) {
			usleep(1);
			$status = proc_get_status($proc);
		}
		
		fgets($pipe[1], 1024);
		$spell = fread($pipe[1], 4096);
		
		if(substr($spell, 0, 1)=='*') {
			GGapi::putRichText('Pisownia poprawna', FALSE, FALSE, FALSE, 0, 150, 0);
		}
		elseif(substr($spell, 0, 1)=='#') {
			GGapi::putText('Brak propozycji poprawnej pisowni');
		}
		else
		{
			$spell = explode(': ', $spell, 2);
			$spell = explode(',', $spell[1]);
			
			$txt = 'Prawdopobnie chodziło ci o:';
			foreach($spell as $val) {
				$txt .= "\n".'- '.trim($val);
			}
			
			GGapi::putText($txt);
		}
		
		fclose($pipe[1]);
		proc_close($proc);
	}
}
?>