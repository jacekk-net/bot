<?php
class bash implements module {
	static function register_cmd() {
		return array(
			'bash' => 'cmd_bash',
			'sh' => 'cmd_bash',
			'b' => 'cmd_bash',
		);
	}
	
	static function help($cmd=NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('bash ', TRUE);
			GGapi::putRichText('[cytat]', FALSE, TRUE);
			GGapi::putRichText("\n".'   Cytat z polskiego basha'."\n\n");
		}
		else
		{
			GGapi::putRichText('bash ', TRUE);
			GGapi::putRichText('[cytat]', FALSE, TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('sh, b', TRUE);
			GGapi::putRichText(')'."\n".'   Podaje cytat nr ');
			GGapi::putRichText('[cytat]', FALSE, TRUE);
			GGapi::putRichText(' lub wylosowaną regułkę, jeśli brak argumentu lub dany rekord nie istnieje.');
		}
	}
	
	static function cmd_bash($name, $arg) {
		$data = unserialize(file_get_contents('./data/bash/index.txt'));
		
		$arg = (int)trim($arg);
		if(!$arg || !isset($data[$arg])) {
			$arg = array_rand($data);
		}
		
		$data = $data[$arg];
		
		$fp = fopen('./data/bash/text.txt', 'r');
		fseek($fp, $data);
		
		$data = '';
		$line = '';
		
		while(!feof($fp) && trim($line)!='%') {
			$data .= $line;
			$line = fgets($fp);
		}
		
		fclose($fp);
		
		GGapi::putRichText('Cytat #'.$arg, TRUE);
		GGapi::putText("\n".trim($data));
	}
}
?>