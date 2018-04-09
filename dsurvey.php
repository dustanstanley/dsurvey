<?php
   /*
   Plugin Name: Discipleship Survey - Submission Handler -
   Plugin URI: http://www.ggcn.org
   Description: This plugin manages the submissions from Ninja Forms 3.
   Version: 0.03
   Author: Dustan Stanley
   Author URI: http://www.dustanstanley.com
   */

////////////////// PLUGIN SETUP /////////////////////
global $wpdb;
DEFINED ( 'ABSPATH' ) or die();
DEFINE ( 'DSURVEY_DB_VERSION', 0.512) ; // Set the DB version. As we update the db we will change this so wordpress will upgrade it using delta.
DEFINE ( 'DSURVEY_SUB_TABLE', $wpdb->prefix .'dsurvey_surveys' );
DEFINE ( 'DSURVEY_REF_TABLE', $wpdb->prefix .'dsurvey_ref' );

// PLUGIN SETUP FUNCTION //
register_activation_hook ( __FILE__, 'dsurvey_install' );
function dsurvey_install () {
  global $wpdb; // Access the built in wordpress database class.
  global $dsurvey_db_version; // Bring the DB version into the function.
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // Needed to Upgrade Tables instead of create new ones everytime
  $installed_ver = get_option( "dsurvey_db_version" ); // Check the database version stored at the end of this function.

  if ( ( $installed_ver != DSURVEY_DB_VERSION ) || ( $wpdb->get_var( "SHOW TABLES LIKE '".DSURVEY_REF_TABLE."'" ) != DSURVEY_REF_TABLE ) || ( $wpdb->get_var( "SHOW TABLES LIKE '".DSURVEY_SUB_TABLE."'" ) != DSURVEY_SUB_TABLE ) ) {
	// Table to hold referrel codes ////////////////////////////////////////////
	 // SQL to create the table in the database.
	  $sql = "CREATE TABLE ".DSURVEY_REF_TABLE." (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time_joined datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			name tinytext NOT NULL,
			email tinytext NOT NULL,
			organization tinytext,
			ref_code tinytext NOT NULL,
			sub_count int(11) NOT NULL DEFAULT '0',
			send_email tinyint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (id)
		) $charset_collate;";
    //error_log($sql);
		dbDelta( $sql );

		// Table to hold form submissions ////////////////////////////////////////////
		$sql = "CREATE TABLE ".DSURVEY_SUB_TABLE." (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  formid mediumint(9) NOT NULL,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  email tinytext,
		  data longtext NOT NULL,
		  ref_code tinytext,
		  token tinytext,
		  name tinytext NULL,
		  PRIMARY KEY (id)
		) $charset_collate;";
    //error_log($sql);
		dbDelta( $sql );

		// Update the DB version in the database so when we change it on the plugin it will update.
		update_option( 'dsurvey_db_version', DSURVEY_DB_VERSION );
	}
}

///////////////////// PUBLIC REPORT - Shortcode Creation /////////////////////
// This Section add a shortcode that waits for a token that matches a token found in the dsurvey_surveys database table.
// It then includes the REPORT template.
add_shortcode('show_public_report', 'show_public_report');
function show_public_report() {
	global $wpdb;
	if ( $wpdb->get_var( "SELECT COUNT(*) FROM ".DSURVEY_SUB_TABLE." WHERE token = '".$_GET['t']."'") ) {
    	require_once ( plugin_dir_path( __FILE__ ) . 'includes/sections/public-report.php' );
	}
	return $output;
}

///////////////////// WORDPRESS ADMIN PAGE & MENUS /////////////////////
add_action('admin_menu', 'dsurvey_create_options_page'); // Hook to tell wordpress to add the toplevel page
function dsurvey_create_options_page() {
	// Add new top level menu page to wordpress admin area.
	add_menu_page( 'Discipleship Survey', 'Discipleship Survey', 'manage_options', 'dsurvey_settings', 'dsurvey_settings_page', 'dashicons-list-view',2);
	// Rename top submenu link.
	add_submenu_page( 'dsurvey_settings', 'Setup', 'Setup', 'manage_options', 'dsurvey_settings', 'dsurvey_settings_page' );
	// Add additonal submenu items.
	add_submenu_page('dsurvey_settings', 'Referrals', 'Referrals', 'manage_options', 'dsurvey_refs', 'dsurvey_settings_refs');
	add_submenu_page('dsurvey_settings', 'Submissions', 'Submissions', 'manage_options', 'dsurvey_subs', 'dsurvey_settings_subs');
}

// Callback funtion for top level menu.
function dsurvey_settings_page() {
	require_once ( plugin_dir_path( __FILE__ ) . 'setup.php' );
}

// Callback funtion for referrel section.
function dsurvey_settings_refs () {
	require_once ( plugin_dir_path( __FILE__ ) . 'includes/sections/referral.php' );
}
// Callback function for submissions section.
function dsurvey_settings_subs () {
	require_once ( plugin_dir_path( __FILE__ ) . 'includes/sections/submissions.php' );
}

