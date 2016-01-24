<?php
/*
 *	QUOTEBOARD
 *	Invitation Signup Form
 *
 *	Called from includes/invite-check-code.php, accessed when a user receives
 *	an invitation code and is not an existing member, they arrive at a screen
 *	with this form, which prompts them to complete their registration.
 */
?>

<section id="hero">
	<h1 id="logo">Quoteboard</h1>
	<h2>Thanks for accepting our invitation</h2>
	<p>We're glad you're interested in following <em><?= $board_name; ?></em>. Since you don't yet have an account with us, we kindly ask that you create one (it's free).</p>
	<p>Your account will be tied to the email <?= $invite_email; ?>, but you can change this later.</p>
	<form action="" id="register-form" method="post">
		<!--
		<p class="sso-options">
			<a class="social fb" href="#">Sign in with Facebook</a>
			<a class="social tw" href="#">Sign in with Twitter</a>
		</p>

		<p class="form-alt-options">No Facebook or Twitter account?<br />Finish creating your account below:</p>
	-->

		<div>
			<label for="register-password">Password</label>
			<input autocomplete="off" id="register-password" maxlength="32" name="password" placeholder="Password" type="password" required />
			<span class="toggle-pw" title="Reveal password">Show</span>
		</div>
		<div>
			<label for="register-username">Username</label>
			<input autocomplete="off" id="register-username" maxlength="32" name="username" placeholder="Username" type="text" required />
			<i class="error icon-remove-sign"></i>
		</div>
		<div class="register-location">
			<label for="register-location">Location</label>
			<input autocomplete="off" id="register-location" maxlength="32" name="location" placeholder="Location" type="text" />
		</div>
		<div>
			<input name="redirect_to" type="hidden" />
			<input name="email" type="hidden" value="<?= $invite_email; ?>" />
			<input name="invite_code" type="hidden" value="<?= $invite_code; ?>" />
			<input name="form_name" type="hidden" value="register" />
			<button class="btn wide" type="submit">Join <em>for</em> Free</button>
			<small>By submitting this form, you acknowledge that you have read and agree to our <a href="#">Terms of Use</a>.</small>
		</div>
	</form>
</section> <!-- // hero -->