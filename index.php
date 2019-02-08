<?php

	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/
	

// start a session
session_start();

$visohotlinkRelease = '1.0';
$maxSessionTime = 1800;

// initialize session var if needed 
if (!isset($_SESSION['userok'])) $_SESSION['userok'] = '0';

// Get the time since last connection
if (isset($_SESSION['time'])) {
	$sinceLast = time() - $_SESSION['time'];
	$_SESSION['time'] = time();
} else {
	$sinceLast = 0;
	$_SESSION['time'] = time();
}

// inlcude file configuration
include_once('config/config.visohotlink.php');

// if no file is found, user has to set up VisoHotlink
if (!file_exists( $mosConfig_absolute_path . '/includes/functions.visohotlink.php')) {
	echo 'Please <a href="install/index.php">set up</a> first';
	die();
}

// include file functions and classes
include_once( $mosConfig_absolute_path . '/includes/functions.visohotlink.php');

// include HTML output class	
include_once( $mosConfig_absolute_path . '/admin.visohotlink.html.php');

// Include language file
if (file_exists($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php')) {
	include($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php');
} else {
	include($mosConfig_absolute_path . '/language/english.php');
}


// get user identifying parameters	
$user = mosGetParam( $_REQUEST, 'user', '' );
$pass = mosGetParam( $_REQUEST, 'pass', '' );
$msg = mosGetParam( $_REQUEST, 'mosmsg', '' );

// if user is already known, continue
if (( $_SESSION['userok'] == '1') && ($sinceLast < $maxSessionTime)) {
	
	$database = new database($host, $login, $password, $base, $prefix_table);
	
	$mainframe = new mosMainFrame ( $database, '', '.' );
	
	// Look for links id parameters
	$cid 	= mosGetParam( $_REQUEST, 'cid', array(0) );
	if (!is_array( $cid )) {
		$cid = array(0);
	}
	
	// look for some parameters
	$task = mosGetParam( $_REQUEST, 'task', '' );
	$id = mosGetParam( $_REQUEST, 'id', '0' );
	
	//show page header
	HTML_visohotlink::pageHeader($msg);
	
	switch ($task) {
		case 'showLinks':
			showLinks();
			break;
		
		case 'editLink':
			editLink($cid[0]);
			break;
			
		case 'editLinkA':
			editLink($id);
			break;
			
		case 'saveLink':
			saveLink();
			break;
		
		case 'removeLink':
			removeLink($cid);
			break;
			
		case 'config':
			config();
			break;
	
		case 'saveConfig':
			saveConfig();
			break;	
		
		case 'deconnect':
			deconnect();
			break;
		
		case 'massAction':
			massAction($cid);
			break;
		
		case 'massActionSave':
			massActionSave($cid);
			break;
			
		case 'showSites':
			showSites();
		 	break;
		
		case 'editSite':
			editSite($cid[0]);
			break;
			
		case 'editSiteA':
			editSite($id);
			break;
		
		case 'saveSite':
			saveSite();
			break;
		
		case 'removeSite':
			removeSite($cid);
			break;
		
		case 'showEngines':
			showEngines();
			break;
		
		case 'showKeywords':
			showKeywords();
			break;
			
		case 'showReferers':
			showReferers();
			break;
			
		case 'showSitesRef':
			showSitesRef();
			break;
			
		case 'showEnginesRef':
			showEnginesRef();
			break;
		
		case 'showKeywordsRef':
			showKeywordsRef();
			break;			
		
		default:
			showLinks();
			break;
	}
	
	
	// show page end
	HTML_visohotlink::pageEnd();

// in case of identifying parameters are correct, set the user as known in the session var
} elseif (($user == $visohotlinkUser) && ($pass == $visohotlinkPass)) {
	$_SESSION['userok'] = '1';
	mosRedirect( $mosConfig_live_site.'/index.php', '');
//in case of identifying parameters are wrong, come back to the form with an error message
} elseif ((!empty($user)) || (!empty($pass))) {
	HTML_visohotlink::pageHeaderConnect(_VH_WRONG_IDENTIFY);
	HTML_visohotlink::connect();
	HTML_visohotlink::pageEnd();

//in case of identifying parameters are empty, go to the form with a message
} else {
	HTML_visohotlink::pageHeaderConnect(_VH_IDENTIFY);
	HTML_visohotlink::connect();
	HTML_visohotlink::pageEnd();
}

// Query the MySQL Database with a lot of parameters in order to have a list of hotlinks
function showLinks () {
	global $database, $mainframe, $entries_per_page;
	
	// Look for parameters
	$limit 		= mosGetParam( $_REQUEST, 'limit', $entries_per_page );
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', '0' );
	
	$group_by_file = mosGetParam( $_REQUEST, 'group_by_file', '' );
	$group_by_host = mosGetParam( $_REQUEST, 'group_by_host', '' );
	$group_by_site_referer = mosGetParam( $_REQUEST, 'group_by_site_referer', '' );
	$group_by_referer = mosGetParam( $_REQUEST, 'group_by_referer', '' );
	$group_by_answer_type = mosGetParam( $_REQUEST, 'group_by_answer_type', '' );
	$group_by_link_type = mosGetParam( $_REQUEST, 'group_by_link_type', '' );
	$group_by_mime_type = mosGetParam( $_REQUEST, 'group_by_mime_type', '' );
	$group_by_referer_type = mosGetParam( $_REQUEST, 'group_by_referer_type', '' );
	$sort_by = mosGetParam( $_REQUEST, 'sort_by', 'date' );
	$ascdesc = mosGetParam( $_REQUEST, 'ascdesc', 'DESC' );
	
	// For each parameter, build the corresponding query part
	$query_group_by_file = '';
	if ($group_by_file!='') {
		$query_group_by_file = "\n AND a.uri='$group_by_file '";
	}
	
	$query_group_by_host = '';
	if ($group_by_host!='') {
		$query_group_by_host = "\n AND a.host='$group_by_host'";
	}
	
	$query_group_by_site_referer = '';
	if ($group_by_site_referer!='') {
		$query_group_by_site_referer = "\n AND b.url='$group_by_site_referer'";
	}
	
	$query_group_by_referer = '';
	if ($group_by_referer!='') {
		$query_group_by_referer = "\n AND c.referer='$group_by_referer'";
	}
	
	$query_group_by_answer_type = '';
	if ($group_by_answer_type!='') {
		$query_group_by_answer_type = "\n AND a.answer_type='$group_by_answer_type'";
	}
	
	$query_group_by_link_type = '';
	if ($group_by_link_type!='') {
		$query_group_by_link_type = "\n AND a.link_type='$group_by_link_type'";
	}
	
	$query_group_by_mime_type = '';
	if ($group_by_mime_type!='') {
		$query_group_by_mime_type = "\n AND a.mime_type='$group_by_mime_type'";
	}
	
	$query_group_by_referer_type = '';
	if ($group_by_referer_type!='') {
		if ($group_by_referer_type=='normal') {
			$query_group_by_referer_type = "\n AND b.referer_type='$group_by_referer_type' OR b.referer_type=''";
		}
		else {
			$query_group_by_referer_type = "\n AND b.referer_type='$group_by_referer_type'";
		}
	}
	
	switch ($sort_by) {
		case 'site_referer':
			$query_sort_by = "\n ORDER BY b.url ".$ascdesc;
			break;
		
		case 'host':
			$query_sort_by = "\n ORDER BY a.host ".$ascdesc;
			break;
			
		case 'file':
			$query_sort_by = "\n ORDER BY a.uri ".$ascdesc;
			break;
		
		case 'mime_type':
			$query_sort_by = "\n ORDER BY a.mime_type ".$ascdesc;
			break;
			
		case 'referer_type':
			$query_sort_by = "\n ORDER BY b.referer_type ".$ascdesc;
			break;
			
		case 'downloaded':
			$query_sort_by = "\n ORDER BY a.downloaded ".$ascdesc;
			break;
			
		case 'size':
			$query_sort_by = "\n ORDER BY a.size ".$ascdesc;
			break;
			
		case 'bandwidth':
			$query_sort_by = "\n ORDER BY (a.size * a.downloaded) ".$ascdesc;
			break;
		
		case 'date':
			$query_sort_by = "\n ORDER BY a.date ".$ascdesc;
			break;
		
		default;
			$query_sort_by = '';
			break;
	}
	
	// An now is the query	
	$query = "SELECT a.id AS id, a.uri AS uri, a.size AS size, a.downloaded AS downloaded, a.host AS host, b.url AS site_referer, c.referer AS referer, b.referer_type AS referer_type"
	. "\n FROM #__visohotlink_link AS a"
	. "\n INNER JOIN #__visohotlink_referer AS c"
	. "\n ON c.id=a.id_referer"
	. "\n INNER JOIN #__visohotlink_site AS b"
	. "\n ON b.id=c.id_site"
	. "\n WHERE b.id=b.id"
	. $query_group_by_file
	. $query_group_by_host
	. $query_group_by_site_referer
	. $query_group_by_answer_type
	. $query_group_by_link_type
	. $query_group_by_mime_type
	. $query_group_by_referer_type
	. $query_group_by_referer
	. $query_sort_by
	;
	
	// Set the query and load results
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	$total = count($rows);
	
	// Ask for the class that build the page navigation system
	$pageNav = new mosPageNav( $total, $limitstart, $limit );
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	// Initialize some vars
	$stats->downloaded = 0;
	$stats->bandwidth = 0;
	
	// Compute some statistics of the query results
	foreach ($rows as $row) {
		$stats->downloaded = $stats->downloaded + $row->downloaded;
		$stats->bandwidth = $stats->bandwidth + $row->downloaded * $row->size;
	}
	
	// We need only a part of results
	$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit); 
	
	// Build the select lists
	$select_list = '';
	
	$select_list .= '<tr><td width="150">' . _VH_FILES . '</td><td>'.buildlist('group_by_file', $group_by_file, 'uri', '', 'link').'</td></tr>';
	$select_list .= '<tr><td>' . _VH_MIME_TYPE . '</td><td>'.buildlist('group_by_mime_type', $group_by_mime_type, 'mime_type', '', 'link').'</td></tr>';
	/* $select_list .= '<tr><td>' . _VH_FILES_HOSTS . '</td><td>'.buildlist('group_by_host', $group_by_host, 'host').'</td></tr>'; */
	
	$select_list .= '<tr><td>' . _VH_REFERER_TYPE . '</td><td><select name="group_by_referer_type" onchange="document.adminForm.submit();">
					<option value="" '. ($group_by_referer_type=='' ?  'selected="selected"' : '') .'></option>
					<option value="normal" '. ($group_by_referer_type=='normal' ?  'selected="selected"' : '') .'>' . _VH_NORMAL . '</option>
					<option value="engine" '. ($group_by_referer_type=='engine' ?  'selected="selected"' : '') .'>' . _VH_ENGINE . '</option>			
			</select></td></tr>';
	
	$select_list .= '<tr><td>' . _VH_HOTLINK_SITES . '</td><td>'.buildlist('group_by_site_referer', $group_by_site_referer, 'url', '', 'site').'</td></tr>';
	
	$select_list .= '<tr><td>' . _VH_HOTLINK_TYPES . '</td><td><select name="group_by_link_type" onchange="document.adminForm.submit();" >
					<option value="" '. ($group_by_link_type=='' ?  'selected="selected"' : '') .'></option>
					<option value="default" '. ($group_by_link_type=='default' ?  'selected="selected"' : '') .'>' . _VH_DEFAULT . '</option> 
					<option value="partner" '. ($group_by_link_type=='partner' ?  'selected="selected"' : '') .'>' . _VH_PARTNER . '</option>
					<option value="linked" '. ($group_by_link_type=='linked' ?  'selected="selected"' : '') .'>' . _VH_RECIPROCAL_LINK . '</option>
					<option value="link_ask" '. ($group_by_link_type=='link_ask' ?  'selected="selected"' : '') .'>' . _VH_ASKED_LINK . '</option>
					<option value="nolink" '. ($group_by_link_type=='nolink' ?  'selected="selected"' : '') .'>' . _VH_WITHOUT_RECIPROCAL_LINK . '</option>
					<option value="other" '. ($group_by_link_type=='other' ?  'selected="selected"' : '') .'>' . _VH_OTHER . '</option>  
			</select></td></tr>';
			
	$select_list .= '<tr><td>' . _VH_ANSWER_TYPES . '</td><td><select name="group_by_answer_type" onchange="document.adminForm.submit();">
					<option value="" '. ($group_by_answer_type=='' ?  'selected="selected"' : '') .'></option>
					<option value="default" '. ($group_by_answer_type=='default' ?  'selected="selected"' : '') .'>' . _VH_DEFAULT . '</option>
					<option value="sendpic" '. ($group_by_answer_type=='sendpic' ?  'selected="selected"' : '') .'>' . _VH_SEND_PIC . '</option>
					<option value="replace" '. ($group_by_answer_type=='replace' ?  'selected="selected"' : '') .'>' . _VH_REPLACE_PIC . '</option>
					<option value="waterm" '. ($group_by_answer_type=='waterm' ?  'selected="selected"' : '') .'>' . _VH_AD_WATERMARK . '</option>
					<option value="dontsend" '. ($group_by_answer_type=='dontsend' ?  'selected="selected"' : '') .'>' . _VH_DONT_SEND . '</option> 		
					<option value="redirect" '. ($group_by_answer_type=='redirect' ?  'selected="selected"' : '') .'>' . _VH_REDIRECT . '</option> 					
			</select></td></tr>';
	
	$select_list .= '<tr><td>' . _VH_SORTING . '</td><td><select name="sort_by" onchange="document.adminForm.submit();">
					<option value="referer_type" '. ($sort_by=='referer_type' ?  'selected="selected"' : '') .'>' . _VH_REFERER_TYPE . '</option>
					<option value="site_referer" '. ($sort_by=='site_referer' ?  'selected="selected"' : '') .'>' . _VH_HOTLINK_SITES . '</option>
					<option value="file" '. ($sort_by=='file' ?  'selected="selected"' : '') .'>' . _VH_FILES . '</option>
					<option value="mime_type" '. ($sort_by=='mime_type' ?  'selected="selected"' : '') .'>' . _VH_MIME_TYPE . '</option>
					<option value="downloaded" '. ($sort_by=='downloaded' ?  'selected="selected"' : '') .'>' . _VH_DOWNLOADS . '</option>
					<option value="size" '. ($sort_by=='size' ?  'selected="selected"' : '') .'>' . _VH_SIZE . '</option>
					<option value="bandwidth" '. ($sort_by=='bandwidth' ?  'selected="selected"' : '') .'>' . _VH_BANDWIDH . '</option>
					<option value="date" '. ($sort_by=='date' ?  'selected="selected"' : '') .'>' . _VH_DATE . '</option>
					</select>
					
					<select name="ascdesc" onchange="document.adminForm.submit();">
					<option value="ASC" '. ($ascdesc=='ASC' ?  'selected="selected"' : '') .'>' . _VH_ASC . '</option>
					<option value="DESC" '. ($ascdesc=='DESC' ?  'selected="selected"' : '') .'>' . _VH_DESC . '</option>
					</select>
					</td></tr>';
	
	
	$button = array();
	$button[0]->type = 'edit';
	$button[0]->task = 'editLink';
	$button[1]->type = 'action';
	$button[1]->task = 'massAction';
	$button[2]->type = 'delete';
	$button[2]->task = 'removeLink';
	showButton ($button);

	// Sow results
	HTML_visohotlink::showLinks ($rows, $pageNav, $select_list, $stats , selection());
	
}

// Query the MySQL Database with a lot of parameters in order to have a list of sites
function showSites () {
	global $database, $mainframe, $entries_per_page;
	
	// Look for parameters
	$limit 		= mosGetParam( $_REQUEST, 'limit', $entries_per_page );
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', '0' );
	$group_by_answer_type = mosGetParam( $_REQUEST, 'group_by_answer_type', '' );
	$group_by_link_type = mosGetParam( $_REQUEST, 'group_by_link_type', '' );
	$sort_by = mosGetParam( $_REQUEST, 'sort_by', 'downloaded' );
	$ascdesc = mosGetParam( $_REQUEST, 'ascdesc', 'DESC' );
	
	$query_group_by_answer_type = '';
	if ($group_by_answer_type!='') {
		$query_group_by_answer_type = "\n AND b.answer_type='$group_by_answer_type'";
	}
	
	$query_group_by_link_type = '';
	if ($group_by_link_type!='') {
		$query_group_by_link_type = "\n AND b.link_type='$group_by_link_type'";
	}
	
	switch ($sort_by) {
		case 'url':
			$query_sort_by = "\n ORDER BY b.url ".$ascdesc;
			break;
			
		case 'downloaded':
			$query_sort_by = "\n ORDER BY a.downloaded ".$ascdesc;
			break;
			
		case 'bandwidth':
			$query_sort_by = "\n ORDER BY a.bandwidth ".$ascdesc;
			break;
		
		case 'date':
			$query_sort_by = "\n ORDER BY b.date ".$ascdesc;
			break;
		
		case 'visits':
			$query_sort_by = "\n ORDER BY visits ".$ascdesc;
			break;
			
		case 'performance_rate':
			$query_sort_by = "\n ORDER BY performance_rate ".$ascdesc;
			break;
		case 'performance_bw_rate':
			$query_sort_by = "\n ORDER BY performance_bw_rate ".$ascdesc;
			break;
		
		default;
			$query_sort_by = '';
			break;
	}
	
	// An now is the query	
	$query = "INSERT INTO #__visohotlink_temp2 SELECT DISTINCT b.id, SUM(a.downloaded), SUM(a.size*a.downloaded) FROM #__visohotlink_site AS b INNER JOIN #__visohotlink_referer AS c ON b.id=c.id_site INNER JOIN #__visohotlink_link AS a ON a.id_referer=c.id WHERE b.referer_type='normal' GROUP BY b.id";
	
	$database->setQuery( $query );
	$database->query();
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	$query = "SELECT DISTINCT b.*, a.downloaded AS downloaded, a.bandwidth AS bandwidth, SUM(d.visits) AS visits, TRUNCATE (SUM(d.visits) / a.downloaded * 100, 2) AS performance_rate, TRUNCATE (SUM(d.visits) / a.bandwidth * 1000000, 2) AS performance_bw_rate"
	. "\n FROM #__visohotlink_site AS b"
	. "\n LEFT JOIN #__visohotlink_temp2 AS a"
	. "\n ON a.id_site=b.id"
	. "\n LEFT JOIN #__visohotlink_referer AS c"
	. "\n ON c.id_site=b.id"
	. "\n LEFT JOIN #__visohotlink_visits AS d"
	. "\n ON d.id_referer=c.id"
	. "\n WHERE b.referer_type='normal'"
	. $query_group_by_answer_type
	. $query_group_by_link_type
	. "\n GROUP BY b.id"
	. $query_sort_by;
	
	// Set the query and load results
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	$query = "TRUNCATE TABLE #__visohotlink_temp2";
	$database->setQuery( $query );
	$database->query();
	
	$total = count($rows);
	
	// Ask for the class that build the page navigation system
	$pageNav = new mosPageNav( $total, $limitstart, $limit );
	
	// Initialize some vars
	$stats->downloaded = 0;
	$stats->bandwidth = 0;
	$stats->visits = 0;
	
	// Compute some statistics of the query results
	foreach ($rows as $row) {
		$stats->downloaded = $stats->downloaded + $row->downloaded;
		$stats->bandwidth = $stats->bandwidth + $row->bandwidth;
		$stats->visits = $stats->visits + $row->visits;
	}
	
	// We need only a part of results
	$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit); 
	
	
	// Build the select lists
	$select_list = '';
	
	$select_list .= '<tr><td width="150">' . _VH_HOTLINK_TYPES . '</td><td><select name="group_by_link_type" onchange="document.adminForm.submit();" >
					<option value="" '. ($group_by_link_type=='' ?  'selected="selected"' : '') .'></option>
					<option value="default" '. ($group_by_link_type=='default' ?  'selected="selected"' : '') .'>' . _VH_DEFAULT . '</option> 
					<option value="partner" '. ($group_by_link_type=='partner' ?  'selected="selected"' : '') .'>' . _VH_PARTNER . '</option>
					<option value="linked" '. ($group_by_link_type=='linked' ?  'selected="selected"' : '') .'>' . _VH_RECIPROCAL_LINK . '</option>
					<option value="link_ask" '. ($group_by_link_type=='link_ask' ?  'selected="selected"' : '') .'>' . _VH_ASKED_LINK . '</option>
					<option value="nolink" '. ($group_by_link_type=='nolink' ?  'selected="selected"' : '') .'>' . _VH_WITHOUT_RECIPROCAL_LINK . '</option>
					<option value="other" '. ($group_by_link_type=='other' ?  'selected="selected"' : '') .'>' . _VH_OTHER . '</option>  
			</select></td></tr>';
			
	$select_list .= '<tr><td>' . _VH_ANSWER_TYPES . '</td><td><select name="group_by_answer_type" onchange="document.adminForm.submit();">
					<option value="" '. ($group_by_answer_type=='' ?  'selected="selected"' : '') .'></option>
					<option value="default" '. ($group_by_answer_type=='default' ?  'selected="selected"' : '') .'>' . _VH_DEFAULT . '</option>
					<option value="sendpic" '. ($group_by_answer_type=='sendpic' ?  'selected="selected"' : '') .'>' . _VH_SEND_PIC . '</option>
					<option value="replace" '. ($group_by_answer_type=='replace' ?  'selected="selected"' : '') .'>' . _VH_REPLACE_PIC . '</option>
					<option value="waterm" '. ($group_by_answer_type=='waterm' ?  'selected="selected"' : '') .'>' . _VH_AD_WATERMARK . '</option>
					<option value="dontsend" '. ($group_by_answer_type=='dontsend' ?  'selected="selected"' : '') .'>' . _VH_DONT_SEND . '</option> 		
					<option value="redirect" '. ($group_by_answer_type=='redirect' ?  'selected="selected"' : '') .'>' . _VH_REDIRECT . '</option> 					
			</select></td></tr>';
	$select_list .= '<tr><td>' . _VH_SORTING . '</td><td><select name="sort_by" onchange="document.adminForm.submit();">
					<option value="url" '. ($sort_by=='url' ?  'selected="selected"' : '') .'>' . _VH_SITE_URL . '</option>
					<option value="downloaded" '. ($sort_by=='downloaded' ?  'selected="selected"' : '') .'>' . _VH_DOWNLOADS . '</option>
					<option value="bandwidth" '. ($sort_by=='bandwidth' ?  'selected="selected"' : '') .'>' . _VH_BANDWIDH . '</option>
					<option value="visits" '. ($sort_by=='visits' ?  'selected="selected"' : '') .'>' . _VH_VISITS . '</option>
					<option value="performance_rate" '. ($sort_by=='performance_rate' ?  'selected="selected"' : '') .'>' . _VH_PERFORMANCE_RATE . '</option>
					<option value="performance_bw_rate" '. ($sort_by=='performance_bw_rate' ?  'selected="selected"' : '') .'>' . _VH_PERFORMANCE_BW_RATE . '</option>
					<option value="date" '. ($sort_by=='date' ?  'selected="selected"' : '') .'>' . _VH_DATE . '</option>
					</select>
					
					<select name="ascdesc" onchange="document.adminForm.submit();">
					<option value="ASC" '. ($ascdesc=='ASC' ?  'selected="selected"' : '') .'>' . _VH_ASC . '</option>
					<option value="DESC" '. ($ascdesc=='DESC' ?  'selected="selected"' : '') .'>' . _VH_DESC . '</option>
					</select>
					</td></tr>';
	
	$button = array();
	$button[0]->type = 'edit';
	$button[0]->task = 'editSite';
	$button[2]->type = 'delete';
	$button[2]->task = 'removeSite';
	showButton ($button);
	
	// Sow results
	HTML_visohotlink::showSites ($rows, $pageNav, $select_list, selection_site(), $stats, 'showSites');
}

