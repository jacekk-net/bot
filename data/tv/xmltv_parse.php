<?php
class xmltv_parse {
	static $file;
	static $aliases;
	
	static function schedule($id, $datetime=NULL) {
		if($datetime === NULL) $datetime = time();
		
		$dane = simplexml_load_file(self::$file);
		$abc = $dane->xpath('programme[@channel=\''.$id.'\' and number(substring(@stop, 1, 12))>\''.date('YmdHi', $datetime).'\' and number(substring(@start, 1, 12))<\''.date('YmdHi', $datetime+(3600*24)).'\']');
		
		$last = 0;
		$concat = '';
		foreach($abc as $value) {
			$now = date('d.m.Y', strtotime(substr($value['start'], 0, -6)));
			if($now != $last) {
				if(!empty($concat)) GGapi::putRichText($concat);
				GGapi::putRichText("\n".$now."\n", TRUE);
				$last = $now;
				$concat = '';
			}
			$concat .= date('H:i', strtotime(substr($value['start'], 0, -6))).' '.$value->title."\n";
		}
		
		if(!empty($concat)) GGapi::putRichText($concat);
	}
	
	static function aliases($tv = NULL) {
		$tv = funcs::utfToAscii($tv);
		$dane = file(self::$aliases);
		
		$return = array();
		foreach($dane as $line) {
			$line = trim($line);
			if(empty($line) OR substr($line, 0, 1)=='#')
				continue;
			
			$line = explode("\t", $line);
			for($i=0; $i<count($line); $i++) {
				if($tv!==NULL AND funcs::utfToAscii($line[$i])==$tv) {
					return $line[0];
				}
				else
				{
					$return[$line[$i]] = $line[0];
				}
			}
		}
		
		if($tv!==NULL) {
			return FALSE;
		}
		else
		{
			return $return;
		}
	}
	
	static function channels() {
		$dane = file(self::$aliases);
		
		$return = array();
		foreach($dane as $nazwa) {
			$nazwa = trim($nazwa);
			if(empty($nazwa) OR substr($nazwa, 0, 1)=='#')
				continue;
			
			$return[] = strtok($nazwa, "\t\n");
		}
		
		return $return;
	}
}
?>