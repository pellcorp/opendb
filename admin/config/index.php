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

include_once("./lib/config.php");
include_once("./lib/export.php");
include_once("./lib/user.php");
include_once("./lib/theme.php");
include_once("./lib/language.php");

/**
    boolean - TRUE or FALSE only
    text - arbritrary text
    textarea - arbritrary text
    email - email address
    number - enforce a numeric value
    datemask - enforce a date mask.
    usertype - Restrict to a single user type only.
    usertype_array - Restrict to set of user types only.
    value_select(option1,option2)
    array - keys will be numeric and in sequence only.

 */
function get_group_block_input_field($config_group_item_r, $value) {
	// replace period with '$', so we can avoid PHP auto-replacing '.' with '_' and the
	// confusion that will bring.
	$config_group_item_r['group_id'] = str_replace('.', '$', $config_group_item_r['group_id']);

	$fieldname = $config_group_item_r['group_id'] . '[' . $config_group_item_r['id'] . ']';
	if ($config_group_item_r['keyid'] != '0')
		$fieldname .= '[' . $config_group_item_r['keyid'] . ']';

	switch ($config_group_item_r['type']) {
	case 'boolean':
		return checkbox_field($fieldname, $config_group_item_r['prompt'], ($value !== NULL && $value === TRUE), 'TRUE');
		break;

	case 'readonly':
		return readonly_field($fieldname, htmlspecialchars($value));
		break;

	case 'text':
		return text_field($fieldname, $config_group_item_r['prompt'], 50, 255, 'N', htmlspecialchars($value));
		break;

	case 'password':
		return password_field($fieldname, $config_group_item_r['prompt'], 50, 255, 'N', htmlspecialchars($value));
		break;

	case 'textarea':
		return textarea_field($fieldname, $config_group_item_r['prompt'], 50, 5, 255, 'N', htmlspecialchars($value));
		break;

	case 'email':
		return email_field($fieldname, $config_group_item_r['prompt'], 50, 255, 'N', htmlspecialchars($value));
		break;

	case 'number':
		return number_field($fieldname, $config_group_item_r['prompt'], 10, 50, 'N', htmlspecialchars($value));
		break;

	case 'datemask':
		return text_field($fieldname, $config_group_item_r['prompt'], 50, 255, 'N', htmlspecialchars($value));
		break;

	case 'instance_attribute_type':
		return custom_select($fieldname, fetch_instance_attribute_type_rs(), '%s_attribute_type% - %description%', 1, $value, 's_attribute_type');
		break;

	case 'language':
		return custom_select($fieldname, fetch_language_rs(), '%language%', 1, $value, 'language', NULL, 'default_ind');
		break;

	case 'theme':
		return custom_select($fieldname, get_user_theme_r(), '%value%', 1, $value);
		break;

	case 'export':
		return custom_select($fieldname, array_merge(array(''), get_export_r()), '%value%', 1, $value);
		break;

	case 'value_select':
		$value_options_r = explode(',', $config_group_item_r['subtype']);
		return value_select($fieldname, $value_options_r, 1, $value);
		break;

	case 'array':
		$buffer = '';

		switch ($config_group_item_r['subtype']) {
		case 'text':
		case 'number':
			$element_name = $config_group_item_r['group_id'] . "[" . $config_group_item_r['id'] . "][]";

			$buffer .= "<select name=\"" . $element_name . "\" size=\"5\" MULTIPLE>\n";

			if (is_array($value)) {
				reset($value);
				foreach ($value as $key => $val) {
					$buffer .= "<option value=\"" . $val . "\" SELECTED>" . $val . "\n";
				}
			}
			$buffer .= "</select>";

			$buffer .= "<ul class=\"actionButtons\">";
			$buffer .= "<li><input type=\"button\" class=\"button\" value=\"Edit\" onClick=\"updateSelectedOption(this.form['" . $element_name . "'], '" . $config_group_item_r['prompt'] . "', '" . $config_group_item_r['subtype'] . "');\"></li>";
			$buffer .= "<li><input type=\"button\" class=\"button\" value=\"Add\" onClick=\"addSelectOption(this.form['" . $element_name . "'], '" . $config_group_item_r['prompt'] . "', '" . $config_group_item_r['subtype'] . "');\"></li>";
			$buffer .= "<li><input type=\"button\" class=\"button\" value=\"Delete\" onClick=\"removeSelectedOption(this.form['" . $element_name . "']);\"></li>";
			$buffer .= "</ul>";

			break;
		}

		return $buffer;
	}

	//else
	return '>>> ERROR <<<';
}

/**
Will return Group Block, including any subblocks
 */
