<?php
/*
 *	QUOTEBOARD
 *	Add Quote to Favorites
 *
 *	Adds or removes a quote from a user's favorites list
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// set default timezone
date_default_timezone_set ( 'America/Denver' );

// gather and sanitize form fields
$quote_id 	= absint( $_POST['qid'] );
$user_id 	= get_current_user_id();
$has_saved	= false;
$fave_count = get_post_meta( $quote_id, 'quote_fave', true );

// check for required fields
if ( empty( $quote_id ) ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
}

// get field info
$rows = get_field( 'quote_fave', $quote_id );

// determine if user already faved this quote
if ( $rows ) {
	foreach ( $rows as $key => $row ) {
		if ( $row['quote_fave_user']['ID'] == $user_id ) {
			$has_saved = true;
			break;
		}
	}
}

// user is un-favoriting the quote - remove faves association
if ( $has_saved ) {
	delete_post_meta( $quote_id, '_quote_fave_' . $key . '_quote_fave_date' );
	delete_post_meta( $quote_id, '_quote_fave_' . $key . '_quote_fave_user' );
	delete_post_meta( $quote_id, 'quote_fave_' . $key . '_quote_fave_date' );
	delete_post_meta( $quote_id, 'quote_fave_' . $key . '_quote_fave_user' );
	
	// update row (fave) count
	if ( $success = update_post_meta( $quote_id, 'quote_fave', $fave_count - 1 ) ) {
		$fave_count--;
	}

// user is favoriting the quote - add a new row to the repeater field
} else {
	$field_key	= 'field_5077ada8aad08';
	$value		= get_field( $field_key, $quote_id );
	$value[] 	= array( 'quote_fave_user' => $user_id, 'quote_fave_date' => date( 'Ymd' ) );
	
	if ( $success = update_field( $field_key, $value, $quote_id ) ) {
		$fave_count++;
	}
}

// return params for jQuery
if ( $success ) {
	echo json_encode(
		array(
			'fave_count' => $fave_count
		)
	);

} else {
	echo json_encode(
		array(
			'errors' => 'There was a problem favoriting or un-favoriting this quote'
		)
	);
}

exit();