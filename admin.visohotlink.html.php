<?php

	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/

class HTML_visohotlink {
	
	//Show a list of links
	function showLinks (&$rows, &$pageNav, $select_list, $stats, $selection='') {
		global $mosConfig_live_site;
		mosCommonHTML::loadOverlib();
		?>

		<form action="index.php" method="post" name="adminForm">
			<table class="adminheading">
				<tr>
					<th><?php echo _VH_HOTLINKS_LIST; ?></th>
				</tr>
			</table>
			
			<table class="adminform">
			<tr>
				<th colspan="2">
				<?php echo _VH_SELECTION; ?>
				</th>
			</tr>			
				<?php echo $select_list; ?>
			</tr>
			</table>
			
			<table class="adminform">
			<tr>
				<th>
				<?php echo _VH_SELECTION_STATS; ?>
				</th>
			</tr>			
			<tr>
				<td>
				<?php echo _VH_DOWNLOADS_NUMBER . $stats->downloaded; ?> | <?php echo _VH_BANDWIDTH_USED . round ($stats->bandwidth / 1000000 , 2); ?> Mo
				</td>
			</tr>
			</table>
			
			<table class="adminlist">
			<tr>
				<th width="10">
				#
				</th>
				<th width="10">
				<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th width="10">
				</th>
				<th width="10">
				</th>
				<th width="10">
				</th>
				<th align="left">
				<?php echo _VH_HOTLINKS_URLS; ?>
				</th>
				<th width="35%" align="left">
				<?php echo _VH_FILES; ?>
				</th>
				<th width="100" align="center" colspan="2">
				<?php echo _VH_DOWNLOADS; ?>
				</th>
				<th width="110" align="center" colspan="2">
				<?php echo _VH_BANDWIDH; ?>
				</th>
			</tr>
			<?php
			
			$k = 0;
			
			// List all links inside $rows
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
	
				$link = 'index.php?task=editLinkA&id=' . $row->id . '&' . $selection;
				$link_referer 	= 'index.php?group_by_site_referer=' . urlencode($row->site_referer);
				$link_file = 'index.php?group_by_file=' . urlencode($row->uri);			
				
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				if ($row->referer_type=='engine') {
					$keyword = utf8_decode(urldecode($row->referer));
				}
				?>
				
				<tr class="<?php echo "row$k"; ?>">
					<td width="10">
					<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
					<td width="10">
					<?php echo $checked; ?>
					</td>
					<td width="10">
					<a href="<?php echo $link; ?>"><img src="<?php echo $mosConfig_live_site. "/images/edit_small.png"; ?>" border="0" alt="<?php echo _VH_EDIT; ?>" title="<?php echo _VH_EDIT; ?>" /></a>
					</td>
					<td width="10"><?php 
						if ($row->referer_type!='engine') { ?> <a href="<?php echo $row->referer; ?>" target="_blank"><img src="<?php echo $mosConfig_live_site. "/images/link.png"; ?>" border="0" alt="<?php echo _VH_HOTLINK_LINK; ?>" title="<?php echo _VH_HOTLINK_LINK; ?>" /></a><?php } ?>
					</td>
					<td width="10"><a href="<?php echo "http://" . $row->host.$row->uri; ?>" target="_blank"><img src="<?php echo $mosConfig_live_site. "/images/image.png"; ?>" border="0" alt="<?php echo _VH_SEE_FILE; ?>" title="<?php echo _VH_SEE_FILE; ?>" /></a>
					</td>
					<td>
					<a href="<?php echo $link_referer; ?>" title="
					<?php if ($row->referer_type=='engine') echo $row->site_referer . ' : ' . $keyword;
					else echo $row->referer; ?>"><?php if ($row->referer_type=='engine') echo $row->site_referer . ' : ' . $keyword;
					else echo substr($row->referer, 0, 50); ?></a>
					</td>
					<td width="35%">
					<a href="<?php echo $link_file; ?>" title="<?php echo $row->uri; ?>"><?php echo substr($row->uri, 0, 50); ?></a>
					</td>
					<td align="right">
						<?php echo $row->downloaded; ?>
					</td>
					<td align="left">
						<?php if ($stats->downloaded!='0') echo '(' . round($row->downloaded / $stats->downloaded * 100, 2) .'%)'; 
								else echo '(-%)'; ?>
					</td>
					<td align="right">
						<?php echo round($row->downloaded * $row->size / 1000000, 2); ?> Mo
					</td>
					<td align="left">
					<?php if ($stats->bandwidth!='0') echo '(' . round($row->downloaded * $row->size / $stats->bandwidth * 100, 2) .'%)'; 
								else echo '(-%)'; ?>
								
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
		</div>
		</div>		
		<?php 
		echo $pageNav->getListFooter(); ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php	
	}
	
