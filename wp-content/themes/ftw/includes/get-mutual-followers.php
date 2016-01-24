<?php
/*
 *	QUOTEBOARD
 *	Get mutual followers
 *
 *	Retrieves a list of mutual followers (people the current user is following
 *	that are also following the current user). Used to populate the "who said it"
 *	dropdown. Called when the "Add Quote" or "Invite Friends" lightbox is opened.
 *
 *	NOTE: this file is not currently being used, but is here for potential future
 *	use. The idea is that, as Quoteboard is yet a small community, it doesn't
 *	make sense to limit people to just inviting or seeing mutual followers.
 *	For now, we are instead using get-authors.php (for adding/editing quotes)
 *	and get-following.php (for inviting to boards) to make it easier to find people.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;


if ( isset( $_POST['form_name'] ) && $_POST['form_name'] == 'get-mutual-followers' ) {
	global $wpdb;
	$user_id 		= get_current_user_id();
	$user_followers = array();
	$json_response 	= '';

	// get a list of current user's followers
	$get_followers = $wpdb->get_results(
	   "SELECT follower_id
		FROM wp_qb_followers
		WHERE user_id = '$user_id'"
	);

	// push results to an array; avoids querying within a loop
	foreach ( $get_followers as $follower ) {
		array_push( $user_followers, $follower->follower_id );
	}

	$user_followers_string = implode( ',', $user_followers );

	// get a list of mutual followers
	$get_mutual_following = $wpdb->get_results(
	   "SELECT ID, display_name
		FROM wp_qb_followers
		JOIN $wpdb->users ON user_id = ID
		WHERE follower_id = '$user_id'
		AND user_id IN($user_followers_string)"
	);

	// build JSON object for autocomplete plugin
	// for proper formatting, see docs: https://github.com/devbridge/jQuery-Autocomplete
	if ( $get_mutual_following ) {
		$json_response = '[';

		foreach ( $get_mutual_following as $following ) {
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