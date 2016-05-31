<?php
/*
 *	QUOTEBOARD
 *	Search Form
 */
?>

<form action="<?= esc_url( home_url( '/' ) ); ?>" class="search-form" id="site-search" method="get" role="search">
	<label class="hidden" for="s">Search Quoteboard</label>
	<input id="s" name="s" placeholder="Find something new" type="text" />
	<button class="no-ajax" type="submit">Search <i class="material-icons">search</i></button>
</form>