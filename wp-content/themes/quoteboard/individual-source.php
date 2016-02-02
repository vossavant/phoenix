<?php
/*
 *	QUOTEBOARD
 *	Single Source
 *
 *	Called from loop-sources.php and search.php.
 *	Displays a single source in list view.
 */
$source_id = get_the_ID();

// fetch stats
// $quotecount  = $wpdb->get_var ( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'quote_board' AND meta_value LIKE '%" . $post->ID . "%'" );

// thumbnail
if ( has_post_thumbnail() ) {
	$source_thumbnail	= wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
	$source_thumbnail	= '<img src="' . $source_thumbnail[0] . '" alt="' . get_the_title() . '">';
} else {
	$source_thumbnail	= get_default_thumbnail( 'thumbnail' );
}

if ( !$source_description = get_the_content() ) {
	$source_description = '<em>Alas, this source has no description.</em>';
}

echo
'<article class="board box">
	<a class="avatar" href="' . get_permalink() . '">' . $source_thumbnail . '</a>
	<div>
		<div class="bubble" data-link="' . get_permalink() . '">
			<h4>' . get_the_title() . '</h4>
			<p>' . $source_description . '</p>
		</div>
	</div>
</article>';