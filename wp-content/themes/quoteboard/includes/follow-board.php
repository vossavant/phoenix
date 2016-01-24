<?php
/*
 *	QUOTEBOARD
 *	Follow Board
 *
 *	Adds or removes the current user as a follower (non-collaborative member)
 *	of another board.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// gather and sanitize form fields
$board_id	= absint( $_POST['id'] );
$user_id 	= get_current_user_id();

// check for required fields
if ( empty( $board_id ) || $board_id === 0 ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

// determine if user is already following this board
$members 		= get_field( 'board_members', $board_id );
$member_count	= count( $members );

foreach ( $members as $key => $member ) {
	if ( $member['board_members_user']['ID'] == $user_id ) {
		$already_following = true;
		break;
	}
}

// if already following, unfollow the board
if ( $already_following ) {
	/*
	 *	First, check for default board - this should never come up (since unfollow option is hidden
	 *	with CSS for default boards), but users should not be allowed to unfollow their default board
	 */
	global $wpdb;
	$is_default = $wpdb->get_var( "SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = 'is_default' AND post_id = '$board_id' AND meta_value = 'yes'" );
	$board_author = get_post_field( 'post_author', $board_id );
	
	if ( $is_default && $board_author == $user_id ) {
		echo json_encode( array( 'errors' => 'Sorry, you cannot unfollow your default board' ) );
		exit;
	}

	delete_post_meta( $board_id, '_board_members_' . $key . '_board_members_user' );
	delete_post_meta( $board_id, '_board_members_' . $key . '_can_collaborate' );
	delete_post_meta( $board_id, 'board_members_' . $key . '_board_members_user' );
	delete_post_meta( $board_id, 'board_members_' . $key . '_can_collaborate' );
	
	if ( !update_post_meta( $board_id, 'board_members', $member_count - 1 ) ) {
		echo json_encode( array( 'errors' => 'There was a problem unfollowing this board' ) );
		exit;
	}

	$success = true;

// not yet following; follow board
} else {
	// make sure we're trying to follow an actual board
	global $wpdb;
	$is_actual_board = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$board_id' AND post_status <> 'trash' AND post_type = 'board'" );

	if ( !$is_actual_board ) :
		echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
		exit;
	endif;

	// add a new row to the repeater field
	$field_key	= 'field_5077ae931b6f6';
	$value		= get_field( $field_key, $board_id );
	
	if ( $_POST['is_public'] == true ) {
		$value[] = array( 'board_members_user' => $user_id, 'can_collaborate' => 'y' );
	} else {
		$value[] = array( 'board_members_user' => $user_id, 'can_collaborate' => 'n' );
	}
	
	if ( !update_field( $field_key, $value, $board_id ) ) {
		echo json_encode( array( 'errors' => 'There was a problem following this board' ) );
		exit;
	}

	$success = true;
}

// return params for jQuery
if ( $success ) {
	echo json_encode( array( 'result' => true ) );
}

exit;