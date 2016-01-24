<?php
/*
 *	QUOTEBOARD
 *	Send board invite emails to members
 *
 *	Called after a user has added invitees to a queue (to invite them to a board)
 *	and clicked "Send Invites". This script takes all of the queued emails and
 *	calls Mandrill's API to send them.
 *
 *	TO DO: create account notifications for QB members
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

$emails = $_POST['emails'];
$board_id = absint( $_POST['board_id'] );

// make sure all form data is present and proper
if ( empty( $emails ) || !is_array( $emails ) || empty( $board_id ) || $board_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// breaking this out separately for better legibility
if ( empty( $_POST['form_name'] ) || $_POST['form_name'] != 'invite-member-send' ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

//$board_id = 27;								// TESTING only
//require_once( '../../../../wp-load.php' );	// TESTING only
// specify email contents, etc
$board_name			= get_the_title( $board_id );
$board_permalink 	= get_permalink( $board_id );
$sender_name		= get_user_by( 'id', get_current_user_id() )->display_name;
$email_html			= qb_build_email_html( $board_name, $board_permalink, $sender_name );
$email_text 		= qb_build_email_text( $board_name, $board_permalink, $sender_name );
$email_subject		= 'You are invited to join the board "' . $board_name . '"';
$result 			= array();

/*
echo $email_html;
echo json_encode( array( 'file_content' => $email_html ) );
exit;
*/

// load up Mandrill dependencies
require_once( 'mandrill/send.php' );

// emails should already be legit (they were validated on user input), but check again
foreach ( $emails as $email_to_address ) {
	if ( $email_to_address == filter_var( $email_to_address, FILTER_SANITIZE_EMAIL ) && filter_var( $email_to_address, FILTER_VALIDATE_EMAIL ) ) {
		
		/*
		 *	qb_generate_invite_url() returns false if an invite was already sent
		 *	to the current email; i.e., silently skip emails that already have
		 *	pending invitations
		 */
		if ( $invitation_url = qb_generate_invite_url( $email_to_address, $board_id ) ) {

			// email belongs to a current member
			if ( $recipient = get_user_by( 'email', $email_to_address ) ) {
				$email_to_name 	= $recipient->display_name;
				$email_tags		= array( 'invite-board' );

			// email is being sent to a non-member
			} else {
				$email_to_name	= 'Fellow Quoter';
				$email_tags		= array( 'invite-board', 'invite-user' );
				$non_membr_lang	= '<p><strong>What\'s Quoteboard</strong>, you ask? <a href="link-to-more-info">Read our story</a> to find out more, or click the invite link above to create your free account and start following your first board.</p>';

				$email_html = str_replace( '{{NON_MEMBER_LANGUAGE}}', $non_membr_lang, $email_html );
				$email_text = str_replace( '{{NON_MEMBER_LANGUAGE}}', $non_membr_lang, $email_text );
			}

			// parse name placeholder
			$email_html = str_replace( '{{NAME}}', $email_to_name, $email_html );
			$email_text = str_replace( '{{NAME}}', $email_to_name, $email_text );

			// parse invite URL placeholder
			$email_html = str_replace( '{{INVITE_URL}}', $invitation_url, $email_html );
			$email_text = str_replace( '{{INVITE_URL}}', $invitation_url, $email_text );

			// parse non-member language placeholder
			$email_html = str_replace( '{{NON_MEMBER_LANGUAGE}}', '', $email_html );
			$email_text = str_replace( '{{NON_MEMBER_LANGUAGE}}', '', $email_text );

			// send the email
			$result[] = mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to_address, $email_to_name, $email_tags );

			// if an exception was thrown, notify admin
			if ( $result[0]['mandrill_error'] ) {
				qb_notify_admin_mandrill_error( $result[0]['mandrill_error'], $emails, $email_to_address, $board_id );
				break;
			}
		} else {
			// TO DO: optional: return a different status indicating invitations are pending. For now, we just return "sent" to keep things simple.
			//$result[] = 'already-sent';
			$result[0][0] = array( 'status' => 'sent' );
		}
	}
}

// return result for jQuery goodness
echo json_encode(
	array(
		'result' => $result
	)
);

exit;


/*
 *	This function loads the master template and replaces the
 *	placeholder values with text specific to a board invitation.
 */
