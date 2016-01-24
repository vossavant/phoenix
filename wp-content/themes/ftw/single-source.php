<?php
/*
 *	QUOTEBOARD
 *	Single Source View (mimics layout of archives page)
 */

include( TEMPLATEPATH . '/header.php' );

echo '
<main>
	<h3>Quotes from &ldquo;' . $post->post_title . '&rdquo;</h3>';

	$quotes = get_posts(
		array(
			'meta_query' => array(
				array(
					'key' 	=> 'quote_source',
					'value'	=> $post->ID
				)
			),
			'posts_per_page'	=> -1,
			'post_status'	=> 'publish',
			'post_type' 	=> 'quote'
		)
	);

	include( TEMPLATEPATH . '/loop-quotes.php' );

	echo '
</main>';

include( TEMPLATEPATH . '/footer.php' );