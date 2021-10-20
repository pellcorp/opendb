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
include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item_type.php");
include_once("./lib/theme.php");
include_once("./lib/utils.php");
include_once("./lib/datetime.php");
include_once("./lib/status_type.php");
include_once("./lib/BooleanParser.class.php");
include_once("./lib/widgets.php");
include_once("./lib/parseutils.php");
include_once("./lib/item_type_group.php");

/**
 Will split up a 'item_id_instance_no' value into
 its component item_id / instance_no or return FALSE.

 Format of input parameter:
 {item_id}_{instance_no}
 */
function get_item_id_and_instance_no($item_id_instance_no) {
	if (strlen ( $item_id_instance_no ) > 0) {
		$splitidx = strpos ( $item_id_instance_no, '_' );
		if ($splitidx !== FALSE) {
			$item_id = substr ( $item_id_instance_no, 0, $splitidx );
			$instance_no = substr ( $item_id_instance_no, $splitidx + 1 );
			if (is_numeric ( $item_id ) && is_numeric ( $instance_no ))
				return array (
						'item_id' => $item_id,
						'instance_no' => $instance_no );
		}
	}

	//else
	return FALSE;
}

/**
	Will check that the $uid has an item_instance for item_id.  If instance_no specified,
	will check that the user owns the specified instance, otherwise this function is
	only checking that the $uid owns at least one instance of the item.
*/
function is_user_owner_of_item($item_id, $instance_no, $user_id = NULL) {
	if (strlen ( $user_id ) == 0)
		$user_id = get_opendb_session_var ( 'user_id' );
	
	$query = "SELECT 'x' FROM item_instance WHERE item_id = '$item_id' AND owner_id = '$user_id' ";
	if (is_numeric ( $instance_no ))
		$query .= " AND instance_no = '$instance_no' ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		// The very fact that at least one row was returned indicates that owner
		// has at least one instance of item.
		return TRUE;
	}
	
	//else
	return FALSE;
}

/**
*/
function fetch_item_owner_id($item_id, $instance_no) {
	$query = "SELECT owner_id FROM item_instance WHERE item_id = '$item_id' AND instance_no = '$instance_no'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		if ($found) {
			db_free_result ( $result );
			return $found ['owner_id'];
		}
	}
	//else
	return FALSE;
}

function fetch_item_s_status_type($item_id, $instance_no) {
	$query = "SELECT s_status_type FROM item_instance WHERE item_id = '$item_id' AND instance_no = '$instance_no'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		if ($found) {
			db_free_result ( $result );
			return $found ['s_status_type'];
		}
	}
	//else
	return FALSE;
}

/**
	Returns a count of item instances stored in the database, or false if none found.
	NOTE: If $s_item_type is specified, only the given $s_item_type is counted (otherwise all)
*/
function fetch_item_instance_cnt($s_item_type = NULL) {
	$query = "SELECT count(ii.item_id) as count ";
	$from = "FROM item_instance ii, s_status_type sst, user u, item i ";
	$where = "WHERE i.id = ii.item_id AND u.user_id = ii.owner_id AND u.active_ind = 'Y' AND sst.s_status_type = ii.s_status_type ";
	
	if ($s_item_type) {
		$where .= " AND i.s_item_type = '" . $s_item_type . "' ";
	}
	
	// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$where .= " AND ( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') ";
	}
	
	$query .= "$from $where";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/**
	Returns resultset of item_instance's for the particular item_id
*/
function fetch_item_instance_rs($item_id, $owner_id = NULL) {
	// so that both resultset use item_id for the item.id or item_instance.item_id!!!
	$query = "SELECT ii.item_id, ii.instance_no, ii.owner_id, ii.borrow_duration, ii.s_status_type, ii.status_comment, UNIX_TIMESTAMP(ii.update_on) AS update_on " . " FROM item_instance ii, s_status_type sst, user u, item i" . " WHERE i.id = ii.item_id AND 
					u.user_id = ii.owner_id AND 
					u.active_ind = 'Y' AND 
					sst.s_status_type = ii.s_status_type AND 
					ii.item_id='" . $item_id . "' " . (strlen ( $owner_id ) > 0 ? "AND ii.owner_id = '$owner_id'" : "");
	
	// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$query .= " AND ( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') ";
	}
	
	$query .= " ORDER BY ii.instance_no	";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
	Returns a count of items owned by the specified owner_id, or FALSE if no records.
	Assumes this will NOT be called for INACTIVE users, if being called from Statistics
	
	NOTE: If $s_item_type is specified, only the given s_item_type is counted (otherwise all)
	
*/
function fetch_owner_item_cnt($owner_id, $s_item_type = NULL) {
	$query = "SELECT count(ii.item_id) AS count ";
	$from = "FROM item_instance ii, s_status_type sst, item i ";
	$where = "WHERE i.id = ii.item_id AND sst.s_status_type = ii.s_status_type ";
	
	if ($s_item_type) {
		$where .= "AND i.s_item_type='" . $s_item_type . "' ";
	}
	$where .= "AND ii.owner_id = '$owner_id' ";
	
	// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$where .= " AND ( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') ";
	}
	
	$query .= "$from $where";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/*
*/
function fetch_owner_s_status_type_item_cnt($owner_id, $s_status_type) {
	$query = "SELECT count(ii.item_id) as count " . "FROM item_instance ii, s_status_type sst, item i " . "WHERE i.id = ii.item_id AND sst.s_status_type = ii.s_status_type AND ii.owner_id='" . $owner_id . "' AND ii.s_status_type = '$s_status_type' ";

	// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$query .= " AND ( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') ";
	}

	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/**
	Returns a count of items in the specified $s_item_type category, or FALSE if no records.
	NOTE: If $s_item_type is specified, only the given s_item_type is counted (otherwise all)
*/
function fetch_category_item_cnt($category, $s_item_type = NULL) {
	$query = "SELECT COUNT(DISTINCT ii.item_id) AS count " . "FROM item i, item_instance ii, s_status_type sst, user u, s_attribute_type sat, s_item_attribute_type siat, item_attribute ia " . "WHERE u.user_id = ii.owner_id AND " . "siat.s_item_type = i.s_item_type AND " . "sat.s_attribute_type = siat.s_attribute_type AND " . "sat.s_field_type = 'CATEGORY' AND " . "ia.s_attribute_type = siat.s_attribute_type AND " . "ia.item_id = ii.item_id AND " . "(ia.instance_no = 0 OR ia.instance_no = ii.instance_no) AND " . "ia.order_no = siat.order_no AND " . "u.active_ind = 'Y' AND " . "sst.s_status_type = ii.s_status_type AND " . "i.id = ii.item_id AND " . "ia.lookup_attribute_val = '" . $category . "'";
	
	if ($s_item_type) {
		$query .= " AND i.s_item_type='" . $s_item_type . "' ";
	}
	
	// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$query .= " AND ( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') ";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE) {
			return $found ['count'];
		}
	}
	
	//else
	return FALSE;
}