function qb_build_email_html( $board_name, $board_permalink, $sender_name ) {
	$email_header = "You're Invited!";
	$email_notification = '<p>To turn off notifications like this, sign into your account and go to <i>Edit Profile &rsaquo; Notifications</i>.<br />Uncheck the box labeled <i>When someone invites me to a board</i>.</p>';
	$email_body = '
	<p>Hi {{NAME}},</p>
	<p>Your friend ' . $sender_name . ' invited you to collaborate on the <a href="' . $board_permalink . '"><strong>' . $board_name . '</strong></a> board on Quoteboard. If you\'d like to accept this invitation, click on the link below:</p>
	<p><a href="{{INVITE_URL}}">{{INVITE_URL}}</a></p>
	{{NON_MEMBER_LANGUAGE}}
	<p>If you accept this invitation, you\'ll be able to add and remove quotes on <strong>' . $board_name . '</strong>. You may stop following this board at any time.</p>
	<p>If you don\'t want to accept this invitation, simply ignore this email.</p>';

	$template = @file_get_contents( get_template_directory_uri() . '/includes/mandrill/templates/master-html.html' );
	
	if ( $template ) {
		$template = str_replace( '{{HEADER}}', $email_header, $template );
		$template = str_replace( '{{BODY}}', $email_body, $template );
		$template = str_replace( '{{NOTIFICATION}}', $email_notification, $template );
	}

	return $template;
}


/*
 *	Same as previous function, except it loads the plain text template.
 */
function qb_build_email_text( $board_name, $board_permalink, $sender_name ) {
	$email_header = strtoupper( "You're Invited!" );
	$email_notification = "To turn off notifications like this, sign into your account and go to Edit Profile > Notifications.\r\n\r\nUncheck the box labeled WHEN SOMEONE INVITES ME TO A BOARD.";
	$email_body = "
Hi {{NAME}},\r\n\r\n
Your friend $sender_name invited you to collaborate the $board_name [1] board on Quoteboard. If you'd like to accept this invitation, copy the link below and paste it into your browser's address bar.\r\n\r\n	
{{INVITE_URL}}\r\n\r\n
{{NON_MEMBER_LANGUAGE}}
Once you accept this invitation, you\'ll be able to add and remove quotes on $board_name. You may stop following this board at any time.\r\n\r\n
If you don't want to accept this invitation, simply ignore this email.\r\n\r\n
[1] $board_permalink\r\n\r\n
[2] link-to-more-info\r\n\r\n
[3] link-to-more-info\r\n\r\n";

	$template = @file_get_contents( get_template_directory_uri() . '/includes/mandrill/templates/master-html.html' );
	
	if ( $template ) {
		$template = str_replace( '{{HEADER}}', $email_header, $template );
		$template = str_replace( '{{BODY}}', $email_body, $template );
		$template = str_replace( '{{NOTIFICATION}}', $email_notification, $template );
	}

	return $template;
}


/*
 *	Generates a unique invitation URL, which is stored in a table
 *	along with the invitees email address. When clicked, this
 *	unique URL takes people to a page that adds them to the board.
 */
function qb_generate_invite_url( $email, $board_id ) {
	// generate 32 character random string
	$invite_code = trim( base64_encode( time() ), '=' );
	$invite_code = hash_hmac( 'sha1', $invite_code, $email );
	$invite_code = substr( $invite_code, 0, 32 );

	// check if invitee is a member
	if ( get_user_by( 'email', $email ) ) {
		$is_member = 1;
	} else {
		$is_member = 0;
	}

	global $wpdb;

	// if user already has a pending invite, exit
	if ( $wpdb->get_var( "SELECT invite_code FROM wp_qb_invite_codes WHERE invite_email = '$email' AND board_id = '$board_id'" ) ) {
		return false;

	// else create a new invite record
	} else {
		$create_invite_record = $wpdb->insert(
			wp_qb_invite_codes,
			array(
				'invite_email'	=> $email,
				'invite_code'	=> $invite_code,
				'invite_date'	=> date( 'Y-m-d H:i:s' ),
				'board_id'		=> $board_id,
				'is_member'		=> $is_member
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d'
			)
		);

		// build a complete invite URL
		if ( $create_invite_record ) {
			$invite_url = get_bloginfo( 'home' ) . '/invite/?' . $invite_code;
		} else {
			$invite_url = '';
		}

		return $invite_url;
	}
}


/*
 *	Sends a notification email to the site admin if there was a thrown exception
 *	while attempting to send an email.
 */
function qb_notify_admin_mandrill_error( $exception, $email_list, $single_email, $board_id ) {
	$email_html 		= '
	<h2>Mandrill Exception Thrown - Board Invite</h2>
	<p>A Mandrill exception was thrown just now, while a user was attempting to invite people to a board: </p>
	<p><strong>' . $exception . '</strong></p>
	<p>The email list being sent was:</p>
	<ul>';

	foreach( $email_list as $email ) {
		$email_html .= '<li>' . $email . '</li>';
	}

	$email_html .= '</ul><p>The send failed on the email <strong>' . $single_email . '</strong> and the board in question was <strong>' . get_the_title( $board_id ) . '</strong>.';

	$email_list;
	$email_text 		= $email_html;
	$email_subject 		= 'Mandrill Exception Thrown';
	$email_to_address 	= get_option( 'admin_email' );
	//$email_to_name 		= 'Quoteboard Admin';
	//$email_tags 		= array( 'mandrill-error' );

	//mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to_address, $email_to_name, $email_tags );
	// TO DO: test that this works (live server)
	mail( $email_to_address, $email_subject, $email_html, "From: noreply@quoteboard.com" );
}