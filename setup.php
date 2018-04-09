<?php defined( 'ABSPATH' ) or die(); ?>
<?php if ( !current_user_can( 'edit_users' ) ) { die(); } ?>
<style>
  .setup-boxes {
    margin-left:10px;
    border: 1px solid #dcdcdc !important;
    padding: 10px;
    width: 92%;
  }

</style>

<hr style="clear: both;">
<h1 style="float: left;">Setup</h1>
<span style="float: right; font-size: 10px; text-align: right; margin-top: 30px;">Discipleship Survey v0.01</span>
<hr style="clear: both;">
<?php
  if ( ( !empty($_REQUEST['update_report_page'] ) ) && (  is_numeric ( $_REQUEST['update_report_page'] ) ) ) {
    update_option( 'dsurvey_report_page', $_REQUEST['update_report_page']  );
    echo "<p class='notice notice-success'>Public Report Page Updated </p>";
  }
  $dsurvey_report_page = get_option( "dsurvey_report_page" );

  if ( !empty($_REQUEST['dsurvey_email_text'] ) ) {
    update_option( 'dsurvey_email_text', htmlentities ( stripslashes ( $_REQUEST['dsurvey_email_text'] ) ) );
    echo "<p class='notice notice-success'>The text sent in the email has been updated.</p>";
  }
  $dsurvey_email_text = get_option( "dsurvey_email_text" );

  if ( !empty($_REQUEST['dsurvey_email_surveyor_text'] ) ) {
    update_option( 'dsurvey_email_surveyor_text', htmlentities ( stripslashes ( $_REQUEST['dsurvey_email_surveyor_text'] ) ) );
    echo "<p class='notice notice-success'>The text sent in the email to the surveyor has been updated.</p>";
  }
  $dsurvey_email_surveyor_text = get_option( "dsurvey_email_surveyor_text" );



  if ( !empty($_REQUEST['dsurvey_from_email'] ) )  {
    if (  is_email ( $_REQUEST['dsurvey_from_email'] ) ) {
      update_option( 'dsurvey_from_email', $_REQUEST['dsurvey_from_email']  );
      echo "<p class='notice notice-success'>Email FROM Address Updated </p>";
    }
    else {
      echo "<p class='notice notice-error'>Not a valid email address. </p>";
    }
  }
  $dsurvey_from_email = get_option( "dsurvey_from_email" );



?>

<h3>Step 1:</h3>
  <form class="setup-boxes" action="" method="post">
    Insert what email address you would like the emails to the referrals to come <b>FROM</b>.
    <br><br>
    <input type="text" value="<?php echo $dsurvey_from_email; ?>" name="dsurvey_from_email" size="45">
    <br>
    <input type="submit" name="submit" value="Save" />
  </form>
  <br><br>

<h3>Step 2:</h3>
  <form class="setup-boxes" action="" method="post">
    Enter what you would like the email to each referral to say.
    <br><br>
    <textarea style="width: 100%; max-width: 400px; height: 200px;" name="dsurvey_email_text"><?php echo $dsurvey_email_text; ?></textarea>
    <br>
    <input type="submit" name="submit" value="Save" />
  </form>
  <br><br>

  <form class="setup-boxes" action="" method="post">
    Enter what you would like the email to each surveyor to say.
    <br><br>
    <textarea style="width: 100%; max-width: 400px; height: 200px;" name="dsurvey_email_surveyor_text"><?php echo $dsurvey_email_surveyor_text; ?></textarea>
    <br>
    <input type="submit" name="submit" value="Save" />
  </form>
  <br><br>


<h3>Step 3:</h3>
<p class="setup-boxes">
  To tell Ninja Forms to process your form, please add a <b>CUSTOM</b> <i>Emails and Action</i> with the <i>Hook Tag</i>: <b>dsurvey_processing</b>.
<br><br>
<?php echo '<img style="max-width: 350px; width: 100%;" src="' . plugins_url( 'images/ds-ss2.jpg', __FILE__ ) . '" > '; ?>
<br><br>

</p>

<h3>Step 4:</h3>
<p class="setup-boxes">
  To setup the <i>PUBLIC REPORT</i> page, create a new page and insert the shortcode: <b>[show_public_report]</b>.

  <br><br>
  <?php echo '<img style="max-width: 350px; width: 100%;" src="' . plugins_url( 'images/ds-ss3.jpg', __FILE__ ) . '" > '; ?>
  <br><br>

<h3>Step 5:</h3>

   <form class="setup-boxes" action="" method="post">
     Now, choose the page that you inserted the shortcode here, and hit save.
     <br><br>
     <?php wp_dropdown_pages( array(
        'depth' => 0,
        'child_of' => 0,
        'selected' => $dsurvey_report_page,
        'echo' => 1,
        'name' => 'update_report_page',
        'id' => '',
        'class' => '',
        'show_option_none' => 'What page has the shortcode?',
        'show_option_no_change' => '',
        'option_none_value' => '',
        'value_field' => 'ID',
    )
  ); ?>
	<br>
    <input type="submit" name="submit" value="Save" />
   </form>




</p>


<h3>Step 6:</h3>
<p class="setup-boxes">
  To setup a form to work with the referral code section, insert a new <i>Single Line Text</i> form section under the <i>Form Fields</i>. Then, in the <i>FIELD KEY</i> section under the <i>ADMINISTRATION</i> dropdown, insert <b>ref_code</b>.

  <br><br>
  <?php echo '<img style="max-width: 350px; width: 100%;" src="' . plugins_url( 'images/ds-ss4.jpg', __FILE__ ) . '" > '; ?>
  <br><br>


<h2 style="color: red;">Important Notes:</h2>
<p>If you want this plugin to send an email to the taker of the survey, their MUST be an email field with the FIELD_KEY variable set to <b><i>email</i></b> under the Administration section of the field.





<h2>What does this plugin do?</h2>
<p>This plugin is designed to extend Ninja Form Three's functionality.
  <ol>
    <li>
      It creates a referral code system that, if a referral code is entered in a field named <i>ref_code</i>, it searches the database to find connections matching that code and emails the corrosponding contact if the <i>Send Emails when Form is Submitted?</i> option is set to <i>Yes</i>.
    </li>
    <li>
      It creates public report that can be viewed using a token that is automatically generated. This token is passed with the link and if clicked will send you to report viewable on the front end of the website.
    </li>

  </ol>
</p>