/*
* Returns a list of items owned by the specified owner.
* item_id,instance_no,title
* 
* Note: Used exclusively in User Admin.  This we can restrict
* set returned, to only those records which are accessible to
* the administrator.
*/
function fetch_owner_item_instance_rs($owner_id) {
	$query = "SELECT ii.item_id, ii.instance_no, ii.s_status_type, ii.status_comment, ii.borrow_duration, ii.owner_id, i.title, i.s_item_type 
			FROM item i, item_instance ii 
			WHERE i.id = ii.item_id AND ii.owner_id='" . $owner_id . "' ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

define ( 'RELATED_CHILDREN_MODE', 'CHILDREN' );
define ( 'RELATED_PARENTS_MODE', 'PARENTS' );

function fetch_item_instance_relationship_rs($item_id, $instance_no = NULL, $related_mode = RELATED_CHILDREN_MODE) {
	$query = "SELECT DISTINCT ii.item_id, 
					ii.instance_no, 
					i.title,
					i.s_item_type,
					ii.s_status_type,
					ii.owner_id,
					ii.status_comment
			FROM	item_instance_relationship iir,
					item_instance ii,
				 	item i,
				 	s_status_type sst
			WHERE 	sst.s_status_type = ii.s_status_type AND 
					ii.item_id = i.id AND ";
	
	// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$query .= " ( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') AND ";
	}
	
	if ($related_mode == RELATED_CHILDREN_MODE) {
		$query .= "ii.item_id = iir.related_item_id AND
					ii.instance_no = iir.related_instance_no ";
		
		$query .= "AND iir.item_id = $item_id ";
		if (is_numeric ( $instance_no )) {
			$query .= "AND iir.instance_no = $instance_no";
		}
	} else {
		$query .= "ii.item_id = iir.item_id AND
					ii.instance_no = iir.instance_no ";
		
		$query .= "AND iir.related_item_id = $item_id ";
		if (is_numeric ( $instance_no )) {
			$query .= "AND iir.related_instance_no = $instance_no";
		}
	}
	
	$query .= " ORDER BY 1, 2 ASC";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_item_instance_relationship_r($item_id, $instance_no = NULL, $related_mode = RELATED_CHILDREN_MODE) {
    $result = fetch_item_instance_relationship_rs($item_id, $instance_no, $related_mode);
    $item_instance = db_fetch_assoc($result);
    db_free_result($result);

    return $item_instance;
}

function fetch_available_item_parents($HTTP_VARS, $item_r, $filter = null, $include_parents = true) {
	$current_parents = array();
	if ($HTTP_VARS['parent_item_id'] ?? 0 > 0) {
		$current_parents[] = $HTTP_VARS['parent_item_id'];
	} else {
		$parent_instance_rs = fetch_item_instance_relationship_rs($item_r['item_id'], $item_r['instance_no'] ?? NULL, RELATED_PARENTS_MODE);

		if ($parent_instance_rs !== false) {
			foreach ($parent_instance_rs as $parent_instance_r) {
				$current_parents[] = $parent_instance_r['item_id'];
			}
			db_free_result($parent_instance_rs);
        }
    }

	$children_rs = fetch_item_instance_relationship_rs($item_r['item_id'], $item_r['instance_no'] ?? NULL);
	$children = array();
	if ($children_rs !== false) {
		while ($child = db_fetch_assoc($children_rs)) {
			$children[] = $child;
		}
		db_free_result($children_rs);
	}
    
	$items = array();

	if (is_null($filter)) {
		// Fetch every item.
		$items_rs = fetch_item_listing_rs(null, null, 'title', 'asc');
	} elseif ($filter == '%parent_only%') {
		// Fetch parent items only.
		foreach ($current_parents as $parent) {
			$item = fetch_item_r($parent);
			$item['current_parent'] = true;
			$items[] = $item;
		}
		return $items;
    } else {
        // Filter items.
        $items_rs = fetch_item_listing_rs(array('title' => $filter, 'title_match' => 'partial'), array(), 'title', 'asc');
    }
    
    while ($item = db_fetch_assoc($items_rs)) {
        if ($item['item_id'] != $item_r['item_id']) {
            $is_child = false;
			foreach ($children as $child) {
                if ($item['item_id'] == $child['item_id']) {
                    $is_child = true;
                }
            }

            if (!$is_child) {
                foreach ($current_parents as $parent) {
                    if ($item['item_id'] == $parent) {
                        $item['current_parent'] = true;
                    }
                }
            }

            if (!isset($item['current_parent']) || ($item['current_parent'] && $include_parents)) {
                $items[] = $item;
            }
        }
    }

    db_free_result($items_rs);

    return $items;
}

/**
 * Does current item have any related items
 *
 * @param unknown_type $item_id
 * @param unknown_type $instance_no
 * @return unknown
 */
function is_exists_item_instance_relationship($item_id, $instance_no) {
	$query = "SELECT 'X'
			FROM item_instance_relationship
			WHERE item_id = $item_id AND
				instance_no = $instance_no";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Check whether item instance is referenced in at least one item instance relationship.  Where parent_item_id and
 * parent_instance_no are specified, relationship checking will not count a relationship featuring this parent - good
 * for when deleting item and we have a parent context.
 *
 * @param unknown_type $item_id
 * @param unknown_type $instance_no
 */
function is_exists_related_item_instance_relationship($item_id, $instance_no, $parent_item_id = NULL, $parent_instance_no = NULL) {
	$query = "SELECT 'X'
			FROM item_instance_relationship
			WHERE related_item_id = $item_id AND
				related_instance_no = $instance_no";
	
	if (is_numeric ( $parent_item_id ) && is_numeric ( $parent_instance_no )) {
		$query .= " AND item_id = $parent_item_id AND instance_no = $parent_instance_no ";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	} else {
		return FALSE;
	}
}

//
// Returns an associative array for a single item.
//id,owner_id,title,s_item_type
//
function fetch_item_instance_r($item_id, $instance_no) {
	$query = "SELECT ii.item_id, ii.instance_no, ii.s_status_type, ii.status_comment, ii.borrow_duration, ii.owner_id, UNIX_TIMESTAMP(ii.update_on) AS update_on, i.title, i.s_item_type 
			FROM item i, item_instance ii 
			WHERE i.id = ii.item_id AND i.id ='" . $item_id . "' AND ii.instance_no = '" . $instance_no . "'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

function fetch_item_r($item_id) {
	$query = "SELECT id as item_id, title, s_item_type FROM item WHERE id = '" . $item_id . "'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found;
	} else
		return FALSE;
}

//
// Returns an associative array for a single item.
//id,owner_id,title,s_item_type
//
function fetch_child_item_r($item_id) {
	return fetch_item_r ( $item_id );
}

/**
	Return the item title.
*/
function fetch_item_title($item_id) {
	// Only load previous record if edit.
	$query = "SELECT title FROM item WHERE id = '" . $item_id . "'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found ['title'];
	} else
		return FALSE;
}

//
// Return the item title.
//
function fetch_item_type($item_id) {
	// Only load previous record if edit.
	$query = "SELECT s_item_type FROM item WHERE id = '" . $item_id . "'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found ['s_item_type'];
	} else
		return FALSE;
}

