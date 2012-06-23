<?php
class bot_lang_module implements BotModule {
	private $APPID = '';
	
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
			$data = file_get_contents('http://api.microsofttranslator.com/v2/Http.svc/Translate?appId='.urlencode($this->APPID).'&text='.urlencode($args).'&from='.$params[0].'&to='.$params[1]);
			
			if(!$data) {
				return new BotMsg('Błąd podczas pobierania danych ze słownika. Przepraszamy.');
			}
			
			return new BotMsg('<u>Tłumaczenie (by Microsoft Translator):</u><br />'."\n".strip_tags($data));
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
