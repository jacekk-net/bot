<?php
class DOMHelper {
	static function ltrim($node) {
		while(($node->firstChild instanceof DOMElement) && $node->firstChild->tagName == 'br' && $node->lastChild->getAttribute('auto')=='1') {
			$node->removeChild($node->firstChild);
		}
		
		if($node->firstChild instanceof DOMElement) {
			self::ltrim($node->firstChid);
		}
	}
	
	static function rtrim($node) {
		while(($node->lastChild instanceof DOMElement) && $node->lastChild->tagName == 'br' && $node->lastChild->getAttribute('auto')=='1') {
			$node->removeChild($node->lastChild);
		}
		
		if($node->lastChild instanceof DOMElement) {
			self::rtrim($node->lastChild);
		}
	}
	
	static function trim($node) {
		self::ltrim($node);
		self::rtrim($node);
	}
	
	static function cloneNode($node, $saveto, $tag=NULL) {
		if($tag === NULL) {
			$tag = $node->tagName;
		}
		
		$saveto = $saveto->ownerDocument->createElement($tag);
		
		foreach($node->attributes as $attr) {
			if($attr->name == 'color' || $attr->name == 'style' || $attr->name == 'src') {
				$saveto->setAttributeNode($saveto->ownerDocument->importNode($attr, TRUE));
			}
		}
		
		return $saveto;
	}
	
	static function insertElement($tag, $node) {
		$tag = $node->ownerDocument->createElement($tag);
		$tag->setAttribute('auto', '1');
		$node->appendChild($tag);
	}
}
?>