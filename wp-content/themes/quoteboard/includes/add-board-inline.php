<?php
/*
 *	QUOTEBOARD
 *	Add Board on the Fly
 *
 *	Adds a new board from the "Add Quote" form. Loaded when a user selects
 *	the "Create New Board" option. Only board name and category are required.
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
//$board_category_id	= absint( $_POST['board_category'] );
$board_description	= sanitize_text_field( $_POST['board_description'] );

// check for required fields
if ( empty( $board_name ) ) {
	echo json_encode( array( 'errors' => 'Please give your board a name' ) );
	exit;
}

/* not worried about categories right now
if ( empty( $board_category_id ) ) {
	echo json_encode( array( 'errors' => 'Please add your board to a category' ) );
	exit;
}*/

// check if a board with this name already exists for this user
global $wpdb;
if ( $board_exists = $wpdb->get_var( "SELECT post_title FROM $wpdb->posts WHERE post_author = '" . $board_user_id . "' AND post_title LIKE '$board_name' AND post_status = 'publish' AND post_type = 'board'" ) ) {
	echo json_encode( array( 'errors' => 'You already have a board named "' . $board_exists . '"' ) );
	exit;
}

// prepare for insert
$insert_parameters = array (
	'post_author'	=> $board_user_id,
	'post_content'	=> truncate( $board_description, 500 ),
	'post_name'		=> $board_slug,
	'post_status'	=> 'publish',
	'post_title'	=> $board_name,
	'post_type'		=> 'board'
);

// insert the post
$board_added_id = wp_insert_post( $insert_parameters );

if ( $board_added_id ) {
	// add board members
	require( TEMPLATEPATH . '/includes/add-board-members.php' );

	// return params for jQuery
	echo json_encode(
		array(
			'board_id' => $board_added_id,
			'board_name' => $board_name
		)
	);

} else {
	echo json_encode(
		array(
			'errors' => 'There was a problem adding your board'
		)
	);
}

exit();