function get_group_block($config_group_r) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	$buffer = "<form name=\"config\" action=\"$PHP_SELF\" method=\"POST\">" . "<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">" . "<input type=\"hidden\" name=\"op\" value=\"save\">" . "<input type=\"hidden\" name=\"group_id\" value=\"" . $config_group_r['id'] . "\">";

	$buffer .= "<ul class=\"saveButtons\">
				<li><input type=\"submit\" class=\"submit\" value=\"Refresh\" onclick=\"this.form['op'].value='';\"></li>
				<li><input type=\"submit\" class=\"submit\" value=\"Save\"></li>
			</ul>\n";

	$buffer .= "<h3>" . $config_group_r['name'] . "</h3>\n";

	if (strlen($config_group_r['description']) > 0) {
		$buffer .= $config_group_r['description'];
	}

	$buffer .= "<table>";
	$results = fetch_s_config_group_item_rs($config_group_r['id']);
	if ($results) {
		while ($config_group_item_r = db_fetch_assoc($results)) {
			if (strpos($config_group_item_r['type'], 'array') !== FALSE)
				$values_r = get_opendb_db_config_var($config_group_item_r['group_id'], $config_group_item_r['id']);
			else
				$values_r = get_opendb_db_config_var($config_group_item_r['group_id'], $config_group_item_r['id'], $config_group_item_r['keyid']);

			$buffer .= "<tr>" . "\n<td class=\"prompt\">" . $config_group_item_r['prompt'] . " <a href=\"#\" onmouseover=\"show_tooltip('" . addslashes(str_replace('"', '&quot;', $config_group_item_r['description'])) . "','" . addslashes($config_group_item_r['prompt'])
					. "');\" onmouseout=\"return hide_tooltip();\">(?)</a>:</td>" . "<td class=\"data\">" . get_group_block_input_field($config_group_item_r, $values_r) . "</td></tr>";
		}
		db_free_result($results);
	}

	$buffer .= "</table>";

	// now do any subgroups
	$results = fetch_s_config_subgroup_rs($config_group_r['id']);
	if ($results) {
		while ($config_subgroup_r = db_fetch_assoc($results)) {
			$buffer .= "<h3>" . $config_subgroup_r['name'] . "</h3>";
			if (strlen($config_subgroup_r['description']) > 0) {
				$buffer .= $config_subgroup_r['description'];
			}

			$buffer .= "<table>";

			$results2 = fetch_s_config_group_item_rs($config_subgroup_r['id']);
			if ($results2) {
				while ($config_group_item_r = db_fetch_assoc($results2)) {
					if (strpos($config_group_item_r['type'], 'array') !== FALSE)
						$values_r = get_opendb_db_config_var($config_group_item_r['group_id'], $config_group_item_r['id']);
					else
						$values_r = get_opendb_db_config_var($config_group_item_r['group_id'], $config_group_item_r['id'], $config_group_item_r['keyid']);

					$buffer .= "<tr>" . "\n<td class=\"prompt\">" . $config_group_item_r['prompt'] . "<a href=\"#\" onmouseover=\"show_tooltip('" . addslashes(str_replace('"', '&quot;', $config_group_item_r['description'])) . "','" . addslashes($config_group_item_r['prompt'])
							. "');\" onmouseout=\"return hide_tooltip();\">(?)</a>:</td>" . "<td class=\"data\">" . get_group_block_input_field($config_group_item_r, $values_r) . "</td></tr>";
				}
				db_free_result($results2);
			}

			$buffer .= "</table>";
		}
		db_free_result($results);
	}

	$buffer .= "</form>";

	return $buffer;
}

function save_config($HTTP_VARS, &$errors) {
	// had to add USER and s_language tables because these tables are accessed in the validations
	if (db_query("LOCK TABLES user READ, s_language READ, s_config_group WRITE, s_config_group_item WRITE, s_config_group_item_var WRITE")) {
		$results = fetch_s_config_group_rs($HTTP_VARS['group_id']);
		if ($results) {
			while ($config_group_r = db_fetch_assoc($results)) {
				$results2 = fetch_s_config_group_item_rs($config_group_r['id']);
				if ($results2) {
					while ($config_group_item_r = db_fetch_assoc($results2)) {
						save_config_item($config_group_item_r, $HTTP_VARS, $errors);
					}
					db_free_result($results2);
				}

				//now progress subgroup
				$results2 = fetch_s_config_subgroup_rs($config_group_r['id']);
				if ($results2) {
					while ($config_subgroup_r = db_fetch_assoc($results2)) {
						// we need to match to the HTTP group name, which has the '$' instead of '.'
						$http_group_id = str_replace('.', '$', $config_subgroup_r['id']);
						if (is_array($HTTP_VARS[$http_group_id])) {
							$HTTP_VARS = array_merge($HTTP_VARS, array($config_subgroup_r['id'] => $HTTP_VARS[$http_group_id]));
						}

						$results3 = fetch_s_config_group_item_rs($config_subgroup_r['id']);
						if ($results3) {
							while ($config_group_item_r = db_fetch_assoc($results3)) {
								save_config_item($config_group_item_r, $HTTP_VARS, $errors);
							}
							db_free_result($results3);
						}
					}
					db_free_result($results2);
				}
			}
			db_free_result($results);
		}

		db_query("UNLOCK TABLES");
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error());
		return FALSE;
	}
}

