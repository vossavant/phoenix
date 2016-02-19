<?php
/*
 *	QUOTEBOARD
 *	Get Characters
 *
 *	Retrieves a list of characters. Used to populate the character
 *	dropdown, and called when the "Add Quote" or "Edit Quote" forms
 *	are used.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;


if ( isset( $_POST['form_name'] ) && $_POST['form_name'] == 'get-characters' ) {
	global $wpdb;
	$json_response 	= '';


	/**
	 *	Build JSON object for autocomplete plugin (for proper formatting, see docs: https://github.com/devbridge/jQuery-Autocomplete)
	 *	addslashes() escapes double quotes, which break everything
	 */
	if ( $get_characters = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'character' AND post_status = 'publish' ORDER BY post_title" ) ) {
		$json_response = '[';

		foreach ( $get_characters as $character ) {
			$json_response .= '{ value: "' . addslashes( $character->post_title ) . '", data: "' . $character->ID . '" },';
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