<?php
function botAutoload($class) {
	if($class == 'BotModuleInit') {
		$class = 'BotModule';
	}
	elseif($class == 'BotLegacyEnd') {
		$class = 'funcs';
	}
	elseif(substr($class, -9) == 'Exception') {
		$class = substr($class, 0, -9);
	}
	elseif(substr($class, -9) == 'Interface') {
		$class = substr($class, 0, -9);
	}
	
	if(is_file(BOT_TOPDIR.'/class/legacy/'.$class.'.php')) {
		require_once(BOT_TOPDIR.'/class/legacy/'.$class.'.php');
	}
	elseif(is_file(BOT_TOPDIR.'/class/'.$class.'.php')) {
		require_once(BOT_TOPDIR.'/class/'.$class.'.php');
	}
}

if(!defined('BOT_TOPDIR')) {
	define('BOT_TOPDIR', dirname(__FILE__).'/../');
}

function errorToException($errno, $errstr, $errfile, $errline) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

if(!defined('PHPUNIT')) {
	error_reporting(E_COMPILE_ERROR|E_PARSE);
	set_error_handler('errorToException', E_ALL & ~E_NOTICE);
}

setlocale(LC_CTYPE, 'pl_PL.utf8', 'pl_PL', 'polish', 'plk');
mb_internal_encoding('UTF-8');
libxml_use_internal_errors();
libxml_disable_entity_loader(true);
spl_autoload_register('botAutoload');
