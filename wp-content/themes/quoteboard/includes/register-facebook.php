<?php
/*
 *  QUOTEBOARD
 *  Register User via Facebook
 *
 *  Creates a new user account from the "Join" form when a user clicks
 *	"Sign Up with Facebook". Facebook sends back an object containing
 *	some user info, which we use here to populate the new account.
 *
 *	A random password is generated, and an email sent (via Mandrill)
 *	to the new user welcoming them to Quoteboard.
 */

if ( is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'You are already signed in!' ) );
	exit;
endif;

$facebook_id	= sanitize_text_field( $_POST['user_id'] );
$facebook_token	= sanitize_text_field( $_POST['token'] );

if ( $_POST['form_name'] != 'register-facebook' || empty( $facebook_id ) || $facebook_id === 0 || empty( $facebook_token ) || $facebook_token === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// gather and sanitize form fields
$user_email     = sanitize_email( $_POST['email'] );
$first_name		= sanitize_text_field( $_POST['first_name'] );
$last_name		= sanitize_text_field( $_POST['last_name'] );
$full_name		= sanitize_text_field( $_POST['full_name'] );
$facebook_url	= sanitize_text_field( $_POST['fb_url'] );
$facebook_photo	= sanitize_text_field( $_POST['fb_photo'] );
$cover_photo	= sanitize_text_field( $_POST['cover'] );
$user_nicename	= sanitize_title_with_dashes( $full_name );
$user_username  = $user_email;
//$invite_code	= sanitize_text_field( $_POST['invite_code'] );

// edge cases
// TO DO: allow for case where user wants to link an account, not register a new one
// should be as simple as "Do you want to link your account?" and then add facebook_id and facebook_token
if ( email_exists( $user_email ) ) {
	echo json_encode( array( 'errors' => 'The email address associated with your Facebook account is already in use; perhaps you already signed up?' ) );
	exit;
}

/*
 *	Nicename is generated from the person's full name, which is unlikely
 *	to be unique. If there is a conflict, append a number. Recursively
 *	check until nicename is unique.
 */
global $wpdb;
function qb_create_unique_nicename( $nicename, $suffix ) {
	global $wpdb;

	if ( $wpdb->get_var( "SELECT ID FROM $wpdb->users WHERE user_nicename = '$nicename'" ) ) {
		if ( $suffix == 1 ) {
			$nicename .= '-' . $suffix;
		} else {
			$nicename = substr( $nicename, 0, strlen( $suffix ) * -1 ) . $suffix;
		}

		$suffix++;

		return qb_create_unique_nicename( $nicename, $suffix );
	}

	return $nicename;
}

if ( $wpdb->get_var( "SELECT ID FROM $wpdb->users WHERE user_nicename = '$user_nicename'" ) ) {
	$user_nicename = qb_create_unique_nicename( $user_nicename, 1 );
}

// randomly generate a password
$user_password = wp_generate_password();

// create the user (this should never return false since we already checked against all possible errors)
if ( $new_user_id = wp_create_user( $user_username, $user_password, $user_email ) ) :
	/*
	 *	Update user role to "Author", set custom fields
	 */
	wp_update_user( array(
		'ID'			=> $new_user_id,
		'display_name'	=> $full_name,
		'role' 			=> 'author',
		'user_nicename' => $user_nicename
		)
	);

	// add facebook ID and token
	update_user_meta( $new_user_id, 'facebook_id', $facebook_id );
	update_user_meta( $new_user_id, 'facebook_token', $facebook_token );

	// set additional user meta
	update_user_meta( $new_user_id, 'first_name', $first_name );
	update_user_meta( $new_user_id, 'last_name', $last_name );
	update_user_meta( $new_user_id, 'nickname', $full_name );

	// add meta indicating user has not yet logged in (to trigger the welcome dialog on 1st login)
	add_user_meta( $new_user_id, 'show_tutorial', 1 );
	add_user_meta( $new_user_id, 'show_tutorial_board', 1 );

	// set Facebook URL custom field
	update_field( 'field_53a0f339a0d1f', $facebook_url, 'user_' . $new_user_id );


	/*
	 *	Get profile photo (thanks to Prebhdev Singh / Auto Save Remote Image plugin)
	 *	TO DO: as of 12/8/14 this is not working - adds to media library ok but does not grab photo URL
	 */
	$get 	= wp_remote_get( $facebook_photo );
	$type 	= wp_remote_retrieve_header( $get, 'content-type' );
	$mirror = wp_upload_bits( rawurldecode( basename( $facebook_photo ) ), '', wp_remote_retrieve_body( $get ) );

	// add image file as an attachment
	$attachment = array(
		'post_mime_type'	=> $type,
		'post_title' 		=> 'User Profile Photo',
		'post_content' 		=> '',
		'post_status' 		=> 'inherit'
	);

	// add image to media library
	$attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	// set profile photo as WP User Avatar
	update_user_meta( $new_user_id, 'wp_user_avatar', $attach_id );

	// set user as author of uploaded image
	wp_update_post( array( 'ID' => $attach_id, 'post_author' => $new_user_id ) );

	// link avatar with user
	add_post_meta( $attach_id, '_wp_attachment_wp_user_avatar', $new_user_id );


	/*
	 *	Get cover/background photo
	 *	TO DO: as of 12/8/14 this is not working - adds to media library ok but does not grab photo URL
	 */
	$get 	= wp_remote_get( $cover_photo );
	$type 	= wp_remote_retrieve_header( $get, 'content-type' );
	$mirror = wp_upload_bits( rawurldecode( basename( $cover_photo ) ), '', wp_remote_retrieve_body( $get ) );

	// add image file as an attachment
	$attachment = array(
		'post_mime_type'	=> $type,
		'post_title' 		=> 'User Background',
		'post_content' 		=> '',
		'post_status' 		=> 'inherit'
	);

	// add image to media library
	$attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	// set background image ID as custom field
	update_field( 'field_5320e08f8d2fd', $attach_id, 'user_' . $new_user_id );

	// set user as author of uploaded image
	wp_update_post( array( 'ID' => $attach_id, 'post_author' => $new_user_id ) );


	/*
	 *	Send welcome email via Mandrill API
	 */
	// load up Mandrill dependencies
	require_once( 'mandrill/send.php' );

	$email_html 		= qb_build_email_html( $first_name, $user_email );
	$email_text 		= qb_build_email_text( $first_name, $user_email );
	$email_subject 		= 'Welcome to Quoteboard';
	$email_to_address 	= $user_email;
	$email_to_name 		= $full_name;
	$email_tags 		= array( 'welcome', 'welcome-facebook' );

	// send the email
	$result[] = mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to_address, $email_to_name, $email_tags );

	// if an exception was thrown, notify admin
	if ( $result[0]['mandrill_error'] ) {
		qb_notify_admin_mandrill_error( $result[0]['mandrill_error'], $email_to_address );
	}

	// finish registration
	require( TEMPLATEPATH . '/includes/register-finish.php' );

