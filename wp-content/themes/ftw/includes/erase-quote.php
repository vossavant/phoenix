<?php
/*
 *	QUOTEBOARD
 *	Erase Quote
 *
 *	Trashes a single quote. Called when a quote contributor clicks
 *	the "Delete" icon on a quote he submitted.
 */

$quote_id = absint( $_POST['quote_id'] );

if ( !is_user_logged_in() || empty( $quote_id ) || $quote_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

if ( !isset( $_POST['form_name'] ) || $_POST['form_name'] != 'erase-quote' ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

global $wpdb;

// determine if user is actually the WordPress author of this quote
$quote_author_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$quote_id' AND post_type = 'quote'" );

if ( !$quote_author_id || $quote_author_id != get_current_user_id() ) {
	echo json_encode( array( 'errors' => "You can't erase a quote you didn't contribute." ) );
	exit;
}

// move quote to trash
if ( wp_trash_post( $quote_id ) ) {
	echo json_encode( array(
		'result' => true
	) );
} else {
	echo json_encode( array(
		'errors' => 'Unable to delete this quote.'
	) );
}

exit;