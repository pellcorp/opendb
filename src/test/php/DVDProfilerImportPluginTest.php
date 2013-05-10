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

// TODO - how do we get the target directory for tests, this is dodgy!
define('__OPENDB_BASEDIR__', dirname(dirname(dirname(dirname(__FILE__)))) . '/target');

include_once("lib/WrapperFileHandler.class.php");
include_once("lib/XMLImportPluginHandler.class.php");
include_once("lib/import/DVDProfilerImportPlugin.class.php");
include_once("lib/fileutils.php");

class DVDProfilerImportPluginTest extends PHPUnit_Framework_TestCase {
	function testXMLParse() {
		$plugin = new DVDProfilerImportPlugin();
		$importHandler = new TestItemImportHandler();

		$plugin->setItemImportHandler($importHandler);

		$f = file_open("test-classes/DVDProfilerCollection.xml", 'rb');
		if ($f) {
			$fileHandler = new WrapperFileHandler($f);

			$xmlHandler = new XMLImportPluginHandler($plugin, $fileHandler);
			if ($xmlHandler->handleImport()) {
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

class TestItemImportHandler {
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
