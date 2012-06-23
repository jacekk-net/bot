<?php
/**
 * Klasa konwertuje wiadomość ({@link BotMsg}) do formatu odpowiedniego dla IMified.com
 */
class BotMsgIMI implements BotMsgInterface {
	private $html = '';
	
	/**
	 * @param BotMsg $msg Wiadomość do przekonwertowania
	 */
	function __construct(BotMsg $msg) {
		$msg->a('<reset />');
		$parser = $msg->getParser();
		$this->html = $parser->saveXML($parser->getElementsByTagName('body')->item(0));
		$this->html = (string)substr($this->html, 6, -7);
	}
	
	/**
	 * @return string
	 * Zwraca wiadomość zgodną z API IMified.com 
	 */
	function __toString() {
		return $this->html;
	}
	
	/**
	 * @return string
	 * Zwraca wiadomość w formacie HTML z tagami odpowienimi dla IMified.com
	 */
	function getHTML() {
		return $this->html;
	}
	
	function sendPullResponse() {
		header('Content-Type: text/html; charset=utf-8');
		echo $this;
	}
}
?>