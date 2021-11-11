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

include_once("./admin/s_item_type_group/functions.php");
include_once("./admin/s_attribute_type/functions.php");

include_once("./lib/site_plugin.php");
include_once("./lib/item_type.php");
include_once("./lib/item_type_group.php");
include_once("./lib/install.php");

// attributes delivered as part of core installation or included in optional patches
$_CORE_ATTRIBUTE_TYPES = array('S_DURATION', 'S_ITEM_ID', 'S_STATUS', 'S_TITLE', 'S_RATING', 'S_STATCMNT');

function display_item_type_insert_field($title, $fieldType) {
	$lookup_results = fetch_sfieldtype_attribute_type_rs($fieldType);
	echo ("<tr><td class=\"prompt\">" . "$title <a href=\"#\" onmouseover=\"return show_sat_select_tooltip(document.forms['s_item_type']['s_field_type[$fieldType]'], arrayOfSystemAttributeTypeTooptips);\" onmouseout=\"return hide_tooltip();\">(?)</a>: " . "</td><td class=\"data\">"
			. custom_select("s_field_type[$fieldType]", $lookup_results, "%s_attribute_type% - %description%", 1, is_array($HTTP_VARS['s_field_type']) ? $HTTP_VARS['s_field_type'][$fieldType] : NULL, "s_attribute_type") . "</td></tr>");
}

function display_s_item_type_insert_form($HTTP_VARS) {
	$sat_results = fetch_sfieldtype_attribute_type_rs(array('TITLE', 'CATEGORY', 'STATUSTYPE', 'STATUSCMNT', 'DURATION'));
	while ($attribute_type_r = db_fetch_assoc($sat_results)) {
		$s_attribute_type_list_rs[] = $attribute_type_r;
	}
	db_free_result($sat_results);

	echo get_s_attribute_type_tooltip_array($s_attribute_type_list_rs);

	echo ("\n<table>");

	// s_item_type
	echo get_input_field("s_item_type", NULL, "Item Type", "text(10,10)", "Y", $HTTP_VARS['s_item_type']);

	//description
	echo get_input_field("description", NULL, "Description", "text(30,60)", "Y", $HTTP_VARS['description']);

	//image
	echo get_input_field("image", NULL, "Image", "url(15,*,\"gif,jpg,png\",N)", "N", $HTTP_VARS['image']);

	echo ("\n</table>");

	echo ("<h4>Field Type Attributes</h4>");

	echo ("\n<table>");

	display_item_type_insert_field('Title', 'TITLE');
	display_item_type_insert_field('Category', 'CATEGORY');
	display_item_type_insert_field('Status Type', 'STATUSTYPE');
	display_item_type_insert_field('Status Comment', 'STATUSCMNT');

	if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('borrow', 'duration_support') !== FALSE) {
		display_item_type_insert_field('Borrow Duration', 'DURATION');
	}

	echo ("\n</table>");
}

/*
 * Item Types main display.
 */
