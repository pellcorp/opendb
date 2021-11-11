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
class RowImportPluginHandler {
	var $itemImportHandler;
	var $importPlugin;
	var $fileHandler;
	var $field_column_r;
	var $field_default_r;
	var $field_initcap_r;
	
	// stores the first error encountered.
	var $_error;

	function __construct(&$itemImportHandler, &$importPlugin, &$fileHandler, $field_column_r, $field_default_r, $field_initcap_r) {
		$this->itemImportHandler = & $itemImportHandler;
		$this->importPlugin = & $importPlugin;
		$this->fileHandler = & $fileHandler;
		$this->field_column_r = $field_column_r;
		$this->field_default_r = $field_default_r;
		$this->field_initcap_r = $field_initcap_r;
	}

	/**
	Will attempt to get the value of the fieldname, via the
	$tokens array and any $fieldname_default.
	*/
	function get_field_value($fieldname, $s_attribute_type, $tokens) {
		if (isset ( $this->field_column_r [$fieldname] ) && is_numeric ( $this->field_column_r [$fieldname] ) && strlen ( $tokens [$this->field_column_r [$fieldname]] ) > 0) {
			// Only support INITCAP of actual tokens imported from CSV/DIF file!!!
			if ($this->field_initcap_r [$fieldname] == 'true' && ! is_array ( $tokens [$this->field_column_r [$fieldname]] ))
				return initcap ( $tokens [$this->field_column_r [$fieldname]] );
			else
				return $tokens [$this->field_column_r [$fieldname]];
		} else if (isset ( $this->field_default_r [$fieldname] )) {
			return $this->field_default_r [$fieldname];
		} else // no $value to return
			return FALSE;
	}
	
	/*
	* Will call read_header() and ignore it, if is_header_row() == FALSE.  Otherwise will call
	* read_row() and ignore it, if $include_header_row == FALSE
	*/
	function handleImport($include_header_row, $s_item_type) {
		// skip the header row if appropriate.
		if ($this->importPlugin->is_header_row () !== TRUE || $include_header_row !== TRUE) {
			$this->importPlugin->read_header ( $this->fileHandler, $this->_error );
		}
		
		while ( ! $this->fileHandler->isEof () && $this->itemImportHandler->isError () != TRUE && ($read_row_r = $this->importPlugin->read_row ( $this->fileHandler, $this->_error )) !== FALSE ) {
			// ensure we have a array that is not empty, or empty except for first element, which is empty.
			// Either no s_item_type restriction applies, or the s_item_type column is the same as
			// the current s_item_type we are processing.
			if ((is_not_empty_array ( $read_row_r ) && (count ( $read_row_r ) > 1 || strlen ( $read_row_r [0] ) > 0)) && (! is_numeric ( $this->field_column_r ['s_item_type'] ) || strlen ( $read_row_r [$this->field_column_r ['s_item_type']] ) == 0 || strcasecmp ( $read_row_r [$this->field_column_r ['s_item_type']], $s_item_type ) === 0)) {
				$this->itemImportHandler->startItem ( $s_item_type );
				
				// Now do the title.
				$title_attr_type_r = fetch_sfieldtype_item_attribute_type_r ( $s_item_type, 'TITLE' );
				$title = $this->get_field_value ( get_field_name ( $title_attr_type_r ['s_attribute_type'], $title_attr_type_r ['order_no'] ), NULL, $read_row_r );
				$this->itemImportHandler->setTitle ( $title );
				
				$results = fetch_item_attribute_type_rs ( $s_item_type, NULL, FALSE );
				if ($results) {
					while ( $item_attribute_type_r = db_fetch_assoc ( $results ) ) {
						// these field types are references to item_instance values, and not true attribute types.
						if ($item_attribute_type_r ['s_field_type'] != 'TITLE' && $item_attribute_type_r ['s_field_type'] != 'STATUSTYPE' && $item_attribute_type_r ['s_field_type'] != 'STATUSCMNT' && $item_attribute_type_r ['s_field_type'] != 'DURATION' && $item_attribute_type_r ['s_field_type'] != 'ITEM_ID') {
							$value = $this->get_field_value ( get_field_name ( $item_attribute_type_r ['s_attribute_type'], $item_attribute_type_r ['order_no'] ), $item_attribute_type_r ['s_attribute_type'], $read_row_r );
							
							if (strlen ( $value ) > 0) {
								if ($item_attribute_type_r ['lookup_attribute_ind'] == 'Y' || $item_attribute_type_r ['multi_attribute_ind'] == 'Y') {
									// row based are comma delimited.
									$values_r = trim_explode ( ',', $value );
								} else {
									$values_r = $value;
								}
								
								$this->itemImportHandler->addAttribute ( $item_attribute_type_r ['s_attribute_type'], $item_attribute_type_r ['order_no'], $values_r );
							} //if(strlen($value)>0)
						}
					}
					db_free_result ( $results );
				} //if($results)
				

				$status_attr_type_r = fetch_sfieldtype_item_attribute_type_r ( $s_item_type, 'STATUSTYPE' );
				$s_status_type = $this->get_field_value ( get_field_name ( $status_attr_type_r ['s_attribute_type'], $status_attr_type_r ['order_no'] ), $status_attr_type_r ['s_attribute_type'], $read_row_r );
				
				$status_cmnt_attr_type_r = fetch_sfieldtype_item_attribute_type_r ( $s_item_type, 'STATUSCMNT' );
				$status_comment = $this->get_field_value ( get_field_name ( $status_cmnt_attr_type_r ['s_attribute_type'], $status_cmnt_attr_type_r ['order_no'] ), $status_cmnt_attr_type_r ['s_attribute_type'], $read_row_r );
				
				$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r ( $s_item_type, 'DURATION' );
				$borrow_duration = $this->get_field_value ( get_field_name ( $duration_attr_type_r ['s_attribute_type'], $duration_attr_type_r ['order_no'] ), $duration_attr_type_r ['s_attribute_type'], $read_row_r );
				
				$this->itemImportHandler->startItemInstance ();
				$this->itemImportHandler->setInstanceStatusType ( $s_status_type );
				$this->itemImportHandler->setInstanceStatusComment ( $status_comment );
				$this->itemImportHandler->setInstanceBorrowDuration ( $borrow_duration );
				$this->itemImportHandler->endItemInstance ();
				
				$this->itemImportHandler->endItem ();
			}
		}
		
		if ($this->itemImportHandler->isError ()) {
			// copy the first error in
			$itemImportHandlerErrors = & $this->itemImportHandler->getRawErrors ();
			if (is_array ( $itemImportHandlerErrors )) {
				$this->_error = $itemImportHandlerErrors [0] ['error'];
			}
			
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function getError() {
		return $this->_error;
	}
}
?>