/**
	Will ascertain whether an item already exists with the same information:
		title, s_item_type
	OR
		title, s_item_type, owner_id		
*/
function is_exists_title($title, $s_item_type, $owner_id = NULL) {
	$query = "SELECT 'x' FROM item i,item_instance ii, s_status_type sst, user u " . "WHERE i.id = ii.item_id AND " . "u.user_id = ii.owner_id AND " . "u.active_ind = 'Y' AND " . "sst.s_status_type = ii.s_status_type AND " . "i.title = '" . addslashes ( $title ) . "' AND " . "i.s_item_type = '" . $s_item_type . "' ";
	
	if (strlen ( $owner_id ) > 0)
		$query .= " AND ii.owner_id = '" . $owner_id . "'";
		
		// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$query .= " AND ( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') ";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
	Assumes that lock_item_instance has been called to ensure that
	between delete of item_instance and delete of item, no further activity
	can occur on the table.
	
	If '$instance_no' specified, will test whether the specific instance 
	exists or not; Other wise will test whether any instances exist.

	This will not cater for 'linked' items which do not have a 
	item_instance record.
*/
function is_exists_item_instance($item_id, $instance_no = NULL) {
	$query = "SELECT 'x' FROM item_instance WHERE item_id = '$item_id' ";
	if ($instance_no)
		$query .= "AND instance_no = '$instance_no'";
	$query .= " LIMIT 0,1";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

function is_exists_item_instance_with_owner($owner_id) {
	$query = "SELECT 'x' FROM item_instance WHERE owner_id = '$owner_id'";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

function is_exists_item_instance_with_owner_and_status($item_id, $s_status_type, $owner_id) {
	$query = "SELECT 'x' FROM item_instance WHERE item_id = '$item_id' AND owner_id = '$owner_id' AND s_status_type = '$s_status_type'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

/**
	Checks if the underlying item record actually exists or not.
*/
function is_exists_item($item_id) {
	if (!isset($item_id))
		return FALSE;
	$query = "SELECT 'x' FROM item WHERE id = '$item_id'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		db_free_result ( $result );
		return TRUE;
	}
	
	//else
	return FALSE;
}

/**
	Assumes that lock_item_instance has been called to 
	ensure that the max_instance_no DOES NOT CHANGE between the select
	and insert.
*/
function fetch_max_instance_no($item_id) {
	$query = "SELECT MAX(instance_no) as instance_no FROM item_instance WHERE item_id = $item_id";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['instance_no'];
	}
	
	//else
	return FALSE;
}

/**
	@param item_where_clause - the result of running the item_where_clause statement.

	@param $column_display_config_rs - Format of this array is:
		array(
			's_attribute_type' => '',
			'order_no' => '',
			'value' => '',
			'lookup' => '',
			'attr_match' => '',
			'search_attribute_ind' => 'Y');

	 Arrays where value is defined, attr_match is one of 'word' or 'partial'
	 and search_attribute_ind = 'y' will be included in the select statement.
*/
function fetch_item_listing_cnt($HTTP_VARS, $column_display_config_rs = NULL) {
	$query = "SELECT COUNT(DISTINCT i.id, ii.instance_no";
	if (is_array ( $column_display_config_rs )) {
		for($i = 0; $i < count ( $column_display_config_rs ); $i ++) {
			// ignore all other configuration columns as they are already included
			if ($column_display_config_rs [$i] ['column_type'] == 's_attribute_type') {
				// if not an order by column, we want to generate the fields individually in the listings page.
				if ($column_display_config_rs [$i] ['orderby_support_ind'] === 'Y') {
					if (strlen ( $column_display_config_rs[$i]['attribute_val'] ?? '') > 0 &&
						($column_display_config_rs[$i]['attr_match'] == 'word' ||
						 $column_display_config_rs[$i]['attr_match'] == 'partial') &&
						$column_display_config_rs[$i]['search_attribute_ind'] == 'y') {
						$query .= ', ifnull(ia' . $i . '.attribute_val,ia' . $i . '.lookup_attribute_val)';
					}
				}
			}
		}
	}
	$query .= ") AS count ";
	
	$query .= from_and_where_clause ( $HTTP_VARS, $column_display_config_rs, 'COUNT' );
	
	//echo "\n<code>Listing Query: $query</code>";
	

	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/**
	Add the SELECT and ORDER BY CLAUSES

	@param $HTTP_VARS
	@param $column_display_config_rs

	@param	$order_by by should be one of "s_item_type, owner_id, title, update_on"
		The $order_by value will control which order column is first in the list, the rest
		will be filled in after with defaults.
	
	@param	$sortorder will be either "asc" or "desc"  if not defined it will default to "asc"
	@param	$index is the LIMIT value to apply.
*/
function fetch_item_listing_rs($HTTP_VARS, $column_display_config_rs, $order_by, $sortorder, $start_index = NULL, $items_per_page = NULL) {
	$query = 'SELECT DISTINCT i.id AS item_id, ii.instance_no, ii.s_status_type, ii.status_comment, ii.owner_id, ii.borrow_duration, i.s_item_type, i.title, UNIX_TIMESTAMP(ii.update_on) AS update_on';
	
	$column_order_by_rs = array ();
	
	if (is_array ( $column_display_config_rs )) {
		for($i = 0; $i < count ( $column_display_config_rs ); $i ++) {
			$fieldname = NULL;
			
			if ($column_display_config_rs [$i] ['column_type'] == 's_attribute_type') {
				$fieldname = get_field_name ( $column_display_config_rs [$i] ['s_attribute_type'], $column_display_config_rs [$i] ['order_no'] );
				
				// if not an order by column, we want to generate the fields individually in the listings page.
				if ($column_display_config_rs [$i] ['orderby_support_ind'] === 'Y' || $column_display_config_rs [$i] ['search_attribute_ind'] === 'y') {
					if ($column_display_config_rs [$i] ['orderby_datatype'] === 'numeric')
						$query .= ', (ifnull(ia' . $i . '.attribute_val, ia' . $i . '.lookup_attribute_val)+0) AS \'' . $fieldname .'\'';
					else
						$query .= ', ifnull(ia' . $i . '.attribute_val, ia' . $i . '.lookup_attribute_val) AS \'' . $fieldname .'\'';
				}
			} else if ($column_display_config_rs [$i] ['column_type'] == 's_field_type') {
				
				// TODO - we need to be able to specify the order by which the default order by's are actioned.  At the
				// moment in the code title, instance_no and item_type are hardcoded to be added.  When we an order field
				// for default order by for a listing config we can get rid of the hard coded order by and enable the TITLE
				// and ITEMTYPE.
				$field_type_fieldnames_r = array (
						'ITEM_ID' => 'i.id',
						//'TITLE'=>'i.title',
						'STATUSTYPE' => 'ii.s_status_type',
						//'ITEMTYPE'=>'i.s_item_type',
						'OWNER' => 'ii.owner_id' );
				
				if ($column_display_config_rs [$i] ['s_field_type'] == 'CATEGORY') {
					$query .= ', catia.lookup_attribute_val AS catia_lookup_attribute_val, catia.s_attribute_type AS catia_s_attribute_type, catia.order_no AS catia_order_no';
					$fieldname = 'catia_lookup_attribute_val';
				} else if ($column_display_config_rs [$i] ['s_field_type'] == 'INTEREST') {
					$query .= ', it.level AS interest_level';
					$fieldname = 'interest_level';
				} else {
					$fieldname = $field_type_fieldnames_r [$column_display_config_rs [$i] ['s_field_type']];
				}
			}
			
			if (strlen ( $fieldname ) > 0) {
				if (strlen ( $order_by ) == 0) {
					if ($column_display_config_rs [$i] ['orderby_default_ind'] === 'Y') {
						$column_order_by_rs [] = array (
								'orderby' => $fieldname,
								'sortorder' => strtoupper ( ifempty ( $column_display_config_rs [$i] ['orderby_sort_order'], 'ASC' ) ) );
					}
				} else if (strcasecmp ( $order_by, $fieldname ) === 0) {
					$column_order_by_rs [] = array (
							'orderby' => $fieldname,
							'sortorder' => strtoupper ( ifempty ( $sortorder, ifempty ( $column_display_config_rs [$i] ['orderby_sort_order'], 'ASC' ) ) ) );
				}
			}
		}
	}
	
	$query .= " " . from_and_where_clause ( $HTTP_VARS, $column_display_config_rs, 'LISTING' );
	
	if (count ( $column_order_by_rs ) > 0) {
		$orderbyquery = '';

		foreach ( $column_order_by_rs as $column_order_by_r ) {
			if (strlen ( $orderbyquery ) > 0) {
				$orderbyquery .= ', ';
			}
			$orderbyquery .= $column_order_by_r ['orderby'] . ' ' . $column_order_by_r ['sortorder'];
		}
		
		$query .= ' ORDER BY ' . $orderbyquery . ', i.title, ii.instance_no ASC, i.s_item_type';
	} else if ($order_by == 's_item_type') {
		$query .= ' ORDER BY i.s_item_type ' . $sortorder . ', i.title, ii.instance_no ASC';
	} else if ($order_by == 'category') {
		$query .= ' ORDER BY catia_lookup_attribute_val ' . $sortorder . ', i.title, ii.instance_no ASC, i.s_item_type';
	} else if ($order_by == 'owner_id') {
		$query .= ' ORDER BY ii.owner_id ' . $sortorder . ', i.title, ii.instance_no ASC, i.s_item_type';
	} else if ($order_by == 's_status_type') {
		$query .= ' ORDER BY ii.s_status_type ' . $sortorder . ', i.title, ii.instance_no ASC, i.s_item_type';
	} else if ($order_by == 'update_on') {
		$query .= ' ORDER BY ii.update_on ' . $sortorder . ', i.title, ii.instance_no ASC, i.s_item_type';
	} else if ($order_by === 'item_id') {
		$query .= ' ORDER BY i.id ' . $sortorder . ', ii.instance_no ASC, i.s_item_type';
	} else if ($order_by === 'interest') {
		$query .= ' ORDER BY interest_level ' . $sortorder . ', i.title, ii.instance_no ASC, i.s_item_type';
	} else { //if($order_by === 'title')
		$query .= ' ORDER BY i.title ' . $sortorder . ', ii.instance_no ASC, i.s_item_type';
	}
	
	if (is_numeric ( $start_index ) && is_numeric ( $items_per_page )) {
		$query .= ' LIMIT ' . $start_index . ', ' . $items_per_page;
	}
	
	//echo "\n<code>Listing Query: $query</code>";
	

	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

/**
* 	NOTE: PRIVATE FUNCTION.

	Will return the FROM and WHERE clauses for a selection from the item table.
	
	If $owner_id defined, will limit to only items owned by owner_id
	If $s_item_type defined, will limit to only items of that type.
	If $category defined, will limit to only items of that category.
	If $letter defined will limit to item.title starting with that letter.
	If $interest_level defined will limit to items with that interest level or higher.
	
	@param $HTTP_VARS['...'] variables supported: 
		owner_id, s_item_type, s_item_type[], s_item_type_group, title, title_match, category,
		rating, attribute_type, lookup_attribute_val, attribute_val, attr_match, 
		update_on, datetimemask, update_on_days, letter, start_item_id
		s_status_type[], status_comment, not_s_status_type[], interest_level
*/
function from_and_where_clause($HTTP_VARS, $column_display_config_rs = NULL, $query_type = 'LISTING') {
	// For checking whether count (DISTINCT ...) is supported, and thus 
	// whether we have to do any special processing!
	$from_r [] = 'item i';
	$from_r [] = 'item_instance ii';
	
	$where_r [] = 'ii.item_id = i.id'; // only parent items should ever be listed.
	$query = "";
	

	//
	// Owner restriction
	//
	if (strlen($HTTP_VARS ['owner_id'] ?? '') > 0)
		$where_r [] = 'ii.owner_id = \'' . $HTTP_VARS ['owner_id'] . '\'';
	else if (strlen( $HTTP_VARS ['not_owner_id'] ?? '' ) > 0) //For not showing current user items.
		$where_r [] = 'ii.owner_id <> \'' . $HTTP_VARS ['not_owner_id'] . '\'';

	//
	// Item Type / Item Type group restriction
	//
	if (isset($HTTP_VARS['s_item_type']) &&
		!is_array( $HTTP_VARS['s_item_type'] ) &&
		strlen( $HTTP_VARS['s_item_type'] ) > 0)
	{
		$where_r [] = 'i.s_item_type = \'' . $HTTP_VARS ['s_item_type'] . '\'';
	} else if (strlen( $HTTP_VARS ['s_item_type_group'] ?? '') > 0) {
		$from_r [] = 's_item_type_group_rltshp sitgr';
		$where_r [] = 'sitgr.s_item_type = i.s_item_type';
		$where_r [] = 'sitgr.s_item_type_group = \'' . $HTTP_VARS ['s_item_type_group'] . '\'';
	} else if (is_not_empty_array( $HTTP_VARS['s_item_type'] ?? '' )) {
		$where_r [] = 'i.s_item_type IN(' . format_sql_in_clause ( $HTTP_VARS ['s_item_type'] ) . ')';
	}

	$from_r [] = 's_status_type sst';
	$where_r [] = 'sst.s_status_type = ii.s_status_type';

	//
	// Status Type restriction
	//
	if (is_not_empty_array( $HTTP_VARS['s_status_type'] ?? '' )) {
		$where_r [] = 'sst.s_status_type IN(' . format_sql_in_clause ( $HTTP_VARS ['s_status_type'] ) . ')';
	} else if (isset($HTTP_VARS ['s_status_type']) && $HTTP_VARS ['s_status_type'] != 'ALL' ) {
		$where_r [] = 'sst.s_status_type = \'' . $HTTP_VARS ['s_status_type'] . '\'';
	}

	// no need for such a restriction if current user is item admin
	if (! is_user_granted_permission( PERM_ITEM_ADMIN )) {
		$where_r [] = "( sst.hidden_ind = 'N' OR ii.owner_id = '" . get_opendb_session_var ( 'user_id' ) . "') ";
	}

	//
	// User and Status type restriction
	//
	if (strcmp( ($HTTP_VARS['owner_id'] ?? ''), get_opendb_session_var( 'user_id' ) ) !== 0) {	// not current user
		$from_r [] = 'user u';
		$where_r [] = 'u.user_id = ii.owner_id';
		$where_r [] = 'u.active_ind = \'Y\'';
	}

	//
	// Status Comment restriction
	//
	if (strlen( $HTTP_VARS['status_comment'] ?? '') > 0) {
		// Escape only the single quote!
		$HTTP_VARS['status_comment'] = str_replace( "'", "\\'", $HTTP_VARS['status_comment'] );

		if ($HTTP_VARS['status_comment_match'] != 'exact') {
			$parser = new BooleanParser ();
			$statements = $parser->parseBooleanStatement ( $HTTP_VARS ['status_comment'] );
			if (is_array ( $statements )) {
				$where_r [] = build_boolean_clause ( $statements, 'ii.status_comment',
													 $HTTP_VARS['status_comment_match'], 'AND',
													 $HTTP_VARS['status_comment_case'] );
			}
		} else {
			if (is_null( $HTTP_VARS['status_comment_case'] )) {
				$where_r[] = 'ii.status_comment = \'' . $HTTP_VARS['status_comment'] . '\'';
			} else {
				$where_r[] = 'BINARY ii.status_comment = \'' . $HTTP_VARS['status_comment'] . '\'';
			}
		}
	}

	//
	// Title restriction
	//
	if (strlen( $HTTP_VARS ['title'] ?? '') > 0) {
		// Escape only the single quote!
		$HTTP_VARS ['title'] = str_replace ( "'", "\\'", $HTTP_VARS ['title'] );

		if ($HTTP_VARS ['title_match'] != 'exact') {
			$parser = new BooleanParser ();
			$statements = $parser->parseBooleanStatement ( $HTTP_VARS ['title'] );
			if (is_array ( $statements )) {
				$where_r [] = build_boolean_clause ( $statements, 'i.title', $HTTP_VARS ['title_match'], 'AND', $HTTP_VARS ['title_case'] ?? NULL);
			}
		} else {
			if (is_null ( $HTTP_VARS ['title_case'] )) {
				$where_r [] = 'i.title = \'' . $HTTP_VARS ['title'] . '\'';
			} else {
				$where_r [] = 'BINARY i.title = \'' . $HTTP_VARS ['title'] . '\'';
			}
		}
	}
	if (strlen ( $HTTP_VARS ['letter'] ?? '' ) > 0) {
		// Numeric match.
		if ($HTTP_VARS ['letter'] == '#')
			$where_r [] = 'ASCII(LEFT(title,1)) BETWEEN ASCII(\'0\') AND ASCII(\'9\')';
		else
			$where_r [] = 'UPPER(LEFT(i.title,1)) = \'' . strtoupper ( $HTTP_VARS ['letter'] ) . '\'';
	}

	//
	// Last Updated support
	//
	if (strlen( $HTTP_VARS ['update_on'] ?? "" ) > 0) {
		if (strlen( $HTTP_VARS ['datetimemask'] ?? "" ) > 0) {
			$timestamp = get_timestamp_for_datetime ( $HTTP_VARS ['update_on'], $HTTP_VARS ['datetimemask'] );
			if ($timestamp !== FALSE) {
				$where_r [] = 'ii.update_on >= FROM_UNIXTIME(' . $timestamp . ')';
			} else {
				// by default get items from 1 day ago, if update_on can not be parsed correctly.
				$where_r [] = 'TO_DAYS(ii.update_on) >= (TO_DAYS(now())-1)';
			}
		} else {
			$where_r [] = 'ii.update_on >= \'' . $HTTP_VARS ['update_on'] . '\'';
		}
	} else if (is_numeric ( $HTTP_VARS ['update_on_days'] ?? "" )) {	// Give us all records updated in the last however many days.
		$where_r [] = 'TO_DAYS(ii.update_on) >= (TO_DAYS(now())-' . $HTTP_VARS ['update_on_days'] . ')';
	}
	
	//
	// Item Attribute listing/restriction
	//
	if (is_array ( $column_display_config_rs )) {
		for($i = 0; $i < count ( $column_display_config_rs ); $i ++) {
			if ($column_display_config_rs [$i] ['column_type'] == 's_attribute_type') {
				if ($column_display_config_rs [$i] ['search_attribute_ind'] != 'y') {
					// either LISTING or COUNT
					if ($query_type != 'COUNT') {
						$left_join = 'LEFT JOIN item_attribute ia' . $i . ' ON ' . 'ia' . $i . '.item_id = i.id AND (ia' . $i . '.instance_no = 0 OR ia' . $i . '.instance_no = ii.instance_no) AND ia' . $i . '.s_attribute_type = \'' . $column_display_config_rs [$i] ['s_attribute_type'] . '\' AND ia' . $i . '.attribute_no = 1';
						
						// So we can work out which search attribute types to display
						if (is_numeric ( $column_display_config_rs [$i] ['order_no'] )) {
							$left_join .= ' AND ia' . $i . '.order_no = ' . $column_display_config_rs [$i] ['order_no'];
						}
						$left_join_from_r [] = $left_join;
					}
				} else {// search attribute
					$from_r [] = 'item_attribute ia' . $i;
					
					// now do the where clause.
					$where_r [] = 'ia' . $i . '.item_id = i.id AND (ia' . $i . '.instance_no = 0 OR ia' . $i . '.instance_no = ii.instance_no) AND ia' . $i . '.s_attribute_type = \'' . $column_display_config_rs [$i] ['s_attribute_type'] . '\''; // AND ia'.$i.'.attribute_no = 1';
					

					if (strlen ( $column_display_config_rs [$i] ['attribute_val'] ) > 0 && $column_display_config_rs [$i] ['attribute_val'] != '%' && $column_display_config_rs [$i] ['attr_match'] != 'exact') {
						$parser = new BooleanParser ();
						$statements = $parser->parseBooleanStatement ( strtoupper ( str_replace ( "'", "\\'", $column_display_config_rs [$i] ['attribute_val'] ) ) );
						if (is_array ( $statements )) {
							if ($column_display_config_rs [$i] ['lookup_attribute_ind'] == 'Y') {
								$where_r [] = build_boolean_clause ( $statements, 'ia' . $i . '.lookup_attribute_val', 'plain', 'AND', $HTTP_VARS ['attr_case'] );
							} else {
								$where_r [] = build_boolean_clause ( $statements, 'ia' . $i . '.attribute_val', $column_display_config_rs [$i] ['attr_match'], 'AND', $HTTP_VARS ['attr_case'] );
							}
						}
					} else if (strlen ( $column_display_config_rs [$i] ['lookup_attribute_val'] ) > 0 && $column_display_config_rs [$i] ['lookup_attribute_val'] != '%' && $column_display_config_rs [$i] ['lookup_attribute_ind'] == 'Y') {
						$value = str_replace ( "'", "\\'", $column_display_config_rs [$i] ['lookup_attribute_val'] );
						$where_r [] = 'ia' . $i . '.lookup_attribute_val = \'' . str_replace ( '\_', '_', $value ) . '\'';
					} else if (strlen ( $column_display_config_rs [$i] ['attribute_val'] ) > 0 && $column_display_config_rs [$i] ['attribute_val'] != '%') {
						if (starts_with ( $column_display_config_rs [$i] ['attribute_val'], '"' ) && ends_with ( $column_display_config_rs [$i] ['attribute_val'], '"' )) {
							$column_display_config_rs [$i] ['attribute_val'] = substr ( $column_display_config_rs [$i] ['attribute_val'], 1, - 1 );
						}
						
						$value = strtoupper ( str_replace ( "'", "\\'", $column_display_config_rs [$i] ['attribute_val'] ) );
						
						$where_r [] = 'UPPER(ia' . $i . '.attribute_val) = \'' . str_replace ( '\_', '_', $value ) . '\'';
					}
					
					if (strlen ( $HTTP_VARS ['attr_update_on'] ) > 0) {
						if (strlen ( $HTTP_VARS ['datetimemask'] ) > 0) {
							$timestamp = get_timestamp_for_datetime ( $HTTP_VARS ['attr_update_on'], $HTTP_VARS ['datetimemask'] );
							if ($timestamp !== FALSE) {
								$where_r [] = 'ia' . $i . '.update_on >= FROM_UNIXTIME(' . $timestamp . ')';
							} else {
								// by default get items from 1 day ago, if update_on can not be parsed correctly.
								$where_r [] = 'TO_DAYS(ia' . $i . '.update_on) >= (TO_DAYS(now())-1)';
							}
						} else {
							$where_r [] = 'ia' . $i . '.update_on >= \'' . $HTTP_VARS ['attr_update_on'] . '\'';
						}
					} else if (is_numeric ( $HTTP_VARS ['attr_update_on_days'] )) {	// GIve us all records updated in the last however many days.
						$where_r [] = 'TO_DAYS(ia' . $i . '.update_on) >= (TO_DAYS(now())-' . $HTTP_VARS ['attr_update_on_days'] . ')';
					}
				}
			} else if ($column_display_config_rs [$i] ['column_type'] == 's_field_type') {
				if ($column_display_config_rs [$i] ['s_field_type'] == 'CATEGORY') {
					$from_r [] = 's_item_attribute_type catsiat';
					$from_r [] = 's_attribute_type catsat';
					$where_r [] = 'catsiat.s_item_type = i.s_item_type AND catsat.s_attribute_type = catsiat.s_attribute_type AND catsat.s_field_type = \'CATEGORY\'';
					
					$left_join_clause = 'LEFT JOIN item_attribute catia ON ' . 'catia.item_id = i.id AND (catia.instance_no = 0 OR catia.instance_no = ii.instance_no) AND catia.s_attribute_type = catsiat.s_attribute_type AND catia.order_no = catsiat.order_no';
					
					if (strlen ( $HTTP_VARS ['category'] ) > 0 || (strcasecmp ( $HTTP_VARS ['attr_match'], 'category' ) === 0 && strlen ( $HTTP_VARS ['attribute_val'] ) > 0)) {// Support specifying $attribute_val for $category where $attr_match=="category"!
						// If item_type && item_type_group are not set!
						if (strlen ( $HTTP_VARS ['attribute_type'] ) > 0 && ! is_array ( $HTTP_VARS ['s_item_type'] ) && strlen ( $HTTP_VARS ['s_item_type'] ) == 0 && strlen ( $HTTP_VARS ['s_item_type_group'] ) == 0) {
							$where_r [] = 'catsat.s_attribute_type = \'' . $HTTP_VARS ['attribute_type'] . '\'';
						}
						
						// Escape single quotes only.
						$value = strtoupper ( str_replace ( "'", "\\'", ifempty ( $HTTP_VARS ['category'], $HTTP_VARS ['attribute_val'] ) ) );
						$where_r [] = 'UPPER(catia.lookup_attribute_val) = \'' . str_replace ( '\_', '_', $value ) . '\'';
					} else {
						$left_join_clause .= ' AND catia.attribute_no = 1';
					}
					
					$left_join_from_r [] = $left_join_clause;
				} else if ($column_display_config_rs [$i] ['s_field_type'] == 'INTEREST') {
					// can only restrict interest level if its displayed as a column
					if (strlen ( $HTTP_VARS ['interest_level'] ) > 0) {
						$where_r [] = "it.item_id = ii.item_id AND it.instance_no = ii.instance_no AND it.user_id = '" . get_opendb_session_var ( 'user_id' ) . "'" . " AND it.level >= " . $HTTP_VARS ['interest_level'];
						
						$from_r [] = "user_item_interest it";
					} else {
						$left_join_from_r [] = "LEFT JOIN user_item_interest it ON it.item_id = i.id AND it.instance_no = ii.instance_no AND it.user_id = '" . get_opendb_session_var ( 'user_id' ) . "'";
					}
				}
			}
		}
	}
	
	// If attribute_val specified without a attribute_type, then do a loose join to item_attribute table,
	// only on attribute_val column.
	if ( strlen ( $HTTP_VARS ['attribute_type'] ?? "" ) == 0 &&
		 ( strlen ( $HTTP_VARS ['attribute_val'] ?? 0 ) > 0 ||
		   strlen ( $HTTP_VARS ['attr_update_on'] ?? 0 ) > 0 ||
		   strlen ( $HTTP_VARS ['attr_update_on_days'] ?? 0 ) > 0)) {
		$from_r [] = 'item_attribute ia';
		
		// now do the where clause.
		$where_r [] = 'ia.item_id = i.id '; //AND ia.attribute_no = 1';
		

		if ($HTTP_VARS['attr_match'] ?? '' != 'exact') {
			$parser = new BooleanParser ();
			$statements = $parser->parseBooleanStatement ( strtoupper ( str_replace ( "'", "\\'", $HTTP_VARS ['attribute_val'] ?? '' ) ) );
			if (is_array ( $statements )) {
				if (is_lookup_attribute_type ( $HTTP_VARS ['attribute_type'] )) {
					$where_r [] = build_boolean_clause ( $statements, 'ia.lookup_attribute_val', 'plain', 'AND', $HTTP_VARS ['attr_case'] );
				} else {
					$where_r [] = build_boolean_clause ( $statements, 'ia.attribute_val', $HTTP_VARS ['attr_match'], 'AND', $HTTP_VARS ['attr_case'] );
				}
			}
		} else {		// attr_match = 'exact'
			if (is_lookup_attribute_type ( $HTTP_VARS ['attribute_type'] )) {
				$value = str_replace ( "'", "\\'", $HTTP_VARS ['attribute_val'] );
				
				$where_r [] = 'ia.lookup_attribute_val = \'' . str_replace ( '\_', '_', $value ) . '\'';
			} else {
				$value = str_replace ( "'", "\\'", $HTTP_VARS ['attribute_val'] );
				
				if (is_null ( $HTTP_VARS ['attr_case'] )) {
					$where_r [] = '( ia.attribute_val = \'' . str_replace ( '\_', '_', $value ) . '\' OR ' . 'ia.attribute_val LIKE \'% ' . $value . ' %\' OR ' . 'ia.attribute_val LIKE \'' . $value . ' %\' OR ' . 'ia.attribute_val LIKE \'% ' . $value . '\')';
				} else {
					$where_r [] = '( BINARY ia.attribute_val = \'' . str_replace ( '\_', '_', $value ) . '\' OR ' . 'ia.attribute_val LIKE BINARY \'% ' . $value . ' %\' OR ' . 'ia.attribute_val LIKE BINARY \'' . $value . ' %\' OR ' . 'ia.attribute_val LIKE BINARY \'% ' . $value . '\')';
				}
			}
		}
		
		if (strlen ( $HTTP_VARS ['attr_update_on'] ?? '' ) > 0) {
			if (strlen ( $HTTP_VARS ['datetimemask'] ) > 0) {
				$timestamp = get_timestamp_for_datetime ( $HTTP_VARS ['attr_update_on'], $HTTP_VARS ['datetimemask'] );
				if ($timestamp !== FALSE) {
					$where_r [] = 'ia.update_on >= FROM_UNIXTIME(' . $timestamp . ')';
				} else {
					// by default get items from 1 day ago, if update_on can not be parsed correctly.
					$where_r [] = 'TO_DAYS(ia.update_on) >= (TO_DAYS(now())-1)';
				}
			} else {
				$where_r [] = 'ia.update_on >= \'' . $HTTP_VARS ['attr_update_on'] . '\'';
			}
		} else if (is_numeric ( $HTTP_VARS ['attr_update_on_days'] ?? '' )) {		// Give us all records updated in the last however many days.
			$where_r [] = 'TO_DAYS(ia.update_on) >= (TO_DAYS(now())-' . $HTTP_VARS ['attr_update_on_days'] . ')';
		}
	}
	
	//
	// Review restrictions
	//
	if (strlen ( $HTTP_VARS ['rating'] ?? "" ) > 0) {
		$where_r [] = 'r.item_id = i.id AND r.rating >= ' . $HTTP_VARS ['rating'];
		$from_r [] = 'review r';
	}
	
	//
	// Item ID range restriction (Used by Import script)
	//
	if (strlen ( $HTTP_VARS ['item_id_range'] ?? "" ) > 0) {
		$where_r [] = 'i.id IN (' . expand_number_range ( $HTTP_VARS ['item_id_range'] ) . ')';
	}
	
	//
	// Now build the SQL query
	//	
	if (is_array ( $from_r )) {
		$from_clause = '';
		for($i = 0; $i < count ( $from_r ); $i ++) {
			if (strlen ( $from_clause ) > 0)
				$from_clause .= ', ';
			$from_clause .= $from_r [$i];
		}
		$query .= 'FROM (' . $from_clause . ') ';
	}
	
	if (isset ($left_join_from_r) && is_array ( $left_join_from_r )) {
		$left_join_from_clause = '';
		for($i = 0; $i < count ( $left_join_from_r ); $i ++) {
			if (strlen ( $left_join_from_clause ) > 0)
				$left_join_from_clause .= ' ';
			$left_join_from_clause .= $left_join_from_r [$i];
		}
		
		$query .= $left_join_from_clause . ' ';
	}
	
	if (is_array ( $where_r )) {
		$where_clause = '';
		for($i = 0; $i < count ( $where_r ); $i ++) {
			if (strlen ( $where_clause ) > 0)
				$where_clause .= ' AND ';
			$where_clause .= $where_r [$i];
		}
		$query .= 'WHERE ' . $where_clause;
	}
	
	return $query;
}

//
// If successful will return the new ID for the item, otherwise will return FALSE.
//
function insert_item($s_item_type, $title) {
	if (strlen ( $title ) > 0) {
		$title = addslashes ( replace_newlines ( trim ( strip_tags ( $title ) ) ) );
		
		$query = "INSERT INTO item (s_item_type, title)" . " VALUES ('$s_item_type', '$title')";
		
		$insert = db_query ( $query );
		if ($insert && db_affected_rows () > 0) {
			$new_item_id = db_insert_id ();
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$s_item_type,
					$title ) );
			return $new_item_id;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$s_item_type,
					$title ) );
			return FALSE;
		}
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$s_item_type,
				$title ) );
		return FALSE;
	}
}

