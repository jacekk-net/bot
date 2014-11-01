<?php
/**
 * Klasa konwertuje wiadomość ({@link BotMsg}) do formatu specyficznego dla Gadu-Gadu
 */
class BotMsgGG implements BotMsgInterface {
	private $parser;
	private $html = '';
	private $old = '';
	private $format = '';
	
	private $images = array();
	
	private $f_handl = FALSE;
	private $f_old = '';
	private $f_type = 0x00;
	private $f_color = '';
	
	const FORMAT_BOLD =	0x01;
	const FORMAT_ITALIC =	0x02;
	const FORMAT_UNDERLINE =0x04;
	const FORMAT_COLOR =	0x08;
	const FORMAT_IMAGE =	0x80;
	
	/**
	 * @param BotMsg $msg Wiadomość do przekonwertowania
	 */
	function __construct(BotMsg $msg) {
		$parser = $msg->getParser();
		unset($msg);
		
		$this->parser = new DOMDocument('1.0', 'utf-8');
		$this->parser->loadHTML('<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body></body></html>');
		
		$this->rewrite( $parser->getElementsByTagName('body')->item(0), $this->parser->getElementsByTagName('body')->item(0) );
		unset($parser);
		
		$this->parse( $this->parser->getElementsByTagName('body')->item(0) );
		
		$this->html = strtr(
			(string)substr($this->parser->saveXML( $this->parser->getElementsByTagName('body')->item(0) ), 6, -7),
			array('/>' => '>') // Tak! GG nie lubi XML!
		);
	}
	
	/**
	 * Zwraca wiadomość zgodną z BotAPI Gadu-Gadu, którą można przekazać bezpośrednio do BotMastera
	 * @param NULL|bool $img Czy dołączać obrazki?
	 * @return string
	 */
	function getGG($image = NULL) {
		if($image === FALSE) {
			$image = '';
		}
		elseif($image === TRUE) {
			$last = array_pop($this->images);
			if(count($this->images) > 0) {
				$push = new BotAPIGG();
				foreach($this->images as $data) {
					$push->putImage($image[3]);
				}
			}
			
			$image = $last[2].file_get_contents($last[3]);
		}
		else
		{
			if(count($this->images) > 0) {
				$push = new BotAPIGG();
				foreach($this->images as $image) {
					if(!$push->existsImage($image[2])) {
						$push->putImage($image[3]);
					}
				}
			}
			
			$image = '';
		}
		
		$format = $this->getFormat();
		
		return pack('VVVV', strlen($this->html)+1, strlen($this->old)+1, strlen($image), strlen($format)).$this->html."\0".$this->old."\0".$image.$format;
	}
	
	/**
	 * Zwraca wiadomość zgodną z BotAPI Gadu-Gadu, którą można przekazać bezpośrednio do BotMastera
	 * @return string
	 */
	function __toString() {
		return $this->getGG();
	}
	
	/**
	 * Zwraca wiadomość w formacie HTML przekonwertowaną tak, by zawierała jedynie dozwolone tagi.
	 * @return string
	 */
	function getHTML() {
		return $this->html;
	}
	
	/**
	 * Zwraca wiadomość jako tekst
	 * @return string
	 */
	function getText() {
		return $this->old;
	}
	
	/**
	 * Zwraca formatowanie wiadomości tekstowej zgodne z BotAPI Gadu-Gadu
	 * @see BotMsgGG::getText()
	 * @return string
	 */
	function getFormat() {
		if($this->format == '') {
			return '';
		}
		else
		{
			return pack('Cv', 0x02, strlen($this->format)).$this->format;
		}
	}
	
	/**
	 * Wyślij wiadomość na standardowe wyjście w sposób właściwy dla BotAPI
	 */
	function sendPullResponse() {
		header('Content-Type: application/x-gadu-gadu; charset=utf-8');
		echo $this->getGG();
	}
	
