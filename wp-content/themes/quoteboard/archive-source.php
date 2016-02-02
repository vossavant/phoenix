<?php
/*
 *	QUOTEBOARD
 *	Source Archives Template
 */

include( TEMPLATEPATH . '/header.php' );

$paged = ( get_query_var('paged') ? get_query_var('paged') : 1 );

echo '
<section class="main">
	<h3>Source Archive <span>Page ' . $paged . '</span></h3>';

	$sources = new WP_Query(
		array(
			'paged' 		=> $paged,
			'posts_per_page'=> RESULTS_PER_PAGE,
			'post_status'	=> 'publish',
			'post_type' 	=> 'source'
		)
	);

	include( TEMPLATEPATH . '/loop-sources.php' );

	echo '
</section>';

include( TEMPLATEPATH . '/footer.php' );