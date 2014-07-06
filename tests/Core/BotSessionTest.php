<?php
class BotSessionTest extends PHPUnit_Framework_TestCase {
	private static $dataFolder;
	private static $legacyFolder;
	
	private static function tmpdir() {
		$tmpName = tempnam(sys_get_temp_dir(), 'Bot');
		unlink($tmpName);
		mkdir($tmpName);
		return $tmpName;
	}
	
	private static function rmdir($dir) {
		foreach(glob($dir.'/*', GLOB_NOSORT) as $name) {
			if($name == '.' || $name == '..') continue;
			
			if(is_dir($name)) {
				self::rmdir($name);
			} else {
				unlink($name);
			}
		}
		
		rmdir($dir);
	}
	
	/**
	 * Create one-time directories for testing purposes.
	 */
	static function setUpBeforeClass() {
		self::$dataFolder = self::tmpdir();
		self::$legacyFolder = self::tmpdir();
	}
	
	function testPullEmpty() {
		$session = new BotSession('test://user1@test', self::$dataFolder, self::$legacyFolder);
		$session->setClass('test');
		
		$this->assertEquals(array(), $session->pull());
		$this->assertTrue(count(glob(self::$dataFolder.'/*.sqlite')) == 1);
	}
	
	/**
	 * @expectedException Exception
	 */
	function testSetClass() {
		$session = new BotSession('test://testException', self::$dataFolder, self::$legacyFolder);
		$session->pull();
	}
	
	function testLegacyImport() {
		$data = array('test' => true, 'other' => 'yes, sir!');
		$data_serialized = serialize($data);
		
		$this->assertTrue(mkdir(self::$legacyFolder.'/test'));
		
		$filename = self::$legacyFolder.'/test/legacyUser.ggdb';
		$this->assertEquals(strlen($data_serialized), file_put_contents($filename, $data_serialized));
		$this->assertEquals($data_serialized, file_get_contents($filename));
		
		$session = new BotSession('test://legacyUser@test', self::$dataFolder, self::$legacyFolder);
		$session->setClass('test');
		
		$this->assertTrue(isset($session->test));
		$this->assertEquals($data, $session->pull());
		
		$this->assertFalse(file_exists($filename));
	}
	
	/**
	 * @depends testPullEmpty
	 */
	function testManualExample() {
		$session = new BotSession('test://user1@test', self::$dataFolder, self::$legacyFolder);
		$session->setClass('test');
		
		// Ustawienie pojedynczej wartości
		$session->zmienna = 'To jest test';
		$this->assertTrue(isset($session->zmienna));
		$this->assertEquals('To jest test', $session->zmienna);
		
		// Usunięcie pojedynczej wartości
		unset($session->zmienna);
		$this->assertFalse(isset($session->zmienna));
		$this->assertEquals(NULL, $session->zmienna);
		
		// Ustawienie pojedynczej wartości ponownie
		$session->zmienna = 'To jest test';
		$this->assertTrue(isset($session->zmienna));
		$this->assertEquals('To jest test', $session->zmienna);
		
		// Usunięcie wszystkich danych
		$session->truncate();
		$this->assertFalse(isset($session->zmienna));
		$this->assertEquals(NULL, $session->zmienna);
		$this->assertEquals(array(), $session->pull());

		// Dopisanie (nadpisanie) danych
		$array = array(
			'zmienna' => 'To jest test2',
			'zmienna2' => new DateTime('2012-01-10')
		);
		$session->push($array);

		$this->assertEquals('To jest test2', $session->zmienna);
		$this->assertEquals($array, $session->pull());
		
		// push() nie usuwa istniejących danych
		$session->zmienna3 = '333';
		$session->push($array);
		$this->assertNotEquals($array, $session->pull());
		
		unset($this->session);
	}
	
	/**
	 * @depends testManualExample
	 */
	function testManualExample2() {
		$session = new BotSession('test://user1@test', self::$dataFolder, self::$legacyFolder);
		$session->setClass('test');
		
		$array = array(
			'zmienna' => 'To jest test2',
			'zmienna2' => new DateTime('2012-01-10'),
			'zmienna3' => '333'
		);
		
		$this->assertEquals($array, $session->pull());
		
		
		$session->setClass('test2');
		$this->assertEquals(array(), $session->pull());
	}
	
	static function tearDownAfterClass() {
		foreach(glob(self::$dataFolder.'/*.sqlite') as $file) {
			unlink($file);
		}
		
		self::rmdir(self::$dataFolder);
		self::rmdir(self::$legacyFolder);
	}
}