	//Show a list of links
	function showSites (&$rows, &$pageNav, $select_list, $selection='', $stats, $task='showSites') {
		global $mosConfig_live_site;
		mosCommonHTML::loadOverlib();
		
		switch ($task) {				
			case 'showEngines':
				$title = _VH_ENGINES_LIST;
				$name = _VH_ENGINE_NAME;
				break;
				
			case 'showKeywords':
				$title = _VH_KEYWORDS_LIST;
				$name = _VH_KEYWORD;
				break;
				
			default:
				$title = _VH_SITES_LIST;
				$name = _VH_SITES_URLS;
				break;
		}
		?>

		<form action="index.php" method="post" name="adminForm">
			<table class="adminheading">
				<tr>
					<th><?php echo $title; ?></th>
				</tr>
			</table>
			
			<table class="adminform">
			<tr>
				<th colspan="2">
				<?php echo _VH_SELECTION; ?>
				</th>
			</tr>			
				<?php echo $select_list; ?>
			</table>
			
			<table class="adminform">
			<tr>
				<th>
				<?php echo _VH_SELECTION_STATS; ?>
				</th>
			</tr>			
			<tr>
				<td>
				<?php echo _VH_DOWNLOADS_NUMBER . $stats->downloaded; ?> | <?php echo _VH_BANDWIDTH_USED .  round ($stats->bandwidth / 1000000 , 2); ?> Mo | <?php echo _VH_VISITS_NUMBER . $stats->visits; 
				if (($stats->visits!='0') && ($stats->bandwidth!='0')) {
						echo ' | ' . _VH_PERFORMANCE_RATE; ?> (V)/(D) , (V)/(Mo) : <?php echo round( $stats->visits / $stats->downloaded , 2) . ' % , ' . round( $stats->visits / $stats->bandwidth * 1000000, 2); } ?>
				</td>
			</tr>
			</table>
			
			<table class="adminlist">
			<tr>
				<th width="10">
				#
				</th>
				<?php if ($task=='showSites') { ?>
				<th width="10">
				<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th width="10">
				</th>
				<th width="10">
				</th>
				<?php } ?>
				<th width="10">
				</th>
				<th width="10">
				</th>
				<?php if ($task=='showEngines') { ?>
				<th width="10">
				</th>
				<?php } ?>
				<th align="left">
				<?php echo $name; ?>
				</th>
				<th width="100" align="center" colspan="2">
				<?php echo _VH_DOWNLOADS; ?><br />(D)
				</th>
				<th width="110" align="center" colspan="2">
				<?php echo _VH_BANDWIDH; ?><br />(Mo)
				</th>
				<th width="100" align="center" colspan="2">
				<?php echo _VH_VISITS; ?><br />(V)
				</th>
				<th width="100" align="center" colspan="2"><?php echo _VH_PERFORMANCE_RATE; ?><br />(V)/(D) | (V)/(Mo)
				</th>
			</tr>
			<?php
			
			$k = 0;
			
			// List all links inside $rows
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
				
				if ($task=='showSites') {
					$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
					$link_hotlink = 'index.php?task=showLinks&group_by_site_referer=' . urlencode($row->url) . '&sort_by=downloaded&ascdesc=DESC';
					$link_referer = 'index.php?task=showReferers&group_by_site_referer=' . urlencode($row->url) . '&sort_by=visits&ascdesc=DESC';
					$link_edit = 'index.php?task=editSiteA&id=' . $row->id . '&' . $selection;
				}
				else if ($task=='showEngines') {
					$link_hotlink = 'index.php?task=showLinks&group_by_site_referer=' . urlencode($row->url) . '&sort_by=downloaded&ascdesc=DESC';
					$link_referer = 'index.php?task=showReferers&group_by_site_referer=' . urlencode($row->url) . '&sort_by=visits&ascdesc=DESC';
					$link_keyword = 'index.php?task=showKeywords&group_by_site_referer=' . urlencode($row->url) . '&sort_by=downloaded&ascdesc=DESC';
				}
				else if ($task=='showKeywords') {
					$row->url = utf8_decode(urldecode($row->referer));
					$link_hotlink = 'index.php?task=showLinks&group_by_referer=' . urlencode($row->referer) . '&sort_by=downloaded&ascdesc=DESC';
					$link_referer = 'index.php?task=showReferers&group_by_referer=' . urlencode($row->referer) . '&sort_by=visits&ascdesc=DESC';
				}			
				
				if ($row->performance_rate=='') $row->performance_rate = '0';
				if ($row->performance_bw_rate=='') $row->performance_bw_rate = '0';
				if ($row->visits=='') $row->visits = '0';
				
				
				?>
				
				<tr class="<?php echo "row$k"; ?>">
					<td width="10">
					<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
					<?php if ($task=='showSites') { ?>
					 <td width="10">
					<?php echo $checked; ?>
					</td>
					<td align="left" width="10">
					<a href="<?php echo $link_edit; ?>"><img src="<?php echo $mosConfig_live_site. "/images/edit_small.png"; ?>" border="0" alt="<?php echo _VH_EDIT; ?>" title="<?php echo _VH_EDIT; ?>" /></a>
					</td>
					<td align="left" width="10">
					<a href="<?php echo $row->url; ?>" target="_blank"><img src="<?php echo $mosConfig_live_site. "/images/link.png"; ?>" border="0" alt="<?php echo $row->url; ?>" title="<?php echo $row->url; ?>" /></a>
					</td>
					<?php } ?>
					<td align="left" width="10">
					<a href="<?php echo $link_hotlink; ?>" ><img src="<?php echo $mosConfig_live_site. "/images/hotlink.png"; ?>" border="0" alt="<?php echo _VH_HOTLINKS; ?>" title="<?php echo _VH_HOTLINKS; ?>" /></a>
					</td>
					<td align="left" width="10">
					<a href="<?php echo $link_referer; ?>" ><img src="<?php echo $mosConfig_live_site. "/images/visit.png"; ?>" border="0" alt="<?php echo _VH_REFERERS; ?>" title="<?php echo _VH_REFERERS; ?>" /></a>
					</td>
					<?php if ($task=='showEngines') { ?>
					<td width="10">
					<a href="<?php echo $link_keyword; ?>" ><img src="<?php echo $mosConfig_live_site. "/images/keyword.png"; ?>" border="0" alt="<?php echo _VH_KEYWORDS; ?>" title="<?php echo _VH_KEYWORDS; ?>" /></a>
					</td>
					<?php } ?>
					<td>
					<?php echo substr($row->url, 0, 60); ?>
					</td>
					<td align="right"><?php echo $row->downloaded; ?>
					</td>
					<td align="left">
						<?php if ($stats->downloaded!='0') echo '(' . round($row->downloaded / $stats->downloaded * 100, 2) . '%)'; 
								else echo '(-%)'; ?>
					</td>
					<td align="right">
						<?php echo round($row->bandwidth / 1000000, 2);?> Mo
					</td>
					<td align="left">
						<?php if ($stats->bandwidth!='0') echo '(' . round($row->bandwidth / $stats->bandwidth * 100, 2) .'%)'; 
								else echo '(-%)'; ?>
					</td>
					<td align="right"><?php echo $row->visits; ?>	
					</td>
					<td align="left">
						<?php if ($stats->visits!='0') echo '(' . round($row->visits / $stats->visits * 100, 2) .'%)'; 
								else echo '(-%)'; ?>
					</td>
					<td align="right"><?php echo $row->performance_rate; ?> %
					</td>
					<td align="right"><?php echo $row->performance_bw_rate; ?> 
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
		</div>
		</div>		
		<?php 
		echo $pageNav->getListFooter(); ?>
		<input type="hidden" name="task" value="<?php echo $task; ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php	
	}
	
