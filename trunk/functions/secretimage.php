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

	This code is based on the GFX check functionality in PHP-Nuke 7.7
*/

include_once("./functions/config.php");
include_once("./functions/http.php");

function get_secretimage_random_num()
{
    mt_srand ((double)microtime()*1000000);
	$maxran = 1000000;
	$random_num = mt_rand(0, $maxran);
	
	return $random_num;
}

function secretimage($random_num)
{
	$security_hash = get_opendb_config_var('site', 'security_hash');
	
    $datekey = date("F j");
	$rcode = hexdec(md5(get_http_env('HTTP_USER_AGENT') . $security_hash . $random_num . $datekey));
	$code = substr($rcode, 2, 6);

	$image = ImageCreateFromJPEG(_theme_image_src('code_bg.jpg'));
	$text_color = ImageColorAllocate($image, 80, 80, 80);

	header("Cache-control: no-store");
	header("Pragma: no-store");
	header("Expires: 0");
	Header("Content-type: image/jpeg");
	ImageString($image, 5, 12, 2, $code, $text_color);
	ImageJPEG($image, '', 75);
	ImageDestroy($image);
}

/**
	Validate code entered against the generated image number
*/
function is_secretimage_code_valid($gfxcode, $random_num)
{
	$security_hash = get_opendb_config_var('site', 'security_hash');
    
    $datekey = date("F j");
	$rcode = hexdec(md5(get_http_env('HTTP_USER_AGENT') . $security_hash . $random_num . $datekey));
	$code = substr($rcode, 2, 6);
	
	if($code != $gfxcode)
	    return FALSE;
	else
	    return TRUE;
}
?>
