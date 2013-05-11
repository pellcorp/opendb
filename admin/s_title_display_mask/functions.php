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
include_once("./lib/item_type_group.php");
include_once("./lib/item_type.php");

function fetch_s_title_display_mask_rs()
{
	$query = "SELECT id, description ".
			"FROM s_title_display_mask ".
			"ORDER BY id";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/**
	Returns a distinct set of item_type_group / item_type relationships
*/
function fetch_title_mask_items_r($stdm_id, $s_item_type_group, $s_item_type)
{
	if(strlen($s_item_type) == 0)
		$s_item_type = '*';
    if(strlen($s_item_type_group) == 0)
		$s_item_type_group = '*';

    $query = "SELECT display_mask FROM s_title_display_mask_item ".
				"WHERE stdm_id = '$stdm_id' AND s_item_type_group = '$s_item_type_group' AND s_item_type = '$s_item_type'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		return $found;
	}
	else
		return FALSE;
}

function is_exists_s_title_display_mask($stdm_id)
{
	if(strlen($stdm_id)>0)
	{
		$query = "SELECT 'x' FROM s_title_display_mask WHERE id = '$stdm_id'";

		$result = db_query($query);
		if($result && db_num_rows($result)>0)
		{
			db_free_result($result);
			return TRUE;
		}
	}
	//else
	return FALSE;
}

function is_exists_s_title_display_mask_group($s_item_type_group, $s_item_type)
{
	if($s_item_type_group == NULL)
		$s_item_type_group = '*';
	
	if($s_item_type == NULL)
		$s_item_type = '*';
		
	$query = "SELECT 'X' FROM s_title_display_mask_item WHERE s_item_type_group = '$s_item_type_group' AND s_item_type = '$s_item_type'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function is_exists_s_title_display_mask_item($stdm_id, $s_item_type_group, $s_item_type)
{
	if(strlen($stdm_id)>0)
	{
		$query = "SELECT 'x' FROM s_title_display_mask_item ".
				"WHERE stdm_id = '$stdm_id' AND s_item_type_group = '$s_item_type_group' AND s_item_type = '$s_item_type'";

		$result = db_query($query);
		if($result && db_num_rows($result)>0)
		{
			db_free_result($result);
			return TRUE;
		}
	}
	//else
	return FALSE;
}

function insert_s_title_display_mask_item($stdm_id, $s_item_type_group, $s_item_type, $display_mask)
{
    if(strlen($stdm_id)>0 && strlen($s_item_type_group)>0 && strlen($s_item_type)>0 && strlen($display_mask)>0)
	{
	    // ensure parent record exists
		if(is_exists_s_title_display_mask($stdm_id))
		{
		    if(($s_item_type_group == '*' || is_exists_item_type_group($s_item_type_group)) &&
		            ($s_item_type == '*' || is_exists_item_type($s_item_type)))
		    {
		    	$query = "INSERT INTO s_title_display_mask_item (stdm_id, s_item_type_group, s_item_type, display_mask) "
						."VALUES ('$stdm_id', '$s_item_type_group', '$s_item_type', '".addslashes(trim(strip_tags($display_mask)))."')";

				$insert = db_query($query);
				if ($insert && db_affected_rows() > 0)
				{
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($stdm_id, $s_item_type_group, $s_item_type, $display_mask));
					return TRUE;
				}
				else
				{
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($stdm_id, $s_item_type_group, $s_item_type, $display_mask));
					return FALSE;
				}
			}
			else
			{
			    opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid s_item_type_group or s_item_type', array($stdm_id, $s_item_type_group, $s_item_type, $display_mask));
				return FALSE;
			}
		}
		else
		{
            opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Parent s_title_display_mask not found', array($stdm_id, $s_item_type_group, $s_item_type, $display_mask));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

/**
*/
function update_s_title_display_mask_item($stdm_id, $s_item_type_group, $s_item_type, $display_mask)
{
    if(strlen($stdm_id)>0 && strlen($s_item_type_group)>0 && strlen($s_item_type)>0 && strlen($display_mask)>0)
	{
	    // ensure parent record exists
		if(is_exists_s_title_display_mask_item($stdm_id, $s_item_type_group, $s_item_type))
		{
			$query = "UPDATE s_title_display_mask_item "
				."SET display_mask = '".addslashes(trim(strip_tags($display_mask)))."'"
				." WHERE stdm_id = '$stdm_id' AND "
				."s_item_type_group = '$s_item_type_group' AND "
                ."s_item_type = '$s_item_type'";

			$update = db_query($query);

			// We should not treat updates that were not actually updated because value did not change as failures.
			if($update && ($rows_affected = db_affected_rows()) !== -1)
			{
				if($rows_affected>0)
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($stdm_id, $s_item_type_group, $s_item_type, $display_mask));
				return TRUE;
			}
			else
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($stdm_id, $s_item_type_group, $s_item_type, $display_mask));
				return FALSE;
			}
		}//if(is_exists_s_title_display_mask_item($stdm_id, $s_item_type_group, $s_item_type))
		else
		{
			return FALSE;
		}
	}

	//else
	return FALSE;
}

/**
*/
function delete_s_title_display_mask_item($stdm_id, $s_item_type_group, $s_item_type)
{
	if(strlen($stdm_id)>0 && strlen($s_item_type_group)>0 && strlen($s_item_type)>0)
	{
		// ensure parent record exists
		if(is_exists_s_title_display_mask($stdm_id))
		{
			$query = "DELETE FROM s_title_display_mask_item ".
				" WHERE stdm_id = '$stdm_id' AND ".
				"s_item_type_group = '$s_item_type_group' AND ".
                "s_item_type = '$s_item_type'";

			$delete = db_query($query);
			// We should not treat deletes that were not actually updated because value did not change as failures.

			if($delete && ($rows_affected = db_affected_rows()) !== -1)
			{
				if($rows_affected>0)
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($stdm_id, $s_item_type_group, $s_item_type));
				return TRUE;
			}
			else
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($stdm_id, $s_item_type_group, $s_item_type));
				return FALSE;
			}
		}
	}

	//else
	return FALSE;
}
?>
