<?php
ini_set("memory_limit", "1024M");

$MAX_WIDTH = 512;
$MAX_HEIGHT = 512;

function get_extension($mimetype) {
	if ($mimetype == "image/gif") {
		return "gif";
	}
	elseif ($mimetype == "image/jpeg" || $mimetype == "image/jpg") {
		return "jpg";
	}
	elseif ($mimetype == "image/png") {
		return "png";
	}
	
	return "";
}

function load_palette($file) {
	$pal = array();
	$handle = fopen($file, "r");
	while($line = fgetcsv($handle)) {
		array_push($pal, array($line[0], $line[1]));
	}
	fclose($handle);
	
	return $pal;
}

function get_brightness($hex) {
	$hex = str_replace("#", "", $hex);
	
	$c_r = hexdec(substr($hex, 0, 2));
	$c_g = hexdec(substr($hex, 2, 2));
	$c_b = hexdec(substr($hex, 4, 2));
	
	return (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;
}

#$palette = "perler";
$palette = (isset($_REQUEST["palette"]) ? $_REQUEST["palette"] : "Perler_Colors_-_2016");
$cellsize = (isset($_REQUEST["cellsize"]) ? intval($_REQUEST["cellsize"]) : 5);
$rowcol_numbers = (isset($_REQUEST["rowcol_numbers"]) && $_REQUEST["rowcol_numbers"] == "off" ? "off" : "on");
$grid = (isset($_REQUEST["grid"]) && $_REQUEST["grid"] == "off" ? "off" : "on");
$color_numbers = (isset($_REQUEST["color_numbers"]) && $_REQUEST["color_numbers"] == "on" ? "on" : "off");

if (trim($palette) == "" || !file_exists("./palettes/".$palette)) {
	$palette = "Perler_Colors_-_2016";
}
$colors = array();
if ($fp = fopen("./palettes/".$palette, "r")) {
	while($line = fgetcsv($fp)) {
		$colors[trim($line[0])] = trim($line[1]);
	}
	fclose($fp);
}

$img_file = "";
if (isset($_REQUEST["imgurl"]) && trim($_REQUEST["imgurl"]) != "") {
	$img_file = tempnam("/tmp", "php");
	$in = fopen($_REQUEST["imgurl"], "rb");
	$out = fopen($img_file, "wb");
	stream_copy_to_stream($in, $out);
	fclose($in);
	fclose($out);
}
elseif ($_FILES["file"]["tmp_name"]) {
	$img_file = $_FILES["file"]["tmp_name"];
}

$errors = array();
if (trim($img_file) != "") {
	$info = getimagesize($img_file);
	if ($info[0] > $MAX_WIDTH || $info[1] > $MAX_HEIGHT) {
		$errors[] = "Image too large (Max is ".$MAX_WIDTH."x".$MAX_HEIGHT.")";
		$img_file = "example.png";
	}
	elseif (get_extension($info["mime"]) == "") {
		$errors[] = "Invalid image type";
		$img_file = "example.png";
	}
	else {
		$ext = get_extension($info["mime"]);
		$file = "./images/".basename($img_file.".".$ext);
		rename($img_file, $file);
		$img_file = $file;
	}
}
elseif (isset($_REQUEST["img"])) {
	$img_file = $_REQUEST["img"];
	if ($img_file != "example.png" && !preg_match('/images\//', $img_file)) {
		$img_file = "./images/".$img_file;
	}
}
else {
	$img_file = "example.png";
}

if (trim($img_file) == "" || !file_exists($img_file)) {
	$img_file = "example.png";
}
if (trim($img_file) != "") {
	$color_rgb = array();
	$color_name = array();

	$x = 0;
	$max_colors = count(array_keys($colors));
	$pal = imagecreate($max_colors, 1);
	foreach(array_keys($colors) as $color) {
		$color_name[$x] = $color;
		$color_rgb[$x] = $colors[$color];
		$red = hexdec(substr($colors[$color], 0, 2));
		$green = hexdec(substr($colors[$color], 2, 2));
		$blue = hexdec(substr($colors[$color], 4, 2));
		$col = imagecolorallocate($pal, $red, $green, $blue);
		imagefilledrectangle($pal, $x, 0, $x, 0, $col);
		$x++;
	}
	
	$path_info = pathinfo($img_file);
	if ($path_info["extension"] == "png") {
		$image = imagecreatefrompng($img_file);
	}
	elseif ($path_info["extension"] == "gif") {
		$image = imagecreatefromgif ($img_file);
	}
	elseif ($path_info["extension"] == "jpg") {
		$image = imagecreatefromjpeg($img_file);
	}
	else {
		throw new \Exception("Unsupported file type: ", $path_info["extension"]);
	}
}

$red_hex = "#900";
$blue_hex = "#009";

$print = isset($_REQUEST["print"]);
$width = imagesx($image);
$height = imagesy($image);

$the_grid = "
<table id=\"grid\" cellspacing=0 cellpadding=0>";

$total_beads = 0;
$color_count = array();
for($y = 0; $y < $height; $y++) {
	$the_grid .= "
	<tr>
		".($rowcol_numbers == "on" && $grid == "on" ? "<td style=\"font-size: ".$cellsize."px; padding: ".($cellsize * .1)."px; color: #fff; background-color: #333; border: 1px solid ".(($y / 29) % 2 != 0 ? $blue_hex : $red_hex).";\">".($y + 1)."</td>" : "");
	for($x = 0; $x < $width; $x++) {
		$border = "border: 0px;";
		if ($grid == "on") {
			$border = "border: 1px solid ".((($x / 29) % 2) == (($y / 29) % 2) ? $red_hex : $blue_hex).";";
		}
		
		$title = "(".($x + 1).",".($y + 1).") - (".(($x % 29) + 1).",".(($y % 29) + 1).")";
		
		$rgb = imagecolorat($image, $x, $y);
		$color = imagecolorsforindex($image, $rgb);
		if ($color["alpha"]) {
			$the_grid .= "
		<td style=\"padding: ".$cellsize."px; ".$border."\" class=\"trans\" title=\"".$title."\"></td>";
		}
		else {
			$index = imagecolorclosest($pal, $color["red"], $color["green"], $color["blue"]);
			
			$total_beads++;
			if (!isset($color_count[$index])) {
				$color_count[$index] = 0;
			}
			$color_count[$index]++;
			arsort($color_count);
			
			$label = "";
			$font_color = "#fff";
			$padding = $cellsize;
			if ($color_numbers == "on") {
				$label = $index + 1;
				if (get_brightness($color_rgb[$index]) > 130) {
					$font_color = "#000";
				}
				
				$padding = $cellsize * .1;
			}

			$the_grid .= "
		<td style=\"font-size: ".$cellsize."px; padding: ".$padding."px; ".$border." color: ".$font_color."; background-color: #".$color_rgb[$index]."\" class=\"color_".$index."\" title=\"".$title." - ".ucwords(preg_replace('/_/', " ", $color_name[$index]))."\">".$label."</td>";
		}
	}
	$the_grid .= "
	</tr>";
}