	//Show a list of referers
	function showReferers (&$rows, &$pageNav, $select_list, $stats) {
		global $mosConfig_live_site;
		mosCommonHTML::loadOverlib();
		
		?>

		<form action="index.php" method="post" name="adminForm">
			<table class="adminheading">
				<tr>
					<th><?php echo _VH_REFERERS_LIST; ?></th>
				</tr>
			</table>
			
			<table class="adminform">
			<tr>
				<th colspan="2">
				<?php echo _VH_SELECTION; ?>
				</th>
			</tr>			
				<?php echo $select_list; ?>
			</table>
			
			<table class="adminform">
			<tr>
				<th>
				<?php echo _VH_SELECTION_STATS; ?>
				</th>
			</tr>			
			<tr>
				<td colspan="2">
				 <?php echo _VH_VISITS_NUMBER . $stats->visits; ?>
				</td>
			</tr>
			</table>
			
			<table class="adminlist">
			<tr>
				<th width="10">
				#
				</th>
				<th width="10">
				</th>
				<th align="left">
				<?php echo _VH_REFERER; ?>
				</th>
				<th width="10">
				</th>
				<th align="left">
				<?php echo _VH_ENTRY_PAGE; ?>
				</th>
				<th width="100" align="center" colspan="2">
				<?php echo _VH_VISITS; ?>
				</th>
			</tr>
			<?php
			
			$k = 0;
			
			// List all links inside $rows
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
				$link = 'index.php?task=showReferers&group_by_site_referer=' . urlencode($row->site_referer);
				$link_page = 'index.php?task=showReferers&group_by_entry_page=' . urlencode($row->entry_page);
				
				if ($row->referer_type=='engine') {
				$keyword = utf8_decode(urldecode($row->referer));
				}
				
				?>
				
				<tr class="<?php echo "row$k"; ?>">
					<td width="10">
					<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
					<td width="10"><?php 
						if ($row->referer_type!='engine') { ?> <a href="<?php echo $row->referer; ?>" target="_blank"><img src="<?php echo $mosConfig_live_site. "/images/link.png"; ?>" border="0" alt="<?php echo _VH_REFERER_LINK; ?>" title="<?php echo _VH_REFERER_LINK; ?>" /></a><?php } ?>
					</td>
					<td>
					<a href="<?php echo $link; ?>" title="
					<?php if ($row->referer_type=='engine') echo $row->site_referer . ' : ' . $keyword;
					else echo $row->referer; ?>"><?php if ($row->referer_type=='engine') echo $row->site_referer . ' : ' . $keyword;
					else echo substr($row->referer, 0, 50); ?></a>
					</td>
					<td width="10">
					<a href="<?php echo $row->entry_page; ?>" target="_blank"><img src="<?php echo $mosConfig_live_site. "/images/link.png"; ?>" border="0" alt="<?php echo $row->entry_page; ?>" title="<?php echo $row->entry_page; ?>" /></a>
					</td>
					<td>
					<a href="<?php echo $link_page; ?>" title="
					<?php echo $row->entry_page; ?>"><?php echo substr($row->entry_page, 0, 50); ?></a>
					</td>
					<td align="right"><?php echo $row->visits; ?>	
					</td>
					<td align="left">
					<?php if ($stats->visits!='0') echo '(' . round($row->visits / $stats->visits * 100, 2) .'%)'; 
								else echo '(-%)'; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
		</div>
		</div>		
		<?php 
		echo $pageNav->getListFooter(); ?>
		<input type="hidden" name="task" value="showReferers" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php	
	}
	
