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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");
include_once("./lib/datetime.php");
include_once("./lib/widgets.php");
include_once("./lib/http.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/item.php");
include_once("./lib/email.php");
include_once("./lib/review.php");
include_once("./lib/listutils.php");
include_once("./lib/sortutils.php");
include_once("./lib/HTML_Listing.class.php");
include_once("./lib/TitleMask.class.php");

if (is_site_enabled()) {
	if (is_opendb_valid_session()) {
		if (get_opendb_config_var('borrow', 'enable') !== FALSE) {
			if (is_user_granted_permission(PERM_USER_BORROWER)) {
				$listingObject = new HTML_Listing($PHP_SELF, $HTTP_VARS);
				$listingObject->setNoRowsMessage(get_opendb_lang_var('no_records_found'));

				$show_listings = TRUE;
				$checkbox_column = FALSE;

				if ($HTTP_VARS['op'] == 'my_borrowed') {//all titles that the person has actually borrowed from others.
 					$page_title = get_opendb_lang_var('my_borrowed_items');
					if (is_numeric($listingObject->getItemsPerPage())) {
						$listingObject->setTotalItems(fetch_my_borrowed_item_cnt(get_opendb_session_var('user_id')));
						if ($listingObject->getTotalItemCount() > 0) {
							$result = fetch_my_borrowed_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
						}
					} else {
						$result = fetch_my_borrowed_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
					}
				} else if ($HTTP_VARS['op'] == 'all_borrowed' && is_user_granted_permission(PERM_ADMIN_BORROWER)) {
					$page_title = get_opendb_lang_var('items_borrowed');

					if (is_numeric($listingObject->getItemsPerPage())) {
						$listingObject->setTotalItems(fetch_all_borrowed_item_cnt());
						if ($listingObject->getTotalItemCount() > 0) {
							$result = fetch_all_borrowed_item_rs($listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
						}
					} else {
						$result = fetch_all_borrowed_item_rs($listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
					}
				} else if ($HTTP_VARS['op'] == 'all_reserved' && is_user_granted_permission(PERM_ADMIN_BORROWER)) {
					$page_title = get_opendb_lang_var('items_reserved');
					if (is_numeric($listingObject->getItemsPerPage())) {
						$listingObject->setTotalItems(fetch_all_reserved_item_cnt());
						if ($listingObject->getTotalItemCount() > 0) {
							$result = fetch_all_reserved_item_rs($listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
						}
					} else {
						$result = fetch_all_reserved_item_rs($listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
					}
				} else if ($HTTP_VARS['op'] == 'my_reserved') { //all titles that the person has reserved from others.
 					$page_title = get_opendb_lang_var('my_reserved_items');

					if (is_numeric($listingObject->getItemsPerPage())) {
						$listingObject->setTotalItems(fetch_my_reserved_item_cnt(get_opendb_session_var('user_id')));
						if ($listingObject->getTotalItemCount() > 0) {
							$checkbox_column = TRUE;

							$result = fetch_my_reserved_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
						}
					} else {
						$result = fetch_my_reserved_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
					}
				} else if ($HTTP_VARS['op'] == 'owner_borrowed') {//all titles the owner currently has lent out to others.
 					$page_title = get_opendb_lang_var('check_in_item(s)');

					if (is_numeric($listingObject->getItemsPerPage())) {
						$listingObject->setTotalItems(fetch_owner_borrowed_item_cnt(get_opendb_session_var('user_id')));
						if ($listingObject->getTotalItemCount() > 0) {
							$checkbox_column = TRUE;

							$result = fetch_owner_borrowed_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
						}
					} else {
						$result = fetch_owner_borrowed_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
					}
				} else if ($HTTP_VARS['op'] == 'owner_reserved') {//all titles the owner currently has reservations for.
 					$page_title = get_opendb_lang_var('check_out_item(s)');

					if (is_numeric($listingObject->getItemsPerPage())) {
						$listingObject->setTotalItems(fetch_owner_reserved_item_cnt(get_opendb_session_var('user_id')));
						if ($listingObject->getTotalItemCount() > 0) {
							$checkbox_column = TRUE;

							$result = fetch_owner_reserved_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
						}
					} else {
						$result = fetch_owner_reserved_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy());
					}
				} else if ($HTTP_VARS['op'] == 'my_item_history') {
					$show_listings = FALSE;

					$item_r = fetch_item_instance_r($HTTP_VARS['item_id'], $HTTP_VARS['instance_no']);
					if (is_not_empty_array($item_r)) {
						$footer_links_r[] = array('url' => "item_display.php?item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
						if (is_opendb_session_var('listing_url_vars')) {
							$footer_links_r[] = array('url' => "listings.php?" . get_url_string(get_opendb_session_var('listing_url_vars')), 'text' => get_opendb_lang_var('back_to_listing'));
						}

						// Cannot view item history, unless you are admin, or own the item.
						if (is_user_owner_of_item($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id')) || is_user_granted_permission(PERM_ADMIN_BORROWER)) {
							$show_listings = TRUE;

							$titleMaskCfg = new TitleMask('item_display');
							$page_title = get_opendb_lang_var('history_for_title', 'display_title', $titleMaskCfg->expand_item_title($item_r));
							$page_image = get_item_image($item_r['s_item_type'], $item_r['item_id']);

							if (is_numeric($listingObject->getItemsPerPage())) {
								$listingObject->setTotalItems(fetch_item_instance_history_cnt($item_r['item_id'], $item_r['instance_no']));
								if ($listingObject->getTotalItemCount() > 0) {
									$result = fetch_item_instance_history_rs($item_r['item_id'], $item_r['instance_no'], $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
								}
							} else {
								$result = fetch_item_instance_history_rs($item_r['item_id'], $item_r['instance_no'], $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
							}
						} else {
							opendb_not_authorised_page();
						}
					} else {
						echo _theme_header(get_opendb_lang_var('item_not_found'));
						echo ("<p class=\"error\">" . get_opendb_lang_var('item_not_found') . "</p>");
						echo (_theme_footer());
					}
				} else if ($HTTP_VARS['op'] == 'my_history') {
					if (is_user_valid($HTTP_VARS['uid']) && $HTTP_VARS['uid'] !== get_opendb_session_var('user_id') && is_user_granted_permission(PERM_ADMIN_BORROWER)) {
						$page_title = get_opendb_lang_var('borrower_history_for_fullname', array('fullname' => fetch_user_name($HTTP_VARS['uid']), 'user_id' => $HTTP_VARS['uid']));

						if (is_numeric($listingObject->getItemsPerPage())) {
							$listingObject->setTotalItems(fetch_my_history_item_cnt($HTTP_VARS['uid']));
							if ($listingObject->getTotalItemCount() > 0) {
								$result = fetch_my_history_item_rs($HTTP_VARS['uid'], $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
							}
						} else {
							$result = fetch_my_history_item_rs($HTTP_VARS['uid'], $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
						}
					} else {
						$page_title = get_opendb_lang_var('my_history');

						if (is_numeric($listingObject->getItemsPerPage())) {
							$listingObject->setTotalItems(fetch_my_history_item_cnt(get_opendb_session_var('user_id')));
							if ($listingObject->getTotalItemCount() > 0) {
								$result = fetch_my_history_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
							}
						} else {
							$result = fetch_my_history_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
						}
					}
				} else if (get_opendb_config_var('borrow', 'reserve_basket') !== FALSE && ($HTTP_VARS['op'] == 'my_reserve_basket' || $HTTP_VARS['op'] == 'update_my_reserve_basket' || $HTTP_VARS['op'] == 'delete_from_my_reserve_basket')) {
					if ($HTTP_VARS['op'] == 'update_my_reserve_basket' || $HTTP_VARS['op'] == 'delete_from_my_reserve_basket') {
						// We might be reserving a single item only - item_display.php would initiate this operation
						if (is_empty_array($HTTP_VARS['item_id_instance_no']) && is_numeric($HTTP_VARS['item_id']) && is_numeric($HTTP_VARS['instance_no'])) {
							// Set it up so it looks as if an item was previously checked, but has now been unchecked! - called from item_display.php
							if ($HTTP_VARS['op'] == 'delete_from_my_reserve_basket') {
								$sequence_number = fetch_borrowed_item_seq_no($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'], 'T', get_opendb_session_var('user_id'));
								if ($sequence_number !== FALSE) {
									delete_cart_item($sequence_number);
								}
							} else if (!is_item_in_reserve_basket($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'], get_opendb_session_var('user_id'))) { // else add item to session array. 
 								insert_cart_item($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'], get_opendb_session_var('user_id'));
							}
						} else if ($HTTP_VARS['op'] == 'update_my_reserve_basket' && is_not_empty_array($HTTP_VARS['item_id_instance_no'])) { // initiated from listings.php page!
							foreach ($HTTP_VARS['item_id_instance_no'] as $item_id_instance_no ) {
								$item_r = get_item_id_and_instance_no($item_id_instance_no);
								if (!is_item_in_reserve_basket($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id'))) { // else add item to session array. 
 									insert_cart_item($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id'));
								}
							}
						} else if ($HTTP_VARS['op'] == 'delete_from_my_reserve_basket' && is_not_empty_array($HTTP_VARS['sequence_number'])) {
							foreach ( $HTTP_VARS['sequence_number'] as $sequence_number ) {
								delete_cart_item($sequence_number);
							}
						}
					}

					$page_title = get_opendb_lang_var('item_reserve_list');

					if (is_numeric($listingObject->getItemsPerPage())) {
						$listingObject->setTotalItems(fetch_my_basket_item_cnt(get_opendb_session_var('user_id')));
						if ($listingObject->getTotalItemCount() > 0) {
							$checkbox_column = TRUE;

							$result = fetch_my_basket_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder(), $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
						}
					} else {
						$result = fetch_my_basket_item_rs(get_opendb_session_var('user_id'), $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());
					}

					// Set it explicitly here.
					$HTTP_VARS['op'] = 'my_reserve_basket';

					if (is_exists_item_instance($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'])) {
						$footer_links_r[] = array('url' => "item_display.php?item_id=" . $HTTP_VARS['item_id'] . "&instance_no=" . $HTTP_VARS['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
					}

					if (is_opendb_session_var('listing_url_vars')) {
						$footer_links_r[] = array('url' => "listings.php?" . get_url_string(get_opendb_session_var('listing_url_vars')), 'text' => get_opendb_lang_var('back_to_listing'));
					}
				} else if ($HTTP_VARS['op'] == 'admin_history') {
					echo (_theme_header(get_opendb_lang_var('borrower_history')));
					echo ("<h2>" . get_opendb_lang_var('borrower_history') . "</h2>");

					echo ("\n<form action=\"$PHP_SELF\" method=\"GET\">");
					echo ("\n<input type=\"hidden\" name=\"op\" value=\"my_history\">");

					echo ("\n<table>");
					$results = fetch_user_rs(PERM_USER_BORROWER, INCLUDE_ROLE_PERMISSIONS, INCLUDE_CURRENT_USER, EXCLUDE_DEACTIVATED_USER, "fullname", "ASC");
					echo (format_field(get_opendb_lang_var('borrower'), custom_select('uid', $results, '%fullname% (%user_id%)', 1, get_opendb_session_var('user_id'), 'user_id')));

					echo ("</table>");

					echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">");

					echo ("</form>");

					echo (_theme_footer());

					$show_listings = FALSE;
				} else {
					opendb_operation_not_available();

					$show_listings = FALSE;
				}

				if ($show_listings) {
					echo (_theme_header($page_title));
					echo ('<h2>' . $page_title . ' ' . ($page_image ?? '') . '</h2>');

					$listingObject->startListing($page_title);

					if ($checkbox_column !== FALSE) {
						$listingObject->addHeaderColumn(NULL, 'sequence_number', FALSE, 'checkbox');
					}

					if ($HTTP_VARS['op'] != 'my_item_history') {
						$listingObject->addHeaderColumn(get_opendb_lang_var('type'), 's_item_type');
						$listingObject->addHeaderColumn(get_opendb_lang_var('title'), 'title');
					}

					if ($HTTP_VARS['op'] == 'my_reserve_basket' || $HTTP_VARS['op'] == 'my_borrowed' || $HTTP_VARS['op'] == 'my_reserved' || $HTTP_VARS['op'] == 'my_history' || $HTTP_VARS['op'] == 'all_borrowed' || $HTTP_VARS['op'] == 'all_reserved') {
						$listingObject->addHeaderColumn(get_opendb_lang_var('owner'), 'owner');
					}

					if ($HTTP_VARS['op'] == 'owner_reserved' || $HTTP_VARS['op'] == 'owner_borrowed' || $HTTP_VARS['op'] == 'my_item_history' || (get_opendb_config_var('borrow', 'include_borrower_column') !== FALSE && ($HTTP_VARS['op'] == 'all_borrowed' || $HTTP_VARS['op'] == 'all_reserved'))) {
						$listingObject->addHeaderColumn(get_opendb_lang_var('borrower'), 'borrower');
					}

					if ($HTTP_VARS['op'] == 'owner_reserved' || $HTTP_VARS['op'] == 'my_reserved' || $HTTP_VARS['op'] == 'all_reserved' || $HTTP_VARS['op'] == 'my_item_history' || $HTTP_VARS['op'] == 'my_history') {
						$listingObject->addHeaderColumn(get_opendb_lang_var('borrow_status'));
					}

					if ($HTTP_VARS['op'] == 'my_item_history' || $HTTP_VARS['op'] == 'my_history') {
						$listingObject->addHeaderColumn(get_opendb_lang_var('borrow_date') . " /\n" . get_opendb_lang_var('reserve_date'));
						$listingObject->addHeaderColumn(get_opendb_lang_var('return_date') . " /\n" . get_opendb_lang_var('due_date'));
						$listingObject->addHeaderColumn(get_opendb_lang_var('total_duration') . " /\n" . get_opendb_lang_var('borrow_duration'));
					} else if ($HTTP_VARS['op'] == 'owner_reserved' || $HTTP_VARS['op'] == 'my_reserved' || $HTTP_VARS['op'] == 'all_reserved') {
						$listingObject->addHeaderColumn(get_opendb_lang_var('reserve_date'), 'reserve_date');
					}

					if (get_opendb_config_var('borrow', 'duration_support')) {
						if ($HTTP_VARS['op'] == 'my_history' || $HTTP_VARS['op'] == 'my_item_history')
							$listingObject->addHeaderColumn(get_opendb_lang_var('overdue_duration'));
						else if ($HTTP_VARS['op'] == 'my_borrowed' || $HTTP_VARS['op'] == 'all_borrowed' || $HTTP_VARS['op'] == 'owner_borrowed')
							$listingObject->addHeaderColumn(get_opendb_lang_var('due_date'), 'due_date');
						else
							// reserved!
							$listingObject->addHeaderColumn(get_opendb_lang_var('borrow_duration'));
					}

					$listingObject->addHeaderColumn(get_opendb_lang_var('more_information'));

					// If mysql resultset or static $item_reservation_rs array defined, we can continue.
					if (isset($result)) {
						while ($borrowed_item_r = db_fetch_assoc($result)) {
							$listingObject->startRow();

							$status_type_r = fetch_status_type_r($borrowed_item_r['s_status_type']);

							if ($checkbox_column !== FALSE) {
								if (($HTTP_VARS['op'] == 'my_reserve_basket' || $HTTP_VARS['op'] == 'my_reserved' || $HTTP_VARS['op'] == 'owner_borrowed') || ($HTTP_VARS['op'] == 'owner_reserved' && !is_item_borrowed($borrowed_item_r['item_id'], $borrowed_item_r['instance_no']))) {
									$listingObject->addCheckboxColumn($borrowed_item_r['sequence_number'], FALSE);
								} else {
									$listingObject->addColumn();
								}
							}

							if ($HTTP_VARS['op'] != 'my_item_history') {
								// Type
								$listingObject->addItemTypeImageColumn($borrowed_item_r['s_item_type']);

								if ($HTTP_VARS['op'] == 'my_borrowed' || $HTTP_VARS['op'] == 'my_history') {
									$listingObject->addTitleColumn($borrowed_item_r);
								} else {
									$listingObject->addTitleColumn($borrowed_item_r);
								}
							}

							// Owner/Borrower
							if ($HTTP_VARS['op'] == 'my_reserve_basket' || $HTTP_VARS['op'] == 'my_borrowed' || $HTTP_VARS['op'] == 'my_reserved' || $HTTP_VARS['op'] == 'my_history' || $HTTP_VARS['op'] == 'all_borrowed' || $HTTP_VARS['op'] == 'all_reserved') {
								$listingObject->addUserNameColumn($borrowed_item_r['owner_id'], array('bi_sequence_number' => $borrowed_item_r['sequence_number']));
							}

							if ($HTTP_VARS['op'] == 'owner_reserved' || $HTTP_VARS['op'] == 'owner_borrowed' || $HTTP_VARS['op'] == 'my_item_history'
									|| (get_opendb_config_var('borrow', 'include_borrower_column') !== FALSE && ($HTTP_VARS['op'] == 'all_borrowed' || $HTTP_VARS['op'] == 'all_reserved'))) {
								$listingObject->addUserNameColumn($borrowed_item_r['borrower_id'], array('bi_sequence_number' => $borrowed_item_r['sequence_number']));
							}

							// Checked Out status!
							if ($HTTP_VARS['op'] == 'owner_reserved' || $HTTP_VARS['op'] == 'my_reserved' || $HTTP_VARS['op'] == 'all_reserved') {
								if (is_item_borrowed($borrowed_item_r['item_id'], $borrowed_item_r['instance_no'])) {
									$listingObject->addThemeImageColumn('borrowed.gif', get_opendb_lang_var('borrowed'), get_opendb_lang_var('borrowed'), //title
											'borrowed_item');
								} else {
									$listingObject->addThemeImageColumn('reserved.gif', get_opendb_lang_var('reserved'), get_opendb_lang_var('reserved'), //title
											'borrowed_item');
								}
							} else if ($HTTP_VARS['op'] == 'my_item_history' || $HTTP_VARS['op'] == 'my_history') {
								if ($borrowed_item_r['status'] == 'X') {
									$listingObject->addColumn(get_opendb_lang_var('cancelled'));
								} else if ($borrowed_item_r['status'] == 'R') {
									$listingObject->addThemeImageColumn('reserved.gif', get_opendb_lang_var('reserved'), get_opendb_lang_var('reserved'), //title
											'borrowed_item');
								} else if ($borrowed_item_r['status'] == 'C') {
									$listingObject->addColumn(get_opendb_lang_var('checked_in'));
								} else if ($borrowed_item_r['status'] == 'B') {
									//$listingObject->addColumn(get_opendb_lang_var('checked_out'));
									$listingObject->addThemeImageColumn('borrowed.gif', get_opendb_lang_var('borrowed'), get_opendb_lang_var('borrowed'), //title
											'borrowed_item');
								}
							}

							// Borrowed / Due Date / Borrow Duration / (Returned & Total Days & Overdue Days)
							if ($HTTP_VARS['op'] == 'my_history' || $HTTP_VARS['op'] == 'my_item_history') {
								// borrow date
								if ($borrowed_item_r['status'] != 'X') {
									if (strlen($borrowed_item_r['borrow_date']) > 0)
										$listingObject->addColumn(get_localised_timestamp(get_opendb_config_var('borrow', 'date_mask'), $borrowed_item_r['borrow_date']));
									else
										$listingObject->addColumn(get_opendb_lang_var('undefined'));
								} else {
									$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
								}

								// Returned / Due Date
								if ($borrowed_item_r['status'] == 'C' && strlen($borrowed_item_r['return_date']) > 0) {
									$listingObject->addColumn(get_localised_timestamp(get_opendb_config_var('borrow', 'date_mask'), $borrowed_item_r['return_date']));
								} else if ($borrowed_item_r['status'] == 'X') {
									$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
								} else if ($borrowed_item_r['status'] == 'B') {
									if (get_opendb_config_var('borrow', 'duration_support') && strlen($borrowed_item_r['due_date']) > 0)
										$listingObject->addColumn(get_localised_timestamp(get_opendb_config_var('borrow', 'date_mask'), $borrowed_item_r['due_date']));
									else
										$listingObject->addColumn(get_opendb_lang_var('undefined'));
								} else if ($borrowed_item_r['status'] == 'R') {
									$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
								} else {
									$listingObject->addColumn();
								}

								// Total Duration / Borrow Duration
								if ($borrowed_item_r['status'] == 'C' && is_numeric($borrowed_item_r['total_duration'])) {
									$listingObject->addColumn($borrowed_item_r['total_duration']);
								} else if ($borrowed_item_r['status'] == 'X') {
									$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
								} else if ($borrowed_item_r['status'] == 'B' && is_numeric($borrowed_item_r['calc_total_duration'])) {
									$listingObject->addColumn($borrowed_item_r['calc_total_duration']);
								} else if ($borrowed_item_r['status'] == 'R') {
									$borrow_duration = NULL;
									if (is_numeric($borrowed_item_r['ii_borrow_duration']) && $borrowed_item_r['ii_borrow_duration'] > 0) {
										$borrow_duration = $borrowed_item_r['ii_borrow_duration'];
									}

									if (is_numeric($borrow_duration)) {
										$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r($borrowed_item_r['s_item_type'], 'DURATION');
										$listingObject->addDisplayColumn($duration_attr_type_r['s_attribute_type'], NULL, //$prompt
												$duration_attr_type_r['display_type'], $borrow_duration);
									} else {
										$listingObject->addColumn(get_opendb_lang_var('undefined'));
									}
								} else {
									$listingObject->addColumn(get_opendb_lang_var('unknown'));
								}
							}

							if ($HTTP_VARS['op'] == 'owner_reserved' || $HTTP_VARS['op'] == 'my_reserved' || $HTTP_VARS['op'] == 'all_reserved') {
								$listingObject->addColumn(get_localised_timestamp(get_opendb_config_var('borrow', 'datetime_mask'), $borrowed_item_r['reserve_date']));
							}

							if (get_opendb_config_var('borrow', 'duration_support')) {
								if ($HTTP_VARS['op'] == 'my_history' || $HTTP_VARS['op'] == 'my_item_history') {
									// Overdue Days
									if ($borrowed_item_r['status'] == 'X')
										$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
									else if ($borrowed_item_r['status'] == 'C' || $borrowed_item_r['status'] == 'B') {
										if (is_numeric($borrowed_item_r['borrow_duration']) && $borrowed_item_r['borrow_duration'] > 0 && is_numeric($borrowed_item_r['total_duration'])) {
											if ($borrowed_item_r['total_duration'] > $borrowed_item_r['borrow_duration'])
												$listingObject->addColumn($borrowed_item_r['total_duration'] - $borrowed_item_r['borrow_duration']);
											else
												$listingObject->addColumn('0');
										} else if (is_numeric($borrowed_item_r['borrow_duration']) && $borrowed_item_r['borrow_duration'] > 0 && is_numeric($borrowed_item_r['calc_total_duration'])) {
											if ($borrowed_item_r['calc_total_duration'] > $borrowed_item_r['borrow_duration'])
												$listingObject->addColumn($borrowed_item_r['calc_total_duration'] - $borrowed_item_r['borrow_duration']);
											else
												$listingObject->addColumn('0');
										} else {
											$listingObject->addColumn(get_opendb_lang_var('unknown'));
										}
									} else { // reserved - item_history
 										$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
									}
								} else if ($HTTP_VARS['op'] == 'my_borrowed' || $HTTP_VARS['op'] == 'all_borrowed' || $HTTP_VARS['op'] == 'owner_borrowed') {
									if (strlen($borrowed_item_r['due_date']) > 0)
										$listingObject->addColumn(get_localised_timestamp(get_opendb_config_var('borrow', 'date_mask'), $borrowed_item_r['due_date']));
									else
										$listingObject->addColumn(get_opendb_lang_var('undefined'));
								} else { // Reserved
 									if (is_numeric($borrowed_item_r['borrow_duration']) && $borrowed_item_r['borrow_duration'] > 0) {
										$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r($borrowed_item_r['s_item_type'], 'DURATION');
										$listingObject->addDisplayColumn($duration_attr_type_r['s_attribute_type'], NULL, $duration_attr_type_r['display_type'], $borrowed_item_r['borrow_duration']);
									} else {
										$listingObject->addColumn(get_opendb_lang_var('undefined'));
									}
								}
							}

							$listingObject->addColumn(nl2br($borrowed_item_r['more_information']));

							$listingObject->endRow();
						}// End of while

						@db_free_result($result);
					}

					$listingObject->endListing();

					if ($listingObject->isCheckboxColumns() > 0) {
						if ($HTTP_VARS['op'] == 'my_reserve_basket') {
							$checkbox_action_rs[] = array('action' => $PHP_SELF, 'op' => 'delete_from_my_reserve_basket', 'link' => get_opendb_lang_var('delete_from_reserve_list'));
							$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'reserve', 'link' => get_opendb_lang_var('reserve_item(s)'));
							$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'reserve_all', 'checked' => FALSE, 'link' => get_opendb_lang_var('reserve_all_item(s)'));
						} else if ($HTTP_VARS['op'] == 'my_reserved') {
							$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'cancel_reserve', 'link' => get_opendb_lang_var('cancel_reservation(s)'));
						} else if ($HTTP_VARS['op'] == 'owner_borrowed') {
							$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'check_in', 'link' => get_opendb_lang_var('check_in_item(s)'));

							if (is_valid_opendb_mailer()) {
								$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'reminder', 'link' => get_opendb_lang_var('send_reminder(s)'));
							}

							if (get_opendb_config_var('borrow', 'duration_support') !== FALSE) {
								$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'extension', 'link' => get_opendb_lang_var('borrow_duration_extension(s)'));
							}
						} else if ($HTTP_VARS['op'] == 'owner_reserved') {
							$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'check_out', 'link' => get_opendb_lang_var('check_out_item(s)'));
							$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'cancel_reserve', 'link' => get_opendb_lang_var('cancel_reservation(s)'));
						}

						echo (format_checkbox_action_links('sequence_number', get_opendb_lang_var('no_items_checked'), $checkbox_action_rs));
					}

					echo (format_help_block($listingObject->getHelpEntries()));

					echo ("<ul class=\"listingControls\">");
					if (get_opendb_config_var('listings', 'allow_override_show_item_image') !== FALSE) {
						echo ("<li>" . getToggleControl($PHP_SELF, $HTTP_VARS, get_opendb_lang_var('show_item_image'), 'show_item_image', ifempty($HTTP_VARS['show_item_image'], get_opendb_config_var('listings', 'show_item_image') == TRUE ? 'Y' : 'N')) . "</li>");
					}
					echo ("<li>" . getItemsPerPageControl($PHP_SELF, $HTTP_VARS) . "</li>");
					echo ("</ul>");

					echo ("<p class=\"listingDate\">" . get_opendb_lang_var('listing_generated', 'datetime', get_localised_timestamp(get_opendb_config_var('listings', 'print_listing_datetime_mask'))) . "</p>");

					echo (format_footer_links($footer_links_r ?? ''));
					echo (_theme_footer());

				}//end if($show_listings)		
			} else { //no guests allowed!
 				opendb_not_authorised_page(PERM_USER_BORROWER, $HTTP_VARS);
			}
		} else { //borrow functionality disabled.
 			echo (_theme_header(get_opendb_lang_var('borrow_not_supported')));
			echo ("<p class=\"error\">" . get_opendb_lang_var('borrow_not_supported') . "</p>");
			echo (_theme_footer());
		}
	} else {
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
} else { //if(is_site_enabled())
 	opendb_site_disabled();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
