<?php
/*
 *	QUOTEBOARD
 *	Get Authors
 *
 *	Retrieves a list of authors ("member-added" user role). Used to
 *	populate the "who said it" dropdown, and called when the "Add Quote"
 *	or "Edit Quote" forms are used.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;


if ( isset( $_POST['form_name'] ) && $_POST['form_name'] == 'get-authors' ) {
	global $wpdb;
	$json_response 	= '';

	// get the authors
	$get_authors = get_users(
		array(
			'orderby'	=> 'display_name',
			'role'		=> 'member_added'
		)
	);


	/**
	 *	Build JSON object for autocomplete plugin (for proper formatting, see docs: https://github.com/devbridge/jQuery-Autocomplete)
	 *	addslashes() escapes double quotes, which break everything
	 */
	if ( $get_authors ) {
		$json_response = '[';

		foreach ( $get_authors as $member_added ) {
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