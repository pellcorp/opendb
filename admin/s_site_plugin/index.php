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

include_once("./lib/datetime.php");
include_once("./lib/item_type.php");
include_once("./lib/item_type_group.php");
include_once("./lib/import.php");
include_once("./admin/s_item_type/functions.php");
include_once("./admin/s_attribute_type/functions.php");
include_once("./lib/install.php");

$cfg_date_mask = 'DD/MM/YYYY HH24:MI:SS';

function display_edit_site_plugin($record_r, $HTTP_VARS = NULL) {
	if (is_array($record_r))
		echo get_input_field('site_type', NULL, 'Site Type', 'readonly', 'Y', $record_r['site_type']);
	else
		echo get_input_field('site_type', NULL, 'Site Type', 'text(10,10)', 'Y', $record_r['site_type']);

	echo get_input_field('classname', NULL, 'Class Name', 'text(50,50)', 'Y', $record_r['classname']);

	echo get_input_field('title', NULL, 'Title', 'text(25,50)', 'Y', $record_r['title']);

	$field = get_input_field('image', NULL, 'Image', 'url(25,*,"gif,jpg,png",N)', 'N', $record_r['image'], FALSE);

	if (strlen($record_r['image']) > 0)
		$image_src = "images/site/" . $record_r['image'];

	if ($image_src !== FALSE && strlen($image_src) > 0 && file_exists($image_src)) {
		$field .= theme_image("images/site/" . $image_src, NULL, NULL);
	}

	echo format_field('Image', $field);

	echo get_input_field('description', NULL, 'Description', 'text(50,255)', 'Y', $record_r['description']);
	echo get_input_field('external_url', NULL, 'External URL', 'text(50,255)', 'Y', $record_r['external_url']);
	echo get_input_field('more_info_url', NULL, 'More Info URL', 'text(50,255)', 'N', $record_r['more_info_url']);
	echo get_input_field('items_per_page', NULL, 'Items Per Page', 'number(3)', 'N', $record_r['items_per_page']);
}

function display_site_plugin_conf_row($record_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	if (is_not_empty_array($record_r)) {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">");
		echo ("<td class=\"data\">" . get_input_field("name[$row]", NULL, "Name", "readonly", "Y", $record_r['name'], FALSE) . "</td>");
		echo ("<td class=\"data\">" . get_input_field("keyid[$row]", NULL, "Key ID", "readonly", "Y", $record_r['keyid'], FALSE) . "</td>");
	} else {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"N\">");
		echo ("<td class=\"data\">" . get_input_field("name[$row]", NULL, "Name", "text(20,50)", "Y", $record_r['name'], FALSE) . "</td>");
		echo ("<td class=\"data\">" . get_input_field("keyid[$row]", NULL, "Key ID", "text(10,50)", "Y", $record_r['keyid'], FALSE) . "</td>");
	}

	echo ("<td class=\"data\">" . get_input_field("description[$row]", NULL, "Description", "text(20,255)", 'N', $record_r['description'], FALSE) . "</td>");
	echo ("<td class=\"data\">" . get_input_field("value[$row]", NULL, "Value", "text(20,255)", "N", $record_r['value'], FALSE) . "</td>");

	echo ("\n<td class=\"data\">");
	if (is_not_empty_array($record_r)) {
		echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete_site_plugin_conf&site_type=" . $record_r['site_type'] . "&name=" . $record_r['name'] . "&keyid=" . $record_r['keyid'] . "\">Delete</a>");
	} else {
		echo ("&nbsp;");
	}
	echo ("\n</td>");
}

function display_site_plugin_link_row($record_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	if (is_not_empty_array($record_r)) {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">");
		echo ("<input type=\"hidden\" name=\"sequence_number[$row]\" value=\"" . $record_r['sequence_number'] . "\">");
	} else {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"N\">");
	}

	echo ("<td class=\"data\">" . get_input_field("order_no[$row]", NULL, "Order No", "number(3)", "Y", $record_r['order_no'], FALSE) . "</td>");

	$item_type_groups[] = '*';
	$results = fetch_item_type_group_rs();
	if ($results) {
		while ($item_type_group_r = db_fetch_assoc($results)) {
			$item_type_groups[] = $item_type_group_r['s_item_type_group'];
		}
		db_free_result($results);
	}

	echo ("\n<td class=\"data\">"
			. custom_select("s_item_type_group[$row]", $item_type_groups, "%value%", 1, is_array($record_r) ? $record_r['s_item_type_group'] : '*', 'value', NULL, NULL, "if(this.options[this.options.selectedIndex].value != '*'){this.form['s_item_type[$row]'].options[0].selected=true;}") . "</td>");

	$item_types[] = '*';
	$results = fetch_item_type_rs();
	if ($results) {
		while ($item_type_r = db_fetch_assoc($results)) {
			$item_types[] = $item_type_r['s_item_type'];
		}
		db_free_result($results);
	}
	echo ("\n<td class=\"data\">" . custom_select("s_item_type[$row]", $item_types, "%value%", 1, is_array($record_r) ? $record_r['s_item_type'] : NULL, 'value', NULL, NULL, "if(this.options[this.options.selectedIndex].value != '*'){this.form['s_item_type_group[$row]'].options[0].selected=true;}")
			. "</td>");

	echo ("<td class=\"data\">" . get_input_field("description[$row]", NULL, "Description", "text(20,255)", 'N', $record_r['description'], FALSE) . "</td>");
	echo ("<td class=\"data\">" . get_input_field("url[$row]", NULL, "URL", "text(20,255)", 'N', $record_r['url'], FALSE) . "</td>");
	echo ("<td class=\"data\">" . get_input_field("title_url[$row]", NULL, "Title URL", "text(20,255)", 'N', $record_r['title_url'], FALSE) . "</td>");

	echo ("\n<td class=\"data\">");
	if (is_not_empty_array($record_r)) {
		echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete_site_plugin_link&site_type=" . $record_r['site_type'] . "&sequence_number=" . $record_r['sequence_number'] . "\">Delete</a>");
	} else {
		echo ("&nbsp;");
	}
	echo ("\n</td>");
}

function display_site_plugin_input_field_row($record_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	if (is_not_empty_array($record_r)) {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">");
		echo ("<td class=\"data\">" . get_input_field("order_no[$row]", NULL, "Order No", "number(3)", "Y", $record_r['order_no'], FALSE) . "</td>");
		echo ("<td class=\"data\">" . get_input_field("field[$row]", NULL, "Field Name", "readonly", "Y", $record_r['field'], FALSE) . "</td>");
	} else {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"N\">");
		echo ("<td class=\"data\">" . get_input_field("order_no[$row]", NULL, "Order No", "number(3)", "Y", NULL, FALSE) . "</td>");
		echo ("<td class=\"data\">" . get_input_field("field[$row]", NULL, "Field Name", "text(10,20)", "Y", NULL, FALSE) . "</td>");
	}

	echo ("<td class=\"data\">" . get_input_field("description[$row]", NULL, "Description", "text(20,255)", 'N', $record_r['description'], FALSE) . "</td>");
	echo ("<td class=\"data\">" . get_input_field("prompt[$row]", NULL, "URL", "text(10,30)", 'N', $record_r['prompt'], FALSE) . "</td>");
	echo ("<td class=\"data\">" . custom_select("field_type[$row]", get_legal_input_field_types(), "%value%", 1, $record_r['field_type']) . "</td>");
	echo ("<td class=\"data\">" . get_input_field("refresh_mask[$row]", NULL, "Refresh Mask", "text(20,255)", 'N', $record_r['refresh_mask'], FALSE) . "</td>");
	echo ("<td class=\"data\">" . get_input_field("default_value[$row]", NULL, "Default Value", "text(7,50)", 'N', $record_r['default_value'], FALSE) . "</td>");

	echo ("\n<td class=\"data\">");
	if (is_not_empty_array($record_r)) {
		echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete_site_plugin_input_field&site_type=" . $record_r['site_type'] . "&field=" . $record_r['field'] . "\">Delete</a>");
	} else {
		echo ("&nbsp;");
	}
	echo ("\n</td>");
}

