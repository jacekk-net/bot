<?php
function test($name, $expr) {
	echo '<tr> <td>'.$name.'</td> <td>';
	if($expr) {
		echo '<strong style="color:darkgreen">OK</strong><br />';
	}
	else
	{
		echo '<strong style="color:red">FAIL</strong><br />';
	}
	echo '</td> </tr>'."\n";
}

function testdir($dir) {
	test($dir, is_writable($dir));
}

echo '<table>
<tr> <th class="head" colspan="2">Interpreter PHP</th> </tr>
';
test('Wersja PHP >= 5.2', version_compare(PHP_VERSION, '5.2.0', '>='));
test('magic_quotes_gpc = Off', (get_magic_quotes_gpc() == 0));
test('allow_url_fopen = On', (ini_get('allow_url_fopen') == 1));
test('setlocale(pl_PL.UTF8)', setlocale(LC_CTYPE, 'pl_PL.UTF8', 'pl_PL', 'polish', 'plk'));

echo '<tr> <th class="head" colspan="2">Rozszerzenia PHP</th> </tr>
';
test('cURL', function_exists('curl_init'));
test('mbstring', function_exists('mb_strlen'));
test('iconv', function_exists('iconv'));
test('Ctype', function_exists('ctype_digit'));
test('DOM', class_exists('DOMDocument'));
test('SimpleXML', class_exists('SimpleXMLElement'));
test('PDO', class_exists('PDO'));
test('PDO SQLite', in_array('sqlite', PDO::getAvailableDrivers()));

echo '<tr> <th class="head" colspan="2">Prawa do zapisu w katalogach</th> </tr>
';
testdir('./cache');
testdir('./data');
testdir('./data/humor');
testdir('./data/humor/archiwum');
testdir('./data/kino/cache');
testdir('./data/kurs');
testdir('./data/kurs/archiwum');
testdir('./data/lotto');
testdir('./data/lotto/archiwum');
testdir('./data/pogoda');
testdir('./data/rss');
testdir('./data/tv');
testdir('./data/tv/cache');
testdir('./db');
if(is_dir('./database')) {
	testdir('./database');
}

echo '</table>';
?>
