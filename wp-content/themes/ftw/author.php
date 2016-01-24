<?php
/*
 *	QUOTEBOARD
 *	Author Template
 *
 *	This template is called by visiting a URL like this:
 *
 *		http://www.quoteboard.com/[username]
 *
 *	It displays the various views for a particular user; e.g., quotes, boards,
 *	faves, and followers. Relies on the WP_Rewrite endpoints plugin.
 *
 *	Permalinks must be set to custom thusly: /%author%/%postname%/
 *
 *	TO DO: URL segments will likely change when we move live; double check them here.
 *	url segments in local dev go like this:
 *
 *		qb/author/burney/quotes
 *
 *	where segment 1 = qb, segment 4 = quotes
 */

// doing it this way vs get_header() gives access to all variables declared in header
if (!is_front_page() ) {
	include( TEMPLATEPATH . '/header.php' );
}

$base_author_url = get_bloginfo( 'home' ) . '/author/' . $user_url_nicename;

if ( $current_page_user_id == $current_user->ID ) {
	$home_feed_url = get_bloginfo( 'home' );
	$post_status = array('private', 'publish');
	$public_only = false;
} else {
	$home_feed_url = $base_author_url;
	$post_status = 'publish';
	$public_only = true;
}

$count_faves =
  "SELECT COUNT(*)
   FROM $wpdb->postmeta
   INNER JOIN $wpdb->posts ON post_id = ID
   WHERE meta_key LIKE 'quote_fave_%_quote_fave_user'
   AND meta_value = '$current_page_user_id'
   AND post_status <> 'trash'";

// stats for current user
$current_user_quotes 	= count_user_posts_by_type( $current_page_user_id, 'quote', $public_only );
$current_user_boards 	= count_user_posts_by_type( $current_page_user_id, 'board', $public_only );
$current_user_faves		= $wpdb->get_var( $count_faves );
$current_user_followers = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$current_page_user_id'" );
$current_user_following = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE follower_id = '$current_page_user_id'" );

// determine if current user is following the viewed user
if ( is_user_logged_in() ) {
	if ( $current_page_user_id != $current_user->ID ) {
		$is_following = $wpdb->get_var( "SELECT COUNT(*) FROM wp_qb_followers WHERE user_id = '$current_page_user_id' AND follower_id = '$current_user->ID'" );
	} else {
		$is_following = false;
	}
}

// description
if ( !$user_bio = get_user_meta( $current_page_user_id, 'description', true ) ) {
	$user_bio = '<em>Alas, this user has nothing interesting to say.</em>';
}

// determine what kind of author we're viewing (get current author role)
$author_info = get_user_by( 'id', $current_page_user_id );
$author_role = implode( ', ', $author_info->roles );


/**
 *	Member added authors have a different display that copies that of the archives page.
 *	This is because they are not normal members and do not have member-curated profiles.
 */
if ($author_role == 'member_added') :
	echo '
	<main>
		<h3>Quotes attributed to ' . $author_info->display_name . '</h3>';

		$quotes = get_posts(
			array(
				'meta_query' => array(
					array(
						'key' 	=> 'quote_author',
						'value'	=> $current_page_user_id
					)
				),
				'posts_per_page'	=> -1,
				'post_status'	=> 'publish',
				'post_type' 	=> 'quote'
			)
		);

		include( TEMPLATEPATH . '/loop-quotes.php' );

		echo '
	</main>';


/**
 *	Display for normal members: full profile, then quotes and various endpoints.
 */
