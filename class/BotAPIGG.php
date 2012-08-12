<?php
class BotAPIGGHTTPException extends Exception {
	private $httpcode;
	private $content;
	
	function __construct($msg, $httpcode, $content) {
		$this->httpcode = $httpcode;
		$this->content = $content;
		parent::__construct($msg.' Błąd '.$httpcode);
	}
	
	function __get($name) {
		return $this->$name;
	}
}
class BotAPIGGXMLException extends Exception {
	private $content;
	
	function __construct($msg, $content) {
		$this->content = $content;
		parent::__construct($msg);
	}
	
	function __get($name) {
		return $this->$name;
	}
}

class BotAPIGGReplyException extends Exception {
	private $xml;
	
	function __construct($msg, SimpleXMLElement $xml) {
		$this->xml = $xml;
		parent::__construct($msg);
	}
	
	function __get($name) {
		return $this->$name;
	}
	
	function __toString() {
		return $this->getMessage().' Błąd '.((string)$this->xml->status).': '.((string)$this->xml->errorMsg);
	}
}

class BotAPIGG extends config {
	private static $token;
	
	const STATUS_DOSTEPNY = 2;
	const STATUS_DOSTEPNY_DESC = 4;
	const STATUS_ONLINE = 2;
	const STATUS_ONLINE_DESC = 4;
	
	const STATUS_ZAJETY = 3;
	const STATUS_ZAJETY_DESC = 5;
	const STATUS_AWAY = 3;
	const STATUS_AWAY_DESC = 5;
	
	const STATUS_NIE_PRZESZKADZAC = 33;
	const STATUS_NIE_PRZESZKADZAC_DESC = 34;
	const STATUS_DND = 33;
	const STATUS_DND_DESC = 34;
	
	const STATUS_POROZMAWIAJ = 23;
	const STATUS_POROZMAWIAJ_DESC = 24;
	const STATUS_CHAT = 23;
	const STATUS_CHAT_DESC = 24;
	
	const STATUS_NIEWIDOCZNY = 20;
	const STATUS_NIEWIDOCZNY_DESC = 22;
	const STATUS_INVISIBLE = 20;
	const STATUS_INVISIBLE_DESC = 22;
	
	private function httpQuery($address, $curlopts = array(), $useToken = TRUE, $parseXML = TRUE) {
		if(!is_array($curlopts)) {
			$curlopts = array();
		}
		
		if($useToken) {
			if(!isset($curlopts[CURLOPT_HTTPHEADER]) || !is_array($curlopts[CURLOPT_HTTPHEADER])) {
				$curlopts[CURLOPT_HTTPHEADER] = array();
			}
			
			$token = $this->getToken();
			
			$curlopts[CURLOPT_HTTPHEADER][] = 'Token: '.$token['token'];
		}
		
		$dane = curl_init($address);
		$curlopts[CURLOPT_RETURNTRANSFER] = TRUE;
		$curlopts[CURLOPT_USERAGENT] = 'Bot Gadu-Gadu/'.main::VERSION.' (http://jacekk.info/botgg)';
		$curlopts[CURLOPT_SSL_CIPHER_LIST] = 'HIGH:-MD5:-aNULL:-DES';
		$curlopts[CURLOPT_SSL_VERIFYPEER] = TRUE;
		$curlopts[CURLOPT_SSL_VERIFYHOST] = 2;
		$curlopts[CURLOPT_CAPATH] = BOT_TOPDIR.'/data/ca-certificates/';
		curl_setopt_array($dane, $curlopts);
		$tok2 = $tok = curl_exec($dane);
		$info = curl_getinfo($dane);
		
		if($parseXML) {
			try {
				libxml_use_internal_errors(TRUE);
				$tok = new SimpleXMLElement($tok);
			}
			catch(Exception $e) {
				throw new BotAPIGGXMLException('Otrzymano błędny XML od botmastera.', $tok2);
			}
			
			if(!$tok) {
				if($info['http_code'] != 200) {
					throw new BotAPIGGHTTPException('Nie udało się wykonać zapytania HTTP.', $info['http_code'], $tok2);
				}
				else
				{
					throw new BotAPIGGXMLException('Otrzymano błędny XML od botmastera.', $tok2);
				}
			}
		}
		else
		{
			if($info['http_code'] != 200) {
				throw new BotAPIGGHTTPException('Nie udało się wykonać zapytania HTTP.', $info['http_code'], $tok2);
			}
		}
		
		return $tok;
	}
	
	function getToken($force = FALSE) {
		if($force || self::$token === NULL) {
			$auth = $this->APIs['Gadu-Gadu'];
			
			$tok = $this->httpQuery('https://botapi.gadu-gadu.pl/botmaster/getToken/'.$auth['numer'],  array(
				CURLOPT_USERPWD => $auth['login'].':'.$auth['haslo'],
				CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			), FALSE);
			
			if($tok->errorMsg) {
				throw new BotAPIGGReplyException('Pobieranie tokena nie powiodło się.', $tok);
			}
			
			self::$token = array('token' => (string)$tok->token, 'host' => (string)$tok->server, 'port' => (int)$tok->port);
		}
		
		return self::$token;
	}
	
