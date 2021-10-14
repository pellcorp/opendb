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

include_once("./lib/email.php");
include_once("./lib/http.php");
include_once("./lib/utils.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/item.php");
include_once("./lib/datetime.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item_type.php");
include_once("./lib/widgets.php");
include_once("./lib/review.php");
include_once("./lib/listutils.php");
include_once("./lib/status_type.php");
include_once("./lib/HTML_Listing.class.php");
include_once("./lib/TitleMask.class.php");

function fetch_alt_item_id_attribute_type_r() {
	$attribute_type_r = fetch_attribute_type_r(ifempty(get_opendb_config_var('borrow.checkout', 'alt_id_attribute_type'), 'S_ITEM_ID'));

	// just for S_ITEM_ID s_attribute_type
	if ($attribute_type_r['input_type'] == 'hidden') {
		$attribute_type_r['input_type'] = 'number';
		$attribute_type_r['input_type_arg1'] = '10';
		$attribute_type_r['input_type_arg2'] = '10';
	}

	// need to be able to to checkout action - so this cannot be compulsory
	$attribute_type_r['compulsory_ind'] = 'N';

	return $attribute_type_r;
}

function display_borrower_form($HTTP_VARS) {
	echo ("\n<form action=\"$PHP_SELF\" method=\"GET\">");
	echo ("\n<input type=\"hidden\" name=\"op\" value=\"checkout\">");

	echo ("\n<table class=\"borrowerForm\">");
	if (get_opendb_config_var('borrow', 'admin_quick_checkout_borrower_lov') !== TRUE) {
		echo (get_input_field('borrower_id', NULL, // s_attribute_type
		get_opendb_lang_var('borrower'), "filtered(20,20,a-zA-Z0-9_.)", //input type.
		"Y", //compulsory!
		NULL, //value
		TRUE));
	} else {
		$results = fetch_user_rs(PERM_USER_BORROWER, INCLUDE_ROLE_PERMISSIONS, EXCLUDE_CURRENT_USER, EXCLUDE_DEACTIVATED_USER, 'fullname', 'ASC');
		if ($results) {
			echo (format_field(get_opendb_lang_var('borrower'), custom_select('borrower_id', $results, '%fullname% (%user_id%)', 1, NULL, 'user_id')));
		} else {
			echo (format_field(get_opendb_lang_var('borrower'), get_opendb_lang_var('no_records_found')));
		}

	}
	echo ("</table>");

	echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">");
	echo ("</form>");
}

function is_item_instance_in_array($item_instance_r, $item_instance_rs) {
	if (is_array($item_instance_rs)) {
		reset($item_instance_rs);
		foreach ($item_instance_rs as $instance_r) {
			if ($instance_r['item_id'] == $item_instance_r['item_id'] &&
				$instance_r['instance_no'] == $item_instance_r['instance_no']) {
				return TRUE;
			}
		}
	}

	return FALSE;
}

function get_new_altid_item_instance_rs($alt_item_id, $attribute_type_r, $altid_item_instance_rs) {
	$alt_item_id = trim($alt_item_id);
	if (strlen($alt_item_id)) {
		$attribute_type = ifempty(get_opendb_config_var('borrow.checkout', 'alt_id_attribute_type'), 'S_ITEM_ID');

		if ($attribute_type_r['s_field_type'] != 'ITEM_ID') {
			$results = fetch_item_instance_for_attribute_val_rs($alt_item_id, $attribute_type);
			if ($results) {
				$item_instance_rs = array();

				while ($item_instance_r = db_fetch_assoc($results)) {
					if (!is_item_instance_in_array($item_instance_r, $altid_item_instance_rs)) {
						$item_instance_rs[] = $item_instance_r;
					}
				}
				db_free_result($results);

				return $item_instance_rs;
			}
		} else {
			if (preg_match("/([0-9]+)\.([0-9]+)/", $alt_item_id, $matches) || preg_match("/([0-9]+)/", $alt_item_id, $matches)) {
				$item_id = $matches[1];
				$instance_no = ifempty($matches[2], '1');

				$item_instance_r = array('item_id' => $item_id, 'instance_no' => $instance_no);

				if (!is_item_instance_in_array($item_instance_r, $altid_item_instance_rs)) {
					$item_instance_r = fetch_item_instance_r($item_instance_r['item_id'], $item_instance_r['instance_no']);
					if (is_array($item_instance_r)) {
						$item_instance_rs[] = $item_instance_r;
						return $item_instance_rs;
					}
				}
			}
		}

		// item not found
		return FALSE;
	} else {
		return array();
	}
}

