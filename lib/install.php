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
include_once("./lib/fileutils.php");
include_once("./lib/utils.php");

function fix_version($version) {
	if ($version == '1.0') {
		$version = '1.0.0';
	} else if (preg_match('/^1\.0(RC)([0-9]+)$/', $version, $matches) 
			|| preg_match('/^1\.0(b)([0-9]+)$/', $version, $matches) 
			|| preg_match('/^1\.0(a)([0-9]+)$/', $version, $matches) 
			|| preg_match('/^1\.0(pl)([0-9]+)$/', $version, $matches)) {
		$version = '1.0.0' . $matches[1] . $matches[2];
	} else if (preg_match('/^(1\.[5|6]\.)([0-9]+)\.([0-9]+)(.*)([0-9]+)$/', $version, $matches))  {
		$version = $matches[1] . $matches[3] . $matches[4] . $matches[5];
	} else if (preg_match('/^(1\.[5|6]\.)0\.([0-9]+)$/', $version, $matches))  { // 1.5.0.8 -> 1.5.8
		$version = $matches[1] . $matches[2];
	}
	return $version;
}

function opendb_version_compare($from_version, $to_version, $operator) {
	$to_version = fix_version($to_version);
	$from_version = fix_version($from_version);
	
	return version_compare($from_version, $to_version, $operator);
}

function get_opendb_table_column_collation_mismatches(&$table_colation_mismatch, &$table_column_colation_mismatch) {
	$default_collation = fetch_opendb_database_collation ();
	
	$prevalent_table_colation = fetch_opendb_table_column_collations ( $table_collations_r, $table_column_collations_r );
	if ($default_collation === FALSE || $default_collation == NULL) {
		$default_collation = $prevalent_table_colation;
	}
	
	$table_colation_mismatch = array ();
	$table_column_colation_mismatch = array ();
	
	if (strlen ( $default_collation ) > 0) {
		reset ( $table_collations_r );
		foreach ( $table_collations_r as $table => $collation ) {
			if (strlen ( $collation ) > 0 && $collation != $default_collation) {
				$table_colation_mismatch [$table] = $collation;
			}
		}
		
		foreach ( $table_column_collations_r as $table => $columns_r ) {
			foreach ( $columns_r as $column => $collation ) {
				if (strlen ( $collation ) > 0 && $collation != $default_collation) {
					$table_column_colation_mismatch [$table] [$column] = $collation;
				}
			}
		}
	}
	
	return $default_collation;
}

/**
 * taken from phpMyAdmin
 *
 * @param unknown_type $db
 * @return unknown
 */
function fetch_opendb_database_collation() {
	$dbname = get_opendb_config_var ( 'db_server', 'dbname' );
	
	$query = "SHOW CREATE DATABASE `$dbname`";
	$result = db_query ( $query );
	
	if ($result && db_num_rows ( $result ) > 0) {
		$record_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		
		$tokenized = explode ( ' ', $record_r ['Create Database'] );
		for($i = 1; $i + 3 < count ( $tokenized ); $i ++) {
			if ($tokenized [$i] == 'DEFAULT' && $tokenized [$i + 1] == 'CHARACTER' && $tokenized [$i + 2] == 'SET') {
				// We've found the character set!
				if (isset ( $tokenized [$i + 5] ) && $tokenized [$i + 4] == 'COLLATE') {
					return $tokenized [$i + 5]; // We found the collation!
				} else {
					// We did not find the collation
					return NULL;
				}
			}
		}
	}
	
	return FALSE;
}

