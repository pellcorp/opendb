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
include_once("./lib/fileutils.php");

function get_page_id($url) {
	$index = strpos ( $url, "?" );
	if ($index !== FALSE) {
		$url = substr ( $url, 0, $index );
	}
	return basename ( $url, '.php' );
}

/**
	this script is included by include/begin.inc.php, so available
	to all user executable scripts.
*/
function redirect_login($PHP_SELF, $HTTP_VARS, $rememberMeLogin = FALSE) {
	$redirect = basename ( $PHP_SELF );
	
	$url = get_url_string ( $HTTP_VARS );
	if (strlen ( $url ) > 0)
		$redirect .= '?' . $url;
	
	
	opendb_redirect ( "login.php?op=login&rememberMeLogin=".($rememberMeLogin ? "true" : "false")."&redirect=" . urlencode ( $redirect ) );
}

/**
 * Simple HTTTP Location redirect
 *
 * A simple function to redirect browsers via the HTTP Location header.
 *
 * @param string $link The URL to redirect the user's browser to
 */
function opendb_redirect($link) {
	if (! is_url_absolute ( $link )) {
		$protocol = 'http';
		if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
			$protocol = 'https';
		}
		
		$host = $_SERVER ['HTTP_HOST'];
		
		// fix for windows
		$path = str_replace ( '\\', '/', dirname ( $_SERVER ['PHP_SELF'] ) );
		
		if (substr ( $path, - 1, 1 ) != '/') {
			$path .= '/';
		}
		$path .= $link;
		
		$url = $protocol . '://' . $host . $path;
		
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
				$link,
				$url ) );
		
		header( 'Location: ' . $url );
	} else {
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
				$link ) );
		
		header( 'Location: ' . $link );
	}
}

/**
* Original logic for checking file upload status taken 
* from phpMyAdmin db_details.php script
*/
function is_file_upload_enabled() {
	// Allows us to disable file upload at OpenDb level.
	if (get_opendb_config_var ( 'site', 'file_upload_enable' ) !== FALSE)
		return (ini_get ( 'file_uploads' ) == 1 || strtolower ( ini_get ( 'file_uploads' ) ) == 'on');
	else
		return get_opendb_config_var ( 'site', 'file_upload_enable' );
}

/*
* It seems that some sites have ini_get as a disabled function.  So all the logic
* to ascertain register_globals is in this function, where we can implement a workaround.
*/
function is_register_globals_enabled() {
	if (get_opendb_config_var ( 'site', 'register_globals_enabled' ) !== FALSE)
		return (ini_get ( 'register_globals' ) == 1 || strtolower ( ini_get ( 'register_globals' ) ) == 'on');
	else
		return get_opendb_config_var ( 'site', 'register_globals_enabled' );
}

/**
	Get access to a server variable value in a php
	version independant manner.
*/
function get_http_env($envname) {
	return $_SERVER[$envname];
}

/**
	Check if $url has a protocol at the start. 
*/
function is_url_absolute($url) {
	if (preg_match ( "!([a-zA-Z]+)://!", $url )) {
		return TRUE;
	} else
		return FALSE;
}

/**
	Fetches site url
*/
function get_site_url() {
	$protocol = get_site_protocol ();
	$host = get_site_host ();
	$port = get_site_port ();
	$path = get_site_path ();
	
	// do not display port if default port for either protocol.
	if (($protocol == 'http' && $port == '80') || ($protocol == 'https' && $port == '443'))
		$port = '';
	
	return $protocol . "://" . $host . (strlen ( $port ) > 0 ? ":" . $port : "") . $path;
}

function get_site_protocol() {
	// Override auto
	$protocol = get_opendb_config_var ( 'site.url', 'protocol' );
	if (strlen ( $protocol ) > 0)
		return $protocol;
	else {
		if (get_http_env ( "HTTPS" ) == "on")
			return "https";
		else
			return "http";
	}
}

function get_site_host() {
	// Override auto
	$host = get_opendb_config_var ( 'site.url', 'host' );
	if (strlen ( $host ) > 0)
		return $host;
	else
		return get_http_env ( "SERVER_NAME" );
}

function get_site_port() {
	// Override auto
	$port = get_opendb_config_var ( 'site.url', 'port' );
	if (strlen ( $port ) > 0)
		return $port;
	else
		return get_http_env ( "SERVER_PORT" );
}

