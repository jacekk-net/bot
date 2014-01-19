<?php
class rss implements module {
	static function register_cmd() {
		return array(
			'r' => 'cmd_rss',
			'rss' => 'cmd_rss',
			'kanal' => 'cmd_rss',
			'kanaly' => 'cmd_rss',
			'news' => 'cmd_rss',
			'newsy' => 'cmd_rss',
			'wiad' => 'cmd_rss',
			'wiadomosc' => 'cmd_rss',
			'wiadomosci' => 'cmd_rss',
			
			'kanal' => 'cmd_set',
			'kanaly' => 'cmd_set',
			
			'rss2' => 'cmd_rssex',
			'exrss' => 'cmd_rssex',
			'rssex' => 'cmd_rssex',
		);
	}
	
	static function help($cmd=NULL) {
		if($cmd === NULL) {
			GGapi::putRichText('rss ', TRUE);
			GGapi::putRichText('[kanał]', FALSE, TRUE);
			GGapi::putRichText("\n".'   Wiadomości z podanego kanału'."\n");
			GGapi::putRichText('kanal ', TRUE);
			GGapi::putRichText('kanał', FALSE, TRUE);
			GGapi::putRichText("\n".'   Ustawia domyślny kanał dla komendy rss'."\n\n");
		}
		elseif($cmd == 'kanal' || $cmd == 'kanaly') {
			GGapi::putRichText('kanal ', TRUE);
			GGapi::putRichText('kanał', FALSE, TRUE);
			GGapi::putRichText("\n".'   Zapisuje domyślny ');
			GGapi::putRichText('kanał', FALSE, TRUE);
			GGapi::putRichText(' RSS dla użytkownika. Dostępne kanały: '."\n".'- '.implode("\n".'- ', self::channels()));
		}
		else
		{
			GGapi::putRichText('rss ', TRUE);
			GGapi::putRichText('[kanał]', FALSE, TRUE);
			GGapi::putRichText(' (alias: ');
			GGapi::putRichText('r, news, wiadomosci', TRUE);
			GGapi::putRichText(')'."\n".'   Podaje ostatnie wiadomości z kanału RSS ');
			GGapi::putRichText('kanał', FALSE, TRUE);
			GGapi::putRichText('. Dostępne kanały: '."\n".'- '.implode("\n".'- ', self::channels()));
		}
	}
	
	private static function channels() {
		$file = file('./data/rss/channels.list');
		$return = array();
		foreach($file as $chan) {
			$chan = trim($chan);
			if(empty($chan) || substr($chan, 0, 1)=='#') {
				continue;
			}
			
			$parts = preg_split('/\t[\40\t]*/', $chan, 4);
			for($i=0; $i<4; $i++) {
				$parts[$i] = trim($parts[$i]);
			}
			
			if($aliases) {
				$return[$parts[1]] = $parts[1];
				
				if($parts[2] == 'NULL') continue;
				
				$alias = explode(',', $parts[2]);
				foreach($alias as $val) {
					$return[trim($val)] = $parts[1];
				}
			}
			else
			{
				$return[] = $parts[1];
			}
		}
		
		return $return;
	}
	
	private static function channel($name, $verbose=FALSE) {
		$file = file('./data/rss/channels.list');
		
		foreach($file as $chan) {
			$chan = trim($chan);
			if(empty($chan) || substr($chan, 0, 1)=='#') {
				continue;
			}
			
			$parts = preg_split('/\t[\40\t]*/', $chan, 4);
			for($i=1; $i<3; $i++) {
				$parts[$i] = trim($parts[$i]);
			}
			
			if($parts[1] == $name) {
				if($verbose) {
					return $parts;
				}
				else
				{
					return $parts[1];
				}
			}
			elseif($parts[2] == 'NULL') {
				continue;
			}
			else
			{
				$alias = explode(',', $parts[2]);
				foreach($alias as $val) {
					if($val == $name) {
						if($verbose) {
							return $parts;
						}
						else
						{
							return $parts[1];
						}
					}
				}
			}
		}
		
		return FALSE;
	}
	
	static function p($text, $bash=FALSE) {
		$text = trim($text);
		$replace = array(
			"\n" => ' ',
			"\r" => ' ',
			'–' => '-',
			'&#8211;' => '-',
			'&#39;' => '"',
			'&#8222;' => '"',
			'&#8221;' => '"',
		);
		$text = strtr($text, $replace);
		$text = html_entity_decode($text);
		$replace = array(
			'<br />' => "\n",
			'<br/>' => "\n",
			'<br>' => "\n",
			'  ' => ' ',
		);
		$text = strtr($text, $replace);
		if(!$bash) {
			$text = strip_tags($text);
		}
		$text = str_replace(array('&copy;', '&#169;'), '(C)', $text);
		return $text;
	}
	
