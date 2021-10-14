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
include_once("./lib/utils.php");
include_once("./lib/sortutils.php");
include_once("./lib/http.php");
include_once("./lib/theme.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item_type_group.php");

function get_list_username($user_id, $mode, $subject = NULL, $redirect_link = NULL, $redirect_url = NULL) {
	// Do not include email link, if Current User.
	if ($user_id == get_opendb_session_var ( 'user_id' )) {
		return get_opendb_lang_var ( 'current_user', array (
				'fullname' => fetch_user_name ( $user_id ),
				'user_id' => $user_id ) );
	} else {
		$user_name = get_opendb_lang_var ( 'user_name', array (
				'fullname' => fetch_user_name ( $user_id ),
				'user_id' => $user_id ) );
		if (is_user_granted_permission ( PERM_VIEW_USER_PROFILE ))
			return "<a href=\"user_profile.php?uid=" . $user_id . "&subject=" . urlencode ( ifempty ( $subject, get_opendb_lang_var ( 'no_subject' ) ) ) . "&redirect_link=" . urlencode ( $redirect_link ) . "&redirect_url=" . urlencode ( $redirect_url ) . "\" title=\"" . htmlspecialchars ( get_opendb_lang_var ( 'user_profile' ) ) . "\">$user_name</a>";
		else
			return $user_name;
	}
}

function getAlphaListBlock($PHP_SELF, $HTTP_VARS) {
	$buffer = '<ul class="alphalist">';
	
	$context_vars = $HTTP_VARS;
	if (get_opendb_config_var ( 'listings', 'alphalist_new_search_context' ) !== FALSE) {
		$context_vars = array (
				'owner_id' => $HTTP_VARS ['owner_id'],
				'order_by' => $HTTP_VARS ['order_by'],
				'sortorder' => $HTTP_VARS ['sortorder'] );
	}

	$letter = $HTTP_VARS ['letter'] ?? '';
	
	foreach ( array_merge ( array ('#' ), range ( 'A', 'Z' ) ) as $char ) {
		if ($letter == $char) {
			$buffer .= "<li class=\"current\">$char</li>";
		} else {
			$buffer .= "<li><a href=\"$PHP_SELF?" . get_url_string ( $context_vars, array (
					'letter' => $char ), array (
					'page_no' ) ) . "\">" . $char . "</a></li>";
		}
	}
	
	if (strlen( $letter ) > 0) {
		$buffer .= "<li class=\"all\"><a href=\"$PHP_SELF?" . get_url_string ( $context_vars, array ( 'letter' => '' ) ) .
				"\">" . get_opendb_lang_var ( 'all' ) . "</a></li>";
	}
	
	$buffer .= '</ul>';
	
	return $buffer;
}

function getItemsPerPageControl($PHP_SELF, $HTTP_VARS) {
	$buffer = '';
	$items_per_page_options_r = get_opendb_config_var ( 'listings', 'items_per_page_options' );
	if (is_not_empty_array ( $items_per_page_options_r )) {
		$items_per_page_rs = array ();
		foreach ( $items_per_page_options_r as $items_per_page ) {
			if ($items_per_page == '0')
				$display = get_opendb_lang_var ( 'all' );
			else
				$display = $items_per_page;
			
			$items_per_page_rs [] = array (
					'value' => $items_per_page,
					'display' => $display );
		}
		
		$buffer .= "<form class=\"itemsPerPageControl\" id=\"form-items_per_page\" action=\"" . $PHP_SELF . "\" method=\"GET\">" . get_url_fields ( $HTTP_VARS ) . "<label for=\"select-items_per_page\">" . get_opendb_lang_var ( 'items_per_page' ) . '</label>' . "<select id=\"select-items_per_page\" name=\"items_per_page\" class=\"footer\" onChange=\"this.form.submit()\">" . custom_select ( 'items_per_page', $items_per_page_rs, '%display%', 'NA', ifempty ( $HTTP_VARS ['items_per_page'], get_opendb_config_var ( 'listings', 'items_per_page' ) ), 'value' ) . "\n</select></form>";
	}
	return $buffer;
}

