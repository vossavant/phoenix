<?php
/*
 *	QUOTEBOARD
 *	Add Quote (lightbox, not inline)
 */
?>

<form action="#" class="hidden" id="quote-create-new" method="post">
	<h2>Add New Quote <span class="ico close" title="Close">Close</span></h2>
	
	<div class="hidden success">
		<p class="success message shown">Well done! Your new quote was successfully added to <span></span>.</p>
		<div class="flex vertical-align" style="margin: 0;">
			<a style="flex-grow: 1;">View Quote &raquo;</a>
			<button class="btn wide another no-ajax" type="button">Add Another</button>
		</div>
	</div>

	<div class="hidden error">
		<p class="error message shown">Lame-O! There was a problem adding your quote. Please reload the page and try again, or <a class="fancybox feedback" href="#feedback-form">send us feedback</a>.</p>
		<button class="btn wide close no-ajax" type="button">Ok</button>
	</div>
	
	<section class="ajax-wrapper">
		<div>
			<textarea name="quote_text" placeholder="Enter your quote here..." required></textarea>
			<span class="box-meta charcount"><span>750</span> characters left</span>
		</div>
		<div>
			<input name="quote_author" placeholder="Who said it? (if anonymous, leave blank)" type="text">
			<span class="ico user"></span>
			<input name="quote_author_id" type="hidden" />
		</div>
		<div>
			<input name="quote_character" placeholder="Which character said it?" type="text">
			<span class="ico user"></span>
			<input name="quote_character_id" type="hidden">
		</div>
		<div>
			<input name="quote_source" placeholder="Where was it said?" type="text" />
			<span class="ico earth"></span>
			<input name="quote_source_id" type="hidden" />
		</div>
		<div class="no-bottom-margin">
			<input name="quote_source_info" placeholder="More info on source" type="text" />
			<span class="ico more"></span>
		</div>
			
		<div class="hidden extra-fields">
			<div class="choose-board">
				<?php get_template_part('includes/get-board-list'); ?>
				<span class="ico boards"></span>
			</div>
		</div>

		<div class="flex vertical-align">
			<a class="more-options" href="#"><span>Add more details</span> <span class="ico arrow-down"></span></a>
			<input name="form_name" type="hidden" value="add-quote" />
			<button class="btn" type="submit">Add Quote &raquo;</button>
		</div>
	</section> <!-- // ajax-wrapper -->
</form>