function fetch_opendb_table_column_collations(&$table_collations_r, &$table_column_collations_r) {
	$table_r = fetch_opendb_table_list_r ();
	
	$table_collations_r = array ();
	$table_column_collations_r = array ();
	$collation_spread = array ();
	
	reset ( $table_r );
	foreach ( $table_r as $table ) {
		$query = "SHOW TABLE STATUS LIKE '$table'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$record_r = db_fetch_assoc ( $result );
			$table_collations_r [$table] = $record_r ['Collation'];
			db_free_result ( $result );
			
			if (! is_numeric ( $collation_spread [$record_r ['Collation']] )) {
				$collation_spread [$record_r ['Collation']] = 1;
			} else {
				$collation_spread [$record_r ['Collation']] ++;
			}
		}
		
		$results = db_query ( "SHOW FULL COLUMNS FROM $table" );
		if ($results && db_num_rows ( $results ) > 0) {
			while ( $column_r = db_fetch_assoc ( $results ) ) {
				if ($column_r ['Collation'] != 'NULL') {
					$table_column_collations_r [$table] [$column_r ['Field']] = $column_r ['Collation'];
				}
			}
			db_free_result ( $results );
		}
	}
	
	$prevalent_colation = NULL;
	$colation_highest_count = NULL;

	foreach ( $collation_spread as $collation => $count ) {
		if (! is_numeric ( $colation_highest_count ) || $count > $colation_highest_count) {
			$prevalent_colation = $collation;
			$colation_highest_count = $count;
		}
	}
	return $prevalent_colation;
}

/**
 * assumes db is already connected.
 */
function get_dbuser_privileges() {
	$user_privileges = array ();
	
	if (is_db_connected ()) {
		if (check_opendb_table ( 'user' )) {
			if (db_query ( "LOCK TABLES user WRITE" ) !== FALSE) {
				$user_privileges [] = array (
						'privilege' => 'LOCK TABLES',
						'granted' => TRUE );
				db_query ( "UNLOCK TABLES" );
			} else {
				$user_privileges [] = array (
						'privilege' => 'LOCK TABLES',
						'granted' => FALSE );
			}
		}
	}
	
	return $user_privileges;
}

/**
 * A _slightly_ modified version of this function from phpMyAdmin
 *
 * Note: This function is a little slow, but the benefit is, that you can correctly
 * process a sql file where statements are not necessarily formatted in a consistent manner!
 *
 * Removes comment lines and splits up large sql files into individual queries
 *
 * Last revision: September 23, 2001 - gandon
 *
 * @param   array    the splitted sql commands
 * @param   string   the sql commands
 *
 * @return  boolean  always true
 *
 * @access  public
 */
function split_sql_file($sql, &$pieces) {
	// Quick hack to support end of file comments!
	$sql = trim ( $sql ) . "\n";
	$sql_len = strlen ( $sql );
	$char = '';
	$string_start = '';
	$in_string = FALSE;
	
	for($i = 0; $i < $sql_len; ++ $i) {
		$char = $sql [$i];
		
		// We are in a string, check for not escaped end of strings except for
		// backquotes that can't be escaped
		if ($in_string) {
			for(;;) {
				$i = strpos ( $sql, $string_start, $i );
				// No end of string found -> add the current substring to the
				// returned array
				if (! $i) {
					$pieces [] = $sql;
					return TRUE;
				} 				// Backquotes or no backslashes before quotes: it's indeed the
				// end of the string -> exit the loop
				else if ($string_start == '`' || $sql [$i - 1] != '\\') {
					$string_start = '';
					$in_string = FALSE;
					break;
				} 				// one or more Backslashes before the presumed end of string...
				else {
					// ... first checks for escaped backslashes
					$j = 2;
					$escaped_backslash = FALSE;
					while ( $i - $j > 0 && $sql [$i - $j] == '\\' ) {
						$escaped_backslash = ! $escaped_backslash;
						$j ++;
					}
					// ... if escaped backslashes: it's really the end of the
					// string -> exit the loop
					if ($escaped_backslash) {
						$string_start = '';
						$in_string = FALSE;
						break;
					} 					// ... else loop
					else {
						$i ++;
					}
				} // end if...elseif...else
			} // end for
		} 		// end if (in string)
		

		// We are not in a string, first check for delimiter...
		else if ($char == ';') {
			// if delimiter found, add the parsed part to the returned array
			

			$pieces [] = substr ( $sql, 0, $i );
			$sql = ltrim ( substr ( $sql, min ( $i + 1, $sql_len ) ) );
			$sql_len = strlen ( $sql );
			if ($sql_len) {
				$i = - 1;
			} else {
				// The submited statement(s) end(s) here
				return TRUE;
			}
		} 		// end else if (is delimiter)
		

		// ... then check for start of a string,...
		else if (($char == '"') || ($char == '\'') || ($char == '`')) {
			$in_string = TRUE;
			$string_start = $char;
		} 		// end else if (is start of string)
		

		// ... for start of a comment (and remove this comment if found)...
		else if ($char == '#' || ($char == ' ' && $i > 1 && $sql [$i - 2] . $sql [$i - 1] == '--')) {
			// starting position of the comment depends on the comment type
			$start_of_comment = (($sql [$i] == '#') ? $i : $i - 2);
			// if no "\n" exists in the remaining string, checks for "\r"
			// (Mac eol style)
			$end_of_comment = (strpos ( ' ' . $sql, "\012", $i + 2 )) ? strpos ( ' ' . $sql, "\012", $i + 2 ) : strpos ( ' ' . $sql, "\015", $i + 2 );
			
			if (! $end_of_comment) {
				// no eol found after '#', add the parsed part to the returned
				// array and exit
				$pieces [] = trim ( substr ( $sql, 0, $i - 1 ) );
				return TRUE;
			} else {
				$sql = substr ( $sql, 0, $start_of_comment ) . ltrim ( substr ( $sql, $end_of_comment ) );
				$sql_len = strlen ( $sql );
				$i --;
			} // end if...else
		} // end else if (is comment)
	} // end for
	

	// add any rest to the returned array
	if (strlen ( $sql ) > 0 && preg_match ( '/[^[:space:]]+/', $sql )) {
		$pieces [] = $sql;
	}
	
	// Return array of sql lines.
	return $pieces;
} // end of the 'split_sql_file()' function


