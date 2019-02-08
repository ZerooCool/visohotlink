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

class mosCommonHTML {

	/*
	* Loads all necessary files for JS Overlib tooltips
	*/
	function loadOverlib() {
		global  $mosConfig_live_site, $mainframe;

		if ( !$mainframe->get( 'loadOverlib' ) ) {
		// check if this function is already loaded
			?>
			<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
			<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_hideform_mini.js"></script>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
			<?php
			// change state so it isnt loaded a second time
			$mainframe->set( 'loadOverlib', true );
		}
	}

	function CheckedOutProcessing( &$row, $i ) {
		global $my;

		if ( $row->checked_out) {
			$checked = mosCommonHTML::checkedOut( $row );
		} else {
			$checked = mosHTML::idBox( $i, $row->id, ($row->checked_out && $row->checked_out != $my->id ) );
		}

		return $checked;
	}
}

?>
