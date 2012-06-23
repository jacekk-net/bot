<?php
/**
 * Klasa ładująca dla modułów napisanych dla poprzedniej wersji bota
 */
class bot_legacy_init implements BotModuleInit {
	const BOT_FUNCTIONS_FILE = '/cache/legacy.functions';
	const BOT_HELP_FILE = '/cache/legacy.help';
	
	/**
	 * Wypisuje listę komend obsługiwanych przez dany plik modułu
	 * @param string $file Nazwa pliku
	 * @return array
	 */
	function load_funcs($file) {
		$name = substr(basename($file), 3, -4);
		
		$ret = @include_once($file);
		if(!$ret) return array();
		
		$funcs = call_user_func(array($name, 'register_cmd'));
		
		$return = array();
		
		foreach($funcs as $n => $method) {
			if(!isset($return[$n])) {
				$return[$n] = array();
			}
			
			$return[$n][] = array(
				'file' => 'handler.php',
				'class' => 'bot_legacy_module',
				'method' => 'handle',
				'params' => array(basename($file), $name, $method)
			);
		}
		
		return $return;
	}
	
	/**
	 * Zwraca (z użyciem cache'a) listę obsługiwanych komend ze starych modułów
	 * @return array
	 */
	function register() {
		if(is_file(BOT_TOPDIR.self::BOT_FUNCTIONS_FILE)) {
			return unserialize(file_get_contents(BOT_TOPDIR.self::BOT_FUNCTIONS_FILE));
		}
		else
		{
			$return = array();
			$files = glob('./modules/*.php');
			
			foreach($files as $file) {
				$return = array_merge_recursive($return, $this->load_funcs($file));
			}
			
			file_put_contents(BOT_TOPDIR.self::BOT_FUNCTIONS_FILE, serialize($return));
			
			return $return;
		}
	}
	
	/**
	 * Ładuje skróconą listę poleceń i ich parametrów z pliku starego modułu
	 * @param string $file Nazwa pliku
	 */
	function load_help($file) {
		$name = substr(basename($file), 3, -4);
		
		$ret = @include_once($file);
		if(!$ret) return array();
		
		call_user_func(array($name, 'help'), NULL);
	}
	
	/**
	 * Zwraca (z użyciem cache'a) skróconą pomoc dla obsługiwanych poleceń
	 * @return BotMsg
	 */
	function cache_help() {
		if(is_file(BOT_TOPDIR.self::BOT_HELP_FILE)) {
			return unserialize(file_get_contents(BOT_TOPDIR.self::BOT_HELP_FILE));
		}
		else
		{
			$files = glob(BOT_TOPDIR.'/modules/*.php');
			
			foreach($files as $file) {
				$this->load_help($file);
			}
			
			$return = GGapi::getResponse();
			
			file_put_contents(BOT_TOPDIR.self::BOT_HELP_FILE, serialize($return));
			
			return $return;
		}
	}
	
	/**
	 * Zwraca pomoc dla określonej parametrem komendy
	 * @param null|string $params Nazwa komendy
	 * @return false|BotMsg Zwracana wiadomość
	 */
	function help($params = NULL) {
		if($params === NULL) {
			return $this->cache_help();
		}
		else
		{
			$data = $this->register();
			
			if(!$data[$params]) {
				return FALSE;
			}
			
			foreach($data[$params] as $module) {
				$ret = @include_once(BOT_TOPDIR.'/modules/'.$module['params'][0]);
				if(!$ret) continue;
				
				call_user_func(array($module['params'][1], 'help'), $params);
			}
			
			$data = GGapi::getResponse();
			if($data instanceof BotMsg) {
				return $data;
			}
			else
			{
				return FALSE;
			}
		}
	}
}

return 'bot_legacy_init';
?>