///////////////////// NINJA FORMS CALLBACK FUNCTION /////////////////////
// This section create the 'dsurvey_processing' callback function. This should be added under Ninja Forms 3 under Emails and Action.
// Use the CUSTOM type of action and put dsurvey_processing under the HOOK TAG section.
add_action ( 'dsurvey_processing', 'dsurvey_processing_callback' );
function dsurvey_processing_callback( $form_data ){
	global $wpdb; // Make the wordpress DB class local to this function.
    $form_fields   =  $form_data[ 'fields' ];  // Form data is sent using the $form_data array.
	$serialized_form = serialize($form_fields); // Serialize all the form fields for an easy way to store the data in the database.
		
	
   	// Scroll through the fiels and assign them all to an array.
    foreach( $form_fields as $field ) {
   		$sub[$field[ 'key' ]] = $field[ 'value' ];
    }
    //$er = print_r($sub, TRUE);
    //error_log($er, 3, "/var/www/vhosts/ggcn.org/httpdocs/ggcn-errors.log");


    // Store certain for data into variable for easy reference.
    $form_settings = $form_data[ 'settings' ];
    $form_title    = $form_data[ 'settings' ][ 'title' ];

	// Create random unique token to send by email that will link to a public report.
	$token = md5 ( uniqid( rand(), true ) );
	$date = date ( 'Y-m-d H:i:s' );
	
	
    //error_log("\n".$token, 3, "/var/www/vhosts/ggcn.org/httpdocs/ggcn-errors.log");
    

	//$serialized_form = $wpdb->_escape($serialized_form);
	$raw = $wpdb->insert( DSURVEY_SUB_TABLE, array (
    	'formid' => $form_data[ 'form_id' ],
    	'time' => $date,
    	'email' => $sub['email'],
    	'name' => $sub['name'],
    	'ref_code' => $sub['ref_code'],
    	'token' => $token,
    	'data' => $serialized_form
    ) );
    
    
    $wpdb->print_error();
    
    
    //error_log($serialized_form, 3, "/var/www/vhosts/ggcn.org/httpdocs/ggcn-errors.log");
    

    
    $dsurvey_surveyor_email = $sub['email'];
    //error_log($dsurvey_surveyor_email, 3, "/var/www/vhosts/test.ggcn.org/httpdocs/ggcn-errors.log");

    // Update the number of referral submissions in the database.
	$wpdb->update ( DSURVEY_REF_TABLE,
		array( 'sub_count' => $wpdb->get_var( "SELECT COUNT(*) FROM ".DSURVEY_SUB_TABLE." WHERE ref_code = '".$sub['ref_code']."'" ) ),
		array( 'ref_code' => $sub['ref_code'] )
	);

    $refinfo = $wpdb->get_results( "SELECT * FROM ".DSURVEY_REF_TABLE." WHERE ref_code = '".$sub['ref_code']."'" );
    if ( count ( $refinfo ) ) {
	    foreach ( $refinfo as $r ) {
  			if ( ( !empty( $r->send_email ) ) && ( $r->send_email == TRUE ) ) {
	  			$dsurvey_report_page = get_option( "dsurvey_report_page" );
	  			$dsurvey_from_email = get_option( "dsurvey_from_email" );
	  			$report_url = get_permalink( $dsurvey_report_page );
  
  				// Send email, 
  				$dsurvey_email_text = nl2br ( html_entity_decode( get_option( "dsurvey_email_text" ) ) );
  				$dsurvey_email_surveyor_text = nl2br ( html_entity_decode( get_option( "dsurvey_email_surveyor_text" ) ) );
  				
  				$message = $dsurvey_email_text ." <br /><br /> <b>To view the submission, go here:</b> ".$report_url."?t=".$token;
  				$surveyor_message = $dsurvey_email_surveyor_text ." <br /><br /> <b>To view the submission, go here:</b> ".$report_url."?t=".$token;
  				$subject = $form_title." - ".$sub['name'];
  				$surveyor_subject = $form_title." - ".$sub['name'];
  				
  				$headers[] = 'From: Discipleship Survey <'.$dsurvey_from_email.'>';
  				$headers[] = 'Content-Type: text/html; charset=UTF-8';
  				
  				$to = $r->email;
  				
  				wp_mail( $to, $subject, $message, $headers );
  				wp_mail( $dsurvey_surveyor_email, $surveyor_subject, $surveyor_message, $headers );
  				//$er = "$dsurvey_surveyor_email, $surveyor_subject, $surveyor_message";
  				//error_log($er, 3, "/var/www/vhosts/ggcn.org/httpdocs/ggcn-errors.log");
  			}
  		}
  	}

}

function createSubmissionReport ( $subid ) {

}

function is_valid ( $str ) {
    return !preg_match ( '/[^A-Za-z0-9_ -]/', $str );
}

function is_valid_letters_only($str) {
    return !preg_match ( '/[^A-Za-z ]/', $str );
}

function is_valid_no_space ( $str ) {
    return !preg_match ( '/[^A-Za-z0-9]/', $str);
}
?>
