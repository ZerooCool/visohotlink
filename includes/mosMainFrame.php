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
* Joomla! Mainframe class
*
* Provide many supporting API functions
* @package Joomla
*/
class mosMainFrame {
	/** @var database Internal database class pointer */
	var $_db						= null;
	/** @var object An object of configuration variables */
	var $_config					= null;
	/** @var object An object of path variables */
	var $_path						= null;
	/** @var mosSession The current session */
	var $_session					= null;
	/** @var string The current template */
	var $_template					= null;
	/** @var array An array to hold global user state within a session */
	var $_userstate					= null;
	/** @var array An array of page meta information */
	var $_head						= null;
	/** @var string Custom html string to append to the pathway */
	var $_custom_pathway			= null;
	/** @var boolean True if in the admin client */
	var $_isAdmin 					= false;	
	

	/**
	* Class constructor
	* @param database A database connection object
	* @param string The url option
	* @param string The path of the mos directory
	*/
	function mosMainFrame( &$db, $option, $basePath, $isAdmin=false ) {
		$this->_db =& $db;

		// load the configuration values
		$this->_setTemplate( $isAdmin );
		$this->_setAdminPaths( $option, $this->getCfg( 'absolute_path' ) );
		if (isset( $_SESSION['session_userstate'] )) {
			$this->_userstate =& $_SESSION['session_userstate'];
		} else {
			$this->_userstate = null;
		}
		$this->_head = array();
		$this->_head['title'] 	= $GLOBALS['mosConfig_sitename'];
		$this->_head['meta'] 	= array();
		$this->_head['custom'] 	= array();

		//set the admin check
		$this->_isAdmin 		= (boolean) $isAdmin;
		
		$now = date( 'Y-m-d H:i:s', time() );
		$this->set( 'now', $now );
	}

	/**
	 * Gets the id number for a client
	 * @param mixed A client identifier
	 */
	function getClientID( $client ) {
		switch ($client) {
			case '2':
			case 'installation':
				return 2;
				break;

			case '1':
			case 'admin':
			case 'administrator':
				return 1;
				break;

			case '0':
			case 'site':
			case 'front':
			default:
				return 0;
				break;
		}
	}

	/**
	 * Gets the client name
	 * @param int The client identifier
	 * @return strint The text name of the client
	 */
	function getClientName( $client_id ) {
		 // do not translate
		$clients = array( 'site', 'admin', 'installer' );
		return mosGetParam( $clients, $client_id, 'unknown' );
	}

	/**
	 * Gets the base path for the client
	 * @param mixed A client identifier
	 * @param boolean True (default) to add traling slash
	 */
	function getBasePath( $client=0, $addTrailingSlash=true ) {
		global $mosConfig_absolute_path;

		switch ($client) {
			case '0':
			case 'site':
			case 'front':
			default:
				return mosPathName( $mosConfig_absolute_path, $addTrailingSlash );
				break;

			case '2':
			case 'installation':
				return mosPathName( $mosConfig_absolute_path . '/installation', $addTrailingSlash );
				break;

			case '1':
			case 'admin':
			case 'administrator':
				return mosPathName( $mosConfig_absolute_path . '/administrator', $addTrailingSlash );
				break;

		}
	}

	/**
	* @param string
	*/
	function setPageTitle( $title=null ) {
		if (@$GLOBALS['mosConfig_pagetitles']) {
			$title = trim( htmlspecialchars( $title ) );
			$title = stripslashes($title);
			$this->_head['title'] = $title ? $GLOBALS['mosConfig_sitename'] . ' - '. $title : $GLOBALS['mosConfig_sitename'];
		}
	}
	/**
	* @param string The value of the name attibute
	* @param string The value of the content attibute
	* @param string Text to display before the tag
	* @param string Text to display after the tag
	*/
	function addMetaTag( $name, $content, $prepend='', $append='' ) {
		$name = trim( htmlspecialchars( $name ) );
		$content = trim( htmlspecialchars( $content ) );
		$prepend = trim( $prepend );
		$append = trim( $append );
		$this->_head['meta'][] = array( $name, $content, $prepend, $append );
	}
	
	/** Modifié par Visoterra
	* @param string The value of the name attibute
	* @param string The value of the content attibute to append to the existing
	* Tags ordered in with Site Keywords and Description first
	*/
	function appendMetaTag( $name, $content ) {
		$name = trim( htmlspecialchars( $name ) );
		$n = count( $this->_head['meta'] );
		for ($i = 0; $i < $n; $i++) {
			if ($this->_head['meta'][$i][0] == $name) {
				$content = trim( htmlspecialchars( $content ) );
				if ( $content ) {
					if ( !$this->_head['meta'][$i][1] ) {
						$this->_head['meta'][$i][1] = $content ;
					} else {
						$this->_head['meta'][$i][1] = $this->_head['meta'][$i][1] .' '. $content;
					}
				}
				return;
			}
		}
		$this->addMetaTag( $name , $content );
	}

	/**
	* @param string The value of the name attibute
	* @param string The value of the content attibute to append to the existing
	*/
	function prependMetaTag( $name, $content ) {
		$name = trim( htmlspecialchars( $name ) );
		$n = count( $this->_head['meta'] );
		for ($i = 0; $i < $n; $i++) {
			if ($this->_head['meta'][$i][0] == $name) {
				$content = trim( htmlspecialchars( $content ) );
				$this->_head['meta'][$i][1] = $content . $this->_head['meta'][$i][1];
				return;
			}
		}
		$this->addMetaTag( $name, $content );
	}
	/**
	 * Adds a custom html string to the head block
	 * @param string The html to add to the head
	 */
	function addCustomHeadTag( $html ) {
		$this->_head['custom'][] = trim( $html );
	}
	/**
	* @return string
	*/
	function getHead() {
		$head = array();
		$head[] = '<title>' . $this->_head['title'] . '</title>';
		foreach ($this->_head['meta'] as $meta) {
			if ($meta[2]) {
				$head[] = $meta[2];
			}
			$head[] = '<meta name="' . $meta[0] . '" content="' . $meta[1] . '" />';
			if ($meta[3]) {
				$head[] = $meta[3];
			}
		}
		foreach ($this->_head['custom'] as $html) {
			$head[] = $html;
		}
		return implode( "\n", $head ) . "\n";
	}
	
	
	/**
	* @return string
	*/
	function getPageTitle() {
		return $this->_head['title'];
	}

