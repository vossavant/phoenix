<?php
/*
 *	QUOTEBOARD
 *	Single Character
 *
 *	Called from loop-characters.php and search.php.
 *	Displays a single character in list view.
 */
$character_id = get_the_ID();

// fetch stats
// $quotecount  = $wpdb->get_var ( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'quote_board' AND meta_value LIKE '%" . $post->ID . "%'" );

// thumbnail
if ( has_post_thumbnail() ) {
	$character_thumbnail	= wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
	$character_thumbnail	= '<img src="' . $character_thumbnail[0] . '" alt="' . get_the_title() . '">';
} else {
	$character_thumbnail	= get_default_thumbnail( 'thumbnail' );
}

if ( !$character_description = get_the_content() ) {
	$character_description = '<em>Alas, this character has no description.</em>';
}

echo
'<article class="board box">
	<a class="avatar" href="' . get_permalink() . '">' . $character_thumbnail . '</a>
	<div>
		<div class="bubble" data-link="' . get_permalink() . '">
			<h4>' . get_the_title() . '</h4>
			<p>' . $character_description . '</p>
		</div>
	</div>
</article>';