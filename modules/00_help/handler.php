<?php
class bot_help_module implements BotModule {
	const BOT_HELP_FILE = '/cache/help.cache';
	
	private $PDO;
	
	private function getHelp() {
		if(is_file(BOT_TOPDIR.self::BOT_HELP_FILE)) {
			return unserialize(file_get_contents(BOT_TOPDIR.self::BOT_HELP_FILE));
		}
		
		$st = $this->PDO->prepare('SELECT DISTINCT dir, init FROM functions ORDER BY dir ASC');
		$st->execute();
		$data = $st->fetchAll();
		
		$help = new BotMsg();
		
		foreach($data as $element) {
			$dir = $element['dir'];
			
			if(!is_file(BOT_TOPDIR.$dir.'/init.php')) {
				continue;
			}
			
			$class = include_once(BOT_TOPDIR.$dir.'/init.php');
			if($class === TRUE) {
				$class = $element['init'];
			}
			
			if(!$class || !class_exists($class, FALSE)) {
				continue;
			}
			
			$init = new $class;
			if(!($init instanceof BotModuleInit)) {
				continue;
			}
			
			$row = $init->help();
			if(!($row instanceof BotMsg)) {
				continue;
			}
			
			$help->append($row);
		}
		
		$help->append('Objaśnienie:<br />'."\n"
			. '   <i>argument</i> jest wymagany<br />'."\n"
			. '   <i>[argument]</i> jest opcjonalny');
		
		file_put_contents(BOT_TOPDIR.self::BOT_HELP_FILE, serialize($help));
		
		return $help;
	}
	
	function handle($msg, $params) {
		try {
			try {
				$this->PDO = new PDO('sqlite:'.BOT_TOPDIR.'/cache/functions.sqlite');
				$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
			}
			catch(Exception $e) {
				@unlink(BOT_TOPDIR.'/cache/functions.sqlite');
				throw $e;
			}
			
			$args = trim($msg->args);
			
			if(empty($args)) {
				return $this->getHelp();
			}
			
			$args = strtok($args, " \t\n\r\0\x0B");
			
			$st = $this->PDO->prepare('SELECT dir, init FROM functions WHERE name=? ORDER BY priority ASC');
			$st->execute(array($args));
			$data1 = $st->fetchAll();
			$st->execute(array('*'));
			$data2 = $st->fetchAll();
			
			$data = array_merge($data1, $data2);
			unset($data1, $data2);
			
			foreach($data as $func) {
				if(!is_file(BOT_TOPDIR.$func['dir'].'/init.php')) {
					continue;
				}
				
				$class = require_once(BOT_TOPDIR.$func['dir'].'/init.php');
				if($class === TRUE) {
					$class = $func['init'];
				}
				
				if(!$class || !class_exists($class, FALSE)) {
					continue;
				}
			
				$init = new $class;
				if(!($init instanceof BotModuleInit)) {
					continue;
				}
				
				$help = $init->help($args);
				if($help instanceof BotMsg) {
					return $help;
				}
			}
		}
		catch(Exception $e) {
			return new BotMsg('Wystąpił błąd podczas pobierania pomocy dla polecenia. Komunikat:<br />'.nl2br($e));
		}
	}
}
?>