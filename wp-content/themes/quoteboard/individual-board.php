<?php
/*
 *	QUOTEBOARD
 *	Single Board
 *
 *	Called from loop-boards.php and search.php.
 *	Displays a single board in list view.
 */
$board_id = get_the_ID();
$is_board_curator = false;
$follow_button = '';

// fetch stats
$quotecount  = $wpdb->get_var ( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'quote_board' AND meta_value LIKE '%" . $post->ID . "%'" );
$followcount = get_post_meta( $board_id, 'board_members', true );

// if admin authored, it's a public board (this will change)
if ( $post->post_author == SUPERADMIN_USER_ID ) {
	$is_public = true;
} else {
	$is_public = false;
}

// check if default
$is_default = $wpdb->get_var( "SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = 'is_default' AND post_id = '$board_id' AND meta_value = 'yes'" );

// thumbnail
if ( has_post_thumbnail() ) {
	$board_thumbnail	= wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
	$board_thumbnail	= '<img src="' . $board_thumbnail[0] . '" alt="' . get_the_title() . '">';
} else {
	$board_thumbnail	= get_default_thumbnail( 'thumbnail' );
}

if ( !$board_description = get_the_content() ) {
	$board_description = '<em>Alas, this board has no description.</em>';
}

echo
'<article class="board box">
	<a class="avatar" href="' . get_permalink() . '">' . $board_thumbnail . '</a>
	<div>
		<div class="bubble" data-link="' . get_permalink() . '">
			<h4>' . get_the_title() . '</h4>
			<p>' . $board_description . '</p>
		</div>
	</div>
</article>';