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
include_once ('./lib/http.php');

// function startSession($time = 0, $ses = 'OpenDbSession') {
// 	// Reset the expiration time upon page load
// 	if (isset($_COOKIE[$ses])) {
// 		setcookie($ses, $_COOKIE[$ses], time() + $time, "/");
// 	}
// }

function register_opendb_session_var($name, $value) {
	$_SESSION [$name] = $value;
}

function register_opendb_session_array_var($name, $key, $value) {
	if (! is_array ( $_SESSION [$name] )) {
		$_SESSION [$name] = array ();
	}
	$_SESSION [$name] [$key] = $value;
}

function get_opendb_session_array_var($name, $key) {
	if (isset ( $_SESSION [$name] [$key] )) {
		return $_SESSION [$name] [$key];
	} else {
		return FALSE;
	}
}

function unregister_opendb_session_var($name) {
	$_SESSION [$name] = NULL;
}

function is_opendb_session_var($name) {
	return get_opendb_session_var ( $name ) !== NULL;
}

function get_opendb_session_var($name) {
	if (isset ( $_SESSION [$name] )) {
		return $_SESSION [$name];
	} else {
		return NULL;
	}
}
?>