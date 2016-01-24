<?php
/*
 *	QUOTEBOARD
 *	Remove Profile Photo
 *
 *	Removes a profile or background photo from a user or board profile
 *	by setting the thumbnail id to the default image's ID.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

error_reporting(E_ALL ^ E_WARNING);

// make sure form data is non-negative integer
$board_id 	= absint( $_POST['bid'] );
$remove_bg 	= absint( $_POST['bg'] );
$user_id 	= absint( $_POST['uid'] );
$errors 	= '';

global $wpdb;

// if removing from a board profile form
if ( !empty( $board_id ) ) {
	// make sure current user authored this board
	$can_edit = $wpdb->get_var( "SELECT post_status FROM $wpdb->posts WHERE ID = '" . $board_id . "' AND post_author = '" . get_current_user_id() . "'" );

	if ( !$can_edit ) :
		echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
		exit;
	endif;

	// determine which meta field to update
	if ( $remove_bg === 1 ) {
		$meta_key 	= 'background_image';
		$default 	= DEFAULT_BACKGROUND;
		$default_id = DEFAULT_BACKGROUND_ID;
	} else {
		$meta_key = '_thumbnail_id';
		$default 	= DEFAULT_THUMBNAIL;
		$default_id = DEFAULT_THUMBNAIL_ID;
	}

	// get id of profile image before it is set to default
	$existing_profile_image = (int)get_post_meta( $board_id, $meta_key, true );

	// don't let people remove the default
	if ( $existing_profile_image == $default_id ) {
		echo json_encode( array( 'errors' => 'Sorry, you cannot delete the default.' ) );
		exit();
	}

	// update post meta reference to point to default image
	// syntax: $wpdb->update( [table], [SET column => value], [WHERE column => value])
	if ( !$update_thumbnail_id = $wpdb->update( 'wp_postmeta', array( 'meta_value' => $default_id ), array( 'post_id' => $board_id, 'meta_key' => $meta_key ) ) ) {
		$errors .= 'There was a problem updating the board photo to the default';
	}

// if removing from a user profile form
} elseif ( !empty( $user_id ) ) {
	if ( $user_id != get_current_user_id() ) :
		echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
		exit;
	endif;

	// determine which meta field to update
	if ( $remove_bg === 1 ) {
		$meta_key 	= 'user_background';
		$default 	= DEFAULT_BACKGROUND;
		$default_id = DEFAULT_BACKGROUND_ID;
	} else {
		$meta_key 	= 'wp_user_avatar';
		$default 	= DEFAULT_THUMBNAIL;
		$default_id = DEFAULT_THUMBNAIL_ID;
	}

	$existing_profile_image = get_user_meta( $user_id, $meta_key, true );

	if ( $existing_profile_image == $default_id ) {
		echo json_encode( array( 'errors' => 'Sorry, you cannot delete the default.' ) );
		exit();
	}

	if ( !$update_thumbnail_id = $wpdb->update( 'wp_usermeta', array( 'meta_value' => $default_id ), array( 'user_id' => $user_id, 'meta_key' => $meta_key ) ) ) {
		$errors .= 'There was a problem updating the user photo to the default';
	}
}

// trash the previously uploaded profile image
if ( is_numeric( $existing_profile_image ) ) {
	if ( wp_delete_attachment( $existing_profile_image ) === false ) {
		$errors .= 'There was a problem trashing the previous photo';
	}
}

if ( $update_thumbnail_id ) {
	echo json_encode(
		array(
			'full_img'	=> $default,
			'remove_bg' => $remove_bg,
			'thumbnail'	=> TIMTHUMB_PATH . $default . '&w=48&h=48'
		)
	);

} else {
	echo json_encode(
		array(
			'errors' => $errors
		)
	);
}

exit();