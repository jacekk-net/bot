<?php
echo STAR.'Średnie kursy walut';
$link = @file_get_contents('http://www.nbp.pl/kursy/kursya.html');
if(!$link) {
	echo FAIL;
	return FALSE;
}

$last = @file_get_contents('./A_last_link.txt');

$link = strstr($link, 'xml/');
$lnk = $link = '/kursy/'.substr($link, 0, strpos($link, '"'));

if($link == $last) {
	echo NOT;
	return TRUE;
}

$link = @file_get_contents('http://www.nbp.pl'.$link);
if(!$link) {
	echo FAIL;
	return FALSE;
}

$link = @simplexml_load_string($link);
if(!$link) {
	echo FAIL;
	return FALSE;
}

file_put_contents('./A_last_link.txt', $lnk);
unset($lnk);

$waluta['i_n_f_o']['tabela'] = (string)$link->numer_tabeli;
$waluta['i_n_f_o']['data'] = (string)$link->data_publikacji;

foreach($link->pozycja as $val) {
	$key = (string)$val->kod_waluty;
	$waluta[$key]['nazwa'] = (string)$val->nazwa_waluty;
	$waluta[$key]['ilosc'] = (string)$val->przelicznik;
	$waluta[$key]['kurs'] = (string)$val->kurs_sredni;
}

file_put_contents('./A_kursy.txt', serialize($waluta));
file_put_contents('./archiwum/A_'.date('j.m.Y', strtotime($waluta['i_n_f_o']['data'])).'.txt', serialize($waluta));

echo OK;
return TRUE;
?>