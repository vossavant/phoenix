<?php
/*
 *	QUOTEBOARD
 *	Check Email
 *
 *	Checks the user table for existence of a specific email
 */

if ( $_POST['form_name'] != 'check-email' ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

$email = sanitize_email( $_POST['email'] );

if ( !empty( $email ) && filter_var( $email, FILTER_VALIDATE_EMAIL ) !== false ) {
	if ( email_exists( $email ) ) {
		echo json_encode( array( 'result' => 'exists' ) );
	} else {
		echo json_encode( array( 'result' => 'unique' ) );
	}
} else {
	echo json_encode( array( 'result' => 'invalid' ) );
}

exit;