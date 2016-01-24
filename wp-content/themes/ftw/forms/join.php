<?php
/*
 *	QUOTEBOARD
 *	Signup Form
 */
?>

<form action="" class="hidden" id="register-form" method="post">
	<h2>Join <span class="ico close" title="Close">Close</span></h2>
	<div class="sso-options">
		<span class="ico facebook" onclick="qb.utility.facebookLogin('register');">Join with Facebook</span>
		<span class="ico twitter">Coming Soon</span>
	</div>
	<div>
		<input autocomplete="off" id="register-email" maxlength="64" name="email" placeholder="Your Email Address" type="email" required>
		<span class="ico email"></span>
		<p class="error-inline">Rats! This email is unavailable!</p>
	</div>
	<div>
		<input autocomplete="off" id="register-password" maxlength="32" name="password" placeholder="Your Password" type="password" required>
		<span class="ico lock"></span>
		<p class="error-inline">Eek! Password must be at least <?= MINIMUM_PASSWORD_LENGTH ?> characters</p>
	</div>
	<div>
		<input autocomplete="off" id="register-username" maxlength="32" name="username" placeholder="Username" type="text" required>
		<span class="ico user"></span>
		<p class="error-inline">Oops! Username must be at least <?= MINIMUM_USERNAME_LENGTH ?> characters</p>
	</div>
	<div class="register-location">
		<label for="register-location">Location</label>
		<input autocomplete="off" id="register-location" maxlength="32" name="location" placeholder="Location" type="text">
	</div>
	<div>
		<input name="redirect_to" type="hidden" />
		<input name="form_name" type="hidden" value="register" />
		<span class="box-meta">By submitting this form, you acknowledge that you have read and agree to our <a href="<?= get_permalink(TERMS_PAGE_ID); ?>">Terms of Use</a>.</span>
		<button class="btn register" type="submit">Join Now</button>
	</div>
	<p class="sso-loading"></p>
</form>