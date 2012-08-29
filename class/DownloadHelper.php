<?php
class DownloadHelper {
	protected $url;
	protected $curl;
	protected $headers = array();
	protected $return = NULL;
	
	protected $cacheDir = './cache/';
	protected $cacheFile;
	protected $cacheInfo = array();
	
	function build_url($components) {
		return $components['scheme'].'://'.(isset($components['user']) && !empty($components['user']) ? $components['user'].(isset($components['pass']) && !empty($components['pass']) ? ':'.$components['pass'] : '').'@' : '').$components['host'].($components['path'] ? $components['path'] : '/').($components['query'] ? '?'.$components['query'] : '');
	}
	
	function __construct($url) {
		$this->url = parse_url($url);
		if(!$this->url) {
			throw new Exception('Parametr przekazywany do DownloadHelper::__construct() musi być poprawnym adresem URL.');
		}
		
		if($this->url['scheme'] != 'http' && $this->url['scheme'] != 'https') {
			throw new Exception('Klasa DownloadHelper obsługuje tylko i wyłącznie protokoły HTTP i HTTPS.');
		}
		
		if(strlen($this->url['host']) == 0) {
			throw new Exception('URL przekazany klasie DownloadHelper jest nieprawidłowy - brak nazwy hosta.');
		}
		
		$url = $this->build_url($this->url);
		$this->cacheFile = $this->url['host'].'/'.$this->url['scheme'].'-'.sha1($url);
		
		$this->curl = curl_init($url);
	}
	
	function setopt($option, $value) {
		if($option == CURLOPT_HTTPHEADER) {
			if(is_string($value)) {
				$value = array($value);
			}
			
			if(!is_array($value)) {
				throw new Exception('Parametr przekazywany jako CURLOPT_HTTPHEADER musi być tablicą.');
			}
			
			$this->headers = array_merge($this->headers, $value);
		}
		
		curl_setopt($this->curl, $option, $value);
	}
	
	function setopt_array($options) {
		if(!is_array($options)) {
			throw new Exception('Parametr przekazywany do DownloadHelper::setopt_array() musi być tablicą.');
		}
		
		foreach($options as $option => $value) {
			$this->setopt($option, $value);
		}
	}
	
	function cacheDir($directory) {
		$this->cacheDir = $directory;
	}
	
	function exec() {
		if(!is_dir($this->cacheDir)) {
			mkdir($this->cacheDir);
		}
		
		if(!is_dir($this->cacheDir.$this->url['host'])) {
			mkdir($this->cacheDir.$this->url['host']);
		}
		
		// Sprawdź, czy są dane na temat pliku w cache...
		if(is_file($this->cacheDir.$this->cacheFile.'-info')) {
			$this->cacheInfo = unserialize(file_get_contents($this->cacheDir.$this->cacheFile.'-info'));
			if(!$this->cacheInfo) {
				$this->cacheInfo = array();
			}
		}
		else
		{
			if(is_file($this->cacheDir.$this->cacheFile)) {
				unlink($this->cacheDir.$this->cacheFile);
			}
		}
		
		// Czy można wykorzystać cache...
		if(isset($this->cacheInfo['cache']) && $this->cacheInfo['cache'] >= time()) {
			if(is_file($this->cacheDir.$this->cacheFile)) {
				return file_get_contents($this->cacheDir.$this->cacheFile);
			}
			else
			{
				return FALSE;
			}
		}
		
		// Nie można wykorzystać cache, sprawdź czy plik się zmienił...
		if(isset($this->cacheInfo['last_seen'])) {
			$this->headers[] = 'If-Modified-Since: '.date(DATE_RFC1123, $this->cacheInfo['last_seen']);
		}
		
		if(count($this->headers) > 0) {
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
		}
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
		
		$this->return = curl_exec($this->curl);
		
		$this->cacheInfo['last_updated'] = time();
		$info = $this->getinfo();
		
		if($info['http_code'] == '304') {
			// Plik się nie zmienił...
			$this->cacheInfo['last_seen'] = time();
			
			if(is_file($this->cacheDir.$this->cacheFile)) {
				$this->return = file_get_contents($this->cacheDir.$this->cacheFile);
			}
			else
			{
				$this->return = FALSE;
			}
		}
		
		return $this->return;
	}
	
	function cacheFor($seconds) {
		$this->cacheUntil(time() + $seconds);
	}
	
	function cacheUntil($timestamp) {
		if($timestamp >= time()) {
			// Można cache'ować
			$this->cacheInfo['cache'] = $timestamp;
		}
		else
		{
			if(isset($this->cacheInfo['cache'])) {
				unset($this->cacheInfo['cache']);
			}
		}
		
		file_put_contents($this->cacheDir.$this->cacheFile.'-info', serialize($this->cacheInfo));
		
		if($this->return === FALSE) {
			// Usuń stary plik z cache - zapytanie nie powiodło się
			if(is_file($this->cacheDir.$this->cacheFile)) {
				unlink($this->cacheDir.$this->cacheFile);
			}
		}
		elseif($this->return !== NULL) {
			// Umieść w cache nowy plik...
			$this->cacheInfo['last_seen'] = $this->cacheInfo['downloaded'] = time();
			file_put_contents($this->cacheDir.$this->cacheFile, $this->return);
		}
	}
	
	function invalidate() {
		if(is_file($this->cacheDir.$this->cacheFile)) {
			unlink($this->cacheDir.$this->cacheFile);
		}
		
		if(is_file($this->cacheDir.$this->cacheFile.'-info')) {
			unlink($this->cacheDir.$this->cacheFile.'-info');
		}
	}
	
	function getinfo() {
		return curl_getinfo($this->curl);
	}
	
	function __destruct() {
		curl_close($this->curl);
	}
}
?>