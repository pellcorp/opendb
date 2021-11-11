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

include_once("./lib/install.php");

function generate_language_sql($language, $options = NULL) {
	$CRLF = get_user_browser_crlf();

	$sqlscript = '';

	//language, description, default_ind
	$language_r = fetch_language_r($language);
	if (is_not_empty_array($language_r)) {
		$sqlscript = '#########################################################' . $CRLF . '# OpenDb ' . get_opendb_version() . ' \'' . $language . '\' Language Pack' . $CRLF . '#########################################################' . $CRLF . $CRLF;

		$sqlscript .= "INSERT INTO s_language (language, description, default_ind) " . "VALUES ('" . $language_r['language'] . "', '" . addslashes($language_r['description']) . "', '" . $language_r['default_ind'] . "'); " . $CRLF;

		$results = fetch_language_langvar_rs($language, $options);
		if ($results) {
			$sqlscript .= $CRLF . '#' . $CRLF . '# System Language Variables' . $CRLF . '#' . $CRLF;

			while ($lang_var_r = db_fetch_assoc($results)) {
				if ($language_r['default_ind'] != 'Y')
					$value = ifempty($lang_var_r['value'], $lang_var_r['default_value']);
				else
					$value = $lang_var_r['value'];

				$sqlscript .= "INSERT INTO s_language_var (language, varname, value) " . "VALUES ('" . $language_r['language'] . "', '" . $lang_var_r['varname'] . "', '" . addslashes($value) . "'); " . $CRLF;
			}
			db_free_result($results);
		}

		if ($language_r['default_ind'] != 'Y') {
			$table_r = get_system_table_r();
			if (is_array($table_r)) {
				$sqlscript .= $CRLF . '#' . $CRLF . '# System Table Language Variables' . $CRLF . '#' . $CRLF;

				reset($table_r);
				foreach ($table_r as $table) {
					$tableconf_r = get_system_table_config($table); // key, column
					if (is_array($tableconf_r) && is_array($tableconf_r['columns'])) {
						reset($tableconf_r['columns']);
						foreach ($tableconf_r['columns'] as $column) {
							$results = fetch_system_table_column_langvar_rs($language, $table, $column, $options);
							if ($results) {
								while ($lang_var_r = db_fetch_assoc($results)) {
									if ($language_r['default_ind'] != 'Y')
										$value = ifempty($lang_var_r['value'], $lang_var_r[$column]);
									else
										$value = $lang_var_r['value'];

									if (strlen($value)) {
										$sqlscript .= "INSERT INTO s_table_language_var (language, tablename, columnname, key1, key2, key3, value) " . "VALUES ('" . $language_r['language'] . "', '" . $table . "', '" . $column . "', '" . $lang_var_r['key1'] . "', "
												. (strlen($lang_var_r['key2']) > 0 ? "'" . $lang_var_r['key2'] . "'" : "''") . ", " . (strlen($lang_var_r['key3']) > 0 ? "'" . $lang_var_r['key3'] . "'" : "''") . ", '" . addslashes($value) . "'); " . $CRLF;
									}
								}
								db_free_result($results);
							}
						}
					}
				}
			}
		}
	}

	return $sqlscript;
}