function get_decoded_item_instance_rs($op, $item_instance_list_r) {
	$item_instance_rs = array();
	if (is_array($item_instance_list_r)) {
		reset($item_instance_list_r);
		foreach($item_instance_list_r as $item_id_and_instance_no) {
			if (strlen($item_id_and_instance_no) > 0) {
				$item_instance_r = get_item_id_and_instance_no($item_id_and_instance_no);
				if (is_not_empty_array($item_instance_r)) {
					$item_instance_r = fetch_item_instance_r($item_instance_r['item_id'], $item_instance_r['instance_no']);
					if (is_array($item_instance_r)) {
						if ($op == 'checkin') {
							$sequence_number = fetch_borrowed_item_seq_no($item_instance_r['item_id'], $item_instance_r['instance_no'], 'B');
							if ($sequence_number != FALSE) {
								$item_instance_r['sequence_number'] = $sequence_number;
								$item_instance_rs[] = $item_instance_r;
							}
						} else {
							$item_instance_rs[] = $item_instance_r;
						}
					}
				}
			}
		}
	}
	return $item_instance_rs;
}

function get_encoded_item_instance_rs($checkout_item_instance_rs) {
	$encoded_item_instance_r = array();

	if (is_array($checkout_item_instance_rs)) {
		reset($checkout_item_instance_rs);
		foreach ($checkout_item_instance_rs as $item_instance_r) {
			$encoded_item_instance_r[] = $item_instance_r['item_id'] . '_' . $item_instance_r['instance_no'];
		}
	}

	return $encoded_item_instance_r;
}

function get_borrowed_item_sequence_number_r($altid_item_instance_rs) {
	if (is_array($altid_item_instance_rs)) {
		reset($altid_item_instance_rs);
		foreach ($altid_item_instance_rs as $altid_item_instance_r) {
			$sequence_number[] = $altid_item_instance_r['sequence_number'];
		}
	}
	return $sequence_number;
}

function update_altid_item_instance_rs($op, $alt_item_id, $attribute_type_r, $altid_item_instance_rs, &$errors) {
	if (!is_array($altid_item_instance_rs)) {
		$altid_item_instance_rs = array();
	}

	if (strlen($alt_item_id) > 0) {
		$item_instance_rs = get_new_altid_item_instance_rs($alt_item_id, $attribute_type_r, $altid_item_instance_rs);
		if (is_array($item_instance_rs)) {
			foreach ($item_instance_rs as $item_instance_r) {
				if ($item_instance_r['owner_id'] != $HTTP_VARS['borrower_id']) {
					if ($op == 'checkout') {
						if (is_item_instance_checkoutable($item_instance_r, $errors)) {
							$altid_item_instance_rs[] = $item_instance_r;
						}
					} else if ($op == 'checkin') {
						$sequence_number = fetch_borrowed_item_seq_no($item_instance_r['item_id'], $item_instance_r['instance_no'], 'B');
						if ($sequence_number != FALSE) {
							$item_instance_r['sequence_number'] = $sequence_number;
							$altid_item_instance_rs[] = $item_instance_r;
						} else {
							$errors[] = get_opendb_lang_var('item_is_not_checked_out');
						}
					}
				} else {
					$errors[] = get_opendb_lang_var('user_is_owner_of_item');
				}
			}
		} else {
			$errors[] = get_opendb_lang_var('item_not_found');
		}
	}

	return $altid_item_instance_rs;
}

