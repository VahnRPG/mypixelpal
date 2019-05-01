<?php
ini_set('memory_limit', '1024M');

$max_width = 512;
$max_height = 512;
$script = $_SERVER['SCRIPT_NAME'];

$imagedir = "./images";
$paldir = "./palettes";

$err = array();
$color_count = array();

$img_file = '';
$print = '';
$the_grid = '';
$cell_height = '';
$cell_width = '';
$font_size = '';
$font_color = '';
$color_num = '';
$total_beads = '';
$the_palette = '';

if ($_GET['grid'] && $_GET['grid'] == 'off') {
	$grid = 'off';
}
else
	$grid = "on";
}

if ($_GET['pegboards'] && $_GET['pegboards'] == 'off') {
	$pegboards = 'off';
}
else {
	$pegboards = "on";
}

if ($_GET['color_numbers'] && $_GET['color_numbers'] == 'on') {
	$color_numbers = 'on';
}
else {
	$color_numbers = "off";
}

if ($_GET['palette']) {
	$palette = $_GET['palette'];
}
else {
	$palette = 'Perler_Colors_-_2016';
	#$palette = 'perler';
}

if ($_GET['print']) {
	$print++;
}

# Load palette
$colors = array();
$pal = fopen("$paldir/$palette", "r");
if ($pal !== false) {
	while($line = fgetcsv($pal)) {
		$key = $line[0];
		$value = $line[1];
		$colors[$key] = $value;
	}
	fclose($pal);
}

if ($imgurl = $_POST['imgurl']) {
	$img_file = tempnam("/tmp", "php");
	$in = fopen($imgurl, "rb");
	$out = fopen($img_file, "wb");
	stream_copy_to_stream($in, $out);
	fclose($in);
	fclose($out);
}
elseif ($_FILES['file']['tmp_name']) {
	$img_file = $_FILES['file']['tmp_name'];
}

if ($img_file) {
	$info = getimagesize($img_file);
	if ($info[0] > $max_width || $info[1] > $max_height) {
		array_push($err, "Image too large(Max is ${max_width}x${max_height})");
		$img_file = "example.png";
	}
	elseif ( get_extension($info['mime']) == "") {
		array_push($err, "Invalid image type");
		$img_file = "example.png";
	}
	else {
		$ext = get_extension($info['mime']);
		$file = $imagedir.'/'.basename($img_file.".$ext");
		rename($img_file, $file);
		$img_file = $file;
	}
}
elseif ($file = $_GET['img']) {
	if ($file != "example.png") {
		$img_file = $imagedir.'/'.$file;
	}
	else {
		$img_file = $file;
	}
}
else {
	$img_file = "example.png";
}

// Verify that the file exists
if (!file_exists($img_file)) {
	echo "File does not exist<br />";
	exit();
}

if ($img_file) {
	$color_rgb = array();
	$color_name = array();

	$x = 0;
	$max_colors = count(array_keys($colors));
	$pal = imagecreate($max_colors, 1);
	foreach(array_keys($colors) as $color) {
		$color_name[$x] = $color;
		$color_rgb[$x] = $colors[$color];
		$red = hexdec(substr($colors[$color],0,2));
		$green = hexdec(substr($colors[$color],2,2));
		$blue = hexdec(substr($colors[$color],4,2));
		$col = imagecolorallocate($pal, $red, $green, $blue);
		imagefilledrectangle($pal, $x, 0, $x, 0, $col);
		$x++;
	}
	$path_info = pathinfo($img_file);
	if ($path_info['extension'] == 'png') {
		$image = imagecreatefrompng($img_file);
	}
	elseif ($path_info['extension'] == 'gif') {
		$image = imagecreatefromgif ($img_file);
	}
	elseif ($path_info['extension'] == 'jpg') {
		$image = imagecreatefromjpeg($img_file);
	}
	elseif ($path_info['extension'] == 'bmp') {
		$image = imagecreatefrombmp($img_file);
	}
}

$width = imagesx($image);
$height = imagesy($image);
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>";
echo "<head>";
echo "<title>MyPixelPal</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head>";
if ($print) {
	echo "<body onLoad='window.print()'>";
}
else {
	echo "<body>";
}

