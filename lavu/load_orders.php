<?php
/*
	Parameters: load_orders.php [dateStart] [dateEnd]
	
	If parameters are empty, script will load data starting from 2017/07/01 to today.

*/

ini_set('memory_limit', '1024M');

include "lavu_func.php";
include "lavu_db_load.php";

// General Configuration Settings
$db_config = parse_ini_file("config.ini");
date_default_timezone_set("America/Detroit");

// POSLavu API Settings
$lavu_config = parse_ini_file("lavu_config.ini");

// Check to see if both date parameters have been passed
if (! isset($argv[1]) === isset($argv[2])) {  
	exit("** Incomplete dates **\r\n   - load_orders.php [date start] [date end]\r\n");
}

// If parameters have been passed, check them and get them ready
if (isset($argv[1], $argv[2])) {
	$useParams = TRUE;

	// Check for errors in the date format
	if (date_parse($argv[1])[error_count] > 0 OR date_parse($argv[2])[error_count] >0 ) {
		exit("** Unparseable date parameter **\r\n");
	}

	$startParam = $argv[1];
	$endParam = $argv[2];
	//print_r(date('Y-m-d', $dateStart) . " to " . date('Y-m-d', $dateEnd) . "\r\n");
	
		// Check for other mistakes
	if ( strtotime($startParam) > strtotime($endParam) ) { exit("** Date error **\r\n"); }
	if ( strtotime($startParam) < strtotime('2017/07/01') ) { exit("** Start date before 2017/01/01 ** \r\n");}
	
}

// Set date range
if ($useParams) { // Parameters were passed, so use them.
	$startDate = new DateTime($startParam);
	$endDate = new DateTime($endParam);
	
	// Add one day to $endDate (it's exclusive)
	$endDate->add(date_interval_create_from_date_string('1 day'));
	
	// If the end date was set later than today, reset it
	if ($endDate > new DateTime()) {
		$endDate = new DateTime();
		$endDate->setTime(0, 0, 0); // truncate the time
		print_r (" * Note: The end date was set to today *\r\n");
	}
} else { // No parameters, set to defaults
	$startDate = new DateTime('2017-07-01');
	$endDate = new DateTime();
	$endDate->setTime(0, 0, 0); // truncate the time
}

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

// Check for an overlap between existing data and date range of the new data.
// If there is an overlap, ask what to do.
$sql = "SELECT COUNT(*) FROM orders WHERE opened >= ? AND opened < ?;";
$row_check = $db_handle->prepare($sql);
$row_check->execute([$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
$r = $row_check->fetch();

if ($r[0] > 0) {
	// Find the min and max dates, for informational purposes.
	$sql = "SELECT MIN(opened) AS MinDate, MAX(opened) AS MaxDate FROM orders;";
	$overlap = $db_handle->prepare($sql);
	$overlap->execute();
	$r = $overlap->fetch();

	print_r("There's data in the orders table from " . substr($r["MinDate"], 0, 10) . " to " . substr($r["MaxDate"], 0, 10) . "\r\n" );
	$l = readline("What should I do? (D)elete overlapping dates or (C)ancel? ");
	
	switch (strtoupper($l)) {
		case "C":
			exit("Canceled.\r\n");
			break;
		case "D":
			$sql = "DELETE FROM orders WHERE opened >= ? and opened < ?;";
			$db_handle->prepare($sql)->execute([$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
			echo "Cleared overlapping rows.\r\n";
			break;
		default;
			exit("I don't know what $l means! Canceling.\r\n");
			break;
	}
}

// ------------------------------------------------------
// Fill the order table

// Set up Lavu API parameters
$lav_query = array(
	config => $lavu_config,
	filt_col => NULL,
	filt_val => NULL,
	filt_min => NULL,
	filt_max => NULL,
	limit => '1000'
);

$keep_columns = array('id', 'order_id', 'opened', 'closed', 'subtotal', 'tax', 'total',
						'server', 'server_id', 'discount', 'cash_paid', 'card_paid',
						'cashier', 'cashier_id', 'guests', 'order_status',
						'reopened_datetime', 'reclosed_datetime', 'void', 'discount_id');
$lav_query['filt_col'] = 'opened';

// Loop through dates, five days at a time, and load to the dB
$date = $startDate;
while ( $date < $endDate ) {
	// Set the Lavu filter min date
	$lav_query['filt_min'] = $date->format('Y-m-d');
	
	// Add five days
	$date->add(date_interval_create_from_date_string('5 days'));
	
	// Don't import beyond the end date
	if ( $date > $endDate ) { $date = $endDate; }
	
	// Set the Lavu filter end date (exclusive)
	$lav_query['filt_max'] = $date->format('Y-m-d');
	
	$rows = import_table('orders', $keep_columns, $lav_query, $db_handle, FALSE, TRUE);
	if ($rows === $lav_query['limit']) { echo " ** WARNING: limit <= the number of rows returned **\r\n"; }
}
?>