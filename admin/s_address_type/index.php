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

if (!defined('OPENDB_ADMIN_TOOLS')) {
	die('Admin tools not accessible directly');
}

include_once("./admin/s_item_type/functions.php");
include_once("./admin/s_attribute_type/functions.php");

include_once("./lib/site_plugin.php");
include_once("./lib/address_type.php");

/*
 * s_address_type			varchar(10) NOT NULL,
  description				varchar(30) NOT NULL,
  display_order				tinyint(2),
  closed_ind				varchar(1) NOT NULL default 'N',
 */
function display_s_address_type_insert_form($HTTP_VARS) {
	echo get_input_field("s_address_type", NULL, "Address Type", "text(10,10)", "Y", $HTTP_VARS['s_address_type']);
	echo get_input_field("description", NULL, "Description", "text(30,60)", "Y", $HTTP_VARS['description']);
}

/*
 * Item Types main display.
 * s_address_type			varchar(10) NOT NULL,
  description				varchar(30) NOT NULL,
  display_order				tinyint(2),
  closed_ind				varchar(1) NOT NULL default 'N',
 */
function display_s_address_type_row($address_type_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;
	global $HTTP_VARS;

	echo ("\n<tr>");

	// order_no
	echo ("\n<td class=\"data\">" . get_input_field("display_order[$row]", NULL, NULL, "number(3)", "N", $address_type_r['display_order'], FALSE) . "</td>");

	// s_address_type
	echo ("\n<td class=\"data\">" . get_input_field("s_address_type[$row]", NULL, NULL, "readonly", "Y", $address_type_r['s_address_type'], FALSE) . "<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">" . "</td>");

	//description
	echo ("\n<td class=\"data\">" . get_input_field("description[$row]", NULL, NULL, "text(15,30)", "N", $address_type_r['description'], FALSE) . "</td>");

	if (is_array($address_type_r))
		echo ("\n<td class=\"data\">" . get_input_field("closed_ind[$row]", NULL, NULL, "simple_checkbox(" . ($address_type_r['closed_ind'] == 'Y' ? 'CHECKED' : '') . ")", "N", "Y", FALSE) . "</td>");

	echo ("\n<td class=\"data\">");
	echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_address_type=" . $address_type_r['s_address_type'] . "\">Edit</a>");
	echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&s_address_type=" . $address_type_r['s_address_type'] . "\">Delete</a>");
	echo ("</td>");

	echo ("</tr>");
}

/*
 * Specific item type - s_item_attribute_type block.
 */
