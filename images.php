<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>MyPixelPal - Images</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
<div id="header">
	<h1>MyPixelPal v2.0</h1>
	<h2>&copy;2016-<?php echo date("Y") ?> Marc Ryan</h2>
	<h2>Images</h2>
</div>
<div style="clear: both;"></div>
<hr>

<h3>All Images:</h3>
<?php
$files = array();
$dir = opendir("./images");
while($file = readdir($dir)) {
	$mime_type = mime_content_type("./images/".$file);
	if ($mime_type == "image/png" || $mime_type == "image/gif" || $mime_type == "image/jpeg" || $mime_type == "image/jpg") {
		$files[$file] = filectime("./images/".$file);
	}
}
asort($files);

foreach($files as $file => $filetime) {
	echo "<a href=\"index.php?img=".$file."\"><img src=\"./images/".$file."\" border=0 class=\"recent_image\"></a><br>";
}
?>
</body>
</html>