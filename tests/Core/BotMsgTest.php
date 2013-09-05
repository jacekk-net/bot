<?php
class BotMsgTest extends PHPUnit_Framework_TestCase {
	function testAppend() {
		$text = '';

		$substring = 'abc';
		$msg = new BotMsg($substring);
		$text .= $substring;
		
		$substring = 'cba';
		$msg->a($substring);
		$text .= $substring;
		
		$substring = 'cba';
		$msg->append($substring);
		$text .= $substring;

		$this->assertEquals($text, $msg->getRaw());
	}
	
	function testBeautifilText() {
		$msg = new BotMsg('<h1>Test</h1><p><u><i>This.</i></u></p><p><b>That!</b></p>');
		$expect = '= Test ='."\n"
			.'_/This./_'."\n\n"
			.'*That!*';
		$msg->setBeautiful(TRUE);
		$this->assertEquals($expect, $msg->getText());
		
		$expect = 'Test'."\n"
			.'This.'."\n\n"
			.'That!';
		$msg->setBeautiful(FALSE);
		$this->assertEquals($expect, $msg->getText());
		
	}
	
	function testGetText() {
		$msg = new BotMsg('<h2>Test</h2>'."\n"
			.'<h3>Test h3</h3>'."\n"
			.'<p><a href="http://jacekk.info">http://jacekk.info</a><br />'."\n"
			.'<a href="http://jacekk.info">Jacekk.info</a></p>');
		$expect = '== Test =='."\n"
			.'=== Test h3 ==='."\n"
			.'http://jacekk.info'."\n"
			.'Jacekk.info (http://jacekk.info)';
		$this->assertEquals($expect, $msg->getText());
		
		$msg = new BotMsg('<table>'."\n"
			.'<tr><th>Header 1</th> <th>Header 2</th></tr>'."\n"
			.'<tr><td>Cell 1</td> <td>Cell 2<img src="" /></td></tr>'."\n"
			.'</table>');
		$expect = '*Header 1*	 *Header 2*'."\n"
			.'Cell 1	 Cell 2';
		$this->assertEquals($expect, $msg->getText());
		
		$msg = new BotMsg('<h3>Test h3</h3>abc<p>Test</p>');
		$expect = '=== Test h3 ==='."\n"
			.'abc'."\n\n"
			.'Test';
		$this->assertEquals($expect, $msg->getText());
	}
	
	function testGetHTML() {
		$msg = new BotMsg('<h1>Test</h1>'."\n"
			.'<p><u><i>This.</i></u></p>'."\n"
			.'<p><b color="#fff">That!</b></p>'."\n"
			.'<p><a>http://jacekk.info</a></p>');
		$expect = '<h1>Test</h1>'."\n"
			.'<p><u><i>This.</i></u></p>'."\n"
			.'<p><b style="color:#fff;">That!</b></p>'."\n"
			.'<p><a href="http://jacekk.info">http://jacekk.info</a></p>';
		
		$this->assertEquals($expect, $msg->getHTML());
		$this->assertEquals($expect, (string)$msg);
	}
	
	function testHTMLError() {
		$oldhandler = set_error_handler('errorToException');
		
		$msg = new BotMsg('<![CDATA[ <p></p> ]]>');
		$msg->getHTML();
		
		set_error_handler($oldhandler);
	}
	
	function testSleep() {
		$msg = new BotMsg('<h1>Test</h1><p><u><i>This.</i></u></p><p><b>That!</b></p>');
		$raw = $msg->getRaw();
		$text = $msg->getText();
		$html = $msg->getHTML();
		
		$serialized = serialize($msg);
		$msg = unserialize($serialized);
		
		$this->assertEquals($raw, $msg->getRaw());
		$this->assertEquals($text, $msg->getText());
		$this->assertEquals($html, $msg->getHTML());
	}
}
