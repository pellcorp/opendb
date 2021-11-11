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

	@author Doug Meyers <dgmyrs@users.sourceforge.net>
*/

/*
	This is the script for the announcement table.
*/
include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/user.php");
include_once("./lib/utils.php");

function get_announcements_whereclause($limit_days, $limit_closed) {
	$where = NULL;
	if ($limit_days == 'Y')
		$where [] = " ((TO_DAYS(NOW()) - TO_DAYS(submit_on)) <= display_days OR display_days = 0) ";
	
	if ($limit_closed == 'Y')
		$where [] = " closed_ind = 'N' ";
	
	if (is_array ( $where )) {
		$query = 'WHERE ';
		
		$whereclause = '';
		foreach ( $where as $q ) {
			if (strlen ( $whereclause ) > 0)
				$whereclause .= ' AND';
			
			$whereclause .= $q;
		}
		
		$query .= $whereclause;
	}
	
	return $query;
}
/*
	This will return all announcements available to the given user type
*/
function fetch_announcement_rs($order_by = 'submit_on', $sortorder = 'DESC', $start_index = NULL, $items_per_page = NULL, $limit_days = 'N', $limit_closed = 'N') {
	// Uses the special 'zero' value lastvisit = 0 to test for default date value.
	$query = "SELECT sequence_number, user_id, title, content, " . " UNIX_TIMESTAMP(submit_on) as submit_on, " . " display_days, closed_ind " . " FROM announcement ";
	
	$query .= get_announcements_whereclause ( $limit_days, $limit_closed );
	
	// For simplicity sake!
	if (strlen ( $order_by ) == 0)
		$order_by = "submit_on";
	if (strlen ( $sortorder ) == 0)
		$sortorder = "DESC";
	
	if ($order_by === "submit_on")
		$query .= " ORDER BY submit_on " . $sortorder;
	else if ($order_by === "title")
		$query .= " ORDER BY title " . $sortorder . ", submit_on DESC";
	else if ($order_by === "display_days")
		$query .= " ORDER BY display_days " . $sortorder . ", submit_on DESC";
	else if ($order_by === "closed_ind")
		$query .= " ORDER BY closed_ind " . $sortorder . ", submit_on DESC";
	
	if (is_numeric ( $start_index ) && is_numeric ( $items_per_page ))
		$query .= ' LIMIT ' . $start_index . ', ' . $items_per_page;
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
	Returns the number of announcements, or FALSE if no records.
*/
function fetch_announcement_cnt($limit_days = 'N', $limit_closed = 'N') {
	// Uses the special 'zero' value lastvisit = 0 to test for default date value.
	$query = "SELECT COUNT('x') as count " . " FROM announcement ";
	
	$query .= get_announcements_whereclause ( $limit_days, $limit_closed );
	
	if ($limit_days == 'Y')
		$query .= " AND ((TO_DAYS(NOW()) - TO_DAYS(submit_on)) <= display_days OR display_days = 0) ";
	
	if ($limit_closed == 'Y')
		$query .= " AND closed_ind = 'N' ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/*
	This will return an annoucement record for a given id
*/
function fetch_announcement_r($announcement_id) {
	$query = "SELECT sequence_number as announcement_id, user_id, title, content, " . " UNIX_TIMESTAMP(submit_on) as submit_on, " . " display_days, closed_ind " . " FROM announcement " . " WHERE sequence_number = " . $announcement_id;
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	}
	//else
	return FALSE;
}

/**
	Returns the announcement title.
*/
function fetch_announcement_title($announcement_id) {
	$query = "SELECT title FROM announcement where sequence_number = " . $announcement_id;
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['title'];
	}
	
	//else
	return FALSE;
}
?>
