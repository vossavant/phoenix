<?php
/*
 *	QUOTEBOARD
 *	Board Archives Template
 */

include( TEMPLATEPATH . '/header.php' );

$paged = ( get_query_var('paged') ? get_query_var('paged') : 1 );

echo '
<section class="main">
	<h3>Board Archive <span class="pagenum">Page ' . $paged . '</span></h3>';

	$boards = new WP_Query(
		array(
			'paged' 		=> $paged,
			'posts_per_page'=> RESULTS_PER_PAGE,
			'post_status'	=> 'publish',
			'post_type' 	=> 'board'
		)
	);

	include( TEMPLATEPATH . '/loop-boards.php' );

	echo '
</section>';

include( TEMPLATEPATH . '/footer.php' );