	static function cmd_rss($name, $arg) {
		$arg = self::channel(funcs::utfToAscii($arg));
		if(!$arg) {
			$arg = database::get($_GET['from'], 'rss', 'kanal');
			if(!$arg) {
				$arg = self::channel('DEFAULT');
			}
		}
		
		$rss = @simplexml_load_file('./data/rss/'.$arg.'.rss');
		if(!$rss) {
			GGapi::putText('Błąd przy przetwarzaniu kanału, przepraszamy.');
			return FALSE;
		}
		
		if($rss->entry) {
			GGapi::putRichText(self::p($rss->title), TRUE);
			
			foreach($rss->entry as $item) {
				GGapi::putRichText("\n\n".self::p($item->title), TRUE);
				GGapi::putRichText("\n".self::p($item->summary, ($arg=='bash'))."\n".self::p($item->link['href']));
			
				if(GGapi::getLength() > 1700) {
					return;
				}
			}
		}
		else
		{
			GGapi::putRichText(self::p($rss->channel->title), TRUE);
			if($rss->channel->copyright) {
				GGapi::putRichText("\n".self::p($rss->channel->copyright));
			}
			
			foreach($rss->channel->item as $item) {
				GGapi::putRichText("\n\n".self::p($item->title), TRUE);
				GGapi::putRichText("\n".self::p($item->description, ($arg=='bash'))."\n".self::p($item->link));
			
				if(GGapi::getLength() > 1700) {
					return;
				}
			}
		}
	}
	
	static function cmd_set($name, $arg) {
		$arg = self::channel(funcs::utfToAscii($arg), TRUE);
		if(!$arg) {
			GGapi::putText('Wybrany kanał nie istnieje! Dostępne kanały: '."\n".'- '.implode("\n".'- ', self::channels()));
		}
		else
		{
			database::add($_GET['from'], 'rss', 'kanal', $arg[1]);
			GGapi::putText('Kanał '.$arg[3].' został ustawiony jako domyślny. Teraz zamiast:'."\n".'rss '.$arg[1]."\n".'możesz wpisać samo'."\n".'rss');
		}
	}
	
	static function testurl($url) {
		$url = parse_url($url);
		$schemas = array('http', 'https', 'ftp');
		if(empty($url['scheme'])) {
			return array(-1, 'Podaj pełny adres do kanału RSS (z http://)!');
		}
		if(!in_array(strtolower($url['scheme']), $schemas)) {
			return array(-1, 'Niedozowolona metoda dostępu (dostępne: http, https, ftp)');
		}
		
		$hosts = gethostbynamel($url['host']);
		if(!is_array($hosts)) return array(-2, 'Podany host nie istnieje');
		foreach($hosts as $ip) {
			if(substr($ip, 0, 4)=='127.' || substr($ip, 0, 3)=='10.' || substr($ip, 0, 2)=='0.' || substr($ip, 0, 3) > 223) {
				return array(-2, 'Niedozwolony numer IP hosta');
			}
		}
		
		$res = @simplexml_load_file($url['scheme'].'://'.$url['user'].':'.$url['pass'].'@'.$url['host'].'/'.ltrim($url['path'], '/'));
		if(!$res) {
			return array(-3, 'Nie udało się załadować podanego kanału RSS');
		}
		
		return $res;
	}
	
	static function cmd_rssex($name, $arg) {
		if(!$arg) {
			$arg = database::get($_GET['from'], 'rssex', 'kanal');
			if(!$arg) {
				GGapi::putText('Podaj pełny adres kanału (z http://) lub ustaw domyślny funkcją ');
				GGapi::putRichText('kanal2', TRUE);
				GGapi::putRichText('!'."\n\n");
				GGapi::putRichText('Przykład:', FALSE, FALSE, TRUE);
				GGapi::putRichText("\n".'rss2 http://wiadomosci.onet.pl/2,kategoria.rss');
				return FALSE;
			}
		}
		
		$rss = self::testurl($arg);
		if(is_array($rss)) {
			GGapi::putText('Nie udało się pobrać wybranego kanału RSS. Błąd: '.$rss[1]);
			return FALSE;
		}
		elseif(!is_object($rss)) {
			GGapi::putText('Wystąpił nieznany błąd przy pobieraniu danych. Przepraszamy.');
		}
		
		GGapi::putRichText(self::p($rss->channel->title), TRUE);
		if($rss->channel->copyright) {
			GGapi::putRichText("\n".self::p($rss->channel->copyright));
		}
		
		foreach($rss->channel->item as $item) {
			GGapi::putRichText("\n\n".self::p($item->title), TRUE);
			GGapi::putRichText("\n".self::p($item->description, ($arg=='bash'))."\n".self::p($item->link));
		
			if(GGapi::getLength() > 1700) {
				return;
			}
		}
	}
	
	static function cmd_setex($name, $arg) {
		if(!$arg) {
			GGapi::putText('Podaj pełny adres kanału (z http://)!'."\n\n");
			GGapi::putRichText('Przykład:', FALSE, FALSE, TRUE);
			GGapi::putRichText("\n".'kanal2 http://wiadomosci.onet.pl/2,kategoria.rss');
		}
		else
		{
			$ret = self::testurl($arg);
			if(is_object($ret)) {
				database::add($_GET['from'], 'rssex', 'kanal', $arg[1]);
				GGapi::putText('Kanał '.$arg.' został ustawiony jako domyślny. Teraz zamiast:'."\n".'rss '.$arg."\n".'możesz wpisać samo'."\n".'rss');
			}
			elseif(is_array($ret)) {
				GGapi::putText('Nie udało się pobrać wybranego kanału RSS. Błąd: '.$ret[1]);
			}
			else
			{
				GGapi::putText('Wystąpił nieznany błąd przy pobieraniu danych. Przepraszamy.');
			}
		}
	}
}
?>