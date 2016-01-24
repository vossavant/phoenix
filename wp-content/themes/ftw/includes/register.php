<?php
/*
 *  QUOTEBOARD
 *  Register User
 *
 *  Creates a new user account from the "Join" form
 *	Also creates a user from the "Register Invite" form
 */

if ( is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'You are already signed in!' ) );
	exit;
endif;

// check honeypot
if ( !empty( $_POST['location'] ) ) :
	exit;
endif;

// gather and sanitize form fields
$user_email     = sanitize_email( $_POST['email'] );
$user_password  = sanitize_text_field( $_POST['password'] );
$user_username  = sanitize_user( $_POST['username'] );
$invite_code	= sanitize_text_field( $_POST['invite_code'] );

// check for required fields
if ( empty( $user_email ) ) {
	echo json_encode( array( 'errors' => 'Please enter an email address' ) );
	exit;
}

if ( empty( $user_password ) ) {
	echo json_encode( array( 'errors' => 'Please enter a password' ) );
	exit;
}

if ( empty( $user_username ) ) {
	echo json_encode( array( 'errors' => 'Please enter a username' ) );
	exit;
}

if ( email_exists( $user_email ) ) {
	echo json_encode( array( 'errors' => 'The email address you entered is already in use; please try another' ) );
	exit;
}

if ( strlen( $user_password ) < MINIMUM_PASSWORD_LENGTH ) {
	echo json_encode( array( 'errors' => 'Your password is too short (it must be at least ' . MINIMUM_PASSWORD_LENGTH . ' characters)' ) );
	exit;
}

if ( strlen( $user_username ) < MINIMUM_USERNAME_LENGTH ) {
	echo json_encode( array( 'errors' => 'Your username is too short (it must be at least ' . MINIMUM_USERNAME_LENGTH . ' characters)' ) );
	exit;
}

if ( username_exists( $user_username ) ) {
	echo json_encode( array( 'errors' => 'The username you entered is already in use; please try another' ) );
	exit;
}

// create the user (this should never return false since we already checked against all possible errors)
$new_user_id = wp_create_user( $user_username, $user_password, $user_email );

// move user to "Author" role
$new_user_updated = wp_update_user( array(
	'ID'    => $new_user_id,
	'role'  => 'author'
	)
);

// add meta indicating user has not yet logged in (to trigger the welcome dialog on 1st login)
add_user_meta( $new_user_id, 'show_tutorial', 1 );
add_user_meta( $new_user_id, 'show_tutorial_board', 1 );

// there was a problem creating the user
if ( is_wp_error( $new_user_id ) || is_wp_error( $new_user_updated ) ) {
	echo json_encode(
		array(
			'result' => false,
			'errors' => 'There was a problem completing your registration: ' . $new_user_id->get_error_message() . ', ' . $new_user_updated->get_error_message()
			//'errors' => $authenticate->get_error_message() // this isn't formatted ideally
		)
	);

// else, send welcome email and auto-sign in the new user
} else {
	/*
	 *	Send welcome email
	 */
	// load up Mandrill dependencies
	require_once( 'mandrill/send.php' );

	$email_html 		= qb_build_email_html( '', $user_email );
	$email_text 		= qb_build_email_text( '', $user_email );
	$email_subject 		= 'Welcome to Quoteboard';
	$email_to_address 	= $user_email;
	$email_to_name 		= '';
	$email_tags 		= array( 'welcome' );

	// send the email (check superadmin ID to avoid mail() errors in localhost)
	if ( SUPERADMIN_USER_ID == 2 ) {
		$result[] = mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to_address, $email_to_name, $email_tags );
	} else {
		$result[] = true;
	}

	// if an exception was thrown, notify admin
	if ( $result[0]['mandrill_error'] ) {
		qb_notify_admin_mandrill_error( $result[0]['mandrill_error'], $email_to_address );
		break;
	}


	/*
	 *	If user registered from an invite form, add user to invited board
	 */
	if ( $invite_code ) {
		global $wpdb;
		if ( $board_id = $wpdb->get_var( "SELECT board_id FROM wp_qb_invite_codes WHERE invite_email = '$user_email' AND invite_code = '$invite_code'" ) ) {
			$field_key	= 'field_5077ae931b6f6';
			$value		= get_field( $field_key, $board_id );
			$value[] 	= array( 'board_members_user' => $new_user_id, 'can_collaborate' => 'n' );
			
			if ( !update_field( $field_key, $value, $board_id ) ) {
				echo json_encode( array( 'errors' => 'There was a problem adding a follower with ID ' . $new_user_id . ' to board #' . $board_id ) );
				exit;
			} else {
				// if successfully added, delete invite code
				$wpdb->delete(
					wp_qb_invite_codes,
					array(
						'invite_email' 	=> $user_email,
						'invite_code'	=> $invite_code
					),
					array(
						'%s',
						'%s'
					)
				);
			}
		} else {
			echo json_encode( array( 'errors' => 'The invite code and email specified do not match' ) );
			exit;
		}
	}

	// finish registration
	require( TEMPLATEPATH . '/includes/register-finish.php' );
}



