<?php
class pogoda implements module {
	static $wojewodztwa = array(
		'Kuiavia-Pomerania' => 'kujawsko-pomorskie',
		'Kuyavian-Pomeranian' => 'kujawsko-pomorskie',
		'Kujawsko-Pomorskie' => 'kujawsko-pomorskie',
		
		'Lesser Poland' => 'małopolskie',
		
		'Lodz' => 'łódzkie',
		'Łódź' => 'łódzkie',
		
		'Lower Silesia' => 'dolnośląskie',
		'Lower Silesian' => 'dolnośląskie',
		
		'Lublin' => 'lubelskie',
		'Lubelskie' => 'lubelskie',
		
		'Lubuskie' => 'lubuskie',
		
		'Mazovia' => 'mazowieckie',
		'Masovian' => 'mazowieckie',
		'Mazowieckie' => 'mazowieckie',
		
		'Opole' => 'opolskie',
		
		'Subcarpathia' => 'podkarpackie',
		'Podkarpackie' => 'podkarpackie',
		
		'Podlachia' => 'podlaskie',
		
		'Pomerania' => 'pomorskie',
		'Pomorskie' => 'pomorskie',
		
		'Silesia' => 'śląskie',
		
		'Swietokrzyskie' => 'świętokrzyskie',
		
		'Warmia and Masuria' => 'warmińsko-mazurskie',
		
		'Western Pomerania' => 'zachodniopomorskie',
		
		'Greater Poland' => 'wielkopolskie',
		'Wielkopolskie' => 'wielkopolskie',
	);
	
	static function register_cmd() {
		return array(
			'pogoda' => 'cmd_pogoda',
			'p' => 'cmd_pogoda',
			'weather' => 'cmd_pogoda',
			'miasto' => 'cmd_miasto',
			'm' => 'cmd_miasto',
			'temp' => 'cmd_pogoda',
			'temperatura' => 'cmd_pogoda',
		);
	}
	
