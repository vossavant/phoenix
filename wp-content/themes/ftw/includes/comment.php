<?php
/*
 *	QUOTEBOARD
 *	Add comment
 *
 *	Programatically adds a comment to the database. This is part of a custom
 *	replacement for the built-in WP comment form.
 *
 *	TO DO: adjust 'comment_parent' so it accounts for nested comments.
 */

if ( !is_user_logged_in() ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed.' ) );
	exit;
endif;

// gather and sanitize comment
$comment 	= wp_unslash( sanitize_text_field( $_POST['comment'] ) );
$quote_id 	= absint( $_POST['comment_post_ID'] );

// check for required fields
if ( empty( $comment ) ) {
	echo json_encode( array( 'errors' => 'Please enter a comment' ) );
	exit;
}

if ( $quote_id === 0 ) :
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed (invalid quote ID).' ) );
	exit;
endif;

// get commenter info
$current_user = wp_get_current_user();

// set up comment properties
$comment_parameters = array(
	//'comment_agent'			=> $_SERVER['HTTP_USER_AGENT'],	//uncomment if we care about tracking this
	//'comment_approved'	=> 1,
	'comment_author' 		=> $current_user->display_name,
	'comment_author_email'	=> $current_user->user_email,
	'comment_author_IP'		=> $_SERVER['REMOTE_ADDR'],
	'comment_author_url'	=> $current_user->user_url,
	'comment_content'		=> $comment,
	'comment_date'			=> current_time( 'mysql' ),
	'comment_parent'		=> 0,	// TO DO: dynamically set this to account for nested comments (replies)
	'comment_post_ID' 		=> $quote_id,
	//'comment_type' 		=> '',
	'user_id'				=> $current_user->ID
);

// add the comment
if ( $comment_added_id = wp_insert_comment( $comment_parameters ) ) {
	wp_notify_postauthor( $comment_added_id );

	if ( !$comment_avatar = get_wp_user_avatar( $current_user->ID, 48 ) ) {
		$comment_avatar = DEFAULT_THUMBNAIL;
	}

	$comment_html = '
	<li>' . $comment_avatar . $current_user->display_name . '<time class="timeago" datetime="' . date( 'c' ) . '">' . date( 'F j, Y' ) . '</time>
		<p>' . $comment . '</p>
	</li>';

	echo json_encode(
		array(
			'comment_html' => $comment_html
		)
	);

} else {
	echo json_encode(
		array(
			'errors' => 'There was a problem adding your comment'
		)
	);
}

exit();


/*
function ajaxify_comments($comment_ID, $comment_status){
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	//If AJAX Request Then
		switch($comment_status){
			case '0':
				//notify moderator of unapproved comment
				wp_notify_moderator($comment_ID);
			case '1': //Approved comment
				echo json_encode( array( 'result' => 'Things went well' ) );
				$commentdata=&get_comment($comment_ID, ARRAY_A);
				$post=&get_post($commentdata['comment_post_ID']); 
				wp_notify_postauthor($comment_ID, $commentdata['comment_type']);
			break;
			default:
				echo json_encode( array( 'result' => 'Things went poorly' ) );
		}
		exit;
	}
}
add_action( 'comment_post', 'ajaxify_comments', 20, 2 );
*/