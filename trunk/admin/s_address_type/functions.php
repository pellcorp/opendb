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

include_once('./functions/user.php');
include_once('./functions/address_type.php');

/**
* If any items found with the specified s_item_type, then
* the s_item_type is not deletable.
*/
function is_s_address_type_deletable($s_address_type)
{
	$query = "SELECT 'x' FROM user_address WHERE s_address_type='".$s_address_type."'";
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return FALSE;
	}

	//else
	return TRUE;
}

/**
* If any item_attributes found with the specified s_item_type, s_attribute_type
* and order_no then the s_item_attribute_type record is not deletable.
*/
function is_s_addr_attribute_type_rltshp_deletable($s_address_type, $s_attribute_type, $order_no)
{
	$query = "SELECT 'x' FROM user_address ua, user_address_attribute uaa ".
			"WHERE ua.sequence_number = uaa.ua_sequence_number AND ua.s_address_type = '$s_address_type' AND ".
			"uaa.s_attribute_type = '$s_attribute_type' AND uaa.order_no = '$order_no'";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return FALSE;
	}

	//else
	return TRUE;
}

function fetch_s_addr_attribute_type_rltshp_rs($s_address_type)
{
	$query = "SELECT s_attribute_type, order_no, prompt, closed_ind FROM s_addr_attribute_type_rltshp WHERE s_address_type = '$s_address_type' ORDER BY order_no ASC";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_s_address_type_rs($orderby = "display_order", $order = "asc")
{
	$query = "SELECT s_address_type, display_order, description, closed_ind FROM s_address_type ORDER BY $orderby $order";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_s_address_type_r($s_address_type)
{
	$query = "SELECT s_address_type, display_order, description, closed_ind FROM s_address_type WHERE s_address_type = '$s_address_type'";
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		return $found;
	}
	
	//else
	return FALSE;
}

/*
* This function will insert the initial s_item_type only, no reference to the
* s_item_attribute_type's which will come later.
*/ 
function insert_s_address_type($s_address_type, $display_order, $description)
{
	$description = addslashes(trim(strip_tags($description)));
	
	$query = "INSERT INTO s_address_type (s_address_type, display_order, description) "
			."VALUES ('$s_address_type', ".(is_numeric($display_order)?"'$display_order'":"NULL").", '$description')";

	$insert = db_query($query);
	if($insert && db_affected_rows() > 0)
	{
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_address_type, $display_order, $description));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_address_type, $display_order, $description));
		return FALSE;
	}
}

function update_s_address_type($s_address_type, $display_order, $description, $closed_ind)
{
	$description = addslashes(trim(strip_tags($description)));

	$closed_ind = strtoupper(trim($closed_ind));
	if($closed_ind != 'Y')
		$closed_ind = 'N';
	
	$query = "UPDATE s_address_type "
			."SET "
			.($display_order!==FALSE?" display_order = ".(is_numeric($display_order)?"'$display_order', ":"NULL, "):"")
			."description = '$description' "
			.", closed_ind = '$closed_ind' "
			." WHERE s_address_type = '$s_address_type'";

	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if($update && $rows_affected !== -1)
	{
		if($rows_affected>0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_address_type, $display_order, $description, $closed_ind));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_address_type, $display_order, $description, $closed_ind));
		return FALSE;
	}
}

function delete_s_address_type($s_address_type)
{
	$query = "DELETE FROM s_address_type "
			."WHERE s_address_type = '$s_address_type'";

	$delete = db_query($query);
	
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if($delete && $rows_affected !== -1)
	{
		if($rows_affected>0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_address_type));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_address_type));
		return FALSE;
	}
}

/**
*/
function insert_s_addr_attribute_type_rltshp($s_address_type, $s_attribute_type, $order_no, $prompt, $closed_ind)
{
	$prompt = addslashes(trim(strip_tags($prompt)));
	
	$query = "INSERT INTO s_addr_attribute_type_rltshp (s_address_type, s_attribute_type, order_no, prompt) "
			."VALUES ('$s_address_type', '$s_attribute_type', ".(is_numeric($order_no)?"'$order_no'":"0").", '$prompt')";
	$insert = db_query($query);
	if($insert && db_affected_rows() > 0)
	{
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_address_type, $s_attribute_type, $order_no, $prompt, $closed_ind));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_address_type, $s_attribute_type, $order_no, $prompt, $closed_ind));
		return FALSE;
	}
}

function update_s_addr_attribute_type_rltshp($s_address_type, $s_attribute_type, $order_no, $prompt, $closed_ind)
{
	$prompt = addslashes(trim(strip_tags($prompt)));
	
	$closed_ind = strtoupper(trim($closed_ind));
	if($closed_ind != 'Y')
		$closed_ind = 'N';

	$query = "UPDATE s_addr_attribute_type_rltshp "
			."SET prompt = '$prompt' "
			.", closed_ind = '$closed_ind' "
			." WHERE s_address_type = '$s_address_type' AND s_attribute_type = '$s_attribute_type' AND order_no = '$order_no'";

	$update = db_query($query);
	
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if($update && $rows_affected !== -1)
	{
		if($rows_affected>0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_address_type, $s_attribute_type, $order_no, $prompt, $closed_ind));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_address_type, $s_attribute_type, $order_no, $prompt, $closed_ind));
		return FALSE;
	}
}

function delete_s_addr_attribute_type_rltshp($s_address_type, $s_attribute_type, $order_no)
{
	$query = "DELETE FROM s_addr_attribute_type_rltshp "
			."WHERE s_address_type = '$s_address_type'";

	if(strlen($s_attribute_type)>0)
	{			
		$query .= " AND s_attribute_type = '$s_attribute_type' AND order_no = '$order_no'";
	}

	$delete = db_query($query);
	
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if($delete && $rows_affected !== -1)
	{
		if($rows_affected>0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_address_type, $s_attribute_type, $order_no));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_address_type, $s_attribute_type, $order_no));
		return FALSE;
	}
}
?>
