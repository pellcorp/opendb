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

include_once("./lib/user.php");
include_once("./lib/status_type.php");
include_once("./lib/install.php");

$_COLUMN_DESC = array('s_status_type' => 'Status Type', 'description' => 'Description', 'img' => 'Image', 'delete_ind' => 'Delete', 'change_owner_ind' => 'Change Owner', 'borrow_ind' => 'Borrow Support', 'status_comment_ind' => 'Status Comments', 'hidden_ind' => 'Hidden', 'default_ind' => 'Default',
		'closed_ind' => 'Closed');

$_COLUMN_HELP = array('delete_ind' => array('Item instances of this type can be deleted.'), 'change_owner_ind' => array('Item instances of this type can have their owner changed.'),
		'borrow_ind' => array('If \'Y\', items can be reserved / checked out / checked in / quick checked out.', 'If \'N\', items can not be reserved, but can be checked out if a reservation already exists.',),
		'default_ind' => array('Controls which type is checked by default for item input and searching, etc.'), 'hidden_ind' => array('Item is not visible unless Owner or user has Item Admin privilege.'),
		'status_comment_ind' => array('If \'Y\', status comment\'s will be visible.', 'If \'N\', status comment\'s will be invisible to all but admins and the owner of the item.'),
		'closed_ind' => array('This column, allows s_status_type records to be used in existing records, but no new ' . 'records can be created with this type, and existing records can not have their status ' . 'changed to types with closed_ind = \'Y\''));

function display_s_status_type_row($status_type_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	echo ("\n<tr>");

	echo ("\n<td class=\"data\">" . $status_type_r['s_status_type'] . "</td>");
	echo ("\n<td class=\"data\">" . $status_type_r['description'] . "</td>");

	echo ("<td class=\"data\">");
	// Get the theme specific source of the image.
	if (strlen($status_type_r['img']) > 0) {
		$src = theme_image_src($status_type_r['img']);
	}
	if ($src !== FALSE && strlen($src) > 0)
		echo ("<img src=\"$src\">");
	else
		echo ("&nbsp;");
	echo ("</td>");

	echo ("\n<td class=\"data\">" . ifempty($status_type_r['default_ind'], 'N') . "</td>");
	echo ("\n<td class=\"data\">" . $status_type_r['closed_ind'] . "</td>");

	echo ("\n<td class=\"data\"><a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_status_type=" . $status_type_r['s_status_type'] . "\">Edit</a>" . " / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&s_status_type=" . $status_type_r['s_status_type'] . "\">Delete</a></td>");

	echo ("</tr>");
}

function get_edit_form_tooltip($columnname) {
	global $_COLUMN_HELP;
	global $_COLUMN_DESC;

	$buffer = "return show_tooltip(";
	if (isset($_COLUMN_HELP[$columnname])) {
		$block = '';
		if (is_array($_COLUMN_HELP) && is_array($_COLUMN_HELP[$columnname])) {
			for ($i = 0; $i < count($_COLUMN_HELP[$columnname]); $i++) {
				if (is_array($_COLUMN_HELP[$columnname][$i])) {
					$block .= '<ul>';
					for ($j = 0; $j < count($_COLUMN_HELP[$columnname][$i]); $j++) {
						$block .= '<li>' . addslashes($_COLUMN_HELP[$columnname][$i][$j]) . '</li>';
					}
					$block .= '</ul>';
				} else {
					$block .= "<p>" . addslashes($_COLUMN_HELP[$columnname][$i]) . "</p>";
				}
			}
		}

		$buffer .= "'" . $block . "'";
	} else {
		$buffer .= "'No tooltip available'";
	}

	if (isset($_COLUMN_DESC[$columnname])) {
		$buffer .= ", '" . addslashes($_COLUMN_DESC[$columnname]) . "'";
	}

	$buffer .= ");";

	return $buffer;
}

