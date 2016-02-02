<?php
/*
 *	QUOTEBOARD
 *	Archives Template
 */

include( TEMPLATEPATH . '/header.php' );

echo '
<section class="main">
	<h3>';
		if ( is_tag() ) :
			echo 'Quotes tagged with &ldquo;' . $wpdb->get_var( "SELECT name FROM $wpdb->terms WHERE slug = '" . $url_endpoint . "'" ) . '&rdquo;';
		else :
			echo 'Some other archive';
		endif;
		echo '
	</h3>';
	// probably better to just use WP_Query and not mess with this crap
// $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 0;
$paged = ( get_query_var('paged') ? get_query_var('paged') : 0 );
	echo 'Page: ' . $paged;
	echo '<br>Found Posts: ' . $wp_query->found_posts;
	echo '<br>Max Pages: ' . $wp_query->max_num_pages;	// found by dividing found posts by 10 (default posts per page)
	echo '<br>Offset: ' . ($paged - 1) * RESULTS_PER_PAGE;

	$quotes = get_posts(
		array(
			'paged' => $paged,
			'posts_per_page'=> RESULTS_PER_PAGE,
			'post_status'	=> 'publish',
			'post_type' 	=> 'quote',
			'offset' 		=> ( $paged - 1 ) * RESULTS_PER_PAGE
			// 'tag'			=> $url_endpoint
		)
	);

	include( TEMPLATEPATH . '/loop-quotes.php' );

	echo '
</section>';

include( TEMPLATEPATH . '/footer.php' );