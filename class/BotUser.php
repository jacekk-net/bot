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
	 * @var string
	 */
	private $interface;
	
	/**
	 * Numer lub identyfikator użytkownika
	 * @var string
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
	 * @var string
	 */
	private $network;
	
	/**
	 * Identyfikator/unikalna nazwa bota, do którego skierowano zapytanie.
	 * Najczęściej numer Gadu-Gadu lub botkey w przypadku IMified.com
	 */
	private $bot;
	
	/**
	 * Parametry zapytania. Przy IMified równe zmiennej $_POST['channel']
	 * @var string
	 */
	private $params;
	
	function __construct($user) {
		$data = parse_url($user);
		
		$this->interface = $data['scheme'];
		$this->uid = strtr($data['user'], array('\\@' => '@'));
		$this->network = $data['host'];
		$this->bot = substr($data['path'], 1);
		$this->params = $data['query'];
	}
	
	function __get($name) {
		return $this->$name;
	}
}
?>