<?php
/*
 *	QUOTEBOARD
 *	Author Followers
 *
 *	Displays the members following the current user
 *
 *	This template is called from author.php and is only accessed when
 *	the URL endpoint is 'followers'
 */

$query = "SELECT * FROM wp_qb_followers WHERE user_id = '$current_page_user_id'";

if ( $followers = $wpdb->get_results( $query ) ) :

	foreach ( $followers as $follower ):
		$follower = get_user_by( 'id', $follower->follower_id );
		$follower_url = home_url( '/' ) . 'author/' . $follower->user_nicename;

		// get user avatar, and specify a fallback
		if ( !$follower_avatar = get_wp_user_avatar_src( $follower->ID, 80 ) ) {
			$follower_avatar = DEFAULT_THUMBNAIL;
		}

		// count stats
		$quotecount = count_user_posts_by_type( $follower->ID, 'quote' );
		$boardcount = count_user_posts_by_type( $follower->ID, 'board' );

		// member background
		if ( $background_id = get_user_meta( $follower->ID, 'user_background', true ) ) {
			$upload_directory 	= wp_upload_dir();
			$background_src 	= $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '" . $background_id . "' AND meta_key = '_wp_attached_file'" );
			$user_background 	= $upload_directory['baseurl'] . '/' . $background_src;
		} else {
			$user_background = DEFAULT_BACKGROUND;
		}

		// description
		if ( strlen( $member_description = get_user_meta( $follower->ID, 'description', true ) ) > 200 ) {
			$member_description = substr( $member_description, 0, 200 ) . '...';
		} elseif ( empty( $member_description ) ) {
			$member_description = '<em>Alas, this user has nothing interesting to say.</em>';
		}

		// determine if current user is following this member, and show the proper button
		if ( is_user_logged_in() ) {
			if ( $is_following = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$follower->ID' AND follower_id = '$current_user->ID'" ) ) {
				$follow_button = '<span class="ico follow following" data-id="' . $follower->ID . '" title="Following"></span>';
			} elseif ( $follower->ID != $current_user->ID ) {
				$follow_button = '<span class="ico follow" data-id="' . $follower->ID . '" title="Follow"></span>';
			} else {
				$follow_button = '';
			}
		}

		// abstract variables to work with individual-member.php
		$member_home_url 	= $follower_url;
		$member_avatar 		= $follower_avatar;
		$member_screen_name = $follower->display_name;
		$member_username 	= $follower->user_nicename;

		// load individual members
		include( TEMPLATEPATH . '/individual-member.php' );
	endforeach;

else:
	$follower = get_user_by( 'slug', url_segment( 2 ) );

	echo '<p class="message shown info">';
	if ( $follower->user_login == $current_user->user_login ) {
		echo "You don't";
	} elseif ( $follower->user_login == url_segment( 2 ) ) {
		echo $follower->display_name . " doesn't";
	}
	echo " have any followers yet</p>";

	// TO DO: add action boxes to encourage more participation
	/*
	 *	e.g., Tips for Getting Followers
	 *	Invite People
	 *	Follow Top Quoters (suggested users)
	 */

	echo '</section> <!-- // follower-list -->';

endif;