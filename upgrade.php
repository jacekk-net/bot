<?php
require_once('./class/std.php');
$files = glob('database/*.sqlite');
$count = count($files);

header('Content-Type: text/plain');

foreach($files as $num => $file) {
	echo 'Plik '.$num.' z '.$count.' ('.$file.')...'."\n";
	flush();
	
	$PDO = new PDO('sqlite:'.BOT_TOPDIR.'/'.$file);
	$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
	$stmt = $PDO->query('UPDATE data SET class=\'kino\' WHERE class=\'\' AND name=\'kino\'');
	$stmt->closeCursor();
	echo "\t".'poprawiono '.$stmt->rowCount().' zmiennych sesyjnych'."\n";
	unset($stmt);
	flush();
	
	unset($PDO);
}
?>