function display_s_item_type_row($item_type_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	echo ("\n<tr>");

	$errors = NULL;
	if (check_item_type_structure($item_type_r['s_item_type'], $errors))
		$class = "data";
	else
		$class = "error";

	// order_no
	echo ("\n<td class=\"$class\">" . get_input_field("order_no[$row]", NULL, NULL, "number(3)", "N", $item_type_r['order_no'], FALSE) . "</td>");

	echo ("\n<td class=\"$class\">" . get_input_field("s_item_type[$row]", NULL, "Item Type", "readonly", "Y", $item_type_r['s_item_type'], FALSE) . "<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">" . "</td>");

	//description
	echo ("\n<td class=\"$class\">" . get_input_field("description[$row]", NULL, NULL, "text(30,30)", "N", $item_type_r['description'], FALSE) . "</td>");

	echo ("<td class=\"$class\">");
	// Get the theme specific source of the image.
	if (strlen($item_type_r['image']) > 0) {
		$src = theme_image_src($item_type_r['image']);
	}
	if ($src !== FALSE && strlen($src) > 0)
		echo ("<img src=\"$src\">");
	else
		echo ("&nbsp;");
	echo ("</td>");

	echo ("\n<td class=\"$class\">" . get_input_field("image[$row]", NULL, NULL, "url(15,*,\"gif,jpg,png\",N)", "N", $item_type_r['image'], FALSE) . "</td>");

	echo ("\n<td class=\"$class\">");
	echo ("\n<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_item_type=" . $item_type_r['s_item_type'] . "\">Edit</a>" . " / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete_type&s_item_type=" . $item_type_r['s_item_type'] . "\">Delete</a>" . "</td>");

	echo ("\n<td class=\"$class\">");
	echo ("\n<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=sql&s_item_type=${item_type_r['s_item_type']}&mode=job\">SQL</a>");
	echo ("\n</td>");

	echo ("</tr>");

	if (is_not_empty_array($errors)) {
		echo ("\n<tr>");
		echo ("\n<td colspan=\"6\" class=\"$class\">");
		echo format_error_block($errors);
		echo ("\n</td>");
		echo ("\n<tr>");
	}
}

/*
 * Specific item type - s_item_attribute_type block.
 */
function display_s_item_attribute_type_row($s_item_type, $s_item_attribute_type_r, $row, $exists_error_ind = FALSE, $s_attribute_type_list_rs) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	echo ("<tr>");

	// Indicates this record is in error, and we need to make this clear.
	if ($exists_error_ind)
		$class = "error";
	else
		$class = "data";

	// s_attribute_type
	if (is_not_empty_array($s_item_attribute_type_r) && $exists_error_ind == FALSE) {
		// order_no
		echo ("<td class=\"$class\">");
		if (!is_s_item_attribute_type_deletable($s_item_type, $s_item_attribute_type_r['s_attribute_type'], $s_item_attribute_type_r['order_no']))
			echo (get_input_field("order_no[$row]", NULL, "Order No.", "readonly", "N", $s_item_attribute_type_r['order_no'], FALSE));
		else
			echo (get_input_field("order_no[$row]", NULL, "Order No.", "number(3)", "N", $s_item_attribute_type_r['order_no'], FALSE));
		echo ("<input type=\"hidden\" name=\"old_order_no[$row]\" value=\"" . $s_item_attribute_type_r['order_no'] . "\"></td>");

		// See if a s_field_type defined for this attribute type
		$attribute_type_r = fetch_s_attribute_type_r($s_item_attribute_type_r['s_attribute_type']);
		if (strlen($attribute_type_r['s_field_type']) > 0)
			$value = $s_item_attribute_type_r['s_attribute_type'] . " [" . $attribute_type_r['s_field_type'] . "]";
		else
			$value = $s_item_attribute_type_r['s_attribute_type'];

		echo ("<input type=\"hidden\" name=\"s_attribute_type[$row]\" value=\"" . $s_item_attribute_type_r['s_attribute_type'] . "\">" . "<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">");

		echo ("<td class=\"$class\">" . $value . "</td>");

		echo ("<td class=\"$class\"><a href=\"#\" onmouseover=\"return show_sat_tooltip('" . $s_item_attribute_type_r['s_attribute_type'] . "', arrayOfSystemAttributeTypeTooptips);\" onmouseout=\"return hide_tooltip();\">(?)</a></td>");
	} else {
		// order_no
		echo ("<td class=\"$class\">" . ($exists_error_ind ? theme_image("rs.gif", "Duplicate Attribute Type & Order No") : "") . get_input_field("order_no[$row]", NULL, NULL, "number(3)", "N", $s_item_attribute_type_r['order_no'], FALSE) . "</td>");

		echo ("<td class=\"$class\">" . "<select name=\"s_attribute_type[$row]\">" . "\n<option value=\"\">");
		reset($s_attribute_type_list_rs);
		foreach ($s_attribute_type_list_rs as $attribute_type_r) {
			if (is_not_empty_array($s_item_attribute_type_r) && $s_item_attribute_type_r['s_attribute_type'] == $attribute_type_r['s_attribute_type'])
				echo ("\n<option value=\"" . $attribute_type_r['s_attribute_type'] . "\" SELECTED>" . $attribute_type_r['s_attribute_type']);
			else
				echo ("\n<option value=\"" . $attribute_type_r['s_attribute_type'] . "\">" . $attribute_type_r['s_attribute_type']);

			if (strlen($attribute_type_r['s_field_type']) > 0)
				echo (" [" . $attribute_type_r['s_field_type'] . "]");
		}
		echo ("\n</select></td>");

		echo ("<td class=\"$class\"><a href=\"#\" onmouseover=\"return show_sat_select_tooltip(document.forms['s_item_attribute_type']['s_attribute_type[$row]'], arrayOfSystemAttributeTypeTooptips);\" onmouseout=\"return hide_tooltip();\">(?)</a></td>");
	}

	echo ("<td class=\"$class\">" . get_input_field("prompt[$row]", NULL, NULL, "text(15,30)", "N", $s_item_attribute_type_r['prompt'], FALSE) . "</td>");

	if (!is_array($s_item_attribute_type_r)
			|| ($attribute_type_r['s_field_type'] != 'STATUSTYPE' && $attribute_type_r['s_field_type'] != 'STATUSCMNT' && $attribute_type_r['s_field_type'] != 'DURATION' && $attribute_type_r['s_field_type'] != 'TITLE' && $attribute_type_r['s_field_type'] != 'ITEM_ID'
					&& $attribute_type_r['s_field_type'] != 'UPDATE_ON')) {
		echo ("<td class=\"$class\">");
		echo (get_input_field("instance_attribute_ind[$row]", NULL, NULL, "simple_checkbox(" . (strtoupper($s_item_attribute_type_r['instance_attribute_ind']) == "Y" ? "CHECKED" : "") . ")", "N", "Y", FALSE));
		echo ("</td>");

		echo ("<td class=\"$class\">");
		echo (get_input_field("compulsory_ind[$row]", NULL, NULL, "simple_checkbox(" . (strtoupper($s_item_attribute_type_r['compulsory_ind']) == "Y" ? "CHECKED" : "") . ")", "N", "Y", FALSE));
		echo ("</td>");
	} else {
		// title is not supported at instance level
		if ($attribute_type_r['s_field_type'] == 'TITLE')
			echo ("<td class=\"$class\">N</td>");
		else
			echo ("<td class=\"$class\">Y</td>");

		echo ("<td class=\"$class\">Y</td>");
	}

	echo ("\n<td class=\"$class\">");
	echo (get_input_field("rss_ind[$row]", NULL, NULL, "simple_checkbox(" . (strtoupper($s_item_attribute_type_r['rss_ind']) == "Y" ? "CHECKED" : "") . ")", "N", "Y", FALSE) . "&nbsp;Rss Feed<br />");
	echo (get_input_field("printable_ind[$row]", NULL, NULL, "simple_checkbox(" . (strtoupper($s_item_attribute_type_r['printable_ind']) == "Y" ? "CHECKED" : "") . ")", "N", "Y", FALSE) . "&nbsp;Printable");
	echo ("</td>");

	echo ("\n<td class=\"$class\">");
	if (is_not_empty_array($s_item_attribute_type_r)) {
		echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&s_item_type=" . $s_item_type . "&s_attribute_type=" . $s_item_attribute_type_r['s_attribute_type'] . "&order_no=" . $s_item_attribute_type_r['order_no'] . "\">Delete</a>");
	}
	echo ("</td>");
	echo ("</tr>");
}

