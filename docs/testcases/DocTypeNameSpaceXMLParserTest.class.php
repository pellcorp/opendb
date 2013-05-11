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
*/

require_once 'PHPUnit.php';
require_once("./lib/DocTypeNameSpaceXMLParser.class.php");

class DocTypeNameSpaceXMLParserTest extends PHPUnit_TestCase
{
	var $baseDir = './docs/testcases/resources/';
	
	function DocTypeNameSpaceXMLParserTest($name)
	{
		parent::PHPUnit_TestCase($name);
	}

	function testNoNameSpaceParser() {
		$parser = new DocTypeNameSpaceXMLParser();
		if($parser->parseFile($this->baseDir.'FileWithNoNameSpace.xml')) {
			$this->assertEquals('opendb-items', $parser->getDocType());
			$this->assertNull($parser->getNameSpace());
		} else {
			print_r($parser->getErrors());
			$this->assertTrue(false);
		}
	}
	
	function testWithNameSpaceParser() {
		$parser = new DocTypeNameSpaceXMLParser();
		if($parser->parseFile($this->baseDir.'FileWithNameSpace.xml')) {
			$this->assertEquals('Items', $parser->getDocType());
			$this->assertEquals('http://opendb.iamvegan.net/xsd/Items-1.3.xsd', $parser->getNameSpace());
		} else {
			print_r($parser->getErrors());
			$this->assertTrue(false);
		}
	}
}
?>