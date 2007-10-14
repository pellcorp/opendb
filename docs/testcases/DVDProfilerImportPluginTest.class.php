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

// TODO - add tests

require_once 'PHPUnit.php';

include_once("./functions/WrapperFileHandler.class.php");
include_once("./functions/XMLImportPluginHandler.class.php");
include_once("./import/DVDProfilerImportPlugin.class.php");



class DVDProfilerImportPluginTest extends PHPUnit_TestCase
{
	function DVDProfilerImportPluginTest($name) {
		parent::PHPUnit_TestCase($name);
	}
	
	function testXMLParse() {
		$plugin =& new DVDProfilerImportPlugin();
		$importHandler =& new TestItemImportHandler();
		
		$plugin->setItemImportHandler($importHandler);
		
		$f = fopen("./docs/testcases/resources/DVDProfilerCollection.xml", 'rb');
		if($f) {
			$fileHandler = new WrapperFileHandler($f);
			
			$xmlHandler = new XMLImportPluginHandler($plugin, $fileHandler);
			if($xmlHandler->handleImport()) {
				$this->assertEquals(4, $importHandler->getItemCount());
			} else {
				$this->fail("XML Parser failed");
			}
			
			fclose($f);
			
		} else {
			$this->fail("Could not open DVDProfilerCollection.xml");			
		}
	}
}

class TestItemImportHandler
{
	var $__items = array();
	
	function TestItemImportHandler() {
	}
	
	function getItemCount() {
		return count($this->__items);
	}
	
	function addError($method, $error) {
	}
	
	function startItem($itemType) {
		$this->__items[] = $itemType;
	}
	
	function endItem() {
	}
	
	function startItemInstance() {
	}
	
	function endItemInstance() {
	}
	
	function setTitle($title) {
	}
	
	function setInstanceStatusType($statusType) {
	}
	
	function setInstanceStatusComment($statusComment) {
	}
	
	function setInstanceBorrowDuration($borrowDuration) {
	}
	
	function addAttribute($attributeType, $orderNo, $attributeVal) {
	}
}
?>