<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<link rel="shortcut icon" href="<?php bloginfo( 'template_url' ); ?>/favicon.ico">
	<link rel="apple-touch-startup-image" href="<?php bloginfo( 'template_url' ); ?>/images/mobile/ios-startup.png">
	<link rel="apple-touch-icon-precomposed" href="<?php bloginfo( 'template_url' ); ?>/images/mobile/apple-touch.png">

	<?php
	// meta description
	if ($meta_description = get_field( 'seo_description' ) ) {
		echo '<meta name="description" content="' . $meta_description . '">';
	}

	// get current user object (username, etc.)
	$current_user = wp_get_current_user();
	if ( !$current_user instanceof WP_User ) {
		return;
	}

	// fetch final URL segment
	$url_endpoint = array_pop(explode('/', rtrim($_SERVER['REQUEST_URI'], '/')));

	// get user nicename from URL
	if ( !$user_url_nicename = url_segment( 2 ) ) { // this needs to be "2" for the live site
		$user_url_nicename = $current_user->user_nicename;
	}

	// determine if user is viewing his own pages
	$is_viewing_own_page 	= true;
	$current_page_user_id 	= $current_user->ID;

	if ( $current_user->user_nicename != $user_url_nicename && is_author() ) {
		$is_viewing_own_page	= false;
		$current_page_user_id 	= get_user_by( 'slug', $user_url_nicename )->ID;
	}

	// get user avatar, and specify a fallback
	if ( !$avatar = get_wp_user_avatar_src( $current_page_user_id, 80 ) ) {
		$avatar = DEFAULT_THUMBNAIL;
	}

	if ( !$menu_avatar = get_wp_user_avatar_src( $current_user->ID, 48 ) ) {
		$menu_avatar = DEFAULT_THUMBNAIL;
	}

	// get user background
	if ( is_user_logged_in() ) {
		if ( is_page() || is_singular( 'post' ) || is_search() ) {
			$page_background = DEFAULT_PAGE_BACKGROUND;
		} else {
			if ( $background_id = get_user_meta( $current_page_user_id, 'user_background', true ) ) {
				global $wpdb;
				$upload_directory 	= wp_upload_dir();
				$background_src 	= $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '" . $background_id . "' AND meta_key = '_wp_attached_file'" );
				$page_background 	= $user_background = $upload_directory['baseurl'] . '/' . $background_src;
			} else {
				$page_background = $user_background = DEFAULT_BACKGROUND;
			}
		}
	} else {
		$page_background = DEFAULT_PAGE_BACKGROUND;
	}
	?>

	<title>
		<?php
		if ( $custom_page_title = get_field( 'seo_title' ) ) {
			echo $custom_page_title;
		} elseif ( is_front_page() ) {
			bloginfo( 'description' );

		} elseif ( is_404() ) {
			echo 'Page Not Found';

		} elseif ( is_author() ) {
			echo 'Quotes by ' . get_user_by( 'slug', $user_url_nicename )->display_name;

		} elseif ( is_singular( 'quote' ) ) {
			echo get_the_title();

		} elseif ( is_singular( 'board' ) ) {
			echo get_the_title();

		} elseif ( is_page() ) {
			echo get_the_title();

		} elseif ( is_search() ) {
			echo 'Search Results for &quot;' . get_search_query() . '&quot;';
			
		} else {
			wp_title( '' );
		}
		echo ' | ' . get_bloginfo( 'name' );
		?>
	</title>

	<?php wp_head(); ?>
</head>

