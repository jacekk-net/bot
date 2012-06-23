<?php
// Skrypt aktualizujący dane pobierane okresowo.

define('MAINSTAR', ' '."\033".'[1;37m*'.' ');
define('NORMAL', "\033".'[0m');
define('STAR', '    '."\033".'[1;34m*'."\033".'[0m ');
define('OK', "\033[55G\033[32m".'[  OK  ]'."\033[0m\n");
define('NOT', "\033[55G\033[33m".'[  OK  ]'."\033[0m\n");
define('FAIL', "\033[55G\033[31m".'[ FAIL ]'."\033[0m\n");

echo "\033".'[1;37;44m   Bot GG - Skrypt aktualizujący ('.date('d.m.Y H:i:s').')'."\033".'[K'."\033".'[0m'."\n\n";

function crontab_field($field, $number) {
	$field = explode(',', $field);
	foreach($field as $one) {
		$mod = FALSE;
		$range_start = 0;
		$range_stop = 0;
		
		if(($pos=strpos($one, '/'))!==FALSE) {
			$mod = (int)substr($one, $pos+1);
			if($mod==0) {
				continue;
			}
			$one = substr($one, 0, $pos);
		}
		
		if($one != '*') {
			if(($pos=strpos($one, '-'))!==FALSE) {
				$range_start = (int)substr($one, 0, $pos);
				$range_stop = (int)substr($one, $pos+1);
			}
			else
			{
				$range_start = $one;
				$range_stop = $one;
			}
			
			if($range_start > $number OR $range_stop < $number) {
				continue;
			}
		}
		
		if($mod && ($number-$range_start)%$mod != 0) {
			continue;
		}
		
		return TRUE;
	}
	
	return FALSE;
}

function crontab_match($line) {
	$parts = preg_split('/[\40\t]+/', $line, 6);
	// Minutes part - skip
	
	// Hour part
	if(!crontab_field($parts[1], date('H'))) {
		return FALSE;
	}
	
	// Day part
	if(!crontab_field($parts[2], date('j'))) {
		return FALSE;
	}
	
	// Month part
	if(!crontab_field($parts[3], date('n'))) {
		return FALSE;
	}
	
	// Weekday part
	if(!crontab_field($parts[4], date('w'))) {
		return FALSE;
	}
	
	return $parts[5];
}

function launch($file) {
	return include($file);
}

function crontab_parse($dir) {
	chdir($dir);
	
	$done = FALSE;
	
	$file = file('crontab');
	foreach($file as $line) {
		$line = trim($line);
		if(empty($line) || substr($line, 0, 1)=='#') continue;
		
		$ret = crontab_match($line);
		if($ret) {
			if(!$done)
				echo "\n";
			launch($ret);
			$done = TRUE;
		}
	}
	
	if(!$done) {
		echo NOT;
	}
	
	chdir('..');
}

chdir(dirname(__FILE__));

$dirs = glob('./*', GLOB_ONLYDIR);
foreach($dirs as $dir) {
	if(file_exists($dir.'/crontab')) {
		echo MAINSTAR.'Moduł '.basename($dir).NORMAL;
		crontab_parse($dir);
	}
}

echo "\n";
?>