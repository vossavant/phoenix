/*
 *	QUOTEBOARD
 *	Board Welcome Message
 *
 *	This file loads up the step-by-step tutorial that appears when a user
 *	adds their first board and is redirected to the new board page.
 */
(function ($) {
	// oddly, without window.load, Fancybox overlay doesn't appear
	$(window).load(function () {
		$.fancybox({
			content		: $('#welcome-msg'),
			closeBtn 	: qb.fancybox.closeBtn,
			margin		: qb.fancybox.margin,
			maxWidth 	: qb.fancybox.maxWidth,
			minWidth	: qb.fancybox.minWidth,
			padding 	: qb.fancybox.padding
		});
	});

	// add data attributes to certain page elements; used for the introductory board tour
	// step 1
	$('.box.small').attr('data-step', '1').attr('data-intro', '<h4>Quickly Add Quotes</h4>When you view a board you created or a board on which you are allowed to post quotes <strong>(collaborate)</strong>, you\'ll see this form.<br><br>Quotes added here will automatically be posted to the board you are viewing.').attr('data-position', 'top');

	// step 2
	$('aside ul').attr('data-step', '2').attr('data-intro', '<h4>Followers are Automatically Added</h4>When you create a board, <strong>your followers are automatically added as members</strong> (they can unfollow your board at any time).').attr('data-position', 'left');

	// step 3
	$('.btn.invite').attr('data-step', '3').attr('data-intro', '<h4>Invite and Collaborate</h4>You can invite anyone you are following (or any of your followers) to your board as a <strong>collaborator</strong>.<br><br>Regular board followers can only look at the quotes on your board, while collaborators can add quotes, too.<br><br>You can also promote any of your board followers to collaborators.').attr('data-position', 'left');

	// step 4
	$('.tabnav').find('li:last-child').attr('data-step', '4').attr('data-intro', '<h4>Personalize</h4>Click here to edit your board\'s name, description, and profile &amp; cover photos.').attr('data-position', 'left');

	// launch tour on button click (set timeout smooths transition between overlays after fancybox closes)
	$('#welcome-msg').find('button').click(function () {
		$.fancybox.close();
		setTimeout(function() {
			introJs().start();
		}, 350 );
	});
})(jQuery)