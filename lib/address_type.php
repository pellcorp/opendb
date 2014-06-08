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
include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/user.php");
include_once("./lib/utils.php");

function is_exists_address_type($s_address_type) {
	if (strlen ( $s_address_type ) > 0) {
		$query = "SELECT 'x' FROM s_address_type WHERE s_address_type = '" . $s_address_type . "'";
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			db_free_result ( $result );
			return TRUE;
		}
	}
	//else
	return FALSE;
}

function fetch_address_type_r($address_type) {
	$query = 'SELECT s_address_type,' . 'ifnull(stlv.value, description) AS description ' . 'FROM s_address_type ' . 'LEFT JOIN s_table_language_var stlv
			ON stlv.language = \'' . get_opendb_site_language () . '\' AND
			stlv.tablename = \'s_address_type\' AND
			stlv.columnname = \'description\' AND
			stlv.key1 = s_address_type ' . 'WHERE s_address_type = \'' . $address_type . '\' ' . 'LIMIT 0,1';
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

function fetch_addr_attribute_type_rltshp_r($address_type, $attribute_type, $order_no = NULL) {
	$query = 'SELECT s_address_type,' . 's_attribute_type,' . 'order_no,' . 'IFNULL(stlv.value, prompt) AS prompt ' . 'FROM s_addr_attribute_type_rltshp ' . 'LEFT JOIN s_table_language_var stlv
			ON stlv.language = \'' . get_opendb_site_language () . '\' AND
			stlv.tablename = \'s_addr_attribute_type_rltshp\' AND
			stlv.columnname = \'prompt\' AND
			stlv.key1 = s_address_type AND
			stlv.key2 = s_attribute_type AND
			stlv.key3 = order_no ' . 'WHERE s_address_type = \'' . $address_type . '\' AND ' . 's_attribute_type = \'' . $attribute_type . '\'';
	
	// if an order_no provided, there is no need to sort to get the first order_no	
	if (is_numeric ( $order_no )) {
		$query .= ' AND order_no = \'' . $order_no . '\' ';
	} else {
		$query .= 'ORDER BY order_no ';
	}
	
	$query .= 'LIMIT 0,1';
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

function fetch_user_address_type_r($user_id, $address_type) {
	$query = "SELECT ua.sequence_number, " . "sadt.s_address_type, " . "IFNULL(stlv.value, sadt.description) AS description, " . "ua.public_address_ind, " . "ua.borrow_address_ind " . "FROM (s_address_type sadt, " . "user_address ua) " . "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language () . "' AND
			stlv.tablename = 's_address_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sadt.s_address_type " . "WHERE ua.user_id = '" . $user_id . "' AND " . "ua.s_address_type = '" . $address_type . "' AND " . "ua.start_dt <= now() AND (ua.end_dt IS NULL OR ua.end_dt < now()) AND " . "sadt.s_address_type = ua.s_address_type ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	}
	
	//else
	return FALSE;
}

function fetch_user_address_type_rs($user_id, $order_by = FALSE) {
	$query = "SELECT ua.sequence_number, " . "ua.public_address_ind, " . "ua.borrow_address_ind, " . "sadt.s_address_type, " . "IFNULL(stlv.value, sadt.description) AS description " . "FROM (s_address_type sadt) ";
	
	$query .= "LEFT JOIN user_address ua " . "ON ua.user_id = '" . $user_id . "' AND " . "ua.s_address_type = sadt.s_address_type AND " . "ua.start_dt <= now() AND (ua.end_dt IS NULL OR ua.end_dt < now()) ";
	
	$query .= "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language () . "' AND
			stlv.tablename = 's_address_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sadt.s_address_type ";
	
	if ($order_by) {
		$query .= "ORDER BY sadt.display_order, ua.start_dt ASC";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else {
		return FALSE;
	}
}

/**
	Will return the attribute_val for the record, or FALSE if no found.
	
	Note: $s_attribute_type should be UPPERCASE
*/
function fetch_user_address_attribute_val($ua_sequence_number, $s_attribute_type, $order_no = NULL) {
	// Only load previous record if edit.
	$query = "SELECT uaa.attribute_val " . "FROM user_address ua," . "user_address_attribute uaa " . "WHERE ua.sequence_number = '" . $ua_sequence_number . "' AND " . "ua.start_dt <= now() AND (ua.end_dt IS NULL OR ua.end_dt < now()) AND " . "uaa.ua_sequence_number = ua.sequence_number AND " . "uaa.s_attribute_type = '" . $s_attribute_type . "' ";
	
	// Only add order_no where condition if order_no defined, otherwise we
	// will return the first instance of s_attribute_type.
	if (is_numeric ( $order_no ))
		$query .= "AND uaa.order_no = '" . $order_no . "' ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found ['attribute_val'];
	} else
		return FALSE;
}

function fetch_user_address_lookup_attribute_val($ua_sequence_number, $s_attribute_type, $order_no = NULL) {
	// Only load previous record if edit.
	$query = "SELECT uaa.lookup_attribute_val " . "FROM user_address ua," . "user_address_attribute uaa " . "WHERE ua.sequence_number = '" . $ua_sequence_number . "' AND " . "ua.start_dt <= now() AND (ua.end_dt IS NULL OR ua.end_dt < now()) AND " . "uaa.ua_sequence_number = ua.sequence_number AND " . "uaa.s_attribute_type = '" . $s_attribute_type . "' ";
	
	// Only add order_no where condition if order_no defined, otherwise we
	// will return the first instance of s_attribute_type.
	if (is_numeric ( $order_no ))
		$query .= "AND uaa.order_no = '" . $order_no . "' ";
	
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		$lookup_val_r = NULL;
		while ( $lookup_r = db_fetch_assoc ( $results ) ) {
			$lookup_val_r [] = $lookup_r ['lookup_attribute_val'];
		}
		
		db_free_result ( $results );
		return $lookup_val_r;
	} else {
		return FALSE;
	}
}

/**
* Return a set of address types for a new user
*/
function fetch_address_type_rs($order_by = FALSE) {
	$query = "SELECT sadt.s_address_type, " . "IFNULL(stlv.value, sadt.description) AS description " . "FROM s_address_type sadt " . "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language () . "' AND
			stlv.tablename = 's_address_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sadt.s_address_type ";
	
	if ($order_by) {
		$query .= "ORDER BY sadt.display_order";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
* @param $mode - One of 'query' or 'edit'.  
* 
* 					In 'query' mode, will restrict what records 
*/
function fetch_address_type_attribute_type_rs($s_address_type, $mode = 'query', $order_by = FALSE) {
	$query = "SELECT sadt.s_address_type, " . "saatr.s_attribute_type, " . "saatr.order_no, " . "if(length(IFNULL(stlv.value,saatr.prompt))>0,IFNULL(stlv.value,saatr.prompt),IFNULL(stlv2.value,sat.prompt)) as prompt, " . "sat.display_type, " . "sat.display_type_arg1," . "sat.display_type_arg2," . "sat.display_type_arg3," . "sat.display_type_arg4," . "sat.display_type_arg5," . "sat.input_type," . "sat.input_type_arg1," . "sat.input_type_arg2," . "sat.input_type_arg3," . "sat.input_type_arg4," . "sat.input_type_arg5," . "sat.listing_link_ind ";
	
	if ($mode == 'update') {
		$query .= ", 'N' as compulsory_ind ";
	}
	
	$query .= "FROM	(s_addr_attribute_type_rltshp saatr, " . "s_attribute_type sat, " . "s_address_type sadt) " . "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language () . "' AND
			stlv.tablename = 's_addr_attribute_type_rltshp' AND
			stlv.columnname = 'prompt' AND
			stlv.key1 = saatr.s_address_type AND
			stlv.key2 = saatr.s_attribute_type AND
			stlv.key3 = saatr.order_no " . "LEFT JOIN s_table_language_var stlv2
			ON stlv2.language = '" . get_opendb_site_language () . "' AND
			stlv2.tablename = 's_attribute_type' AND
			stlv2.columnname = 'prompt' AND
			stlv2.key1 = sat.s_attribute_type " . "WHERE sadt.s_address_type = '" . $s_address_type . "' AND " . "saatr.s_address_type = sadt.s_address_type AND " . "sat.s_attribute_type = saatr.s_attribute_type ";
	
	if ($mode == 'update') {
		// do not close for display purposes, only update
		$query .= "AND sadt.closed_ind <> 'Y' AND saatr.closed_ind <> 'Y'";
	}
	
	if ($order_by) {
		$query .= "order by saatr.s_address_type ASC, saatr.order_no ASC";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		return $result;
	} else {
		return FALSE;
	}
}

function is_exists_valid_user_address($user_id, $s_address_type) {
	$query = "SELECT 'x' FROM user_address WHERE user_id = '$user_id' AND " . "s_address_type = '$s_address_type' AND " . "start_dt <= now() AND (end_dt IS NULL OR end_dt < now()) ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		// The very fact that at least one row was returned indicates that user_address exists
		return TRUE;
	}
	
	//else
	return FALSE;
}

//
// If successful will return the new ID for the item, otherwise will return FALSE.
//
function insert_user_address($user_id, $s_address_type, $public_address_ind, $borrow_address_ind, $start_dt = NULL, $end_dt = NULL) {
	if (strlen ( $user_id ) > 0) {
		$public_address_ind = validate_ind_column ( $public_address_ind );
		$borrow_address_ind = validate_ind_column ( $borrow_address_ind );
		
		$query = "INSERT INTO user_address (user_id, s_address_type, start_dt, end_dt, public_address_ind, borrow_address_ind)" . " VALUES ('" . $user_id . "','" . $s_address_type . "'," . (strlen ( $start_dt ) > 0 ? $start_dt : "now()") . ", " . (strlen ( $end_dt ) > 0 ? "'" . $end_dt . "'" : "NULL") . ", '$public_address_ind', '$borrow_address_ind')";
		
		$insert = db_query ( $query );
		if ($insert && db_affected_rows () > 0) {
			$new_sequence_number = db_insert_id ();
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$user_id,
					$s_address_type,
					$public_address_ind,
					$borrow_address_ind,
					$start_dt,
					$end_dt ) );
			return $new_sequence_number;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$user_id,
					$s_address_type,
					$public_address_ind,
					$borrow_address_ind,
					$start_dt,
					$end_dt ) );
			return FALSE;
		}
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$user_id,
				$s_address_type,
				$start_dt,
				$end_dt ) );
		return FALSE;
	}
}

