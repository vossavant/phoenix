<?php
/*
 *	QUOTEBOARD
 *	Add Board
 *
 *	Adds a new board. Loaded when a user clicks the "New Board" button.
 *	Also adds all of the board author's followers as members of the board.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// set default timezone
date_default_timezone_set ( 'America/Denver' );

// gather and sanitize form fields
$board_user_id 		= get_current_user_id();
$board_name 		= sanitize_text_field( $_POST['board_name'] );
$board_slug			= sanitize_title( $board_name );
$board_description	= sanitize_text_field( $_POST['board_description'] );
//$board_category_id	= absint( $_POST['board_category'] );
$board_photo 		= $_FILES['profile_photo'];
$board_bg_photo		= $_FILES['profile_bg_photo'];
$profile_photo_id	= null;
$bg_photo_id		= null;

// check for required fields
if ( empty( $board_name ) ) {
	echo json_encode( array( 'errors' => 'Please give your board a name' ) );
	exit;
}

/*
if ( empty( $board_category_id ) ) {
	echo json_encode( array( 'errors' => 'Please add your board to a category' ) );
	exit;
}*/

// prepare for insert
$insert_parameters = array (
	'post_author'	=> $board_user_id,
	'post_content'	=> truncate( $board_description, 500 ),
	'post_name'		=> $board_slug,
	'post_status'	=> 'publish',
	'post_title'	=> $board_name,
	'post_type'		=> 'board',
	'tax_input' 	=> array( 'board_cats' => array( $board_category_id ) )
);

// insert the post
$board_added_id = wp_insert_post( $insert_parameters );

if ( $board_added_id ) {

	if ( isset( $board_photo ) ) {
		$upload_field 	= 'profile_photo';
		$meta_field 	= '_thumbnail_id';
		$upload_title 	= 'Board Profile Photo';
		$board_id 		= $board_added_id;
		require( TEMPLATEPATH . '/includes/upload-photo.php' );

		add_post_meta( $board_added_id, '_thumbnail_id', $profile_photo_id );
	} else {
		add_post_meta( $board_added_id, '_thumbnail_id', DEFAULT_THUMBNAIL_ID );
	}

	if ( isset( $board_bg_photo ) ) {
		$upload_field 	= 'profile_bg_photo';
		$meta_field 	= 'background_image';
		$upload_title 	= 'Board Background';
		$board_id 		= $board_added_id;
		require( TEMPLATEPATH . '/includes/upload-photo.php' );

		//update_field( 'field_52e58bcbd6d1d', $bg_photo_id, $board_added_id );
	} else {
		//update_field( 'field_52e58bcbd6d1d', DEFAULT_BACKGROUND_ID, $board_added_id );
	}

	//add_post_meta( $board_added_id, '_background_image', 'field_52e58bcbd6d1d' );
	update_field( 'field_52e58bcbd6d1d', DEFAULT_BACKGROUND_ID, $board_added_id );

	// add board members
	require( TEMPLATEPATH . '/includes/add-board-members.php' );

	// return params for jQuery
	echo json_encode(
		array(
			'permalink' => get_permalink( $board_added_id )
		)
	);

	exit();

} else {
	echo json_encode(
		array(
			'errors' => 'There was a problem adding your board'
		)
	);
}