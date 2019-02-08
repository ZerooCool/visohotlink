<?php
	
	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/

class visohotlink_link extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $id_referer			= null;
	/** @var text */	
	var $uri				= null;
	/** @var varchar(255) */	
	var $host				= null;
	/** @var datetime */	
	var $date				= null;
	/** @var int */	
	var $size				= null;
	/** @var int */	
	var $downloaded			= null;
	/** @var varchar(8) */	
	var $link_type			= null;
	/** @var varchar(8) */	
	var $answer_type		= null;
	/** @var text */	
	var $text				= null;
	/** @var text */	
	var $replace_file		= null;
	/** @var varchar(255) */	
	var $mime_type			= null;
	/** @var varchar(255) */	
	var $redirect_url		= null;
	/** @var int */	
	var $checked_out		= null;
	/** @var datetime */	
	var $checked_out_time	= null;
	
	
	/**
	* @param database A database connector object
	*/
	function visohotlink_link( &$db ) {
		$this->mosDBTable( '#__visohotlink_link', 'id', $db );
	}
	
	function loadsite() {
		global $database;
		
		$query = "SELECT c.referer AS referer, b.url AS site_referer, b.referer_type AS referer_type"
			. "\n FROM #__visohotlink_link AS a"
			. "\n LEFT JOIN #__visohotlink_referer AS c"
			. "\n ON c.id=a.id_referer"
			. "\n LEFT JOIN #__visohotlink_site AS b"
			. "\n ON b.id=c.id_site"
			. "\n WHERE a.id='$this->id'";
		
		$database->setQuery( $query );
		$result = $database->loadObjectList();
		
		// If database error, stop here
		if ($database->getErrorNum()) {
			echo $database->stderr();
			return false;
		}
		
		$this->referer = $result[0]->referer;
		$this->site_referer = $result[0]->site_referer;
		$this->referer_type = $result[0]->referer_type;
		
		return;			
	}
}

?>
