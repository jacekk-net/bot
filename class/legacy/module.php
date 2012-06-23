<?php
interface module {
	static function register_cmd();
	// Returns:
	// array(
	//   'CMD1_NAME' => 'FUNCTION_INSIDE_CLASS',
	//   'CMD2_NAME' => 'FUNCTION_INSIDE_CLASS',
	//   ...
	// )
	
	static function help($cmd=NULL);
	// Return help content about command $cmd to GGapi::put*() functions
	//    if $cmd is NULL return help content for all commands
	
	// static function FUNCTION_INSIDE_CLASS(CMD_NAME, REST_OF_PLAINTEXT)
	//    REST_OF_PLAINTEXT is raw (non trimmed etc.) part after command name, without leading space
}
?>