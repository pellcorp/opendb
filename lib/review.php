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

include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/datetime.php");
include_once("./lib/utils.php");
include_once("./lib/item.php");
include_once("./lib/item_type_group.php");

function is_item_reviewed($item_id)
{
	$query = "SELECT COUNT('x') AS count ".
			"FROM review r ";
	
	if(get_opendb_config_var('item_review', 'include_other_title_reviews')===TRUE)
	{
		$item_r = fetch_item_r($item_id);
		$query .= ", item i ".
				  "WHERE r.item_id = i.id AND (i.id = $item_id OR i.title = '".addslashes($item_r['title'])."')";
		
		if(get_opendb_config_var('item_review', 'other_title_reviews_restrict_to_item_type_group')!==FALSE)
		{	
			$item_type_group_r = fetch_item_type_groups_for_item_type_r($item_r['s_item_type']);
			if(is_array($item_type_group_r))
			{
				$item_type_r = fetch_item_types_for_group_r($item_type_group_r[0]);
				if(is_array($item_type_r))
				{
					$query .= " AND i.s_item_type IN (".format_sql_in_clause($item_type_r).")";
				}
			}
		}
	}
	else
	{	
		$query .= "WHERE r.item_id = $item_id";
	}
	
	$query .= " LIMIT 0,1";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!==FALSE && $found['count']>0)
			return TRUE;
	}
	
	//else
	return FALSE;
}

//
// This will return all reviews for a $item_id.  This may match multiple $item_id records
// if more than one item of the same title exists, which is quite possible.
//
function fetch_review_rs($item_id)
{
	$query = "SELECT r.sequence_number, i.id AS item_id, i.title, i.s_item_type, r.author_id, r.comment, r.rating, UNIX_TIMESTAMP(r.update_on) AS update_on".
			" FROM review r, item i ".
			" WHERE r.item_id = i.id AND ";
				
	if(get_opendb_config_var('item_review', 'include_other_title_reviews')===TRUE)
	{
		$item_r = fetch_item_r($item_id);
		$query .= "(i.id = $item_id OR i.title = '".addslashes($item_r['title'])."')";
		
		if(get_opendb_config_var('item_review', 'other_title_reviews_restrict_to_item_type_group')!==FALSE)
		{		  
			// first of all we need to get the groups this item belongs to, then we need
			// to get the list of all other s_item_type's that are in those groups.	  
			$item_type_group_r = fetch_item_type_groups_for_item_type_r($item_r['s_item_type']);
			if(is_array($item_type_group_r))
			{
				$item_type_r = fetch_item_types_for_group_r($item_type_group_r[0]); // only use first one.
				if(is_array($item_type_r))
				{
					$query .= " AND i.s_item_type IN (".format_sql_in_clause($item_type_r).")";
				}
			}
		}
	}
	else
	{
		$query .= "i.id = $item_id";
	}
	
	$query .= " ORDER BY r.update_on DESC";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

/*
 * Returns average rating for title.
 */
function fetch_review_rating($item_id = NULL)
{
	if($item_id)
	{
		$query = "SELECT r.rating FROM review r ";
		
		if(get_opendb_config_var('item_review', 'include_other_title_reviews')===TRUE)
		{
			$item_r = fetch_item_r($item_id);
			$query .= ", item i ".
					  "WHERE r.item_id = i.id AND (i.id = $item_id OR i.title = '".addslashes($item_r['title'])."')";
					  
			if(get_opendb_config_var('item_review', 'other_title_reviews_restrict_to_item_type_group')!==FALSE)
			{		  
				// first of all we need to get the groups this item belongs to, then we need
				// to get the list of all other s_item_type's that are in those groups.	  
				$item_type_group_r = fetch_item_type_groups_for_item_type_r($item_r['s_item_type']);
				if(is_array($item_type_group_r))
				{
					$item_type_r = fetch_item_types_for_group_r($item_type_group_r[0]); // only use first one.
					if(is_array($item_type_r))
					{
						$query .= " AND i.s_item_type IN (".format_sql_in_clause($item_type_r).")";
					}
				}
			}
		}
		else
		{	
			$query .= "WHERE r.item_id = $item_id";
		}
	}
	else
	{
		$query = "SELECT rating FROM review";
	}

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		// Initialise.
		$total = 0;
		$number = 0;

		while($review_r = db_fetch_assoc($result))
		{
			$total = $total + $review_r['rating'];
			$number++;
		}
		db_free_result($result);

		if ($number==0)
			return 0;
		else
			return $total / $number;
	}
	
	//else
	return FALSE;
}

