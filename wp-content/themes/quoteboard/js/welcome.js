/*
 *	QUOTEBOARD
 *	Welcome Message
 *
 *	This file loads up the step-by-step tutorial that appears when a user
 *	first signs into the site.
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

	// add data attributes to certain page elements; used for the introductory tour
	// step 1
	$('.box.small').attr('data-step', '1').attr('data-intro', 'Use this form to quickly add a quote. This form appears on your pages and on any boards you create or collaborate on.').attr('data-position', 'top');

	// step 2
	$('#menu-item-624').attr('data-step', '2').attr('data-intro', 'You can also add a quote from any page by clicking here.').attr('data-position', 'left');

	// step 3
	$('#dropdown-avatar').attr('data-step', '3').attr('data-intro', 'Access your quotes, boards, and more from any page by hovering over your profile photo.').attr('data-position', 'left');

	// step 4
	$('#profile').find('.tabnav').attr('data-step', '4').attr('data-intro', 'You can also use these tabs to navigate. Hover over the &ldquo;...&rdquo; to see followers, favorites, and more.').attr('data-position', 'top');

	// step 5
	$('#logo').attr('data-step', '5').attr('data-intro', 'Click the &ldquo;Q&rdquo; logo to reveal links to popular pages, a great way to explore what\'s new on Quoteboard.').attr('data-position', 'right');

	// launch tour on button click (set timeout smooths transition between overlays after fancybox closes)
	$('#welcome-msg').find('button').click(function () {
		$.fancybox.close();
		setTimeout(function() {
			introJs().start();
		}, 350 );
	});
})(jQuery)