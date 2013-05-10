<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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
    
    MYSQLI_ api wrapper - a one - to - one wrapper around each of the mysqli_ functions, no opendb specific
    code, such as db configuration, etc, these will be handled by a separate function, called from
    include/begin.inc.php
 */

/**
 */
//		'mysqli_connect', 'mysqli_close', 'mysqli_error', 'mysqli_errno', 'mysqli_query', 'mysqli_affected_rows', 'mysqli_free_result',
//		'mysqli_fetch_assoc', 'mysqli_fetch_row', 'mysqli_fetch_field_direct', 'mysqli_num_rows', 'mysqli_num_fields', 'mysqli_insert_id');

/**
    $host - hostname:port, hostname:socket, socket, where a socket is to be provided, the : is compulsory, even
    if its first character, in which case localhost is assumed
    
    @param $cache_link - if TRUE, save reference to link for reuse.
 */
function db_connect($host, $user, $passwd, $dbname) {
	$index = strpos($host, ':');
	if ($index !== FALSE) {
		$port = substr($host, $index + 1);
		$host = substr($host, 0, $index);

		// probably a socket
		if (!is_numeric($port)) {
			$socket = $port;
			unset($port);
		}
	}

	if (strlen($socket) > 0) {
		$link = @mysqli_connect($host, $user, $passwd, $dbname, NULL, $socket);
	} else {
		if (strlen($port) > 0) {
			$link = @mysqli_connect($host, $user, $passwd, $dbname, $port);
		} else {
			$link = @mysqli_connect($host, $user, $passwd, $dbname);
		}
	}

	return $link;
}

function _db_ping($link) {
	return @mysqli_ping($link);
}

/**
remove reference to cached link upon close
 */
function _db_close($link) {
	return @mysqli_close($link);
}

function _db_error($link = NULL) {
	// link will be null if connect failed
	if ($link != NULL) {
		return @mysqli_error($link);
	} else {
		return @mysqli_connect_error();
	}
}

function _db_errno($link = NULL) {
	// link will be null if connect failed
	if ($link != NULL) {
		return @mysqli_errno($link);
	} else {
		return @mysqli_connect_errno();
	}
}

function _db_query($link, $sql) {
	return @mysqli_query($link, $sql);
}

function _db_affected_rows($link) {
	return @mysqli_affected_rows($link);
}

function _db_insert_id($link) {
	return @mysqli_insert_id($link);
}

function _db_free_result($result) {
	return @mysqli_free_result($result);
}

function _db_fetch_assoc($result) {
	return @mysqli_fetch_assoc($result);
}

function _db_fetch_row($result) {
	return @mysqli_fetch_row($result);
}

function _db_field_name($result, $field_offset) {
	$finfo = @mysqli_fetch_field_direct($result, $field_offset);
	if ($finfo != NULL) {
		return $finfo->name;
	} else {
		return NULL;
	}
}

function _db_num_rows($result) {
	return @mysqli_num_rows($result);
}

function _db_num_fields($result) {
	return @mysqli_num_fields($result);
}
?>