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
 */
include_once("lib/user.php");

function insert_announcement($title, $content, $display_days) {
	$title = addslashes(replace_newlines(trim($title)));
	$content = addslashes(replace_newlines(trim($content)));

	if (strlen($title) > 0 && strlen($content) > 0 && is_numeric($display_days)) {
		$query = "INSERT INTO announcement (user_id, title, content, display_days, closed_ind)" . " VALUES('" . get_opendb_session_var('user_id') . "'," . "'" . $title . "'," . "'" . $content . "'," . $display_days . ", " . "'N')";

		$insert = db_query($query);
		if (db_affected_rows() > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($title, $content, $display_days));
			return TRUE;
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($title, $content, $display_days));
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function update_announcement($announcement_id, $title, $content, $display_days, $closed_ind) {
	$title = addslashes(replace_newlines(trim($title)));
	$content = addslashes(replace_newlines(trim($content)));

	if (strlen($title) > 0 && strlen($content) > 0 && is_numeric($display_days)) {
		if ($closed_ind != NULL && $closed_ind == 'Y' || $closed_ind == 'y')
			$closed_ind = 'Y';
		else
			$closed_ind = 'N';

		$query = "UPDATE announcement SET " . "title='" . $title . "', " . "content='" . $content . "', " . "submit_on=submit_on, " . "display_days=" . $display_days . ", " . "closed_ind='" . $closed_ind . "' " . " WHERE sequence_number = " . $announcement_id;

		$update = db_query($query);
		$rows_affected = db_affected_rows();
		if ($update && $rows_affected !== -1) {
			if ($rows_affected > 0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($announcement_id, $title, $content, $display_days, $closed_ind));
			return TRUE;
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($announcement_id, $title, $content, $display_days, $closed_ind));
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function delete_announcement($announcement_id) {
	$query = "DELETE FROM announcement WHERE sequence_number = " . $announcement_id;
	$delete = db_query($query);
	if (db_affected_rows() > 0) {
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($announcement_id));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($announcement_id));
		return FALSE;
	}
}
?>