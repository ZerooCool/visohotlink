<?php

	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/
	
	function searchEngineMethod($engine, $url_referer) {
		$resultEngine->url_referer = '';
		$resultEngine->site_referer = '';
		if ($engine->family=='google_images') {
			$resultEngine->url_referer = urldecode($url_referer);
			$resultEngine->url_referer = explode('q=', $resultEngine->url_referer);
			$resultEngine->url_referer = explode('&', $resultEngine->url_referer[1]);
			$resultEngine->url_referer = trim(strtolower(str_replace('+', ' ', $resultEngine->url_referer[0])));
			$resultEngine->site_referer = $engine->name;
		}
		if (($engine->family=='google') || ($engine->family=='club_internet')) {
			$resultEngine->url_referer = explode('q=', $url_referer);
			$resultEngine->url_referer = explode('&', $resultEngine->url_referer[1]);
			$resultEngine->url_referer = trim(strtolower(str_replace('+', ' ', $resultEngine->url_referer[0])));
			$resultEngine->site_referer = $engine->name;
		}
		if ($engine->family=='yahoo') {
			$resultEngine->url_referer = explode('p=', $url_referer);
			$resultEngine->url_referer = explode('&', $resultEngine->url_referer[1]);
			$resultEngine->url_referer = trim(strtolower(str_replace('+', ' ', $resultEngine->url_referer[0])));
			$yahoo_image = substr_count($url_referer, '/search/images/');
			if ($yahoo_image!='0') $resultEngine->site_referer = $engine->name . ' Images'; 
			else $resultEngine->site_referer = $engine->name;
		}
		return $resultEngine;
	}

?>