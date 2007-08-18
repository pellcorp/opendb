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

include_once("./functions/status_type.php");
include_once("./functions/user.php");

/*
* If more than one default_ind=Y record, then the one being updated,can be updated
* safely.
*/
function fetch_default_status_type_cnt()
{
	$query = "SELECT count('x') as count FROM s_status_type WHERE closed_ind <> 'Y' AND default_ind = 'Y'";
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!== FALSE)
			return $found['count'];
	}

	//else
	return FALSE;
}

function is_exists_items_with_status_type($s_status_type)
{
	$query = "SELECT 'x' FROM item_instance WHERE s_status_type = '".$s_status_type."'";
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function is_exists_items_for_user_type_and_status_type($s_status_type, $user_type)
{
	$query = "SELECT 'x' ".
			"FROM item_instance ii, user u ".
			"WHERE ii.owner_id = u.user_id AND ".
			"ii.s_status_type = '$s_status_type' AND ".
			"IF(LENGTH(type)>0,u.type,'N') IN(".format_sql_in_clause(get_min_user_type_r($user_type)).")";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function is_exists_borrowed_items_for_status_type($s_status_type, $borrowed_items_only = FALSE)
{
	$query = "SELECT 'x' ".
			"FROM item_instance ii, borrowed_item bi ".
			"WHERE ii.item_id = bi.item_id AND ".
			"ii.instance_no = bi.instance_no AND ".
			"ii.s_status_type = '$s_status_type' ";

	if($borrowed_items_only)
	{
		$query .= "AND bi.status = 'B'";
	}
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

/*
* A single user type is all that is allowed.
*/
function validate_user_type_column($column)
{
	if(strlen(trim($column))>0)
	{
		if(is_usertype_valid(trim($column)))
			return trim($column);
		else
			return FALSE;
	}
	else
		return "";
}

function insert_s_status_type($s_status_type, $description, $img, 
							$insert_ind, $update_ind, $delete_ind, $change_owner_ind, 
							$min_display_user_type,	$min_create_user_type,
							$borrow_ind, $status_comment_ind, $default_ind)
{
	$s_status_type = strtoupper(substr(trim($s_status_type),0,1));
	
	// this should never happen.
	if(strlen(trim($s_status_type))==0)
	{
		return FALSE;
	}
	
	$description = addslashes(trim(strip_tags($description)));
	
	// do this one first, as we need to validate the data for the others based on this one.
	$change_owner_ind = validate_ind_column($change_owner_ind);
	
	$status_comment_ind = validate_ind_column($status_comment_ind);
	$insert_ind = validate_ind_column($insert_ind);
	$update_ind = validate_ind_column($update_ind);
	$delete_ind = validate_ind_column($delete_ind);
	
	$min_display_user_type = validate_user_type_column($min_display_user_type);
	
	$min_create_user_type = validate_user_type_column($min_create_user_type);
	
	$borrow_ind = validate_ind_column($borrow_ind);
	
	$query = "INSERT INTO s_status_type ( s_status_type, description, img, insert_ind, update_ind, delete_ind, change_owner_ind, min_display_user_type, min_create_user_type, borrow_ind, status_comment_ind, default_ind, closed_ind )".
			"VALUES ('$s_status_type', '$description', '$img', '$insert_ind', '$update_ind', '$delete_ind', '$change_owner_ind', '$min_display_user_type', '$min_create_user_type', '$borrow_ind', '$status_comment_ind', '$default_ind', 'N')";

	$insert = db_query($query);
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if($insert && $rows_affected !== -1)
	{
		if($rows_affected>0)
		{
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, db_error(), array($s_status_type, $description, $img, 
							$insert_ind, $update_ind, $delete_ind, $change_owner_ind, 
							$min_display_user_type,	$min_create_user_type,
							$borrow_ind, $status_comment_ind, $default_ind));
		}
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_status_type, $description, $img, 
							$insert_ind, $update_ind, $delete_ind, $change_owner_ind, 
							$min_display_user_type,	$min_create_user_type,
							$borrow_ind, $status_comment_ind, $default_ind));
		return FALSE;
	}
}

/**
Clear default indicator for all s_status_types except for current 
*/
function update_default_status_type($exclude_s_status_type)
{
	$update = db_query("UPDATE s_status_type SET default_ind = 'N' WHERE default_ind = 'Y' AND s_status_type <> '$exclude_s_status_type'");	
	$rows_affected = db_affected_rows();
	if($update && $rows_affected !== -1)
	{
		if($rows_affected>0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($exclude_s_status_type));
		}					
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($exclude_s_status_type));
		return FALSE;
	}
}

/*
*/
function update_s_status_type($s_status_type, $description, $img, 
							$insert_ind, $update_ind, $delete_ind, $change_owner_ind, 
							$min_display_user_type,	$min_create_user_type,
							$borrow_ind, $status_comment_ind, $default_ind,
							$closed_ind)
{
	$s_status_type = strtoupper($s_status_type);
	$description = addslashes(trim(strip_tags($description)));

	// do this one first, as we need to validate the data for the others based on this one.
	$change_owner_ind = validate_ind_column($change_owner_ind);
	
	$insert_ind = validate_ind_column($insert_ind);
	$update_ind = validate_ind_column($update_ind);
	$delete_ind = validate_ind_column($delete_ind);
	
	$min_display_user_type = validate_user_type_column($min_display_user_type);
	
	$min_create_user_type = validate_user_type_column($min_create_user_type);
	
	$borrow_ind = validate_ind_column($borrow_ind);
		
	$status_comment_ind = validate_ind_column($status_comment_ind);
	$default_ind = validate_ind_column($default_ind);
	$closed_ind = validate_ind_column($closed_ind);
	
	$query = "UPDATE s_status_type ".
				"SET description = '$description', ".
				"img = '$img', ".
				"insert_ind = '$insert_ind', ".
				"update_ind = '$update_ind', ".
				"delete_ind = '$delete_ind', ".
				"change_owner_ind = '$change_owner_ind', ".
				"min_display_user_type = '$min_display_user_type', ".
				"min_create_user_type = '$min_create_user_type', ".
				"borrow_ind = '$borrow_ind', ".
				"status_comment_ind = '$status_comment_ind', ".
				"default_ind = '$default_ind', ".
				"closed_ind = '$closed_ind' ".
			" WHERE s_status_type = '$s_status_type'";

	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if($update && $rows_affected !== -1)
	{
		if($rows_affected>0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_status_type, $description, $img, 
							$insert_ind, $update_ind, $delete_ind, $change_owner_ind,
							$min_display_user_type,	$min_create_user_type,
							$borrow_ind, $status_comment_ind, $default_ind,
							$closed_ind));
							
			if($default_ind == 'Y')
			{
				// clear any other s_status_type's records that currently have default_ind = Y
				update_default_status_type($s_status_type);
			}
		}					
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_status_type, $description, $img, 
							$insert_ind, $update_ind, $delete_ind, $change_owner_ind,
							$min_display_user_type,	$min_create_user_type,
							$borrow_ind, $status_comment_ind, $default_ind,
							$closed_ind));
		return FALSE;
	}
}

function delete_s_status_type($s_status_type)
{
	$s_status_type = strtoupper($s_status_type);
		
	$query = "DELETE FROM s_status_type "
			."WHERE s_status_type = '$s_status_type'";

	$delete = db_query($query);
	
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if($delete && $rows_affected !== -1)
	{
		if($rows_affected>0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_status_type));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_status_type));
		return FALSE;
	}
}
?>