function is_item_instance_checkoutable($item_instance_r, &$errors) {
	if (!is_item_borrowed($item_instance_r['item_id'], $item_instance_r['instance_no'])) {
		$status_type_r = fetch_status_type_r($item_instance_r['s_status_type']);
		if ($status_type_r['borrow_ind'] == 'Y') {
			return TRUE;
		} else if (is_array($status_type_r)) {
			$errors[] = get_opendb_lang_var('s_status_type_items_cannot_be_borrowed', 's_status_type_desc', $status_type_r['description']);
		} else {
			$errors[] = get_opendb_lang_var('invalid_s_status_type', 's_status_type', $item_instance_r['s_status_type']);

		}
	} else {
		$errors[] = get_opendb_lang_var('item_is_already_checked_out');
	}

	//else
	return FALSE;
}

function validate_borrower_id($borrower_id, &$errors) {
	if (strlen($borrower_id) > 0) {
		if (!is_user_active($borrower_id)) {
			$errors[] = get_opendb_lang_var('invalid_borrower_user', 'user_id', $HTTP_VARS['borrower_id']);
			return FALSE;
		} else if (!is_user_granted_permission(PERM_USER_BORROWER, $borrower_id)) {
			$errors[] = get_opendb_lang_var('user_must_be_borrower', 'user_id', $HTTP_VARS['borrower_id']);
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		return FALSE;
	}
}

if (is_site_enabled()) {
	if (is_opendb_valid_session()) {
		if (is_user_granted_permission(PERM_ADMIN_QUICK_CHECKOUT)) {
			if (get_opendb_config_var('borrow', 'enable') !== FALSE) {
				if ($HTTP_VARS['op'] == 'checkout' || $HTTP_VARS['op'] == 'checkin') {
					if ($HTTP_VARS['op'] == 'checkout' && !validate_borrower_id($HTTP_VARS['borrower_id'], $errors)) {
						echo _theme_header(get_opendb_lang_var('quick_check_out'));
						echo ("<h2>" . get_opendb_lang_var('quick_check_out') . "</h2>");

						if (is_array($errors) > 0)
							echo (format_error_block($errors));

						display_borrower_form($HTTP_VARS);
					} else {
						if ($HTTP_VARS['op'] == 'checkout')
							$page_title = get_opendb_lang_var('quick_check_out_for_fullname', array('user_id' => $HTTP_VARS['borrower_id'], 'fullname' => fetch_user_name($HTTP_VARS['borrower_id'])));
						else if ($HTTP_VARS['op'] == 'checkin')
							$page_title = get_opendb_lang_var('quick_check_in');

						echo (_theme_header($page_title));
						echo ('<h2>' . $page_title . ' ' . $page_image . '</h2>');

						$attribute_type_r = fetch_alt_item_id_attribute_type_r();

						$altid_item_instance_rs = update_altid_item_instance_rs($HTTP_VARS['op'], $HTTP_VARS['alt_item_id'], $attribute_type_r, get_decoded_item_instance_rs($HTTP_VARS['op'], $HTTP_VARS['checkout_item_instance_rs']), $errors);

						if (is_array($errors) > 0)
							echo (format_error_block($errors));

						echo ("\n<form action=\"$PHP_SELF\" method=\"POST\">");
						echo ("\n<input type=\"hidden\" name=\"op\" value=\"" . $HTTP_VARS['op'] . "\">");
						echo ("\n<input type=\"hidden\" name=\"page_no\" value=\"\">");//dummy

						if ($HTTP_VARS['op'] == 'checkout') {
							echo ("\n<input type=\"hidden\" name=\"borrower_id\" value=\"" . $HTTP_VARS['borrower_id'] . "\">");
						}

						echo ("\n<table class=\"borrowerForm\">");
						echo get_item_input_field('alt_item_id', $attribute_type_r, NULL);
						echo ("\n</table>");

						echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('add_item') . "\">");

						$HTTP_VARS['checkout_item_instance_rs'] = get_encoded_item_instance_rs($altid_item_instance_rs);
						echo (get_url_fields(NULL, array('checkout_item_instance_rs' => $HTTP_VARS['checkout_item_instance_rs'])));

						if (is_not_empty_array($HTTP_VARS['checkout_item_instance_rs'])) {
							if ($HTTP_VARS['op'] == 'checkout') {
								echo ("<input type=\"button\" class=\"button\" onclick=\"doFormSubmit(this.form, 'item_borrow.php', 'quick_check_out')\" value=\"" . get_opendb_lang_var('check_out_item(s)') . "\">");
							} else {
								$HTTP_VARS['sequence_number'] = get_borrowed_item_sequence_number_r($altid_item_instance_rs);
								echo (get_url_fields(NULL, array('sequence_number' => $HTTP_VARS['sequence_number'])));

								echo ("<input type=\"button\" class=\"button\" onclick=\"doFormSubmit(this.form, 'item_borrow.php', 'check_in')\" value=\"" . get_opendb_lang_var('check_in_item(s)') . "\">");
							}

						}
						echo ("</form>");

						unset($HTTP_VARS['alt_item_id']);

						$listingObject = new HTML_Listing($PHP_SELF, $HTTP_VARS);
						$listingObject->setNoRowsMessage(get_opendb_lang_var('no_records_found'));

						if (is_numeric($listingObject->getItemsPerPage())) {
							$listingObject->setTotalItems(count($altid_item_instance_rs));
						}

						if (is_array($altid_item_instance_rs)) {
							sort_item_listing($altid_item_instance_rs, $listingObject->getCurrentOrderBy(), $listingObject->getCurrentSortOrder());

							// Now get the bit we actually want for this page.
							if (is_numeric($listingObject->getItemsPerPage())) {
								$altid_item_instance_rs = array_slice($altid_item_instance_rs, $listingObject->getStartIndex(), $listingObject->getItemsPerPage());
							}

							// Ensure we are at the start of the array.
							if (is_array($altid_item_instance_rs))
								reset($altid_item_instance_rs);
						}

						echo ("<div id=\"checkOutListing\">");
						$listingObject->startListing($page_title);

						$listingObject->addHeaderColumn(get_opendb_lang_var('type'), 's_item_type');
						$listingObject->addHeaderColumn(get_opendb_lang_var('title'), 'title');
						$listingObject->addHeaderColumn(get_opendb_lang_var('owner'), 'owner');

						if (get_opendb_config_var('borrow', 'duration_support')) {
							$listingObject->addHeaderColumn(get_opendb_lang_var('borrow_duration'), 'borrow_duration', FALSE);
						}

						if (is_not_empty_array($altid_item_instance_rs)) {
							foreach ($altid_item_instance_rs as $item_instance_r) {
								$listingObject->startRow();

								$listingObject->addItemTypeImageColumn($item_instance_r['s_item_type']);
								$listingObject->addTitleColumn($item_instance_r);
								$listingObject->addUserNameColumn($item_instance_r['owner_id']);

								if (is_numeric($item_instance_r['borrow_duration']) && $item_instance_r['borrow_duration'] > 0) {
									$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r($item_instance_r['s_item_type'], 'DURATION');
									$listingObject->addDisplayColumn($duration_attr_type_r['s_attribute_type'], NULL, $duration_attr_type_r['display_type'], $item_instance_r['borrow_duration']);
								} else {
									$listingObject->addColumn(get_opendb_lang_var('undefined'));
								}

								$listingObject->endRow();
							}
						}

						$listingObject->endListing();
						echo ("</div>");

						echo ("<ul class=\"listingControls\">");
						if (get_opendb_config_var('listings', 'allow_override_show_item_image') !== FALSE) {
							echo ("<li>" . getToggleControl($PHP_SELF, $HTTP_VARS, get_opendb_lang_var('show_item_image'), 'show_item_image', ifempty($HTTP_VARS['show_item_image'], get_opendb_config_var('listings', 'show_item_image') == TRUE ? 'Y' : 'N')) . "</li>");
						}
						echo ("</ul>");
					}

					echo (_theme_footer());
				} else {
					opendb_operation_not_available();
				}
			} else { //borrow functionality disabled.
 				echo _theme_header(get_opendb_lang_var('borrow_not_supported'));
				echo ("<p class=\"error\">" . get_opendb_lang_var('borrow_not_supported') . "</p>");
				echo _theme_footer();
			}
		} else {
			opendb_not_authorised_page(PERM_ADMIN_QUICK_CHECKOUT, $HTTP_VARS);
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