else :
	// get avatar (relies on WP User Avatar plugin)
	if ( !$author_avatar = get_wp_user_avatar( $post->post_author, 200 ) ) {
		$author_avatar = DEFAULT_THUMBNAIL;
	}
	?>

	<!-- cover photo -->
	<div id="hero" style="background: url('<?= TIMTHUMB_PATH . $user_background ?>&h=550') center no-repeat;"></div>

	<!-- profile -->
	<div class="clearfix" id="profile">
		<section>
			<!-- <img src="<?= TIMTHUMB_PATH . get_wp_user_avatar_src( $current_page_user_id, 200 ); ?>&w=200&h=200" alt="<?= $author_info->display_name; ?>" /> -->
			<?= $author_avatar; ?>
			<div>
				<h3><?= $author_info->display_name; ?></h3>
				<p><?= $user_bio; ?></p>
			</div>

			<aside>
				<ul>
					<li>
						<span class="ico quote"></span> <?= $current_user_quotes; ?> Quote<?php if ( $current_user_quotes != 1 ) echo 's'; ?>
					</li>
					<li>
						<span class="ico boards"></span> <?= $current_user_boards; ?> Board<?php if ( $current_user_boards != 1 ) echo 's'; ?>
					</li>
					<?php/*
					<li>
						<span class="ico users"></span> <?= $current_user_followers; ?> Follower<?php if ( $current_user_followers != 1 ) echo 's'; ?>
					</li>
					<li>
						<span class="ico follow"></span> <?= $current_user_following; ?> Following
					</li>
					<li>
						<span class="ico fave"></span> <?= $current_user_faves; ?> Fave<?php if ( $current_user_faves != 1 ) echo 's'; ?>
					</li>
					*/
					?>
				</ul>

				<?php
				if ( is_user_logged_in() ) :
					if ( $current_user->ID == $current_page_user_id ) {
						if ($url_endpoint != 'profile') {
							echo '<a class="btn" href="' . $base_author_url . '/profile">Edit Profile</a>';
						}
					} else {
						if ( $is_following ) {
							echo '<a class="btn follow following" data-id="' . $current_page_user_id . '" href="">Following</a>';
						} else {
							echo '<a class="btn follow" data-id="' . $current_page_user_id . '" href="">Follow</a>';
						}
					}
				endif;
				?>
			</aside>

			<ul class="tabnav">
				<li><a<?php if ( $url_endpoint == '' ) { echo ' class="active"'; } ?> href="<?= $base_author_url; ?>">Stream</a></li>
				<li><a<?php if ( $url_endpoint == 'quotes' ) { echo ' class="active"'; } ?> href="<?= $base_author_url; ?>/quotes">Quotes</a></li>
				<li><a<?php if ( $url_endpoint == 'boards' ) { echo ' class="active"'; } ?> href="<?= $base_author_url; ?>/boards">Boards</a></li>
				<li class="context"><a href="#"><span class="ico more"></span> More</a>
					<ul>
						<li><a<?php if ( $url_endpoint == 'faves' ) { echo ' class="active"'; } ?> href="<?= $base_author_url; ?>/faves">Faves</a></li>
						<li><a<?php if ( $url_endpoint == 'followers' ) { echo ' class="active"'; } ?> href="<?= $base_author_url; ?>/followers">Followers</a></li>
						<li><a<?php if ( $url_endpoint == 'following' ) { echo ' class="active"'; } ?> href="<?= $base_author_url; ?>/following">Following</a></li>
						<?php
						if ( $current_user->ID == $current_page_user_id ) {
							echo '<li><a' . ( $url_endpoint == 'profile' ? ' class="active"' : '' ) . ' href="' . $base_author_url . '/profile">Edit Profile</a></li>';
						}
						?>
					</ul>
				</li>
			</ul>
		</section>
	</div> <!-- // profile -->

	<main>

	<?php
	// grab URL endpoint (that is, the final segment in the URL)
	//$url_endpoint = url_segment( 4 ); // TO DO : change to 4 on live site

	// load a separate template for each endpoint (e.g., author-quotes.php)
	if ( isset( $wp_query->query_vars[$url_endpoint] ) ) {
		include( TEMPLATEPATH . '/author-' . $url_endpoint . '.php' );

	// if URL contains no valid endpoint, load user's quote feed
	} else {
		include( TEMPLATEPATH . '/author-stream.php' );
	}

endif;
?>


<?php
echo '</main>';

// doing it this way vs get_footer() gives footer access to all variables
include( TEMPLATEPATH . '/footer.php' );