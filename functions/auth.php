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

define('PERM_VIEW_ANNOUNCEMENTS', 'PERM_VIEW_ANNOUNCEMENTS');
define('PERM_VIEW_WHATSNEW', 'PERM_VIEW_WHATSNEW');
define('PERM_VIEW_LISTINGS', 'PERM_VIEW_LISTINGS');
define('PERM_VIEW_STATS', 'PERM_VIEW_STATS');
define('PERM_VIEW_ADVANCED_SEARCH', 'PERM_VIEW_ADVANCED_SEARCH');
define('PERM_VIEW_USER_PROFILE', 'PERM_VIEW_USER_PROFILE');
define('PERM_VIEW_ITEM_DISPLAY', 'PERM_VIEW_ITEM_DISPLAY');
define('PERM_VIEW_ITEM_COVERS', 'PERM_VIEW_ITEM_COVERS');

define('PERM_ADMIN_TOOLS', 'PERM_ADMIN_TOOLS');
define('PERM_USER_BORROWER', 'PERM_USER_BORROWER');
define('PERM_ADMIN_BORROWER', 'PERM_ADMIN_BORROWER');
define('PERM_ADMIN_REVIEWER', 'PERM_ADMIN_REVIEWER');
define('PERM_USER_REVIEWER', 'PERM_USER_REVIEWER');
define('PERM_ADMIN_EXPORT', 'PERM_ADMIN_EXPORT');
define('PERM_USER_EXPORT', 'PERM_USER_EXPORT');
define('PERM_ADMIN_IMPORT', 'PERM_ADMIN_IMPORT');
define('PERM_USER_IMPORT', 'PERM_USER_IMPORT');
define('PERM_ITEM_OWNER', 'PERM_ITEM_OWNER');
define('PERM_ITEM_ADMIN', 'PERM_ITEM_ADMIN');
define('PERM_ADMIN_ANNOUNCEMENTS', 'PERM_ADMIN_ANNOUNCEMENTS');
define('PERM_ADMIN_USER_PROFILE', 'PERM_ADMIN_USER_PROFILE');
define('PERM_ADMIN_USER_LISTING', 'PERM_ADMIN_USER_LISTING');
define('PERM_EDIT_USER_PROFILE', 'PERM_EDIT_USER_PROFILE');
define('PERM_CHANGE_PASSWORD', 'PERM_CHANGE_PASSWORD');
define('PERM_ADMIN_QUICK_CHECKOUT', 'PERM_ADMIN_QUICK_CHECKOUT');
define('PERM_ADMIN_CREATE_USER', 'PERM_ADMIN_CREATE_USER');
define('PERM_ADMIN_CHANGE_PASSWORD', 'PERM_ADMIN_CHANGE_PASSWORD');
define('PERM_ADMIN_LOGIN', 'PERM_ADMIN_LOGIN');
define('PERM_ADMIN_CHANGE_USER', 'PERM_ADMIN_CHANGE_USER');
define('PERM_CHANGE_USER', 'PERM_CHANGE_USER');

define('PERM_ADMIN_SEND_EMAIL', 'PERM_ADMIN_SEND_EMAIL');
define('PERM_SEND_EMAIL', 'PERM_SEND_EMAIL');
define('PERM_RECEIVE_EMAIL', 'PERM_RECEIVE_EMAIL');

// todo - cache user_role and permissions list after first call
function is_user_granted_permission($permission, $user_id = NULL)
{
	$user_permissions_clause = format_sql_in_clause($permission);
	
	if(is_site_public_access())
	{
		$query = "SELECT 'X' 
			FROM 	s_role_permission
			WHERE 	role_name = 'PUBLICACCESS' AND
				  	permission_name IN ($user_permissions_clause)";
	}
	else 
	{
		if(strlen($user_id)==0)
			$user_id = get_opendb_session_var('user_id');

		$query = "SELECT 'X' 
			FROM 	s_role_permission srp, 
				 	user u 
			WHERE 	u.user_role = srp.role_name AND
				  	srp.permission_name IN ($user_permissions_clause) AND
				  	u.user_id = '$user_id'";
	}
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function is_site_public_access()
{
	if(is_opendb_configured() && !is_opendb_valid_session() && get_opendb_config_var('site.public_access', 'enable') === TRUE)
	{
		return TRUE;
	}
	else
	{
	    return FALSE;
	}
}

function is_site_enabled()
{
    if(is_opendb_configured())
	{
		// if an administrator is logged in, then the site is considered enabled, even if
		// configured to be disabled.
		if(get_opendb_config_var('site', 'enable')!==FALSE)
    	    return TRUE;
		else if(is_user_granted_permission(PERM_ADMIN_LOGIN))
			return TRUE;
		else if(is_user_admin_changed_user())
		{
			return TRUE;
		}
	}

	//else
    return FALSE;
}

function is_user_admin_changed_user()
{
	if(get_opendb_config_var('login', 'enable_change_user')!==FALSE && 
					strlen(get_opendb_session_var('admin_user_id'))>0 && 
						is_user_granted_permission(PERM_ADMIN_LOGIN, get_opendb_session_var('admin_user_id')))
	{
		return TRUE;		
	}
	else
	{
		return FALSE;
	}
}

/**
*/
function is_opendb_valid_session()
{
    if(is_opendb_configured())
    {
		if(get_opendb_session_var('login_time')!=NULL &&
				get_opendb_session_var('last_access_time')!=NULL &&
				get_opendb_session_var('user_id')!=NULL &&
				get_opendb_session_var('hash_check')!=NULL)
		{
			$site_r = get_opendb_config_var('site');
			
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