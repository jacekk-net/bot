<?php
require_once(dirname(__FILE__).'/api_geonames.php');
require_once(dirname(__FILE__).'/api_yrno.php');

class bot_pogoda_module implements BotModule {
	function pogoda($msg, $params) {
		$arg = trim($msg->args);
		
		$out = new BotMsg();
		$loc = FALSE;
		
		if(empty($arg)) {
			$msg->session->setClass('pogoda');
			
			if(isset($msg->session->miasto) && !isset($msg->session->geo)) {
				if(strlen($msg->session->miasto) > 0) {
					$out->a('<p>Wymagane przekonwertowanie danych... Wywoływanie komendy <i>miasto '.htmlspecialchars($msg->session->miasto).'</i>...</p>');
					
					$out->a($this->miasto($msg, $msg->session->miasto));
					$out->a('<p></p>');
				}
				else
				{
					unset($msg->session->miasto);
				}
			}
			
			if(!isset($msg->session->miasto)) {
				try {
					$api = new BotAPIGG();
					$data = $api->getPublicData($msg->user);
					if(is_array($data) && isset($data['city'])) {
						$arg = trim($data['city']);
					}
					unset($data, $api);
				}
				catch(Exception $e) {
				}
				
				if(empty($arg)) {
					$arg = 'Warszawa';
					$forced = TRUE;
				}
				
				$out->a('<p>Nie ustawiono miasta (pomoc - wpisz: help miasto) - '.(!$forced ? 'na podstawie danych z katalogu publicznego ' : '').'wybieram miasto '.$arg.'</p>'."\n\n");
			}
			else
			{
				$loc = array(
					'name' => $msg->session->miasto,
					'countryName' => $msg->session->kraj,
					'coutryCode' => $msg->session->cc,
					'lat' => $msg->session->geo['lat'],
					'lng' => $msg->session->geo['lon']
				);
			}
		}
		
		if($loc === FALSE) {
			$loc = new api_geonames();
			$loc = $loc->search($arg);
			
			if($loc === FALSE) {
				return new BotMsg('Nie udało się pobrać danych o podanym miejscu - spróbuj ponownie za około 10 minut.');
			}
			elseif($loc === NULL) {
				return new BotMsg('Dla podanego miejsca nie udało się uzyskać współrzędnych geograficznych - spróbuj wpisać inną nazwę.');
			}
		}
		
		$api = yrno_weather($loc['lat'], $loc['lng']);
		if($api == FALSE) {
			return new BotMsg('Nie udało się pobrać danych o pogodzie - spróbuj ponownie za około 10 minut.');
		}
		
		$out->a('<p>Pogoda dla '.htmlspecialchars($loc['name']).', '.htmlspecialchars($loc['countryName']).'.</p>'."\n\n");
		
		$icon = (int)$api->getCurrentIcon();
		$weather = $api->getCurrentWeather();
		
		$out->a('<p><b>Teraz</b><br />'."\n"
			. '<img src="./data/pogoda/'.$icon.'.png" /><br />'."\n"
			. api_yrno_parse::$symbols[$icon].'<br />'."\n"
			. 'Temp.: '.htmlspecialchars($weather['temp']).'°C<br />'."\n"
			. 'Wiatr: '.htmlspecialchars($weather['wind_speed']).' km/h, '.api_yrno_parse::wind($weather['wind_dir']).'<br />'."\n"
			. 'Ciśnienie: '.htmlspecialchars($weather['pressure']).' hPa</p>'."\n\n");
		
		$when = time();
		if($when < strtotime('19:00')) {
			$out->a($this->getHTMLforWeather('Dziś', $api->getDaypartIcon($when), $api->getDaypartWeather($when)));
		}
		
		$when = strtotime('+1 day', $when);
		$out->a($this->getHTMLforWeather('Jutro', $api->getDaypartIcon($when), $api->getDaypartWeather($when)));
		$when = strtotime('+1 day', $when);
		$out->a($this->getHTMLforWeather('Pojutrze', $api->getDaypartIcon($when), $api->getDaypartWeather($when)));
		
		$out->a('<p>Dane lokalizacyjne pochodzą z serwisu GeoNames.<br />'."\n"
			. 'Dane pogodowe pochodzą z Norweskiego Instytutu Meteorologicznego.</p>');
		
		return $out;
	}
	
	function getHTMLforRange($data) {
		return htmlspecialchars($data['from'].($data['from'] != $data['to'] ? '-'.$data['to'] : ''));
	}
	
	function getHTMLforWeather($name, $icons, $weather) {
		$html = '<p><b>'.$name.'</b><br />'."\n";
		$desc = array();
		$curr = 0;
		foreach($icons as $icon) {
			$icon = (int)$icon;
			if(is_file('./data/pogoda/'.$icon.'.png')) {
				$html .= '<img src="./data/pogoda/'.$icon.'.png" alt="" /> ';
				if($icon != $curr) {
					$desc[] = api_yrno_parse::$symbols[$icon];
					$curr = $icon;
				}
			}
		}
		$html .= '<br />'."\n"
			. implode(' / ', $desc).'<br />'."\n"
			. 'Temp.: '.$this->getHTMLforRange($weather['temp']['day']).'°C (w nocy: '.$this->getHTMLforRange($weather['temp']['night']).'°C)<br />'."\n"
			. 'Wiatr: '.$this->getHTMLforRange($weather['wind']['day']).' km/h (w nocy: '.$this->getHTMLforRange($weather['wind']['night']).' km/h)</p>'."\n\n";
		
		return $html;
	}
	
	function miasto($msg, $params) {
		$msg->session->setClass('pogoda');
		
		if(strlen($params) > 0) {
			$arg = trim($params);
		}
		else
		{
			$arg = trim($msg->args);
		}
		
		if(empty($arg)) {
			if(isset($this->session->miasto)) {
				return new BotMsg('Aktualnie ustawione miejsce to: '.htmlspecialchars($this->session->miasto).', '.htmlspecialchars($this->session->countryName));
			}
			
			try {
				$api = new BotAPIGG();
				$dane = $api->getPublicData($msg->user);
				if(!isset($arg['city']) || empty($arg['city'])) {
					throw new Exception('Brak miasta w danych w katalogu publicznym.');
				}
				
				$arg = trim($arg['city']);
			}
			catch(Exception $e) {
				return new BotMsg('Nie podano wymaganego argumentu <i>miasto</i>.');
			}
			
			$out->a('<p>Na podstawie danych z katalogu publicznego wybieram miasto: '.htmlspecialchars($arg).'</p>'."\n\n");
		}
		else
		{
			$out = new BotMsg();
		}
		
		$api = new api_geonames();
		$dane = $api->search($arg);
		
		if($dane === FALSE) {
			return new BotMsg('Wystąpił błąd przy wyszukiwaniu miasta. Spróbuj ponownie później.');
		}
		elseif($dane === NULL) {
			return new BotMsg('Nie udało się zlokalizować podanego miejsca. Spróbuj wpisać inną nazwę.');
		}
		
		$msg->session->miasto = $dane['name'];
		$msg->session->kraj = $dane['countryName'];
		$msg->session->cc = $dane['countryCode'];
		$msg->session->geo = array('lat' => $dane['lat'], 'lon' => $dane['lng']);
		
		$out->a('<p>Ustawiono miejsce: '.htmlspecialchars($this->session->miasto).', '.htmlspecialchars($this->session->countryName).'</p>');
		
		return $out;
	}
}
?>