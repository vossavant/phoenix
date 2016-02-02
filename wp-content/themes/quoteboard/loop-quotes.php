<?php
/** TEMPLATE PART: Quotes Loop
	Loops through quotes in the DB. Prior to this segment being loaded,
	a get_posts, query_posts, or wpdb query filter is set to limit results.
	**/

/** this bit is compatible with WP_Query (which might be needed for pagination - I'm not yet sure) */
if ( $quotes->have_posts() ) :

	while ( $quotes->have_posts() ) :
		$quotes->the_post();

		// fetch layout for single quote
		include( TEMPLATEPATH . '/individual-quote.php' );

	endwhile;

	// this pagination works on archives
	qb_paginate();

	// this pagination *kinda* works on, e.g., single-board.php
	// $query = $quotes;
	// include( TEMPLATEPATH . '/includes/pagination.php' );

else :
	echo '<p class="message shown info">Alack... there are no quotes on this page.</p>';
endif;


/** this bit is compatible with get_posts() */
// if ( $quotes ) {
// 	foreach ( $quotes as $post ) : setup_postdata( $post );
//  		include( TEMPLATEPATH . '/individual-quote.php' );
// 	endforeach;

// 	// pagination
// 	qb_paginate();
// 	//$query = $quotes;
// 	//include( TEMPLATEPATH . '/includes/pagination.php' );

// 	wp_reset_postdata();
// } else {
// 	echo '<p class="message shown info"><strong>Alack, alack!</strong> There are no quotes on this page.</p>';
// }