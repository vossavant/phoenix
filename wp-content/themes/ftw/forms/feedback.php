<?php
/*
 *	QUOTEBOARD
 *	Beta Tester Feedback Form
 */
?>

<form action="" id="feedback-form" method="post">
	<p class="success message"><strong>Awesome sauce!</strong> Your feedback is on its way. Thank you!</p>
	<p class="error message"><strong>Crikey!</strong> We were unable to send your feedback. Please reload the page and try again, or email <a href="mailto:feedback@quoteboard.com">feedback@quoteboard.com</a>.</p>

	<!--<section class="ajax-wrapper">-->
		<div>
			<p class="message info">Quoteboard is currently in a private beta. We thank you for helping us test our app and welcome any of your bug reports, questions, or suggestions.<br /><br />If reporting a bug, please be as specific as possible, including steps you took to create the issue.</p>
		</div>

		<?php if ( is_user_logged_in() ) : ?>
			<input name="email" type="hidden" value="<?= $current_user->user_email; ?>" />
			<input name="username" type="hidden" value="<?= $current_user->user_nicename; ?>" />
		<?php else : ?>
			<div>
				<input autocomplete="off" maxlength="64" name="email" placeholder="Email" type="email" required />
				<span class="ico email"></span>
			</div>
		<?php endif; ?>

		<div>
			<textarea id="feedback" name="feedback" placeholder="Bug Report, Question, or Suggestion..." required></textarea>
		</div>
		<div class="register-location">
			<label for="register-location">Location</label>
			<input autocomplete="off" id="register-location" maxlength="32" name="location" placeholder="Location" type="text" />
		</div>
		<div>
			<input name="form_name" type="hidden" value="feedback" />
			<input name="current_page" type="hidden" value="<?= $_SERVER['REQUEST_URI']; ?>" />
			<input name="user_agent" type="hidden" value="<?= $_SERVER['HTTP_USER_AGENT']; ?>" />
			<button class="btn wide register" type="submit">Send Feedback</button>
		</div>
	<!--</section> -->
</form>