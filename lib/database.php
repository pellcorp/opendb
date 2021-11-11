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
$_OPENDB_DB_CONNECTED = NULL;

function init_db_connection() {
	$dbserver_conf_r = get_opendb_config_var( 'db_server' );
	if (is_array( $dbserver_conf_r )) {
		return db_connect( $dbserver_conf_r['host'], $dbserver_conf_r['username'], $dbserver_conf_r['passwd'], $dbserver_conf_r['dbname'], $dbserver_conf_r['charset']);
	}

	return FALSE;
}

function is_db_connected() {
	global $_OPENDB_DB_CONNECTED;

	if (! is_bool ( $_OPENDB_DB_CONNECTED )) {
		$_OPENDB_DB_CONNECTED = (init_db_connection () !== FALSE);
	}

	return $_OPENDB_DB_CONNECTED;
}

/**
* @param $sql
*/
function opendb_pre_query($sql) {
	$dbserver_conf_r = get_opendb_config_var ( 'db_server' );
	if (strlen ( $dbserver_conf_r ['table_prefix'] ) > 0) {
		$sql = parse_sql_statement ( $sql, $dbserver_conf_r ['table_prefix'] );
	}
	
	if ($dbserver_conf_r ['debug-sql'] === TRUE) {
		echo ('<p class="debug-sql">SQL: ' . $sql . '</p>');
	}
	
	return $sql;
}

/*
* This function is designed to collapse repeated whitespace,
* and \t \r \n, to a single space character.  This is except
* where that text is enclosed in single quotes, as in a SQL
* statement, where all whitespace is maintained.
* 
* this function could definately have been simplified to a regular
* expression - but it was felt necessary to try and avoid a regular
* expression for performance reasons.
*/
function remove_sql_ws($sql) {
	$sql2 = $sql;
	$sql = '';
	
	$single_quote = FALSE;
	for($i = 0; $i < strlen ( $sql2 ); $i ++) {
		switch (substr ( $sql2, $i, 1 )) {
			case ' ' :
			case "\t" :
			case "\n" :
			case "\r" :
				if ($i == 0 || $single_quote !== FALSE)
					$sql .= substr ( $sql2, $i, 1 );
				else if (strlen ( $sql ) == 0 || substr ( $sql, - 1 ) !== ' ') {
					// Ignore all other types of whitespace, and only insert single spaces.
					$sql .= ' ';
				}
				break;
			
			case '\'' :
				// Check for whether quote has been escaped or not.
				if ($i == 0 || substr ( $sql2, $i - 1, 1 ) != '\\')
					$single_quote = ! $single_quote;
				
				$sql .= substr ( $sql2, $i, 1 );
				break;
			
			default :
				$sql .= substr ( $sql2, $i, 1 );
		}
	}
	return $sql;
}

