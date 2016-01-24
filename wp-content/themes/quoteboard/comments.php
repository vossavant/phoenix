<?php
/*
 *	QUOTEBOARD
 *	Comment Form - loaded in single-quote.php
 */

// get avatar (relies on WP User Avatar plugin)
if ( !$current_user_avatar = get_wp_user_avatar( get_current_user_id(), 48 ) ) {
	$current_user_avatar = DEFAULT_THUMBNAIL;
}

if ( have_comments() ) :
?>

<section class="comment box">
	<div class="bubble">
		<h4>Comments</h4>
		<span class="box-meta">
			<?php comments_number( 'No comments yet', '1 comment so far', '% comments so far' ); ?>
		</span>

		<?php
		if ( have_comments() ) {
			echo '<ul id="comment-list">';
				wp_list_comments( array( 'callback' => 'qb_comments' ) );
			echo '</ul>';
		}
		?>
	</div> <!-- // bubble -->
</section> <!-- // comments -->

<?php endif; ?>

<div class="box small">
	<?php if ( is_user_logged_in() ) : ?>
		<a class="avatar" href="<?= home_url('/') . $home_url; ?>" title="Posted by <?= $contributor; ?>">
			<?= $current_user_avatar; ?>
		</a>
		<div>
			<div class="bubble">
				<form action="#" id="comment-form" method="post">
					<p class="message success">Thanks for your comment!</p>
					<p class="message error">There was a problem posting your comment.</p>
					<textarea class="expandable" id="quote-comment" name="comment" placeholder="Add a comment about this quote..."></textarea>
					<input name="comment_post_ID" type="hidden" value="<?php the_ID(); ?>" />
					<input name="form_name" type="hidden" value="comment" />
					<button class="btn" type="submit">Leave Comment</button>
				</form>
			</div> <!-- // bubble -->
		</div>
	<?php else : ?>
		<a class="avatar" href="<?= home_url('/') . $home_url; ?>">
			<img src="<?= TIMTHUMB_PATH . DEFAULT_THUMBNAIL; ?>&w=48&h=48">
		</a>
		<div>
			<div class="bubble">
				<p>To post comments, please <a class="fancybox" href="#login-form">sign in to your Quoteboard account</a>.</p>
			</div> <!-- // bubble -->
		</div>
	<?php endif; ?>
</div>