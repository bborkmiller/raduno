<?php

function load_menu($fhandle) {
	// We'll store the menu data in this array
	$menu_body = array();
	while(! feof($fhandle))
		{
		// ltrim to just handle possible extra whitespace at the beginning
		// rtrim to strip off the line feed at the end	
		$line = ltrim(rtrim(fgets($fhandle)));
	
		// Check for blank lines. Don't do anything with those
		if (strlen($line) > 0) {
			// We'll put info for the new line in here
			$new_line = array();
			// Split line into words
			$words = explode(" ", $line);
	
			// Figure out what kind of line we're looking at
			switch ($words[0]) {
				case "#"; // H1 header
					$new_line["tag"] = "h1";
					break;
				case "##"; // H2 header
					$new_line["tag"] = "h2";
					break;
				case "---"; // Column break
					$new_line["tag"] = "div";
					break;
				default;
					// Check for an image
					if (strpos($words[0], ".jpg") !== false || strpos($words[0], ".png") !== false) {
						$new_line["tag"] = "img";
						} else {
						$new_line["tag"] = "p";
						}
				} // end switch
	
			// Set the opening tag and the rest of the line
			switch ($new_line["tag"]) {
				case "p";
					$new_line["content"] = ucfirst(ltrim($line));
					break;
				case "div";
					$new_line["content"] = "</div>

	<div class=\"section\">";
					break;
				case "img";
					$new_line["content"] = $line;
					break;
				default;
					$trim_line = ltrim(rtrim(strstr($line, " ")));
					//echo ucwords($trim_line) . "</" . $line_type . ">";
					$new_line["content"] = ucwords($trim_line);
			}
		
			$menu_body[] = $new_line;
		} // end if
	} // end while

	return $menu_body;
} // end load_menu


function archive_menu($m_file, $m_type) {

	// Get contents of the archive directory
	$arc_dir = scandir('menu_archive/');
	
	// Subset to the menu type (regular or breakfast) we're dealing with
	$m_files = array();
	foreach ($arc_dir as $k => $v) {
		if ( substr($v, 0, strpos($v, "_")) == $m_type ) {
			$m_files[$k] = $v;
		}
	}

	// Sort and take the last (most recent) file
	asort($m_files);
	$prev_menu = end($m_files);

	// Check that the file exists
	if ( file_exists("menu_archive/$prev_menu") ) {
		// Read current menu and previous archived version
		$s_cur_menu  = file_get_contents($m_file);
		$s_prev_menu = file_get_contents("menu_archive/$prev_menu");

		// Compare versions, stripping whitespace, and archive if they don't match
		if ( preg_replace('/\s+/', '', $s_cur_menu) != preg_replace('/\s+/', '', $s_prev_menu) ) {

			$datetime = date("Ymd-Hi");
			$newfile = "menu_archive/" . $m_type . "_" . $datetime . '.txt';

			if (!copy($m_file, $newfile)) {
				echo "** Failed to archive menu...";
			}
		}
	}
}

?>