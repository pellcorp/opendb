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
include_once("./lib/TitleMask.class.php");
include_once("./lib/HTML_Listing.class.php");
include_once("./lib/item.php");

/**
	This function will return a complete table of all links valid
	for this item_type.

	This is useful because it allows the use of a site plugin for
	generating links only, by specifying as the default site type.
*/
function get_site_plugin_links($page_title, $item_r) {
	$pageContents = '';
	
	$results = fetch_site_plugin_rs ( $item_r ['s_item_type'] );
	if ($results) {
		$titleMaskCfg = new TitleMask ();
		
		$pageContents = "<ul class=\"sitepluginLinks\">";
		
		while ( $site_plugin_type_r = db_fetch_assoc ( $results ) ) {
			if (is_exists_site_plugin ( $site_plugin_type_r ['site_type'] )) {
				$site_plugin_conf_rs = get_site_plugin_conf_r ( $site_plugin_type_r ['site_type'] );
				
				if (strlen ( $site_plugin_type_r ['image'] ) > 0)
					$link_text = theme_image ( "images/site/" . $site_plugin_type_r ['image'], htmlspecialchars ( $site_plugin_type_r ['title'] ) );
				else
					$link_text = $site_plugin_type_r ['title'];
				
				$results2 = fetch_site_plugin_link_rs ( $site_plugin_type_r ['site_type'], $item_r ['s_item_type'] );
				if ($results2) {
					while ( $site_plugin_link_r = db_fetch_assoc ( $results2 ) ) {
						$parse_url = NULL;
						
						if (strlen ( $site_plugin_link_r ['url'] ) > 0 && is_exists_site_item_attribute ( $site_plugin_type_r ['site_type'], $item_r ['item_id'], $item_r ['instance_no'] ))
							$parse_url = $site_plugin_link_r ['url'];
						else if (strlen ( $site_plugin_link_r ['title_url'] ) > 0)
							$parse_url = $site_plugin_link_r ['title_url'];
						
						if ($parse_url != NULL) {
							$titleMaskCfg->reset ();
							
							$parse_url = trim ( $titleMaskCfg->expand_title ( $item_r, $parse_url, $site_plugin_conf_rs ) );
							if (strlen ( $parse_url ) > 0) {
								$pageContents .= "<li><a href=\"" . $parse_url . "\" target=\"_new\">$link_text";
								$pageContents .= "<span class=\"sitePluginDescription\">" . $site_plugin_link_r ['description'] . "</span>";
								$pageContents .= "</a></li>";
							}
						}
					} //while
					db_free_result ( $results2 );
				}
			}
		} //while
		db_free_result ( $results );
		
		$pageContents .= "</ul>";
		return $pageContents;
	}
}

function get_item_review_block($item_r) {
	$buffer = "<h3>" . get_opendb_lang_var ( 'review(s)' ) . "</h3>";
	
	$result = fetch_review_rs ( $item_r ['item_id'] );
	if ($result) {
		$buffer .= "<ul>";
		while ( $review_r = db_fetch_assoc ( $result ) ) {
			$action_links = NULL;
			
			$buffer .= "<li>";
			
			// even if already review author its possible to revoke rights to
			// edit / modify own reviews by revoking the PERM_USER_REVIEWER grant!
			if (is_user_granted_permission ( PERM_ADMIN_REVIEWER ) || (is_user_granted_permission ( PERM_USER_REVIEWER ) && is_review_author ( $review_r ['sequence_number'] ))) {
				$action_links_rs = NULL;
				if (get_opendb_config_var ( 'item_review', 'update_support' ) !== FALSE)
					$action_links [] = array (
							'url' => "item_review.php?op=edit&sequence_number=" . $review_r ['sequence_number'] . "&item_id=" . $item_r ['item_id'] . "&instance_no=" . $item_r ['instance_no'],
							'text' => get_opendb_lang_var ( 'edit' ) );
				if (get_opendb_config_var ( 'item_review', 'delete_support' ) !== FALSE)
					$action_links [] = array (
							'url' => "item_review.php?op=delete&sequence_number=" . $review_r ['sequence_number'] . "&item_id=" . $item_r ['item_id'] . "&instance_no=" . $item_r ['instance_no'],
							'text' => get_opendb_lang_var ( 'delete' ) );
				
				$buffer .= format_footer_links ( $action_links );
			}
			
			$buffer .= "<p class=\"author\">";
			$buffer .= get_opendb_lang_var ( 'on_date_name_wrote_the_following', array (
					'date' => get_localised_timestamp ( get_opendb_config_var ( 'item_display', 'review_datetime_mask' ), $review_r ['update_on'] ),
					'fullname' => fetch_user_name ( $review_r ['author_id'] ),
					'user_id' => $review_r ['author_id'] ) );
			$buffer .= "</p>";
			
			$buffer .= "<p class=\"comments\">" . nl2br ( trim ( $review_r ['comment'] ) );
			if ($review_r ['item_id'] != $item_r ['item_id']) {
				$buffer .= "<span class=\"reference\">" . get_opendb_lang_var ( 'review_for_item_type_title', array (
						's_item_type' => $review_r ['s_item_type'],
						'item_id' => $review_r ['item_id'] ) ) . "</span>";
			}
			$buffer .= "</p>";
			
			$average = $review_r ['rating'];
			$attribute_type_r = fetch_attribute_type_r ( "S_RATING" );
			$buffer .= "<span class=\"rating\">" . get_display_field ( $attribute_type_r ['s_attribute_type'], NULL, 'review()', 			// display_type
						$average, FALSE ) . "</span>";
			
			$buffer .= "</li>";
		} //while
		

		$buffer .= "</ul>";
	} else {
		$buffer .= '<p>' . get_opendb_lang_var ( 'no_item_reviews' ) . '</p>';
	}
	
	$action_links = NULL;
	if (is_user_granted_permission ( PERM_USER_REVIEWER )) {
		$action_links [] = array (
				'url' => "item_review.php?op=add&item_id=" . $item_r ['item_id'] . "&instance_no=" . $item_r ['instance_no'],
				'text' => get_opendb_lang_var ( 'review' ) );
		
		$buffer .= format_footer_links ( $action_links );
	}
	
	return $buffer;
}