function update_item($item_id, $title) {
	if (strlen ( $title ) > 0) {
		$query = "UPDATE item " . "SET title = '" . addslashes ( replace_newlines ( trim ( strip_tags ( $title ) ) ) ) . "'" . "WHERE id = '$item_id'";
		
		$update = db_query ( $query );
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows ();
		if ($update && $rows_affected !== - 1) {
			if ($rows_affected > 0)
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
						$item_id,
						$title ) );
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$title ) );
			return FALSE;
		}
	}
}

/**
	Delete item and return boolean indicating success or failure.
	
	This function does not check for any dependencies.
*/
function delete_item($item_id) {
	$query = "DELETE FROM item WHERE id='" . $item_id . "'";
	$delete = db_query ( $query );
	if ($delete && db_affected_rows () > 0) {
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
				$item_id ) );
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id ) );
		return FALSE;
	}
}

/*
* Delete item_attributes, item.
* 
* Assumes instances have been deleted.
*/
function delete_item_cascaded($item_id) {
	if (db_query ( "LOCK TABLES item WRITE, item_attribute WRITE, item_instance WRITE" )) {
		if (delete_item_attributes ( $item_id, NULL, NULL, NULL )) {
			if (delete_item ( $item_id )) {
				// Can't forget to unlock table.
				db_query ( "UNLOCK TABLES" );
				return TRUE;
			} else {
				// Can't forget to unlock table.
				db_query ( "UNLOCK TABLES" );
				
				return FALSE;
			}
		} else {
			// Can't forget to unlock table.
			db_query ( "UNLOCK TABLES" );
			
			return FALSE;
		}
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id ) );
		return FALSE;
	}
}

