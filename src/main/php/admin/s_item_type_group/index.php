<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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

include_once("lib/item_type.php");
include_once("lib/item_type_group.php");

function display_s_item_type_group_row($item_type_group_r, $row) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	echo ("\n<tr>");

	if (is_not_empty_array($item_type_group_r)) {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">");
		echo ("<td class=\"data\">");
		echo (get_input_field("s_item_type_group[$row]", NULL, "Item Type Group", "readonly", "Y", $item_type_group_r['s_item_type_group'], FALSE));
		if (fetch_item_type_item_type_group_cnt($item_type_group_r['s_item_type_group']) === 0)
			echo ("*");
		echo ("</td>");
	} else {
		echo ("<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"N\">");
		echo ("\n<td class=\"data\">" . get_input_field("s_item_type_group[$row]", NULL, "Item Type Group", "text(10,10)", "Y", NULL, FALSE) . "</td>");
	}

	echo ("<td class=\"data\">" . get_input_field("description[$row]", NULL, "Description", "text(30,255)", 'Y', $item_type_group_r['description'], FALSE) . "</td>");

	echo ("\n<td class=\"data\">");
	if (is_not_empty_array($item_type_group_r)) {
		echo ("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit_item_type_group_rltshps&s_item_type_group=" . $item_type_group_r['s_item_type_group'] . "\">Edit</a>" . " / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete_item_type_group&s_item_type_group="
				. $item_type_group_r['s_item_type_group'] . "\">Delete</a>");
	}
	echo ("\n</td>");
	echo ("</tr>");
}

if (strlen($HTTP_VARS['op']) == 0)
	$HTTP_VARS['op'] = 'edit_item_type_groups';

if ($HTTP_VARS['op'] == 'delete_item_type_group') {
	$item_type_group_r = fetch_item_type_group_r($HTTP_VARS['s_item_type_group']);
	if (is_not_empty_array($item_type_group_r)) {
		if ($HTTP_VARS['confirmed'] == 'false') {
			// return to edit form
			$HTTP_VARS['op'] = 'edit_item_type_groups';
		} else {
			if ($HTTP_VARS['confirmed'] != 'true') {
				echo "<h3>Delete Item Type Group</h3>";

				echo get_op_confirm_form($PHP_SELF, 'Are you sure you want to delete Item Type Group ' . $HTTP_VARS['s_item_type_group'] . '?', $HTTP_VARS);
			} else// $HTTP_VARS['confirmed'] == 'true'
 {
				if (delete_s_item_type_group_rltshp($HTTP_VARS['s_item_type_group'])) {
					if (!delete_s_item_type_group($HTTP_VARS['s_item_type_group']))
						$errors[] = array('error' => 'Item Type Group not deleted', 'detail' => db_error());
				} else {
					$errors[] = array('error' => 'Item Type Group not deleted', 'detail' => db_error());
				}

				$HTTP_VARS['op'] = 'edit_item_type_groups';
			}
		}
	} else {
		$errors[] = array('error' => 'Item Type Group not found');
		$HTTP_VARS['op'] = 'edit_item_type_groups';
	}
} else if ($HTTP_VARS['op'] == 'update_item_type_groups') {
	if (is_not_empty_array($HTTP_VARS['exists_ind'])) {
		for ($i = 0; $i < count($HTTP_VARS['exists_ind']); $i++) {
			if (strlen($HTTP_VARS['s_item_type_group'][$i]) > 0 && strlen($HTTP_VARS['description'][$i]) > 0) {
				if ($HTTP_VARS['exists_ind'][$i] == 'N') {
					$HTTP_VARS['s_item_type_group'][$i] = strtoupper(preg_replace("/[\s|'|\\\\|\"]+/", "", trim(strip_tags($HTTP_VARS['s_item_type_group'][$i]))));
					if (!insert_s_item_type_group($HTTP_VARS['s_item_type_group'][$i], $HTTP_VARS['description'][$i]))
						$errors[] = array('error' => 'Item Type Group not inserted', 'detail' => db_error());
				} else {
					if (!update_s_item_type_group($HTTP_VARS['s_item_type_group'][$i], $HTTP_VARS['description'][$i]))
						$errors[] = array('error' => 'Item Type Group not updated', 'detail' => db_error());
				}
			}
		}
	}

	$HTTP_VARS['op'] = 'edit_item_type_groups';
} else if ($HTTP_VARS['op'] == 'update_item_type_group_rltshps') {
	$results = fetch_s_item_type_join_sitgr_rs($HTTP_VARS['s_item_type_group']);
	if ($results) {
		while ($item_type_r = db_fetch_assoc($results)) {
			$key = array_search2($item_type_r['s_item_type'], $HTTP_VARS['s_item_type']);

			if ($item_type_r['exists_ind'] == 'Y') {
				if ($key === FALSE) // only if no longer checked, should we delete
 {
					if (!delete_s_item_type_group_rltshp($HTTP_VARS['s_item_type_group'], $item_type_r['s_item_type']))
						$errors[] = array('error' => 'Item Type Group Relationship not deleted', 'detail' => db_error());
				}
			} else {
				if ($key !== FALSE) {
					if (!insert_s_item_type_group_rltshp($HTTP_VARS['s_item_type_group'], $HTTP_VARS['s_item_type'][$key]))
						$errors[] = array('error' => 'Item Type Group Relationship not inserted', 'detail' => db_error());
				}
			}
		}
	}
	$HTTP_VARS['op'] = 'edit_item_type_group_rltshps';
}

