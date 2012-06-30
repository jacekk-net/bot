<?php
require_once(dirname(__FILE__).'/msapi_config.php');

class msapi extends msapi_config {
	public $url;
	
	function __construct($url) {
		$this->url = $url;
	}
	
	function execute($params) {
		if(!is_array($params)) {
			throw new Exception('Przekazany parametr nie jest tablicą');
		}
		
		foreach($params as $name => &$param) {
			if(substr($name, 0, 1)!='$' && is_string($param)) {
				$param = '\''.$param.'\'';
			}
		}
		unset($param);
		$params['$format'] = 'json';
		
		$context = stream_context_create(array(
			'http' => array(
				'request_fulluri' => TRUE,
				'header' => 'Authorization: Basic '.base64_encode(':'.$this->accountKey)
			),
		));
		
		$content = file_get_contents($this->url.'?'.http_build_query($params, '', '&'), FALSE, $context);
		if(!$content) {
			return FALSE;
		}
		
		$content = json_decode($content, TRUE);
		if(!$content) {
			return FALSE;
		}
		
		return $content;
	}
}
?>
