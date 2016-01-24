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


/**
 *	Logged in user sees his quote feed
 */
if ( is_user_logged_in() ) :
	include( TEMPLATEPATH . '/author.php' );


/**
 *	Logged out user sees hero and latest quotes from all members
 */
else : ?>
	<section id="hero">
		<div class="overlay"></div>
		<div>
			<h1>Quoteboard</h1>
			<h2>The world's best way to find and share quotes.</h2>
			<?php get_search_form(); ?>

			<?php
			/* old design
			<footer>
				<a href="#">About</a>
				<a href="#">Leave Feedback</a>
				<a href="#">Privacy</a>
				<span>Copyright &copy; 2016 Quoteboard</span>
			</footer>
			<a class="btn secondary explore" href="#">See What's New</a>


			<div>
				<h1>Quoteboard</h1>
				<h2>The fun and easy way to share quotes with your friends.</h2>
				<a class="btn secondary explore" href="#">Explore</a>
			</div>
			<div>
				<ul class="tabs">
					<li><a class="active" href="#signin">Sign In</a></li>
					<li><a href="#join">Join</a></li>
				</ul>
				<div class="pane visible" id="signin">
					<?php get_template_part( 'forms/login' ); ?>
				</div>
				<div class="pane" id="join">
					<?php get_template_part( 'forms/join' ); ?>
				</div>

				<footer>
					<ul>
						<?php
						$menu_params = array(
							'echo'				=> true,
							'container' 		=> false,
							'items_wrap' 		=> '%3$s',
							'theme_location' 	=> 'secondary'
						);

						wp_nav_menu( $menu_params );
						?>
						
						<li>&copy; <?= date( 'Y' ); ?> Quoteboard</li>
					</ul>
				</footer>
			</div> <!-- // form wrapper -->
			*/
			?>
		</div> <!-- // inner wrapper -->
	</section> <!-- // hero -->
	
	<main class="extra wide">
		<?php
		/**
		 *	Show a list of curated boards
		 */

		if ( $curated_boards = get_field( 'curated_boards', 'option' ) ) :
			echo '<h3>Boards We Like</h3>';
			echo '<div class="flex curated boards">';

			foreach ( $curated_boards as $key => $board ) :
				foreach ( $board['board'] as $key => $value) : // I don't know why this nested foreach is needed...
					if ( has_post_thumbnail( $value->ID ) ) {
						$board_thumbnail		= wp_get_attachment_image_src( get_post_thumbnail_id( $value->ID ), 'board-thumb' );
						$board_thumbnail_src	= $board_thumbnail[0];
					} else {
						$board_thumbnail_src	= get_field( 'default_thumbnail', 'option' );
					}

					echo
					'<article>
						<a class="avatar" href="' . get_permalink( $value->ID ) . '">
							<img src="' . $board_thumbnail_src . '" alt="' . get_the_title( $value->ID ) . '" />
						</a>
						<a href="' . get_permalink( $value->ID ) . '">
							<div>
								<h4>' . $value->post_title . '</h4>
								<p>' . mb_strimwidth( $value->post_content, 0, 100, '...' ) . '</p>
							</div>
						</a>
					</article>';
				endforeach;
			endforeach;

			echo '</div>';
		endif;


		/**
		 *	Show a list of all quotes from all members
		 */
		echo '
		<div class="flex">
			<div class="flex-child flex-66">
				<h3>Latest Quotes</h3>';

				$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
				$quotes = get_posts(
					array(
						'paged' 			=> $paged,
						'posts_per_page'	=> 10, //RESULTS_PER_PAGE,
						'post_type' 		=> 'quote'
					)
				);

				include( TEMPLATEPATH . '/loop-quotes.php' );
				
				echo
			'</div>

			<div class="flex-child flex-33">
				<div style="padding: 0 50px;">
					<h3>Sidebar Stuff</h3>
					<p>Hello, Mr. Fox!</p>
				</div>
			</div>
		</div>
	</main>';
endif;


/**
 *	Loading footer this way vs get_footer() gives us access to the $current_user variable
 */
include( TEMPLATEPATH . '/footer.php' );