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
	
	require_once( $mosConfig_absolute_path . '/includes/mosGetParam.php' );
	require_once( $mosConfig_absolute_path . '/includes/phpInputFilter/class.inputfilter.php' );
	require_once( $mosConfig_absolute_path . '/includes/database.php' );
	require_once( $mosConfig_absolute_path . '/includes/visohotlinkLink.php' );
	require_once( $mosConfig_absolute_path . '/includes/visohotlinkSite.php' );
	require_once( $mosConfig_absolute_path . '/includes/pageNavigation.php' );
	require_once( $mosConfig_absolute_path . '/includes/mosCommonHtml.php' );
	require_once( $mosConfig_absolute_path . '/includes/mosMainFrame.php' );
	require_once( $mosConfig_absolute_path . '/includes/mosHtml.php' );

function mosRedirect( $url, $msg='' ) {
	global $mosConfig_live_site;
	
    // specific filters
	/* $iFilter = new InputFilter();
	$url = $iFilter->process( $url );
	if (!empty($msg)) {
		$msg = $iFilter->process( $msg );
	}

	if ($iFilter->badAttributeValue( array( 'href', $url ))) {
		$url = $mosConfig_live_site;
	} */

	if (trim( $msg )) {
	 	if (strpos( $url, '?' )) {
			$url .= '&mosmsg=' . urlencode( $msg );
		} else {
			$url .= '?mosmsg=' . urlencode( $msg );
		}
	}
	
	echo '<br>' . $url;
	
	if (headers_sent()) {
		echo "<script>document.location.href='$url';</script>\n";
	} else {
		//@ob_end_clean(); // clear output buffer
		header( 'HTTP/1.1 301 Moved Permanently' );
		header( "Location: ". $url );
	}
	exit();
}

/**
* Copy the named array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
*/
function mosBindArrayToObject( $array, &$obj, $ignore='', $prefix=NULL, $checkSlashes=true ) {
	if (!is_array( $array ) || !is_object( $obj )) {
		return (false);
	}

	foreach (get_object_vars($obj) as $k => $v) {
		if( substr( $k, 0, 1 ) != '_' ) {			// internal attributes of an object are ignored
			if (strpos( $ignore, $k) === false) {
				if ($prefix) {
					$ak = $prefix . $k;
				} else {
					$ak = $k;
				}
				if (isset($array[$ak])) {
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? mosStripslashes( $array[$ak] ) : $array[$ak];
				}
			}
		}
	}

	return true;
}

/**
 * Strip slashes from strings or arrays of strings
 * @param mixed The input string or array
 * @return mixed String or array stripped of slashes
 */
function mosStripslashes( &$value ) {
	$ret = '';
	if (is_string( $value )) {
		$ret = stripslashes( $value );
	} else {
		if (is_array( $value )) {
			$ret = array();
			foreach ($value as $key => $val) {
				$ret[$key] = mosStripslashes( $val );
			}
		} else {
			$ret = $value;
		}
	}
	return $ret;
}

?>