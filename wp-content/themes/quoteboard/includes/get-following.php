<?php
/*
 *	QUOTEBOARD
 *	Get following
 *
 *	Retrieves a list of people the current user is following. Used to populate
 *	the "invite friends to board" borm. Called when "Invite Friends" lightbox
 *	is opened. This file will likely be replaced by get-mutual-followers.php
 *	as Quoteboard continues to grow.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;


if ( isset( $_POST['form_name'] ) && $_POST['form_name'] == 'get-following' ) {
	global $wpdb;
	$user_id 		= get_current_user_id();
	$user_following = array();
	$json_response 	= '';

	// get a list of peeps current user is following
	$get_following = $wpdb->get_results(
	   "SELECT ID, display_name
		FROM wp_qb_followers
		JOIN $wpdb->users ON user_id = ID
		WHERE follower_id = '$user_id'"
	);

	// build JSON object for autocomplete plugin
	// for proper formatting, see docs: https://github.com/devbridge/jQuery-Autocomplete
	if ( $get_following ) {
		$json_response = '[';

		foreach ( $get_following as $following ) {
			$json_response .= '{ value: "' . $following->display_name . '", data: "' . $following->ID . '" },';
		}

		$json_response = rtrim( $json_response, ',' );
		$json_response .= ']';
	} else {
		$json_response = '[]';
	}

	echo json_encode(
		array(
			'json_response' => $json_response
		)
	);

	exit;
}