<?php
require_once('./class/std.php');
try {
	$api = new BotAPIGG;
	$api->setStatus(BotAPIGG::STATUS_DOSTEPNY_DESC, 'Wpisz help, by uzyskać pomoc | http://jacekk.info/botgg');
}
catch(Exception $e) {
	echo $e;
}
?>