	/**
	* @return string
	*/
	function getCustomPathWay() {
		return $this->_custom_pathway;
	}

	function appendPathWay( $html ) {
	$this->_custom_pathway[] = $html;
  }

  /**
	* Gets the value of a user state variable
	* @param string The name of the variable
	*/
	function getUserState( $var_name ) {
		if (is_array( $this->_userstate )) {
			return mosGetParam( $this->_userstate, $var_name, null );
		} else {
			return null;
		}
	}
	/**
	* Gets the value of a user state variable
	* @param string The name of the user state variable
	* @param string The name of the variable passed in a request
	* @param string The default value for the variable if not found
	*/
	function getUserStateFromRequest( $var_name, $req_name, $var_default=null ) {
		if (is_array( $this->_userstate )) {
			if (isset( $_REQUEST[$req_name] )) {
				$this->setUserState( $var_name, $_REQUEST[$req_name] );
			} else if (!isset( $this->_userstate[$var_name] )) {
				$this->setUserState( $var_name, $var_default );
			}			
			
			// filter input
			$iFilter = new InputFilter();			
			$this->_userstate[$var_name] = $iFilter->process( $this->_userstate[$var_name] );
			
			return $this->_userstate[$var_name];
		} else {
			return null;
		}
	}
	/**
	* Sets the value of a user state variable
	* @param string The name of the variable
	* @param string The value of the variable
	*/
	function setUserState( $var_name, $var_value ) {
		if (is_array( $this->_userstate )) {
			$this->_userstate[$var_name] = $var_value;
		}
	}
	/**
	* Initialises the user session
	*
	* Old sessions are flushed based on the configuration value for the cookie
	* lifetime. If an existing session, then the last access time is updated.
	* If a new session, a session id is generated and a record is created in
	* the jos_sessions table.
	*/
	function initSession() {
		// initailize session variables
		$session 	=& $this->_session;
		$session 	= new mosSession( $this->_db );
		
		// purge expired sessions
		$session->purge('core');

		// Session Cookie `name`
		$sessionCookieName 	= mosMainFrame::sessionCookieName();
		// Get Session Cookie `value`
		$sessioncookie 		= strval( mosGetParam( $_COOKIE, $sessionCookieName, null ) );
		
		// Session ID / `value`
		$sessionValueCheck 	= mosMainFrame::sessionCookieValue( $sessioncookie );

		// Check if existing session exists in db corresponding to Session cookie `value` 
		// extra check added in 1.0.8 to test sessioncookie value is of correct length
		if ( $sessioncookie && strlen($sessioncookie) == 32 && $sessioncookie != '-' && $session->load($sessionValueCheck) ) {
			// update time in session table
			$session->time = time();
			$session->update();
		} else {
			// Remember Me Cookie `name`
			$remCookieName = mosMainFrame::remCookieName_User();
			
			// test if cookie found
			$cookie_found = false;
			if ( isset($_COOKIE[$sessionCookieName]) || isset($_COOKIE[$remCookieName]) || isset($_POST['force_session']) ) {
				$cookie_found = true;
			}
			
			// check if neither remembermecookie or sessioncookie found
			if (!$cookie_found) {
				// create sessioncookie and set it to a test value set to expire on session end
				setcookie( $sessionCookieName, '-', false, '/' );				
			} else {
			// otherwise, sessioncookie was found, but set to test val or the session expired, prepare for session registration and register the session
				$url = strval( mosGetParam( $_SERVER, 'REQUEST_URI', null ) );
				// stop sessions being created for requests to syndicated feeds
				if ( strpos( $url, 'option=com_rss' ) === false && strpos( $url, 'feed=' ) === false ) {
					$session->guest 	= 1;
					$session->username 	= '';
					$session->time 		= time();
					$session->gid 		= 0;
					// Generate Session Cookie `value`
					$session->generateId();
					
					if (!$session->insert()) {
						die( $session->getError() );
					}
					
					// create Session Tracking Cookie set to expire on session end
					setcookie( $sessionCookieName, $session->getCookie(), false, '/' );
				}				
			}

			// Cookie used by Remember me functionality
			$remCookieValue	= strval( mosGetParam( $_COOKIE, $remCookieName, null ) );
			
			// test if cookie is correct length			
			if ( strlen($remCookieValue) > 64 ) {
				// Separate Values from Remember Me Cookie
				$remUser	= substr( $remCookieValue, 0, 32 );
				$remPass	= substr( $remCookieValue, 32, 32 );
				$remID		= intval( substr( $remCookieValue, 64  ) );

				// check if Remember me cookie exists. Login with usercookie info.
				if ( strlen($remUser) == 32 && strlen($remPass) == 32 ) {
					$this->login( $remUser, $remPass, 1, $remID );
				}
			}
		}
	}
	
