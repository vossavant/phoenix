<?php
/*
 *	QUOTEBOARD
 *	Requote
 *
 *	Duplicates a quote and assigns the duplicate to a new board
 *
 *	TO DO: probably a good idea to prevent people from requoting the same quote
 *	to the same board
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

date_default_timezone_set ( 'America/Denver' );

// gather and sanitize form fields
$quote_id 	= absint( $_POST['reqid'] );
$board_id 	= absint( $_POST['quote_board'] );

// check for required fields
if ( empty( $board_id ) || $board_id === 0 ) {
	echo json_encode( array( 'errors' => 'Please choose a board.' ) );
	exit;
}

if ( empty( $quote_id ) || $quote_id === 0 ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

// make sure user is requoting to a board he owns or belongs to
global $wpdb;
$user_id = get_current_user_id();
$is_user_member = 
   "SELECT *
	FROM $wpdb->posts
	INNER JOIN $wpdb->postmeta ON ID = post_id
	WHERE meta_key LIKE 'board_members_%_board_members_user'
	AND ID = '$board_id'
	AND meta_value = '$user_id'
	AND post_status <> 'trash'
	AND post_type = 'board'
	ORDER BY post_date DESC";

if ( !$wpdb->get_results( $is_user_member ) ) {
	echo json_encode( array( 'errors' => 'You can only requote to a board of which you are a member.' ) );
	exit;
}

// now that we're sure this is a valid submission, fetch main info for the quote to be copied
$query =
   "SELECT post_content, post_title
	FROM $wpdb->posts
	WHERE ID = '" . $quote_id . "'";

// last check to make sure users aren't being sneaky
if ( !$original_quote = $wpdb->get_row( $query ) ) {
	echo json_encode( array( 'errors' => 'Miraculously, the quote you are trying to requote does not exist.' ) );
	exit;
}

// fetch quote tags
$quote_tags = get_the_terms( $quote_id, 'post_tag' );
$tag_array	= array();
$post_tags	= '';

if ( $quote_tags ) {
	foreach ( $quote_tags as $tag ) {
		$tag_array[] = $tag->name;
	}
	
	$post_tags = join( ', ', $tag_array );
}

// take the retrieved data and use it to create a new copy
$new_quote = array (
	'post_author'	=> $user_id,
	'post_content'	=> $original_quote->post_content,
	'post_name'		=> sanitize_title( $original_quote->post_title ),
	'post_status'	=> 'publish',
	'post_title'	=> $original_quote->post_title,
	'post_type'		=> 'quote',
	'tags_input'	=> $post_tags
);

// insert the copied quote and update custom fields
if ( $new_quote_id = wp_insert_post( $new_quote ) ) {

	$quote_author	= get_post_meta( $quote_id, 'quote_author', true );
	$quote_source	= get_post_meta( $quote_id, 'quote_source', true );
	$quote_context	= get_post_meta( $quote_id, 'quote_context', true );

	// add custom fields
	if ( $quote_author ) {
		add_post_meta( $new_quote_id, 'quote_author', $quote_author );
		add_post_meta( $new_quote_id, '_quote_author', 'field_507658884a81f' );
	}

	if ( $quote_source ) {
		add_post_meta( $new_quote_id, 'quote_source', $quote_source );
		add_post_meta( $new_quote_id, '_quote_source', 'field_507658884b202' );
	}

	if ( $quote_context ) {
		add_post_meta( $new_quote_id, 'quote_context', $quote_context );
		add_post_meta( $new_quote_id, '_quote_context', 'field_507658884b8f3' );
	}

	// set board to the one the requoting user chose
	add_post_meta( $new_quote_id, 'quote_board', $board_id );
	add_post_meta( $new_quote_id, '_quote_board', 'field_5077ae14b09b9' );

	// set "requoted from" to the ID of the original quote
	add_post_meta( $new_quote_id, 'requoted_from', $quote_id );
	add_post_meta( $new_quote_id, '_requoted_from', 'field_51a64fcead85b' );
	
	// update "requoted on" for original quote to include the new board
	$previous_requotes = get_post_meta( $quote_id, 'requoted_to', true );

	// special case: if no previous requotes, variable returned will be an empty string
	if ( empty( $previous_requotes ) ) {
		$previous_requotes[0] = (string)$board_id;
	} else {
		array_push( $previous_requotes, (string)$board_id );
	}

	// send back updated requote count
	if ( update_post_meta( $quote_id, 'requoted_to', $previous_requotes ) ) {
		echo json_encode( array(
			'board_id' => $board_id
		) );
	}

} else {
	echo json_encode(
		array(
			'errors' => 'There was a problem requoting this quote'
		)
	);
}

exit;