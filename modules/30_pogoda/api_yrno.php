<?php
class api_yrno_parse {
	protected $xml;
	protected $dane;
	
	public static $symbols = array(
		1 => 'Słonecznie',
		2 => 'Lekkie zachmurzenie',
		3 => 'Częściowe zachmurzenie',
		4 => 'Zachmurzenie',
		5 => 'Lekki deszcz z przejaśnieniami',
		6 => 'Lekki deszcz i burze',
		7 => 'Lekki deszcz ze śniegiem',
		8 => 'Śnieg',
		9 => 'Lekki deszcz',
		10 => 'Deszcz',
		11 => 'Burze z deszczem',
		12 => 'Deszcz ze śniegiem',
		13 => 'Śnieg',
		14 => 'Burze ze śniegiem',
		15 => 'Mgły',
		16 => 1,
		17 => 2,
		18 => 5,
		19 => 8,
		20 => 'Deszcz ze śniegiem, burze, możliwe przejaśnienia',
		21 => 'Burze ze śniegiem, możliwe przejaśnienia',
		22 => 'Lekki deszcz i burze',
		23 => 'Deszcz ze śniegiem, burze'
	);
	
	public static $wind = array(
		'N' => 'północny',
		'NW' => 'północno-zachodni',
		'W' => 'zachodni',
		'SW' => 'południowo-zachodni',
		'S' => 'południowy',
		'SE' => 'południowo-wschodni',
		'E' => 'wschodni',
		'NE' => 'północno-wschodni',
	);
	
	public static function wind($dir) {
		if(isset(self::$wind[$dir])) {
			return self::$wind[$dir];
		}
		else
		{
			return '';
		}
	}
	
	public function __construct($xml) {
		libxml_use_internal_errors();
		$this->xml = simplexml_load_string($xml);
		libxml_clear_errors();
		
		if(!$this->xml) {
			throw new Exception('Niepoprawny format danych meteorologicznych!');
		}
	}
	
	public function mktime($time) {
		return strtotime(substr($time, 0, -1));
	}
	
	public function parseForecast() {
		$this->dane = array(
			'0h' => array(),
			'3h' => array(),
			'6h' => array(),
		);
		
		foreach($this->xml->product->time as $time) {
			$to = $this->mktime((string)$time->attributes()->to);
			$from = $this->mktime((string)$time->attributes()->from);
			
			$time = $time->location;
			
			if($to == $from) {
				$this->dane['0h'][$to] = array(
					'temp' => (string)$time->temperature->attributes()->value,
					'wind_speed' => (string)$time->windSpeed->attributes()->mps,
					'wind_dir' => (string)$time->windDirection->attributes()->name,
					'humidity' => (string)$time->humidity->attributes()->value,
					'pressure' => (string)$time->pressure->attributes()->value,
				);
			}
			elseif($to-$from > 0) {
				if($to-$from > 14400) {
					$put = '6h';
				}
				else
				{
					$put = '3h';
				}
				
				$icon = (int)$time->symbol->attributes()->number;
				if(is_int(self::$symbols[$icon])) {
					$icon = self::$symbols[$icon];
				}
				
				$this->dane[$put][$to] = array(
					'from' => $from,
					'to' => $to,
					'icon' => $icon
				);
			}
		}
	}
	
	public function getCurrentIcon() {
		$now = time();
		foreach($this->dane['3h'] as $value) {
			if($value['from'] <= $now && $now < $value['to']) {
				return $value['icon'];
			}
		}
		
		return NULL;
	}
	
	public function getCurrentWeather() {
		$dist = PHP_INT_MAX;
		$current = NULL;
		foreach($this->dane['0h'] as $time => $value) {
			if(abs($time - time()) < $dist) {
				$dist = abs($time - time());
				$current = $value;
			}
			else
			{
				break;
			}
		}
		
		return $current;
	}
	
	public function getDaypartWeather($timestamp) {
		$start = strtotime('6:00', $timestamp);
		$dayend = strtotime('19:30', $timestamp);
		$end = $start + 22*3600;
		
		$wind = $temp = array(
			'day' => array(),
			'night' => array(),
		);
		
		foreach($this->dane['0h'] as $time => $value) {
			$part = NULL;
			if($start <= $time && $time < $dayend) {
				$part = 'day';
			}
			elseif($dayend < $time && $time <= $end) {
				$part = 'night';
			}
			elseif($end <= $time) {
				break;
			}
			
			if($part !== NULL) {
				if(!isset($temp[$part]['from']) || $value['temp'] < $temp[$part]['from']) {
					$temp[$part]['from'] = $value['temp'];
				}
				if(!isset($temp[$part]['to']) || $value['temp'] > $temp[$part]['to']) {
					$temp[$part]['to'] = $value['temp'];
				}
				
				if(!isset($wind[$part]['from']) || $value['wind_speed'] < $wind[$part]['from']) {
					$wind[$part]['from'] = $value['wind_speed'];
				}
				if(!isset($wind[$part]['to']) || $value['wind_speed'] > $wind[$part]['to']) {
					$wind[$part]['to'] = $value['wind_speed'];
				}
			}
		}
		
		if($temp['day'] == array() || $wind['day'] == array()) {
			unset($temp['day']);
			unset($wind['day']);
		}
		
		return array('temp' => $temp, 'wind' => $wind);
	}
	
	public function getDaypartIcon($timestamp) {
		$start = strtotime('6:00', $timestamp);
		$end = strtotime('24:00', $timestamp);
		
		$return = array();
		foreach($this->dane['3h'] as $time => $value) {
			if($start <= $value['from'] && $value['to'] <= $end) {
				$return[] = $value['icon'];
			}
			elseif($end <= $value['from']) {
				break;
			}
		}
		
		return $return;
	}
}

function yrno_weather($lat, $lon) {
	$down = new DownloadHelper('http://api.yr.no/weatherapi/locationforecastlts/1.2/?lat='.urlencode($lat).';lon='.urlencode($lon));
	$down->setopt(CURLOPT_USERAGENT, 'BotGG/'.main::VERSION_NUM.' WeatherModule/1.0 (http://bot.jacekk.net/weather.html)');
	try {
		$data = $down->exec();
		$data = new api_yrno_parse($data);
		$data->parseForecast();
	}
	catch(Exception $e) {
		$down->cacheFor(600);
		return FALSE;
	}
	
	$down->cacheFor(7200);
	return $data;
}
?>