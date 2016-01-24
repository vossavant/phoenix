<?php
/*
 *	QUOTEBOARD
 *	Add Board Lightbox Form
 */

//$board_categories = get_terms( 'board_cats', array( 'hide_empty' => 0 ) );
?>

<form action="" class="hidden" id="board-create-new" method="post">
	<h2>Add New Board <span class="ico close" title="Close">Close</span></h2>
	<div>
		<input id="board-name" name="board_name" placeholder="Name your board..." type="text" required />
		<span class="ico pencil"></span>
	</div>
	<?php /* deferring for later
	<div>
		<select data-placeholder="Select a category..." name="board_category">
			<option value=""></option>
			<?php
			foreach ( $board_categories as $category ) {
				echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
			}
			?>
		</select>
	</div>
	*/ ?>
	<div>
		<textarea id="edit-board-description" name="board_description" placeholder="Briefly describe your board..."></textarea>
		<?php //<span class="box-meta charcount"><span>500</span> characters left</span> ?>
	</div>
	<div>
		<input name="form_name" type="hidden" value="add-board-inline" />
		<button class="btn wide" type="submit">Add Board</button>
	</div>
</form>