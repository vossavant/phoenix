<?php
/*
 *	QUOTEBOARD
 *	Board Invite Form
 *
 *	Loaded via AJAX when a user clicks to "Invite Peeps" from a board page.
 *	Pulls up a list of all current and pending collaborators.
 */

if ( $_POST['form_name'] != 'modal-board-invite' ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed' ) );
	exit;
}

$board_id 		= absint( $_POST['modal_id'] );
$board_author	= get_post( $board_id )->post_author;
$user_id 		= get_current_user_id();

if ( $user_id != $board_author ) {
	echo json_encode( array( 'errors' => 'Sorry, you can only invite people to collaborate on boards you have created' ) );
	exit;
}

// get collaborators
global $wpdb;
$collaborators = '';

foreach( $members = get_field( 'board_members', $board_id ) as $member ) {
	// get avatar
	if ( !$member_avatar = get_wp_user_avatar( $member['board_members_user']['ID'], 48 ) ) {
		$member_avatar = DEFAULT_THUMBNAIL;
	}

	// get email
	$member_email = $member['board_members_user']['user_email'];

	// get current collaborators
	if ( $member['can_collaborate'] == 'y' ) {
		$collaborators .= '
		<li data-email="' . $member_email . '" data-id="' . $member['board_members_user']['ID'] . '">
			<a href="' . get_bloginfo( 'home' ) . '/author/' . $member['board_members_user']['user_nicename'] . '" target="_blank">' .
				$member_avatar . '
				<span>' . $member['board_members_user']['display_name'] . '</span>' .
				'<span>' . ( $user_id != $member['board_members_user']['ID'] ? 'Invited by you' : 'You created this board' ) . '</span>
			</a>' . ( $user_id != $member['board_members_user']['ID'] ? '<a class="delete remove-collab" href="">(remove)</a>' : '' ) . '
		</li>';
	}
}

// check for pending collaborators (invitations sent by board curator but not yet accepted)
if ( $pending_invitees = $wpdb->get_results( "SELECT invite_email, is_member FROM wp_qb_invite_codes WHERE board_id = '$board_id'" ) ) {
	foreach ( $pending_invitees as $pending ) {
		// get info for existing members
		if ( $pending->is_member ) {
			$existing_member = get_user_by( 'email', $pending->invite_email );

			$collaborators .= '
			<li class="awaiting-invite" data-email="' . $pending->invite_email . '" data-id="' . $existing_member->ID . '">
			<a href="' . get_bloginfo( 'home' ) . '/author/' . $existing_member->user_nicename . '" target="_blank">' .
				get_wp_user_avatar( $existing_member->ID, 48 ) . '
				<span>' . $existing_member->display_name . '</span>
				<span>Invitation Sent &ndash; Awaiting Response</span>
			</a></li>';// . ( $user_id != $member['board_members_user']['ID'] ? '<a class="delete remove-pending-collab" href="">(remove)</a>' : '' ) . '
			//</li>';
		
		// else the pending invite is for a non-member
		} else {
			$collaborators .= '
			<li class="awaiting-invite" data-email="' . $pending->invite_email . '">
				<div class="ico email invite-email"></div>
				<span>' . $pending->invite_email . '</span>
				<span>Invitation Sent &ndash; Awaiting Response</span>
			</a></li>';
		}
	}
}



// build HTML return
$profile_html = '
<form action="" id="board-invite" method="post">
	<h2>Invite People to This Board <span class="ico close" title="Close">Close</span></h2>

	<div class="inner-btn">
		<input id="invite-email" name="invite_email" placeholder="Type a name or email address..." type="text" />
		<a class="btn secondary add-invite" data-id="" href="" title="Add to queue">Add</a>
		<span class="error invite hidden">Please enter a name or email address</span>
	</div>

	<div class="hidden success">
		<p class="success message shown">Success! Your invitations were sent OK.</p>
		<button class="btn close no-ajax" type="button">Ok</button>
	</div>

	<div class="hidden error">
		<p class="error message shown">Bananas! We were unable to send all of your invitations. We\'ve been notified and will take a look ASAP.</p>
		<button class="btn close no-ajax" type="button">Ok</button>
	</div>

	<div class="who-can-add">
		<h4>Who can add quotes?</h4>
		<ul class="stacked invitees" data-board="' . $board_id . '">' . $collaborators . '</ul>
	</div>

	<div style="padding-bottom: 20px;">
		<input name="board_id" type="hidden" value="' . $board_id . '" />
		<input name="form_name" type="hidden" value="invite-member-send" />
		<button class="btn wide" disabled type="submit">Send Invites</button>
	</div>
</form>';

echo json_encode( array( 'html' => $profile_html ) );
exit;