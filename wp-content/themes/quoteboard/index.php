<?php
/**
 *	QUOTEBOARD
 *	Home Template
 *
 *	The default display; loads home page (logged out) and quote feed
 *	(logged in).
 */


/**
 *	Load header this way vs get_header() so header has access to all variables
 */
include( TEMPLATEPATH . '/header.php' );

echo
'<section id="hero">
	<div class="overlay"></div>
	<div>
		<h1>Quoteboard</h1>
		<h2>The world\'s best way to find and share quotes.</h2>';
		get_search_form();

		echo
	'</div> <!-- // inner wrapper -->
</section> <!-- // hero -->';


/**
 *	Show a list of curated boards
 */
if ( $curated_boards = get_field( 'curated_boards', 'option' ) ) :
	echo 
	'<section class="main extra wide">
		<h3>Boards We Like <span><a href="' . home_url() . '/boards">See More Boards &raquo;</a></span></h3>
		<div class="flex curated boards">';

		foreach ( $curated_boards as $key => $board ) :
			foreach ( $board['board'] as $key => $value) : // I don't know why this nested foreach is needed...
				if ( has_post_thumbnail( $value->ID ) ) {
					$board_thumbnail	= wp_get_attachment_image_src( get_post_thumbnail_id( $value->ID ), 'board-thumb' );
					$board_thumbnail	= '<img src="' . $board_thumbnail[0] . '">';
				} else {
					$board_thumbnail 	= get_default_thumbnail( 'board-thumb' );
				}

				echo
				'<article>
					<a class="avatar" href="' . get_permalink( $value->ID ) . '">' .
						$board_thumbnail .
					'</a>
					<a href="' . get_permalink( $value->ID ) . '">
						<div>
							<h4>' . $value->post_title . '</h4>
							<p>' . mb_strimwidth( $value->post_content, 0, 100, '...' ) . '</p>
						</div>
					</a>
				</article>';
			endforeach;
		endforeach;

		echo
		'</div>
	</section>';
endif;


/**
 *	Show a list of all quotes from all members
 */
echo
'<section class="off-white">
	<div class="main extra wide">
		<div class="flex">
			<div class="flex-child flex-60">
				<h3>Latest Quotes</h3>';

				$quotes = new WP_Query(
					array(
						'posts_per_page'	=> 10,
						'post_status'		=> 'publish',
						'post_type' 		=> 'quote'
					)
				);

				// $quotes = new WP_Query(
				// 	array(
				// 		'posts_per_page'	=> 10, //RESULTS_PER_PAGE,
				// 		'post_type' 		=> 'quote'
				// 	)
				// );

				include( TEMPLATEPATH . '/loop-quotes.php' );
				
				echo
				'<a class="btn right" href="' . home_url() . '/quotes">More Quotes &raquo;</a>
			</div>

			<div class="flex-child flex-40">
				<h3 style="margin-left: 30px;">Notable Quoters</h3>';

				if ( $curated_authors = get_field( 'curated_authors', 'option' ) ) :
					foreach ( $curated_authors as $key => $author ) :
						foreach ( $author['author'] as $key => $value) : // I don't know why this nested foreach is needed...
							// get author avatar
							if ( !$author_avatar = get_wp_user_avatar( $value['ID'], 'thumbnail' ) ) {
								$author_avatar = get_default_thumbnail( 'thumbnail' );
							}

							// get user background
							if ( $background_id = get_user_meta( $value['ID'], 'user_background', true ) ) :
								$upload_directory 	= wp_upload_dir();
								$background_src 	= $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '" . $background_id . "' AND meta_key = '_wp_attached_file'" );
								$author_cover_photo = $upload_directory['baseurl'] . '/' . $background_src;
							else : 
								$author_cover_photo = DEFAULT_BACKGROUND;
							endif;

							// get author quote counts
							$author_quote_count 	= $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'quote_author' AND meta_value = '" . $value['ID'] . "'" );

							echo
							'<div class="profile static" data-link="' . home_url('/') . 'author/' . $value['user_nicename'] . '">
								<div class="cover" style="background: url(' . $author_cover_photo . ') center no-repeat;">' .
									$author_avatar . 
									'<div>
										<div>
											<h3>' . $value['display_name'] . '</h3>
											<ul>
												<li>
													<span class="ico quote"></span>' . $author_quote_count . ' Quote' . ( $author_quote_count != 1 ? 's' : '' ) .
												'</li>
												<li>
													<span class="ico fave"></span>' . $author_fave_count . ' Favorite' . ( $author_fave_count != 1 ? 's' : '' ) .
												'</li>
											</ul>
										</div>
									</div>
								</div>
								<div class="meta">' . wpautop( $value['user_description'] ) . '</div>
							</div>';
						endforeach;
					endforeach;
				endif;

				echo
			'</div>
		</div>
	</div>
</section>';


/**
 *	Loading footer this way vs get_footer() gives us access to the $current_user variable
 */
include( TEMPLATEPATH . '/footer.php' );