if (!$print) {
	echo "<div id='header'>";
	echo "<h1>MyPixelPal v. 1.6</h1>";
	echo "<h2>&copy;2008-16 Ed Salisbury</h2>";
	echo "<h2><a href='pal_editor.php'>Palette Editor</a></h2>";
	echo "<br>";
	echo "</div>";
}
else {
	echo "<a href='#' onclick='alert(\"Be sure to turn on background image printing in Print Settings to see the image.\"); return false;'>Printing Help</a><br><br>";
}
echo "<table id='main' cellpadding='0' cellspacing='10'>";
echo "<tr>";

$cellsize = 15;
if (isset($_GET['cellsize'])) {
	$cellsize = intval($_GET['cellsize']);
}
if (!$cellsize) {
	$cellsize = round(15 /($width / 15));
}

$the_grid .= "<table id='grid' cellspacing='0' cellpadding='0'>";

for($y = 0; $y < $height; $y++) {
	$the_grid .= "\n<tr>\n";
	for($x = 0; $x < $width; $x++) {
		if ($pegboards == "on") {
			if ((($x / 29) % 2) ==(($y / 29) % 2)) {
				$border_color = '#000';
			}
			else {
				$border_color = '#555';
			}
		}
		else {
			$border_color = '#000';
		}

		if ($grid == 'on') {
			$border = "border: 1px solid $border_color;";
		}
		else {
			$border = "border: 0px;";
		}


		$rgb = imagecolorat($image, $x, $y);
		$color = imagecolorsforindex($image, $rgb);
		if ($color['alpha']) {
			$padding = "padding: ${cellsize}px;";
			#$padding = "width: ${cellsize}px; padding-bottom: ${cellsize}px;";

			#$the_grid .= "<td style='$padding $border $font_size class='trans'><img src='spacer.png' height='1' width='1'></td>\n";
			$the_grid .= "<td style='$padding $border $font_size class='trans'></td>\n";
		}
		else {
			$index = imagecolorclosest($pal, $color['red'], $color['green'], $color['blue']);

			$padding = "padding: ${cellsize}px;";
			#$padding = "width: ${cellsize}px; padding-bottom: ${cellsize}px;";

			if ($color_numbers == 'on') {
				$color_num = $index + 1;
				$font_size = "font-size: ${cellsize}px;";

				$cell_height = "height: 1px;";
				$cell_width = "width: ${cellsize}px;";

				if (get_brightness($color_rgb[$index]) > 130) {
					$font_color = "color: #000;";
				}
				else {
					$font_color = "color: #fff;";
				}

				$padding = "padding: ".$cellsize * .1."px;";
			}

			$the_grid .= "<td style='$cell_height $cell_width $padding $border $font_size $font_color background-color: #".$color_rgb[$index]."' title='".ucwords(preg_replace('/_/', ' ', $color_name[$index]))."'></td>\n";
			if (array_key_exists($index, $color_count)) {
				$color_count[$index]++;
			}
			else {
				$color_count[$index] = 1;
			}
			$total_beads++;
		}
	}
	$the_grid .= "\n</tr>\n";
}
$the_grid .= "</table>";

$the_palette .= "\n<table id='grid' cellspacing='0' cellpadding='0'>";
$the_palette .= "<tr>";

$count = 0;

$padding = $cellsize;

for($ii = 0; $ii < count($colors); $ii++) {
	if ($color_numbers == 'on') {
		$color_num = $ii+1;
		$font_size = "font-size: ${cellsize}px;";

		if (get_brightness($color_rgb[$ii]) > 130) {
			$font_color = "color: #000;";
		}
		else {
			$font_color = "color: #fff;";
		}

		$padding = $cellsize * .3;
	}

	$the_palette .= "<td style='padding: ${padding}px; border: 1px solid #000; $font_color $font_size background-color: #".$color_rgb[$ii]."' title='".ucwords(preg_replace('/_/', ' ', $color_name[$ii]))."'>$color_num</td>\n";

	$count++;

	if ($count >= $width) {
		$the_palette .= "</tr><tr>";
		$count = 0;
	}
}

if ($count != 0) {
	for($ii = 0; $ii < $width-$count; $ii++) {
		$the_palette .= "<td style='padding: ${cellsize}px; border: 1px solid #000;'><img src='spacer.png' width='1' height='1'></td>\n";
	}
}
$the_palette .= "</tr></table>";