function get_site_path() {
	$path = get_opendb_config_var ( 'site.url', 'path' );
	if (strlen ( $path )) {
		return $path;
	} else {
		// It seems that Win32 uses PATH_INFO instead of SCRIPT_NAME
		$path = ifempty ( get_http_env ( "PATH_INFO" ), ifempty ( get_http_env ( "PHP_SELF" ), get_http_env ( "SCRIPT_NAME" ) ) );
		
		// Now process path to get rid of anything after last /
		$index = strrpos ( $path, "/" );
		if ($index !== FALSE)
			$path = substr ( $path, 0, $index + 1 ); //include last slash!
			

		// if path does not end in /, at this character.
		if (substr ( $path, - 1, 1 ) != '/') {
			$path .= '/';
		}
		
		return $path;
	}
}

/**
	Convert all HTTP variables into a GET string.
	Does not include empty fields.
*/
function get_url_string($http_vars, $extra_vars_r = NULL, $exclude_keys_r = NULL) {
	$url = '';
	
	// Merge - $extra_vars_r may contain new values for existing variables.
	if (is_array ( $http_vars ) && is_array ( $extra_vars_r ))
		$http_vars = array_merge ( $http_vars, $extra_vars_r );
	else if (is_array ( $extra_vars_r ))
		$http_vars = $extra_vars_r;
	
	@reset ( $http_vars );
	foreach ( $http_vars as $key => $value ) {
		if (! is_array ( $exclude_keys_r ) || ! in_array ( $key, $exclude_keys_r )) {
			$url = _get_url_string ( $url, $key, $value );
		}
	}
	
	return $url;
}

function _get_url_string($url, $key, $value) {
	if (is_array ( $value )) {
		foreach ( $value as $akey => $avalue ) {
			if (is_numeric ( $akey ))
				$url = _get_url_string ( $url, $key . '[]', $avalue );
			else
				$url = _get_url_string ( $url, $key . '[' . $akey . ']', $avalue );
		}
	} else if (strlen ( $value ) > 0) {
		if (strlen ( $url ) > 0)
			$url .= '&';
		
		$url .= $key . '=' . rawurlencode ( $value );
	}
	
	return $url;
}

/**
	Pass all http variables onto next instance...

	Note: Includes empty fields which may cause problems in some situations.
*/
function get_url_fields($http_vars, $extra_vars_r = NULL, $exclude_keys_r = NULL) {
	$fields = '';
	
	// Merge - $extra_vars_r may contain new values for existing variables.
	if (is_array ( $http_vars ) && is_array ( $extra_vars_r ))
		$http_vars = array_merge ( $http_vars, $extra_vars_r );
	else if (is_array ( $extra_vars_r ))
		$http_vars = $extra_vars_r;
	
	@reset ( $http_vars );
	foreach ( $http_vars as $key => $value ) {
		if (! is_array ( $exclude_keys_r ) || ! in_array ( $key, $exclude_keys_r )) {
			$fields .= _get_url_field ( $key, $value );
		}
	}
	return $fields;
}

function _get_url_field($key, $value) {
	$fields = '';
	if (is_array ( $value )) {
		foreach ( $value as $akey => $avalue ) {
			if (is_numeric ( $akey ))
				$fields .= _get_url_field ( $key . '[]', $avalue );
			else
				$fields .= _get_url_field ( $key . '[' . $akey . ']', $avalue );
		}
	} else {
		$fields .= "\n<input type=\"hidden\" name=\"$key\" value=\"" . htmlspecialchars ( $value ) . "\" />";
	}
	
	return $fields;
}

/**
* Will parse string
*/
function is_uri_domain_in_list($url, $domain_list_r) {
	if (strlen ( $url ) && is_not_empty_array ( $domain_list_r )) {
		$url_parts_r = parse_url ( $url );
		
		$domain = $url_parts_r ['host'];
		
		while ( ($index = strpos ( $domain, '.' )) !== FALSE ) {
			if (in_array ( $domain, $domain_list_r )) {
				return TRUE;
			} else {
				$domain = substr ( $domain, $index + 1 );
			}
		}
	}
	
	////else
	return FALSE;
}
?>