// Computes stats for search engines
function showEngines () {
	global $database, $mainframe, $entries_per_page;
	
	// Look for parameters
	$limit 		= mosGetParam( $_REQUEST, 'limit', $entries_per_page );
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', '0' );
	$sort_by = mosGetParam( $_REQUEST, 'sort_by', 'downloaded' );
	$ascdesc = mosGetParam( $_REQUEST, 'ascdesc', 'DESC' );
	
	switch ($sort_by) {
		case 'url':
			$query_sort_by = "\n ORDER BY b.url ".$ascdesc;
			break;
			
		case 'downloaded':
			$query_sort_by = "\n ORDER BY downloaded ".$ascdesc;
			break;
			
		case 'bandwidth':
			$query_sort_by = "\n ORDER BY bandwidth ".$ascdesc;
			break;
		
		case 'date':
			$query_sort_by = "\n ORDER BY b.date ".$ascdesc;
			break;
		
		case 'visits':
			$query_sort_by = "\n ORDER BY visits ".$ascdesc;
			break;
			
		case 'performance_rate':
			$query_sort_by = "\n ORDER BY performance_rate ".$ascdesc;
			break;
		case 'performance_bw_rate':
			$query_sort_by = "\n ORDER BY performance_bw_rate ".$ascdesc;
			break;
		
		default;
			$query_sort_by = '';
			break;
	}
	
	// An now is the query		
	$query = "INSERT INTO #__visohotlink_temp2 SELECT DISTINCT b.id, SUM(a.downloaded), SUM(a.size*a.downloaded) FROM #__visohotlink_site AS b INNER JOIN #__visohotlink_referer AS c ON b.id=c.id_site INNER JOIN #__visohotlink_link AS a ON a.id_referer=c.id WHERE b.referer_type='engine' GROUP BY b.id";
	
	$database->setQuery( $query );
	$database->query();
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	$query = "SELECT DISTINCT b.*, a.downloaded AS downloaded, a.bandwidth AS bandwidth, SUM(d.visits) AS visits, TRUNCATE (SUM(d.visits) / a.downloaded * 100, 2) AS performance_rate, TRUNCATE (SUM(d.visits) / a.bandwidth * 1000000, 2) AS performance_bw_rate"
	. "\n FROM #__visohotlink_site AS b"
	. "\n LEFT JOIN #__visohotlink_temp2 AS a"
	. "\n ON a.id_site=b.id"
	. "\n LEFT JOIN #__visohotlink_referer AS c"
	. "\n ON c.id_site=b.id"
	. "\n LEFT JOIN #__visohotlink_visits AS d"
	. "\n ON d.id_referer=c.id"
	. "\n WHERE b.referer_type='engine'"
	. "\n GROUP BY b.id"
	. $query_sort_by;
	
	// Set the query and load results
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	$query = "TRUNCATE TABLE #__visohotlink_temp2";
	$database->setQuery( $query );
	$database->query();
	
	$total = count($rows);
	
	// Ask for the class that build the page navigation system
	$pageNav = new mosPageNav( $total, $limitstart, $limit );
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	// Initialize some vars
	$stats->downloaded = 0;
	$stats->bandwidth = 0;
	$stats->visits = 0;
	
	// Compute some statistics of the query results
	foreach ($rows as $row) {
		$stats->downloaded = $stats->downloaded + $row->downloaded;
		$stats->bandwidth = $stats->bandwidth + $row->bandwidth;
		$stats->visits = $stats->visits + $row->visits;
	}
	
	// We need only a part of results
	$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit); 
	
	
	// Build the select lists
	$select_list = '';
	$select_list .= '<tr><td>' . _VH_SORTING . '</td><td><select name="sort_by" onchange="document.adminForm.submit();">
					<option value="url" '. ($sort_by=='url' ?  'selected="selected"' : '') .'>' . _VH_ENGINE . '</option>
					<option value="downloaded" '. ($sort_by=='downloaded' ?  'selected="selected"' : '') .'>' . _VH_DOWNLOADS . '</option>
					<option value="bandwidth" '. ($sort_by=='bandwidth' ?  'selected="selected"' : '') .'>' . _VH_BANDWIDH . '</option>
					<option value="visits" '. ($sort_by=='visits' ?  'selected="selected"' : '') .'>' . _VH_VISITS . '</option>
					<option value="performance_rate" '. ($sort_by=='performance_rate' ?  'selected="selected"' : '') .'>' . _VH_PERFORMANCE_RATE . '</option>
					<option value="performance_bw_rate" '. ($sort_by=='performance_bw_rate' ?  'selected="selected"' : '') .'>' . _VH_PERFORMANCE_BW_RATE . '</option>
					<option value="date" '. ($sort_by=='date' ?  'selected="selected"' : '') .'>' . _VH_DATE . '</option>
					</select>
					
					<select name="ascdesc" onchange="document.adminForm.submit();">
					<option value="ASC" '. ($ascdesc=='ASC' ?  'selected="selected"' : '') .'>' . _VH_ASC . '</option>
					<option value="DESC" '. ($ascdesc=='DESC' ?  'selected="selected"' : '') .'>' . _VH_DESC . '</option>
					</select>
					</td></tr>';
	
	// Sow results
	HTML_visohotlink::showSites ($rows, $pageNav, $select_list, selection_site(), $stats, 'showEngines');
}

