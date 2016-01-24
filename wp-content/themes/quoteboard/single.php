<?php
/*
 *	QUOTEBOARD
 *	Single Post Template
 */

include( TEMPLATEPATH . '/header.php' );

echo '<section class="content">';

if ( have_posts() ) : while ( have_posts() ) : the_post();
	// post thumbnail (featured image)
	if ( has_post_thumbnail() ) {
		$post_thumbnail		= wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		$post_thumbnail_src	= $post_thumbnail[0];
	} else {
		$post_thumbnail_src	= get_field( 'default_thumbnail', 'option' );
	}

	echo '
	<article class="post">
		<h2>' . get_the_title() . '</h2>
		<p class="meta">Posted ' . get_the_time( 'F j, Y' ) . '</p>
		<img class="post-featured" src="' . TIMTHUMB_PATH . $post_thumbnail_src . '&w=550" alt="' . get_the_title() . '" />';
		the_content();
		echo '
	</article>';
endwhile; endif;


echo '</section> <!-- // content -->';

include( TEMPLATEPATH . '/sidebar-blog.php');

include( TEMPLATEPATH . '/footer.php' );