<?php
/* 	Open Media Collectors Database
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
include_once("./lib/item.php");
include_once("./lib/parseutils.php");
include_once("./lib/http.php");
include_once("./lib/widgets.php");
include_once("./lib/listutils.php");
include_once("./lib/user.php");
include_once("./lib/item_attribute.php");
include_once("./lib/review.php");
include_once("./lib/filecache.php");
include_once("./lib/TitleMask.class.php");

function get_last_num_items_rs($num_of_items, $owner_id = NULL, $s_item_type = NULL, $update_on = NULL, $not_owner_id = NULL, $site_url_prefix = NULL, $title_mask_id = NULL) {
	$search_vars_r = array();
	if (strlen ( $owner_id ) > 0)
		$search_vars_r ['owner_id'] = $owner_id;
	
	if (strlen ( $s_item_type ) > 0)
		$search_vars_r ['s_item_type'] = $s_item_type;
	
	if (strlen ( $update_on ) > 0)
		$search_vars_r ['update_on'] = $update_on;
	
	if (strlen ( $not_owner_id ) > 0)
		$search_vars_r ['not_owner_id'] = $not_owner_id;
	
	$dummy_r = NULL;
	$results = fetch_item_listing_rs ( $search_vars_r, $dummy_r, 'update_on', 'DESC', 0, $num_of_items );
	if ($results) {
		if ($title_mask_id == 'feeds') {
			$title_mask_group = array (
					'feeds',
					'item_display' );
		} else {
			$title_mask_group = array (
					'last_items_list',
					'item_listing' );
		}
		
		$titleMaskCfg = new TitleMask ( $title_mask_group );
		
		$image_attribute_type_rs = NULL;
		while ( $item_r = db_fetch_assoc ( $results ) ) {
			$item_r ['title'] = $titleMaskCfg->expand_item_title ( $item_r );
			$item_r ['update_on'] = get_localised_timestamp ( get_opendb_config_var ( 'welcome.last_items_list', 'datetime_mask' ), $item_r ['update_on'] );
			
			$item_r ['item_display_url'] = 'item_display.php?item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'];
			
			if ($site_url_prefix != NULL) {
				$item_r ['item_display_url'] = $site_url_prefix . $item_r ['item_display_url'];
			}
			
			$item_type_r = fetch_item_type_r ( $item_r ['s_item_type'] );
			$itemtypeimagesrc = theme_image_src ( $item_type_r ['image'] );

			if ($itemtypeimagesrc) {
				$size = @getimagesize ( $itemtypeimagesrc );
				if (is_array ( $size )) {
					$item_r ['itemtypeimage'] ['width'] = $size [0];
					$item_r ['itemtypeimage'] ['height'] = $size [1];
				}
			}
			
			$item_r ['itemtypeimage'] ['url'] = $itemtypeimagesrc;
			if ($site_url_prefix != NULL) {
				$item_r ['itemtypeimage'] ['url'] = $site_url_prefix . $item_r ['itemtypeimage'] ['url'];
			}
			
			$item_r ['itemtypeimage'] ['title'] = $item_type_r ['description'];
			$item_r ['itemtypeimage'] ['s_item_type'] = $item_r ['s_item_type'];
			
			if (get_opendb_config_var ( 'listings', 'show_item_image' ) !== FALSE) {
				if (! is_array ( $image_attribute_type_rs ) || ! is_array ( $image_attribute_type_rs [$item_r ['s_item_type']] )) {
					$image_attribute_type_rs [$item_r ['s_item_type']] = fetch_sfieldtype_item_attribute_type_r ( $item_r ['s_item_type'], 'IMAGE' );
				}
				
				// of a IMAGE s_attribute defined for this s_item_type
				if (is_array ( $image_attribute_type_rs [$item_r ['s_item_type']] )) {
					$attribute_type_r = $image_attribute_type_rs [$item_r ['s_item_type']];
					
					$imageurl = fetch_attribute_val ( $item_r ['item_id'], NULL, $attribute_type_r ['s_attribute_type'] );
					
					$file_r = file_cache_get_image_r ( $imageurl, 'display' );
					
					$item_r ['imageurl'] ['url'] = $file_r ['thumbnail'] ['url'];
					if ($site_url_prefix != NULL) {
						$item_r ['imageurl'] ['url'] = $site_url_prefix . $item_r ['imageurl'] ['url'];
					}

					if (isset($file_r['thumbnail']['width']))
						$item_r['imageurl']['width'] = $file_r['thumbnail'] ['width'];
					if (isset($file_r['thumbnail']['height']))
						$item_r['imageurl']['height'] = $file_r['thumbnail']['height'];
					
					$item_r ['imageurl'] ['title'] = $item_r ['title'];
				}
			}
			
			$item_rs [] = $item_r;
			
			unset ( $item_r );
		} //while
		db_free_result ( $results );
	}
	return $item_rs;
}

function get_last_item_list($num_of_items, $owner_id = NULL, $s_item_type = NULL, $update_on = NULL, $not_owner_id = NULL, $site_url_prefix = NULL, $is_new_window_item_display = FALSE) {
	$list_item_rs = get_last_num_items_rs ( $num_of_items, 	// number of items to return
											$owner_id, 	//owner_id
											$s_item_type, 	// s_item_type
											$update_on, 	//update_on
											$not_owner_id, 	// not_owner_id
											$site_url_prefix, 'last_items_list' );

	foreach ( $list_item_rs as $key => $list_item_r ) {
		$item_block = '';
		
		if ($is_new_window_item_display) {
			$href_link = "<a href=\"" . $list_item_r ['item_display_url'] . "&inc_menu=N\" target=\"_new\">";
		} else {
			$href_link = "<a href=\"" . $list_item_r ['item_display_url'] . "\">";
		}
		
		if (is_user_granted_permission ( PERM_VIEW_ITEM_COVERS )) {
			$imageblock = get_image_block ( $list_item_r ['imageurl'] );
			if ($imageblock != NULL) {
				$item_block .= "<span class=\"coverImage\">" . $href_link . $imageblock . "</a></span>";
			}
		}
		
		if (is_array ( $list_item_r ['itemtypeimage'] )) {
			$itemimageblock = theme_image ( $list_item_r ['itemtypeimage'] ['url'], $list_item_r ['itemtypeimage'] ['title'], 's_item_type' );
			
			$titleblock = $href_link . $itemimageblock . " " . $list_item_r ['title'] . "</a>";
		} else {
			$titleblock = $href_link . $list_item_r ['title'] . "</a>";
		}
		
		$item_block .= "<h4 class=\"title\">$titleblock</h4>";
		
		$item_block .= "<small class=\"updateOn\">" . $list_item_r ['update_on'] . "</small>";
		
		$itemblocks [] = $item_block;
	}
	
	return $itemblocks;
}

function get_image_block($image_r, $class = NULL) {
	$imageblock = NULL;
	
	if (is_array ( $image_r )) {
		$imageblock .= "<img src=\"" . $image_r ['url'] . "\" border=0 title=\"" . htmlspecialchars ( $image_r ['title'] ) . "\" ";
		
		if ($class != NULL)
			$imageblock .= ' class="' . $class . '"';
		
		if (is_numeric ( $image_r ['width'] ?? ""))
			$imageblock .= ' width="' . $image_r ['width'] . '"';
		if (is_numeric ( $image_r ['height'] ?? "" ))
			$imageblock .= ' height="' . $image_r ['height'] . '"';
		
		$imageblock .= ">";
	}
	return $imageblock;
}

function get_last_item_list_marquee($blocks_r) {
	$buffer = '';
	if (is_array ( $blocks_r )) {
		foreach ( $blocks_r as $key => $block ) {
			$buffer .= "\n<div class=\"lastitemlist-item\" style=\"display: none;\">";
			$buffer .= $block;
			$buffer .= "</div>";
		}
	}
	
	return $buffer;
}

function get_last_item_list_table($blocks_r) {
	$buffer = '';
	if (is_array ( $blocks_r )) {
		$buffer .= "\n<ul>";
		foreach ( $blocks_r as $key => $block ) {
			$buffer .= "\n<li>" . $block . "\n</li>";
		}
		$buffer .= "\n</ul>";
	}
	return $buffer;
}

function get_welcome_last_item_list($update_on, $user_id) {
	$last_items_list_conf_r = get_opendb_config_var ( 'welcome.last_items_list' );
	if ($last_items_list_conf_r ['enable'] !== FALSE) {
		if ($last_items_list_conf_r ['exclude_current_user'] !== TRUE)
			$user_id = NULL;
		
		if ($last_items_list_conf_r ['restrict_last_login'] !== TRUE)
			$update_on = NULL;
		
		return get_last_item_list ( $last_items_list_conf_r ['total_num_items'], NULL, NULL, $update_on, $user_id, NULL, FALSE );
	} else {
		return NULL;
	}
}

function get_whats_new_details($update_on, $user_id = NULL) {
	$whats_new_conf_r = get_opendb_config_var ( 'welcome.whats_new' );
	if ($whats_new_conf_r ['enable'] !== FALSE) {
		$whats_new_rs = NULL;
		
		// Get the list of valid status_types, which we can display in this whatsnew page.
		$results = fetch_status_type_rs ();
		if ($results) {
			if ($whats_new_conf_r ['restrict_last_login'] !== TRUE)
				$update_on = NULL;
			
			$search_vars_r ['update_on'] = $update_on;
			
			if ($whats_new_conf_r ['exclude_current_user'] !== FALSE)
				$search_vars_r ['not_owner_id'] = $user_id;
			
			$whats_new_r ['heading'] = get_opendb_lang_var ( 'item_stats' );
			
			while ( $status_type_r = db_fetch_assoc ( $results ) ) {
				$search_vars_r ['s_status_type'] = $status_type_r ['s_status_type'];
				
				$status_items_updated = fetch_item_listing_cnt ( $search_vars_r );
				$status_title = get_opendb_lang_var ( 'cnt_item(s)_added_updated', array (
						'count' => $status_items_updated,
						's_status_type_desc' => $status_type_r ['description'] ) );
				
				if ($status_items_updated > 0) {
					$item_r ['class'] = 'tick';
					$item_r ['content'] = '<a href="listings.php?' . (strlen ( $search_vars_r['not_owner_id'] ?? '' ) > 0 ? 'not_owner_id=' . $search_vars_r ['not_owner_id'] . '&' : '') . 's_status_type=' . $status_type_r ['s_status_type'] . '&update_on=' . urlencode ( $update_on ) . '">' . $status_title . '</a>';
				} else {
					$item_r ['class'] = 'cross';
					$item_r ['content'] = $status_title;
				}
				
				$whats_new_r ['items'] [] = $item_r;
			}
			db_free_result ( $results );
			
			if (is_array ( $whats_new_r )) {
				$whats_new_rs [] = $whats_new_r;
			}
		}
		
		if (get_opendb_config_var ( 'borrow', 'enable' ) !== FALSE && $whats_new_conf_r ['borrow_stats'] !== FALSE) {
			$whats_new_r ['heading'] = get_opendb_lang_var ( 'borrow_stats' );
			
			$whats_new_r ['items'] = NULL;
			
			$returned_cnt = fetch_borrowed_item_status_atdate_cnt ( 'C', $update_on );
			if ($returned_cnt > 0)
				$item_r ['class'] = 'tick';
			else
				$item_r ['class'] = 'cross';
			
			$item_r ['content'] = get_opendb_lang_var ( 'cnt_item(s)_returned', 'count', $returned_cnt );
			$whats_new_r ['items'] [] = $item_r;
			
			$borrowed_cnt = fetch_borrowed_item_status_atdate_cnt ( 'B', $update_on );
			if ($borrowed_cnt > 0)
				$item_r ['class'] = 'tick';
			else
				$item_r ['class'] = 'cross';
			$item_r ['content'] = get_opendb_lang_var ( 'cnt_item(s)_borrowed', 'count', $borrowed_cnt );
			$whats_new_r ['items'] [] = $item_r;
			
			$reserved_cnt = fetch_borrowed_item_status_atdate_cnt ( 'R', $update_on );
			if ($reserved_cnt > 0)
				$item_r ['class'] = 'tick';
			else
				$item_r ['class'] = 'cross';
			$item_r ['content'] = get_opendb_lang_var ( 'cnt_item(s)_reserved', 'count', $reserved_cnt );
			
			$whats_new_r ['items'] [] = $item_r;
			
			if (is_array ( $whats_new_r )) {
				$whats_new_rs [] = $whats_new_r;
			}
		}
		
		if (get_opendb_config_var ( 'item_review', 'enable' ) !== FALSE && $whats_new_conf_r ['review_stats'] !== FALSE) {
			$whats_new_r ['heading'] = get_opendb_lang_var ( 'review(s)' );
			
			$whats_new_r ['items'] = NULL;
			
			$block = '';
			$review_cnt = fetch_review_atdate_cnt ( $update_on );
			if ($review_cnt > 0)
				$item_r ['class'] = 'tick';
			else
				$item_r ['class'] = 'cross';
			
			$item_r ['content'] = get_opendb_lang_var ( 'cnt_review(s)', 'count', $review_cnt );
			
			$whats_new_r ['items'] [] = $item_r;
			
			if (is_array ( $whats_new_r )) {
				$whats_new_rs [] = $whats_new_r;
			}
		}
		
		return $whats_new_rs;
	} else {
		return NULL;
	}
}
?>
