<?php defined( 'ABSPATH' ) or die(); ?>
<?php if ( !current_user_can( 'edit_users' ) ) { die(); } ?>
<style>	.dsurvery-top-button-red { margin-right: 7px !important; background-color: #b5000d !important; color: #ffffff !important; } </style>

<?php

if ( ($_GET['action'] == "add_referral") && ($added_new_ref != 1) ) {
  $submit_text = "Add New Referral";
  if ( !empty( $_POST['ref_name'] ) ) {
    $data->name = $_POST['ref_name'];
  }

  if ( !empty( $_POST['ref_email'] ) ) {
    $data->email = $_POST['ref_email'];
  }

  if ( !empty( $_POST['ref_org'] ) ) {
    $data->organization = $_POST['ref_org'];
  }

  if ( !empty( $_POST['ref_code'] ) ) {
    $data->ref_code = $_POST['ref_code'];
  }
}
elseif ($_GET['action'] == "edit_referral") {
    $submit_text = "Update Referral";
}
 ?>

<form method="post" action="">
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Full Name</th>
        <td><input type="text" name="ref_name" value="<?php echo esc_attr( $data->name ); ?>" size="50" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Email</th>
        <td><input type="text" name="ref_email" value="<?php echo esc_attr( $data->email ); ?>" size="50"/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Organization</th>
        <td><input type="text" name="ref_org" value="<?php echo esc_attr( $data->organization ); ?>" size="50"/></td>
        </tr>


        <tr valign="top">
        <th scope="row">Referral Code</th>
        <td><input type="text" name="ref_code" value="<?php echo esc_attr( $data->ref_code ); ?>" size="50"/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Send Emails when Form is Submitted?</th>
        <td>
        	<select name="ref_emails">
        		<option value="1" <?php if($data->send_email == 1) { echo "selected"; } ?>>Yes</option>
        		<option value="0" <?php if($data->send_email == 0) { echo "selected"; } ?>>No</option>
        	</select>
        </td>
        </tr>
    </table>


	<?php
	submit_button($submit_text, "button button-primary left", "update", false ); ?>

		<?php if ( (!empty($_GET['ref_id'])) && ($hide_delete != 1) ) : ?>
	<a
		class="button button-error left dsurvery-top-button-red right"
		onclick="return confirm('Click OKAY to delete this referral.\nIf you are unsure, click CANCEL.')" href="?page=dsurvey_refs&action=delete_referral&ref_id=<?php echo $_GET['ref_id']; ?>">
		<span class="dashicons dashicons-trash" style="margin-top: 5px;"></span> Delete this Referral
	</a>
<?php endif; ?>

</form>
