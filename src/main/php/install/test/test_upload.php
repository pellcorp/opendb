<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// get the specific set of variables depending on php version!
if (is_array($_GET) && count($_GET) > 0) {
	$HTTP_VARS = $_GET;
} else if (is_array($_POST) && count($_POST) > 0) {
	$HTTP_VARS = $_POST;
}

if ($HTTP_VARS['op'] == "upload") {
	echo "<h1>File uploaded</h1>";

	if (strlen($_FILES['uploadfile']['name']) > 0) {
		echo "<p>Upload file name is '" . $_FILES['uploadfile']['name'] . "'.";

		if (is_uploaded_file($_FILES['uploadfile']['tmp_name'])) {
			echo "<p>Temporary upload file is '" . $_FILES['uploadfile']['tmp_name'] . "'.";

			$tempname = tempnam(NULL, "csvfile");
			if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $tempname)) {
				echo "<p>Upload file moved to $tempname</p>";

				// Now get rid of it, as we are finished our test.
				unlink($tempname);
			} else
				echo "<p>Upload file NOT moved to $tempname</p>";
		} else
			echo "<p>No temporary upload file found!";
	} else
		echo "<p>No upload file name found!";
} else {
	echo ("\n<h1>Upload</h1>");
	echo ("\n<form name=\"main\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"POST\" enctype=\"multipart/form-data\">");
	echo ("\n<input type=\"hidden\" name=\"op\" value=\"upload\">");
	echo ("<input type=\"file\" class=\"file\" size=\"25\" name=\"uploadfile\">");
	echo ("\n<input type=\"submit\" class=\"submit\" value=\"Upload\">");
	echo ("\n</form>");
}
?>