/**
	Report errors only
	
	@param $sql - if this function is being called from exec_install_sql,
	assume that the prefix has already been applied if applicable.
*/
function exec_install_sql_statement($sql, &$error) {
	$result = db_query ( $sql );
	if ($result) {
		$error = NULL;
		return TRUE;
	} else {
		$errno = db_errno ();
		$error = array (
				'error' => db_error () . ' (' . $errno . ')',
				'detail' => $sql );
		
		// Need to keep this up to date, for any errors which are not
		// strictly errors!
		

		if ($errno == 1062) // row already exists
			return TRUE;
		else if ($errno == 1060) // column already exists
			return TRUE;
		else if ($errno == 1091) // column cannot be dropped because it no longer exists.
			return TRUE;
		else if ($errno == 1050) // table already exists
			return TRUE;
		else
			return FALSE;
	}
}

/**
Will return an array of all errors that have occurred. The
culprit SQL statements will also be returned.
*/
function exec_install_sql($sqltext, &$errors) {
	$queries = array ();
	split_sql_file ( $sqltext, $queries );
	
	$return_val = TRUE;
	foreach ( $queries as $sql ) {
		if (! exec_install_sql_statement ( $sql, $error )) {
			$return_val = FALSE;
			$errors [] = $error;
		}
		
		// echo out a character per SQL statement, so the browser does not close the connection.
		echo ' ';
	}
	
	return $return_val;
}

/**
	Loads the $filename from patch/$from/ directory and calls exec_install_sql($text) on the
	contents.  This function and the exec_sql(...) one will display results of
	queries executed.
*/
function exec_install_sql_file($sqlfile, &$errors) {
	$errormsg = NULL;
	$sqltext = file_get_contents ( $sqlfile );
	if ($sqltext === FALSE) {
		$errors [] = 'Error loading ' . $sqlfile;
		return FALSE;
	} else {
		// evaulate the sql contents of this textfile
		return exec_install_sql ( $sqltext, $errors );
	}
}

/**
	Check if a given table exists or not
*/
function check_opendb_table($table) {
	// In this case the db_query() would return FALSE, if
	// table does not exist.
	$result = db_query ( "DESCRIBE $table" );
	if ($result) {
		db_free_result ( $result );
		
		// The table exists (Does not have to have any records!)
		return TRUE;
	}
	
	//else
	return FALSE;
}

