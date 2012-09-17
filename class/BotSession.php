<?php
/**
 * Klasa przechowująca dane użytkownika. Całość przypomina mechanizm sesji w PHP.
 */
class BotSession {
	private $PDO;
	
	/**
	 * Nazwa modułu, którego zmienne klasa przetwarza
	 * @var string max. 40 znaków
	 */
	protected $class = '';
	protected $class_empty = TRUE;
	
	private $user;
	
	/**
	 * Inicjuje klasę w zależności od użytkownika
	 */
	function __construct($user) {
		$this->user = sha1($user);
		$this->user_struct = parse_url($user);
		
		$this->class_empty = FALSE;
	}
	
	private function init() {
		if(strlen($this->class) == 0 && !$this->class_empty) {
			throw new Exception('Przed użyciem $msg->session należy ustawić nazwę modułu za pomocą metody setClass - patrz "Poradnik tworzenia modułów", dział "Klasa BotMessage", rozdział "Pole $session".');
		}
		
		if($this->PDO) {
			return NULL;
		}
		
		if(is_file(BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite')) {
			$this->PDO = new PDO('sqlite:'.BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite');
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
			
			$st = $this->PDO->query('SELECT value FROM data WHERE class=\'\' AND name=\'_version\'');
			if($st->rowCount > 0) {
				$row = $st->fetch(PDO::FETCH_ASSOC);
				$version = (int)$row['value'];
			}
			else
			{
				$version = 0;
			}
			$st->closeCursor();
			
			if($version < 1) {
				$this->PDO->query('UPDATE data SET class=\'kino\' WHERE class=\'\' AND name=\'kino\'');
				$this->PDO->query('INSERT OR REPLACE INTO data (class, name, value) VALUES (\'\', \'_version\', 1)');
				$version = 1;
			}
			
			if($version < 2) {
				$this->PDO->query('DELETE FROM data WHERE class=NULL AND name=\'user_struct\'');
				$this->PDO->query('INSERT OR REPLACE INTO data (class, name, value) VALUES (\'\', \'_version\', 2)');
				$version = 2;
			}
			
			return;
		}
		
		try {
			$this->PDO = new PDO('sqlite:'.BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite');
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
			
			$this->PDO->query(
				'CREATE TABLE data (
					class VARCHAR(50),
					name VARCHAR(40) NOT NULL,
					value TEXT NOT NULL,
					PRIMARY KEY (
						class ASC,
						name ASC
					)
				)'
			);
			
			$files = glob(BOT_TOPDIR.'/db/*/'.$this->user_struct['user'].'.ggdb');
			if(!$files) {
				return;
			}
			
			$this->PDO->beginTransaction();
			$st = $this->PDO->prepare('INSERT OR REPLACE INTO data (class, name, value) VALUES (?, ?, ?)');
			
			$st->execute(array('', '_version', 2));
			
			foreach($files as $file) {
				$data = unserialize(file_get_contents($file));
				foreach($data as $name => $value) {
					$st->execute(array($this->class, $name, $value));
				}
			}
			
			$this->PDO->commit();
			
			foreach($files as $file) {
				unlink($file);
			}
		}
		catch(Exception $e) {
			if(file_exists(BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite')) {
				@unlink(BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite');
			}
			throw $e;
		}
	}
	
	function __get($name) {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT value FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
		$st = $st->fetch(PDO::FETCH_ASSOC);
		
		if(is_array($st)) {
			return unserialize($st['value']);
		}
		else
		{
			return NULL;
		}
	}
	
	function __set($name, $value) {
		$this->init();
		
		$st = $this->PDO->prepare('INSERT OR REPLACE INTO data (class, name, value) VALUES (?, ?, ?)');
		$st->execute(array($this->class, $name, serialize($value)));
	}
	
	function __isset($name) {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT COUNT(name) FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
		$st = $st->fetch(PDO::FETCH_NUM);
		
		return ($st[0]>0);
	}
	
	function __unset($name) {
		$this->init();
		
		$st = $this->PDO->prepare('DELETE FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
	}
	
	function push($array) {
		$this->PDO->beginTransaction();
		foreach($array as $name => $value) {
			$this->__set($name, $value);
		}
		$this->PDO->commit();
	}
	
	function pull() {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT name, value FROM data WHERE class=?');
		$st->execute(array($this->class));
		$st = $st->fetchAll(PDO::FETCH_ASSOC);
		
		$return = array();
		foreach($st as $row) {
			$return[$row['name']] = $row['value'];
		}
		
		return $return;
	}
	
	function setClass($class) {
		$this->class = $class;
	}
	
	function truncate() {
		$this->init();
		
		$st = $this->PDO->prepare('DELETE FROM data WHERE class=?');
		$st->execute(array($this->class));
	}
}
?>