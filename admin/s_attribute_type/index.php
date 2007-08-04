<?php
/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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

include_once("./functions/site_plugin.php");
include_once("./functions/parseutils.php");
include_once("./functions/scripts.php");

// Display help footer for lookup attribute type edit form
$edit_satl_form_help = array('Image(s) must be in a <i>theme search path</i> directory.');

$input_type_functions_cats = array(
			'file'=>array('url'),
			'lookup'=>array('radio_grid', 'checkbox_grid', 'single_select', 'multi_select'),
			'multi'=>array('text'),
			'normal'=>array(),
			'restricted'=>array('review_options'));

reset($input_type_functions);
while(list($key,) = each($input_type_functions))
{
	if(!in_array($key, $input_type_functions_cats['lookup']) && !in_array($key, $input_type_functions_cats['restricted']))
	{
		$input_type_functions_cats['normal'][] = $key;		
	}
}

function get_attribute_ind_type($attribute_type_r, $HTTP_VARS)
{
	$attribute_ind_type = 'normal';
	
	if(is_array($attribute_type_r))
	{
		if(strtoupper($attribute_type_r['file_attribute_ind']) == 'Y')
			$attribute_ind_type = 'file';
		else if(strtoupper($attribute_type_r['lookup_attribute_ind']) == 'Y')
			$attribute_ind_type = 'lookup';
		else if(strtoupper($attribute_type_r['multi_attribute_ind']) == 'Y')
			$attribute_ind_type = 'multi';
	}
	else if(strlen($HTTP_VARS['attribute_ind_type'])>0)
	{
		$attribute_ind_type = $HTTP_VARS['attribute_ind_type'];
	}
	
	return $attribute_ind_type;
}

