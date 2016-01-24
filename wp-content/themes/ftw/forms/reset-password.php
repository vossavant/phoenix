<?php
/*
 *	QUOTEBOARD
 *	Lost Password Form (this one is not being used; see forms/login.php)
 */
?>

<form action="<?php echo wp_lostpassword_url( get_bloginfo( 'url' ) ); ?>" class="hidden" id="reset-pw" method="post">
	<h2>Reset Password <span class="ico close" title="Close">Close</span></h2>
	<div>
		<p class="message shown info">Enter the email address you used to create your account, and we'll send you a link to reset your password.</p>
	</div>
	<div>
		<input autocomplete="off" name="user_login" placeholder="Email address" type="email" required />
		<span class="ico email"></span>
	</div>
	<div>
		<input name="redirect_to" type="hidden" />
		<button class="btn no-ajax" type="submit">Get Reset Link</button>
	</div>
</form>