	//Show link details and user can edit it
	function editLink (&$row, $selection='') {
		
		switch ($row->referer_type) {
			case 'normal':
				$referer_type_text = _VH_NORMAL;
				break;
			case 'engine':
				$referer_type_text = _VH_ENGINE;
				break;
			default:
				$referer_type_text = '';
				break;
		}
		
		?>
		<form action="index.php?<?php echo $selection; ?>" method="post" name="adminForm">
		
		<table class="adminheading">
		<tr>
			<th>
			<?php echo _VH_LINK_EDITION; ?>
			</th>
		</tr>
		<tr>
			<td>
			<?php echo _VH_THE_FILE; ?><a href="<?php echo 'http://' . $row->host . $row->uri ; ?>" target="_blank"><?php echo substr('http://' . $row->host . $row->uri, 0, 100); ?></a> <br />
			<?php 
				if ($row->referer_type=='normal') { echo _VH_IS_USED; ?><a href="<?php echo $row->referer; ?>" target="_blank"><?php echo substr($row->referer, 0, 100); ?></a>
				<?php } else if ($row->referer_type=='engine') { echo _VH_IS_USED . ' ' . $row->site_referer . ' ' . _VH_FOR_KEYWORD . ' \'' . $row->referer . '\''; } ?>
			</td>
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<th width="15%">
			<?php echo _VH_DETECTION_DATE; ?>
			</th>
			<th width="15%">
			<?php echo _VH_DOWNLOADS; ?>
			</th>
			<th width="15%">
			<?php echo _VH_FILE_SIZE; ?>
			</th>
			<th width="15%">
			<?php echo _VH_BANDWIDTH_USED; ?>
			</th>
			<th width="20%">
			<?php echo _VH_MIME_TYPE; ?>
			</th>
			<th width="20%">
			<?php echo _VH_REFERER_TYPE; ?>
			</th>
		</tr>
		<tr>
			<td>
			<?php 
				setlocale(LC_TIME, "fr");
				echo strftime("%d %b %G", strtotime($row->date));
			?>
			</td>
			<td>
			<?php echo $row->downloaded; ?>
			</td>
			<td>
			<?php echo round($row->size / 1000, 0); ?> ko
			</td>
			<td>
			<?php echo round($row->downloaded * $row->size / 1000000, 2); ?> Mo
			</td>
			<td>
			<?php echo $row->mime_type; ?>
			</td>
			<td>
			<?php echo $referer_type_text; ?>
			</td>
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<th>
			<?php echo _VH_HOTLINK_TYPE; ?>
			</th>
			<th>
			<?php echo _VH_ANSWER_TYPE; ?>
			</th>
		</tr>	
		<tr>
			<td>
			<select name="link_type" >
					<option value="default"<?php if ($row->link_type=='default') echo 'selected="selected"'; ?>><?php echo _VH_DEFAULT; ?></option> 
					<option value="partner"<?php if ($row->link_type=='partner') echo 'selected="selected"'; ?>><?php echo _VH_PARTNER; ?></option>
					<option value="linked"<?php if ($row->link_type=='linked') echo 'selected="selected"'; ?>><?php echo _VH_RECIPROCAL_LINK; ?></option>
					<option value="link_ask"<?php if ($row->link_type=='link_ask') echo 'selected="selected"'; ?>><?php echo _VH_ASKED_LINK; ?></option>
					<option value="nolink"<?php if ($row->link_type=='nolink') echo 'selected="selected"'; ?>><?php echo _VH_WITHOUT_RECIPROCAL_LINK; ?></option>
					<option value="other"<?php if ($row->link_type=='other') echo 'selected="selected"'; ?>><?php echo _VH_OTHER; ?></option>  
			</select>
			</td>
			<td>
			<select name="answer_type">
					<option value="default"<?php if ($row->answer_type=='default') echo 'selected="selected"'; ?>><?php echo _VH_DEFAULT; ?></option>
					<option value="sendpic"<?php if ($row->answer_type=='sendpic') echo 'selected="selected"'; ?>><?php echo _VH_SEND_PIC; ?></option>
					<option value="replace"<?php if ($row->answer_type=='replace') echo 'selected="selected"'; ?>><?php echo _VH_REPLACE_PIC; ?></option>
					<option value="waterm" <?php echo ($row->answer_type=='waterm' ?  'selected="selected"' : '') ?>><?php echo _VH_AD_WATERMARK; ?></option>
					<option value="dontsend"<?php if ($row->answer_type=='dontsend') echo 'selected="selected"'; ?>><?php echo _VH_DONT_SEND; ?></option>
					<option value="redirect"<?php if ($row->answer_type=='redirect') echo 'selected="selected"'; ?>><?php echo _VH_REDIRECT; ?></option> 					
			</select><br />
			<?php echo _VH_REPLACE_FILE; ?><br />
			<input type="text" name="replace_file" size= "40" value="<?php echo $row->replace_file; ?>" /><br />
			<?php echo _VH_REDIRECT_URL; ?><br />
			<input type="text" name="redirect_url" size="40" value="<?php echo $row->redirect_url; ?>" />
			</td>		
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<th>
			<?php echo _VH_NOTES; ?>
			</th>	
		</tr>	
		<tr>
			<td>
			<textarea name="text" cols="60" rows="5"><?php echo $row->text; ?></textarea>
			</td>
		</tr>
		</table>
		
		<input type="hidden" name="task" value="" />	
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		</form>
		<?php
		
	}
	
