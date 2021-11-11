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
include_once("./lib/utils.php");

/**
*/
function get_opendb_site_language() {
	global $_OPENDB_LANGUAGE;
	
	return $_OPENDB_LANGUAGE;
}

/**
*/
function is_exists_language($language) {
	$language = strtoupper ( trim ( $language ) );
	if (strlen ( $language ) > 0) {
		$query = "SELECT 'x' FROM s_language WHERE language = '$language'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			db_free_result ( $result );
			return TRUE;
		}
	}
	
	//else
	return FALSE;
}

function is_exists_language_var($language, $varname) {
	$language = strtoupper ( trim ( $language ) );
	if (strlen ( $language ) > 0) {
		$query = "SELECT 'X' FROM s_language_var 
				WHERE language = '$language' AND varname = '$varname'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			db_free_result ( $result );
			return TRUE;
		}
	}
	
	//else
	return FALSE;
}

function is_default_language($language) {
	return (fetch_default_language () == $language);
}

/**
	The default language query is cached
*/
function fetch_default_language() {
	global $_OPENDB_DEFAULT_LANGUAGE;
	
	if (strlen ( $_OPENDB_DEFAULT_LANGUAGE ) == 0) {
		$query = "SELECT language FROM s_language WHERE default_ind = 'Y'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$record_r = db_fetch_assoc ( $result );
			db_free_result ( $result );
			
			if ($record_r) {
				$_OPENDB_DEFAULT_LANGUAGE = $record_r ['language'];
			}
		}
	}
	
	return $_OPENDB_DEFAULT_LANGUAGE;
}

/**
*/
function fetch_language_rs() {
	$query = "SELECT language, description, default_ind FROM s_language ORDER BY default_ind DESC, language ASC";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_language_r($language) {
	$query = "SELECT language, description, default_ind FROM s_language WHERE language = '$language'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$record_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		
		if ($record_r !== FALSE)
			return $record_r;
	}
	
	//else
	return FALSE;
}

function fetch_language_cnt() {
	$query = "SELECT COUNT('x') AS count FROM s_language";
	
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
	@param $varname
	@param $find
	@param $replace

	If $find is array, and $replace is NULL, then assume that the $find
	array keys are what we look to replace, and the values are the replacements.

	If $find is not array and $replace is not array, then we assume simple
	find and replace.

	If the value matched by $varname is an array, no find replace will
	be performed.

	Any \n will be expanded to actual newlines using this function.
*/
function get_opendb_lang_var($varname, $find = NULL, $replace = NULL) {
	global $LANG_VARS;
	global $_OPENDB_LANGUAGE;
	
	if (strlen( $LANG_VARS[$varname] ?? '' ) == 0) {
		$value = fetch_opendb_db_lang_var( $_OPENDB_LANGUAGE, $varname );
		if ($value !== FALSE) {
			$LANG_VARS[$varname] = $value;
		} else if (! is_default_language ( $_OPENDB_LANGUAGE )) 		// otherwise get default
{
			$value = fetch_opendb_db_lang_var ( fetch_default_language (), $varname );
			if ($value !== FALSE) {
				$LANG_VARS [$varname] = $value;
			}
		}
	}
	
	if (strlen ( $LANG_VARS [$varname] ?? '') > 0) {
		$langval = trim ( str_replace ( "\\n", "\n", $LANG_VARS [$varname] ) );
		
		if (is_array ( $find )) {
			reset ( $find );
			foreach ( $find as $key => $value ) {
				$langval = str_replace ( '{' . $key . '}', $value, $langval );
			}
		} else if (strlen ( $find ) > 0 && $replace !== NULL) 		// can replace with empty string.
{
			$langval = str_replace ( '{' . $find . '}', $replace, $langval );
		}
		
		return $langval;
	} else {
		return NULL;
	}
}

function get_opendb_table_lang_var($table, $column, $key1, $key2 = NULL, $key3 = NULL) {
	global $_OPENDB_LANGUAGE;
	
	// todo cache values returned for performance if required.
	$langval = fetch_opendb_table_lang_var ( $_OPENDB_LANGUAGE, $table, $column, $key1, $key2, $key3 );
	if (strlen ( $langval ) > 0) {
		return $langval;
	} else if (! is_default_language ( $_OPENDB_LANGUAGE )) {
		return fetch_opendb_table_lang_var ( fetch_default_language (), $table, $column, $key1, $key2, $key3 );
	} else {
		return FALSE;
	}
}

function fetch_opendb_db_lang_var($language, $varname) {
	$query = "SELECT value FROM s_language_var
	WHERE language = '$language' AND varname = '$varname'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$record_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $record_r ['value'];
	}
	
	//else
	return FALSE;
}

