<?php
/*
 *	QUOTEBOARD
 *	Author Following
 *
 *	Displays the members the current user is following
 *
 *	This template is called from author.php and is only accessed when
 *	the URL endpoint is 'following'
 */

$query = "SELECT * FROM wp_qb_followers WHERE follower_id = '$current_page_user_id'";

if ( $followings = $wpdb->get_results( $query ) ) :

	foreach ( $followings as $following ):
		$following = get_user_by( 'id', $following->user_id );
		$following_url = home_url( '/' ) . 'author/' . $following->user_nicename;

		// get user avatar, and specify a fallback
		if ( !$following_avatar = get_wp_user_avatar_src( $following->ID, 80 ) ) {
			$following_avatar = DEFAULT_THUMBNAIL;
		}

		// count stats
		$quotecount = count_user_posts_by_type( $following->ID, 'quote' );
		$boardcount = count_user_posts_by_type( $following->ID, 'board' );

		// member background
		if ( $background_id = get_user_meta( $following->ID, 'user_background', true ) ) {
			$upload_directory 	= wp_upload_dir();
			$background_src 	= $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '" . $background_id . "' AND meta_key = '_wp_attached_file'" );
			$user_background 	= $upload_directory['baseurl'] . '/' . $background_src;
		} else {
			$user_background = DEFAULT_BACKGROUND;
		}

		// description
		if ( strlen( $following->description = get_user_meta( $following->ID, 'description', true ) ) > 200 ) {
			$following->description = substr( $following->description, 0, 200 ) . '...';
		} elseif ( empty( $following->description ) ) {
			$following->description = '<em>Alas, this user has nothing interesting to say.</em>';
		}

		// determine if current user is following this member, and show the proper button
		if ( is_user_logged_in() ) {
			if ( $is_following = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$following->ID' AND follower_id = '$current_user->ID'" ) ) {
				$follow_button = '<span class="ico follow following" data-id="' . $following->ID . '" title="Following"></span>';
			} elseif ( $following->ID != $current_user->ID ) {
				$follow_button = '<span class="ico follow" data-id="' . $following->ID . '" title="Follow"></span>';
			} else {
				$follow_button = '';
			}
		}

		// abstract variables to work with individual-member.php
		$member_home_url 	= $following_url;
		$member_avatar 		= $following_avatar;
		$member_screen_name = $following->display_name;
		$member_username 	= $following->user_nicename;
		$member_description	= $following->description;

		// load individual members
		include( TEMPLATEPATH . '/individual-member.php' );
		
	endforeach;

else:
	$following = get_user_by( 'slug', url_segment( 2 ) );

	echo '<p class="message shown info">';
	if ( $following->user_login == $current_user->user_login ) {
		echo '<strong>You aren\'t following anyone yet.</strong> Check out the <a href="' . home_url() . '/members">Members page</a> for some good people to follow.';
	} elseif ( $following->user_login == url_segment( 2 ) ) {
		echo $following->display_name . " isn't following anyone yet.";
	}
	echo '</p>';

	// TO DO: add action boxes to encourage more participation
	/*
	 *	e.g., Tips for Getting Followers
	 *	Invite People
	 *	Follow Top Quoters (suggested users)
	 */

	//echo '</section> <!-- // following-list -->';

endif;