if ($rowcol_numbers == "on" && $grid == "on") {
	$the_grid .= "
	</tr>
	<tr>
		<td style=\"font-size: ".$cellsize."px; padding: ".($cellsize * .1)."px;\">&nbsp;</td>";
	for($x = 0; $x < $width; $x++) {
		$the_grid .= "
		<td style=\"font-size: ".$cellsize."px; padding: ".($cellsize * .1)."px; color: #fff; background-color: #333; border: 1px solid ".(($x / 29 % 2) == ($y / 29 % 2) ? $blue_hex : $red_hex).";\"\">".($x + 1)."</td>";
	}
}

$the_grid .= "
	</tr>
</table>";

$the_palette = "
<table id=\"grid\" cellspacing=0 cellpadding=0>
	<tr>";

$count = 0;
for($i = 0; $i < count($colors); $i++) {
	$padding = $cellsize;
	$font_color = "#fff";
	$label = "";
	if ($color_numbers == "on") {
		$label = $i + 1;
		if (get_brightness($color_rgb[$i]) > 130) {
			$font_color = "#000";
		}

		$padding = $cellsize * .3;
	}

	$the_palette .= "
		<td style=\"padding: ".$padding."px; border: 1px solid #000; color: ".$font_color."; font-size: ".$cellsize."px; background-color: #".$color_rgb[$i]."\" title=\"".ucwords(preg_replace('/_/', " ", $color_name[$i]))."\">".$label."</td>";

	if (++$count % $width == 0) {
		$the_palette .= "
	</tr>
	<tr>";
	}
}

