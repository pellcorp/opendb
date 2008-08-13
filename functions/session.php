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
include_once('./functions/http.php');

function register_opendb_session_var($name, $value) {
	// supposedly not required
	if(!isset($_SESSION)) {
		global $_SESSION;
	}

    $_SESSION[$name] = $value;

    if(is_register_globals_enabled()) {
		// if globals enabled
		global $$name;

		session_register($name);
		$$name = $_SESSION[$name];
	}
}

function unregister_opendb_session_var($name) {
	// supposedly not required
	if(!isset($_SESSION)) {
		global $_SESSION;
	}

    if(is_register_globals_enabled()) {
		// PHP manual suggests unregistering session variables
		session_unregister($name);
	}

    $_SESSION[$name] = NULL;
}

function is_opendb_session_var($name) {
	return get_opendb_session_var($name)!==NULL;
}

function get_opendb_session_var($name) {
	// supposedly not required
	if(!isset($_SESSION)) {
		global $_SESSION;
	}

	if(is_array($_SESSION) && isset($_SESSION[$name])) {
		return $_SESSION[$name];
	} else {
		return NULL;
	}
}
?>