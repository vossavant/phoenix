<?php
/*
 *	QUOTEBOARD
 *	Check Username
 *
 *	Checks the user table for existence of a specific username
 */

if ( $_POST['form_name'] != 'check-username' ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

$username = sanitize_user( sanitize_title_with_dashes( $_POST['username'] ) );

if ( !empty( $username ) ) {
	if ( username_exists( $username ) ) {
		echo json_encode( array( 'result' => 'exists' ) );
	} elseif ( strlen( $username ) < MINIMUM_USERNAME_LENGTH ) {
		echo json_encode( array( 'result' => 'too short' ) );
	} else {
		echo json_encode( array( 'result' => 'unique' ) );
	}
} else {
	echo json_encode( array( 'result' => 'invalid' ) );
}

exit;