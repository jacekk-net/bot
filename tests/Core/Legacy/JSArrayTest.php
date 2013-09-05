<?php
class JSArrayTest extends PHPUnit_Framework_TestCase {
	public function testEmptyString() {
		$result = jsarray::parse('');
		$this->assertSame(NULL, $result);
	}
	
	public function testEmptyArray() {
		$result = jsarray::parse('[]');
		$this->assertEquals(array(), $result);
	}
	
	public function testNestedArrays() { 
		$array = array(
			array(1, 2, array(), 5, array(6, array(7, 8))),
			array(9),
			'10'
		);
		$array_js = json_encode($array);
		$array_decoded = jsarray::parse($array_js);
		
		$this->assertEquals($array, $array_decoded);
	}
	
	public function testInvalid() {
		$result = jsarray::parse('()');
		$this->assertSame(FALSE, $result);
	}
}
