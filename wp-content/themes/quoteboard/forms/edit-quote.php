<?php
/*
 *	QUOTEBOARD
 *	Edit Quote
 */
?>

<form action="" class="hidden clearfix" id="quote-edit" method="post">
	<h2>Edit Quote <i class="material-icons">close</i></h2>
	<div>
		<textarea id="edit-quote-text" name="quote_text" placeholder="Type your quote here..." required></textarea>
		<span class="box-meta charcount"><span>750</span> characters left</span>
	</div>
	<div>
		<input id="edit-quote-author" name="quote_author" placeholder="Who said it? (if anonymous, leave blank)" type="text" />
		<i class="material-icons">mode_edit</i>
		<input name="quote_author_id" type="hidden" />
	</div>
	<div>
		<input name="quote_source" placeholder="Where was it said?" type="text" />
		<i class="material-icons">explore</i>
		<input name="quote_source_id" type="hidden" />
	</div>
	<div class="choose-board">
		<?php get_template_part('includes/get-board-list'); ?>
		<i class="material-icons">library_books</i>
	</div>

	<div class="hidden extra-fields">
		<div>
			<input name="quote_character" placeholder="Which character said it?" type="text">
			<i class="material-icons">person</i>
			<input name="quote_character_id" type="hidden">
		</div>
		<div>
			<input name="quote_source_info" placeholder="More info on source" type="text" />
			<i class="material-icons">more</i>
		</div>
	</div>

	<div class="flex vertical-align">
		<a class="more-options" href="#"><span>Add more details</span> <i class="material-icons">keyboard_arrow_down</i></a>
		<input name="qid" type="hidden" value="">
		<input name="form_name" type="hidden" value="edit-quote">
		<button class="btn wide" type="submit">Update Quote</button>
	</div>
</form>