	//Show link details and user can edit it
	function editSite (&$row, $selection='') {
		
		?>
		<form action="index.php?<?php echo $selection; ?>" method="post" name="adminForm">
		
		<table class="adminheading">
		<tr>
			<th>
			<?php echo _VH_SITE_EDITION; ?>
			</th>
		</tr>
		<tr>
			<td>
			<?php echo _VH_SITE_URL; ?> : <a href="<?php echo $row->url; ?>" target="_blank"><?php echo $row->url; ?></a>
			</td>
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<th colspan="2">
			<?php echo _VH_SITE_BEHAVIOR; ?>
			</th>
		</tr>	
		
		<tr>
			<td>
				<?php echo _VH_HOTLINK_TYPE; ?><br />
				<select name="link_type">
					<option value="default" <?php echo ($row->link_type=='default' ?  'selected="selected"' : '') ?>><?php echo _VH_DEFAULT; ?></option> 
					<option value="partner" <?php echo ($row->link_type=='partner' ?  'selected="selected"' : '') ?>><?php echo _VH_PARTNER; ?></option>
					<option value="linked" <?php echo ($row->link_type=='linked' ?  'selected="selected"' : '') ?>><?php echo _VH_RECIPROCAL_LINK; ?></option>
					<option value="link_ask" <?php echo ($row->link_type=='link_ask' ?  'selected="selected"' : '') ?>><?php echo _VH_ASKED_LINK; ?></option>
					<option value="nolink" <?php echo ($row->link_type=='nolink' ?  'selected="selected"' : '') ?>><?php echo _VH_WITHOUT_RECIPROCAL_LINK; ?></option>
					<option value="other" <?php echo ($row->link_type=='other' ?  'selected="selected"' : '') ?>><?php echo _VH_OTHER; ?></option>  
				</select>
			</td>
			<td>
				<?php echo _VH_ANSWER_TYPE; ?><br />
				<select name="answer_type">
					<option value="default"<?php if ($row->answer_type=='default') echo 'selected="selected"'; ?>><?php echo _VH_DEFAULT; ?></option>
					<option value="sendpic" <?php echo ($row->answer_type=='sendpic' ?  'selected="selected"' : '') ?>><?php echo _VH_SEND_PIC; ?></option>
					<option value="replace" <?php echo ($row->answer_type=='replace' ?  'selected="selected"' : '') ?>><?php echo _VH_REPLACE_PIC; ?></option>
					<option value="waterm" <?php echo ($row->answer_type=='waterm' ?  'selected="selected"' : '') ?>><?php echo _VH_AD_WATERMARK; ?></option>
					<option value="dontsend" <?php echo ($row->answer_type=='dontsend' ?  'selected="selected"' : '') ?>><?php echo _VH_DONT_SEND; ?></option>
					<option value="redirect" <?php echo ($row->answer_type=='redirect' ?  'selected="selected"' : '') ?>><?php echo _VH_REDIRECT; ?></option> 					
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REDIRECT_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="redirect_url" size="40" value="<?php echo $row->redirect_url; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_JPEG_URL; ?>
			</td>
			<td>
				<input type="text" name="url_replace_jpeg" size="40" value="<?php echo $row->url_replace_jpeg; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_AUDIO_MPEG_URL; ?>
			</td>
			<td>
				<input type="text" name="url_replace_audio_mpeg" size="40" value="<?php echo $row->url_replace_audio_mpeg; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_VIDEO_MPEG_URL; ?>
			</td>
			<td>
				<input type="text" name="url_replace_video_mpeg" size="40" value="<?php echo $row->url_replace_video_mpeg; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_GIF_URL; ?>
			</td>
			<td>
				<input type="text" name="url_replace_gif" size="40" value="<?php echo $row->url_replace_gif; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_PNG_URL; ?>
			</td>
			<td>
				<input type="text" name="url_replace_png" size="40" value="<?php echo $row->url_replace_png; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_AVI_URL; ?>
			</td>
			<td>
				<input type="text" name="url_replace_avi" size="40" value="<?php echo $row->url_replace_avi; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_MOV_URL; ?>
			</td>
			<td>
				<input type="text" name="url_replace_mov" size="40" value="<?php echo $row->url_replace_mov; ?>" />
			</td>
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<th>
			<?php echo _VH_NOTES; ?>
			</th>	
		</tr>	
		<tr>
			<td>
			<textarea name="text" cols="60" rows="5"><?php echo $row->text; ?></textarea>
			</td>
		</tr>
		</table>
		
		<input type="hidden" name="task" value="" />	
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		</form>
		<?php
		
	}
	
	// Page header with menu
	function pageHeader($msg='') {
		global $mosConfig_live_site;
		?>
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="copyright" CONTENT="Arnaud LECUS - VISOCREA">
		<meta name="Author" CONTENT="Arnaud LECUS - VISOCREA">
		<script language="JavaScript" src="<?php echo $mosConfig_live_site; ?>/includes/js/visohotlink.js" type="text/javascript"></script>
		<title>Visohotlink</title>
		<link href="<?php echo $mosConfig_live_site; ?>/css/visohotlink.css" rel="stylesheet" type="text/css"/>
		</head>
		
		<body>
		<div id="menu">	
				<dl>			
					<dt onMouseOver="javascript:montre('smenu1');"><a href="index.php"><?php echo _VH_ANALYSIS; ?></a></dt>
					
					<dd id="smenu1" onMouseOver="javascript:montre('smenu1');" onMouseOut="javascript:montre('');">
						<ul>
							<li><a href="index.php"><?php echo _VH_HOTLINKS_LIST; ?></a></li>
							<li><a href="index.php?task=showReferers"><?php echo _VH_REFERERS_LIST; ?></a></li>
							<li><a href="index.php?task=showSites"><?php echo _VH_SITES_LIST; ?></a></li>
							<li><a href="index.php?task=showEngines"><?php echo _VH_ENGINES_LIST; ?></a></li>
							<li><a href="index.php?task=showKeywords"><?php echo _VH_KEYWORDS_LIST; ?></a></li>
						</ul>
					</dd>
				</dl>
				<dl>
					<dt onMouseOver="javascript:montre();"><a href="index.php?task=config"><?php echo _VH_CONFIG; ?></a></dt>
				</dl>
				<dl>
					<dt onMouseOver="javascript:montre();"><a href="<?php echo _VH_URL_HELP; ?>" target="_blank"><?php echo _VH_HELP; ?></a></dt>
				</dl>
		</div>			
		<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
			<tr>
			<td class="menubackgr" style="padding-left:5px;" align="left" valign="middle" width="10">
				<img src="<?php echo $mosConfig_live_site; ?>/images/logo_visohotlink_32h.gif" width="129" height="32" border="0" />
			</td>
			<td class="menubackgr" style="padding-left:5px;" align="right" >
				<strong><a href="index.php?task=deconnect"><?php echo _VH_DECONNECT; ?></a></strong>
			</td>
			</tr>
			<?php if ($msg!='') {?>
			<tr>
			 <td align="center" class="menubackgr" colspan="2">
			 	<strong><?php echo $msg; ?></strong>
			 </td>
			</tr>
			<?php } ?>
		</table>
		
		<?php
	}
	