function is_exists_review($sequence_number)
{
	$query = "SELECT 'x' FROM review WHERE sequence_number = '$sequence_number'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function is_review_author($sequence_number, $author_id = NULL)
{
	if($author_id == NULL)
		$author_id = get_opendb_session_var('user_id');
		
	$query = "SELECT author_id FROM review ".
			"WHERE sequence_number = $sequence_number";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found && $found['author_id'] == $author_id)
			return TRUE;
	}
	
	//else
	return FALSE;
}

/**
	Returns a count of items stored in the database, or false if none found.
*/
function fetch_review_atdate_cnt($update_on)
{
	$query = "SELECT COUNT(r.item_id) AS count FROM review r WHERE r.update_on >= '$update_on'";
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

/**
	Returns a count of items stored in the database, or false if none found.
	If $s_item_type is specified, only reviews for items of the given s_item_type are counted
*/
function fetch_review_cnt($s_item_type = NULL)
{
	$query = "SELECT COUNT(r.item_id) AS count FROM review r";
	if($s_item_type)
		$query .= ", item i WHERE r.item_id = i.id AND i.s_item_type = '$s_item_type'";

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

function fetch_review_r($sequence_number)
{
	$query = "SELECT r.sequence_number, i.id as item_id, i.title, i.s_item_type, r.author_id, r.comment, r.rating, UNIX_TIMESTAMP(r.update_on) AS update_on".
				" FROM review r, item i".
				" WHERE r.item_id = i.id AND r.sequence_number = $sequence_number";

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

/**
* @param $author_id
*/
function fetch_author_review_cnt($author_id)
{
	$query = "SELECT COUNT('X') AS count FROM review WHERE author_id = '$author_id'";

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

/*
* Checks whether any reviews created by specified user.
* 
* @param $exclude_user_items - If TRUE, we should not count reviews, for items the user owns
*/
function is_user_author($user_id, $exclude_user_items = FALSE)
{
	if($exclude_user_items!==TRUE) {
		$query = "SELECT 'X' FROM review WHERE author_id = '$user_id'";
	} else {
		$query = "SELECT 'X' ".
				"FROM review r, item_instance ii ".
				"WHERE r.item_id = ii.item_id AND ".
				"r.author_id = '$user_id' AND ".
				"ii.owner_id <> '$user_id'";
	}
	
	$result = db_query($query);
	if ($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}
	
	//else
	return FALSE;
}



//
// Insert a review
//
function insert_review($item_id, $author_id, $comment, $rating)
{
	// Ensure no html can be used!
	$comment = addslashes(replace_newlines(trim($comment)));

	$query="INSERT INTO review (item_id,author_id,comment,rating)".
			"VALUES ('$item_id','$author_id','$comment','$rating')";
	$insert = db_query($query);
	if ($insert && db_affected_rows() > 0)
	{
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($item_id, $author_id, $comment, $rating));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($item_id, $author_id, $comment, $rating));
		return FALSE;
	}
}

/*
*/
function update_review($sequence_number, $comment, $rating)
{
	// Ensure no html can be used!
	$comment = addslashes(replace_newlines(trim($comment)));
	
	$query = "UPDATE review ".
			"SET comment = '$comment',".
			" rating = '$rating' ".
			"WHERE sequence_number = $sequence_number";
	
	$update = db_query($query);
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($rows_affected !== -1)
	{
		if($rows_affected>0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($sequence_number, $comment, $rating));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($sequence_number, $comment, $rating));
		return FALSE;
	}
}

/*
* Delete a single review
*/
function delete_review($sequence_number)
{
	$query = "DELETE FROM review WHERE sequence_number = '$sequence_number'";
	$delete = db_query($query);
	
	if ($delete && db_affected_rows() > 0)
	{
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($sequence_number));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($sequence_number));
		return FALSE;
	}
}

/*
* Delete all reviews for an item_id
*/
function delete_reviews($item_id)
{
	$query = "DELETE FROM review WHERE item_id = '$item_id'";
	$delete = db_query($query);
	// doesn't matter if no items deleted, as long as operation was successful.
	if ($delete)
	{
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($item_id));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($item_id));
		return FALSE;
	}
}

function delete_author_reviews($author_id)
{
	$query = "DELETE FROM review WHERE author_id = '$author_id'";
	$delete = db_query($query);
	// doesn't matter if no items deleted, as long as operation was successful.
	if($delete)
	{
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($author_id));
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($author_id));
		return FALSE;
	}
}
?>
