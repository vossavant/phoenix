<?php
/*
 *	QUOTEBOARD
 *	Get authors
 *
 *	Retrieves a list of member-added authors AND people the current author
 *	is following. Used to populate the "who said it" dropdown, and called
 *	when the "Add Quote" (inline and fancybox) or "Edit Quote" form is used.
 *
 *	NOTE: this is relatively open and gives users more choices, rather than
 *	limiting the results to mutual followers. I think it's better to keep
 *	it more open at the early stages, when QB is small, to facilitate growth.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;


if ( isset( $_POST['form_name'] ) && $_POST['form_name'] == 'get-authors' ) {
	global $wpdb;
	$user_id 		= get_current_user_id();
	$user_following = array();
	$json_response 	= '';


	/**
	 *	Step one: get user objects for people the current user is following
	 */

	// get IDs
	$get_following = $wpdb->get_results(
	   "SELECT user_id
		FROM wp_qb_followers
		WHERE follower_id = '$user_id'"
	);

	// extract IDs from query
	foreach ( $get_following as $following ) {
		array_push( $user_following, $following->user_id );
	}

	// create a string that we can use in the next query
	$user_following_string = implode( ',', $user_following );

	// prevent get_users() from retrieving ALL users (which is what happens when an empty array is passed)
	if ( empty( $user_following_string ) ) {
		$user_following_string = array( 0 );
	}

	// turn IDs into user objects
	$get_following = get_users(
		array(
			'include'	=> $user_following_string,
			'orderby'	=> 'display_name'
		)
	);


	/**
	 *	Step two: get a list of member-added authors
	 */
	$get_members = get_users(
		array(
			'orderby'	=> 'display_name',
			'role'		=> 'member_added'
		)
	);

	// build JSON object for autocomplete plugin (for proper formatting, see docs: https://github.com/devbridge/jQuery-Autocomplete)
	// addslashes() escapes double quotes, which break everything
	if ( $get_following || $get_members ) {
		$json_response = '[';

		foreach ( $get_following as $following ) {
			$json_response .= '{ value: "' . addslashes( $following->display_name ) . '", data: "' . $following->ID . '" },';
		}

		foreach ( $get_members as $member_added ) {
			$json_response .= '{ value: "' . addslashes( $member_added->display_name ) . '", data: "' . $member_added->ID . '" },';
		}

		$json_response = rtrim( $json_response, ',' );
		$json_response .= ']';
	} else {
		$json_response = '[]';
	}

	// return for jQuery goodness
	echo json_encode(
		array(
			'json_response' => $json_response
		)
	);

	exit;
}