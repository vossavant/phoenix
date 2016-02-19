<?php
/*
 *	QUOTEBOARD
 *	Author Template
 *
 *	This template is called by visiting a URL like this:
 *
 *		http://www.quoteboard.com/[username]
 *
 *	It displays the various views for a particular user; e.g., quotes, boards,
 *	faves, and followers. Relies on the WP_Rewrite endpoints plugin.
 *
 *	Permalinks must be set to custom thusly: /%author%/%postname%/
 *
 *	TO DO: URL segments will likely change when we move live; double check them here.
 *	url segments in local dev go like this:
 *
 *		qb/author/burney/quotes
 *
 *	where segment 1 = qb, segment 4 = quotes
 */

// doing it this way vs get_header() gives access to all variables declared in header
if (!is_front_page() ) {
	include( TEMPLATEPATH . '/header.php' );
}

$base_author_url = get_bloginfo( 'home' ) . '/author/' . $user_url_nicename;

if ( $current_page_user_id == $current_user->ID ) {
	$home_feed_url = get_bloginfo( 'home' );
	$post_status = array('private', 'publish');
	$public_only = false;
} else {
	$home_feed_url = $base_author_url;
	$post_status = 'publish';
	$public_only = true;
}

$count_faves =
  "SELECT COUNT(*)
   FROM $wpdb->postmeta
   INNER JOIN $wpdb->posts ON post_id = ID
   WHERE meta_key LIKE 'quote_fave_%_quote_fave_user'
   AND meta_value = '$current_page_user_id'
   AND post_status <> 'trash'";

// stats for current user
$author_quote_count 	= $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'quote_author' AND meta_value = $current_page_user_id" );
$current_user_boards 	= count_user_posts_by_type( $current_page_user_id, 'board', $public_only );
$current_user_faves		= $wpdb->get_var( $count_faves );
// $current_user_followers = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$current_page_user_id'" );
// $current_user_following = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE follower_id = '$current_page_user_id'" );

// used a couple of times
$upload_directory 		= wp_upload_dir();

// get user background
if ( $background_id = get_user_meta( $current_page_user_id, 'user_background', true ) ) :
	$background_src 	= $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '" . $background_id . "' AND meta_key = '_wp_attached_file'" );
	$author_cover_photo = $upload_directory['baseurl'] . '/' . $background_src;
else : 
	$author_cover_photo = DEFAULT_BACKGROUND;
endif;

// determine what kind of author we're viewing (get current author role)
$author_info = get_user_by( 'id', $current_page_user_id );
$author_role = implode( ', ', $author_info->roles );

// change author name to Wikipedia friendly (underscores, capitalized)
$author_name = ucwords( str_replace( '-', ' ', $author_info->user_nicename ) );
$author_name = str_replace( ' ', '_', $author_name );


/**
 *	Fetch User Avatar
 *
 *	First, check the wp_uploads/avatars directory for an image name matching author nicename.
 *	If this image isn't found, use the Google Custom Search API to get an image, then save it.
 *	If this fails, fall back to user avatar plugin, and finally to a default thumbnail.
 *
 *	TO DO: this should really check for WP User Avatar first, so I can override the Google image
 *	if I don't like it. This would require:
 *
 *		1. Removing the default WP User Avatar for all users
 *		2. Updating the registration flow to assign users a Google image vs. default WP User Avatar
 *			- ID of attachment stored in wp_usermeta table under key `wp_user_avatar` (Dillard: 684 - attachment ID: 2511)
 *		3. Reworking the logic below to check first for a WP User Avatar, then for Google Image, then assign default
 */