function get_attribute_ind_type_function_list($type)
{
	global $input_type_functions_cats;
	global $input_type_functions;
	
	$new_function_list = Array();
	
	reset($input_type_functions);
	while(list($key,$function) = each($input_type_functions))
	{
		if(in_array($key, $input_type_functions_cats[$type]))
		{
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
function build_function_list($name, $list_array, $function_type, $onchange_event=NULL)
{
	$select = "\n<select name=\"$name\" onchange=\"$onchange_event\">";

	while(list($key,) = each($list_array))
	{
		if(strcasecmp($function_type, $key)===0)
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
function get_function_spec($type, $func_args)
{
	$args = "";

	@reset($func_args);
	while(list(,$value) = @each($func_args))
	{
		if(substr($value,-3) === '[Y]')
		{
			$value = substr($value,0,-3);
			if(strlen($args)==0)
				$args .= $value;
			else
				$args .= ", $value";
		}
		else
		{
			if(strlen($args)==0)
				$args .= "[$value]";
			else
				$args .= "[, $value]";
		}
	}

	if(strlen($args)>0)
		return $type."(".$args.")";
	else
		return $type;
}

function get_widget_tooltip_array()
{
	global $input_type_functions;
	global $display_type_functions;
	
	$arrayOfAttributes = "arrayOfWidgetTooltips = new Array(".(count($input_type_functions)+count($display_type_functions)).");\n";
	$count = 0;
	
	//name, type, description, spec, args
	reset($input_type_functions);
	while(list($name, $definition) = each($input_type_functions))
	{
		$arrayOfAttributes .= "arrayOfWidgetTooltips[$count] = ".get_widget_js_entry($name, 'input', $definition);
		$count++;
	}
	
	reset($display_type_functions);
	while(list($name, $definition) = each($display_type_functions))
	{
		$arrayOfAttributes .= "arrayOfWidgetTooltips[$count] = ".get_widget_js_entry($name, 'display', $definition);
		$count++;
	}	
	
	return "<script language=\"JavaScript\">".$arrayOfAttributes."</script>";
}

function get_widget_js_entry($name, $type, $definition)
{
	global $argument_types;
	
	$description = $definition['description'];
	$spec = get_function_spec($name, $definition['args']);
	
	$args = array();
	while(list(,$value) = each($definition['args']))
	{
		if(substr($value,-3) === "[Y]")
		{
			$value = substr($value,0,-3);
		}
			
		$arg = $value." - ";
			
		if(is_array($argument_types[$value]))
		{
			$arg .= $argument_types[$value]['description'];
		}
		
		$args[] = $arg;
	}
	
	return "new WidgetToolTip('$name', '$type', '".addslashes($description)."', '".addslashes($spec)."', ".get_javascript_array($args).");\n";
}

function get_function_help_link($type)
{
	$fieldname = $type."_type";
	
	return "<a href=\"#\" onmouseover=\"return show_widget_select_tooltip(document.forms['s_attribute_type']['$fieldname'], '$type', arrayOfWidgetTooltips);\" onmouseout=\"return hide_tooltip();\">(?)</a>";
}

function display_attribute_type_form($HTTP_VARS)
{
	global $PHP_SELF;
	global $ADMIN_TYPE;
	
	$block = "<div class=\"tabContainer\"><form name=\"s_attribute_type_lookup\" action=\"$PHP_SELF\" method=\"POST\">".
		"<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">".
		"<input type=\"hidden\" name=\"op\" value=\"\">".
		"<input type=\"hidden\" name=\"s_attribute_type\" value=\"".$HTTP_VARS['s_attribute_type']."\">";
	
	$tabBlock = '';
   	$paneBlock = '';
   	$pageno = 1;
   	$count = 0;
   	
	$results = fetch_attribute_type_rs();
	if($results)
	{
		// value, display, img, checked_ind, order_no
		$row = 0;
		while($attribute_type_r = db_fetch_assoc($results))
		{
			if($count == 20)
			{
				$count = 0;
				$pageno++;
				$paneBlock .= "</table></div>";
			}
			
			if($count == 0)
			{   
				$tabBlock .= "<li id=\"menu-pane$pageno\"".($pageno==1?" class=\"activetab\" ":"")." onclick=\"return activateTab('pane$pageno', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">Page&nbsp;$pageno</li>";
					
				$paneBlock .= "<div id=\"pane$pageno\" class=\"".($pageno==1?"tabContent":"tabContentHidden")."\">\n".
					    "\n<table>".
						"<tr class=\"navbar\">".
						"<th>Type</th>".
						"<th>Description</th>";
			
				if($ADMIN_TYPE != 's_address_attribute_type')
				{
					$paneBlock .= "<th>Field Type</th>";
				}
			
				$paneBlock .= "<th colspan=2></th></tr>";
			}
		
			$paneBlock .= display_s_attribute_type_row($attribute_type_r, $row);
			$count++;
			$row++;
		}
		db_free_result($results);
	}
	$paneBlock .= "</table></div>";

	$block .= "<ul class=\"tabMenu\" id=\"tab-menu\">".$tabBlock."</ul>";
	$block .= '<div id="tab-content">'.$paneBlock.'</div>';
	
	$block .= '</form></div>';
				
	return $block;
}

function display_lookup_attribute_type_form($HTTP_VARS)
{
	global $PHP_SELF;
	global $ADMIN_TYPE;
	
	$block = "<div class=\"tabContainer\"><form name=\"s_attribute_type_lookup\" action=\"$PHP_SELF\" method=\"POST\">".
		"<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">".
		"<input type=\"hidden\" name=\"op\" value=\"update-lookups\">".
		"<input type=\"hidden\" name=\"s_attribute_type\" value=\"".$HTTP_VARS['s_attribute_type']."\">";
		
	$row = 0;
	$attribute_type_rows = NULL;
	$results = fetch_attribute_type_lookup_rs($HTTP_VARS['s_attribute_type'], 'order_no, value ASC', FALSE);
	if($results)
	{
        // value, display, img, checked_ind, order_no
		while($attribute_type_lookup_r = db_fetch_assoc($results))
		{
			$attribute_type_rows[] = display_s_attribute_type_lookup_row($attribute_type_lookup_r, $row++);
		}
		db_free_result($results);
	}

	$emptyrows = 20 - (count($attribute_type_rows) % 20);
	if($emptyrows == 0)
		$emptyrows = 20;
	
	for($i=0; $i<$emptyrows; $i++)
	{
		$attribute_type_rows[] = display_s_attribute_type_lookup_row(array(), $row++);
	}
	
	$tabBlock = '';
   	$paneBlock = '';
   	$pageno = 1;
   	$count = 0;
	
	for($i=0; $i<count($attribute_type_rows); $i++)
	{
		if($count == 20)
		{
			$count = 0;
			$pageno++;
			$paneBlock .= "</table></div>";
		}
		
		if($count == 0)
		{   
			$tabBlock .= "<li id=\"menu-pane$pageno\"".($pageno==1?" class=\"activetab\" ":"")." onclick=\"return activateTab('pane$pageno', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">Page&nbsp;$pageno</li>";
					
			$paneBlock .= "<div id=\"pane$pageno\" class=\"".($pageno==1?"tabContent":"tabContentHidden")."\">\n".
							"<div style=\"{text-align: right;}\"><input type=submit value=\"Update\"></div>\n".
				"<table>".
				"<tr class=\"navbar\">"
				."<th>Delete</th>"
				."<th>Order</th>"
				."<th>Value</th>"
				."<th>Display</th>"
				."<th colspan=2>Image</th>"
				."<th>No<br>Image</th>"
    			."<th>Checked</th>"
				."</tr>";
		}
		
		$paneBlock .= $attribute_type_rows[$i];
		$count++;
	}
	
	$paneBlock .= "</table></div>";

	$block .= "<ul class=\"tabMenu\" id=\"tab-menu\">".$tabBlock."</ul>";
	$block .= '<div id="tab-content">'.$paneBlock.'</div>';
	
	
	$block .= '</form>';
	
	$block .= '</div>';

	return $block;
}

function display_s_attribute_type_row($attribute_type_r, $row)
{
	global $ADMIN_TYPE;
	global $PHP_SELF;

	$block = "\n<tr>";

	$block .= "\n<td class=\"data\" align=center>".$attribute_type_r['s_attribute_type']."</td>";
	$block .= "\n<td class=\"data\" align=center>".$attribute_type_r['description']."</td>";

	if($ADMIN_TYPE != 's_address_attribute_type')
	{
		$block .= "\n<td class=\"data\" align=center>".$attribute_type_r['s_field_type']."</td>";
	}

	$block .= "\n<td class=\"data\">[";
	$block .= " <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_attribute_type=".$attribute_type_r['s_attribute_type']."\">Edit</a>";
	$block .= " / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&s_attribute_type=".$attribute_type_r['s_attribute_type']."\">Delete</a>";
	
	if($attribute_type_r['lookup_attribute_ind']=='Y')
		$block .= " / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit-lookups&s_attribute_type=".$attribute_type_r['s_attribute_type']."\">Edit Lookup</a>";
	
	$block .= " ]</td></tr>";
	
	return $block;
}

function display_s_attribute_type_lookup_row($lookup_r, $row)
{
	$block = "<tr>";
	
	$block .= "<td class=\"data\" align=center>";
	if(is_not_empty_array($lookup_r))
		$block .= get_input_field("delete_ind[$row]", NULL, NULL, "simple_checkbox()", "N", "Y", FALSE);
	else
		$block .= "&nbsp;";
	$block .= "</td>";
	
	$block .= "<td class=\"data\" align=center>".get_input_field("order_no[$row]", NULL, NULL, "number(3)", "N", $lookup_r['order_no'], FALSE)."</td>";

	// value
	if(is_not_empty_array($lookup_r))
	{
		$block .= "<td class=\"data\">".get_input_field("value[$row]", NULL, "Value", "readonly", "Y", $lookup_r['value'], FALSE).
			"<input type=hidden name=\"exists_ind[$row]\" value=\"Y\">".
			"</td>";
	}
	else // Limit value to 50 characters, because this is really as large as they should get!
	{
		$block .= "<td class=\"data\">".get_input_field("value[$row]", NULL, "Value", "text(10,50)", "Y", NULL, FALSE).
			"<input type=hidden name=\"exists_ind[$row]\" value=\"N\">".
			"</td>";
	}

	//display
	$block .= "<td class=\"data\">".get_input_field("display[$row]", NULL, NULL, "text(20,255)", "N", $lookup_r['display'], FALSE)."</td>";

	// Get the theme specific source of the image.
	if($lookup_r['img'] != 'none')
		$src = _theme_image_src($lookup_r['img']);

	$block .= "<td class=\"data\" align=center>";
	if($src!==FALSE && strlen($src)>0)
		$block .= "<img src=\"$src\">";
	else
		$block .= "&nbsp;";
	$block .= "</td>";

	$block .= "<td class=\"data\">".get_input_field("img[$row]", NULL, "Image", "url(15,*,\"gif,jpg,png\",N)", "N", $lookup_r['img']!="none"?$lookup_r['img']:NULL, FALSE, NULL, "if(this.value.length>0){this.form['none_img[$row]'].checked=false;}")."</td>";
	$block .= "<td class=\"data\" align=center>".get_input_field("none_img[$row]", NULL, NULL, "simple_checkbox(".($lookup_r['img'] == "none"?"CHECKED":"").")", "N", "Y", FALSE, NULL, "if(this.checked){this.form['img[$row]'].value='';}")."</td>";
	$block .= "<td class=\"data\" align=center><input type=\"checkbox\" name=\"checked_ind[{$row}]\" value=\"Y\" onclick=\"toggleChecked(this, 'checked_ind')\" ".(strtoupper($lookup_r['checked_ind'])== 'Y'?'CHECKED':'').">";

	$block .= "</tr>";
	
	return $block;
}

function display_edit_form($attribute_type_r, $HTTP_VARS=NULL)
{
	global $display_type_functions;
	global $_FIELD_TYPES;
	
	// s_attribute_type
	if(is_array($attribute_type_r))
		echo get_input_field("s_attribute_type", NULL, "Attribute Type", "readonly", "Y", $attribute_type_r['s_attribute_type']);
	else
		echo get_input_field("s_attribute_type", NULL, "Attribute Type", "text(10,10)", "Y", $HTTP_VARS['s_attribute_type'], TRUE, NULL, 'this.value=trim(this.value.toUpperCase()); if(this.value.substring(0,2) == \'S_\'){alert(\'Attributes with a \\\'S_\\\' prefix are reserved for internal use.\'); this.value=\'\'; this.focus(); return false; }');

	//description
	echo get_input_field("description", NULL, "Description", "text(30,60)", "Y", ifempty($attribute_type_r['description'], $HTTP_VARS['description']));
	
	//prompt
	echo get_input_field("prompt", NULL, "Prompt", "text(20,30)", "Y", ifempty($attribute_type_r['prompt'], $HTTP_VARS['prompt']));

	$is_reserved_attribute_type = is_reserved_s_attribute_type($attribute_type_r['s_attribute_type']);

	if(!$is_reserved_attribute_type)
	{
		edit_attribute_ind_type_js();
		$attribute_ind_type = get_attribute_ind_type($attribute_type_r, $HTTP_VARS);
		echo format_field('Attribute Type Indicator', NULL, build_attribute_ind_type_widget($attribute_ind_type));
	}			
   
	if($is_reserved_attribute_type)
	{
        echo format_field("Input Type", NULL, $attribute_type_r['input_type']);
        
        if(strlen($attribute_type_r['input_type_arg1'])>0)
			echo format_field("Input Type Arg 1", NULL, $attribute_type_r['input_type_arg1']);
		if(strlen($attribute_type_r['input_type_arg2'])>0)
			echo format_field("Input Type Arg 2", NULL, $attribute_type_r['input_type_arg2']);
		if(strlen($attribute_type_r['input_type_arg3'])>0)
			echo format_field("Input Type Arg 3", NULL, $attribute_type_r['input_type_arg3']);
		//if(strlen($attribute_type_r['input_type_arg4'])>0)
		//	echo format_field("Input Type Arg 4", NULL, $attribute_type_r['input_type_arg4']);
		//if(strlen($attribute_type_r['input_type_arg5'])>0)
		//	echo format_field("Input Type Arg 5", NULL, $attribute_type_r['input_type_arg5']);
	}
	else
	{
		$input_function_list = get_attribute_ind_type_function_list($attribute_ind_type);
		echo format_field("Input Type", 
						NULL, 
						build_function_list("input_type", $input_function_list, ifempty($attribute_type_r['input_type'], $HTTP_VARS['input_type'])).
						get_function_help_link('input'));
		
		echo get_input_field("input_type_arg1", NULL, "Input Type Arg 1", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg1'], $HTTP_VARS['input_type_arg1']));
		echo get_input_field("input_type_arg2", NULL, "Input Type Arg 2", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg2'], $HTTP_VARS['input_type_arg2']));
		echo get_input_field("input_type_arg3", NULL, "Input Type Arg 3", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg3'], $HTTP_VARS['input_type_arg3']));
		//echo get_input_field("input_type_arg4", NULL, "Input Type Arg 4", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg4'], $HTTP_VARS['input_type_arg4']));
		//echo get_input_field("input_type_arg5", NULL, "Input Type Arg 5", "text(25)", 'N', ifempty($attribute_type_r['input_type_arg5'], $HTTP_VARS['input_type_arg5']));
	}

    if($attribute_type_r['s_field_type'] == 'ITEM_ID' || !$is_reserved_attribute_type)
	{
        if($attribute_type_r['s_field_type'] == 'ITEM_ID')
        	$function_list = build_function_list("display_type", array('hidden'=>array(),'display'=>array()), ifempty($attribute_type_r['display_type'], $HTTP_VARS['display_type']));
		else
            $function_list = build_function_list("display_type", $display_type_functions, ifempty($attribute_type_r['display_type'], $HTTP_VARS['display_type']));

		echo format_field("Display Type",
					NULL,
					$function_list.
					get_function_help_link('display'));
	}
	else
	{
		echo format_field("Display Type", NULL, $attribute_type_r['display_type']);
	}
	
	if(!$is_reserved_attribute_type)
	{
		echo get_input_field("display_type_arg1", NULL, "Display Type Arg 1", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg1'], $HTTP_VARS['display_type_arg1']));
		echo get_input_field("display_type_arg2", NULL, "Display Type Arg 2", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg2'], $HTTP_VARS['display_type_arg2']));
		echo get_input_field("display_type_arg3", NULL, "Display Type Arg 3", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg3'], $HTTP_VARS['display_type_arg3']));
		//echo get_input_field("display_type_arg4", NULL, "Display Type Arg 4", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg4'], $HTTP_VARS['display_type_arg4']));
		//echo get_input_field("display_type_arg5", NULL, "Display Type Arg 5", "text(25)", 'N', ifempty($attribute_type_r['display_type_arg5'], $HTTP_VARS['display_type_arg5']));
	}
	else
	{
        if(strlen($attribute_type_r['display_type_arg1'])>0)
			echo format_field("Display Type Arg 1", NULL, $attribute_type_r['display_type_arg1']);
		if(strlen($attribute_type_r['display_type_arg2'])>0)
			echo format_field("Input Type Arg 2", NULL, $attribute_type_r['display_type_arg2']);
		if(strlen($attribute_type_r['display_type_arg3'])>0)
			echo format_field("Display Type Arg 3", NULL, $attribute_type_r['display_type_arg3']);
		//if(strlen($attribute_type_r['display_type_arg4'])>0)
		//	echo format_field("Display Type Arg 4", NULL, $attribute_type_r['display_type_arg4']);
		//if(strlen($attribute_type_r['display_type_arg5'])>0)
		//	echo format_field("Display Type Arg 5", NULL, $attribute_type_r['display_type_arg5']);
	}

	echo get_input_field("listing_link_ind", NULL, "Listing Link Indicator", "checkbox(Y,N)", "N", ifempty($attribute_type_r['listing_link_ind'],$HTTP_VARS['listing_link_ind']));
	
    if(!$is_reserved_attribute_type && $attribute_type_r['s_field_type'] != 'ADDRESS' && $attribute_type_r['s_field_type'] != 'RATING')
	{
		echo format_field("Field type",
				NULL,
				custom_select("s_field_type", $_FIELD_TYPES, "%key% - %value%", 1, ifempty($attribute_type_r['s_field_type'], $HTTP_VARS['s_field_type']), "key"));
			
			$sites = get_site_plugin_list_r();
			if(!is_array($sites))
				$sites[] = '';
			else if(!in_array('', $sites))
				$sites = array_merge(array(''),$sites);
				
			if(strlen($attribute_type_r['site_type'])>0 && !in_array($attribute_type_r['site_type'], $sites))
				$sites[] = $attribute_type_r['site_type'];
			
			echo format_field("Site type", 
						NULL,
						custom_select("site_type", $sites, "%value%", 1, ifempty($attribute_type_r['site_type'], $HTTP_VARS['site_type'])));
	}
	else
	{
		echo format_field("Field type", NULL, $attribute_type_r['s_field_type']);
	}
}

function build_options_array($type, $input_type_functions_cats)
{
	$buffer = "inputOptions['$type'] = new Array(";
	reset($input_type_functions_cats[$type]);
	while(list(,$value) = each($input_type_functions_cats[$type]))
	{
		$buffer .= "'$value',";
	}
	
	$buffer = substr($buffer, 0, -1);
	$buffer .= ");\n";
	
	return $buffer;
}

function edit_attribute_ind_type_js()
{
	global $input_type_functions_cats;
	
?>
<script language="JavaScript">

var inputOptions = new Array();

<?php
echo build_options_array('file', $input_type_functions_cats);
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

function build_attribute_ind_type_widget($attribute_ind_type)
{
	$options = array('normal'=>'Normal',
					'file'=>'File Resource',
					'lookup'=>'Lookup',
					'multi'=>'Multi Value');
	
	$count = 0;
	$field = '';
	while(list($key,$value) = each($options))
	{
		$field .= "<input type=\"radio\" name=\"attribute_ind_type\" value=\"$key\" onClick=\"populateInputSelect(this.form['input_type'], '$key');\"";
		if($key == $attribute_ind_type)
			$field .= ' CHECKED';
				
		$field .= ">$value ";

		$count++;		
		if($count > 0 && $count % 2 == 0)
			$field .= "<br>";
	}
	
	return $field;
}

function set_attribute_ind_type(&$HTTP_VARS)
{
	$HTTP_VARS['file_attribute_ind'] = 'N';
	$HTTP_VARS['lookup_attribute_ind'] = 'N';
	$HTTP_VARS['multi_attribute_ind'] = 'N';
	
	if($HTTP_VARS['attribute_ind_type'] == 'file')
	{
		$HTTP_VARS['file_attribute_ind'] = 'Y';	
	}
	else if($HTTP_VARS['attribute_ind_type'] == 'lookup')
	{
		$HTTP_VARS['lookup_attribute_ind'] = 'Y';
	}
	else if($HTTP_VARS['attribute_ind_type'] == 'multi')
	{
		$HTTP_VARS['multi_attribute_ind'] = 'Y';
	}
}

if(is_opendb_valid_session())
{
	if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
        if($HTTP_VARS['op'] == 'delete')
		{
            if(!is_exists_item_attribute_type(NULL, $HTTP_VARS['s_attribute_type']))
			{
                if(!is_reserved_s_attribute_type($HTTP_VARS['s_attribute_type']))
				{
                    $s_field_type = fetch_attribute_type_s_field_type($HTTP_VARS['s_attribute_type']);
                    if($s_field_type == 'ADDRESS' && is_exists_addr_attribute_type_rltshp(NULL, $HTTP_VARS['s_attribute_type']))
					{
						$errors[] = array('error'=>'Attribute type not deleted.', 'detail'=>'Attribute is linked to at least one system address type');
                        $HTTP_VARS['op'] = '';
					}
					else if($s_field_type == 'RATING')
					{
						$errors[] = array('error'=>'Attribute type not deleted.', 'detail'=>'Attribute is reserved for ratings');
                        $HTTP_VARS['op'] = '';
					}
					else if($s_field_type != 'ADDRESS' && $s_field_type != 'RATING' && is_exists_item_attribute_type(NULL, $HTTP_VARS['s_attribute_type']))
					{
						$errors[] = array('error'=>'Attribute type not deleted.', 'detail'=>'Attribute is linked to at least one system item type');
                        $HTTP_VARS['op'] = '';
					}
					else if($HTTP_VARS['confirmed'] == 'true')
					{
						if(delete_s_attribute_type($HTTP_VARS['s_attribute_type']))
							$HTTP_VARS['op'] = NULL;
						else
						{
							$errors[] = array('error'=>'Attribute type not deleted.','detail'=>db_error());
                            $HTTP_VARS['op'] = '';
						}
					}
					else if($HTTP_VARS['confirmed'] != 'false')
					{
		                echo "\n<h3>Delete Attribute type</h3>";
						echo(get_op_confirm_form($PHP_SELF,
							"Are you sure you want to delete attribute type '".$HTTP_VARS['s_attribute_type']."'?",
							array('type'=>$ADMIN_TYPE, 'op'=>'delete', 's_attribute_type'=>$HTTP_VARS['s_attribute_type'])));
					}
                    else
					{
						$HTTP_VARS['op'] = '';
					}
				}
				else
				{
                    $errors[] = array('error'=>'Attributes with \'S_\' prefix are reserved for internal use.');
                    $HTTP_VARS['op'] = '';
				}
			}
			else
			{
                $errors[] = array('error'=>'Attribute type not deleted.', 'detail'=>'Attribute is referenced by one or more Item Types');
                $HTTP_VARS['op'] = '';
			}
		}
		else if($HTTP_VARS['op'] == 'update')
		{
			set_attribute_ind_type($HTTP_VARS);
			
			if(is_exists_attribute_type($HTTP_VARS['s_attribute_type']))
			{
				
				// fetch the current s_field_type
				$s_field_type = fetch_attribute_type_s_field_type($HTTP_VARS['s_attribute_type']);
					
				if($s_field_type == 'ITEM_ID')
				{
					if($HTTP_VARS['display_type'] == 'display')
					{
						$HTTP_VARS['display_type_arg1'] = '%value%';
					}
					else if($HTTP_VARS['display_type'] != 'hidden')
					{
						$HTTP_VARS['display_type'] = FALSE;
						$HTTP_VARS['display_type_arg1'] = FALSE;
					}
					
                    $update_result = update_s_attribute_type(
								$HTTP_VARS['s_attribute_type'], 
								$HTTP_VARS['description'], 
								$HTTP_VARS['prompt'], 
								FALSE, //$HTTP_VARS['input_type'],
								FALSE, //$HTTP_VARS['input_type_arg1'], 
								FALSE, //$HTTP_VARS['input_type_arg2'], 
								FALSE, //$HTTP_VARS['input_type_arg3'], 
								FALSE, //$HTTP_VARS['input_type_arg4'], 
								FALSE, //$HTTP_VARS['input_type_arg5'], 
								$HTTP_VARS['display_type'],
								$HTTP_VARS['display_type_arg1'],
								FALSE, //$HTTP_VARS['display_type_arg2'],
								FALSE, //$HTTP_VARS['display_type_arg3'],
								FALSE, //$HTTP_VARS['display_type_arg4'],
								FALSE, //$HTTP_VARS['display_type_arg5'],
								FALSE, //$HTTP_VARS['s_field_type'], 
								FALSE, //$HTTP_VARS['site_type'], 
								FALSE, //$HTTP_VARS['listing_link_ind'], 
								FALSE, //$HTTP_VARS['file_attribute_ind'], 
								FALSE, //$HTTP_VARS['lookup_attribute_ind'], 
								FALSE); //$HTTP_VARS['multi_attribute_ind']
				}
				else if($s_field_type == 'RATING' || is_reserved_s_attribute_type($HTTP_VARS['s_attribute_type'])) // For reserved types, only allow update of prompt.
				{
					$update_result = update_s_attribute_type(
								$HTTP_VARS['s_attribute_type'], 
								$HTTP_VARS['description'], 
								$HTTP_VARS['prompt'], 
								FALSE, //$HTTP_VARS['input_type'],
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
								FALSE); //$HTTP_VARS['multi_attribute_ind']
				}
				else if($s_field_type == 'ADDRESS') // for non S_ attributes, but those with an s_field_type of 'ADDRESS' the s_field_type should not be updateable, and the site_type should remain NULL
				{
					$update_result = update_s_attribute_type(
								$HTTP_VARS['s_attribute_type'], 
								$HTTP_VARS['description'], 
								$HTTP_VARS['prompt'], 
								$HTTP_VARS['input_type'],
								$HTTP_VARS['input_type_arg1'], 
								$HTTP_VARS['input_type_arg2'], 
								$HTTP_VARS['input_type_arg3'], 
								$HTTP_VARS['input_type_arg4'], 
								$HTTP_VARS['input_type_arg5'], 
								$HTTP_VARS['display_type'],
								$HTTP_VARS['display_type_arg1'],
								$HTTP_VARS['display_type_arg2'],
								$HTTP_VARS['display_type_arg3'],
								$HTTP_VARS['display_type_arg4'],
								$HTTP_VARS['display_type_arg5'],
								FALSE, //$HTTP_VARS['s_field_type'], 
								FALSE, //$HTTP_VARS['site_type'], 
								FALSE, //$HTTP_VARS['listing_link_ind'], 
								FALSE, //$HTTP_VARS['file_attribute_ind'], 
								FALSE, //$HTTP_VARS['lookup_attribute_ind'], 
								FALSE); //$HTTP_VARS['multi_attribute_ind']
				}
				else
				{
                    if(strtoupper($HTTP_VARS['lookup_attribute_ind']) != 'Y' && fetch_s_attribute_type_lookup_cnt($HTTP_VARS['s_attribute_type'])>0)
					{
                        $HTTP_VARS['lookup_attribute_ind'] = 'Y';

                        $errors[] = array('error'=>'System Attribute type lookups exist', 'detail'=>'Lookup Attribute Indicator reset to Y');
					}

					$update_result = update_s_attribute_type(
								$HTTP_VARS['s_attribute_type'], 
								$HTTP_VARS['description'], 
								$HTTP_VARS['prompt'], 
								$HTTP_VARS['input_type'],
								$HTTP_VARS['input_type_arg1'], 
								$HTTP_VARS['input_type_arg2'], 
								$HTTP_VARS['input_type_arg3'], 
								$HTTP_VARS['input_type_arg4'], 
								$HTTP_VARS['input_type_arg5'], 
								$HTTP_VARS['display_type'],
								$HTTP_VARS['display_type_arg1'],
								$HTTP_VARS['display_type_arg2'],
								$HTTP_VARS['display_type_arg3'],
								$HTTP_VARS['display_type_arg4'],
								$HTTP_VARS['display_type_arg5'],
								$HTTP_VARS['s_field_type'], 
								$HTTP_VARS['site_type'], 
								$HTTP_VARS['listing_link_ind'], 
								$HTTP_VARS['file_attribute_ind'], 
								$HTTP_VARS['lookup_attribute_ind'], 
								$HTTP_VARS['multi_attribute_ind']);
				}

				if(!$update_result)
				{
					$errors[] = array('error'=>'Attribute type not updated','detail'=>db_error());
				}

                $HTTP_VARS['op'] = 'edit';
            }
			else
			{
				$HTTP_VARS['op'] = 'edit';
			}
		}
		else if($HTTP_VARS['op'] == 'insert')
		{
			set_attribute_ind_type($HTTP_VARS);
			
            $HTTP_VARS['s_attribute_type'] = strtoupper(preg_replace("/[\s|'|\\\\|\"]+/", "", trim(strip_tags($HTTP_VARS['s_attribute_type']))));
            if(!is_exists_attribute_type($HTTP_VARS['s_attribute_type']))
			{
				if(!is_reserved_s_attribute_type($HTTP_VARS['s_attribute_type']))
				{
					// site type not valid for these
					if($HTTP_VARS['s_field_type'] == 'ADDRESS' || $HTTP_VARS['s_field_type'] == 'RATING')
					{
						$HTTP_VARS['site_type'] = NULL;
					}
					
					if(!insert_s_attribute_type(
								$HTTP_VARS['s_attribute_type'], 
								$HTTP_VARS['description'], 
								$HTTP_VARS['prompt'], 
								$HTTP_VARS['input_type'],
								$HTTP_VARS['input_type_arg1'], 
								$HTTP_VARS['input_type_arg2'], 
								$HTTP_VARS['input_type_arg3'], 
								$HTTP_VARS['input_type_arg4'], 
								$HTTP_VARS['input_type_arg5'], 
								$HTTP_VARS['display_type'],
								$HTTP_VARS['display_type_arg1'],
								$HTTP_VARS['display_type_arg2'],
								$HTTP_VARS['display_type_arg3'],
								$HTTP_VARS['display_type_arg4'],
								$HTTP_VARS['display_type_arg5'],
								$HTTP_VARS['s_field_type'], 
								$HTTP_VARS['site_type'], 
								$HTTP_VARS['listing_link_ind'], 
								$HTTP_VARS['file_attribute_ind'], 
								$HTTP_VARS['lookup_attribute_ind'], 
								$HTTP_VARS['multi_attribute_ind']))
					{
						$errors[] = array('error'=>'Attribute type ('.$HTTP_VARS['s_attribute_type'].') not inserted','detail'=>db_error());
                        $HTTP_VARS['op'] = 'new';
					}
					else
					{
                        $HTTP_VARS['op'] = 'edit';
					}
				}
				else
				{
					$errors[] = array('error'=>'Attribute type\'s with a \'S_\' prefix are reserved for internal use.');
                    $HTTP_VARS['op'] = 'new';
				}
			}
            else
			{
				$errors[] = array('error'=>'Attribute type ('.$HTTP_VARS['s_attribute_type'].') already exists.');
                $HTTP_VARS['op'] = 'new';
			}
		}
		else if($HTTP_VARS['op'] == 'update-lookups')
		{
			if(is_not_empty_array($HTTP_VARS['value']))
			{
				for($i=0; $i<count($HTTP_VARS['value']); $i++)
				{
					// If exists_ind and value is empty, this is fine.  Or as long as a display value is specified for an
					// empty value, and there is not already an empty value, then this is legal as well.
					if(strlen($HTTP_VARS['value'][$i])>0 || strlen($HTTP_VARS['display'][$i])>0 || $HTTP_VARS['exists_ind'][$i] == 'Y')
					{
						// an update or delete.
						if($HTTP_VARS['exists_ind'][$i] == 'Y')
						{
							if(is_exists_s_atribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i]))
							{
								if($HTTP_VARS['delete_ind'][$i] === 'Y')
								{
									if(!delete_s_attribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i]))
									{
										$errors[] = array('error'=>'Lookup value ('.$HTTP_VARS['value'][$i].') not deleted','detail'=>db_error());
									}
								}
								else //update
								{
                                    if(_theme_image_src($HTTP_VARS['img'][$i])==FALSE)
									 	$HTTP_VARS['img'][$i] = '';

									if(strlen($HTTP_VARS['img'][$i])==0 && $HTTP_VARS['none_img'][$i] == 'Y')
										$HTTP_VARS['img'][$i] = 'none';

									if(!update_s_attribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i], $HTTP_VARS['display'][$i], $HTTP_VARS['img'][$i], $HTTP_VARS['checked_ind'][$i], $HTTP_VARS['order_no'][$i]))
									{
										$errors[] = array('error'=>'Lookup value ('.$HTTP_VARS['value'][$i].') not updated','detail'=>db_error());
									}
								}
							}
							else
							{
								$errors[] = array('error'=>'Lookup value ('.$HTTP_VARS['value'][$i].') not found','detail'=>'');
							}
						}
						else //insert!
						{
							// Get rid of all spaces, and illegal characters.
							$HTTP_VARS['value'][$i] = preg_replace("/[\s|'|\\\\|\"]+/", "", trim(strip_tags($HTTP_VARS['value'][$i])));

							if(!is_exists_s_atribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i]))
							{
								if(_theme_image_src($HTTP_VARS['img'][$i])==FALSE)
								 	$HTTP_VARS['img'][$i] = '';

								if(strlen($HTTP_VARS['img'][$i])==0 && $HTTP_VARS['none_img'][$i] == 'Y')
									$HTTP_VARS['img'][$i] = 'none';

								// First of all we need to handle the image upload here.
								if(!insert_s_attribute_type_lookup($HTTP_VARS['s_attribute_type'], $HTTP_VARS['value'][$i], $HTTP_VARS['display'][$i], $HTTP_VARS['img'][$i], $HTTP_VARS['checked_ind'][$i], $HTTP_VARS['order_no'][$i]))
								{
									$errors[] = array('error'=>'Lookup value ('.$HTTP_VARS['value'][$i].') not inserted','detail'=>db_error());
								}
							}
							else
							{
								$errors[] = array('error'=>'Lookup value ('.$HTTP_VARS['value'][$i].') already exists','detail'=>'');
							}
						}
					}
				}
			}

            $HTTP_VARS['op'] = 'edit-lookups';
		}

		if($HTTP_VARS['op'] == 'new')
		{
            echo get_validation_javascript();
            
			echo("<script language=\"JavaScript\" type=\"text/javascript\" src=\"./admin/s_attribute_type/widgettooltips.js\"></script>");
			echo get_widget_tooltip_array();

			echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</div>");

			echo("\n<h3>New Attribute type</h3>");

            if(is_not_empty_array($errors))
				echo format_error_block($errors);

			echo("\n<table cellspacing=2 border=0>");
			echo("\n<form name=\"s_attribute_type\" action=\"$PHP_SELF\" method=\"POST\">");

			echo("\n<input type=\"hidden\" name=\"op\" value=\"insert\">");
			echo("\n<input type=\"hidden\" name=\"type\" value=\"".$HTTP_VARS['type']."\">");
			
			display_edit_form(NULL, $HTTP_VARS);
			
			if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
			{
				echo("\n<tr><td align=left nowrap>".
						format_help_block(array(array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field')))).
					"</td><td>&nbsp;</td></tr>");
			}
			
			echo("\n<tr><td colspan=\"2\" align=center>");
			if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
				echo("\n<input type=button value=\"Insert\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
			else
				echo("\n<input type=button value=\"Insert\" onclick=\"this.form.submit();\">");
			echo("\n</td></tr>");
			
			echo("\n</form>");
			echo("\n</table>");
		}
		else if($HTTP_VARS['op'] == 'edit')
		{
            echo get_validation_javascript();
			
			echo("<script language=\"JavaScript\" type=\"text/javascript\" src=\"./admin/s_attribute_type/widgettooltips.js\"></script>");
            echo get_widget_tooltip_array();
            
			echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</div>");

			echo("\n<h3>Edit Attribute type</h3>");

            if(is_not_empty_array($errors))
				echo format_error_block($errors);

			$attribute_type_r = fetch_s_attribute_type_r($HTTP_VARS['s_attribute_type']);
			if($attribute_type_r!==FALSE)
			{
				echo("\n<table cellspacing=2 border=0>");
				echo("\n<form name=\"s_attribute_type\" action=\"$PHP_SELF\" method=\"POST\">");

				echo("\n<input type=\"hidden\" name=\"op\" value=\"update\">");
				echo("\n<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">");

				display_edit_form($attribute_type_r);
				
				if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
				{
					echo("\n<tr><td align=left nowrap>".
						format_help_block(array(array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field')))).
						"</td><td>&nbsp;</td></tr>");
				}
			
				echo("\n<tr><td colspan=\"2\" align=center>");
				
				if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
					echo("\n<input type=button value=\"Update\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
				else
					echo("\n<input type=button value=\"Update\" onclick=\"this.form.submit();\">");
				echo("\n</td></tr>");

				echo("\n</form>");
				echo("\n</table>");
			}
			else
			{
				echo format_error_block('Attribute type ('.$HTTP_VARS['s_attribute_type'].') not found');
			}
		}
        else if($HTTP_VARS['op'] == 'edit-lookups')
		{
			// ################################################################
			// Do for both 'update' and 'edit'
			// ################################################################

			echo get_validation_javascript();

			echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</div>");

            echo("<script language=\"JavaScript1.2\">
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
			
            echo(get_common_javascript());
			echo get_tabs_javascript();
			
            echo("\n<h3>Edit ".$HTTP_VARS['s_attribute_type']." Attribute Type Lookups</h3>");

			if(is_not_empty_array($errors))
				echo format_error_block($errors);

			echo(display_lookup_attribute_type_form($HTTP_VARS));

			echo(format_help_block($edit_satl_form_help));
		}
		else if($HTTP_VARS['op'] == '')
		{
            if(is_not_empty_array($errors))
				echo format_error_block($errors);

			echo("[ <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new\">New Attribute Type</a> ]");
				 
			echo(get_common_javascript());
			echo get_tabs_javascript();
            echo(display_attribute_type_form($HTTP_VARS));
            
           
		}
	}
}//if(is_opendb_valid_session())