function build_langvar_page($language) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</p>");

	echo ("<h3>Edit $language Language Variables</h3>");

	echo ("<div class=\"tabContainer\"><form name=\"config\" action=\"$PHP_SELF\" method=\"POST\">" . "<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">" . "<input type=\"hidden\" name=\"op\" value=\"update-langvars\">" . "<input type=\"hidden\" name=\"language\" value=\""
			. $language . "\">");

	$initLetter = NULL;
	$currentInitLetter = NULL;

	$results = fetch_language_langvar_rs($language, !is_default_language($language) ? OPENDB_LANG_INCLUDE_DEFAULT : NULL);
	if ($results) {
		$alpha_lang_var_rs = NULL;

		while ($lang_var_r = db_fetch_assoc($results)) {
			$initLetter = strtoupper(substr($lang_var_r['varname'], 0, 1));

			if ($currentInitLetter == NULL || $currentInitLetter != $initLetter) {
				$currentInitLetter = $initLetter;
			}

			$alpha_lang_var_rs[$currentInitLetter][] = $lang_var_r;
		}
		db_free_result($results);
	}

	echo ("<ul class=\"tabMenu\" id=\"tab-menu\">");

	$isFirst = true;
	foreach ($alpha_lang_var_rs as $letter => $lang_var_rs) {
		echo ("<li id=\"menu-pane$letter\"" . ($isFirst ? " class=\"first activetab\" " : "") . " onclick=\"return activateTab('pane$letter')\">&nbsp;$letter&nbsp;</li>");
		$isFirst = false;
	}
	echo ("</ul>");

	reset($alpha_lang_var_rs);

	echo ('<div id="tab-content">');
	echo ("<ul class=\"saveButtons\"><li><input type=\"submit\" class=\"submit\" value=\"Update\"></li></ul>");

	$isFirst = true;
	foreach ($alpha_lang_var_rs as $letter => $lang_var_rs) {
		echo ("<div id=\"pane$letter\" class=\"" . ($isFirst ? "tabContent" : "tabContentHidden") . "\">\n");

		echo ('<table><tr class="navbar">');
		echo ('<th>Varname</th>');

		if (!is_default_language($language)) {
			echo ('<th>Default</th>');
		}

		echo ('<th>Value</th></tr>');
		foreach ($lang_var_rs as $letter => $lang_var_r) {
			echo ('<tr>');
			echo ('<td class="prompt">' . $lang_var_r['varname'] . '</td>');
			if (!is_default_language($language)) {
				echo ('<td class="data">' . htmlspecialchars($lang_var_r['default_value']) . '</td>');
			}
			echo ('<td class="data"><input type="text" class="text" size="60" name="lang_var[' . $lang_var_r['varname'] . ']" value="' . htmlspecialchars($lang_var_r['value']) . '"></td>');
			echo ('</tr>');
		}
		echo ('</table>');

		echo ("\n</div>");

		$isFirst = false;
	}

	echo ('</form></div>');
}

function build_table_page($language) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	$block = "<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</p>";

	$block .= "<h3>Edit System Table $language Language Variables</h3>";

	$block .= "<div class=\"tabContainer\"><form name=\"config\" action=\"$PHP_SELF\" method=\"POST\">" . "<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">" . "<input type=\"hidden\" name=\"op\" value=\"update-tables\">" . "<input type=\"hidden\" name=\"language\" value=\""
			. $language . "\">";

	$tabBlock = "";
	$paneBlock = "";
	$count = 1;

	$row = 0;

	$table_r = get_system_table_r();
	reset($table_r);
	foreach ($table_r as $table) {
		$tableconf_r = get_system_table_config($table);
		if (is_array($tableconf_r) && is_array($tableconf_r['columns'])) {
			reset($tableconf_r['columns']);
			foreach ($tableconf_r['columns'] as $column) {
				$results = fetch_system_table_column_langvar_rs($language, $table, $column, !is_default_language($language) ? OPENDB_LANG_INCLUDE_DEFAULT : NULL);
				if ($results) {
					$current_key1_value = NULL;
					$table_lang_rs = NULL;
					while ($table_lang_r = db_fetch_assoc($results)) {
						if (count($tableconf_r['key']) > 1) {
							if ($current_key1_value == NULL)
								$current_key1_value = $table_lang_r['key1'];
							else if ($current_key1_value != $table_lang_r['key1']) {
								$id = "$table&nbsp;/&nbsp;$column&nbsp;/&nbsp;" . $current_key1_value;

								$tabBlock .= "\n<li id=\"menu-pane$count\"" . ($count == 1 ? " class=\"first activetab\" " : "") . " onclick=\"return activateTab('pane$count')\">$id</li>";

								$paneBlock .= "<div id=\"pane$count\" class=\"" . ($count == 1 ? "tabContent" : "tabContentHidden") . "\">\n" . "<input type=\"submit\" class=\"submit\" value=\"Update\">" . build_div_table($language, $table, $column, $table_lang_rs, $row) . "</div>";

								$table_lang_rs = NULL;
								$current_key1_value = $table_lang_r['key1'];

								$count++;
							}
						}

						$table_lang_rs[] = $table_lang_r;
					}
					db_free_result($results);
				}

				if (count($tableconf_r['key']) == 1) {
					if (count($tableconf_r['columns']) > 1)
						$id = "$table&nbsp;/&nbsp;$column";
					else
						$id = "$table";

					$tabBlock .= "\n<li id=\"menu-pane$count\"" . ($count == 1 ? " class=\"first activetab\" " : "") . " onclick=\"return activateTab('pane$count')\">$id</li>";

					$paneBlock .= "<div id=\"pane$count\" class=\"" . ($count == 1 ? "tabContent" : "tabContentHidden") . "\">\n" . "<input type=\"submit\" class=\"submit\" value=\"Update\">" . build_div_table($language, $table, $column, $table_lang_rs, $row) . "\n</div>";

					$table_lang_rs = NULL;
					$count++;
				}
			}
		}
	}

	$block .= "<ul class=\"tabMenu\" id=\"tab-menu\">" . $tabBlock . "</ul>";
	$block .= '<div id="tab-content">' . $paneBlock . '</div>';
	$block .= '</form>';
	return $block;
}