/**
 * Generate a complete s_item_type script.
 * 
 * Data generated:
 * 	s_item_type
 * 	s_attribute_type's (Those not included in $_ATTRIBUTE_TYPES['core'])
 * 	s_item_attribute_type's
 * 	s_item_attribute_type_lookup (Those not included in $_ATTRIBUTE_TYPES['core'])
 */
function generate_s_item_type_sql($s_item_type) {
	global $_CORE_ATTRIBUTE_TYPES;

	$CRLF = get_user_browser_crlf();

	$s_item_type_r = fetch_s_item_type_r($s_item_type);
	if ($s_item_type_r !== FALSE) {
		$type_sql = $CRLF . '#' . $CRLF . '# Item Type' . $CRLF . '#' . $CRLF . 'INSERT INTO s_item_type ( s_item_type, description, image ) VALUES ( \'' . $s_item_type . '\', \'' . addslashes($s_item_type_r['description']) . '\', \'' . addslashes($s_item_type_r['image']) . '\' );' . $CRLF;

		$results = fetch_item_type_group_rlshp_rs(NULL, $s_item_type);
		if ($results) {
			$type_sql .= $CRLF . '#' . $CRLF . '# Item Type Group Relationships' . $CRLF . '#' . $CRLF;

			while ($item_type_group_rlshp_r = db_fetch_assoc($results)) {
				$type_sql .= 'INSERT INTO s_item_type_group_rltshp ( s_item_type_group, s_item_type ) VALUES ( \'' . $item_type_group_rlshp_r['s_item_type_group'] . '\', \'' . $item_type_group_rlshp_r['s_item_type'] . '\' );' . $CRLF;

			}
			db_fetch_assoc($results);
		}

		$attr_sql = '';

		$item_attr_sql = $CRLF . '#' . $CRLF . '# Item Attribute Relationships' . $CRLF . '#';

		$attr_lookup_sql = '';

		$result = fetch_s_item_attribute_type_rs($s_item_type);
		if ($result) {
			$attr_added = array();
			while ($s_item_attribute_type_r = db_fetch_assoc($result)) {
				// Ensure to also check that the attribute has not already been encountered, in case we have duplicates
				// in s_item_attribute_type structure (such as CDTRACK for instance)
				if (strlen($s_item_attribute_type_r['site_type']) == 0 && // do not include any site type attributes 
						(!is_array($_CORE_ATTRIBUTE_TYPES) || !in_array($s_item_attribute_type_r['s_attribute_type'], $_CORE_ATTRIBUTE_TYPES)) && !in_array($s_item_attribute_type_r['s_attribute_type'], $attr_added)) {
					// Add s_attribute_type, so we do not insert it twice.
					$attr_added[] = $s_item_attribute_type_r['s_attribute_type'];

					$s_attribute_type_r = fetch_s_attribute_type_r($s_item_attribute_type_r['s_attribute_type']);

					$attr_sql .= $CRLF
							. "INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) "
							. "VALUES ( '" . $s_attribute_type_r['s_attribute_type'] . "', " . "'" . addslashes($s_attribute_type_r['description']) . "', " . "'" . addslashes($s_attribute_type_r['prompt']) . "', " . "'" . $s_attribute_type_r['input_type'] . "', "
							. (strlen($s_attribute_type_r['input_type_arg1']) > 0 ? "'" . addslashes($s_attribute_type_r['input_type_arg1']) . "'" : "NULL") . ", " . (strlen($s_attribute_type_r['input_type_arg2']) > 0 ? "'" . addslashes($s_attribute_type_r['input_type_arg2']) . "'" : "NULL") . ", "
							. (strlen($s_attribute_type_r['input_type_arg3']) > 0 ? "'" . addslashes($s_attribute_type_r['input_type_arg3']) . "'" : "NULL") . ", " . (strlen($s_attribute_type_r['input_type_arg4']) > 0 ? "'" . addslashes($s_attribute_type_r['input_type_arg4']) . "'" : "NULL") . ", "
							. (strlen($s_attribute_type_r['input_type_arg5']) > 0 ? "'" . addslashes($s_attribute_type_r['input_type_arg5']) . "'" : "NULL") . ", " . "'" . $s_attribute_type_r['display_type'] . "',"
							. (strlen($s_attribute_type_r['display_type_arg1']) > 0 ? "'" . addslashes($s_attribute_type_r['display_type_arg1']) . "'" : "NULL") . ", " . (strlen($s_attribute_type_r['display_type_arg2']) > 0 ? "'" . addslashes($s_attribute_type_r['display_type_arg2']) . "'" : "NULL")
							. ", " . (strlen($s_attribute_type_r['display_type_arg3']) > 0 ? "'" . addslashes($s_attribute_type_r['display_type_arg3']) . "'" : "NULL") . ", " . (strlen($s_attribute_type_r['display_type_arg4']) > 0 ? "'" . addslashes($s_attribute_type_r['display_type_arg4']) . "'"
									: "NULL") . ", " . (strlen($s_attribute_type_r['display_type_arg5']) > 0 ? "'" . addslashes($s_attribute_type_r['display_type_arg5']) . "'" : "NULL") . ", " . "'" . $s_attribute_type_r['listing_link_ind'] . "', " . "'" . $s_attribute_type_r['file_attribute_ind']
							. "', " . "'" . $s_attribute_type_r['lookup_attribute_ind'] . "', " . "'" . $s_attribute_type_r['multi_attribute_ind'] . "', " . (strlen($s_attribute_type_r['s_field_type']) > 0 ? "'" . $s_attribute_type_r['s_field_type'] . "'" : "NULL") . ", "
							. (strlen($s_attribute_type_r['site_type']) > 0 ? "'" . $s_attribute_type_r['s_field_type'] . "'" : "NULL") . ");";

					$result2 = fetch_attribute_type_lookup_rs($s_item_attribute_type_r['s_attribute_type'], 'value ASC', FALSE);
					if ($result2) {
						$attr_lookup_sql .= $CRLF . '#' . $CRLF . '# Attribute Type Lookup (' . $s_item_attribute_type_r['s_attribute_type'] . ')' . $CRLF . '#';

						while ($s_attribute_type_lookup_r = db_fetch_assoc($result2)) {
							$attr_lookup_sql .= $CRLF . 'INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( \'' . $s_item_attribute_type_r['s_attribute_type'] . '\', '
									. (is_numeric($s_attribute_type_lookup_r['order_no']) ? $s_attribute_type_lookup_r['order_no'] : 'NULL') . ', \'' . addslashes($s_attribute_type_lookup_r['value']) . '\', \'' . addslashes($s_attribute_type_lookup_r['display']) . '\', \''
									. addslashes($s_attribute_type_lookup_r['img']) . '\', \'' . $s_attribute_type_lookup_r['checked_ind'] . '\' );';
						}
						db_fetch_assoc($result2);

						$attr_lookup_sql .= $CRLF;
					}
				}

				$item_attr_sql .= $CRLF . 'INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( \'' . $s_item_type . '\', \'' . $s_item_attribute_type_r['s_attribute_type'] . '\', '
						. $s_item_attribute_type_r['order_no'] . ', ' . (strlen($s_item_attribute_type_r['prompt']) > 0 ? '\'' . $s_item_attribute_type_r['prompt'] . '\'' : 'NULL') . ', \'' . $s_item_attribute_type_r['instance_attribute_ind'] . '\', \'' . $s_item_attribute_type_r['compulsory_ind']
						. '\', \'' . $s_item_attribute_type_r['printable_ind'] . '\', \'' . $s_item_attribute_type_r['rss_ind'] . '\' );';
			}
			db_fetch_assoc($result);
		}

		if (strlen($attr_sql) > 0) {
			$attr_sql = $CRLF . '#' . $CRLF . '# Attributes (non-core)' . $CRLF . '#' . $attr_sql;
		}

		$sqlscript = '#########################################################' . $CRLF . '# OpenDb ' . get_opendb_version() . ' \'' . $s_item_type . '\' Item Type' . $CRLF . '#########################################################' . $CRLF . $type_sql . $attr_sql . $CRLF . $item_attr_sql
				. $CRLF . $attr_lookup_sql;

		return $sqlscript;
	} else {
		return NULL;
	}
}

