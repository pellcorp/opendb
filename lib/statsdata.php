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
include_once("./lib/borrowed_item.php");

include_once("./lib/user.php");
include_once("./lib/item.php");
include_once("./lib/review.php");
include_once("./lib/item_type.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item.php");

function build_owner_item_chart_data($s_item_type) {
	$result = fetch_user_rs ( PERM_ITEM_OWNER );
	if ($result) {
		while ( $user_r = db_fetch_assoc ( $result ) ) {
			$num_total = fetch_owner_item_cnt ( $user_r ['user_id'], $s_item_type );
			if ($num_total > 0) {
				$data [] = array (
						'display' => $user_r ['fullname'],
						'value' => $num_total );
			}
		}
		db_free_result ( $result );
	}
	
	return $data;
}

function build_item_category_chart_data($s_item_type) {
	$category_attribute_type = fetch_sfieldtype_item_attribute_type ( $s_item_type, 'CATEGORY' );
	if ($category_attribute_type) {
		$results = fetch_attribute_type_lookup_rs ( $category_attribute_type, 'order_no, value ASC' );
		if ($results) {
			while ( $attribute_type_r = db_fetch_assoc ( $results ) ) 			// next category...
{
				$num_total = fetch_category_item_cnt ( $attribute_type_r ['value'], $s_item_type );
				if ($num_total > 0) {
					$data [] = array (
							'display' => $attribute_type_r ['display'],
							'value' => $num_total );
				}
			}
			db_free_result ( $results );
		}
	}
	
	return $data;
}

function build_category_chart_data() {
	$results = fetch_field_type_attribute_lookup_rs ( 'CATEGORY' );
	if ($results) {
		while ( $attribute_type_r = db_fetch_assoc ( $results ) ) {
			$num_total = fetch_category_item_cnt ( $attribute_type_r ['value'], NULL );
			if ($num_total > 0) {
				$data [] = array (
						'display' => $attribute_type_r ['display'],
						'value' => $num_total );
			}
		}
		db_free_result ( $results );
	}
	
	return $data;
}

function build_item_types_chart_data() {
	$results = fetch_item_type_rs ();
	while ( $item_type_r = db_fetch_assoc ( $results ) ) {
		$num_total = fetch_item_instance_cnt ( $item_type_r ['s_item_type'] );
		if ($num_total > 0) {
			$data [] = array (
					'display' => $item_type_r ['s_item_type'],
					'value' => $num_total );
		}
	}
	db_free_result ( $results );
	
	return $data;
}

function build_item_ownership_chart_data() {
	$results = fetch_status_type_rs ();
	if ($results) {
		while ( $status_type_r = db_fetch_assoc ( $results ) ) {
			$status_type_rs [] = $status_type_r;
		}
		db_free_result ( $results );
	}
	
	$results = fetch_user_rs ( PERM_ITEM_OWNER );
	if ($results) {
		while ( $user_r = db_fetch_assoc ( $results ) ) {
			$num_total = 0;
			if (is_not_empty_array ( $status_type_rs )) {
				reset ( $status_type_rs );
				foreach ($status_type_rs as $key => $status_type_r) {
					$status_total = fetch_owner_s_status_type_item_cnt ( $user_r ['user_id'], $status_type_r ['s_status_type'] );
					$num_total += $status_total;
				}
			}
			
			// pie chart data
			if ($num_total > 0) {
				$data [] = array (
						'display' => $user_r ['fullname'],
						'value' => $num_total );
			}
		}
		db_free_result ( $results );
	}
	
	return $data;
}

function do_stats_graph($HTTP_VARS) {
	// Load GD Library if not already loaded - todo is this still required
	// Thanks to Laurent CHASTEL (lchastel)
	if (! @extension_loaded ( 'gd' )) {
		if (( boolean ) @ini_get ( 'enable_dl' )) 		// is dynamic load enabled
{
			$gd_library = get_opendb_config_var ( 'site.gd', 'library' );
			if (strlen ( $gd_library ) > 0) {
				@dl ( $gd_library );
			}
		}
	}
	
	switch ($HTTP_VARS ['graphtype']) {
		case 'item_ownership' :
			build_and_send_graph ( build_item_ownership_chart_data (), 'piechart', get_chart_alt_text ( $HTTP_VARS ['graphtype'] ) );
			break;
		
		case 'item_types' :
			build_and_send_graph ( build_item_types_chart_data (), 'piechart', get_chart_alt_text ( $HTTP_VARS ['graphtype'] ) );
			break;
		
		case 'categories' :
			$chartType = 'piechart';
			if (get_opendb_config_var ( 'stats', 'category_barchart' ) === TRUE)
				$chartType = 'barchart';
			
			build_and_send_graph ( build_category_chart_data (), $chartType, get_chart_alt_text ( $HTTP_VARS ['graphtype'] ) );
			
			break;
		
		case 'item_type_ownership' :
			build_and_send_graph ( build_owner_item_chart_data ( $HTTP_VARS ['s_item_type'] ), 'piechart', get_chart_alt_text ( $HTTP_VARS ['graphtype'], $HTTP_VARS ['s_item_type'] ) );
			break;
		
		case 'item_type_category' :
			$chartType = 'piechart';
			if (get_opendb_config_var ( 'stats', 'category_barchart' ) === TRUE)
				$chartType = 'barchart';
			
			build_and_send_graph ( build_item_category_chart_data ( $HTTP_VARS ['s_item_type'] ), $chartType, get_chart_alt_text ( $HTTP_VARS ['graphtype'], $HTTP_VARS ['s_item_type'] ) );
			
			break;
		
		default :
		// what to do here!
	}
}

function render_chart_image($graphType, $itemType = NULL) {
	$graphCfg = _theme_graph_config ();
	$chartLib = get_opendb_config_var ( 'stats', 'chart_lib' );
	if ($chartLib != 'legacy') {
		$widthHeightAttribs = "width=\"${graphCfg['width']}\" height=\"${graphCfg['height']}\"";
	}
	
	$altText = get_chart_alt_text ( $graphType, $itemType );
	
	return "<img src=\"stats.php?op=graph&graphtype=$graphType" . (strlen ( $itemType ) > 0 ? "&s_item_type=" . urlencode ( $itemType ) : "") . "\" $widthHeightAttribs alt=\"$altText\">";
}

function get_chart_alt_text($graphType, $itemType = NULL) {
	switch ($graphType) {
		case 'item_ownership' :
			return get_opendb_lang_var ( 'database_ownership_chart' );
		case 'item_types' :
			return get_opendb_lang_var ( 'database_itemtype_chart' );
		case 'categories' :
			return get_opendb_lang_var ( 'category_chart' );
		case 'item_type_ownership' :
			return get_opendb_lang_var ( 'itemtype_ownership_chart', 's_item_type', $itemType );
		case 'item_type_category' :
			return get_opendb_lang_var ( 'itemtype_category_chart', 's_item_type', $itemType );
		default :
			return NULL;
	}
}
?>
