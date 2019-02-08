<?php

	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/
	
	$visohotlinkRelease = '1.0';
	
	$task = $_POST['task'];
	
	switch ($task) {
		case 'install':
			install();
			break;
		
		default:
			showForm();
			break;
	}
	
	// show installation form
	function showForm ($host='', $login='', $base='', $prefix_table='vh_', $mosConfig_lang='english', $visohotlinkUser='', $msg='', $url_site='') {
		?>
		
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Visohotlink</title>
		<link href="../css/visohotlink.css" rel="stylesheet" type="text/css"/>
		</head>
		
		<body>
		
		<?php 
		if ($msg!='') {
			
			?>
		<strong><center><?php echo $msg; ?></center></strong>	
			<?php
	
		}
		?>
		<img src="../images/logo_visohotlink_32h.gif" width="129" height="32" />
		<table class="adminheading">
		<tr>
			<td>
			Installation
			</td>
		</tr>
		</table>
		
		<form action="index.php" method="post">
		
		<table class="adminform">
			<tr>
				<th colspan="2">Database access</th>
			</tr>
			<tr>
				<td width="15%">
				Host (usually 'localhost')
				</td>
				<td>
				<input name="host" value="<?php echo $host; ?>" type="text" size="20">
				</td>
			</tr>
			<tr>
				<td>
				User
				</td>
				<td>
				<input name="login" value="<?php echo $login; ?>" type="text" size="20">
				</td>
			</tr>
			<tr>
				<td>
				Password
				</td>
				<td>
				<input name="password" value="" type="password" size="20">
				</td>
			</tr>
			<tr>
				<td>
				Base
				</td>
				<td>
				<input name="base" value="<?php echo $base; ?>" type="text" size="20">
				</td>
			</tr>
			<tr>
				<td>
				Prefix
				</td>
				<td>
				<input name="prefix_table" value="<?php echo $prefix_table; ?>" type="text" size="20">
				</td>
			</tr>
			<tr>
				<th colspan="2">Language</th>
			</tr>
			<tr>
				<td>
				Please select
				</td>
				<td>
				<select name="mosConfig_lang">
					<option value="french" <?php echo ($mosConfig_lang=='french' ?  'selected="selected"' : '') ?>>Français</option>
					<option value="english" <?php echo ($mosConfig_lang=='english' ?  'selected="selected"' : '') ?>>English</option>
				</select>
				</td>
			</tr>
			<tr>
				<th colspan="2">VisoHotlink administration access</th>
			</tr>
			<tr>
				<td>
				Login
				</td>
				<td>
				<input name="visohotlinkUser" value="<?php echo $visohotlinkUser; ?>" type="text" size="20">
				</td>
			</tr>
			<tr>
				<td>
				Password
				</td>
				<td>
				<input name="visohotlinkPass" value="<?php echo $visohotlinkPass; ?>" type="password" size="20">
				</td>
			</tr>
			<tr>
				<th colspan="2">Your website</th>
			</tr>
			<tr>
				<td>
				Url
				</td>
				<td>
				<input name="url_site" value="<?php echo $url_site; ?>" type="text" size="20">
				</td>
			</tr>			
		</table>
		<input type="submit" value="Install!!!" />
		<input type="hidden" name="task" value="install" />
		</form>
		<?php
		global $visohotlinkRelease;
		?>
		
		<div class="footer" align="center">
		<a href='http://www.visohotlink.org' target='_blank'>VisoHolink <?php echo $visohotlinkRelease; ?></a> is powered by <a href='http://www.visocrea.fr' target='_blank'>Visocrea</a>
		</div>
		<div class="footer" align="center">
		Original Template <a href='http://www.joomla.org' target='_blank'>Joomla!</a>
		</div>
		</body>
		</html>
		
		<?php	
	} 
	
	// install VisoHotlink
	function install () {
		
		// look for some paramaters
		$host = $_POST['host'];
		$login = $_POST['login'];
		$password = $_POST['password'];
		$base = $_POST['base'];
		$prefix_table = $_POST['prefix_table'];
		
		$trans = array("/install/index.php" => "");
	
		$mosConfig_absolute_path = strtr( $_SERVER['SCRIPT_FILENAME'], $trans );
		 
		$mosConfig_live_site = strtr( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'], $trans );
		
		$mosConfig_lang = $_POST['mosConfig_lang'];
		$skin = 'default';
		$visohotlinkUser = $_POST['visohotlinkUser'];
		$visohotlinkPass = $_POST['visohotlinkPass'];
		$url_site = $_POST['url_site'];
		
		//if asked parameters are not empty
		if (($host!='') && ($login!='') && ($password!='') && ($base!='') && ($visohotlinkUser!='') && ($visohotlinkPass!='') && ($url_site!='')) {
			
			// try to connect to the database
			$db = mysql_connect($host, $login, $password);
			
			// database connection ok
			if ($db != false ) {
				
				//try to select the base
				if (mysql_select_db($base,$db)) {
					
					// create the query
					$query1 = "CREATE TABLE IF NOT EXISTS `".$prefix_table."visohotlink_link` (
						  `id` int(11) NOT NULL auto_increment,
						  `id_referer` int(11) NOT NULL default '0',
						  `uri` text NOT NULL,
						  `host` varchar(255) NOT NULL default '',
						  `date` datetime NOT NULL default '0000-00-00 00:00:00',
						  `size` int(11) NOT NULL default '0',
						  `downloaded` int(11) NOT NULL default '0',
						  `link_type` varchar(8) NOT NULL default '',
						  `answer_type` varchar(8) NOT NULL default '',
						  `text` text NOT NULL,
						  `replace_file` text NOT NULL,
						  `mime_type` varchar(255) NOT NULL default '',
						  `redirect_url` varchar(255) NOT NULL default '',
						  `checked_out` int(11) NOT NULL default '0',
						  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
						  PRIMARY KEY  (`id`),
						  KEY `id_referer` (`id_referer`),
						  KEY `host` (`host`)
						) TYPE=MyISAM"
					;
					$query2 = "CREATE TABLE IF NOT EXISTS `".$prefix_table."visohotlink_referer` (
						  `id` int(11) NOT NULL auto_increment,
						  `id_site` int(11) NOT NULL default '0',
						  `referer` text NOT NULL,
						  `date` datetime NOT NULL default '0000-00-00 00:00:00',
						  PRIMARY KEY  (`id`),
						  KEY `id_site` (`id_site`)
						) TYPE=MyISAM"
					;
					$query3 = "CREATE TABLE IF NOT EXISTS `".$prefix_table."visohotlink_site` (
						  `id` int(11) NOT NULL auto_increment,
						  `referer_type` varchar(8) NOT NULL default '',
						  `url` varchar(255) NOT NULL default '',
						  `link_type` varchar(8) NOT NULL default '',
						  `answer_type` varchar(8) NOT NULL default '',
						  `text` text NOT NULL,
						  `url_replace_jpeg` text NOT NULL,
						  `url_replace_audio_mpeg` text NOT NULL,
						  `url_replace_video_mpeg` text NOT NULL,
						  `url_replace_gif` text NOT NULL,
						  `url_replace_png` text NOT NULL,
						  `url_replace_avi` text NOT NULL,
						  `url_replace_mov` text NOT NULL,
						  `redirect_url` varchar(255) NOT NULL default '',
						  `date` datetime NOT NULL default '0000-00-00 00:00:00',
						  `checked_out` int(11) NOT NULL default '0',
						  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
						  PRIMARY KEY  (`id`),
						  KEY `url` (`url`)
						) TYPE=MyISAM"
					;
					
					$query4 = "CREATE TABLE IF NOT EXISTS `".$prefix_table."visohotlink_visits` (
						  `id` int(11) NOT NULL auto_increment,
						  `id_referer` int(11) NOT NULL default '0',
						  `entry_page` text NOT NULL,
						  `visits` int(11) NOT NULL default '0',
						  `date` datetime NOT NULL default '0000-00-00 00:00:00',
						  PRIMARY KEY  (`id`),
						  KEY `id_referer` (`id_referer`)
						) TYPE=MyISAM"
					;
					
					$query5 = "CREATE TABLE IF NOT EXISTS `".$prefix_table."visohotlink_temp` (
						  `referer` text NOT NULL,
						  `id_referer` int(11) NOT NULL default '0',
						  `downloaded` int(11) NOT NULL default '0',
						  `bandwidth` int(11) NOT NULL default '0'
						) TYPE=MyISAM"
					;
					
					$query6 = "CREATE TABLE IF NOT EXISTS `".$prefix_table."visohotlink_temp2` (
						  `id_site` int(11) NOT NULL default '0',
						  `downloaded` int(11) NOT NULL default '0',
						  `bandwidth` int(11) NOT NULL default '0'
						) TYPE=MyISAM"
					;
					
					// if the query is set successfully
					if ((mysql_query($query1)) && (mysql_query($query2)) && (mysql_query($query3)) && (mysql_query($query4)) && (mysql_query($query5)) && (mysql_query($query6))) {
					
						// create the config file and if successfully		
						if (createConfigFile($host, $login, $password, $base, $prefix_table, $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $skin, $visohotlinkUser, $visohotlinkPass, $url_site)) {
							// tell the user the installation is successful
							showInstallSuccess($mosConfig_live_site, $url_site);
						} else {
							die('An error occurs when creating the settings file !');
						}
					} else showForm ($host, $login, $base, $prefix_table, $mosConfig_lang, $visohotlinkUser, 'Error when creating the table', $url_site);
				} else showForm ($host, $login, $base, $prefix_table, $mosConfig_lang, $visohotlinkUser, 'Error with the database selected', $url_site);
			} else showForm ($host, $login, $base, $prefix_table, $mosConfig_lang, $visohotlinkUser, 'Error with MySQL server connection parameters', $url_site);
		} else showForm ($host, $login, $base, $prefix_table, $mosConfig_lang, $visohotlinkUser, 'Please fill all fields', $url_site);
		
	}
	
	//create the config file. Return false if error
	function createConfigFile ($host, $login, $password, $base, $prefix_table, $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $skin, $visohotlinkUser, $visohotlinkPass, $url_site) {
		
		// look if the config file is writable
		$configfile = "../config/config.visohotlink.php";
		chmod ($configfile, 0766);
		$permission = is_writable($configfile);
		
		// initialize some parameters
		$link_type = 'default';
		$answer_type = 'sendpic';
		
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
		$config .= "\$ignored_sites = '';\n";
		$config .= "\$ignored_files = '';\n";
		
		$config .= "\$redirect_url = '';\n";
		$config .= "\$url_replace_jpeg = '';\n";
		$config .= "\$url_replace_audio_mpeg = '';\n";
		$config .= "\$url_replace_video_mpeg = '';\n";
		$config .= "\$url_replace_gif = '';\n";
		$config .= "\$url_replace_png = '';\n";
		$config .= "\$url_replace_avi = '';\n";
		$config .= "\$url_replace_mov = '';\n";
		
		
		$config .= "\$entries_per_page = '30';\n";
		
		$config .= "\$activ_alert = 'No';\n";
		$config .= "\$alert_threshold = '0';\n";
		$config .= "\$email = '" . $email . "';\n";
		
		$config .= "\$mosConfig_mailer = 'mail';\n";
		$config .= "\$mosConfig_mailfrom = '';\n";
		$config .= "\$mosConfig_smtpauth = '0';\n";
		$config .= "\$mosConfig_smtpuser = '';\n";
		$config .= "\$mosConfig_smtppass = '';\n";
		$config .= "\$mosConfig_smtphost = '';\n";
		$config .= "\$mosConfig_sendmail = '';\n";
		
		$config .= "\$show_logo = 'Yes';\n";
		$config .= "\$url_site = '" . $url_site . "';\n";
		$config .= "\$redirect_engine = 'No';\n";
			
		$config .= "?>";
		
		// save the file content
		if ($fp = fopen("$configfile", "w")) {
			fputs($fp, $config, strlen($config));
			fclose ($fp);
			return true;
		}
		else return false;
	}
	
	// show the installation success page
	function showInstallSuccess ($mosConfig_live_site) {
		
		// inlcude file configuration
		include_once('../config/config.visohotlink.php');
		// Include language file
		if (file_exists($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php')) {
			include($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php');
		} else {
			include($mosConfig_absolute_path . '/language/english.php');
		}
		
		// build the content of the .htaccess file
		$trans = array("www." => "");
	
		$ndd = strtr( $_SERVER['HTTP_HOST'], $trans );
		
		$trans = array( "http://".$_SERVER['HTTP_HOST']."/" => "");
		$visoHotlinkPath = strtr( $mosConfig_live_site, $trans );
					
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
	?>
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Visohotlink</title>
		<link href="../css/visohotlink.css" rel="stylesheet" type="text/css"/>
		</head>
		
		<body>
		<img src="../images/logo_visohotlink_32h.gif" width="129" height="32" border="0" />
		<table class="adminheading">
		<tr>
			<td>
			Installation successful ! Please delete 'install' directory for security reason!
			</td>
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<td>Visohotlink helps you to detect hotlink of your files. To activate this protection, please copy the following content in the .htaccess file of the main directory of your website and replace 'directory_1', 'sub_directory_1' by the directory you want to protect.
			</td>
		</tr>
		<tr>
			<td>
			<textarea name="htaccess" readonly="readonly" cols="90" rows="6"><?php echo $htaccess; ?></textarea>
			</td>
		</tr>
		<tr>
			<td>VisoHotlink can also detect the referers and page entry of your website. It then produce more detailed analysis. You just have to paste the following tag on each page of your website. Note that you can set it visible or not in your VisoHotlink settings.
			</td>
		</tr>
		<tr>
			<td>
			<textarea name="htaccess" readonly="readonly" cols="90" rows="10"><?php echo $tag; ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
			To be informed of the last release, bugs and stuff like that, please visit <a href="http://www.visohotlink.org" target="_blank">http://www.visohotlink.org</a>.
			</td>
		</tr>
		<tr>
			<td>
			To report a bug, please send a mail to <a href="mailto:bugs@visohotlink.org">bugs@visohotlink.org</a>
			</td>
		</tr>
		<tr>
			<td>
			You can now :
			<ul>
				<li><a href="<?php echo $mosConfig_live_site; ?>/index.php?task=showLinks">Watch your file hotlinks</a></li>
				<li><a href="<?php echo $mosConfig_live_site; ?>/index.php?task=config">Configure Visohotlink</a></li>
			</ul>
			</td>
		</tr>
		</table>
		
		<?php
		global $visohotlinkRelease;
		?>
		
		<div class="footer" align="center">
		<a href='http://www.visohotlink.org' target='_blank'>VisoHolink <?php echo $visohotlinkRelease; ?></a> is powered by <a href='http://www.visocrea.fr' target='_blank'>Visocrea</a>
		</div>
		<div class="footer" align="center">
		Original Template <a href='http://www.joomla.org' target='_blank'>Joomla!</a>
		</div>
		</body>
		</html>
	<?php

	}
?>