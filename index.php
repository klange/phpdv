<?php
/*
    phpDV (PHP directory listing script)

    Copyright 2006-2013 Kevin Lange <klange@dakko.us>

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    PHP Directory Viewer

    Place this somewhere readable by your web server and then place
    an index.php with the following in each directory you want indexed:

    <?php include "/path/to/phpdv.php"; ?>

    Then, link your system icons however you want as follows:
        - Archives [archive.png]
        - Audio files [audio.png]
        - Directories [dir.png]
        - Web files [html.png]
        - Vector graphics [svg.png]
        - Text files [txt.png]
        - Video files [vid.png]
        - Unknown files [unknown.png]
        - "Up one" [up.png]
    And place them in another directory PHP can access, then set the
    $_THUMBPATH variable below to this directory (with trailing slash).

    You may want to append or prepend some text to a directory listing.
    To do this, create a file called `.index_prepend` for text you want
    to place before the listing and `.index_append` for text you want
    to place after the listing in the directories you want to display
    the extra text for. Use HTML formatting in .index_ files.


    DISCLAIMER: This script is about 7 years old as of this writing.
                It is not guaranteed to be scalable, stable, or worth
                using in anyway. There are probably much better,
                professionally-written solutions that could be used
                instead. You have been warned!
*/

// Constants
// This needs to be an absolute path to the icon directory
$_THUMBPATH = "/home/klange/public_html/random/dirth/";
$_ICONSIZE = 32; // Icon size

$TOTAL_HEIGHT = $_ICONSIZE + 4;

function readable_size($bytes) {
	$symbol = array('B', 'KiB', 'MiB', 'GiB', 'TiB',
					'PiB', 'EiB', 'ZiB', 'YiB');

	$exp = 0;
	$converted_value = 0;
	if( $bytes > 0 ) {
		$exp = floor( log($bytes)/log(1024) );
		$converted_value = ( $bytes/pow(1024,floor($exp)) );
	}

	return sprintf( '%.2f '.$symbol[$exp], $converted_value );
}

