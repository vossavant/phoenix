<?php
/**
 *	QUOTEBOARD
 *	Character Loop
 *
 *	Displays all the characters returned from a particular query.
 */

if ( $characters ) :

	while ( $characters->have_posts() ) :
		$characters->the_post();

		// fetch layout for single chaaracter
		include( TEMPLATEPATH . '/individual-chaaracter.php' );

	endwhile;

	// pagination
	qb_paginate();
	// $query = $characters;
	// include( TEMPLATEPATH . '/includes/pagination.php' );

else:
	echo '<p class="message info"><strong>Dagnabit!</strong>There are no chaaracters on this page</p>';
endif;