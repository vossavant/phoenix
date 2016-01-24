<?php
/*
 *	QUOTEBOARD
 *	Board Members
 *
 *	Displays all of the members of the current board.
 *
 *	This template is called from single-board.php and is only accessed when
 *	the URL endpoint is 'members'
 */

if ( $members ) :
	echo '<main>';

	foreach ($members as $member ) :
		// get user info
		$member_id			= $member['board_members_user']['ID'];
		$member_screen_name = $member['board_members_user']['display_name'];
		$member_username 	= $member['board_members_user']['user_nicename'];
		$member_description	= $member['board_members_user']['user_description'];
		$member_home_url	= get_bloginfo( 'home' ) . '/author/' . $member_username;

		// count stats
		$quotecount = count_user_posts_by_type( $member_id, 'quote' );
		$boardcount = count_user_posts_by_type( $member_id, 'board' );
		//$follower_count 	= $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE follower_id = '$member_id'" );
		
		// member thumbnail
		if ( has_wp_user_avatar( $member_id ) ) {
			$avatar	= get_wp_user_avatar_src( $member_id, 80 );
		} else {
			$avatar	= DEFAULT_THUMBNAIL;
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
		if ( strlen( $member_description = get_user_meta( $member_id, 'description', true ) ) > 200 ) {
			$member_description = substr( $member_description, 0, 200 ) . '...';
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

		// abstract variables to work with individual-member.php
		$member_avatar 		= $avatar;

		// load individual members
		include( TEMPLATEPATH . '/individual-member.php' );
	endforeach;

	echo '</main>';

endif;