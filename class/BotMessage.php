<?php
class BotMessage {
	/**
	 * Informacje o kliencie
	 * @var BotUser $user
	 */
	protected $user;
	
	/**
	 * Informacje o kliencie zgodne z poprzednią wersją Bota (dot. API IMified).
	 * Najczęściej równe {@link BotMessage::$user}
	 * @var BotUser $userAlt
	 */
	protected $userAlt;
	
	/**
	 * Sesja przypisana do użytkownika i modułu
	 * @var BotSession $session
	 */
	protected $session;
	
	/**
	 * Tekst otrzymany od API - bez zmian
	 * @var string $rawText
	 */
	protected $rawText;
	
	/**
	 * Czysty tekst, tylko znaki ASCII, małe litery, podwójne spacje zamienione na pojedyncze
	 * @var string $text
	 */
	protected $text;
	
	/**
	 * Tablica obrazków (zobacz {@link BotImage}) przesłanych do bota przez użytkownika.
	 * @var array $images
	 */
	protected $images = array();
	
	/**
	 * Komenda, tylko znaki ASCII, małe litery
	 * @var string $command
	 */
	private $command;
	
	/**
	 * Argumenty polecenia - oryginalne
	 * @var string $args
	 */
	private $args;
	
	/**
	 * Umożliwia dostęp tylko do odczytu do prywanych zmiennych
	 * @param string $name Nazwa zmiennej
	 * @return mixed Wartość zmiennej prywatnej
	 */
	function __get($name) {
		return $this->$name;
	}
	
	/**
	 * Na podstawie nieprzetworzonej wiadomości ({@link BotMessage::$rawText})
	 * metoda ustawia wszystkie pola klasy.
	 * @param string $value Nieprzetworzona wiadomość
	 */
	function setText($value) {
		$this->rawText = $value;
		
		$value = trim($value);
		$this->text = funcs::utfToAscii($value);
		$this->command = funcs::utfToAscii(trim(strtok($value, " \t\r\n")));
		$this->args = trim(strtok(''));
	}
}
?>