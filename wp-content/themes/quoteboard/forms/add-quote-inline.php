<?php
/*
 *	QUOTEBOARD
 *	Add Quote (inline, no lightbox)
 */
?>

<div class="box small">
	<a class="avatar" href="<?= home_url('/') . $home_url; ?>">
		<img alt="<?= $current_user->display_name; ?>'s Avatar" src="<?= TIMTHUMB_PATH . $avatar; ?>&w=48&h=48">
	</a>
	<div>
		<div class="bubble">
			<div>
				<p class="message success">Your quote was posted. Reload the page to see it.</p>
				<p class="message error">There was a problem adding your quote.</p>
			</div>

			<form action="#" id="quote-create-new-inline" method="post">
				<div>
					<textarea class="expandable" name="quote_text" placeholder="<?= $inline_form_placeholder; ?>" required></textarea>
					<span class="box-meta charcount"><span>750</span> characters left</span>
				</div>

				<div class="hidden author">
					<input name="quote_author" placeholder="Who said it? (if anonymous, leave blank)" type="text">
					<span class="ico user"></span>
					<input name="quote_author_id" type="hidden">
				</div>

				<div class="hidden extra-fields">
					<div>
						<input name="quote_source" placeholder="Where was it said?" type="text">
						<span class="ico earth"></span>
						<input name="quote_source_id" type="hidden" />
					</div>
					<?php if (is_singular('board')) {
						echo '<input name="quote_board" type="hidden" value="' . $board_id . '">';
					} else {
						echo '<div class="choose-board">';
						get_template_part('includes/get-board-list');
						echo '<span class="ico boards"></span></div>';
					}
					?>
				</div> <!-- // extra-fields -->

				<div class="hidden submit">
					<div class="flex vertical-align">
						<a class="more-options" href="#"><span>Add more details</span> <span class="ico arrow-down"></span></a>
						<input name="form_name" type="hidden" value="add-quote">
						<button class="btn" type="submit">Add Quote &raquo;</button>
					</div>
				</div>
			</form>
		</div> <!-- // bubble -->
	</div>
</div> <!-- // box small -->