function display_site_plugin_s_attribute_type_map_row($record_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	if (is_not_empty_array($record_r)) {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">");
		echo ("<input type=\"hidden\" name=\"sequence_number[$row]\" value=\"" . $record_r['sequence_number'] . "\">");
		echo ("<td class=\"data\">" . get_input_field("variable[$row]", NULL, "Variable", "readonly", "Y", $record_r['variable'], FALSE) . "</td>");
	} else {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"N\">");
		echo ("<td class=\"data\">" . get_input_field("variable[$row]", NULL, "Variable", "text(10,20)", "Y", NULL, FALSE) . "</td>");
	}

	$item_type_groups[] = '*';
	$results = fetch_item_type_group_rs();
	if ($results) {
		while ($item_type_group_r = db_fetch_assoc($results)) {
			$item_type_groups[] = $item_type_group_r['s_item_type_group'];
		}
		db_free_result($results);
	}

	// this is to avoid confusion if system data is defined for non-existent s_item_type_groups
	if (!in_array($record_r['s_item_type_group'], $item_type_groups))
		$item_type_groups[] = $record_r['s_item_type_group'];

	echo ("\n<td class=\"data\">" . custom_select("s_item_type_group[$row]", $item_type_groups, "%value%", 1, $record_r['s_item_type_group'], 'value', NULL, NULL, "if(this.options[this.options.selectedIndex].value != '*'){this.form['s_item_type[$row]'].options[0].selected=true;}") . "</td>");

	$item_types[] = '*';
	$results = fetch_item_type_rs();
	if ($results) {
		while ($item_type_r = db_fetch_assoc($results)) {
			$item_types[] = $item_type_r['s_item_type'];
		}
		db_free_result($results);
	}

	// this is to avoid confusion if system data is defined for non-existent s_item_types
	if (!in_array($record_r['s_item_type'], $item_types))
		$item_types[] = $record_r['s_item_type'];

	echo ("\n<td class=\"data\">" . custom_select("s_item_type[$row]", $item_types, "%value%", 1, $record_r['s_item_type'], 'value', NULL, NULL, "if(this.options[this.options.selectedIndex].value != '*'){this.form['s_item_type_group[$row]'].options[0].selected=true;}") . "</td>");

	$attribute_types[] = '';
	$results = fetch_item_type_s_attribute_type_rs();
	if ($results) {
		while ($attribute_type_r = db_fetch_assoc($results)) {
			$attribute_types[] = $attribute_type_r['s_attribute_type'];
		}
		db_free_result($results);
	}

	// this is to avoid confusion if system data is defined for non-existent s_attribute_types
	if (!in_array($record_r['s_attribute_type'], $attribute_types))
		$attribute_types[] = $record_r['s_attribute_type'];

	echo ("\n<td class=\"data\">" . custom_select("s_attribute_type[$row]", $attribute_types, "%value%", 1, $record_r['s_attribute_type']) . "</td>");

	if (is_array($record_r) && is_lookup_attribute_type($record_r['s_attribute_type']))
		echo ("<td class=\"data\"><input type=\"checkbox\" class=\"checkbox\" name=\"lookup_attribute_val_restrict_ind[$row]\" value=\"Y\"" . (strtoupper($record_r['lookup_attribute_val_restrict_ind']) == 'Y' ? 'CHECKED' : '') . ">");
	else
		echo ("\n<td class=\"data\">&nbsp;</td>");

	echo ("\n<td class=\"data\">");
	if (is_not_empty_array($record_r)) {
		echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete_site_plugin_s_attribute_type_map&site_type=" . $record_r['site_type'] . "&sequence_number=" . $record_r['sequence_number'] . "\">Delete</a>");
	} else {
		echo ("&nbsp;");
	}
	echo ("\n</td>");
}

function get_lookup_attribute_type_array() {
	$buffer = "";

	// Give us the whole s_attribute_type_lookup table
	$arrayOfLookupValues = "";
	$arrayOfLookupValuesCount = 0;

	$attresults = fetch_attribute_type_lookup_rs(NULL, 's_attribute_type ASC');
	while ($attribute_type_r = db_fetch_assoc($attresults)) {
		$arrayOfLookupValues .= "\narrayOfLookupValues[$arrayOfLookupValuesCount] = new LookupAttribute(\"" . $attribute_type_r['s_attribute_type'] . "\",\"" . $attribute_type_r['value'] . "\",\"" . $attribute_type_r['value'] . " - " . $attribute_type_r['display'] . "\");";
		$arrayOfLookupValuesCount++;
	}

	$buffer .= "\n\narrayOfLookupValues = new Array($arrayOfLookupValuesCount);";
	$buffer .= $arrayOfLookupValues;

	// Now wrap and return
	return "\n<script language=\"JavaScript\">\n<!-- // hide from stupid browsers\n" . $buffer . "\n// -->\n</script>\n";
}

function display_site_plugin_s_attribute_type_lookup_map_row($record_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	if (is_not_empty_array($record_r)) {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">");
		echo ("<input type=\"hidden\" name=\"sequence_number[$row]\" value=\"" . $record_r['sequence_number'] . "\">");
		echo ("<td class=\"data\">" . get_input_field("s_attribute_type[$row]", NULL, "Attribute Type", "readonly", "Y", $record_r['s_attribute_type'], FALSE) . "</td>");
		echo ("<td class=\"data\">" . get_input_field("value[$row]", NULL, "Site Value", "readonly", "Y", $record_r['value'], FALSE) . "</td>");

		$results = fetch_attribute_type_lookup_rs($record_r['s_attribute_type'], 'order_no, value ASC');
		echo ("\n<td class=\"data\">" . custom_select("lookup_attribute_val[$row]", $results, "%value% - %display%", 1, $record_r['lookup_attribute_val']) . "</td>");
	} else {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"N\">");
		$attribute_types[] = '';
		$results = fetch_lookup_s_attribute_type_rs();
		if ($results) {
			while ($attribute_type_r = db_fetch_assoc($results)) {
				$attribute_types[] = $attribute_type_r['s_attribute_type'];
			}
			db_free_result($results);
		}
		echo ("\n<td class=\"data\">" . custom_select("s_attribute_type[$row]", $attribute_types, "%value%", 1, NULL, 'value', NULL, NULL, "populateList(this.options[this.options.selectedIndex].value, this.form['lookup_attribute_val[$row]'], arrayOfLookupValues, false, null, false);") . "</td>");
		echo ("<td class=\"data\">" . get_input_field("value[$row]", NULL, "Site Value", "text(20,100)", "Y", NULL, FALSE) . "</td>");
		echo ("\n<td class=\"data\">" . custom_select("lookup_attribute_val[$row]", array(array('value' => '', 'display' => '')), "%value% - %display%", 1, NULL) . "</td>");
	}

	echo ("\n<td class=\"data\">");
	if (is_not_empty_array($record_r)) {
		echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete_site_plugin_s_attribute_type_lookup_map&site_type=" . $record_r['site_type'] . "&sequence_number=" . $record_r['sequence_number'] . "\">Delete</a>");
	} else {
		echo ("&nbsp;");
	}
	echo ("\n</td>");
}

/**
    @param $edit_op - indicates the operation that spawned this table, will be used
    by the refresh operation.
 */
