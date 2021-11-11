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

include_once("./lib/site_plugin.php");
include_once("./lib/parseutils.php");
include_once("./lib/javascript.php");

$input_type_functions_cats = array('lookup' => array('radio_grid', 'checkbox_grid', 'single_select', 'multi_select'), 'multi' => array('datetime', 'email', 'filtered', 'number', 'password', 'text', 'url'), 'normal' => array(), 'restricted' => array('review_options'));

reset($input_type_functions);
foreach($input_type_functions as $key => $_v) {
	if (!in_array($key, $input_type_functions_cats['lookup']) && !in_array($key, $input_type_functions_cats['restricted'])) {
		$input_type_functions_cats['normal'][] = $key;
	}
}

function get_attribute_ind_type($attribute_type_r, $HTTP_VARS) {
	$attribute_ind_type = 'normal';

	if (is_array($attribute_type_r)) {
		if (strtoupper($attribute_type_r['lookup_attribute_ind']) == 'Y')
			$attribute_ind_type = 'lookup';
		else if (strtoupper($attribute_type_r['multi_attribute_ind']) == 'Y')
			$attribute_ind_type = 'multi';
	} else if (strlen($HTTP_VARS['attribute_ind_type']) > 0) {
		$attribute_ind_type = $HTTP_VARS['attribute_ind_type'];
	}

	return $attribute_ind_type;
}

function get_attribute_ind_type_function_list($type) {
	global $input_type_functions_cats;
	global $input_type_functions;

	$new_function_list = Array();

	reset($input_type_functions);
	foreach ($input_type_functions as $key => $function) {
		if (in_array($key, $input_type_functions_cats[$type])) {
			$new_function_list[$key] = $function;
		}
	}

	return $new_function_list;
}

/**
    Will generate a function list, based on the format of the
    $input_type_funcs and $display_type_funcs where the name
    of the function is the key.
 */
function build_function_list($name, $list_array, $function_type, $onchange_event = NULL) {
	$select = "\n<select name=\"$name\" onchange=\"$onchange_event\">";

	foreach($list_array as $key => $_v) {
		if (strcasecmp($function_type, $key) === 0)
			$select .= "\n<option value=\"$key\" SELECTED>$key";
		else
			$select .= "\n<option value=\"$key\">$key";
	}

	$select .= "\n</select>";

	return $select;
}

/**
    Produce full function spec for display in
    Function Help.
 */
function get_function_spec($type, $func_args) {
	$args = "";

	@reset($func_args);
	foreach ($func_args as $value) {
		if (substr($value, -3) === '[Y]') {
			$value = substr($value, 0, -3);
			if (strlen($args) == 0)
				$args .= $value;
			else
				$args .= ", $value";
		} else {
			if (strlen($args) == 0)
				$args .= "[$value]";
			else
				$args .= "[, $value]";
		}
	}

	if (strlen($args) > 0)
		return $type . "(" . $args . ")";
	else
		return $type;
}

function get_widget_tooltip_array() {
	global $input_type_functions;
	global $display_type_functions;

	$arrayOfAttributes = "arrayOfWidgetTooltips = new Array(" . (count($input_type_functions) + count($display_type_functions)) . ");\n";
	$count = 0;

	//name, type, description, spec, args
	reset($input_type_functions);
	foreach ($input_type_functions as $name => $definition) {
		$arrayOfAttributes .= "arrayOfWidgetTooltips[$count] = " . get_widget_js_entry($name, 'input', $definition);
		$count++;
	}

	reset($display_type_functions);
	foreach ($display_type_functions as $name => $definition) {
		$arrayOfAttributes .= "arrayOfWidgetTooltips[$count] = " . get_widget_js_entry($name, 'display', $definition);
		$count++;
	}

	return "<script language=\"JavaScript\">" . $arrayOfAttributes . "</script>";
}

function get_widget_js_entry($name, $type, $definition) {
	global $argument_types;

	$description = $definition['description'];
	$spec = get_function_spec($name, $definition['args']);

	$args = array();
	foreach ($definition['args'] as $value) {
		if (substr($value, -3) === "[Y]") {
			$value = substr($value, 0, -3);
		}

		$arg = $value . " - ";

		if (is_array($argument_types[$value])) {
			$arg .= $argument_types[$value]['description'];
		}

		$args[] = $arg;
	}

	return "new WidgetToolTip('$name', '$type', '" . addslashes($description) . "', '" . addslashes($spec) . "', " . encode_javascript_array($args) . ");\n";
}

function get_function_help_link($type) {
	$fieldname = $type . "_type";

	return "<a href=\"#\" onmouseover=\"return show_widget_select_tooltip(document.forms['s_attribute_type']['$fieldname'], '$type', arrayOfWidgetTooltips);\" onmouseout=\"return hide_tooltip();\">(?)</a>";
}

