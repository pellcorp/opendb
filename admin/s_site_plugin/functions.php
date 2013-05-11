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

include_once("./lib/site_plugin.php");

function get_legal_input_field_types()
{
	return array('text', 'scan-isbn', 'scan-upc', 'hidden');
}

function fetch_max_site_plugin_order_no()
{
	$query = "SELECT MAX(order_no) as max_order_no ".
			"FROM s_site_plugin";
			
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$site_plugin_r = db_fetch_assoc($result);
		db_free_result($result);
		
		return $site_plugin_r['max_order_no'];
	}

	//else
	return FALSE;
}

function fetch_max_site_plugin_link_order_no($site_type)
{
	$query = "SELECT MAX(order_no) as max_order_no ".
			"FROM s_site_plugin_link ".
			"WHERE site_type = '$site_type'";
			
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$site_plugin_r = db_fetch_assoc($result);
		db_free_result($result);
		
		return $site_plugin_r['max_order_no'];
	}

	//else
	return FALSE;
}

function fetch_site_attribute_type_rs($site_type)
{
	$query = "SELECT s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type FROM s_attribute_type WHERE site_type = '$site_type'";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_site_item_attribute_type_rs($site_type, $s_item_type = NULL)
{
	$query = "SELECT siat.s_item_type, siat.s_attribute_type, siat.order_no, siat.prompt, siat.compulsory_ind ".
			"FROM s_item_attribute_type siat, s_attribute_type sat ".
			"WHERE sat.s_attribute_type = siat.s_attribute_type AND sat.site_type = '$site_type' ";
			
	if(strlen($s_item_type))
		$query .= "AND siat.s_item_type = '".$s_item_type."' ";
		
	$query .= "ORDER BY siat.s_attribute_type, siat.s_item_type, siat.order_no";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/**
A unique set of all s_attribute_type's which have at least one record in the
s_attribute_type_lookup table.
*/
function fetch_lookup_s_attribute_type_rs()
{
	$query = "SELECT DISTINCT sat.s_attribute_type ".
			"FROM s_attribute_type_lookup satl, ".
			"s_attribute_type sat ".
			"WHERE sat.s_attribute_type = satl.s_attribute_type ".
			"ORDER BY satl.s_attribute_type";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/*
* This function will insert the initial s_item_type only, no reference to the
* s_item_attribute_type's which will come later.
*/ 
function insert_s_site_plugin($site_type, $classname, $order_no, $title, $image, $description, $external_url, $items_per_page, $more_info_url)
{
	if(strlen($site_type)>0)
	{
		$site_type = strtolower($site_type);
		
		$title = addslashes(trim(strip_tags($title)));
		$description = addslashes(trim(strip_tags($description)));
		$more_info_url = addslashes(trim(strip_tags($more_info_url)));
		$external_url = addslashes(trim(strip_tags($external_url)));
		$image = addslashes(trim(strip_tags($image)));
		
		if(!is_numeric($order_no))
			$order_no = 0;
		
		if(!is_numeric($items_per_page))
			$items_per_page = 0;
	
		$query = "INSERT INTO s_site_plugin (site_type, classname, order_no, title, image, description, external_url, items_per_page, more_info_url) "
				."VALUES ('$site_type', '$classname', $order_no, '$title', '$image', '$description', '$external_url', $items_per_page, '$more_info_url')";
		$insert = db_query($query);
		
		if ($insert && db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $classname, $order_no, $title, $image, $description, $external_url, $items_per_page, $more_info_url));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $classname, $order_no, $title, $image, $description, $external_url, $items_per_page, $more_info_url));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function update_s_site_plugin($site_type, $classname, $order_no, $title, $image, $description, $external_url, $items_per_page, $more_info_url)
{	
	if(strlen($site_type)>0)
	{
		$site_type = strtolower($site_type);
		
		if($order_no !== FALSE && !is_numeric($order_no))
		{
			$order_no = FALSE;
		}
		
		if($items_per_page !== FALSE && !is_numeric($items_per_page))
		{
			$items_per_page = FALSE;
		}
		
		$query = "UPDATE s_site_plugin "
				."SET description = ".($description!==FALSE?"'".addslashes(trim(strip_tags($description)))."'":"description")
				.($classname!==FALSE?", classname = '".$classname."'":"")
				.($order_no!==FALSE?", order_no = $order_no":"")
				.($title!==FALSE?", title = '".addslashes(trim(strip_tags($title)))."'":"")
				.($image!==FALSE?", image = '".addslashes(trim(strip_tags($image)))."'":"")
				.($external_url!==FALSE?", external_url = '".addslashes(trim(strip_tags($external_url)))."'":"")
				.($items_per_page!==FALSE?", items_per_page = $items_per_page":"")
				.($more_info_url!==FALSE?", more_info_url = '".addslashes(trim(strip_tags($more_info_url)))."'":"")
				." WHERE site_type = '$site_type'";
	
		$update = db_query($query);
	
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($update && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $classname, $order_no, $title, $image, $description, $external_url, $items_per_page, $more_info_url));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $classname, $order_no, $title, $image, $description, $external_url, $items_per_page, $more_info_url));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_site_plugin($site_type)
{
	
	if(strlen($site_type)>0)
	{	
		$site_type = strtolower($site_type);
		
		$query = "DELETE FROM s_site_plugin WHERE site_type = '$site_type'";
		$delete = db_query($query);
		
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, db_error(), array($site_type));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function insert_s_site_plugin_conf($site_type, $name, $keyid, $value, $description = NULL)
{
	if(strlen($site_type)>0 && strlen($name)>0)
	{
		$site_type = strtolower($site_type);
		$name = strtolower($name);
		
		if(strlen($keyid) == 0)
			$keyid = '0';
		else
			$keyid = addslashes(trim(strip_tags($keyid)));
				
		$name = addslashes(trim(strip_tags($name)));
		$description = addslashes(trim(strip_tags($description)));
		$value = addslashes(trim(strip_tags($value)));
	
		$query = "INSERT INTO s_site_plugin_conf (site_type, description, name, keyid, value) "
				."VALUES ('$site_type', '$description', '$name', '$keyid', '$value')";
		$insert = db_query($query);
		
		if ($insert && db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $name, $keyid, $description, $value));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $name, $keyid, $description, $value));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function update_s_site_plugin_conf($site_type, $name, $keyid, $value, $description = FALSE)
{	
	if(strlen($site_type)>0 && strlen($name)>0 && strlen($keyid)>0)
	{
		$site_type = strtolower($site_type);
		$name = strtolower($name);
		
		$query = "UPDATE s_site_plugin_conf "
				."SET description = ".($description!==FALSE?"'".addslashes(trim(strip_tags($description)))."'":"description")
				.($value!==FALSE?", value = '".addslashes(trim(strip_tags($value)))."'":"")
				." WHERE site_type = '$site_type' AND name = '$name' AND keyid = '$keyid'";
	
		$update = db_query($query);
	
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($update && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $name, $keyid, $description, $value));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $name, $keyid, $description, $value));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_site_plugin_conf($site_type, $name = NULL, $keyid = NULL)
{
	if(strlen($site_type)>0)
	{
		$site_type = strtolower($site_type);
		
		$query = "DELETE FROM s_site_plugin_conf WHERE site_type = '$site_type'";
		
		if(strlen($name)>0)
		{
			$name = strtolower($name);
			$query .= " AND name = '$name'";
		}
		
		if(strlen($keyid)>0)
		{
			$query .= " AND keyid = '$keyid'";
		}
		
		$delete = db_query($query);
		
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $name, $keyid));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, db_error(), array($site_type, $name, $keyid));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function insert_s_site_plugin_input_field($site_type, $field, $order_no, $description, $prompt, $field_type, $default_value, $refresh_mask)
{
	if(strlen($site_type)>0 && strlen($field)>0)
	{
		$site_type = strtolower($site_type);
		$field = strtolower($field);
		
		$field_type = addslashes(trim(strip_tags($field_type)));
		$description = addslashes(trim(strip_tags($description)));
		$prompt = addslashes(trim(strip_tags($prompt)));
		$default_value = addslashes(trim(strip_tags($default_value)));
		$refresh_mask = addslashes(trim(strip_tags($refresh_mask)));
		
		if(!is_numeric($order_no))
			$order_no = 0;
			
		$type = strtolower($type);
		if(!in_array($type, get_legal_input_field_types()))
			$type = 'text';
			
		$query = "INSERT INTO s_site_plugin_input_field (site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask) "
				."VALUES ('$site_type', '$field', $order_no, '$description', '$prompt', '$field_type', '$default_value', '$refresh_mask')";
		
		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $field, $order_no, $description, $prompt, $field_type, $default_value, $refresh_mask));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $field, $order_no, $description, $prompt, $field_type, $default_value, $refresh_mask));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function update_s_site_plugin_input_field($site_type, $field, $order_no, $description, $prompt, $field_type, $default_value, $refresh_mask)
{	
	if(strlen($site_type)>0 && strlen($field)>0)
	{
		$site_type = strtolower($site_type);
		$field = strtolower($field);
		
		if($order_no !== FALSE && !is_numeric($order_no))
		{
			$order_no = 0;
		}
		
		if($field_type !== FALSE)
		{	
			$field_type = strtolower($field_type);
			if(!in_array($field_type, get_legal_input_field_types()))
				$field_type = 'text';
		}
			
		$query = "UPDATE s_site_plugin_input_field "
				."SET description = ".($description!==FALSE?"'".addslashes(trim(strip_tags($description)))."'":"description")
				.($order_no!==FALSE?", order_no = ".$order_no."":"")
				.($prompt!==FALSE?", prompt = '".addslashes(trim(strip_tags($prompt)))."'":"")
				.($field_type!==FALSE?", field_type = '".$field_type."'":"")
				.($default_value!==FALSE?", default_value = '".addslashes(trim(strip_tags($default_value)))."'":"")
				.($refresh_mask!==FALSE?", refresh_mask = '".addslashes(trim(strip_tags($refresh_mask)))."'":"")
				." WHERE site_type = '$site_type' AND field = '$field'";
	
		$update = db_query($query);
	
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($update && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $field, $order_no, $description, $prompt, $field_type, $default_value, $refresh_mask));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $field, $order_no, $description, $prompt, $field_type, $default_value, $refresh_mask));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_site_plugin_input_field($site_type, $field = NULL)
{
	if(strlen($site_type)>0)
	{	
		$site_type = strtolower($site_type);
		
		$query = "DELETE FROM s_site_plugin_input_field WHERE site_type = '$site_type'";
		if(strlen($field)>0)
		{
			$field = strtolower($field);
			
			$query .= " AND field = '$field'";
		}
		$delete = db_query($query);
		
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $field));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, db_error(), array($site_type, $field));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function insert_s_site_plugin_s_attribute_type_map($site_type, $variable, $s_item_type_group, $s_item_type, $s_attribute_type, $lookup_attribute_val_restrict_ind)
{
	if(strlen($site_type)>0 && strlen($variable)>0 && strlen($s_item_type_group)>0 && strlen($s_item_type)>0 && strlen($s_attribute_type)>0)
	{
		$site_type = strtolower($site_type);
		$variable = strtolower($variable);
		$s_attribute_type = strtoupper($s_attribute_type);
		$s_item_type = strtoupper($s_item_type);
		$s_item_type_group = strtoupper($s_item_type_group);
		
		if(is_lookup_attribute_type($s_attribute_type))
		{
			if($lookup_attribute_val_restrict_ind == 'Y' || $lookup_attribute_val_restrict_ind == 'y')
				$lookup_attribute_val_restrict_ind = 'Y';
			else
			    $lookup_attribute_val_restrict_ind = 'N';
		}
		else
		{
		    $lookup_attribute_val_restrict_ind = 'N';
		}
		
		// make sure only one of s_item_type_group and s_item_type is configured.
		if($s_item_type_group !== FALSE && 
				$s_item_type !== FALSE && 
				strlen($s_item_type)>0 && 
				strlen($s_item_type_group)>0 && 
				// if both are set to '*', its not a problem.
				($s_item_type != '*' || $s_item_type_group != '*'))
		{
			if($s_item_type != '*')
				$s_item_type_group = '*';
			else if($s_item_type_group != '*')
				$s_item_type = '*';
		}
		
		$query = "INSERT INTO s_site_plugin_s_attribute_type_map (site_type, variable, s_item_type_group, s_item_type, s_attribute_type, lookup_attribute_val_restrict_ind) "
				."VALUES ('$site_type', '$variable', '$s_item_type_group', '$s_item_type', '$s_attribute_type', '$lookup_attribute_val_restrict_ind')";
		
		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			$new_item_id = db_insert_id();
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $variable, $s_item_type_group, $s_item_type, $s_attribute_type, $lookup_attribute_val_restrict_ind));
			return $new_item_id;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $variable, $s_item_type_group, $s_item_type, $s_attribute_type, $lookup_attribute_val_restrict_ind));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function update_s_site_plugin_s_attribute_type_map($site_type, $sequence_number, $s_item_type_group, $s_item_type, $s_attribute_type, $lookup_attribute_val_restrict_ind)
{	
	if(strlen($s_item_type_group)>0 && is_numeric($sequence_number))
	{
		$s_item_type_group = strtoupper($s_item_type_group);
		$s_item_type = strtoupper($s_item_type);
		$s_attribute_type = strtoupper($s_attribute_type);
		
		if(is_lookup_attribute_type($s_attribute_type))
		{
			if($lookup_attribute_val_restrict_ind == 'Y' || $lookup_attribute_val_restrict_ind == 'y')
				$lookup_attribute_val_restrict_ind = 'Y';
			else
			    $lookup_attribute_val_restrict_ind = 'N';
		}
		else
		{
		    $lookup_attribute_val_restrict_ind = 'N';
		}
		
		// make sure only one of s_item_type_group and s_item_type is configured.
		if($s_item_type_group !== FALSE && 
				$s_item_type !== FALSE && 
				strlen($s_item_type)>0 && 
				strlen($s_item_type_group)>0 && 
				// if both are set to '*', its not a problem.
				($s_item_type != '*' || $s_item_type_group != '*'))
		{
			if($s_item_type != '*')
				$s_item_type_group = '*';
			else if($s_item_type_group != '*')
				$s_item_type = '*';
		}
		
		$query = "UPDATE s_site_plugin_s_attribute_type_map "
				."SET variable = variable "
				.($s_item_type_group!==FALSE?", s_item_type_group = '".$s_item_type_group."'":"")
				.($s_item_type!==FALSE?", s_item_type = '".$s_item_type."'":"")
				.($s_attribute_type!==FALSE?", s_attribute_type = '".$s_attribute_type."'":"")
				.($lookup_attribute_val_restrict_ind!==FALSE?", lookup_attribute_val_restrict_ind = '".$lookup_attribute_val_restrict_ind."'":"")
				." WHERE site_type = '$site_type' AND sequence_number = $sequence_number";
				
		$update = db_query($query);
	
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($update && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $sequence_number, $s_item_type_group, $s_item_type, $s_attribute_type, $lookup_attribute_val_restrict_ind));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $sequence_number, $s_item_type_group, $s_item_type, $s_attribute_type, $lookup_attribute_val_restrict_ind));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_site_plugin_s_attribute_type_map($site_type, $sequence_number = NULL)
{
	if(strlen($site_type)>0)
	{
		$site_type = strtolower($site_type);
		
		$query = "DELETE FROM s_site_plugin_s_attribute_type_map ".
			"WHERE site_type = '$site_type'";
			
		if(is_numeric($sequence_number))
		{
			$query .= " AND sequence_number = $sequence_number";
		}
		
		$delete = db_query($query);
		
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $sequence_number));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, db_error(), array($site_type, $sequence_number));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function insert_s_site_plugin_s_attribute_type_lookup_map($site_type, $s_attribute_type, $value, $lookup_attribute_val)
{
	if(strlen($site_type)>0 && strlen($s_attribute_type)>0 && strlen($value)>0 && strlen($lookup_attribute_val)>0)
	{
		$site_type = strtolower($site_type);
		$s_attribute_type = strtoupper($s_attribute_type);
		
		$value = addslashes(trim(strip_tags($value)));
		$lookup_attribute_val = addslashes(trim(strip_tags($lookup_attribute_val)));
		
		$query = "INSERT INTO s_site_plugin_s_attribute_type_lookup_map (site_type, s_attribute_type, value, lookup_attribute_val) "
				."VALUES ('$site_type', '$s_attribute_type', '$value', '$lookup_attribute_val')";
		
		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			$new_item_id = db_insert_id();
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $s_attribute_type, $value, $lookup_attribute_val));
			return $new_item_id;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $s_attribute_type, $value, $lookup_attribute_val));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function update_s_site_plugin_s_attribute_type_lookup_map($site_type, $sequence_number, $lookup_attribute_val)
{	
	if(strlen($site_type)>0 && is_numeric($sequence_number))
	{	
		$site_type = strtolower($site_type);
		
		$query = "UPDATE s_site_plugin_s_attribute_type_lookup_map "
				."SET lookup_attribute_val = '".addslashes(trim(strip_tags($lookup_attribute_val)))."'"
				." WHERE site_type = '$site_type' AND sequence_number = $sequence_number";
	
		$update = db_query($query);
	
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($update && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $sequence_number, $lookup_attribute_val));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $sequence_number, $lookup_attribute_val));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_site_plugin_s_attribute_type_lookup_map($site_type, $sequence_number = NULL)
{
	if(strlen($site_type)>0)
	{
		$site_type = strtolower($site_type);
		
		$query = "DELETE FROM s_site_plugin_s_attribute_type_lookup_map ".
			"WHERE site_type = '$site_type'";
			
		if(is_numeric($sequence_number))
		{
			$query .= " AND sequence_number = $sequence_number";
		}
		
		$delete = db_query($query);
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $sequence_number));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, db_error(), array($site_type, $sequence_number));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function insert_s_site_plugin_link($site_type, $s_item_type_group, $s_item_type, $order_no, $description, $url, $title_url)
{
	if(strlen($site_type)>0 && strlen($s_item_type_group)>0 && strlen($s_item_type)>0 && is_numeric($order_no))
	{
		$site_type = strtolower($site_type);
		$s_item_type = strtoupper($s_item_type);
		$s_item_type_group = strtoupper($s_item_type_group);
		
		// make sure only one of s_item_type_group and s_item_type is configured.
		if(strlen($s_item_type)>0 && 
				strlen($s_item_type_group)>0 && 
				// if both are set to '*', its not a problem.
				($s_item_type != '*' || $s_item_type_group != '*'))
		{
			if($s_item_type != '*')
				$s_item_type_group = '*';
			else if($s_item_type_group != '*')
				$s_item_type = '*';
		}
		
		$description = addslashes(trim(strip_tags($description)));
		$url = addslashes(trim(strip_tags($url)));
		$title_url = addslashes(trim(strip_tags($title_url)));
		
		if(!is_numeric($order_no))
		{
			$order_no = 0;
		}
		
		$query = "INSERT INTO s_site_plugin_link (site_type, s_item_type_group, s_item_type, order_no, description, url, title_url) "
				."VALUES ('$site_type', '$s_item_type_group', '$s_item_type', $order_no, '$description', '$url', '$title_url')";
		
		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			$new_item_id = db_insert_id();
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $s_item_type_group, $s_item_type, $order_no, $description, $url, $title_url));
			return $new_item_id;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $s_item_type_group, $s_item_type, $order_no, $description, $url, $title_url));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function update_s_site_plugin_link($site_type, $sequence_number, $s_item_type_group, $s_item_type, $order_no, $description, $url, $title_url)
{	
	if(strlen($site_type)>0 && is_numeric($sequence_number))
	{
		$site_type = strtolower($site_type);
		$s_item_type = strtoupper($s_item_type);
		$s_item_type_group = strtoupper($s_item_type_group);
		
		// make sure only one of s_item_type_group and s_item_type is configured.
		if($s_item_type_group !== FALSE && 
				$s_item_type !== FALSE && 
				strlen($s_item_type)>0 && 
				strlen($s_item_type_group)>0 && 
				// if both are set to '*', its not a problem.
				($s_item_type != '*' || $s_item_type_group != '*'))
		{
			if($s_item_type != '*')
				$s_item_type_group = '*';
			else if($s_item_type_group != '*')
				$s_item_type = '*';
		}
			
		$query = "UPDATE s_site_plugin_link "
				."SET description = ".($description!==FALSE?"'".addslashes(trim(strip_tags($description)))."'":"description")
				.($url!==FALSE?", url = '".addslashes(trim(strip_tags($url)))."'":"")
				.($title_url!==FALSE?", title_url = '".addslashes(trim(strip_tags($title_url)))."'":"")
				.($order_no!==FALSE && is_numeric($order_no)?", order_no = $order_no":"")
				.($s_item_type_group!==FALSE?", s_item_type_group = '$s_item_type_group'":"")
				.($s_item_type!==FALSE?", s_item_type = '$s_item_type'":"")
				// I know this clause is not required, but it is there as a safety messure.
				." WHERE site_type = '$site_type' AND "
				."sequence_number = $sequence_number";
				
		$update = db_query($query);
	
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($update && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $sequence_number, $s_item_type_group, $s_item_type, $order_no, $description, $url, $title_url));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $sequence_number, $s_item_type_group, $s_item_type, $order_no, $description, $url, $title_url));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}

function delete_s_site_plugin_link($site_type, $sequence_number = NULL)
{
	if(strlen($site_type)>0)
	{
		$site_type = strtolower($site_type);
		
		$query = "DELETE FROM s_site_plugin_link ".
			"WHERE site_type = '$site_type'";
			
		if(is_numeric($sequence_number))
		{
			$query .= " AND sequence_number = $sequence_number";
		}

		$delete = db_query($query);
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows();
		if($delete && $rows_affected !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($site_type, $sequence_number));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($site_type, $sequence_number));
			return FALSE;
		}
	}
	
	//else
	return FALSE;
}
?>