/*
* @param $toggle_options - Format should be
* 				array('value'=>'display', 'value'=>'display')
*/
function getToggleControl($PHP_SELF, $HTTP_VARS, $text, $fieldname, $value) {
	$buffer = "<form class=\"toggleControl\" id=\"toggle-$fieldname\" action=\"" . $PHP_SELF . "\" method=\"GET\">" . get_url_fields ( $HTTP_VARS, NULL, array (
			$fieldname ) ) . "<input type=\"hidden\" name=\"$fieldname\" value=\"$value\">" . "<label for=\"toggle-$fieldname-cbox\">" . $text . "</label><input type=\"checkbox\" class=\"checkbox\" id=\"toggle-$fieldname-cbox\" name=\"${fieldname}_cbox\" value=\"Y\" onclick=\"if(this.checked){this.form['${fieldname}'].value='Y';}else{this.form['${fieldname}'].value='N';} this.form.submit()\"" . (strcasecmp ( $value, 'Y' ) === 0 ? " CHECKED" : "") . ">" . "</form>";
	return $buffer;
}

/**
	This does a simple sort of the admin_checkout arrays.  There is no
	reference to any other column in the sort order, only the primary one.

	Only supports sorting by:
		title,s_item_type,owner 
*/
function sort_item_listing(&$item_listing_rs, $order_by, $sortorder) {
	if ($order_by == 's_item_type')
		$order_by_clause = "s_item_type $sortorder, title $sortorder, instance_no ASC, owner_id $sortorder";
	else if ($order_by == 'title')
		$order_by_clause = "title $sortorder, instance_no ASC, type $sortorder, owner_id $sortorder";
	else if ($order_by == 'owner')
		$order_by_clause = "owner_id $sortorder, title $sortorder, instance_no ASC, type $sortorder";
	
	usort ( $item_listing_rs, create_function ( '$a,$b', get_usort_function ( $order_by_clause ) ) );
}

/**
	Assumes all arrays are indexed by integer and are not more
	than two dimensions deep.
	
	Will return a new $old_item_array, with any values removed, that are found in 
	$checked_item_array but not in $new_item_array.
*/
function remove_array_values($old_item_array, $new_item_array, $checked_item_array) {
	// In order to work out what to remove from the $session_array, the $old_item_array
	// must exist.
	if (is_not_empty_array ( $old_item_array ) && is_not_empty_array ( $checked_item_array )) {
		$new_array = array ();
		
		reset ( $old_item_array );
		foreach ( $old_item_array as $value ) {
			// $value must exist in $old_item_array.  If $new_item_array is not an array, or the 
			// $value is not found, remove from $session_array
			if (! (in_array ( $value, $checked_item_array ) && @! in_array ( $value, $new_item_array ))) {
				array_push ( $new_array, $value );
			}
		}
		return $new_array;
	} else
		return $old_item_array;
}

/*
* Any values found in $item_array, should be removed from $old_item_array
*/
function minus_array_values($old_item_array, $item_array) {
	// In order to work out what to remove from the $session_array, the $old_item_array
	// must exist.
	if (is_not_empty_array ( $old_item_array ) && is_not_empty_array ( $item_array )) {
		$new_array = array ();
		
		reset ( $old_item_array );
		foreach ( $old_item_array as $value ) {
			if (! in_array ( $value, $item_array )) {
				array_push ( $new_array, $value );
			}
		}
		return $new_array;
	} else
		return $old_item_array;
}

function convert_array_to_csv_list($array_list) {
	$csvlist = '';
	
	if (is_array ( $array_list )) {
		reset ( $array_list );
		foreach ( $array_list as $value ) {
			if (strlen ( $csvlist ) > 0)
				$csvlist .= ',';
			$csvlist .= $value;
		}
	}
	return $csvlist;
}
?>
