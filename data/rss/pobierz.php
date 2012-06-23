<?php
$file = file('./channels.list');
foreach($file as $chan) {
	$chan = trim($chan);
	if(empty($chan) || substr($chan, 0, 1)=='#') {
		continue;
	}
	
	$parts = preg_split('/\t[\40\t]*/', $chan, 4);
	
	echo STAR.'Pobieranie kanału '.$parts[3];
	
	$curl = curl_init($parts[0]);
	curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
	curl_setopt($curl, CURLOPT_FILETIME, TRUE);
	curl_setopt($curl, CURLOPT_USERAGENT, 'BotGaduGadu/1.0 mod_rss/1.0 (http://jacekk.info/botgg)');
	if(is_file($parts[1].'.rss')) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'If-Modified-Since: '.date('r', filemtime($parts[1].'.rss')),
		));
	}
	
	$return = curl_exec($curl);
	$info = curl_getinfo($curl);
	
	if($info['http_code'] == 304) {
		echo NOT;
	}
	else if($info['http_code'] == 200) {
		file_put_contents($parts[1].'.rss', $return);
		if($info['filetime']>0) {
			touch($parts[1].'.rss', $info['filetime']);
		}
		echo OK;
	}
	else
	{
		echo '('.$info['http_code'].') '.FAIL;
	}
	
	curl_close($curl);
	
	unset($return);
}
?>