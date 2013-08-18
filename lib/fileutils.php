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
include_once ("./lib/logging.php");

/**
* This will always return an array, no
* matter what.  If no files in specified
* $from directory (with specified $ext)
* an empty array will be returned instead.
* 
* Does not RECURSE subdirectories.
* 
* @param $ext IS CASE SENSITIVE and can be an array
*/
function get_file_list($dir, $ext = NULL) {
	$filelist = array ();
	
	$handle = @opendir ( $dir );
	if ($handle) {
		while ( $file = readdir ( $handle ) ) {
			if ($file != "." && $file != ".." && ! is_dir ( $dir . '/' . $file )) {
				// get the extension first.
				$fileext = get_file_ext ( $file );
				
				if ($ext == NULL || (! is_array ( $ext ) && $fileext == $ext) || (is_array ( $ext ) && in_array ( $fileext, $ext ))) {
					$filelist [] = $file;
				}
			}
		}
		closedir ( $handle );
	}
	
	return $filelist;
}

/**
	Create a temporary file using configured temporary directory
*/
function opendb_tempnam($prefix) {
	$temp_dir = get_opendb_config_var ( 'site', 'tmpdir' );
	return tempnam ( $temp_dir, $prefix );
}

/**
 * ensure that tempname is created in correct directory, or bail out
 *
 * @param unknown_type $dir
 * @param unknown_type $prefix
 * @return unknown
 */
function dir_tempnam($dir, $prefix) {
	// ensure relative directory does not have last slash
	if (ends_with ( $dir, '/' ))
		$dir = substr ( $dir, 0, - 1 );
	
	$real_dir_path = realpath ( $dir );
	if (substr ( $real_dir_path, - 1 ) != '/')
		$real_dir_path .= '/';
	
	$tempfile = tempnam ( $real_dir_path, $prefix );
	$name = basename ( $tempfile );
	
	if (is_file ( $real_dir_path . $name )) {
		return $dir . '/' . $name;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Temporary file could not be created', array (
				$dir,
				$prefix,
				$real_dir_path ) );
		
		@unlink ( $name );
		return FALSE;
	}
}

function get_file_ext($filename) {
	$lastindex = strrpos ( $filename, "." );
	if ($lastindex !== FALSE && $lastindex > 0)
		return substr ( $filename, $lastindex + 1 ); //+1 for the "."
	else
		return FALSE; // What else can I do!
}

function parse_file($filename) {
	$lastindex = strrpos ( $filename, "." );
	if ($lastindex !== FALSE && $lastindex > 0) {
		return array (
				'name' => substr ( $filename, 0, $lastindex ),
				'extension' => substr ( $filename, $lastindex + 1 ) ); //+1 for the "."
	} else {
		return FALSE; // What else can I do!
	}
}

/**
	Will return the filename extension if it is legal. Or false
	otherwise.
*/
function get_valid_extension($filename, $extensions) {
	$ext = strtolower ( get_file_ext ( $filename ) );
	if ($ext !== FALSE) {
		// If no extension specified this indicates that all are legal.
		if (strlen ( $extensions ) == 0) {
			return $ext;
		} else {
			// As $extensions is not empty, $extensions_r will have
			// at least one element.
			$extensions_r = explode ( ",", strtolower ( $extensions ) );
			if (in_array ( $ext, $extensions_r )) {
				return $ext;
			}
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
function is_exists_opendb_file($fileLocation) {
	if (strlen ( $fileLocation ) > 0 && $fileLocation != '.' && $fileLocation != '..') {
		return TRUE;
	}
	
	//else
	return FALSE;
}

function delete_file($filename) {
	if (@is_file ( $filename )) {
		if (@unlink ( $filename )) {
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$filename ) );
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, NULL, array (
					$filename ) );
			return FALSE;
		}
	} else {
		return FALSE;
	}
}
?>