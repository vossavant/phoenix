<?php
/*
 *	QUOTEBOARD
 *	Get Board List
 *
 *	Retrieves a list of the boards the current user follows.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

$user_id = get_current_user_id();

$query =
   "SELECT post_id, post_status, meta_key, meta_value, post_title
   	FROM $wpdb->postmeta
   	INNER JOIN $wpdb->posts ON post_id = ID
   	WHERE meta_key LIKE 'board_members_%_board_members_user'
	AND meta_value = '$user_id'
	AND post_status <> 'trash'
	AND post_type = 'board'
	ORDER BY post_title";

$board_list = '
<select data-placeholder="Post to which boards?" multiple name="quote_boards[]">
	<option value=""></option>
	<option value="">&mdash; Create New Board &mdash;</option>';

	// see: http://www.advancedcustomfields.com/resources/tutorials/querying-the-database-for-repeater-sub-field-values/
	if ( $boards_member_of = $wpdb->get_results( $query, OBJECT ) ) {
		foreach ( $boards_member_of as $board ) {
			preg_match( '_([0-9]+)_', $board->meta_key, $matches );
			$meta_key = 'board_members_' . $matches[0] . '_can_collaborate';
			
			if ( get_post_meta( $board->post_id, $meta_key, true ) == 'y' ) {
				$board_list .= '<option class="' . ( $board->post_status == 'private' ? 'private' : '' ) . '" value="' . $board->post_id . '">' . $board->post_title . '</option>';
			}
		}
	}

	$board_list .= '
</select>';

echo $board_list;