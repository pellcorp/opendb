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

// Set error reporting to something OpenDb is capable of handling
if (!defined('E_DEPRECATED')) {
	define('E_DEPRECATED', 0);
}
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// PLEASE DO NOT CHANGE THIS AS ITS AN INTERNAL VARIABLE FOR USE IN INSTALLER and other functions.
define('__OPENDB_RELEASE__', '1.6.3-DEV');
define('__OPENDB_TITLE__', 'OpenDb');

if (extension_loaded('mysqli')) {
	include_once('./lib/database/mysqli.inc.php');
} else if (extension_loaded('mysql')) {
	include_once('./lib/database/mysql.inc.php');
}

// force db session handling by default
$CONFIG_VARS['session_handler']['enable'] = TRUE;

if (file_exists("./include/local.config.php")) {
	include_once("./include/local.config.php");
}

include_once("./lib/config.php");

include_once("./lib/http.php");
include_once("./lib/utils.php");
include_once("./lib/auth.php");
include_once("./lib/session.php");
include_once("./lib/database.php");
include_once("./lib/theme.php");
include_once("./lib/language.php");
include_once("./lib/menu.php");

include_once("./lib/OpenDbBrowserSniffer.class.php");

// Only if $PHP_SELF is not already defined.
if (!isset($PHP_SELF)) {
	// get_http_env is a OpenDb function!
	$PHP_SELF = get_http_env('PHP_SELF');
}

// We want all the HTTP variables into the $HTTP_VARS array, so
// we can reference everything from the one place.
// any upload files will be in new post php 4.1 $_FILES array
if (!empty($_GET)) {
	// fixes for XSS vulnerabilities reported in OpenDb 1.0.6
	// http://secunia.com/advisories/31719
	$HTTP_VARS = strip_tags_array($_GET);
} else if (!empty($_POST)) {
	$HTTP_VARS = $_POST;
} else {
    $HTTP_VARS = array();
}

if (!isset($HTTP_VARS['op']))
    $HTTP_VARS['op'] = "";
if (!isset($HTTP_VARS['mode']))
    $HTTP_VARS['mode'] = "";

//define a global browser sniffer object for use by theme and elsewhere
$_OpendbBrowserSniffer = new OpenDbBrowserSniffer();

// if the mysql[i] extension has been loaded, the db_connect function should exist
if (function_exists('db_connect')) {
	// defaults where no database access		    
	$_OPENDB_THEME = 'default';
	$_OPENDB_LANGUAGE = 'ENGLISH';

	if (is_opendb_configured()) {
		if (is_db_connected()) {
			// Cache often used configuration entries
			$CONFIG_VARS['logging'] = get_opendb_config_var('logging');

			// Buffer output for possible pushing through ob_gzhandler handler
			if (is_gzip_compression_enabled($PHP_SELF)) {
				ob_start('ob_gzhandler');
			}

			// Restrict cookie to site host and path.
			if (get_opendb_config_var('site', 'restrict_session_cookie_to_host_path') === TRUE) {
				session_set_cookie_params(0, get_site_path(), get_site_host());
			}

			if (get_opendb_config_var('session_handler', 'enable') === TRUE) {
				require_once("./lib/dbsession.php");

				if (strtolower(ini_get('session.save_handler')) == 'user' || ini_set('session.save_handler', 'user')) {
					session_set_save_handler('db_session_open', 'db_session_close', 'db_session_read', 'db_session_write', 'db_session_destroy', 'db_session_gc');
				} else {
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, NULL, 'Cannot set session.save_handler to \'user\'');
				}
			}

			// We want to start the session here, so we can get access to the $_SESSION properly.
			session_name(get_opendb_session_cookie_name());
			session_start();
			
			handle_opendb_remember_me();
			
			//allows specific pages to overide themes
			global $_OVRD_OPENDB_THEME;
			if (is_exists_theme($_OVRD_OPENDB_THEME)) {
				$_OPENDB_THEME = $_OVRD_OPENDB_THEME;

			} else {
				if ( strlen(get_opendb_session_var('user_id')) > 0 &&
					 get_opendb_config_var('user_admin', 'user_themes_support') !== FALSE ) {
					$user_theme = fetch_user_theme(get_opendb_session_var('user_id'));
					if (is_exists_theme($user_theme))
						$_OPENDB_THEME = $user_theme;
				}

				if (strlen($_OPENDB_THEME) == 0) {
					if (is_exists_theme(get_opendb_config_var('site', 'theme')))
						$_OPENDB_THEME = get_opendb_config_var('site', 'theme');
					else
						$_OPENDB_THEME = 'default';
				}
			}

			global $_OVRD_OPENDB_LANGUAGE;
			if (is_exists_language($_OVRD_OPENDB_LANGUAGE)) {
				$_OPENDB_LANGUAGE = $_OVRD_OPENDB_LANGUAGE;
			} else {
				#unset($_OPENDB_LANGUAGE);

				if (strlen(get_opendb_session_var('user_id')) > 0 && get_opendb_config_var('user_admin', 'user_language_support') !== FALSE) {
					$user_language = fetch_user_language(get_opendb_session_var('user_id'));

					if (is_exists_language($user_language))
						$_OPENDB_LANGUAGE = $user_language;
				}

				if (strlen($_OPENDB_LANGUAGE) == 0) {
					if (is_exists_language(get_opendb_config_var('site', 'language')))
						$_OPENDB_LANGUAGE = strtoupper(get_opendb_config_var('site', 'language'));
					else
						$_OPENDB_LANGUAGE = fetch_default_language();
				}
			}
		}
	}

	if (isset($HTTP_VARS["mode"]) && $HTTP_VARS['mode'] == 'job') {
		$_OPENDB_THEME = '';
	}

	if (strlen($_OPENDB_THEME) > 0) {
		include_once("./theme/$_OPENDB_THEME/theme.php");
	}
} else {
	die('MySQL extension is not available');
}
?>