function get_instance_info_block($item_r, $HTTP_VARS, &$instance_info_links_r) {
	$buffer = '<div id="instanceInfo">';
	
	$buffer .= "<h3>" . get_opendb_lang_var ( 'instance_info' ) . "</h3>";
	
	$results = fetch_item_instance_rs ( $item_r ['item_id'], NULL );
	if ($results) {
		$buffer .= "<table>" . "\n<tr class=\"navbar\">" . "\n<th>" . get_opendb_lang_var ( 'instance' ) . "</th>" . "\n<th>" . get_opendb_lang_var ( 'owner' ) . "</th>" . "\n<th>" . get_opendb_lang_var ( 'action' ) . "</th>" . "\n<th>" . get_opendb_lang_var ( 'status' ) . "</th>" . "\n<th>" . get_opendb_lang_var ( 'status_comment' ) . "</th>";
		
		if (get_opendb_config_var ( 'borrow', 'enable' ) !== FALSE) {
			if (get_opendb_config_var ( 'borrow', 'include_borrower_column' ) !== FALSE) {
				$buffer .= "\n<th>" . get_opendb_lang_var ( 'borrower' ) . "</th>";
			}
			
			$buffer .= "\n<th>" . get_opendb_lang_var ( 'borrow_status' ) . "</th>";
			
			if (get_opendb_config_var ( 'borrow', 'duration_support' ) !== FALSE) {
				if (is_item_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] )) {
					$buffer .= "\n<th>" . get_opendb_lang_var ( 'due_date' ) . "</th>";
				} else {
					$buffer .= "\n<th>" . get_opendb_lang_var ( 'borrow_duration' ) . "</th>";
				}
			}
		}
		$buffer .= "\n</tr>";
		
		$toggle = TRUE;
		$numrows = db_num_rows ( $results );
		while ( $item_instance_r = db_fetch_assoc ( $results ) ) {
			if ($toggle)
				$color = "oddRow";
			else
				$color = "evenRow";
			
			$toggle = ! $toggle;
			
			$buffer .= get_item_status_row ( $color, array_merge ( $item_r, $item_instance_r ), $numrows > 1 && $item_r ['instance_no'] === $item_instance_r ['instance_no'] );
		}
		
		$buffer .= "\n</table>";
	} else { // No instances found, because user has been deactivated and/or items are hidden.
		$buffer .= get_opendb_lang_var ( 'no_records_found' );
	}
	
	if (is_user_granted_permission ( PERM_ITEM_OWNER )) {
		if (get_opendb_config_var ( 'item_input', 'item_instance_support' ) !== FALSE) {
			array_push ( $instance_info_links_r, array (
					'url' => "item_input.php?op=newinstance&item_id=" . $item_r ['item_id'] . "&instance_no=" . $item_r ['instance_no'],
					'text' => get_opendb_lang_var ( 'new_item_instance' ) ) );
		}
		
		if (get_opendb_config_var ( 'item_input', 'clone_item_support' ) !== FALSE) {
			array_push ( $instance_info_links_r, array (
					'url' => "item_input.php?op=clone_item&item_id=" . $item_r ['item_id'] . "&instance_no=" . $item_r ['instance_no'],
					'text' => get_opendb_lang_var ( 'clone_item' ) ) );
		}
	}
	
	$buffer .= "</div>";
	
	return $buffer;
}

