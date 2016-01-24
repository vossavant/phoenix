<?php
/*
 *	QUOTEBOARD
 *	Single Quote
 *
 *	Called from loop-quotes.php and search.php.
 *	Displays a single quote in list view.
 */

// fetch qoute author
$author = get_field( 'quote_author' ) ? get_field( 'quote_author' ) : 'Anonymous';

// fetch quote contributor; set to "You" for quotes added by current user
$contributor = get_the_author_meta( 'display_name' );
if ( $contributor == $current_user->display_name ) {
	$contributor = 'You';
	$quote_class = ' yours';
	$home_url = '';
} else {
	$quote_class = '';
	$home_url = 'author/' . get_the_author_meta( 'user_nicename' );
}

// fetch quote board
$board_id = get_post_meta( $post->ID, 'quote_board', true );

// get avatar (relies on WP User Avatar plugin)
if ( !$author_avatar = get_wp_user_avatar( $author['ID'], 60 ) ) {
	$author_avatar = get_default_thumbnail( 'thumb-small' );
}
?>

<article class="quote box<?= $quote_class; ?>" data-author="<?= $author['ID']; ?>" data-board="<?= $board_id; ?>" data-id="<?= $post->ID; ?>">
	<a class="avatar" href="<?= home_url('/') . 'author/' . $author['user_nicename']; ?>" title="Attributed to <?= $author['display_name']; ?>">
		<?= $author_avatar; ?>
	</a>
	<div>
		<div class="bubble" data-link="<?php the_permalink(); ?>">
			<?php
			if ( is_singular( 'quote' ) ) {
				// parse all hashtags into URLs
				echo '<q cite="q_' . $post->ID . '">' . preg_replace( '/#(\w+)/', ' <i>#</i><a href="' . home_url('/') . 'tag/$1">$1</a>', wpautop( get_the_content() ) ) . '</q>';
				echo '<cite id="q_' . $post->ID . '"><a href="' . home_url('/') . 'author/' . $author['user_nicename'] . '" title="See quotes attributed to ' . $author['display_name'] . '">' . $author['display_name'] . '</a></cite>';

				if ( $quote_source = get_field( 'quote_source' ) ) {
					echo '<span class="box-meta quote-source"><a href="' . home_url('/') . 'source/' . $quote_source->post_name . '" title="See quotes from ' . $quote_source->post_title . '">' . $quote_source->post_title . '</a></span>';
				}
			} else {
				echo '<h4>';

					if ( $post->post_status == 'private' ) {
						echo '<span class="status private" title="Private Quote"></span>';
					}

					if (is_singular('quote')) {
						// echo $contributor . '<span>shared this</span>';
					} else {
						echo $author['display_name'];// . '<span>@' . get_the_author_meta('user_nicename') . '</span>';
					}

				echo '</h4>';
				// echo '<time class="timeago" datetime="' . get_the_time( 'c' ) . '">' . get_the_time( 'F j, Y' ) . '</time>';
				// strip hashtags on all other views
				echo '<q cite="q_' . $post->ID . '">' . str_replace( '#', '', wpautop( get_the_content() ) ) . '</q>';
			}

			if ( ( $current_user->ID == SUPERADMIN_USER_ID || ( get_the_author_meta( 'ID' ) == get_current_user_id() ) ) && is_singular( 'quote' ) ) :
				echo '<footer>';
					echo '<a class="fancybox edit-quote" href="#quote-edit">Edit</a>';
					echo '<a class="delete delete-quote" href="">Delete</a>';

					if ( $post->post_status == 'private' ) {
						echo '<span class="private" title="This is a private quote">Private</span>';
					}
				echo '</footer>';
			endif;
			?>
		</div>

		<menu>
			<?php
			if ( is_user_logged_in() ) :
				$faves = qb_count_faves( $post->ID );

				// get requote count {
				//$requotes = count ( get_post_meta ( $post->ID, 'quote_board', true ) ) - 1;
				//if ( empty ( $requotes ) ) { $requotes = 0; }

				echo '<span class="ico fave ' . ( $faves['has_faved'] ? 'faved' : '' ) . '" title="' . ( $faves['has_faved'] ? 'Unfave' : 'Fave' ) . '">' . ( $faves['fave_count'] > 0 ? '<span>' . $faves['fave_count'] . '</span>' : '' ) . '</span>';
				#echo '<span class="ajax-modal ico requote" data-title="Requote" data-type="modal-requote"> Requote</span>';

			else :
				#echo '<a class="ico user" href="' . home_url('/') . $home_url . '" style="background: url(' . $contributor . ')" data-title="Posted by ' . $contributor . '"> Posted by ' . $contributor . '</a>';
			endif;
			?>
		</menu>
	</div>
</article>