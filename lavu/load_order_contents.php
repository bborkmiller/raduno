<?php

ini_set('memory_limit', '1024M');

include "lavu_func.php";
include "lavu_db_load.php";

// General Configuration Settings
$db_config = parse_ini_file("config.ini");
date_default_timezone_set("America/Detroit");

// POSLavu API Settings
$lavu_config = parse_ini_file("lavu_config.ini");

// Connect to database
try {
	$dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
	$db_handle = new PDO($dsn, $db_config['user'], $db_config['pass']);
	$db_handle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $e) {
	echo $e->getMessage() . "\r\n";
	exit("** Unable to connect to the database **\r\n");
}

// Set up Lavu API parameters
$lav_query = array(
	config => $lavu_config,
	filt_col => NULL,
	filt_val => NULL,
	filt_min => NULL,
	filt_max => NULL,
);

// Check for existing rows
$row_check = $db_handle->prepare("SELECT COUNT(*) FROM orders;");
$row_check->execute();
$r = $row_check->fetch();
if ($r[0] > 0) { 
	$l = readline("The order_contents table is not empty. (D)elete or (C)ancel? ");
	switch (strtoupper($l)) {
		case "D":
			$sql = "DELETE FROM order_contents;";
			$db_handle->exec($sql);
			echo "Cleared the table.\r\n";
			break;
		case "C":
			exit("Canceled.\r\n");
			break;
		default;
			exit("I don't know what that means! Canceling.\r\n");
			break;
	}
}

// ------------------------------------------------------
// Fill the order_contents table
$keep_columns = array('id', 'order_id', 'item', 'price', 'quantity', 'item_id', 'category_id');

// Query the table in 1000 row increments
$start = 0;
$increment = 1000;
$rows = 1000;

while ( $rows == $increment ) {
	$lav_query['limit'] = $start . ',' . $increment;
// 	echo $lav_query['limit'] . "\r\n";
	$rows = import_table('order_contents', $keep_columns, $lav_query, $db_handle, FALSE, TRUE);
	$start = $start + $increment;
}
?>