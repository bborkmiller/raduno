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
	limit => '1000'
);

// Check for existing rows
$row_check = $db_handle->prepare("SELECT COUNT(*) FROM orders;");
$row_check->execute();
$r = $row_check->fetch();
if ($r[0] > 0) { 
	$l = readline("The orders table is not empty. (D)elete or (C)ancel? ");
	switch (strtoupper($l)) {
		case "D":
			$sql = "DELETE FROM orders;";
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
// Fill the order table
$keep_columns = array('id', 'order_id', 'opened', 'closed', 'subtotal', 'tax', 'total',
						'server', 'server_id', 'discount', 'cash_paid', 'card_paid',
						'cashier', 'cashier_id', 'guests', 'order_status',
						'reopened_datetime', 'reclosed_datetime', 'void', 'discount_id');
$lav_query['filt_col'] = 'opened';
$lav_query['filt_min'] = $dteStart;
$lav_query['filt_max'] = $dteEnd;

// Load five day increments from 7/1 to today
$date = new DateTime('2017-07-01');
$now = new DateTime();
$now->setTime(0, 0, 0); // truncate time
while ( $date < $now ) {
	$lav_query['filt_min'] = $date->format('Y-m-d');
	$date->add(date_interval_create_from_date_string('5 days'));
	
	// Don't import today (since it's not complete yet)
	if ( $date > $now ) { $date = $now; }
	
	$lav_query['filt_max'] = $date->format('Y-m-d');
	
	$rows = import_table('orders', $keep_columns, $lav_query, $db_handle, FALSE, TRUE);
	if ($rows === $lav_query['limit']) { echo " ** WARNING: limit <= the number of rows returned **\r\n"; }
}
?>