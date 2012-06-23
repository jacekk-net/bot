<?php
echo STAR.'Ceny kupna/sprzedaży walut';
$link = @file_get_contents('http://www.nbp.pl/kursy/kursyc.html');
if(!$link) {
	echo FAIL;
	return FALSE;
}

$last = @file_get_contents('./C_last_link.txt');

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

file_put_contents('./C_last_link.txt', $lnk);
unset($lnk);

$waluta['i_n_f_o']['tabela'] = (string)$link->numer_tabeli;
$waluta['i_n_f_o']['data_not'] = (string)$link->data_notowania;
$waluta['i_n_f_o']['data'] = (string)$link->data_publikacji;

foreach($link->pozycja as $val) {
	$key = (string)$val->kod_waluty;
	$waluta[$key]['nazwa'] = (string)$val->nazwa_waluty;
	$waluta[$key]['ilosc'] = (string)$val->przelicznik;
	$waluta[$key]['kupno'] = (string)$val->kurs_kupna;
	$waluta[$key]['sprzedaz'] = (string)$val->kurs_sprzedazy;
}

file_put_contents('./C_kursy.txt', serialize($waluta));
file_put_contents('./archiwum/C_'.date('j.m.Y', strtotime($waluta['i_n_f_o']['data'])).'.txt', serialize($waluta));

echo OK;
return TRUE;
?>