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

// these are defined here - so they can be overriden by downstream packagers
// as required.  they will no longer be exposed via configuration.
define ( 'OPENDB_IMPORT_CACHE_DIRECTORY', './importcache' );
define ( 'OPENDB_ITEM_CACHE_DIRECTORY', './itemcache' );
define ( 'OPENDB_ITEM_UPLOAD_DIRECTORY', './upload' );
define ( 'OPENDB_HTTP_CACHE_DIRECTORY', './httpcache' );

$_OPENDB_CONFIG_EXISTS = NULL;

function is_gzip_compression_enabled($php_self) {
	$page = basename ( $php_self, '.php' );
	
	if (get_opendb_config_var( 'site.gzip_compression', 'enable' ) === TRUE) {
		// hard code disable for installer and url as most images already compressed
		// so is superfluous.
		if ($page != 'install' && $page != 'url' && ! in_array ( $page, get_opendb_config_var( 'site.gzip_compression', 'disabled' ) )) {
			return TRUE;
		}
	}
	
	//else
	return FALSE;
}

function get_opendb_image_type() {
	return strlen( get_opendb_config_var( 'site', 'image_type' ) ) > 0 ? get_opendb_config_var ( 'site', 'image_type' ) : "auto";
}

/**
 * @return unknown
 */
function is_show_login_menu_enabled() {
	return get_opendb_config_var( 'login', 'show_menu' ) !== FALSE;
}

/**
	The current opendb distribution version, which takes into account
	the release and patch.
*/
function get_opendb_version() {
	return __OPENDB_RELEASE__;
}

/**
 * Enter description here...
 *
 * @param unknown_type $override_title
 * @return unknown
 */
function get_opendb_title($override_default_title = TRUE) {
	if ($override_default_title) {
		return ifempty ( get_opendb_config_var ( 'site', 'title' ), __OPENDB_TITLE__ );
	} else {
		return __OPENDB_TITLE__;
	}
}

function get_opendb_title_and_version() {
	return get_opendb_title ( FALSE ) . " " . get_opendb_version ();
}

