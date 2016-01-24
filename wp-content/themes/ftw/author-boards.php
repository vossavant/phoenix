<?php
/*
 *	QUOTEBOARD
 *	Author Boards
 *
 *	Displays all of the boards current user created. This template
 *	is called from author.php and is only accessed when the URL
 *	endpoint is 'boards'.
 *
 *	Later: show boards user follows?
 */

/* this query pulls boards the current user authored or is a member of (regardless of whether can collaborate)
$query = 
   "SELECT *
	FROM $wpdb->posts
	INNER JOIN $wpdb->postmeta ON ID = post_id
	WHERE meta_key LIKE 'board_members_%_board_members_user'
	AND meta_value = '$current_page_user_id'
	AND post_status <> 'trash'
	AND post_type = 'board'
	ORDER BY post_date DESC";
*/
/* this doesn't work b/c of the constant
$query =
  "SELECT *
   FROM $wpdb->posts
   WHERE post_author = '$current_page_user_id'
   AND post_status <> 'trash'
   AND post_type = 'board'
   ORDER BY post_date DESC
   LIMIT '" . RESULTS_PER_PAGE . "'";
*/

$boards = get_posts(
	array(
		'author'    	=> $current_page_user_id,
		'posts_per_page'=> RESULTS_PER_PAGE,
		'post_status'	=> $post_status,
		'post_type'   	=> 'board'
	)
);

//$boards = $wpdb->get_results( $query, OBJECT );

include( TEMPLATEPATH . '/loop-boards.php' );