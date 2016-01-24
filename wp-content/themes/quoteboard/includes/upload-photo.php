<?php
/*
 *	QUOTEBOARD
 *	Upload Photo
 *
 *	You guessed it...uploads a photo.
 *
 *	The variable $upload_field is equivalent to the name of the upload field.
 *	This way, the script can be called multiple times for different upload fields.
 */

if ( isset( $_FILES[$upload_field] ) && $_FILES[$upload_field]['size'] > 0 ) {
	// handle different types of uploads
	if ( $board_id ) {
		$post_id = $board_id;
	} elseif ( $user_id ) {
		$post_id = $user_id;
	}
	
	// get type of uploaded file
	$file_type_info 	= wp_check_filetype( basename( $_FILES[$upload_field]['name'] ) );
	$uploaded_file_type = $file_type_info['type'];
	$upload_errors		= '';

	// set array of allowed types
	$allowed_file_types = array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png' );

	// if uploaded file is an allowed format
	if ( in_array( $uploaded_file_type, $allowed_file_types ) ) {

		// docs: http://codex.wordpress.org/Function_Reference/wp_handle_upload
		$uploaded_file = wp_handle_upload( $_FILES[$upload_field], array( 'test_form' => false ) );

		// if wp_handle_upload() returned a local path for the image
		if ( isset( $uploaded_file['file'] ) ) {
			$file_name_and_location = $uploaded_file['file'];

			// options for adding this file as an attachment
			$attachment = array(
				'post_mime_type'	=> $uploaded_file_type,
				'post_title' 		=> $upload_title,
				'post_content' 		=> '',
				'post_status' 		=> 'inherit'
			);

			// add the file to the media library, generate thumbnails, and attach to post
			if ( $user_id ) {
				// don't attach a user profile photo
				$attach_id = wp_insert_attachment( $attachment, $file_name_and_location );
			} else {
				$attach_id = wp_insert_attachment( $attachment, $file_name_and_location, $post_id );
			}

			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// trash any previously uploaded profile image for this post, provided it isn't the default thumbnail
			if ( $user_id ) {
				$existing_profile_image = (int)get_user_meta( $post_id, $meta_field, true );
			} else {
				$existing_profile_image = (int)get_post_meta( $post_id, $meta_field, true );
			}

			if ( is_numeric( $existing_profile_image ) && $existing_profile_image != $default_id ) {
				if ( wp_delete_attachment( $existing_profile_image ) === false ) {
					$upload_errors .= 'There was a problem removing the previous photo';
				}
			}

			// associate the new image with the post (ignore for user photos)
			if ( !$user_id ) {
				if ( !update_post_meta( $post_id, $meta_field, $attach_id ) ) {
					$upload_errors .= 'There was a problem assigning your photo';
				}
			}

			// assign attachment IDs, so we can differentiate in JSON return
			if ( $upload_field == 'profile_photo' ) {
				$profile_photo_id = $attach_id;
			}

			if ( $upload_field == 'profile_bg_photo' ) {
				$bg_photo_id = $attach_id;
			}
		}
	}
}