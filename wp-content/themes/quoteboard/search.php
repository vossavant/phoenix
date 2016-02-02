<?php
/*
 *	QUOTEBOARD
 *	Search Results Template
 */

// doing it this way vs get_header() gives header access to all variables
include( TEMPLATEPATH . '/header.php' );

$total_results = $wp_query->found_posts;

echo
'<section class="main">
	<h3>' . $total_results . ( $total_results != 1 ? ' things' : ' thing' ) . ' matched your search for &ldquo;' . get_search_query() . '&rdquo;</h3>';

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
		echo '<p class="message shown info">We didn\'t find anything. Nuts.</p>';
	endif;
echo '</section>';

// doing it this way vs get_footer() gives us access to the $current_user variable
include( TEMPLATEPATH . '/footer.php' );