<?php
/*
 *	QUOTEBOARD
 *	Add board members
 *
 *	Called from includes/add-board.php and includes/register.php
 *	Adds board author as first member, then checks board author's list
 *	of followers, adding them as non-collaborative members.
 */

// add board creator as first collaborator
$field_key	= 'field_5077ae931b6f6';
$value		= get_field( $field_key, $board_added_id );
$value[] 	= array( 'board_members_user' => $board_user_id, 'can_collaborate' => 'y' );



if ( !update_field( $field_key, $value, $board_added_id ) ) {
	echo json_encode( array( 'errors' => 'There was a problem adding you to your own board' ) );
	exit;
}

global $wpdb;
// checks board creator's follower list and adds them all as members
if ( $followers = $wpdb->get_results( "SELECT follower_id FROM wp_qb_followers WHERE user_id = '$board_user_id'" ) ) {
	// if this is a public board (created by admin), add everyone as a collaborator
	if ( get_post_field( 'post_author', $board_added_id ) == SUPERADMIN_USER_ID ) {
		$can_collaborate = 'y';
	} else {
		$can_collaborate = 'n';
	}

	$member_count = 1;
	foreach ( $followers as $follower ) {
		$field_key	= 'field_5077ae931b6f6';
		$value		= get_field( $field_key, $board_added_id );
		$value[] 	= array( 'board_members_user' => $follower->follower_id, 'can_collaborate' => $can_collaborate );
		
		if ( !update_field( $field_key, $value, $board_added_id ) ) {
			echo json_encode( array( 'errors' => 'There was a problem adding a follower with ID ' . $follower->follower_id . ' to this board' ) );
			exit;
		}

		$member_count++;
	}

	update_post_meta( $board_added_id, 'board_members', $member_count );
}