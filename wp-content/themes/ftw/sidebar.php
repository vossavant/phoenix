<?php
/*
 *	QUOTEBOARD
 *	Page Sidebar
 */
?>

<aside class="clearfix">
	<?php if ( !function_exists( 'dynamic_sidebar' ) || !dynamic_sidebar( 'sidebar' ) ) : endif; ?>
</aside>