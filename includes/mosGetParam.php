<?php

	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/
	
	/****************************************************\
	**   Original copyright (C) !Joomla				    **
	**   Homepage   : http://www.joomla.org		        **
	**   Released Under GNU GPL Public License          **
	\****************************************************/

/**
 * Utility function to return a value from a named array or a specified default
 * @param array A named array
 * @param string The key to search for
 * @param mixed The default value to give if no key found
 * @param int An options mask: _MOS_NOTRIM prevents trim, _MOS_ALLOWHTML allows safe html, _MOS_ALLOWRAW allows raw input
 */

define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
define( "_MOS_ALLOWRAW", 0x0004 );
function mosGetParam( &$arr, $name, $def=null, $mask=0 ) {
	static $noHtmlFilter 	= null;
	static $safeHtmlFilter 	= null;

	$return = null;
	if (isset( $arr[$name] )) {
		$return = $arr[$name];
		
		if (is_string( $return )) {
			// trim data
			if (!($mask&_MOS_NOTRIM)) {
				$return = trim( $return );
			}
			
			if ($mask&_MOS_ALLOWRAW) {
				// do nothing
			} else if ($mask&_MOS_ALLOWHTML) {
				// do nothing - compatibility mode
			} else {
				// send to inputfilter
				if (is_null( $noHtmlFilter )) {
					$noHtmlFilter = new InputFilter( /* $tags, $attr, $tag_method, $attr_method, $xss_auto */ );
				}
				$return = $noHtmlFilter->process( $return );
				
				if (is_numeric($def)) {
				// if default value is numeric set variable type to integer
					$return = intval($return);
				}				
			}
			
			// account for magic quotes setting
			if (!get_magic_quotes_gpc()) {
				$return = addslashes( $return );
			}
		}

		return $return;
	} else {
		return $def;
	}
}


?>
