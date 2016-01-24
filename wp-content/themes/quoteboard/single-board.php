<?php
/*
 *	QUOTEBOARD
 *	Board Permalink
 *
 *	Displays the quotes from a single board.
 *
 *	This bit routes bad requests (e.g., boards/board-name/members/foo) to a 404 page.
 *	Required b/c the endpoints plugin will serve any request that *contains* a valid
 *	endpoint (in this case, "members" is valid).
 *
 *	Endpoint is at segment 3, so anything beyond that indicates a bad URL.
 *
 *	TO DO: to enhance privacy for our members, generate 404 headers when an admin
 *	attempts to view a private board (this is already done for other roles)
 */

if ( url_segment( 4 ) ) { // TO DO: this needs to be set to "4" on the live site, since there is no "quoteboard" in the URL
	status_header( 404 );
	nocache_headers();
	include( get_404_template() );
	exit();
}

// doing it this way vs get_header() gives header access to all variables
include( TEMPLATEPATH . '/header.php' );

if ( have_posts() ) :
	while ( have_posts() ) : the_post();

		// grab board ID for later use
		$board_id		= get_the_ID();

		// store permalink for efficiency
		$permalink 		= get_permalink();
		
		// grab URL endpoint (that is, the final segment in the URL)
		$url_endpoint	= url_segment( 3 ); // TO DO: change to 3 on live site

		// grab a list of board members
		$members 		= get_field( 'board_members' ); 

		// default: user is not a member of this board
		$is_member		= false;

		// get board author ID
		$board_author	= get_the_author_meta( 'id' );

		// get description
		if ( !$board_description = apply_filters( 'the_content', get_the_content() ) ) {
			$board_description = '<em>Alas, this board has no description.</em>';
		}

		// cache result, since we do this check a few times
		if ( $current_user->ID == $board_author ) {
			$current_user_is_board_author = true;
		} else {
			$current_user_is_board_author = false;
		}

		// determine if current user is a member of this board
		foreach ( $members as $member ) {
			if ( $member['board_members_user']['ID'] == $current_user->ID ) {
				$is_member = true;

				if ( $member['can_collaborate'] == 'y' ) {
					$can_collaborate = true;
				}

				break;
			}
		}

		/*
		 *	Grab quotes related to (on) the current board
		 *	we store the results here so we can count them (for the #quotes stat)
		 *	and then use the query later. this is better than running a query here
		 *	simply to count the quotes, and then running the same query again later
		 *	in the template to show the quotes
		 */
		$quotes = get_posts(
			array (
				'posts_per_page'=> -1,
				'post_status'	=> ( $current_user_is_board_author && $post->post_status == 'private' ? array( 'private', 'publish' ) : 'publish' ),
				'post_type'		=> 'quote',
				'meta_query'	=> array (
					array (
						'key' 	=> 'quote_board',
						'value' => $board_id
					)
				)
			)
		);

		// board profile
		$board_quote_count 	= count( $quotes );
		$board_member_count = count( get_field( 'board_members' ) );

		if ( has_post_thumbnail() ) {
			$board_thumbnail	= wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
			$board_thumbnail	= '<img src="' . $board_thumbnail[0] . '">';
		} else {
			$board_thumbnail 	= get_default_thumbnail( 'thumbnail' );
		}
	endwhile;
endif;

// load a separate template for each endpoint (e.g., board-quotes.php)
if ( isset( $wp_query->query_vars['members'] ) ) {
	include( TEMPLATEPATH . '/board-members.php' );
}

elseif ( isset( $wp_query->query_vars['profile'] ) ) {
	include( TEMPLATEPATH . '/board-profile.php' );

// default: show a list of quotes
} else {
	echo
	'<section class="main extra wide">
		<div class="flex">
			<div class="flex-60">';

				// first check if this is a newly created board
				if ( stristr( $_SERVER['REQUEST_URI'], '?new' ) ) {
					// check if user has already seen board tutorial
					if ( get_user_meta( $current_user->ID, 'show_tutorial_board', true ) == 1 ) {
						// load scripts required for welcome message and tutorial
						wp_enqueue_script( 'intro', get_template_directory_uri() . '/js/intro.js', array( 'jquery' ), null, true );
						wp_enqueue_script( 'welcome', get_template_directory_uri() . '/js/welcome-board.js', array( 'intro' ), null, true );

						// load hidden welcome message
						get_template_part( 'includes/modal-welcome-board' );

						// update meta to prevent welcome message from reappearing
						update_user_meta( $current_user->ID, 'show_tutorial_board', 0 );
					}

				}

				// show "add quote" option to board members
				if ( $can_collaborate ) {
					$inline_form_placeholder = 'Add a quote to this board...';
					// include( TEMPLATEPATH . '/forms/add-quote-inline.php' );
				}

				// admins can see private boards by default, but we hid the quotes from them, so show a helpful note
				if ( $post->post_status == 'private' && !$current_user_is_board_author ) {
					echo '<p class="message warning shown"><strong>This is a private board.</strong> You cannot view quotes on a private board, even as an admin.</p>';
				}

				// show board quotes
				include( TEMPLATEPATH . '/loop-quotes.php' );
			echo 
			'</div>
			
			<div class="flex-child flex-40">
				<div class="profile">
					<div class="cover" style="background: url(' . DEFAULT_BACKGROUND . ') center no-repeat;">' .
						$board_thumbnail .
						'<div>
							<div>
								<h3><span>Quotes Posted To</span>' . get_the_title() . '</h3>';
								/*
								<ul>
									<li>
										<span class="ico quote"></span>' . $board_quote_count . ' Quote' . ( $board_quote_count != 1 ? 's' : '' ) .
									'</li>
									<li>
										<span class="ico users"></span>' . $board_member_count . ' Follower' . ( $board_member_count != 1 ? 's' : '' ) .
									'</li>
								</ul>
								*/
								echo
							'</div>
						</div>
					</div>
					<div class="meta">' . $board_description . '</div>
				</div>
			</div>
		</div>
	</section>';
}

// doing it this way vs get_footer() gives footer access to all variables
include( TEMPLATEPATH . '/footer.php' );