/**
	Check if a table has any records.
*/
function count_opendb_table_rows($table) {
	// Only get one row, to save processing them.
	$result = db_query ( "SELECT count(*) as count FROM $table" );
	
	// In this case the db_query() would return FALSE, if table does not exist.
	if ($result) {
		if (db_num_rows ( $result ) > 0) {
			$found = db_fetch_assoc ( $result );
			db_free_result ( $result );
			if ($found !== FALSE) {
				return ( int ) $found ['count']; // So that === will evaluate successfully!
			}
		} else {
			db_free_result ( $result );
			return 0;
		}
	}
	
	//else
	return FALSE;
}

/**
	count the number fields in table
*/
function count_opendb_table_columns($table) {
	$result = db_query ( "SELECT * FROM $table LIMIT 0,1" );
	if ($result) {
		// count fields (columns) in table
		return db_num_fields ( $result );
	}
	
	//else
	return FALSE;
}

/**
 * Initializes a database and user for OpenDB with a super- user (root) on MySQL 3.22.11
 * and above. If unsuccessful the result will be the mysql error message,
 * otherwise no value is returned.
 * @link http://dev.mysql.com/doc/mysql/ MySQL Documentation
 *
 * @param string dbserver host running database server (eg 'localhost')
 * @param string username login of database administrator (eg 'root')
 * @param string password credentials used to authenticate (eg '')
 * @param string instance name of the database to create (eg 'opendb')
 * @param string authorized_host webserver connecting to db (eg 'localhost')
 */
function create_opendb_user_and_database($db_root_username, $db_root_password, $hostname, $database, $username, $password, &$error) {
	$link = db_connect ( $hostname, $db_root_username, $db_root_password, 'mysql', FALSE );
	if ($link !== FALSE) {
		if (db_query ( 'CREATE DATABASE ' . $database, $link )) {
			$sqltext = "GRANT ALL PRIVILEGES ON " . $database . ".* TO " . $username . "@" . $hostname . " IDENTIFIED BY '" . $password . "'";
			
			if (db_query ( $sqltext, $link )) {
				return TRUE;
			} else {
				$error = db_error ( $link );
				return FALSE;
			}
		} else {
			$error = db_error ( $link );
			return FALSE;
		}
	} else {
		$error = db_error (); //default link here, as no link was returned
		return FALSE;
	}
}

/**
	Check for existence of the database.

	returns FALSE if database@hostname is invalid.
*/
function check_opendb_database($hostname, $database, $username, $password, &$error) {
	$link = db_connect ( $hostname, $username, $password, $database, FALSE );
	if ($link !== FALSE) {
		db_close ( $link );
		return TRUE;
	} else {
		$error = db_error ();
		return FALSE;
	}
}

/**
	Returns a version which will be one of:
		0.81
		0.80
		
	If its determined that the database version of opendb is pre-0.80, this method
	will return FALSE
*/
function install_determine_opendb_database_version() {
	if (check_opendb_table ( 's_site_plugin' )) {
		return '0.81';
	} else if (check_opendb_table ( 's_address_type' )) {
		return '0.80';
	} else {
		return FALSE;
	}
}

/**
* Return TRUE if up to date, otherwise FALSE
* 
* Assumes database exists
*/
function check_opendb_version() {
	$opendb_release_version = fetch_opendb_release_version();
	
	if ($opendb_release_version !== FALSE) {
		// the $opendb_release_version is unlikely to be larger than get_opendb_version(),
		// so this could be simplified to a '=', but leave as is.
		if (opendb_version_compare ( $opendb_release_version, get_opendb_version(), '>=' )) {
			return TRUE;
		}
	}
	
	//else
	return FALSE;
}

/**
 */
function fetch_database_tables_r($dblink = NULL) {
	$table_r = array ();
	
	$results = db_query ( "SHOW TABLES", $dblink );
	if ($results && db_num_rows ( $results ) > 0) {
		while ( $record_r = db_fetch_row ( $results ) ) {
			$table_r [] = $record_r [0];
		}
		db_free_result ( $results );
	}
	
	return $table_r;
}

/**
 * For the list of database tables provided, will return a list of all tables
 * that are opendb tables, as provided in the $table_r parameter.  If a table
 * prefix is provided, then only tables in the list that have that prefix will
 * be returned.
 */
