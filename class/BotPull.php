<?php
/**
 * Klasa obsługująca żądania wysyłane do bota
 */
class BotPull {
	private $PDO;
	
	private function init() {
		if(is_file(BOT_TOPDIR.'/cache/functions.sqlite')) {
			$this->PDO = new PDO('sqlite:'.BOT_TOPDIR.'/cache/functions.sqlite');
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
			return;
		}
		
		$functions = array();
		
		$modules = glob(BOT_TOPDIR.'/modules/*', GLOB_ONLYDIR);
		foreach($modules as $dir) {
			if(!is_file($dir.'/init.php')) {
				continue;
			}
			
			$class = include_once($dir.'/init.php');
			if(!$class || !class_exists($class, FALSE)) {
				continue;
			}
			
			$init = new $class;
			if(!($init instanceof BotModuleInit)) {
				continue;
			}
			
			$row = $init->register();
			if(!is_array($row)) {
				$row = array();
			}
			
			foreach($row as $name => &$value) {
				if(!is_array($value)) {
					unset($row[$name]);
					continue;
				}
				
				foreach($value as &$val) {
					$val['dir'] = '/modules/'.basename($dir).'/';
					$val['init'] = $class;
					$val['file'] = $val['file'];
				}
				
				$name2 = funcs::utfToAscii(strtolower($name));
				if($name != $name2) {
					if(isset($row[$name2])) {
						$row[$name2] = array_merge_recursive($row[$name2], $row[$name]);
					}
					else
					{
						$row[$name2] = $row[$name];
					}
					
					unset($row[$name]);
				}
			}
			
			$functions = array_merge_recursive($functions, $row);
		}
		
		try {
			$this->PDO = new PDO('sqlite:'.BOT_TOPDIR.'/cache/functions.sqlite');
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
			
			$this->PDO->query('CREATE TABLE functions (
				name VARCHAR(50),
				priority INT,
				dir VARCHAR(255),
				init VARCHAR(255),
				file VARCHAR(255),
				class VARCHAR(255),
				method VARCHAR(255),
				params TEXT,
				PRIMARY KEY (
					name ASC,
					priority ASC
				)
			)');
			
			$this->PDO->query('CREATE INDEX file ON functions (dir, file)');
			
			$st = $this->PDO->prepare('INSERT INTO functions (name, priority, dir, init, file, class, method, params) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
			
			$this->PDO->beginTransaction();
			foreach($functions as $name => $funcs) {
				$name = funcs::utfToAscii(strtolower($name));
				foreach($funcs as $priority => $func) {
					if(!isset($func['params'])) {
						$func['params'] = NULL;
					}
					
					$st->execute(array($name, $priority, $func['dir'], $func['init'], $func['file'], $func['class'], $func['method'], serialize($func['params'])));
				}
			}
			$this->PDO->commit();
		}
		catch(Exception $e) {
			@unlink(BOT_TOPDIR.'/cache/functions.sqlite');
			throw $e;
		}
	}
	
	/**
	 * Przetwarza wiadomość do bota
	 * @param BotMessage $msg Wiadomość
	 */
	function __construct(BotMessage $msg) {
		try {
			$this->init();
			
			$st = $this->PDO->prepare('SELECT dir, file, class, method, params FROM functions WHERE name=? ORDER BY priority ASC');
			$st->execute(array($msg->command));
			$data1 = $st->fetchAll();
			$st->execute(array('*'));
			$data2 = $st->fetchAll();
			
			$data = array_merge($data1, $data2);
			unset($data1, $data2);
			
			$return = NULL;
			
			foreach($data as $func) {
				if(!is_file(BOT_TOPDIR.$func['dir'].$func['file'])) {
					$st = $this->PDO->prepare('DELETE FROM functions WHERE dir=? AND file=?');
					$st->excecute(array($func['dir'], $func['file']));
					continue;
				}
				
				require_once(BOT_TOPDIR.$func['dir'].$func['file']);
				
				$class = new $func['class'];
				$return = $class->$func['method']($msg, unserialize($func['params']));
				
				if($return instanceof BotMsg) {
					break;
				}
			}
			
			if(!($return instanceof BotMsg)) {
				$return = new BotMsg('Nieznane polecenie. Wpisz <b>help</b> by uzyskać listę komend.');
			}
		}
		catch(Exception $e) {
			$return = new BotMsg('Wystąpił błąd podczas przetwarzania poleceń. Komunikat:<br />'.nl2br($e));
		}
		
		try {
			$class = substr(get_class($msg), strlen('BotMessage'));
			if(!$class) {
				throw new Exception('Wiadomość dostarczona za pomocą nieznanego interfejsu.');
			}
			
			$class = 'BotMsg'.$class;
			
			try {
				$class = new $class($return);
			}
			catch(Exception $e) {
				$class = new $class(new BotMsg('Wystąpił błąd podczas przetwarzania poleceń. Komunikat:<br />'.nl2br($e)));
			}
			
			$class->sendPullResponse();
		}
		catch(Exception $e) {
			echo 'Wystąpił błąd podczas przetwarzania poleceń. Komunikat: '.$e;
		}
	}
}
?>