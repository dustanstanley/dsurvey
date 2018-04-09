<?php defined( 'ABSPATH' ) or die(); ?>
<?php if ( !current_user_can( 'edit_users' ) ) { die(); } ?>
<div class="wrap">
	<h1>Discipleship Survey - Submissions</h1>
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
		global $status, $page, $referrals;

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
			case 'time':
				return date("F jS, Y - g:i a", strtotime($item[$column_name]));
            case 'email':
            	return strtolower($item[$column_name]);
            case 'ref_code':
            	return strtolower($item[$column_name]);
			default:
            	return $item[$column_name];
            	//return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_name($item){
		//Build row actions
        $actions = array(
			'submissions'   => sprintf('<a href="?page=%s&t=%s&ref_code=%s">View Submission</a>',$_REQUEST['page'],$item['token'],$item['ref_code']),
            'delete'   	 	=> sprintf('<a onclick="return confirm(\'Is it okay to delete this submission?\')" href="?page=%s&action=%s&sub_id=%s">Delete</a>',$_REQUEST['page'],'delete_submission',$item['id']),
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


	function get_columns(){
        $columns = array(
            'cb'        	=> '<input type="checkbox" />', //Render a checkbox instead of text
            'name'      	=> 'Name',
            'time'  		=> 'Time',
            'email'  		=> 'Email',
            'ref_code'  	=> 'Reference Code'
        );
		return $columns;
	}

   function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('name',false),     //true means it's already sorted
            'time'    => array('time',false),
            'email'  => array('email',false),
            'ref_code'  => array('ref_code',false)
        );
        return $sortable_columns;
    }



    function get_bulk_actions() {
        $actions = array(
            'delete_multiple'    => 'Delete'
        );
        return $actions;
    }



    function process_bulk_action() {
			global $wpdb;
        //Detect when a bulk action is being triggered...
        if( 'delete_multiple' === $this->current_action() ) {
					$sql = "DELETE from ".DSURVEY_SUB_TABLE." WHERE id IN (";

					foreach ( $_GET['referral'] as $ref) {
						if ( $r1 ) {
							$sql .= ",";
						}
						$sql .= $ref;
						$r1 = true;
					}

					$sql .= ")";
					$rows_affected = $wpdb->query($sql);
					if ($rows_affected) {
							$message = "Submissions removed successfully.";
							echo "<div class='notice notice-success'><p>{$message}</p></div>";
					}
        }
    }

	function prepare_items() {
    	global $wpdb; //This is used only if making any database queries

			$this->process_bulk_action();
			
    	if ( !empty($_GET['ref_code'] ) ) {
	    	echo "<p style='width: 100%; clear: both;'><b>Showing all submissions with referral code <i>".$_GET['ref_code']."</i>. To view all submissions, <a href='?page=".$_REQUEST['page']."'>click here</a>.</b></p>";
	    	$referrals = json_decode( json_encode( $wpdb->get_results( "SELECT * FROM `".DSURVEY_SUB_TABLE."` WHERE `ref_code` = '".$_GET['ref_code']."'" ) ), true );
    	}
    	else {
    		$referrals = json_decode( json_encode( $wpdb->get_results( "SELECT * FROM ".DSURVEY_SUB_TABLE ) ), true ); // Get all referell info from Database
		}

		$per_page = 20; // How many to display per page?

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);



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
if ($_GET['action'] == "delete_submission") {

	$sql = "DELETE from ".DSURVEY_SUB_TABLE." WHERE `id` = '".$_GET['sub_id']."'";
	$rows_affected = $wpdb->query($sql);

	if ($rows_affected) {
		// If update is success
		$hide_delete = 1;
		$message = "Submission Removed!";
		echo "<div class='notice notice-success'><p>{$message}</p></div>";
	}
}





// If the token is set in the URL, include the public report. /////////////////////////////////////////////////////////////////////////
if ( !empty ( $_GET['t'] ) ) {
	require_once ( plugin_dir_path( __FILE__ ) . 'public-report.php' ); // If a token is included, require the public report page here.
	echo $output;
}

// If a token is not set, show all the submissions in sortable table. /////////////////////////////////////////////////////////////////
else {
	$testListTable = new dsurvey_List_Table();
	$testListTable->prepare_items(); ?>

	<form id="movies-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $testListTable->display() ?>
	</form>

<?php } ?>


</div>