// Computes stats for keywords
function showKeywords () {
	global $database, $mainframe, $entries_per_page;
	
	// Look for parameters
	$limit 		= mosGetParam( $_REQUEST, 'limit', $entries_per_page );
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', '0' );
	$sort_by = mosGetParam( $_REQUEST, 'sort_by', 'downloaded' );
	$ascdesc = mosGetParam( $_REQUEST, 'ascdesc', 'DESC' );
	$group_by_site_referer = mosGetParam( $_REQUEST, 'group_by_site_referer', '' );
	
	$query_group_by_site_referer = '';
	if ($group_by_site_referer!='') {
		$query_group_by_site_referer = "\n AND b.url='$group_by_site_referer'";
	}
	
	switch ($sort_by) {
		case 'url':
			$query_sort_by = "\n ORDER BY c.referer ".$ascdesc;
			break;
			
		case 'downloaded':
			$query_sort_by = "\n ORDER BY a.downloaded ".$ascdesc;
			break;
			
		case 'bandwidth':
			$query_sort_by = "\n ORDER BY a.bandwidth ".$ascdesc;
			break;
		
		case 'date':
			$query_sort_by = "\n ORDER BY c.date ".$ascdesc;
			break;
		
		case 'visits':
			$query_sort_by = "\n ORDER BY visits ".$ascdesc;
			break;
			
		case 'performance_rate':
			$query_sort_by = "\n ORDER BY performance_rate ".$ascdesc;
			break;
		case 'performance_bw_rate':
			$query_sort_by = "\n ORDER BY performance_bw_rate ".$ascdesc;
			break;
		
		default;
			$query_sort_by = '';
			break;
	}
	
	// An now is the query	
	$query = "INSERT INTO #__visohotlink_temp SELECT DISTINCT c.referer, c.id, SUM(a.downloaded), SUM(a.size*a.downloaded) FROM #__visohotlink_site AS b INNER JOIN #__visohotlink_referer AS c ON b.id=c.id_site INNER JOIN #__visohotlink_link AS a ON a.id_referer=c.id WHERE b.referer_type='engine' $query_group_by_site_referer GROUP BY c.referer";
	
	$database->setQuery( $query );
	$database->query();
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	$query = "SELECT DISTINCT c.referer, c.id, c.date, a.downloaded AS downloaded, a.bandwidth AS bandwidth, SUM(d.visits) AS visits, TRUNCATE (SUM(d.visits) / a.downloaded * 100, 2) AS performance_rate, TRUNCATE (SUM(d.visits) / a.bandwidth * 1000000, 2) AS performance_bw_rate"
	. "\n FROM #__visohotlink_referer AS c"
	. "\n LEFT JOIN #__visohotlink_temp AS a"
	. "\n ON a.referer=c.referer"
	. "\n LEFT JOIN #__visohotlink_visits AS d"
	. "\n ON c.id=d.id_referer"
	. "\n INNER JOIN #__visohotlink_site AS b"
	. "\n ON b.id=c.id_site"
	. "\n WHERE b.referer_type='engine'"
	. $query_group_by_site_referer
	. "\n GROUP BY c.referer"
	. $query_sort_by;
	
	// Set the query and load results
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	$query = "TRUNCATE TABLE #__visohotlink_temp";
	$database->setQuery( $query );
	$database->query();
	
	$total = count($rows);
	
	// Ask for the class that build the page navigation system
	$pageNav = new mosPageNav( $total, $limitstart, $limit );
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	// Initialize some vars
	$stats->downloaded = 0;
	$stats->bandwidth = 0;
	$stats->visits = 0;
	
	// Compute some statistics of the query results
	foreach ($rows as $row) {
		$stats->downloaded = $stats->downloaded + $row->downloaded;
		$stats->bandwidth = $stats->bandwidth + $row->bandwidth;
		$stats->visits = $stats->visits + $row->visits;
	}
	
	// We need only a part of results
	$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit); 
	
	
	// Build the select lists
	$select_list = '';
	$select_list .= '<tr><td width="150">' . _VH_ENGINE_NAME . '</td><td>'.buildlist('group_by_site_referer', $group_by_site_referer, 'url', 'engine').'</td></tr>';
	$select_list .= '<tr><td>' . _VH_SORTING . '</td><td><select name="sort_by" onchange="document.adminForm.submit();">
					<option value="url" '. ($sort_by=='url' ?  'selected="selected"' : '') .'>' . _VH_KEYWORD . '</option>
					<option value="downloaded" '. ($sort_by=='downloaded' ?  'selected="selected"' : '') .'>' . _VH_DOWNLOADS . '</option>
					<option value="bandwidth" '. ($sort_by=='bandwidth' ?  'selected="selected"' : '') .'>' . _VH_BANDWIDH . '</option>
					<option value="visits" '. ($sort_by=='visits' ?  'selected="selected"' : '') .'>' . _VH_VISITS . '</option>
					<option value="performance_rate" '. ($sort_by=='performance_rate' ?  'selected="selected"' : '') .'>' . _VH_PERFORMANCE_RATE . '</option>
					<option value="performance_bw_rate" '. ($sort_by=='performance_bw_rate' ?  'selected="selected"' : '') .'>' . _VH_PERFORMANCE_BW_RATE . '</option>
					<option value="date" '. ($sort_by=='date' ?  'selected="selected"' : '') .'>' . _VH_DATE . '</option>
					</select>
					
					<select name="ascdesc" onchange="document.adminForm.submit();">
					<option value="ASC" '. ($ascdesc=='ASC' ?  'selected="selected"' : '') .'>' . _VH_ASC . '</option>
					<option value="DESC" '. ($ascdesc=='DESC' ?  'selected="selected"' : '') .'>' . _VH_DESC . '</option>
					</select>
					</td></tr>';
	
	// Sow results
	HTML_visohotlink::showSites ($rows, $pageNav, $select_list, selection_site(), $stats, 'showKeywords');
}

