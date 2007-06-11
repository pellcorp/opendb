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
function fetch_newitem_status_type_rs($owner_id)
{
	$user_type_r = get_min_user_type_r(fetch_user_type($owner_id));
	if(is_not_empty_array($user_type_r))
		$in_clause = format_sql_in_clause($user_type_r);
		
	$query = "SELECT sst.s_status_type, sst.s_status_type as value, IFNULL(stlv.value, sst.description) as display, sst.img, sst.default_ind as checked_ind ".
			"FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ".
			"WHERE sst.closed_ind <> 'Y' AND ".
			"sst.insert_ind = 'Y' AND ".
			"(LENGTH(IFNULL(sst.min_create_user_type,'')) = 0 OR ".
			"sst.min_create_user_type IN($in_clause) ) ".
			"ORDER BY 1 ASC";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/*
* Update an existing item_instance
*/
function fetch_update_status_type_rs($item_id, $instance_no, $owner_id)
{
	function get_status_type_in_clause($query)
	{
		$result = db_query($query);
		if($result && db_num_rows($result)>0)
		{
			$inclause = "";
			while($status_type_r = db_fetch_assoc($result))
			{
				if(strlen($inclause)>0)
					$inclause .= ",";
				$inclause .= "'".$status_type_r['s_status_type']."'";
			}
	
			if(strlen($inclause)>0)
				return "$inclause";
		}
		return FALSE;
	}

	$user_type_r = get_min_user_type_r(fetch_user_type($owner_id));
	if(is_not_empty_array($user_type_r))
		$in_clause = format_sql_in_clause($user_type_r);

	$status_type_r = fetch_status_type_r(fetch_item_s_status_type($item_id, $instance_no));
	
	// If a borrow record already exists for a record, then can only reset item s_status_type
	// to one where borrows are allowed (borrow_ind=Y), or temporarily disabled (borrow_ind=N),
	// but not completely disallowed. (borrow_ind=X)
	if(is_item_borrowed($item_id, $instance_no))
		$borrow_clause = "sst.borrow_ind IN('Y','N') ";	
	else if(is_exists_item_instance_borrowed_item($item_id, $instance_no))
		$borrow_clause = "sst.borrow_ind IN('Y','N','B') ";	
		
	$query = "SELECT DISTINCT sst.s_status_type as value, IFNULL(stlv.value, sst.description) as display, sst.img, sst.default_ind as checked_ind ".
			"FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ".
			"WHERE ";
	
	$query .= "sst.s_status_type = '".$status_type_r['s_status_type']."' OR ";
	
	$query .= "(sst.closed_ind <> 'Y' AND ".
				"sst.update_ind = 'Y' AND ".
				(strlen($borrow_clause)>0?" $borrow_clause AND ":"").
				"(LENGTH(IFNULL(sst.min_create_user_type,'')) = 0 OR ".
				"sst.min_create_user_type IN($in_clause)) ";
	
	$query .= ") ORDER BY 1 ASC";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/**
* @param $user_restrict
* @param $lookup_mode
* @param $all_checked
*/
function fetch_status_type_rs($user_restrict=FALSE, $lookup_mode=FALSE, $all_checked=FALSE)
{
	if($lookup_mode)
		$query = "SELECT sst.s_status_type as value, IFNULL(stlv.value, sst.description) as display, sst.img ";
	else
		$query = "SELECT sst.s_status_type, IFNULL(stlv.value, sst.description) AS description, sst.img ";
	
	if($all_checked)
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

	if($user_restrict)
	{
		$user_type_r = get_min_user_type_r(get_opendb_session_var('user_type'));
		if(is_not_empty_array($user_type_r))
			$in_clause = format_sql_in_clause($user_type_r);
		if(strlen($in_clause)>0)
		{
			$query .= "WHERE ".
				" ( (LENGTH(IFNULL(sst.min_display_user_type,'')) = 0 OR ".
				" sst.min_display_user_type IN(".$in_clause.")) OR ".
				" (LENGTH(IFNULL(sst.min_create_user_type,'')) = 0 OR ".
				" sst.min_create_user_type IN(".$in_clause.")) ) ";
		}
	}
	
	$query .= " ORDER BY s_status_type ASC";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_whatsnew_status_type_rs()
{
	$query = "SELECT sst.s_status_type, IFNULL(stlv.value, sst.description) AS description, sst.img ".
			"FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ".
			"WHERE sst.closed_ind <> 'Y' ";
	
	$user_type_r = get_min_user_type_r(get_opendb_session_var('user_type'));
	if(is_not_empty_array($user_type_r))
		$in_clause = format_sql_in_clause($user_type_r);
	if(strlen($in_clause)>0)
	{
		$query .= "AND (LENGTH(IFNULL(sst.min_display_user_type,'')) = 0 OR ".
				" sst.min_display_user_type IN($in_clause) ) ";
	}
	
	$query .= " ORDER BY s_status_type ASC";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/*
* @param $s_status_type
*/
function fetch_status_type_r($s_status_type, $full_record=TRUE)
{
	$query = "SELECT sst.s_status_type, IFNULL(stlv.value, sst.description) AS description, sst.img";
	
	if($full_record)
		$query .= ", sst.insert_ind, sst.update_ind, sst.delete_ind, sst.change_owner_ind, sst.min_display_user_type, sst.min_create_user_type, sst.borrow_ind, sst.status_comment_ind, sst.default_ind, sst.closed_ind ";
	
	$query .= " FROM s_status_type sst ".
			"LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_status_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sst.s_status_type ".
			"WHERE sst.s_status_type = '$s_status_type' ".
			"LIMIT 0,1";
	 
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

function fetch_default_status_type_for_owner($owner_id)
{
	$user_type_r = get_min_user_type_r(fetch_user_type($owner_id));
	if(is_not_empty_array($user_type_r))
		$in_clause = format_sql_in_clause($user_type_r);
		
	$query = "SELECT sst.s_status_type ".
			"FROM s_status_type sst ".
			"WHERE sst.closed_ind <> 'Y' AND ".
			"sst.insert_ind = 'Y' AND ".
			"sst.default_ind = 'Y' AND ".
			"(LENGTH(IFNULL(sst.min_create_user_type,'')) = 0 OR ".
			"sst.min_create_user_type IN($in_clause) ) ".
			"ORDER BY 1 ASC LIMIT 0,1";
		
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
		if($new_status_type_r['update_ind'] == 'Y')
		{
			$owner_user_type = fetch_user_type($owner_id);
		
			// Either no existing borrowed_item records, or new s_status_type borrow_ind must allow borrowing (==Y), or
			// only temporarily disable it (==N).
			if( (($new_status_type_r['borrow_ind'] != 'Y' && 
						$new_status_type_r['borrow_ind'] != 'N' && 
						$new_status_type_r['borrow_ind'] != 'B' && 
						is_exists_item_instance_borrowed_item($item_id, $instance_no)) || 
					($new_status_type_r['borrow_ind'] != 'Y' && 
						$new_status_type_r['borrow_ind'] != 'N' && 
						is_item_borrowed($item_id, $instance_no))) )
			{
				$errors = get_opendb_lang_var('operation_not_avail_s_status_type', array('usertype'=>get_usertype_prompt($owner_user_type),'s_status_type_desc'=>$new_status_type_r['description']));
				return FALSE;
			}
			else
			{
				// Owner must have enough permission to create items of this type.
				if(strlen($new_status_type_r['min_create_user_type'])==0 || 
							in_array($new_status_type_r['min_create_user_type'], get_min_user_type_r($owner_user_type)))
				{
					return TRUE;
				}
				else
				{
					$errors = get_opendb_lang_var('s_status_type_create_access_disabled_for_usertype', array('usertype'=>get_usertype_prompt($owner_user_type),'s_status_type_desc'=>$new_status_type_r['description']));
					return FALSE;
				}
			}
		}
		else
		{
			$errors = array('error'=>get_opendb_lang_var('operation_not_avail_s_status_type', 's_status_type_desc', $new_status_type_r['description']),'detail'=>'');
			return FALSE;
		}
	}
	else//if($new_status_type_r['closed_ind'] != 'Y')
	{
		$errors = array('error'=>get_opendb_lang_var('s_status_type_not_supported', 's_status_type_desc', $new_status_type_r['description']),'detail'=>'');
		return FALSE;
	}
}

function is_newinstance_status_type_valid($item_id, $owner_id, $new_status_type_r, &$errors)
{
	if($new_status_type_r['closed_ind'] != 'Y')
	{
		if($new_status_type_r['insert_ind'] == 'Y')
		{
			$owner_user_type = fetch_user_type($owner_id);

			// Owner must have enough permission to create items of this type.
			if(strlen($new_status_type_r['min_create_user_type'])==0 || in_array($new_status_type_r['min_create_user_type'], get_min_user_type_r($owner_user_type)))
			{
				if(get_opendb_config_var('item_input', 'new_instance_owner_only')!==TRUE || is_user_owner_of_item($item_id, NULL, $owner_id))
				{
					return TRUE;
				}
				else//if(get_opendb_config_var('item_input', 'new_instance_owner_only')!==TRUE || is_user_owner_of_item($item_r['item_id'], NULL, get_opendb_session_var('user_id')))
				{
					$errors = array('error'=>get_opendb_lang_var('operation_not_avail_new_instance'),'detail'=>'');
					return FALSE;
				}
			}
			else
			{
				$errors = get_opendb_lang_var('s_status_type_create_access_disabled_for_usertype', array('usertype'=>get_usertype_prompt($owner_user_type),'s_status_type_desc'=>$new_status_type_r['description']));
				return FALSE;
			}
		}//if($new_status_type_r['insert_ind'] == 'Y')
		else
		{
			$errors = array('error'=>get_opendb_lang_var('operation_not_avail_s_status_type', 's_status_type_desc', $new_status_type_r['description']),'detail'=>'');
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
