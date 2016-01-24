<?php
/*
 *	QUOTEBOARD
 *	Add Member to Invite Queue
 *
 *	Called when a user is inviting people to a board. Checks the provided member ID
 *	or email to ensure the member being invited is not already a collaborator of the
 *	board, that the member inviting is following the invitee, and that any entered
 *	email is valid.
 */

if ( !is_user_logged_in() || empty( $_POST['form_name'] ) || $_POST['form_name'] != 'invite-member-add' ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// gather and sanitize form fields
$user_input = wp_unslash( sanitize_text_field( $_POST['user_input'] ) );
$invite_id	= absint( $_POST['invite_id'] );
$board_id	= absint( $_POST['board_id'] );

// check for required fields
if ( empty( $user_input ) ) {
	echo json_encode( array( 'errors' => 'Please enter a name or email address.' ) );
	exit;
}

if ( empty( $board_id ) || $board_id === 0 ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

// if no invite ID, assume user entered an email address
if ( empty( $invite_id ) ) {
	// verify sanitized email matches user input, and that email is valid
	if ( $user_input == filter_var( $user_input, FILTER_SANITIZE_EMAIL ) && filter_var( $user_input, FILTER_VALIDATE_EMAIL ) ) {

		// make sure invitee is not already a member of the board
		if ( qb_check_if_collaborating( $user_input, $board_id ) == false ) {
			echo json_encode( array( 'errors' => 'This email belongs to someone who is already collaborating on this board.' ) );
			exit;
		}

		// make sure invitee doesn't have a pending invite
		if ( qb_check_if_invited( $user_input, $board_id ) == false ) {
			echo json_encode( array( 'errors' => 'You already invited this person to collaborate, but they have yet to respond. Patience, young grasshopper.' ) );
			exit;
		}

		$return_html = '
		<li class="awaiting-invite" data-email="' . $user_input . '">
			<a href="#">
				<div class="ico email invite-email"></div>
				<span>' . $user_input . '</span>
				<span>Awaiting Invitation</span>
			</a>
			<a class="delete remove-invitee" href="">(remove)</a>
		</li>';

		echo json_encode( array( 'html' => $return_html, 'email' => $user_input ) );
	} else {
		echo json_encode( array( 'errors' => "It looks like you tried to enter an email, but it isn't valid. Please try again." ) );
	}
	exit;
}

global $wpdb;
$user_id = get_current_user_id();

/*
 *	If an ID was passed - the most likely outcome, indicating the user
 *	selected a value from the autocomplete - it should be a legitimate
 *	selection, but validate anyway.
 */
if ( $invite_id !== 0 ) {

	// check that user is being followed by the person being invited
	// 11/24/14 - temporarily disabling this check to facilitate invites
	//$is_followed	= $wpdb->get_var( "SELECT user_id FROM wp_qb_followers WHERE user_id = '$user_id' AND follower_id = '$invite_id'" );

	// check that person being invited is followed by the current user
	$is_following	= $wpdb->get_var( "SELECT user_id FROM wp_qb_followers WHERE user_id = '$invite_id' AND follower_id = '$user_id'" );

	// if mutual followers, look up invitee info
	if ( $is_following ) { //&& $is_followed ) {
		$invitee = get_user_by( 'id', $invite_id );

		// make sure invitee is not already a collaborator on the board
		if ( qb_check_if_collaborating( $invitee->user_email, $board_id ) == false ) {
			echo json_encode( array( 'errors' => 'This person is already a collaborator on this board.' ) );
			exit;
		}

		if ( !$invitee_avatar = get_wp_user_avatar_src( $invitee->ID, 48 ) ) {
			$invitee_avatar = DEFAULT_THUMBNAIL;
		}
		
		$return_html = '
		<li class="awaiting-invite" data-email="' . $invitee->user_email . '" data-id="' . $invitee->ID . '">
			<a href="' . get_bloginfo( 'home' ) . '/author/' . $invitee->user_nicename . '" target="_blank">
				<img src="' . $invitee_avatar . '" width="48" height="48" />
				<span>' . $invitee->display_name . '</span>
				<span>Awaiting Invitation</span>
			</a>
			<a class="delete remove-invitee" href="">(remove)</a>
		</li>';

		echo json_encode( array( 'html' => $return_html, 'email' => $invitee->user_email ) );
	} else {
		echo json_encode( array( 'errors' => 'To invite someone, you must be following them and they must also be following you.' ) );
	}

	exit;
}


/*
 *	Checks if the passed in email belongs to a user that is already
 *	collaborating on the board, to prevent a superfluous invitation
 *	from being sent.
 */
function qb_check_if_collaborating( $email, $board_id ) {
	foreach ( $members = get_field( 'board_members', $board_id ) as $member ) {
		if ( $member['board_members_user']['user_email'] == $email && $member['can_collaborate'] == 'y' ) {
			return false;
		}
	}

	return true;
}


/*
 *	Checks if the passed in email belongs to a user that was already
 *	invited to collaborate. Does this by looking for an invite code
 *	in the database.
 */
function qb_check_if_invited( $email, $board_id ) {
	global $wpdb;
	foreach ( $members = get_field( 'board_members', $board_id ) as $member ) {
		$member_email =  $member['board_members_user']['user_email'];
		if ( $member_email == $email && $wpdb->get_var( "SELECT invite_code FROM wp_qb_invite_codes WHERE invite_email = '$member_email' AND board_id = '$board_id'" ) ) {
			return false;
		}
	}

	return true;
}