<body <?php echo body_class(); ?>>
	<?php	
	// link to user landing page
	$user_url = home_url( '/' ) . 'author/' . $current_user->user_nicename;

	echo
	'<header>
		<a href="' . home_url( '/' ) . '" id="logo" title="Toggle side navigation">';
			include('includes/logo-icon.php'); echo 'Quoteboard
		</a>';

		// user menu
		if ( is_user_logged_in() ) :
			echo
			'<nav>
				<ul>
					<li>
						<a id="dropdown-avatar" href="' . $user_url . '" title="Your quote stream">
							<img alt="' . $current_user->display_name . '" src="' . $menu_avatar . '" />
							<span class="ico arrow-down"></span>
						</a>

						<ul>
							<li><a href="' . $user_url . '" id="dropdown-name" title="Your quote stream">' . $current_user->display_name . '<span>@' . $current_user->user_nicename . '</span></a></li>
							<li' . ( $url_endpoint == 'Quotes' && $is_viewing_own_page ? ' class="current-menu-item"' : '' ) . '><a href="' . $user_url . '/quotes">Quotes</a></li>
							<li' . ( $url_endpoint == 'Boards' && $is_viewing_own_page ? ' class="current-menu-item"' : '' ) . '><a href="' . $user_url . '/boards">Boards</a></li>
							<li class="group' . ( $url_endpoint == 'Faves' && $is_viewing_own_page ? ' current-menu-item' : '' ) . '"><a href="' . $user_url . '/faves">Favorites</a></li>
							<li><a href="' . wp_logout_url( home_url() ) . '">Sign Out</a></li>
						</ul>
					</li>
				</ul>
			</nav>';
			
			// only admin can see these options
			if ( current_user_can( 'activate_plugins' ) ) :
				echo
				'<ul>
					<li id="menu-item-718" class="menu-item-718"><a href="#">Add Quote</a></li>
					<li id="menu-item-719" class="menu-item-719"><a href="#">Add Board</a></li>
				</ul>';
			endif;

		else :
			echo
			'<div id="login-options">
				<a class="fancybox" href="#login-form">Sign In</a>';
				//<a class="fancybox" href="#register-form">Join</a>
				echo
			'</div>';
		endif;

		// search form
		get_search_form();

		// main menu
		echo '<ul>';
		
		$menu_params = array(
			'echo'				=> true,
			'container' 		=> false,
			'items_wrap' 		=> '%3$s',
			'theme_location' 	=> 'primary'
		);

		wp_nav_menu( $menu_params );
		
		echo '</ul>';
		?>
	</header>

	<?php
	/*
	<nav>
		<?php //echo $url_endpoint; ?>
		<ul>
			<li class="last"><span class="ico home"></span><a<?php if ( url_segment(2) == '' ) { echo ' class="active"'; } ?> href="<?= home_url(); ?>">Home</a></li>
			<li><span class="ico quote"></span><a<?php if ( url_segment(2) == 'quotes' ) { echo ' class="active"'; } ?> href="<?= home_url(); ?>/quotes">Newest Quotes</a></li>
			<li><span class="ico boards"></span><a<?php if ( url_segment(2) == 'boards' ) { echo ' class="active"'; } ?> href="<?= home_url(); ?>/boards">Newest Boards</a></li>
			<li class="last"><span class="ico users"></span><a<?php if ( url_segment(2) == 'members' ) { echo ' class="active"'; } ?> href="<?= home_url(); ?>/members">Newest Members</a></li>
			<li><span class="ico pencil"></span><a<?php if ( url_segment(2) == 'feedback' ) { echo ' class="active"'; } ?> href="<?= home_url(); ?>/feedback">Leave Feedback</a></li>
		</ul>

		<footer>
			<?php
			$menu_params = array(
				'echo'				=> true,
				'container' 		=> false,
				'items_wrap' 		=> '<ul>%3$s</ul>',
				'theme_location' 	=> 'secondary'
			);

			wp_nav_menu( $menu_params );

			if ( $twitter_url = get_field( 'twitter_url', 'options' ) ) {
				echo '<a class="social ico twitter" href="' . $twitter_url . '">Quoteboard on Facebook</a>';
			}

			if ( $facebook_url = get_field( 'facebook_url', 'options' ) ) {
				echo '<a class="social ico facebook" href="' . $facebook_url . '">Quoteboard on Twitter</a>';
			}

			if ( $pinterest_url = get_field( 'pinterest_url', 'options' ) ) {
				echo '<a class="social ico pinterest" href="' . $pinterest_url . '">Quoteboard on Pinterest</a>';
			}

			if ( $instagram_url = get_field( 'instagram_url', 'options' ) ) {
				echo '<a class="social ico instagram" href="' . $instagram_url . '">Quoteboard on Instagram</a>';
			}
			?>
			
			<p>&copy; <?= date( 'Y' ); ?> Quoteboard</p>
		</footer>
	</nav>
	*/