else :
	echo json_encode(
		array(
			'result' => false,
			'errors' => 'There was a problem completing your registration: ' . $new_user_id->get_error_message()
		)
	);
endif;


/*
 *	This function loads the master template and replaces the
 *	placeholder values with text specific to a new user registration.
 */
function qb_build_email_html( $first_name, $user_email ) {
	$email_header = "Welcome!";
	$email_body = '
	<p>' . $first_name . ',</p>
	<p>Thank you for joining Quoteboard. We&rsquo;re excited to have you on board as we build the most fun and engaging platform in the world for finding and sharing quotes.</p>
	<p>We&rsquo;re just getting started, so there are bound to be things we can do better. We welcome your honest feedback; tweet at us <a href="https://twitter.com/quoteboard">@quoteboard</a> and tag your experiences with <a href="https://twitter.com/hashtag/qbux">#qbux</a>, or email us at <a href="mailto:feedback@quoteboard.com">feedback@quoteboard.com</a>.</p>
	<p style="font-size: 16px; margin-top: 2em;"><strong>Important Account Info</strong></p>
	<p>Since you signed up with your Facebook account, you can always use the "Sign in with Facebook" option when signing in, and never need to use your Quoteboard username and password.</p>
	<p>If you ever want to sign into Quoteboard without signing into Facebook, use the following:</p>
	<ul>
		<li><strong>Username: </strong>' . $user_email . '</li>
		<li><strong>Password: </strong>use the "Forgot password" option to reset**
	</ul>
	<p><small>**For security, your Quoteboard password was randomly generated and is unknown to anyone. <strong>You will never need this password <em>unless</em></strong> you decide to skip the "Sign in with Facebook" option.</small></p>
	
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
$first_name,\r\n\r\n
Thank you for joining Quoteboard. We&rsquo;re excited to have you on board as we build the most fun and engaging platform in the world for finding and sharing quotes.\r\n\r\n
We&rsquo;re just getting started, so there are bound to be things we can do better. We welcome your honest feedback; tweet at us @quoteboard [1] and tag your experiences with #qbux [2], or email us at feedback@quoteboard.com.\r\n\r\n\r\n\r\n
IMPORTANT ACCOUNT INFO\r\n\r\n
Since you signed up with your Facebook account, you can always use the --Sign in with Facebook-- option when signing in, and never need to use your Quoteboard username and password.\r\n\r\n
If you ever want to sign into Quoteboard without signing into Facebook, use the following:\r\n\r\n
  -- Username: $user_email\r\n\r\n
  -- Password: use the --Forgot password-- option to reset**\r\n\r\n
**For security, your Quoteboard password was randomly generated and is unknown to anyone. YOU WILL NEVER NEED THIS PASSWORD unless you decide to skip the --Sign in with Facebook-- option.\r\n\r\n

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
	<h2>Mandrill Exception Thrown - Welcome Email (FB)</h2>
	<p>A Mandrill exception was thrown just now, while the system was attempting to send a welcome email to a new member: </p>
	<p><strong>' . $exception . '</strong></p>
	<p>The send failed on the email <strong>' . $email . '</strong>.';

	$email_text 		= $email_html;
	$email_subject 		= 'Mandrill Exception Thrown';
	$email_to_address 	= get_option( 'admin_email' );
	//$email_to_name 		= 'Quoteboard Admin';
	//$email_tags 		= array( 'mandrill-error' );

	//mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to_address, $email_to_name, $email_tags );
	@mail( $email_to_address, $email_subject, $email_html, "From: noreply@quoteboard.com" );
}

exit();