function display_attribute_type_form($HTTP_VARS) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	$initLetter = NULL;
	$currentInitLetter = NULL;

	$alpha_attribute_type_rs = array();
	$results = fetch_attribute_type_rs();
	if ($results) {
		while ($attribute_type_r = db_fetch_assoc($results)) {
			$initLetter = strtoupper(substr($attribute_type_r['s_attribute_type'], 0, 1));

			if ($currentInitLetter == NULL || $currentInitLetter != $initLetter) {
				$currentInitLetter = $initLetter;
			}

			$alpha_attribute_type_rs[$currentInitLetter][] = $attribute_type_r;
		}
		db_free_result($results);
	}

	echo ("<div class=\"tabContainer\">" . "<form name=\"s_attribute_type_lookup\" action=\"$PHP_SELF\" method=\"POST\">" . "<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">" . "<input type=\"hidden\" name=\"op\" value=\"\">"
		  . "<input type=\"hidden\" name=\"s_attribute_type\" value=\"" . ($HTTP_VARS['s_attribute_type'] ?? "") . "\">");

	$isFirst = true;
	echo ("<ul class=\"tabMenu\" id=\"tab-menu\">");
	if (!isset($HTTP_VARS['active_tab']))
		$HTTP_VARS['active_tab'] = "";
	foreach ($alpha_attribute_type_rs as $letter => $attribute_type_rs) {
		$isFirst ? $class = "first" : $class = "";
		if ($letter == $HTTP_VARS['active_tab'] || ($HTTP_VARS['active_tab'] == "" && $isFirst))
			$class .= " activeTab";
		echo ("<li id=\"menu-pane$letter\" class=\"$class\" onclick=\"return activateTab('pane$letter')\">&nbsp;$letter&nbsp;</li>");
		$isFirst = false;
	}
	echo ("</ul>");

	reset($alpha_attribute_type_rs);
	$isFirst = true;
	echo ('<div id="tab-content">');
	foreach ($alpha_attribute_type_rs as $letter => $attribute_type_rs) {
		($isFirst && $HTTP_VARS['active_tab'] == "") ? $class = "tabContent" : $class = "tabContentHidden";
		if ($letter == $HTTP_VARS['active_tab'])
			$class = "tabContent";
		echo ("<div id=\"pane$letter\" class=\"$class\">\n" . "\n<table>" . "<tr class=\"navbar\">" . "<th>Type</th>" . "<th>Description</th>" . "<th>Field Type</th>" . "<th colspan=\"2\"></th>" . "</tr>");

		foreach ($attribute_type_rs as $attribute_type_r) {
			echo (get_s_attribute_type_row($attribute_type_r, NULL, $letter));
		}

		echo ("</table></div>");

		$isFirst = false;
	}
	echo ("</div>");

	echo ('</form></div>');
}