function display_edit_table($edit_op, $update_op, $headers, $display_functioname, $context_http_vars, $results) {
	global $PHP_SELF;
	global $ADMIN_TYPE;
	global $HTTP_VARS;

	echo "\n<form name=\"editform\" action=\"$PHP_SELF\" method=\"POST\">";
	echo "\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">";
	echo "\n<input type=\"hidden\" name=\"op\" value=\"$edit_op\">";
	echo get_url_fields($context_http_vars, NULL, array('op', 'type'));

	echo "<table>";
	echo "\n<tr class=\"navbar\">";
	for ($i = 0; $i < count($headers); $i++) {
		echo "\n<th>" . $headers[$i] . "</th>";
	}
	echo "\n</tr>";

	$row = 0;
	if ($results) {
		while ($result_r = db_fetch_assoc($results)) {
			echo "\n<tr>";
			$display_functioname($result_r, $row);
			echo "\n</tr>";
			$row++;
		}
		db_free_result($results);
	}

	if (is_numeric($HTTP_VARS['blank_rows']))
		$blank_rows = (int) $HTTP_VARS['blank_rows'];
	else
		$blank_rows = 5;

	for ($i = $row; $i < $row + $blank_rows; $i++) {
		echo "\n<tr>";
		$display_functioname(array(), $i);
		echo "\n</tr>";
	}

	echo "</table>";

	echo get_input_field("blank_rows", NULL, NULL, "value_select(\"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20\",1)", "N", ifempty($HTTP_VARS['blank_rows'], "5"), FALSE, NULL, "this.form.submit();");

	echo "<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='$edit_op'; this.form.submit();\">";
	echo "<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='$update_op'; this.form.submit();\">";

	echo "</form>";

}

function generate_site_plugin_sql($site_type_r) {
	$CRLF = get_user_browser_crlf();

	$buffer = "#########################################################" . $CRLF . "# OpenDb " . get_opendb_version() . " " . $site_type_r['title'] . " (" . $site_type_r['site_type'] . ") Site Plugin" . $CRLF . "#########################################################";

	$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin." . $CRLF . "#" . $CRLF;

	$buffer .= $CRLF . "INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url ) " . "VALUES ( " . "'" . $site_type_r['site_type'] . "', " . "'" . $site_type_r['classname'] . "', " . "'" . addslashes($site_type_r['title']) . "', "
			. "'" . addslashes($site_type_r['image']) . "', " . "'" . addslashes($site_type_r['description']) . "', " . "'" . addslashes($site_type_r['external_url']) . "', " . "" . $site_type_r['items_per_page'] . ", " . "'" . addslashes($site_type_r['more_info_url']) . "' );";

	$results = fetch_site_plugin_conf_rs($site_type_r['site_type']);
	if ($results) {
		$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin Configuration" . $CRLF . "#" . $CRLF;

		while ($site_plugin_conf_r = db_fetch_assoc($results)) {
			$buffer .= $CRLF . "INSERT INTO s_site_plugin_conf ( site_type, name, keyid, description, value ) " . "VALUES ( " . "'" . $site_plugin_conf_r['site_type'] . "', " . "'" . addslashes($site_plugin_conf_r['name']) . "', " . "'" . addslashes($site_plugin_conf_r['keyid']) . "', " . "'"
					. addslashes($site_plugin_conf_r['description']) . "', " . "'" . addslashes($site_plugin_conf_r['value']) . "' );";
		}
		db_fetch_assoc($results);
	}

	$results = fetch_site_plugin_input_field_rs($site_type_r['site_type']);
	if ($results) {
		$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin Input Fields" . $CRLF . "#" . $CRLF;

		while ($site_plugin_input_field_r = db_fetch_assoc($results)) {
			$buffer .= $CRLF . "INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) " . "VALUES ( " . "'" . $site_plugin_input_field_r['site_type'] . "', " . "'" . $site_plugin_input_field_r['field'] . "', " . ""
					. $site_plugin_input_field_r['order_no'] . ", " . "'" . addslashes($site_plugin_input_field_r['description']) . "', " . "'" . addslashes($site_plugin_input_field_r['prompt']) . "', " . "'" . $site_plugin_input_field_r['field_type'] . "', " . "'"
					. addslashes($site_plugin_input_field_r['default_value']) . "', " . "'" . addslashes($site_plugin_input_field_r['refresh_mask']) . "' );";
		}
		db_fetch_assoc($results);
	}

	$results = fetch_site_plugin_link_rs($site_type_r['site_type']);
	if ($results) {
		$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin Links" . $CRLF . "#" . $CRLF;

		while ($fetch_site_plugin_link_r = db_fetch_assoc($results)) {
			$buffer .= $CRLF . "INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) " . "VALUES ( " . "'" . $fetch_site_plugin_link_r['site_type'] . "', " . "'" . $fetch_site_plugin_link_r['s_item_type_group'] . "', " . "'"
					. $fetch_site_plugin_link_r['s_item_type'] . "', " . "" . $fetch_site_plugin_link_r['order_no'] . ", " . "'" . addslashes($fetch_site_plugin_link_r['description']) . "', " . "'" . addslashes($fetch_site_plugin_link_r['url']) . "', " . "'"
					. addslashes($fetch_site_plugin_link_r['title_url']) . "' );";
		}
		db_fetch_assoc($results);
	}

	$results = fetch_site_plugin_s_attribute_type_map_rs($site_type_r['site_type']);
	if ($results) {
		$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin System Attribute Type Map" . $CRLF . "#" . $CRLF;

		while ($site_plugin_s_attribute_type_map_r = db_fetch_assoc($results)) {
			$buffer .= $CRLF . "INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) " . "VALUES ( " . "'" . $site_plugin_s_attribute_type_map_r['site_type'] . "', " . "'"
					. $site_plugin_s_attribute_type_map_r['s_item_type_group'] . "', " . "'" . $site_plugin_s_attribute_type_map_r['s_item_type'] . "', " . "'" . addslashes($site_plugin_s_attribute_type_map_r['variable']) . "', " . "'" . $site_plugin_s_attribute_type_map_r['s_attribute_type']
					. "', " . "'" . $site_plugin_s_attribute_type_map_r['lookup_attribute_val_restrict_ind'] . "' );";
		}
		db_fetch_assoc($results);
	}

	$results = fetch_site_plugin_s_attribute_type_lookup_map_rs($site_type_r['site_type']);
	if ($results) {
		$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin System Attribute Type Lookup Map" . $CRLF . "#" . $CRLF;

		while ($site_plugin_s_attribute_type_lookup_map_r = db_fetch_assoc($results)) {
			$buffer .= $CRLF . "INSERT INTO s_site_plugin_s_attribute_type_lookup_map ( site_type, s_attribute_type, value, lookup_attribute_val ) " . "VALUES ( " . "'" . $site_plugin_s_attribute_type_lookup_map_r['site_type'] . "', " . "'"
					. $site_plugin_s_attribute_type_lookup_map_r['s_attribute_type'] . "', " . "'" . addslashes($site_plugin_s_attribute_type_lookup_map_r['value']) . "', " . "'" . addslashes($site_plugin_s_attribute_type_lookup_map_r['lookup_attribute_val']) . "' ); ";
		}
		db_fetch_assoc($results);
	}

	$results = fetch_site_attribute_type_rs($site_type_r['site_type']);
	if ($results) {
		$buffer .= $CRLF . $CRLF . "####################################################################################################" . $CRLF . "# Item Type / Attribute Type relationships" . $CRLF
				. "####################################################################################################";

		$attr_inserts = "";

		$list_of_attribute_types = NULL;
		while ($site_attribute_type_r = db_fetch_assoc($results)) {
			$list_of_attribute_types[] = $site_attribute_type_r['s_attribute_type'];

			$attr_inserts .= $CRLF . "INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) " . "VALUES ( " . "'" . $site_attribute_type_r['s_attribute_type'] . "', " . "'" . addslashes($site_attribute_type_r['description'])
					. "', " . "'" . addslashes($site_attribute_type_r['prompt']) . "', " . "'" . addslashes($site_attribute_type_r['input_type']) . "', " . "'" . addslashes($site_attribute_type_r['display_type']) . "', " . "'" . $site_attribute_type_r['s_field_type'] . "', " . "'"
					. $site_attribute_type_r['site_type'] . "' );";

		}
		db_fetch_assoc($results);

		$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin Attribute Type(s)" . $CRLF . "#" . $CRLF;

		$buffer .= $attr_inserts;

		$results = fetch_site_item_attribute_type_rs($site_type_r['site_type']);
		if ($results) {
			$buffer .= $CRLF . $CRLF . "#" . $CRLF . "# Site Plugin Item Attribute Type Relationship(s)" . $CRLF . "#" . $CRLF;
			while ($site_item_attribute_type_r = db_fetch_assoc($results)) {
				$buffer .= $CRLF . "INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) " . "VALUES ( " . "'" . $site_item_attribute_type_r['s_item_type'] . "', " . "'" . $site_item_attribute_type_r['s_attribute_type'] . "', " . " "
						. $site_item_attribute_type_r['order_no'] . ", " . "'" . addslashes($site_item_attribute_type_r['prompt']) . "', " . "'" . ifempty($site_item_attribute_type_r['compulsory_ind'], 'N') . "' );";
			}
			db_fetch_assoc($results);
		}
	}

	$buffer .= $CRLF;

	return $buffer;
}