	// Page header for connect form
	function pageHeaderConnect($msg='') {
		global $mosConfig_live_site;
		?>
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="copyright" CONTENT="Arnaud LECUS - VISOCREA">
		<meta name="Author" CONTENT="Arnaud LECUS - VISOCREA">
		<title>Visohotlink</title>
		<link href="<?php echo $mosConfig_live_site; ?>/css/visohotlink.css" rel="stylesheet" type="text/css"/>
		</head>
		
		<body>
		<center><strong><?php echo $msg; ?></strong></center>

		<?php
	}
	
	// Page end
	function pageEnd() {
		global $visohotlinkRelease;
		?>
		<div class="footer" align="center">
		<?php
		$credit = str_replace ( '[VER]', $visohotlinkRelease, _VH_CREDITS); 
		echo $credit;
		?>
		</div>
		<div class="footer" align="center">
		<?php
		echo _VH_JOOMLA;
		?>
		</div>
		</body>
		</html> 
		<?php
	}
	
	// connect form
	function connect() {
		?>
		<div align="center">
		<form action="index.php" method="post" name="adminForm">
		<table>
		<tr>
			<td>
			<?php echo _VH_USER; ?>
			</td>
			<td>
				<input type="text" name="user" size="10">
			</td>
		</tr>
		<tr>
			<td>
			<?php echo _VH_PASSWORD; ?>
			</td>
			<td>
				<input type="password" name="pass" size="10">
			</td>
		</tr>
		</table>
		<input type="submit" name="submit" value="<?php echo _VH_VALIDATE; ?>" />
		</form>
		</div>
		<div align="center"><?php echo _VH_DEMO; ?></div>			
		<?php
	}
	
