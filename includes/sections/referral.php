<?php defined( 'ABSPATH' ) or die(); ?>
<?php if ( !current_user_can( 'edit_users' ) ) { die(); } ?>
<div class="wrap">

	<h1>Discipleship Survey - Referrals</h1>
	<hr>
	<?php require_once ( plugin_dir_path( __FILE__ ) . 'menu.php' ); ?>

	<?php

	global $wpdb;

	// Include Wordpress List table class //////////////////////////////////////////////////////////////////////////////////////////////////
	if(!class_exists('WP_List_Table')){
	require_once ( plugin_dir_path( __FILE__ ) . 'includes/class-wp-list-table' );
}

	// Build Custom Wordpress List Table Class to Make Table in Admin //////////////////////////////////////////////////////////////////////
	class dsurvey_List_Table extends WP_List_Table {

	function __construct(){
		global $status, $page, $referral;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'referral',     //singular name of the listed records
            'plural'    => 'referrals',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
	}

	function column_default($item, $column_name){
    	switch($column_name){
        	//case 'name':
			case 'organization':
				return $item[$column_name];
            case 'email':
            	return strtolower($item[$column_name]);
            case 'ref_code':
            	return strtolower($item[$column_name]);
			case 'send_email':
            	return strtolower($item[$column_name]);
            case 'sub_count':
            	return $item[$column_name];
            	break;
			default:
            	return $item[$column_name];
            	//return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_name($item){
		//Build row actions
        $actions = array(
			'submissions'   => sprintf('<a href="?page=%s&ref_code=%s">View Submissions</a>','dsurvey_subs', strtolower($item['ref_code'])),
            'edit'      	=> sprintf('<a href="?page=%s&action=%s&ref_id=%s">Edit</a>',$_REQUEST['page'],'edit_referral',strtolower($item['id'])),
            'delete'   	 	=> sprintf('<a onclick="return confirm(\'Click OKAY to delete this referral.\nIf you are unsure, click CANCEL.\')" href="?page=%s&action=%s&ref_id=%s">Delete</a>',$_REQUEST['page'],'delete_referral',$item['id']),
        );

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['name'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

	function column_cb($item){
   	   	return sprintf(
   	   		'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
		);
	}

	function column_send_email($item) {
		if ($item['send_email'] == 1) { echo "Yes"; } else { echo "No"; }
	}

	function get_columns(){
        $columns = array(
            'cb'        	=> '<input type="checkbox" />', //Render a checkbox instead of text
            'name'      	=> 'Name',
            'organization'  => 'Organization',
            'email'    		=> 'Email',
            'ref_code'  	=> 'Referral Code',
            'send_email'  	=> 'Send Email',
            'sub_count'  	=> 'Submission Count'
        );
		return $columns;
	}

   function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('name',false),     //true means it's already sorted
            'organization'    => array('organization',false),
            'email'  => array('email',false),
            'ref_code'  => array('ref_code',false),
            'send_email'  => array('send_email',false),
            'sub_count'  => array('sub_count',false)
        );
        return $sortable_columns;
    }


    /* function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if( 'delete' === $this->current_action() ) {
						echo "<div class='notice notice-success'>Items Deleted<p>";
        }
    }
    */

	function prepare_items() {
    	global $wpdb; //This is used only if making any database queries

    	$referrals = json_decode( json_encode( $wpdb->get_results( "SELECT * FROM ".DSURVEY_REF_TABLE ) ), true ); // Get all referrals info from Database
		$per_page = 10; // How many to display per page?

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$data = $referrals;
		//echo "<pre>".print_r($data, true)."</pre>";

		function usort_reorder($a,$b) {
        	$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
			$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
			return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
		}

		usort($data, 'usort_reorder');
    	$current_page = $this->get_pagenum();
		$total_items = count($data);
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		$this->items = $data;

		$this->set_pagination_args(
			array(
       			'total_items' => $total_items,                  //WE have to calculate the total number of items
	   			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
	   			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
	   		)
	   	);
	 } ////////// End prepare items

}

	// Process Delete First	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($_GET['action'] == "delete_referral") {

		$sql = "DELETE from ".DSURVEY_REF_TABLE." WHERE `id` = '".$_GET['ref_id']."'";
		$rows_affected = $wpdb->query($sql);

		if ($rows_affected) {
			// If update is success
			$hide_delete = 1;
			$message = "Referral Removed!";
			echo "<div class='notice notice-success'><p>{$message}</p></div>";
		}
	}

	// Add Referral Code ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($_GET['action'] == "add_referral") {

		if ( (!empty($_POST['ref_name'])) || (!empty($_POST['ref_emails'])) || (!empty($_POST['ref_org'])) || (!empty($_POST['ref_code'])) || (!empty($_POST['ref_name']))  ) {

				// If a form has been submitted, process. //////////////////////////////////////////////////////////////////////////////////////////
			$error = false;

			// Name - Build SQL //////////////////////////////
			if (!is_valid_letters_only($_POST['ref_name'])) {
				$error = true;
				$message .= "Full Name Can be Letters and Spaces Only.<br>";
			}

			// Email - Build SQL //////////////////////////////
			if (!is_email($_POST['ref_email'])) {
		    	$error = true;
				$message .= "Email is invalid.<br>";
		    }

			// Organization - Build SQL //////////////////////////////
			if (!is_valid($_POST['ref_org'])) {
		    	$error = true;
				$message .= "Organization can contain only letters and Numbers.<br>";
		    }

			// Ref Code - Build SQL //////////////////////////////
			if (empty($_POST['ref_code'])) {
				$error = true;
				$message .= "You did not enter a referral code.<br>";
			}
			elseif (!is_valid_no_space($_POST['ref_code'])) {
		    $error = true;
				$message .= "Referral Code can only contain letters and Numbers. No Spaces or special characters.<br>";
		  }

			// Emails - Build SQL //////////////////////////////
			if ( ( $_POST['ref_emails'] != 0 ) && ($_POST['ref_emails'] != 1 ) ) {
		    	$error = true;
				$message .= "Only yes or no are allowed..<br>";
		    }

				if ($error) {
					echo "<div class='notice notice-error'><p>{$message}</p></div>";
				}
				else {
					$sql = "INSERT INTO `".DSURVEY_REF_TABLE."` (`name`, `email`, `organization`, `ref_code`, `send_email`) VALUE ('".$_POST['ref_name']."', '".$_POST['ref_email']."', '".$_POST['ref_org']."', '".$_POST['ref_code']."', '".$_POST['ref_emails']."')";

					$rows_affected = $wpdb->query($sql);
					if ($rows_affected) {
						// If update is success
						$message = "New Referral Added!";
						echo "<div class='notice notice-success'><p>{$message}</p></div>";
						echo "<hr>";
						$added_new_ref = 1;
					}
				}
			}
			require_once ( plugin_dir_path( __FILE__ ) . 'referral-form.php' );
	} // End Add Referral Code ////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	// Update Referral Code ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ( ($_GET['action'] == "edit_referral") && (!empty($_GET['ref_id'])) ) {

		// If a form has been submitted, process. //////////////////////////////////////////////////////////////////////////////////////////
		if (!empty($_POST['ref_name'])) {
	$sql = "UPDATE ".DSURVEY_REF_TABLE." SET ";
	$error = false;

	// Name - Build SQL //////////////////////////////
	if (!is_valid_letters_only($_POST['ref_name'])) {
		$error = true;
		$message .= "Full Name Can be Letters and Spaces Only.<br>";
	}
	else {
		$sql .= "`name` = '{$_POST["ref_name"]}'";
		$update = 1;
	}

	// Email - Build SQL //////////////////////////////
	if (!is_email($_POST['ref_email'])) {
    	$error = true;
		$message .= "Email is invalid.<br>";
    }
    else {
	    if ($update) { $sql .= ", ";}
	    $sql .= "`email` = '{$_POST["ref_email"]}'";
	    $update = 1;
    }

	// Organization - Build SQL //////////////////////////////
	if (!is_valid($_POST['ref_org'])) {
    	$error = true;
		$message .= "Organization can contain only letters and Numbers.<br>";
    }
    else {
	    if ($update) { $sql .= ", ";}
	    $sql .= "`organization` = '{$_POST["ref_org"]}'";
	    $update = 1;
    }

	// Ref Code - Build SQL //////////////////////////////
	if (!is_valid_no_space($_POST['ref_code'])) {
    	$error = true;
		$message .= "Referral Code can only contain letters and Numbers. No Spaces or special characters.<br>";
    }
    else {
	    if ($update) { $sql .= ", ";}
	    $sql .= "`ref_code` = '{$_POST["ref_code"]}'";
	    $update = 1;
    }

	// Emails - Build SQL //////////////////////////////
	if ( ( $_POST['ref_emails'] != 0 ) && ($_POST['ref_emails'] != 1 ) ) {
    	$error = true;
		$message .= "Only yes or no are allowed..<br>";
    }
    else {
	    if ($update) { $sql .= ", ";}
	    $sql .= "`send_email` = {$_POST["ref_emails"]}";
	    $update = 1;
    }

    // Finish Building SQL //////////////////////////////
    $sql .= " WHERE `id` = {$_GET["ref_id"]}";

	if ($error) {
		echo "<div class='notice notice-error'><p>{$message}</p></div>";
	}

	if ($update == 1) { // No Error and a Value Was Changed, so Prepare and update Database

		$rows_affected = $wpdb->query($sql);

		if ($rows_affected) {
			// If update is success
			$message = "Referral Updated!";
			echo "<div class='notice notice-success'><p>{$message}</p></div>";
		}
	}
	}

		// Get Data from Database
		$data = $wpdb->get_row( "SELECT * FROM ".DSURVEY_REF_TABLE." WHERE id = '".$_GET['ref_id']."'");
		require_once ( plugin_dir_path( __FILE__ ) . 'referral-form.php' );
		echo "<hr>";

	}


	$testListTable = new dsurvey_List_Table();
	$testListTable->prepare_items();
	?>
		<h3> Referrals </h3>
		<form id="movies-filter" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php $testListTable->display() ?>
		</form>
	</div>
