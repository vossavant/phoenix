<?php
/*
 *	QUOTEBOARD
 *	Edit Quote
 *
 *	Updates a quote
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// make sure quote ID is a non-negative integer
$quote_id = absint( $_POST['qid'] );

if ( $quote_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed (invalid quote ID).' ) );
	exit;
endif;

// make sure current user authored this quote, or is the admin (user ID #2)
global $wpdb;
$current_user_id = get_current_user_id();
$can_edit = $wpdb->get_var( "SELECT post_status FROM $wpdb->posts WHERE ID = '" . $quote_id . "' AND post_author = '" . $current_user_id . "'" );

if ( !$can_edit ) :
	if ( $current_user_id != 2 ) {
		echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
		exit;
	}
endif;

// gather and sanitize fields, etc
include_once( TEMPLATEPATH . '/includes/gather-quote-fields.php' );

// prepare for update
$update_parameters	= array(
	'ID'			=> $quote_id,
	'post_content'	=> $quote_text,
	'post_status'	=> $quote_status,
	'post_title'	=> $quote_title,
	'tags_input'	=> $quote_tags
);

// do the update
$quote_updated_id	= wp_update_post( $update_parameters );

// update custom fields
if ( $quote_updated_id ) {

	update_post_meta( $quote_updated_id, 'quote_author', $quote_attributed_to_id );
	update_post_meta( $quote_updated_id, '_quote_author', 'field_507658884a81f' );

	update_post_meta( $quote_updated_id, 'quote_source', $quote_sourced_to_id );
	update_post_meta( $quote_updated_id, '_quote_source', 'field_507658884b202' );

	update_post_meta( $quote_updated_id, 'quote_board', $quote_board );
	update_post_meta( $quote_updated_id, '_quote_board', 'field_5077ae14b09b9' );

	// replace hashtags with links
	$quote_text = preg_replace( '/#(\w+)/', ' <i>#</i><a href="' . home_url('/') . 'tag/$1">$1</a>', $quote_text );

	// link contributor name
	$contributor_username = get_user_by( 'id', $quote_attributed_to_id );
	$quote_attributed_to = '<a href="' . home_url('/') . 'author/' . $contributor_username->user_nicename . '" title="See quotes attributed to ' . $quote_attributed_to . '">' . $quote_attributed_to . '</a>';

	// link source
	$quote_source = '<a href="' . get_permalink( $quote_sourced_to_id ) . '" title="See quotes from ' . $quote_source . '">' . $quote_source . '</a>';

	// board image and link
	if ( has_post_thumbnail( $quote_board ) ) {
		$board_thumbnail		= wp_get_attachment_image_src( get_post_thumbnail_id( $quote_board ), 'full' );
		$board_thumbnail_src	= $board_thumbnail[0];
	} else {
		$board_thumbnail_src	= get_field( 'default_thumbnail', 'option' );
	}

	$quote_board_img = '<a href="' . get_permalink( $quote_board ) . '"><img src="' . TIMTHUMB_PATH . $board_thumbnail_src . '&w=48&h=48" alt="' . get_the_title( $quote_board ) . '" />' . get_the_title( $quote_board ) . '</a>';

	// return params for jQuery
	echo json_encode(
		array(
			'quote_id' 			=> $quote_updated_id,
			'quote_text'		=> wp_unslash( nl2br( $quote_text ) ),
			'quote_author'		=> $quote_attributed_to,
			'quote_author_id'	=> $quote_attributed_to_id,
			'quote_source'		=> $quote_source,
			'quote_board_id'	=> $quote_board,
			'quote_board_img'	=> $quote_board_img,
			'quote_status'		=> $quote_status
		)
	);

} else {
	echo json_encode(
		array(
			'errors' => 'There was a problem updating your quote'
		)
	);
}

exit();