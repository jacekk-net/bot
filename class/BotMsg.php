<?php
class BotMsgException extends Exception {}

/**
 * Interfejs dla klas przetwarzających wiadomości wychodzące
 * do formatu właściwego dla danej sieci.
 */
interface BotMsgInterface {
	/**
	 * Konstruktor
	 * @param BotMsg $msg Wiadomość do przetworzenia
	 */
	function __construct(BotMsg $msg);
	/**
	 * Zwraca przetworzoną wiadomość
	 * @return string Wiadomość po przetworzeniu
	 */
	function __toString();
	
	/**
	 * Podaje na wyjście (np. za pomocą echo) wiadomość w formacie
	 * odpowiednim dla danego API, uwzględniając nagłówki HTTP
	 * i inne konieczne elementy.
	 */
	function sendPullResponse();
}

/**
 * Klasa reprezentująca wiadomość wychodzącą.
 */
class BotMsg {
	private $beautiful = TRUE;
	private $parser = NULL;
	private $html = NULL;
	private $text = NULL;
	private $raw = '';
	
	/**
	 * Włącza lub wyłącza "upiększanie" konwertowanej
	 * do czystego tekstu ({@link BotMsg::getText()}) wiadomości, np.:
	 *
	 * &lt;b&gt;abc&lt;/b&gt; zamieniane jest na \*abc\*
	 *
	 * &lt;h1&gt;efg&lt;h1&gt; przechodzi w = efg =
	 *
	 * Domyślnie włączone
	 * @param bool $set Ustawienie "upiększania"
	 */
	function setBeautiful($set = FALSE) {
		if($this->beautiful != $set) {
			$this->text = $this->html = $this->parser = NULL;
			$this->beautiful = (bool)$set;
		}
	}
	
	/**
	 * @deprecated Zastąpiono funkcją {@link BotMsg::setBeautiful()}
	 */
	function beautifulText($set = FALSE) {
		$this->setBeautiful($set);
	}
	
	/**
	 * Konstruktor. De facto alias dla {@link BotMsg::append()}
	 */
	function __construct($str = NULL) {
		if($str !== NULL) {
			$this->append($str);
		}
	}
	
	/**
	 * Serializacja klasy wymaga zapisania tylko niektórych elementów
	 */
	function __sleep() {
		return array('beautiful', 'raw');
	}
	
	/**
	 * Alias dla {@link BotMsg::append()}
	 */
	function a($str) {
		$this->append($str);
	}
	
	/**
	 * Dodaje kod HTML na koniec wiadomości
	 * @param string $str Treść do dodania
	 */
	function append($str) {
		$this->text = $this->html = $this->parser = NULL;
		$this->raw .= (string)$str;
	}
	
	/**
	 * Zwraca wiadomość jako czysty tekst
	 * @return string Wiadomość
	 */
	function getText() {
		if($this->text === NULL) {
			$this->text = trim($this->parseTextDOM($this->getParser()->getElementsByTagName('body')->item(0)));
		}
		
		return $this->text;
	}
	
	/**
	 * Zwraca wiadomość jako kod HTML
	 * @return string Wiadomość
	 */
	function getHTML() {
		if($this->html === NULL) {
			$doc = $this->getParser();
			$this->parseHTMLDOM( $doc->getElementsByTagName('body')->item(0) );
			$this->html = $doc->saveXML( $doc->getElementsByTagName('body')->item(0) );
		}
		
		return (string)substr($this->html, 6, -7);
	}
	
	/**
	 * Zwraca treść wiadomości zapisaną przy użyciu {@link BotMsg::append()} bez żadnych modyfikacji
	 * @return string Oryginalna wiadomość
	 */
	function getRaw() {
		return $this->raw;
	}
	
	/**
	 * Zwraca wiadomość jako kod HTML
	 * @return string Wiadomość
	 */
	function __toString() {
		return $this->getHTML();
	}
	
