<?php
class BotMessage {
	/**
	 * Informacje o kliencie
	 * @var BotUser
	 */
	protected $user;
	/**
	 * Informacje o kliencie zgodne z poprzednią wersją Bota (dot. API IMified).
	 * Najczęściej równe {@link BotMessage::$user}
	 * @var BotUser
	 */
	protected $userAlt;
	
	/**
	 * Sesja przypisana do użytkownika i modułu
	 * @var BotSession
	 */
	protected $session;
	
	/**
	 * Tekst otrzymany od API - bez zmian
	 * @var string
	 */
	protected $rawText;
	
	/**
	 * Czysty tekst, tylko znaki ASCII, małe litery, podwójne spacje zamienione na pojedyncze
	 * @var string
	 */
	protected $text;
	
	/**
	 * Komenda, tylko znaki ASCII, małe litery
	 * @var string
	 */
	private $command;
	
	/**
	 * Argumenty polecenia - oryginalne
	 * @var string
	 */
	private $args;
	
	function __get($name) {
		return $this->$name;
	}
	
	function setText($value) {
		$this->rawText = $value;
		$this->text = funcs::utfToAscii($value);
		$this->command = funcs::utfToAscii(trim(strtok($value, " \t\r\n")));
		$this->args = trim(strtok(''));
	}
}
?>