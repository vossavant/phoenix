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

if ( have_posts() ) : while ( have_posts() ) : the_post();

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

	// if admin authored, it's a public board (this will change)
	if ( $board_author == SUPERADMIN_USER_ID ) {
		$is_public = true;
	} else {
		$is_public = false;
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
		$board_thumbnail		= wp_get_attachment_image_src( get_post_thumbnail_id(), 'board-thumb' );
		$board_thumbnail_src	= $board_thumbnail[0];
	} else {
		$board_thumbnail_src	= DEFAULT_THUMBNAIL;
	}
	?>

	<!-- cover photo -->
	<!-- <div id="hero" style="background: url('<?= $page_background ?>') center no-repeat;"></div> -->

	<!-- profile -->
	<!-- <div class="clearfix" id="profile">
		<section>
			<img src="<?= $board_thumbnail_src; ?>" alt="<?= get_the_title(); ?>" />
			<?php // official board designation
			if ( $is_public ) {
				echo '<span class="ico status public" title="Public Board"></span>';
			} elseif ( $post->post_status == 'private' ) {
				echo '<span class="ico status private" title="Private Board"></span>';
			}
			?>
			<div>
				<h3><?php the_title(); ?></h3>
				<?= $board_description; ?>
			</div>

			<aside>
				<ul>
					<li>
						<span class="ico quote"></span> <?= $board_quote_count; ?> Quote<?php if ( $board_quote_count != 1 ) echo 's'; ?>
					</li>
					<li>
						<span class="ico users"></span> <?= $board_member_count; ?> Follower<?php if ( $board_member_count != 1 ) echo 's'; ?>
					</li>
				</ul>

				<?php
				if ( is_user_logged_in() ) :
					if ( $is_member ) {
						if ( $current_user_is_board_author ) {
							echo '<a class="btn ajax-modal invite" data-id="' . $board_id . '" data-type="modal-board-invite" href="">Invite</a>';
						} else {
							if ( $is_public || $member['can_collaborate'] == 'y' ) {
								echo '<a class="btn collab collaborating board" data-id="' . $board_id . '" href="">Collaborating</a>';
							} else {
								echo '<a class="btn follow following board" data-id="' . $board_id . '" href="">Following</a>';
							}
						}
					} else {
						if ( $is_public ) {
							echo '<a class="btn collab board" data-id="' . $board_id . '" href="">Collaborate</a>';
						} else {
							echo '<a class="btn follow board" data-id="' . $board_id . '" href="">Follow</a>';
						}
					}
				endif;
				?>
			</aside>

			<ul class="tabnav">
				<li><a<?php if ( $url_endpoint == '' ) { echo ' class="active"'; } ?> href="<?php the_permalink(); ?>">Quotes</a></li>
				<li><a<?php if ( $url_endpoint == 'members' ) { echo ' class="active"'; } ?> href="<?php the_permalink(); ?>members">Members</a></li>
				<?php
				if ( $current_user_is_board_author ) {
					echo '<li><a' . ( $url_endpoint == 'profile' ? ' class="active"' : '' ) . ' href="' . get_permalink() . 'profile">Edit Profile</a></li>';
				}
				?>
			</ul>
		</section>
	</div> <!-- // profile --> -->

	<?php
endwhile; endif;

// load a separate template for each endpoint (e.g., board-quotes.php)
if ( isset( $wp_query->query_vars['members'] ) ) {
	include( TEMPLATEPATH . '/board-members.php' );
}

elseif ( isset( $wp_query->query_vars['profile'] ) ) {
	include( TEMPLATEPATH . '/board-profile.php' );

// default: show a list of quotes
} else {
	echo
	'<main class="extra wide">
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
					include( TEMPLATEPATH . '/forms/add-quote-inline.php' );
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
				<div id="profile">
					<div class="cover" style="background: url(' . $page_background . ') center no-repeat;">
						<div>
							<h3>' . get_the_title() . '</h3>
							<p>137 Quotes</p>
						</div>
					</div>
					<div class="meta">' . $board_description . '</div>
				</div>
			</div>
		</div>
	</main>';
}

// doing it this way vs get_footer() gives footer access to all variables
include( TEMPLATEPATH . '/footer.php' );