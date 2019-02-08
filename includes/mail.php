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
* Function to create a mail object for futher use (uses phpMailer)
* @param string From e-mail address
* @param string From name
* @param string E-mail subject
* @param string Message body
* @return object Mail object
*/
function mosCreateMail( $from='', $fromname='', $subject, $body ) {
	global $mosConfig_absolute_path, $mosConfig_mailer, $mosConfig_mailfrom;
	global $mosConfig_smtpauth, $mosConfig_smtpuser, $mosConfig_smtppass, $mosConfig_smtphost;
	global $mosConfig_sendmail;
	
	$mosConfig_fromname = 'Visohotlink';
	
	include_once($mosConfig_absolute_path .'/includes/phpmailer/class.phpmailer.php');
	include_once($mosConfig_absolute_path .'/includes/phpmailer/class.smtp.php');
	
	$mail = new mosPHPMailer();

	$mail->PluginDir = $mosConfig_absolute_path .'/includes/phpmailer/';
	$mail->SetLanguage( 'en', $mosConfig_absolute_path . '/includes/phpmailer/language/' );
	$mail->CharSet 	= substr_replace(_ISO, '', 0, 8);
	$mail->IsMail();
	$mail->From 	= $from ? $from : $mosConfig_mailfrom;
	$mail->FromName = $fromname ? $fromname : $mosConfig_fromname;
	$mail->Mailer 	= $mosConfig_mailer;
	
	// Add smtp values if needed
	if ( $mosConfig_mailer == 'smtp' ) {
		$mail->SMTPAuth = $mosConfig_smtpauth;
		$mail->Username = $mosConfig_smtpuser;
		$mail->Password = $mosConfig_smtppass;
		$mail->Host 	= $mosConfig_smtphost;
	} else

	// Set sendmail path
	if ( $mosConfig_mailer == 'sendmail' ) {
		if (isset($mosConfig_sendmail))
			$mail->Sendmail = $mosConfig_sendmail;
	} // if
	
	$mail->Subject 	= $subject;
	$mail->Body 	= $body;

	return $mail;
}

/**
* Mail function (uses phpMailer)
* @param string From e-mail address
* @param string From name
* @param string/array Recipient e-mail address(es)
* @param string E-mail subject
* @param string Message body
* @param boolean false = plain text, true = HTML
* @param string/array CC e-mail address(es)
* @param string/array BCC e-mail address(es)
* @param string/array Attachment file name(s)
* @param string/array ReplyTo e-mail address(es)
* @param string/array ReplyTo name(s)
*/
function mosMail( $from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) {
	
	$mosConfig_debug = false;
	
	$mail = mosCreateMail( $from, $fromname, $subject, $body );

	// activate HTML formatted emails
	if ( $mode ) {
		$mail->IsHTML(true);
	}

	if (is_array( $recipient )) {
		foreach ($recipient as $to) {
			$mail->AddAddress( $to );
		}
	} else {
		$mail->AddAddress( $recipient );
	}
	if (isset( $cc )) {
		if (is_array( $cc )) {
			foreach ($cc as $to) {
				$mail->AddCC($to);
			}
		} else {
			$mail->AddCC($cc);
		}
	}
	if (isset( $bcc )) {
		if (is_array( $bcc )) {
			foreach ($bcc as $to) {
				$mail->AddBCC( $to );
			}
		} else {
			$mail->AddBCC( $bcc );
		}
	}
	if ($attachment) {
		if (is_array( $attachment )) {
			foreach ($attachment as $fname) {
				$mail->AddAttachment( $fname );
			}
		} else {
			$mail->AddAttachment($attachment);
		}
	}
	//Important for being able to use mosMail without spoofing...
	if ($replyto) {
		if (is_array( $replyto )) {
			reset( $replytoname );
			foreach ($replyto as $to) {
				$toname = ((list( $key, $value ) = each( $replytoname )) ? $value : '');
				$mail->AddReplyTo( $to, $toname );
			}
        } else {
			$mail->AddReplyTo($replyto, $replytoname);
		}
    }

	$mailssend = $mail->Send();

	if( $mosConfig_debug ) {
		//$mosDebug->message( "Mails send: $mailssend");
	}
	if( $mail->error_count > 0 ) {
		//$mosDebug->message( "The mail message $fromname <$from> about $subject to $recipient <b>failed</b><br /><pre>$body</pre>", false );
		//$mosDebug->message( "Mailer Error: " . $mail->ErrorInfo . "" );
	}
	return $mailssend;
} // mosMail
?>
