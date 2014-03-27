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
include_once ("./lib/user.php");
include_once ("./lib/utils.php");
include_once ("./lib/http.php");
include_once ("./lib/config.php");

define ( 'PERM_VIEW_ANNOUNCEMENTS', 'PERM_VIEW_ANNOUNCEMENTS' );
define ( 'PERM_VIEW_WHATSNEW', 'PERM_VIEW_WHATSNEW' );
define ( 'PERM_VIEW_LISTINGS', 'PERM_VIEW_LISTINGS' );
define ( 'PERM_VIEW_STATS', 'PERM_VIEW_STATS' );
define ( 'PERM_VIEW_ADVANCED_SEARCH', 'PERM_VIEW_ADVANCED_SEARCH' );
define ( 'PERM_VIEW_USER_PROFILE', 'PERM_VIEW_USER_PROFILE' );
define ( 'PERM_VIEW_ITEM_DISPLAY', 'PERM_VIEW_ITEM_DISPLAY' );
define ( 'PERM_VIEW_ITEM_COVERS', 'PERM_VIEW_ITEM_COVERS' );

define ( 'PERM_ADMIN_TOOLS', 'PERM_ADMIN_TOOLS' );
define ( 'PERM_USER_BORROWER', 'PERM_USER_BORROWER' );
define ( 'PERM_ADMIN_BORROWER', 'PERM_ADMIN_BORROWER' );
define ( 'PERM_ADMIN_REVIEWER', 'PERM_ADMIN_REVIEWER' );
define ( 'PERM_USER_REVIEWER', 'PERM_USER_REVIEWER' );
define ( 'PERM_ADMIN_EXPORT', 'PERM_ADMIN_EXPORT' );
define ( 'PERM_USER_EXPORT', 'PERM_USER_EXPORT' );
define ( 'PERM_ADMIN_IMPORT', 'PERM_ADMIN_IMPORT' );
define ( 'PERM_USER_IMPORT', 'PERM_USER_IMPORT' );
define ( 'PERM_ITEM_OWNER', 'PERM_ITEM_OWNER' );
define ( 'PERM_ITEM_ADMIN', 'PERM_ITEM_ADMIN' );
define ( 'PERM_ADMIN_ANNOUNCEMENTS', 'PERM_ADMIN_ANNOUNCEMENTS' );
define ( 'PERM_ADMIN_USER_PROFILE', 'PERM_ADMIN_USER_PROFILE' );
define ( 'PERM_ADMIN_USER_LISTING', 'PERM_ADMIN_USER_LISTING' );
define ( 'PERM_EDIT_USER_PROFILE', 'PERM_EDIT_USER_PROFILE' );
define ( 'PERM_CHANGE_PASSWORD', 'PERM_CHANGE_PASSWORD' );
define ( 'PERM_ADMIN_QUICK_CHECKOUT', 'PERM_ADMIN_QUICK_CHECKOUT' );
define ( 'PERM_ADMIN_CREATE_USER', 'PERM_ADMIN_CREATE_USER' );
define ( 'PERM_ADMIN_CHANGE_PASSWORD', 'PERM_ADMIN_CHANGE_PASSWORD' );
define ( 'PERM_ADMIN_LOGIN', 'PERM_ADMIN_LOGIN' );
define ( 'PERM_ADMIN_CHANGE_USER', 'PERM_ADMIN_CHANGE_USER' );

define ( 'PERM_ADMIN_SEND_EMAIL', 'PERM_ADMIN_SEND_EMAIL' );
define ( 'PERM_SEND_EMAIL', 'PERM_SEND_EMAIL' );
define ( 'PERM_RECEIVE_EMAIL', 'PERM_RECEIVE_EMAIL' );

/**
 * reduce hardcoding of this value - ideally should be a flag in the s_role table.
 *
 * @return unknown
 */
function get_public_access_rolename() {
	return "PUBLICACCESS";
}

