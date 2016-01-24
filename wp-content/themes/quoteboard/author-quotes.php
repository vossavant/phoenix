<?php
/*
 *	QUOTEBOARD
 *	Author Quotes
 *	Displays all of the quotes authored by the current user.
 *
 *	This template is called from author.php and is only accessed when
 *	the URL endpoint is 'quotes'
 */

get_header();

$quotes = get_posts(
	array(
		'author'		=> $current_page_user_id,
		'paged' 		=> ( get_query_var('paged') ? get_query_var('paged') : 1 ),
		'posts_per_page'=> RESULTS_PER_PAGE,
		'post_status'	=> ( $current_user->ID == $current_page_user_id ? array( 'private', 'publish' ) : 'publish' ),
		'post_type' 	=> 'quote'
	)
);

// show quick add form
if ( $current_user->ID == $current_page_user_id ) {
	$inline_form_placeholder = 'Add a quote...';
	include( TEMPLATEPATH . '/forms/add-quote-inline.php' );
}

// show the quotes
include( TEMPLATEPATH . '/loop-quotes.php' );