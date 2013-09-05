<?php
class BotMessageTest extends PHPUnit_Framework_TestCase {
	function testAppend() {
		$input = '   ąęß  śćżń  óó ';
		$message = new BotMessage();
		$message->setText($input);
		
		$this->assertEquals($input, $message->rawText);
		$this->assertEquals('aess sczn oo', $message->text);
		$this->assertEquals('aess', $message->command);
		$this->assertEquals('śćżń  óó', $message->args);
	}
}
