<?php
/* 	
	OpenDb Media Collector Database
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
include_once("./lib/utils.php");
include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/install.php");

/**
* This class is designed to manage the maintenance of a single table.  
* 
* This class will NOT create the table, its assumed that was done 
* previously with a script.
* 
* The basic idea is that this class's _handleRow method will be called 
* for each row of CSV data from the file, used to populate the table.  
* The subclass is responsible for working out whether to insert/update/delete 
* the appropriate records, and to keep track of such an action.
*/
class Install_Table {
	var $_header_row = NULL;
	var $_raw_header_row = NULL;
	var $_table_name = NULL;
	var $_insert_count = 0;
	var $_update_count = 0;
	var $_delete_count = 0;
	var $_processed_count = 0;
	var $_rowcount = 0;
	var $_begin_row = 0;
	var $_end_row = NULL;

	/**
	*/
	function __construct($table) {
		$this->_table_name = $table;
		
		if (! check_opendb_table ( $this->_table_name )) {
			$this->doInstallTable ();
		}
	}

	/**
		indicates what type of installation this class supports, currently only 
		one install type is supported at the moment - 'Install_Table'
	*/
	function getInstallType() {
		return 'Install_Table';
	}

	function doInstallTable() {
	}

	function getLastUpdated() {
		$query = "SELECT UNIX_TIMESTAMP(MAX(update_on)) as update_on FROM " . $this->_table_name;
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$found = db_fetch_assoc ( $result );
			db_free_result ( $result );
			if ($found !== FALSE)
				return $found ['update_on'];
		}
		
		//else
		return FALSE;
	}

	function getRecordCount() {
		$query = "SELECT COUNT('x') as count FROM " . $this->_table_name;
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$found = db_fetch_assoc ( $result );
			db_free_result ( $result );
			if ($found !== FALSE)
				return $found ['count'];
		}
		
		//else
		return FALSE;
	}

	function getInstallTable() {
		return $this->_table_name;
	}

	function getInsertCount() {
		return $this->_insert_count;
	}

	function getUpdateCount() {
		return $this->_update_count;
	}

	function getDeleteCount() {
		return $this->_delete_count;
	}

	function getProcessedCount() {
		return $this->_processed_count;
	}

	function getRowCount() {
		return $this->_rowcount;
	}

	function addError($error, $row, $detail = NULL) {
		$this->_errors [] = array (
				'error' => $error,
				'rowcount' => $this->_rowcount,
				'row' => $row,
				'details' => $detail );
	}

	function getErrors() {
		return $this->_errors;
	}

	function setRowRange($begin, $end) {
		$this->_begin_row = $begin;
		$this->_end_row = $end;
	}

	/**
		@return
			__UPDATE__
			__UPDATE_FAILED__
			__INSERT__
			__INSERT_FAILED__
			__DELETE__
			__DELETE_FAILED__
	*/
	function handleRow($row_data, &$error) {
		return FALSE;
	}

	function _handleRow($row) {
		$this->_rowcount ++;
		
		if (is_array ( $row ) && count ( $row ) > 0) {
			if ($this->_header_row == NULL) {
				for($i = 0; $i < count ( $row ); $i ++) {
					$this->_raw_header_row = $row;
					
					// process it, so that we replace spaces with underscores in any of the names.
					for($j = 0; $j < count ( $this->_raw_header_row ); $j ++) {
						$this->_raw_header_row [$j] = strtolower ( preg_replace ( "/[ \n\r\t]+/i", "_", trim ( $this->_raw_header_row [$j] ) ) );
					}
					
					// if a column mapping is provided.
					if (isset ( $this->_column_mappings [$row [$i]] ))
						$this->_header_row [$i] = $this->_column_mappings [$row [$i]];
					else
						$this->_header_row [$i] = $row [$i];
				}
				
				return TRUE;
			} else {
				if (count ( $this->_raw_header_row ) == count ( $row )) {
					if ($this->_rowcount >= $this->_begin_row) {
						$this->_processed_count ++;
						
						//convert $row to use index names matching the header column names
						for($j = 0; $j < count ( $row ); $j ++) {
							$row_data [$this->_raw_header_row [$j]] = $row [$j];
						}
						
						$returnVal = $this->handleRow ( $row_data, $error );
						if ($returnVal === FALSE) {
							$this->addError ( 'Unknown Error', $row, $error );
							return FALSE;
						} else if ($returnVal === '__INSERT__') {
							$this->_insert_count ++;
							return TRUE;
						} else if ($returnVal === '__UPDATE__') {
							$this->_update_count ++;
							return TRUE;
						} else if ($returnVal === '__DELETE__') {
							$this->_delete_count ++;
							return TRUE;
						} else if ($returnVal === '__INSERT_FAILED__') {
							$this->addError ( 'Insert Failed', $row, $error );
							return FALSE;
						} else if ($returnVal === '__UPDATE_FAILED__') {
							$this->addError ( 'Update Failed', $row, $error );
							return FALSE;
						} else if ($returnVal === '__DELETE_FAILED__') {
							$this->addError ( 'Delete Failed', $row, $error );
							return FALSE;
						}
					} else {	//if($this_row_count >= $this->_begin_row)
						return TRUE;
					}
				} else {
					$this->addError ( 'Invalid record', $row, 'Incorrect number of columns' );
					return FALSE; // mismatch row count
				}
			}
		}
		
		//else
		return FALSE;
	}

	/**
	 * @return unknown
	 */
	function isEndRowFound() {
		if (is_numeric ( $this->_end_row ) && $this->_rowcount >= $this->_end_row)
			return TRUE;
		else
			return FALSE;
	}

	function getRawHeaderRow() {
		return $this->_raw_header_row;
	}

	function getHeaderRow() {
		return $this->_header_row;
	}
}
?>