if (strlen($HTTP_VARS['op']) == 0)
	$HTTP_VARS['op'] = 'list_site_plugins';

if ($HTTP_VARS['op'] == 'insert_site_plugin') {
	if (!is_exists_site_plugin($HTTP_VARS['site_type'], FALSE)) {
		// first of all we need to derive the order_no
		$max_order_no = fetch_max_site_plugin_order_no();
		if ($max_order_no !== FALSE && is_numeric($max_order_no))
			$order_no = $max_order_no + 1;
		else
			$order_no = 1; // first plugin

		if (insert_s_site_plugin($HTTP_VARS['site_type'], $HTTP_VARS['classname'], $order_no, $HTTP_VARS['title'], $HTTP_VARS['image'], $HTTP_VARS['description'], $HTTP_VARS['external_url'], $HTTP_VARS['items_per_page'], $HTTP_VARS['more_info_url'])) {
			// return to edit form, so rest of site plugin information can be populated.
			$HTTP_VARS['op'] = 'list_site_plugins';
		} else {
			$errors[] = array('error' => 'Site Plugin not inserted', 'detail' => db_error());
			$HTTP_VARS['op'] = 'new_site_plugin';
		}
	} else {
		$errors[] = array('error' => 'Site Plugin exists', 'detail' => db_error());
		$HTTP_VARS['op'] = 'new_site_plugin';
	}
} else if ($HTTP_VARS['op'] == 'update_site_plugin') {
	if (is_exists_site_plugin($HTTP_VARS['site_type'], FALSE)) {
		if (!update_s_site_plugin($HTTP_VARS['site_type'], $HTTP_VARS['classname'], FALSE, $HTTP_VARS['title'], $HTTP_VARS['image'], $HTTP_VARS['description'], $HTTP_VARS['external_url'], $HTTP_VARS['items_per_page'], $HTTP_VARS['more_info_url'])) {
			$errors[] = array('error' => 'Site Plugin not updated', 'detail' => db_error());
		}
	} else {
		$errors[] = array('error' => 'Site Plugin not found.', 'detail' => '');
	}

	// at the end return to list site plugins
	$HTTP_VARS['op'] = 'list_site_plugins';
} else if ($HTTP_VARS['op'] == 'update_site_plugins') {
	if (is_not_empty_array($HTTP_VARS['order_no'])) {
		for ($i = 0; $i < count($HTTP_VARS['order_no']); $i++) {
			if (strlen($HTTP_VARS['order_no'][$i]) > 0) {
				if (!update_s_site_plugin($HTTP_VARS['site_type'][$i], FALSE, $HTTP_VARS['order_no'][$i], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE))
					$errors[] = array('error' => 'Site Plugin Link not updated', 'detail' => db_error());
			}//else ignore
		}
	}

	// return to edit mode
	$HTTP_VARS['op'] = 'list_site_plugins';
} else if ($HTTP_VARS['op'] == 'delete') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		if ($HTTP_VARS['confirmed'] == 'false') {
			// return to edit form
			$HTTP_VARS['op'] = 'list_site_plugins';
		} else {
			if ($HTTP_VARS['confirmed'] != 'true') {
				// Get the theme specific source of the image.
				echo "<h3>Delete Site Plugin</h3>";

				$op_confirm_prompt .= "Are you sure you want to delete Site Plugin \"" . $site_plugin_r['site_type'] . " - " . $site_plugin_r['title'] . "\"?";
				echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
			} else { // $HTTP_VARS['confirmed'] == 'true'
				// perform the delete process.
				delete_s_site_plugin_s_attribute_type_lookup_map($HTTP_VARS['site_type']);
				delete_s_site_plugin_s_attribute_type_map($HTTP_VARS['site_type']);
				delete_s_site_plugin_input_field($HTTP_VARS['site_type']);
				delete_s_site_plugin_link($HTTP_VARS['site_type']);
				delete_s_site_plugin_conf($HTTP_VARS['site_type']);
				delete_s_site_plugin($HTTP_VARS['site_type']);

				// return to edit form
				$HTTP_VARS['op'] = 'list_site_plugins';
			}
		}
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'update_site_plugin_item_types') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		$results = fetch_site_attribute_type_rs($HTTP_VARS['site_type']);
		if ($results) {
			$site_attribute_type_r = array();
			while ($site_plugin_attribute_type_r = db_fetch_assoc($results)) {
				$site_attribute_type_r[] = $site_plugin_attribute_type_r['s_attribute_type'];
			}
			db_free_result($results);
		}

		if (is_not_empty_array($site_attribute_type_r)) {
			if (is_not_empty_array($HTTP_VARS['s_item_type'])) {
				reset($HTTP_VARS['s_item_type']);
				foreach ($HTTP_VARS['s_item_type'] as $v_s_item_type => $value) {
					if (is_exists_item_type($v_s_item_type)) {
						// now we need to transfer in 
						if ($value == 'exclude') {
							$site_item_attribute_type_rs = array();
							// so this is the list of attributes currently attached to the s_item_type
							$results2 = fetch_site_item_attribute_type_rs($HTTP_VARS['site_type'], $v_s_item_type);
							if ($results2) {
								while ($attribute_type_r = db_fetch_assoc($results2)) {
									$site_item_attribute_type_rs[] = $attribute_type_r;
								}
								db_free_result($results2);
							}

							$delete = TRUE;
							reset($site_item_attribute_type_rs);
							foreach ($site_item_attribute_type_rs as $site_item_attribute_type_r) {
								if (!is_s_item_attribute_type_deletable($v_s_item_type, $site_item_attribute_type_r['s_attribute_type'], $site_item_attribute_type_r['order_no'])) {
									$errors[] = array('error' => 'Dependent Item Attribute records exist', 'detail' => 's_item_type=' . $v_s_item_type . ', s_attribute_type=' . $site_item_attribute_type_r['s_attribute_type'] . ', order_no=' . $site_item_attribute_type_r['order_no']);
									$delete = FALSE;
								}
							}

							if ($delete) {
								reset($site_item_attribute_type_rs);
								foreach ($site_item_attribute_type_rs as $site_item_attribute_type_r) {
									if (!delete_s_item_attribute_type($v_s_item_type, $site_item_attribute_type_r['s_attribute_type'], $site_item_attribute_type_r['order_no']))
										$errors[] = array('error' => 'System Item Attribute Type (s_item_type=' . $v_s_item_type . ', s_attribute_type=' . $site_item_attribute_type_r['s_attribute_type'] . ', order_no=' . $site_item_attribute_type_r['order_no'] . ') not deleted', 'detail' => db_error());
								}
							}
						} else if ($value == 'include') {
							for ($i = 0; $i < count($site_attribute_type_r); $i++) {
								if (!is_exists_item_attribute_type($v_s_item_type, $site_attribute_type_r[$i], 0)) {
									if (!insert_s_item_attribute_type($v_s_item_type, $site_attribute_type_r[$i], 0, NULL, 'N', 'N', 'N', 'N'))
										$errors[] = array('error' => 'System Item Attribute Type (s_item_type=' . $v_s_item_type . ', s_attribute_type=' . $site_item_attribute_type_r['s_attribute_type'] . ', order_no=' . $site_attribute_type_r['order_no'] . ') not inserted', 'detail' => db_error());
								}
							}
						}
					}
				}
			}//if(is_not_empty_array($HTTP_VARS['s_item_type']))

			// return to edit mode
			$HTTP_VARS['op'] = 'edit_site_plugin_item_types';

		} else { //if(is_not_empty_array($site_attribute_type_r))
			$errors[] = array('error' => 'Site Plugin attribute type\'s not found');
			$HTTP_VARS['op'] = 'list_site_plugins';
		}
	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'update_site_plugin_links') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		if (is_not_empty_array($HTTP_VARS['exists_ind'])) {
			for ($i = 0; $i < count($HTTP_VARS['exists_ind']); $i++) {
				if (strlen($HTTP_VARS['s_item_type_group'][$i]) > 0 && strlen($HTTP_VARS['s_item_type'][$i]) > 0 && is_numeric($HTTP_VARS['order_no'][$i]) && strlen($HTTP_VARS['description'][$i]) > 0 && (strlen($HTTP_VARS['url'][$i]) > 0 || strlen($HTTP_VARS['title_url'][$i]) > 0)) {
					if ($HTTP_VARS['exists_ind'][$i] == 'N') {
						if (!insert_s_site_plugin_link($HTTP_VARS['site_type'], $HTTP_VARS['s_item_type_group'][$i], $HTTP_VARS['s_item_type'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['description'][$i], $HTTP_VARS['url'][$i], $HTTP_VARS['title_url'][$i]))
							$errors[] = array('error' => 'Site Plugin Link not inserted', 'detail' => db_error());
					} else if (is_numeric($HTTP_VARS['sequence_number'][$i])) {
						if (!update_s_site_plugin_link($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number'][$i], $HTTP_VARS['s_item_type_group'][$i], $HTTP_VARS['s_item_type'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['description'][$i], $HTTP_VARS['url'][$i], $HTTP_VARS['title_url'][$i]))
							$errors[] = array('error' => 'Site Plugin Link not updated', 'detail' => db_error());
					}
				}//else ignore
			}
		}

		// return to edit mode
		$HTTP_VARS['op'] = 'edit_site_plugin_links';
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'update_site_plugin_confs') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		if (is_not_empty_array($HTTP_VARS['exists_ind'])) {
			for ($i = 0; $i < count($HTTP_VARS['exists_ind']); $i++) {
				if (strlen($HTTP_VARS['name'][$i]) > 0 && strlen($HTTP_VARS['keyid'][$i]) > 0) {
					if ($HTTP_VARS['exists_ind'][$i] == 'N') {
						if (!insert_s_site_plugin_conf($HTTP_VARS['site_type'], $HTTP_VARS['name'][$i], $HTTP_VARS['keyid'][$i], $HTTP_VARS['value'][$i], $HTTP_VARS['description'][$i]))
							$errors[] = array('error' => 'Site Plugin Configuration not inserted', 'detail' => db_error());
					} else {
						if (!update_s_site_plugin_conf($HTTP_VARS['site_type'], $HTTP_VARS['name'][$i], $HTTP_VARS['keyid'][$i], $HTTP_VARS['value'][$i], $HTTP_VARS['description'][$i]))
							$errors[] = array('error' => 'Site Plugin Configuration not updated', 'detail' => db_error());
					}
				}//else ignore
			}
		}

		// return to edit mode
		$HTTP_VARS['op'] = 'edit_site_plugin_confs';
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'update_site_plugin_input_fields') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		if (is_not_empty_array($HTTP_VARS['exists_ind'])) {
			for ($i = 0; $i < count($HTTP_VARS['exists_ind']); $i++) {
				if (strlen($HTTP_VARS['field'][$i]) > 0 && strlen($HTTP_VARS['order_no'][$i]) > 0) {
					if ($HTTP_VARS['exists_ind'][$i] == 'N') {
						if (!insert_s_site_plugin_input_field($HTTP_VARS['site_type'], $HTTP_VARS['field'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['description'][$i], $HTTP_VARS['prompt'][$i], $HTTP_VARS['field_type'][$i], $HTTP_VARS['default_value'][$i], $HTTP_VARS['refresh_mask'][$i]))
							$errors[] = array('error' => 'Site Plugin Input Field not inserted', 'detail' => db_error());
					} else {
						if (!update_s_site_plugin_input_field($HTTP_VARS['site_type'], $HTTP_VARS['field'][$i], $HTTP_VARS['order_no'][$i], $HTTP_VARS['description'][$i], $HTTP_VARS['prompt'][$i], $HTTP_VARS['field_type'][$i], $HTTP_VARS['default_value'][$i], $HTTP_VARS['refresh_mask'][$i]))
							$errors[] = array('error' => 'Site Plugin Input Field not updated', 'detail' => db_error());
					}
				}//else ignore
			}
		}

		// return to edit mode
		$HTTP_VARS['op'] = 'edit_site_plugin_input_fields';
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'update_site_plugin_s_attribute_type_maps') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		if (is_not_empty_array($HTTP_VARS['exists_ind'])) {
			for ($i = 0; $i < count($HTTP_VARS['exists_ind']); $i++) {
				if (strlen($HTTP_VARS['variable'][$i]) > 0 && strlen($HTTP_VARS['s_item_type_group'][$i]) > 0 && strlen($HTTP_VARS['s_item_type'][$i]) > 0 && strlen($HTTP_VARS['s_attribute_type'][$i]) > 0) {
					if ($HTTP_VARS['exists_ind'][$i] == 'N') {
						if (!insert_s_site_plugin_s_attribute_type_map($HTTP_VARS['site_type'], $HTTP_VARS['variable'][$i], $HTTP_VARS['s_item_type_group'][$i], $HTTP_VARS['s_item_type'][$i], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['lookup_attribute_val_restrict_ind'][$i]))
							$errors[] = array('error' => 'Site Plugin Attribute Type Map not inserted', 'detail' => db_error());
					} else if (is_numeric($HTTP_VARS['sequence_number'][$i])) {
						if (!update_s_site_plugin_s_attribute_type_map($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number'][$i], $HTTP_VARS['s_item_type_group'][$i], $HTTP_VARS['s_item_type'][$i], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['lookup_attribute_val_restrict_ind'][$i]))
							$errors[] = array('error' => 'Site Plugin Attribute Type Map not updated', 'detail' => db_error());
					}
				}//else ignore
			}
		}

		// return to edit mode
		$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_maps';
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'update_site_plugin_s_attribute_type_lookup_maps') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		if (is_not_empty_array($HTTP_VARS['s_attribute_type'])) {
			for ($i = 0; $i < count($HTTP_VARS['s_attribute_type']); $i++) {
				if (strlen($HTTP_VARS['s_attribute_type'][$i]) > 0 && strlen($HTTP_VARS['value'][$i]) > 0 && strlen($HTTP_VARS['lookup_attribute_val'][$i]) > 0) {
					if ($HTTP_VARS['exists_ind'][$i] == 'N') {
						if (!insert_s_site_plugin_s_attribute_type_lookup_map($HTTP_VARS['site_type'], $HTTP_VARS['s_attribute_type'][$i], $HTTP_VARS['value'][$i], $HTTP_VARS['lookup_attribute_val'][$i]))
							$errors[] = array('error' => 'Site Plugin Lookup Attribute Type Map not inserted', 'detail' => db_error());
					} else if (is_numeric($HTTP_VARS['sequence_number'][$i])) {
						if (!update_s_site_plugin_s_attribute_type_lookup_map($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number'][$i], $HTTP_VARS['lookup_attribute_val'][$i]))
							$errors[] = array('error' => 'Site Plugin Lookup Attribute Type Map not updated', 'detail' => db_error());
					}
				}//else ignore
			}
		}

		// return to edit mode
		$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_lookup_maps';
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'delete_site_plugin_link') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		$site_plugin_link_r = fetch_site_plugin_link_r($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number']);
		if (is_not_empty_array($site_plugin_link_r)) {
			if ($HTTP_VARS['confirmed'] == 'false') {
				// return to edit mode
				$HTTP_VARS['op'] = 'edit_site_plugin_links';
			} else {
				if ($HTTP_VARS['confirmed'] != 'true') {
					echo ("\n<h3>Delete " . $site_plugin_r['title'] . " Site Plugin Link</h3>");

					$op_confirm_prompt .= "Are you sure you want to delete Site Plugin Link (" . $site_plugin_link_r['description'] . ")?";
					echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
				} else { // $HTTP_VARS['confirmed'] == 'true'
					delete_s_site_plugin_link($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number']);

					// return to edit mode
					$HTTP_VARS['op'] = 'edit_site_plugin_links';
				}
			}
		} else {
			$errors[] = array('error' => 'Site Plugin Link not found');
			$HTTP_VARS['op'] = 'edit_site_plugin_links';
		}
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'delete_site_plugin_conf') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		$site_plugin_conf_r = fetch_site_plugin_conf_r($HTTP_VARS['site_type'], $HTTP_VARS['name'], $HTTP_VARS['keyid']);
		if (is_not_empty_array($site_plugin_conf_r)) {
			if ($HTTP_VARS['confirmed'] == 'false') {
				// return to edit mode
				$HTTP_VARS['op'] = 'edit_site_plugin_confs';
			} else {
				if ($HTTP_VARS['confirmed'] != 'true') {
					echo ("\n<h3>Delete " . $site_plugin_r['title'] . " Site Plugin Configuration</h3>");

					$op_confirm_prompt .= "Are you sure you want to delete Site Plugin Configuration (name=" . $site_plugin_conf_r['name'] . ", keyid=" . $site_plugin_conf_r['keyid'] . ", value=" . $site_plugin_conf_r['value'] . ")?";
					echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
				} else { // $HTTP_VARS['confirmed'] == 'true'
					delete_s_site_plugin_conf($HTTP_VARS['site_type'], $HTTP_VARS['name'], $HTTP_VARS['keyid']);

					// return to edit mode
					$HTTP_VARS['op'] = 'edit_site_plugin_confs';
				}
			}
		} else {
			$errors[] = array('error' => 'Site Plugin Configuration not found');
			$HTTP_VARS['op'] = 'edit_site_plugin_confs';
		}
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'delete_site_plugin_input_field') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		$site_plugin_input_field_r = fetch_site_plugin_input_field_r($HTTP_VARS['site_type'], $HTTP_VARS['field']);
		if (is_not_empty_array($site_plugin_input_field_r)) {
			if ($HTTP_VARS['confirmed'] == 'false') {
				// return to edit mode
				$HTTP_VARS['op'] = 'edit_site_plugin_input_fields';
			} else {
				if ($HTTP_VARS['confirmed'] != 'true') {
					echo ("\n<h3>Delete " . $site_plugin_r['title'] . " Site Plugin Input Field</h3>");

					$op_confirm_prompt .= "Are you sure you want to delete Site Plugin Input Field (field=" . $site_plugin_input_field_r['field'] . ", prompt=" . $site_plugin_input_field_r['prompt'] . ", description=" . $site_plugin_input_field_r['description'] . ")?";
					echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
				} else { // $HTTP_VARS['confirmed'] == 'true'
					delete_s_site_plugin_input_field($HTTP_VARS['site_type'], $HTTP_VARS['field']);

					// return to edit mode
					$HTTP_VARS['op'] = 'edit_site_plugin_input_fields';
				}
			}
		} else {
			$errors[] = array('error' => 'Site Plugin Input Field not found');
			$HTTP_VARS['op'] = 'edit_site_plugin_input_fields';
		}
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'delete_site_plugin_s_attribute_type_map') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		$site_plugin_s_attribute_type_map_r = fetch_site_plugin_s_attribute_type_map_r($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number']);
		if (is_not_empty_array($site_plugin_s_attribute_type_map_r)) {
			if ($HTTP_VARS['confirmed'] == 'false') {
				// return to edit mode
				$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_maps';
			} else {
				if ($HTTP_VARS['confirmed'] != 'true') {
					echo ("\n<h3>Delete " . $site_plugin_r['title'] . " Site Plugin Attribute Type Map</h3>");

					$op_confirm_prompt .= "Are you sure you want to delete Site Plugin Attribute Type Map (variable=" . $site_plugin_s_attribute_type_map_r['variable'] . ", s_item_type_group=" . $site_plugin_s_attribute_type_map_r['s_item_type_group'] . ", s_item_type="
							. $site_plugin_s_attribute_type_map_r['s_item_type'] . ", s_attribute_type=" . $site_plugin_s_attribute_type_map_r['s_attribute_type'] . ")?";
					echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
				} else { // $HTTP_VARS['confirmed'] == 'true'
					delete_s_site_plugin_s_attribute_type_map($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number']);

					// return to edit mode
					$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_maps';
				}
			}
		} else {
			$errors[] = array('error' => 'Site Plugin Attribute Type Map not found');
			$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_maps';
		}
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'delete_site_plugin_s_attribute_type_lookup_map') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		$site_plugin_s_attribute_type_lookup_map_r = fetch_site_plugin_s_attribute_type_lookup_map_r($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number']);
		if (is_not_empty_array($site_plugin_s_attribute_type_lookup_map_r)) {
			if ($HTTP_VARS['confirmed'] == 'false') {
				// return to edit mode
				$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_lookup_maps';
			} else {
				if ($HTTP_VARS['confirmed'] != 'true') {
					echo ("\n<h3>Delete " . $site_plugin_r['title'] . " Site Plugin Lookup Attribute Type Map</h3>");

					$op_confirm_prompt .= "Are you sure you want to delete Site Plugin Lookup Attribute Type Map (s_attribute_type=" . $site_plugin_s_attribute_type_lookup_map_r['s_attribute_type'] . ", value=" . $site_plugin_s_attribute_type_lookup_map_r['value'] . ", lookup_attribute_val="
							. $site_plugin_s_attribute_type_lookup_map_r['lookup_attribute_val'] . ")?";
					echo get_op_confirm_form($PHP_SELF, $op_confirm_prompt, $HTTP_VARS);
				} else { // $HTTP_VARS['confirmed'] == 'true'
					delete_s_site_plugin_s_attribute_type_lookup_map($HTTP_VARS['site_type'], $HTTP_VARS['sequence_number']);

					// return to edit mode
					$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_lookup_maps';
				}
			}
		} else {
			$errors[] = array('error' => 'Site Plugin Lookup Attribute Type Map not found');
			$HTTP_VARS['op'] = 'edit_site_plugin_s_attribute_type_lookup_maps';
		}
	} else {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
}

if ($HTTP_VARS['op'] == 'edit_site_plugin_item_types') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		$results = fetch_site_attribute_type_rs($HTTP_VARS['site_type']);
		if ($results) {
			$site_attribute_type_r = array();
			while ($site_plugin_attribute_type_r = db_fetch_assoc($results)) {
				$site_attribute_type_r[] = $site_plugin_attribute_type_r['s_attribute_type'];
			}
			db_free_result($results);
		}

		if (is_not_empty_array($site_attribute_type_r)) {
			echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");
			echo ("\n<h3>Edit " . $site_plugin_r['title'] . " Site Plugin System Item Types</h3>");

			if (is_not_empty_array($errors))
				echo format_error_block($errors);

            echo get_javascript('admin/select.js');

			echo ("\n<form name=\"edit_site_plugin_item_types\" action=\"$PHP_SELF\" method=\"POST\">");
			echo ("\n<input type=\"hidden\" name=\"op\" value=\"" . $HTTP_VARS['op'] . "\">");
			echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");
			echo ("\n<input type=\"hidden\" name=\"site_type\" value=\"" . $HTTP_VARS['site_type'] . "\">");

			echo ("<table>");
			$exists_item_type_rs = array();
			$not_exists_item_type_rs = array();

			$results = fetch_item_type_rs();
			if ($results) {
				while ($item_type_r = db_fetch_assoc($results)) {
					$exists = FALSE;
					for ($i = 0; $i < count($site_attribute_type_r); $i++) {
						if (is_exists_item_attribute_type($item_type_r['s_item_type'], $site_attribute_type_r[$i])) {
							$exists = TRUE;
						}
					}

					if ($exists)
						$exists_item_type_rs[] = $item_type_r;
					else
						$not_exists_item_type_rs[] = $item_type_r;
				}
				db_free_result($results);
			}

			echo ("<tr class=\"navbar\">
			<th>Exclude</th>
			<th></th>
			<th>Include</th>
			</tr>");
			echo ("<tr><td><select name=\"from_item_types\" class=\"includeExcludeWidget\" size=\"15\" MULTIPLE>");
			for ($i = 0; $i < count($not_exists_item_type_rs); $i++) {
				echo ("<option value=\"" . $not_exists_item_type_rs[$i]['s_item_type'] . "\">" . $not_exists_item_type_rs[$i]['s_item_type'] . " - " . $not_exists_item_type_rs[$i]['description'] . "\n");
			}
			echo ("</select></td>");

			echo ("<td>");
			echo ("<input type=\"button\" class=\"button\" value=\">\" onClick=\"moveOptions(this.form, 's_item_type', this.form['from_item_types'], this.form['to_item_types']);\">"
					. "<input type=\"button\" class=\"button\" value=\">>\" onClick=\"moveAllOptions(this.form, 's_item_type', this.form['from_item_types'], this.form['to_item_types']);\">");

			echo ("<input type=\"button\" class=\"button\" value=\"<\" onClick=\"moveOptions(this.form, 's_item_type', this.form['to_item_types'], this.form['from_item_types']);\">"
					. "<input type=\"button\" class=\"button\" value=\"<<\" onClick=\"moveAllOptions(this.form, 's_item_type', this.form['to_item_types'], this.form['from_item_types']);\">");

			echo ("</td>");

			echo ("<td><select name=\"to_item_types\" class=\"includeExcludeWidget\" size=\"15\" MULTIPLE>");
			for ($i = 0; $i < count($exists_item_type_rs); $i++) {
				echo ("<option value=\"" . $exists_item_type_rs[$i]['s_item_type'] . "\">" . $exists_item_type_rs[$i]['s_item_type'] . " - " . $exists_item_type_rs[$i]['description'] . "\n");
			}
			echo ("</select></td>");
			echo ("</table>");

			for ($i = 0; $i < count($not_exists_item_type_rs); $i++) {
				echo ("\n<input type=\"hidden\" name=\"s_item_type[" . $not_exists_item_type_rs[$i]['s_item_type'] . "]\" value=\"exclude\">");
			}

			for ($i = 0; $i < count($exists_item_type_rs); $i++) {
				echo ("\n<input type=\"hidden\" name=\"s_item_type[" . $exists_item_type_rs[$i]['s_item_type'] . "]\" value=\"include\">");
			}

			echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='" . $HTTP_VARS['op'] . "'; this.form.submit();\">");
			echo ("\n<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update_site_plugin_item_types'; this.form.submit();\">");

			echo ("</form>");

		} else { //if(is_not_empty_array($attribute_type_r))
			$errors[] = array('error' => 'Site Plugin attribute type\'s not found');
			$HTTP_VARS['op'] = 'list_site_plugins';
		}
	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'edit_site_plugin_links') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");
		echo ("\n<h3>Edit " . $site_plugin_r['title'] . " Site Plugin Links</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		display_edit_table($HTTP_VARS['op'], 'update_site_plugin_links', array('Order', 'Item Type<br />Group', 'Item Type', 'Description', 'URL', 'Title URL', ''), 'display_site_plugin_link_row', array('site_type' => $HTTP_VARS['site_type']), fetch_site_plugin_link_rs($HTTP_VARS['site_type']));
	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'edit_site_plugin_confs') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");
		echo ("\n<h3>Edit " . $site_plugin_r['title'] . " Site Plugin Configuration</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		display_edit_table($HTTP_VARS['op'], 'update_site_plugin_confs', array('Name', 'Key ID', 'Description', 'Value', ''), 'display_site_plugin_conf_row', array('site_type' => $HTTP_VARS['site_type']), fetch_site_plugin_conf_rs($HTTP_VARS['site_type']));
	} else//if(is_not_empty_array($site_plugin_r))
 {
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'edit_site_plugin_input_fields') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");
		echo ("\n<h3>Edit " . $site_plugin_r['title'] . " Site Plugin Input Fields</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		display_edit_table($HTTP_VARS['op'], 'update_site_plugin_input_fields', array('Order No', 'Field Name', 'Description', 'Prompt', 'Field Type', 'Refresh Mask', 'Default Value', ''), 'display_site_plugin_input_field_row', array('site_type' => $HTTP_VARS['site_type']),
				fetch_site_plugin_input_field_rs($HTTP_VARS['site_type']));
	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'edit_site_plugin_s_attribute_type_maps') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");
		echo ("\n<h3>Edit " . $site_plugin_r['title'] . " Site Plugin Attribute Type Map</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		display_edit_table($HTTP_VARS['op'], 'update_site_plugin_s_attribute_type_maps', array('Variable', 'Item Type<br />Group', 'Item Type', 'Attribute Type', 'Restrict to <br />Lookup Values', ''), 'display_site_plugin_s_attribute_type_map_row', array('site_type' => $HTTP_VARS['site_type']),
				fetch_site_plugin_s_attribute_type_map_rs($HTTP_VARS['site_type']));
	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'edit_site_plugin_s_attribute_type_lookup_maps') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");
		echo ("\n<h3>Edit " . $site_plugin_r['title'] . " Site Plugin Lookup Attribute Type Map</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		echo get_lookup_attribute_type_array();

		display_edit_table($HTTP_VARS['op'], 'update_site_plugin_s_attribute_type_lookup_maps', array('Attribute Type', 'Site Value', 'Lookup Attribute Value', ''), 'display_site_plugin_s_attribute_type_lookup_map_row', array('site_type' => $HTTP_VARS['site_type']),
				fetch_site_plugin_s_attribute_type_lookup_map_rs($HTTP_VARS['site_type']));

	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'edit') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");

		echo ("\n<h3>Edit Site Plugin</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		echo ("\n<form name=\"s_site_plugin\" action=\"$PHP_SELF\" method=\"POST\">");
		echo ("\n<input type=\"hidden\" name=\"op\" value=\"" . $HTTP_VARS['op'] . "\">");
		echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");

		echo ("\n<table>");
		display_edit_site_plugin($site_plugin_r, $HTTP_VARS);
		echo ("\n</table>");

		echo (format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory')));

		if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
			echo ("\n<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.op.value='update_site_plugin'; this.form.submit();}\">");
		else
			echo ("\n<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form.op.value='update_site_plugin'; this.form.submit();\">");

		echo ("\n</form>");

	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'new_site_plugin') {
	echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");

	echo ("\n<h3>New Site Plugin</h3>");

	if (is_not_empty_array($errors))
		echo format_error_block($errors);

	echo ("\n<form name=\"s_site_plugin\" action=\"$PHP_SELF\" method=\"POST\">");
	echo ("\n<input type=\"hidden\" name=\"op\" value=\"insert_site_plugin\">");
	echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");

	echo ("\n<table>");
	display_edit_site_plugin(NULL, $HTTP_VARS);
	echo ("\n</table>");

	echo (format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory')));

	if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
		echo ("\n<input type=\"button\" class=\"button\" value=\"Insert\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
	else
		echo ("\n<input type=\"button\" class=\"button\" value=\"Insert\" onclick=\"this.form.submit();\">");

	echo ("\n</form>");
} else if ($HTTP_VARS['op'] == 'sql') {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		header("Cache-control: no-store");
		header("Pragma: no-store");
		header("Expires: 0");
		header("Content-disposition: attachment; filename=" . $HTTP_VARS['site_type'] . ".sql");
		header("Content-type: application/octet-stream");

		echo generate_site_plugin_sql($site_plugin_r);
	} else {
		echo format_error_block(array('error' => 'Site Plugin not found'));
	}
} else if ($HTTP_VARS['op'] == 'maintain_site_plugin_install') // special function to allow upload of file into database,etc
 {
	$site_plugin_r = fetch_site_plugin_r($HTTP_VARS['site_type']);
	if (is_not_empty_array($site_plugin_r)) {
		if (strlen($HTTP_VARS['import_file']) > 0 && file_exists('./admin/s_site_plugin/upload/' . $HTTP_VARS['import_file'])) {
			@set_time_limit(600);

			echo ("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&site_type=" . $HTTP_VARS['site_type'] . "&op=maintain_site_plugin_install\">Back to CSV File List</a>]</div>");

			echo ("\n<h3>" . $site_plugin_r['title'] . " Installation Maintenance</h3>");

			echo ("<p>Importing " . $HTTP_VARS['import_file'] . "...</p>");

			$jobObj->printJobProgressBar($HTTP_VARS['import_file']);

		} else { //if(strlen($HTTP_VARS['import_file'])>0)
			echo ("\n<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list_site_plugins\">Back to Main</a>]</p>");
			echo ("\n<h3>" . $site_plugin_r['title'] . " Installation Maintenance</h3>");

			$classname = "Install_" . $HTTP_VARS['site_type'];
			include_once("./admin/s_site_plugin/sql/" . $HTTP_VARS['site_type'] . ".install.class.php");
			$installPlugin = new $classname();

			$recordCount = $installPlugin->getRecordCount();
			if (is_numeric($recordCount)) {
				echo ("\n<p>Record Count: " . $recordCount . "</p>");
			}

			$lastUpdated = $installPlugin->getLastUpdated();
			if ($lastUpdated !== FALSE) {
				$lastUpdatedString = get_localised_timestamp($cfg_date_mask, $lastUpdated);
			}

			if (strlen($lastUpdated) > 0) {
				echo ("\n<p>Last updated: " . $lastUpdatedString . "</p>");
			}

			echo ("\n<h4>Listing <code>./admin/s_site_plugin/upload/</code> directory</h4>");
			echo ("\n<table>");
			echo ("\n<tr class=\"navbar\">" . "<th>CSV File</th>" . "<th>Action</th>" . "\n</tr>");

			$file_list_r = get_file_list('./admin/s_site_plugin/upload/', 'csv');
			if (is_not_empty_array($file_list_r)) {
				$toggle = TRUE;
				reset($file_list_r);
				foreach ($file_list_r as $file) {
					$color = ($toggle ? "oddRow" : "evenRow");
					$toggle = !$toggle;

					echo ("\n<form name=\"import_file\" action=\"$PHP_SELF\" method=\"GET\">" . "\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">" . "\n<input type=\"hidden\" name=\"site_type\" value=\"" . $HTTP_VARS['site_type'] . "\">"
							. "\n<input type=\"hidden\" name=\"op\" value=\"" . $HTTP_VARS['op'] . "\">" . "\n<input type=\"hidden\" name=\"import_file\" value=\"" . $file . "\">");

					echo ("\n<tr>");
					echo ("\n<td class=\"$color\">" . $file . "</td>");
					echo ("<td class=\"$color\"><input type=\"submit\" class=\"submit\" value=\"Import\"></td></tr>");

					echo ("\n</form>");

					echo ("</table>");
				}
			} else {
				echo ("</table>");
				echo ("<div class=\"error\">No files found</div>");
			}

			echo (format_help_block(array('text' => 'Upload CSV files directly (using FTP or equivalent) to the <code>./admin/s_site_plugin/upload/</code> directory.')));
		}
	} else { //if(is_not_empty_array($site_plugin_r))
		$errors[] = array('error' => 'Site Plugin not found');
		$HTTP_VARS['op'] = 'list_site_plugins';
	}
} else if ($HTTP_VARS['op'] == 'installsql') {
	execute_sql_install($ADMIN_TYPE, $HTTP_VARS['sqlfile'], $errors);
	$HTTP_VARS['op'] = 'list_site_plugins';
}

if ($HTTP_VARS['op'] == 'list_site_plugins') {
	echo ("<p>[<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new_site_plugin\">New Site Plugin</a>]</p>");

	if (is_not_empty_array($errors ?? ""))
		echo format_error_block($errors);

	$results = fetch_site_plugin_rs();
	if ($results) {
		echo ("\n<form name=\"s_site_plugin\" action=\"$PHP_SELF\" method=\"GET\">");
		echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");
		echo ("\n<input type=\"hidden\" name=\"op\" value=\"new_site_plugin\">");
		echo ("\n<input type=\"hidden\" name=\"site_type\" value=\"\">");

		echo ("<table>");
		echo ("<tr class=\"navbar\">" . "<th>Order</th>" . "<th>Site</th>" . "<th>Title</th>" . "<th colspan=3></th>" . "</tr>");

		$row = 0;
		while ($site_plugin_r = db_fetch_assoc($results)) {
			$href = "<a href=\"admin.php?type=$ADMIN_TYPE&op=edit&site_type=" . $site_plugin_r['site_type'] . "\">";

			echo ("<tr>");
			echo ("\n<td class=\"data\">" . get_input_field("order_no[$row]", NULL, NULL, "number(3)", "N", $site_plugin_r['order_no'], FALSE) . "</td>");
			echo ("<td class=\"data\">" . $site_plugin_r['site_type'] . "<input type=\"hidden\" name=\"site_type[$row]\" value=\"" . $site_plugin_r['site_type'] . "\"></td>");
			echo ("<td class=\"data\">" . $site_plugin_r['title'] . "</td>");

			echo ("<td class=\"data\">");
			echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&site_type=" . $site_plugin_r['site_type'] . "\">Edit</a>");
			echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&site_type=" . $site_plugin_r['site_type'] . "\">Delete</a>");
			echo ("</td>");

			echo ("<td class=\"data\">");
			echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit_site_plugin_item_types&site_type=" . $site_plugin_r['site_type'] . "\">Item Types</a>");
			echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit_site_plugin_links&site_type=" . $site_plugin_r['site_type'] . "\">Links</a>");
			echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit_site_plugin_confs&site_type=" . $site_plugin_r['site_type'] . "\">Configuration</a>");
			echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit_site_plugin_input_fields&site_type=" . $site_plugin_r['site_type'] . "\">Input Fields</a>");
			echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit_site_plugin_s_attribute_type_maps&site_type=" . $site_plugin_r['site_type'] . "\">Attribute Map</a>");
			echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit_site_plugin_s_attribute_type_lookup_maps&site_type=" . $site_plugin_r['site_type'] . "\">Lookup Attribute Map</a>");
			if (file_exists('./admin/s_site_plugin/sql/' . $site_plugin_r['site_type'] . '.install.class.php')) {
				echo (" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=maintain_site_plugin_install&site_type=" . $site_plugin_r['site_type'] . "\">Install&nbsp;Maintenance</a>");
			}
			echo ("</td>");

			echo ("<td class=\"data\">");
			echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=sql&site_type=" . $site_plugin_r['site_type'] . "&mode=job\">SQL</a>");
			echo ("</td>");
			echo ("</tr>");

			$row++;
		}
		db_free_result($results);

		echo ("</table>");

		echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"document.forms['navigate'].op.value='" . $HTTP_VARS['op'] . "'; document.forms['navigate'].submit();\">"
				. "<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form.op.value='update_site_plugins'; this.form.submit();\">");

		echo ("</form>");

	} else { //if($results)
		echo ("<p class=\"error\">No Site Plugins Installed</p>");
	}

	function is_not_exists_site_plugin($type) {
		return !is_exists_site_plugin($type, FALSE);
	}
	generate_sql_list($ADMIN_TYPE, 'Site Plugin', NULL, 'is_not_exists_site_plugin');
}
?>
