<?php
/*
 *	QUOTEBOARD
 *	Author Favorites
 *	Displays all of a user's favorite quotes. For favorites, we must run
 *	a custom $wpdb query, since the standard get_posts function does
 *	not let us filter results by a meta_key that *contains* certain text.
 *	
 *	To filter favorites, we search for a meta_key that has 'quote_fave_user'
 *	in it, then match the meta_value with the current user ID.
 *	
 *	This template is called from author.php and is only accessed when
 *	the URL endpoint is 'faves'
 */

$query =
   "SELECT $wpdb->posts.*
	FROM $wpdb->posts, $wpdb->postmeta
	WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
	AND INSTR ( $wpdb->postmeta.meta_key, 'quote_fave_user' ) > 0
	AND $wpdb->postmeta.meta_value = $current_page_user_id
	AND $wpdb->posts.post_status <> 'trash'
	AND $wpdb->posts.post_type = 'quote'
	AND $wpdb->posts.post_date < NOW()
	ORDER BY $wpdb->posts.post_date DESC
	LIMIT " . RESULTS_PER_PAGE;

$quotes = $wpdb->get_results( $query, OBJECT );

include( TEMPLATEPATH . '/loop-quotes.php' );

// LEFT OFF HERE - two problems:
// 1. favorited quote is not highlighted in the heart area
// 2. private quotes show up for everyone on faves page