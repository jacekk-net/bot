<?php
require_once('wp_parse.php');

echo STAR.'Pobieranie programu TV...';
$stations = array(
	1 => 'TVP 1',
	2 => 'TVP 2',
	233 => 'TVP3 Regionalna',
	3 => 'TV Polonia',
	368 => 'TVP Kultura',
	5 => 'Polsat',
	6 => 'Polsat 2',
	435 => 'Polsat Cafe',
	224 => 'Polsat Sport',
	17 => 'TVN',
	238 => 'TVN 7',
	151 => 'TVN 24',
	375 => 'TVN Style',
	265 => 'TVN Turbo',
	18 => 'TV 4',
	235 => 'TV Puls',
	16 => 'Tele 5',
	355 => 'Animal Planet',
	15 => 'Planete',
	360 => 'National Geographic',
	67 => 'Discovery',
	356 => 'Discovery Science',
	186 => 'Discovery World',
	14 => 'HBO',
	201 => 'HBO 2',
	421 => 'HBO Comedy',
	13 => 'CANAL+',
	179 => 'CANAL+ Film',
	183 => 'CANAL+ Sport',
	437 => 'Cinemax',
	442 => 'Cinemax 2',
	436 => 'FilmBox',
	433 => 'FilmBox Extra',
	174 => 'AXN',
	418 => 'AXN Crime',
	416 => 'AXN Sci-fi',
	85 => 'Ale Kino!',
	205 => 'Kino Polska',
	403 => 'TCM',
	400 => 'Comedy Central',
	42 => 'Eurosport',
	364 => 'Eurosport 2',
	420 => 'BBC Entertainment',
	448 => 'BBC Knowledge',
	415 => 'BBC Lifestyle',
	71 => 'Zone Club',
	78 => 'Zone Romantica',
	267 => 'Zone Europa',
	84 => 'Zone Reality',
	434 => 'Religia TV',
	449 => 'BBC CBeebies',
	74 => 'Jetix',
	217 => 'ZigZap',
	361 => 'Cartoon Network',
);
$NUMOF = count($stations)*7;

$c = curl_init();
$out = fopen('./xmltv-pre.xml', 'w');
fwrite($out, '<?xml version="1.0" encoding="UTF-8" ?>
<tv date="'.date('YmdHis O').'" generator-info-name="BotGG" generator-info-url="http://jacekk.info/botgg">
');
$address = 'http://tv.wp.pl/program.html?stid=$STATION';
$date = date('Y-m-d');

$counter = 0;
foreach($stations as $num => $station) {
	echo "\r".STAR.'Pobieranie programu TV: '.floor($counter/$NUMOF*100).'%';
	
	if(!file_exists('./cache/'.$num.'_'.$date) || filesize('./cache/'.$num.'_'.$date)==0) {
		curl_setopt($c, CURLOPT_URL, str_replace(array('$DATE', '$STATION'), array($date, $num), $address));
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($c, CURLOPT_MAXREDIRS, 5);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.2; pl-PL; rv:1.9.2) Gecko/20100101 Firefox/3.6'));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		$data = curl_exec($c);
		if(!$data) {
			echo FAIL;
			return;
		}
		
		file_put_contents('./cache/'.$num.'_'.$date, $data);
		unset($data);
	}
	
	$doc = new DOMDocument;
	@$doc->loadHTMLFile('./cache/'.$num.'_'.$date);
	
	$wp = new wp_parse($doc);
	$wp->xmltv($station, $out);
	
	$counter++;
}

fwrite($out, '</tv>');
fclose($out);

rename('./xmltv-pre.xml', './xmltv-utf.xml');

echo "\r".STAR.'Pobieranie programu TV: 100%'.OK;

echo STAR.'Czyszczenie cache...';
$today = strtotime('today');
foreach(glob('./cache/*') as $garbage) {
	$date = substr($garbage, -10);
	if(strtotime($date) < $today) {
		unlink($garbage);
	}
}
echo OK;
?>
