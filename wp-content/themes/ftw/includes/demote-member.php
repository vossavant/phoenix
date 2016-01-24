<?php
/*
 *	QUOTEBOARD
 *	Demote Member
 *
 *	Accepts a user ID and changes the collaborator status of that user to "no".
 *	Called when a user pulls up the board invite list and removes the person
 *	as a collaborator. The person remains on the board as a follower.
 */

$collaborator_id = absint( $_POST['user_id'] );

if ( !is_user_logged_in() || empty( $collaborator_id ) || $collaborator_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

if ( !isset( $_POST['form_name'] ) || $_POST['form_name'] != 'demote-member' ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

global $wpdb;
$user_id 	= get_current_user_id();
$board_id 	= absint( $_POST['board_id'] );

// determine if user is actually the author of this board
$board_author_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$board_id' AND post_type = 'board'" );

if ( !$board_author_id || $board_author_id != $user_id ) {
	echo json_encode( array( 'errors' => "You can't edit collaborators on a board you didn't create." ) );
	exit;
}

// change collaborator status
$demote_user = false;
$updated = false;

foreach( $members = get_field( 'board_members', $board_id ) as $key => $member ) {
	if ( $member['board_members_user']['ID'] == $collaborator_id ) {
		$updated = update_post_meta( $board_id, 'board_members_' . $key . '_can_collaborate', 'n' );
		break;
	}
}

if ( $updated ) {
	echo json_encode( array(
		'result' => 'good'
	) );
} else {
		echo json_encode( array(
		'result' => 'bad'
	) );
}

exit;