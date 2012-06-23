<?php
class BotModuleException extends Exception {}

interface BotModule {
}

interface BotModuleInit {
	function register();
	function help($params = NULL);
}
?>