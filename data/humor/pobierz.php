<?php
echo STAR.'Pobieranie danych...';
$humor = @file_get_contents('http://ddserv.vsdsoftware.pl/eksport/');

if(!$humor) {
	echo FAIL;
	return FALSE;
}

echo OK;
echo STAR.'Sprawdzanie formatu odpowiedzi...';

if(strpos($humor, '<img src="')!==FALSE) {
	preg_match('/\<img src=\"(.*)\" border/', $humor, $humor);
	$humor = $humor[1];
	
	echo ' obrazek'.OK;
	echo STAR.'Pobieranie obrazka...';
	
	$img = @file_get_contents($humor);
	if(!$img) {
		echo FAIL;
		return FALSE;
	}
	
	@unlink('humor.txt');
	file_put_contents('./humor.jpg', $img);
	file_put_contents('./archiwum/'.date('j.m.Y').'.jpg', $img);
	
	echo OK;
	return FALSE;
}
elseif(strpos($humor, '<td class="text">')!==FALSE)
{
	echo ' tekst'.OK;
	echo STAR.'Zapisywanie odpowiedzi';
	$humor = explode('<td class="text">', $humor);
	$humor = explode('</td>', $humor[1]);
	$humor = strip_tags(str_replace('<br>', "\n", $humor[0]));
}
else
{
	echo FAIL;
	return FALSE;
}

$humor = trim($humor);

if(empty($humor)) {
	echo FAIL;
	return FALSE;
}

$humor = iconv('iso-8859-2', 'utf-8', $humor);

file_put_contents('./humor.txt', $humor);
file_put_contents('./archiwum/'.date('j.m.Y').'.txt', trim($humor));

echo OK;
?>