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

include_once("lib/logging.php");

if (!defined('__OPENDB_BASEDIR__')) {
	define('__OPENDB_BASEDIR__', dirname(dirname(__FILE__)));
}

function get_opendb_basedir() {
	return __OPENDB_BASEDIR__;
}

function get_opendb_file($filename) {
	$baseDir = get_opendb_basedir();
	return $baseDir . '/' . $filename;
}

function opendb_file_exists($filename) {
	$baseDir = get_opendb_basedir();
	return @file_exists($baseDir . '/' . $filename);
}

function get_opendb_relative_file($path) {
	$baseDir = get_opendb_basedir();
	if (starts_with($path, $baseDir)) {
		return substr($path, strlen($baseDir) + 1); // remove the slash as well!
	} else {
		return $path; // fallback not much else to do!
	}
}

/**
 * Its assumed that the filename will be relative to the base directory returned
 * by get_opendb_basedir()
 *
 * This is a convenient method to work for both tests and production to support
 * relative file paths based on the root (the parent of lib in this case)
 *
 * @param String $filename
 * @param String $mode -
 *
 * @see get_opendb_basedir()
 */
function file_open($filename, $mode) {
	if (!starts_with($filename, '/')) {
		$filename = get_opendb_file($filename);
	}

	$resource = @fopen(get_opendb_file($filename), $mode);
	if ($resource !== FALSE) {
		return $resource;
	} else {
		return NULL;
	}
}

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
	$filelist = array();

	$handle = @opendir(get_opendb_file($dir));
	if ($handle) {
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && !is_dir($dir . '/' . $file)) {
				// get the extension first.
				$fileext = get_file_ext($file);

				if ($ext == NULL || (!is_array($ext) && $fileext == $ext) || (is_array($ext) && in_array($fileext, $ext))) {
					$filelist[] = $file;
				}
			}
		}
		closedir($handle);
	}

	return $filelist;
}

function get_dir_list($dir, $isValidFunc = NULL) {
	$dirlist = array();

	$dir = get_opendb_file($dir);
	$handle = @opendir($dir);
	while ($childdir = readdir($handle)) {
		if ($childdir != "." && $childdir != ".." && is_dir($dir . '/' . $childdir)) {
			if ($isValidFunc == NULL || $isValidFunc($childdir)) {
				$dirlist[] = $childdir;
			}
		}
	}
	closedir($handle);

	if (is_not_empty_array($dirlist)) {
		return $dirlist;
	} else { // empty array as last resort.
		return array();
	}
}

/**
 * Guarantees that any image sources referenced are relative to opendb and currently
 * to make this validation simpler, only images which have at most one directory
 * level deep are supported, all others have their directory information removed.
 *
 * @param unknown_type $src
 * @return unknown
 */
function safe_filename($src) {
	// ensure dealing with only one path separator!
	$src = str_replace("\\", "/", $src);

	$file = basename($src);

	$dir = dirname($src);
	if ($dir == '/' || $dir == '.') {
		$dir = NULL;
	}

	if (strpos($dir, "/") !== FALSE) {
		return $file; // return basename as illegal pathname - more than one directory path - only one level supported currently!
	} else if (strlen($dir) > 0) {
		return $dir . '/' . $file;
	} else {
		return $file;
	}
}

/**
    Create a temporary file using configured temporary directory
 */
function opendb_tempnam($prefix) {
	$temp_dir = get_opendb_config_var('site', 'tmpdir');
	return tempnam($temp_dir, $prefix);
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
	if (ends_with($dir, '/')) {
		$dir = substr($dir, 0, -1);
	}

	$real_dir_path = realpath($dir);
	if (substr($real_dir_path, -1) != '/') {
		$real_dir_path .= '/';
	}

	$tempfile = tempnam($real_dir_path, $prefix);
	$name = basename($tempfile);

	if (is_file($real_dir_path . $name)) {
		return $dir . '/' . $name;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Temporary file could not be created', array($dir, $prefix, $real_dir_path));

		@unlink($name);
		return FALSE;
	}
}

/**
    Get file extension.
 */
function get_file_ext($filename) {
	$lastindex = strrpos($filename, ".");
	if ($lastindex !== FALSE && $lastindex > 0) {
		return substr($filename, $lastindex + 1);//+1 for the "."
	} else {
		return FALSE;// What else can I do
	}
}

function parse_file($filename) {
	$lastindex = strrpos($filename, ".");
	if ($lastindex !== FALSE && $lastindex > 0) {
		return array('name' => substr($filename, 0, $lastindex), 'extension' => substr($filename, $lastindex + 1));//+1 for the "."
	} else {
		return FALSE;// What else can I do!
	}
}

/**
    Will return the filename extension if it is legal. Or false
    otherwise.
    
    @param $extensions - comma delimited set of extensions, NOT an ARRAY!!!
 */
function get_valid_extension($filename, $extensions) {
	$ext = strtolower(get_file_ext($filename));
	if ($ext !== FALSE) {
		// If no extension specified this indicates that all are legal.
		if (strlen($extensions) == 0) {
			return $ext;
		} else {
			// As $extensions is not empty, $extensions_r will have
			// at least one element.
			$extensions_r = explode(",", strtolower($extensions));
			if (in_array($ext, $extensions_r)) {
				return $ext;
			}
		}
	}

	//else
	return FALSE;
}

function delete_file($filename) {
	$filename = get_opendb_file($filename);
	if (@is_file($filename)) {
		if (@unlink($filename)) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($filename));
			return TRUE;
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, NULL, array($filename));
			return FALSE;
		}
	} else {
		return FALSE;
	}
}
?>