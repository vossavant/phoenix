<?php
/*
Plugin Name: WP_Rewrite endpoints demo
Description: A plugin giving example usage of the WP_Rewrite endpoint API
Plugin URI: http://make.wordpress.org/plugins/2012/06/07/rewrite-endpoints-api/
Author: Jon Cave
Author URI: http://joncave.co.uk/
*/

function makeplugins_endpoints_add_endpoint() {
	// register a "boards" endpoint to be applied to authors
	add_rewrite_endpoint( 'quotes', EP_AUTHORS | EP_PERMALINK );
	add_rewrite_endpoint( 'boards', EP_AUTHORS );
	add_rewrite_endpoint( 'faves', EP_AUTHORS );
	add_rewrite_endpoint( 'members', EP_PERMALINK );
	add_rewrite_endpoint( 'followers', EP_AUTHORS | EP_PERMALINK );
	add_rewrite_endpoint( 'following', EP_AUTHORS | EP_PERMALINK );
	add_rewrite_endpoint( 'profile', EP_AUTHORS | EP_PERMALINK );
}
add_action( 'init', 'makeplugins_endpoints_add_endpoint' );

/*
function makeplugins_endpoints_template_redirect() {
	global $wp_query;
	
	// if this is not a request for boards or it's not a singular object then bail
	if ( ! isset( $wp_query->query_vars['boards'] ) || ! is_singular() )
			return;
	
	// output some boards (normally you might include a template file here)
	include dirname( __FILE__ ) . '/boards-template.php';
	exit;
}
add_action( 'template_redirect', 'makeplugins_endpoints_template_redirect' );
*/

function makeplugins_endpoints_activate() {
	// ensure our endpoint is added before flushing rewrite rules
	makeplugins_endpoints_add_endpoint();
	// flush rewrite rules - only do this on activation as anything more frequent is bad!
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'makeplugins_endpoints_activate' );

function makeplugins_endpoints_deactivate() {
	// flush rules on deactivate as well so they're not left hanging around uselessly
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'makeplugins_endpoints_deactivate' );