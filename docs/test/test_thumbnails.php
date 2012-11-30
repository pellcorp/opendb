<?php
/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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

include_once('../../lib/phpthumb/phpthumb.class.php');
?>
<html>
<head>
<title>Testing Thumbnails</title>
</head>
<body>
<table cellspacing=5 cellpadding=5><tr><td>
<img src="./itemcache/5.cache.jpeg">
</td>
<?php
if(@$_GET['op'] == 'generate')
{
	$phpThumb = new phpThumb();
	$phpThumb->setParameter('config_error_die_on_error', FALSE);
	$phpThumb->setParameter('config_allow_src_above_docroot', TRUE); 
	
	$phpThumb->setParameter('h', 75);

	// input and output format should match			
	$phpThumb->setParameter('f', 'jpeg');
	$phpThumb->setParameter('config_output_format', 'jpeg');
	$phpThumb->setParameter('config_cache_directory', '/tmp');
	
	$phpThumb->setSourceFilename('./itemcache/5.cache.jpeg');
				
	// generate & output thumbnail
	if ($phpThumb->GenerateThumbnail() && $phpThumb->RenderToFile('./itemcache/5_THUMB.cache.jpeg')) 
	{
		echo '<td>Thumbnail generated<br />';
		echo('<img src="./itemcache/5_THUMB.cache.jpeg"></td>');
	}
	else
	{
		echo("<td><pre>");
		print_r($phpThumb->debugmessages);
		echo("</pre></td>");
	}
}
else
{
	echo("<td><a href=\"${_SERVER['PHP_SELF']}?op=generate\">Generate Thumbnail</a></td>");
}
echo("</tr></table>");

?>
</body>
</html>