<?php
/*
 *	HELPER FUNCTIONS
 *	Various helper functions used throughout the theme's scripts
 */

// makes a URL-friendly string (DEPCRECATED and UNUSED since we can use WP's sanitize_title() function)
//function slugify( $text ) {
//    // Swap out Non "Letters" with a -
//    $text = preg_replace('/[^\\pL\d]+/u', '-', $text); 
//
//    // Trim out extra -'s
//    $text = trim($text, '-');
//
//    // Convert letters that we have left to the closest ASCII representation
//    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
//
//    // Make text lowercase
//    $text = strtolower($text);
//
//    // Strip out anything we haven't been able to convert
//    $text = preg_replace('/[^-\w]+/', '', $text);
//
//    return $text;
//}

// creates an excerpt from post content
function truncate( $str, $length = 100, $trailing = '...' ) {
	// take off chars for the trailing
	$length -= mb_strlen ( $trailing );

	if ( mb_strlen ( $str ) > $length ) {
		// string exceeded length, truncate and add trailing dots
		return mb_substr ( $str, 0, $length ) . $trailing;
	} else {
		// string was already short enough, return the string
		$res = $str;
	}

	return $res;
}