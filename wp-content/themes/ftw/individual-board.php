<?php
/*
 *	QUOTEBOARD
 *	Single Board
 *
 *	Called from loop-boards.php and search.php.
 *	Displays a single board in list view.
 */
$board_id = get_the_ID();
$is_board_curator = false;
$follow_button = '';

// fetch stats
$quotecount  = $wpdb->get_var ( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'quote_board' AND meta_value LIKE '%" . $post->ID . "%'" );
$followcount = get_post_meta( $board_id, 'board_members', true );

// if admin authored, it's a public board (this will change)
if ( $post->post_author == SUPERADMIN_USER_ID ) {
	$is_public = true;
} else {
	$is_public = false;
}

// check if default
$is_default = $wpdb->get_var( "SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = 'is_default' AND post_id = '$board_id' AND meta_value = 'yes'" );

// thumbnail
if ( has_post_thumbnail() ) {
	$board_thumbnail		= wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
	$board_thumbnail_src	= $board_thumbnail[0];
} else {
	$board_thumbnail_src	= get_field( 'default_thumbnail', 'option' );
}

// background
if ( !$board_background = get_field( 'background_image', $post->ID ) ) {
	$board_background = DEFAULT_BACKGROUND;
}

if ( !$board_description = get_the_content() ) {
	$board_description = '<em>Alas, this board has no description.</em>';
}

if ( is_user_logged_in() ) :
	// follow button & curator star (get list of members first)
	$members = get_field( 'board_members', $board_id );

	// determine if current user is a member of this board
	foreach ( $members as $member ) :
		// further check if author
		if ( $current_user->ID == $post->post_author ) {
			$is_board_curator = true;
			break;
		} else {
			if ( $member['board_members_user']['ID'] == $current_user->ID ) {
				if ( $is_public || $member['can_collaborate'] == 'y' ) {
					$follow_button = '<span class="ico collab collaborating board" data-id="' . $board_id . '" title="Collaborating"></span>';
				} else {
					$follow_button = '<span class="ico follow following board" data-id="' . $board_id . '" title="Following"></span>';
				}

				break;
			} else {
				if ( $is_public ) {
					$follow_button = '<span class="ico collab board" data-id="' . $board_id . '" title="Collaborate"></span>';
				} else {
					$follow_button = '<span class="ico follow board" data-id="' . $board_id . '" title="Follow"></span>';
				}
			}
		}
	endforeach;
endif;

// etc
$board_title = get_the_title();
$board_url = get_permalink();

echo
'<article class="board box' . ( $post->post_status == 'private' ? ' private' : '' ) . ( $is_public ? ' public' : '' ) . ( $is_default ? ' default' : '' ) . '"' . ( $post->post_status == 'private' ? ' title="Private Board"' : '' ) . ( $is_public ? ' title="Public Board"' : '' ) . '>
	<a class="avatar" href="' . $board_url . '">
		<img src="' . TIMTHUMB_PATH . $board_thumbnail_src . '&w=60&h=60" alt="' . $board_title . '" />
	</a>
	<div>
		<div class="bubble" data-link="' . get_permalink() . '">
			<h4>' . $board_title . '</h4>' . (  $is_public ? '<span class="box-meta">This is a public board</span>' : ( $is_default ? '<span class="box-meta">Your default board</span>' : ( $is_board_curator ? '<span class="box-meta">You created this board</span>' : '' ) ) ) . '
			<p>' . $board_description . '</p>
		</div>
		<menu>' . $follow_button . '</menu>
	</div>
</article>';