// Computes stats for search engines
function showReferers () {
	global $database, $mainframe, $entries_per_page;
	
	// Look for parameters
	$limit 		= mosGetParam( $_REQUEST, 'limit', $entries_per_page );
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', '0' );
	$sort_by = mosGetParam( $_REQUEST, 'sort_by', 'date' );
	$ascdesc = mosGetParam( $_REQUEST, 'ascdesc', 'DESC' );
	$group_by_site_referer = mosGetParam( $_REQUEST, 'group_by_site_referer', '' );
	$group_by_referer = mosGetParam( $_REQUEST, 'group_by_referer', '' );
	$group_by_referer_type = mosGetParam( $_REQUEST, 'group_by_referer_type', '' );
	$group_by_entry_page = mosGetParam( $_REQUEST, 'group_by_entry_page', '' );
	
	$query_group_by_site_referer = '';
	if ($group_by_site_referer!='') {
		$query_group_by_site_referer = "\n AND b.url='$group_by_site_referer'";
	}
	
	$query_group_by_referer = '';
	if ($group_by_referer!='') {
		$query_group_by_referer = "\n AND c.referer='$group_by_referer'";
	}
	
	$query_group_by_referer_type = '';
	if ($group_by_referer_type!='') {
		$query_group_by_referer_type = "\n AND b.referer_type='$group_by_referer_type'";
	}
	
	$query_group_by_entry_page = '';
	if ($group_by_entry_page!='') {
		$query_group_by_entry_page = "\n AND d.entry_page='$group_by_entry_page'";
	}
	
	switch ($sort_by) {
		case 'referer':
			$query_sort_by = "\n ORDER BY c.referer ".$ascdesc;
			break;
		
		case 'date':
			$query_sort_by = "\n ORDER BY d.date ".$ascdesc;
			break;
		
		case 'visits':
			$query_sort_by = "\n ORDER BY d.visits ".$ascdesc;
			break;
		
		default;
			$query_sort_by = '';
			break;
	}
	
	// An now is the query	
	$query = "SELECT d.*, c.referer AS referer, b.url AS site_referer, b.referer_type AS referer_type"
			. "\n FROM #__visohotlink_visits AS d"
			. "\n LEFT JOIN #__visohotlink_referer AS c"
			. "\n ON c.id=d.id_referer"
			. "\n LEFT JOIN #__visohotlink_site AS b"
			. "\n ON b.id=c.id_site"
			. "\n WHERE d.id=d.id"
			. $query_group_by_site_referer
			. $query_group_by_referer
			. $query_group_by_referer_type
			 .$query_group_by_entry_page
			. $query_sort_by;
	
	// Set the query and load results
	$database->setQuery( $query );
	$rows = $database->loadObjectList();
	$total = count($rows);
	
	// Ask for the class that build the page navigation system
	$pageNav = new mosPageNav( $total, $limitstart, $limit );
	
	// If database error, stop here
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	// Initialize some vars
	$stats->visits = 0;
	
	// Compute some statistics of the query results
	foreach ($rows as $row) {
		$stats->visits = $stats->visits + $row->visits;
	}
	
	// We need only a part of results
	$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit); 
	
	
	// Build the select lists
	$select_list = '';
	$select_list .= '<tr><td width="150">' . _VH_REFERER_SITE . '</td><td>'.buildlist('group_by_site_referer', $group_by_site_referer, 'url', '', 'site').'</td></tr>';
	
	$select_list .= '<tr><td width="150">' . _VH_ENTRY_PAGE . '</td><td>'.buildlist('group_by_entry_page', $group_by_entry_page, 'entry_page', '', 'visits').'</td></tr>';
	
	$select_list .= '<tr><td>' . _VH_REFERER_TYPE . '</td><td><select name="group_by_referer_type" onchange="document.adminForm.submit();">
					<option value="" '. ($group_by_referer_type=='' ?  'selected="selected"' : '') .'></option>
					<option value="normal" '. ($group_by_referer_type=='normal' ?  'selected="selected"' : '') .'>' . _VH_NORMAL . '</option>
					<option value="engine" '. ($group_by_referer_type=='engine' ?  'selected="selected"' : '') .'>' . _VH_ENGINE . '</option>			
			</select></td></tr>';
	$select_list .= '<tr><td>' . _VH_SORTING . '</td><td><select name="sort_by" onchange="document.adminForm.submit();">
					<option value="referer" '. ($sort_by=='referer' ?  'selected="selected"' : '') .'>' . _VH_REFERER_SITE . '</option>
					<option value="visits" '. ($sort_by=='visits' ?  'selected="selected"' : '') .'>' . _VH_VISITS . '</option>
					<option value="date" '. ($sort_by=='date' ?  'selected="selected"' : '') .'>' . _VH_DATE . '</option>
					</select>
					
					<select name="ascdesc" onchange="document.adminForm.submit();">
					<option value="ASC" '. ($ascdesc=='ASC' ?  'selected="selected"' : '') .'>' . _VH_ASC . '</option>
					<option value="DESC" '. ($ascdesc=='DESC' ?  'selected="selected"' : '') .'>' . _VH_DESC . '</option>
					</select>
					</td></tr>';
	
	// Sow results
	HTML_visohotlink::showReferers ($rows, $pageNav, $select_list, $stats);
}

