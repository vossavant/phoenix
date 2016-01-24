<?php
/*
 *	QUOTEBOARD
 *	Author Quote Feed
 *	Displays all quotes by people / on boards followed by the current user
 *
 *	This template is called from author.php and is only accessed when the
 *	URL endpoint is empty (e.g., URL looks like: qb.com/ or qb.com/authorname)
 */


// grab a list of all boards followed by current user
$boards_followed = $wpdb->get_results( "
	SELECT post_id, post_title
	FROM $wpdb->postmeta
	JOIN $wpdb->posts ON post_id = ID
	WHERE INSTR ( meta_key, 'board_members_user' ) > 0
	AND meta_value = '" . $current_page_user_id . "'
	AND post_status <> 'trash'
	" );

$allquotes = array();

//print_r($boards_followed);
//exit;
// grab quotes on each board
foreach ( $boards_followed as $board_followed ) {

	$quotes_on_board = $wpdb->get_results( "
		SELECT post_id
		FROM $wpdb->postmeta
		WHERE meta_key = 'quote_board'
		AND meta_value = '$board_followed->post_id'
	" );

	foreach ( $quotes_on_board as $quote ) {
		array_push( $allquotes, $quote->post_id );
	}
}

// remove duplicates
$allquotes = array_unique( $allquotes );

// custom query, make pagination work widdit
$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
$quotes = get_posts(
	array(
		'paged' 			=> $paged,
		'post__in'			=> $allquotes,
		'posts_per_page'	=> RESULTS_PER_PAGE,
		'post_type' 		=> 'quote'
	)
);

// show welcome message to new users
if ( stristr( $_SERVER['REQUEST_URI'], '?welcome' ) ) {
	// check if user has already seen tutorial
	if ( get_user_meta( $current_user->ID, 'show_tutorial', true ) == 1 ) {
		// load scripts required for welcome message and tutorial
		wp_enqueue_script( 'intro', get_template_directory_uri() . '/js/intro.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'welcome', get_template_directory_uri() . '/js/welcome.js', array( 'intro' ), null, true );

		// load hidden welcome message
		get_template_part( 'includes/modal-welcome' );

		// update meta to prevent welcome message from reappearing
		update_user_meta( $current_user->ID, 'show_tutorial', 0 );
	}
}

// show quick add form
if ( $current_user->ID == $current_page_user_id ) {
	$inline_form_placeholder = 'Add a quote...';
	include( TEMPLATEPATH . '/forms/add-quote-inline.php' );
}

// display the quotes!
include( TEMPLATEPATH . '/loop-quotes.php' );