<?php
/*
 *	Custom functions for extending the FTW theme.
 */

// add support for menus
register_nav_menu( 'primary', 'Header Menu' );
register_nav_menu( 'secondary', 'Footer Menu' );
register_nav_menu( 'drawer', 'Side Drawer Menu' );

// add support for post thumbnails
add_theme_support( 'post-thumbnails' );

// custom image sizes
add_image_size ( 'board-thumb', 210, 210, true );
add_image_size ( 'thumb-small', 60, 60, true );

// determine if on local
if ( stristr( home_url(), 'localhost') ) {
	$is_sandbox = true;
} else {
	$is_sandbox = false;
}

// define constants
define( IS_SANDBOX, $is_sandbox );
define( SUPERADMIN_USER_ID, 2 );
define( DEFAULT_THUMBNAIL_URL, get_field( 'default_thumbnail', 'option' ) );
define( DEFAULT_BACKGROUND, get_field( 'default_background', 'option' ) );
define( DEFAULT_PAGE_BACKGROUND, get_field( 'default_site_background', 'option' ) );
define( DEFAULT_THUMBNAIL_ID, qb_get_attachment_id_by_url( DEFAULT_THUMBNAIL_URL ) );
define( DEFAULT_BACKGROUND_ID, qb_get_attachment_id_by_url( DEFAULT_BACKGROUND ) );
define( MINIMUM_PASSWORD_LENGTH, 6 );
define( MINIMUM_USERNAME_LENGTH, 4 );
define( TIMTHUMB_PATH, get_bloginfo( 'template_url' ) . '/includes/timthumb.php?src=' );
define( TERMS_PAGE_ID, 74);
define( RESULTS_PER_PAGE, 20);
define( GOOGLE_CSE_ID, '010552372868030502357:om3c3sh6jmw' );
define( GOOGLE_API_KEY, 'AIzaSyDF4c90Y32LWHCTsO5OAWxhgm5wYCF-GOA' );

// set default timezone
date_default_timezone_set( 'America/Denver' );


/*
 *	Fetch a segment from the URL
 *	To test locally, where the URL has an extra segment,
 *	simply change the last line to:
 *
 *		return $segments[$n+1];
 */
function url_segment( $n ) {
	$segments = explode( '/', $_SERVER['REQUEST_URI'] );

	// URL structure is different between dev and prod
	if ( IS_SANDBOX === true ) {
		return $segments[$n+2];
	} else {
		return $segments[$n];
	}
}


/*
 *	Returns the ID of an attachment, given an attachment URL
 *	Useful for making sure the default thumbnail and background IDs
 *	defined in the constants section are always accurate
 *
 *	Thanks to Frankie Jarrett
 *	http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
 */
function qb_get_attachment_id_by_url( $url ) {
	// split the $url into two parts with the wp-content directory as the separator.
	$parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

	// get the host of the current site and the host of the $url, ignoring www.
	$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
	$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

	// return nothing if there aren't any $url parts or if the current host and $url host do not match.
	if ( ! isset( $parse_url[1] ) || empty( $parse_url[1] ) || ( $this_host != $file_host ) ) {
		return;
	}

	// quickly search the DB for any attachment GUID with a partial path match.
	// Example: /uploads/2013/05/test-image.jpg
	global $wpdb;

	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1] ) );

	// returns null if no attachment is found.
	return $attachment[0];
}


/*
 *	Add new role for "Member Added" as a copy of Subscriber
 *	It is for accounts created when users attribute quotes to those not yet members
 */
function qb_add_roles(){
	$roles_set = get_option( 'member_added_role_set' );
	
	if ( !$roles_set ) {
		add_role( 'member_added', 'Member Added', array(
			'read' => true
		) );
		update_option( 'member_added_role_set', true );
	}
}
add_action( 'after_setup_theme', 'qb_add_roles' );


/*
 *  Customize ACF Options Page
 *  Overrides the default settings for the Advanced Custom Fields
 *  "Options" page. For more on this, see the docs here:
 *  http://www.advancedcustomfields.com/resources/filters/acfoptions_pagesettings/
 */

function qb_acf_options( $settings ) {
	$settings['title'] = 'Site Options';
	$settings['pages'] = array( 'Code', 'Default Imagery', 'Email Templates', 'Homepage', 'Social Media' );

	return $settings;
}
 
add_filter( 'acf/options_page/settings', 'qb_acf_options' );


/*
 *	Properly enqueue required JS scripts
 *
 *  For more on wp_enqueue_script(), see the docs at:
 *  http://codex.wordpress.org/Function_Reference/wp_enqueue_script
 */