/*
 * Remove ALL records associated with an s_item_type, which includes:
 * 	review
 * 	borrowed_item
 * 	item_attribute
 * 	item_instance
 * 	item
 */
function delete_sitemtype_items($s_item_type) {
	$result = db_query("SELECT id as item_id FROM item WHERE s_item_type = '" . $s_item_type . "'");
	if ($result && db_num_rows($result) > 0) {
		while ($item_r = db_fetch_assoc($result)) {
			if (db_query("DELETE FROM review WHERE item_id = " . $item_r['item_id']) && db_affected_rows() > 0) {
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User deleted system item type review records', array($s_item_type));
			}

			if (db_query("DELETE FROM borrowed_item WHERE item_id = " . $item_r['item_id']) && db_affected_rows() > 0) {
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User deleted system item type borrowed_item records', array($s_item_type));
			}

			if (db_query("DELETE FROM item_attribute WHERE item_id = " . $item_r['item_id']) && db_affected_rows() > 0) {
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User deleted system item type item_attribute records', array($s_item_type));
			}

			if (db_query("DELETE FROM item_instance WHERE item_id = " . $item_r['item_id']) && db_affected_rows() > 0) {
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User deleted system item type item_instance records', array($s_item_type));
			}

			if (db_query("DELETE FROM item WHERE id = " . $item_r['item_id']) && db_affected_rows() > 0) {
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User deleted system item type item records', array($s_item_type));
			}
		}
		db_free_result($result);

		return TRUE;
	} else // By returning false, we make it clear that no items were found.
 {
		return FALSE;
	}
}

