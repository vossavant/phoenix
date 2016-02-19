<?php
/*
 *	QUOTEBOARD
 *	Single Character View (mimics layout of archives page)
 */

include( TEMPLATEPATH . '/header.php' );

// get cover photo
if ( !$character_cover_image = get_field( 'character_image' ) ) {
	$character_cover_image = DEFAULT_BACKGROUND;
}

echo
'<section class="main extra wide">
	<div class="flex">
		<div class="flex-60">';

			$quotes = new WP_Query(
				array(
					'meta_query' => array(
						array(
							'key' 	=> 'quote_character',
							'value'	=> $post->ID
						)
					),
					'posts_per_page'=> RESULTS_PER_PAGE,
					'post_status'	=> 'publish',
					'post_type' 	=> 'quote'
				)
			);

			include( TEMPLATEPATH . '/loop-quotes.php' );

			wp_reset_postdata();

			echo
		'</div>

		<div class="flex-child flex-40">
			<div class="profile">
				<div class="cover" style="background: url(' . $character_cover_image . ') center no-repeat;">
					<div>
						<h3><span>Quotes attributed to the character</span>' . $post->post_title . '</h3>';
						// <ul>
						// 	<li>
						// 		<span class="ico quote"></span>' . $current_user_quotes . ' Quote' . ( $current_user_quotes != 1 ? 's' : '' ) .
						// 	'</li>
						// 	<li>
						// 		<span class="ico fave"></span>' . $current_user_faves . ' Favorite' . ( $current_user_faves != 1 ? 's' : '' ) .
						// 	'</li>
						// </ul>
					echo
					'</div>
				</div>
				<div class="meta">' . the_content() . '</div>
			</div>
			<!-- here list books/movies in which the character appears ... -->
		</div>
	</div>
</section>';

include( TEMPLATEPATH . '/footer.php' );