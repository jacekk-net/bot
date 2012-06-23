<?php
$bash = fopen('text.txt', 'r');
$cytaty = array();

while(!feof($bash)) {
	$cytat = trim(fgets($bash));
	if(empty($cytat)) continue;
	var_dump($cytat);
	$cytaty[(int)substr($cytat, 1)] = ftell($bash);
	while(!feof($bash) && trim(fgets($bash))!='%');
}

fclose($bash);

ksort($cytaty);
var_dump(count($cytaty));

file_put_contents('index.txt', serialize($cytaty));
?>