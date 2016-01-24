<?php
/*
 *	QUOTEBOARD
 *	Follow user
 *
 *	Adds or removes the current user as a follower of another user.
 *	Also adds or removes the current user as a follower of all of
 *	the other user's public boards.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// gather and sanitize form fields
$following_id	= absint( $_POST['id'] );
$follower_id 	= get_current_user_id();

// check for required fields
if ( empty( $following_id ) || $following_id === 0 ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

global $wpdb;

// determine if we're already following this user
$already_following = $wpdb->get_var( "SELECT user_id FROM wp_qb_followers WHERE user_id = '" . $following_id . "' AND follower_id = '" . $follower_id . "'" );

// get a list of all boards by the user we want to follow/unfollow
$user_boards = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_author = '$following_id' AND post_type = 'board' AND post_status = 'publish'" );

// already following; unfollow user and all his boards
if ( $already_following ) {
	// unfollow user: $wpdb->delete( $table, $where, $where_format )
	$unfollow_user = $wpdb->delete(
		wp_qb_followers,
		array(
			'user_id' 		=> $following_id,
			'follower_id'	=> $follower_id
		),
		array(
			'%d',
			'%d'
		)
	);

	// unfollow all of user's (public) boards
	foreach ( $user_boards as $board ) {
		$members 		= get_field( 'board_members', $board->ID );
		$member_count 	= count( $members ); // RB - do we need this? 4/13/14

		// get user's index so we can do a proper delete
		if ( $members ) {
			foreach ( $members as $key => $row ) {
				if ( $row['board_members_user']['ID'] == $follower_id ) {
					$is_following = true;
					break;
				}
			}
		}

		// check for is_following in case user individually unfollowed a board (can't delete twice)
		if ( $is_following ) {
			delete_post_meta( $board->ID, '_board_members_' . $key . '_board_members_user' );
			delete_post_meta( $board->ID, '_board_members_' . $key . '_can_collaborate' );
			delete_post_meta( $board->ID, 'board_members_' . $key . '_board_members_user' );
			delete_post_meta( $board->ID, 'board_members_' . $key . '_can_collaborate' );
			
			if ( !update_post_meta( $board->ID, 'board_members', $member_count - 1 ) ) {
				echo json_encode( array( 'errors' => 'There was a problem unfollowing the board ' . $board->post_name ) );
				exit;
			}
		}
	}

	$success = true;

// not yet following; follow user and all his boards
} else {
	// make sure we're trying to follow an actual user
	$is_actual_user = $wpdb->get_var( "SELECT user_login FROM $wpdb->users WHERE ID = '" . $following_id . "'" );

	if ( !$is_actual_user ) :
		echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
		exit;
	endif;

	// follow user: $wpdb->insert( $table, $data, $format )
	$follow_user = $wpdb->insert(
		wp_qb_followers,
		array(
			'user_id'		=> $following_id,
			'follower_id'	=> $follower_id
		),
		array(
			'%d',
			'%d'
		)
	);

	// follow all of user's (public) boards
	foreach ( $user_boards as $board ) {
		// determine if user is already following this board
		$members = get_field( 'board_members', $board->ID );
		$already_following = false;

		foreach ( $members as $member ) {
			if ( $member['board_members_user']['ID'] == $follower_id ) {
				$already_following = true;
				break;
			}
		}

		if ( !$already_following ) {
			// determine if public board to set collaboration
			if ( get_post_field( 'post_author', $board->ID ) == SUPERADMIN_USER_ID ) {
				$can_collaborate = 'y';
			} else {
				$can_collaborate = 'n';
			}

			// add a new row to the repeater field
			$field_key	= 'field_5077ae931b6f6';
			$value		= get_field( $field_key, $board->ID );
			$value[] 	= array( 'board_members_user' => $follower_id, 'can_collaborate' => $can_collaborate );
			
			if ( !update_field( $field_key, $value, $board->ID ) ) {
				echo json_encode( array( 'errors' => 'There was a problem following the board ' . $board->ID ) );
				exit;
			}
		}
	}

	$success = true;
}

// return params for jQuery
if ( $success ) {
	echo json_encode(
		array(
			'result' => true
		)
	);

} else {
	echo json_encode(
		array(
			'errors' => 'There was a problem following or un-following this user'
		)
	);
}

exit();