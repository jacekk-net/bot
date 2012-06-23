<?php
class bot_legacy_module implements BotModule {
	function handle($msg, $params) {
		require_once(BOT_TOPDIR.'/modules/'.$params[0]);
		
		if(!defined('BOT_TYPE')) {
			define('BOT_TYPE', $msg->userAlt->interface);
		}
		
		$_GET['to'] = $msg->userAlt->bot;
		$_GET['from'] = $msg->userAlt->uid;
		
		try {
			call_user_func(array($params[1], $params[2]), $msg->command, $msg->args);
		}
		catch(BotLegacyEnd $e) {
		}
		
		return GGapi::getResponse();
	}
}
?>