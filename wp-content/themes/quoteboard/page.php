<?php
/*
 *	QUOTEBOARD
 *	Page Template
 */

// doing it this way vs get_header() gives access to all variables declared in header
include( TEMPLATEPATH . '/header.php' );

// prevent jankiness with pagination
$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

echo '<section class="main">';

switch( url_segment( 1 ) ) :
	/**
	 *	Main Boards Page - shows latest boards sitewide
	 */
	case 'boards' :
		echo '<h3>Latest Boards by All Members</h3>';
		$boards = get_posts(
			array(
				'paged' 			=> $paged,
				'posts_per_page'	=> RESULTS_PER_PAGE,
				'post_status'		=> 'publish',
				'post_type' 		=> 'board'
			)
		);

		include( TEMPLATEPATH . '/loop-boards.php' );
	break;


	/**
	 *	Leave Feedback Page
	 */
	case 'feedback' :
		echo '<article id="page-wrapper">';
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo '<h1>' . get_the_title() . '</h1>';
			the_content();

			include( TEMPLATEPATH . '/forms/feedback.php' );
		endwhile; endif;
		echo '</article>';
	break;

	/**
	 *	Board Invite
	 *
	 *	Specialized case for the "Invite" page, which serves as a
	 *	landing page for users who have been sent board invitation codes
	 *	via email. Each invitation code is tied to a particular email
	 *	and board ID. If the code and email match, the user is added to
	 *	the board and redirected there; otherwise, the user sees a notice
	 *	indicating that the code is invalid or expired.
	 */
	case 'invite' :
		echo '<article id="page-wrapper">';
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo '<h1>' . get_the_title() . '</h1>';
			the_content();

			// validate code in URL
			include( TEMPLATEPATH . '/includes/invite-check-code.php' );
		endwhile; endif;
		echo '</article>';
	break;


	/**
	 *	Main Members Page - shows newest site members
	 */
	case 'members' :
		echo '<h3>Newest Members</h3>';
		$members = get_users(
			array(
				'exclude'	=> array( 1, 2 ),
				'number'	=> RESULTS_PER_PAGE,
				'order'		=> 'DESC',
				'orderby'	=> 'registered',
				'paged' 	=> $paged,
				'who'		=> 'authors'
			)
		);

		foreach ($members as $member ) :
			// get user info
			$member_id			= $member->ID;
			$member_screen_name = $member->display_name;
			$member_username 	= $member->user_nicename;
			$member_description	= $member->user_description;
			$member_home_url	= get_bloginfo( 'home' ) . '/author/' . $member_username;
			
			// member thumbnail
			if ( has_wp_user_avatar( $member_id ) ) {
				$member_avatar	= get_wp_user_avatar_src( $member_id, 80 );
			} else {
				$member_avatar	= DEFAULT_THUMBNAIL;
			}

			// member background
			if ( $background_id = get_user_meta( $member_id, 'user_background', true ) ) {
				$upload_directory 	= wp_upload_dir();
				$background_src 	= $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '" . $background_id . "' AND meta_key = '_wp_attached_file'" );
				$user_background 	= $upload_directory['baseurl'] . '/' . $background_src;
			} else {
				$user_background = DEFAULT_BACKGROUND;
			}

			// description
			if ( strlen( $member_description = get_user_meta( $member_id, 'description', true ) ) > 80 ) {
				$member_description = substr( $member_description, 0, 120 ) . '...';
			} elseif ( empty( $member_description ) ) {
				$member_description = '<em>Alas, this user has nothing interesting to say.</em>';
			}

			// determine if current user is following this member, and show the proper button
			if ( is_user_logged_in() ) {
				if ( $is_following = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$member_id' AND follower_id = '$current_user->ID'" ) ) {
					$follow_button = '<span class="ico follow following" data-id="' . $member_id . '" title="Following"></span>';
				} elseif ( $member_id != $current_user->ID ) {
					$follow_button = '<span class="ico follow" data-id="' . $member_id . '" title="Follow"></span>';
				} else {
					$follow_button = '';
				}
			}

			// load individual members
			include( TEMPLATEPATH . '/individual-member.php' );
		endforeach;
	break;


	/**
	 *	Main Quotes Page - shows latest quotes sitewide
	 */
	case 'quotes' :
		echo '<h3>Latest Quotes from All Members</h3>';
		$quotes = get_posts(
			array(
				'paged' 			=> $paged,
				'posts_per_page'	=> RESULTS_PER_PAGE,
				'post_status'		=> 'publish',
				'post_type' 		=> 'quote'
			)
		);

		include( TEMPLATEPATH . '/loop-quotes.php' );
	break;


	/**
	 *	Default Page Layout
	 */
	default :
		echo '<article id="page-wrapper">';
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo '<h1>' . get_the_title() . '</h1>';
			the_content();
		endwhile; endif;
		echo '</article>';
	break;
endswitch;

echo '</section> <!-- // main -->';

//get_sidebar();

// doing it this way vs get_footer() gives footer access to all variables
include( TEMPLATEPATH . '/footer.php' );