	/*
	* Function used to conduct admin session duties
	* Added as of 1.0.8
	* Deperciated 1.1
	*/
	function initSessionAdmin($option, $task) {	
		global $_VERSION, $mosConfig_admin_expired;
		
		// logout check
		if ($option == 'logout') {
			require $GLOBALS['mosConfig_absolute_path'] .'/administrator/logout.php';
			exit();
		}
		
		$site = $GLOBALS['mosConfig_live_site'];
		
		// check if session name corresponds to correct format
		if ( session_name() != md5( $site ) ) {
			echo "<script>document.location.href='index.php'</script>\n";
			exit();
		}

		// restore some session variables
		$my 			= new mosUser( $this->_db );
		$my->id 		= intval( mosGetParam( $_SESSION, 'session_user_id', '' ) );
		$my->username 	= strval( mosGetParam( $_SESSION, 'session_username', '' ) );
		$my->usertype 	= strval( mosGetParam( $_SESSION, 'session_usertype', '' ) );
		$my->gid 		= intval( mosGetParam( $_SESSION, 'session_gid', '' ) );
		$my->params		= mosGetParam( $_SESSION, 'session_user_params', '' );

		$session_id 	= mosGetParam( $_SESSION, 'session_id', '' );
		$logintime 		= mosGetParam( $_SESSION, 'session_logintime', '' );

		// check to see if session id corresponds with correct format
		if ( $session_id == md5( $my->id . $my->username . $my->usertype . $logintime ) ) {
			// if task action is to `save` or `apply` complete action before doing session checks.
			if ($task != 'save' && $task != 'apply') {
				// test for session_life_admin
				if ( @$GLOBALS['mosConfig_session_life_admin'] ) {
					$session_life_admin = $GLOBALS['mosConfig_session_life_admin'];
				} else {
					$session_life_admin = 1800;
				}
				
				// purge expired admin sessions only		
				$past = time() - $session_life_admin;
				$query = "DELETE FROM #__session"
				. "\n WHERE time < '$past'"
				. "\n AND guest = 1"
				. "\n AND gid = 0"
				. "\n AND userid <> 0"
				;
				$this->_db->setQuery( $query );
				$this->_db->query();	
				
				// update session timestamp
				$current_time = time();
				$query = "UPDATE #__session"
				. "\n SET time = '$current_time'"
				. "\n WHERE session_id = '$session_id'"
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
				
				// set garbage cleaning timeout
				$this->setSessionGarbageClean();
				
				// check against db record of session
				$query = "SELECT COUNT( session_id )"
				. "\n FROM #__session"
				. "\n WHERE session_id = '$session_id'"
				. "\n AND username = ". $this->_db->Quote( $my->username )
				. "\n AND userid = ". intval( $my->id )
				;
				$this->_db->setQuery( $query );
				$count = $this->_db->loadResult();
				
				// if no entry in session table that corresponds boot from admin area
				if ( $count == 0 ) {
					$link 	= NULL;
					
					if ($_SERVER['QUERY_STRING']) {
						$link = 'index2.php?'. $_SERVER['QUERY_STRING'];
					}
					
					// check if site designated as a production site 
					// for a demo site disallow expired page functionality
					// link must also be a Joomla link to stop malicious redirection
					if ( $link && strpos( $link, 'index2.php?option=com_' ) === 0 && $_VERSION->SITE == 1 && @$mosConfig_admin_expired === '1' ) {
						$now 	= time();
						
						$file 	= $this->getPath( 'com_xml', 'com_users' );
						$params =& new mosParameters( $my->params, $file, 'component' );
						
						// return to expired page functionality
						$params->set( 'expired', 		$link );
						$params->set( 'expired_time', 	$now );

						// param handling
						if (is_array( $params->toArray() )) {
							$txt = array();
							foreach ( $params->toArray() as $k=>$v) {
								$txt[] = "$k=$v";
							}
							$saveparams = implode( "\n", $txt );
						}
						
						// save expired page info to user data
						$query = "UPDATE #__users"
						. "\n SET params = '$saveparams'"
						. "\n WHERE id = $my->id"
						. "\n AND username = '$my->username'"
						. "\n AND usertype = '$my->usertype'"
						;
						$this->_db->setQuery( $query );
						$this->_db->query();
					}

					echo "<script>document.location.href='index.php?mosmsg=Session Admin expirée'</script>\n";
					exit();
				}
			}
		} else if ($session_id == '') {
			// no session_id as user has person has not attempted to login
			echo "<script>document.location.href='index.php?mosmsg=Vous devez vous identifier'</script>\n";
			exit();
		} else {
			// session id does not correspond to required session format
			echo "<script>document.location.href='index.php?mosmsg=Session Invalide'</script>\n";
			exit();
		}

		return $my;
	}
	
	/*
	* Function used to set Session Garbage Cleaning
	* garbage cleaning set at configured session time + 600 seconds
	* Added as of 1.0.8
	* Deperciated 1.1
	*/
	function setSessionGarbageClean() {
		/** ensure that funciton is only called once */
		if (!defined( '_JOS_GARBAGECLEAN' )) {
			define( '_JOS_GARBAGECLEAN', 1 );
			
			$garbage_timeout = $this->getCfg('session_life_admin') + 600;
			@ini_set('session.gc_maxlifetime', $garbage_timeout);
		}
	}
	
	/*
	* Static Function used to generate the Session Cookie Name
	* Added as of 1.0.8
	* Deperciated 1.1
	*/
	function sessionCookieName() {
		global $mainframe;
		
		return md5( 'site' . $mainframe->getCfg( 'live_site' ) );		
	}
	
	/*
	* Static Function used to generate the Session Cookie Value
	* Added as of 1.0.8
	* Deperciated 1.1
	*/
	function sessionCookieValue( $id=null ) {
		global $mainframe;		
	
		$type 		= $mainframe->getCfg( 'session_type' );
		
		$browser 	= @$_SERVER['HTTP_USER_AGENT'];
		
		switch ($type) {
			case 2:
			// 1.0.0 to 1.0.7 Compatibility
			// lowest level security
				$value 			= md5( $id . $_SERVER['REMOTE_ADDR'] );
				break;

			case 1:
			// slightly reduced security - 3rd level IP authentication for those behind IP Proxy 
				$remote_addr 	= explode('.',$_SERVER['REMOTE_ADDR']);
				$ip				= $remote_addr[0] .'.'. $remote_addr[1] .'.'. $remote_addr[2];
				$value 			= mosHash( $id . $ip . $browser );
				break;
			
			default:
			// Highest security level - new default for 1.0.8 and beyond
				$ip				= $_SERVER['REMOTE_ADDR'];
				$value 			= mosHash( $id . $ip . $browser );
				break;
		}		

		return $value;
	}
	
