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

// This must be first - includes config.php
include_once("include/begin.inc.php");

class ThemeTest extends PHPUnit_Framework_TestCase {
	function testFindThemesList() {
		$themes = get_user_theme_r();
		$this->assertEquals(1, count($themes));
	}

	function testThemeImageSrc() {
		global $_OPENDB_THEME;

		// lets just ensure that the default theme is active
		$this->assertEquals('default', $_OPENDB_THEME);

		$src = theme_image_src('code_bg.png');
		$this->assertEquals('images/code_bg.png', $src);

		$src = theme_image_src('images/site/imdb.gif');
		$this->assertEquals('images/site/imdb.gif', $src);
	}

	function testGetJavascript() {
		$js = get_theme_javascript('admin');
		$this->assertTrue(strpos($js, "javascript/overlibmws/overlibmws_function.js") !== FALSE);

		$js = get_theme_javascript('item_input');
		$this->assertTrue(strpos($js, "javascript/overlibmws/overlibmws_function.js") === FALSE);
	}

	function testGetCss() {
		$csslist = theme_css_file_list('item_display');
		$this->assertEquals(3, count($csslist)); // style, style_ie and item_display should be returned

		$csslist = theme_css_file_list('item_display', 'printable');
		$this->assertEquals(1, count($csslist)); // style_print

		$csslist = theme_css_file_list('item_display', 'no-menu');
		$this->assertEquals(1, count($csslist)); // style_nomenu
	}
}
