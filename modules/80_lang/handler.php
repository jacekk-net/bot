<?php
require_once(dirname(__FILE__).'/msapi.php');

class bot_lang_module implements BotModule {
	function handle($msg, $params) {
		$args = trim($msg->args);
		
		if(empty($args)) {
			return BotMsg('Podaj tekst do przetłumaczenia!');
		}
		
		$url = 'http://translate.google.com/translate_a/t?client=t&text='.urlencode($args).'&sl='.$params[0].'&tl='.$params[1].'&hl=pl&ie=utf-8&oe=utf-8';
		$data = @file_get_contents($url, 0, stream_context_create(array(
			'http' => array(
				'method' => 'GET',
			),
		)));
		
		if(!$data) {
			return new BotMsg('Błąd podczas pobierania danych ze słownika. Przepraszamy.');
		}
		
		$data = jsarray::parse($data);
		
		if(!$data OR count($data)==0 OR count($data[1])==0) {
			$api = new msapi('https://api.datamarket.azure.com/Bing/MicrosoftTranslator/');
			$data = $api->execute(array(
				'From' => $params[0],
				'To' => $params[1],
				'Text' => $args,
				'$skip' => 0,
				'$top' => 1
			));
			
			if(!$data || !isset($data['d']['results'][0]['Text'])) {
				return new BotMsg('Błąd podczas pobierania danych z tłumacza. Przepraszamy.');
			}
			
			$data = $data['d']['results'][0]['Text'];
			
			return new BotMsg('<u>Tłumaczenie (by Microsoft Translator):</u><br />'."\n".htmlspecialchars($data));
		}
		else
		{
			$html = '<u>Słownik (by Google):</u>';
			foreach($data[1] as $przyp) {
				$html .= '<br />'."\n".'<b>'.htmlspecialchars($przyp[0]).'</b>';
				foreach($przyp[1] as $term) {
					$html .= '<br />'."\n".'- '.htmlspecialchars($term);
				}
			}
			
			return new BotMsg($html);
		}
	}
	
	function typo($msg, $params) {
		return new BotMsg('Wybrana komenda nie istnieje. Prawdopodobnie chodziło ci o jedną z komend językowych, których nazwy zapisywane są <b>bez</b> spacji pomiędzy spacji pomiędzy kodami języków (angpol, a nie: ang pol).<br /><br />'."\n\n"
		
		. '<u>Spróbuj:</u><br />'."\n"
		. $msg->command.ltrim($msg->args));
	}
}
?>
