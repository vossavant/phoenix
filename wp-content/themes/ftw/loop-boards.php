<?php
/**
 *	QUOTEBOARD
 *	Board Loop
 *
 *	Displays all the boards returned from a particular query.
 */

/** this bit is compatible with WP_Query (which might be needed for pagination - I'm not yet sure) */
// if ( $boards ) :

// 	while ( $boards->have_posts() ) :
// 		$boards->the_post();

// 		// fetch layout for single board
// 		include( TEMPLATEPATH . '/individual-board.php' );

// 	endwhile;

// 	// pagination
// 	$query = $boards;
// 	include( TEMPLATEPATH . '/includes/pagination.php' );

// else:
// 	echo '<p class="message info"><strong>Dagnabit!</strong>There are no boards on this page</p>';
// endif;


/** this bit is compatible with get_posts() */
if ( $boards ) {
	foreach ( $boards as $post ) : setup_postdata( $post );
 		include( TEMPLATEPATH . '/individual-board.php' );
	endforeach;

	wp_reset_postdata();

} else {
	echo '<p class="message shown info"><strong>Dagnabit!</strong> There are no boards on this page.</p>';
}