$_OPENDB_LANG_SYSTEM_TABLES = array (
		's_item_type' => array (
				'key' => array (
						's_item_type' ),
				'columns' => array (
						'description' ) ),
		's_item_type_group' => array (
				'key' => array (
						's_item_type_group' ),
				'columns' => array (
						'description' ) ),
		's_role' => array (
				'key' => array (
						'role_name' ),
				'columns' => array (
						'description' ) ),
		's_attribute_type' => array (
				'key' => array (
						's_attribute_type' ),
				'columns' => array (
						'prompt',
						'description' ) ),
		's_item_attribute_type' => array (
				'key' => array (
						's_item_type',
						's_attribute_type',
						'order_no' ),
				'columns' => array (
						'prompt' ) ),
		's_attribute_type_lookup' => array (
				'key' => array (
						's_attribute_type',
						'value' ),
				'columns' => array (
						'display' ) ),
		's_status_type' => array (
				'key' => array (
						's_status_type' ),
				'columns' => array (
						'description' ) ),
		's_address_type' => array (
				'key' => array (
						's_address_type' ),
				'columns' => array (
						'description' ) ),
		's_addr_attribute_type_rltshp' => array (
				'key' => array (
						's_address_type',
						's_attribute_type',
						'order_no' ),
				'columns' => array (
						'prompt' ) ),
		's_site_plugin_input_field' => array (
				'key' => array (
						'site_type',
						'field' ),
				'columns' => array (
						'prompt' ) ) )

;

function get_system_table_r() {
	global $_OPENDB_LANG_SYSTEM_TABLES;
	
	$tables_r = NULL;
	
	reset ( $_OPENDB_LANG_SYSTEM_TABLES );
	foreach ( $_OPENDB_LANG_SYSTEM_TABLES as $table => $_v ) {
		$tables_r [] = $table;
	}
	
	return $tables_r;
}

function get_system_table_config($table) {
	global $_OPENDB_LANG_SYSTEM_TABLES;
	
	if (is_array ( $_OPENDB_LANG_SYSTEM_TABLES [$table] )) {
		return $_OPENDB_LANG_SYSTEM_TABLES [$table];
	} else {
		return FALSE;
	}
}

function is_exists_system_table_language_var($language, $table, $column, $key1, $key2 = NULL, $key3 = NULL) {
	$language = strtoupper ( trim ( $language ) );
	if (strlen ( $language ) > 0) {
		$tableconf_r = get_system_table_config ( $table );
		if (is_array ( $tableconf_r ['key'] )) {
			$query = "SELECT 'X' FROM s_table_language_var 
						WHERE language = '$language' AND tablename = '$table' AND columnname = '$column' ";
			
			for($i = 1; $i <= count ( $tableconf_r ['key'] ); $i ++) {
				$query .= "AND key$i = ";
				if ($i == 1)
					$query .= "'" . addslashes ( $key1 ) . "'";
				else if ($i == 2)
					$query .= "'" . addslashes ( $key2 ) . "'";
				else if ($i == 3)
					$query .= "'" . addslashes ( $key3 ) . "'";
			}
			
			$result = db_query ( $query );
			if ($result && db_num_rows ( $result ) > 0) {
				db_free_result ( $result );
				return TRUE;
			}
		}
	}
	
	//else
	return FALSE;
}

