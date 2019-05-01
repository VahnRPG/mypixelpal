<?php
$imagedir = "./images";
$paldir = "./palettes";

$palfile = '';
$num_colors = '';
$pal = array();
$new_palette_name = '';
$err = '';
$script = 'pal_editor.php';

if (@$_REQUEST['palette'])
	$palfile = $_REQUEST['palette'];

if (@$_POST['submit'] && $_REQUEST['submit'] != 'Load') {
	$num_colors = $_POST['last_entry'];
	$new_palette_name = strip_name($_POST['new_palette_name']);

	for ($ii = 0; $ii < $num_colors; $ii++) {
		$key = $_POST['key_'.$ii];
		$value = $_POST['value_'.$ii];
		if ($value != '') {
			array_push($pal, array($key, $value));
		}
	}
	if (!file_exists("$paldir/$new_palette_name")) {
		save_palette("$paldir/$new_palette_name", $pal);
		$err = "Saved new palette $new_palette_name";
		$new_palette_name = "";
	}
	else {
		$err = "Permission Denied - please choose another name";
		$new_palette_name = "";
	}
}

if ($palfile) {
	if ($palfile == "(new)") {
		$pal = array();
	}
	else {
		$pal = load_palette("$paldir/$palfile");
	}
}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>";
echo "<head>";
echo "<script src='pal_editor.js' type='text/javascript'></script>";
echo "<title>MyPixelPal - Palette Editor</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head>";
echo "<body>";
echo "<div id='header'>";
echo "<h1>MyPixelPal v. 1.6</h1>";
echo "<h1>Palette Editor</h1>";
echo "<h2>&copy;2008-16 Ed Salisbury</h2>";
echo "</div>";
echo "<table id='main'>";
echo "<tr><td class='box'>";
echo "<table width='100%'><tr><td>";
echo "<form method='get' action='$script'>";
echo "Load Palette: <select name='palette'>";
echo "<option selected='selected'>(new)</option>";

$files = scandir($paldir);
foreach ($files as $file) {
	if (substr($file, 0, 1) != ".") {
		if ($palfile == $file) {
			echo "<option selected='selected'>$file</option>";
		}
		else {
			echo "<option>$file</option>";
		}
	}
}
echo "</option>";
echo "<input type='submit' value='Load'></form>";
echo "</td></tr></table>";
echo "<form method='post' action='$script'>";

echo "<table id='table_form'>";
if ($err) {
	echo "<tr><td colspan='3'>$err</td></tr>";
}
echo "<tr><th>Color Name</th><th>Hex Value</th></tr>";

$row_num = 0;

for ($ii = 0; $ii < count($pal); $ii++) {
	$key = '';
	$value = '';
	if (array_key_exists($ii, $pal)) {
		if (array_key_exists(0, $pal[$ii])) {
			$key = $pal[$ii][0];
		}
		if (array_key_exists(1, $pal[$ii])) {
			$value = $pal[$ii][1];
		}
	}
	echo "<tr id='row_$row_num'>";
	echo "<td><input type='text' name='key_$row_num' value='$key' size='20'></td>";
	echo "<td>#<input type='text' id='value_$row_num' name='value_$row_num' value='$value' size='10' onchange='update_preview(\"$row_num\")'></td>";
	echo "<td id='preview_$row_num' style='background-color: #$value ; padding-left:10px; border: 1px solid black'>&nbsp;</td>";
	echo "<td><input type='button' value='Delete' onclick='delete_row(\"$row_num\")'></td>";
	echo "</tr>";
	$row_num++;
}
echo "<input type='hidden' id='last_entry' name='last_entry' value='$row_num'>";
echo "<tr id='last_row'><td colspan='3'></td><td align='center'><input type='button' value='Add' onclick='add_entry()'></td></tr>";
echo "<tr><td>Palette Name:</td><td><input type='text' name='new_palette_name' value='$new_palette_name'></td></tr>\n";
echo "<tr id='last_row'><td colspan='4' align='center'><input type='submit' name='submit' value='Save'></td></tr>";
echo "</form>";

echo "</table>";

echo "</td></tr></table>";

echo "</body>";
echo "</html>";

function load_palette($file) {
	$pal = array();
	$handle = fopen($file, "r");
	while ($line = fgetcsv($handle)) {
		array_push($pal, array($line[0], $line[1]));
	}
	fclose($handle);
	
	return $pal;
}

function save_palette($file, $pal) {
	$handle = fopen($file, "w");
	for($ii = 0; $ii < count($pal); $ii++) {
		fputcsv($handle, $pal[$ii]);
	}
	fclose($handle);
}

function strip_name($name) {
	$name = stripslashes($name);
	$name = trim($name);
	$name = strip_tags($name);
	$name = preg_replace('/ /', '_', $name);
	$name = preg_replace("/'/", '', $name);
	$name = preg_replace('/"/', '', $name);
	
	return $name;
}
?>