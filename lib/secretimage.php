<?php
/*
 Open Media Collectors Database
 Copyright (C) 2001,2013 by Jason Pell

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
include_once("./lib/config.php");
include_once("./lib/http.php");
require_once ("./lib/ODImage.class.php");

function get_secret_image_random_num() {
	mt_srand ( ( double ) microtime () * 1000000 );
	$maxran = 1000000;
	$random_num = mt_rand ( 0, $maxran );
	
	return $random_num;
}

/**
 * TODO - note that date is used as part of the generated code, so if someone tries to
 * register just before a date change, the registration may fail - but its a fairly
 * unlikely occurence.
 *
 * @param unknown_type $random_num
 * @return unknown
 */
function get_secret_image_code($random_num) {
	$security_hash = get_opendb_config_var ( 'site', 'security_hash' );
	
	$datekey = date ( "F j" );
	$rcode = hexdec ( md5 ( get_http_env ( 'HTTP_USER_AGENT' ) . $security_hash . $random_num . $datekey ) );
	$code = substr ( $rcode, 2, 6 );
	
	return $code;
}

/**
 Validate code entered against the generated image number
 */
function is_secret_image_code_valid($gfxcode, $random_num) {
	if (is_numeric ( $gfxcode ) && is_numeric ( $random_num )) {
		$code = get_secret_image_code ( $random_num );
		if ($code != $gfxcode) {
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		return FALSE;
	}
}

function CenterImageString($image, $image_width, $string, $font_size, $y, $color) {
	$text_width = imagefontwidth ( $font_size ) * strlen ( $string );
	$center = ceil ( $image_width / 2 );
	$x = $center - (ceil ( $text_width / 2 ));
	ImageString ( $image, $font_size, $x, $y, $string, $color );
}

function render_secret_image($random_num) {
	$gdImage = new ODImage ( get_opendb_image_type () );
	$gdImage->createImage ( 'code_bg' );
	$image = & $gdImage->getImage ();
	$text_color = ImageColorAllocate ( $image, 80, 80, 80 );
	
	header ( "Cache-control: no-store" );
	header ( "Pragma: no-store" );
	header ( "Expires: 0" );
	CenterImageString ( $image, 100, get_secret_image_code ( $random_num ), 5, 7, $text_color );
	
	$gdImage->sendImage ();
	
	unset ( $gdImage );
}

function render_secret_image_form_field() {
	$random_num = get_secret_image_random_num ();
	$buffer .= "\n<input type=\"hidden\" name=\"gfx_random_number\" value=\"$random_num\">";
	
	$buffer .= "<p class=\"verifyCode\"><label for=\"gfx_code_check\">" . get_opendb_lang_var ( 'verify_code' ) . "</label>" . "<img width=\"120\" height=\"25\" src=\"secretimage.php?op=gfx_code_check&gfx_random_number=$random_num\">" . "<input type=\"text\" class=\"text\" id=\"gfx_code_check\" name=\"gfx_code_check\" size=\"15\" maxlength=\"6\"></p>";
	return $buffer;
}
?>
