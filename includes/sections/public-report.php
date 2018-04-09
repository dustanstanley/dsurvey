<?php defined( 'ABSPATH' ) or die(); ?>
<p style="width: 100%; clear: both; height: 10px;"></p>
<?php
	$sql = "SELECT * FROM ".DSURVEY_SUB_TABLE." WHERE token = '".$_GET['t']."'";
	//echo $sql;
	$data = $wpdb->get_results( $sql );
	$d = $data[0];
	//print_r($d);
	$sdata = unserialize($d->data);

	foreach($sdata as $sd) {
		//echo "<pre>".print_r($sd, true)."</pre>";
		if ($sd['key'] !== "submit") {
			$output .= "<div style='clear: both;><span style='float: left;'><b>".$sd['label'].":</b></span><span style='float: right;'>".$sd['value']."</span></div><div style='clear: both; border-bottom: #dcdcdc dashed 1px;'></div>";
		}
	}
?>
