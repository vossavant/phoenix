<?php
/*
 *	QUOTEBOARD
 *	Get Sources
 *
 *	Retrieves a list of quote sources. Used to populate the "where was it said"
 *	input. Called when the "Add Quote" or "Edit Quote" forms are opened.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;


if ( isset( $_POST['form_name'] ) && $_POST['form_name'] == 'get-sources' ) {
	global $wpdb;
	$json_response 	= '';

	// build JSON object for autocomplete plugin (for proper formatting, see docs: https://github.com/devbridge/jQuery-Autocomplete)
	if ( $get_sources = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'source' AND post_status = 'publish' ORDER BY post_title") ) {
		$json_response = '[';

		foreach ( $get_sources as $source ) {
			$json_response .= '{ value: "' . $source->post_title . '", data: "' . $source->ID . '" },';
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