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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

include_once("./lib/widgets.php");
include_once("./lib/http.php");
include_once("./lib/importcache.php");

if (is_user_admin_changed_user()) {
	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Administrator logging out change user');

	$user_r = fetch_user_r(get_opendb_session_var('admin_user_id'));
	register_opendb_session_var('user_id', get_opendb_session_var('admin_user_id'));

	unregister_opendb_session_var('admin_user_id');

	opendb_redirect('index.php');
} else {
	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User logged out');

	if (strlen(get_opendb_session_var('user_id')) > 0) {
		import_cache_delete_for_user(get_opendb_session_var('user_id'));
	}

	unregister_opendb_session_var('user_id');
	unregister_opendb_session_var('hash_check');
	unregister_opendb_session_var('login_time');
	unregister_opendb_session_var('last_access_time');
	unregister_opendb_session_var('login_lastvisit');

	// close session
	@session_destroy();

	opendb_redirect('index.php');
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>