if (!$print) {
	echo "<td valign='top' class='box' width='100'>";
	echo "<center>";
	echo "<form enctype='multipart/form-data' action='$script' method='post'>";
	echo "<table id='table_form'>";
	echo "<tr><td>Image File:</td><td><input name='file' type='file' /></td></tr>";
	echo "<tr><td>Image URL:</td><td><input type='text' name='imgurl' size='30' /></td></tr>";
	echo "<tr><td colspan='2' align='center'><input type='submit' value='Submit' /></td></tr>";
	echo "</table>";
	echo "</form>";
	foreach($err as $msg) {
		echo "Error: $msg<br>";
	}
	echo "<br />";
	echo "Original Image:<br>";
	$img_file = basename($img_file);
	
	if ($img_file == "example.png") {
		$img_link = "example.png";
	}
	else {
		$img_link = "images/$img_file";
	}
	
	echo "<img class='preview_image' src='$img_link' width='".$width."' height='".$height."' alt='Original Size'>";
	if ($width < 100) {
		echo "<img class='preview_image' src='$img_link' width='".$width*2."' height='".$height*2."' alt='Double Size'>";
	}
	if ($width < 50) {
		echo "<img class='preview_image' src='$img_link' width='".$width*3."' height='".$height*3."' alt='Triple Size'>";
	}
	echo "<br><br>";
	echo "<a href='$script?img=$img_file'>Permanent Link</a><br />";
	echo "<br>";
	echo "<a href='$script?img=$img_file&print=1&cellsize=$cellsize&pegboards=$pegboards&palette=$palette&color_numbers=$color_numbers&grid=$grid'>Print</a><br />";
	echo "<br>";
	echo "Gridsize: ${width}x$height<br />";
	echo "<br>";

	// Show Color Count
	echo "Color Count:<br />";
	echo "<table id='color_count' cellpadding='0' cellspacing='0'>";
	foreach(array_keys($color_count) as $index) {
		echo "<tr><td style='background-color: #".$color_rgb[$index]."' title='".ucwords(preg_replace('/_/', ' ', $color_name[$index]))."'></td><td>".ucwords(preg_replace('/_/', ' ', $color_name[$index]))."</td><td>$color_count[$index]</td></tr>";
	}
	echo "<tr><td>&nbsp;</td><td>Total Beads</td><td>$total_beads</td></tr>";
	echo "</table>";

	// Change palette
	echo "<br>";
	echo "Choose Palette: ";
	echo "<form method='get' action='$script'>";
	echo "<select name='palette'>";

	$files = scandir($paldir);
	foreach($files as $file) {
		if (substr($file, 0, 1) != ".") {
			if ($palette == $file)
				echo "<option selected='selected'>$file</option>";
			else
				echo "<option>$file</option>";
		}
	}
	echo "</option>";
	echo "<input type='hidden' name='img' value='$img_file'>";
	echo "<input type='hidden' name='cellsize' value='$cellsize'>";
	echo "<input type='hidden' name='pegboards' value='$pegboards'>";
	echo "<input type='hidden' name='grid' value='$grid'>";
	echo "<input type='hidden' name='color_numbers' value='$color_numbers'>";
	echo "<input type='submit' value='OK'</input></form>";

	// Change cell size
	echo "<br>";
	echo "Change cell size: <form method='get' action='$script'><select name='cellsize'>";
	for($ii = 1; $ii < 16; $ii++) {
		if ($cellsize == $ii) {
			echo "<option selected='selected'>$ii</option>";
		}
		else {
			echo "<option>$ii</option>";
		}
	}
	echo "</select>";
	echo "<input type='hidden' name='palette' value='$palette'>";
	echo "<input type='hidden' name='img' value='$img_file'>";
	echo "<input type='hidden' name='pegboards' value='$pegboards'>";
	echo "<input type='hidden' name='grid' value='$grid'>";
	echo "<input type='hidden' name='color_numbers' value='$color_numbers'>";
	echo "<input type='submit' value='OK'</input></form>";
	
	echo "<form method='get' action='$script'>";

	// Show Grid
	echo "<br>";
	echo "<form method='get' action='$script'>";
	echo "Show grid? ";
	if ($grid == "on") {
		echo "<input type='radio' name='grid' value='on' checked> On ";
		echo "<input type='radio' name='grid' value='off'> Off ";
	}
	else {
		echo "<input type='radio' name='grid' value='on'> On ";
		echo "<input type='radio' name='grid' value='off' checked> Off ";
	}
	echo "<input type='hidden' name='color_numbers' value='$color_numbers'>";
	echo "<input type='hidden' name='palette' value='$palette'>";
	echo "<input type='hidden' name='pegboards' value='$pegboards'>";
	echo "<input type='hidden' name='cellsize' value='$cellsize'>";
	echo "<input type='hidden' name='img' value='$img_file'>";
	echo "<input type='submit' value='OK'</input></form>";

	// Show pegboards
	echo "<br>";
	echo "<form method='get' action='$script'>";
	echo "Show pegboards? ";
	if ($pegboards == "on") {
		echo "<input type='radio' name='pegboards' value='on' checked> On ";
		echo "<input type='radio' name='pegboards' value='off'> Off ";
	}
	else {
		echo "<input type='radio' name='pegboards' value='on'> On ";
		echo "<input type='radio' name='pegboards' value='off' checked> Off ";
	}
	echo "<input type='hidden' name='color_numbers' value='$color_numbers'>";
	echo "<input type='hidden' name='palette' value='$palette'>";
	echo "<input type='hidden' name='cellsize' value='$cellsize'>";
	echo "<input type='hidden' name='grid' value='$grid'>";
	echo "<input type='hidden' name='img' value='$img_file'>";
	echo "<input type='submit' value='OK'</input></form>";

	// Show numbers
	echo "<br>";
	echo "<form method='get' action='$script'>";
	echo "Show color numbers? ";
	if ($color_numbers == "on") {
		echo "<input type='radio' name='color_numbers' value='on' checked> On ";
		echo "<input type='radio' name='color_numbers' value='off'> Off ";
	}
	else {
		echo "<input type='radio' name='color_numbers' value='on'> On ";
		echo "<input type='radio' name='color_numbers' value='off' checked> Off ";
	}
	
	echo "<input type='hidden' name='palette' value='$palette'>";
	echo "<input type='hidden' name='cellsize' value='$cellsize'>";
	echo "<input type='hidden' name='pegboards' value='$pegboards'>";
	echo "<input type='hidden' name='grid' value='$grid'>";
	echo "<input type='hidden' name='img' value='$img_file'>";
	echo "<input type='submit' value='OK'</input></form>";
	
	echo "<h3><a href='images.php'>View Recent Images</a></h3>";
	echo "</td>";
	echo "<td valign='top' class='box'>$the_grid <br><h2>Palette($palette)</h2> $the_palette</td>";
	//echo "</tr>";
	//echo "<tr><td colspan='2' class='box'>";

	/*
	$files = array();
	$dir = opendir($imagedir);
	while($file = readdir($dir)) {
		if (substr($file, 0, 1) != ".") {
			$filectime = filectime("$imagedir/$file");
			array_push($files, array('filename' => $file, 'ctime' => $filectime));
		}
	}
	
	$files = msort($files, 'ctime');
	for($ii = count($files); $ii > count($files)-25; $ii--) {
		$file = $files[$ii]['filename'];
		echo "<a href='$script?img=$file'><img src='images/$file' border='0' class='recent_image'></a>";
	}
	
	echo "</td></tr></table>";
	*/
}
else {
	print $the_grid;
	echo "<br><h2>Palette($palette)</h2>";
	print $the_palette;
}

