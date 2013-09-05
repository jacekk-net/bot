<?php
/**
 * Interfejs do obsługi obrazków przychodzących
 */
abstract class BotImage {
	/**
	 * Zmienna przechowująca zasób biblioteki GD2 z obrazkiem
	 * @var resource $data
	 */
	protected $data = NULL;
	
	/**
	 * Funkcja zwracająca zasób GD2 z obrazkiem
	 * @return resource Zasób GD2
	 */
	abstract function getImage();
	
	/**
	 * Funkcja zwracająca dane obrazka w formie ciągu bajtów.
	 * @return string Obrazek
	 */
	abstract function getImageData();
}
?>