function install_filter_opendb_tables($tables_r, $table_prefix = NULL) {
	$opendb_table_r = array ();
	
	if (is_array ( $tables_r )) {
		if ($table_prefix == NULL) // just for sanity sake
			$table_prefix = '';
		
		$opendb_table_list_r = fetch_opendb_table_list_r ();
		foreach ( $opendb_table_list_r as $table ) {
			if ($table != 's_opendb_release') {
				if (array_search ( $table_prefix . $table, $tables_r ) !== FALSE) {
					$opendb_table_r [] = $table;
				}
			}
		}
	}
	
	return $opendb_table_r;
}

function install_determine_opendb_database_status(&$db_details_r, &$db_version) {
	$dblink = db_connect ( $db_details_r ['host'], $db_details_r ['username'], $db_details_r ['passwd'], $db_details_r ['dbname'], FALSE );
	if ($dblink !== FALSE) {
		// get a list of ALL tables from the database specified, not just opendb tables.
		$tables_r = fetch_database_tables_r ( $dblink );
		
		db_close ( $dblink );
		
		if (is_not_empty_array ( $tables_r )) {
			// at this point we want to see if opendb tables exist, with the specified prefix.
			$opendb_table_r = install_filter_opendb_tables ( $tables_r, $db_details_r ['table_prefix'] );
			if (is_not_empty_array ( $opendb_table_r )) {
				if (array_search ( 's_config_group', $opendb_table_r ) !== FALSE)
					$db_version = '1.0';
				else if (array_search ( 's_site_plugin', $opendb_table_r ) !== FALSE)
					$db_version = '0.81';
				else if (array_search ( 's_address_type', $opendb_table_r ) !== FALSE)
					$db_version = '0.80';
				
				return 'OPENDB_DATABASE_EXISTS';
			} else {
				// try again without the prefix, if we find some tables, we should provide a warning.
				if (strlen ( $db_details_r ['table_prefix'] ) > 0) {
					$opendb_table_r = install_filter_opendb_tables ( $tables_r );
					if (is_not_empty_array ( $opendb_table_r )) {
						if (array_search ( 's_config_group', $opendb_table_r ) !== FALSE)
							$db_version = '1.0';
						else if (array_search ( 's_site_plugin', $opendb_table_r ) !== FALSE)
							$db_version = '0.81';
						else if (array_search ( 's_address_type', $opendb_table_r ) !== FALSE)
							$db_version = '0.80';
						
						return 'OPENDB_DATABASE_WITH_NO_PREFIX_EXISTS';
					}
				}
				
				// need to try and work out what prefix might be appropriate here
				$prefixes_r = install_determine_db_prefixes ( $tables_r );
				if (is_not_empty_array ( $prefixes_r )) {
					if (count ( $prefixes_r ) == 1) {
						$db_details_r ['table_prefix'] = $prefixes_r [0];
						return 'OPENDB_DATABASE_WITH_PREFIX_EXISTS';
					} else {
						return 'OPENDB_DATABASE_WITH_MULTIPLE_PREFIXES_EXISTS';
					}
				}
				
				return 'DATABASE_WITH_TABLES_EXISTS';
			}
		} else {
			return 'DATABASE_WITH_NO_TABLES_EXISTS';
		}
	} else {
		return FALSE; // should never happen
	}
}

function install_determine_db_prefixes($db_tables_r) {
	$prefixes = array ();
	
	$opendb_table_list_r = fetch_opendb_table_list_r ();
	foreach ( $opendb_table_list_r as $odb_table ) {
		reset ( $db_tables_r );

		foreach ( $db_tables_r as $table ) {
			if (preg_match ( "/^(.+)" . preg_quote ( $odb_table ) . "$/", $table, $matches )) {
				// exclude prefix match for tables that end in _item
				if ($odb_table == 'item' && ends_with ( $table, '_item' )) {
					continue; // skip this one.
				}
				
				if (array_search ( $matches [1], $prefixes ) === FALSE) {
					$prefixes [] = $matches [1];
				}
			}
		}
	}
	
	return $prefixes;
}

