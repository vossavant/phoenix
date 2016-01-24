<?php
/*
 *	QUOTEBOARD
 *	Single Member
 *
 *	Called from author-followers.php, author-following.php, and board-members.php
 *	Displays a single member in list view.
 */
?>

<article class="member box">
	<a class="avatar" href="<?= $member_home_url; ?>">
		<img src="<?= TIMTHUMB_PATH . $member_avatar; ?>&w=60&h=60" alt="<?= $member_screen_name; ?>" />
	</a>
	<div>
		<div class="bubble" data-link="<?= $member_home_url; ?>">
			<h4><?= $member_screen_name; ?><span>@<?= $member_username; ?></span></h4>
			<?php if ( is_singular('board') ) {
				if ( $board_author == $member_id ) {
					echo '<span class="box-meta">Board Curator</span>';
				} else if ( $member['can_collaborate'] == 'y' ) {
					echo '<span class="box-meta">Collaborator</span>';
				}
			} ?>
			<p><?= $member_description; ?></p>
		</div>
		<menu><?= $follow_button; ?></menu>
	</div>
</article>