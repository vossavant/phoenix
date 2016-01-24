<?php
/*
 *	QUOTEBOARD
 *	Quote Archives Template (presently, only set up for tags)
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

	$quotes = get_posts(
		array(
			'posts_per_page'=> -1,
			'post_status'	=> 'publish',
			'post_type' 	=> 'quote',
			'tag'			=> $url_endpoint
		)
	);

	include( TEMPLATEPATH . '/loop-quotes.php' );

	echo '
</section>';

include( TEMPLATEPATH . '/footer.php' );