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

include_once("./lib/JsonRpcServer.class.php");
include_once("./lib/api/ItemSearch.class.php");

function request_http_basic_auth() {
	header('WWW-Authenticate: Basic realm="' . get_opendb_title() . '"');
	header('HTTP/1.0 401 Unauthorized');
}

if (is_site_enabled()) {
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		request_http_basic_auth();
	} else {
		$userId = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
		
		if (is_user_active($userId) && validate_user_passwd($userId, $password)) {
			$server = new JsonRpcServer();
			$server->registerClass(new ItemSearch());
			$server->handle($object);
		} else {
			request_http_basic_auth();
		}
	}
} else {
	header('HTTP/1.0 503 Service Unavailable');
    echo "<h1>503 Service Unavailable</h1>";
    echo "This site is currently disabled";
    exit();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>