echo "</tr></table><div style='text-align:center;font-size:10px'>Note: Product names, logos, brands, and other trademarks featured or referred to within the mypixelpal.com website are the property of their respective trademark holders.";
echo "</div>";

echo "</body>";
echo "</html>";

function get_extension($mimetype) {
	if ($mimetype == "image/gif") return "gif";
	elseif ($mimetype == "image/jpeg") return "jpg";
	elseif ($mimetype == "image/png") return "png";
	elseif ($mimetype == "image/bmp") return "bmp";
	else return "";
}

// Multi-dimensional sort, by alishahnovin@hotmail.com(from php.net)
function msort($array, $id="id") {
	$temp_array = array();
	while(count($array) > 0) {
		$lowest_id = 0;
		$index=0;
		foreach($array as $item) {
			if ($item[$id] < $array[$lowest_id][$id]) {
				$lowest_id = $index;
			}
			$index++;
		}
		$temp_array[] = $array[$lowest_id];
		$array = array_merge(array_slice($array, 0, $lowest_id), array_slice($array, $lowest_id+1));
	}
	
	return $temp_array;
}

/**********************************************/
/* Fonction:	ImageCreateFromBMP							*/
/* Author:		DHKold													*/
/* Contact:		admin@dhkold.com								*/
/* Date:			The 15th of June 2005						*/
/* Version:		2.0B														*/
/**********************************************/