	private function rewrite($dom, $saveto, $top = TRUE) {
		if(!($dom instanceof DOMElement)) {
			throw new BotMsgException('Nieznany element DOM: '.get_class($dom));
		}
		
		foreach($dom->childNodes as $node) {
			if($node instanceof DOMElement) {
				switch(strtolower($node->tagName)) {
					case 'b':
					case 'i':
					case 'u':
					case 'sup':
					case 'sub':
					case 'span':
						$tag = DOMHelper::cloneNode($node, $saveto);
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
					break;
					
					case 'strong':
						$tag = DOMHelper::cloneNode($node, $saveto, 'b');
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
					break;
					
					case 'script':
					case 'style':
					break;
					
					case 'p':
						DOMHelper::rtrim($saveto);
						DOMHelper::insertElement('br', $saveto);
						DOMHelper::insertElement('br', $saveto);
						
						$tag = DOMHelper::cloneNode($node, $saveto, 'span');
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
						
						
						DOMHelper::insertElement('br', $saveto);
						DOMHelper::insertElement('br', $saveto);
					break;
					case 'a':
						$tag = DOMHelper::cloneNode($node, $saveto, 'span');
						
						$this->rewrite($node, $tag, FALSE);
						if($node->getAttribute('href') != $node->textContent) {
							$tag->appendChild($tag->ownerDocument->createTextNode(' ('.$node->getAttribute('href').')'));
						}
						
						$saveto->appendChild($tag);
					break;
					
					case 'h1':
						DOMHelper::rtrim($saveto);
						DOMHelper::insertElement('br', $saveto);
						DOMHelper::insertElement('br', $saveto);
						
						$tag = DOMHelper::cloneNode($node, $saveto, 'b');
						$tag2 = $tag->ownerDocument->createElement('u');
						$tag->appendChild($tag2);
						
						$this->rewrite($node, $tag2, FALSE);
						
						$saveto->appendChild($tag);
						
						DOMHelper::insertElement('br', $saveto);
					break;
					case 'h2':
						DOMHelper::rtrim($saveto);
						DOMHelper::insertElement('br', $saveto);
						DOMHelper::insertElement('br', $saveto);
						
						$tag = DOMHelper::cloneNode($node, $saveto, 'b');
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
						
						
						DOMHelper::insertElement('br', $saveto);
					break;
					case 'h3':
						DOMHelper::rtrim($saveto);
						DOMHelper::insertElement('br', $saveto);
						DOMHelper::insertElement('br', $saveto);
						
						$tag = DOMHelper::cloneNode($node, $saveto, 'b');
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
						
						
						DOMHelper::insertElement('br', $saveto);
					break;
					
					case 'ul':
						DOMHelper::rtrim($saveto);
						DOMHelper::insertElement('br', $saveto);
						
						$tag = DOMHelper::cloneNode($node, $saveto, 'span');
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
					break;
					case 'ol':
						DOMHelper::rtrim($saveto);
						DOMHelper::insertElement('br', $saveto);
						
						if(!$node->hasAttribute('start') || !ctype_digit($node->getAttribute('start'))) {
							$node->setAttribute('start', 1);
						}
						
						$tag = DOMHelper::cloneNode($node, $saveto, 'span');
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
					break;
					case 'li':
						$tag = DOMHelper::cloneNode($node, $saveto, 'span');
						
						if(strtolower($dom->tagName) == 'ul') {
							$tag->appendChild($tag->ownerDocument->createTextNode('- '));
						}
						elseif(strtolower($dom->tagName) == 'ol') {
							$tag->appendChild($tag->ownerDocument->createTextNode($dom->getAttribute('start').'. '));
							
							$dom->setAttribute('start', $dom->getAttribute('start')+1);
						}
						
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
						
						$saveto->appendChild($saveto->ownerDocument->createElement('br'));
					break;
					
					case 'br':
					case 'img':
						$tag = DOMHelper::cloneNode($node, $saveto);
						$saveto->appendChild($tag);
					break;
					
					default:
						$tag = DOMHelper::cloneNode($node, $saveto, 'span');
						$this->rewrite($node, $tag, FALSE);
						$saveto->appendChild($tag);
					break;
				}
			}
			elseif($node instanceof DOMText) {
				$val = strtr($node->nodeValue, array("\n" => '', "\r" => ''));
				if($val) {
					$saveto->appendChild($saveto->ownerDocument->createTextNode($val));
				}
			}
			else
			{
				$saveto->appendChild($saveto->ownerDocument->importNode($node, TRUE));
			}
		}
		
		if($top) {
			DOMHelper::trim($saveto);
		}
		
		foreach($saveto->childNodes as $node) {
			if(($node instanceof DOMElement) && $node->tagName == 'span' && $node->attributes->length == 0) {
				while($node->hasChildNodes()) {
					$node->parentNode->insertBefore($node->firstChild, $node);
				}
				$node->parentNode->removeChild($node);
			}
		}
		
		foreach($saveto->childNodes as $node) {
			if(($node instanceof DOMElement) && $node->hasAttribute('auto')) {
				$node->removeAttribute('auto');
			}
		}
	}
	
