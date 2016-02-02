<?php
/**
 *	QUOTEBOARD
 *	Source Loop
 *
 *	Displays all the sources returned from a particular query.
 */

/** this bit is compatible with WP_Query (which might be needed for pagination - I'm not yet sure) */
if ( $sources ) :

	while ( $sources->have_posts() ) :
		$sources->the_post();

		// fetch layout for single source
		include( TEMPLATEPATH . '/individual-source.php' );

	endwhile;

	// pagination
	qb_paginate();
	// $query = $sources;
	// include( TEMPLATEPATH . '/includes/pagination.php' );

else:
	echo '<p class="message info"><strong>Dagnabit!</strong>There are no sources on this page</p>';
endif;