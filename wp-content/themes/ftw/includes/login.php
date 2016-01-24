<?php
/*
 *	QUOTEBOARD
 *	Login User
 *
 *	Authenticates a user and signs them into the site. Only fires when
 *	user is signing in with a standard WP account, *not* with SSO via
 *	Facebook, Twitter, etc.
 */

if ( is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'You are already signed in!' ) );
	exit;
endif;

// gather and sanitize form fields
$user_email		= sanitize_email( $_POST['username'] );
$user_password 	= sanitize_text_field( $_POST['password'] );

// check for required fields
if ( empty( $user_email ) ) {
	echo json_encode( array( 'errors' => 'Please enter your email address' ) );
	exit;
}

if ( empty( $user_password ) ) {
	echo json_encode( array( 'errors' => 'Please enter your password' ) );
	exit;
}

// create array of user data
$credentials = array(
	'user_login' 	=> $user_email,
	'user_password' => $user_password,
	'remember' 		=> true
);

// authenticate user
$authenticate = wp_signon( $credentials, false );
if ( is_wp_error( $authenticate ) ) {
    echo json_encode(
    	array(
    		'result' => false,
    		'errors' => 'Whoops! The email or password you entered is incorrect.'
    		//'errors' => $authenticate->get_error_message() // this isn't formatted ideally
    	)
    );

} else {
    echo json_encode(
    	array(
    		'result' =>	true,
    		'redirect_to' => $_SERVER['HTTP_REFERER']
    	)
    );
}

exit();