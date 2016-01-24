<?php
/*
 *	QUOTEBOARD
 *	Login Form
 */

// load up password reset form
include( locate_template( 'forms/reset-password.php' ) );
?>

<form action="" class="hidden" id="login-form" method="post">
	<h2>Sign In <span class="ico close" title="Close">Close</span></h2>
	<div class="sso-options">
		<span class="ico facebook" onclick="qb.utility.facebookLogin('login');">Sign in with Facebook</span>
		<span class="ico twitter">Coming Soon</span>
	</div>
	<div>
		<input autocomplete="off" name="username" placeholder="Your Email Address" type="email" required>
		<span class="ico email"></span>
	</div>
	<div>
		<input autocomplete="off" name="password" placeholder="Your Password" type="password" required>
		<span class="ico lock"></span>
	</div>

	<div id="signin-options">
		<label><input type="checkbox"> Keep me signed in</label>
		<a class="fancybox" href="#reset-pw">Reset my password</a>
	</div>
	<div>
		<input name="rememberme" type="hidden" value="forever" />
		<input name="form_name" type="hidden" value="login" />
		<button class="btn" type="submit">Sign In</button>
	</div>
	<p class="sso-loading"></p>
</form>