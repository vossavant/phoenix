<?php
/*
 *	QUOTEBOARD
 *	Requote Form (loaded via AJAX)
 */

if ( $_POST['form_name'] != 'modal-requote' ) {
	echo json_encode( array( 'errors' => 'Sorry, this form submission is not allowed' ) );
	exit;
}

$html = '
<form action="" class="quote-details" id="requote-form" method="post">
	<h2>Requote <span class="ico close" title="Close">Close</span></h2>
	<div>
		<p class="hidden message success"><strong>Requote successful!</strong></p>
	</div>
	<div id="requote-this"></div>
	<div class="choose-board"></div>
	<div>
		<input name="form_name" type="hidden" value="requote" />
		<input name="reqid" type="hidden" />
		<button class="btn wide" type="submit">Requote It</button>
	</div>
</form>';

echo json_encode( array( 'html' => $html ) );
exit;