// Edit a link
function editLink ($uid=0) {
	global $database;
	
	$row = new visohotlink_link( $database );
	// load the row from the db table
	$row->load( $uid );
	
	// fail if checked out not by 'me'
	/* if ($row->isCheckedOut( $my->id )) {
		mosRedirect( 'index2.php?option='. $option, 'The poll '. $row->title .' is currently being edited by another administrator.' );
	} */
	
	$row->loadsite();
	
	$button = array();
	$button[0]->type = 'save';
	$button[0]->task = 'saveLink';
	$button[1]->type = 'cancel';
	$button[1]->task = 'showLinks';
	showButton ($button);
	HTML_visohotlink::editLink($row, selection() );
}

// Edit a link
function editSite ($uid=0) {
	global $database;
	
	$row = new visohotlink_site( $database );
	// load the row from the db table
	$row->load( $uid );
	
	// fail if checked out not by 'me'
	/* if ($row->isCheckedOut( $my->id )) {
		mosRedirect( 'index2.php?option='. $option, 'The poll '. $row->title .' is currently being edited by another administrator.' );
	} */
	
	$button = array();
	$button[0]->type = 'save';
	$button[0]->task = 'saveSite';
	$button[1]->type = 'cancel';
	$button[1]->task = 'showSites';
	showButton ($button);
	HTML_visohotlink::editSite($row, selection_site() );
}