	private function image($node) {
		if($node->hasAttribute('src')) {
			$src = $node->getAttribute('src');
			$node->removeAttribute('src');
			
			if(!is_file($src)) {
				return;
			}
			
			if(isset($this->images[$src])) {
				list($crc, $size, $name) = $this->images[$src];
			}
			else
			{
				$size = filesize($src);
				if($size<0 || $size>262144) {
					return;
				}
				
				$crc = hash_file('crc32b', $src);
				$name = sprintf('%08s%08x', $crc, $size);
				
				$this->images[$src] = array($crc, $size, $name, $src);
			}
			
			$node->setAttribute('name', $name);
			
			$this->format .= pack('vC', mb_strlen($this->old), self::FORMAT_IMAGE)
					.pack('CCVV', 0x09, 0x01, $size, hexdec($crc));
			$this->f_old = '';
		}
	}
	
	private function format(&$node) {
		$node->setAttribute('beforeFormatType', dechex($this->f_type));
		$node->setAttribute('beforeFormatColor', base64_encode($this->f_color));
		
		if($node->hasAttribute('color')) {
			$color = trim($node->getAttribute('color'));
			if(substr($color, 0, 1)=='#' AND (strlen($color)==4 OR strlen($color)==7) AND ctype_xdigit(substr($color, 1))) {
				$node->setAttribute('style', 'color:'.$color.';'.$node->getAttribute('style'));
				
				$R = $G = $B = 0;
				if(strlen($color)==4) {
					$R = hexdec(str_repeat(substr($color, 1, 1), 2));
					$G = hexdec(str_repeat(substr($color, 2, 1), 2));
					$B = hexdec(str_repeat(substr($color, 3, 1), 2));
				}
				else
				{
					$R = hexdec(substr($color, 1, 2));
					$G = hexdec(substr($color, 3, 2));
					$B = hexdec(substr($color, 5, 2));
				}
				
				$this->f_color = chr($R).chr($G).chr($B);
				$this->f_type |= self::FORMAT_COLOR;
			}
			$node->removeAttribute('color');
		}
		
		switch(strtolower($node->tagName)) {
			case 'b':
				$this->f_type |= self::FORMAT_BOLD;
			break;
			case 'i':
				$this->f_type |= self::FORMAT_ITALIC;
			break;
			case 'u':
				$this->f_type |= self::FORMAT_UNDERLINE;
			break;
		}
	}
	
	private function unformat($node) {
		$this->f_type = hexdec($node->getAttribute('beforeFormatType'));
		$node->removeAttribute('beforeFormatType');
		
		$this->f_color = base64_decode($node->getAttribute('beforeFormatColor'));
		$node->removeAttribute('beforeFormatColor');
		
		return TRUE;
	}
	
	private function cf() {
		$format = pack('C', $this->f_type).$this->f_color;
		
		if($this->f_old != $format) {
			$this->format .= pack('v', mb_strlen($this->old)).$format;
			$this->f_old = $format;
		}
	}
	
	private function parse($dom) {
		if(!($dom instanceof DOMElement)) {
			throw new BotMsgException('Nieznany element DOM: '.$dom);
		}
		
		foreach($dom->childNodes as $node) {
			if($node instanceof DOMText || $node instanceof DOMEntity) {
				$this->cf();
				$this->old .= $node->nodeValue;
			}
			elseif($node instanceof DOMElement) {
				if($node->tagName == 'br') {
					$this->old .= "\r\n";
					continue;
				}
				elseif($node->tagName == 'img') {
					$this->image($node);
					continue;
				}
				
				$this->format($node);
				$this->parse($node);
				$this->unformat($node);
			}
			else
			{
				throw new BotMsgException('Nieznany element DOM: '.$node);
			}
		}
	}
}
?>