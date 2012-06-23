<?php
include('../../classes/funcs.php');
$plik = file('./thesaurus.txt');

$sqlite = sqlite_open('thesaurus.sqlite') or die(sqlite_error_string(sqlite_last_error($sqlite)));

sqlite_query($sqlite, 'CREATE TABLE thesaurus (slowo varchar(255), podobne TEXT)') or die(sqlite_error_string(sqlite_last_error($sqlite)));

$len = $rows = 0;

foreach($plik as $line) {
	if(substr($line, 0, 1) == '#') {
		continue;
	}
	
	$line = explode(';', iconv('ISO-8859-2', 'UTF-8', rtrim($line)));
	foreach($line as $words) {
		$similar = array();
		foreach($line as $word) {
			if($word == $words) {
				continue;
			}
			$similar[] = $word;
		}
		$similar = implode(', ', $similar);
		$len = max($len, strlen($words) + strlen($similar) + 1);
		$rows++;
		sqlite_query($sqlite, 'INSERT INTO thesaurus (slowo, podobne) VALUES (\''.funcs::utfToAscii($words).'\', \''.$similar.'\')') or die(sqlite_error_string(sqlite_last_error($sqlite)));
	}
}

unset($plik);

$fp = fopen('thesaurus.res', 'w');
fwrite($fp, $rows.'x'.$len."\n");

$data = sqlite_unbuffered_query($sqlite, 'SELECT * FROM thesaurus ORDER BY slowo ASC');

while($en = sqlite_fetch_array($data, SQLITE_ASSOC)) {
	fwrite($fp, str_pad($en['slowo'].';'.$en['podobne'], $len, "\0"));
}

fclose($fp);
?>