function ImageCreateFromBMP($filename) {
	//Ouverture du fichier en mode binaire
	if (!$f1 = fopen($filename,"rb")) return false;
	
	//1 : Chargement des ent�tes FICHIER
	$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
	if ($FILE['file_type'] != 19778) return false;
	
	//2 : Chargement des ent�tes BMP
	$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
					'/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
					'/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
	$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
	if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
	$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
	$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
	$BMP['decal'] =($BMP['width']*$BMP['bytes_per_pixel']/4);
	$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
	$BMP['decal'] = 4-(4*$BMP['decal']);
	if ($BMP['decal'] == 4) $BMP['decal'] = 0;
	
	//3 : Chargement des couleurs de la palette
	$PALETTE = array();
	if ($BMP['colors'] < 16777216) {
		$PALETTE = unpack('V'.$BMP['colors'], fread($f1, $BMP['colors']*4));
	}
	
	//4 : Cr�ation de l'image
	$IMG = fread($f1, $BMP['size_bitmap']);
	$VIDE = chr(0);
	
	$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
	$P = 0;
	$Y = $BMP['height']-1;
	while($Y >= 0) {
		$X=0;
		while($X < $BMP['width']) {
			if ($BMP['bits_per_pixel'] == 24)
				$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
			elseif ($BMP['bits_per_pixel'] == 16) {
				$COLOR = unpack("n",substr($IMG,$P,2));
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			elseif ($BMP['bits_per_pixel'] == 8) {
				$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			elseif ($BMP['bits_per_pixel'] == 4) {
				$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
				if (($P * 2) % 2 == 0)
					$COLOR[1] =($COLOR[1] >> 4);
				else
					$COLOR[1] =($COLOR[1] & 0x0F);
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			elseif ($BMP['bits_per_pixel'] == 1) {
				$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
				if (($P*8)%8 == 0) $COLOR[1] = $COLOR[1] >>7;
				elseif (($P*8)%8 == 1) $COLOR[1] =($COLOR[1] & 0x40)>>6;
				elseif (($P*8)%8 == 2) $COLOR[1] =($COLOR[1] & 0x20)>>5;
				elseif (($P*8)%8 == 3) $COLOR[1] =($COLOR[1] & 0x10)>>4;
				elseif (($P*8)%8 == 4) $COLOR[1] =($COLOR[1] & 0x8)>>3;
				elseif (($P*8)%8 == 5) $COLOR[1] =($COLOR[1] & 0x4)>>2;
				elseif (($P*8)%8 == 6) $COLOR[1] =($COLOR[1] & 0x2)>>1;
				elseif (($P*8)%8 == 7) $COLOR[1] =($COLOR[1] & 0x1);
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			else
				return false;
			imagesetpixel($res,$X,$Y,$COLOR[1]);
			$X++;
			$P += $BMP['bytes_per_pixel'];
		}
		$Y--;
		$P+=$BMP['decal'];
	}
	
	//Fermeture du fichier
	fclose($f1);
	
	return $res;
}

function load_palette($file) {
	$pal = array();
	$handle = fopen($file, "r");
	while($line = fgetcsv($handle)) {
		array_push($pal, array($line[0], $line[1]));
	}
	fclose($handle);
	
	return($pal);
}

function get_brightness($hex) {
	// returns brightness value from 0 to 255
	
	// strip off any leading #
	$hex = str_replace('#', '', $hex);
	
	$c_r = hexdec(substr($hex, 0, 2));
	$c_g = hexdec(substr($hex, 2, 2));
	$c_b = hexdec(substr($hex, 4, 2));
	
	return(($c_r * 299) +($c_g * 587) +($c_b * 114)) / 1000;
}
?>