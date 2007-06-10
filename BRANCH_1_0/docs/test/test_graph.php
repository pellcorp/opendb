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

header("Content-type: image/jpeg");
$im = ImageCreate(300, 300);
$orange = imagecolorallocate($im, 220, 210, 60);
$black = imagecolorallocate($im, 0, 0, 0);

$string = 'Testing basic GD Support';

$px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
$py     = (imagesy($im) - 7.5) / 2;
imagestring($im, 3, $px, $py, $string, $black);

imagejpeg($im);
imagedestroy($im);
?>