/**
	@selected will be currently selected record.

	$borrow_duration is the item_instance.borrow_duration value in all cases, the rest of the values to do
	with borrow duration will be calculated.
*/
function get_item_status_row($class, $item_r, $selected) {
	global $HTTP_VARS;
	global $PHP_SELF;
	global $titleMaskCfg;
	
	$rowcontents = "\n<tr class=\"$class\"><td";
	
	if ($selected) {
		$rowcontents .= " class=\"currentItemInstance\">" . $item_r ['instance_no'] . "</span>";
	} else {
		$rowcontents .= "><a href=\"$PHP_SELF?item_id=${item_r['item_id']}&instance_no=${item_r['instance_no']}\">" . $item_r ['instance_no'] . "</a>";
	}
	$rowcontents .= "\n</td>";
	
	$page_title = $titleMaskCfg->expand_item_title ( $item_r );
	
	$page_title = remove_enclosing_quotes ( $page_title );
	
	$rowcontents .= "<td>" . get_list_username ( $item_r ['owner_id'], $HTTP_VARS ['mode'], $page_title, get_opendb_lang_var ( 'back_to_item' ), 'item_display.php?item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'] ) . "</td>";
	
	$status_type_r = fetch_status_type_r ( $item_r ['s_status_type'] );
	
	// ---------------------- Borrow,Reserve,Cancel,Edit,Delete,etc operations here.
	$action_links_rs = NULL;
	
	if ((is_user_granted_permission ( PERM_ITEM_OWNER ) && get_opendb_session_var ( 'user_id' ) === $item_r ['owner_id']) || is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$action_links_rs [] = array (
				'url' => 'item_input.php?op=edit&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
				'img' => 'edit.gif',
				'text' => get_opendb_lang_var ( 'edit' ) );
		
		// Checks if any legal site plugins defined for $item_r['s_item_type']
		if (is_item_legal_site_type ( $item_r ['s_item_type'] )) {
			$action_links_rs [] = array (
					'url' => 'item_input.php?op=site-refresh&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
					'img' => 'refresh.gif',
					'text' => get_opendb_lang_var ( 'refresh' ) );
		}
		
		if ($status_type_r ['delete_ind'] == 'Y' && ! is_item_reserved_or_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] )) {
			$action_links_rs [] = array (
					'url' => 'item_input.php?op=delete&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
					'img' => 'delete.gif',
					'text' => get_opendb_lang_var ( 'delete' ) );
		}
	}
	
	if (is_user_granted_permission ( array (
			PERM_USER_BORROWER,
			PERM_ADMIN_BORROWER ) )) {
		if (get_opendb_config_var ( 'borrow', 'enable' ) !== FALSE) {
			if (get_opendb_config_var ( 'borrow', 'quick_checkout' ) !== FALSE && $status_type_r ['borrow_ind'] == 'Y' && is_user_allowed_to_checkout_item ( $item_r ['item_id'], $item_r ['instance_no'] )) {
				if (! is_item_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] )) {
					$action_links_rs [] = array (
							'url' => 'item_borrow.php?op=quick_check_out&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
							'img' => 'quick_check_out.gif',
							'text' => get_opendb_lang_var ( 'quick_check_out' ) );
				}
			}
			
			// Check if already in reservation session variable.
			if (get_opendb_config_var ( 'borrow', 'reserve_basket' ) !== FALSE && is_item_in_reserve_basket ( $item_r ['item_id'], $item_r ['instance_no'] )) {
				$action_links_rs [] = array (
						'url' => 'borrow.php?op=delete_from_my_reserve_basket&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
						'img' => 'delete_reserve_basket.gif',
						'text' => get_opendb_lang_var ( 'delete_from_reserve_list' ) );
			} else if (is_item_reserved_or_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] )) {
				if (is_item_reserved_by_user ( $item_r ['item_id'], $item_r ['instance_no'] )) {
					$action_links_rs [] = array (
							'url' => 'item_borrow.php?op=cancel_reserve&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
							'img' => 'cancel_reserve.gif',
							'text' => get_opendb_lang_var ( 'cancel_reservation' ) );
				} else if (! is_item_borrowed_by_user ( $item_r ['item_id'], $item_r ['instance_no'] )) {
					if ($status_type_r ['borrow_ind'] == 'Y' && (get_opendb_config_var ( 'borrow', 'allow_reserve_if_borrowed' ) !== FALSE || ! is_item_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] )) && (get_opendb_config_var ( 'borrow', 'allow_multi_reserve' ) !== FALSE || ! is_item_reserved ( $item_r ['item_id'], $item_r ['instance_no'] ))) {
						if (get_opendb_config_var ( 'borrow', 'reserve_basket' ) !== FALSE) {
							$action_links_rs [] = array (
									'url' => 'borrow.php?op=update_my_reserve_basket&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
									'img' => 'add_reserve_basket.gif',
									'text' => get_opendb_lang_var ( 'add_to_reserve_list' ) );
						} else {
							$action_links_rs [] = array (
									'url' => 'item_borrow.php?op=reserve&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
									'img' => 'reserve_item.gif',
									'text' => get_opendb_lang_var ( 'reserve_item' ) );
						}
					}
				}
			} else {
				if ($status_type_r ['borrow_ind'] == 'Y') {
					if (get_opendb_config_var ( 'borrow', 'reserve_basket' ) !== FALSE) {
						$action_links_rs [] = array (
								'url' => 'borrow.php?op=update_my_reserve_basket&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
								'img' => 'add_reserve_basket.gif',
								'text' => get_opendb_lang_var ( 'add_to_reserve_list' ) );
					} else {
						$action_links_rs [] = array (
								'url' => 'item_borrow.php?op=reserve&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
								'img' => 'reserve_item.gif',
								'text' => get_opendb_lang_var ( 'reserve_item' ) );
					}
				}
			}
		}
	}
	
	if (is_item_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] ) && is_user_allowed_to_checkin_item ( $item_r ['item_id'], $item_r ['instance_no'] )) {
		$action_links_rs [] = array (
				'url' => 'item_borrow.php?op=check_in&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
				'img' => 'check_in_item.gif',
				'text' => get_opendb_lang_var ( 'check_in_item' ) );
	}
	
	if ($item_r ['owner_id'] == get_opendb_session_var ( 'user_id' ) || is_user_granted_permission ( PERM_ADMIN_BORROWER )) {
		if (is_exists_item_instance_history_borrowed_item ( $item_r ['item_id'], $item_r ['instance_no'] )) {
			$action_links_rs [] = array (
					'url' => 'borrow.php?op=my_item_history&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'],
					'img' => 'item_history.gif',
					'text' => get_opendb_lang_var ( 'item_history' ) );
		}
	}
	
	$rowcontents .= "\n<td>";
	$rowcontents .= ifempty ( format_action_links ( $action_links_rs ), get_opendb_lang_var ( 'not_applicable' ) );
	$rowcontents .= "</td>";
	
	// Item Status Image.
	$rowcontents .= "\n<td>";
	$rowcontents .= theme_image ( $status_type_r ['img'], $status_type_r ['description'], "s_status_type" );
	$rowcontents .= "</td>";
	
	// If a comment is allowed and defined, add it in.
	$rowcontents .= "\n<td>";
	if ($status_type_r ['status_comment_ind'] == 'Y' || get_opendb_session_var ( 'user_id' ) === $item_r ['owner_id'] || is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		$rowcontents .= ifempty ( nl2br ( $item_r ['status_comment'] ), "&nbsp;" ); // support newlines in this field
	} else {
		$rowcontents .= get_opendb_lang_var ( 'not_applicable' );
	}
	$rowcontents .= "</td>";
	
	if (get_opendb_config_var ( 'borrow', 'enable' ) !== FALSE) {
		if (get_opendb_config_var ( 'borrow', 'include_borrower_column' ) !== FALSE) {
			$rowcontents .= "\n<td>";
			if (is_item_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] ))
				$rowcontents .= get_list_username ( fetch_item_borrower ( $item_r ['item_id'], $item_r ['instance_no'] ), NULL, $page_title, get_opendb_lang_var ( 'back_to_item' ), 'item_display.php?item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'] );
			else
				$rowcontents .= get_opendb_lang_var ( 'not_applicable' );
			$rowcontents .= "</td>";
		}
		
		// Borrow Status Image.
		$rowcontents .= "\n<td>";
		if (is_item_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] )) {
			$rowcontents .= theme_image ( "borrowed.gif", get_opendb_lang_var ( 'borrowed' ), "borrowed_item" );
		} else if (is_item_reserved ( $item_r ['item_id'], $item_r ['instance_no'] )) {
			$rowcontents .= theme_image ( "reserved.gif", get_opendb_lang_var ( 'reserved' ), "borrowed_item" );
		} else {
			$rowcontents .= get_opendb_lang_var ( 'not_applicable' );
		}
		$rowcontents .= "</td>";
		
		if (get_opendb_config_var ( 'borrow', 'duration_support' ) !== FALSE) {
			// 'Due Back' functionality.	
			$rowcontents .= "\n<td>";
			if (is_item_borrowed ( $item_r ['item_id'], $item_r ['instance_no'] )) {
				$due_date = fetch_item_duedate_timestamp ( $item_r ['item_id'], $item_r ['instance_no'] );
				if (strlen ( $due_date ) > 0)
					$rowcontents .= get_localised_timestamp ( get_opendb_config_var ( 'borrow', 'date_mask' ), $due_date );
				else
					$rowcontents .= get_opendb_lang_var ( 'undefined' );
			} else if (is_numeric ( $item_r ['borrow_duration'] )) {
				$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r ( $item_r ['s_item_type'], 'DURATION' );
				$rowcontents .= get_item_display_field ( $item_r, $duration_attr_type_r, $item_r ['borrow_duration'], FALSE );
			} else {
				$rowcontents .= get_opendb_lang_var ( 'undefined' );
			}
			$rowcontents .= "</td>";
		}
	}
	
	$rowcontents .= "\n</tr>";
	return $rowcontents;
}

