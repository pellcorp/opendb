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

require_once 'PHPUnit.php';
require_once("./functions/GDImage.class.php");

class GDImageTest extends PHPUnit_TestCase
{
	var $baseDir = './docs/testcases/resources/';
	
	function GDImageTest($name)
	{
		parent::PHPUnit_TestCase($name);
	}
	
	function testPngBasicFunctions() {
		$gdImage = new GDImage('png');
		$this->assertEquals('png', $gdImage->getImageType());
		$this->assertEquals('png', $gdImage->getImageExtension());
		$this->assertEquals('image/png', $gdImage->getImageContentType());
		$this->assertEquals('./images/code_bg.png', $gdImage->_getImageSrc('code_bg'));
		
		$this->assertEquals(TRUE, $gdImage->isImageTypeValid('png'));
		$this->assertEquals(FALSE, $gdImage->isImageTypeValid('xxx'));
		
		print_r($gdImage->getErrors());
	}
	
	function testJpgBasicFunctions() {
		$gdImage = new GDImage('jpg');
		$this->assertEquals('jpg', $gdImage->getImageExtension());
		
		print_r($gdImage->getErrors());
	}
	
	function testGetImageConfig() {
		$gdImage = new GDImage('png');
		$image_config_r = $gdImage->getImageTypeConfig();
		$this->assertEquals('png', $image_config_r['extension']);
		
		print_r($gdImage->getErrors());
	}
	
	function testImageCreate() {
		$gdImage = new GDImage('png');
		
		$this->assertEquals(FALSE, $gdImage->createImage('code_bg.png'));
		
		$this->assertEquals(TRUE, $gdImage->createImage('code_bg'));
		$this->assertEquals('./images/code_bg.png', $gdImage->getImageSrc());
		
		print_r($gdImage->getErrors());
	}
	
	// run after disabling ImagePNG
//	function testImageCreateWithoutPng() {
//		$gdImage = new GDImage();
//		$this->assertEquals('jpg', $gdImage->getImageType());
//	}
}
?>