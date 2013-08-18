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
include_once ("./lib/database.php");
include_once ("./lib/logging.php");

function fetch_interest_level($item_id, $instance_no, $user_id) {
	$query = "SELECT level" . " FROM user_item_interest i" . " WHERE i.item_id = $item_id AND i.instance_no = $instance_no AND i.user_id = '$user_id'";
	//    	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, "query:" . $query);
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		// 	   	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, "level:" . $found['level']);
		return trim ( $found ['level'] );
	} else
		return FALSE;
}

function db_insert_interest_level($item_id, $instance_no, $user_id, $level) {
	// 	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($item_id, $user_id, $level));
	$query = "INSERT INTO user_item_interest (item_id, instance_no, user_id, level) " . "VALUES ('" . addslashes ( $item_id ) . "', " . "'" . addslashes ( $instance_no ) . "', " . "'" . addslashes ( $user_id ) . "', " . "'" . addslashes ( $level ) . "')";
	
	$update = db_query ( $query );
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows ();
	if ($update && $rows_affected !== - 1) {
		if ($rows_affected > 0)
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$item_id,
					$user_id,
					$level ) );
			// The update did not work. We make an insert. 
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$user_id,
				$level ) );
		return FALSE;
	}
}

function db_update_interest_level($item_id, $instance_no, $user_id, $level) {
	
	// 	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($item_id, $user_id, $level));
	$query = "UPDATE user_item_interest SET " . "level='" . addslashes ( $level ) . "'" . " WHERE item_id = '$item_id'" . " AND instance_no = '$instance_no'" . " AND user_id = '$user_id'";
	
	// If level is 0, we remove the DB entry
	if ($level == 0) {
		$query = "DELETE FROM user_item_interest" . " WHERE item_id = '$item_id'" . " AND instance_no = '$instance_no'";
		" AND user_id = '$user_id'";
	}
	
	$update = db_query ( $query );
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows ();
	if ($update && $rows_affected !== - 1) {
		if ($rows_affected > 0) {
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$item_id,
					$user_id,
					$level ) );
		} 		// The update did not work. We make an insert. 
		else if ($level > 0) {
			return db_insert_interest_level ( $item_id, $instance_no, $user_id, $level );
		}
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$user_id,
				$level ) );
		return FALSE;
	}
}

function db_remove_all_interest_level($user_id) {
	$query = "DELETE FROM user_item_interest" . " WHERE user_id = '$user_id'";
	
	$update = db_query ( $query );
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows ();
	if ($update && $rows_affected !== - 1) {
		if ($rows_affected > 0) {
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$user_id ) );
		}
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$user_id ) );
		return FALSE;
	}
}

function ajax_update_interest_level($item_id, $instance_no, $level) {
	$objResponse = new xajaxResponse ();
	
	$user_id = get_opendb_session_var ( 'user_id' );
	
	if ($level == 0) {
		$objResponse->assign ( "interest_level_$item_id" . "_$instance_no", "src", theme_image_src ( 'interest_0.gif' ) );
		$objResponse->assign ( "interest_level_$item_id" . "_$instance_no", "alt", get_opendb_lang_var ( 'interest_mark' ) );
		$objResponse->assign ( "interest_level_$item_id" . "_$instance_no", "title", get_opendb_lang_var ( 'interest_mark' ) );
		$objResponse->assign ( "new_level_value_$item_id" . "_$instance_no", "value", "1" );
	} else {
		$objResponse->assign ( "interest_level_$item_id" . "_$instance_no", "src", theme_image_src ( 'interest_1.gif' ) );
		$objResponse->assign ( "interest_level_$item_id" . "_$instance_no", "alt", get_opendb_lang_var ( 'interest_remove' ) );
		$objResponse->assign ( "interest_level_$item_id" . "_$instance_no", "title", get_opendb_lang_var ( 'interest_remove' ) );
		$objResponse->assign ( "new_level_value_$item_id" . "_$instance_no", "value", "0" );
	}
	
	db_update_interest_level ( $item_id, $instance_no, $user_id, $level );
	
	return $objResponse;
}

function ajax_remove_all_interest_level() {
	$user_id = get_opendb_session_var ( 'user_id' );
	
	$objResponse = new xajaxResponse ();
	
	if (db_remove_all_interest_level ( $user_id )) {
		// We update all the images
		$objResponse->call ( doRemoveInterestAllInterestLevel, theme_image_src ( 'interest_0.gif' ), get_opendb_lang_var ( 'interest_mark' ) );
	}
	return $objResponse;
}
?>