/**
* Used by all insert/update item_instance functions to make sure the data is valid.  Will
* also update $status_comment / $borrow_duration and set them to a legal value for the
* specified $s_status_type
*/
function validate_item_instance_fields($s_status_type, &$status_comment, &$borrow_duration) {
	// At this point, a specific $s_status_type MUST be supplied.
	if (strlen ( $s_status_type ) > 0) {
		$status_type_r = fetch_status_type_r ( $s_status_type );
	}
	
	if (is_not_empty_array ( $status_type_r )) {
		// A $borrow_duration explicitly set to FALSE, is
		// an indication that nothing should be done with it.
		if ($borrow_duration !== FALSE && $borrow_duration !== NULL) {		//if already null, no need to check again.
			// Ensure we have a valid $borrow_duration
			if (is_numeric ( $borrow_duration )) {			//column cannot handle more than 999
				if ($borrow_duration > 999)
					$borrow_duration = '999';
			} else {
				$borrow_duration = NULL;
			}
		}
		
		$status_comment = addslashes ( substr ( replace_newlines ( trim ( strip_tags ( $status_comment ) ) ), 0, 255 ) );
		
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid Status Type', array (
				$s_status_type ) );
		
		return FALSE;
	}
}

/**
	For subsequent item_instance inserts.

	Does not support $s_status_type = 'W' specific functionality.

*/
function insert_item_instance($item_id, $instance_no, $s_status_type, $status_comment, $borrow_duration, $owner_id) {
	if (validate_item_instance_fields ( $s_status_type, $status_comment, $borrow_duration )) {
		$item_instance_locked = FALSE;
		
		// No need to lock if new item, as no other item_instances will
		// be assigned to it yet!
		if (! is_numeric ( $instance_no )) {
			if (db_query ( "LOCK TABLES item_instance WRITE" )) {
				$item_instance_locked = TRUE;
				
				// If not specified, work out the next one along.
				$instance_no = fetch_max_instance_no ( $item_id );
				if (is_numeric ( $instance_no )) {
					// Add 1 to $instance_no
					$instance_no = ( int ) $instance_no;
					$instance_no ++;
				} else 				// new item
{
					$instance_no = 1;
				}
			} else {
				opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
						$item_id,
						$instance_no,
						$s_status_type,
						$status_comment,
						$borrow_duration,
						$owner_id ) );
				return FALSE;
			}
		}
		
		//Either the instance_no was specified to begin with, or the LOCK TABLES and fetch_max_instance_no call worked.
		$query = "INSERT INTO item_instance(item_id, instance_no, owner_id, borrow_duration, s_status_type, status_comment)" . "VALUES ('$item_id','$instance_no','$owner_id'," . (is_numeric ( $borrow_duration ) ? "'$borrow_duration'" : "NULL") . ",UPPER('" . $s_status_type . "'),'$status_comment')";
		
		$insert = db_query ( $query );
		if ($insert && db_affected_rows () > 0) {
			// Can't forget to unlock table.
			if ($item_instance_locked) {
				db_query ( "UNLOCK TABLES" );
			}
			
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$item_id,
					$instance_no,
					$s_status_type,
					$status_comment,
					$borrow_duration,
					$owner_id ) );
			return $instance_no;
		} else {
			// Can't forget to unlock table.
			if ($item_instance_locked) {
				db_query ( "UNLOCK TABLES" );
			}
			
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$instance_no,
					$s_status_type,
					$status_comment,
					$borrow_duration,
					$owner_id ) );
			return FALSE;
		}
	} else {	//if(validate_item_instance_fields($s_status_type, $borrow_duration))
		return FALSE;
	}
}