/**
	Only supports LEFT JOIN - no other join syntax. 

	Any other JOIN use will have to be added to this script to
	enable proper prefixing of table names.
*/
function parse_sql_statement($sql, $prefix) {
	// Match all whitespace and convert to single character
	// space.  Maintain whitespace within strings enclosed
	// in single quotes, so that INSERTS,UPDATES, etc will
	// still work properly.
	$sql = remove_sql_ws ( $sql );
	
	// A copy of $sql variable for parsing only!
	$upper_sql = strtoupper ( $sql );
	
	if (substr ( $upper_sql, 0, 7 ) == 'SELECT ') {
		$start_idx = strpos ( $upper_sql, 'FROM ', $start_idx );
		if ($start_idx !== FALSE) {
			$start_idx += 5; //5="FROM "
			

			// LEFT JOIN at the moment - will add more if required.
			$end_idx = strpos ( $upper_sql, 'LEFT JOIN ', $start_idx );
			if ($end_idx !== FALSE) {
				$tmp_end_idx = $end_idx;
				while ( $tmp_end_idx !== FALSE ) {
					$left_join_end_idx = strpos ( $upper_sql, 'ON', $tmp_end_idx );
					if ($left_join_end_idx === FALSE) {
						$left_join_end_idx = strpos ( $upper_sql, 'USING', $tmp_end_idx );
					}
					
					// Nothing else we can do if it does not match.
					if ($left_join_end_idx === FALSE) {
						$left_join_end_idx = strlen ( $upper_sql );
					}
					
					// Now we have to add the prefix to the LEFT JOIN table.
					$sql = substr ( $sql, 0, $tmp_end_idx + 10 ) . 					//10="LEFT JOIN "
							$prefix . trim ( substr ( $sql, $tmp_end_idx + 10, $left_join_end_idx - ($tmp_end_idx + 10) ) ) . ' ' . substr ( $sql, $left_join_end_idx );
					
					// Its too complicated to work out where we are in $upper_sql compared to $sql, so
					// lets just reassign in this case.
					$upper_sql = strtoupper ( $sql );
					
					$tmp_end_idx = strpos ( $upper_sql, 'LEFT JOIN ', $left_join_end_idx + strlen ( $prefix ) );
				}
			} else {
				$end_idx = strpos ( $upper_sql, 'WHERE ', $start_idx );
				if ($end_idx === FALSE) {
					$end_idx = strpos ( $upper_sql, 'GROUP BY ', $start_idx );
					if ($end_idx === FALSE) {
						$end_idx = strpos ( $upper_sql, 'HAVING ', $start_idx );
						if ($end_idx === FALSE) {
							$end_idx = strpos ( $upper_sql, 'ORDER BY ', $start_idx );
							if ($end_idx === FALSE) {
								$end_idx = strpos ( $upper_sql, 'LIMIT ', $start_idx );
							}
						}
					}
				}
			}
			
			//if still FALSE, then assume nothing but a FROM clause.
			if ($end_idx === FALSE) {
				$end_idx = strlen ( $upper_sql );
			}
			
			$from_clause = trim ( substr ( $sql, $start_idx, $end_idx - $start_idx ) );
			
			$starts_ends_with_brackets = FALSE;
			if (starts_with ( $from_clause, "(" ) && ends_with ( $from_clause, ")" )) {
				$starts_ends_with_brackets = TRUE;
				$from_clause = substr ( $from_clause, 1, - 1 );
			}
			
			$array_of_tables = explode ( ',', $from_clause );
			
			// Reset from clause.
			$from_clause = '';
			foreach ($array_of_tables as $table) {
				if (strlen ( $from_clause ) > 0)
					$from_clause .= ', ';
				$from_clause .= $prefix . trim ( $table );
			}
			
			if ($starts_ends_with_brackets) {
				$from_clause = '(' . $from_clause . ')';
			}
			
			return substr ( $sql, 0, $start_idx ) . ' ' . $from_clause . ' ' . substr ( $sql, $end_idx );
		}
	} else if (substr ( $upper_sql, 0, 12 ) == 'INSERT INTO ') {
		$end_idx = strpos ( $upper_sql, '(' );
		if ($end_idx !== FALSE) {
			return 'INSERT INTO ' . $prefix . trim ( substr ( $sql, 12, $end_idx - 12 ) ) . ' ' . substr ( $sql, $end_idx ); // 12 = "INSERT INTO "
		}
	} else if (substr ( $upper_sql, 0, 7 ) == 'UPDATE ') {
		$end_idx = strpos ( $upper_sql, 'SET ' );
		if ($end_idx !== FALSE) {
			return 'UPDATE ' . $prefix . trim ( substr ( $sql, 7, $end_idx - 7 ) ) . ' ' . substr ( $sql, $end_idx ); // 7 == "UPDATE "
		}
	} else if (substr ( $upper_sql, 0, 12 ) == 'DELETE FROM ') {
		$end_idx = strpos ( $upper_sql, 'WHERE ' );
		// No restriction, all records deleted.
		if ($end_idx === FALSE)
			$end_idx = strlen ( $upper_sql );
		
		return 'DELETE FROM ' . $prefix . trim ( substr ( $sql, 12, $end_idx - 12 ) ) . ' ' . substr ( $sql, $end_idx ); //12="DELETE FROM "
	} else if (substr ( $upper_sql, 0, 13 ) != 'UNLOCK TABLES' && substr ( $upper_sql, 0, 12 ) == 'LOCK TABLES ') {
		// NOTE: assume that LOCK tables statement will encompass the whole $upper_sql text.
		$tables_r = explode ( ',', substr ( $sql, 12 ) ); //LOCK TABLES	
		if (is_array ( $tables_r )) {
			$query = '';
			foreach ( $tables_r as  $key => $table ) {
				if (strlen ( $query ) > 0)
					$query .= ', ';
				
				$query .= $prefix . trim ( $table );
			}
			
			return 'LOCK TABLES ' . $query;
		}
	} else if (substr ( $upper_sql, 0, 13 ) == 'CREATE TABLE ') {
		$end_idx = strpos ( $upper_sql, '(' );
		if ($end_idx !== FALSE) {
			return 'CREATE TABLE ' . $prefix . trim ( substr ( $sql, 13, $end_idx - 13 ) ) . ' ' . substr ( $sql, $end_idx ); // 13 = "CREATE TABLE "
		}
	} else if (substr ( $upper_sql, 0, 12 ) == 'ALTER TABLE ') {
		if (($end_idx = strpos ( $upper_sql, 'ADD ', 12 )) !== FALSE)
			$end_idx += 3; // 'ADD'
		else if (($end_idx = strpos ( $upper_sql, 'DROP ', 12 )) !== FALSE)
			$end_idx += 4; // 'DROP'
		else if (($end_idx = strpos ( $upper_sql, 'CHANGE ', 12 )) !== FALSE)
			$end_idx += 6; // 'CHANGE'
		else if (($end_idx = strpos ( $upper_sql, 'ALTER ', 12 )) !== FALSE)
			$end_idx += 5; // 'ALTER'
		else if (($end_idx = strpos ( $upper_sql, 'MODIFY ', 12 )) !== FALSE)
			$end_idx += 6; // 'MODIFY'
		else if (($end_idx = strpos ( $upper_sql, 'RENAME ', 12 )) !== FALSE)
			$end_idx += 6; // 'RENAME'
		else if (($end_idx = strpos ( $upper_sql, 'ORDER BY ', 12 )) !== FALSE)
			$end_idx += 8; // 'ORDER BY'
		

		if ($end_idx !== FALSE) {
			return 'ALTER TABLE ' . $prefix . trim ( substr ( $sql, 12, $end_idx - 12 ) ) . ' ' . trim ( substr ( $sql, $end_idx ) ); // 12 = "ALTER TABLE "
		}
	} else if (substr ( $upper_sql, 0, 11 ) == 'DROP TABLE ') {
		$start_idx = 11; // 11 = "DROP TABLE "
		if (strpos ( $upper_sql, 'IF EXISTS ', 11 ) !== FALSE)
			$start_idx = 21; // 21 = "DROP TABLE IF EXISTS "
		

		$end_idx = strpos ( $upper_sql, ';' );
		if ($end_idx === FALSE)
			$end_idx = strlen ( $upper_sql );
		
		return substr ( $upper_sql, 0, $start_idx ) . $prefix . trim ( substr ( $sql, $start_idx, $end_idx - $start_idx ) ) . substr ( $sql, $end_idx ); // 12 = "CREATE TABLE "
	} else if (substr ( $upper_sql, 0, 13 ) == 'RENAME TABLE ') {	// Only supports ONE table rename!!!
		$to_idx = strpos ( $upper_sql, 'TO' );
		$from_name = trim ( substr ( $sql, 13, $to_idx - 13 ) );
		$to_name = trim ( substr ( $sql, $to_idx + 2 ) );
		
		return 'RENAME TABLE ' . $prefix . $from_name . ' TO ' . $prefix . $to_name; // 13 = "RENAME TABLE "
	} else if (substr ( $upper_sql, 0, 9 ) == 'DESCRIBE ') {	// Only supports ONE table rename!!!
		$from_name = trim ( substr ( $sql, 9 ) );
		return 'DESCRIBE ' . $prefix . $from_name;
	} else if (substr ( $upper_sql, 0, 24 ) == 'SHOW TABLE STATUS LIKE \'') {	// SHOW TABLE STATUS LIKE 'item_instance'
		$table_name = trim ( substr ( $sql, 24 ) );
		return 'SHOW TABLE STATUS LIKE \'' . $prefix . $table_name;
	} else if (substr ( $upper_sql, 0, 23 ) == 'SHOW FULL COLUMNS FROM ') { 	// SHOW FULL COLUMNS FROM item_instance
		$table_name = trim ( substr ( $sql, 23 ) );
		return 'SHOW FULL COLUMNS FROM ' . $prefix . $table_name;
	} else if (substr ( $upper_sql, 0, 18 ) == 'SHOW COLUMNS FROM ') {	// SHOW COLUMNS FROM item_instance
		$table_name = trim ( substr ( $sql, 18 ) );
		return 'SHOW COLUMNS FROM ' . $prefix . $table_name;
	} else {	//cannot parse - so return original $sql as last resort.
		return $sql;
	}
}

