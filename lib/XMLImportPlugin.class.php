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
class XMLImportPlugin {
	var $_itemImportHandler;

	/**
	 * Import script will inject the ItemImportHandler before using this
	 * class.
	 *
	 * @param unknown_type $itemImportHandler
	 */
	function setItemImportHandler(&$itemImportHandler) {
		$this->_itemImportHandler = & $itemImportHandler;
	}

	function addError($method, $error) {
		$this->_itemImportHandler->addError ( $method, $error );
	}

	function startItem($itemType) {
		$this->_itemImportHandler->startItem ( $itemType );
	}

	function endItem() {
		$this->_itemImportHandler->endItem ();
	}

	function startItemInstance() {
		$this->_itemImportHandler->startItemInstance ();
	}

	function endItemInstance() {
		$this->_itemImportHandler->endItemInstance ();
	}

	function setTitle($title) {
		$this->_itemImportHandler->setTitle ( $title );
	}

	function setInstanceStatusType($statusType) {
		$this->_itemImportHandler->setInstanceStatusType ( $statusType );
	}

	function setInstanceStatusComment($statusComment) {
		$this->_itemImportHandler->setInstanceStatusComment ( $statusComment );
	}

	function setInstanceBorrowDuration($borrowDuration) {
		$this->_itemImportHandler->setInstanceBorrowDuration ( $borrowDuration );
	}

	function addAttribute($attributeType, $orderNo, $attributeVal) {
		$this->_itemImportHandler->addAttribute ( $attributeType, $orderNo, $attributeVal );
	}
}
?>
