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

require_once("./lib/XMLImportPluginHandler.class.php");
require_once("./lib/StringFileHandler.class.php");
include_once(dirname(__FILE__)."lib/XMLImportPlugin.class.php");

class XMLImportPluginHandlerTest extends PHPUnit_TestCase
{
	var $baseDir = './docs/testcases/resources/';
	
	function XMLImportPluginHandlerTest($name)
	{
		parent::PHPUnit_TestCase($name);
	}
	
	function testXPath() {
		$xml = 
		"<Collection>
  			<DVD>
		    	<ProfileTimestamp>2007-05-19T23:03:06.000Z</ProfileTimestamp>
		    	<ID>012236115007</ID>
			    <MediaTypes>
			      <DVD>True</DVD>
			      <HDDVD>False</HDDVD>
			      <BluRay>False</BluRay>
			    </MediaTypes>
		    </DVD>
		</Collection>";
		
		$handler = new StringFileHandler($xml);
		$implortPlugin = new TestImportPlugin();
		
		$importHandler = new XMLImportPluginHandler($implortPlugin, $handler);
		if($importHandler->handleImport()) {
			$this->assertTrue(true, "XML Successful");
			
			$this->assertEquals(
				8,
				count($implortPlugin->getStartElementXPaths()),
				'');
			
			$startXPaths = $implortPlugin->getStartElementXPaths();
			
			$this->assertEquals("/Collection", $startXPaths[0], 'start xpath 1');
			$this->assertEquals("/Collection/DVD", $startXPaths[1], 'start xpath 2');
			$this->assertEquals("/Collection/DVD/ProfileTimestamp", $startXPaths[2], 'start xpath 3');
			$this->assertEquals("/Collection/DVD/ID", $startXPaths[3], 'start xpath 4');
			$this->assertEquals("/Collection/DVD/MediaTypes", $startXPaths[4], 'start xpath 5');
			$this->assertEquals("/Collection/DVD/MediaTypes/DVD", $startXPaths[5], 'start xpath 6');
			$this->assertEquals("/Collection/DVD/MediaTypes/HDDVD", $startXPaths[6], 'start xpath 7');
			$this->assertEquals("/Collection/DVD/MediaTypes/BluRay", $startXPaths[7], 'start xpath 8');
				
			$this->assertEquals(
				8,
				count($implortPlugin->getEndElementXPaths()),
				'');
			
			$endXPaths = $implortPlugin->getEndElementXPaths();
			
			$this->assertEquals("/Collection/DVD/ProfileTimestamp", $endXPaths[0], 'end xpath 1');
			$this->assertEquals("/Collection/DVD/ID", $endXPaths[1], 'end xpath 2');
			$this->assertEquals("/Collection/DVD/MediaTypes/DVD", $endXPaths[2], 'end xpath 3');
			$this->assertEquals("/Collection/DVD/MediaTypes/HDDVD", $endXPaths[3], 'end xpath 4');
			$this->assertEquals("/Collection/DVD/MediaTypes/BluRay", $endXPaths[4], 'end xpath 5');
			$this->assertEquals("/Collection/DVD/MediaTypes", $endXPaths[5], 'end xpath 6');
			$this->assertEquals("/Collection/DVD", $endXPaths[6], 'end xpath 7');
			$this->assertEquals("/Collection", $endXPaths[7], 'end xpath 8');
			
		} else {
			$this->fail("XML Parser failed");
		}
	}
}

class TestImportPlugin extends XMLImportPlugin {
	var $_startXpaths = array();
	var $_endXpaths = array();
	
	function TestImportPlugin() {
		parent::XMLImportPlugin();
	}
	
	function start_element($xpath, $name, $attribs, $pcdata) {
		$this->_startXpaths[] = $xpath;
	}
	
	function end_element($xpath, $name) {
		$this->_endXpaths[] = $xpath;
	}
	
	function getStartElementXPaths() {
		return $this->_startXpaths;
	}
	
	function getEndElementXPaths() {
		return $this->_endXpaths;
	}
}
?>