/**
	This function should be updated every time a new table is added.
*/
function fetch_opendb_table_list_r() {
	$tables = array (
			's_item_type',
			's_item_type_group',
			's_item_type_group_rltshp',
			's_attribute_type',
			's_attribute_type_lookup',
			's_item_attribute_type',
			's_status_type',
			's_address_type',
			's_addr_attribute_type_rltshp',
			's_site_plugin',
			's_site_plugin_conf',
			's_site_plugin_input_field',
			's_site_plugin_s_attribute_type_map',
			's_site_plugin_s_attribute_type_lookup_map',
			's_site_plugin_link',
			's_config_group',
			's_config_group_item',
			's_config_group_item_var',
			's_title_display_mask',
			's_title_display_mask_item',
			's_item_listing_conf',
			's_item_listing_column_conf',
			'user',
			'user_address',
			'user_address_attribute',
			'review',
			'item',
			'item_instance',
			'item_attribute',
			'borrowed_item',
			'announcement',
			'import_cache',
			'file_cache',
			'php_session',
			's_file_type_content_group',
			's_file_type',
			's_file_type_extension',
			's_language',
			's_language_var',
			's_table_language_var',
			's_opendb_release',
			'item_instance_relationship',
			'mailbox',
			's_role',
			's_permission',
			's_role_permission',
			'remember_me' );
	
	return $tables;
}
?>
