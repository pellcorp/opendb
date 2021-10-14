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
include_once("./lib/utils.php");
include_once("./lib/item_type.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item_type_group.php");
include_once("./lib/parseutils.php");
include_once("./lib/TitleMask.class.php");
include_once("./lib/OpenDbSnoopy.class.php");
include_once("./lib/phpcuecat/PHPCueCat.class.php");
include_once("./lib/ISBN/ISBN.class.php");

// Construct a single copy of this object for use within the site plugin
$SITE_PLUGIN_SNOOPY = new OpenDbSnoopy ( TRUE ); //debugging always on

function get_month_num_for_name($monthname, $months) {
	$key = array_search ( strtolower ( $monthname ), $months );
	if ($key !== FALSE)
		$month = $key + 1;
	else
		$month = 1;
	
	return $month;
}

function get_cuecat_isbn_code($field) {
	$cuecat = new PHPCueCat ();
	if ($cuecat->parse ( $field )) {
		if ($cuecat->is_valid ()) {
			$isbnInfo = $cuecat->get_isbn_info ();
			if ($isbnInfo !== FALSE && $cuecat->check_isbn ( $isbnInfo ['isbn'] ))
				return $isbnInfo ['isbn'];
			else
				return $cuecat->bar_code;
		}
	}
	
	return FALSE;
}

function get_cuecat_upc_code($field) {
	$cuecat = new PHPCueCat ();
	if ($cuecat->parse ( $field )) {
		if ($cuecat->is_valid ()) {
			return $cuecat->bar_code;
		}
	}
	
	return FALSE;
}

/**
 * UPC's can be at most 13 characters
 *
 * @param unknown_type $field
 * @return unknown
 */
function get_upc_code($field) {
	$scanCode = substr ( strtoupper ( $field ), 0, 13 );
	
	if (is_numeric ( $scanCode )) {
		return substr ( $scanCode, 0, 13 );
	}
	
	return FALSE;
}

function get_isbn_code($field) {
	$ISBN = new ISBN ();
	
	$field = strtoupper ( $field );
	
	$isbntype = $ISBN->gettype ( $field );
	
	if (($isbntype == 10 && $ISBN->validateten ( $field )) || ($isbntype == 13 && $ISBN->validatettn ( $field ))) {
		return $field;
	}
	
	return FALSE;
}

function is_exists_any_site_plugin() {
	$query = "SELECT 'x' FROM s_site_plugin";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	return FALSE;
}

function &get_site_plugin_instance($site_type) {
	$site_plugin_classname = fetch_site_plugin_classname ( $site_type );
	if ($site_plugin_classname !== FALSE) {
		include_once("./lib/site/" . $site_plugin_classname . ".class.php");
		$sitePlugin = new $site_plugin_classname ( $site_type );
		
		return $sitePlugin;
	} else {
		return FALSE;
	}
}

/**
* check for record in site_plugin table and class in site/ directory.
*/
function is_exists_site_plugin($site_type, $check_classname = TRUE) {
	$query = "SELECT site_type, classname " . "FROM s_site_plugin " . "WHERE site_type = '" . $site_type . "'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$site_plugin_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		
		if ($check_classname === FALSE || file_exists ( "./lib/site/" . $site_plugin_r ['classname'] . ".class.php" ))
			return TRUE;
		else
			return FALSE;
	} else {
		return FALSE;
	}
}

/**
* Returns site_plugin record from table for site_type
*/
function fetch_site_plugin_r($site_type) {
	$query = "SELECT site_type, classname, order_no, title, image, description, external_url, items_per_page, more_info_url " . "FROM s_site_plugin " . "WHERE site_type = '" . $site_type . "'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$site_plugin_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		
		return $site_plugin_r;
	} else
		return FALSE;
}

function fetch_site_plugin_classname($site_type) {
	$query = "SELECT classname " . "FROM s_site_plugin " . "WHERE site_type = '" . $site_type . "'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$site_plugin_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		
		return $site_plugin_r ['classname'];
	}
	
	//else
	return FALSE;
}

function get_site_plugin_list_r() {
	$query = "SELECT site_type " . "FROM s_site_plugin " . "ORDER BY order_no";
	
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		$site_list_r = array ();
		
		while ( $site_plugin_r = db_fetch_assoc ( $results ) ) {
			$site_list_r [] = $site_plugin_r ['site_type'];
		}
		db_free_result ( $results );
		
		return $site_list_r;
	} else {
		return FALSE;
	}
}