/**
	@param $borrow_duration If set to FALSE, no update will occur.  If not numeric, will be
	set to NULL otherwise.
	
	@param $status_comment If set to FALSE, no update will occur.  If not numeric, will be
	set to NULL otherwise.
*/
function update_item_instance($item_id, $instance_no, $s_status_type, $status_comment, $borrow_duration) {
	if (validate_item_instance_fields ( $s_status_type, $status_comment, $borrow_duration )) {
		$query = "UPDATE item_instance SET ";
		
		// If $borrow_duration explicitly set to FALSE, then no update should occur!
		if ($borrow_duration !== FALSE) {
			$query .= "borrow_duration = " . (is_numeric ( $borrow_duration ) ? "'$borrow_duration'" : "NULL") . ", ";
		}
		
		if ($status_comment !== FALSE)
			$query .= "status_comment = '$status_comment', ";
		
		$query .= "s_status_type = UPPER('" . $s_status_type . "') " . "WHERE item_id = '$item_id' AND instance_no = '$instance_no'";
		
		$update = db_query ( $query );
		
		// We should not treat updates that were not actually updated because value did not change as failures.
		$rows_affected = db_affected_rows ();
		if ($update && $rows_affected !== - 1) {
			if ($rows_affected > 0)
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
						$item_id,
						$instance_no,
						$s_status_type,
						$status_comment,
						$borrow_duration ) );
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$instance_no,
					$s_status_type,
					$status_comment,
					$borrow_duration ) );
			return FALSE;
		}
	} else {	//if(validate_item_instance_fields($s_status_type, $borrow_duration))
		return FALSE;
	}
}

