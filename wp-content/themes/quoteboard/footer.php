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

	// don't load analytics on localhost (custom field isn't working right now)
	if ( !stristr( $_SERVER['REQUEST_URI'], 'localhost' ) ) {
		if ( $analytics = get_field( 'ga_tracking_code', 'option' ) ) {
			echo $analytics;
		} else {
			echo
			"<script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

				ga('create', 'UA-73002005-1', 'auto');
				ga('send', 'pageview');
			</script>";
		}
	}

	wp_footer();
	?>
</body>
</html>