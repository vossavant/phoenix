<?php
/*
 *	QUOTEBOARD
 *	Validate board invite code
 *
 *	Called from page-invite.php. Checks the validation code in the URL
 *	and ensures it is valid and associated with the logged in user's
 *	email address. If so, add user as a collaborator and delete the invite.
 */

if ( url_segment( 2 ) ) {
	// clean and sanitize invite code
	$invite_code = ltrim( url_segment( 2 ), '?' );
	$invite_code = sanitize_text_field( $invite_code );
	$can_collab  = false;

	if ( is_user_logged_in() ) {

		// check if this invite code is valid for this email
		if ( $board_id = $wpdb->get_var( "SELECT board_id FROM wp_qb_invite_codes WHERE invite_email = '$current_user->user_email' AND invite_code = '$invite_code'" ) ) {
			// check if user is already following the board
			foreach( $members = get_field( 'board_members', $board_id ) as $key => $member ) {
				if ( $member['board_members_user']['ID'] == $current_user->ID ) {
					$updated = update_post_meta( $board_id, 'board_members_' . $key . '_can_collaborate', 'y' );
					$can_collab = true;
					break;
				}
			}

			// if not, add user as a collaborator
			if ( !$can_collab ) {
				$field_key	= 'field_5077ae931b6f6';
				$value		= get_field( $field_key, $board_id );
				$value[] 	= array( 'board_members_user' => $current_user->ID, 'can_collaborate' => 'y' );
				
				if ( update_field( $field_key, $value, $board_id ) ) {
					$can_collab = true;

				// unable to add user as board member
				} else {
					echo '<p class="message shown error"><strong>Bloody hell!</strong> There was a problem adding you as a collaborator to the board <em>' . get_the_title( $board_id ) . '</em>.</p>';
					get_template_part('forms/feedback');
				}
			}

			// if successfully added/updated, delete invite code and show success message
			if ( $can_collab ) {
				$wpdb->delete(
					wp_qb_invite_codes,
					array(
						'invite_email' 	=> $current_user->user_email,
						'invite_code'	=> $invite_code
					),
					array(
						'%s',
						'%s'
					)
				);

				echo '<p class="message shown success"><strong>Success.</strong> You\'re now a collaborator on <em><a href="' . get_permalink( $board_id ) . '">' . get_the_title( $board_id ) . '</a></em>, meaning you can add quotes there. With great power comes great responsibility...</p>';
				echo '<a class="btn" href="' . get_permalink( $board_id ) . '">Take me to ' . get_the_title( $board_id ) . '</a>';
			}
		
		// invalid invite code
		} else {
			echo '<p class="message shown error"><strong>Your invitation code is invalid</strong>. Make sure you are signed into the right account; the invitation was sent to the same email you use to sign in.<br /><br />Also, verify that you clicked the correct link and that it is not missing any characters.<br /><br />If you need help, fill out the form below.</p>';
		}

	// user not logged in
	} else {
		// check if invite code is for an existing member
		if ( $is_member = $wpdb->get_var( "SELECT is_member FROM wp_qb_invite_codes WHERE invite_code = '$invite_code'" ) ) {
			echo '<p class="message shown info"><strong>Please sign in</strong>. Your invitation code appears to be valid, but we need you to <a class="fancybox" href="#login-form">sign in</a> so we can be sure.</p>';

		// invite code is for a non-member
		} else {
			if ( $invite_email = $wpdb->get_var( "SELECT invite_email FROM wp_qb_invite_codes WHERE invite_code = '$invite_code'" ) ) {
				$board_name = $wpdb->get_var(
				   "SELECT post_title
					FROM $wpdb->posts
					INNER JOIN wp_qb_invite_codes ON board_id = ID
					WHERE invite_email = '$invite_email' AND invite_code = '$invite_code'"
				);
				include( TEMPLATEPATH . '/forms/register-invite.php' );
			} else {
				echo '<p class="message shown error"><strong>Your invitation code is invalid</strong>. Please verify that you clicked the correct link in the invitation that was emailed to you. It is also possible that you already signed up with this code.<br /><br />If you need help, fill out the form below.</p>';
				get_template_part('forms/feedback');
			}
		}
	}

// no invite code in URL
} else {
	echo '<p class="message shown error"><strong>Your invite code is missing</strong>. Please be sure to click the link in your invitation email, or copy and paste the entire link in your browser\'s address bar. If you need help, fill out the form below.</p>';
	get_template_part('forms/feedback');
}