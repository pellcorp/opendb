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
include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/utils.php");
include_once("./lib/item_type.php");
include_once("./lib/filecache.php");
include_once("./lib/file_type.php");
include_once("./lib/http.php");

function fetch_item_instance_for_attribute_val_rs($attribute_val, $s_attribute_type) {
	$query = "SELECT ii.item_id, ii.instance_no, ii.s_status_type, ii.status_comment, ii.borrow_duration, ii.owner_id, i.title, i.s_item_type
			FROM item_attribute ia, item_instance ii, item i
			WHERE i.id = ii.item_id AND ii.item_id = ia.item_id AND ii.instance_no = ia.instance_no AND
			ia.attribute_val = '$attribute_val' AND
			ia.s_attribute_type = '$s_attribute_type' ";
	
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		return $results;
	}
}

/**
	Checks that the $s_attribute_type actually exists
*/
function is_exists_attribute_type($s_attribute_type) {
	$query = "SELECT 'x' FROM s_attribute_type WHERE s_attribute_type = '" . $s_attribute_type . "'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

/**
* Check to see if s_item_type has at least one site_type
* specific s_item_attribute_type attached.
*/
function is_exists_site_item_attribute($site_type, $item_id, $instance_no) {
	$query = "SELECT 'X' " . "FROM s_attribute_type sit, " . "	item_attribute ia " . "WHERE ia.s_attribute_type = sit.s_attribute_type AND " . "ia.item_id = '" . $item_id . "' AND " . "(ia.instance_no = 0 OR ia.instance_no = '" . $instance_no . "') AND " . "sit.site_type = '" . $site_type . "'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

/**
	Return a resultset of all lookup columns for the s_attribute_type

	@param s_attribute_type If this parameter is not defined (or FALSE), then
	all s_attribute_type_lookup records will be returned.  The select statement
	will also include s_attribute_type in this case.

	NOTE: If you want to use a '' (empty) option in your lookups, you must be
	sure that it will be encountered before any checked indicator option.
	
	@param $order_by
		order_no
		value
		display 
	
	The $order_by parameter will be ignored if $s_attribute_type is not specified.
*/
function fetch_attribute_type_lookup_rs($s_attribute_type = NULL, $order_by = 'value ASC', $default_display = TRUE) {
	$query = "SELECT satl.value, " . ($default_display ? "IF(LENGTH(IFNULL(stlv.value, display))>0,IFNULL(stlv.value, display),satl.value)" : "IFNULL(stlv.value, display)") . " AS display, satl.img, IF(LENGTH(satl.checked_ind)>0,satl.checked_ind,'N') as checked_ind, satl.s_attribute_type, satl.order_no, sat.s_field_type 
	FROM (s_attribute_type_lookup satl, s_attribute_type sat)
	LEFT JOIN s_table_language_var stlv
	ON stlv.language = '" . get_opendb_site_language () . "' AND
	stlv.tablename = 's_attribute_type_lookup' AND
	stlv.columnname = 'display' AND
	stlv.key1 = satl.s_attribute_type AND
	stlv.key2 = satl.value ";
	
	$query .= " WHERE sat.s_attribute_type = satl.s_attribute_type ";
	
	if (strlen ( $s_attribute_type ) > 0) {
		$query .= " AND satl.s_attribute_type = '" . $s_attribute_type . "'";
	}
	
	if (is_string ( $order_by )) {
		$query .= " ORDER BY order_no, $order_by";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_field_type_attribute_lookup_rs($field_type) {
	$query = "SELECT DISTINCT satl.value, IF(LENGTH(IFNULL(stlv.value, display))>0,IFNULL(stlv.value, display),satl.value) AS display
	FROM (s_attribute_type_lookup satl, s_attribute_type sat)
	LEFT JOIN s_table_language_var stlv 
	ON stlv.language = '" . get_opendb_site_language () . "' AND
		stlv.tablename = 's_attribute_type_lookup' AND
		stlv.columnname = 'display' AND
		stlv.key1 = satl.s_attribute_type AND
		stlv.key2 = satl.value 
	WHERE sat.s_attribute_type = satl.s_attribute_type AND 
		sat.s_field_type = '$field_type'
	ORDER BY order_no, value ASC";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
	Return s_attribute_type_lookup record for the specified $s_attribute_type and $value
	parameters.
*/
function fetch_attribute_type_lookup_r($s_attribute_type, $value, $column = NULL) {
	$query = "SELECT satl.value, IF(LENGTH(IFNULL(stlv.value, display))>0,IFNULL(stlv.value, display),satl.value) AS display, img 
	FROM s_attribute_type_lookup satl
	LEFT JOIN s_table_language_var stlv
	ON stlv.language = '" . get_opendb_site_language () . "' AND
	stlv.tablename = 's_attribute_type_lookup' AND
	stlv.columnname = 'display' AND
	stlv.key1 = s_attribute_type AND
	stlv.key2 = satl.value 
	WHERE satl.s_attribute_type = '" . $s_attribute_type . "' AND satl.value = '$value'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		
		if ($column != NULL)
			return $found [$column];
		else
			return $found;
	} else
		return FALSE;
}

function fetch_value_match_attribute_type_lookup_rs($s_attribute_type, $value_array, $order_by = 'value', $order = 'asc') {
	$query = "SELECT satl.value, IF(LENGTH(IFNULL(stlv.value, display))>0,IFNULL(stlv.value, display),satl.value) AS display, img, checked_ind " . "FROM s_attribute_type_lookup satl
			LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language () . "' AND
			stlv.tablename = 's_attribute_type_lookup' AND
			stlv.columnname = 'display' AND
			stlv.key1 = s_attribute_type AND
			stlv.key2 = satl.value 
			WHERE satl.s_attribute_type = '" . $s_attribute_type . "' AND satl.value IN (" . format_sql_in_clause ( $value_array ) . ") " . "ORDER BY satl.order_no, $order_by $order";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
* @param $value - if empty, query will not be executed, because it will
* cause unpredictable results.
*/
function fetch_attribute_type_lookup_value($s_attribute_type, $value) {
	if (strlen ( $value ) > 0) {
		$query = "SELECT value FROM s_attribute_type_lookup " . "WHERE s_attribute_type = '" . $s_attribute_type . "' AND " . "LOWER(value) = '" . strtolower ( $value ) . "' OR LOWER(display) = '" . strtolower ( $value ) . "'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$found = db_fetch_assoc ( $result );
			db_free_result ( $result );
			return $found ['value'];
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/*
* Check if value exists in s_attribute_type_lookup table, for specified
* s_attribute_type.
*/
function is_exists_lookup_value($s_attribute_type, $attribute_val) {
	$query = "SELECT 'x' FROM s_attribute_type_lookup WHERE s_attribute_type = '$s_attribute_type' AND " . "value = '" . addslashes ( $attribute_val ) . "' LIMIT 0,1";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

function is_lookup_attribute_type($s_attribute_type) {
	$query = "SELECT lookup_attribute_ind FROM s_attribute_type WHERE s_attribute_type = '$s_attribute_type'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found ['lookup_attribute_ind'] == 'Y')
			return TRUE;
		else
			return FALSE;
	}
	
	//else
	return FALSE;
}

function is_multivalue_attribute_type($s_attribute_type) {
	$query = "SELECT multi_attribute_ind, lookup_attribute_ind FROM s_attribute_type WHERE s_attribute_type = '$s_attribute_type'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		
		if ($found ['lookup_attribute_ind'] == 'Y' || $found ['multi_attribute_ind'] == 'Y')
			return TRUE;
		else
			return FALSE;
	}
	
	//else
	return FALSE;
}

function is_file_resource_attribute_type($s_attribute_type) {
	$query = "SELECT file_attribute_ind FROM s_attribute_type WHERE s_attribute_type = '$s_attribute_type'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found ['file_attribute_ind'] == 'Y')
			return TRUE;
		else
			return FALSE;
	}
	
	//else
	return FALSE;
}

function is_valid_field_type($field_type) {
	return in_array ( $field_type, array (
			'TITLE',
			'ITEM_ID',
			'IMAGE',
			'CATEGORY',
			'DURATION',
			'STATUSTYPE',
			'STATUSCMNT',
			'RATING' ) );
}

function fetch_sfieldtype_item_attribute_type($s_item_type, $s_field_type) {
	$attribute_type_r = fetch_sfieldtype_item_attribute_type_r ( $s_item_type, $s_field_type );
	if ($attribute_type_r)
		return $attribute_type_r ['s_attribute_type'];
	else
		return FALSE;
}

function fetch_sfieldtype_item_attribute_type_r($s_item_type, $s_field_type) {
	$results = fetch_item_attribute_type_rs ( $s_item_type, $s_field_type );
	if ($results) {
		$record_r = db_fetch_assoc ( $results );
		db_free_result ( $results );
		return $record_r;
	}
	
	//else
	return FALSE;
}

/**
	Returns a list of all s_attribute_types linked to a s_item_type.

	@param $s_item_type
	@param $order_by 's_attribute_type', 'prompt', or TRUE to order by 'order_no'
	@param $distinct
	@param $restrict_type - restrict to particular indicator, valid values include:
				[rss_ind | printable_ind | instance_attribute_ind | instance_attribute_ind | instance_field_types | 
				not_instance_field_types | a valid s_field_type ]
*/
function fetch_item_attribute_type_rs($s_item_type, $restrict_type = NULL, $order_by = TRUE, $distinct = FALSE) {
	$query = "SELECT" . ($distinct ? " DISTINCT " : " ") . "
				siat.s_attribute_type,
       			siat.order_no,
       			siat.s_item_type,
				UPPER(sat.s_field_type) as s_field_type, 
				IF(LENGTH(IFNULL(stlv2.value, siat.prompt))>0,IFNULL(stlv2.value, siat.prompt),IFNULL(stlv.value, sat.prompt)) AS prompt,
				IFNULL(stlv3.value, sat.description) AS description,		      
				sat.display_type,
				sat.display_type_arg1,
				sat.display_type_arg2,
				sat.display_type_arg3,
				sat.display_type_arg4,
				sat.display_type_arg5,
				sat.input_type,
				sat.input_type_arg1,
				sat.input_type_arg2,
				sat.input_type_arg3,
				sat.input_type_arg4,
				sat.input_type_arg5,
				sat.listing_link_ind,
                sat.lookup_attribute_ind,
                sat.multi_attribute_ind,
                sat.file_attribute_ind,
                sat.view_perm,
                siat.instance_attribute_ind,
				siat.compulsory_ind,
				siat.rss_ind,
				siat.printable_ind
		FROM	(s_item_attribute_type siat,
				s_attribute_type sat) 
		LEFT JOIN s_table_language_var stlv ON
				stlv.language = '" . get_opendb_site_language () . "' AND
				stlv.tablename = 's_attribute_type' AND
				stlv.columnname = 'prompt' AND
				stlv.key1 = sat.s_attribute_type 
		LEFT JOIN s_table_language_var stlv2 ON
				stlv2.language = '" . get_opendb_site_language () . "' AND
				stlv2.tablename = 's_item_attribute_type' AND
				stlv2.columnname = 'prompt' AND
				stlv2.key1 = siat.s_item_type AND
				stlv2.key2 = siat.s_attribute_type AND
				stlv2.key3 = siat.order_no 
		LEFT JOIN s_table_language_var stlv3
				ON stlv3.language = '" . get_opendb_site_language () . "' AND
				stlv3.tablename = 's_attribute_type' AND
				stlv3.columnname = 'description' AND
				stlv3.key1 = sat.s_attribute_type
		WHERE	sat.s_attribute_type = siat.s_attribute_type ";
	
	if (strlen ( $s_item_type ) > 0) {
		$query .= "AND siat.s_item_type = '" . $s_item_type . "' ";
	}
	
	if ($restrict_type == 'rss_ind') {
		$query .= "AND rss_ind = 'Y' ";
	} else if ($restrict_type == 'printable_ind') {
		$query .= "AND printable_ind = 'Y' ";
	} else if ($restrict_type == 'instance_attribute_ind') {
		$query .= "AND instance_attribute_ind = 'Y' ";
	} else if ($restrict_type == 'item_attribute_ind') {
		$query .= "AND instance_attribute_ind <> 'Y' ";
	} else if ($restrict_type == 'instance_field_types') {
		$query .= "AND (sat.s_field_type IS NOT NULL AND UPPER(sat.s_field_type) IN('STATUSTYPE','STATUSCMNT','DURATION')) ";
	} else if ($restrict_type == 'not_instance_field_types') {
		$query .= "AND (sat.s_field_type IS NULL OR UPPER(sat.s_field_type) NOT IN('STATUSTYPE','STATUSCMNT','DURATION')) ";
	} else if (is_valid_field_type ( $restrict_type )) {
		$query .= "AND UPPER(sat.s_field_type) IN('" . strtoupper ( $restrict_type ) . "') ";
	}
	
	if ($distinct) {
		$query .= "GROUP BY sat.s_attribute_type ";
	}
	
	if (is_string ( $order_by )) {
		switch ($order_by) {
			case 's_attribute_type' :
				$query .= "ORDER BY siat.s_attribute_type ASC";
				break;
			
			case 'prompt' :
				$query .= "ORDER BY prompt ASC";
				break;
		}
	} else if ($order_by === TRUE) {
		$query .= "ORDER BY siat.order_no ASC";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_item_type_attr_prompt($s_item_type, $s_attribute_type, $order_no = NULL) {
	$query = "SELECT IF(LENGTH(IFNULL(stlv2.value, siat.prompt))>0,IFNULL(stlv2.value, siat.prompt),IFNULL(stlv.value, sat.prompt)) AS prompt " . "FROM (s_item_attribute_type siat," . "s_attribute_type sat) " . "LEFT JOIN s_table_language_var stlv ON
				stlv.language = '" . get_opendb_site_language () . "' AND
				stlv.tablename = 's_attribute_type' AND
				stlv.columnname = 'prompt' AND
				stlv.key1 = sat.s_attribute_type 
			LEFT JOIN s_table_language_var stlv2 ON
				stlv2.language = '" . get_opendb_site_language () . "' AND
				stlv2.tablename = 's_item_attribute_type' AND
				stlv2.columnname = 'prompt' AND
				stlv2.key1 = siat.s_item_type AND
				stlv2.key2 = siat.s_attribute_type AND
				stlv2.key3 = siat.order_no " . "WHERE siat.s_item_type = '$s_item_type' AND " . "siat.s_attribute_type = sat.s_attribute_type AND " . "sat.s_attribute_type = '$s_attribute_type' ";
	
	if (is_numeric ( $order_no ))
		$query .= "AND sat.order_no = '$order_no' ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found ['prompt'];
	} else
		return FALSE;
}

function fetch_s_item_attribute_type_next_order_no($s_item_type, $s_attribute_type, $order_no = NULL) {
	$query = "SELECT order_no " . "FROM 	s_item_attribute_type " . "WHERE 	s_item_type = '$s_item_type' AND " . "		s_attribute_type = '$s_attribute_type' ";
	
	if (is_numeric ( $order_no )) {
		$query .= " AND	order_no > $order_no ";
	}
	
	$query .= "ORDER BY order_no ASC LIMIT 0,1";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found ['order_no'];
	} else
		return FALSE;
}

function fetch_attribute_type_r($s_attribute_type) {
	$query = "SELECT sat.s_attribute_type," . "IFNULL(stlv.value, sat.prompt) AS prompt," . "IFNULL(stlv2.value, sat.description) AS description," . "sat.display_type, " . "sat.display_type_arg1," . "sat.display_type_arg2," . "sat.display_type_arg3," . "sat.display_type_arg4," . "sat.display_type_arg5," . "sat.input_type," . "sat.input_type_arg1," . "sat.input_type_arg2," . "sat.input_type_arg3," . "sat.input_type_arg4," . "sat.input_type_arg5," . "sat.listing_link_ind," . "sat.s_field_type, " . "sat.lookup_attribute_ind, " . "sat.multi_attribute_ind, " . "sat.file_attribute_ind " . "FROM	s_attribute_type sat " . "LEFT JOIN s_table_language_var stlv ON
				stlv.language = '" . get_opendb_site_language () . "' AND
				stlv.tablename = 's_attribute_type' AND
				stlv.columnname = 'prompt' AND
				stlv.key1 = sat.s_attribute_type " . "LEFT JOIN s_table_language_var stlv2 ON
				stlv2.language = '" . get_opendb_site_language () . "' AND
				stlv2.tablename = 's_attribute_type' AND
				stlv2.columnname = 'description' AND
				stlv2.key1 = sat.s_attribute_type " . "WHERE	sat.s_attribute_type = '" . $s_attribute_type . "'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$record_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $record_r;
	} else
		return FALSE;
}

function fetch_attribute_type_cnt($s_attribute_type) {
	$query = "SELECT count('x') as count FROM s_attribute_type_lookup WHERE s_attribute_type = '" . $s_attribute_type . "'";
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

/**
	Will return the attribute_val for the record, or FALSE if no found.

	Note: $s_attribute_type should be UPPERCASE
*/
function fetch_attribute_val($item_id, $instance_no, $s_attribute_type, $order_no = NULL) {
	$attribute_val_r = fetch_attribute_val_r ( $item_id, $instance_no, $s_attribute_type, $order_no );
	if (is_array ( $attribute_val_r )) {
		return $attribute_val_r [0];
	} else {
		return $attribute_val_r;
	}
}

/**
* Return array of attribute_val's for $s_attribute_type, $order_no, $item_id, $instance_no
*/
function fetch_attribute_val_r($item_id, $instance_no, $s_attribute_type, $order_no = NULL) {
	$query = "SELECT item_id, instance_no, s_attribute_type, order_no, attribute_no, lookup_attribute_val, attribute_val FROM item_attribute " . "WHERE item_id = '$item_id' AND " . "s_attribute_type = '$s_attribute_type'";
	
	if (is_numeric ( $instance_no ))
		$query .= " AND (instance_no = 0 OR instance_no = $instance_no) ";
		
		// Only add order_no where condition if order_no defined, otherwise we
		// will return the first instance of s_attribute_type.
	if (is_numeric ( $order_no ))
		$query .= " AND order_no = '$order_no'";
	
	$query .= " ORDER BY attribute_no";
	
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		$attribute_type_r = fetch_attribute_type_r ( $s_attribute_type );
		
		$attribute_val_r = NULL;
		while ( $item_attribute_r = db_fetch_assoc ( $results ) ) {
			if ($attribute_type_r ['lookup_attribute_ind'] == 'Y') {
				$attribute_val_r [] = $item_attribute_r ['lookup_attribute_val'];
			} else if ($attribute_type_r ['file_attribute_ind'] != 'Y') {
				$attribute_val_r [] = $item_attribute_r ['attribute_val'];
			} else {		//if($attribute_type_r['file_attribute_ind'] == 'Y')
				$attribute_val_r [] = $item_attribute_r ['attribute_val'];
			}
		}
		
		db_free_result ( $results );
		
		return $attribute_val_r;
	} else {
		return FALSE;
	}
}

/**
* If item_id has a item_attribure recorded for the $s_attribute_type and optional
* order_no, this function returns TRUE, otherwise it returns FALSE.
*/
function is_item_attribute_set($item_id, $instance_no, $s_attribute_type, $order_no = NULL) {
	if (is_numeric ( $item_id ) && strlen ( $s_attribute_type ) > 0) {
		$query = "SELECT count('x') as count FROM item_attribute " . "WHERE item_id = '" . $item_id . "' AND " . "s_attribute_type = '" . $s_attribute_type . "' ";
		
		if (is_numeric ( $instance_no ))
			$query .= " AND (instance_no = 0 OR instance_no = $instance_no) ";
		
		if (is_numeric ( $order_no ))
			$query .= "AND order_no = '" . $order_no . "'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$record_r = db_fetch_assoc ( $result );
			db_free_result ( $result );
			if ($record_r !== FALSE && $record_r ['count'] > 0)
				return TRUE;
		}
	}
	
	//else
	return FALSE;
}

/**
 * @param unknown_type $attribute_val_r
 * @return unknown
 */
function validate_attribute_val_r($attribute_val_r, $remove_duplicates = FALSE) {
	$value_r = array ();
	
	if (! is_array ( $attribute_val_r ) && strlen ( trim ( $attribute_val_r ) ) > 0)
		$value_r [] = addslashes ( trim ( replace_newlines ( $attribute_val_r ) ) );
	else {
		for($i = 0; $i < count ( $attribute_val_r ); $i ++) {
			$value = addslashes ( trim ( replace_newlines ( $attribute_val_r [$i] ) ) );
			
			// lets make sure this $value does not already exist
			if (strlen ( $value ) > 0 && (! $remove_duplicates || ! is_array ( $value_r ) || array_search ( $value, $value_r ) === FALSE)) {
				$value_r [] = $value;
			}
		}
	}
	
	return $value_r;
}

/**
	Returns a array of all attribute records for a specified item_id / instance_no / s_attribute_type / order_no combination
	
	Returns update_on in results, so that we can keep the update_on value for a attribute
	that is only changing attribute_no
*/
function fetch_arrayof_item_attribute_rs($item_id, $instance_no, $s_attribute_type, $order_no) {
	$query = "SELECT attribute_val, lookup_attribute_val, attribute_no, update_on
	FROM item_attribute 
	WHERE item_id='" . $item_id . "' ";
	
	if (is_numeric ( $instance_no ))
		$query .= " AND instance_no = '$instance_no'";
	
	$query .= " AND s_attribute_type = '" . $s_attribute_type . "' AND order_no = '" . $order_no . "' 
	ORDER BY attribute_no";
	
	$results = db_query ( $query );
	if ($results) {
		$item_attribute_rs = array ();
		
		while ( $item_attribute_r = db_fetch_assoc ( $results ) ) {
			$item_attribute_rs [] = $item_attribute_r;
		}
		db_free_result ( $results );
		
		return $item_attribute_rs;
	}
	
	return FALSE;
}

function get_unique_filename($old_filename) {
	$file_r = get_root_filename ( $old_filename );
	$filelist_r = get_filename_list ( $file_r );
	
	$filename = generate_unique_filename ( $file_r, $filelist_r );
	
	if ($filename != $old_filename) {
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, "Upload file already exists - generating a unique filename", array (
				$old_filename,
				$filename ) );
	}
	
	return $filename;
}

function get_root_filename($filename) {
	$file_r = parse_file ( $filename );
	$filename_r = parse_numeric_suffix ( $file_r ['name'] );
	
	if (is_array ( $filename_r ) && strlen ( $filename_r ['prefix'] ) > 0 && is_numeric ( $filename ['prefix'] )) {
		$file_r ['name'] = $filename_r ['prefix'];
	} else if (is_array ( $filename_r ) && strlen ( $filename_r ['suffix'] ) > 0) { // filename is number only!
		$file_r ['name'] = $filename_r ['suffix'] . '_';
	}
	return $file_r;
}

function generate_unique_filename($file_r, $filelist_r) {
	$count = 0;
	do {
		$count ++;
		$filename = $file_r ['name'] . $count . '.' . $file_r ['extension'];
	} while ( in_array ( $filename, $filelist_r ) );
	
	return $filename;
}

function get_filename_list($file_r) {
	$query = "SELECT attribute_val 
			FROM item_attribute ia, s_attribute_type sat 
			 WHERE ia.s_attribute_type = sat.s_attribute_type AND 
			 sat.file_attribute_ind = 'Y' AND 
			 ia.attribute_val LIKE '{$file_r['name']}%.{$file_r['extension']}'";
	
	$filename_r = array ();
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		while ( $item_attribute_r = db_fetch_assoc ( $results ) ) {
			$filename_r [] = $item_attribute_r ['attribute_val'];
		}
		db_free_result ( $results );
	}
	
	return $filename_r;
}

function is_exists_upload_file_item_attribute($filename) {
	$filename = addslashes ( $filename );
	
	$query = "SELECT 'X' 
			FROM item_attribute ia,
				s_attribute_type sat
			WHERE ia.s_attribute_type = sat.s_attribute_type AND
			sat.file_attribute_ind = 'Y' AND
			ia.attribute_val = '$filename'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	return FALSE;
}

/**
	A new item process is less expensive than an update item process, so these should probably be split up.
*/
function insert_item_attributes($item_id, $instance_no, $s_item_type, $s_attribute_type, $order_no, $attribute_val_r, $file_r = NULL) {
	return _insert_or_update_item_attributes ( $item_id, $instance_no, $s_item_type, $s_attribute_type, $order_no, $attribute_val_r, $file_r );
}

function update_item_attributes($item_id, $instance_no, $s_item_type, $s_attribute_type, $order_no, $attribute_val_r, $file_r = NULL) {
	return _insert_or_update_item_attributes ( $item_id, $instance_no, $s_item_type, $s_attribute_type, $order_no, $attribute_val_r, $file_r );
}

/**
*/
function _insert_or_update_item_attributes($item_id, $instance_no, $s_item_type, $s_attribute_type, $order_no, $attribute_val_r, $file_r = NULL) {
	$is_lookup_attribute_type = is_lookup_attribute_type ( $s_attribute_type );
	
	$attribute_val_r = validate_attribute_val_r ( $attribute_val_r, $is_lookup_attribute_type );
	
	// if not instance item attribute, then discard the $instance_no
	if (! is_instance_item_attribute_type ( $s_item_type, $s_attribute_type ))
		$instance_no = NULL;
	
	$is_file_resource_attribute_type = is_file_resource_attribute_type ( $s_attribute_type );
	
	if (db_query ( "LOCK TABLES item_attribute WRITE, item_attribute AS ia READ, s_attribute_type AS sat READ" )) {
		$item_attribute_type_rs = fetch_arrayof_item_attribute_rs ( $item_id, $instance_no, $s_attribute_type, $order_no );

        if ($item_attribute_type_rs === FALSE ) {
			db_query ( "UNLOCK TABLES" );
			return FALSE;
        }

		// if same number of attributes, then we can perform an update only.			
		if (count ( $item_attribute_type_rs ) > 0 && count ( $item_attribute_type_rs ) == count ( $attribute_val_r )) {
			$op = 'update';
		} else if (count ( $item_attribute_type_rs ) == 0 || delete_item_attributes ( $item_id, $instance_no, $s_attribute_type, $order_no )) {
			$op = 'insert';
		} else {
			// if this occurs then the delete_item_attributes function returned FALSE, and that failure would have been logged.
			db_query ( "UNLOCK TABLES" );
			return FALSE;
		}
		
		// if there is actually something to insert at this point.
		if (count ( $attribute_val_r ) > 0) {
			$file_attributes_r = NULL;
			for($i = 0; $i < count ( $attribute_val_r ); $i ++) {
				$attribute_no = ($i + 1);
				
				if ($is_lookup_attribute_type) {
					if ($op == 'insert')
						insert_item_attribute ( $item_id, $instance_no, $s_attribute_type, $order_no, $attribute_no, $attribute_val_r [$i], NULL );
					else if ($item_attribute_type_rs [$i] ['lookup_attribute_val'] != $attribute_val_r [$i])
						update_item_attribute ( $item_id, $instance_no, $s_attribute_type, $order_no, $attribute_no, $attribute_val_r [$i], NULL );
				} else {
					if ($is_file_resource_attribute_type) {
						if (is_array ( $file_r ) && is_uploaded_file ( $file_r ['tmp_name'] )) {
							if ($item_attribute_type_rs [$i] ['attribute_val'] != $attribute_val_r [$i] && is_exists_upload_file_item_attribute ( $attribute_val_r [$i] )) {
								$attribute_val_r [$i] = get_unique_filename ( $attribute_val_r [$i] );
							}
							
							if (! save_uploaded_file ( $file_r ['tmp_name'], $attribute_val_r [$i] )) {
								$attribute_val_r [$i] = NULL;
							}
							
							$file_attributes_rs [] = array (
									'file_attribute_ind' => 'Y',
									'attribute_no' => $attribute_no,
									'attribute_val' => $attribute_val_r [$i] );
						} else {
							$file_attributes_rs [] = array (
									'attribute_no' => $attribute_no,
									'attribute_val' => $attribute_val_r [$i] );
						}
					}
					
					if (strlen ( $attribute_val_r [$i] ) > 0) {
						if ($op == 'insert')
							insert_item_attribute ( $item_id, $instance_no, $s_attribute_type, $order_no, $attribute_no, NULL, $attribute_val_r [$i] );
						else if ($item_attribute_type_rs [$i] ['attribute_val'] != $attribute_val_r [$i])
							update_item_attribute ( $item_id, $instance_no, $s_attribute_type, $order_no, $attribute_no, NULL, $attribute_val_r [$i] );
					}
				}
			}
			
			db_query ( "UNLOCK TABLES" );
			
			if (isset( $file_attributes_rs )) {
				foreach ($file_attributes_rs as $file_attribute_r) {
					file_cache_insert_file ( $file_attribute_r ['attribute_val'], NULL, NULL, NULL, 'ITEM', $file_attribute_r ['file_attribute_ind'] == 'Y' );
				}
			}
		} else {
			db_query ( "UNLOCK TABLES" );
		}
		
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$instance_no,
				$s_item_type,
				$s_attribute_type,
				$order_no,
				$attribute_val_r,
				$file_r ) );
		return FALSE;
	}
}

