<?php
/*
 *	QUOTEBOARD
 *	Search Results Template
 */

// doing it this way vs get_header() gives header access to all variables
include( TEMPLATEPATH . '/header.php' );

/**
 *	Search for users separately (for now) and show first -- TO DO: tab these results
 *	More here: http://codex.wordpress.org/Class_Reference/WP_User_Query
 *	TO DO: make work with pagination
 */
$args = array (
	'role' 		=> 'member_added',
	'order' 	=> 'ASC',
	'orderby' 	=> 'display_name',
	'search' 	=> '*' . esc_attr( get_search_query() ) . '*'
	// 'meta_query' 		=> array(
	// 	'relation' 		=> 'OR',
	// 	array(
	// 		'key'     	=> 'first_name',
	// 		'value'   	=> get_search_query(),
	// 		'compare' 	=> 'LIKE'
	// 	),
	// 	array(
	// 		'key'     	=> 'last_name',
	// 		'value'   	=> get_search_query(),
	// 		'compare' 	=> 'LIKE'
	// 	)
	// )
);

$wp_user_query = new WP_User_Query($args);
$users = $wp_user_query->get_results();
$user_count = $wp_user_query->get_total();

// include user total
$total_results = $wp_query->found_posts + $user_count;

echo
'<section class="main">
	<h3>' . $total_results . ( $total_results != 1 ? ' things' : ' thing' ) . ' matched your search for &ldquo;' . get_search_query() . '&rdquo;</h3>';

	foreach ( $users as $user ) {
		include( TEMPLATEPATH . '/individual-author.php' );
	}


	// show quote and board results
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();

			if ( get_post_type() == 'quote' ) {
				// fetch layout for single quote
				include( TEMPLATEPATH . '/individual-quote.php' );
			} elseif ( get_post_type() == 'board' ) {
				// fetch layout for single board
				include( TEMPLATEPATH . '/individual-board.php' );
			}

		endwhile;

		qb_paginate();
	else:
		if ( !$user_count ) {
			echo '<p class="message shown info">We didn\'t find anything. Nuts.</p>';
		}
	endif;
echo '</section>';

// doing it this way vs get_footer() gives us access to the $current_user variable
include( TEMPLATEPATH . '/footer.php' );