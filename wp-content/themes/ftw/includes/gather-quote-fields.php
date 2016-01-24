<?php
/*
 *	QUOTEBOARD
 *	Gather Form Fields
 *
 *	Gathers form fields and performs operations common to both
 *	adding and editing a quote. Better than having code in two places.
 *	Called from add-quote.php and edit-quote.php.
 */

// gather and sanitize form fields
//$quote_text_hashed		= wp_unslash( sanitize_text_field( $_POST['quote_text'] ) );
$quote_text_hashed		= implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['quote_text'] ) ) ); // keeps line breaks
$quote_attributed_to 	= wp_unslash( sanitize_text_field( $_POST['quote_author'] ) );
$quote_source		 	= wp_unslash( sanitize_text_field( $_POST['quote_source'] ) );
$quote_attributed_to_id	= absint( $_POST['quote_author_id'] );
$quote_sourced_to_id	= absint( $_POST['quote_source_id'] );
$quote_board 			= absint( $_POST['quote_board'] );
$quote_hashtags 		= array();
$current_user_id 		= get_current_user_id();
$source_added_id		= '';

// check for required fields
if ( empty( $quote_text_hashed ) ) {
	echo json_encode( array( 'errors' => 'Please enter a quote' ) );
	exit;
}

// if no board specified, assign to default board
if ( empty( $quote_board ) ) {
	$quote_board = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta JOIN $wpdb->posts ON post_id = ID WHERE meta_key = 'is_default' AND meta_value = 'yes' AND post_author = '$current_user_id'" );

// make sure user can collaborate on the chosen board
} else {
	foreach ( $members = get_field( 'board_members', $quote_board ) as $member ) {
		if ( $member['board_members_user']['ID'] == $current_user_id ) {
			if ( $member['can_collaborate'] != 'y' ) {
				echo json_encode( array( 'errors' => 'You are not allowed to post quotes to this board' ) );
				exit;
			} else {
				break;
			}
		}
	}
}

// if adding or moving quote to a public board, make sure quote privacy is set to publish
if ( get_post_status( $quote_board ) == 'private' ) {
	$quote_status = 'private';
} else {
	$quote_status = 'publish';
}

// if "who said it" field blank, assign to "Anonymous"
if ( empty( $quote_attributed_to ) ) {
	//$quote_attributed_to = get_user_by( 'id', $current_user_id )->display_name; // this assigns to the current user and was the old way
	$quote_attributed_to = 'Anonymous';
	//$quote_attributed_to_id = $current_user_id; // old way
	$quote_attributed_to_id = '145';

// else user chose autocomplete option (make sure current user is followed by and following the passed-in user ID)
} elseif ( $is_following = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$quote_attributed_to_id' AND follower_id = '$current_user_id'" ) && $followed_by = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$current_user_id' AND follower_id = '$quote_attributed_to_id'" ) ) {
	$quote_attributed_to_id = $quote_attributed_to_id;

// else check if the string entered matches nickname of an existing user
} elseif ( $existing_user_id = $wpdb->get_var( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'nickname' AND meta_value LIKE '$quote_attributed_to'" ) ) {
	$quote_attributed_to_id = $existing_user_id;

// else create a new user in the "Member Added" role
} else {
	$new_username = sanitize_user( sanitize_title_with_dashes( $quote_attributed_to ) );

	// assign a random password, then move to the "Member Added" role
	if ( !username_exists( $new_username ) ) {
		$quote_attributed_to_id = wp_create_user( $new_username, wp_generate_password() );
		wp_update_user( array(
			'ID' 			=> $quote_attributed_to_id,
			'display_name'	=> $quote_attributed_to,
			'nickname' 		=> $quote_attributed_to,
			'role' 			=> 'member_added'
			)
		);

	// in the event the username already exists, attribute this quote to the current user
	} else {
		$quote_attributed_to_id = $current_user_id;
	}
}

// assign quote source
if ( !empty( $quote_source ) ) {
	// check if ID matches an existing ID
	if ( $existing_source_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE ID = '" . $quote_sourced_to_id . "' AND post_type = 'source' AND post_status = 'publish'" ) ) {
		$quote_sourced_to_id = $existing_source_id;

	// check if the string entered matches an existing source
	// if ( $existing_source_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . mysql_real_escape_string( $quote_source ) . "' AND post_type = 'source' AND post_status = 'publish'" ) ) {
	// 	$quote_sourced_to_id = $existing_source_id;

	// else create a new source
	} else {
		$source_title 	= truncate( $quote_source, 72 );
		$source_slug	= sanitize_title( $source_title );

		$insert_parameters = array (
			'post_author'	=> $current_user_id,
			'post_name'		=> $source_slug,
			'post_status'	=> 'publish',
			'post_title'	=> $source_title,
			'post_type'		=> 'source',
		);

		$source_added_id = wp_insert_post( $insert_parameters );
		$quote_sourced_to_id = $source_added_id;
	}
}

// store all hashtags in an array (note: add dash support for two words by using \w+-?\w+)
preg_match_all( '/(#\w+)/', $quote_text_hashed, $hashtags );

// create new, hashless array of tags
foreach ( $hashtags[0] as $key => $tag ) {
	array_push( $quote_hashtags, trim( $tag, '#' ) );
}

// transform array of hashtags into comma-separated string
$quote_tags = implode( ',', $quote_hashtags );

// strip hashes from quote
$quote_text_hashless = truncate( str_replace( '#', '', $quote_text_hashed ), 750 );

// keep hashes in quote
$quote_text = truncate( $quote_text_hashed, 750 );

// assign other necessary fields
$quote_title 	= truncate( str_replace( "\n", ' ', $quote_text_hashless ), 72 );
$quote_slug		= sanitize_title( $quote_title );