if ($HTTP_VARS['op'] == 'edit_item_type_groups') {
	if (is_not_empty_array($errors))
		echo format_error_block($errors);

	echo ("\n<form name=\"s_item_type_group\" action=\"$PHP_SELF\" method=\"POST\">");
	echo ("\n<input type=\"hidden\" name=\"op\" value=\"" . $HTTP_VARS['op'] . "\">");
	echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");

	echo ("<table>");
	echo ("\n<tr class=\"navbar\">" . "\n<th>Group</th>" . "\n<th>Description</th>" . "\n<th></th>" . "\n</tr>");
	$column_count = 4;

	$results = fetch_s_item_type_group_rs();
	if ($results) {
		$row = 0;
		while ($item_type_group_r = db_fetch_assoc($results)) {
			display_s_item_type_group_row($item_type_group_r, $row);

			$row++;
		}
		db_free_result($results);
	}

	if (is_numeric($HTTP_VARS['blank_rows']))
		$blank_rows = (int) $HTTP_VARS['blank_rows'];
	else
		$blank_rows = 5;

	for ($i = $row; $i < $row + $blank_rows; $i++) {
		display_s_item_type_group_row(array(), $i);
	}
	echo ("</table>");

	echo (get_input_field("blank_rows", NULL, NULL, "value_select(\"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20\",1)", "N", ifempty($HTTP_VARS['blank_rows'], "5"), FALSE, NULL, "this.form.submit();"));

	echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='" . $HTTP_VARS['op'] . "'; this.form.submit();\">");
	echo ("<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update_item_type_groups'; this.form.submit();\">");

	echo ("</form>");
} else if ($HTTP_VARS['op'] == 'edit_item_type_group_rltshps') {
	$item_type_group_r = fetch_item_type_group_r($HTTP_VARS['s_item_type_group']);
	if (is_not_empty_array($item_type_group_r)) {
		echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=edit_item_type_groups\">Back to Main</a>]</p>");

		echo ("\n<h3>Edit " . $HTTP_VARS['s_item_type_group'] . " Item Type Group Relationships</h3>");

		if (is_not_empty_array($errors))
			echo format_error_block($errors);

		$results = fetch_s_item_type_join_sitgr_rs($HTTP_VARS['s_item_type_group']);
		if ($results) {
			echo ("\n<form name=\"editform\" action=\"$PHP_SELF\" method=\"POST\">");
			echo ("\n<input type=\"hidden\" name=\"op\" value=\"update_item_type_group_rltshps\">");
			echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");
			echo ("\n<input type=\"hidden\" name=\"s_item_type_group\" value=\"" . $HTTP_VARS['s_item_type_group'] . "\">");

			echo ("<ul class=\"itemTypes\">");
			while ($item_type_r = db_fetch_assoc($results)) {
				echo ("<li>" . "<input type=\"checkbox\" class=\"checkbox\" name=\"s_item_type[]\" value=\"" . $item_type_r['s_item_type'] . "\"" . ($item_type_r['exists_ind'] == 'Y' ? 'CHECKED' : '') . ">" . $item_type_r['s_item_type'] . "</li>");
			}
			db_free_result($results);
			echo ("</ul>");

			echo ("<input type=\"submit\" class=\"submit\" value=\"Update\">");
			echo ("</form>");
		}
	}
}
?>