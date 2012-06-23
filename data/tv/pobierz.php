<?php
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
$address = 'http://tv.wp.pl/program.html?stid=$STATION&date=$DATE&time=';

$counter = 0;
foreach($stations as $num => $station) {
	fwrite($out, '	<channel id="'.$station.'">
		<display-name>'.$station.'</display-name>
	</channel>
');
	for($i=0; $i<7; $i++) {
		echo "\r".STAR.'Pobieranie programu TV: '.floor(($counter*7 + $i)/$NUMOF*100).'%';
		
		$timestamp = strtotime('+'.$i.' days');
		$date = date('Y-m-d', $timestamp);
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
			
			$data = str_replace(array('id="C_TSR-franc"', 'id="C_TSR-2-franc"', 'id="stationId"', 'id="searchForm"', '&'), array('', '', '', '', '&amp;'), $data);
			
			file_put_contents('./cache/'.$num.'_'.$date, $data);
			unset($data);
		}
		
		$doc = new DOMDocument;
		$doc->loadHTMLFile('./cache/'.$num.'_'.$date);
		$doc = $doc->getElementById('bxNazwaBoksu')->childNodes;
		
		foreach($doc as $el) {
			if($el instanceof DOMElement) {
				$doc = $el->childNodes;
				break;
			}
		}
		
		$last_time = 0;
		$last_timestamp = 0;
		foreach($doc as $el) {
			if(!$el instanceof DOMElement || substr($el->getAttribute('class'), 0, 7)!='program') continue;
			
			$time = $el->getElementsByTagName('strong')->item(0)->childNodes->item(0)->nodeValue;
			$time = trim($time);
			if($last_time>(int)$time) {
				$timestamp = strtotime('+1 day', $timestamp);
			}
			$last_time = (int)$time;
			$timestamp = strtotime($time, $timestamp);
			
			if($last_timestamp) {
				fwrite($out, '	<programme channel="'.$station.'" start="'.date('YmdHis O', $last_timestamp).'" stop="'.date('YmdHis O', $timestamp).'">
		<title>'.$name.'</title>
		<desc/>
	</programme>
');
			}
			
			$name = $el->getElementsByTagName('h4')->item(0)->childNodes->item(0)->childNodes->item(0)->nodeValue;
			$name = htmlspecialchars(trim($name), ENT_COMPAT, 'UTF-8');
			$last_timestamp = $timestamp;
		}
		
		fwrite($out, '	<programme channel="'.$station.'" start="'.date('YmdHis O', $timestamp).'" stop="'.date('YmdHis O', $timestamp+3600).'">
		<title>'.$name.'</title>
		<desc/>
	</programme>
');
		
		unset($doc);
	}
	
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