if ($count != 0) {
	for($i = 0; $i < $width-$count; $i++) {
		$the_palette .= "
		<td style=\"padding: ".$cellsize."px; border: 1px solid #000;\"><img src=\"spacer.png\" width=1 height=1></td>";
	}
}
$the_palette .= "
	</tr>
</table>";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>MyPixelPal</title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</head>
<?php if ($print): ?>
	<body onLoad="window.print()">
	<a href="javascript:void(0)" onClick="alert('Be sure to turn on background image printing in print Settings to see the image.'); return false;">Printing Help</a><br><br>
<?php else: ?>
<body>
<div id="header">
	<h1>MyPixelPal v2.0</h1>
	<h2>&copy;2016-<?php echo date("Y") ?> Marc Ryan</h2>
	<h2><a href="pal_editor.php">Palette Editor</a></h2>
</div>

<div style="clear: both;"></div>
<hr>
<?php endif; ?>

<table id="main" cellpadding=0 cellspacing=10>
	<tr>
	<?php if (!$print): ?>
		<td valign="top" align="center" class="box" width=350>
			<form enctype="multipart/form-data" action="<?php $php_self ?>" method="post">
			<table id="table_form" width=350>
				<tr>
					<td>Image File:</td>
					<td><input name="file" type="file" /></td>
				</tr>
				<tr>
					<td>Image URL:</td>
					<td><input type="text" name="imgurl" size=30 /></td>
				</tr>
				<tr>
					<td colspan=2 align="center"><input type="submit" name="submit" value="Submit" /></td>
				</tr>
			</table>
			</form>
			<br>
			
			<?php foreach($errors as $error): ?>
				Error: <?php echo $error ?><br>
			<?php endforeach; ?>
			
			<b>Original Image:</b><br>
			<?php
			$img_link = basename($img_file);
			if ($img_link != "example.png" && !preg_match('/images\//', $img_link)) {
				$img_link = "./images/".$img_file;
			}
			?>
			<img src="<?php echo $img_file ?>" width="<?php echo $width ?>" height="<?php echo $height ?>" class="preview_image" alt="Original Size">
			<?php if ($width < 100): ?>
				<img src="<?php echo $img_file ?>" width="<?php echo ($width * 2) ?>" height="<?php echo ($height * 2) ?>" class="preview_image" alt="Double Size">
			<?php endif; ?>
			<?php if ($width < 50): ?>
				<img src="<?php echo $img_file ?>" width="<?php echo ($width * 3) ?>" height="<?php echo ($height * 3) ?>" class="preview_image" alt="Triple Size">
			<?php endif; ?>
			<br><br>
			
			<a href="index.php?img=<?php echo $img_file ?>">Permanent Link</a><br><br>
			<a href="index.php?print=1&img=<?php echo $img_file ?>&palette=<?php echo $palette ?>&cellsize=<?php echo $cellsize ?>&rowcol_numbers=<?php echo $rowcol_numbers ?>&grid=<?php echo $grid ?>&color_numbers=<?php echo $color_numbers ?>">Print</a><br><br>
			Grid Size: <?php echo $width ?> x <?php echo $height ?><br>
			Boards: <?php echo ceil($width / 29) ?> x <?php echo ceil($height / 29) ?> = <?php echo (ceil($width / 29) * ceil($height / 29)) ?> boards<br>
			Final Size: <?php echo ceil($width / 29) * 5.7 ?>" x <?php echo ceil($height / 29) * 5.7 ?>"<br><br>
			
			<b>Color Count:</b><br>
			<table id="color_count" cellpadding=0 cellspacing=0>
				<?php foreach($color_count as $index => $count): ?>
					<tr>
						<td style="text-align: center; color: <?php echo (get_brightness($color_rgb[$index]) > 130 ? "#000" : "#fff") ?>; background-color: #<?php echo $color_rgb[$index] ?>;" title="<?php echo ucwords(preg_replace('/_/', " ", $color_name[$index])) ?>"><?php echo ($color_numbers == "on" ? $index + 1 : "") ?></td>
						<td><?php echo ucwords(preg_replace('/_/', " ", $color_name[$index])) ?></td>
						<td><?php echo $count ?></td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td>&nbsp;</td>
					<td>Total Beads</td>
					<td><?php echo $total_beads ?></td>
				</tr>
			</table>
			<br><br>
			
			<b>Options:</b><br>
			<form method="get" action="<?php $php_self ?>">
			<input type="hidden" name="img" value="<?php echo $img_file ?>">
			<table id="options" cellpadding=0 cellspacing=0>
				<tr>
					<td colspan=2>Choose Palette:</td>
				</tr>
				<tr>
					<td colspan=2>
						<select name="palette">
							<?php $files = scandir("./palettes"); ?>
							<?php foreach($files as $file): ?>
								<?php if (substr($file, 0, 1) != "."): ?>
									<option value="<?php echo $file ?>"<?php echo ($palette == $file ? " selected=\"selected\"" : "") ?>><?php echo $file ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Change Cell Size:</td>
					<td>
						<select name="cellsize">
							<?php for($i = 1; $i < 6; $i++): ?>
								<option value="<?php echo $i * 5 ?>"<?php echo ($cellsize == $i * 5 ? " selected=\"selected\"" : "") ?>><?php echo $i * 5 ?></option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Show Rows / Cols?</td>
					<td>
						<input type="radio" name="rowcol_numbers" value="on"<?php echo ($rowcol_numbers == "on" ? " checked" : "") ?>> On
						<input type="radio" name="rowcol_numbers" value="off"<?php echo ($rowcol_numbers == "off" ? " checked" : "") ?>> Off
					</td>
				</tr>
				<tr>
					<td>Show Grid?</td>
					<td>
						<input type="radio" name="grid" value="on"<?php echo ($grid == "on" ? " checked" : "") ?>> On
						<input type="radio" name="grid" value="off"<?php echo ($grid == "off" ? " checked" : "") ?>> Off
					</td>
				</tr>
				<tr>
					<td>Show Color Numbers?</td>
					<td>
						<input type="radio" name="color_numbers" value="on"<?php echo ($color_numbers == "on" ? " checked" : "") ?>> On
						<input type="radio" name="color_numbers" value="off"<?php echo ($color_numbers == "off" ? " checked" : "") ?>> Off
					</td>
				</tr>
				<tr>
					<td colspan=2 align="center">
						<input type="submit" name="submit" value="Submit">
					</td>
				</tr>
			</table>
			</form>
			
			<h3><a href="images.php">View Recent Images</a></h3>
		</td>
		
		<td valign="top" class="box">
			<?php echo $the_grid ?><br>
			
			<h2>Palette (<?php echo $palette ?>)</h2>
			<?php echo $the_palette ?>
		</td>
	<?php else: ?>
		<?php echo $the_grid ?><br>
		
		<h2>Palette (<?php echo $palette ?>)</h2>
		<?php echo $the_palette ?>
	<?php endif; ?>
	</tr>
</table>

<div style="text-align: center; font-size: 10px">
	Note: Product names, logos, brands, and other trademarks featured or referred to within the mypixelpal.com website are the
	property of their respective trademark holders.
</div>
</body>
</html>