/**
    Passed an array of all required rows to display in table
 */
function build_div_table($language, $table, $column, $table_lang_rs, &$row) {
	$block = '<table><tr class="navbar">';

	$tableconf_r = get_system_table_config($table);

	for ($i = 0; $i < count($tableconf_r['key']); $i++) {
		$block .= '<th>' . $tableconf_r['key'][$i] . '</th>';
	}

	$block .= '<th>Default ' . $column . '</th><th>Language ' . $column . '</th></tr>';

	foreach ($table_lang_rs as $table_lang_r) {
		$block .= '<tr>';

		for ($i = 1; $i <= count($tableconf_r['key']); $i++) {
			$block .= '<td class="prompt"><input type="hidden" name="lang_var[' . $table . '][' . $column . '][' . $row . '][key' . $i . ']" value="' . $table_lang_r['key' . $i] . '">' . $table_lang_r['key' . $i] . '</td>';
		}

		$block .= '<td class="data">' . htmlspecialchars($table_lang_r[$column]) . '</td>';
		$block .= '<td class="data"><input type="text" class="text" name="lang_var[' . $table . '][' . $column . '][' . $row . '][langvar]" value="' . htmlspecialchars($table_lang_r['value']) . '"></td>';

		$block .= '</tr>';

		$row++;
	}
	$block .= '</table>';

	return $block;
}

function update_system_table_lang_vars($HTTP_VARS) {
	if (is_array($HTTP_VARS['lang_var'])) {
		reset($HTTP_VARS['lang_var']);
		foreach ($HTTP_VARS['lang_var'] as $table => $_v) {
			if (is_array($HTTP_VARS['lang_var'][$table])) {
				reset($HTTP_VARS['lang_var'][$table]);
				foreach ($HTTP_VARS['lang_var'][$table] as $column => $_v) {
					if (is_array($HTTP_VARS['lang_var'][$table][$column])) {
						reset($HTTP_VARS['lang_var'][$table][$column]);
						foreach ($HTTP_VARS['lang_var'][$table][$column] as $lang_conf_r) {
							if (strlen($lang_conf_r['langvar']) > 0) {
								if (is_exists_system_table_language_var($HTTP_VARS['language'], $table, $column, $lang_conf_r['key1'], $lang_conf_r['key2'], $lang_conf_r['key3'])) {
									update_s_table_language_var($HTTP_VARS['language'], $table, $column, $lang_conf_r['key1'], $lang_conf_r['key2'], $lang_conf_r['key3'], $lang_conf_r['langvar']);
								} else {
									insert_s_table_language_var($HTTP_VARS['language'], $table, $column, $lang_conf_r['key1'], $lang_conf_r['key2'], $lang_conf_r['key3'], $lang_conf_r['langvar']);
								}
							} else if (is_exists_system_table_language_var($HTTP_VARS['language'], $table, $column, $lang_conf_r['key1'], $lang_conf_r['key2'], $lang_conf_r['key3'])) {
								delete_s_table_language_var($HTTP_VARS['language'], $table, $column, $lang_conf_r['key1'], $lang_conf_r['key2'], $lang_conf_r['key3']);
							}
						}
					}
				}
			}
		}
	}
}

function update_lang_vars($HTTP_VARS) {
	if (is_array($HTTP_VARS['lang_var'])) {
		reset($HTTP_VARS['lang_var']);
		foreach ($HTTP_VARS['lang_var'] as $varname => $value) {
			if (strlen($value) > 0) {
				if (is_exists_language_var($HTTP_VARS['language'], $varname)) {
					update_s_language_var($HTTP_VARS['language'], $varname, $value);
				} else {
					insert_s_language_var($HTTP_VARS['language'], $varname, $value);

				}
			} else if (is_exists_language_var($HTTP_VARS['language'], $varname)) {
				delete_s_language_var($HTTP_VARS['language'], $varname);
			}
		}
	}
}

@set_time_limit(600);