function qb_enqueue_scripts() {
	wp_enqueue_script( 'autocomplete', get_template_directory_uri() . '/js/autocomplete.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/js/fancybox.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'chosen', get_template_directory_uri() . '/js/chosen.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'form', get_template_directory_uri() . '/js/form.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'timeago', get_template_directory_uri() . '/js/timeago.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'load', get_template_directory_uri() . '/js/load.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'utility', get_template_directory_uri() . '/js/utility.js', array( 'load' ), '0.0.4', true );
	// TO DO: load final.min.js instead (ajax may depend on the name "utility" above)

	// load tutorial script on welcome page
	if ( stristr( $_SERVER['REQUEST_URI'], '?welcome' ) ) {
	}
}

add_action( 'wp_enqueue_scripts', 'qb_enqueue_scripts' );


/*
 *  Properly enqueue required CSS files
 *
 *  For more on wp_enqueue_style(), see the docs at:
 *  http://codex.wordpress.org/Function_Reference/wp_enqueue_style
 */

function qb_enqueue_styles() {
	wp_enqueue_style( 'master', get_template_directory_uri() . '/css/style.css', '0.0.2' );

	// load tutorial script on welcome page
	if ( stristr( $_SERVER['REQUEST_URI'], '?welcome' ) || stristr( $_SERVER['REQUEST_URI'], '?new' ) ) {
		wp_enqueue_style( 'intro', get_template_directory_uri() . '/css/introjs.css' );
	}
}

add_action( 'wp_enqueue_scripts', 'qb_enqueue_styles' );


/*
 *	Register custom sidebar widgets
 */
register_sidebar( array(
	'after_title' 	=> '</h3>',
	'after_widget' 	=> '</div>',
	'before_title' 	=> '<h3>',
	'before_widget'	=> '<div class="widget">',
	'description' 	=> 'Widgetized area for static page sidebars.',
	'id'      		=> 'sidebar',
	'name'      	=> 'Page Sidebar'
) );

register_sidebar( array(
	'after_title' 	=> '</h3>',
	'after_widget' 	=> '</div>',
	'before_title' 	=> '<h3>',
	'before_widget'	=> '<div class="widget">',
	'description' 	=> 'Widgetized area for blog sidebars.',
	'id'      		=> 'sidebar-blog',
	'name'      	=> 'Blog Sidebar'
) );


/*
 *	Custom Comment Display
 *
 *	Overrides default WP comment display with our own.
 */

function qb_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	//$avatar 			= get_avatar( $comment, 48 );
	$comment_author_id	= get_comment( get_comment_ID() )->user_id;
	$comment_author_url	= get_comment_author_url();

	if ( has_wp_user_avatar( $comment_author_id ) ) {
		//$avatar	= get_wp_user_avatar_src( $comment_author_id, 48 );
		$avatar = get_wp_user_avatar( $comment_author_id, 48 );
	} else {
		//$avatar = get_bloginfo( 'template_url' ) . '/images/default/48.png';
		// temporary: this is the same for now, for testing; eventually, it should fall back to default image or Facebook image or smt else
		//$avatar	= get_wp_user_avatar_src( $comment_author_id, 48 );
		$avatar = get_wp_user_avatar( $comment_author_id, 48 );
	}
	?>

	<li <?php get_comment_class(); ?>><?php

		if ( $comment_author_url ) {
			echo '<a href="' . $comment_author_url . '">' . $avatar . get_comment_author() . '</a>';
		} else {
			echo $avatar . get_comment_author();
		}

		echo '<time class="timeago" datetime="' . get_comment_date( 'c' ) . '">' . get_comment_date( 'F j, Y' ) . '</time>';

		comment_text();

		//edit_comment_link( 'Edit', '<span class="edit">', '</span>' );
		if ( $comment->comment_approved == '0' ) {
			echo '<em class="moderate">Your comment is awaiting moderation.</em>';
		}

		/*comment_reply_link(
			array_merge(
				$args,
				array(
					'reply_text'	=> 'Reply',
					'depth' 		=> $depth,
					'max_depth' 	=> $args['max_depth']
				)
			)
		);*/
	echo '</li>';
}


/*
 *	Initializes the Facebook JavaScript SDK
 *	Docs: https://developers.facebook.com/docs/facebook-login/login-flow-for-web/v2.0
 *
 *	We only care about setting this up and checking login status
 *	for logged out users.
 */
