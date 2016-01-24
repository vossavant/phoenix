<?php
/*
 *	QUOTEBOARD
 *	404 Template
 */
get_header();

echo '
<main>
	<h1>Page Not Found (Error 404)</h1>
	<p>Sorry, but we can\'t find that page. It\'s about as real as a prancing unicorn.</p>
	<img alt="Prancing unicorn" src="' . get_bloginfo('template_url') . '/images/unicorn.jpg">
</main>';

get_footer();