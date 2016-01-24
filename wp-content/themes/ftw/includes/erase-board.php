<?php
/*
 *	QUOTEBOARD
 *	Erase Board
 *
 *	Removes all members and quotes from a board and trashes the board.
 *	Called when a board author clicks the "Erase Board" link in the
 *	single-board.php dropdown.
 *
 *	Also trashes all quotes on the board. We can easily change this
 *	to permanently delete the quotes/board by using wp_delete_post().
 */

$board_id = absint( $_POST['board_id'] );

if ( !is_user_logged_in() || empty( $board_id ) || $board_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

if ( !isset( $_POST['form_name'] ) || $_POST['form_name'] != 'erase-board' ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

global $wpdb;
$user_id 	= get_current_user_id();
$user_login = get_user_by( 'id', $user_id )->user_nicename;

// determine if user is actually the author of this board
$board_author_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$board_id' AND post_type = 'board'" );

if ( !$board_author_id || $board_author_id != $user_id ) {
	echo json_encode( array( 'errors' => "You can't erase a board you didn't create." ) );
	exit;
}

// determine if this is the default board
if ( $is_default_board = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta JOIN $wpdb->posts ON post_id = ID WHERE post_id = '$board_id' AND meta_key = 'is_default' AND meta_value = 'yes' AND post_author = '$user_id'" ) ) {
	echo json_encode( array( 'errors' => "You can't erase your default board." ) );
	exit;
}

// move board to trash
if ( wp_trash_post( $board_id ) ) {

	// trash all quotes on the board
	$quotes_on_board = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'quote_board' AND meta_value = '$board_id'" );
	foreach ( $quotes_on_board as $delete ) {
		wp_trash_post( $delete->post_id );
	}

	echo json_encode( array(
		'redirect_to' => home_url( '/' ) . 'author/' . $user_login . '/boards'
	) );
} else {
	echo json_encode( array(
		'errors' => 'Unable to delete this board.'
	) );
}

exit;