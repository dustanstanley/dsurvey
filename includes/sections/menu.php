<?php defined( 'ABSPATH' ) or die(); ?>
<?php if ( !current_user_can( 'edit_users' ) ) { die(); } ?>
<style>
	.dsurvery-top-button { margin-right: 7px !important; }
	.dsurvery-top-button-icon { margin-top: 5px !important; }
</style>


	<hr>
	<a class="button button-primary right dsurvery-top-button" href="?page=dsurvey_refs&action=add_referral">
		<span class="dashicons dashicons-plus-alt dsurvery-top-button-icon"></span> Add a Referral
	</a>

	<a class="button button-primary dsurvery-top-button" href="?page=dsurvey_subs">
		<span class="dashicons dashicons-media-spreadsheet dsurvery-top-button-icon"></span> View Submissions
	</a>

	<a class="button button-primary dsurvery-top-button" href="?page=dsurvey_refs">
		<span class="dashicons dashicons-redo dsurvery-top-button-icon"></span> View Referrals
	</a>

	<?php
	if ( !empty ( $_GET['t'] ) ) {
		if ( !empty($_GET['ref_code'] ) ) : ?>
			<a class="button button-primary" style="margin-right: 7px !important;" href="?page=dsurvey_subs&ref_code=<?php echo $_GET['ref_code']; ?>">
				View Submissions with Referral Code: <i><?php echo $_GET['ref_code']; ?></i>
			</a>
		<?php endif;
	}
	?>