	function setStatus($status, $desc = '') {
		$auth = $this->APIs['Gadu-Gadu'];
		$token = $this->getToken();
		
		$tok = $this->httpQuery('https://'.$token['host'].'/setStatus/'.$auth['numer'], array(
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => http_build_query(array(
				'status' => $status,
				'desc' => $desc,
			), '', '&'),
		));
		
		if( (string)$tok->status != '0') {
			throw new BotAPIGGReplyException('Ustawianie statusu nie powiodło się.', $tok);
		}
	}
	
	function setUrl($url) {
		$auth = $this->APIs['Gadu-Gadu'];
		
		$tok = $this->httpQuery('https://botapi.gadu-gadu.pl/botmaster/setUrl/'.$auth['numer'], array(
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $url,
		));
		
		if( (string)$tok->status != '0') {
			throw new BotAPIGGReplyException('Ustawianie adresu URL bota nie powiodło się.', $tok);
		}
		
		return $tok;
	}
	
	function getImage($hash) {
		$auth = $this->APIs['Gadu-Gadu'];
		$token = $this->getToken();
		
		$tok = $this->httpQuery('https://'.$token['host'].'/botmaster/setUrl/'.$auth['numer'], array(
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => http_build_query(array('hash' => $hash), '', '&'),
		), TRUE, FALSE);
		
		return $tok;
	}
	
	function existsImage($hash) {
		$auth = $this->APIs['Gadu-Gadu'];
		$token = $this->getToken();
		
		$tok = $this->httpQuery('https://'.$token['host'].'/botmaster/setUrl/'.$auth['numer'], array(
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => http_build_query(array('hash' => $hash), '', '&'),
		));
		
		if( (string)$tok->status != '0') {
			return FALSE;
		}
		
		return TRUE;
	}
	
	function putImage($path) {
		$fp = fopen($path, 'r');
		if(!$fp) {
			return FALSE;
		}
		
		$auth = $this->APIs['Gadu-Gadu'];
		$token = $this->getToken();
		
		$tok = $this->httpQuery('https://'.$token['host'].'/botmaster/setUrl/'.$auth['numer'], array(
			CURLOPT_HTTPHEADER => array('Content-Type: image/x-any'),
			CURLOPT_POST => TRUE,
			CURLOPT_INFILE => $fp,
		));
		
		if( (string)$tok->status != '0') {
			throw new BotAPIGGReplyException('Przesyłanie obrazka do botmastera nie powiodło się.', $tok);
		}
		
		return (string)$tok->hash;
	}
	
	/**
	 * Wysyła wiadomość do podanych użytkowników
	 * @param array $toURL Lista adresatów wiadomości w postaci: array('Gadu-Gadu://NUMER@gadu-gadu.pl', ...)
	 * @param BotMsg $msg Wiadomość do wysłania
	 * @param array $params Parametry przekazywane funkcji. Aktualnie dostępne:
	 * array( 'SendToOffline' => (bool)TRUE/FALSE )
	 */
	function sendMessage($toURL, BotMsg $msg, $params = array()) {
		$to = array();
		foreach($toURL as $url) {
			$url = parse_url($url);
			if($url['scheme'] != 'Gadu-Gadu') {
				continue;
			}
			
			if($url['user'] == '' || !ctype_digit($url['user'])) {
				throw new Exception('Nieznany użytkownik sieci Gadu-Gadu, któremu należy dostarczyć wiadomość.');
			}
			
			$to[] = $url['user'];
		}
		
		if(empty($to)) {
			return NULL;
		}
		
		$msg = new BotMsgGG($msg);
		
		$auth = $this->APIs['Gadu-Gadu'];
		$token = $this->getToken();
		
		$headers = array('Content-Type: application/x-www-form-urlencoded');
		
		if($params['SendToOffline'] == FALSE) {
			$headers[] = 'Send-to-offline: 0';
		}
		
		while(!empty($to)) {
			$to_part = implode(',', array_splice($to, -5000));
			
			$tok = $this->httpQuery('https://'.$token['host'].'/sendMessage/'.$auth['numer'], array(
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => array(
					'to' => $to_part,
					'msg' => $msg->getGG(FALSE),
				),
			));
			
			if((string)$tok->status == '18') {
				$tok = $this->httpQuery('https://'.$token['host'].'/sendMessage/'.$auth['numer'], FALSE, array(
					CURLOPT_HTTPHEADER => $headers,
					CURLOPT_POST => TRUE,
					CURLOPT_POSTFIELDS => array(
						'to' => $to_part,
						'msg' => $msg->getGG(TRUE),
					),
				));
			}
			
			if((string)$tok->status != '0') {
				throw new BotAPIGGReplyException('Problemy przy wysyłaniu wiadomości do sieci Gadu-Gadu.', $tok);
			}
		}
		
		return TRUE;
	}
}
?>