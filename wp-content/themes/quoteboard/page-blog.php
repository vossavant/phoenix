<?php
/*
 *	QUOTEBOARD
 *	Blog Homepage Template
 *
 *	Template Name: Blog 
 */

include( TEMPLATEPATH . '/header.php' );

echo '<section class="content">';

$query = new WP_Query( array( 'post_type' => 'post' ) );

if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
	// make "read more" work with WP_Query
	global $more;
	$more = 0;

	// post thumbnail (featured image)
	if ( has_post_thumbnail() ) {
		$post_thumbnail		= wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		$post_thumbnail_src	= $post_thumbnail[0];
	} else {
		$post_thumbnail_src	= get_field( 'default_thumbnail', 'option' );
	}

	echo '
	<article class="post">
		<a href="' . get_permalink() . '"><img src="' . TIMTHUMB_PATH . $post_thumbnail_src . '&w=100&h=100" alt="' . get_the_title() . '" /></a>
		<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>
		<p class="meta">Posted ' . get_the_time( 'F j, Y' ) . '</p>';
		the_content( 'Read more...' );
		echo '
	</article>';
endwhile; endif;

wp_reset_postdata();

echo '</section> <!-- // content -->';

include( TEMPLATEPATH . '/sidebar-blog.php');

include( TEMPLATEPATH . '/footer.php' );