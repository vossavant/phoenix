	<footer>
		<q>&ldquo;The way you do anything is the way you do everything.&rdquo;</q>
		<ul>
			<?php
			$menu_params = array(
				'echo'				=> true,
				'container' 		=> false,
				'items_wrap' 		=> '%3$s',
				'theme_location' 	=> 'secondary'
			);

			wp_nav_menu( $menu_params );
			?>
		</ul>
<!-- 		<ul>
			<li><a href="#">About</a></li>
			<li><a href="#">Feedback</a></li>
			<li><a href="#">Privacy</a></li>
			<li><a href="#">Terms</a></li>
		</ul> -->
		<p>QUOTEBOARD v1.0.2.1 &copy; <?= date('Y'); ?></p>
	</footer>

	<?php
	if ( is_user_logged_in() ) {
		include( locate_template( 'forms/add-board.php' ) );
		include( locate_template( 'forms/add-quote.php' ) );
		
		if (is_singular('quote')) {
			include( locate_template( 'forms/edit-quote.php' ) );
		}
	} else {
		qb_init_facebook_sdk();
		include( locate_template( 'forms/login.php' ) );
		if ( !$invite_email ) {
			include( locate_template( 'forms/join.php' ) );
		}
	}

	//include( locate_template( 'forms/feedback.php' ) );

	wp_footer();
	?>
</body>
</html>