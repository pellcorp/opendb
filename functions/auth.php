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

include_once("./functions/user.php");
include_once("./functions/utils.php");
include_once("./functions/http.php");
include_once("./functions/config.php");

/**
 * Initiate public access session if applicable
 */
function init_public_access_session()
{
	if(!is_opendb_valid_session())
	{
	    $site_public_access_r = get_opendb_config_var('site.public_access');
		if($site_public_access_r['enable']!==FALSE)
		{
			if(strlen(get_opendb_session_var('user_id'))==0)
	            register_opendb_session_var('user_type', 'G');
			else
	            unregister_opendb_session_var('user_type');
		}
	}
}

/**
Test that public access enabled, and currently 'logged' in user is the
configured public access user.

Its important to remember that the caller is expecting this method to return TRUE, if
public access is enabled in configuration, and page currently being access is available
via public access configuration.  This method will only ever be used where there is not
currently a login session.
*/
function is_site_public_access()
{
	global $PHP_SELF;

	if(is_opendb_configured())
	{
		if(strlen(get_opendb_session_var('user_id'))==0)
		{
			$site_plugin_access_r = get_opendb_config_var('site.public_access');
			if($site_plugin_access_r['enable'] === TRUE)
			{
				$page = basename($PHP_SELF, '.php');
				return is_site_public_access_page($page);
			}
		}
	}
	//else
    return FALSE;
}

function is_site_public_access_page($page)
{
	$default_allowed_pages = array('index', 'login', 'url');
	if(in_array($page, $default_allowed_pages))
	{
		return TRUE;
	}
	
	$site_plugin_access_r = get_opendb_config_var('site.public_access');
	
	$public_access_allowed_pages_r = array('rss', 'listings', 'item_display', 'item_review', 'stats');
	if(in_array($page, $public_access_allowed_pages_r))
	{
		return ($site_plugin_access_r[$page]!==FALSE);
	}
	
	//else
	return FALSE;
}
/**
	If currently logged in user is an Administrator, then even if site is explicitly
	disabled, the admin will still be able to use the site.  All users will be able
	to login, even when site is disabled, but as soon as they are successfully logged
	in, and they are not admin, all other functions will be disabled.
*/
function is_site_enabled()
{
    if(is_opendb_configured())
	{
		// if an administrator is logged in, then the site is considered enabled, even if
		// configured to be disabled.
		if(get_opendb_config_var('site', 'enable')!==FALSE)
    	    return TRUE;
		else if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
			return TRUE;
		else if(get_opendb_config_var('login', 'enable_change_user')!==FALSE && // change user active
					strlen(get_opendb_session_var('admin_user_id'))>0 && is_user_admin(get_opendb_session_var('admin_user_id')))
		{
			return TRUE;
		}
	}

	//else
    return FALSE;
}

/**
*/
function is_opendb_valid_session()
{
    if(is_opendb_configured())
    {
		$site_r = get_opendb_config_var('site');

		if(is_site_public_access())
		{
			return TRUE;
		}
		else if(get_opendb_session_var('login_time')!=NULL &&
				get_opendb_session_var('last_access_time')!=NULL &&
				get_opendb_session_var('user_id')!=NULL &&
				get_opendb_session_var('hash_check')!=NULL)
		{
			// A valid session as far as the variables go at least.
			if($site_r['security_hash'] == get_opendb_session_var('hash_check'))
			{
				// idle_timeout is how long between requests a login session
				// can remain valid.  If login_timeout is set, then this controls
				// how long a session can remain active overall.
				$current_time = time();

				if (!is_numeric($site_r['login_timeout']) || 
						( ($current_time - get_opendb_session_var('login_time')) < $site_r['login_timeout']) )
				{
					if ( !is_numeric($site_r['idle_timeout']) ||
							( ($current_time - get_opendb_session_var('last_access_time')) < $site_r['idle_timeout']) )
					{
						if(is_user_active(get_opendb_session_var('user_id')))
						{
                            // reset the time, as we are only interested in idle session tests.
                            register_opendb_session_var('last_access_time', $current_time);
							return TRUE;
						}
						else
						{
							opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'Invalid user encountered');
							return FALSE;
						}
					}
				}
			}//if($site_r['security_hash'] == get_opendb_session_var('hash_check'))
			else
			{
				opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'Invalid security-hash login invalidated');
				return FALSE;
			}
		}
	}//if(is_opendb_configured())
	
	//else
	return FALSE;
}
?>