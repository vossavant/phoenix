<?php
/*
 *	QUOTEBOARD
 *	Quote Permalink
 *
 *	Displays a single quote; currently only accessible via lightbox.
 */

// doing it this way vs get_header() gives header access to all variables
include( TEMPLATEPATH . '/header.php' );

$quote_id = array_pop(explode('/', rtrim($_SERVER['REQUEST_URI'], '/')));

if ( have_posts() ) : while ( have_posts() ) : the_post();
	// get fave count
	$faves = qb_count_faves( $post->ID );

	// get comment count
	$comments = wp_count_comments( $post->ID );

	$quote_id = get_the_ID();
	//$attributed_to = get_field( 'quote_author' ) ? get_field( 'quote_author' ) : 'Anonymous';

	// fetch quote contributor; set to "You" for quotes added by current user
	$contributor = get_the_author_meta( 'display_name' );
	if ( $contributor == $current_user->display_name ) {
		$contributor .= ' (you)';
	}

	// get author avatar
	if ( !$author_avatar = get_wp_user_avatar_src( $post->post_author, 80 ) ) {
		$author_avatar = DEFAULT_THUMBNAIL;
	}

	echo
	'<section class="main">
		<p class="message quote-edit success">Quote updated successfully</p>';

		$quotes = get_posts(
			array(
				'p' 			=> $quote_id,
				'post_status'	=> array('private', 'publish'),
				'post_type' 	=> 'quote'
			)
		);

		include( TEMPLATEPATH . '/loop-quotes.php' );

		/**
		 *	Tags
		 */
		if ( $tags = get_the_terms( $post->ID, 'post_tag' ) ) {
			echo '
			<section class="tag box">
				<div class="bubble">
					<h4>Tags</h4>
					<ul>';
						foreach ( $tags as $tag ) {
							$tag_string .= '<li><a href="' . get_tag_link( $tag->term_id ) . '">' . $tag->name . '</a></li>, ';
						}
						echo rtrim($tag_string, ', ') . '
					</ul>
				</div>
			</section>';
		}


		/**
		 *	Boards
		 */
		echo '
		<section class="boards box">
			<div class="bubble">
				<h4>Boards</h4>
				<ul>';
					// show original board
					$post = get_field('quote_board');
					setup_postdata( $post );
					get_template_part( 'includes/get-board' );
					wp_reset_postdata();

					// show requoted boards
					if ( $boards = get_field( 'requoted_to' ) ) {
						foreach ( $boards as $post ):
							setup_postdata( $post );
							get_template_part( 'includes/get-board' );
						endforeach;
						wp_reset_postdata();
					}
					echo '
				</ul>
			</div>
		</section>';


		/**
		 *	Faves
		 */
		if ( $faves = get_field( 'quote_fave' ) ) {
			echo '
			<section class="faves box">
				<div class="bubble">
					<h4>Faves</h4>
					<ul>';
						foreach ( $faves as $fave ) {
							if ( has_wp_user_avatar( $fave['quote_fave_user']['ID'] ) ) {
								$avatar	= get_wp_user_avatar( $fave['quote_fave_user']['ID'], 48 );
							} else {
								$avatar	= DEFAULT_THUMBNAIL;
							}

							echo '
							<li>
								<a href="' . home_url('/') . 'author/' . $fave['quote_fave_user']['user_nicename'] . '">' . $avatar . $fave['quote_fave_user']['display_name'] . '</a>
							</li>';
						}
						echo '
					</ul>
				</div>
			</section>';
		}


		/**
		 *	Comments
		 */
		comments_template();
	echo '</section> <!-- // main -->';
	?>

<aside style="display: none;"> <!-- temp inline style -->

	<section class="widget" data-button="show-tags" id="tagged">
		<?php
		if ( $tags ) {
			$tag_count = count( $tags );

			echo '
			<header>
				<h3>Tagged <span class="mobile">(' . $tag_count . '&times;)</span><span>This quote has ' . $tag_count . ' tag' . ( $tag_count != 1 ? 's' : '' ) . '</span></h3>
			</header>';
			
			if ( $tags ) {
				echo '<ul>';
				foreach ( $tags as $tag ) {
					echo '<li><a href="' . get_tag_link( $tag->term_id ) . '">' . $tag->name . '</a></li>';
				}
				echo '</ul>';
			}
		} else {
			echo '
			<header class="empty">
				<h3>Tagged <span>Not tagged yet</span></h3>
			</header>';
		}
		?>
	</section>
</aside>

<?php /*
<div class="quote-details">
	<?php //echo twitter_oauth(); ?>
*/?>


<?php
endwhile;
endif;

// doing it this way vs get_footer() gives footer access to all variables
include( TEMPLATEPATH . '/footer.php' );
?>