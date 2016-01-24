<?php
/*
 *	QUOTEBOARD
 *	Board Profile
 *
 *	Displays an edit board profile for the current board.
 *	Called from single-board.php.
 */

// determine if this is a user's default board
$is_default_board = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta JOIN $wpdb->posts ON post_id = ID WHERE post_id = '$board_id' AND meta_key = 'is_default' AND meta_value = 'yes' AND post_author = '$current_user->ID'" );

echo '
<main class="wide">
	<form action="" enctype="multipart/form-data" id="board-profile-edit" method="post">' .
		( $is_default_board ? '<div><p class="message info shown"><strong>This is your default board.</strong> New quotes you add are posted to this board automatically, unless you specify a different board.</p></div>' : '' ) . '
		<div>
			<label for="edit-board-name" data-alt="Board Name">Board Name</label>
			<input id="edit-board-name" name="board_name" placeholder="Board Name" type="text" value="' . get_the_title() . '" required />
		</div>
		<div>
			<label class="left" for="edit-board-description">Board Description</label>
			<span class="box-meta charcount"><span>500</span> characters left</span>
			<textarea id="edit-board-description" name="board_description" placeholder="Briefly describe your board...">' . get_the_content() . '</textarea>
		</div>';

		/* deferring categories to a later phase
		<div>
			<select name="board_category">';
				
				$board_categories = get_terms( 'board_cats', array( 'hide_empty' => 0 ) );

				// if board not yet categorized
				if ( !$current_board_category_id ) {
					echo '<option selected value="">Choose a category...</option>';
				}

				// loop through all cats, selecting the current one
				foreach ( $board_categories as $category ) {
					if ( $category->term_id == $current_board_category_id ) {
						$selected = ' selected';
					} else {
						$selected = '';
					}

					echo '<option' . $selected . ' value="' . $category->term_id . '">' . $category->name . '</option>';
				}
				
				echo '
			</select>
		</div>
		*/
		echo '
		<div class="photo clearfix">
			<label for="edit-board-photo">Board Photo</label>
			<img alt="Board Photo" src="' . TIMTHUMB_PATH . $board_thumbnail_src . '&w=80&h=80" />
			<input id="edit-board-photo" name="profile_photo" type="file" />
			<a class="delete remove-photo pp' . ( get_post_thumbnail_id( $board_id ) == DEFAULT_THUMBNAIL_ID ? ' default' : '' ) . '" href="" title="Delete Photo">Delete Photo</a>
			<span class="loading"></span>
		</div>
		<div class="photo clearfix">
			<label for="edit-board-cover">Cover Image </label>
			<img alt="Background Image" src="' . TIMTHUMB_PATH . $page_background . '&w=80&h=80" />
			<input id="edit-board-cover" name="profile_bg_photo" type="file" />
			<a class="delete remove-photo bg' . ( $page_background == DEFAULT_BACKGROUND ? ' default' : '' ) . '" href="" title="Delete Photo">Delete Photo</a>
			<span class="loading"></span>
		</div>
		<div>
			<input name="bid" type="hidden" value="' . $board_id . '" />
			<input name="form_name" type="hidden" value="edit-board" />
			<button class="btn wide" type="submit">Update Profile</button>' .
			( $is_default_board ? '' : '<p class="form-alt-options"><a class="delete erase-board" data-id="' . $board_id . '" href="" href="">Erase this Board</a></p>' ) . '
		</div>
	</form>
</main>';