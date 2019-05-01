<?php
set_time_limit(360);

$imagedir = "./images";

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>";
echo "<head>";
echo "<title>MyPixelPal Images</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head>";
echo "<body>";
echo "<div id='header'>";
echo "<h1>MyPixelPal v. 1.6</h1>";
echo "<h2>&copy;2008-16 Ed Salisbury</h2>";
echo "</div>";
echo "<table id='main'>";
echo "<tr><td class='box'>";
echo "<h3>All Images:</h3>";

$files = array();
$dir = opendir($imagedir);
while ($file = readdir($dir)) {
	if (substr($file, 0, 1) != ".") {
		$filectime = filectime("$imagedir/$file");
		array_push($files, array('filename' => $file, 'ctime' => $filectime));
	}
}

$num_images = 100;

$files = msort($files, 'ctime');

for ($ii = 0; $ii < count($files); $ii++) {
	$file = $files[$ii]['filename'];
	echo "<a href='index.php?img=$file'><img src='../images/$file' border='0' class='recent_image'></a>";
	if ($ii % 10 == 0)
		echo "<br>";
}

echo "</td></tr></table>";

echo "</body>";
echo "</html>";

// Multi-dimensional sort, by alishahnovin@hotmail.com (from php.net)
function msort($array, $id="id") {
	$temp_array = array();
	while(count($array)>0) {
		$lowest_id = 0;
		$index=0;
		foreach ($array as $item) {
			if ($item[$id] < $array[$lowest_id][$id])
				$lowest_id = $index;
			$index++;
		}
		$temp_array[] = $array[$lowest_id];
		$array = array_merge(array_slice($array, 0, $lowest_id), array_slice($array, $lowest_id+1));
	}
	
	return $temp_array;
}
?>