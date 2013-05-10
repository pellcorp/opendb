<?php
/* 	
    OpenDb Media Collector Database
    Copyright (C) 2001-2012 by Jason Pell

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
include_once("lib/fileutils.php");
include_once("lib/import.php");

/**
 * Add randomness to avoid 
 *
 * @param unknown_type $directory
 * @param unknown_type $new_sequence_number
 * @return unknown
 */
function import_get_cache_file($new_sequence_number) {
	$randomNum = generate_random_num();
	return $new_sequence_number . '_' . $randomNum . '.cache';
}

function import_cache_get_cache_directory() {
	$cacheDir = OPENDB_ITEM_UPLOAD_DIRECTORY;
	if (is_dir($cacheDir)) {
		if (ends_with($cacheDir, '/'))
			$cacheDir = substr($cacheDir, 0, -1);

		return $cacheDir;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Import Cache directory does not exist', array($cacheDir));
		return FALSE;
	}
}

/**
 * @$include_content if TRUE, will request content as well.
 */
function fetch_import_cache_r($sequence_number, $user_id = NULL) {
	if (is_numeric($sequence_number)) {
		$query = "SELECT user_id, plugin_name, content_length, cache_file " . "FROM import_cache " . "WHERE sequence_number = '$sequence_number'";

		// allows to enforce the fact that this user owns the
		// particular record.
		if (strlen($user_id) > 0) {
			$query .= " AND user_id = '$user_id'";
		}

		$result = db_query($query);
		if ($result && db_num_rows($result) > 0) {
			$found = db_fetch_assoc($result);
			db_free_result($result);
			return $found;
		}
	}

	//else
	return FALSE;
}

/**
 * Validate that a file reference is a legal relative opendb file to
 * save into the following locations:
 * 	importcache
 * 	itemcache
 * 	upload
 *
 * @param unknown_type $filename
 */
function is_valid_opendb_file($fileLocation) {
	if (strlen($fileLocation) > 0 && $fileLocation != '.' && $fileLocation != '..') {
		return TRUE;
	}

	//else
	return FALSE;
}

/**
 * Returns an open file pointer that must be closed
 *
 * @param unknown_type $sequence_number
 * @return unknown
 */
function import_cache_fetch_file($sequence_number) {
	$query = "SELECT cache_file FROM import_cache WHERE sequence_number = '$sequence_number'";
	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		$record_r = db_fetch_assoc($result);
		db_free_result($result);
		if ($record_r !== FALSE) {
			$directory = import_cache_get_cache_directory();
			if (is_valid_opendb_file($directory . '/' . $record_r['cache_file'])) {
				$import_file = file_open($directory . '/' . $record_r['cache_file'], 'rb');
				if ($import_file) {
					return $import_file;
				} else {

					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($sequence_number));
					return FALSE;
				}
			}
		}
	}

	//else
	return FALSE;
}

function import_cache_insert($user_id, $plugin_name, $infile_location) {
	if (file_exists($infile_location)) {
		$content_length = @filesize($infile_location);
		if ($content_length > 0) {
			$directory = import_cache_get_cache_directory();
			if (is_dir($directory)) {
				$query = "INSERT INTO import_cache(user_id, plugin_name, content_length, cache_file)" . " VALUES ('$user_id','$plugin_name','$content_length', '$cache_file')";

				$insert = db_query($query);
				if ($insert && db_affected_rows() > 0) {
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($user_id, $plugin_name, $cache_file));

					$new_sequence_number = db_insert_id();

					$cache_file = import_get_cache_file($new_sequence_number);
					if (copy($infile_location, $directory . '/' . $cache_file) !== FALSE) {
						// failure to store reference to cache file makes the whole process pointless.
						if (!import_cache_update_cachefile($new_sequence_number, $cache_file)) {
							return FALSE;
						}
					} else {
						opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Error copying files', array($user_id, $plugin_name, $infile_location, $directory . '/' . $cache_file));
						return FALSE;
					}

					return $new_sequence_number;
				} else {
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($user_id, $plugin_name, $cache_file));
					return FALSE;
				}
			} else {
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Importcache directory is not accessible', array($user_id, $plugin_name, $infile_location, $directory));
				return FALSE;
			}
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Input file is empty', array($user_id, $plugin_name, $infile_location));
			return FALSE;
		}
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Input file not found', array($user_id, $plugin_name, $infile_location));
		return FALSE;
	}
}

function import_cache_update_cachefile($sequence_number, $cache_file) {
	$cache_file = addslashes($cache_file);

	$update = db_query("UPDATE import_cache SET cache_file = '$cache_file' WHERE sequence_number = '$sequence_number'");
	$rows_affected = db_affected_rows();
	if ($update && db_affected_rows() > 0) {
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($sequence_number, $cache_file));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($sequence_number, $cache_file));
		return FALSE;
	}
}

function import_cache_delete($sequence_number) {
	$cache_r = fetch_import_cache_r($sequence_number);
	if (is_array($cache_r)) {
		$query = "DELETE FROM import_cache WHERE sequence_number = '$sequence_number'";
		$delete = db_query($query);
		if ($delete && db_affected_rows() > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Deleted import_cache record', array($sequence_number));

			$directory = import_cache_get_cache_directory();
			return delete_file($directory . '/' . $cache_r['cache_file']);
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($sequence_number));
			return FALSE;
		}
	} else {
		// already deleted
		return TRUE;
	}
}

function import_cache_delete_for_user($user_id) {
	// hack
	import_cache_get_cache_directory();

	if (db_query("LOCK TABLES import_cache WRITE")) {
		$query = "SELECT sequence_number FROM import_cache WHERE user_id = '$user_id'";
		$results = db_query($query);
		if ($results) {
			while ($import_cache_r = db_fetch_assoc($results)) {
				import_cache_delete($import_cache_r['sequence_number']);
			}
			db_free_result($results);
		}

		db_query("UNLOCK TABLES");

		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($user_id));
		return FALSE;
	}
}

function import_cache_deleteall() {
	// hack
	import_cache_get_cache_directory();

	if (db_query("LOCK TABLES import_cache WRITE")) {
		$query = "SELECT sequence_number FROM import_cache";
		$results = db_query($query);
		if ($results) {
			while ($import_cache_r = db_fetch_assoc($results)) {
				import_cache_delete($import_cache_r['sequence_number']);
			}
			db_free_result($results);
		}

		db_query("UNLOCK TABLES");
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error());
		return FALSE;
	}
}
?>