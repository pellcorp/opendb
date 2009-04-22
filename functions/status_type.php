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

include_once("./functions/borrowed_item.php");
include_once("./functions/user.php");
include_once("./functions/language.php");

/*
* Only sofar as the s_status_type exists!
*/
function is_valid_s_status_type($s_status_type)
{
	$query = "SELECT 'X' FROM s_status_type WHERE s_status_type = '$s_status_type'";
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
*/
function fetch_newitem_status_type_rs()
{
	$query = "SELECT sst.s_status_type, sst.s_status_type as value, IFNULL(stlv.value, sst.description) as display, sst.img, sst.default_ind as checked_ind ".
			"FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ".
			"WHERE sst.closed_ind <> 'Y' ".
			"ORDER BY 1 ASC";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_update_status_type_rs($status_type)
{
	$query = "SELECT DISTINCT sst.s_status_type, sst.s_status_type as value, IFNULL(stlv.value, sst.description) as display, sst.img, sst.default_ind as checked_ind ".
			"FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ".
			"WHERE ";
	
	$query .= "sst.closed_ind <> 'Y' OR sst.s_status_type = '$status_type' ";
	
	$query .= "ORDER BY 1 ASC";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_status_type_rs($lookup_mode=FALSE)
{
	if($lookup_mode)
		$query = "SELECT sst.s_status_type as value, IFNULL(stlv.value, sst.description) as display, sst.img ";
	else
		$query = "SELECT sst.s_status_type, IFNULL(stlv.value, sst.description) AS description, sst.img ";
	
	if($lookup_mode)
		$query .= ", 'Y' as checked_ind ";
	else
        $query .= ", default_ind AS default_ind ";

	$query .= ", closed_ind ";

	$query .= "FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ";

	$query .= " ORDER BY s_status_type ASC";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}


function fetch_status_type_r($s_status_type)
{
	$query = "SELECT sst.s_status_type, IFNULL(stlv.value, sst.description) AS description, sst.img, ".
			"sst.delete_ind, sst.change_owner_ind, sst.borrow_ind, sst.status_comment_ind, sst.hidden_ind, sst.default_ind, sst.closed_ind ";
	
	$query .= " FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ".
			"WHERE sst.s_status_type = '$s_status_type' ".
			"LIMIT 1";
	 
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

function fetch_default_status_type()
{
	$query = "SELECT sst.s_status_type ".
			"FROM s_status_type sst ".
			"WHERE sst.closed_ind <> 'Y' AND ".
			"sst.default_ind = 'Y' ".
			"ORDER BY 1 ASC LIMIT 1";
		
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		if ($found)
		{
			db_free_result($result);
			return $found['s_status_type'];
		}
	}
	else
		return FALSE;
}

/*
* @parma $item_r - The item the update is being performed against.  This allows us to check the
* 					borrow status of the item, as well as the owner_id, which allows us to
* 					validate updates.  If an administrator is performing the
* 					update, the validations should still be performed in context of
* 					the owner of the item.

* @param $old_status_type_r - Originating s_status_type record as returned from fetch_status_type_r
* @param $new_status_type_r - Proposed update s_status_type record as returned from fetch_status_type_r
* 
* This function assumes the basic checks, such as the old and new s_status_type are different,
* This function does not do 'update' specific logic, but merely tests that the change
* change from one s_status_type to another is valid.
* 
* Refer to docs/notes/s_status_type.txt for more information
*/
function is_update_status_type_valid($item_id, $instance_no, $owner_id, $old_status_type_r, $new_status_type_r, &$errors)
{
	// New status cannot be closed.
	if($new_status_type_r['closed_ind'] != 'Y')
	{
		return TRUE;
	}
	else
	{
		$errors = array('error'=>get_opendb_lang_var('s_status_type_not_supported', 's_status_type_desc', $new_status_type_r['description']),'detail'=>'');
		return FALSE;
	}
}

function is_newinstance_status_type_valid($item_id, $owner_id, $new_status_type_r, &$errors)
{
	if($new_status_type_r['closed_ind'] != 'Y')
	{
		if( (get_opendb_config_var('item_input', 'item_instance_support')!==FALSE || !is_exists_item_instance($item_id) ) &&
				( get_opendb_config_var('item_input', 'new_instance_owner_only')!==TRUE || is_user_owner_of_item($item_id, NULL, $owner_id) ) )
		{
			return TRUE;
		}
		else//if(get_opendb_config_var('item_input', 'new_instance_owner_only')!==TRUE || is_user_owner_of_item($item_r['item_id'], NULL, get_opendb_session_var('user_id')))
		{
			$errors = array('error'=>get_opendb_lang_var('operation_not_avail_new_instance'),'detail'=>'');
			return FALSE;
		}
	}
	else//if($new_status_type_r['closed_ind'] != 'Y')
	{
		$errors = array('error'=>get_opendb_lang_var('s_status_type_not_supported', 's_status_type_desc', $new_status_type_r['description']),'detail'=>'');
		return FALSE;
	}
}
?>