/**
	Check whether opendb is installed or not installed.
	
	@return TRUE - if at least one table found
	@return FALSE - if no tables at all.
*/
function is_opendb_partially_installed() {
	$table_list_r = fetch_opendb_table_list_r ();
	
	reset ( $table_list_r );
	foreach ( $table_list_r as $table ) {
		// ignore release table
		if ($table != 's_opendb_release') {
			if (check_opendb_table ( $table )) {
				return TRUE;
			}
		}
	}
	
	//else - no tables found
	return FALSE;
}

/**
	@param $latest_to_version - is latest version that a upgrade is provided for
*/
function build_upgrader_list(&$upgrader_rs, &$latest_to_version) {
	$latest_to_version = NULL;
	
	$upgrader_rs = array ();
	$handle = @opendir ( './install/upgrade/' );
	while ( $file = readdir ( $handle ) ) {
		if (! preg_match ( "/^\./", $file ) && preg_match ( "/Upgrader_(.*).class.php$/", $file, $regs )) {
			$upgraderRef = 'Upgrader_' . $regs [1];
			
			include_once ('./install/upgrade/' . $upgraderRef . '.class.php');
			$upgraderPlugin = new $upgraderRef();
			
			if ($upgraderPlugin !== NULL) {
				$upgrader_rs [] = array (
						'to_version' => $upgraderPlugin->getToVersion (),
						'from_version' => $upgraderPlugin->getFromVersion (),
						'description' => $upgraderPlugin->getDescription (),
						'upgrader_plugin' => $upgraderRef );
				
				if ($latest_to_version == NULL 
						|| opendb_version_compare ( $upgraderPlugin->getToVersion (), $latest_to_version, '>' )) {
					$latest_to_version = $upgraderPlugin->getToVersion ();
				}
			}
		}
	}
	closedir ( $handle );
	
	return TRUE;
}

/**
	Retrieve plugin info responsible for latest s_opendb_release version
*/
function get_upgrader_r($db_version) {
	$upgrader_rs = NULL;
	$latest_to_version = NULL;
	
	build_upgrader_list ( $upgrader_rs, $latest_to_version );
	
	if (is_array ( $upgrader_rs ) && count ( $upgrader_rs ) > 0) {
		for($i = 0; $i < count ( $upgrader_rs ); $i ++) {
			if ($db_version == $upgrader_rs [$i] ['to_version'])
				return $upgrader_rs [$i];
		}
		
		for($i = 0; $i < count ( $upgrader_rs ); $i ++) {
			if ($latest_to_version == $upgrader_rs [$i] ['to_version'])
				return $upgrader_rs [$i];
		}
	}
	
	return FALSE;
}

/**
	Returns a list of all upgraders, including specific details about them including:
	    to_version=>
	    from_version=>
	    description=>
	    upgrader_plugin=>

	Will not display any plugins which are not appropriate for the $db_version_r, $opendb_version

	@param $db_version - is the version of the opendb database we are upgrading.
	@param $opendb_version - is the version of the code we are updating the database to
	@param $latest_to_version - is the latest upgrader to_version available.
*/
function get_upgraders_rs($db_version, $opendb_version, &$latest_to_version) {
	$all_upgrader_rs = NULL;
	
	build_upgrader_list ( $all_upgrader_rs, $latest_to_version );
	
	$upgraders_rs = array ();
	
	if (count ( $all_upgrader_rs ) > 0) {
		if (strlen ( $db_version ) == 0) {// no database installed so get a list of all
			$upgraders_rs [] = $upgrader_r;
		} else {
			// initial filter - 
			for($i = 0; $i < count ( $all_upgrader_rs ); $i ++) {
				$upgrader_r = $all_upgrader_rs [$i];
				
				if (opendb_version_compare ( $db_version, $upgrader_r ['from_version'], '>=' ) && opendb_version_compare ( $db_version, $upgrader_r ['to_version'], '<' )) {
					$upgraders_rs [] = $upgrader_r;
				}
			}
			
			// if no matches, then get latest and upgrade with that.
			if (count ( $upgraders_rs ) == 0) {
				for($i = 0; $i < count ( $all_upgrader_rs ); $i ++) {
					if ($latest_to_version == $upgrader_r ['to_version'] && opendb_version_compare ( $db_version, $upgrader_r ['to_version'], '<' )) {
						$upgraders_rs [] = $upgrader_r;
					}
				}
			}
		}
		
		if (count ( $upgraders_rs ) > 0) {
			// now have to process it to remove those options that are not appropriate
			if (strlen ( $db_version ) > 0) {
				$revised_list_rs = NULL;
				for($i = 0; $i < count ( $upgraders_rs ); $i ++) {
					$upgrader_r = $upgraders_rs [$i];
					
					// lets look for a from_version which matches db_version exactly, and don't bother
					// looking anywhere else if found.
					if (opendb_version_compare ( $db_version, $upgrader_r ['from_version'], '=' )) {
						$revised_list_rs [] = $upgrader_r;
						break;
					}
				}
				
				if (is_array ( $revised_list_rs ))
					return $revised_list_rs;
				else
					return $upgraders_rs;
			} else {
				return $upgraders_rs;
			}
		} else {		// empty array as last resort.
			return array ();
		}
	} else {
		$errors [] = 'No Upgrader plugin classes found - this is a fatal error!';
		return FALSE;
	}
}

