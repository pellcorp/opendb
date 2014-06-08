<?php
/* 	
	OpenDb Media Collector Database
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
include_once("./lib/item_type_group.php");
include_once("./lib/item_type.php");

function fetch_item_listing_conf_rs() {
	$query = "SELECT id, s_item_type, s_item_type_group FROM s_item_listing_conf";
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_item_listing_column_conf_rs($silc_id) {
	$query = 'SELECT column_no, column_type, s_field_type, s_attribute_type, override_prompt, orderby_support_ind, orderby_datatype, orderby_sort_order, orderby_default_ind, printable_support_ind ' . 'FROM s_item_listing_column_conf ' . 'WHERE silc_id = ' . $silc_id . ' ' . 'ORDER BY column_no';
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_item_listing_conf_id($s_item_type_group, $s_item_type) {
	if ($s_item_type_group == NULL)
		$s_item_type_group = '*';
	
	if ($s_item_type == NULL)
		$s_item_type = '*';
	
	$query = "SELECT id FROM s_item_listing_conf WHERE s_item_type_group = '$s_item_type_group' AND s_item_type = '$s_item_type'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		return $found ['id'];
	}
	
	//else
	return FALSE;
}

function is_exists_s_item_listing_column_conf($silc_id, $s_item_type_group = NULL, $s_item_type = NULL) {
	if (! is_numeric ( $silc_id ))
		$silc_id = fetch_s_item_listing_conf_id ( $s_item_type_group, $s_item_type );
	
	if (is_numeric ( $silc_id )) {
		$query = "SELECT 'x' FROM s_item_listing_column_conf WHERE silc_id = $silc_id";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			db_free_result ( $result );
			return TRUE;
		}
	}
	
	//else
	return FALSE;
}

/**
	This function is to be called from listings.php, for either a s_item_type_group or
	s_item_type.  if s_item_type is defined, thats used first, otherwise we rely on
	s_item_type_group
*/
function get_s_item_listing_column_conf_rs($s_item_type_group, $s_item_type) {
	$silc_id = NULL;
	
	if (strlen ( $s_item_type ) > 0 && $s_item_type != '*' && is_exists_item_type ( $s_item_type )) {
		$silc_id = fetch_s_item_listing_conf_id ( NULL, $s_item_type );
	} else if (strlen ( $s_item_type_group ) > 0 && $s_item_type_group != '*' && is_exists_item_type_group ( $s_item_type_group )) {
		$silc_id = fetch_s_item_listing_conf_id ( $s_item_type_group, NULL );
	}
	
	if (! is_numeric ( $silc_id )) {
		// get the default
		$silc_id = fetch_s_item_listing_conf_id ( NULL, NULL );
	}
	
	if (is_exists_s_item_listing_column_conf ( $silc_id )) {
		$results = fetch_s_item_listing_column_conf_rs ( $silc_id );
		if ($results) {
			while ( $item_listing_column_conf_r = db_fetch_assoc ( $results ) ) {
				// special indicator that this column is an item listings configuration column and should be 
				// left in the listings page even if exact match on this column is being performed.
				$item_listing_column_conf_r ['item_listing_conf_ind'] = 'Y';
				
				$item_listing_column_conf_rs [] = $item_listing_column_conf_r;
			}
			db_free_result ( $results );
		}
		
		return $item_listing_column_conf_rs;
	} else {
		return array (
				array (
						column_type => 's_field_type',
						s_field_type => 'TITLE' ),
				array (
						column_type => 'action_links' ),
				array (
						column_type => 's_field_type',
						s_field_type => 'OWNER' ),
				array (
						column_type => 's_field_type',
						s_field_type => 'STATUSTYPE' ),
				array (
						column_type => 's_field_type',
						s_field_type => 'CATEGORY' ) );
	}
}
?>