function insert_item_attribute($item_id, $instance_no, $s_attribute_type, $order_no, $attribute_no, $lookup_attribute_val, $attribute_val) {
	if ($attribute_val !== NULL || $lookup_attribute_val !== NULL) {
		$query = "INSERT INTO item_attribute (item_id, instance_no, s_attribute_type, order_no, attribute_no, lookup_attribute_val, attribute_val)" . "VALUES ($item_id, " . (is_numeric ( $instance_no ) ? $instance_no : "0") . ", '$s_attribute_type', $order_no, $attribute_no, " . ($lookup_attribute_val !== NULL ? "'$lookup_attribute_val'" : "NULL") . ", " . ($attribute_val !== NULL ? "'$attribute_val'" : "NULL") . ")";
		
		$insert = db_query ( $query );
		if ($insert && db_affected_rows () > 0) {
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$item_id,
					$instance_no,
					$s_attribute_type,
					$order_no,
					$attribute_no,
					$lookup_attribute_val,
					$attribute_val ) );
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$instance_no,
					$s_attribute_type,
					$order_no,
					$attribute_no,
					$lookup_attribute_val,
					$attribute_val ) );
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function update_item_attribute($item_id, $instance_no, $s_attribute_type, $order_no, $attribute_no, $lookup_attribute_val, $attribute_val) {
	if ($attribute_val !== NULL || $lookup_attribute_val !== NULL) {
		$query = "UPDATE item_attribute SET ";
		if ($attribute_val !== NULL) {
			$query .= "attribute_val = '$attribute_val'";
		} else {
			$query .= "attribute_val = NULL";
		}
		
		if ($lookup_attribute_val !== NULL) {
			$query .= ", lookup_attribute_val = '$lookup_attribute_val'";
		} else {
			$query .= ", lookup_attribute_val = NULL";
		}
		
		$query .= " WHERE item_id = '$item_id'";
		if (is_numeric ( $instance_no )) {
			$query .= " AND instance_no = $instance_no ";
		}
		
		$query .= " AND s_attribute_type = '$s_attribute_type' and order_no = $order_no AND attribute_no = $attribute_no";
		
		$update = db_query ( $query );
		$rows_affected = db_affected_rows ();
		if ($update && $rows_affected !== - 1) {// We should not treat updates that were not actually updated because value did not change as failures.
			if ($rows_affected > 0)
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
						$item_id,
						$instance_no,
						$s_attribute_type,
						$order_no,
						$attribute_no,
						$lookup_attribute_val,
						$attribute_val ) );
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$instance_no,
					$s_attribute_type,
					$order_no,
					$attribute_no,
					$lookup_attribute_val,
					$attribute_val ) );
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/**
Does not delete any item cache files
*/
function delete_item_attributes($item_id, $instance_no = NULL, $s_attribute_type = NULL, $order_no = NULL) {
	$query = "DELETE FROM item_attribute " . "WHERE item_id = '" . $item_id . "' ";
	
	if (is_numeric ( $instance_no ))
		$query .= "AND (instance_no = 0 OR instance_no = " . $instance_no . ") ";
	
	if (strlen ( $s_attribute_type ) > 0)
		$query .= "AND s_attribute_type = '" . $s_attribute_type . "' ";
	
	if (is_numeric ( $order_no ))
		$query .= "AND order_no = '" . $order_no . "'";
	
	$delete = db_query ( $query );
	if ($delete) {	// Even if no attributes were deleted, because there were none, this should still return true.
		if (db_affected_rows () > 0)
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$item_id,
					$instance_no,
					$s_attribute_type,
					$order_no ) );
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$instance_no,
				$s_attribute_type,
				$order_no ) );
		return FALSE;
	}
}
?>