if ( !$author_photo = get_wp_user_avatar( $current_page_user_id, 'thumbnail' ) ) :
	// specify the API query 
	$google_api_url = build_google_api_url( urlencode( $author_info->display_name ) );

	// get API result via cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $google_api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// if API fails, use default thumbnail
	if ( !$google_api_response = curl_exec($ch) ) {
		$author_photo = DEFAULT_THUMBNAIL;

	// parse API result - see docs for example response (https://developers.google.com/custom-search/json-api/v1/reference/cse/list?hl=en#response)
	} else {
		$google_api_response_json = json_decode( $google_api_response, true );
		$google_image_url = $google_api_response_json['items'][0]['link'];
		$local_image_path = $upload_directory['basedir'] . '/avatars/' . $author_info->user_nicename . '.jpg';
		$remote_image_url = $upload_directory['baseurl'] . '/avatars/' . $author_info->user_nicename . '.jpg';

		// crop and save image with native WP function - for more see: http://bhoover.com/wp_image_editor-wordpress-image-editing-tutorial/
		$wp_avatar = wp_get_image_editor( $google_image_url );
		if ( !is_wp_error( $wp_avatar ) ) {
			// $wp_avatar->resize( 180, 180, true );
			// $wp_avatar->crop( 0, 0, 140, 180 );
			$wp_avatar->set_quality( 100 );
			$wp_avatar->save( $local_image_path );

			/**
			 *	Now that the image is uploaded to the wp_uploads directory,
			 *	we need to add it to the media library as an attachment.
			 */
			$attachment = array(
				'post_mime_type'	=> 'image/jpeg',
				'post_title' 		=> $author_info->display_name,
				'post_content' 		=> '',
				'post_status' 		=> 'inherit'
			);


			$attach_id = wp_insert_attachment( $attachment, $local_image_path );

			// require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $local_image_path );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// link avatar with user (so it's recognized by WP User Avatar)
			add_post_meta( $attach_id, '_wp_attachment_wp_user_avatar', $current_page_user_id );
			update_user_meta( $current_page_user_id, 'wp_user_avatar', $attach_id );
		}

		// user now has an avatar, so fetch it!
		$author_photo = get_wp_user_avatar( $current_page_user_id, 'thumbnail' );
	}

	curl_close($ch);
endif;


// user bio - if it isn't specified, attempt to get from Wikipedia
if ( !$user_bio = get_user_meta( $current_page_user_id, 'description', true ) ) {
	$api_url = 'https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exsentences=2&redirects&titles=' . $author_name;
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Quoteboard/1.0 (http://www.quoteboard.com/; elebrias@gmail.com)');

	$api_result = curl_exec($ch);

	curl_close($ch);

	// API call fails
	if (!$api_result) {
		$user_bio = 'Alas, we don\'t have any information about this person.';
		exit('cURL Error: ' . curl_error( $ch ) );

	// parse API result
	} else {
		$api_result = json_decode( $api_result, true );
		$api_result = $api_result['query']['pages'];

		foreach ($api_result as $page_details) {
			$user_bio  = wp_strip_all_tags( $page_details['extract'] );
			$user_bio .= '<p><a href="http://www.wikipedia.org/wiki/' . $author_name . '">Wikipedia</a></p>';
			break;
		}

		// if the author name was "normalized" or changed to a format Wikipedia recognizes
		// if ( $api_result['query']['normalized'] ) {
		// }
	}

	// update bio
	update_user_meta( $current_page_user_id, 'description', $user_bio );
}


echo
'<section class="main extra wide">
	<div class="flex">
		<div class="flex-60">';

			$quotes = new WP_Query(
				array(
					'meta_query' => array(
						array(
							'key' 	=> 'quote_author',
							'value'	=> $current_page_user_id
						)
					),
					'posts_per_page'	=> -1,
					'post_status'	=> 'publish',
					'post_type' 	=> 'quote'
				)
			);

			include( TEMPLATEPATH . '/loop-quotes.php' );

			echo '
		</div>

		<div class="flex-child flex-40">
			<div class="profile">
				<div class="cover" style="background: url(' . $author_cover_photo . ') center no-repeat;">' .
					$author_photo .
					'<div>
						<div>
							<h3><span>Quotes ' . ( $author_role == 'member_added' ? 'Attributed To' : 'Added By' ) . '</span>' . $author_info->display_name . '</h3>
						</div>
					</div>
				</div>
				<div class="meta">' .
					wpautop( $user_bio );

					echo
					'<a href="' . home_url() . '/feedback">Report an Error</a>
				</div>';
				
				// debug for wikipedia author name conversion
				// print_r($test);
				
				echo
			'</div>
		</div>
	</div>
</section>';


// doing it this way vs get_footer() gives footer access to all variables
include( TEMPLATEPATH . '/footer.php' );