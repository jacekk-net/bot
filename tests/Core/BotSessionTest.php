<?php
class BotSessionTest extends PHPUnit_Framework_TestCase {
	function testSessionFolder() {
		$dbFolder = dirname(__FILE__).'/../../database';
		
		$this->assertTrue(is_writable($dbFolder));
		$this->assertTrue(count(glob($dbFolder.'/*.sqlite')) == 0);
	}
	
	/**
	 * @depends testSessionFolder
	 */
	function testPullEmpty() {
		$dbFolder = dirname(__FILE__).'/../../database';
		
		$session = new BotSession('test://user1@test');
		$session->setClass('test');
		
		$this->assertEquals(array(), $session->pull());
		$this->assertTrue(count(glob($dbFolder.'/*.sqlite')) == 1);
	}
	
	/**
	 * @depends testPullEmpty
	 * @expectedException Exception
	 */
	function testSetClass() {
		$session = new BotSession('test://user1');
		$session->pull();
	}
	
	/**
	 * @depends testPullEmpty
	 */
	function testLegacyImport() {
		$dbFolder = dirname(__FILE__).'/../../database';
		$oldDbFolder = $dbFolder = dirname(__FILE__).'/../../db';
		
		$data = array('test' => true, 'other' => 'yes, sir!');
		$data_serialized = serialize($data);
		
		$this->assertTrue(mkdir($oldDbFolder));
		$this->assertTrue(is_writable($oldDbFolder));
		$this->assertTrue(mkdir($oldDbFolder.'/test'));
		
		$filename = $oldDbFolder.'/test/testUser.ggdb';
		$this->assertEquals(strlen($data_serialized), file_put_contents($filename, $data_serialized));
		$this->assertEquals($data_serialized, file_get_contents($filename));
		
		$session = new BotSession('test://testUser@test');
		$session->setClass('test');
		
		$this->assertTrue(isset($session->test));
		$this->assertEquals($data, $session->pull());
		
		$this->assertFalse(file_exists($filename));
		$this->assertTrue(rmdir($oldDbFolder.'/test'));
		$this->assertTrue(rmdir($oldDbFolder));
	}
	
	/**
	 * @depends testPullEmpty
	 */
	function testManualExample() {
		$session = new BotSession('test://user1@test');
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
		$session = new BotSession('test://user1@test');
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
	
	/**
	 * @depends testManualExample2
	 */
	function testCleanup() {
		$dbFolder = dirname(__FILE__).'/../../database';
		foreach(glob($dbFolder.'/*.sqlite') as $file) {
			unlink($file);
		}
	}
}
