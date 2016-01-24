<?php
/*
 *	QUOTEBOARD
 *	Edit Quote
 */
?>

<form action="" class="hidden clearfix" id="quote-edit" method="post">
	<h2>Edit Quote <span class="ico close" title="Close">Close</span></h2>
	<div>
		<textarea id="edit-quote-text" name="quote_text" placeholder="Type your quote here..." required></textarea>
	</div>
	<div class="form-options">
		<a class="more-options" href="#"><span>More Options</span> <span class="ico arrow-down"></span></a>
		<span class="box-meta charcount"><span>750</span> characters left</span>
	</div>

	<div class="hidden extra-fields">
		<div>
			<input id="edit-quote-author" name="quote_author" placeholder="Who said it? (if anonymous, leave blank)" type="text" />
			<span class="ico user"></span>
			<input name="quote_author_id" type="hidden" />
		</div>
		<div>
			<input name="quote_source" placeholder="Where was it said?" type="text" />
			<span class="ico earth"></span>
			<input name="quote_source_id" type="hidden" />
		</div>
		<div class="choose-board">
			<?php get_template_part('includes/get-board-list'); ?>
			<span class="ico boards"></span>
		</div>
	</div>
	<div>
		<input name="qid" type="hidden" value="">
		<input name="form_name" type="hidden" value="edit-quote">
		<button class="btn wide" type="submit">Update Quote</button>
	</div>
</form>