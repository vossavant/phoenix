<?php
/*
 *	QUOTEBOARD
 *	Add Quote
 *
 *	Adds a new quote. Loaded when a user clicks the "New Quote" button.
 */
echo json_encode(array('boards' => $_POST['quote_boards']));
exit;

if ($_POST['is_scrape']) {
	require_once( '../../../../wp-load.php' );
} elseif ( !is_user_logged_in() ) {
	echo json_encode(array('errors' => 'Sorry, this form submission is not allowed.'));
	exit;
}

// set default timezone
date_default_timezone_set ( 'America/Denver' );

// gain access to wpdb object
global $wpdb;

// gather and sanitize fields, etc
include_once( TEMPLATEPATH . '/includes/gather-quote-fields.php' );

if ($_POST['is_scrape']) {
	$current_user_id = 2;
}

// prepare for insert
$insert_parameters = array (
	'post_author'	=> $current_user_id,
	'post_content'	=> $quote_text,
	'post_name'		=> $quote_slug,
	'post_status'	=> $quote_status,
	'post_title'	=> $quote_title,
	'post_type'		=> 'quote',
	'tags_input'	=> $quote_tags
);

// insert the quote
$quote_added_id = wp_insert_post( $insert_parameters );

// prepare params to update quote slug to its ID
$update_parameters = array(
	'ID'		=> $quote_added_id,
	'post_name'	=> $quote_added_id
);

// update the quote slug
$quote_updated	= wp_update_post( $update_parameters );

// serialize board IDs
$quote_boards = serialize($quote_boards);

// update custom fields
if ( $quote_updated ) {

	add_post_meta( $quote_added_id, 'quote_author', $quote_attributed_to_id );
	add_post_meta( $quote_added_id, '_quote_author', 'field_507658884a81f' );

	add_post_meta( $quote_added_id, 'quote_character', $quote_character_id );
	add_post_meta( $quote_added_id, '_quote_character', 'field_56c724e28d8b8' );

	add_post_meta( $quote_added_id, 'quote_source', $quote_sourced_to_id );
	add_post_meta( $quote_added_id, '_quote_source', 'field_507658884b202' );
	
	add_post_meta( $quote_added_id, 'quote_board', $quote_boards );
	add_post_meta( $quote_added_id, '_quote_board', 'field_5077ae14b09b9' );

	add_post_meta( $quote_added_id, 'quote_link', $quote_source_info );
	add_post_meta( $quote_added_id, '_quote_link', 'field_568d2f6302c1e' );

	// return params for jQuery
	echo json_encode(
		array(
			'added_by'	=> $current_user_id,
			'author_id' => $quote_attributed_to_id,
			'board_id'	=> $quote_boards,
			'quote_id'	=> $quote_added_id,
			'home_url'	=> home_url(),
			'avatar'	=> get_wp_user_avatar( $current_user_id, 80 ),
			'quote_url'	=> get_permalink( $quote_added_id ),
			'username'	=> get_user_by( 'id', $current_user_id )->user_nicename,
			'datetime'	=> date('c'),
			'quote'		=> wp_unslash( nl2br( $quote_text_hashless ) ),
			'privacy'	=> $quote_status,
			'permalink'	=> '<a href="' . get_the_permalink( $quote_boards ) . '">' . get_the_title( $quote_boards ) . '</a>',
		)
	);

	exit();

} else {
	echo json_encode(
		array(
			'permalink' => false
		)
	);
}

/*


//test

echo json_encode(
	array(
		'hashed quote' => $quote_text_hashed,
		'attributed to' => $quote_attributed_to,
		'attributed to ID' => $quote_attributed_to_id,
		'board id' => $quote_boards,
		'current user' => $current_user_id,
		'hashtags' => $quote_hashtags,
		'tags' => $quote_tags,
		'text' => $quote_text,
		'title' => $quote_title,
		'slug' => $quote_slug,
		'permalink' => 'http://sand.box:8080/qb/quote/as-civilization-advances-the-sense-of-wonder-declines-such-decline/'
	)
);

exit;
*/