function display_lookup_attribute_type_form($HTTP_VARS) {
	global $PHP_SELF;
	global $ADMIN_TYPE;

	echo ("<div class=\"tabContainer\"><form name=\"s_attribute_type_lookup\" action=\"$PHP_SELF\" method=\"POST\">" . "<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">" . "<input type=\"hidden\" name=\"op\" value=\"update-lookups\">"
			. "<input type=\"hidden\" name=\"s_attribute_type\" value=\"" . $HTTP_VARS['s_attribute_type'] . "\">" . "<input type=\"hidden\" name=\"active_tab\" value=\"" . $HTTP_VARS['active_tab'] . "\">");

	$row = 0;
	$attribute_type_rows = NULL;
	$results = fetch_attribute_type_lookup_rs($HTTP_VARS['s_attribute_type'], 'order_no, value ASC', FALSE);
	if ($results) {
		// value, display, img, checked_ind, order_no
		while ($attribute_type_lookup_r = db_fetch_assoc($results)) {
			$attribute_type_rows[] = get_s_attribute_type_lookup_row($attribute_type_lookup_r, $row++);
		}
		db_free_result($results);
	}

	$emptyrows = 20 - (count($attribute_type_rows ?? []) % 20);
	if ($emptyrows == 0)
		$emptyrows = 20;

	for ($i = 0; $i < $emptyrows; $i++) {
		$attribute_type_rows[] = get_s_attribute_type_lookup_row(array(), $row++);
	}

	$pageno = 1;
	$count = 0;

	echo ("<ul class=\"tabMenu\" id=\"tab-menu\">");
	for ($i = 0; $i < count($attribute_type_rows); $i++) {
		if ($count == 20) {
			$count = 0;
			$pageno++;
		}

		if ($count == 0) {
			echo ("<li id=\"menu-pane$pageno\"" . ($pageno == 1 ? " class=\"first activetab\" " : "") . " onclick=\"return activateTab('pane$pageno')\">Page&nbsp;$pageno</li>");
		}
		$count++;
	}
	echo ("</ul>");

	$pageno = 1;
	$count = 0;

	echo ('<div id="tab-content"><ul class="saveButtons">
   			<li><input type="submit" class="submit" value="Update"></li>
   			</ul>');
	for ($i = 0; $i < count($attribute_type_rows); $i++) {
		if ($count == 20) {
			$count = 0;
			$pageno++;
			echo ("</table></div>");
		}

		if ($count == 0) {
			echo ("<div id=\"pane$pageno\" class=\"" . ($pageno == 1 ? "tabContent" : "tabContentHidden")
					. "\">
				<table>
				<tr class=\"navbar\">
				<th>Delete</th>
				<th>Order</th>
				<th>Value</th>
				<th>Display</th>
				<th colspan=2>Image</th>
				<th>No Image</th>
    			<th>Checked</th>
				</tr>");
		}

		echo ($attribute_type_rows[$i]);
		$count++;
	}
	echo ("</table></div></div>");
	echo ("</form></div>");
}

function get_s_attribute_type_row($attribute_type_r, $row, $letter = "A") {
	global $ADMIN_TYPE;
	global $PHP_SELF;

	$block = "\n<tr>";

	$block .= "\n<td class=\"data\">" . $attribute_type_r['s_attribute_type'] . "</td>";
	$block .= "\n<td class=\"data\">" . $attribute_type_r['description'] . "</td>";
	$block .= "\n<td class=\"data\">" . $attribute_type_r['s_field_type'] . "</td>";

	$block .= "\n<td class=\"data\">";
	$block .= " <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_attribute_type=" . $attribute_type_r['s_attribute_type'] . "&active_tab=" . $letter . "\">Edit</a>";
	$block .= " / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&s_attribute_type=" . $attribute_type_r['s_attribute_type'] . "&active_tab=" . $letter . "\">Delete</a>";

	if ($attribute_type_r['lookup_attribute_ind'] == 'Y')
		$block .= " / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit-lookups&s_attribute_type=" . $attribute_type_r['s_attribute_type'] . "&active_tab=" . $letter . "\">Edit Lookup</a>";

	$block .= "</td></tr>";

	return $block;
}

function get_s_attribute_type_lookup_row($lookup_r, $row) {
	$block = "<tr>";

	$block .= "<td class=\"data\">";
	if (is_not_empty_array($lookup_r))
		$block .= get_input_field("delete_ind[$row]", NULL, NULL, "simple_checkbox()", "N", "Y", FALSE);
	else
		$block .= "&nbsp;";
	$block .= "</td>";

	$block .= "<td class=\"data\">" . get_input_field("order_no[$row]", NULL, NULL, "number(3)", "N", $lookup_r['order_no'], FALSE) . "</td>";

	if (is_not_empty_array($lookup_r)) {
		$block .= "<td class=\"data\">" . get_input_field("value[$row]", NULL, "Value", "readonly", "Y", $lookup_r['value'], FALSE) . "<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"Y\">" . "</td>";
	} else {
		$block .= "<td class=\"data\">" . get_input_field("value[$row]", NULL, "Value", "text(10,50)", "Y", NULL, FALSE) . "<input type=\"hidden\" name=\"exists_ind[$row]\" value=\"N\">" . "</td>";
	}

	$block .= "<td class=\"data\">" . get_input_field("display[$row]", NULL, NULL, "text(20,255)", "N", $lookup_r['display'], FALSE) . "</td>";

	if ($lookup_r['img'] != 'none')
		$src = theme_image_src($lookup_r['img']);

	$block .= "<td class=\"data\">";
	if ($src !== FALSE && strlen($src) > 0)
		$block .= "<img src=\"$src\">";
	else
		$block .= "&nbsp;";
	$block .= "</td>";

	$block .= "<td class=\"data\">" . get_input_field("img[$row]", NULL, "Image", "url(15,*,\"gif,jpg,png\",N)", "N", $lookup_r['img'] != "none" ? $lookup_r['img'] : NULL, FALSE, NULL, "if(this.value.length>0){this.form['none_img[$row]'].checked=false;}") . "</td>";
	$block .= "<td class=\"data\">" . get_input_field("none_img[$row]", NULL, NULL, "simple_checkbox(" . ($lookup_r['img'] == "none" ? "CHECKED" : "") . ")", "N", "Y", FALSE, NULL, "if(this.checked){this.form['img[$row]'].value='';}") . "</td>";
	$block .= "<td class=\"data\"><input type=\"checkbox\" class=\"checkbox\" name=\"checked_ind[{$row}]\" value=\"Y\" onclick=\"toggleChecked(this, 'checked_ind')\" " . (strtoupper($lookup_r['checked_ind']) == 'Y' ? 'CHECKED' : '') . ">";

	$block .= "</tr>";

	return $block;
}

function display_edit_form($attribute_type_r, $HTTP_VARS = NULL) {
	global $display_type_functions;
	global $_FIELD_TYPES;

	// s_attribute_type
	if (is_array($attribute_type_r))
		echo get_input_field("s_attribute_type", NULL, "Attribute Type", "readonly", "Y", $attribute_type_r['s_attribute_type']);
	else
		echo get_input_field("s_attribute_type", NULL, "Attribute Type", "text(30,30)", "Y", $HTTP_VARS['s_attribute_type'], TRUE, NULL,
				'this.value=trim(this.value.toUpperCase()); if(this.value.substring(0,2) == \'S_\'){alert(\'Attributes with a \\\'S_\\\' prefix are reserved for internal use.\'); this.value=\'\'; this.focus(); return false; }');

	//description
	echo get_input_field("description", NULL, "Description", "text(30,60)", "Y", ifempty($attribute_type_r['description'], $HTTP_VARS['description']));

	//prompt
	echo get_input_field("prompt", NULL, "Prompt", "text(20,30)", "Y", ifempty($attribute_type_r['prompt'], $HTTP_VARS['prompt']));

	$is_reserved_attribute_type = is_reserved_s_attribute_type($attribute_type_r['s_attribute_type']);

	if (!$is_reserved_attribute_type) {
		edit_attribute_ind_type_js();
		$attribute_ind_type = get_attribute_ind_type($attribute_type_r, $HTTP_VARS);
		echo format_field('Attribute Type Indicator', build_attribute_ind_type_widget($attribute_ind_type, $HTTP_VARS));
	}

	if ($is_reserved_attribute_type) {
		echo format_field("Input Type", $attribute_type_r['input_type']);

		if (strlen($attribute_type_r['input_type_arg1']) > 0)
			echo format_field("Input Type Arg 1", $attribute_type_r['input_type_arg1']);
		if (strlen($attribute_type_r['input_type_arg2']) > 0)
			echo format_field("Input Type Arg 2", $attribute_type_r['input_type_arg2']);
		if (strlen($attribute_type_r['input_type_arg3']) > 0)
			echo format_field("Input Type Arg 3", $attribute_type_r['input_type_arg3']);
		if (strlen($attribute_type_r['input_type_arg4']) > 0)
			echo format_field("Input Type Arg 4", $attribute_type_r['input_type_arg4']);
		if (strlen($attribute_type_r['input_type_arg5']) > 0)
			echo format_field("Input Type Arg 5", $attribute_type_r['input_type_arg5']);
	} else {
		$input_function_list = get_attribute_ind_type_function_list($attribute_ind_type);
		echo format_field("Input Type", build_function_list("input_type", $input_function_list, ifempty($attribute_type_r['input_type'], $HTTP_VARS['input_type'])) . get_function_help_link('input'));

		echo get_input_field("input_type_arg1", NULL, "Input Type Arg 1", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg1'], $HTTP_VARS['input_type_arg1']));
		echo get_input_field("input_type_arg2", NULL, "Input Type Arg 2", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg2'], $HTTP_VARS['input_type_arg2']));
		echo get_input_field("input_type_arg3", NULL, "Input Type Arg 3", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg3'], $HTTP_VARS['input_type_arg3']));
		echo get_input_field("input_type_arg4", NULL, "Input Type Arg 4", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg4'], $HTTP_VARS['input_type_arg4']));
		echo get_input_field("input_type_arg5", NULL, "Input Type Arg 5", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg5'], $HTTP_VARS['input_type_arg5']));
	}

	if ($attribute_type_r['s_field_type'] == 'ITEM_ID' || !$is_reserved_attribute_type) {
		if ($attribute_type_r['s_field_type'] == 'ITEM_ID')
			$function_list = build_function_list("display_type", array('hidden' => array(), 'display' => array()), ifempty($attribute_type_r['display_type'], $HTTP_VARS['display_type']));
		else
			$function_list = build_function_list("display_type", $display_type_functions, ifempty($attribute_type_r['display_type'], $HTTP_VARS['display_type']));

		echo format_field("Display Type", $function_list . get_function_help_link('display'));
	} else {
		echo format_field("Display Type", $attribute_type_r['display_type']);
	}

	if (!$is_reserved_attribute_type) {
		echo get_input_field("display_type_arg1", NULL, "Display Type Arg 1", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg1'], $HTTP_VARS['display_type_arg1']));
		echo get_input_field("display_type_arg2", NULL, "Display Type Arg 2", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg2'], $HTTP_VARS['display_type_arg2']));
		echo get_input_field("display_type_arg3", NULL, "Display Type Arg 3", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg3'], $HTTP_VARS['display_type_arg3']));
		echo get_input_field("display_type_arg4", NULL, "Display Type Arg 4", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg4'], $HTTP_VARS['display_type_arg4']));
		echo get_input_field("display_type_arg5", NULL, "Display Type Arg 5", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg5'], $HTTP_VARS['display_type_arg5']));
	} else {
		if (strlen($attribute_type_r['display_type_arg1']) > 0)
			echo format_field("Display Type Arg 1", $attribute_type_r['display_type_arg1']);
		if (strlen($attribute_type_r['display_type_arg2']) > 0)
			echo format_field("Input Type Arg 2", $attribute_type_r['display_type_arg2']);
		if (strlen($attribute_type_r['display_type_arg3']) > 0)
			echo format_field("Display Type Arg 3", $attribute_type_r['display_type_arg3']);
		if (strlen($attribute_type_r['display_type_arg4']) > 0)
			echo format_field("Display Type Arg 4", $attribute_type_r['display_type_arg4']);
		if (strlen($attribute_type_r['display_type_arg5']) > 0)
			echo format_field("Display Type Arg 5", $attribute_type_r['display_type_arg5']);
	}

	echo get_input_field("listing_link_ind", NULL, "Listing Link Indicator", "checkbox(Y,N)", "N", ifempty($attribute_type_r['listing_link_ind'], $HTTP_VARS['listing_link_ind']));

	if (!$is_reserved_attribute_type && $attribute_type_r['s_field_type'] != 'ADDRESS' && $attribute_type_r['s_field_type'] != 'RATING') {
		echo format_field("Field type", custom_select("s_field_type", $_FIELD_TYPES, "%key% - %value%", 1, ifempty($attribute_type_r['s_field_type'], $HTTP_VARS['s_field_type']), "key"));

		$sites = get_site_plugin_list_r();
		if (!is_array($sites))
			$sites[] = '';
		else if (!in_array('', $sites))
			$sites = array_merge(array(''), $sites);

		if (strlen($attribute_type_r['site_type']) > 0 && !in_array($attribute_type_r['site_type'], $sites))
			$sites[] = $attribute_type_r['site_type'];

		echo format_field("Site type", custom_select("site_type", $sites, "%value%", 1, ifempty($attribute_type_r['site_type'], $HTTP_VARS['site_type'])));
	} else {
		echo format_field("Field type", $attribute_type_r['s_field_type']);
	}

    echo build_roles_select($attribute_type_r);
}

function build_roles_select($attribute_type_r) {
    $user_roles = array();
    $result = fetch_user_role_rs();

    $public = fetch_role_r(get_public_access_rolename());
    $user_roles[] = array('role_name' => $public['role_name'], 'description' => $public['description']);

    while ($role = db_fetch_assoc($result)) {
        $user_roles[] = $role;
    }
    $select = format_field("View Permission", custom_select('view_perm', $user_roles, '%description%', 1, $attribute_type_r['view_perm'], 'role_name'));
    db_free_result($result);

    return $select;
}

function build_options_array($type, $input_type_functions_cats) {
	$buffer = "inputOptions['$type'] = new Array(";
	reset($input_type_functions_cats[$type]);
	foreach ($input_type_functions_cats[$type] as $value) {
		$buffer .= "'$value',";
	}

	$buffer = substr($buffer, 0, -1);
	$buffer .= ");\n";

	return $buffer;
}

function edit_attribute_ind_type_js() {
	global $input_type_functions_cats;

?>

<script language="JavaScript">

var inputOptions = new Array();
<?php
	echo build_options_array('multi', $input_type_functions_cats);
	echo build_options_array('lookup', $input_type_functions_cats);
	echo build_options_array('normal', $input_type_functions_cats);
?>

function populateInputSelect(selectObject, type)
{
	var value = selectObject.options[selectObject.options.selectedIndex].value;

	if(selectObject.options.length)
	{
		var length = selectObject.options.length;
		for(var i=0; i<length; i++)
			selectObject.options[0] = null;
	}

	for (var i=0; i<inputOptions[type].length; i++)
	{
		selectObject.options[i] = new Option(inputOptions[type][i]);
		if(inputOptions[type][i] == value)
		{
			selectObject.options[i].selected = true;
		}
	}
}

</script>
<?php
}

function build_attribute_ind_type_widget($attribute_ind_type, $HTTP_VARS) {
	$options = array('lookup' => 'Lookup', 'normal' => 'Normal', 'multi' => 'Multi Value');

	$field = '';
	foreach ($options as $key => $value) {
		$field .= "<input type=\"radio\" class=\"radio\" name=\"attribute_ind_type\" value=\"$key\" onClick=\"populateInputSelect(this.form['input_type'], '$key');\"";
		if ($key == $attribute_ind_type)
			$field .= ' CHECKED';

		$field .= ">$value ";
	}

	return $field;
}

function set_attribute_ind_type(&$HTTP_VARS) {
	$HTTP_VARS['file_attribute_ind'] = 'N';
	$HTTP_VARS['lookup_attribute_ind'] = 'N';
	$HTTP_VARS['multi_attribute_ind'] = 'N';

	if ($HTTP_VARS['attribute_ind_type'] == 'lookup') {
		$HTTP_VARS['lookup_attribute_ind'] = 'Y';
	} else if ($HTTP_VARS['attribute_ind_type'] == 'multi') {
		$HTTP_VARS['multi_attribute_ind'] = 'Y';
	}

	if ($HTTP_VARS['attribute_ind_type'] != 'lookup') {
		if ($HTTP_VARS['input_type'] == 'url') {
			$HTTP_VARS['file_attribute_ind'] = 'Y';
		}
	}

	return $HTTP_VARS;
}

if ($HTTP_VARS['op'] == 'delete') {
	if (!is_exists_item_attribute_type(NULL, $HTTP_VARS['s_attribute_type'])) {
		if (!is_reserved_s_attribute_type($HTTP_VARS['s_attribute_type'])) {
			$s_field_type = fetch_attribute_type_s_field_type($HTTP_VARS['s_attribute_type']);
			if ($s_field_type == 'ADDRESS' && is_exists_addr_attribute_type_rltshp(NULL, $HTTP_VARS['s_attribute_type'])) {
				$errors[] = array('error' => 'Attribute type not deleted.', 'detail' => 'Attribute is linked to at least one system address type');
				$HTTP_VARS['op'] = '';
			} else if ($s_field_type == 'RATING') {
				$errors[] = array('error' => 'Attribute type not deleted.', 'detail' => 'Attribute is reserved for ratings');
				$HTTP_VARS['op'] = '';
			} else if ($s_field_type != 'ADDRESS' && $s_field_type != 'RATING' && is_exists_item_attribute_type(NULL, $HTTP_VARS['s_attribute_type'])) {
				$errors[] = array('error' => 'Attribute type not deleted.', 'detail' => 'Attribute is linked to at least one system item type');
				$HTTP_VARS['op'] = '';
			} else if ($HTTP_VARS['confirmed'] == 'true') {
				if (delete_s_attribute_type($HTTP_VARS['s_attribute_type']))
					$HTTP_VARS['op'] = NULL;
				else {
					$errors[] = array('error' => 'Attribute type not deleted.', 'detail' => db_error());
					$HTTP_VARS['op'] = '';
				}
			} else if ($HTTP_VARS['confirmed'] != 'false') {
				echo "\n<h3>Delete Attribute type</h3>";
				echo (get_op_confirm_form($PHP_SELF, "Are you sure you want to delete attribute type '" . $HTTP_VARS['s_attribute_type'] . "'?", array('type' => $ADMIN_TYPE, 'op' => 'delete', 's_attribute_type' => $HTTP_VARS['s_attribute_type'], 'active_tab' => $HTTP_VARS['active_tab'])));
			} else {
				$HTTP_VARS['op'] = '';
			}
		} else {
			$errors[] = array('error' => 'Attributes with \'S_\' prefix are reserved for internal use.');
			$HTTP_VARS['op'] = '';
		}
	} else {
		$errors[] = array('error' => 'Attribute type not deleted.', 'detail' => 'Attribute is referenced by one or more Item Types');
		$HTTP_VARS['op'] = '';
	}
} else if ($HTTP_VARS['op'] == 'update') {
	$HTTP_VARS = set_attribute_ind_type($HTTP_VARS);

	if (is_exists_attribute_type($HTTP_VARS['s_attribute_type'])) {
		$s_field_type = fetch_attribute_type_s_field_type($HTTP_VARS['s_attribute_type']);
		if ($s_field_type == 'ITEM_ID') {
			if ($HTTP_VARS['display_type'] == 'display') {
				$HTTP_VARS['display_type_arg1'] = '%value%';
			} else if ($HTTP_VARS['display_type'] != 'hidden') {
				$HTTP_VARS['display_type'] = FALSE;
				$HTTP_VARS['display_type_arg1'] = FALSE;
			}

			$update_result = update_s_attribute_type($HTTP_VARS['s_attribute_type'], $HTTP_VARS['description'], $HTTP_VARS['prompt'], FALSE, //$HTTP_VARS['input_type'],
					FALSE, //$HTTP_VARS['input_type_arg1'],
					FALSE, //$HTTP_VARS['input_type_arg2'],
					FALSE, //$HTTP_VARS['input_type_arg3'],
					FALSE, //$HTTP_VARS['input_type_arg4'],
					FALSE, //$HTTP_VARS['input_type_arg5'],
					$HTTP_VARS['display_type'], $HTTP_VARS['display_type_arg1'], FALSE, //$HTTP_VARS['display_type_arg2'],
					FALSE, //$HTTP_VARS['display_type_arg3'],
					FALSE, //$HTTP_VARS['display_type_arg4'],
					FALSE, //$HTTP_VARS['display_type_arg5'],
					FALSE, //$HTTP_VARS['s_field_type'],
					FALSE, //$HTTP_VARS['site_type'],
					FALSE, //$HTTP_VARS['listing_link_ind'],
					FALSE, //$HTTP_VARS['file_attribute_ind'],
					FALSE, //$HTTP_VARS['lookup_attribute_ind'],
					FALSE, //$HTTP_VARS['multi_attribute_ind'],
                    FALSE); //$HTTP_VARS['view_perm']
		} else if ($s_field_type == 'RATING' || is_reserved_s_attribute_type($HTTP_VARS['s_attribute_type'])) { // For reserved types, only allow update of prompt.
			$update_result = update_s_attribute_type($HTTP_VARS['s_attribute_type'], $HTTP_VARS['description'], $HTTP_VARS['prompt'], FALSE, //$HTTP_VARS['input_type'],
					FALSE, //$HTTP_VARS['input_type_arg1'],
					FALSE, //$HTTP_VARS['input_type_arg2'],
					FALSE, //$HTTP_VARS['input_type_arg3'],
					FALSE, //$HTTP_VARS['input_type_arg4'],
					FALSE, //$HTTP_VARS['input_type_arg5'],
					FALSE, //$HTTP_VARS['display_type'],
					FALSE, //$HTTP_VARS['display_type_arg1'],
					FALSE, //$HTTP_VARS['display_type_arg2'],
					FALSE, //$HTTP_VARS['display_type_arg3'],
					FALSE, //$HTTP_VARS['display_type_arg4'],
					FALSE, //$HTTP_VARS['display_type_arg5'],
					FALSE, //$HTTP_VARS['s_field_type'],
					FALSE, //$HTTP_VARS['site_type'],
					FALSE, //$HTTP_VARS['listing_link_ind'],
					FALSE, //$HTTP_VARS['file_attribute_ind'],
					FALSE, //$HTTP_VARS['lookup_attribute_ind'],
					FALSE, //$HTTP_VARS['multi_attribute_ind'],
                    FALSE); //$HTTP_VARS['view_perm']
		} else if ($s_field_type == 'ADDRESS') { // for non S_ attributes, but those with an s_field_type of 'ADDRESS' the s_field_type should not be updateable, and the site_type should remain NULL
			$update_result = update_s_attribute_type($HTTP_VARS['s_attribute_type'], $HTTP_VARS['description'], $HTTP_VARS['prompt'], $HTTP_VARS['input_type'], $HTTP_VARS['input_type_arg1'], $HTTP_VARS['input_type_arg2'], $HTTP_VARS['input_type_arg3'], $HTTP_VARS['input_type_arg4'],
					$HTTP_VARS['input_type_arg5'], $HTTP_VARS['display_type'], $HTTP_VARS['display_type_arg1'], $HTTP_VARS['display_type_arg2'], $HTTP_VARS['display_type_arg3'], $HTTP_VARS['display_type_arg4'], $HTTP_VARS['display_type_arg5'], FALSE, //$HTTP_VARS['s_field_type'],
					FALSE, //$HTTP_VARS['site_type'],
					FALSE, //$HTTP_VARS['listing_link_ind'],
					FALSE, //$HTTP_VARS['file_attribute_ind'],
					FALSE, //$HTTP_VARS['lookup_attribute_ind'],
					FALSE, //$HTTP_VARS['multi_attribute_ind'],
                    FALSE); //$HTTP_VARS['view_perm']
		} else {
			if (strtoupper($HTTP_VARS['lookup_attribute_ind']) != 'Y' && fetch_s_attribute_type_lookup_cnt($HTTP_VARS['s_attribute_type']) > 0) {
				$HTTP_VARS['lookup_attribute_ind'] = 'Y';

				$errors[] = array('error' => 'System Attribute type lookups exist', 'detail' => 'Lookup Attribute Indicator reset to Y');
			}

			$update_result = update_s_attribute_type($HTTP_VARS['s_attribute_type'], $HTTP_VARS['description'], $HTTP_VARS['prompt'], $HTTP_VARS['input_type'], $HTTP_VARS['input_type_arg1'], $HTTP_VARS['input_type_arg2'], $HTTP_VARS['input_type_arg3'], $HTTP_VARS['input_type_arg4'],
					$HTTP_VARS['input_type_arg5'], $HTTP_VARS['display_type'], $HTTP_VARS['display_type_arg1'], $HTTP_VARS['display_type_arg2'], $HTTP_VARS['display_type_arg3'], $HTTP_VARS['display_type_arg4'], $HTTP_VARS['display_type_arg5'], $HTTP_VARS['s_field_type'], $HTTP_VARS['site_type'],
					$HTTP_VARS['listing_link_ind'], $HTTP_VARS['file_attribute_ind'], $HTTP_VARS['lookup_attribute_ind'], $HTTP_VARS['multi_attribute_ind'], $HTTP_VARS['view_perm']);
		}

		if (!$update_result) {
			$errors[] = array('error' => 'Attribute type not updated', 'detail' => db_error());
		}

		$HTTP_VARS['op'] = 'edit';
	} else {
		$HTTP_VARS['op'] = 'edit';
	}
} else if ($HTTP_VARS['op'] == 'insert') {
	set_attribute_ind_type($HTTP_VARS);

	$HTTP_VARS['s_attribute_type'] = strtoupper(preg_replace("/[\s|'|\\\\|\"]+/", "", trim(strip_tags($HTTP_VARS['s_attribute_type']))));
	if (!is_exists_attribute_type($HTTP_VARS['s_attribute_type'])) {
		if (!is_reserved_s_attribute_type($HTTP_VARS['s_attribute_type'])) {
			// site type not valid for these
			if ($HTTP_VARS['s_field_type'] == 'ADDRESS' || $HTTP_VARS['s_field_type'] == 'RATING') {
				$HTTP_VARS['site_type'] = NULL;
			}

			if (!insert_s_attribute_type($HTTP_VARS['s_attribute_type'], $HTTP_VARS['description'], $HTTP_VARS['prompt'], $HTTP_VARS['input_type'], $HTTP_VARS['input_type_arg1'], $HTTP_VARS['input_type_arg2'], $HTTP_VARS['input_type_arg3'], $HTTP_VARS['input_type_arg4'],
					$HTTP_VARS['input_type_arg5'], $HTTP_VARS['display_type'], $HTTP_VARS['display_type_arg1'], $HTTP_VARS['display_type_arg2'], $HTTP_VARS['display_type_arg3'], $HTTP_VARS['display_type_arg4'], $HTTP_VARS['display_type_arg5'], $HTTP_VARS['s_field_type'], $HTTP_VARS['site_type'],
					$HTTP_VARS['listing_link_ind'], $HTTP_VARS['file_attribute_ind'], $HTTP_VARS['lookup_attribute_ind'], $HTTP_VARS['multi_attribute_ind'], $HTTP_VARS['view_perm'])) {
				$errors[] = array('error' => 'Attribute type (' . $HTTP_VARS['s_attribute_type'] . ') not inserted', 'detail' => db_error());
				$HTTP_VARS['op'] = 'new';
			} else {
				$HTTP_VARS['op'] = 'edit';
				$HTTP_VARS['active_tab'] = strtoupper(substr(trim($HTTP_VARS['s_attribute_type']), 0, 1));
			}
		} else {
			$errors[] = array('error' => 'Attribute type\'s with a \'S_\' prefix are reserved for internal use.');
			$HTTP_VARS['op'] = 'new';
		}
	} else {
		$errors[] = array('error' => 'Attribute type (' . $HTTP_VARS['s_attribute_type'] . ') already exists.');
		$HTTP_VARS['op'] = 'new';
	}
} else if ($HTTP_VARS['op'] == 'update-lookups') {
	if (is_not_empty_array($HTTP_VARS['value'])) {
		for ($i = 0; $i < count($HTTP_VARS['value']); $i++) {
			// If exists_ind and value is empty, this is fine.  Or as long as a display value is specified for an
			// empty value, and there is not already an empty value, then this is legal as well.
			if (strlen($HTTP_VARS['value'][$i]) > 0 || strlen($HTTP_VARS['display'][$i]) > 0 || $HTTP_VARS['exists_ind'][$i] == 'Y') {
				// an update or delete.
				if ($HTTP_VARS['exists_ind'][$i] == 'Y') {
					if (is_exists_s_atribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i])) {
						if ($HTTP_VARS['delete_ind'][$i] === 'Y') {
							if (!delete_s_attribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i])) {
								$errors[] = array('error' => 'Lookup value (' . $HTTP_VARS['value'][$i] . ') not deleted', 'detail' => db_error());
							}
						} else { //update
							if (theme_image_src($HTTP_VARS['img'][$i]) == FALSE)
								$HTTP_VARS['img'][$i] = '';

							if (strlen($HTTP_VARS['img'][$i]) == 0 && $HTTP_VARS['none_img'][$i] == 'Y')
								$HTTP_VARS['img'][$i] = 'none';

							if (!update_s_attribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i], $HTTP_VARS['display'][$i], $HTTP_VARS['img'][$i], $HTTP_VARS['checked_ind'][$i], $HTTP_VARS['order_no'][$i])) {
								$errors[] = array('error' => 'Lookup value (' . $HTTP_VARS['value'][$i] . ') not updated', 'detail' => db_error());
							}
						}
					} else {
						$errors[] = array('error' => 'Lookup value (' . $HTTP_VARS['value'][$i] . ') not found', 'detail' => '');
					}
				} else { //insert!
					// Get rid of all spaces, and illegal characters.
					$HTTP_VARS['value'][$i] = preg_replace("/[\s|'|\\\\|\"]+/", "", trim(strip_tags($HTTP_VARS['value'][$i])));

					if (!is_exists_s_atribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i])) {
						if (theme_image_src($HTTP_VARS['img'][$i]) == FALSE)
							$HTTP_VARS['img'][$i] = '';

						if (strlen($HTTP_VARS['img'][$i]) == 0 && $HTTP_VARS['none_img'][$i] == 'Y')
							$HTTP_VARS['img'][$i] = 'none';

						// First of all we need to handle the image upload here.
						if (!insert_s_attribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i], $HTTP_VARS['display'][$i], $HTTP_VARS['img'][$i], $HTTP_VARS['checked_ind'][$i], $HTTP_VARS['order_no'][$i])) {
							$errors[] = array('error' => 'Lookup value (' . $HTTP_VARS['value'][$i] . ') not inserted', 'detail' => db_error());
						}
					} else {
						$errors[] = array('error' => 'Lookup value (' . $HTTP_VARS['value'][$i] . ') already exists', 'detail' => '');
					}
				}
			}
		}
	}

	$HTTP_VARS['op'] = 'edit-lookups';
}

if ($HTTP_VARS['op'] == 'new' || $HTTP_VARS['op'] == 'edit') {
	echo get_javascript("admin/s_attribute_type/widgettooltips.js");
	echo get_widget_tooltip_array();

	echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&active_tab=" . $HTTP_VARS['active_tab'] . "\">Back to Main</a>]</p>");

	if ($HTTP_VARS['op'] == 'edit') {
		$attribute_type_r = fetch_s_attribute_type_r($HTTP_VARS['s_attribute_type']);
		if ($attribute_type_r === FALSE)
			$errors[] = 'Attribute type (' . $HTTP_VARS['s_attribute_type'] . ') not found';

		echo ("\n<h3>Edit Attribute type</h3>");

		$save_op = 'update';
		$save_button = 'Update';
	} else {
		echo ("\n<h3>New Attribute type</h3>");
		$save_op = 'insert';
		$save_button = 'Insert';
	}

	if (is_not_empty_array($errors ?? ""))
		echo format_error_block($errors);

	echo ("\n<form name=\"s_attribute_type\" action=\"$PHP_SELF\" method=\"POST\">");
	echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $HTTP_VARS['type'] . "\">");
	echo ("\n<input type=\"hidden\" name=\"op\" value=\"$save_op\">");
	echo ("\n<input type=\"hidden\" name=\"active_tab\" value=\"" . $HTTP_VARS['active_tab'] . "\">");

	echo ("\n<table>");
	display_edit_form($attribute_type_r, $HTTP_VARS);
	echo ("\n</table>");

	echo (format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory')));

	if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
		echo ("\n<input type=\"button\" class=\"button\" value=\"$save_button\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
	else
		echo ("\n<input type=\"button\" class=\"button\" value=\"$save_button\" onclick=\"this.form.submit();\">");

	echo ("\n</form>");
} else if ($HTTP_VARS['op'] == 'edit-lookups') {
	// ################################################################
	// Do for both 'update' and 'edit'
	// ################################################################

	echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&active_tab=" . $HTTP_VARS['active_tab'] . "\">Back to Main</a>]</p>");

	echo ("<script language=\"JavaScript1.2\">
		function toggleChecked(element, name)
		{
			var form = element.form;

			// then we have to uncheck everything else.
			for (var i=0; i < form.length; i++)
			{
		        if (form.elements[i].type.toLowerCase() == 'checkbox' && form.elements[i].name.substring(0, name.length+1) == name+'[')
				{
					if(element.checked && form.elements[i].name != element.name)
		                form.elements[i].checked = false;
				}
			}
		}</script>");

	echo ("\n<h3>Edit " . $HTTP_VARS['s_attribute_type'] . " Attribute Type Lookups</h3>");

	if (is_not_empty_array($errors ?? ""))
		echo format_error_block($errors);

	display_lookup_attribute_type_form($HTTP_VARS);

	echo (format_help_block('Image(s) must be in a <i>theme search path</i> directory.'));

} else if ($HTTP_VARS['op'] == '') {
	if (is_not_empty_array($errors ?? ""))
		echo format_error_block($errors);

	echo ("<p>[<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new\">New Attribute Type</a>]</p>");

	display_attribute_type_form($HTTP_VARS);
}
?>
