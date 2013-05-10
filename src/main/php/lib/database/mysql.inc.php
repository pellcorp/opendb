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
    
    MYSQL_ api wrapper - a one - to - one wrapper around each of the mysql_ functions, no opendb specific
    code, such as db configuration, etc, these will be handled by a separate function, called from
    include/begin.inc.php
 */

//		'mysql_connect', 'mysql_select_db', 'mysql_close', 'mysql_error', 'mysql_errno', 'mysql_query', 
//		'mysql_affected_rows', 'mysql_free_result',	'mysql_fetch_assoc', 'mysql_fetch_row', 'mysql_field_name', 
//		'mysql_num_rows', 'mysql_num_fields', 'mysql_insert_id');

/**
    $host - hostname:port, hostname:socket, socket
 */
function db_connect($host, $user, $passwd, $dbname) {
	$link = @mysql_connect($host, $user, $passwd);
	if ($link !== FALSE) {
		if (@mysql_select_db($dbname, $link)) {
			return $link;
		}
	}

	//else
	return FALSE;
}

function _db_ping($link) {
	return @mysql_ping($link);
}

function _db_close($link) {
	return @mysql_close($link);
}

function _db_error($link = NULL) {
	// link will be null if connect failed
	return @mysql_error($link);
}

function _db_errno($link = NULL) {
	// link will be null if connect failed
	return @mysql_errno($link);
}

function _db_query($link, $sql) {
	return @mysql_query($sql, $link);
}

function _db_affected_rows($link) {
	return @mysql_affected_rows($link);
}

function _db_insert_id($link) {
	return @mysql_insert_id($link);
}

function _db_free_result($result) {
	return @mysql_free_result($result);
}

function _db_fetch_assoc($result) {
	return @mysql_fetch_assoc($result);
}

function _db_fetch_row($result) {
	return @mysql_fetch_row($result);
}

function _db_field_name($result, $field_offset) {
	return @mysql_field_name($result, $field_offset);
}

function _db_num_rows($result) {
	return @mysql_num_rows($result);
}

function _db_num_fields($result) {
	return @mysql_num_fields($result);
}
?>