function get_related_items_block($item_r, $HTTP_VARS, &$instance_info_links_r) {
	$buffer = '<div id="relatedItems">';
	
	$relatedChildrenTable = get_related_items_listing ( $item_r, $HTTP_VARS, RELATED_CHILDREN_MODE );
	$relatedParentsTable = get_related_items_listing ( $item_r, $HTTP_VARS, RELATED_PARENTS_MODE );
	
	if ($relatedChildrenTable != NULL) {
		$buffer .= "<h3>" . get_opendb_lang_var ( 'related_item(s)' ) . "</h3>";
		$buffer .= $relatedChildrenTable;
	}
	
	if ((is_user_granted_permission ( PERM_ITEM_OWNER ) && get_opendb_session_var ( 'user_id' ) === $item_r ['owner_id']) || is_user_granted_permission ( PERM_ITEM_ADMIN )) {
		if (get_opendb_config_var ( 'item_input', 'related_item_support' ) !== FALSE && is_numeric ( $item_r ['item_id'] ) && is_numeric ( $item_r ['instance_no'] )) {
			array_push ( $instance_info_links_r, array (
					'url' => "item_input.php?op=site-add&parent_item_id=" . $item_r ['item_id'] . "&parent_instance_no=" . $item_r ['instance_no'] . "&owner_id=" . $item_r ['owner_id'],
					'text' => get_opendb_lang_var ( 'add_related_item' ) ) );
		}
	}
	
	if ($relatedParentsTable != NULL) {
		$buffer .= "<h3>" . get_opendb_lang_var ( 'related_parent_item(s)' ) . "</h3>";
		$buffer .= $relatedParentsTable;
	}
	
	$buffer .= "</div>";
	
	return $buffer;
}