/**
 * Fetch first attribute type for a site plugin, in most cases
 * there will only be one, if there is more than one, this functions
 * return value is undefined.
 *
 * @param unknown_type $site_type
 * @return unknown
 */
function fetch_site_attribute_type($site_type) {
	$query = "SELECT s_attribute_type
			FROM 	s_attribute_type
			WHERE 	site_type = '" . $site_type . "'";
	
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		$attribute_type_r = db_fetch_assoc ( $results );
		db_free_result ( $results );
		
		return $attribute_type_r ['s_attribute_type'];
	}
	
	//else
	return FALSE;
}

/*
* 	Return resultset of all relevant site types, including all details such as
* title, image, description, external_url, order_no, etc.
*/
function fetch_site_plugin_rs($s_item_type = NULL) {
	$query = "SELECT site_type, classname, title, image, description, external_url, items_per_page, more_info_url, order_no " . "FROM s_site_plugin ";
	
	if ($s_item_type != NULL) {
		$inclause = format_sql_in_clause ( fetch_site_type_rs ( $s_item_type ) );
		if ($inclause != NULL) {
			$query .= "WHERE site_type IN (" . $inclause . ") ";
		} else {
			return FALSE;
		}
	}
	
	$query .= "ORDER BY order_no";
	
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0)
		return $results;
	else
		return FALSE;
}

/**
* Will return a complete array structure of configuration information for a 
* $site_type.
*/
function get_site_plugin_conf_r($site_type) {
	$results = fetch_site_plugin_conf_rs ( $site_type );
	if ($results) {
		$site_plugin_conf_rs = array ();
		
		$name = NULL;
		$value = NULL;
		while ( $site_plugin_conf_r = db_fetch_assoc ( $results ) ) {
			if ($name == NULL || $name != $site_plugin_conf_r ['name']) {
				// need to process entries added to $value
				if ($name != NULL) {
					if (is_array ( $value )) {
						if (count ( $value ) == 1 && isset ( $value [0] ))
							$site_plugin_conf_rs [$name] = $value [0];
						else
							$site_plugin_conf_rs [$name] = $value;
					}
				}
				
				$value = NULL;
				$name = $site_plugin_conf_r ['name'];
			}
			
			$value [$site_plugin_conf_r ['keyid']] = $site_plugin_conf_r ['value'];
		}
		
		if (is_array ( $value )) {
			if (count ( $value ) > 1)
				$site_plugin_conf_rs [$name] = $value;
			else
				$site_plugin_conf_rs [$name] = $value [0];
		}
		db_free_result ( $results );
		
		return $site_plugin_conf_rs;
	} else {
		return FALSE;
	}
}

