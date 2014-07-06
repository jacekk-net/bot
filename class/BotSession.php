<?php
/**
 * Klasa przechowująca dane przekazane przez użytkownika,
 * w szczególności jego ustawienia.
 */
class BotSession {
	/**
	 * Instancja PDO tworzona w metodzie {@link BotSession::init()}.
	 * @var PDO $PDO
	 */
	protected $PDO;

	/**
	 * Katalog, w którym trzymane są dane sesyjne użytkowników.
	 * @var string $sessionDir
	 */
	protected $sessionDir;

	/**
	 * Katalog, w którym trzymane są dane sesyjne użytkowników
	 * z poprzedniej wersji bota.
	 * @var string $legacySessionDir
	 */
	protected $legacySessionDir;

	/**
	 * Nazwa modułu (max. 40 znaków), którego zmienne klasa aktualnie przetwarza,
	 * ustawiana metodą {@link BotSession::setClass()}.
	 * @var string $class
	 */
	protected $class = '';
	
	/**
	 * Pseudo-URL użytkownika.
	 * @see BotUser
	 * @var string $user
	 */
	protected $user;

	/**
	 * Inicjuje klasę dla podanego użytkownika
	 * @param string $user Pseudo-URL użytkownika
	 * @param string $sessionDir Katalog z danymi, domyślnie BOT_TOPDIR/database
	 * @param string $legacySessionDir Katalog z danymi ze starej wersji bota, domyślnie BOT_TOPDIR/db
	 */
	public function __construct($user, $sessionDir = NULL, $legacySessionDir = NULL) {
		if(empty($sessionDir)) {
			$sessionDir = BOT_TOPDIR.'/database';
		}
		if(empty($legacySessionDir)) {
			$legacySessionDir = BOT_TOPDIR.'/db';
		}

		$this->user = $user;
		$this->sessionDir = $sessionDir;
		$this->legacySessionDir = $legacySessionDir;
	}

	/**
	 * Sprawdza ustawienie pola {@link BotSession::$class} oraz, jeśli nie została wykonana wcześniej,
	 * dokonuje inicjalizacji klasy.
	 * Metoda ta winna być wywoływana przez każdą publiczną funkcję operującą na danych.
	 * @throws Exception Wyjątek rzucany, gdy przed użyciem metody, nazwa klasy
	 *  nie została ustawiona metodą {@link BotSession::setClass()}
	 */
	protected function init() {
		if(empty($this->class)) {
			throw new Exception('Przed użyciem mechanizmu sesji należy ustawić nazwę modułu za pomocą metody setClass - patrz "Poradnik tworzenia modułów", dział "Klasa BotMessage", rozdział "Pole $session".');
		}
		
		if($this->PDO) {
			// Inicjalizacja została już przeprowadzona - wyjdź.
			return;
		}

		$dbFile = $this->sessionDir.'/'.sha1(sha1($this->user)).'.sqlite';

		$this->PDO = new PDO('sqlite:'.$dbFile);
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);

		$st = $this->PDO->query('SELECT COUNT(name) FROM sqlite_master WHERE type=\'table\' AND name=\'data\'');
		$num = $st->fetch(PDO::FETCH_NUM);
		$schemaExists = $num[0] > 0;

