<?php
/**
 * Przechowuje informacje o użytkowniku i protokole
 */
class BotUser {
	/**
	 * Interfejs, za pomocą którego nadeszło zapytanie. Jedno z:
	 * - Gadu-Gadu
	 * - IMified
	 * - HTTP
	 * - Local
	 * @var string $interface
	 */
	private $interface;
	
	/**
	 * Numer lub identyfikator użytkownika
	 * @var string $uid
	 */
	private $uid;
	
	/**
	 * Identyfikator sieci użytkownika. Najczęściej jedno z:
	 * - gadu-gadu.pl
	 * - userkey.imified.com - w polu {@link BotUser::$uid} znajduje się unikalny klucz użytkownika
	 * - jabber.imified.com
	 * - aim.imified.com
	 * - msn.imified.com
	 * - yahoo.imified.com
	 * - gtalk.imified.com
	 * - localhost
	 * @var string $network
	 */
	private $network;
	
	/**
	 * Identyfikator/unikalna nazwa bota, do którego skierowano zapytanie.
	 * Najczęściej numer Gadu-Gadu lub botkey w przypadku IMified.com
	 * @var string $bot
	 */
	private $bot;
	
	/**
	 * Parametry zapytania. Przy IMified równe zmiennej $_POST['channel']
	 * @var string $params
	 */
	private $params;
	
	/**
	 * Konstruktor. W argumencie otrzymuje pseudo-URL określający użytkownika i sieć.
	 * Przykłady:
	 * - Gadu-Gadu://123456\@gadu-gadu.pl
	 * - IMified://user\\\@jabber\@jabber.imified.com/BOTKEY?private
	 * @param string $user URL użytkownika
	 */
	function __construct($user) {
		$data = parse_url($user);
		
		$this->interface = $data['scheme'];
		$this->uid = strtr($data['user'], array('\\@' => '@'));
		$this->network = $data['host'];
		$this->bot = substr(@$data['path'], 1);
		$this->params = @$data['query'];
	}
	
	/**
	 * Umożliwia dostęp tylko do odczytu do prywanych zmiennych
	 * @param string $name Nazwa zmiennej
	 * @return mixed Wartość zmiennej prywatnej
	 */
	function __get($name) {
		return $this->$name;
	}
}
?>