function save_config_item($config_group_item_r, $HTTP_VARS, &$errors) {
	$http_value = NULL;
	if ($config_group_item_r['keyid'] != '0')
		$http_value = $HTTP_VARS[$config_group_item_r['group_id']][$config_group_item_r['id']][$config_group_item_r['keyid']] ?? "";
	else
		$http_value = $HTTP_VARS[$config_group_item_r['group_id']][$config_group_item_r['id']] ?? "";

	// if old values exist, and count of new values is the same, then no need to proceed.
	if ($config_group_item_r['type'] == 'array') {
		// drop all existing elements.
		if (is_exists_s_config_group_item_var($config_group_item_r['group_id'], $config_group_item_r['id'])) {
			delete_s_config_group_item_vars($config_group_item_r['group_id'], $config_group_item_r['id'], NULL);
		}

		if (is_not_empty_array($http_value)) {
			reset($http_value);
			foreach ($http_value as $key => $value) {
				if ($value != 'NULL') {
					if (!insert_s_config_group_item_var($config_group_item_r['group_id'], $config_group_item_r['id'], $key, $value)) {
						$errors[] = array('error' => 'Config Group Item Var not inserted', 'detail' => db_error());
					}
				}
			}
		}
	} else { //	if($config_group_item_r['type'] == 'array')
		// make sure booleans always have a value.
		if ($config_group_item_r['type'] == 'boolean') {
			if ($http_value === NULL || $http_value !== 'TRUE') {
				$http_value = 'FALSE';
			}
		}

		// do update
		if (strlen($http_value) > 0) {
			if (is_exists_s_config_group_item_var($config_group_item_r['group_id'], $config_group_item_r['id'], $config_group_item_r['keyid'])) {
				if (!update_s_config_group_item_var($config_group_item_r['group_id'], $config_group_item_r['id'], $config_group_item_r['keyid'], $http_value)) {
					$errors[] = array('error' => 'Config Group Item Var not updated', 'detail' => db_error());
				}
			} else { // do insert
				if (!insert_s_config_group_item_var($config_group_item_r['group_id'], $config_group_item_r['id'], $config_group_item_r['keyid'], $http_value)) {
					$errors[] = array('error' => 'Config Group Item Var not inserted', 'detail' => db_error());
				}
			}
		} else {
			if (is_exists_s_config_group_item_var($config_group_item_r['group_id'], $config_group_item_r['id'], $config_group_item_r['keyid'])) {
				if (!delete_s_config_group_item_vars($config_group_item_r['group_id'], $config_group_item_r['id'], $config_group_item_r['keyid'])) {
					$errors[] = array('error' => 'Config Group Item Var not deleted', 'detail' => db_error());
				}
			}
		}
	}
}

@set_time_limit(0);

if (strlen($HTTP_VARS['group_id'] ?? "") == 0) {
	$HTTP_VARS['group_id'] = 'site';
}

// process any updates
if ($HTTP_VARS['op'] == 'save') {
	//print_r($HTTP_VARS);
	save_config($HTTP_VARS, $errors);
}

if (is_not_empty_array($errors ?? ""))
	echo format_error_block($errors);
echo get_javascript("admin/config/select.js");

echo ("<div class=\"tabContainer\">");

$config_group_rs = NULL;
$results = fetch_s_config_group_rs();
if ($results) {
	while ($config_group_r = db_fetch_assoc($results)) {
		$config_group_rs[] = $config_group_r;
	}
	db_free_result($results);
}

if (is_array($config_group_rs)) {
	echo ("\n<ul class=\"tabMenu\" id=\"tab-menu\">");

	$first = TRUE;

	reset($config_group_rs);
	foreach ($config_group_rs as $config_group_r) {
		if ($config_group_r['id'] == $HTTP_VARS['group_id'])
			echo "\n<li class=\"activetab " . ($first ? " first" : "") . "\">" . $config_group_r['name'] . "</li>";
		else
			echo "\n<li class=\"" . ($first ? "first" : "") . "\"><a href=\"$PHP_SELF?type=$ADMIN_TYPE&group_id=" . $config_group_r['id'] . "\">" . $config_group_r['name'] . "</a></li>";
		$first = FALSE;
	}

	echo ("\n</ul>");

	echo ("<div id=\"tab-content\">");

	reset($config_group_rs);
	foreach ($config_group_rs as $config_group_r) {
		if ($config_group_r['id'] == $HTTP_VARS['group_id']) {
			echo get_group_block($config_group_r);
			break;
		}
	}
	echo ("</div>");
}

echo ("</div>");
?>
