<?php
/*
 *	QUOTEBOARD
 *	Edit User Profile
 *
 *	Updates a user profile.
 *
 *	Once the data is updated, it is echoed back to utility.js via JSON,
 *	where it is then used to update the user profile on the page.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'error' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

$user_id = get_current_user_id();

// gather and sanitize form fields
$display_name 		= sanitize_text_field( $_POST['display_name'] );
$email_address		= sanitize_email( $_POST['email'] );
$biography			= sanitize_text_field( $_POST['biography'] );
$profile_photo 		= $_FILES['profile_photo'];
$profile_bg_photo	= $_FILES['profile_bg_photo'];
$profile_photo_id	= null;
$bg_photo_id		= null;
$is_same_bg			= 'false';

// check for required fields
if ( empty( $display_name ) ) {
	echo json_encode( array( 'errors' => 'Please enter a pen name.' ) );
	exit;
}

// docs: http://codex.wordpress.org/Function_Reference/update_user_meta
update_user_meta( $user_id, 'nickname', $display_name );
update_user_meta( $user_id, 'description', $biography );

// nickname and display name should be identical
wp_update_user( array(
	'ID'			=> $user_id,
	'display_name'	=> $display_name,
	'user_email'	=> $email_address
	)
);

// upload photos
if ( isset( $profile_photo ) ) {
	$upload_field 	= 'profile_photo';
	$meta_field 	= 'wp_user_avatar';
	$default_id		= DEFAULT_THUMBNAIL_ID;
	$upload_title 	= 'User Profile Photo';
	require( TEMPLATEPATH . '/includes/upload-photo.php' );

	// link avatar with user (so it's recognized by WP User Avatar)
	add_post_meta( $profile_photo_id, '_wp_attachment_wp_user_avatar', $user_id );
} else {
	$profile_photo_id = get_user_meta( $user_id, 'wp_user_avatar', true );
}

if ( isset( $profile_bg_photo ) ) {
	$upload_field 	= 'profile_bg_photo';
	$meta_field 	= 'user_background';
	$default_id		= DEFAULT_BACKGROUND_ID;
	$upload_title 	= 'User Background';
	require( TEMPLATEPATH . '/includes/upload-photo.php' );
} else {
	$bg_photo_id 	= get_user_meta( $user_id, 'user_background', true );
	$is_same_bg		= 'true';
}

// update user photo id
update_user_meta( $user_id, 'wp_user_avatar', $profile_photo_id );
update_user_meta( $user_id, 'user_background', $bg_photo_id );

// return params for jQuery
echo json_encode(
	array(
		'name' 			=> $display_name,
		'description'	=> wp_unslash( wpautop( $biography ) ),
		'profile_photo'	=> TIMTHUMB_PATH . wp_get_attachment_url( $profile_photo_id ) . '&w=200&h=200',
		'bg_photo'		=> wp_get_attachment_url( $bg_photo_id ),
		'is_user_page'	=> $_POST['isa'],
		'is_same_bg'	=> $is_same_bg
	)
);

exit();