function fetch_site_plugin_conf_rs($site_type) {
	$query = "SELECT site_type, name, keyid, description, value " . "FROM s_site_plugin_conf " . "WHERE site_type = '" . $site_type . "' " . "ORDER BY name, keyid";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
* Fetch a set of site plugin links, optionally for a specific s_item_type
*/
function fetch_site_plugin_link_rs($site_type, $s_item_type = NULL) {
	$query = "SELECT site_type, sequence_number, s_item_type_group, s_item_type, site_type, description, url, title_url, order_no " . "FROM s_site_plugin_link " . "WHERE site_type = '" . $site_type . "' ";
	
	if ($s_item_type != NULL) {
		$query .= "AND (s_item_type = '*' OR s_item_type = '" . $s_item_type . "') AND ";
		
		$query .= "(s_item_type_group = '*' ";
		$item_type_group_arr = fetch_item_type_groups_for_item_type_r ( $s_item_type, 'Y' );
		if (is_array ( $item_type_group_arr ))
			$query .= "OR s_item_type_group IN (" . format_sql_in_clause ( $item_type_group_arr ) . ")) ";
		else
			$query .= ") ";
	}
	
	$query .= "ORDER BY order_no";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_site_plugin_input_field_rs($site_type) {
	$query = "SELECT site_type, order_no, field, description, IFNULL(stlv.value, prompt) AS prompt, field_type, default_value, refresh_mask " . "FROM s_site_plugin_input_field " . "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language () . "' AND
			stlv.tablename = 's_site_plugin_input_field' AND
			stlv.columnname = 'prompt' AND
			stlv.key1 = site_type AND
			stlv.key2 = field " . "WHERE site_type = '" . $site_type . "' " . "ORDER BY order_no";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_site_plugin_s_attribute_type_map_rs($site_type) {
	$query = "SELECT site_type, sequence_number, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind " . "FROM s_site_plugin_s_attribute_type_map " . "WHERE site_type = '" . $site_type . "' " . "ORDER BY variable, s_attribute_type";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_site_plugin_s_attribute_type_lookup_map_rs($site_type, $s_attribute_type = NULL) {
	$query = "SELECT site_type, sequence_number, s_attribute_type, value, lookup_attribute_val " . "FROM s_site_plugin_s_attribute_type_lookup_map " . "WHERE site_type = '" . $site_type . "' ";
	
	if (strlen ( $s_attribute_type ) > 0) {
		$query .= " AND s_attribute_type = '" . $s_attribute_type . "' ";
	}
	
	$query .= "ORDER BY s_attribute_type, value, lookup_attribute_val";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
	Returns value -> lookup_attribute_val mappings for a specified site_type and attribute type
	combination.  The key of each array entry is the value, while the look_attribute_val is
	the value.
*/
function get_site_plugin_s_attribute_type_lookup_map_r($site_type, $s_attribute_type) {
	$results = fetch_site_plugin_s_attribute_type_lookup_map_rs ( $site_type, $s_attribute_type );
	if ($results && db_num_rows ( $results ) > 0) {
		$lookup_mappings_r = NULL;
		while ( $lookup_map_r = db_fetch_assoc ( $results ) ) {
			$lookup_mappings_r [$lookup_map_r ['value']] = $lookup_map_r ['lookup_attribute_val'];
		}
		db_free_result ( $results );
		
		return $lookup_mappings_r;
	}
	
	//else
	return FALSE;
}

function fetch_site_plugin_link_r($site_type, $sequence_number) {
	$query = "SELECT sequence_number, s_item_type_group, s_item_type, site_type, description, url, title_url, order_no " . "FROM s_site_plugin_link " . "WHERE site_type = '" . $site_type . "' AND sequence_number = $sequence_number";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

function fetch_site_plugin_conf_r($site_type, $name, $keyid) {
	$query = "SELECT name, keyid, description, value " . "FROM s_site_plugin_conf " . "WHERE site_type = '" . $site_type . "' AND name = '$name' AND keyid = '$keyid'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

function fetch_site_plugin_input_field_r($site_type, $field) {
	$query = "SELECT order_no, field, description, IFNULL(stlv.value, prompt) AS prompt, field_type, default_value, refresh_mask " . "FROM s_site_plugin_input_field " . "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language () . "' AND
			stlv.tablename = 's_site_plugin_input_field' AND
			stlv.columnname = 'prompt' AND
			stlv.key1 = site_type AND
			stlv.key2 = field " . "WHERE site_type = '" . $site_type . "' AND field = '$field'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

function fetch_site_plugin_s_attribute_type_map_r($site_type, $sequence_number) {
	$query = "SELECT sequence_number, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind " . "FROM s_site_plugin_s_attribute_type_map " . "WHERE site_type = '" . $site_type . "' AND sequence_number = $sequence_number";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

function fetch_site_plugin_s_attribute_type_lookup_map_r($site_type, $sequence_number) {
	$query = "SELECT sequence_number, s_attribute_type, value, lookup_attribute_val " . "FROM s_site_plugin_s_attribute_type_lookup_map " . "WHERE site_type = '" . $site_type . "' AND sequence_number = $sequence_number";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

/**
* The $site_plugin_attributes_r will consist of array entries, each identified by a 
* alphakey.  The value may in fact be another array and this has to be handled
* appropriately.
*/
function get_expanded_and_mapped_site_plugin_item_variables_r($site_type, $s_item_type, $site_item_attributes_r) {
	$query = "SELECT variable, s_attribute_type, lookup_attribute_val_restrict_ind " . "FROM s_site_plugin_s_attribute_type_map " . "WHERE site_type = '" . $site_type . "' ";
	
	$query .= "AND (s_item_type = '*' OR s_item_type = '" . $s_item_type . "') AND ";
	
	$query .= "(s_item_type_group = '*' ";
	$item_type_group_arr = fetch_item_type_groups_for_item_type_r ( $s_item_type );
	if (is_array ( $item_type_group_arr ))
		$query .= "OR s_item_type_group IN (" . format_sql_in_clause ( $item_type_group_arr ) . ")) ";
	else
		$query .= ") ";
	
	$query .= "ORDER BY variable, s_attribute_type";
	
	$new_attributes_r = array ();
	
	// cache this, so we can check if lookup_attribute_val_restrict_ind = 'Y'
	$lookup_attribute_val_restrict_ind = array ();
	$mapped_attributes_r = array ();
	
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		$variable = NULL;
		
		while ( $attribute_type_map_r = db_fetch_assoc ( $results ) ) {
			$value = NULL;
			
			$variable = $attribute_type_map_r ['variable'];
			
			if (isset ( $site_item_attributes_r [$variable] )) {
				$value = $site_item_attributes_r [$variable];
				
				// at least one direct mapping - title should not be flagged - as there is requirement for multiple mappings
				if ($variable != 'title') {
					$mapped_attributes_r [] = $variable;
				}
			}
			
			$key = strtolower ( $attribute_type_map_r ['s_attribute_type'] );
			if ($value !== NULL) {
				if (isset ( $new_attributes_r [$key] )) {
					if (! is_array ( $new_attributes_r [$key] ))
						$new_attributes_r [$key] = array (
								$new_attributes_r [$key] );
					
					if (is_array ( $value ))
						$new_attributes_r [$key] = array_merge ( $new_attributes_r [$key], $value );
					else
						$new_attributes_r [$key] [] = $value;
				} else {
					$new_attributes_r [$key] = $value;
				}
			}
			
			if ($attribute_type_map_r ['lookup_attribute_val_restrict_ind'] == 'Y') {
				$lookup_attribute_val_restrict_ind_r [$key] = 'Y';
			}
		}
		db_free_result ( $results );
	}
	
	// now for any variables that do not have a mapping, add them to the $new_attributes_r
	reset ( $site_item_attributes_r );
	foreach ( $site_item_attributes_r as $key => $value ) {
		$key = strtolower ( $key );
		if (isset ( $new_attributes_r [$key] )) {
			$oldValue = NULL;
			
			// we want the direct mapping attributes first.
			if (is_array ( $new_attributes_r [$key] ))
				$oldValue = $new_attributes_r [$key];
			else
				$oldValue [] = $new_attributes_r [$key];
			unset ( $new_attributes_r [$key] );
			
			if (is_array ( $value ))
				$new_attributes_r [$key] = $value;
			else
				$new_attributes_r [$key] [] = $value;

			foreach ( $oldValue as $value ) {
				if (! in_array ( $value, $new_attributes_r [$key] )) {
					$new_attributes_r [$key] [] = $value;
				}
			}
		} else if (! in_array ( $key, $mapped_attributes_r )) {
			$new_attributes_r [$key] = $value;
		}
	}
	
	$site_item_attributes_r = NULL;
	
	// now we need to check to see if any lookup mappings exist for each
	// of the attribute values, and update the $value's appropriately.
	reset ( $new_attributes_r );
	foreach ( $new_attributes_r as $key => $value ) {
		// temporary UPPER so we can work with actual s_attribute_type records in database
		$s_attribute_type = strtoupper ( $key );
		
		if (is_lookup_attribute_type ( $s_attribute_type )) {
			$values_r = NULL;
			
			// if a lookup attribute type, we want to make sure that the $value's
			// are all arrays anyway, so lets do that check each time.
			if (is_array ( $value ))
				$values_r = $value;
			else
				$values_r [] = $value;
			
			$results = fetch_site_plugin_s_attribute_type_lookup_map_rs ( $site_type, $s_attribute_type );
			if ($results) {
				$found_entries_r = array ();
				$new_values_r = array ();
				while ( $lookup_map_r = db_fetch_assoc ( $results ) ) {
					for($i = 0; $i < count ( $values_r ); $i ++) {
						if (strcasecmp ( $values_r [$i], $lookup_map_r ['value'] ) === 0) {
							$found_entries_r [] = $values_r [$i];
							
							if (! in_array ( $lookup_map_r ['lookup_attribute_val'], $new_values_r )) {
								$new_values_r [] = $lookup_map_r ['lookup_attribute_val'];
							}
						}
					}
				}
				db_free_result ( $results );
				
				// now process all back into $values_r
				for($i = 0; $i < count ( $values_r ); $i ++) {
					if (! in_array ( $values_r [$i], $found_entries_r ) && ! in_array ( $values_r [$i], $new_values_r )) {
						$new_values_r [] = $values_r [$i];
					}
				}
				$values_r = $new_values_r;
			} //if($results)
			

			// now reassign back.
			$site_item_attributes_r [strtolower ( $s_attribute_type )] = $values_r;
		} else {
			// the next process prefers arrays to deal with, even if single element
			$site_item_attributes_r [strtolower ( $s_attribute_type )] = $value;
		}
	}
	
	//
	// now that we have expanded mappings, we need to map to s_item_attribute_type order number mappings
	//
	$new_attributes_r = $site_item_attributes_r;
	$site_item_attributes_r = NULL;
	
	// now we want to expand the $new_attributes_r, so we have a set of
	// variables that include the order_no $fieldname type format.
	$results = fetch_item_attribute_type_rs ( $s_item_type, NULL, 's_attribute_type' );
	if ($results) {
		// this will be set if array encountered, but not lookup value.
		$processing_s_attribute_type = FALSE;
		
		while ( $attribute_type_r = db_fetch_assoc ( $results ) ) {
			$variable = strtolower ( $attribute_type_r ['s_attribute_type'] );
			if (isset ( $new_attributes_r [$variable] )) {
				$fieldname = get_field_name ( $attribute_type_r ['s_attribute_type'], $attribute_type_r ['order_no'] );
				if (is_not_empty_array ( $new_attributes_r [$variable] )) {
					// TODO: Consider adding values not found in the lookup table to the s_attribute_type_lookup.
					if (is_lookup_attribute_type ( $attribute_type_r ['s_attribute_type'] )) {
						$lookup_attribute_val_restrict_ind = $lookup_attribute_val_restrict_ind_r [strtolower ( $attribute_type_r ['s_attribute_type'] )];
						
						// here is where we want some sanity checking of the options
						$value_r = $new_attributes_r [$variable];
						
						$lookup_value_r = array ();
						for($i = 0; $i < count ( $value_r ); $i ++) {
							$raw_value = trim ( $value_r [$i] );
							if (strlen ( $raw_value ) > 0) {
								$value = fetch_attribute_type_lookup_value ( $attribute_type_r ['s_attribute_type'], $raw_value );
								if ($value !== FALSE)
									$lookup_value_r [] = $value;
								else if ($lookup_attribute_val_restrict_ind != 'Y') // do not include if restricted to lookup values
									$lookup_value_r [] = $raw_value;
							}
						}
						
						$site_item_attributes_r [$fieldname] = array_unique ( $lookup_value_r );
					} else {
						// This indicates we have a repeated s_attribute_type, and so should act appropriately.
						if ($processing_s_attribute_type != NULL && $attribute_type_r ['s_attribute_type'] == $processing_s_attribute_type) {
							$site_item_attributes_r [$fieldname] = $new_attributes_r [$variable] [0];
							
							// remove it
							array_splice ( $new_attributes_r [$variable], 0, 1 );
						} else if (count ( $new_attributes_r [$variable] ) > 1) {
							// this is the first occurence of the s_attribute_type, so lets see if its repeated at least once.
							if (is_numeric ( fetch_s_item_attribute_type_next_order_no ( $s_item_type, $attribute_type_r ['s_attribute_type'], $attribute_type_r ['order_no'] ) )) {
								$site_item_attributes_r [$fieldname] = $new_attributes_r [$variable] [0];
								
								// remove it
								array_splice ( $new_attributes_r [$variable], 0, 1 );
								
								$processing_s_attribute_type = $attribute_type_r ['s_attribute_type'];
							} else {
								// otherwise just copy the whole thing.
								$site_item_attributes_r [$fieldname] = $new_attributes_r [$variable];
								unset ( $new_attributes_r [$variable] );
							}
						} else {
							$site_item_attributes_r [$fieldname] = $new_attributes_r [$variable] [0];
							unset ( $new_attributes_r [$variable] );
						}
					}
				} else if (! is_array ( $new_attributes_r [$variable] )) {
					$site_item_attributes_r [$fieldname] = $new_attributes_r [$variable];
					unset ( $new_attributes_r [$variable] );
				}
			} else if ($attribute_type_r ['s_field_type'] == 'TITLE' && isset ( $new_attributes_r ['title'] )) {
				// in case developer forgot to setup title mapping.
				$fieldname = get_field_name ( $attribute_type_r ['s_attribute_type'], $attribute_type_r ['order_no'] );
				$site_item_attributes_r [$fieldname] = $new_attributes_r ['title'];
			}
		} //while
		db_free_result ( $results );
	}
	
	return $site_item_attributes_r;
}
?>
