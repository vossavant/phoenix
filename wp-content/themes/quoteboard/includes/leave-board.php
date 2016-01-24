<?php
/*
 *	QUOTEBOARD
 *	Leave Board
 *
 *	Removes a user from a board's member list. Called when a user
 *	clicks the "Leave Board" link in the single-board.php dropdown.
 *
 *	Quotes submitted to the board by the departing user are not
 *	removed, since we don't know where to put them. For now, the
 *	user has to manually move each quote.
 */

$board_id = absint( $_POST['board_id'] );

if ( !is_user_logged_in() || empty( $board_id ) || $board_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

if ( !isset( $_POST['form_name'] ) || $_POST['form_name'] != 'leave-board' ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

$user_id 	= get_current_user_id();
$user_login = get_user_by( 'id', $user_id )->user_login;
$is_member 	= false;
$member_cnt	= get_post_meta( $board_id, 'board_members', true );

// determine if user is actually a member of this board
if ( $board_members = get_field( 'board_members', $board_id ) ) {
	foreach ( $board_members as $key => $member ) {
		if ( $member['board_members_user']['ID'] == $user_id ) {
			$is_member = true;
			break;
		}
	}
}

// delete records from the wp_postmeta table, update member count
if ( $is_member ) {
	delete_post_meta( $board_id, '_board_members_' . $key . '_board_members_user' );
	delete_post_meta( $board_id, '_board_members_' . $key . '_can_collaborate' );
	delete_post_meta( $board_id, 'board_members_' . $key . '_board_members_user' );
	delete_post_meta( $board_id, 'board_members_' . $key . '_can_collaborate' );

	if ( update_post_meta( $board_id, 'board_members', $member_cnt - 1 ) ) {
		echo json_encode( array(
			'redirect_to' => home_url( '/' ) . 'author/' . $user_login . '/boards'
		) );
	}

} else {
	echo json_encode( array( 'errors' => "You can't leave a board you don't belong to." ) );
}

exit;