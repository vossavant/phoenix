<?php
/*
 *	QUOTEBOARD
 *	Quote Archives Template
 */

include( TEMPLATEPATH . '/header.php' );

$paged = ( get_query_var('paged') ? get_query_var('paged') : 1 );

echo '
<section class="main">
	<h3>Quote Archive <span class="pagenum">Page ' . $paged . '</span></h3>';

	$quotes = new WP_Query(
		array(
			'paged' 		=> $paged,
			'posts_per_page'=> RESULTS_PER_PAGE,
			'post_status'	=> 'publish',
			'post_type' 	=> 'quote'
			// 'tag'			=> $url_endpoint
		)
	);

	include( TEMPLATEPATH . '/loop-quotes.php' );

	echo '
</section>';

include( TEMPLATEPATH . '/footer.php' );