if ($HTTP_VARS['op'] == 'sql' && is_exists_language($HTTP_VARS['language'])) {
	header("Cache-control: no-store");
	header("Pragma: no-store");
	header("Expires: 0");
	header("Content-disposition: attachment; filename=" . strtolower($HTTP_VARS['language']) . ".sql");
	header("Content-type: text/plain");

	$sqlfile = generate_language_sql($HTTP_VARS['language'], $HTTP_VARS['include_default'] == 'Y' ? OPENDB_LANG_INCLUDE_DEFAULT : NULL);
	header("Content-Length: " . strlen($sqlfile));
	echo ($sqlfile);
} else if ($HTTP_VARS['op'] == 'installsql') {
	execute_sql_install($ADMIN_TYPE, $HTTP_VARS['sqlfile'], $errors);
	$HTTP_VARS['op'] = NULL;
} else if ($HTTP_VARS['op'] == 'update-langvars') {
	if (is_exists_language($HTTP_VARS['language'])) {
		update_lang_vars($HTTP_VARS);

		$HTTP_VARS['op'] = 'edit-langvars';
	} else {
		echo ("<div class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</div>");
	}
} else if ($HTTP_VARS['op'] == 'update-tables') {
	if (is_exists_language($HTTP_VARS['language']) && !is_default_language($HTTP_VARS['language'])) {
		update_system_table_lang_vars($HTTP_VARS);

		$HTTP_VARS['op'] = 'edit-tables';
	} else {
		echo ("<div class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</div>");
	}
} else if ($HTTP_VARS['op'] == 'delete') {
	if (is_exists_language($HTTP_VARS['language']) && !is_default_language($HTTP_VARS['language'])) {
		if ($HTTP_VARS['confirmed'] == 'false') {
			$HTTP_VARS['op'] = '';
		} else if ($HTTP_VARS['confirmed'] != 'true') {
			echo ("\n<h3>Delete Language</h3>");

			echo (get_op_confirm_form($PHP_SELF, "Are you sure you want to delete language '" . $HTTP_VARS['language'] . "'?", array('type' => $ADMIN_TYPE, 'op' => 'delete', 'language' => $HTTP_VARS['language'])));
		} else { // $HTTP_VARS['confirmed'] == 'true'
			delete_s_language_var($HTTP_VARS['language']);
			delete_s_table_language_var($HTTP_VARS['language']);
			delete_s_language($HTTP_VARS['language']);

			$HTTP_VARS['op'] = '';
		}
	} else {
		echo ("<div class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</div>");
	}
}

if ($HTTP_VARS['op'] == 'edit-tables') {
	if (is_exists_language($HTTP_VARS['language']) && !is_default_language($HTTP_VARS['language'])) {
		echo build_table_page($HTTP_VARS['language']);
	} else {
		echo ("<div class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</div>");
	}
} else if ($HTTP_VARS['op'] == 'edit-langvars') {
	if (is_exists_language($HTTP_VARS['language'])) {
		build_langvar_page($HTTP_VARS['language']);
	} else {
		echo ("<div class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</div>");
	}

} else if ($HTTP_VARS['op'] == '') {
	if (is_not_empty_array($errors ?? NULL))
		echo format_error_block($errors);

	// list languages and options
	$results = fetch_language_rs();
	if ($results) {
		echo ("<table><tr class=\"navbar\">
			<th>Language</th>
			<th>Description</th>
			<th colspan=2></th>
			</tr>");

		while ($language_r = db_fetch_assoc($results)) {
			echo ("<tr>
				<td class=\"data\">" . $language_r['language'] . "</td>
				<td class=\"data\">" . $language_r['description'] . "</td>
				<td class=\"data\"><a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=edit-langvars&language=${language_r['language']}\">Language Vars</a>");

			// there should be no concept of system table lang vars for the default language, as it should
			// always fall back to the system tables themselves.
			if (!is_default_language($language_r['language'])) {
				echo (" / <a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=edit-tables&language=${language_r['language']}\">System Table Vars</a>" . " / <a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=delete&language=${language_r['language']}\">Delete</a>");
			}

			echo ("</td>
				<td class=\"data\"><a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=sql&language=${language_r['language']}&mode=job\">SQL</a></td>
				</tr>");
		}
		echo ("</table>");

		db_free_result($results);
	}

	function is_not_exists_language($language) {
		return !is_exists_language($language);
	}
	generate_sql_list($ADMIN_TYPE, 'Language', NULL, 'is_not_exists_language');
}
?>
