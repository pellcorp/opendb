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

include_once('./functions/item_type_group.php');
include_once('./functions/item_type.php');

function is_exists_item_type_item_type_group($s_item_type)
{
	$query = "SELECT 'x' FROM s_item_type_group_rltshp ".
			"WHERE s_item_type = '$s_item_type' LIMIT 0,1";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function fetch_item_type_item_type_group_cnt($s_item_type_group)
{
	$query = "SELECT COUNT('x') AS count FROM s_item_type_group_rltshp ".
			"WHERE s_item_type_group = '$s_item_type_group'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!== FALSE)
			return (int)$found['count'];
	}
	return FALSE;
}

function fetch_s_item_type_group_cnt()
{
	$query = "SELECT count('x') as count FROM s_item_type_group";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!== FALSE)
			return (int)$found['count'];
	}
    return FALSE;
}

function fetch_s_item_type_group_rs()
{
	$query = "SELECT s_item_type_group, description FROM s_item_type_group ORDER BY s_item_type_group";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_s_item_type_join_sitgr_rs($s_item_type_group)
{
    $s_item_type_group = strtoupper($s_item_type_group);

	$query = "SELECT sit.s_item_type, IF(sitgr.s_item_type_group IS NULL,'N','Y') AS exists_ind FROM s_item_type sit ".
			" LEFT JOIN s_item_type_group_rltshp sitgr ON ".
			"sitgr.s_item_type_group = '$s_item_type_group' AND sitgr.s_item_type = sit.s_item_type ".
			"ORDER BY sit.s_item_type";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/*
*/ 
function insert_s_item_type_group($s_item_type_group, $description)
{
	if(strlen($s_item_type_group)>0 && strlen($description)>0)
	{
		$s_item_type_group = strtoupper($s_item_type_group);
		$description = addslashes(trim(strip_tags($description)));

		$query = "INSERT INTO s_item_type_group (s_item_type_group, description) "
				."VALUES ('$s_item_type_group', '$description')";
	
		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type_group, $description));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type_group, $description));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function update_s_item_type_group($s_item_type_group, $description)
{	
	if(strlen($s_item_type_group)>0)
	{	
		$s_item_type_group = strtoupper($s_item_type_group);
		
		$query = "UPDATE s_item_type_group "
				."SET description = ".($description!==FALSE?"'".addslashes(trim(strip_tags($description)))."'":"description")
				." WHERE s_item_type_group = '$s_item_type_group'";
	
		$update = db_query($query);
	
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($update && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type_group, $description));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type_group, $description));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_item_type_group($s_item_type_group)
{
	if(strlen($s_item_type_group)>0)
	{
		$s_item_type_group = strtoupper($s_item_type_group);
	
		$query = "DELETE FROM s_item_type_group WHERE s_item_type_group = '$s_item_type_group'";
			
		$delete = db_query($query);
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type_group));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type_group));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

/*
*/ 
function insert_s_item_type_group_rltshp($s_item_type_group, $s_item_type)
{
	if(strlen($s_item_type_group)>0 || strlen($s_item_type)>0)
	{
		$s_item_type = strtoupper($s_item_type);
		$s_item_type_group = strtoupper($s_item_type_group);
			
		$query = "INSERT INTO s_item_type_group_rltshp (s_item_type_group, s_item_type) "
				."VALUES ('$s_item_type_group', '$s_item_type')";
	
		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type_group, $s_item_type));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type_group, $s_item_type));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_item_type_group_rltshp($s_item_type_group, $s_item_type = NULL)
{
	// ignore attempt to delete every record, this is done by accident!
	if(strlen($s_item_type_group)>0 || strlen($s_item_type)>0)
	{
		$s_item_type = strtoupper($s_item_type);
		$s_item_type_group = strtoupper($s_item_type_group);
	
		$query = "DELETE FROM s_item_type_group_rltshp WHERE ";
		
		if(strlen($s_item_type_group))
		{
			$query .= "s_item_type_group = '$s_item_type_group'";
			if(strlen($s_item_type)>0)
				$query .= " AND s_item_type = '$s_item_type'";
		}
		else
		{
			$query .= "s_item_type = '$s_item_type'";
		}
			
		$delete = db_query($query);
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type_group, $s_item_type));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, db_error(), array($s_item_type_group, $s_item_type));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}
?>
