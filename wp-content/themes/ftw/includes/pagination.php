<?php
/**
 *	QUOTEBOARD
 *	Pagination
 *
 *	Standard pagination, included from multiple templates.
 */

$big = 999999999; // need an unlikely integer

echo '
<div id="lazyloader"></div>
<div class="pagination">' .
	paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => $query->max_num_pages
	) ) .
'</div>';