<?php
header("Content-type: text/csv");  
header("Cache-Control: no-store, no-cache");  
header("Content-Disposition: attachment; filename=\"wp_export_users-" . date("H-m-d H:i:s") . ".csv\""); 
if ( isset( $_POST['submit'] ) ) :
	require $_POST['wp_location'] . '/wp-load.php';
	$download = new WP_Export_Users_Download();
	$user_data = $download->getUserData( $_POST );			
	$download->outputData( $user_data );
endif;