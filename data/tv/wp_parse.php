<?php
class wp_parse {
	var $document, $xpath, $context;
	var $name = '';
	var $program = array();
	var $months = array(
			'stycznia' => 1,
			'lutego' => 2,
			'marca' => 3,
			'kwietnia' => 4,
			'maja' => 5,
			'czerwca' => 6,
			'lipca' => 7,
			'sierpnia' => 8,
			'września' => 9,
			'października' => 10,
			'listopada' => 11,
			'grudnia' => 12,
		);
	var $weekdays = array(
			'poniedziałek' => 'next Monday',
			'wtorek' => 'next Tuesday',
			'środa' => 'next Wednesday',
			'czwartek' => 'next Thursday',
			'piątek' => 'next Friday',
			'sobota' => 'next Saturday',
			'niedziela' => 'next Sunday',
		);
	
	function __construct(DOMDocument $document) {
		$this->document = $document;
		$this->xpath = new DOMXPath($this->document);
		
		$context = $this->xpath->query('//div[@class="ramowka"]');
		if($context->length != 1) {
			throw new Exception('Nie znaleziono ramówki!');
		}
		$this->context = $context->item(0);
		
		$name = $this->xpath->query('.//*[@class="sh2"]//span//text()', $this->context);
		if($name->length != 1) {
			throw new Exception('Nie znaleziono nazwy stacji, błędny HTML.');
		}
		$this->name = $name->item(0)->nodeValue;
	}
	
	function parse_date($date) {
		if($date == 'dzisiaj') {
			// 'dzisiaj'
			return mktime(0, 0, 0);
		}
		elseif(isset($this->weekdays[$date])) {
			// data przyszła: 'poniedziałek'
			return strtotime($this->weekdays[$date]);
		}
		else
		{
			// data przeszła: 'pon. 18 czerwca'
			$date = explode(' ', $date);
			if(!isset($this->months[$date[2]])) {
				throw new Exception('Nie udało się przetworzyć daty ('.$date[2].')');
			}
			$timestamp = mktime(0, 0, 0, $this->months[$date[2]], $date[1]);
			
			// Należy przesunąć się o rok
			if($timestamp > time()) {
				$timestamp = strtotime('-1 year', $timestamp);
			}
			
			return $timestamp;
		}
	}
	
	function xmltv($id, $fp) {
		$program = array();
		
		$days_dom = $this->xpath->query('.//ul[@class="lsDay"]//li', $this->context);
		$days = array();
		foreach($days_dom as $day) {
			$days[] = $this->parse_date($day->nodeValue);
			$program[] = array();
		}
		unset($days_dom, $day);
		
		$hours_dom = $this->xpath->query('.//div[@class="hrsOut"]//div[@class="hour"]', $this->context);
		// Kolejne wiersze (pełne godziny)
		foreach($hours_dom as $in => $hour) {
			$days_dom = $this->xpath->query('.//div[@class="col"]', $hour);
			// Zbiory programów w tych godzinach dla kolejnych dni
			foreach($days_dom as $num => $day) {
				$programs_dom = $this->xpath->query('.//div[@class="prog"]', $day);
				// Kolejne programy w danej godzinie i dniu
				foreach($programs_dom as $n => $programs) {
					$godzina = $this->xpath->query('.//div[@class="tm"]', $programs)->item(0)->textContent;
					$nazwa = $this->xpath->query('.//h3', $programs)->item(0)->textContent;
					$opis = $this->xpath->query('.//p', $programs)->item(0)->textContent;
					
					$program[$num][] = array($godzina, $nazwa, $opis);
				}
				unset($programs_dom, $programs);
			}
			unset($days_dom, $day);
		}
		unset($hours_dom, $hour, $godzina, $nazwa, $opis);
		
		fwrite($fp, "\t".'<channel id="'.$id.'">'."\n"
			."\t\t".'<display-name>'.htmlspecialchars($this->name).'</display-name>'."\n"
			."\t".'</channel>'."\n");
		
		$last_timestamp = $timestamp = $days[0];
		$last_prog = NULL;
		foreach($program as $day => $dayprog) {
			foreach($dayprog as $prog) {
				$timestamp = strtotime($prog[0], $last_timestamp);
				if($timestamp < $last_timestamp) {
					$timestamp = strtotime('+1 day', $timestamp);
				}
				while($timestamp < $days[$day]) {
					$timestamp = strtotime('+1 day', $timestamp);
				}

				if($program !== NULL) 
					fwrite($fp, "\t".'<programme channel="'.$id.'" start="'.date('YmdHis O', $last_timestamp).'"'
						.' stop="'.date('YmdHis O', $timestamp).'">'."\n"
						."\t\t".'<title>'.htmlspecialchars($last_prog[1]).'</title>'."\n"
						."\t\t".'<desc>'.htmlspecialchars($last_prog[2]).'</desc>'."\n"
						."\t".'</programme>'."\n");
				
				$last_prog = $prog;
				$last_timestamp = $timestamp;
			}
		}
	}
}
?>