function update_user_address($sequence_number, $public_address_ind, $borrow_address_ind) {
	if (is_numeric ( $sequence_number )) {
		$public_address_ind = validate_ind_column ( $public_address_ind );
		$borrow_address_ind = validate_ind_column ( $borrow_address_ind );
		
		$query = "UPDATE user_address SET " . "public_address_ind = '" . $public_address_ind . "'" . ", borrow_address_ind = '" . $borrow_address_ind . "'" . " WHERE sequence_number = $sequence_number";
		
		$update = db_query ( $query );
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows ();
		if ($update && $rows_affected !== - 1) {
			if ($rows_affected > 0) {
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
						$sequence_number,
						$public_address_ind,
						$borrow_address_ind ) );
			}
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$sequence_number,
					$public_address_ind,
					$borrow_address_ind ) );
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/**
* Delete all user_address_attributes, followed by the address.  If no 
* sequence number specified, all addresses for the user will be deleted.
*/
function delete_user_addresses($user_id) {
	if (db_query ( "LOCK TABLES user_address WRITE, user_address_attribute WRITE" )) {
		$query = "SELECT sequence_number FROM user_address WHERE user_id='" . $user_id . "'";
		
		$failures = 0;
		$results = db_query ( $query );
		if ($results) {
			while ( $user_address_r = db_fetch_assoc ( $results ) ) {
				if (delete_user_address_attributes ( $user_address_r ['sequence_number'] )) {
					if (! delete_user_address ( $user_address_r ['sequence_number'] )) {
						$failures ++;
					}
				} else {
					$failures ++;
				}
			}
			db_free_result ( $results );
		}
		
		db_query ( "UNLOCK TABLES" );
		if ($failures == 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), $user_id );
		return FALSE;
	}
}