	static function help($cmd=NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('pogoda ', TRUE);
			GGapi::putRichText('[miasto]', FALSE, TRUE);
			GGapi::putRichText("\n".'   Podaje pogodę dla miasta'."\n");
			
			GGapi::putRichText('miasto ', TRUE);
			GGapi::putRichText('miasto', FALSE, TRUE);
			GGapi::putRichText("\n".'   Ustala domyślne miasto dla funkcji pogoda'."\n\n");
		}
		elseif(substr($cmd, 0, 1)=='m')
		{
			GGapi::putRichText('miasto ', TRUE);
			GGapi::putRichText('miasto', FALSE, TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('m', TRUE);
			GGapi::putRichText(')'."\n".'   Ustawia domyślne ');
			GGapi::putRichText('miasto', FALSE, TRUE);
			GGapi::putRichText(' dla funkcji pogoda dla danego numeru Gadu-Gadu.');
		}
		else
		{
			GGapi::putRichText('pogoda ', TRUE);
			GGapi::putRichText('miasto', FALSE, TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('p', TRUE);
			GGapi::putRichText(')'."\n".'   Podaje pogodę dla danego miasta na najbliższe dni. Domyślne miasto można ustawić komendą ');
			GGapi::putRichText('miasto', TRUE);
		}
	}
	
	static function putIcon($icon) {
		if(!empty($icon)) {
			if(!file_exists('./data/pogoda/'.basename($icon))) {
				if(substr($icon, 0, 1) == '/') {
					$icon = 'http://www.google.com'.$icon;
				}
				$img = @file_get_contents($icon);
				if($img) {
					file_put_contents('./data/pogoda/'.basename($icon), $img);
				}
			}
			
			GGapi::putImage('./data/pogoda/'.basename($icon));
			GGapi::putText("\n");
		}

	}
	
	static function cmd_pogoda($name, $arg) {
		if(empty($arg)) {
			$arg = database::get($_GET['from'], 'pogoda', 'miasto');
			if(empty($arg)) {
				$arg = GGapi::getPublicData();
				$arg = trim($arg['city']);
				if(empty($arg)) {
					$arg = 'Warszawa';
					$forced = TRUE;
				}
				GGapi::putText('Nie ustawiono miasta (pomoc - wpisz: help miasto) - '.(!$forced ? 'na podstawie danych z katalogu publicznego ' : '').'wybieram miasto '.$arg."\n\n");
			}
		}
		
		$dane = @file_get_contents('http://www.google.pl/ig/api?weather='.urlencode(ucwords(funcs::utfToAscii($arg))));
		if(!$dane) {
			GGapi::putText('Przepraszamy, nie udało się połączyć z serwisem');
			return;
		}
		
		$dane = iconv('iso-8859-2', 'utf-8', $dane);
		
		$dane = @simplexml_load_string($dane);
		if(!$dane) {
			GGapi::putText('Przepraszamy, błąd przy pobieraniu danych');
			return;
		}
		
		if($dane->weather->problem_cause) {
			GGapi::putText('Problem w serwisie bądź danego miasta nie ma w bazie'."\n\n".'Przykład:'."\n".'pogoda Warszawa'."\n".'pogoda Kraków');
			return;
		}
		
		$short2day = array(
			'pon.' => 'Poniedziałek',
			'wt.' => 'Wtorek',
			'śr.' => 'Środa',
			'czw.' => 'Czwartek',
			'pt.' => 'Piątek',
			'sob.' => 'Sobota',
			'niedz.' => 'Niedziela',
		);
		
		$region = substr(strstr($dane->weather->forecast_information->city['data'], ', '), 2);
		$region = trim(str_replace('Voivodeship', '', $region));
		if(isset(self::$wojewodztwa[$region])) {
			$region = 'województwo '.self::$wojewodztwa[$region];
		}
		
		$miasto = trim((string)$dane->weather->forecast_information->postal_code['data']);
		if(($a=strpos($miasto, '-'))!==FALSE) {
			$miasto = substr($miasto, 0, $a).'-'.ucfirst(substr($miasto, $a+1));
		}
		
		GGapi::putRichText('Pogoda dla miasta '.$miasto.', '.$region."\n\n", TRUE);
		
		GGapi::putRichText('Teraz'."\n", TRUE);
		self::putIcon((string)$dane->weather->current_conditions->icon['data']);
		
		$condition = (string)$dane->weather->current_conditions->condition['data'];
		GGapi::putRichText($txt.($condition ? $condition."\n" : '').'Temp.: '.($dane->weather->current_conditions->temp_c['data']).'°C'."\n".($dane->weather->current_conditions->humidity['data'])."\n".($dane->weather->current_conditions->wind_condition['data']));
		
		$num = TRUE;
		foreach($dane->weather->forecast_conditions as $day) {
			GGapi::putRichText("\n\n".($num ? 'Później' : $short2day[(string)$day->day_of_week['data']])."\n", TRUE);
			self::putIcon((string)$day->icon['data']);
			GGapi::putRichText(($day->condition['data'])."\n".'Temp. od '.($day->low['data']).'°C do '.($day->high['data']).'°C');
			$num = FALSE;
		}
		
	}
	
	static function cmd_miasto($name, $arg) {
		$arg = trim(funcs::utfToAscii($arg));
		if(empty($arg)) {
			$arg = database::get($_GET['from'], 'pogoda', 'miasto');
			if(!empty($arg)) {
				GGapi::putText('Aktualnie ustawione miasto to: '.$arg);
				return;
			}
			
			$arg = GGapi::getPublicData();
			$arg = funcs::utfToAscii($arg['city']);
			
			if(empty($arg)) {
				GGapi::putText('Nie podano wymaganego argumentu ');
				GGapi::putRichText('miasto', FALSE, TRUE);
				return;
			}
			
			
			GGapi::putText('Z katalogu publicznego pobrano miasto '.$arg."\n\n");
		}
		
		$data = @file_get_contents('http://ws.geonames.org/search?name='.urlencode($arg));
		if($data) {
			$data = simplexml_load_string($data);
			if($data && $data->totalResultsCount > 0) {
				$data = $data->geoname[0];
			}
			else
			{
				GGapi::putText('Podane miasto nie zostało odnalezione!');
				return;
			}
		}
		else
		{
			GGapi::putText('Wystąpił błąd przy wyszukiwaniu miasta. Spróbuj ponownie później.');
			return;
		}
		
		if(!$data->geonameId || $data->geonameId=='756135') {
			$data = new SimpleXMLElement('<geoname><name>Warszawa</name><lat>52.25</lat><lng>21.0</lng><geonameId>756135</geonameId><countryCode>PL</countryCode><countryName>Poland</countryName></geoname>');
		}
		
		GGapi::putText('Miasto zostało ustawione na '.(string)$data->name);
		database::add($_GET['from'], 'pogoda', 'miasto', (string)$data->name);
		database::add($_GET['from'], 'pogoda', 'kraj', (string)$data->countryName);
		database::add($_GET['from'], 'pogoda', 'cc', (string)$data->countryCode);
		database::add($_GET['from'], 'pogoda', 'geo', array('lat' => (string)$data->lat, 'lon' => (string)$data->lng));
	}
}
?>
