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
	include_once('config/config.visohotlink.php');
	include( $mosConfig_absolute_path . '/includes/searchEngine.php');
	include( $mosConfig_absolute_path . '/includes/searchEngineMethod.php');
	include( $mosConfig_absolute_path . '/includes/mimeContentType.php');
	
	// connect to the database
	$db = mysql_connect($host, $login, $password);
	mysql_select_db($base,$db);
	
	$url_referer = $_SERVER['HTTP_REFERER'];
	$uri_file = $_SERVER['REQUEST_URI'];
	$url_host = $_SERVER['HTTP_HOST'];
	
	// catch the site root
	$trans = array($_SERVER['SCRIPT_NAME'] => "");
	$root_server = strtr( $_SERVER['SCRIPT_FILENAME'], $trans );

	// try to find the referer site
	$exploded_url_referer = explode('/', $url_referer);
	$site_referer = $exploded_url_referer[0].'//'.$exploded_url_referer[2] ;
	
	// build an array of sites to ignore
	$ignored_sites = explode ( ' ' , $ignored_sites );
	
	// build an array of files to ignore
	$ignored_files = explode ( ' ' , $ignored_files );
	
	// initialize som vars
	
	$referer_type = 'normal';
	$ignore = 0;
	$engine->is = 0;
	$i = 0;
	$count_ignored_sites = count($ignored_sites);
	$count_ignored_files = count($ignored_files);
	$count_engine = count($search_engine);
	
	// look if the referer is in the list of sites to ignore
	while ( ($ignore == 0) && ($i < $count_ignored_sites)) {
		if (!empty($ignored_sites[$i])) {
			$ignore = substr_count( $url_referer , $ignored_sites[$i]);
		}
		$i++;
	}
	
	$i = 0;
	// look if the referer is in the list of files to ignore
	while ( ($ignore == 0) && ($i < $count_ignored_files)) {
		if (!empty($ignored_files[$i])) {
			$ignore = substr_count( $uri_file , $ignored_files[$i]);
		}
		$i++;
	}
	
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
	
	$mime_type = mime_content_type($root_server . $uri_file);
	
	// try to catch the file size
	$filesize = filesize($root_server . $uri_file);
	
	// if datas are not empty and the referer is not in the ignored lists
	if (($url_referer!='') && ($url_host!='') && ($uri_file!='') && ($ignore==0)) {
		
		$query_hotlink = "SELECT a.answer_type, a.downloaded, a.replace_file, b.answer_type, b.url_replace_jpeg, b.url_replace_audio_mpeg, b.url_replace_video_mpeg, b.url_replace_gif, b.url_replace_png, b.url_replace_avi, b.url_replace_mov, a.redirect_url,b.redirect_url, a.id"
						. "\n FROM ".$prefix_table."visohotlink_site AS b"
						. "\n INNER JOIN ".$prefix_table."visohotlink_referer AS c"
						. "\n ON b.id=c.id_site"
						. "\n INNER JOIN ".$prefix_table."visohotlink_link AS a"
						. "\n ON c.id=a.id_referer"
						. "\n WHERE a.uri='$uri_file'"
						. "\n AND a.host='$url_host' "
						. "\n AND c.referer='$url_referer'"
						. "\n AND b.url='$site_referer'";
				
		$result_hotlink = mysql_query($query_hotlink) or die('Erreur SQL !<br>'.$query_hotlink.'<br>'.mysql_error());
		$hotlink_exists = mysql_num_rows($result_hotlink);
		
		
		// If the hotlink doesn't exist
		if ($hotlink_exists=='0') {
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
			
			// Insert a new hotlink
			$query_new_hotlink="INSERT INTO ".$prefix_table."visohotlink_link VALUES ('', '$id_referer', '$uri_file', '$url_host', NOW(), '$filesize', '1', '$link_type', '$answer_type', '', '', '$mime_type', '$redirect_url', '', '' )";
			mysql_query($query_new_hotlink) or die('Erreur SQL !<br>'.$query_new_hotlink.'<br>'.mysql_error());	
			
		} else {
			$array_hotlink = mysql_fetch_array($result_hotlink);
			if ($array_hotlink[0] != 'default') $answer_type = $array_hotlink[0];
			
			if ($referer_type != 'engine') {
				switch ($mime_type) {
					case 'image/jpeg':
						if ($array_hotlink[4] != '') $replace_file =$array_hotlink[4]; else $replace_file = $url_replace_jpeg;
						break;
					case 'audio/mpeg':
						if ($array_hotlink[5] != '') $replace_file =$array_hotlink[5]; else $replace_file = $url_replace_audio_mpeg;
						break;
					case 'video/mpeg':
						if ($array_hotlink[6] != '') $replace_file =$array_hotlink[6]; else $replace_file = $url_replace_video_mpeg;
						break;
					case 'image/gif':
						if ($array_hotlink[7] != '') $replace_file =$array_hotlink[7]; else $replace_file = $url_replace_gif;
						break;
					case 'image/png':
						if ($array_hotlink[8] != '') $replace_file =$array_hotlink[8]; else $replace_file = $url_replace_png;
						break;
					case 'video/x-msvideo':
						if ($array_hotlink[9] != '') $replace_file =$array_hotlink[9]; else $replace_file = $url_replace_avi;
						break;
					case 'video/quicktime':
						if ($array_hotlink[10] != '') $replace_file =$array_hotlink[10]; else $replace_file = $url_replace_mov;
						break;
				}
				
				if ($array_hotlink[11] != '') $redirect_url = $array_hotlink[11];
				if ($array_hotlink[12] != '') $redirect_url = $array_hotlink[12];
			}
						
			if ($array_hotlink[2] != '') $replace_file = $array_hotlink[2];
			
			$id_link = $array_hotlink[13];
			
			// and update 'download' field
			$query_inc = "UPDATE ".$prefix_table."visohotlink_link"
						. "\n SET downloaded = downloaded + 1"
						. "\n WHERE id='$id_link'";
			mysql_query($query_inc) or die('Erreur SQL !<br>'.$query_inc.'<br>'.mysql_error());
			
			//Check for e-mail alert
			if (($activ_alert=='Yes') && ($array_hotlink[1]+1==$alert_threshold)) {
				VHmail ($email, 'http://'.$url_host.$uri_file, $url_referer, $alert_threshold);
			}
		}
		
	}
	
	mysql_close();
	
	if (($referer_type == 'engine') && ($redirect_engine == 'Yes')) $answer_type = 'redirect';
	
	// now, anwser the query
	switch ($answer_type) {
		case 'replace':
			header('Content-type: '.$mime_type);
			header('Location: '. $replace_file);
			break;
			
		case 'dontsend':
			break;
		
		case 'waterm':
			if ($mime_type=='image/jpeg') {
				$image = imagecreatefromjpeg($root_server . $uri_file);
				$textcolor = imagecolorallocate($image, 255, 255, 255);
				imagestring($image, 5, 5, 5, $url_site, $textcolor);
				header('Content-type: image/jpeg');
				imagejpeg($image);	
				imagedestroy($image);
			} else {
				//just send the file
				header('Content-type: '.$mime_type);
				$file = fopen( $root_server . $uri_file, 'r' );	
				fpassthru( $file );
				fclose( $file );
			}
			break;
			
		case 'redirect':
			header('Location: '. $redirect_url);
			break;	
		default:
			//just send the file
			header('Content-type: '.$mime_type);
			$file = fopen( $root_server . $uri_file, 'r' );	
			fpassthru( $file );
			fclose( $file );
			break;
	}
	
	function VHmail ($email, $file, $url_referer, $alert_threshold ) {
		global $mosConfig_live_site, $mosConfig_absolute_path, $mosConfig_lang;
		switch ($mosConfig_lang) {
			case 'french':
				$from = "alerte@visohotlink.fr";
				$fromname = "Alerte VisoHotlink";
				$subject = "Dépassement du seuil d'alerte VisoHotlink sur votre site";
				$body = "Bonjour,"
						."\n\nle seuil d'alerte défini dans votre configuration de VisoHotlink a été dépassé"
						."\nLe fichier ".$file." a été affiché plus de ".$alert_threshold." fois sur ".$url_referer." ."
						."\n\nPour mettre en oeuvre une réponse appropriée, rendez-vous sur ".$mosConfig_live_site." ."
						."\n\nSupport de Visohotlink : http://www.visohotlink.fr/articles/support"
						."\n\nMerci de ne pas répondre à ce mail qui a été envoyé automatiquement";  
				break;
			default:
				$from = "alerte@visohotlink.fr";
				$fromname = "VisoHotlink alert";
				$subject = "VisoHotlink alert treshold on your site is exceeded";
				$body = "Hello,"
						."\n\nThe alert treshold that you defined in your VisoHotlink settings has been exceeded."
						."\n\nThe file ".$file." has been displayed more than ".$alert_threshold." times on ".$url_referer." ."
						."\n\nTo take appropriate measures, go on ".$mosConfig_live_site." ."
						."\n\nVisohotlink help: http://www.visohotlink.fr"
						."\n\nThis mail has been sent automaticaly. Please don't answer to it.";  
				break;
		} 
		include_once($mosConfig_absolute_path.'/includes/mail.php');
		mosMail( $from, $fromname, $email, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL );
	}
	
?>