// save link changes
function saveLink () {
	global $database, $mosConfig_live_site;

	$msg = '';
	
	// save the link information
	$row = new visohotlink_link( $database );
	
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$isNew = ($row->id == 0);
	
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$msg = _VH_SAVE_SUCCESS;	
	
	// redirect to the links list
	mosRedirect( $mosConfig_live_site.'/index.php?' . selection(), $msg );
}

// save link changes
function saveSite () {
	global $database, $mosConfig_live_site;

	$msg = '';
	
	// save the link information
	$row = new visohotlink_site( $database );
	
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$isNew = ($row->id == 0);
	
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	
	if (!$isNew) {
		$query = "UPDATE #__visohotlink_link AS a LEFT JOIN #__visohotlink_referer AS c ON c.id=a.id_referer"
				. "\n SET a.link_type='$row->link_type', a.answer_type='$row->answer_type'"
				. "\n WHERE c.id_site='$row->id'"
				;
		$database->setQuery( $query );
		$database->query();
		// If database error, stop here
		if ($database->getErrorNum()) {
			echo $database->stderr();
			return false;
		}
	}
	
	$msg = _VH_SAVE_SUCCESS;	
	
	// redirect to the sites list
	mosRedirect( $mosConfig_live_site.'/index.php?task=showSites&' . selection_site(), $msg );
}

// remove links
function removeLink ($cid) {
	global $database, $mosConfig_live_site;
	
	$msg = '';
	
	// remove all selected links
	for ($i=0, $n=count($cid); $i < $n; $i++) {
		$hotlink = new visohotlink_link( $database );
		if (!$hotlink->delete( $cid[$i] )) {
			$msg .= $hotlink->getError();
		}
	}
	
	// redirect to the links list with an error message if needed
	mosRedirect( $mosConfig_live_site.'/index.php?' . selection(), _VH_REMOVE_SUCCESS);
}

// remove links
function removeSite ($cid) {
	global $database, $mosConfig_live_site;
	
	$msg = '';
	
	// remove all selected links
	for ($i=0, $n=count($cid); $i < $n; $i++) {
		$site = new visohotlink_site( $database );
		$site->id  = $cid[$i];
		if (!$site->deleteSite()) return false;	
	}
	
	// redirect to the links list with an error message if needed
	mosRedirect( $mosConfig_live_site.'/index.php?task=showSites&' . selection_site(), _VH_REMOVE_SUCCESS.$msg);
}

