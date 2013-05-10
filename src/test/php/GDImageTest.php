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

include_once("include/begin.inc.php");

include_once("lib/GDImage.class.php");

/**
 * These tests will fail if the GD extension is not installed
 */
class GDImageTest extends PHPUnit_Framework_TestCase {
	function testPngBasicFunctions() {
		$gdImage = new GDImage('png');
		$this->assertEquals('png', $gdImage->getImageType());
		$this->assertEquals('png', $gdImage->getImageExtension());
		$this->assertEquals('image/png', $gdImage->getImageContentType());
		$this->assertTrue(ends_with($gdImage->_getImageSrc('code_bg'), 'images/code_bg.png'));

		$this->assertTrue($gdImage->isImageTypeValid('png'));
		$this->assertFalse($gdImage->isImageTypeValid('xxx'));
	}

	function testJpgBasicFunctions() {
		$gdImage = new GDImage('jpg');
		$this->assertEquals('jpg', $gdImage->getImageExtension());
	}

	/**
	 * Assumes default image type is PNG for this test to work - more on this later!
	 *
	 */
	function testAutoBasicFunctions() {
		$gdImage = new GDImage('auto');
		$this->assertEquals('png', $gdImage->getImageExtension());
	}

	function testGetImageConfig() {
		$gdImage = new GDImage('png');
		$image_config_r = $gdImage->getImageTypeConfig();
		$this->assertEquals('png', $image_config_r['extension']);
	}

	function testImageCreate() {
		$gdImage = new GDImage('png');

		$this->assertFalse($gdImage->createImage('code_bg.png'));
		$this->assertTrue($gdImage->createImage('code_bg'));
		$this->assertTrue(ends_with($gdImage->getImageSrc('code_bg'), 'images/code_bg.png'));
	}

	// run after disabling ImagePNG
	//	function testImageCreateWithoutPng() {
	//		$gdImage = new GDImage();
	//		$this->assertEquals('jpg', $gdImage->getImageType());
	//	}
}
?>
