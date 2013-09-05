<?php
/**
 * Klasa przechowująca dane przekazane przez użytkownika,
 * w szczególności jego ustawienia.
 */
class BotSession {
	private $PDO;
	
	/**
	 * Nazwa modułu, którego zmienne klasa przetwarza
	 * @var string $class max. 40 znaków
	 */
	protected $class = '';
	protected $class_empty = TRUE;
	
	/**
	 * Pseudo-URL użytkownika.
	 * @see BotUser
	 * @var string $user URL użytkownika
	 */
	private $user;
	/**
	 * Klasa z identyfikatorem użytkownika
	 * @var BotUser $user_struct
	 */
	private $user_struct;
	
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
			$row = $st->fetch(PDO::FETCH_ASSOC);
			if(is_array($row)) {
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
			
			if($version < 4) {
				$this->PDO->query('DELETE FROM data WHERE class IS NULL AND name=\'user_struct\'');
				$this->PDO->query('INSERT OR REPLACE INTO data (class, name, value) VALUES (\'\', \'_version\', 4)');
				$version = 4;
			}
			
			return;
		}
		
		try {
			$this->PDO = new PDO('sqlite:'.BOT_TOPDIR.'/database/'.sha1($this->user).'.sqlite');
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
			
			$this->PDO->query(
				'CREATE TABLE data (
					class VARCHAR(50) NOT NULL DEFAULT \'\',
					name VARCHAR(40) NOT NULL,
					value TEXT NOT NULL,
					PRIMARY KEY (
						class ASC,
						name ASC
					)
				)'
			);
			
			$this->PDO->query('INSERT INTO data (class, name, value) VALUES (\'\', \'_version\', 4)');
			
			$files = glob(BOT_TOPDIR.'/db/*/'.$this->user_struct['user'].'.ggdb');
			if(!$files) {
				return;
			}
			
			$this->PDO->beginTransaction();
			$st = $this->PDO->prepare('INSERT OR REPLACE INTO data (class, name, value) VALUES (?, ?, ?)');
			
			foreach($files as $file) {
				$data = unserialize(file_get_contents($file));
				foreach($data as $name => $value) {
					$st->execute(array($this->class, $name, serialize($value)));
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
	
	/**
	 * Ustawia nazwę modułu/klasy, której zmienne będą przetwarzane
	 * @param string $class Nazwa modułu
	 */
	function setClass($class) {
		$this->class = $class;
	}
	
	/**
	 * Pobiera zmienną modułu o podanej nazwie (getter).
	 * @param string $name Nazwa zmiennej
	 * @return mixed Wartość zmiennej lub NULL
	 */
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
	
	/**
	 * Ustawia zmienną o podanej nazwie
	 * @param string $name Nazwa zmiennej
	 * @param mixed $value Wartość zmiennej
	 */
	function __set($name, $value) {
		$this->init();
		
		$st = $this->PDO->prepare('INSERT OR REPLACE INTO data (class, name, value) VALUES (?, ?, ?)');
		$st->execute(array($this->class, $name, serialize($value)));
	}
	
	/**
	 * Sprawdza czy podana zmienna została ustawiona.
	 * @param string $name Nazwa zmiennej
	 * @return bool Czy zmienna istnieje?
	 */
	function __isset($name) {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT COUNT(name) FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
		$st = $st->fetch(PDO::FETCH_NUM);
		
		return ($st[0]>0);
	}
	
	/**
	 * Usuwa zmienną o podanej nazwie
	 * @param string $name Nazwa zmiennej
	 */
	function __unset($name) {
		$this->init();
		
		$st = $this->PDO->prepare('DELETE FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
	}
	
	/**
	 * Zapamiętuje tablicę zmiennych danego modułu
	 * @param array $array Tablica zmiennych
	 */
	function push($array) {
		$this->PDO->beginTransaction();
		foreach($array as $name => $value) {
			$this->__set($name, $value);
		}
		$this->PDO->commit();
	}
	
	/**
	 * Zwraca wszystkie ustawione zmienne danego modułu
	 * @return array Lista wszystkich zmiennych
	 */
	function pull() {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT name, value FROM data WHERE class=?');
		$st->execute(array($this->class));
		$st = $st->fetchAll(PDO::FETCH_ASSOC);
		
		$return = array();
		foreach($st as $row) {
			$return[$row['name']] = unserialize($row['value']);
		}
		
		return $return;
	}
	
	/**
	 * Usuwa wszystkie zmienne sesyjne danego modułu.
	 */
	function truncate() {
		$this->init();
		
		$st = $this->PDO->prepare('DELETE FROM data WHERE class=?');
		$st->execute(array($this->class));
	}
}
?>