function display_s_addr_attribute_type_rltshp_row($s_address_type, $s_addr_attribute_type_rltshp_r, $row, $exists_error_ind = FALSE, $s_attribute_type_list_rs) {
	echo ("<tr>");

	// Indicates this record is in error, and we need to make this clear.
	if ($exists_error_ind)
		$class = "error";
	else
		$class = "data";

	// Delete ind
	echo ("<td class=\"$class\">");
	if (!$exists_error_ind && is_not_empty_array($s_addr_attribute_type_rltshp_r) && is_s_addr_attribute_type_rltshp_deletable($s_address_type, $s_addr_attribute_type_rltshp_r['s_attribute_type'], $s_addr_attribute_type_rltshp_r['order_no']))
		echo get_input_field("delete_ind[$row]", NULL, NULL, "simple_checkbox()", "N", "Y", FALSE);
	else
		echo ("&nbsp;");
	echo ("</td>");

	// s_attribute_type
	if (is_not_empty_array($s_addr_attribute_type_rltshp_r) && $exists_error_ind == FALSE) {
		// order_no
		echo ("<td class=\"$class\">" . get_input_field("order_no[$row]", NULL, NULL, "number(3)", "Y", $s_addr_attribute_type_rltshp_r['order_no'], FALSE) . "<input type=\"hidden\" name=\"old_order_no[$row]\" value=\"" . $s_addr_attribute_type_rltshp_r['order_no'] . "\">" . "</td>");

		echo ("\n<td class=\"$class\">" . get_input_field("s_attribute_type[$row]", NULL, NULL, "readonly", "Y", $s_addr_attribute_type_rltshp_r['s_attribute_type'], FALSE) . "<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">" . "</td>");

		echo ("<td class=\"$class\"><a href=\"#\" onmouseover=\"return show_sat_tooltip('" . $s_addr_attribute_type_rltshp_r['s_attribute_type'] . "', arrayOfSystemAttributeTypeTooptips);\" onmouseout=\"return hide_tooltip();\">(?)</a></td>");
	} else {
		// order_no
		echo ("<td class=\"$class\">" . ($exists_error_ind ? theme_image("rs.gif", "Duplicate Attribute Type & Order No") : "") . get_input_field("order_no[$row]", NULL, NULL, "number(3)", "N", $s_addr_attribute_type_rltshp_r['order_no'], FALSE) . "</td>");

		echo ("<td class=\"$class\">" . "<select name=\"s_attribute_type[$row]\">" . "\n<option value=\"\">");
		reset($s_attribute_type_list_rs);
		foreach ($s_attribute_type_list_rs as $attribute_type_r) {
			if (is_not_empty_array($s_addr_attribute_type_rltshp_r) && $s_addr_attribute_type_rltshp_r['s_attribute_type'] == $attribute_type_r['s_attribute_type'])
				echo ("\n<option value=\"" . $attribute_type_r['s_attribute_type'] . "\" SELECTED>" . $attribute_type_r['s_attribute_type']);
			else
				echo ("\n<option value=\"" . $attribute_type_r['s_attribute_type'] . "\">" . $attribute_type_r['s_attribute_type']);
		}
		echo ("\n</select></td>");

		echo ("<td class=\"$class\"><a href=\"#\" onmouseover=\"return show_sat_select_tooltip(document.forms['s_addr_attribute_type_rltshp']['s_attribute_type[$row]'], arrayOfSystemAttributeTypeTooptips);\" onmouseout=\"return hide_tooltip();\">(?)</a></td>");
	}
	echo ("<td class=\"$class\">" . get_input_field("prompt[$row]", NULL, NULL, "text(15,30)", "N", $s_addr_attribute_type_rltshp_r['prompt'], FALSE) . "</td>");

	if (is_array($s_addr_attribute_type_rltshp_r))
		echo ("\n<td class=\"$class\">" . get_input_field("closed_ind[$row]", NULL, NULL, "simple_checkbox(" . ($s_addr_attribute_type_rltshp_r['closed_ind'] == 'Y' ? 'CHECKED' : '') . ")", "N", "Y", FALSE) . "</td>");
	echo ("</td>");

	echo ("</tr>");
}

if ($HTTP_VARS['op'] == 'delete') // This is initiated from the main s_address_type form.
 {
	if (is_exists_address_type($HTTP_VARS['s_address_type'])) {
		// In the case where we are deleting the whole type, there is no need 
		// to check whether individual attributes exist, checking for items
		// is sufficient - we don't care about orphaned attributes.
		if (is_s_address_type_deletable($HTTP_VARS['s_address_type'])) {
			if ($HTTP_VARS['confirmed'] == 'false') {
				// do nothing.
				$HTTP_VARS['op'] = '';
			} else if ($HTTP_VARS['confirmed'] != 'true') {
				echo "<h3>Delete Address Type</h3>";
				echo get_op_confirm_form($PHP_SELF, "Are you sure you want to delete Address Type \"" . $HTTP_VARS['s_address_type'] . "\"?", $HTTP_VARS);
			} else { // $HTTP_VARS['confirmed'] == 'true'
				// Check if there are any s_item_attribute_type records.				
				if (!is_exists_addr_attribute_type_rltshp($HTTP_VARS['s_address_type'], NULL) || delete_s_addr_attribute_type_rltshp($HTTP_VARS['s_address_type'], NULL, NULL)) {
					if (!delete_s_address_type($HTTP_VARS['s_address_type'])) {
						$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'] . ') not deleted', 'detail' => db_error());
					}
				} else {
					$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'] . ') attributes not deleted', 'detail' => db_error());
				}

				$HTTP_VARS['op'] = '';
			}
		} else {
			$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'] . ') not deleted', 'detail' => 'Address Type has dependant item(s)');

			$HTTP_VARS['op'] = '';
		}
	} else {
		$HTTP_VARS['op'] = '';
	}
} else if ($HTTP_VARS['op'] == 'insert_type')// Insert whole new item type
 {
	// All types are uppercase.
	$HTTP_VARS['s_address_type'] = strtoupper($HTTP_VARS['s_address_type']);

	// Get rid of all spaces, and illegal characters.
	$HTTP_VARS['s_address_type'] = preg_replace("/[\s|'|\\\\|\"]+/", "", trim(strip_tags($HTTP_VARS['s_address_type'])));

	if (strlen($HTTP_VARS['s_address_type']) > 0) {
		if (!is_exists_address_type($HTTP_VARS['s_address_type'])) { //insert
			if (insert_s_address_type($HTTP_VARS['s_address_type'], $HTTP_VARS['display_order'], $HTTP_VARS['description'])) {
				// Load the edit_types form now.
				$HTTP_VARS['op'] = 'edit_types';
			} else {
				$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'] . ') not inserted.', 'detail' => db_error());
			}
		} else {
			$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'] . ') already exists.', 'detail' => '');
		}
	} else {
		$errors[] = array('error' => 'Address Type not specified.', 'detail' => '');
	}

	echo format_error_block($errors);
} else if ($HTTP_VARS['op'] == 'update_types') // This is initiated from the main s_address_type form.
 {
	if (is_not_empty_array($HTTP_VARS['s_address_type'])) {
		for ($i = 0; $i < count($HTTP_VARS['s_address_type']); $i++) {
			if (is_exists_address_type($HTTP_VARS['s_address_type'][$i])) {
				if (!update_s_address_type($HTTP_VARS['s_address_type'][$i], $HTTP_VARS['display_order'][$i], $HTTP_VARS['description'][$i], $HTTP_VARS['closed_ind'][$i])) {
					$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'][$i] . ') not updated', 'detail' => db_error());
				}
			} else {
				$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'][$i] . ') not found.', 'detail' => '');
			}
		}
		echo format_error_block($errors);
	}
} else if ($HTTP_VARS['op'] == 'update') { // This is initiated from the lower s_item_attribute_type form.
	if (is_exists_address_type($HTTP_VARS['s_address_type'])) {
		if (is_not_empty_array($HTTP_VARS['s_attribute_type'])) {
			for ($i = 0; $i < count($HTTP_VARS['s_attribute_type']); $i++) {
				//update or delete
				if ($HTTP_VARS['exists_ind'][$i] == 'Y') {
					// The 'old_order_no' will often be the same as 'order_no' but for instances where
					// they are different this test will match both!
					if (is_exists_addr_attribute_type_rltshp($HTTP_VARS['s_address_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['old_order_no'][$i])) {
						// Delete record if delete_ind = Y or the order_no has been changed.
						// In the case of the changed order_no, we will be inserting it in the next
						// for loop, which is why the exists_ind is reset!
						if ($HTTP_VARS['delete_ind'][$i] == 'Y' || $HTTP_VARS['order_no'][$i] != $HTTP_VARS['old_order_no'][$i]) {
							// TODO: Provide functionality to convert existing item_attribute records to the new format.
							if (is_s_addr_attribute_type_rltshp_deletable($HTTP_VARS['s_address_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['old_order_no'][$i])) {
								if ($HTTP_VARS['order_no'][$i] != $HTTP_VARS['old_order_no'][$i])
									$HTTP_VARS['exists_ind'][$i] = 'N';

								// Delete old_order_no in both cases!
								if (!delete_s_addr_attribute_type_rltshp($HTTP_VARS['s_address_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['old_order_no'][$i])) {
									$errors[] = array('error' => 'Address Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not deleted', 'detail' => db_error());
								}
							} else {
								$errors[] = array('error' => 'Address Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not deleted', 'detail' => 'Dependant user address attribute(s) with the same order_no exist.');
							}
						} else { // 'old_order_no' IS THE SAME as 'order_no' here!
							// At the moment we are not checking the order_no's for items with the same type.
							if (!update_s_addr_attribute_type_rltshp($HTTP_VARS['s_address_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['prompt'][$i], $HTTP_VARS['closed_ind'][$i])) {
								$errors[] = array('error' => 'Address Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not updated', 'detail' => db_error());
							}
						}
					} else {
						$errors[] = array('error' => 'Address Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not found', 'detail' => db_error());
					}
				}
			}

			// Now do the inserts.
			for ($i = 0; $i < count($HTTP_VARS['s_attribute_type']); $i++) {
				// Ignore elements that have no order_no or old_order_no specified.
				if ($HTTP_VARS['exists_ind'][$i] != 'Y') {
					if (strlen($HTTP_VARS['s_attribute_type'][$i]) > 0) {
						if (is_numeric($HTTP_VARS['old_order_no'][$i]) || is_numeric($HTTP_VARS['order_no'][$i])) {
							if (!is_exists_addr_attribute_type_rltshp($HTTP_VARS['s_address_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['order_no'][$i])) {
								if (!insert_s_addr_attribute_type_rltshp($HTTP_VARS['s_address_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['prompt'][$i], $HTTP_VARS['closed_ind'][$i])) {
									$errors[] = array('error' => 'Address Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not inserted', 'detail' => db_error());
								}
							} else {
								// Cache any records that could not be inserted.
								$saatr_already_exists[] = array('s_attribute_type' => $HTTP_VARS['s_attribute_type'][$i], 'order_no' => $HTTP_VARS['order_no'][$i], 'prompt' => $HTTP_VARS['prompt'][$i], 'compulsory_ind' => $HTTP_VARS['compulsory_ind'][$i]);
							}
						} else {
							$errors[] = array('error' => 'Item Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . ') not inserted', 'detail' => 'No order_no specified.');
						}
					}//if(strlen($HTTP_VARS['s_attribute_type'][$i])>0)
				}
			}
		}
	} else {
		$errors[] = array('error' => 'Address Type (' . $HTTP_VARS['s_address_type'] . ') not found', 'detail' => '');
	}
}

// Reload edit page after an update.
if ($HTTP_VARS['op'] == 'edit' || $HTTP_VARS['op'] == 'update') {
	echo get_javascript("admin/s_item_type/sattooltips.js");

	echo ("<p>[<a href=\"$PHP_SELF?type=s_address_type&op=edit_types\">Back to Main</a>]</p>");

	$address_type_r = fetch_s_address_type_r($HTTP_VARS['s_address_type']);
	if ($address_type_r !== FALSE) {
		echo ("\n<h3>" . $item_type_r['s_address_type'] . " System Address Attributes</h3>");

		if (is_not_empty_array($errors)) {
			echo format_error_block($errors);
		}

		$column_count = 6;
		echo ("\n<form name=\"s_addr_attribute_type_rltshp\" action=\"$PHP_SELF\" method=\"POST\">");
		echo ("\n<input type=\"hidden\" name=\"op\" value=\"update\">" . "\n<input type=\"hidden\" name=\"type\" value=\"" . $HTTP_VARS['type'] . "\">" . "\n<input type=\"hidden\" name=\"s_address_type\" value=\"" . $HTTP_VARS['s_address_type'] . "\">");

		echo ("<table>");
		echo ("<tr class=\"navbar\">" . "<th>Delete</th>" . "<th>Order</th>" . "<th colspan=2>Attribute Type</th>" . "<th>Prompt</th>" . "<th>Closed</th>" . "</tr>");

		$sat_results = fetch_sfieldtype_attribute_type_rs('ADDRESS');
		while ($attribute_type_r = db_fetch_assoc($sat_results)) {
			$s_attribute_type_list_rs[] = $attribute_type_r;
		}
		db_free_result($sat_results);

		echo get_s_attribute_type_tooltip_array($s_attribute_type_list_rs);

		$results = fetch_s_addr_attribute_type_rltshp_rs($HTTP_VARS['s_address_type']);
		if ($results) {
			// value, display, img, checked_ind, order_no
			$row = 0;
			while ($s_addr_attribute_type_rltshp_r = db_fetch_assoc($results)) {
				display_s_addr_attribute_type_rltshp_row($HTTP_VARS['s_address_type'], $s_addr_attribute_type_rltshp_r, $row, FALSE, $s_attribute_type_list_rs);
				$row++;
			}
			db_free_result($results);
		}

		// Now display records that could not be inserted.
		if (is_not_empty_array($saatr_already_exists)) {
			foreach ($saatr_already_exists as $saatr_r) {
				display_s_addr_attribute_type_rltshp_row($HTTP_VARS['s_address_type'], $saatr_r, $row, TRUE, $s_attribute_type_list_rs);
				$row++;
			}
		}

		if (is_numeric($HTTP_VARS['blank_rows']))
			$blank_rows = (int) $HTTP_VARS['blank_rows'];
		else
			$blank_rows = 5;

		for ($i = $row; $i < $row + $blank_rows; $i++) {
			display_s_addr_attribute_type_rltshp_row($HTTP_VARS['s_address_type'], array(), $i, FALSE, $s_attribute_type_list_rs);
		}
		echo ("</table>");

		$help_entries_rs = NULL;
		if (is_not_empty_array($saatr_already_exists)) {
			$help_entries_rs[] = array('img' => 'rs.gif', 'text' => 'Duplicate Attribute Type & Order No');
		}

		$help_entries_rs[] = array('text' => 'Entries without a <b>attribute type</b> AND <b>order no</b> specified will be ignored.');
		echo (format_help_block($help_entries_rs));

		echo (get_input_field("blank_rows", NULL, NULL, "value_select(\"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20\",1)", "N", ifempty($HTTP_VARS['blank_rows'], "5"), FALSE, NULL, "this.form.submit();"));

		echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='edit'; this.form.submit();\">
		<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update'; this.form.submit();\">");

		echo ("</form>");
	} else {
		echo format_error_block('Item Type (' . $HTTP_VARS['s_address_type'] . ') not found');
	}
} else if ($HTTP_VARS['op'] == 'new_type' || $HTTP_VARS['op'] == 'insert_type') { // Insert type form!
	echo get_javascript("admin/s_item_type/sattooltips.js");

	echo ("<p>[<a href=\"$PHP_SELF?type=s_address_type&op=edit_types\">Back to Main</a>]</p>");

	echo ("\n<h3>New Address Type</h3>");

	echo ("\n<form name=\"s_address_type\" action=\"$PHP_SELF\" method=\"POST\">");

	echo ("\n<input type=\"hidden\" name=\"op\" value=\"insert_type\">");
	echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $HTTP_VARS['type'] . "\">");

	echo ("\n<table>");
	display_s_address_type_insert_form($HTTP_VARS['op'] == 'insert_type' ? $HTTP_VARS : NULL);
	echo ("</table>");

	echo (format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory')));

	if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
		echo ("\n<input type=\"button\" class=\"button\" value=\"Insert\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
	else
		echo ("\n<input type=\"button\" class=\"button\" value=\"Insert\" onclick=\"this.form.submit();\">");

	echo ("\n</form>");
}

// There are specific operations where this form should be displayed.
if (strlen($HTTP_VARS['op']) == 0 || $HTTP_VARS['op'] == 'edit_types' || $HTTP_VARS['op'] == 'update_types') {
	echo ("<p>[<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new_type\">New Address Type</a>]</p>");

	if (is_not_empty_array($errors ?? ""))
		echo format_error_block($errors);

	$results = fetch_s_address_type_rs();
	if ($results) {
		echo ("\n<form name=\"s_address_type\" action=\"$PHP_SELF\" method=\"POST\">");
		echo ("\n<input type=\"hidden\" name=\"op\" value=\"update_types\">");
		echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $HTTP_VARS['type'] . "\">");

		echo ("<table>");
		echo ("<tr class=\"navbar\">" . "<th>Order</th>" . "<th>Type</th>" . "<th>Description</th>" . "<th>Closed</th>" . "<th></th>" . "</tr>");

		// value, display, img, checked_ind, order_no
		$row = 0;
		while ($address_type_r = db_fetch_assoc($results)) {
			display_s_address_type_row($address_type_r, $row);
			$row++;
		}
		db_free_result($results);

		echo ("</table>");

		echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='edit_types'; this.form.submit();\">" . "<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update_types'; this.form.submit();\">");

		echo ("</form>");
	} else {
		echo ("<p class=\"error\">No Address Types Installed</p>");
	}
}
?>
