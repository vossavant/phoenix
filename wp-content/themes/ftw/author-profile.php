<?php
/*
 *	QUOTEBOARD
 *	Author Profile
 *
 *	Displays the edit profile form for the current user.
 *	Called from author.php.
 */

echo '
<form action="" id="user-profile-edit" method="post">
	<div>
		<label for="profile-display-name" data-alt="Pen Name">Pen Name</label>
		<input id="profile-display-name" name="display_name" placeholder="Pen Name" type="text" value="' . $current_user->display_name . '" required>
	</div>
	<div>
		<label for="profile-email" data-alt="Email">Email</label>
		<input id="profile-email" name="email" placeholder="Email Address" type="text" value="' . $current_user->user_email . '" required>
	</div>
	<div>
		<label class="left" for="profile-bio">Biography</label>
		<span class="box-meta charcount"><span>500</span> characters left</span>
		<textarea id="profile-bio" name="biography" placeholder="Tell us about yourself...">' . $current_user->description . '</textarea>
	</div>
	<div class="photo clearfix">
		<label for="profile-photo">Profile Photo</label>
		<img alt="Profile Photo" src="' . TIMTHUMB_PATH . $avatar . '&w=80&h=80" />
		<input id="profile-photo" name="profile_photo" type="file" />
		<a class="delete remove-photo pp' . ( $avatar == DEFAULT_THUMBNAIL ? ' default' : '' ) . '" href="" title="Delete Photo">Delete Photo</a>
		<span class="loading"></span>
	</div>
	<div class="photo clearfix">
		<label for="profile-bg-photo">Cover Image</label>
		<img alt="Background Image" src="' . TIMTHUMB_PATH . $user_background . '&w=80&h=80" />
		<input id="profile-bg-photo" name="profile_bg_photo" type="file" />
		<a class="delete remove-photo bg' . ( $user_background == DEFAULT_BACKGROUND ? ' default' : '' ) . '" href="" title="Delete Photo">Delete Photo</a>
		<span class="loading"></span>
	</div>
	<div>
		<input name="uid" type="hidden" value="' . $current_user->ID . '" />
		<input name="isa" type="hidden" value="' . $is_viewing_own_page . '" />
		<input name="form_name" type="hidden" value="edit-user" />
		<button class="btn wide" type="submit">Update Profile</button>
	</div>
</form>';