<?php
/*
 *	QUOTEBOARD
 *	Check Facebook ID
 *
 *	Checks the usermeta table for the existence of a specific Facebook ID.
 *	If the ID exists, the user has previously signed into the QB site via
 *	Facebook. If not, this is the user's first time signing in with FB.
 *
 *	TO DO: prevent direct script access
 */

$form_name 		= $_POST['form_name'];
$access_token 	= sanitize_text_field( $_POST['token'] );
$fb_user_id 	= sanitize_text_field( $_POST['user_id'] );

if ( $form_name != 'check-facebook-id' || !isset( $access_token ) || !isset( $fb_user_id ) || $fb_user_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

global $wpdb;

// if user exists, update his access token
if ( $wp_user_id = $wpdb->get_var( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'facebook_id' AND meta_value = '$fb_user_id'" ) ) {
	update_user_meta( $wp_user_id, 'facebook_token', $access_token );
} else {
	$wp_user_id = false;
}

echo json_encode( array( 'user_id' => $wp_user_id ) );
exit;