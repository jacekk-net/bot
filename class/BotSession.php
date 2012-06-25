<?php
/**
 * Klasa przechowująca dane użytkownika. Całość przypomina mechanizm sesji w PHP.
 */
class BotSession {
	private $PDO;
	
	/**
	 * Nazwa modułu, którego zmienne klasa przetwarza
	 * @var string max. 40 znak�w
	 */
	var $class;
	
	private $user;
	
	
	/**
	 * Inicjuje klasę w zależności od użytkownika
	 */
	function __construct($user) {
		$this->user = sha1($user);
		$this->user_struct = parse_url($user);
		
		$this->class = '';
	}
	
	private function init() {
		if($this->PDO) {
			return NULL;
		}
		
		if(is_file(BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite')) {
			$this->PDO = new PDO('sqlite:'.BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite');
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
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
			
			$this->PDO->beginTransaction();
			$st = $this->PDO->prepare('INSERT OR REPLACE INTO data (class, name, value) VALUES (?, ?, ?)');
			
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
	
	function truncate() {
		$this->init();
		
		$st = $this->PDO->prepare('DELETE FROM data WHERE class=?');
		$st->execute(array($this->class));
	}
}
?>