if ($HTTP_VARS['op'] == 'sql' && is_exists_item_type($HTTP_VARS['s_item_type'])) {
	header("Cache-control: no-store");
	header("Pragma: no-store");
	header("Expires: 0");
	header("Content-disposition: attachment; filename=" . strtolower($HTTP_VARS['s_item_type']) . ".sql");
	header("Content-type: text/plain");
	$sqlfile = generate_s_item_type_sql($HTTP_VARS['s_item_type']);
	header("Content-Length: " . strlen($sqlfile));
	echo ($sqlfile);

} else if ($HTTP_VARS['op'] == 'delete_type') { // This is initiated from the main s_item_type form.
	$item_type_r = fetch_s_item_type_r($HTTP_VARS['s_item_type']);
	if ($item_type_r !== FALSE) {
		if ($HTTP_VARS['confirmed'] == 'false') {
			// do nothing.
			$HTTP_VARS['op'] = '';
		} else if ($HTTP_VARS['confirmed'] != 'true') {
			echo ("\n<h3>");
			if (strlen($item_type_r['image']) > 0) {
				$src = theme_image_src($item_type_r['image']);
				if ($src !== FALSE && strlen($src) > 0)
					echo ("<img src=\"$src\">&nbsp;");
			}
			echo "Delete Item Type</h3>";

			$op_confirm_prompt = "";
			if (!is_s_item_type_deletable($HTTP_VARS['s_item_type'])) {
				$op_confirm_prompt .= "<div class=\"error\">Dependant items exist for the " . $HTTP_VARS['s_item_type'] . " Item Type - These will also be deleted including all dependant records (review, borrowed_item, item_attribute, item_instance, item)</div>";
			}

			if (is_exists_item_type_item_type_group($HTTP_VARS['s_item_type'])) {
				$op_confirm_prompt .= "<div class=\"error\">The " . $HTTP_VARS['s_item_type'] . " Item Type is referenced in at least one System Item Type Group record, this will also be deleted.</div>";
			}

			$op_confirm_prompt .= "Are you sure you want to delete Item Type \"" . $HTTP_VARS['s_item_type'] . "\"?";

			echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
		} else { // $HTTP_VARS['confirmed'] == 'true'
			// delete every record where s_item_type matches.
			delete_s_item_type_group_rltshp(NULL, $HTTP_VARS['s_item_type']);

			// delete all items.
			delete_sitemtype_items($HTTP_VARS['s_item_type']);

			if (!is_exists_item_attribute_type($HTTP_VARS['s_item_type'], NULL) || delete_s_item_attribute_type($HTTP_VARS['s_item_type'], NULL, NULL)) {
				if (!delete_s_item_type($HTTP_VARS['s_item_type']))
					$errors[] = array('error' => 'Item Type "' . $HTTP_VARS['s_item_type'] . '" not deleted', 'detail' => db_error());
			} else {
				$errors[] = array('error' => 'Item Type "' . $HTTP_VARS['s_item_type'] . '" Attributes not deleted', 'detail' => db_error());
			}

			$HTTP_VARS['op'] = '';
		}
	} else {
		$HTTP_VARS['op'] = '';
	}
} else if ($HTTP_VARS['op'] == 'insert_type') { // Insert whole new item type
	// All types are uppercase.
	$HTTP_VARS['s_item_type'] = strtoupper($HTTP_VARS['s_item_type']);

	// Get rid of all spaces, and illegal characters.
	$HTTP_VARS['s_item_type'] = preg_replace("/[\s|'|\\\\|\"]+/", "", trim(strip_tags($HTTP_VARS['s_item_type'])));

	if (strlen($HTTP_VARS['s_item_type']) > 0) {
		if (!is_exists_item_type($HTTP_VARS['s_item_type'])) { //insert
			// But a s_attribute_type must be supplied for s_field_type's
			// TITLE, STATUS, CATEGORY.   The DURATION is required if
			// get_opendb_config_var('borrow', 'enable') == TRUE AND get_opendb_config_var('borrow', 'duration_support') == TRUE
			$missing_s_field_types = NULL;
			if (strlen($HTTP_VARS['s_field_type']['TITLE']) == 0 || !is_exists_attribute_type($HTTP_VARS['s_field_type']['TITLE']))
				$missing_s_field_types[] = 'TITLE';
			if (strlen($HTTP_VARS['s_field_type']['STATUSCMNT']) == 0 || !is_exists_attribute_type($HTTP_VARS['s_field_type']['STATUSCMNT']))
				$missing_s_field_types[] = 'STATUSCMNT';
			if (strlen($HTTP_VARS['s_field_type']['STATUSTYPE']) == 0 || !is_exists_attribute_type($HTTP_VARS['s_field_type']['STATUSTYPE']))
				$missing_s_field_types[] = 'STATUSTYPE';
			if (strlen($HTTP_VARS['s_field_type']['CATEGORY']) == 0 || !is_exists_attribute_type($HTTP_VARS['s_field_type']['CATEGORY']))
				$missing_s_field_types[] = 'CATEGORY';
			if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('borrow', 'duration_support') !== FALSE && (strlen($HTTP_VARS['s_field_type']['DURATION']) == 0 || !is_exists_attribute_type($HTTP_VARS['s_field_type']['DURATION'])))
				$missing_s_field_types[] = 'DURATION';

			if (is_empty_array($missing_s_field_types)) {
				if (insert_s_item_type($HTTP_VARS['s_item_type'], $HTTP_VARS['order_no'], $HTTP_VARS['description'], $HTTP_VARS['image'])) {
					//Insert required system s_attribute_type's.
					insert_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_field_type']['TITLE'], '1', NULL, 'N', 'Y', 'N', 'N');
					insert_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_field_type']['CATEGORY'], '10', NULL, 'N', 'N', 'N', 'N');

					if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('borrow', 'duration_support') !== FALSE)
						insert_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_field_type']['DURATION'], '200', NULL, 'N', 'N', 'N', 'N');

					insert_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_field_type']['STATUSTYPE'], '254', NULL, 'N', 'N', 'N', 'N');
					insert_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_field_type']['STATUSCMNT'], '255', NULL, 'N', 'N', 'N', 'N');

					// Load the edit_types form now.
					$HTTP_VARS['op'] = 'edit_types';
				} else {
					$errors[] = array('error' => 'Item Type (' . $HTTP_VARS['s_item_type'] . ') not inserted.', 'detail' => db_error());
				}
			} else {
				$errors[] = array('error' => 'The following Attribute <i>Field Type\'s</i> are missing.', 'detail' => $missing_s_field_types);
			}
		} else {
			$errors[] = array('error' => 'Item Type (' . $HTTP_VARS['s_item_type'] . ') already exists.', 'detail' => '');
		}
	} else {
		$errors[] = array('error' => 'Item Type not specified.', 'detail' => '');
	}
} else if ($HTTP_VARS['op'] == 'update_types') { // This is initiated from the main s_item_type form.
	if (is_not_empty_array($HTTP_VARS['s_item_type'])) {
		for ($i = 0; $i < count($HTTP_VARS['s_item_type']); $i++) {
			if (is_exists_item_type($HTTP_VARS['s_item_type'][$i])) {
				if (!update_s_item_type($HTTP_VARS['s_item_type'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['description'][$i], $HTTP_VARS['image'][$i])) {
					$errors[] = array('error' => 'Item Type (' . $HTTP_VARS['s_item_type'][$i] . ') not updated', 'detail' => db_error());
				}
			} else {
				$errors[] = array('error' => 'Item Type (' . $HTTP_VARS['s_item_type'][$i] . ') not found.', 'detail' => '');
			}
		}
	}
} else if ($HTTP_VARS['op'] == 'delete') {
	$item_type_r = fetch_s_item_type_r($HTTP_VARS['s_item_type']);
	if ($item_type_r !== FALSE) {
		$attribute_type_r = fetch_s_attribute_type_r($HTTP_VARS['s_attribute_type']);
		if ($attribute_type_r !== FALSE) {
			if ($HTTP_VARS['confirmed'] == 'false') {
				// return to edit form
				$HTTP_VARS['op'] = 'edit';
			} else {
				if ($HTTP_VARS['confirmed'] != 'true') {
					// Get the theme specific source of the image.
					echo ("\n<h3>");
					if (strlen($item_type_r['image']) > 0) {
						$src = theme_image_src($item_type_r['image']);
						if ($src !== FALSE && strlen($src) > 0)
							echo ("<img src=\"$src\">&nbsp;");
					}

					echo "Delete Item Attribute Type</h3>";

					$op_confirm_prompt = "";

					if (!is_s_item_attribute_type_deletable($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'], $HTTP_VARS['order_no'])) {
						$op_confirm_prompt .= "<div class=\"error\">Dependant items attribute exist for the \"" . $HTTP_VARS['s_attribute_type'] . "[" . $HTTP_VARS['order_no'] . "]\" Item Attribute Type - these will be deleted!</div>";
					}

					if ($attribute_type_r['s_field_type'] == 'TITLE' || $attribute_type_r['s_field_type'] == 'STATUSTYPE' || $attribute_type_r['s_field_type'] == 'STATUSCMNT' || $attribute_type_r['s_field_type'] == 'CATEGORY'
							|| (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('borrow', 'duration_support') !== FALSE && $attribute_type_r['s_field_type'] == 'DURATION')) {
						$op_confirm_prompt .= "<div class=\"error\">Item Attribute Type is a compulsory Field Type (" . $attribute_type_r['s_field_type'] . ") - deleting this attribute will invalidate the " . $HTTP_VARS['s_item_type'] . " Item Type structure.</div>";
					}

					$op_confirm_prompt .= "Are you sure you want to delete Item Attribute Type \"" . $HTTP_VARS['s_attribute_type'] . "[" . $HTTP_VARS['order_no'] . "]\"?";

					echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
				} else { // $HTTP_VARS['confirmed'] == 'true'
					if (is_s_item_attribute_type_with_order_no($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'], $HTTP_VARS['order_no'])) {
						if (delete_item_attribute_order_no($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'], $HTTP_VARS['order_no'])) {
							if (!delete_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'], $HTTP_VARS['order_no']))
								$errors[] = array('error' => 'Item Attribute Type "' . $HTTP_VARS['s_attribute_type'] . '[' . $HTTP_VARS['order_no'] . ']" not deleted.', 'detail' => db_error());
						} else {
							$errors[] = array('error' => 'Item Attributes "' . $HTTP_VARS['s_attribute_type'] . '[' . $HTTP_VARS['order_no'] . ']" not deleted.', 'detail' => db_error());
						}
					}//do nothing

					// return to edit form
					$HTTP_VARS['op'] = 'edit';
				}
			}
		}
	}
} else if ($HTTP_VARS['op'] == 'update') { // This is initiated from the lower s_item_attribute_type form.
	if (is_exists_item_type($HTTP_VARS['s_item_type'])) {
		if (is_not_empty_array($HTTP_VARS['s_attribute_type'])) {
			// Do delete operations here.
			for ($i = 0; $i < count($HTTP_VARS['s_attribute_type']); $i++) {
				//update or delete
				if ($HTTP_VARS['exists_ind'][$i] == 'Y') {
					// The 'old_order_no' will often be the same as 'order_no' but for instances where
					// they are different this test will match both!
					if (is_exists_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['old_order_no'][$i])) {
						if ($HTTP_VARS['order_no'][$i] != $HTTP_VARS['old_order_no'][$i]) {
							// If order_no has changed.
							if (is_s_item_attribute_type_deletable($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['old_order_no'][$i])) {
								if ($HTTP_VARS['order_no'][$i] != $HTTP_VARS['old_order_no'][$i]) {
									$HTTP_VARS['exists_ind'][$i] = 'N';
								}

								// Delete old_order_no in both cases!
								if (!delete_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['old_order_no'][$i])) {
									$errors[] = array('error' => 'Item Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not deleted', 'detail' => db_error());
								}
							} else {
								$errors[] = array('error' => 'Item Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not deleted', 'detail' => 'Dependant item attribute(s) with the same order_no exist.');
							}
						} else { // 'old_order_no' IS THE SAME as 'order_no' here!
							// At the moment we are not checking the order_no's for items with the same type.
							if (!update_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['prompt'][$i], $HTTP_VARS['instance_attribute_ind'][$i], $HTTP_VARS['compulsory_ind'][$i], $HTTP_VARS['rss_ind'][$i],
									$HTTP_VARS['printable_ind'][$i])) {
								$errors[] = array('error' => 'Item Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not updated', 'detail' => db_error());
							} else {
								if ($HTTP_VARS['instance_attribute_ind'][$i] == "Y") {
									if (is_exists_non_instance_item_attributes($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['order_no'][$i])) {
										$errors[] = array('error' => 'Warning setting Instance Indicator to Y for item attribute relationship ' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['order_no'][$i] . ']', 'detail' => 'Item Attributes exist which are not linked to a item instance');
									}
								}
							}
						}
					} else { //if(is_exists_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['old_order_no'][$i]))
						$errors[] = array('error' => 'Item Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not found', 'detail' => db_error());
					}
				}//if($HTTP_VARS['exists_ind'][$i] == 'Y')
			}//for($i=0; $i<count($HTTP_VARS['s_attribute_type']); $i++)

			// Now do the inserts.
			for ($i = 0; $i < count($HTTP_VARS['s_attribute_type']); $i++) {
				// Ignore elements that have no order_no or old_order_no specified.
				if ($HTTP_VARS['exists_ind'][$i] != 'Y') {
					if (strlen($HTTP_VARS['s_attribute_type'][$i]) > 0) {
						if (is_numeric($HTTP_VARS['old_order_no'][$i]) || is_numeric($HTTP_VARS['order_no'][$i])) {
							if (!is_exists_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['order_no'][$i])) {
								if (!insert_s_item_attribute_type($HTTP_VARS['s_item_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['prompt'][$i], $HTTP_VARS['instance_attribute_ind'][$i], $HTTP_VARS['compulsory_ind'][$i], $HTTP_VARS['rss_ind'][$i],
										$HTTP_VARS['printable_ind'][$i])) {
									$errors[] = array('error' => 'Item Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . '[' . $HTTP_VARS['old_order_no'][$i] . ']) not inserted', 'detail' => db_error());
								}
							} else {
								// Cache any records that could not be inserted.
								$sait_already_exists[] = array('s_attribute_type' => $HTTP_VARS['s_attribute_type'][$i], 'order_no' => $HTTP_VARS['order_no'][$i], 'prompt' => $HTTP_VARS['prompt'][$i], 'instance_attribute_ind' => $HTTP_VARS['instance_attribute_ind'][$i],
										'compulsory_ind' => $HTTP_VARS['compulsory_ind'][$i], 'rss_ind' => $HTTP_VARS['rss_ind'][$i], 'printable_ind' => $HTTP_VARS['printable_ind'][$i],);
							}
						} else {
							$errors[] = array('error' => 'Item Attribute type (' . $HTTP_VARS['s_attribute_type'][$i] . ') not inserted', 'detail' => 'No order_no specified.');
						}
					}//if(strlen($HTTP_VARS['s_attribute_type'][$i])>0)
				}
			}
		}
	} else {
		$errors[] = array('error' => 'Item Type (' . $HTTP_VARS['s_item_type'] . ') not found', 'detail' => '');
	}
} else if ($HTTP_VARS['op'] == 'installsql') {
	execute_sql_install($ADMIN_TYPE, $HTTP_VARS['sqlfile'], $errors);
	$HTTP_VARS['op'] = NULL;
}

// Reload edit page after an update.
if ($HTTP_VARS['op'] == 'edit' || $HTTP_VARS['op'] == 'update') {
	echo ("<script language=\"JavaScript\" type=\"text/javascript\" src=\"./admin/s_item_type/sattooltips.js\"></script>");

	echo ("<p>[<a href=\"$PHP_SELF?type=s_item_type&op=edit_types\">Back to Main</a>]</p>");

	$item_type_r = fetch_s_item_type_r($HTTP_VARS['s_item_type']);
	if ($item_type_r !== FALSE) {
		echo ("\n<h3>");
		if (strlen($item_type_r['image']) > 0) {
			$src = theme_image_src($item_type_r['image']);
			if ($src !== FALSE && strlen($src) > 0)
				echo ("<img src=\"$src\">&nbsp;");
		}
		echo ($item_type_r['s_item_type'] . " System Attributes</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		echo ("\n<form name=\"s_item_attribute_type\" action=\"$PHP_SELF\" method=\"POST\">");
		echo ("\n<input type=\"hidden\" name=\"op\" value=\"update\">" . "\n<input type=\"hidden\" name=\"type\" value=\"" . $HTTP_VARS['type'] . "\">" . "\n<input type=\"hidden\" name=\"s_item_type\" value=\"" . $HTTP_VARS['s_item_type'] . "\">");

		echo ("<table>");
		echo ("<tr class=\"navbar\">" . "<th>Order<br />No.</th>" . "<th colspan=2>Attribute Type [Field Type]</th>" . "<th>Prompt</th>" . "<th>Instance Ind.</th>" . "<th>Compulsory</th>" . "<th>Indicators</th>" . "<th></th>" . "</tr>");

		$column_count = 8;

		$sat_results = fetch_item_type_s_attribute_type_rs();
		while ($attribute_type_r = db_fetch_assoc($sat_results)) {
			$s_attribute_type_list_rs[] = $attribute_type_r;
		}
		db_free_result($sat_results);

		echo get_s_attribute_type_tooltip_array($s_attribute_type_list_rs);

		$results = fetch_s_item_attribute_type_rs($HTTP_VARS['s_item_type']);
		if ($results) {
			// value, display, img, checked_ind, order_no
			$row = 0;
			while ($item_attribute_type_r = db_fetch_assoc($results)) {
				display_s_item_attribute_type_row($HTTP_VARS['s_item_type'], $item_attribute_type_r, $row, FALSE, $s_attribute_type_list_rs);
				$row++;
			}
			db_free_result($results);
		}

		// Now display records that could not be inserted.
		if (is_not_empty_array($sait_already_exists)) {
			foreach ($sait_already_exists as $sait_r) {
				display_s_item_attribute_type_row($HTTP_VARS['s_item_type'], $sait_r, $row, TRUE, $s_attribute_type_list_rs);
				$row++;
			}
		}

		if (is_numeric($HTTP_VARS['blank_rows']))
			$blank_rows = (int) $HTTP_VARS['blank_rows'];
		else
			$blank_rows = 5;

		for ($i = $row; $i < $row + $blank_rows; $i++) {
			display_s_item_attribute_type_row($HTTP_VARS['s_item_type'], array(), $i, FALSE, $s_attribute_type_list_rs);
		}
		echo ("</table>");

		$help_entries_rs = NULL;
		if (is_not_empty_array($sait_already_exists)) {
			$help_entries_rs[] = array('img' => 'rs.gif', 'text' => 'Duplicate Attribute Type & Order No');
		}

		$help_entries_rs[] = array('text' => 'Order No. and Attribute Type are compulsory');

		echo (format_help_block($help_entries_rs));

		echo (get_input_field("blank_rows", NULL, NULL, "value_select(\"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20\",1)", "N", ifempty($HTTP_VARS['blank_rows'], "5"), FALSE, NULL, "this.form.submit();"));
		echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='edit'; this.form.submit();\">
			<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update'; this.form.submit();\">");
		echo ("</form>");
	} else {
		echo format_error_block('Item Type (' . $HTTP_VARS['s_item_type'] . ') not found');
	}
} else if ($HTTP_VARS['op'] == 'new_type' || $HTTP_VARS['op'] == 'insert_type') { // Insert type form!
	echo ("<script language=\"JavaScript\" type=\"text/javascript\" src=\"./admin/s_item_type/sattooltips.js\"></script>");

	echo ("<p>[<a href=\"$PHP_SELF?type=s_item_type&op=edit_types\">Back to Main</a>]</p>");

	echo ("\n<h3>New Item Type</h3>");

	if (is_not_empty_array($errors))
		echo format_error_block($errors);

	echo ("\n<form name=\"s_item_type\" action=\"$PHP_SELF\" method=\"POST\">");

	echo ("\n<input type=\"hidden\" name=\"op\" value=\"insert_type\">");
	echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $HTTP_VARS['type'] . "\">");

	display_s_item_type_insert_form($HTTP_VARS['op'] == 'insert_type' ? $HTTP_VARS : NULL);

	echo (format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory')));

	if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
		echo ("\n<input type=\"button\" class=\"button\" value=\"Insert\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
	else
		echo ("\n<input type=\"button\" class=\"button\" value=\"Insert\" onclick=\"this.form.submit();\">");

	echo ("\n</form>");
}

// There are specific operations where this form should be displayed.
if (strlen($HTTP_VARS['op']) == 0 || (($HTTP_VARS['op'] == 'delete_sitem_type_items' && ($HTTP_VARS['confirmed'] == 'false' || $HTTP_VARS['confirmed'] == 'true'))) || (($HTTP_VARS['op'] == 'delete_type_confirm' && ($HTTP_VARS['confirmed'] == 'false' || $HTTP_VARS['confirmed'] == 'true')))
		|| $HTTP_VARS['op'] == 'edit_types' || $HTTP_VARS['op'] == 'update_types') {
	echo ("<p>[<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new_type\">New Item Type</a>]</p>");

	if (is_not_empty_array($errors ?? ""))
		echo format_error_block($errors);

	$results = fetch_s_item_type_rs();
	if ($results) {
		echo ("<form name=\"s_item_type\" action=\"$PHP_SELF\" method=\"POST\">" . "<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">" . "<input type=\"hidden\" name=\"op\" value=\"\">" . "<input type=\"hidden\" name=\"s_item_type\" value=\"\">");

		echo ("<table>");
		echo ("<tr class=\"navbar\">" . "<th>Order</th>" . "<th>Type</th>" . "<th>Description</th>" . "<th colspan=2>Image</th>" . "<th colspan=2></th>" . "</tr>");

		// value, display, img, checked_ind, order_no
		$row = 0;
		while ($item_type_r = db_fetch_assoc($results)) {
			display_s_item_type_row($item_type_r, $row);
			$row++;
		}
		db_free_result($results);
		echo ("</table>");

		echo (format_help_block('Image(s) must be in a <i>theme search path</i> directory.'));

		echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='edit_types'; this.form.submit();\">" . " <input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update_types'; this.form.submit();\">");

		echo ("</form>");
	} else {
		echo ("<p class=\"error\">No Item Types Installed</p>");
	}

	function is_not_exists_item_type($type) {
		return !is_exists_item_type($type, FALSE);
	}
	generate_sql_list($ADMIN_TYPE, 'Item Type', NULL, 'is_not_exists_item_type');
}
?>
