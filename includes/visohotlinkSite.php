<?php
	
	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/

class visohotlink_site extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var text */	
	var $url				= null;
	/** @var varchar(8) */	
	var $link_type			= null;
	/** @var varchar(8) */	
	var $answer_type		= null;
	/** @var text */	
	var $text				= null;
	/** @var text */	
	var $url_replace_jpeg	= null;
	/** @var text */	
	var $url_replace_audio_mpeg	= null;
	/** @var text */	
	var $url_replace_video_mpeg	= null;
	/** @var text */	
	var $url_replace_gif	= null;
	/** @var text */	
	var $url_replace_png	= null;
	/** @var text */	
	var $url_replace_avi	= null;
	/** @var text */	
	var $url_replace_mov	= null;
	/** @var varchar(255) */	
	var $redirect_url		= null;
	/** @var datetime */	
	var $date				= null;
	/** @var int */	
	var $checked_out		= null;
	/** @var datetime */	
	var $checked_out_time	= null;
	
	
	/**
	* @param database A database connector object
	*/
	function visohotlink_site( &$db ) {
		$this->mosDBTable( '#__visohotlink_site', 'id', $db );
	}
	
	function deleteSite() {
		global $database;
		
		$query = "SELECT id FROM #__visohotlink_referer"
				. "\n WHERE id_site='$this->id'"
				;
				
		$database->setQuery( $query );
		$referers = $database->loadObjectList();
		// If database error, stop here
		if ($database->getErrorNum()) {
			echo $database->stderr();
			return false;
		}
		
		for ($j=0, $m=count($referers); $j < $m; $j++) {
			$query = "DELETE FROM #__visohotlink_link"
					. "\n WHERE id_referer='$referers[$j]->id'";
			$database->setQuery( $query );
			$database->query();
			// If database error, stop here
			if ($database->getErrorNum()) {
				echo $database->stderr();
				return false;
			}
			
			$query = "DELETE FROM #__visohotlink_visits"
					. "\n WHERE id_referer='$referers[$j]->id'";
			$database->setQuery( $query );
			$database->query();
			// If database error, stop here
			if ($database->getErrorNum()) {
				echo $database->stderr();
				return false;
			}
					
		}
		$query = "DELETE FROM #__visohotlink_referer"
				. "\n WHERE id_site='$this->id'"
				;
		$database->setQuery( $query );
		$database->query();		
		
		// If database error, stop here
		if ($database->getErrorNum()) {
			echo $database->stderr();
			return false;
		}
		
		if (!$this->delete( $this->id )) {
			echo $this->getError();
			return false;
		}
		
		return true;
	}
}

?>

