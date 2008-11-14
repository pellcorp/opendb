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

include_once("./functions/widgets.php");
include_once("./functions/user.php");
include_once("./functions/parseutils.php");

function fetch_instance_attribute_type_rs()
{
	$query = "SELECT DISTINCT sat.s_attribute_type, sat.description FROM
			s_item_attribute_type siat, s_attribute_type sat
			WHERE siat.s_attribute_type = sat.s_attribute_type AND
			siat.instance_attribute_ind = 'Y' OR 
			sat.s_field_type = 'ITEM_ID'
			ORDER BY 1";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/**
Will query for all groups, where group_id does not have a '.' (period), which
indicates a subgroup.
*/
function fetch_s_config_group_rs($group_id = NULL)
{
    $query = "SELECT id, name, description ".
	        "FROM s_config_group ";

	if($group_id != NULL)
	    $query .= "WHERE id = '$group_id' ";
	else
	    $query .= "WHERE id NOT LIKE '%.%' ";

	$query .= "ORDER by order_no, name, id";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_s_config_subgroup_rs($group_id)
{
	$query = "SELECT id, name, description ".
	        "FROM s_config_group ".
	        "WHERE id LIKE '".$group_id.".%' ".
			"ORDER by order_no, name, id";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_s_config_group_item_rs($group_id)
{
    $query = "SELECT group_id, id, keyid, if(length(prompt)>0,prompt,if(keyid!='0',CONCAT(id,'[',keyid,']'),id)) as prompt, description, type, subtype ".
	        "FROM s_config_group_item ".
	        "WHERE group_id = '$group_id' ".
			"ORDER by order_no, id, keyid";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/**
Does a basic check to make sure the parent record exists.
*/
function is_exists_s_config_group_item($group_id, $id, $keyid)
{
	if(strlen($group_id)>0 && strlen($id)>0 && strlen($keyid)>0)
	{
		$query = "SELECT 'x' FROM s_config_group_item WHERE group_id = '$group_id' AND id = '$id' ";

		if(is_numeric($keyid))
		{
			$query .= " AND (type = 'array' OR keyid = '$keyid') ";
		}
		else
		{
            $query .= " AND keyid = '$keyid' ";
		}

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

function is_exists_s_config_group_item_var($group_id, $id, $keyid = NULL)
{
	if(strlen($group_id)>0 && strlen($id)>0)
	{
		$query = "SELECT 'x' FROM s_config_group_item_var WHERE group_id = '$group_id' AND id = '$id' ";

		if(strlen($keyid))
		{
            $query .= " AND keyid = '$keyid' ";
		}

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

function validate_s_config_group_item($group_id, $id, $keyid, $value)
{
	if(strlen($group_id)>0 && strlen($id)>0 && strlen($keyid)>0)
	{
		$query = "SELECT type, subtype FROM s_config_group_item WHERE group_id = '$group_id' AND id = '$id' ";

		if(is_numeric($keyid))
		{
			$query .= " AND (type = 'array' OR keyid = '$keyid') ";
		}
		else
		{
            $query .= " AND keyid = '$keyid' ";
		}

		$query .= "LIMIT 0,1";

        $result = db_query($query);
		if($result && db_num_rows($result)>0)
		{
			$found = db_fetch_assoc($result);
            $value = trim($value);

			// will not directly validate an array, but instead the subtype of the array.
			if($found['type'] == 'array')
			{
			    // by default its text
			    if(strlen($found['subtype'])==0)
			        $found['subtype'] = 'text';

				if($found['subtype'] == 'usertype')
               		$found['type'] = 'usertype';
				else if($found['subtype'] == 'number')
               	    $found['type'] = 'number';
				else
				    $found['type'] = 'text';
			}

            switch($found['type'])
			{
				case 'boolean':
					$value = strtoupper($value);
					if($value == 'TRUE' || $value == 'FALSE')
						return $value;
					else
						return 'FALSE';

				case 'email':
					if(is_valid_email_addr($value))
						return $value;
					else
						return FALSE;

		        case 'number':
					// filter out any non-numeric characters, but pass the rest in.
					$value = remove_illegal_chars($value, expand_chars_exp('0-9'));
					if(strlen($value)>0)
						return $value;
					else
						return FALSE;

		        case 'datemask': // TODO: Provide a date-mask filter
		            return $value;

                case 'language':
                    if(is_exists_language($value))
						return $value;
					else
						return FALSE;

                case 'theme':
                    if(is_exists_theme($value))
						return $value;
					else
						return FALSE;
						
                case 'export':
                    if(strlen($value) == 0 || is_export_plugin($value))
						return $value;
					else
						return FALSE;

		        case 'value_select':
		            if(strlen($found['subtype'])>0)
		                $options_r = explode(',', $found['subtype']);

     				if(!is_array($options_r) || in_array($value, $options_r)!==FALSE)
						return $value;
					else
						return FALSE;

                //case 'readonly':
                //    return $value;
                    
                //case 'text':
		        //case 'password':
          		//case 'textarea':
                //    return addslashes(replace_newlines(trim($value)));

				default:
				    return addslashes(replace_newlines(trim($value)));

			}//switch

			db_free_result($result);
		}
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
function insert_s_config_group_item_var($group_id, $id, $keyid, $value)
{
    if(strlen($group_id)>0 && strlen($id)>0 && strlen($value)>0)
	{
        if(strlen($keyid)==0)
		    $keyid = '0';

		if(is_exists_s_config_group_item($group_id, $id, $keyid))
		{
			$value = validate_s_config_group_item($group_id, $id, $keyid, $value);

	    	$query = "INSERT INTO s_config_group_item_var (group_id, id, keyid, value) "
					."VALUES ('$group_id', '$id', '$keyid', '".$value."')";

			$insert = db_query($query);
			if ($insert && db_affected_rows() > 0)
			{
				//opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($group_id, $id, $keyid, $value));
				return TRUE;
			}
			else
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($group_id, $id, $keyid, $value));
				return FALSE;
			}
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($group_id, $id, $keyid, $value));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

/**
*/
function update_s_config_group_item_var($group_id, $id, $keyid, $value)
{
    if(strlen($group_id)>0 && strlen($id)>0 && strlen($keyid)>0)
	{
        $value = validate_s_config_group_item($group_id, $id, $keyid, $value);

		$query = "UPDATE s_config_group_item_var "
				."SET value = '".$value."'"
				." WHERE group_id = '$group_id' AND "
				."id = '$id' AND "
                ."keyid = '$keyid'";

		$update = db_query($query);

		// We should not treat updates that were not actually updated because value did not change as failures.
		if($update && ($rows_affected = db_affected_rows()) !== -1)
		{
			//if($rows_affected>0)
			//	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($group_id, $id, $keyid, $value));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($group_id, $id, $keyid, $value));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

/**
*/
function delete_s_config_group_item_vars($group_id, $id, $keyid)
{
	if(strlen($group_id)>0)
	{
		$query = "DELETE FROM s_config_group_item_var ".
			"WHERE group_id = '$group_id'";

        if(strlen($id)>0)
		{
			$query .= " AND id = '$id'";
		}

        if(strlen($keyid)>0)
		{
			$query .= " AND keyid = '$keyid'";
		}

		$delete = db_query($query);
		// We should not treat deletes that were not actually updated because value did not change as failures.
		
		if($delete && ($rows_affected = db_affected_rows()) !== -1)
		{
			//if($rows_affected>0)
			//	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($group_id, $id, $keyid));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($group_id, $id, $keyid));
			return FALSE;
		}
	}

	//else
	return FALSE;
}
?>