function get_related_items_listing($item_r, $HTTP_VARS, $related_mode) {
	global $PHP_SELF;
	
	$buffer = '';
	
	$results = fetch_item_instance_relationship_rs ( $item_r ['item_id'], $item_r ['instance_no'], $related_mode );
	if ($results) {
		$listingObject = new HTML_Listing ( $PHP_SELF, $HTTP_VARS );
		$listingObject->setBufferOutput ( TRUE );
		$listingObject->setNoRowsMessage ( get_opendb_lang_var ( 'no_items_found' ) );
		$listingObject->setShowItemImages ( TRUE );
		$listingObject->setIncludeFooter ( FALSE );
		
		$listingObject->addHeaderColumn ( get_opendb_lang_var ( 'type' ), 'type', FALSE );
		$listingObject->addHeaderColumn ( get_opendb_lang_var ( 'title' ), 'title', FALSE );
		$listingObject->addHeaderColumn ( get_opendb_lang_var ( 'action' ), 'action', FALSE );
		$listingObject->addHeaderColumn ( get_opendb_lang_var ( 'status' ), 'status', FALSE );
		$listingObject->addHeaderColumn ( get_opendb_lang_var ( 'status_comment' ), 'status_comment', FALSE );
		$listingObject->addHeaderColumn ( get_opendb_lang_var ( 'category' ), 'category', FALSE );
		
		$listingObject->startListing ( NULL );
		
		while ( $related_item_r = db_fetch_assoc ( $results ) ) {
			$listingObject->startRow ();
			
			$listingObject->addItemTypeImageColumn ( $related_item_r ['s_item_type'] );
			
			$listingObject->addTitleColumn ( $related_item_r );
			
			$action_links_rs = NULL;
			
			if ((is_user_granted_permission ( PERM_ITEM_OWNER ) && get_opendb_session_var ( 'user_id' ) === $item_r ['owner_id']) || is_user_granted_permission ( PERM_ITEM_ADMIN )) {
				$action_links_rs [] = array (
						'url' => 'item_input.php?op=edit&item_id=' . $related_item_r ['item_id'] . '&instance_no=' . $related_item_r ['instance_no'],
						'img' => 'edit.gif',
						'text' => get_opendb_lang_var ( 'edit' ) );
				
				if (get_opendb_config_var ( 'listings', 'show_refresh_actions' ) && is_item_legal_site_type ( $related_item_r ['s_item_type'] )) {
					$action_links_rs [] = array (
							'url' => 'item_input.php?op=site-refresh&item_id=' . $related_item_r ['item_id'] . '&instance_no=' . $related_item_r ['instance_no'],
							'img' => 'refresh.gif',
							'text' => get_opendb_lang_var ( 'refresh' ) );
				}
				
				$action_links_rs [] = array (
						'url' => 'item_input.php?op=delete&item_id=' . $related_item_r ['item_id'] . '&instance_no=' . $related_item_r ['instance_no'] . '&parent_item_id=' . $item_r ['item_id'] . '&parent_instance_no=' . $item_r ['instance_no'],
						'img' => 'delete.gif',
						'text' => get_opendb_lang_var ( 'delete' ) );
				
				$action_links_rs [] =  array (
						'url' => 'item_input.php?op=delete-relation&item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'] . '&parent_item_id=' . $related_item_r ['item_id'] . '&parent_instance_no=' . $related_item_r ['instance_no'],
						'img' => 'delete.gif',
						'text' => get_opendb_lang_var('delete_relationship') );
			}
			
			$listingObject->addActionColumn ( $action_links_rs );
			
			$status_type_r = fetch_status_type_r ( $related_item_r ['s_status_type'] );
			
			$listingObject->addThemeImageColumn ( $status_type_r ['img'], $status_type_r ['description'], $status_type_r ['description'], 			//title
					's_status_type' ); //type
			
			// If a comment is allowed and defined, add it in.
			if ($status_type_r ['status_comment_ind'] == 'Y' || get_opendb_session_var ( 'user_id' ) === $related_item_r ['owner_id'] || is_user_granted_permission ( PERM_ITEM_ADMIN )) {
				// support newlines in this field
				$listingObject->addColumn ( nl2br ( $related_item_r ['status_comment'] ) );
			} else {
				$listingObject->addColumn ( get_opendb_lang_var ( 'not_applicable' ) );
			}
			
			$attribute_type_r = fetch_sfieldtype_item_attribute_type_r ( $related_item_r ['s_item_type'], 'CATEGORY' );
			if (is_array ( $attribute_type_r )) {
				if ($attribute_type_r ['lookup_attribute_ind'] === 'Y')
					$attribute_val = fetch_attribute_val_r ( $related_item_r ['item_id'], $related_item_r ['instance_no'], $attribute_type_r ['s_attribute_type'], $attribute_type_r ['order_no'] );
				else
					$attribute_val = fetch_attribute_val ( $related_item_r ['item_id'], $related_item_r ['instance_no'], $attribute_type_r ['s_attribute_type'], $attribute_type_r ['order_no'] );
				
				$listingObject->addAttrDisplayColumn ( $related_item_r, $attribute_type_r, $attribute_val );
			}
			
			$listingObject->endRow ();
		}
		
		$listingObject->endListing ();
		
		$buffer = & $listingObject->getContents ();
		
		unset ( $listingObject );
		
		return $buffer;
	} else {
		return NULL;
	}
}
?>