	// Show settings form
	function config($htaccess, $tag) {
		global $mosConfig_absolute_path;
		include($mosConfig_absolute_path . "/config/config.visohotlink.php");
		
		?>
		<form action="index.php" method="post" name="adminForm">
		<table class="adminheading">
			<tr>
				<th><?php echo _VH_CONFIG; ?></th>
			</tr>
			<tr>
				<td>
				<?php echo _VH_LANGUAGE; ?>
				<select name="language">
					<option value="french" <?php echo ($mosConfig_lang=='french' ?  'selected="selected"' : ''); ?>>Français</option>
					<option value="english" <?php echo ($mosConfig_lang=='english' ?  'selected="selected"' : ''); ?>>English</option>
				</select>
				</td>
			</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<th colspan="3">
			<?php echo _VH_DEFAULT_BEHAVIOR; ?>
			</th>	
		</tr>
		<tr>
			<td>
				<?php echo _VH_DEFAULT_HOTLINK_TYPE; ?><br />
				<select name="link_type">
					<option value="default" <?php echo ($link_type=='default' ?  'selected="selected"' : '') ?>><?php echo _VH_DEFAULT; ?></option> 
					<option value="partner" <?php echo ($link_type=='partner' ?  'selected="selected"' : '') ?>><?php echo _VH_PARTNER; ?></option>
					<option value="linked" <?php echo ($link_type=='linked' ?  'selected="selected"' : '') ?>><?php echo _VH_RECIPROCAL_LINK; ?></option>
					<option value="link_ask" <?php echo ($link_type=='link_ask' ?  'selected="selected"' : '') ?>><?php echo _VH_ASKED_LINK; ?></option>
					<option value="nolink" <?php echo ($link_type=='nolink' ?  'selected="selected"' : '') ?>><?php echo _VH_WITHOUT_RECIPROCAL_LINK; ?></option>
					<option value="other" <?php echo ($link_type=='other' ?  'selected="selected"' : '') ?>><?php echo _VH_OTHER; ?></option>  
				</select>
			</td>
			<td colspan="2">
				<?php echo _VH_DEFAULT_ANSWER_TYPE; ?><br />
				<select name="answer_type">
					<option value="sendpic" <?php echo ($answer_type=='sendpic' ?  'selected="selected"' : '') ?>><?php echo _VH_SEND_PIC; ?></option>
					<option value="replace" <?php echo ($answer_type=='replace' ?  'selected="selected"' : '') ?>><?php echo _VH_REPLACE_PIC; ?></option>
					<option value="dontsend" <?php echo ($answer_type=='dontsend' ?  'selected="selected"' : '') ?>><?php echo _VH_DONT_SEND; ?></option>
					<option value="waterm" <?php echo ($answer_type=='waterm' ?  'selected="selected"' : '') ?>><?php echo _VH_AD_WATERMARK; ?></option>
					<option value="redirect" <?php echo ($answer_type=='redirect' ?  'selected="selected"' : '') ?>><?php echo _VH_REDIRECT; ?></option> 					
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REDIRECT_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="redirect_url" size="40" value="<?php echo $redirect_url; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_JPEG_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="url_replace_jpeg" size="40" value="<?php echo $url_replace_jpeg; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_AUDIO_MPEG_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="url_replace_audio_mpeg" size="40" value="<?php echo $url_replace_audio_mpeg; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_VIDEO_MPEG_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="url_replace_video_mpeg" size="40" value="<?php echo $url_replace_video_mpeg; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_GIF_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="url_replace_gif" size="40" value="<?php echo $url_replace_gif; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_PNG_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="url_replace_png" size="40" value="<?php echo $url_replace_png; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_AVI_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="url_replace_avi" size="40" value="<?php echo $url_replace_avi; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _VH_REPLACE_MOV_URL; ?>
			</td>
			<td colspan="2">
				<input type="text" name="url_replace_mov" size="40" value="<?php echo $url_replace_mov; ?>" />
			</td>
		</tr>
		<tr>
			<th colspan="3">
			<?php echo _VH_DISPLAY; ?>
			</th>
		</tr>
		<tr>
			<td colspan="3">
			<?php echo _VH_ENTRIES_PER_PAGE_DEFAULT; ?><br />
			<select name="entries_per_page" class="inputbox"><?php echo $entries_per_page; ?>
				<option value="5" <?php echo ($entries_per_page=='5' ?  'selected="selected"' : '') ?>>5</option>
				<option value="10" <?php echo ($entries_per_page=='10' ?  'selected="selected"' : '') ?>>10</option>
				<option value="15" <?php echo ($entries_per_page=='15' ?  'selected="selected"' : '') ?>>15</option>
				<option value="20" <?php echo ($entries_per_page=='20' ?  'selected="selected"' : '') ?>>20</option>
				<option value="25" <?php echo ($entries_per_page=='25' ?  'selected="selected"' : '') ?>>25</option>
				<option value="30" <?php echo ($entries_per_page=='30' ?  'selected="selected"' : '') ?>>30</option>
				<option value="50"<?php echo ($entries_per_page=='50' ?  'selected="selected"' : '') ?>>50</option>
				<option value="100"<?php echo ($entries_per_page=='100' ?  'selected="selected"' : '') ?>>100</option>
				<option value="200"<?php echo ($entries_per_page=='200' ?  'selected="selected"' : '') ?>>200</option>
				<option value="500"<?php echo ($entries_per_page=='500' ?  'selected="selected"' : '') ?>>500</option>
				<option value="1000"<?php echo ($entries_per_page=='1000' ?  'selected="selected"' : '') ?>>1000</option>
			</select>
			</td>
		</tr>
		<tr>
			<th colspan="3">
			<?php echo _VH_EMAIL_ALERT; ?>
			</th>
		</tr>
		<tr>
			<td>
			<?php echo _VH_ACTIVE_ALERT; ?><br />
			<select name="activ_alert" class="inputbox">
				<option value="Yes" <?php echo ($activ_alert=='Yes' ?  'selected="selected"' : '') ?>><?php echo _VH_YES; ?></option>
				<option value="No" <?php echo ($activ_alert=='No' ?  'selected="selected"' : '') ?>><?php echo _VH_NO; ?></option>
			</select><br />
			<?php echo _VH_ALERT_THRESHOLD; ?><br />
			<input type="text" name="alert_threshold" size="5" value="<?php echo $alert_threshold; ?>" />
			</td>
			<td>
			<?php echo _VH_MAILER; ?><br /> <select name="mosConfig_mailer" class="inputbox">
				<option value="mail" <?php echo ($mosConfig_mailer=='mail' ?  'selected="selected"' : '') ?>>Php mail function</option>
				<option value="sendmail" <?php echo ($mosConfig_mailer=='sendmail' ?  'selected="selected"' : '') ?>>Sendmail</option>
				<option value="smtp" <?php echo ($mosConfig_mailer=='smtp' ?  'selected="selected"' : '') ?>>SMTP Server</option>
			</select><br />
			<?php echo _VH_SENDMAIL; ?><br /> <input type="text" name="mosConfig_sendmail" size="40" value="<?php echo $mosConfig_sendmail; ?>" /><br />
			<?php echo _VH_SMTPAUTH; ?><br /> <input name="mosConfig_smtpauth" id="config_smtpauth0" value="0" <?php echo ($mosConfig_smtpauth=='0' ?  'checked="checked"' : '') ?> class="inputbox" type="radio">
			<label for="config_smtpauth0"><?php echo _VH_NO; ?></label>
			<input name="mosConfig_smtpauth" id="config_smtpauth1" value="1" <?php echo ($mosConfig_smtpauth=='1' ?  'checked="checked"' : '') ?> class="inputbox" type="radio">
			<label for="config_smtpauth1"><?php echo _VH_YES; ?></label><br />
			<?php echo _VH_SMTPUSER; ?><br /> <input type="text" name="mosConfig_smtpuser" size="40" value="<?php echo $mosConfig_smtpuser; ?>" /><br />
			<?php echo _VH_SMTPPASS; ?><br /> <input type="text" name="mosConfig_smtppass" size="40" value="<?php echo $mosConfig_smtppass; ?>" /><br />
			<?php echo _VH_SMTPHOST; ?><br /> <input type="text" name="mosConfig_smtphost" size="40" value="<?php echo $mosConfig_smtphost; ?>" />
			</td>
			<td>
			<?php echo _VH_EMAIL; ?><br />
			<input type="text" name="email" size="40" value="<?php echo $email; ?>" /><br />
			<?php echo _VH_EMAIL_FROM; ?><br />
			<input type="text" name="mosConfig_mailfrom" size="40" value="<?php echo $mosConfig_mailfrom; ?>" />
			</td>
		</tr>
		<tr>
			<th colspan="3">
			<?php echo _VH_REFERER_RECUP; ?>
			</th>
		</tr>
		<tr>
			<td>
			<?php echo _VH_DISPLAY_LOGO; ?><br />
			<select name="show_logo" class="inputbox">
				<option value="Yes" <?php echo ($show_logo=='Yes' ?  'selected="selected"' : '') ?>><?php echo _VH_YES; ?></option>
				<option value="No" <?php echo ($show_logo=='No' ?  'selected="selected"' : '') ?>><?php echo _VH_NO; ?></option>
			</select>
			</td>
			<td>
			<?php echo _VH_SITE_URL; ?><br />
			<input type="text" name="url_site" size="40" value="<?php echo $url_site; ?>" />
			</td>
			<td>
			<?php echo _VH_REDIRECT_ENGINE; ?><br />
			<select name="redirect_engine" class="inputbox">
				<option value="Yes" <?php echo ($redirect_engine=='Yes' ?  'selected="selected"' : '') ?>><?php echo _VH_YES; ?></option>
				<option value="No" <?php echo ($redirect_engine=='No' ?  'selected="selected"' : '') ?>><?php echo _VH_NO; ?></option>
			</select>
			</td>
		</tr>
		<tr>
			<th colspan="3">
			<?php echo _VH_IGNORE_URLS; ?>
			</th>
		</tr>
		<tr>
			<td colspan="3">
			<?php echo _VH_WARNING_SPACES_BETWEEN_URLS; ?><br />
			<textarea name="ignored_sites" cols="90" rows="5"><?php echo $ignored_sites; ?></textarea>
			</td>
		</tr>
		<tr>
			<th colspan="3">
			<?php echo _VH_IGNORE_FILES; ?>
			</th>
		</tr>
		<tr>
			<td colspan="3">
			<?php echo _VH_WARNING_SPACES_BETWEEN_FILES; ?><br />
			<textarea name="ignored_files" cols="90" rows="5"><?php echo $ignored_files; ?></textarea>
			</td>
		</tr>
		<tr>
			<th colspan="3">
			<?php echo _VH_HTACCESS_FILE; ?>
			</th>
		</tr>
		<tr>
			<td colspan="3">
				<textarea name="htaccess" readonly="readonly" cols="90" rows="7"><?php echo $htaccess; ?></textarea>
			</td>
		</tr>
		<tr>
			<th colspan="3">
			<?php echo _VH_INSERT_TAG; ?>
			</th>
		</tr>
		<tr>
			<td colspan="3">
				<textarea name="htaccess" readonly="readonly" cols="90" rows="10"><?php echo $tag; ?></textarea>
			</td>
		</tr>
		</table>
		
		<input type="hidden" name="task" value="">
		</form>
		<?php
	}
	
