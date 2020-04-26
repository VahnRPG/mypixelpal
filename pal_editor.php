<?php
function load_palette($file) {
	$results = array();
	if ($fp = fopen($file, "r")) {
		while($line = fgetcsv($fp)) {
			$results[trim($line[0])] = trim($line[1]);
		}
		fclose($fp);
		
		return $results;
	}
	
	return NULL;
}

function save_palette($file, $palette) {
	if ($fp = fopen($file, "w")) {
		foreach($palette as $color_name => $hex_color) {
			fwrite($fp, "\"".addslashes($color_name)."\",".$hex_color."\n");
		}
		fclose($fp);
		
		return true;
	}
	
	return false;
}

function strip_name($name) {
	$name = stripslashes($name);
	$name = trim($name);
	$name = strip_tags($name);
	$name = preg_replace('/ /', "_", $name);
	$name = preg_replace("/'/", "", $name);
	$name = preg_replace('/"/', "", $name);
	
	return $name;
}

$selected_palette = "";
if (isset($_REQUEST["palette"]) && trim($_REQUEST["palette"]) != "") {
	$selected_palette = $_REQUEST["palette"];
}

$palette = array();
$success_msg = "";
$error_msg = "";
$new_palette_name = "";
if (isset($_REQUEST["submit"]) && trim(strtolower($_REQUEST["submit"])) != "load") {
	$palette = array();
	if (isset($_REQUEST["colors"]) && is_array($_REQUEST["colors"]) && $_REQUEST["colors"] != array()) {
		foreach($_REQUEST["colors"] as $color_data) {
			$palette[$color_data["name"]] = str_replace("#", "", $color_data["color"]);
		}
		
		$new_palette_name = strip_name($_REQUEST["new_palette_name"]);
		if (trim($new_palette_name) != "") {
			if (!file_exists("./palettes/".$new_palette_name)) {
				if (save_palette("./palettes/".$new_palette_name, $palette)) {
					$success_msg = "Saved new palette ".$new_palette_name;
				}
				else {
					$error_msg = "Permission Denied - file not saved";
				}
			}
			else {
				$error_msg = "Permission Denied - please choose another name";
			}
			$new_palette_name = "";
		}
		else {
			$error_msg = "Please provide a palette name";
		}
	}
}
else if (trim($selected_palette) != "" && $selected_palette != "(new)") {
	$palette = load_palette("./palettes/".$selected_palette);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>MyPixelPal - Palette Editor</title>
	<link rel="stylesheet" href="style.css">
	<script src="pal_editor.js" type="text/javascript"></script>
</head>
<body>
<div id="header">
	<h1>MyPixelPal v2.0</h1>
	<h2>&copy;2016-<?php echo date("Y") ?> Marc Ryan</h2>
	<h2>Palette Editor</h2>
</div>

<div style="clear: both;"></div>
<hr>

<?php if (trim($success_msg) != ""): ?>
	<span style="color: #090; font-weight: bold;"><?php echo $success_msg ?></span>
<?php elseif (trim($error_msg) != ""): ?>
	<span style="color: #900; font-weight: bold;">ERROR: <?php echo $error_msg ?></span>
<?php endif; ?>
<table id="main">
	<tr>
		<td class="box">
			<table width="100%">
				<tr>
					<td>
						<form action="<?php $php_self ?>" method="get">
						Load Palette:
						<select name="palette">
							<option value="" selected="selected">(new)</option>
							<?php
							$files = scandir("./palettes");
							foreach($files as $file) {
								if (substr($file, 0, 1) != ".") {
									echo "<option value=\"".$file."\"".($selected_palette == $file ? " selected=\"selected\"" : "").">".$file."</option>";
								}
							}
							?>
						</select>
						<input type="submit" name="submit" value="Load">
						</form>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<form action="<?php $php_self ?>" method="post">
			<table id="table_form">
				<tr>
					<th>Color Name</th>
					<th>Hex Value</th>
				</tr>
				<?php
				$count = 0;
				foreach($palette as $color_name => $hex_color) {
					?>
					<tr id="row_<?php echo $count ?>">
						<td><input type="text" id="colors_<?php echo $count ?>_name" name="colors[<?php echo $count ?>][name]" value="<?php echo $color_name ?>" size=20></td>
						<td>#<input type="text" id="colors_<?php echo $count ?>_color" name="colors[<?php echo $count ?>][color]" value="<?php echo $hex_color ?>" size=10 onChange="update_preview('<?php echo $count ?>')"></td>
						<td id="preview_<?php echo $count ?>" style="background-color: #<?php echo $hex_color ?>; padding-left:10px; border: 1px solid black;">&nbsp;</td>
						<td><input type="button" value="Delete" onClick="delete_row('<?php echo $count ?>')"></td>
					</tr>
					<?php
					$count++;
				}
				?>
				<input type="hidden" id="last_entry" name="last_entry" value="<?php echo $count ?>">
				<tr id="last_row">
					<td colspan=3></td>
					<td align="center"><input type="button" value="Add" onClick="add_entry()"></td>
				</tr>
				<tr>
					<td>Palette Name:</td>
					<td><input type="text" name="new_palette_name" value="<?php echo $new_palette_name ?>"></td>
				</tr>
				<tr id="last_row">
					<td colspan=4 align="center"><input type="submit" name="submit" value="Save"></td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
</table>
</body>
</html>