/**
*/
function is_opendb_configured() {
	global $CONFIG_VARS;
	
	if (is_array ( $CONFIG_VARS ['db_server'] )) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
	Override a config variable for the current script execution only, this should be
	used with a great deal of care, as no type checking is performed.
*/
function set_opendb_config_ovrd_var($group, $id, $var) {
	global $CONFIG_VARS;
	
	// force the caching of the entire group.
	get_opendb_config_var ( $group );
	
	$CONFIG_VARS [$group] [$id] = $var;
}

/**
	$group and $id should normally both be specified, but if
	$group is only specified, then an array of all items in the group
	will be returned.
*/
function get_opendb_config_var($group, $id = NULL, $keyid = NULL) {
	if (is_opendb_configured ()) {
		global $CONFIG_VARS;
		
		if ($group != NULL) {
			// override config value.
			if ( $group == 'db_server' ||
				 $group == 'session_handler' ||
				 ( array_key_exists($group, $CONFIG_VARS) && is_array( $CONFIG_VARS[$group] ))) { // cached vars
				if ($id !== NULL && $keyid !== NULL)
					return $CONFIG_VARS[$group][$id][$keyid];
				else if ($id !== NULL)
					return $CONFIG_VARS[$group][$id];
				else
					return $CONFIG_VARS[$group]; // will return an array of all config items in group
			} else {
				$group_r = get_opendb_db_config_var( $group );
				if (is_array( $group_r )) {
					$CONFIG_VARS[$group] = $group_r;
				}
				if ($id !== NULL && $keyid !== NULL)
					return $CONFIG_VARS[$group][$id][$keyid];
				else if ($id !== NULL)
					return $CONFIG_VARS[$group][$id];
				else
					return $CONFIG_VARS[$group];
			}
		} else {		//if($group!=NULL)
			return NULL;
		}
	} else {
		return NULL;
	}
}

function get_opendb_db_config_var($group, $id = NULL, $keyid = NULL) {
	if (is_db_connected ()) {
		if ($group != NULL) {
			if ($id != NULL && $keyid != NULL) {
				$query = "SELECT cgiv.group_id, cgiv.id, scgi.type, cgiv.keyid, cgiv.value " .
					   "FROM s_config_group_item_var cgiv, s_config_group_item scgi " .
					   "WHERE cgiv.group_id = scgi.group_id AND cgiv.id = scgi.id AND " .
					   " cgiv.keyid = scgi.keyid AND " . "cgiv.group_id = '$group' AND " .
					   " cgiv.id = '$id' AND " . "cgiv.keyid = '$keyid' " .
					   "LIMIT 0,1";
			} else if ($id != NULL) {
				$query = "SELECT cgiv.group_id, cgiv.id, scgi.type, cgiv.keyid, cgiv.value " .
					   "FROM s_config_group_item_var cgiv, s_config_group_item scgi " .
					   "WHERE cgiv.group_id = scgi.group_id AND " . "cgiv.id = scgi.id AND " .
					   " (scgi.type = 'array' OR cgiv.keyid = scgi.keyid) AND " .
					   " cgiv.group_id = '$group' AND cgiv.id = '$id' " .
					   "ORDER BY cgiv.keyid";
			} else {
				// will need to update these lines if we ever add any more array types.
				$query = "SELECT cgiv.group_id, cgiv.id, scgi.type, cgiv.keyid, cgiv.value " .
					   "FROM s_config_group_item_var cgiv, s_config_group_item scgi " .
					   "WHERE cgiv.group_id = scgi.group_id AND cgiv.id = scgi.id AND " .
					   " (scgi.type = 'array' OR cgiv.keyid = scgi.keyid) AND " . "cgiv.group_id = '$group' " .
					   "ORDER BY cgiv.id, cgiv.keyid";
			}
		} else {
			// invalid parameters provided
			return NULL;
		}
		
		$results = db_query( $query );
		if ($results) {
			if (db_num_rows( $results ) > 0) {
				$results_r = NULL;
				$tmp_vars_r = NULL;
				$current_id = NULL;
				$current_type = NULL;

				while ( $config_var_r = db_fetch_assoc( $results ) ) {
					// first time through loop
					if ($current_id == NULL) {
						$current_id = $config_var_r['id'];
						$current_type = $config_var_r['type'];
					} else if ($current_id !== $config_var_r['id']) {
						// end of id, so process
						$results_r[$current_id] = get_db_config_var(
							$current_type, $tmp_vars_r, $group, $id, $keyid
						);

						$current_id = $config_var_r['id'];
						$current_type = $config_var_r['type'];

						// reset
						$tmp_vars_r = NULL;
					}

					$tmp_vars_r[$config_var_r['keyid']] = $config_var_r['value'];
				}
				db_free_result( $results );

				$results_r[$current_id] = get_db_config_var(
					$current_type, $tmp_vars_r, $group, $id, $keyid
				);

				if ($id != NULL)
					return $results_r[$current_id];
					// 				else
					return $results_r;
			} else {			//if(db_num_rows($results)>0)
				return NULL;
			}
		} else {		//if($results)
			// cannot log here - causes recursive loop
			//opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($group, $id, $keyid));
			return NULL;
		}
	} else {	//if(db_ping())
		return NULL;
	}
}

/**
    @param $type
		Can be one of the following types, and will effect how the
		$vars_r value is processed.

			array - keys will be numeric and in sequence only.
			boolean - TRUE or FALSE only
			text - arbritrary text
			textarea - arbritrary text
			number - enforce a numeric value
			datemask - enforce a date mask.
			usertype - Restrict to set of user types only.
    		colour - RGB Hexadecimal colour value.
*/
function get_db_config_var($type, $vars_r, $group, $id, $keyid) {
	if (count ( $vars_r ) > 1) {
		if ($type == 'boolean') {
			$boolean_vars_r = NULL;
			reset( $vars_r );
			foreach ($vars_r as $key => $value) {
				if ($value == 'TRUE')
					$boolean_vars_r[$key] = TRUE;
				else //if($value == 'FALSE')
					$boolean_vars_r[$key] = FALSE;
			}
			return $boolean_vars_r;
		}
		return $vars_r;

	} elseif ($type == 'array') {
		return $vars_r;

	} elseif (count( $vars_r ) == 1) {
		$key = key( $vars_r );
		$value = current( $vars_r );

		if ($type == 'boolean') {
			if ($value == 'TRUE') {
				return TRUE;
			} else {//if($value == 'FALSE')
				return FALSE;
			}
		} else {
			// if keyid specified, or key is numeric, then return simple
			// value, otherwise be helpful and return single element array.
			if ($keyid != NULL || is_numeric ( $key ))
				return $value;
			else {
				return $vars_r;
			}
		}
	}

	if ($type == 'boolean') {
		return FALSE;
	} else {
		return NULL;
	}
}

/**
*/
function fetch_title_display_mask_rs($stdm_id) {
	$query = "SELECT stdmi.display_mask, " . "stdmi.s_item_type," . "stdmi.s_item_type_group " . "FROM s_title_display_mask_item stdmi " . "WHERE stdmi.stdm_id = '$stdm_id'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}
?>
