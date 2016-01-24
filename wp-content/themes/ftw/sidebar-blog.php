<?php
/*
 *	QUOTEBOARD
 *	Blog Sidebar
 */
?>

<aside class="clearfix">
	<?php if ( !function_exists( 'dynamic_sidebar' ) || !dynamic_sidebar( 'sidebar-blog' ) ) : endif; ?>
</aside>