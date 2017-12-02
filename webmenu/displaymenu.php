<?php

include 'menu_func.php';

date_default_timezone_set("America/Detroit");

// These are the headers/footers for our HTML files
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

/* Open the menu file */
$menu_file = "text_menu.txt";
if (!file_exists($menu_file)) {
	die("*** Menu file not found ***\r\n");
} else {
	$handle = fopen($menu_file, "r");
}

// Read the menu file and load it into an array
$menu_body = load_menu($handle);

fclose($handle);

// Write the website menu file
$web_menu = "";
foreach ( $menu_body as $menu_item ) {
	// Omit the div and img
	$skip_tags = array("div", "img");
	if ( ! in_array( $menu_item["tag"], $skip_tags) ) {
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
	switch ($menu_item["tag"]) {
		case "div"; // do nothing
			break;
		case "img";
			$display_menu .= "\t" . '<img src="';
			break;
		default;
			$display_menu .= "\t<".$menu_item["tag"].">";
	}
	$display_menu .= $menu_item["content"];
	switch ($menu_item["tag"]) {
		case "div"; // do nothing
			break;
		case "img";
			$display_menu .= '">';
			break;
		default;
			$display_menu .= "</".$menu_item["tag"].">";
	}
	$display_menu .= "\n";
}
$display_menu .= $display_footer;

// Write out the menu to the browser
echo $display_menu;

// Archive the menu file
archive_menu($menu_file, "menu");
?>