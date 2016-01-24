<?php
/*
 *	QUOTEBOARD
 *	Get Board
 *
 *	Displays a single board, called from single-quote.php
 */

if ( has_post_thumbnail() ) {
	$board_thumbnail	= wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumb-small' );
	$board_thumbnail	= '<img src="' . $board_thumbnail[0] . '">';
} else {
	$board_thumbnail 	= get_default_thumbnail( 'thumb-small' );
}

echo '
<li>
	<a href="' . get_permalink() . '">' .
		$board_thumbnail . 
		get_the_title() . '
	</a>
</li>';