/**
	@param $borrow_duration If set to FALSE, no update will occur.  If not numeric, will be
	set to NULL otherwise.
	
	@param $status_comment If set to FALSE, no update will occur.  If not numeric, will be
	set to NULL otherwise.
*/
function update_item_instance_owner($item_id, $instance_no, $old_owner_id, $owner_id) {
	$query = "UPDATE item_instance SET owner_id = '$owner_id' " . "WHERE item_id = '$item_id' AND instance_no = '$instance_no'";
	
	$update = db_query ( $query );
	
	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows ();
	if ($update && $rows_affected !== - 1) {
		if ($rows_affected > 0)
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$item_id,
					$instance_no,
					$old_owner_id,
					$owner_id ) );
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$instance_no,
				$old_owner_id,
				$owner_id ) );
		return FALSE;
	}
}

//
// Delete item and return boolean indicating success or failure.
//
function delete_item_instance($item_id, $instance_no) {
	// remove all child related item instance relationships only - not the actual instances themselves.
	delete_item_instance_relationships ( $item_r ['item_id'], $item_r ['instance_no'] );
	
	if (! is_exists_related_item_instance_relationship ( $item_r ['item_id'], $item_r ['instance_no'] )) {
		$query = "DELETE FROM item_instance WHERE item_id = '" . $item_id . "' AND instance_no = '$instance_no'";
		$delete = db_query ( $query );
		if (db_affected_rows () > 0) {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$instance_no ) );
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$instance_no ) );
			return FALSE;
		}
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Instance is referenced in at least one item instance relationship', array (
				$item_id,
				$instance_no ) );
		return FALSE;
	}
}

