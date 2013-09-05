<?php
class jsarray {
	static function parse($array) {
		$data = '<?php '.$array.' ?>';
		$data = token_get_all($data);
		
		$stack = array( array() );
		$element = NULL;
		foreach($data as $token) {
			if(is_array($token)) {
				// Ignore  < ? php  and  ? >  added above
				if($token[0] == T_OPEN_TAG OR $token[0] == T_CLOSE_TAG) continue;
				// String/int element within an array
				if($token[0] == T_CONSTANT_ENCAPSED_STRING) {
					$element = substr($token[1], 1, -1);
				}
				if($token[0] == T_LNUMBER) {
					$element = $token[1];
				}
			}
			// Nested array
			elseif($token == '[') {
				array_push($stack, array());
			}
			// End of nested array
			elseif($token == ']') {
				// Put elements into the latest array
				if($element !== NULL && $element !== FALSE) {
					end($stack);
					$stack[key($stack)][] = $element;
					$element = NULL;
				}
				
				// Check - maybe there are no elements between ] and next ]
				$element = FALSE;
				
				$temp = array_pop($stack);
				end($stack);
				$stack[key($stack)][] = $temp;
				unset($temp);
			}
			// Elements separator
			elseif($token == ',') {
				// Put elements into the latest array (]] check)
				if($element !== FALSE) {
					end($stack);
					$stack[key($stack)][] = $element;
				}
				$element = NULL;
			}
			else
			{
				return FALSE;
			}
		}
		
		if(isset($stack[0][0])) {
			return $stack[0][0];
		} else {
			return NULL;
		}
	}
}
?>