function display_edit_form($status_type_r, $HTTP_VARS = NULL) {
	global $_COLUMN_DESC;

	if (is_array($status_type_r))
		echo get_input_field("s_status_type", NULL, $_COLUMN_DESC['s_status_type'], "readonly", "Y", $status_type_r['s_status_type'], TRUE, "%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('s_status_type') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	else
		echo get_input_field("s_status_type", NULL, $_COLUMN_DESC['s_status_type'], "text(1,1)", "Y", $HTTP_VARS['s_status_type'], TRUE, "%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('s_status_type') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	echo get_input_field("description", NULL, $_COLUMN_DESC['description'], "text(30,60)", "Y", ifempty($status_type_r['description'], $HTTP_VARS['description']), TRUE,
			"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('description') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	$field = get_input_field("img", NULL, $_COLUMN_DESC['img'], "url(15,*,\"gif,jpg,png\",N)", "N", ifempty($status_type_r['img'], $HTTP_VARS['img']), FALSE);
	$image_src = theme_image_src(ifempty($status_type_r['img'], $HTTP_VARS['img']));
	if ($image_src !== FALSE && strlen($image_src) > 0)
		$field .= " <img src=\"$image_src\">";

	echo format_field($_COLUMN_DESC['img'], $field, "%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('img') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	echo get_input_field("delete_ind", NULL, $_COLUMN_DESC['delete_ind'], "value_radio_grid('Y,N')", "N", ifempty($status_type_r['delete_ind'], $HTTP_VARS['delete_ind']), TRUE,
			"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('delete_ind') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("change_owner_ind", NULL, $_COLUMN_DESC['change_owner_ind'], "value_radio_grid('Y,N')", "N", ifempty($status_type_r['change_owner_ind'], ifempty($HTTP_VARS['change_owner_ind'], "N")), TRUE,
			"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('change_owner_ind') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	echo get_input_field("borrow_ind", NULL, $_COLUMN_DESC['borrow_ind'], "value_radio_grid('Y,N')", "N", ifempty($status_type_r['borrow_ind'], $HTTP_VARS['borrow_ind']), TRUE,
			"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('borrow_ind') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("status_comment_ind", NULL, $_COLUMN_DESC['status_comment_ind'], "value_radio_grid('Y,N')", "N", ifempty($status_type_r['status_comment_ind'], $HTTP_VARS['status_comment_ind']), TRUE,
			"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('status_comment_ind') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("hidden_ind", NULL, $_COLUMN_DESC['hidden_ind'], "value_radio_grid('Y,N')", "N", ifempty($status_type_r['hidden_ind'], $HTTP_VARS['hidden_ind']), TRUE,
			"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('hidden_ind') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	echo get_input_field("default_ind", NULL, $_COLUMN_DESC['default_ind'], "checkbox(Y,N)", "N", ifempty($status_type_r['default_ind'], $HTTP_VARS['default_ind']), TRUE,
			"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('default_ind') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	if (is_array($status_type_r)) {
		echo get_input_field("closed_ind", NULL, $_COLUMN_DESC['closed_ind'], "checkbox(Y,N)", "N", ifempty($status_type_r['closed_ind'], $HTTP_VARS['closed_ind']), TRUE,
				"%prompt% <a class=\"smlink\" href=\"#\" onmouseover=\"" . get_edit_form_tooltip('closed_ind') . "\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	}
}

if ($HTTP_VARS['op'] == 'delete') {
	if (is_valid_s_status_type($HTTP_VARS['s_status_type'])) {
		$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);

		if ($status_type_r['closed_ind'] != 'Y' && $status_type_r['default_ind'] == 'Y' && fetch_default_status_type_cnt() <= 1) {
			$errors[] = array('error' => 'Status Type not deleted', 'detail' => 'Status Type is default and cannot be deleted');
			$HTTP_VARS['op'] = NULL;
		} else if (is_exists_items_with_status_type($HTTP_VARS['s_status_type'])) { // Validate that no items are attached for this status type.
			$errors[] = array('error' => 'Status Type not deleted', 'detail' => 'Status Type cannot be deleted while ' . $status_type_r['description'] . ' item instance(s) exist.');
			$HTTP_VARS['op'] = NULL;
		} else {
			if ($HTTP_VARS['confirmed'] == 'true') {
				if (!delete_s_status_type($HTTP_VARS['s_status_type']))
					$errors[] = array('error' => 'Status Type not deleted', 'detail' => db_error());

				$HTTP_VARS['op'] = NULL;
			} else if ($HTTP_VARS['confirmed'] != 'false') {
				echo ("\n<h3>Delete Status Type</h3>");
				echo (get_op_confirm_form($PHP_SELF, "Are you sure you want to delete Status Type '" . $HTTP_VARS['s_status_type'] . " - " . $status_type_r['description'] . "'?", array('type' => $HTTP_VARS['type'], 'op' => 'delete', 's_status_type' => $HTTP_VARS['s_status_type'])));
			} else { // confirmation required.
				$HTTP_VARS['op'] = NULL;
			}
		}
	} else {
		$errors[] = array('error' => 'Invalid Status Type specified');
		$HTTP_VARS['op'] = NULL;
	}
} else if ($HTTP_VARS['op'] == 'update') {
	if (is_valid_s_status_type($HTTP_VARS['s_status_type'])) {
		$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);

		// If default_ind is currently 'Y', and being set to 'N', or closed_ind being set 'Y' from 'N'
		if ((($HTTP_VARS['default_ind'] != 'Y' && $status_type_r['default_ind'] == 'Y') || ($HTTP_VARS['closed_ind'] == 'Y' && $status_type_r['closed_ind'] != 'Y' && $status_type_r['default_ind'] == 'Y')) && fetch_default_status_type_cnt() <= 1) {
			$errors[] = array('error' => 'Status Type not updated', 'detail' => 'Status Type is default and cannot be closed');
		} else {
			if (!update_s_status_type($HTTP_VARS['s_status_type'], $HTTP_VARS['description'], $HTTP_VARS['img'], $HTTP_VARS['delete_ind'], $HTTP_VARS['change_owner_ind'], $HTTP_VARS['borrow_ind'], $HTTP_VARS['status_comment_ind'], $HTTP_VARS['hidden_ind'], $HTTP_VARS['default_ind'],
					$HTTP_VARS['closed_ind'])) {
				$errors[] = array('error' => 'Status Type not updated', 'detail' => db_error());
			}
		}

		$HTTP_VARS['op'] = 'edit';
	} else {
		$errors[] = array('error' => 'Invalid Status Type specified');
		$HTTP_VARS['op'] = NULL;
	}
} else if ($HTTP_VARS['op'] == 'insert') {
	$HTTP_VARS['s_status_type'] = strtoupper(substr(trim($HTTP_VARS['s_status_type']), 0, 1));

	if (strlen($HTTP_VARS['s_status_type']) > 0 && !is_valid_s_status_type($HTTP_VARS['s_status_type'])) {
		if (!insert_s_status_type($HTTP_VARS['s_status_type'], $HTTP_VARS['description'], $HTTP_VARS['img'], $HTTP_VARS['delete_ind'], $HTTP_VARS['change_owner_ind'], $HTTP_VARS['borrow_ind'], $HTTP_VARS['status_comment_ind'], $HTTP_VARS['hidden_ind'], $HTTP_VARS['default_ind'])) {
			$errors[] = array('error' => 'Status Type (' . $HTTP_VARS['s_status_type'] . ') not inserted', 'detail' => db_error());
		}

		$HTTP_VARS['op'] = 'edit';
	} else {
		if (strlen($HTTP_VARS['s_status_type']) > 0) {
			$errors[] = array('error' => 'Status Type exists');
		} else {
			$errors[] = array('error' => 'Invalid Status Type specified');
		}
		$HTTP_VARS['op'] = 'new';
	}
} else if ($HTTP_VARS['op'] == 'installsql') {
	execute_sql_install($ADMIN_TYPE, $HTTP_VARS['sqlfile'], $errors);
	$HTTP_VARS['op'] = NULL;
}

if ($HTTP_VARS['op'] == 'new' || $HTTP_VARS['op'] == 'edit') {
	echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</p>");

	if ($HTTP_VARS['op'] == 'edit') {
		$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);
		if ($status_type_r === FALSE)
			$errors[] = 'Status Type (' . $HTTP_VARS['s_status_type'] . ') not found';

		echo ("\n<h3>Edit Status Type</h3>");
		$save_op = 'update';
		$save_button = 'Update';
	} else {
		echo ("\n<h3>New Status Type</h3>");
		$save_op = 'insert';
		$save_button = 'Insert';
	}

	if (is_not_empty_array($errors))
		echo format_error_block($errors);

	echo ("\n<form name=\"s_status_type\" action=\"$PHP_SELF\" method=\"POST\">");

	echo ("\n<input type=\"hidden\" name=\"op\" value=\"$save_op\">");
	echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $HTTP_VARS['type'] . "\">");

	echo ("\n<table>");
	display_edit_form($status_type_r, $HTTP_VARS);
	echo ("\n</table>");

	echo (format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory')));

	if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
		echo ("\n<input type=\"button\" class=\"button\" value=\"$save_button\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
	else
		echo ("\n<input type=\"button\" class=\"button\" value=\"$save_button\" onclick=\"this.form.submit();\">");

	echo ("\n</form>");
} else if (strlen($HTTP_VARS['op']) == 0) {
	echo ("<p>[<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new\">New Status Type</a>]</p>");

	if (is_not_empty_array($errors ?? ""))
		echo format_error_block($errors);

	$results = fetch_status_type_rs();
	if ($results) {
		echo ("\n<form name=\"s_status_type\" action=\"$PHP_SELF\" method=\"POST\">");
		echo ("\n<input type=\"hidden\" name=\"op\" value=\"\">");
		echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");

		echo ("\n<table>");
		echo ("<tr class=\"navbar\">" . "<th>Type</th>" . "<th>Description</th>" . "<th>Image</th>" . "<th>Default</th>" . "<th>Closed</th>" . "<th></th>" . "</tr>");

		$row = 0;
		while ($status_type_r = db_fetch_assoc($results)) {
			display_s_status_type_row($status_type_r, $row++);
		}
		db_free_result($results);

		echo ("</form>");
		echo ("</table>");
	} else {
		echo ("<p class=\"error\">No Status Types Installed</p>");
	}

	function is_not_valid_s_status_type($type) {
		return !is_valid_s_status_type($type, FALSE);
	}
	generate_sql_list($ADMIN_TYPE, 'Status Type', "/([a-zA-Z]{1})-([^$]+)$/", 'is_not_valid_s_status_type');
}
?>