function insert_item_instance_relationships($item_id, $related_item_id, $related_instance_no) {
	$instance_no_r = NULL;
	$results = fetch_item_instance_rs ( $item_id );
	if ($results) {
		while ( $item_instance_r = db_fetch_assoc ( $results ) ) {
			$instance_no_r [] = $item_instance_r ['instance_no'];
		}
		db_free_result ( $results );
	}
	
	// todo - should this be locked?!
	if (is_array ( $instance_no_r )) {
		foreach ($instance_no_r as $instance_no) {
			insert_item_instance_relationship ( $item_id, $instance_no, $related_item_id, $related_instance_no );
		}
	}
}

function insert_item_instance_relationship($item_id, $instance_no, $related_item_id, $related_instance_no) {
	if (is_numeric ( $item_id ) && is_numeric ( $instance_no ) && is_numeric ( $related_item_id ) && is_numeric ( $related_instance_no )) {
		$query = "INSERT INTO item_instance_relationship(item_id, instance_no, related_item_id, related_instance_no)" . "VALUES ($item_id, $instance_no, $related_item_id, $related_instance_no)";
		
		$insert = db_query ( $query );
		if ($insert && db_affected_rows () > 0) {
			$sequence_number = db_insert_id ();
			
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
					$item_id,
					$instance_no,
					$related_item_id,
					$related_instance_no ) );
			return $sequence_number;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$item_id,
					$instance_no,
					$related_item_id,
					$related_instance_no ) );
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function delete_item_instance_relationships($item_id, $instance_no = NULL) {
	$query = "DELETE FROM item_instance WHERE item_id = '" . $item_id . "'";
	
	if (is_numeric ( $instance_no )) {
		$query .= " AND instance_no = '$instance_no'";
	}
	
	$delete = db_query ( $query );
	if (db_affected_rows () > 0) {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$instance_no ) );
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$instance_no ) );
		return FALSE;
	}
}

/**
 * Take item_id and get DISTINCT list of related items, and copy for new instance.
 *
 * @param unknown_type $item_id
 * @param unknown_type $instance_no
 */
function copy_related_item_instance_relationships($item_id, $instance_no) {
	$item_instance_rs = NULL;
	$results = fetch_item_instance_relationship_rs ( $item_id );
	if ($results) {
		while ( $item_instance_r = db_fetch_assoc ( $results ) ) {
			if ($item_instance_r ['instance_no'] != $instance_no) {
				$item_instance_rs [] = $item_instance_r;
			}
		}
		db_free_result ( $results );
	}
	
	if (is_array ( $item_instance_rs )) {
		foreach ($item_instance_rs as $item_instance_r) {
			insert_item_instance_relationship ( $item_id, $instance_no, $item_instance_r ['item_id'], $item_instance_r ['instance_no'] );
		}
	}
}

function delete_related_item_instance_relationship($item_id, $instance_no, $parent_item_id, $parent_instance_no) {
	$query = "DELETE FROM item_instance_relationship 
			WHERE related_item_id = '" . $item_id . "' AND 
				related_instance_no = $instance_no AND
				item_id = $parent_item_id AND
				instance_no = $parent_instance_no";
	
	$delete = db_query ( $query );
	if (db_affected_rows () > 0) {
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
				$item_id,
				$instance_no,
				$parent_item_id,
				$parent_instance_no ) );
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$item_id,
				$instance_no,
				$parent_item_id,
				$parent_instance_no ) );
		return FALSE;
	}
}
?>
