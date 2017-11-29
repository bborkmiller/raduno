<?php
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
	echo $e->getMessage();
}

// Set up Lavu API parameters
$lav_query = array(
	config => $lavu_config,
	filt_col => NULL,
	filt_val => NULL,
	filt_min => NULL,
	filt_max => NULL,
	limit => 50
);

// ------------------------------------------------------
// Fill the menu_group table
$keep_columns = array('id', 'menu_id', 'group_name');
$rows = import_table('menu_groups', $keep_columns, $lav_query, $db_handle, TRUE, TRUE);

// ------------------------------------------------------
// Fill the menu_category table
$keep_columns = array('id', 'menu_id', 'group_id', 'name', 'description', 'active');
$rows = import_table('menu_categories', $keep_columns, $lav_query, $db_handle, TRUE, TRUE);

// ------------------------------------------------------
// Fill menu_items table
$keep_columns = array('id', 'category_id', 'menu_id', 'name', 'price', 'active');
$lav_query['limit'] = 1000;
$rows = import_table('menu_items', $keep_columns, $lav_query, $db_handle, TRUE, TRUE);

?>