<?php
// Subset our result arrays to just the columns we want
function subset_array(&$full, $key, $cols) {
	$full = array_intersect_key($full, $cols);
}

// Get the data and load it into the database
function import_table ($table, $columns, $lav, $dbh, $delete=FALSE, $report=FALSE) {
	if ($report) {
		echo " -- Starting $table --\r\n";
		echo "   - Limit: " . $lav['limit'] . "\r\n";
		if ($lav['filt_min'] !== NULL) { echo"     " . $lav['filt_min'] . " to " . $lav['filt_max'] . "\r\n"; }
	}

	// Get Lavu data
	$lav['table'] = $table;
	$arrData=xmlstr_to_array(plGetData($lav['config'], $lav['table'], $lav['filt_col'], $lav['file_val'], $lav['filt_min'], $lav['filt_max'], $lav['limit']));
	$arrData=$arrData['row'];

	// Subset the results
	$columns = array_flip($columns);
	array_walk($arrData, 'subset_array', $columns);

	$col_names = array();
	foreach($columns as $k => $v ) {
		$col_names[':'.$k] = $k;
		$columns[$k] = $k;
	}

	// clear the table
	if ($delete) {
		$sql = "DELETE FROM $table";
		$dbh->exec($sql);
		if ($report) { echo "   -- Deleted $table --\r\n"; }
	}

	// Insert the data
	$stmt = "INSERT INTO $table (" .implode(', ', array_keys($columns)) .  ") VALUES (" . implode(', ', array_keys($col_names)) . ")";
// 	if ($report) { echo $stmt . "\r\n"; }
	$insert = $dbh->prepare($stmt);
	
	// Handle weird dates (unclosed order)
	if ( $table === 'orders' ) {
		foreach ($arrData as $row) {
			if ( $row['closed'] === '0000-00-00 00:00:00' ) { $row['closed'] = NULL; }
			if ( $row['reopened_datetime'] === '0000-00-00 00:00:00' ) { $row['reopened_datetime'] = NULL; }
			if ( $row['reclosed_datetime'] === '0000-00-00 00:00:00' ) { $row['reclosed_datetime'] = NULL; }
			$insert->execute($row);
		}
	} else {
		foreach ($arrData as $row) {
			$insert->execute($row);
		}
	}
	
	if ($report) { echo "   -- Finished $table: " . count($arrData) . " rows --\r\n"; }
	
	return count($arrData);
}
?>