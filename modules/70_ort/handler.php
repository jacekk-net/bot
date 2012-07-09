<?php
class bot_ort_module implements BotModule {
	function handle($msg, $params) {
		$args = trim($msg->args);
		
		if(empty($args)) {
			return new BotMsg('Funkcja <b>ort</b> wymaga argumentu.<br />'
					. '<br />'."\n"
					. '<u>Przykład:</u><br />'."\n"
					. 'ort grzegżółka<br />'."\n"
					. 'ort warsawa');
		}
		
		$args = strtr($args, array("\r\n" => ' ', "\r" => ' ', "\n" => ' '));
		
		$proc = proc_open('aspell --lang=pl --encoding=utf-8 --ignore-case=true pipe', array(array('pipe', 'r'), array('pipe', 'w'), array('file', '/dev/null', 'w')), $pipe);
		
		fwrite($pipe[0], $args."\n");
		fclose($pipe[0]);
		
		do {
			usleep(1);
			$status = proc_get_status($proc);
		} while($status['running']);
		
		fgets($pipe[1], 1024);
		$spell = fgets($pipe[1], 4096);
		fclose($pipe[1]);
		
		proc_close($proc);
		
		if(empty($spell)) {
			return new BotMsg('Błąd podczas sprawdzania słowa w słowniku. Przepraszamy.');
		}
		elseif(substr($spell, 0, 1)=='*') {
			return new BotMsg('<span style="color:#060;">Pisownia poprawna.</span>');
		}
		elseif(substr($spell, 0, 1)=='#') {
			return new BotMsg('Brak propozycji poprawnej pisowni.');
		}
		else
		{
			$spell = explode(': ', $spell, 2);
			$spell = explode(',', $spell[1]);
			
			$txt = '<p>Prawdopobnie chodziło ci o:</p>'."\n"
				. '<ul>'."\n";
			
			foreach($spell as $val) {
				$txt .= '<li>'.htmlspecialchars(trim($val)).'</li>'."\n";
			}
			$txt .= '</ul>';
			
			return new BotMsg($txt);
		}
	}
}
?>