<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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
$_OPENDB_ERROR_REPORTING = error_reporting(E_ALL & ~E_NOTICE);

// In case session handler error
//ini_set('session.save_handler', 'files'); 

// PLEASE DO NOT CHANGE THIS AS ITS AN INTERNAL VARIABLE FOR USE IN INSTALLER and other functions.
define('__OPENDB_RELEASE__', '1.0pl1');
define('__OPENDB_TITLE__', 'OpenDb');

// definitions for use in logging that may not be defined in older version of PHP, but
// which we want to be able to assume exist.
if(!defined('__FUNCTION__'))
	define('__FUNCTION__', 'unknown');

if(!defined('__CLASS__'))
	define('__CLASS__', 'unknown');

if(!defined('__METHOD__'))
	define('__METHOD__', 'unknown');

// --------------------------------------------------------------------------
// This script contains any code that needs to be executed at the start of
// each and every runnable script.
// --------------------------------------------------------------------------

if(extension_loaded('mysqli'))
{
	include_once('./functions/database/mysqli.inc.php');
}
else if(extension_loaded('mysql'))
{
	include_once('./functions/database/mysql.inc.php');
}

if(file_exists("./include/local.config.php"))
{
	include_once("./include/local.config.php");
}		

include_once("./functions/config.php");

include_once("./functions/http.php");
include_once("./functions/utils.php");
include_once("./functions/auth.php");
include_once("./functions/session.php");
include_once("./functions/database.php");
include_once("./functions/theme.php");
include_once("./functions/language.php");
include_once("./functions/menu.php");

// OpenDb will not work with this on!!!
if(get_magic_quotes_runtime())
{
	set_magic_quotes_runtime(false);
	$_OPENDB_MAGIC_QUOTES_RUNTIME=TRUE;
}

// Only if $PHP_SELF is not already defined.
if(!isset($PHP_SELF))
{
	// get_http_env is a OpenDb function!
	$PHP_SELF = get_http_env('PHP_SELF');
}

// We want all the HTTP variables into the $HTTP_VARS array, so
// we can reference everything from the one place.
// any upload files will be in new post php 4.1 $_FILES array
if(!empty($_GET))
{
	$HTTP_VARS = $_GET;
}
else if(!empty($_POST))
{
	$HTTP_VARS = $_POST;
}

// Strip all slashes from this array.
if(get_magic_quotes_gpc())
{
	// Only tested with normal $HTTP_VARS arrays which should _not_ go deeper than 2 levels in OpenDb.
	function stripslashes_array($array)
	{
		$rs = array();
		while (list($key,$val) = @each($array))
		{
			if(is_array($array[$key]))
			{
				$rs[$key] = stripslashes_array($array[$key]);
			}
			else
			{
				$rs[$key] = stripslashes($val);
			}
		}
		return $rs;
	}
	$HTTP_VARS = stripslashes_array($HTTP_VARS);
}

// if the mysql[i] extension has been loaded, the db_connect function should exist
if(function_exists('db_connect'))
{
	if(is_opendb_configured())
	{	
		if(is_db_connected())
	    {
		    //
			// Cache often used configuration entries
			//
		    $CONFIG_VARS['logging'] = get_opendb_config_var('logging');
	
			// Buffer output for possible pushing through ob_gzhandler handler
			if(is_gzip_compression_enabled($PHP_SELF))
			{
				ob_start('ob_gzhandler');
			}
	
			// Restrict cookie to site host and path.
			if(get_opendb_config_var('site', 'restrict_session_cookie_to_host_path')===TRUE)
			{
				session_set_cookie_params(
							0,
							get_site_path(),
							get_site_host());
			}
	
			if(get_opendb_config_var('session_handler', 'enable') === TRUE)
			{
				// Include the session handling functions here.
				require_once("./functions/dbsession.php");
	
	            // Attempt to change the ini value if required, but complain if not possible.
				if(strtolower(ini_get('session.save_handler')) == 'user' || ini_set('session.save_handler', 'user'))
				{
					session_set_save_handler('db_session_open',
											'db_session_close',
											'db_session_read',
											'db_session_write',
											'db_session_destroy',
											'db_session_gc');
				}
				else
				{
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, NULL, 'Cannot set session.save_handler to \'user\'');
				}
			}

			// do not attempt to start a session if site is disabled.
	
			// We want to start the session here, so we can get access to the $_SESSION properly.
			session_start();

			// this will initiate public access session if applicable.
			init_public_access_session();
			
			//allows specific pages to overide themes
			if(!is_legal_theme($_OVRD_OPENDB_THEME))
			{
				if(strlen(get_opendb_session_var('user_id'))>0)
				{
					$user_theme = fetch_user_theme(get_opendb_session_var('user_id'));
				
					if(is_legal_theme($user_theme))
					{
						$_OPENDB_THEME = $user_theme;
					}
				}
			
				if(strlen($_OPENDB_THEME)==0)
				{
					if(is_legal_theme(get_opendb_config_var('site', 'theme')))
					{
						$_OPENDB_THEME = get_opendb_config_var('site', 'theme');
					}
					else // This is the final default.
					{
						$_OPENDB_THEME = 'default';
					}
				}
			}
			else
			{
				$_OPENDB_THEME = $_OVRD_OPENDB_THEME;
			}
			
			if(!is_exists_language($_OVRD_OPENDB_LANGUAGE))
			{
				if(strlen(get_opendb_session_var('user_id'))>0 && get_opendb_config_var('user_admin', 'user_language_support')!==FALSE)
				{
					$_OPENDB_LANGUAGE = fetch_user_language(get_opendb_session_var('user_id'));
				}
				
				if(strlen($_OPENDB_LANGUAGE)==0)
				{
					if(is_exists_language(get_opendb_config_var('site', 'language')))
						$_OPENDB_LANGUAGE = strtoupper(get_opendb_config_var('site', 'language'));
					else // This is the final default.
						$_OPENDB_LANGUAGE = fetch_default_language();
				}
			}
			else
			{
				$_OPENDB_LANGUAGE = $_OVRD_OPENDB_LANGUAGE;
			}
		}//if(is_db_connected())
		else
		{
			// defaults where no database access		    
	    	$_OPENDB_THEME = 'default';
		    $_OPENDB_LANGUAGE = 'ENGLISH';
		}
	}//if(file_exists("./include/local.config.php"))
	else
	{
		// defaults where no database access		    
	    $_OPENDB_THEME = 'default';
		$_OPENDB_LANGUAGE = 'ENGLISH';
	}
	
	// special handling
	if($HTTP_VARS['mode'] == 'job')
	{
		$_OPENDB_THEME = '';
	}
			
	if(strlen($_OPENDB_THEME)>0)
	{
		// All the particular functions are in this file.
		include_once("./theme/$_OPENDB_THEME/theme.php");
	}
}
else
{
	// todo - pretty this up!
	die('MySQL extension is not available');
}	
?>