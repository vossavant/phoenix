<?php
/*
 *	QUOTEBOARD
 *	Edit Board Profile
 *
 *	Updates a board profile
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// make sure board ID is a non-negative integer
$board_id = absint( $_POST['bid'] );

if ( $board_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed (invalid board ID).' ) );
	exit;
endif;

// make sure current user authored this board
global $wpdb;
$can_edit = $wpdb->get_var( "SELECT post_status FROM $wpdb->posts WHERE ID = '" . $board_id . "' AND post_author = '" . get_current_user_id() . "'" );

if ( !$can_edit ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// gather and sanitize form fields
$board_name 		= sanitize_text_field( $_POST['board_name'] );
$board_slug			= sanitize_title( $board_name );
$board_description	= sanitize_text_field( $_POST['board_description'] );
$board_category_id	= absint( $_POST['board_category'] );
$board_photo 		= $_FILES['profile_photo'];
$board_bg_photo		= $_FILES['profile_bg_photo'];
$profile_photo_id	= null;
$bg_photo_id		= null;

// check for required fields
if ( empty( $board_name ) ) {
	echo json_encode( array( 'errors' => 'Please give your board a name' ) );
	exit;
}

/* holding off on categories for now
if ( empty( $board_category_id ) ) {
	echo json_encode( array( 'errors' => 'Please add your board to a category' ) );
	exit;
}
*/

// prepare for update
$update_parameters	= array(
	'ID'			=> $board_id,
	'post_content'	=> $board_description,
	'post_name'		=> $board_slug,
	'post_title'	=> $board_name
	//'tax_input'		=> array( 'board_cats' => $board_category_id )
);

// do the update
$board_updated_id	= wp_update_post( $update_parameters );

if ( $board_updated_id ) {

	if ( isset( $board_photo ) ) {
		$upload_field 	= 'profile_photo';
		$meta_field 	= '_thumbnail_id';
		$default_id		= DEFAULT_THUMBNAIL_ID;
		$upload_title 	= 'Board Profile Photo';
		require( TEMPLATEPATH . '/includes/upload-photo.php' );
	} else {
		$profile_photo_id = get_post_thumbnail_id( $board_id );
	}

	if ( isset( $board_bg_photo ) ) {
		$upload_field 	= 'profile_bg_photo';
		$meta_field 	= 'background_image';
		$default_id		= DEFAULT_BACKGROUND_ID;
		$upload_title 	= 'Board Background';
		require( TEMPLATEPATH . '/includes/upload-photo.php' );
	} else {
		$bg_photo_id = get_field( 'background_image', $board_id );
	}

	// return params for jQuery
	echo json_encode(
		array(
			'name'	 		=> $board_name,
			'description'	=> wp_unslash( wpautop( $board_description ) ),
			'category_id'	=> $board_category_id,
			'profile_photo'	=> TIMTHUMB_PATH . wp_get_attachment_url( $profile_photo_id ) . '&w=200&h=200',
			'bg_photo'		=> wp_get_attachment_url( $bg_photo_id )
		)
	);

	exit();
}