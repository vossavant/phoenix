<?php
/*
 *	QUOTEBOARD
 *	Login User via Facebook
 *
 *	Called after a user is authenticated and signed into Facebook.
 *  Checks the user token against the one stored in the DB. The token
 *	should match unless the user is trying something sneaky.
 */

if ( is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'You are already signed in!' ) );
	exit;
endif;

$form_name      = $_POST['form_name'];
$access_token   = sanitize_text_field( $_POST['token'] );
$user_id        = sanitize_text_field( $_POST['user_id'] );

if ( $form_name != 'login-facebook' || !isset( $access_token ) || !isset( $user_id ) || $user_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// check auth token
global $wpdb;
if ( $wpdb->get_var( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'facebook_token' AND meta_value = '$access_token'" ) ) {
	// programmatically log the user in
	wp_clear_auth_cookie();
	wp_set_current_user( $user_id );
	wp_set_auth_cookie ( $user_id );

	echo json_encode( array( 'redirect_to' => $_SERVER['HTTP_REFERER'] ) );

} else {
	echo json_encode( array( 'errors' => 'Your auth token is invalid. Please try again.' ) );
}

exit;