	/**
	 * Zwraca kopię drzewa DOM wiadomości
	 * @return DOMDocument Wiadomość
	 */
	function getParser() {
		if($this->parser === NULL) {
			$this->parser = new DOMDocument('1.0', 'utf-8');
			try {
				$this->parser->loadHTML('<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>'.$this->raw.'</body></html>');
			}
			catch(ErrorException $e) {
				if($e->getSeverity() != E_WARNING) {
					throw $e;
				}
			}
			
			foreach($this->parser->getElementsByTagName('a') as $node) {
				if(!$node->hasAttribute('href')) {
					$node->setAttribute('href', $node->textContent);
				}
			}
		}
		
		return $this->parser->cloneNode(TRUE);
	}
	
	private function parseTextDOM($dom) {
		if(!($dom instanceof DOMElement)) {
			throw new BotMsgException('Nieznany element DOM: '.get_class($dom));
		}
		
		$return = '';
		foreach($dom->childNodes as $node) {
			if($node instanceof DOMText || $node instanceof DOMEntity) {
				$return .= strtr($node->nodeValue, array("\n" => '', "\r" => ''));
			}
			elseif($node instanceof DOMElement) {
				switch(strtolower($node->tagName)) {
					case 'b':
					case 'strong':
						$return .= ($this->beautiful ? '*' : '').$this->parseTextDOM($node).($this->beautiful ? '*' : '');
					break;
					case 'u':
						$return .= ($this->beautiful ? '_' : '').$this->parseTextDOM($node).($this->beautiful ? '_' : '');
					break;
					case 'i':
						$return .= ($this->beautiful ? '/' : '').$this->parseTextDOM($node).($this->beautiful ? '/' : '');
					break;
					case 'br':
						$return .= "\n";
					break;
					case 'p':
						if(substr($return, -1) != "\n") {
							$return .= "\n\n";
						}
						$return .= $this->parseTextDOM($node)."\n\n";
					break;
					case 'h1':
						if(substr($return, -1) != "\n") {
							$return .= "\n\n";
						}
						$return .= ($this->beautiful ? '= ' : '').$this->parseTextDOM($node).($this->beautiful ? ' =' : '')."\n";
					break;
					case 'h2':
						if(substr($return, -1) != "\n") {
							$return .= "\n\n";
						}
						$return .= ($this->beautiful ? '== ' : '').$this->parseTextDOM($node).($this->beautiful ? ' ==' : '')."\n";
					break;
					case 'h3':
						if(substr($return, -1) != "\n") {
							$return .= "\n\n";
						}
						$return .= ($this->beautiful ? '=== ' : '').$this->parseTextDOM($node).($this->beautiful ? ' ===' : '')."\n";
					break;
					case 'td':
						$return .= $this->parseTextDOM($node)."\t";
					break;
					case 'th':
						$return .= ($this->beautiful ? '*' : '').$this->parseTextDOM($node).($this->beautiful ? '*' : '')."\t";
					break;
					case 'tr':
						$return .= $this->parseTextDOM($node)."\n";
					break;
					case 'a':
						$return .= $this->parseTextDOM($node);
						
						if($node->getAttribute('href') != $node->textContent) {
							$return .= ' ('.$node->getAttribute('href').')';
						}
					break;
					case 'script':
					case 'style':
					case 'img':
					break;
					default:
						$return .= $this->parseTextDOM($node);
					break;
				}
			}
			else
			{
				throw new BotMsgException('Nieznany element DOM: '.get_class($node));
			}
		}
		
		return trim($return);
	}
	
	private function parseHTMLDOM($dom) {
		if(!($dom instanceof DOMNode)) {
			throw new BotMsgException('Nieznany element DOM: '.get_class($dom));
		}
		
		foreach($dom->childNodes as $node) {
			if($node instanceof DOMElement) {
				if($node->hasAttribute('color')) {
					$color = trim($node->getAttribute('color'));
					$node->removeAttribute('color');
					if(substr($color, 0, 1)=='#' AND (strlen($color)==4 OR strlen($color)==7) AND ctype_xdigit(substr($color, 1))) {
						$node->setAttribute('style', 'color:'.$color.';'.$node->getAttribute('style'));
					}
				}
				
				$this->parseHTMLDOM($node);
			}
		}
	}
}
?>