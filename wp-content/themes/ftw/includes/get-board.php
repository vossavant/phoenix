<?php
/*
 *	QUOTEBOARD
 *	Get Board
 *
 *	Displays a single board, called from single-quote.php
 */

if ( has_post_thumbnail() ) {
	$board_thumbnail		= wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
	$board_thumbnail_src	= $board_thumbnail[0];
} else {
	$board_thumbnail_src	= get_field( 'default_thumbnail', 'option' );
}

echo '
<li>
	<a href="' . get_permalink() . '">
		<img src="' . TIMTHUMB_PATH . $board_thumbnail_src . '&w=48&h=48" alt="' . get_the_title() . '" />' .
		get_the_title() . '
	</a>
</li>';