/*
 *	This function loads the master template and replaces the
 *	placeholder values with text specific to a board invitation.
 */
function qb_build_email_html( $first_name, $user_email ) {
	$email_header = "Welcome!";
	$email_body = '
	<p>Thank you for joining Quoteboard. We&rsquo;re excited to have you on board as we build the most fun and engaging platform in the world for finding and sharing quotes.</p>
	<p>We&rsquo;re just getting started, so there are bound to be things we can do better. We welcome your honest feedback; tweet at us <a href="https://twitter.com/quoteboard">@quoteboard</a> and tag your experiences with <a href="https://twitter.com/hashtag/qbux">#qbux</a>, or email us at <a href="mailto:feedback@quoteboard.com">feedback@quoteboard.com</a>.</p>
	<p>Happy quoting!</p>';

	$template = @file_get_contents( get_template_directory_uri() . '/includes/mandrill/templates/master-html.html' );
	
	if ( $template ) {
		$template = str_replace( '{{HEADER}}', $email_header, $template );
		$template = str_replace( '{{BODY}}', $email_body, $template );
		$template = str_replace( '{{NOTIFICATION}}', '', $template );
	}

	return $template;
}


/*
 *	Same as previous function, except it loads the plain text template.
 */
function qb_build_email_text( $first_name, $user_email ) {
	$email_header = 'WELCOME!';
	$email_body = "
Thank you for joining Quoteboard. We&rsquo;re excited to have you on board as we build the most fun and engaging platform in the world for finding and sharing quotes.\r\n\r\n
We&rsquo;re just getting started, so there are bound to be things we can do better. We welcome your honest feedback; tweet at us @quoteboard [1] and tag your experiences with #qbux [2], or email us at feedback@quoteboard.com.\r\n\r\n

Happy quoting!\r\n\r\n

[1] https://twitter.com/quoteboard\r\n\r\n
[2] https://twitter.com/hashtag/qbux\r\n\r\n";

	$template = @file_get_contents( get_template_directory_uri() . '/includes/mandrill/templates/master-html.html' );
	
	if ( $template ) {
		$template = str_replace( '{{HEADER}}', $email_header, $template );
		$template = str_replace( '{{BODY}}', $email_body, $template );
		$template = str_replace( '{{NOTIFICATION}}', '', $template );
	}

	return $template;
}


/*
 *	Sends a notification email to the site admin if there was a thrown exception
 *	while attempting to send an email.
 */
function qb_notify_admin_mandrill_error( $exception, $email ) {
	$email_html = '
	<h2>Mandrill Exception Thrown - Welcome Email</h2>
	<p>A Mandrill exception was thrown just now, while the system was attempting to send a welcome email to a new member: </p>
	<p><strong>' . $exception . '</strong></p>
	<p>The send failed on the email <strong>' . $email . '</strong>.';

	$email_text 		= $email_html;
	$email_subject 		= 'Mandrill Exception Thrown';
	$email_to_address 	= get_option( 'admin_email' );
	//$email_to_name 		= 'Quoteboard Admin';
	//$email_tags 		= array( 'mandrill-error' );

	//mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to_address, $email_to_name, $email_tags );
	// TO DO: test that this works (live server)
	mail( $email_to_address, $email_subject, $email_html, "From: noreply@quoteboard.com" );
}

exit();