<?php
/*
 *	QUOTEBOARD
 *	Page Template
 */

// doing it this way vs get_header() gives access to all variables declared in header
include( TEMPLATEPATH . '/header.php' );

// prevent jankiness with pagination
$paged = ( get_query_var('paged') ? get_query_var('paged') : 1 );

echo '<section class="main">';

switch( url_segment( 1 ) ) :
	/**
	 *	Main Authors Page - shows newest site members
	 */
	case 'authors' :
		echo '<h3 style="margin-top: 0;">Author Archive <span class="pagenum">Page ' . $paged . '</span></h3>';
		$args = array (
			'number'	=> RESULTS_PER_PAGE,
			'order' 	=> 'DESC',
			'orderby' 	=> 'registered',
			'paged'		=> $paged,
			'role' 		=> 'member_added'
		);

		$wp_user_query = new WP_User_Query($args);
		$users = $wp_user_query->get_results();
		$user_count = $wp_user_query->get_total();

		foreach ( $users as $user ) {
			include( TEMPLATEPATH . '/individual-author.php' );
		}


		$total_pages = ceil( $user_count / RESULTS_PER_PAGE );

		echo
		'<div class="pagination">' .
		paginate_links(
			array(
				'base' 		=> get_pagenum_link(1) . '%_%',
				// 'format' 	=> '?paged=%#%',
				'current' 	=> $paged,
				'show_all'	=> false,
				'total' 	=> $total_pages,
				'type'     	=> 'plain'
			)
		) .
		'</div>';
	break;


	/**
	 *	Leave Feedback Page
	 */
	case 'feedback' :
		echo '<article id="page-wrapper">';
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo '<h1>' . get_the_title() . '</h1>';
			the_content();

			include( TEMPLATEPATH . '/forms/feedback.php' );
		endwhile; endif;
		echo '</article>';
	break;

	/**
	 *	Board Invite
	 *
	 *	Specialized case for the "Invite" page, which serves as a
	 *	landing page for users who have been sent board invitation codes
	 *	via email. Each invitation code is tied to a particular email
	 *	and board ID. If the code and email match, the user is added to
	 *	the board and redirected there; otherwise, the user sees a notice
	 *	indicating that the code is invalid or expired.
	 */
	case 'invite' :
		echo '<article id="page-wrapper">';
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo '<h1>' . get_the_title() . '</h1>';
			the_content();

			// validate code in URL
			include( TEMPLATEPATH . '/includes/invite-check-code.php' );
		endwhile; endif;
		echo '</article>';
	break;


	/**
	 *	Default Page Layout
	 */
	default :
		echo '<article id="page-wrapper">';
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo '<h1>' . get_the_title() . '</h1>';
			the_content();
		endwhile; endif;
		echo '</article>';
	break;
endswitch;

echo '</section> <!-- // main -->';

//get_sidebar();

// doing it this way vs get_footer() gives footer access to all variables
include( TEMPLATEPATH . '/footer.php' );