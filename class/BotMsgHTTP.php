<?php
/**
 * Klasa konwertuje wiadomość ({@link BotMsg}) do formatu odpowiedniego dla interfejsu WWW
 */
class BotMsgHTTP implements BotMsgInterface {
	private $html = '';
	
	/**
	 * @param BotMsg $msg Wiadomość do przekonwertowania
	 */
	function __construct(BotMsg $msg) {
		$this->html = $msg->getHTML();
	}
	
	/**
	 * @return string
	 * Zwraca wiadomość HTML
	 */
	function __toString() {
		return $this->html;
	}
	
	/**
	 * @return string
	 * Zwraca wiadomość w formacie HTML
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