// VisoHotlink settings
function config () {
	global $mosConfig_live_site, $url_site;
	
	// build the content of the .htaccess file
	$trans = array("www." => "");
	
	$ndd = strtr( $_SERVER['HTTP_HOST'], $trans );
	
	$trans = array( "http://".$_SERVER['HTTP_HOST']."/" => "");
	$visoHotlinkPath = strtr( $mosConfig_live_site, $trans );
	
	/* $htaccess = "RewriteEngine on"
				. "\nRewriteCond %{HTTP_REFERER} !^$"
				. "\nRewriteCond %{HTTP_REFERER} !^http://(www.)?" . $ndd . "*"
				. "\nRewriteRule \.jpg$ " . $mosConfig_live_site . "/hotlink.php?uri= [L]"
				. "\nRewriteRule \.jpeg$ " . $mosConfig_live_site . "/hotlink.php [L]"
				; */
	
	$htaccess = "RewriteEngine on"
				. "\nRewriteCond %{HTTP_REFERER} !^$"
				. "\nRewriteCond %{HTTP_REFERER} !^http://(www.)?" . $ndd . "*"
				. "\nRewriteCond %{REQUEST_URI} ^(.*)Directory_1/Sub-directory_1/(.*)$ [OR]"
				. "\nRewriteCond %{REQUEST_URI} ^(.*)Directory_2/Sub-directory_2/(.*)$ [OR]"
				. "\nRewriteCond %{REQUEST_URI} ^(.*)Directory_3/Sub-directory_3/(.*)$"
				. "\nRewriteRule ^(.*)$ " . $visoHotlinkPath . "/hotlink.php [L]"
				;
	
	$tag = "<!-- VisoHotlink -->"
			. "\n<a href=\"" . _VH_URL_TAG . "\" title=\"" . _VH_TEXT_TAG . "\"><script language=\"javascript\" type=\"text/javascript\">"
			. "\nvar VisoHotlinkURL = \"" . $url_site . "/" . $visoHotlinkPath . "/referer.php\";"
			. "\n</script><script language=\"javascript\" src=\"" . $url_site . "/" . $visoHotlinkPath . "/referer.js\" type=\"text/javascript\"></script>"
			. "\n<noscript>" . _VH_TEXT_TAG
			. "\n<img src=\"" . $url_site . "/" . $visoHotlinkPath . "/referer.php\" alt=\"VisoHotlink\" style=\"border:0\" />"
			. "\n</noscript></a>"
			. "\n<!-- /VisoHotlink -->"; 
	
	// show config form
	
	$button = array();
	$button[0]->type = 'save';
	$button[0]->task = 'saveConfig';
	$button[1]->type = 'cancel';
	$button[1]->task = 'showLinks';
	showButton ($button);
	
	HTML_visohotlink::config($htaccess, $tag);
}

function massAction($cid) {
	
	$button[0]->type = 'save';
	$button[0]->task = 'massActionSave';
	$button[1]->type = 'cancel';
	$button[1]->task = 'showLinks';
	showButton ($button);
	
	$selection = selection();
	HTML_visohotlink::massAction($cid, $selection);
}

function massActionSave($cid) {
	global $database, $mosConfig_live_site;
	
	$link_type = mosGetParam( $_REQUEST, 'link_type', '' );
	$answer_type = mosGetParam( $_REQUEST, 'answer_type', '' );
	
	$replace_file = mosGetParam( $_REQUEST, 'replace_file', '' );	
	$redirect_url = mosGetParam( $_REQUEST, 'redirect_url', '' );
	
	// remove all selected links
	for ($i=0, $n=count($cid); $i < $n; $i++) {
		$visohotlink = new visohotlink_link( $database );
		$visohotlink->id = $cid[$i];
		echo '<br />' . $i;
		if ($link_type!='') {
			$visohotlink->link_type = $link_type;
			echo '1';
		}
		if ($answer_type!='') {
			$visohotlink->answer_type = $answer_type;
			echo '2';
		}
		if ($replace_file!='') {
			$visohotlink->replace_file = $replace_file;
			echo '3';
		}
		if ($redirect_url!='') {
			$visohotlink->redirect_url  = $redirect_url;
			echo '4';
		}
		
		$visohotlink->store();
		
		$visohotlink->checkin();		
	}
	
	// redirect to the links list with an error message if needed
	mosRedirect( $mosConfig_live_site.'/index.php?' . selection(), _VH_ACTION_SUCCESS);
}

// save settings
function saveConfig () {
	global $mosConfig_absolute_path, $mosConfig_live_site;
	
	include($mosConfig_absolute_path . "/config/config.visohotlink.php");
	
	// look if the config file is writable
	$configfile = $mosConfig_absolute_path . "/config/config.visohotlink.php";
	@chmod ($configfile, 0766);
	$permission = is_writable($configfile);
	if (!$permission) {
		mosRedirect($mosConfig_live_site."/index.php?task=config", _VH_CONFIG_ERROR );
		break;
	}
	
	// look for some parameters
	$mosConfig_lang 	= mosGetParam( $_REQUEST, 'language', 'french' );
	$link_type = mosGetParam( $_REQUEST, 'link_type', 'default' );
	$answer_type = mosGetParam( $_REQUEST, 'answer_type', 'sendpic' );
	
	$url_replace_jpeg = mosGetParam( $_REQUEST, 'url_replace_jpeg', '' );
	$url_replace_audio_mpeg = mosGetParam( $_REQUEST, 'url_replace_audio_mpeg', '' );
	$url_replace_video_mpeg = mosGetParam( $_REQUEST, 'url_replace_video_mpeg', '' );
	$url_replace_gif = mosGetParam( $_REQUEST, 'url_replace_gif', '' );
	$url_replace_png = mosGetParam( $_REQUEST, 'url_replace_png', '' );
	$url_replace_avi = mosGetParam( $_REQUEST, 'url_replace_avi', '' );
	$url_replace_mov = mosGetParam( $_REQUEST, 'url_replace_mov', '' );
	
	$ignored_sites = mosGetParam( $_REQUEST, 'ignored_sites', '' );
	$ignored_files = mosGetParam( $_REQUEST, 'ignored_files', '' );
	$entries_per_page = mosGetParam( $_REQUEST, 'entries_per_page', '30' );
	$activ_alert = mosGetParam( $_REQUEST, 'activ_alert', 'No' );
	$alert_threshold = mosGetParam( $_REQUEST, 'alert_threshold', '10' );
	$email = mosGetParam( $_REQUEST, 'email', '' );
	
	$mosConfig_mailer = mosGetParam( $_REQUEST, 'mosConfig_mailer', 'mail' );
	$mosConfig_mailfrom = mosGetParam( $_REQUEST, 'mosConfig_mailfrom', '' );
	$mosConfig_smtpauth = mosGetParam( $_REQUEST, 'mosConfig_smtpauth', '0' );
	$mosConfig_smtpuser = mosGetParam( $_REQUEST, 'mosConfig_smtpuser', '' );
	$mosConfig_smtppass = mosGetParam( $_REQUEST, 'mosConfig_smtppass', '' );
	$mosConfig_smtphost = mosGetParam( $_REQUEST, 'mosConfig_smtphost', '' );
	$mosConfig_sendmail = mosGetParam( $_REQUEST, 'mosConfig_sendmail', '' );
	
	$show_logo = mosGetParam( $_REQUEST, 'show_logo', 'Yes' );
	$url_site = mosGetParam( $_REQUEST, 'url_site', '' );
	$redirect_engine = mosGetParam( $_REQUEST, 'redirect_engine', 'No' );
	$redirect_url = mosGetParam( $_REQUEST, 'redirect_url', '' );
	
	// build the file content
	$config = "<?php\n";
	
	$config .= "\$host = '" . $host . "';\n";
	$config .= "\$login = '" . $login . "';\n";
	$config .= "\$password = '" . $password . "';\n";
	$config .= "\$base = '" . $base . "';\n";
	$config .= "\$prefix_table = '".$prefix_table."';\n";
	
	$config .= "\$mosConfig_absolute_path = '" . $mosConfig_absolute_path . "';\n";  
	$config .= "\$mosConfig_live_site = '" . $mosConfig_live_site . "';\n";
	$config .= "\$mosConfig_lang = '" . $mosConfig_lang . "';\n";
	$config .= "\$skin = '" . $skin . "';\n";
	$config .= "\$visohotlinkUser = '" . $visohotlinkUser . "';\n";
	$config .= "\$visohotlinkPass = '" . $visohotlinkPass . "';\n";
	
	$config .= "\$link_type = '" . $link_type . "';\n";
	$config .= "\$answer_type = '" . $answer_type . "';\n";
	$config .= "\$ignored_sites = '" . $ignored_sites . "';\n";
	$config .= "\$ignored_files = '" . $ignored_files . "';\n";
	
	$config .= "\$redirect_url = '" . $redirect_url . "';\n";
	$config .= "\$url_replace_jpeg = '" . $url_replace_jpeg . "';\n";
	$config .= "\$url_replace_audio_mpeg = '" . $url_replace_audio_mpeg . "';\n";
	$config .= "\$url_replace_video_mpeg = '" . $url_replace_video_mpeg . "';\n";
	$config .= "\$url_replace_gif = '" . $url_replace_gif . "';\n";
	$config .= "\$url_replace_png = '" . $url_replace_png . "';\n";
	$config .= "\$url_replace_avi = '" . $url_replace_avi . "';\n";
	$config .= "\$url_replace_mov = '" . $url_replace_mov . "';\n";
	
	
	$config .= "\$entries_per_page = '" . $entries_per_page . "';\n";
	
	$config .= "\$activ_alert = '" . $activ_alert . "';\n";
	$config .= "\$alert_threshold = '" . $alert_threshold . "';\n";
	$config .= "\$email = '" . $email . "';\n";
	
	$config .= "\$mosConfig_mailer = '" . $mosConfig_mailer . "';\n";
	$config .= "\$mosConfig_mailfrom = '" . $mosConfig_mailfrom . "';\n";
	$config .= "\$mosConfig_smtpauth = '" . $mosConfig_smtpauth . "';\n";
	$config .= "\$mosConfig_smtpuser = '" . $mosConfig_smtpuser . "';\n";
	$config .= "\$mosConfig_smtppass = '" . $mosConfig_smtppass . "';\n";
	$config .= "\$mosConfig_smtphost = '" . $mosConfig_smtphost . "';\n";
	$config .= "\$mosConfig_sendmail = '" . $mosConfig_sendmail . "';\n";
	
	$config .= "\$show_logo = '" . $show_logo . "';\n";
	$config .= "\$url_site = '" . $url_site . "';\n";
	$config .= "\$redirect_engine = '" . $redirect_engine . "';\n";
		
	$config .= "?>";
	
	// save the file content
	if ($fp = fopen("$configfile", "w")) {
		fputs($fp, $config, strlen($config));
		fclose ($fp);
	}
	//redirect to the config form
	mosRedirect($mosConfig_live_site."/index.php", _VH_CONFIG_SUCCESS );
		
}

