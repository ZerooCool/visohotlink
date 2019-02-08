<?php

	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/
	
	// include configuration file
	include('config/config.visohotlink.php');
	include( $mosConfig_absolute_path . '/includes/searchEngine.php');
	include( $mosConfig_absolute_path . '/includes/searchEngineMethod.php');
	include( $mosConfig_absolute_path . '/includes/mosGetParam.php' );
	include( $mosConfig_absolute_path . '/includes/phpInputFilter/class.inputfilter.php' );
	
	// connect to the database
	$db = mysql_connect($host, $login, $password);
	mysql_select_db($base,$db);
	
	$url_referer = mosGetParam( $_GET, 'ref', '' );
	$entry_page = mosGetParam( $_GET, 'page', '' );
	
	
	$own_url = false;
	if (($url_site!='') && ($url_referer!='')) {
		$pos_url = strpos($url_referer, $url_site);
		$count_own_url = substr_count( $url_referer , $url_site);
		if (($pos_url=='0') && ($count_own_url!='0')) $own_url = true;
	}
	
	// try to find the referer site
	$exploded_url_referer = explode('/', $url_referer);
	$site_referer = $exploded_url_referer[0].'//'.$exploded_url_referer[2] ;
	
	// build an array of sites to ignore
	$ignored_sites = explode ( ' ' , $ignored_sites );
	
	// initialize som vars
	$i = 0;
	$count_ignored_sites = count($ignored_sites);
	$count_engine = count($search_engine);
	$engine->is = 0;
	$referer_type = 'normal';
	
	// look if the referer is in the list of sites to ignore
	while ( ($ignore == 0) && ($i < $count_ignored_sites)) {
		if (!empty($ignored_sites[$i])) {
			$ignore = substr_count( $url_referer , $ignored_sites[$i]);
		}
		$i++;
	}
	
	// if datas are not empty and the referer is not in the ignored lists
	if (($url_referer!='') && ($ignore==0) && (!$own_url)) {
		
		$i = 0;
		// look if the referer is a search engine
		while ( ($engine->is == 0) && ($i < $count_engine)) {
			$engine->is = substr_count( $url_referer , 'http://'.$search_engine[$i]->url.'/');
			if ($engine->is==0) $engine->is = substr_count( $url_referer , 'http://www.'.$search_engine[$i]->url.'/');
			$engine->name = $search_engine[$i]->name;
			$engine->family = $search_engine[$i]->family;
			$i++;
		}
		
		// if search engine, find the keyword
		if ($engine->is != 0 ) {
			$resultEngine = searchEngineMethod($engine, $url_referer);
			$url_referer = $resultEngine->url_referer;
			$site_referer = $resultEngine->site_referer;
			$referer_type = 'engine';
		}
		
		// look if the visit already exists
		$query_visit = "SELECT a.id"
						. "\n FROM ".$prefix_table."visohotlink_site AS b"
						. "\n INNER JOIN ".$prefix_table."visohotlink_referer AS c"
						. "\n ON b.id=c.id_site"
						. "\n INNER JOIN ".$prefix_table."visohotlink_visits AS a"
						. "\n ON c.id=a.id_referer"
						. "\n WHERE a.entry_page='$entry_page'"
						. "\n AND c.referer='$url_referer'"
						. "\n AND b.url='$site_referer'";
		
		$result_visit = mysql_query($query_visit) or die('Erreur SQL !<br>'.$query_visit.'<br>'.mysql_error());
		$visit_exists = mysql_num_rows($result_visit);	
		
		// if referer doesn't exist 
		if ($visit_exists == 0) {
			// Find if the site already exists
			$query_site = "SELECT id, answer_type, link_type, redirect_url"
						. "\n FROM ".$prefix_table."visohotlink_site"
						. "\n WHERE url='$site_referer'";
			$result_site = mysql_query($query_site) or die('Erreur SQL !<br>'.$query_site.'<br>'.mysql_error());
			$site_exists = mysql_num_rows($result_site);
			
			if ($site_exists=='0') {
				$query_new_site="INSERT INTO ".$prefix_table."visohotlink_site VALUES ('', '$referer_type', '$site_referer', '$link_type', '$answer_type', '', '$url_replace_jpeg', '$url_replace_audio_mpeg', '$url_replace_video_mpeg', '$url_replace_gif', '$url_replace_png', '$url_replace_avi', '$url_replace_mov', '$redirect_url', NOW(), '', '')";
				$result_new_site = mysql_query($query_new_site) or die('Erreur SQL !<br>'.$query_new_site.'<br>'.mysql_error());
				
				// Catch id
				$query_site_id = "SELECT id"
								. "\n FROM ".$prefix_table."visohotlink_site"
								. "\n WHERE url='$site_referer'";
				$result_site_id = mysql_query($query_site_id) or die('Erreur SQL !<br>'.$query_site_id.'<br>'.mysql_error());
				$array_site_id = mysql_fetch_array($result_site_id);
				$id_site = $array_site_id[0]; 								
			} else {
				$array_site = mysql_fetch_array($result_site);
				$id_site = $array_site[0];
				if ($array_site[1] != 'default') $answer_type = $array_site[1];
				if ($array_site[2] != 'default') $link_type = $array_site[2];
				if ($array_site[3] != '') $redirect_url = $array_site[3];
			}		
			
			// Find if the referer already exists
			$query_referer = "SELECT id"
							. "\n FROM ".$prefix_table."visohotlink_referer"
							. "\n WHERE id_site='$id_site'"
							. "\n AND referer='$url_referer'";
			$result_referer = mysql_query($query_referer) or die('Erreur SQL !<br>'.$query_referer.'<br>'.mysql_error());
			$referer_exists = mysql_num_rows($result_referer);
			
			if ($referer_exists=='0') {
				$query_new_referer="INSERT INTO ".$prefix_table."visohotlink_referer VALUES ('', '$id_site', '$url_referer', NOW())";
				$result_new_referer = mysql_query($query_new_referer) or die('Erreur SQL !<br>'.$query_new_referer.'<br>'.mysql_error());
				
				// Catch id
				$query_referer_id = "SELECT id"
							. "\n FROM ".$prefix_table."visohotlink_referer"
							. "\n WHERE id_site='$id_site'"
							. "\n AND referer='$url_referer'";
				$result_referer_id = mysql_query($query_referer_id) or die('Erreur SQL !<br>'.$query_referer_id.'<br>'.mysql_error());
				$array_referer_id = mysql_fetch_array($result_referer_id);
				$id_referer = $array_referer_id[0]; 								
			} else {
				$array_referer = mysql_fetch_array($result_referer);
				$id_referer = $array_referer[0]; 
			}		
			
			// insert a new visit
			$query_new_visit="INSERT INTO ".$prefix_table."visohotlink_visits VALUES ('', '$id_referer', '$entry_page', '1', NOW())";
			mysql_query($query_new_visit) or die('Erreur SQL !<br>'.$query_new_visit.'<br>'.mysql_error());
		} else {
			$array_visit = mysql_fetch_array($result_visit);
			$id_visit = $array_visit[0];
			$query_inc = "UPDATE ".$prefix_table."visohotlink_visits"
						. "\n SET visits = visits + 1"
						. "\n WHERE id='$id_visit'";
			mysql_query($query_inc) or die('Erreur SQL !<br>'.$query_inc.'<br>'.mysql_error());
		}
	}
	
	mysql_close();
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	
	if ($show_logo=='No') {
		header('Content-type: image/gif');
		readfile ( $mosConfig_absolute_path . '/images/pixel.gif');
	} else {
		header('Content-type: image/gif');
		readfile ( $mosConfig_absolute_path . '/images/logo_visohotlink_80x15.gif');
	}
?>
