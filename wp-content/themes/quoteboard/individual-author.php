<?php
/*
 *	QUOTEBOARD
 *	Single User
 *
 *	Currently only called from search.php
 */


if ( !$author_avatar = get_wp_user_avatar( $user->ID, 'thumbnail' ) ) {
	if (is_page('authors')) {
		$author_avatar = get_default_thumbnail( 'thumbnail' );
	} else {
		$author_avatar = get_default_thumbnail( 'thumb-small' );
	}
}

if ( !$user_bio = get_user_meta( $user->ID, 'description', true ) ) {
	$user_bio = 'Alas, we don\'t have any information about this person.';
}

echo
'<article class="board box">
	<a class="avatar" href="' . home_url() . '/author/' . $user->user_nicename . '">' . $author_avatar . '</a>
	<div>
		<div class="bubble" data-link="' . home_url() . '/author/' . $user->user_nicename . '">
			<h4>' . $user->display_name . '</h4>
			<p>' . $user_bio . '</p>
		</div>
	</div>
</article>';