	/*
	* Static Function used to generate the Rememeber Me Cookie Name for Username information
	* Added as of 1.0.8
	* Depreciated 1.1
	*/
	function remCookieName_User() {
		$value = mosHash( 'remembermecookieusername'. mosMainFrame::sessionCookieName() );

		return $value;
	}
	
	/*
	* Static Function used to generate the Rememeber Me Cookie Name for Password information
	* Added as of 1.0.8
	* Depreciated 1.1
	*/
	function remCookieName_Pass() {
		$value = mosHash( 'remembermecookiepassword'. mosMainFrame::sessionCookieName() );

		return $value;
	}
	
	/*
	* Static Function used to generate the Remember Me Cookie Value for Username information
	* Added as of 1.0.8
	* Depreciated 1.1
	*/
	function remCookieValue_User( $username ) {
		$value = md5( $username . mosHash( @$_SERVER['HTTP_USER_AGENT'] ) );

		return $value;
	}
	
	/*
	* Static Function used to generate the Remember Me Cookie Value for Password information
	* Added as of 1.0.8
	* Depreciated 1.1
	*/
	function remCookieValue_Pass( $passwd ) {
		$value 	= md5( $passwd . mosHash( @$_SERVER['HTTP_USER_AGENT'] ) );
		
		return $value;
	}
	