	function massAction($cid, $selection='') {
		
		?>
		<form action="index.php?<?php echo $selection; ?>" method="post" name="adminForm">
		<table class="adminheading">
			<tr>
				<th><?php echo _VH_MASS_ACTION; ?></th>
			</tr>
			<tr>
			<td>
				<?php echo _VH_MASS_ACTION_TEXT; ?>
			</td>
			</tr>
		</table>	
		<table class="adminform">
		<tr>
			<th>
			<?php echo _VH_HOTLINK_TYPE; ?>
			</th>
			<th>
			<?php echo _VH_ANSWER_TYPE; ?>
			</th>
		</tr>
		<tr>
			<td>
				<select name="link_type">
					<option value="" ><?php echo _VH_NO_ACTION; ?></option>
					<option value="default" ><?php echo _VH_DEFAULT; ?></option> 
					<option value="partner" ><?php echo _VH_PARTNER; ?></option>
					<option value="linked" ><?php echo _VH_RECIPROCAL_LINK; ?></option>
					<option value="link_ask" ><?php echo _VH_ASKED_LINK; ?></option>
					<option value="nolink" ><?php echo _VH_WITHOUT_RECIPROCAL_LINK; ?></option>
					<option value="other" ><?php echo _VH_OTHER; ?></option>  
				</select>
			</td>
			<td>
				<select name="answer_type">
					<option value="" ><?php echo _VH_NO_ACTION; ?></option>
					<option value="sendpic" ><?php echo _VH_SEND_PIC; ?></option>
					<option value="replace" ><?php echo _VH_REPLACE_PIC; ?></option>
					<option value="waterm" ><?php echo _VH_AD_WATERMARK; ?></option>
					<option value="dontsend" ><?php echo _VH_DONT_SEND; ?></option>
					<option value="redirect" ><?php echo _VH_REDIRECT; ?></option> 					
				</select><br />
				<?php echo _VH_REPLACE_FILE; ?><br />
				<input type="text" name="replace_file" size= "40" value="<?php echo $row->replace_file; ?>" /><br />
				<?php echo _VH_REDIRECT_URL; ?><br />
				<input type="text" name="redirect_url" size="40" value="<?php echo $row->redirect_url; ?>" />
			</td>
		</tr>
		</table>
		
		<?php 
		for ($i=0, $n=count($cid); $i < $n; $i++) {
			echo "<input type='hidden' name='cid[]' value='" . $cid[$i] . "' />";
		}
		?>
		<input type="hidden" name="task" value="">
		</form>
			
		<?php
	}
	
	function button ($image, $task, $select_text, $task_text) {
		global $mosConfig_live_site;
		
		?>
		
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo $select_text; ?>'); } else { submitform('<?php echo $task; ?>');}">
				<img src="<?php echo $mosConfig_live_site.$image; ?>"  alt="<?php echo $task_text; ?>" name="<?php echo $task; ?>" title="<?php echo $task_text; ?>" align="middle" border="0" /><br /><?php echo $task_text; ?></a>
		</td>
		<td>&nbsp;</td>
		<?php 
	}
	
	function button2 ($image, $task, $task_text) {
		global $mosConfig_live_site;
		
		?>
		
		<td>
			<a class="toolbar" href="javascript:submitform('<?php echo $task; ?>');">
				<img src="<?php echo $mosConfig_live_site.$image; ?>"  alt="<?php echo $task_text; ?>" name="<?php echo $task; ?>" title="<?php echo $task_text; ?>" align="middle" border="0" /><br /><?php echo $task_text; ?></a>
		</td>
		<td>&nbsp;</td>
		<?php 
	}
	
	
}

?>