		if($schemaExists) {
			$this->updateDatabase();
		} else {
			try {
				$this->createSchema();
				$this->importLegacyData();
			}
			catch(Exception $e) {
				// Import danych nie udał się - usuń pozostałości.
				if(file_exists($dbFile)) {
					@unlink($dbFile);
				}
				throw $e;
			}
		}
	}
	
	/**
	 * Ustawia nazwę modułu/klasy, której zmienne będą przetwarzane
	 * @param string $class Nazwa modułu
	 */
	public function setClass($class) {
		$this->class = $class;
	}
	
	/**
	 * Pobiera zmienną o podanej nazwie (getter).
	 * @param string $name Nazwa zmiennej.
	 * @return mixed Wartość zmiennej lub NULL, jeśli zmienna nie istnieje.
	 */
	public function __get($name) {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT value FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
		$st = $st->fetch(PDO::FETCH_ASSOC);
		
		if(is_array($st)) {
			return unserialize($st['value']);
		}

		return NULL;
	}
	
	/**
	 * Ustawia zmienną o podanej nazwie.
	 * @param string $name Nazwa zmiennej.
	 * @param mixed $value Wartość do ustawienia.
	 */
	public function __set($name, $value) {
		$this->init();
		
		$st = $this->PDO->prepare('INSERT OR REPLACE INTO data (class, name, value) VALUES (?, ?, ?)');
		$st->execute(array($this->class, $name, serialize($value)));
	}
	
	/**
	 * Sprawdza czy podana zmienna została ustawiona.
	 * @param string $name Nazwa zmiennej do sprawdzenia.
	 * @return bool Czy zmienna istnieje?
	 */
	public function __isset($name) {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT COUNT(name) FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
		$st = $st->fetch(PDO::FETCH_NUM);
		
		return ($st[0] > 0);
	}
	
	/**
	 * Usuwa zmienną o podanej nazwie.
	 * @param string $name Nazwa zmiennej do usunięcia.
	 */
	public function __unset($name) {
		$this->init();
		
		$st = $this->PDO->prepare('DELETE FROM data WHERE class=? AND name=?');
		$st->execute(array($this->class, $name));
	}
	
	/**
	 * Dodaje tablicę zmiennych do danych użytkownika.
	 * @param array $array Tablica zmiennych do dodania.
	 */
	public function push($array) {
		$this->init();

		$this->PDO->beginTransaction();
		foreach($array as $name => $value) {
			$this->__set($name, $value);
		}
		$this->PDO->commit();
	}
	
	/**
	 * Zwraca wszystkie ustawione zmienne dla modułu.
	 * @return array Lista wszystkich zmiennych.
	 */
	public function pull() {
		$this->init();
		
		$st = $this->PDO->prepare('SELECT name, value FROM data WHERE class=?');
		$st->execute(array($this->class));
		$rows = $st->fetchAll(PDO::FETCH_ASSOC);
		
		$return = array();
		foreach($rows as $row) {
			$return[$row['name']] = unserialize($row['value']);
		}
		
		return $return;
	}
	
	/**
	 * Usuwa wszystkie zmienne sesyjne danego modułu.
	 */
	public function truncate() {
		$this->init();
		
		$st = $this->PDO->prepare('DELETE FROM data WHERE class=?');
		$st->execute(array($this->class));
	}

	/**
	 * Aktualizuje schemat bazy danych oraz dane, w szczególności poprawia błędy
	 * wprowadzone we wcześniejszych wersjach (np. brak ustawionej nazwy klasy).
	 */
	private function updateDatabase() {
		$st = $this->PDO->query('SELECT value FROM data WHERE class=\'\' AND name=\'_version\'');
		$row = $st->fetch(PDO::FETCH_ASSOC);

		$version = 0;
		if (is_array($row)) {
			$version = (int)$row['value'];
		}

		$st->closeCursor();

		switch($version) {
			case 1:
				$this->PDO->query('UPDATE data SET class=\'kino\' WHERE class=\'\' AND name=\'kino\'');
				$this->PDO->query('INSERT OR REPLACE INTO data (class, name, value) VALUES (\'\', \'_version\', 1)');
			case 2:
			case 3:
				$this->PDO->query('DELETE FROM data WHERE class IS NULL AND name=\'user_struct\'');
				$this->PDO->query('INSERT OR REPLACE INTO data (class, name, value) VALUES (\'\', \'_version\', 4)');
				break;
		}
	}

	/**
	 * Tworzy schemat bazy danych sesyjnych.
	 */
	private function createSchema() {
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
	}

	/**
	 * Importuje dane użytkowników z poprzedniej wersji bota.
	 */
	private function importLegacyData() {
		$userData = parse_url($this->user);
		$files = glob($this->legacySessionDir.'/*/'.$userData['user'].'.ggdb');
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
}