function fetch_opendb_table_lang_var($language, $table, $column, $key1, $key2 = NULL, $key3 = NULL) {
	$tableconf_r = get_system_table_config ( $table );
	if (is_array ( $tableconf_r ['key'] )) {
		if (is_default_language ( $language )) {
			$query = "SELECT IFNULL(value, t.$column) AS value FROM $table t ";
			
			$query .= "LEFT OUTER JOIN s_table_language_var stlv ON 
				stlv.language = '$language' AND stlv.tablename = '$table' AND stlv.columnname = '$column' ";
			
			for($i = 1; $i <= count ( $tableconf_r ['key'] ); $i ++) {
				$query .= "AND stlv.key$i = t." . $tableconf_r ['key'] [$i - 1];
			}
			
			$query .= " WHERE ";
			
			for($i = 1; $i <= count ( $tableconf_r ['key'] ); $i ++) {
				if ($i > 1)
					$query .= " AND ";
				
				$query .= " t." . $tableconf_r ['key'] [$i - 1] . " = ";
				if ($i == 1)
					$query .= "'" . addslashes ( $key1 ) . "'";
				else if ($i == 2)
					$query .= "'" . addslashes ( $key2 ) . "'";
				else if ($i == 3)
					$query .= "'" . addslashes ( $key3 ) . "'";
			}
		} else {
			$query = "SELECT value FROM s_table_language_var 
					WHERE language = '$language' AND tablename = '$table' AND columnname = '$column' ";
			
			for($i = 1; $i <= count ( $tableconf_r ['key'] ); $i ++) {
				$query .= "AND " . $tableconf_r ['key'] [$i - 1] . " = ";
				if ($i == 1)
					$query .= "'" . addslashes ( $key1 ) . "'";
				else if ($i == 2)
					$query .= "'" . addslashes ( $key2 ) . "'";
				else if ($i == 3)
					$query .= "'" . addslashes ( $key3 ) . "'";
			}
		}
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$record_r = db_fetch_assoc ( $result );
			db_free_result ( $result );
			return $record_r ['value'];
		}
	}
	
	//else
	return FALSE;
}

/**
	$page should be a basename of $PHP_SELF with .php replaced with .html

	This function will determine whether a page can be found, or else a
	derivative of a page, based on a mapping algorithm.   The initial
	mapping algorithm will match borow.html against any pages that
	end in _borrow.html too
*/
function get_opendb_lang_help_page($language, $help_page) {
	$language = strtolower ( $language );
	
	$filelist_r = get_file_list ( './help/' . $language, 'html' );
	if (is_array ( $filelist_r )) {
		for($i = 0; $i < count ( $filelist_r ); $i ++) {
			if ($help_page == $filelist_r [$i]) {
				return $language . '/' . $filelist_r [$i];
			}
		}
		
		for($i = 0; $i < count ( $filelist_r ); $i ++) {
			// borrow.html will be returned for item_borrow.html too
			if (ends_with ( $help_page, "_" . $filelist_r [$i] )) {
				return $language . '/' . $filelist_r [$i];
			}
		}
	}
	
	return NULL;
}

/**
	Look for language specific help file, or fall back to english language help

	uri of page, minus any specific file reference.
*/
function get_opendb_help_page($pageid) {
	global $_OPENDB_LANGUAGE;
	
	if (strlen ( $_OPENDB_LANGUAGE ) > 0) {
		$page = get_opendb_lang_help_page ( $_OPENDB_LANGUAGE, $pageid . '.html' );
	}
	
	if ($page == NULL && $_OPENDB_LANGUAGE != 'english') {
		$page = get_opendb_lang_help_page ( 'english', $pageid . '.html' );
	}
	
	return $page;
}
?>
