<?php
/*
 *	This script is called from both includes/register.php and includes/register-facebook.php.
 *	It contains code common to both, and performs the following functions:
 *		- Create a new, default board for the user
 *		- Set the user to follow QB Staff
 *		- Set the user to collaborate on all of QB Staff's boards
 *		- Authenticate and log in the user


 *	Create a new, empty board and assign to the user, so they have something to add to
 */

// board name (was)is in the form "User's Board"
//$board_name = get_user_meta( $new_user_id, 'nickname', true ) . "'s Default Board";
$board_name = "Your Default Board";

// prepare for insert
$insert_parameters = array (
	'post_author'	=> $new_user_id,
	'post_content'	=> 'This is my default board. Any new quotes I add will appear here automatically, unless I specify a different board.',
	'post_name'		=> sanitize_title( $board_name ),
	'post_status'	=> 'private',
	'post_title'	=> $board_name,
	'post_type'		=> 'board'
	//'tax_input' 	=> array( 'board_cats' => array( $board_category_id ) )
);

// insert the post
$board_added_id = wp_insert_post( $insert_parameters );

// set thumbnail and cover photo
add_post_meta( $board_added_id, '_thumbnail_id', DEFAULT_THUMBNAIL_ID );
update_field( 'field_52e58bcbd6d1d', DEFAULT_BACKGROUND_ID, $board_added_id );

// set as default board
add_post_meta( $board_added_id, 'is_default', 'yes' );

// add board members
$board_user_id = $new_user_id;
require( TEMPLATEPATH . '/includes/add-board-members.php' );


/*
 *	Set user to follow Quoteboard Staff so they don't have an empty Quote Stream
 */
$wpdb->insert(
	wp_qb_followers,
	array(
		'user_id'		=> SUPERADMIN_USER_ID,
		'follower_id'	=> $new_user_id
	),
	array(
		'%d',
		'%d'
	)
);

// get a list of all boards by Quoteboard Staff
$qb_staff_boards = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_author = '" . SUPERADMIN_USER_ID . "' AND post_type = 'board' AND post_status = 'publish'" );

// follow all of QB Staff's (public) boards
foreach ( $qb_staff_boards as $board ) {
	$field_key	= 'field_5077ae931b6f6';
	$value		= get_field( $field_key, $board->ID );
	$value[] 	= array( 'board_members_user' => $new_user_id, 'can_collaborate' => 'y' );
	
	if ( !update_field( $field_key, $value, $board->ID ) ) {
		echo json_encode( array( 'errors' => 'There was a problem following the board ' . $board->post_title ) );
		exit;
	}
}


/*
 *	Authenticate user and log in
 */

$credentials = array(
	'user_login' 	=> $user_email,
	'user_password' => $user_password,
	'remember' 		=> true
);

$authenticate = wp_signon( $credentials, false );

if ( is_wp_error( $authenticate ) ) {
	echo json_encode(
		array(
			'result' => false,
			'errors' => 'There was a problem signing you in: ' . $authenticate->get_error_message()
		)
	);

} else {
	wp_set_current_user( $authenticate->ID, $authenticate->user_login );

	// return param for jQuery redirect
	echo json_encode(
		array(
			'result' => true,
			'redirect_to' => home_url() . '?welcome'
		)
	);
}