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
include_once("./lib/user.php");
include_once("./lib/utils.php");
include_once("./lib/http.php");
include_once("./lib/config.php");

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
define ( 'PERM_USER_INTEREST', 'PERM_USER_INTEREST' );
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
 */
function get_public_access_rolename() {
	return "PUBLICACCESS";
}

function get_public_access_permission_r() {
	$query = "SELECT srp.permission_name
	FROM 	s_role_permission srp
	WHERE 	srp.role_name = '".get_public_access_rolename()."'";

	$children = array();
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		while ($perm_r = db_fetch_assoc($result)) {
			// for public access, remember me is not applicable
			$children[$perm_r['permission_name']] = 'Y';
		}
		db_free_result($result);
	}
	
	return $children;
}

function get_user_granted_permissions_r($user_id) {
	$query = "SELECT srp.permission_name, srp.remember_me_ind
		FROM 	s_role_permission srp,
		user u
		WHERE 	u.user_role = srp.role_name AND
		u.user_id = '$user_id'";
	
	$children = array();
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		while ($perm_r = db_fetch_assoc($result)) {
			$children[$perm_r['permission_name']] =$perm_r['remember_me_ind'];
		}
		db_free_result($result);
	}
	return $children;
}

function is_user_granted_permission($permission, $user_id = NULL, $ignoreRememberMe = FALSE) {
	$is_remember_me = FALSE;
	if (strlen ( $user_id ) == 0 && is_site_public_access()) {
		$perms_r = get_public_access_permission_r();

	} elseif (strlen ( $user_id ) == 0) {
		$user_id = $_SESSION['user_id'];

		if (!$ignoreRememberMe) {
			$is_remember_me = ($_SESSION['login_method'] == 'remember_me');
		}

		global $PERM_MATRIX;
		if (!is_array($PERM_MATRIX)) {
			$perms_r = get_user_granted_permissions_r($user_id);
			$PERM_MATRIX = $perms_r;
		} else {
			$perms_r = $PERM_MATRIX;
		}

	} else { // won't cache explicit request for perms
		$perms_r = get_user_granted_permissions_r($user_id);
	}
	
	if (is_array($permission)) {
		reset($permission);
		foreach ( $permission as $perm ) {
			if (isset($perms_r[$perm])) {
				$rememberMe = $perms_r[$perm];
				if (!$is_remember_me || $rememberMe == 'Y') {
					return TRUE;
				}
			}
		}
	} elseif (isset($perms_r[$permission])) {
		$rememberMe = $perms_r[$permission];
		if (!$is_remember_me || $rememberMe == 'Y') {
			return TRUE;
		}
	}
	return FALSE;
}

/**
 return TRUE if permission exists for the user but has remember_me set to N
 */
function is_permission_disabled_for_remember_me($permission) {
	global $PERM_MATRIX;
	
	if (is_array($PERM_MATRIX)) {
		if (is_array($permission)) {
			reset($permission);
			foreach ( $permission as $perm ) {
				if (isset($PERM_MATRIX[$perm]) && $PERM_MATRIX[$perm] == 'N') {
					return TRUE;
				}
			}
		}
		if (isset($PERM_MATRIX[$permission])) {
			return $PERM_MATRIX[$permission] == 'N';
		}
	}
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
		if (get_opendb_session_var ( 'login_time' ) != NULL && get_opendb_session_var ( 'last_access_time' ) != NULL 
					&& get_opendb_session_var ( 'user_id' ) != NULL && get_opendb_session_var ( 'hash_check' ) != NULL) {
			$site_r = get_opendb_config_var ( 'site' );
			
			// A valid session as far as the variables go at least.
			if ($site_r ['security_hash'] == get_opendb_session_var ( 'hash_check' )) {
				// idle_timeout is how long between requests a login session
				// can remain valid.  If login_timeout is set, then this controls
				// how long a session can remain active overall.
				$current_time = time ();
				
				if (!is_numeric( $site_r['login_timeout'] ?? "" ) || ( ($current_time - get_opendb_session_var( 'login_time' )) < $site_r['login_timeout'])) {
					if (! is_numeric ( $site_r ['idle_timeout'] ?? "" ) || (($current_time - get_opendb_session_var ( 'last_access_time' )) < $site_r ['idle_timeout'])) {
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

function register_user_login($user_id, $doRememberMe = FALSE, $isRememberMeLogin = FALSE) {
	$time = time();
	$_SESSION['login_time'] = $time;
	$_SESSION['last_access_time'] = $time;

	$user_r = fetch_user_r($user_id);

	$_SESSION['user_id'] = $user_id;
	
	if ($doRememberMe) {
		$_SESSION['remember_me'] = 'true';
	}
	
	if ($isRememberMeLogin) {
		$_SESSION['login_method'] = 'remember_me';
	} else {
		$_SESSION['login_method'] = 'normal';
	}
	
	// Now register security hash, so we can compare.
	$_SESSION['hash_check'] = get_opendb_config_var('site', 'security_hash');

	// Get the previous last visit so we can use in whats new page.
	$_SESSION['login_lastvisit'] = fetch_user_lastvisit($user_id);

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
		delete_remember_me($remember_me_r['id'], $oldCookie);
	}
}

function handle_opendb_remember_me() {
	global $PHP_SELF;
	
	
	// do nothing for these pages, if any more, should add to array and do !in_array check
	$page = basename ( $PHP_SELF, '.php' );
	if ($page != 'install' && $page != 'url' && $page != 'logout' && $page != 'login') {
		if (isset($_SESSION['remember_me']) && isset($_SESSION['user_id'])) {
			$doRememberMe = TRUE;
		} else {
			$doRememberMe = FALSE;
		}
		
		$oldCookie = $_COOKIE[get_opendb_remember_me_cookie_name()] ?? "";
		if (!empty($oldCookie)) {
			$remember_me_r = get_remember_me_r($oldCookie);
			if ($remember_me_r !== FALSE) {

				// no need to register if already logged in
				if ($remember_me_r['valid'] === TRUE && !$doRememberMe) { 
					// the second TRUE, flags the current user login as being enabled by a remember me cookie
					register_user_login($remember_me_r['user_id'], TRUE, TRUE);
					$doRememberMe = TRUE;
				}
				delete_remember_me($remember_me_r['id'], $oldCookie);
			}
		}
	
		if ($doRememberMe) {
			$cookie = generate_opendb_cookie();
			$site_r = get_opendb_config_var('site');
			$login_timeout = (int) ($site_r['login_timeout'] ?? $site_r['idle_timeout'] ?? 3600);
		
			if (insert_remember_me($_SESSION['user_id'], $cookie)) {
				setcookie(get_opendb_remember_me_cookie_name(), $cookie, time() + $login_timeout);
			}
		}
	}
}

function generate_opendb_cookie() {
	if (function_exists('openssl_random_pseudo_bytes')) {
		return sha1(openssl_random_pseudo_bytes(1024));
	} else {
		return mt_rand_str(40);
	}
}

//http://www.php.net/manual/en/function.mt-rand.php
function mt_rand_str ($l, $c = 'abcdefghijklmnopqrstuvwxyz1234567890') {
	for ($s = '', $cl = strlen($c)-1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i);
	return $s;
}

function get_remember_me_r($cookie) {
	$cookie = addslashes($cookie);
	
	$site_r = get_opendb_config_var ('site');
	$login_timeout = (int) ($site_r['login_timeout'] ?? ($site_r['idle_timeout'] ?? 3600));
	
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

function delete_remember_me($id, $cookie) {
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