function is_upgrader_plugin($plugin) {
	if (strlen ( $plugin ) > 0 && file_exists ( './install/upgrade/' . $plugin . '.class.php' ))
		return TRUE;
	else
		return FALSE;
}

/**
	Get the last record added to the database.

	@param $upgrade_steps_complete - if TRUE, then get last s_opendb_release record, where upgrade_step IS NULL
*/
function fetch_opendb_release_version_r($upgrade_steps_complete = TRUE) {
	$query = "SELECT sequence_number, release_version, description, upgrade_step, upgrade_step_part, UNIX_TIMESTAMP(update_on) as update_on FROM s_opendb_release ";
	
	if ($upgrade_steps_complete)
		$query .= "WHERE upgrade_step IS NULL ";
	
	$query .= "ORDER BY sequence_number DESC LIMIT 0,1";
	
	$result = db_query ( $query );
	if ($result) {
		if (db_num_rows ( $result ) > 0) {
			$record_r = db_fetch_assoc ( $result );
			db_free_result ( $result );
			return $record_r;
		}
	}
	
	//else
	return FALSE;
}

function is_valid_opendb_release_table() {
	return check_opendb_table ( 's_opendb_release' ) && count_opendb_table_columns ( 's_opendb_release' ) == 6;
}

/**
	Get the last record added to the database.

    @param $upgrade_steps_complete - if TRUE, then get last s_opendb_release record, where upgrade_step IS NULL
*/
function fetch_opendb_release_version($upgrade_steps_complete = TRUE) {
	$record_r = fetch_opendb_release_version_r ( $upgrade_steps_complete );
	if (is_array ( $record_r )) {
		return $record_r ['release_version'];
	} else {
		return FALSE;
	}
}

function is_exists_opendb_release_version($release_version) {
	$query = "SELECT 'X' FROM s_opendb_release WHERE release_version = '$release_version'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

function insert_opendb_release($release_version, $description, $step = '0') {
	$description = addslashes ( replace_newlines ( trim ( $description ) ) );
	
	$query = "INSERT INTO s_opendb_release (release_version, description, upgrade_step)" . "VALUES ('$release_version','$description', " . (is_numeric ( $step ) ? "'$step'" : "NULL") . ")";
	
	$insert = db_query ( $query );
	if ($insert && db_affected_rows () > 0) {
		$new_item_id = db_insert_id ();
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
				$release_version,
				$description ) );
		return $new_item_id;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$release_version,
				$description ) );
		return FALSE;
	}
}

/*
*/
function update_opendb_release_step($release_version, $step, $step_part = NULL) {
	$query = "UPDATE s_opendb_release " . "SET upgrade_step = " . (is_numeric ( $step ) ? "'$step'" : "NULL") . " " . ",upgrade_step_part = " . (is_numeric ( $step_part ) ? "'$step_part'" : "NULL") . " " . "WHERE release_version = '$release_version'";
	
	$update = db_query ( $query );
	
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows ();
	if ($update && $rows_affected !== - 1) {
		if ($rows_affected > 0)
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$release_version,
					$step,
					$step_part ) );
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$release_version,
				$step,
				$step_part ) );
		return FALSE;
	}
}
?>
