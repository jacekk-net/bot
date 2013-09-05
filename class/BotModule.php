<?php
class BotModuleException extends Exception {}

interface BotModule {}

/**
 * Interfejs klasy inicjującej moduł bota
 */
interface BotModuleInit {
	/**
	 * Funkcja zwracająca listę obsługiwanych komend.
	 * Przykład:
	 * <pre>array(
	 *   'komenda' => array(
	 *     array(
	 *       'file' => 'komenda.php',
	 *       'class' => 'bot_NAZWAMODULU_module',
	 *       'method' => 'komenda1',
	 *       'params' => 'parametr_do_funkcji',
	 *     ),
	 *     array(
	 *       'file' => 'komenda.php',
	 *       'class' => 'bot_NAZWAMODULU_module',
	 *       'method' => 'komenda2',
	 *     ),
	 *   ),
	 *   '*' => array(
	 *     array(
	 *       'file' => 'test.php',
	 *       'class' => 'NAZWAMODULU_test',
	 *       'method' => 'komenda_test',
	 *     ),
	 *   ),
	 * )</pre>
	 * @return array Lista obsługiwanych komend
	 */
	function register();
	
	/**
	 * Zwraca pomoc dla polecenia lub skróconą listę poleceń
	 * obsługiwanych przez dany moduł.
	 * @param string|NULL $params Nazwa komendy
	 * @return BotMsg Pomoc dla komendy
	 */
	function help($params = NULL);
}
?>