// deconnect user
function deconnect () {
	global $mosConfig_live_site;
	
	$_SESSION['userok'] = '0';
	mosRedirect( $mosConfig_live_site.'/index.php' );
}

// build a select list with MySQL Database
function buildlist ( $name , $value_selected, $field, $referer_type = '', $table='site') {
	global $database;
	
	// begin the select list
	$list = "\n <select name='".$name."' onchange='document.adminForm.submit();' >";
	
	$query = "SELECT DISTINCT $field"
			. "\n FROM #__visohotlink_$table"
			;
	if ($referer_type!='') $query .= "\n WHERE referer_type = '$referer_type'";
	$query .= "\n ORDER by $field ASC";
	
	// if nothing is selected
	if ($value_selected=="") $list .= "\n <option value='' selected='selected' ></option>";
	else $list .= "\n <option value='' ></option>";
	
	// compute the query				
	$database->setQuery( $query );
	$results = $database->loadObjectList();
	
	// add an option for each result
	foreach ($results as $result) {
		if ($result->$field==$value_selected) $list .= "\n <option value='".$result->$field."' selected='selected' >".substr($result->$field, 0, 100)."</option>";
		else $list .= "\n <option value='".$result->$field."' >".substr($result->$field, 0, 100)."</option>";
	}
	// close the select list
	$list .= "\n </select>";
	
	// output
	return $list;
}

// return parameters to pass in url
function selection () {
	
	$limit 		= mosGetParam( $_REQUEST, 'limit', '30' );	
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', '0' );
	
	$group_by_file = mosGetParam( $_REQUEST, 'group_by_file', '' );
	$group_by_host = mosGetParam( $_REQUEST, 'group_by_host', '' );
	$group_by_site_referer = mosGetParam( $_REQUEST, 'group_by_site_referer', '' );
	$group_by_answer_type = mosGetParam( $_REQUEST, 'group_by_answer_type', '' );
	$group_by_link_type = mosGetParam( $_REQUEST, 'group_by_link_type', '' );
	$group_by_mime_type = mosGetParam( $_REQUEST, 'group_by_mime_type', '' );
	$group_by_referer_type = mosGetParam( $_REQUEST, 'group_by_referer_type', '' );
	$sort_by = mosGetParam( $_REQUEST, 'sort_by', 'host' );
	$ascdesc = mosGetParam( $_REQUEST, 'ascdesc', 'ASC' );
	
	return "limit=" . $limit . "&limitstart=" . $limitstart . "&group_by_file=" . urlencode($group_by_file) . "&group_by_host=" . urlencode($group_by_host) . "&group_by_site_referer=" . urlencode($group_by_site_referer) . "&group_by_referer=" . urlencode($group_by_referer) . "&group_by_answer_type=" . $group_by_answer_type . "&group_by_link_type=" . $group_by_link_type . "&group_by_mime_type=" . $group_by_mime_type . "&group_by_referer_type=" . $group_by_referer_type . "&sort_by=" . $sort_by . "&ascdesc=" . $ascdesc ;
	
}

// return parameters to pass in url for sites
function selection_site () {
	
	$limit 		= mosGetParam( $_REQUEST, 'limit', '30' );	
	$limitstart = mosGetParam( $_REQUEST, 'limitstart', '0' );

	$group_by_answer_type = mosGetParam( $_REQUEST, 'group_by_answer_type', '' );
	$group_by_link_type = mosGetParam( $_REQUEST, 'group_by_link_type', '' );
	$sort_by = mosGetParam( $_REQUEST, 'sort_by', 'date' );
	$ascdesc = mosGetParam( $_REQUEST, 'ascdesc', 'DESC' );
	
	return "limit=" . $limit . "&limitstart=" . $limitstart . "&group_by_answer_type=" . $group_by_answer_type . "&group_by_link_type=" . $group_by_link_type. "&sort_by=" . $sort_by . "&ascdesc=" . $ascdesc ;
	
}

function showbutton($buttons) {
	echo '<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="menudottedline" align="right">
				<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
				<tr valign="middle" align="center">';
	
	foreach ($buttons as $button) {
		switch ($button->type) {		
			case 'edit':
				HTML_visohotlink::button('/images/edit_f2.png', $button->task, _VH_SELECT_EDIT_LIST, _VH_EDIT);
				break;
				
			case 'delete':
				HTML_visohotlink::button('/images/delete_f2.png', $button->task, _VH_SELECT_DELETE_LIST, _VH_DELETE);
				break;
			
			case 'cancel':
				HTML_visohotlink::button2('/images/cancel_f2.png', $button->task, _VH_CANCEL);
				break;
			
			case 'save':
				HTML_visohotlink::button2('/images/save_f2.png', $button->task, _VH_SAVE);
				break;
			
			case 'action':
				HTML_visohotlink::button('/images/move_f2.png', $button->task, _VH_SELECT_ACTION_LIST, _VH_ACTION);
				break;
			
			default:
				break;
		}
	}
	
	echo '</tr>
		</table>
		</td>
		</tr>

		</table>';
}

?>