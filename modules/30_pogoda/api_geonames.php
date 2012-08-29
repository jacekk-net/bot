<?php
require_once(dirname(__FILE__).'/api_geonames_config.php');

class api_geonames extends api_geonames_config {
	function search($name) {
		$url = 'http://'.$this->host.'/search?name='.urlencode($name).'&lang=pl&maxrows=3'.($this->username !== NULL ? '&username='.urlencode($this->username) : '');
		
		try {
			$download = new DownloadHelper($url);
			$data = $download->exec();
			
			if(!$data) {
				$download->cacheFor(600);
				return FALSE;
			}
			
			libxml_use_internal_errors();
			$data = simplexml_load_string($data);
			libxml_clear_errors();
			
			if(!$data) {
				$download->cacheFor(600);
				return FALSE;
			}
			
			// Trzymaj w cache przez około 116 dni
			$download->cacheFor(10000000);
			
			if($data->geoname[0]->getName() != 'geoname'
				|| $data->geoname[0]->name->getName() != 'name'
				|| $data->geoname[0]->countryName->getName() != 'countryName'
				|| $data->geoname[0]->lat->getName() != 'lat'
				|| $data->geoname[0]->lng->getName() != 'lng') {
				return NULL;
			}
			
			$data = (array)$data->geoname[0];
			foreach($data as &$value) {
				$value = trim($value);
			}
			unset($value);
			
			if(isset($data['countryName']) && $data['countryName'] == 'Rzeczpospolita Polska') {
				$data['countryName'] = 'Polska';
			}
			
			return $data;
		}
		catch(Exception $e) {
			return FALSE;
		}
	}
}
?>