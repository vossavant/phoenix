<?php
/*
 *	QUOTEBOARD
 *	Send transactional email via Mandrill API
 *
 *	Sends email invites to a particular board via the Mandrill API.
 *	Also sends beta tester feedback to the site admin.
 *
 *	Uses the /messages/send.json API call
 *	Docs: https://mandrillapp.com/api/docs/messages.php.html#method=send
 */

require_once( 'Mandrill.php' );
require_once( 'constants.php' );

function mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to_address, $email_to_name, $email_tags ) {
	try {
		$mandrill = new Mandrill( MANDRILL_API_KEY );
		$message = array(
			'html' 			=> $email_html,
			'text' 			=> $email_text,
			'subject' 		=> $email_subject,
			'from_email' 	=> EMAIL_FROM_ADDRESS,
			'from_name' 	=> EMAIL_FROM_NAME,
			'to' 			=> array(
				array(
					'email' => $email_to_address,
					'name' 	=> $email_to_name
				)
			),
			'headers' 		=> array(
				'Reply-To' 	=> 'noreply@quoteboard.com'
			),
			'important' 	=> false,
			'track_opens' 	=> true,
			'track_clicks' 	=> true,
			'auto_text' 	=> true,
			'auto_html' 	=> false,
			'inline_css' 	=> true,
			'url_strip_qs' 	=> false,
			'preserve_recipients' 	=> false,
			'view_content_link' 	=> false,
			/*'merge' => true,
			'global_merge_vars' => array(
				array(
					'name' => 'merge1',
					'content' => 'merge1 content'
				)
			),
			'merge_vars' => array(
				array(
					'rcpt' => 'recipient.email@example.com',
					'vars' => array(
						array(
							'name' => 'merge2',
							'content' => 'merge2 content'
						)
					)
				)
			),*/
			'tags' => $email_tags
		);

		$async		= false;
		$ip_pool  	= 'Main Pool';
		$send_at 	= '';     // gmdate( "Y-m-d H:i:s", mktime( 0, 0, 0, 1, 1, 2011 ) );	// date in the past = email sends immediately
		$result   	= $mandrill->messages->send( $message, $async, $ip_pool, $send_at );

		return $result;
		// $result looks something like:
		/*
		Array
		(
			[0] => Array
				(
					[email] => recipient.email@example.com
					[status] => sent
					[reject_reason] => hard-bounce
					[_id] => abc123abc123abc123abc123abc123
				)
		
		)

		// need to set up a webhook for the "reject" event for more than 10 recipients
		*/
	} catch( Mandrill_Error $e ) {
		//echo 'A mandrill error occurred: ' . get_class( $e ) . ' - ' . $e->getMessage();
		$e = array( 'mandrill_error' => $e->getMessage() );
		return $e;
		//throw $e; // need this?
	}
}