	/**
	* Login validation function
	*
	* Username and encoded password is compare to db entries in the jos_users
	* table. A successful validation updates the current session record with
	* the users details.
	*/
	function login( $username=null,$passwd=null, $remember=0, $userid=NULL ) {
		global $acl, $_VERSION;
		
		$bypost = 0;
		
		// if no username and password passed from function, then function is being called from login module/component
		if (!$username || !$passwd) {
			$username 	= strval( mosGetParam( $_POST, 'username', '' ) );
			$passwd 	= strval( mosGetParam( $_POST, 'passwd', '' ) );
			$passwd 	= md5( $passwd );
			
			$bypost 	= 1;
			
			// Session Cookie `name`
			$sessionCookieName 	= mosMainFrame::sessionCookieName();
			// Get Session Cookie `value`
			$sessioncookie 		= strval( mosGetParam( $_COOKIE, $sessionCookieName, null ) );
			// extra check to ensure that Joomla! sessioncookie exists			
			if (!($sessioncookie == '-' || strlen($sessioncookie) == 32)) {
				header( 'HTTP/1.0 403 Forbidden' );
				mosErrorAlert( _NOT_AUTH );
				return;
			}
			
			josSpoofCheck(NULL,1);
		}

		$row = null;
		if (!$username || !$passwd) {
			echo "<script> alert(\""._LOGIN_INCOMPLETE."\"); window.history.go(-1); </script>\n";
			exit();
		} else {
			if ( $remember && strlen($username) == 32 && strlen($passwd) == 32 && $userid ) {
			// query used for remember me cookie
				$harden = mosHash( @$_SERVER['HTTP_USER_AGENT'] );
				
				$query = "SELECT id, name, username, password, usertype, block, gid"
				. "\n FROM #__users"
				. "\n WHERE id = $userid"
				;
				$this->_db->setQuery( $query );
				$this->_db->loadObject($user);
				
				$check_username = md5( $user->username . $harden );
				$check_password = md5( $user->password . $harden );

				if ( $check_username == $username && $check_password == $passwd ) {
					$row = $user;
				}				
			} else {
			// query used for login via login module
				$query = "SELECT id, name, username, password, usertype, block, gid"
				. "\n FROM #__users"
				. "\n WHERE username = '$username'"
				. "\n AND password = '$passwd'"
				;
				$this->_db->setQuery( $query );
				$this->_db->loadObject( $row );
			}
			
			if (is_object($row)) {
				// user blocked from login
				if ($row->block == 1) {
					mosErrorAlert(_LOGIN_BLOCKED);
				}
				
				// fudge the group stuff
				$grp = $acl->getAroGroup( $row->id );
				$row->gid = 1;
				if ($acl->is_group_child_of( $grp->name, 'Registered', 'ARO' ) || $acl->is_group_child_of( $grp->name, 'Public Backend', 'ARO' )) {
					// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
					$row->gid = 2;
				}
				$row->usertype = $grp->name;

				// initialize session data
				$session 			=& $this->_session;
				$session->guest 	= 0;
				$session->username 	= $row->username;
				$session->userid 	= intval( $row->id );
				$session->usertype 	= $row->usertype;
				$session->gid 		= intval( $row->gid );
				$session->update();
				
				// check to see if site is a production site
				// allows multiple logins with same user for a demo site
				if ( $_VERSION->SITE ) {
					// delete any old front sessions to stop duplicate sessions
					$query = "DELETE FROM #__session"
					. "\n WHERE session_id != '$session->session_id'"
					. "\n AND username = '$row->username'"
					. "\n AND userid = $row->id"
					. "\n AND gid = $row->gid"
					. "\n AND guest = 0"
					;
					$this->_db->setQuery( $query );
					$this->_db->query();	
				}
				
				// update user visit data
				$currentDate = date("Y-m-d\TH:i:s");
				
				$query = "UPDATE #__users"
				. "\n SET lastvisitDate = '$currentDate'"
				. "\n WHERE id = $session->userid"
				;
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					die($this->_db->stderr(true));
				}

				// set remember me cookie if selected
				$remember = strval( mosGetParam( $_POST, 'remember', '' ) );
				if ( $remember == 'yes' ) {
					// cookie lifetime of 365 days
					$lifetime 		= time() + 365*24*60*60;
					$remCookieName 	= mosMainFrame::remCookieName_User();
					$remCookieValue = mosMainFrame::remCookieValue_User( $row->username ) . mosMainFrame::remCookieValue_Pass( $row->password ) . $row->id;
					setcookie( $remCookieName, $remCookieValue, $lifetime, '/' );
				}
				mosCache::cleanCache();
			} else {
				if ( $bypost ) {
					mosErrorAlert(_LOGIN_INCORRECT);
				} else {
					$this->logout();
					mosRedirect($mosConfig_live_site.'/index.php');
				}
				exit();
			}
		}
	}
	
	/**
	* User logout
	*
	* Reverts the current session record back to 'anonymous' parameters
	*/
	function logout() {
		mosCache::cleanCache();
		
		$session 			=& $this->_session;
		$session->guest 	= 1;
		$session->username 	= '';
		$session->userid 	= '';
		$session->usertype 	= '';
		$session->gid 		= 0;

		$session->update();

		// kill remember me cookie
		$lifetime 		= time() - 86400;
		$remCookieName 	= mosMainFrame::remCookieName_User();
		setcookie( $remCookieName, ' ', $lifetime, '/' );
		
		@session_destroy();
	}
	
	/**
	* @return mosUser A user object with the information from the current session
	*/
	function getUser() {
		global $database;

		$user = new mosUser( $this->_db );

		$user->id 			= intval( $this->_session->userid );
		$user->username 	= $this->_session->username;
		$user->usertype 	= $this->_session->usertype;
		$user->gid 			= intval( $this->_session->gid );

		if ($user->id) {
			$query = "SELECT id, name, email, block, sendEmail, registerDate, lastvisitDate, activation, params"
			. "\n FROM #__users"
			. "\n WHERE id = ". intval( $user->id )
			;
			$database->setQuery( $query );
			$database->loadObject( $my );
			
			$user->params 			= $my->params;
			$user->name				= $my->name;
			$user->email			= $my->email;
			$user->block			= $my->block;
			$user->sendEmail		= $my->sendEmail;
			$user->registerDate		= $my->registerDate;
			$user->lastvisitDate	= $my->lastvisitDate;
			$user->activation		= $my->activation;
		}

		return $user;
	}
	/**
	 * @param string The name of the variable (from configuration.php)
	 * @return mixed The value of the configuration variable or null if not found
	 */
	function getCfg( $varname ) {
		$varname = 'mosConfig_' . $varname;
		if (isset( $GLOBALS[$varname] )) {
			return $GLOBALS[$varname];
		} else {
			return null;
		}
	}

	function _setTemplate( $isAdmin=false ) {
		global $Itemid;
		$mosConfig_absolute_path = $this->getCfg( 'absolute_path' );

		if ($isAdmin) {
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = 1"
			. "\n AND menuid = 0"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult();
			$path = "$mosConfig_absolute_path/administrator/templates/$cur_template/index.php";
			if (!file_exists( $path )) {
				$cur_template = 'joomla_admin';
			}
		} else {
			$assigned = ( !empty( $Itemid ) ? " OR menuid = $Itemid" : '' );

			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = 0"
			. "\n AND ( menuid = 0 $assigned )"
			. "\n ORDER BY menuid DESC"
			. "\n LIMIT 1"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult();

			// TemplateChooser Start
			$jos_user_template 		= strval( mosGetParam( $_COOKIE, 'jos_user_template', '' ) );
			$jos_change_template 	= strval( mosGetParam( $_REQUEST, 'jos_change_template', $jos_user_template ) );
			if ($jos_change_template) {
				// clean template name
				$jos_change_template = preg_replace( '#\W#', '', $jos_change_template );
				if ( strlen( $jos_change_template ) >= 40 ) {
					$jos_change_template = substr($jos_change_template, 0 , 39);
				}
				
				// check that template exists in case it was deleted
				if (file_exists( $mosConfig_absolute_path .'/templates/'. $jos_change_template .'/index.php' )) {
					$lifetime 		= 60*10;
					$cur_template 	= $jos_change_template;
					setcookie( 'jos_user_template', "$jos_change_template", time()+$lifetime);
				} else {
					setcookie( 'jos_user_template', '', time()-3600 );
				}
			}
			// TemplateChooser End
		}

		$this->_template = $cur_template;
	}

	function getTemplate() {
		return $this->_template;
	}

	/**
	* Determines the paths for including engine and menu files
	* @param string The current option used in the url
	* @param string The base path from which to load the configuration file
	*/
	function _setAdminPaths( $option, $basePath='.' ) {
		$option = strtolower( $option );
		$this->_path = new stdClass();

		$prefix = substr( $option, 0, 4 );
		if ($prefix != 'com_') {
			// ensure backward compatibility with existing links
			$name = $option;
			$option = "com_$option";
		} else {
			$name = substr( $option, 4 );
		}
		// components
		if (file_exists( "$basePath/templates/$this->_template/components/$name.html.php" )) {
			$this->_path->front = "$basePath/components/$option/$name.php";
			$this->_path->front_html = "$basePath/templates/$this->_template/components/$name.html.php";
		} else if (file_exists( "$basePath/components/$option/$name.php" )) {
			$this->_path->front = "$basePath/components/$option/$name.php";
			$this->_path->front_html = "$basePath/components/$option/$name.html.php";
		}
		if (file_exists( "$basePath/administrator/components/$option/admin.$name.php" )) {
			$this->_path->admin = "$basePath/administrator/components/$option/admin.$name.php";
			$this->_path->admin_html = "$basePath/administrator/components/$option/admin.$name.html.php";
		}
		if (file_exists( "$basePath/administrator/components/$option/toolbar.$name.php" )) {
			$this->_path->toolbar = "$basePath/administrator/components/$option/toolbar.$name.php";
			$this->_path->toolbar_html = "$basePath/administrator/components/$option/toolbar.$name.html.php";
			$this->_path->toolbar_default = "$basePath/administrator/includes/toolbar.html.php";
		}
		if (file_exists( "$basePath/components/$option/$name.class.php" )) {
			$this->_path->class = "$basePath/components/$option/$name.class.php";
		} else if (file_exists( "$basePath/administrator/components/$option/$name.class.php" )) {
			$this->_path->class = "$basePath/administrator/components/$option/$name.class.php";
		} else if (file_exists( "$basePath/includes/$name.php" )) {
			$this->_path->class = "$basePath/includes/$name.php";
		}
		if (file_exists("$basePath/administrator/components/$option/admin.$name.php" )) {
			$this->_path->admin = "$basePath/administrator/components/$option/admin.$name.php";
			$this->_path->admin_html = "$basePath/administrator/components/$option/admin.$name.html.php";
		} else {
			$this->_path->admin = "$basePath/administrator/components/com_admin/admin.admin.php";
			$this->_path->admin_html = "$basePath/administrator/components/com_admin/admin.admin.html.php";
		}
	}
	/**
	* Returns a stored path variable
	*
	*/
	function getPath( $varname, $option='' ) {
		global $mosConfig_absolute_path;
		if ($option) {
			$temp = $this->_path;
			$this->_setAdminPaths( $option, $this->getCfg( 'absolute_path' ) );
		}
		$result = null;
		if (isset( $this->_path->$varname )) {
			$result = $this->_path->$varname;
		} else {
			switch ($varname) {
				case 'com_xml':
					$name = substr( $option, 4 );
					$path = "$mosConfig_absolute_path/administrator/components/$option/$name.xml";
					if (file_exists( $path )) {
						$result = $path;
					} else {
						$path = "$mosConfig_absolute_path/components/$option/$name.xml";
						if (file_exists( $path )) {
							$result = $path;
						}
					}
					break;

				case 'mod0_xml':
					// Site modules
					if ($option == '') {
						$path = $mosConfig_absolute_path . "/modules/custom.xml";
					} else {
						$path = $mosConfig_absolute_path . "/modules/$option.xml";
					}
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'mod1_xml':
					// admin modules
					if ($option == '') {
						$path = $mosConfig_absolute_path . '/administrator/modules/custom.xml';
					} else {
						$path = $mosConfig_absolute_path . "/administrator/modules/$option.xml";
					}
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'bot_xml':
					// Site mambots
					$path = $mosConfig_absolute_path . "/mambots/$option.xml";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'menu_xml':
					$path = $mosConfig_absolute_path . "/administrator/components/com_menus/$option/$option.xml";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'installer_html':
					$path = $mosConfig_absolute_path . "/administrator/components/com_installer/$option/$option.html.php";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'installer_class':
					$path = $mosConfig_absolute_path . "/administrator/components/com_installer/$option/$option.class.php";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;
			}
		}
		if ($option) {
			$this->_path = $temp;
		}
		return $result;
	}
	/**
	* Detects a 'visit'
	*
	* This function updates the agent and domain table hits for a particular
	* visitor.  The user agent is recorded/incremented if this is the first visit.
	* A cookie is set to mark the first visit.
	*/
	function detect() {
		global $mosConfig_enable_stats;
		if ($mosConfig_enable_stats == 1) {
			if (mosGetParam( $_COOKIE, 'mosvisitor', 0 )) {
				return;
			}
			setcookie( 'mosvisitor', 1 );

			if (phpversion() <= '4.2.1') {
				$agent = getenv( 'HTTP_USER_AGENT' );
				$domain = @gethostbyaddr( getenv( "REMOTE_ADDR" ) );
			} else {
				if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
					$agent = $_SERVER['HTTP_USER_AGENT'];
				} else {
					$agent = 'Unknown';
				}

				$domain = @gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
			}

			$browser = mosGetBrowser( $agent );

			$query = "SELECT COUNT(*)"
			. "\n FROM #__stats_agents"
			. "\n WHERE agent = '$browser'"
			. "\n AND type = 0"
			;
			$this->_db->setQuery( $query );
			if ($this->_db->loadResult()) {
				$query = "UPDATE #__stats_agents"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE agent = '$browser'"
				. "\n AND type = 0"
				;
				$this->_db->setQuery( $query );
			} else {
				$query = "INSERT INTO #__stats_agents"
				. "\n ( agent, type )"
				. "\n VALUES ( '$browser', 0 )"
				;
				$this->_db->setQuery( $query );
			}
			$this->_db->query();

			$os = mosGetOS( $agent );

			$query = "SELECT COUNT(*)"
			. "\n FROM #__stats_agents"
			. "\n WHERE agent = '$os'"
			. "\n AND type = 1"
			;
			$this->_db->setQuery( $query );
			if ($this->_db->loadResult()) {
				$query = "UPDATE #__stats_agents"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE agent = '$os'"
				. "\n AND type = 1"
				;
				$this->_db->setQuery( $query );
			} else {
				$query = "INSERT INTO #__stats_agents"
				. "\n ( agent, type )"
				. "\n VALUES ( '$os', 1 )"
				;
				$this->_db->setQuery( $query );
			}
			$this->_db->query();

			// tease out the last element of the domain
			$tldomain = split( "\.", $domain );
			$tldomain = $tldomain[count( $tldomain )-1];

			if (is_numeric( $tldomain )) {
				$tldomain = "Unknown";
			}

			$query = "SELECT COUNT(*)"
			. "\n FROM #__stats_agents"
			. "\n WHERE agent = '$tldomain'"
			. "\n AND type = 2"
			;
			$this->_db->setQuery( $query );
			if ($this->_db->loadResult()) {
				$query = "UPDATE #__stats_agents"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE agent = '$tldomain'"
				. "\n AND type = 2"
				;
				$this->_db->setQuery( $query );
			} else {
				$query = "INSERT INTO #__stats_agents"
				. "\n ( agent, type )"
				. "\n VALUES ( '$tldomain', 2 )"
				;
				$this->_db->setQuery( $query );
			}
			$this->_db->query();
		}
	}

	/**
	* @return correct Itemid for Content Item
	*/
	function getItemid( $id, $typed=1, $link=1, $bs=1, $bc=1, $gbs=1 ) {
		global $Itemid;
		
		$_Itemid = '';
		
		if ($_Itemid == '' && $typed && $this->getStaticContentCount()) {
			$exists = 0;
			foreach( $this->get( '_ContentTyped', array() ) as $key => $value ) {
				// check if id has been tested before, if it is pull from class variable store
				if ( $key == $id ) {
					$_Itemid 	= $value;
					$exists 	= 1;
					break;
				}
			}
			// if id hasnt been checked before initaite query
			if ( !$exists ) {				
				// Search for typed link
				$query = "SELECT id"
				. "\n FROM #__menu"
				. "\n WHERE type = 'content_typed'"
				. "\n AND published = 1"
				. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
				;
				$this->_db->setQuery( $query );
				// pull existing query storage into temp variable
				$ContentTyped 		= $this->get( '_ContentTyped', array() );
				// add query result to temp array storage
				$ContentTyped[$id] 	= $this->_db->loadResult();	
				// save temp array to main array storage
				$this->set( '_ContentTyped', $ContentTyped );
				
				$_Itemid = $ContentTyped[$id];				
			}
		}

		if ($_Itemid == '' && $link && $this->getContentItemLinkCount()) {
			$exists = 0;
			foreach( $this->get( '_ContentItemLink', array() ) as $key => $value ) {
			// check if id has been tested before, if it is pull from class variable store
				if ( $key == $id ) {
					$_Itemid 	= $value;
					$exists 	= 1;
					break;
				}
			}
			// if id hasnt been checked before initaite query
			if ( !$exists ) {				
				// Search for item link
				$query = "SELECT id"
				."\n FROM #__menu"
				."\n WHERE type = 'content_item_link'"
				. "\n AND published = 1"
				. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
				;
				$this->_db->setQuery( $query );
				// pull existing query storage into temp variable
				$ContentItemLink 		= $this->get( '_ContentItemLink', array() );
				// add query result to temp array storage
				$ContentItemLink[$id] 	= $this->_db->loadResult();	
				// save temp array to main array storage
				$this->set( '_ContentItemLink', $ContentItemLink );
				
				$_Itemid = $ContentItemLink[$id];				
			}
		}
				
		if ($_Itemid == '') {
			$exists = 0;
			foreach( $this->get( '_ContentSection', array() ) as $key => $value ) {
			// check if id has been tested before, if it is pull from class variable store
				if ( $key == $id ) {
					$_Itemid 	= $value;
					$exists 	= 1;
					break;
				}
			}
			// if id hasnt been checked before initaite query
			if ( !$exists ) {
				$query = "SELECT ms.id AS sid, ms.type AS stype, mc.id AS cid, mc.type AS ctype, i.id as sectionid, i.id As catid, ms.published AS spub, mc.published AS cpub"
				. "\n FROM #__content AS i"
				. "\n LEFT JOIN #__sections AS s ON i.sectionid = s.id"
				. "\n LEFT JOIN #__menu AS ms ON ms.componentid = s.id "
				. "\n LEFT JOIN #__categories AS c ON i.catid = c.id"
				. "\n LEFT JOIN #__menu AS mc ON mc.componentid = c.id "
				. "\n WHERE ( ms.type IN ( 'content_section', 'content_blog_section' ) OR mc.type IN ( 'content_blog_category', 'content_category' ) )"
				. "\n AND i.id = $id"
				. "\n ORDER BY ms.type DESC, mc.type DESC, ms.id, mc.id"
				;
				$this->_db->setQuery( $query );
				$links = $this->_db->loadObjectList();

				if (count($links)) {
					foreach($links as $link) {
						if ($link->stype == 'content_section' && $link->sectionid == $id && !isset($content_section) && $link->spub == 1) {
							$content_section = $link->sid;
						}
						
						if ($link->stype == 'content_blog_section' && $link->sectionid == $id && !isset($content_blog_section) && $link->spub == 1) {
							$content_blog_section = $link->sid;
						}						
						
						if ($link->ctype == 'content_blog_category' && $link->catid == $id && !isset($content_blog_category) && $link->cpub == 1) {
							$content_blog_category = $link->cid;
						}	
						
						if ($link->ctype == 'content_category' && $link->catid == $id && !isset($content_category) && $link->cpub == 1) {
							$content_category = $link->cid;
						}	
					}
				}			

				if (!isset($content_section)) {
					$content_section = null;
				}

				// pull existing query storage into temp variable
				$ContentSection 		= $this->get( '_ContentSection', array() );
				// add query result to temp array storage
				$ContentSection[$id] 	= $content_section;	
				// save temp array to main array storage
				$this->set( '_ContentSection', $ContentSection );
				
				$_Itemid = $ContentSection[$id];		
			}
		}

		if ($_Itemid == '') {
			$exists = 0;
			foreach( $this->get( '_ContentBlogSection', array() ) as $key => $value ) {
				// check if id has been tested before, if it is pull from class variable store
				if ( $key == $id ) {
					$_Itemid 	= $value;
					$exists 	= 1;
					break;
				}
			}
			// if id hasnt been checked before initaite query
			if ( !$exists ) {
				if (!isset($content_blog_section)) {
					$content_blog_section = null;
				}
				
				// pull existing query storage into temp variable
				$ContentBlogSection 		= $this->get( '_ContentBlogSection', array() );
				// add query result to temp array storage
				$ContentBlogSection[$id] 	= $content_blog_section;	
				// save temp array to main array storage
				$this->set( '_ContentBlogSection', $ContentBlogSection );
				
				$_Itemid = $ContentBlogSection[$id];	
			}
		}

		if ($_Itemid == '') {
			$exists = 0;
			foreach( $this->get( '_ContentBlogCategory', array() ) as $key => $value ) {
				// check if id has been tested before, if it is pull from class variable store
				if ( $key == $id ) {
					$_Itemid 	= $value;
					$exists 	= 1;
					break;
				}
			}
			// if id hasnt been checked before initaite query
			if ( !$exists ) {
				if (!isset($content_blog_category)) {
					$content_blog_category = null;
				}
				
				// pull existing query storage into temp variable
				$ContentBlogCategory 		= $this->get( '_ContentBlogCategory', array() );
				// add query result to temp array storage
				$ContentBlogCategory[$id] 	= $content_blog_category;	
				// save temp array to main array storage
				$this->set( '_ContentBlogCategory', $ContentBlogCategory );
				
				$_Itemid = $ContentBlogCategory[$id];				
			}
		}

		if ($_Itemid == '') {
			// ensure that query is only called once		
			if ( !$this->get( '_GlobalBlogSection' ) && !defined( '_JOS_GBS' ) ) {					
				define( '_JOS_GBS', 1 );
				
				// Search in global blog section
				$query = "SELECT id "
				. "\n FROM #__menu "
				. "\n WHERE type = 'content_blog_section'"
				. "\n AND published = 1"
				. "\n AND componentid = 0"
				;
				$this->_db->setQuery( $query );
				$this->set( '_GlobalBlogSection', $this->_db->loadResult() );
			}
			
			$_Itemid = $this->get( '_GlobalBlogSection' );
		}

		if ($_Itemid == '') {
			$exists = 0;
			foreach( $this->get( '_ContentCategory', array() ) as $key => $value ) {
				// check if id has been tested before, if it is pull from class variable store
				if ( $key == $id ) {
					$_Itemid 	= $value;
					$exists 	= 1;
					break;
				}
			}
			// if id hasnt been checked before initaite query
			if ( !$exists ) {
				if (!isset($content_category)) {
					$content_category = null;
				}
				
				// pull existing query storage into temp variable
				$ContentCategory 		= $this->get( '_ContentCategory', array() );
				// add query result to temp array storage
				//$ContentCategory[$id] 	= $this->_db->loadResult();	
				$ContentCategory[$id] 	= $content_category;	
				// save temp array to main array storage
				$this->set( '_ContentCategory', $ContentCategory );
				
				$_Itemid = $ContentCategory[$id];				
			}
		}

		if ($_Itemid == '') {
			// ensure that query is only called once		
			if ( !$this->get( '_GlobalBlogCategory' ) && !defined( '_JOS_GBC' ) ) {					
				define( '_JOS_GBC', 1 );
				
				// Search in global blog category
				$query = "SELECT id "
				. "\n FROM #__menu "
				. "\n WHERE type = 'content_blog_category'"
				. "\n AND published = 1"
				. "\n AND componentid = 0"
				;
				$this->_db->setQuery( $query );
				$this->set( '_GlobalBlogCategory', $this->_db->loadResult() );
			}
			
			$_Itemid = $this->get( '_GlobalBlogCategory' );
		}
		
		if ( $_Itemid != '' ) {
		// if Itemid value discovered by queries, return this value
			return $_Itemid;
		} else if ( $Itemid != 99999999 && $Itemid === 0 ) { 
		// if queries do not return Itemid value, return Itemid of page - if it is not 99999999
			return $Itemid;
		}
	}

	/**
	* @return number of Published Blog Sections
	* Kept for Backward Compatability
	*/
	function getBlogSectionCount( ) {
		return 1;
	}

	/**
	* @return number of Published Blog Categories
	* Kept for Backward Compatability
	*/
	function getBlogCategoryCount( ) {
		return 1;
	}

	/**
	* @return number of Published Global Blog Sections
	* Kept for Backward Compatability
	*/
	function getGlobalBlogSectionCount( ) {
		return 1;
	}

	/**
	* @return number of Static Content
	*/
	function getStaticContentCount( ) {
		// ensure that query is only called once		
		if ( !$this->get( '_StaticContentCount' ) && !defined( '_JOS_SCC' ) ) {		
			define( '_JOS_SCC', 1 );
			
			$query = "SELECT COUNT( id )"
			."\n FROM #__menu "
			."\n WHERE type = 'content_typed'"
			."\n AND published = 1"
			;
			$this->_db->setQuery( $query );
			// saves query result to variable
			$this->set( '_StaticContentCount', $this->_db->loadResult() );
		}
		
		return $this->get( '_StaticContentCount' );		
	}

	/**
	* @return number of Content Item Links
	*/
	function getContentItemLinkCount( ) {
		// ensure that query is only called once		
		if ( !$this->get( '_ContentItemLinkCount' ) && !defined( '_JOS_CILC' ) ) {		
			define( '_JOS_CILC', 1 );
			
			$query = "SELECT COUNT( id )"
			."\n FROM #__menu "
			."\n WHERE type = 'content_item_link'"
			."\n AND published = 1"
			;
			$this->_db->setQuery( $query );
			// saves query result to variable
			$this->set( '_ContentItemLinkCount', $this->_db->loadResult() );
		}

		return $this->get( '_ContentItemLinkCount' );
	}

	/**
	* @param string The name of the property
	* @param mixed The value of the property to set
	*/
	function set( $property, $value=null ) {
		$this->$property = $value;
	}

	/**
	* @param string The name of the property
	* @param mixed  The default value
	* @return mixed The value of the property
	*/
	function get($property, $default=null) {
		if(isset($this->$property)) {
			return $this->$property;
		} else {
			return $default;
		}
	}

	/** Is admin interface?
	 * @return boolean
	 * @since 1.0.2
	 */
	function isAdmin() {
		return $this->_isAdmin;
	}
}

?>
