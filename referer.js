	
	/****************************************************\
	**   VisoHotlink 								    **
	**   Copyright (C) 2006 by Arnaud LECUS & VISOCREA  **
	**   Version    : 1.0                               **
	**   Homepage   : http://www.visohotlink.org        **
	**	 Mail : arnaud.lecus@visocrea.fr				**
	**   Released Under GNU GPL Public License          **
	\****************************************************/
	
try {ref = top.document.referrer;} catch(e) {
		try {ref = document.referrer;} catch(E) {ref = '';}
	}
document.writeln('<img src="'+ VisoHotlinkURL +'?ref='+escape(ref)+'&page='+escape(document.location)+'" alt="VisoHotlink" style="border:0" />');