// todo - cache user_role and permissions list after first call
function is_user_granted_permission($permission, $user_id = NULL) {
	$user_permissions_clause = format_sql_in_clause ( $permission );
	
	if (strlen ( $user_id ) == 0 && is_site_public_access ()) {
		$query = "SELECT 'X' 
			FROM 	s_role_permission
			WHERE 	role_name = '" . get_public_access_rolename () . "' AND
				  	permission_name IN ($user_permissions_clause)";
	} else {
		if (strlen ( $user_id ) == 0)
			$user_id = get_opendb_session_var ( 'user_id' );
		
		$query = "SELECT 'X' 
			FROM 	s_role_permission srp, 
				 	user u 
			WHERE 	u.user_role = srp.role_name AND
				  	srp.permission_name IN ($user_permissions_clause) AND
				  	u.user_id = '$user_id'";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

function is_site_public_access() {
	if (is_opendb_configured () && !is_opendb_valid_session () && get_opendb_config_var ( 'site.public_access', 'enable' ) === TRUE) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function is_site_enabled() {
	if (is_opendb_configured ()) {
		// if an administrator is logged in, then the site is considered enabled, even if
		// configured to be disabled.
		if (get_opendb_config_var ( 'site', 'enable' ) !== FALSE)
			return TRUE;
		else if (is_user_granted_permission ( PERM_ADMIN_LOGIN ))
			return TRUE;
		else if (is_user_admin_changed_user ()) {
			return TRUE;
		}
	}
	
	//else
	return FALSE;
}

function is_user_admin_changed_user() {
	if (get_opendb_config_var ( 'login', 'enable_change_user' ) !== FALSE && strlen ( get_opendb_session_var ( 'admin_user_id' ) ) > 0 && is_user_granted_permission ( PERM_ADMIN_LOGIN, get_opendb_session_var ( 'admin_user_id' ) )) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function is_opendb_valid_session() {
	if (is_opendb_configured ()) {
		if (get_opendb_session_var ( 'login_time' ) != NULL && get_opendb_session_var ( 'last_access_time' ) != NULL && get_opendb_session_var ( 'user_id' ) != NULL && get_opendb_session_var ( 'hash_check' ) != NULL) {
			$site_r = get_opendb_config_var ( 'site' );
			
			// A valid session as far as the variables go at least.
			if ($site_r ['security_hash'] == get_opendb_session_var ( 'hash_check' )) {
				// idle_timeout is how long between requests a login session
				// can remain valid.  If login_timeout is set, then this controls
				// how long a session can remain active overall.
				$current_time = time ();
				
				if (! is_numeric ( $site_r ['login_timeout'] ) || (($current_time - get_opendb_session_var ( 'login_time' )) < $site_r ['login_timeout'])) {
					if (! is_numeric ( $site_r ['idle_timeout'] ) || (($current_time - get_opendb_session_var ( 'last_access_time' )) < $site_r ['idle_timeout'])) {
						if (is_user_active ( get_opendb_session_var ( 'user_id' ) )) {
							// reset the time, as we are only interested in idle session tests.
							$_SESSION ['last_access_time'] = $current_time;
							return TRUE;
						} else {
							opendb_logger ( OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'Invalid user encountered' );
							return FALSE;
						}
					}
				}
			} else {//if($site_r['security_hash'] == get_opendb_session_var('hash_check'))
				opendb_logger ( OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'Invalid security-hash login invalidated' );
				return FALSE;
			}
		}
	} //if(is_opendb_configured())
	
	//else
	return FALSE;
}

function register_user_login($user_id, $rememberMe = FALSE) {
	$time = time();
	register_opendb_session_var('login_time', $time);
	register_opendb_session_var('last_access_time', $time);

	$user_r = fetch_user_r($user_id);

	register_opendb_session_var('user_id', $user_id);
	
	if ($rememberMe) {
		register_opendb_session_var('remember_me', 'true');
	}
	
	// Now register security hash, so we can compare.
	register_opendb_session_var('hash_check', get_opendb_config_var('site', 'security_hash'));

	// Get the previous last visit so we can use in whats new page.
	register_opendb_session_var('login_lastvisit', fetch_user_lastvisit($user_id));

	// Not much we can do if it does not update.
	update_user_lastvisit($user_id);
}

function get_opendb_remember_me_cookie_name() {
	return __OPENDB_TITLE__ . "_RememberMe";
}

function get_opendb_session_cookie_name() {
	return __OPENDB_TITLE__ . "_Session";
}

function remove_opendb_remember_me() {
	$oldCookie = $_COOKIE[get_opendb_remember_me_cookie_name()];
	setcookie(get_opendb_remember_me_cookie_name(), "", time() - 3600);
	
	$remember_me_r = get_remember_me_r($oldCookie);
	if ($remember_me_r !== FALSE) {
		delete_remember_me($remember_me_r['id']);
	}
}

function handle_opendb_remember_me() {
	if (isset($_SESSION['remember_me']) && isset($_SESSION['user_id'])) {
		$doRememberMe = TRUE;
	} else {
		$doRememberMe = FALSE;
	}

	$oldCookie = $_COOKIE[get_opendb_remember_me_cookie_name()];
	if (!empty($oldCookie)) {
		$remember_me_r = get_remember_me_r($oldCookie);
		if ($remember_me_r !== FALSE) {
			// no need to register if already logged in
			if ($remember_me_r['valid'] === TRUE && !$doRememberMe) { 
				register_user_login($remember_me_r['user_id'], TRUE);
				$doRememberMe = TRUE;
			}
			delete_remember_me($remember_me_r['id']);
		}
	}
	
	if ($doRememberMe) {
		$cookie = sha1(openssl_random_pseudo_bytes(1024));
		$site_r = get_opendb_config_var('site');
		$login_timeout = (int) ifempty(ifempty($site_r['login_timeout'], $site_r['idle_timeout']), 3600);
	
		if (insert_remember_me($_SESSION['user_id'], $cookie)) {
			setcookie(get_opendb_remember_me_cookie_name(), $cookie, time() + $login_timeout);
		}
	}
}

function get_remember_me_r($cookie) {
	$cookie = addslashes($cookie);
	
	$site_r = get_opendb_config_var ('site');
	$login_timeout = (int) ifempty(ifempty($site_r['login_timeout'], $site_r['idle_timeout']), 3600);
	
	$query = "SELECT id, user_id, UNIX_TIMESTAMP() AS 'current_time', UNIX_TIMESTAMP(DATE_ADD(created_on, INTERVAL $login_timeout SECOND)) AS expiry_time FROM remember_me WHERE cookie = '$cookie' ";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );

		$found['valid'] = $found['current_time'] < $found['expiry_time'];
		return $found;
	}
	
	//else
	return FALSE;
}

function delete_remember_me($id) {
	$id = addslashes($id);
	$query = "DELETE FROM remember_me WHERE id = '$id'";
	
	$delete = db_query ( $query );
	if (db_affected_rows () > 0) {
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($cookie));
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array($cookie));
		return FALSE;
	}
}

function insert_remember_me($user_id, $cookie) {
	$cookie = addslashes($cookie);	
	$query = "INSERT INTO remember_me(user_id, cookie)" . "VALUES ('$user_id', '$cookie')";
	
	$insert = db_query ( $query );
	if ($insert && db_affected_rows () > 0) {
		$sequence_number = db_insert_id();
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($user_id, $cookie));
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array($user_id, $cookie));
		return FALSE;
	}
}
?>