function qb_init_facebook_sdk() { ?>
	<script>
		// default to production app ID
		var facebook_app_id = '178780368854509';

		// if on staging, use test app ID		
		if (window.location.host.indexOf('staging') !== -1) {
			facebook_app_id = '825591250840081';
		}

		// Load the SDK asynchronously
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/sdk.js";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));

		window.fbAsyncInit = function() {
			// initialize JavaScript SDK
			FB.init({
				appId      : facebook_app_id,
				cookie     : true,  // enable cookies to allow the server to access the session
				xfbml      : true,  // parse social plugins on this page
				version    : 'v2.0' // use version 2.0
			});
		}
	</script>
	<?php
}


// sandbox for now - working on connecting to Twitter's API
function twitter_oauth() {
	// date_default_timezone_set("UTC");

	$twitter_api_key 			= 'ytlTixe6MRJ5hLqnKN5ZN1jbc';
	$twitter_api_secret			= 'YjewpVpx76ouBpvgQnHwTHMc6LLZ1FOK92skB3odQWXBd0e3Ym';
	$twitter_token				= '531932164-xZGKqSmdpJAitdJ8bpP1llRkF6uXNw8Tcfq74kZ6';
	$twitter_token_secret 		= 'MAP3ZmXlBCxfIhC7lzG6DqvdFEWR1aDbaVdVGQ2cwCHU1';
	$current_unix_timestamp		= time();
	$oauth_nonce 				= trim( base64_encode( $current_unix_timestamp ), '=' );
	$redirect_url				= 'http://www.google.com';


	/*
	 *	Build a valid OAuth signature
	 *	docs: https://dev.twitter.com/docs/auth/creating-signature
	 */
	$signature_http_method		= 'POST';
	$signature_base_url			= 'https://api.twitter.com/oauth/request_token';

	$signature_param_string		= rawurlencode('oauth_callback') . '=' . rawurlencode( $redirect_url );
	$signature_param_string	   .= '&' . rawurlencode('oauth_consumer_key') . '=' . rawurlencode( $twitter_api_key );
	$signature_param_string	   .= '&' . rawurlencode('oauth_nonce') . '=' . rawurlencode( $oauth_nonce );
	$signature_param_string	   .= '&' . rawurlencode('oauth_signature_method') . '=' . rawurlencode('HMAC-SHA1');
	$signature_param_string	   .= '&' . rawurlencode('oauth_timestamp') . '=' . rawurlencode($current_unix_timestamp);
	$signature_param_string	   .= '&' . rawurlencode('oauth_token') . '=' . rawurlencode( $twitter_token );
	$signature_param_string	   .= '&' . rawurlencode('oauth_version') . '=' . rawurlencode('1.0');

	$signature_base_string		= $signature_http_method . '&' . rawurlencode( $signature_base_url );
	$signature_base_string	   .= '&' . rawurlencode( $signature_param_string );

	$signature_signing_key		= rawurlencode( $twitter_api_secret ) . '&';// . rawurlencode( $twitter_token_secret );

	$oauth_signature 			= base64_encode( hash_hmac( 'sha1', $signature_base_string, $signature_signing_key, true ) );
//return $oauth_signature;

	//////////////////// fuck this - try the following: https://github.com/themattharris/tmhOAuth ///////////////////////

	/*
	 *	Build an HTTP authorization header
	 *	docs: https://dev.twitter.com/docs/auth/authorizing-request
	 */
	$oauth_http_header  = 'oauth_callback="' . rawurlencode( $redirect_url ) . '", ';
	$oauth_http_header .= 'oauth_consumer_key="' . rawurlencode( $twitter_api_key ) . '", ';
	$oauth_http_header .= 'oauth_nonce="' . rawurlencode( $oauth_nonce ) . '", ';
	$oauth_http_header .= 'oauth_signature="' . rawurlencode( $oauth_signature ) . '", ';
	$oauth_http_header .= 'oauth_signature_method="' . rawurlencode( 'HMAC-SHA1' ) . '", ';
	$oauth_http_header .= 'oauth_timestamp="' . rawurlencode( $current_unix_timestamp ) . '", ';
	//$oauth_http_header .= 'oauth_token="' . rawurlencode( $twitter_token ) . '", ';
	$oauth_http_header .= 'oauth_version="' . rawurlencode( '1.0' ) . '"';

	//return $oauth_http_header;

	$params = array(
		'oauth_callback' => rawurlencode( $redirect_url )
	);

	/*
	 *	Make an HTTP request via cURL
	 */
	$options = array(
		CURLOPT_URL => $signature_base_url,
		CURLOPT_HEADER => false,
		CURLOPT_HTTPHEADER => array( 'Authorization: OAuth ' . $oauth_http_header, 'Expect:' ),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_POST => count($params),
		CURLOPT_POSTFIELDS => getParams($params)
	);

	//return getParams($params);

	$c = curl_init();
	//curl_setopt($c, CURLOPT_POST, 1);
	curl_setopt($c, CURLOPT_HEADER, 1);
	curl_setopt($c, CURLINFO_HEADER_OUT, true);
	curl_setopt_array($c, $options);

	$response = curl_exec($c);

	//curl_close($c);
	return array('response' => var_dump($response), 'request headers' => var_dump(curl_getinfo($c)) );
}

