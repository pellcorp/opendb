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
include_once("./lib/http.php");

// definitions for use in logging that may not be defined in older version of PHP, but
// which we want to be able to assume exist.
if (! defined ( '__FUNCTION__' ))
	define ( '__FUNCTION__', 'unknown' );

if (! defined ( '__CLASS__' ))
	define ( '__CLASS__', 'unknown' );

if (! defined ( '__METHOD__' ))
	define ( '__METHOD__', 'unknown' );

define ( 'OPENDB_LOG_ERROR', 'E' );
define ( 'OPENDB_LOG_WARN', 'W' );
define ( 'OPENDB_LOG_INFO', 'I' );

function get_relative_opendb_filename($filename) {
	// make all unix for ease of reference
	$dir = trim ( str_replace ( '\\', '/', __FILE__ ) ); // Should end in lib/logging.php
	$index = strpos ( $dir, 'lib/logging.php' );
	if ($index !== FALSE) {
		$dir = substr ( $dir, 0, $index );
		
		$index = strpos ( $filename, $dir );
		if ($index !== FALSE) {
			return substr ( $filename, strlen ( $dir ) );
		}
	}
	
	//else
	return $filename;
}

/**
	A line is space delimited, any columns with spaces in them, should use a quote
	or a square bracket to encode the column.
	
	Order of columns returned will be:
		ip, uid, datetime, type, function, parameters, message
*/
function fget_tokenised_log_entry(&$file) {
	$token_names = array (
			'datetime',
			'type',
			'ip',
			'user_id',
			'admin_user_id',
			'file',
			'function',
			'parameters',
			'message' );
	$tokens = NULL;
	$in_quote = FALSE;
	$in_bracket = FALSE;
	$column = '';
	$count = 0;
	
	while ( ! feof ( $file ) && ($tokens == NULL || count ( $tokens ) < count ( $token_names )) ) {
		$line = trim ( fgets ( $file, 4096 ) );
		
		for($i = 0; $i < strlen ( $line ); $i ++) {
			switch ($line[$i]) {
				case "\\" :
					if ($i < strlen ( $line + 1 ) && $line[$i + 1] != '"') {
						$column .= $line[$i];
					}
					break;
				
				case '"' :
					if ($i == 0 || $line[$i - 1] != "\\") {
						$in_quote = ! $in_quote;
					} else {
						$column .= $line[$i];
					}
					break;
				
				case '[' :
					if (! $in_quote && $token_names[$count] == 'datetime') // only column allowed to be enclodes this way is datetime
						$in_bracket = TRUE;
					else
						$column .= $line[$i];
					break;
				
				case ']' :
					if ($in_bracket && ! $in_quote && $token_names[$count] == 'datetime') // only column allowed to be enclodes this way is datetime
						$in_bracket = FALSE;
					else
						$column .= $line[$i];
					break;
				
				case ' ' :
				case '\r' :
				case '\n' :
					if (! $in_bracket && ! $in_quote) {
						// end of column
						$tokens[$token_names[$count]] = stripslashes ( trim ( $column ) );
						
						if (strlen ( $tokens[$token_names[$count]] ) == 0 || $tokens[$token_names[$count]] == '-')
							$tokens [$token_names [$count]] = NULL;
						
						$column = '';
						$count ++;
					}
					
					$column .= $line[$i];
					break;
				
				default :
					$column .= $line[$i];
			}
		}
		
		// todo - remove code duplication here!
		if (! $in_bracket && ! $in_quote) {
			// end of column
			$tokens[$token_names[$count]] = stripslashes( trim( $column ) );
			
			if (strlen ( $tokens[$token_names[$count]] ) == 0 || $tokens [$token_names [$count]] == '-')
				$tokens[$token_names[$count]] = NULL;
			
			$column = '';
			$count ++;
		}
	} //while
	

	if ($tokens != NULL) {
		return $tokens;
	} else {
		return FALSE;
	}
}

/**
	Appends the given text to the logfile

	This function does some checking to make sure the entry does not
	go over 4000 characters, so as not to confuse the logfile.php
	script.
*/
function opendb_logger($msgtype, $file, $function, $message = NULL, $params_r = NULL) {
	if (get_opendb_config_var ( 'logging', 'enable' ) !== FALSE) 	// only log if enabled in config.php
{
		$entry ['datetime'] = date ( "d/m/y H:i:s" ); // get time and date
		$entry ['ip'] = ifempty ( get_http_env ( "REMOTE_ADDR" ), "0.0.0.0" );
		
		$entry ['user_id'] = get_opendb_session_var ( 'user_id' );
		
		$entry ['admin_user_id'] = get_opendb_session_var ( 'admin_user_id' );
		if (strlen ( $entry ['admin_user_id'] ) == 0)
			$entry ['admin_user_id'] = '-';
		
		$msgtype = strtoupper ( $msgtype );
		if (! in_array ( $msgtype, array (
				'E',
				'I',
				'W' ) ))
			$msgtype = 'E';
			
			// temp bit here!
		switch ($msgtype) {
			case 'E' :
				$entry ['type'] = 'ERROR';
				break;
			case 'W' :
				$entry ['type'] = 'WARN';
				break;
			case 'I' :
				$entry ['type'] = 'INFO';
				break;
		}
		
		$entry ['parameters'] = expand_opendb_logger_params ( $params_r );
		if (strlen ( $entry ['parameters'] ) == 0) {
			$entry ['parameters'] = '-';
		}
		
		if (strlen ( $file ) > 0)
			$entry ['file'] = str_replace ( '\\', '/', $file );
		else
			$entry ['file'] = '-';
		
		if (strlen ( $function ) > 0 && $function != 'unknown')
			$entry ['function'] = $function;
		else
			$entry ['function'] = '-';
		
		if (strlen ( $message ) > 0)
			$entry ['message'] = $message;
		else
			$entry ['message'] = '-';
		
		$fileptr = @fopen( get_opendb_config_var( 'logging', 'file' ), 'a' );
		if ($fileptr) 		// verify file was opened
{
			$entry ['datetime'] = '[' . $entry ['datetime'] . ']';
			
			if ($entry ['parameters'] != '-')
				$entry ['parameters'] = '"' . addslashes ( replace_newlines ( $entry ['parameters'] ) ) . '"';
			
			if ($entry ['message'] != '-')
				$entry ['message'] = '"' . addslashes ( replace_newlines ( $entry ['message'] ) ) . '"';
			
			$line = $entry ['datetime'] . ' ' . $entry ['type'] . ' ' . $entry ['ip'] . ' ' . $entry ['user_id'] . ' ' . $entry ['admin_user_id'] . ' ' . $entry ['file'] . ' ' . $entry ['function'] . ' ' . $entry ['parameters'] . ' ' . $entry ['message'];
			
			fwrite ( $fileptr, $line . "\n" );
			fclose ( $fileptr );
		}
	}
}

function expand_opendb_logger_params($params_r) {
	$params = '';
	if (! is_array ( $params_r )) {
		$params = $params_r;
	} else {
		reset ( $params_r );
		foreach ($params_r as $key => $value) {
			if (strlen ( $params ) > 0)
				$params .= ', ';
			
			if (is_array ( $value )) {
				$params .= "{ ";
				$params .= expand_opendb_logger_params ( $value );
				$params .= " }";
			} else {
				// might not provide named key values, as for example insert statements its assumed parameters are listed in order
				// passed into the function.
				if (! is_numeric ( $key ))
					$params .= $key . '=' . stripslashes ( $value );
				else
					$params .= stripslashes ( $value );
			}
		}
	}
	return $params;
}
?>
