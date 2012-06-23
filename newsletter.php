<?php
require_once('./class/std.php');

try {
	$msg = new BotMsg('<p>To jest tekst!</p>');
	$msg->a('<p>A to tekst <b>pogrubiony</b> oraz <i>pochylony</i>.</p>');
	
	$api = new BotAPIGG;
	var_dump($api->sendMessage(array('Gadu-Gadu://NUMER_GG@gadu-gadu.pl'), $msg));
}
catch(Exception $e) {
	echo $e;
}
?>