function getParams(array $params)
    {
        $r = '';

        ksort($params);

        foreach ($params as $key => $value) {
            $r .= '&' . $key . '=' . rawurlencode($value);
        }

        unset($params, $key, $value);

        return trim($r, '&');
    }

/*
 *	Create bit.ly short links
 *
 *	Automagically create bit.ly URL for use in, e.g., Twitter share links
 *	To use in a template: href="http://twitter.com/share?url=<?= urlencode( bitly() ); ?>
 */
/* currently not being used; here for reference - API key is correct
function bitly() {
	$url 		= get_permalink();
	$login 		= 'quoteboard';
	$apikey 	= 'R_0ed5132d7c414774b19851e1135dc8aa';
	$format 	= 'json';
	$version 	= '2.0.1';
	$bitly 		= 'http://api.bit.ly/shorten?version=' . $version . '&longUrl=' . urlencode( $url ) . '&login=' . $login . '&apiKey=' . $apikey . '&format=' . $format;

	$response = file_get_contents( $bitly );

	if ( strtolower( $format ) == 'json' ) {
		$json = @json_decode( $response, true );
		echo $json['results'][$url]['shortUrl'];
	} else {
		$xml = simplexml_load_string( $response );
		echo 'http://bit.ly/' . $xml->results->nodeKeyVal->hash;
	}
}
*/


/*
 *	Quote Fave Count
 *
 *	Returns whether a user has faved a quote, and the total number
 *	of faves for a given quote.
 */