if (!isset($_GET['th'])) {
	// XXX: Standard execution (display index)
	if (file_exists("favicon.ico")) {
		$tempmeta =  getimagesize("favicon.ico");
		$FAVMETA = "<link rel=\"icon\" type=\"" . $tempmeta['mime'] . 
			"\" href=\"favicon.ico\">";
	} else {
		$FAVMETA = "";
	}
	$dir_true = str_replace("index.php", "", $_SERVER['PHP_SELF']);
	$dir_tree = explode("/",$dir_true);
	array_pop($dir_tree);
	$DIR_NAME = "";
	$dir_path = "";
	foreach ($dir_tree as $dir) {
		$dir_path = $dir_path . $dir . "/";
		$DIR_NAME = $DIR_NAME . "<a class=\"parent\" href=\"$dir_path\">$dir/</a>";
	}
	$DIR_INFO = $_SERVER['HTTP_HOST'] . "<br>powered by phpDV";
	if (file_exists(".index_background")) {
		$background = "background-image: url(\".index_background\");";
	} else {
		$background = "background-color: #FFF;";
	}
	print <<< END
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Index of $dir_path</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
<style type="text/css">
body {
    font-family: Tahoma, sans-serif;
    font-size: 12px;
    $background
}
h1 {
    font-size: 18px;
    font-weight: bold;
}
.file_block {
    float: left;
    padding: 4px;
    margin: 4px;
    width: 250px;
    height: {$TOTAL_HEIGHT}px;
    font-size: 16px;
    vertical-align: middle;
    border: 1px solid #707070;
    background-color: #FFFFFF;
}
span.file_block:hover {
    background-color: #EEEEEE;
    font-weight: bold;
    text-decoration: none;
}
.file_thumb {
    float: left;
    padding: 2px;
    padding-right: 4px;
}
.file_stat {
    font-size: 12px;
}
.spacer {
    width: 100%;
    float: left;
}
.header {
    padding: 4px;
    display: block;
    border: 1px solid #707070;
    background-color: #FFFFFF;
}
.footer {
    padding: 4px;
    margin-bottom: 4px;
    display: block;
    border: 1px solid #707070;
    background-color: #FFFFFF;
}
.dir_info {
    float: right;
    margin-right: 4px;
    display: block;
    color: #777777;
    font-size: 12px;
    text-align: right;
}
.fake_break {
    font-size: 0px;
    line-height: 0px;
}

.parent:link {color: #000000; text-decoration: none;}
.parent:visited {color: #000000; text-decoration: none;}
a.parent:hover {color: #FFF; text-decoration: none; background-color: #000;}

.subdirectory:link {color: #000000; text-decoration: none;}
.subdirectory:visited {color: #000000; text-decoration: none;}
a.subdirectory:hover {color: #888888; text-decoration: underline; }
a.subdirectory:link img, a.subdirectory:visited img { border-style: none }

.file_cont:link {color: #000000; text-decoration: none;}
.file_cont:visited {color: #000000; text-decoration: none;}
a.file_cont:hover {color: #000000; text-decoration: none; }
a.file_cont:link img, a.file_cont:visited img { border-style: none }



</style>
$FAVMETA
</head>
<body>
<div class="dir_info">$DIR_INFO</div>
<h1>Index of $DIR_NAME</h1>

END;

	// Prepend
	if (file_exists(".index_prepend")) {
		print "<div class=\"spacer\"><div class=\"header\">\n";
		print file_get_contents(".index_prepend");
		print "</div></div>\n";
	}
	$myDirectory = opendir("./" . $gal);
	while($entryName = readdir($myDirectory)) {
		$dirArray[] = $entryName;
	}
	closedir($myDirectory);
	sort($dirArray);
	$indexCount	= count($dirArray);
	$directories[] = "..";
	for($index=0; $index < $indexCount; $index++) {
		if (is_dir("$dirArray[$index]") && (substr($dirArray[$index],0,1) != ".")) {
			$value = $dirArray[$index];
			$directories[] = $value;
			unset($dirArray[$index]);
			$dirArray = array_values($dirArray);
			$index = $index - 1;
			$indexCount = $indexCount - 1;
		} elseif (substr($dirArray[$index], 0, 1) == ".") {
			unset($dirArray[$index]);
			$dirArray = array_values($dirArray);
			$index = $index - 1;
			$indexCount = $indexCount - 1;
		}
	}
	$indexCount2 = count($directories);
	for($index=0; $index < $indexCount2; $index++) {
		$dir = $directories[$index];
		if ($dir == "..") {
			if ($dir_true == "/")
				continue;
			print "<a class=\"file_cont\" href=\"..\"><span class=\"file_block\">" .
			   "<span class=\"fake_break\"><br></span>" .
			   "<img src=\"?th=GOUP\" class=\"file_thumb\" alt=\"Go Up\">..<br>" .
			   "<span class=\"file_stat\">(Go up)</span></span></a>\n";
		} else {
			$pret = ucwords(str_replace("_", " ", $dir));
			print "<a class=\"file_cont\" href=\"$dir\"><span class=\"file_block\">" .
			   "<span class=\"fake_break\"><br></span>" .
			   "<img src=\"?th=DIRECTORY\" class=\"file_thumb\" alt=\"*\">$pret<br>" .
			   "<span class=\"file_stat\">Directory - $dir</span></span></a>\n";
		}
	}
	$actual_count = 0;
	for($index=0; $index < $indexCount; $index++) {
		$file = $dirArray[$index];
		// Hide us.
		if (strtolower($file) == "index.php")
			continue;
		$file_sub = substr($file,0,18);
		if (strlen($file) > 18) {
			$file_sub = $file_sub . "...";
		}
		$file_stat = "";
		$size = filesize($file);
		if ($size) {
			$file_stat = $file_stat . readable_size($size);
		}
		$file_stat = $file_stat . " " . mime_content_type($file);
		print "<a class=\"file_cont\" href=\"$file\"><span class=\"file_block\">" .
			"<span class=\"fake_break\"><br></span>" .
			"<img src=\"?th=$file\" class=\"file_thumb\" alt=\"!\">$file_sub<br>" .
			"<span class=\"file_stat\">$file_stat</span></span></a>\n";
		$actual_count++;
	}
	if ($actual_count == 0) {
		print <<<END
<br /><br /><br /><br />
<div width="100%">
<p align="center" style="font-size: 16px; clear: left;">This directory is empty.</p>
</div>
END;
	}
	// Append
	if (file_exists(".index_append")) {
		print "<div class=\"spacer\"><div class=\"footer\">";
		print file_get_contents(".index_append");
		print "</div></div>";
	}
	print <<<END
</body>
</html>
END;
} else {
	// XXX: Thumbnail request
	$file = $_GET['th'];
	$tmp = explode(".",$file);
	$type = strtolower(array_pop($tmp));
	//header("Content-type: image/png");
	error_reporting(E_ERROR);
	if ($file == "DIRECTORY") {
		$im = imagecreatefrompng($_THUMBPATH . "dir.png");
	} else if ($file == "GOUP") {
		$im = imagecreatefrompng($_THUMBPATH . "up.png");
	} else if ((file(".thumbs/" . $file)) and 
		(filemtime(".thumbs/" . $file) > filemtime($file))) {
		$im = imagecreatefrompng(".thumbs/" . $file);
	} else {
		switch ($type) {
			case "png":
				$src = imagecreatefrompng($file);
				break;
			case "jpeg":
			case "jpg":
				$src = imagecreatefromjpeg($file);
				break;
			case "gif":
				$src = imagecreatefromgif($file);
				break;
			case "txt":
				$im = imagecreatefrompng($_THUMBPATH . "txt.png");
				break;
			case "svg":
				$im = imagecreatefrompng($_THUMBPATH . "svg.png");
				break;
			case "avi":
			case "mpg":
			case "mpeg":
			case "wmv":
			case "ogg":
				$im = imagecreatefrompng($_THUMBPATH . "vid.png");
				break;
			case "wav":
			case "mp3":
			case "wma":
				$im = imagecreatefrompng($_THUMBPATH . "audio.png");
				break;
			case "tar":
			case "gz":
			case "bz2":
			case "rar":
			case "zip":
			case "7z":
			case "deb":
				$im = imagecreatefrompng($_THUMBPATH . "archive.png");
				break;
			case "html":
			case "htm":
			case "php":
				$im = imagecreatefrompng($_THUMBPATH . "html.png");
				break;
			default:
				$im = imagecreatefrompng($_THUMBPATH . "unknown.png");
		}
	}
	if (!isset($src)) {
		imagesavealpha($im, true); 
		imagepng($im);
		imagedestroy($im);
	} else {
		$im = imagecreatetruecolor($_ICONSIZE,$_ICONSIZE);
		imagesavealpha($im, true);
		$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
		imagefill($im, 0, 0, $trans);
		$wid = imagesx($src);
		$hei = imagesy($src);
		$_SIZE = $_ICONSIZE;
		$ratio = $wid / $hei;
		$ratio = $wid / $hei;
		if ($ratio > 1) {
			$newsize = $_SIZE / $ratio;
			$offset = ($_SIZE - $newsize) / 2;
			imagecopyresampled($im, $src, 0, $offset, 0, 0, $_SIZE, 
				$_SIZE / $ratio, $wid, $hei);
		} else {
			$newsize = $_SIZE * $ratio;
			$offset = ($_SIZE - $newsize) / 2;
			imagecopyresampled($im, $src, $offset, 0, 0, 0, $_SIZE * $ratio,
				$_SIZE, $wid, $hei);
		}
		imagesavealpha($im, true);
		if (!is_dir(".thumbs")) {
			mkdir(".thumbs");
		}
		imagepng($im, ".thumbs/" . $file);
		imagepng($im);
		imagedestroy($im);
	}
}