/**
	Delete user_address and return boolean indicating success or failure.
	
	This function does not check for any dependencies.
*/
function delete_user_address($sequence_number) {
	$query = "DELETE FROM user_address WHERE sequence_number = '" . $sequence_number . "'";
	$delete = db_query ( $query );
	if ($delete && db_affected_rows () > 0) {
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
				$sequence_number ) );
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$sequence_number ) );
		return FALSE;
	}
}

function insert_user_address_attributes($ua_sequence_number, $s_attribute_type, $order_no, $attribute_val_r) {
	return _insert_or_update_user_address_attributes ( $ua_sequence_number, $s_attribute_type, $order_no, $attribute_val_r );
}

function update_user_address_attributes($ua_sequence_number, $s_attribute_type, $order_no, $attribute_val_r) {
	return _insert_or_update_user_address_attributes ( $ua_sequence_number, $s_attribute_type, $order_no, $attribute_val_r );
}

function _insert_or_update_user_address_attributes($ua_sequence_number, $s_attribute_type, $order_no, $attribute_val_r) {
	if (is_lookup_attribute_type ( $s_attribute_type ))
		$insert_cols = "INSERT INTO user_address_attribute (ua_sequence_number, s_attribute_type, order_no, attribute_no, lookup_attribute_val)";
	else
		$insert_cols = "INSERT INTO user_address_attribute (ua_sequence_number, s_attribute_type, order_no, attribute_no, attribute_val)";
	
	if (db_query ( "LOCK TABLES user_address_attribute WRITE" )) {
		if (delete_user_address_attributes ( $ua_sequence_number, $s_attribute_type, $order_no )) {
			if (! is_array ( $attribute_val_r ))
				$value_r [] = addslashes ( trim ( strip_tags ( $attribute_val_r ) ) );
			else {
				$value_r = NULL;
				
				for($i = 0; $i < count ( $attribute_val_r ); $i ++) {
					$value = addslashes ( trim ( strip_tags ( $attribute_val_r [$i] ) ) );
					
					// lets make sure this $value does not already exist
					if (is_array ( $value_r )) {
						for($j = 0; $j < count ( $value_r ); $j ++) {
							if ($value_r [$j] == $value) {
								$value = NULL;
								break;
							}
						}
					}
					
					if ($value != null) {
						$value_r [] = $value;
					}
				}
			}
			
			for($i = 0; $i < count ( $value_r ); $i ++) {
				$attribute_val = $value_r [$i];
				
				$query = $insert_cols . "VALUES ('$ua_sequence_number','$s_attribute_type', $order_no, " . ($i + 1) . ", '$attribute_val')";
				
				$insert = db_query ( $query );
				if ($insert && db_affected_rows () > 0) {
					opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
							$ua_sequence_number,
							$s_attribute_type,
							$order_no,
							$attribute_val ) );
				} else {
					db_query ( "UNLOCK TABLES" );
					opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
							$ua_sequence_number,
							$s_attribute_type,
							$order_no,
							$attribute_val ) );
					return FALSE;
				}
			}
			
			db_query ( "UNLOCK TABLES" );
			return TRUE;
		} else {
			db_query ( "UNLOCK TABLES" );
			return FALSE;
		}
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$ua_sequence_number,
				$s_attribute_type,
				$order_no,
				$attribute_val_r ) );
		return FALSE;
	}
}

//
// If successful will return TRUE, otherwise will return FALSE.
//
function delete_user_address_attributes($ua_sequence_number, $s_attribute_type = NULL, $order_no = NULL) {
	if (is_numeric ( $ua_sequence_number )) {
		$query = "DELETE FROM user_address_attribute " . "WHERE ua_sequence_number = '" . $ua_sequence_number . "'";
		
		if (strlen ( $s_attribute_type ) > 0 && is_numeric ( $order_no )) {
			$query .= " AND s_attribute_type = '" . $s_attribute_type . "' and order_no = '" . $order_no . "'";
		}
		
		$delete = db_query ( $query );
		// even if no rows deleted because no matches, this still succeeded
		if ($delete) {
			if (db_affected_rows () > 0) {
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
						$ua_sequence_number,
						$s_attribute_type,
						$order_no ) );
			}
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$ua_sequence_number,
					$s_attribute_type,
					$order_no ) );
			return FALSE;
		}
	} else {
		// invalid $ua_sequence_number supplied.
		return FALSE;
	}
}
?>
