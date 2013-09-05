<?php
/**
 * Klasa obsługująca polskie wyrażenia określające daty
 * @todo Potrzebna jest funkcja, które będzie wyciągać datę z początku lub końca danego ciągu. Patrz: {@link tv::parse_date()}
 */
class calendar {
	/**
	 * Na podstawie podanej daty zwraca uniksowy znacznik czasu
	 * @param string $date Data w formacie "naturalnym". Np. jutro, 1 stycznia, piątek
	 * @return int Uniksowy znacznik czasu - liczba sekund od północy 1 stycznia 1970
	 * @see http://www.php.net/date
	 */
	static function parse_date($date) {
		$date = funcs::utfToAscii($date);
		switch($date) {
			case 'jutro':
				return strtotime('tomorrow');
			case 'za tydzien':
				return strtotime('+1 week');
			case 'pojutrze':
			case 'po jutrze':
				return strtotime('tomorrow +1 day');
			case 'dzis':
				return mktime(0, 0, 0);
			case 'teraz':
			case '':
				return time();
			case 'wczoraj':
				return strtotime('yesterday');
			case 'przedwczoraj':
			case 'przed wczoraj':
				return strtotime('yesterday -1 days');
			case 'poniedzialek':
				return strtotime('monday');
			case 'wtorek':
				return strtotime('tuesday');
			case 'sroda':
				return strtotime('wednesday');
			case 'czwartek':
				return strtotime('thursday');
			case 'piatek':
				return strtotime('friday');
			case 'sobota':
				return strtotime('saturday');
			case 'niedziela':
				return strtotime('sunday');
		}
		
		if(substr($date, 0, 2)=='za') {
			$czego = array(
				'sekunde' => 'second',
				'sekund' => 'seconds',
				'sekundy' => 'seconds',
				'minuta' => 'minute',
				'minut' => 'minutes',
				'minuty' => 'minutes',
				'godzine' => 'hour',
				'godzin' => 'hours',
				'godziny' => 'hours',
				'dzien' => 'day',
				'dni' => 'days',
				'miesiac' => 'month',
				'miesiecy' => 'months',
				'miesiace' => 'months',
				'rok' => 'year',
				'lat' => 'years',
				'lata' => 'years',
			);
			$date = trim(substr($date, 2));
			
			$ile = array(
				'zero' => 0,
				'jeden' => 1,
				'dwa' => 2,
				'trzy' => 3,
				'cztery' => 4,
				'piec' => 5,
				'szesc' => 6,
				'siedem' => 7,
				'osiem' => 8,
				'dziewiec' => 9,
				'dziesiec' => 10,
			);
			foreach($ile as $key => $num) {
				if(substr($date, 0, strlen($key)) == $key) {
					$ile = $num;
					$done = TRUE;
					$date = trim(substr($date, strlen($key)));
					break;
				}
			}
			
			if($done) {
			}
			elseif(!is_numeric(substr($date, 0, 1))) {
				$ile = 1;
			}
			else
			{
				$ile = (int)$date;
				$date = trim(substr($date, strlen($ile)));
			}
			
			$czego = $czego[$date];
			if(!$czego) return FALSE;
			
			return strtotime('+'.$ile.' '.$czego);
		}
		
		if(preg_match('/([0-9]{1,2})\.([0-9]{1,2}).([0-9]{2,4})/', $date, $test)) {
			return mktime(0, 0, 0, $test[2], $test[1], $test[3]);
		}
		elseif(preg_match('/([0-9]{1,2})\.([0-9]{1,2})/', $date, $test)) {
			return mktime(0, 0, 0, $test[2], $test[1]);
		}
		elseif(preg_match('/([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{2,4})/', $date, $test)) {
			return mktime(0, 0, 0, $test[2], $test[1], $test[3]);
		}
		elseif(preg_match('/([0-9]{1,2})\s+([a-z]{3,13})\s+([0-9]{2,4})/', $date, $test) || preg_match('/([0-9]{1,2})\s+([a-z]{3,13})/', $date, $test)) {
			$array = array(
				'styczen' => 1,
				'stycznia' => 1,
				'luty' => 2,
				'lutego' => 2,
				'marzec' => 3,
				'marca' => 3,
				'kwiecien' => 4,
				'kwietnia' => 4,
				'maj' => 5,
				'maja' => 5,
				'czerwiec' => 6,
				'czerwca' => 6,
				'lipiec' => 7,
				'lipca' => 7,
				'sierpien' => 8,
				'sierpnia' => 8,
				'wrzesien' => 9,
				'wrzesnia' => 9,
				'pazdziernik' => 10,
				'pazdziernika' => 10,
				'listopad' => 11,
				'listopada' => 11,
				'grudzien' => 12,
				'grudnia' => 12,
			);
			if(!isset($array[$test[2]])) return FALSE;
			if(!$test[3]) $test[3] = date('Y');
			return mktime(0, 0, 0, $array[$test[2]], $test[1], $test[3]);
		}
		else
		{
			return strtotime($date);
		}
	}
}
?>