function qb_count_faves( $quote ) {
	global $wpdb;

	// determine if user has faved quote
	$has_faved	= $wpdb->get_var(
	   "SELECT meta_id
		FROM $wpdb->postmeta
		WHERE post_id = '$quote'
		AND INSTR ( meta_key, 'quote_fave_user' ) > 0
		AND meta_value = '" . get_current_user_id() . "'
	   " );

	// get fave count
	$fave_count	= get_post_meta( $quote, 'quote_fave', true );

	if ( empty( $fave_count ) ) {
		$fave_count = 0;
	}

	return array( 'has_faved' => $has_faved, 'fave_count' => $fave_count );
}


/** 
 * SMTP configuration to pass all emails (even non-templated ones) 
 * through Mandrill.
 *
 *	NOTE: here for decorative purposes, since we accomplish the same functionality
 *	with the wpMandrill plugin.
 */
//add_action( 'phpmailer_init', 'qb_send_email_through_mandrill' );
function qb_send_email_through_mandrill( $phpmailer ) {
	$phpmailer->isSMTP();
    $phpmailer->SMTPAuth = true;
    $phpmailer->SMTPSecure = "tls";
     
    $phpmailer->Host = "smtp.mandrillapp.com";
    $phpmailer->Port = "587";
  
    // Credentials for SMTP authentication
    $phpmailer->Username = 'mandrill@quoteboard.com';
    $phpmailer->Password = 'A0OD2ZTRhCPITZYSpVDHIQ';
  
    // From email and name
    $from_name = 'Quoteboard';
    if ( ! isset( $from_name ) ) {
        $from_name = 'WordPress';
    }
 
    $from_email = 'noreply@quoteboard.com';        
    if ( ! isset( $from_email ) ) {
        // Get the site domain and get rid of www.
        $sitename = strtolower( $_SERVER['SERVER_NAME'] );
        if ( 'www.' == substr( $sitename, 0, 4 )  ) {
            $sitename = substr( $sitename, 4 );
        }
         
        $from_email = 'wordpress@' . $sitename;
    }
     
    $phpmailer->From = $from_email;
    $phpmailer->FromName = $from_name;
}


// count_user_posts(), but with custom post type support
function count_user_posts_by_type( $userid, $post_type = 'post', $public_only = false ) {
	global $wpdb;
	$where = get_posts_by_author_sql( $post_type, true, $userid, $public_only );
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE ( ( post_type = 'quote' AND ( post_status = 'publish' ) ) ) AND post_author = 684" );
	return $where;//apply_filters( 'get_usernumposts', $count, $userid );
}


// custom paginate function, similar to one in WP Codex
function qb_paginate() {
    global $wp_query, $wp_rewrite;
    $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
    $pagination = array(
        // 'base' => @add_query_arg('page','%#%'),
        // 'format' => '',
        'total' 	=> $wp_query->max_num_pages,
        'current' 	=> $current,
        'show_all' 	=> false,
        'type' 		=> 'plain'
    );
    if ( $wp_rewrite->using_permalinks() ) $pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
    if ( !empty($wp_query->query_vars['s']) ) $pagination['add_args'] = array( 's' => get_query_var( 's' ) );
    
    echo '<div class="pagination">' . paginate_links( $pagination ) . '</div>';
}


/**
 * 	Modifies the main query to only count published posts (e.g., quotes)
 *	Otherwise, pagination is off since it counts all posts, which results
 *	in extra pages being shown.
 */
function qb_only_show_public_in_archives( $query ) {
	if ( $query->is_main_query() && $query->is_post_type_archive()  ) {
		$query->set( 'post_status', 'publish' );
	}
}
add_action( 'pre_get_posts', 'qb_only_show_public_in_archives' );


// add_filter('found_posts', 'qb_adjust_archive_pagination', 1, 2 );
// function qb_adjust_archive_pagination($found_posts, $query) {

//     //Define our offset again...
//     $offset = 10;

//     //Ensure we're modifying the right query object...
//     // if ( $query->is_home() ) {
//     //     //Reduce WordPress's found_posts count by the offset... 
//     //     return $found_posts - $offset;
//     // }
//     // return 1069;
//     $query->set( 'post_status', 'publish' );
//     return $found_posts;
// }


// strips "Private" from private board names
function format_private_post_titles( $content ) {
	return '%s';
}
add_filter( 'private_title_format', 'format_private_post_titles' );


/**
 *	Builds an API query string for use with the Google Custom Search API
 *	Docs: https://developers.google.com/custom-search/json-api/v1/reference/cse/list?hl=en
 */
function build_google_api_url( $query ) {
	return 'https://www.googleapis.com/customsearch/v1?q=' . $query . '&cx=' . urlencode( GOOGLE_CSE_ID ) . '&c2coff=1&gl=us&fileType=jpg&filter=1&imgType=face&siteSearchFilter=e&num=1&searchType=image&key=' . urlencode( GOOGLE_API_KEY );
}


/**
 *	Returns a default image of a specified size
 *	$size can be a keyword (e.g., 'thumbnail') or an array (e.g., array(60, 60))
 */
function get_default_thumbnail( $size ) {
	return wp_get_attachment_image( DEFAULT_THUMBNAIL_ID, $size );
	// return '<img src="' . DEFAULT_THUMBNAIL_URL . '" width="' . $size . '" height="' . $size . '">';
}


/**
 *	Display ALL users in author dropdown box when editing post in backend.
 *	Apparently, it is a bug - see more here:
 *	http://wordpress.stackexchange.com/questions/91862/users-with-custom-roles-not-showing-in-post-author-select-box
 */
function author_override( $output ) {
    global $post, $user_ID;

    // return if this isn't the theme author override dropdown
    if (!preg_match('/post_author_override/', $output)) return $output;

    // return if we've already replaced the list (end recursion)
    if (preg_match ('/post_author_override_replaced/', $output)) return $output;

    // replacement call to wp_dropdown_users
      $output = wp_dropdown_users(array(
        'echo' => 0,
        'name' => 'post_author_override_replaced',
        'selected' => empty($post->ID) ? $user_ID : $post->post_author,
        'include_selected' => true
      ));

      // put the original name back
      $output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);

    return $output;
}
add_filter('wp_dropdown_users', 'author_override');


// create custom post types
include_once( TEMPLATEPATH . '/includes/custom_post_type_quotes.php' );
include_once( TEMPLATEPATH . '/includes/custom_post_type_boards.php' );
include_once( TEMPLATEPATH . '/includes/custom_post_type_sources.php' );

// include AJAX and utility functions
include_once( TEMPLATEPATH . '/includes/utility.php' );
include_once( TEMPLATEPATH . '/includes/ajax.php' );