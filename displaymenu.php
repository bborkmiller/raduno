<?php

/* Open the menu file */
if (!file_exists("text_menu.txt")) {
	die("*** Menu file not found ***\r\n");
} else {
	$handle = fopen("text_menu.txt", "r");
}

// We'll store the menu data in this array
$menu_body = array();
while(! feof($handle))
	{
	// ltrim to just handle possible extra whitespace at the beginning
	// rtrim to strip off the line feed at the end	
	$line = ltrim(rtrim(fgets($handle)));
	
	// Check for blank lines. Don't do anything with those
	if (strlen($line) > 0) {
		// We'll put info for the new line in here
		$new_line = array();
		// Split line into words
		$words = explode(" ", $line);
	
		// Figure out what kind of line we're looking at
		switch ($words[0]) {
			case "#";
				$new_line["tag"] = "h1";
				break;
			case "##";
				$new_line["tag"] = "h2";
				break;
			case "---";
				$new_line["tag"] = "div";
				break;
			default;
				$new_line["tag"] = "p";
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
			default;
				$trim_line = ltrim(rtrim(strstr($line, " ")));
				//echo ucwords($trim_line) . "</" . $line_type . ">";
				$new_line["content"] = ucwords($trim_line);
		}
		
		$menu_body[] = $new_line;
	} // end if
} // end while

fclose($handle);

// These are the headers/footers for our two HTML files
// The display menu header
$display_header = "<!DOCTYPE html>
<html>
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
	<title></title>
	<link href=\"https://fonts.googleapis.com/css?family=Mada:400,500,900|Vampiro+One\" rel=\"stylesheet\" />
	<link href=\"menu_styles.css\" rel=\"stylesheet\" />
</head>
<body>
<div id=\"header\"><p id=\"hleft\">&bull; Eat In &bull;</p><p id=\"hright\">&bull; Take Out &bull;</p></div>

<div id=\"menu_container\">
<div class=\"section\">\r\n";

// The display menu footer
$display_footer = "</div>

</div><!-- close container div -->

<div id=\"footer\"></div>
</body>
</html>";

// Write the website menu file
$web_menu = "";
foreach ( $menu_body as $menu_item ) {
	// Omit the div
	if ($menu_item["tag"] <> "div") {
		$web_menu .= "\t<".$menu_item["tag"].">";
		$web_menu .= $menu_item["content"];
		$web_menu .= "</".$menu_item["tag"].">";
		}
	$web_menu .= "\n";
}

$handle = fopen('web_menu.html', 'w');
fwrite($handle, $web_menu);
fclose($handle);

// Generate the display menu
$display_menu = "";
$display_menu .= $display_header;
foreach ( $menu_body as $menu_item ) {
	if ($menu_item["tag"] <> "div") {
		$display_menu .= "\t<".$menu_item["tag"].">";
		}
	$display_menu .= $menu_item["content"];
	if ($menu_item["tag"] <> "div") {
		$display_menu .= "</".$menu_item["tag"].">";
		}
	$display_menu .= "\n";
}
$display_menu .= $display_footer;

echo $display_menu;

/* Archive the menu file */
date_default_timezone_set("America/Detroit");
$datetime = date("Ymd-Gi");

$file = "text_menu.txt";
$newfile = "menu_archive/menu_" . $datetime . '.txt';

if (!copy($file, $newfile)) {
    echo "** Failed to archive menu...";
}

?>