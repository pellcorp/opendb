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
include_once("./lib/http.php");
include_once("./lib/fileutils.php");
include_once("./lib/utils.php");
include_once("./lib/parseutils.php");
include_once("./lib/datetime.php");
include_once("./lib/email.php");
include_once("./lib/status_type.php");
include_once("./lib/theme.php");

// todo - work out which scripts need input fields and which need display fields
include_once("./lib/inputfields.php");
include_once("./lib/displayfields.php");

/*
* A display mask consists of %value%, %display% and %img% mask variables, the
* first of %value% or %display% encountered will effect the order by chosen.
*/
function get_lookup_order_by($display_mask) {
	// if display mask is empty return default of 'value'
	if (strlen ( $display_mask ) == 0)
		return 'value'; //default orderby is 'value'
	else {
		$displayPos = strpos ( $display_mask, '%display%' );
		$valuePos = strpos ( $display_mask, '%value%' );
		if ($displayPos !== FALSE && ($valuePos === FALSE || $valuePos > $displayPos))
			return 'display';
		else
			return 'value';
	}
}

/**
	@param $name
	@param $lookup_results	This can be a MySQL $results reference or an PHP associative
							array.  Even if you index your array numerically, the 'value'
							and index still get set inside this function.
	@param $display_mask
			This mask can be anything.  Any %??????% will be interpreted as a database
			column name and will be selected from there as appropriate.
	@param $lookup_results	Database results mysql reference
	@param $size			Size of SELECT object.  Specify a size of 'NA' to stop the 
	 						<select ...> and </select> tags being generated.  This will allow
	 						things like empty options to be included, while still taking
	 						advantage of the generation of database/array options.
	@param $value			Checked Ind value.  If the value param is found in the current
							'value' column, that record will be SELECTED.
	@param $value_column	Specifies the value column, or 'value' as a default.
	@param $checked_ind 	Specifies the checked_ind column to select a default record, or
							'' as default, which will mean the first record will be selected
							by the browser when building the generated select.
	@param $include_ind_func
							If defined, will call the function with the current lookup array
 						    as argument.  If the function returns TRUE, the record will be
							included, otherwise it will be skipped.
	
	However the $lookup_results DO have to include a 'value' column for this
	process to work, as this will be used for the value of each select option.

	You can however set the $value_column to a value indicating a column name to
	be used instead of 'value'
*/
function custom_select($name, $lookup_results, $display_mask, $size = 1, $value = NULL, $value_column = 'value', $include_ind_func = NULL, $checked_ind = '', $onchange_event = '', $disabled = FALSE, $id = NULL) {
	// allows function to be called with an array of args, instead of individual arguments.
	if (is_array ( $name )) {
		extract ( $name );
	}
	
	if ($size !== 'NA') {
		if (is_numeric ( $size ) && $size > 1)
			$var = "\n<select " . ($id != NULL ? "id=\"$id\"" : "") . " name=\"" . $name . "[]\" size=\"$size\" onchange=\"$onchange_event\"" . ($disabled ? ' DISABLED' : '') . " MULTIPLE>";
		else
			$var = "\n<select " . ($id != NULL ? "id=\"$id\"" : "") . " name=\"$name\" onchange=\"$onchange_event\"" . ($disabled ? ' DISABLED' : '') . ">";
	} else {
		$var = '';
	}
	
	$lookup_results = fetch_results_array ( $lookup_results );
	reset ( $lookup_results );
	$empty_display_mask = expand_display_mask ( $display_mask, NULL, '%' );
	
	$value_found = FALSE;
	foreach ( $lookup_results as $lookup_r_key => $lookup_r_value ) {
		$lookup_r = ["key" => $lookup_r_key, "value" => $lookup_r_value];
		// Check if this record should be included in list of values.
		if (! function_exists ( $include_ind_func ) || $include_ind_func ( $lookup_r )) {
			$lookup_value = get_array_variable_value ( $lookup_r, $value_column );
			
			$display = expand_display_mask ( $display_mask, $lookup_r, '%' );
			
			// if all variables were replaced with nothing, then assume empty option
			if (strlen ( strval ( $lookup_value ) ) == 0 && $display == $empty_display_mask) {			//thawn: added strval() to fix variable type mismatch warning in php5.3
				$display = '';
			}
			
			if (is_array ( $value )) {
				if (in_array ( $lookup_value, $value ) !== FALSE)
					$var .= "\n<option value=\"" . $lookup_value . "\" SELECTED>$display";
				else
					$var .= "\n<option value=\"" . $lookup_value . "\">$display";
			} else {
				if (!$value_found && $value == NULL && ( $checked_ind == "" || $lookup_r [$checked_ind] == 'Y')) {
					$var .= "\n<option value=\"" . $lookup_value . "\" SELECTED>$display";
					$value_found = TRUE;
				} else {
					if (strcasecmp ( trim ( $value ), strval ( $lookup_value ) ) === 0) { 					//thawn: added strval() to fix variable type mismatch warning in php5.3
						$value_found = TRUE;
						$var .= "\n<option value=\"" . $lookup_value . "\" SELECTED>$display";
					} else {
						$var .= "\n<option value=\"" . $lookup_value . "\">$display";
					}
				}
			}
		}
	}
	
	if ($size !== 'NA') {
		$var .= "\n</select>";
	}
	return $var;
}

function format_data($field, $field_mask = NULL) {
	if (strlen ( $field_mask ) > 0 && strpos ( $field_mask, "%field%" ) !== FALSE)
		$field = str_replace ( "%field%", $field, $field_mask );
	
	if (strlen ( $field ) == 0)
		$field = "&nbsp;";
	
	return "<td class=\"data\">$field</td>";
}

function format_prompt($prompt, $prompt_mask = NULL) {
	if (strlen ( $prompt_mask ) > 0 && strpos ( $prompt_mask, "%prompt%" ) !== FALSE)
		$prompt = str_replace ( "%prompt%", $prompt, $prompt_mask );
	
	return "<th class=\"prompt\" scope=\"row\">$prompt:</th>";
}

function format_field($prompt, $field, $prompt_mask = NULL, $field_mask = NULL) {
	return "\n<tr>" . format_prompt ( $prompt, $prompt_mask ) . format_data ( $field, $field_mask ) . "</tr>";
}

function format_item_data_field($attribute_type_r, $field, $prompt_mask = NULL) {
	$prompt = $attribute_type_r ['prompt'];
	if (strlen ( $prompt_mask ) > 0 && strpos ( $prompt_mask, "%prompt%" ) !== FALSE)
		$prompt = str_replace ( "%prompt%", $prompt, $prompt_mask );
	
	if ($attribute_type_r ['compulsory_ind'] == 'Y') {
		if (strlen ( $prompt_mask ) == 0)
			$prompt_mask = '%prompt%';
		
		$prompt .= theme_image ( "compulsory.gif", get_opendb_lang_var ( 'compulsory_field' ), 'compulsory' );
	}
	
	$fieldid = strtolower ( str_replace ( '_', '-', $attribute_type_r ['s_attribute_type'] ) . '-' . $attribute_type_r ['order_no'] );
	$fieldclass = strtolower ( str_replace ( '_', NULL, $attribute_type_r ['s_attribute_type'] ) );
	
	return "\n<tr id=\"$fieldid\">" . "<th class=\"prompt\" scope=\"row\">$prompt:</th>" . "<td class=\"data $fieldclass\">" . ifempty ( $field, '&nbsp;' ) . "</td>" . "</tr>";
}

function get_field_name($s_attribute_type, $order_no = NULL) {
	if (is_numeric ( $order_no ))
		return strtolower ( $s_attribute_type ) . "_" . $order_no;
	else
		return strtolower ( $s_attribute_type );
}

function get_op_confirm_form($PHP_SELF, $confirm_message, $HTTP_VARS) {
	$formContents = "\n<form class=\"confirmForm\" action=\"$PHP_SELF\" method=\"POST\">";
	
	$formContents .= "<p>" . $confirm_message . "</p>" . get_url_fields ( $HTTP_VARS, NULL, array (
			'confirmed' ) ) . 	// Pass all http variables
"<fieldset>" . "<label for=\"confirm_yes\">" . get_opendb_lang_var ( 'yes' ) . "</label>" . "<input type=\"radio\" class=\"radio\" name=\"confirmed\" id=\"confirm_yes\" value=\"true\">" . "<label for=\"confirm_no\">" . get_opendb_lang_var ( 'no' ) . "</label>" . "<input type=\"radio\" class=\"radio\" name=\"confirmed\" id=\"confirm_no\" value=\"false\" CHECKED>" . "</fieldset>" . "<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var ( 'submit' ) . "\">" . "</form>\n";
	
	return $formContents;
}

function format_footer_links($links_rs) {
	$field = NULL;
	
	if (is_array ( $links_rs ) && isset ( $links_rs ['url'] ))
		$footer_links_rs [] = $links_rs;
	else
		$footer_links_rs = & $links_rs;
	
	if (is_array ( $footer_links_rs )) {
		$field = "<ul class=\"footer-links\">";
		
		$totalCount = count ( $footer_links_rs );
		for($i = 0; $i < $totalCount; $i ++) {
			$footer_links_r = $footer_links_rs [$i];
			
			$field .= "<li class=\"";
			if ($i == 0) {
				$field .= "first ";
			}
			
			if ($i + 1 == $totalCount) {
				$field .= "last";
			}
			
			$field .= "\"><a";
			if (strlen ( $footer_links_r ['url'] ?? '' ) > 0) {
				$field .= " href=\"" . $footer_links_r ['url'] . "\"";
			}
			
			if (strlen ( $footer_links_r ['onclick'] ?? '') > 0) {
				$field .= " onclick=\"" . $footer_links_r ['onclick'] . "\" ";
			}
			
			if (starts_with ( $footer_links_r ['target'] ?? '', 'popup' )) {
				$spec = prc_function_spec ( $footer_links_r ['target'] );
				
				if (! is_array ( $spec ['args'] )) {
					$spec ['args'] [0] = '800';
					$spec ['args'] [1] = '600';
				}
				
				$field .= " onclick=\"popup('" . $footer_links_r ['url'] . "','" . $spec ['args'] [0] . "','" . $spec ['args'] [1] . "'); return false;\">";
			} else {
				$field .= (strlen ( $footer_links_r ['target'] ?? "") > 0 ? "target=\"" . $footer_links_r ['target'] . "\"" : "") . ">";
			}
			
			if (strlen ( $footer_links_r ['img'] ?? '' ) > 0) {
				$field .= '<img src="' . $footer_links_r ['img'] . '" title="' . $footer_links_r ['text'] . '">';
			}
			
			// don't allow wrapping of link
			$field .= $footer_links_r ['text'] . "</a>";
			
			$field .= "</li>";
		}
		
		$field .= "</ul>";
	}
	return $field;
}

function _format_help_entry($help_entry_r) {
	if (is_array ( $help_entry_r )) {
		$entry = '';
		if (isset ( $help_entry_r ['img'] ))
			$entry .= theme_image ( $help_entry_r ['img'], $help_entry_r ['text'] ) . " ";
		$entry .= $help_entry_r ['text'];
		
		return $entry;
	} else {
		return $help_entry_r;
	}
}

function _format_help_list($help_entry) {
	$field = '';
	
	if (is_array ( $help_entry ) && ! isset ( $help_entry ['text'] )) {
		foreach ( $help_entry as $entry ) {
			$field .= _format_help_list ( $entry );
		}
	} else {
		$field .= "\n<li>" . _format_help_entry ( $help_entry ) . "</li>";
	}
	
	return $field;
}

function format_help_block($help_entries_rs) {
	if (! is_array ( $help_entries_rs ) && strlen ( $help_entries_rs ) > 0)
		$entries [] = array (
				array (
						'text' => $help_entries_rs ) );
	else if (is_array ( $help_entries_rs ) && isset ( $help_entries_rs ['text'] ))
		$entries [] = array (
				$help_entries_rs );
	else if (is_array ( $help_entries_rs ))
		$entries [] = & $help_entries_rs;
	else
		$entries = array();
	
	if (is_array ( $entries )) {
		return "\n<ul class=\"help\">" . _format_help_list ( $entries ) . "</ul>\n";
	} else {
		return NULL;
	}
}

function format_action_links($action_links_rs) {
	$field = '';
	$first = TRUE;
	if (!isset($action_links_rs))
		return NULL;

	foreach ($action_links_rs as $action_link_r) {
		if (strlen ( $action_link_r ['img'] ) > 0)
			$action_image = theme_image ( 'action_' . $action_link_r ['img'], $action_link_r ['text'], "action" );
		else
			$action_image = FALSE;

		$field .= "<li class=\"" . ($first ? 'first' : '') . "\"><a href=\"" . $action_link_r ['url'] . "\">";
		if ($first)
			$first = FALSE;
			
			// Either strlen($action_link_r['img'])==0 or theme specific theme_image does not want images for actions, and
			// returned NULL as a result.
		if ($action_image !== FALSE && strlen ( $action_image ) > 0) {
			$field .= $action_image;
		} else {
			$field .= $action_link_r ['text'];
		}
		
		$field .= '</a></li>';
	}
	
	if (strlen ( $field ) > 0) {
		return "<ul class=\"action-links\">" . $field . "</ul>";
	} else {
		return NULL;
	}
}

function format_checkbox_action_links($cbname, $not_checked_message, $action_links_rs) {
	$field = "<ul class=\"checkbox-action-links\">";
	
	$first = TRUE;
	
	@reset ( $action_links_rs );
	foreach ($action_links_rs as $action_links_r) {
		$field .= "<li class=\"" . ($first ? 'first' : '') . "\">";
		
		if ($action_links_r ['checked'] !== FALSE) {
			$field .= "<a href=\"#\" onclick=\"if(isChecked(document.forms['" . $cbname . "'], '" . $cbname . "[]')){doFormSubmit(document.forms['" . $cbname . "'], '" . $action_links_r ['action'] . "', '" . $action_links_r ['op'] . "');}else{alert('" . $not_checked_message . "');} return false;\">" . $action_links_r ['link'] . "</a>";
		} else {
			$field .= "<a href=\"" . $action_links_r ['action'] . "?op=" . $action_links_r ['op'] . "\">" . $action_links_r ['link'] . "</a>";
		}
		$field .= "</li>";
		
		if ($first)
			$first = FALSE;
	}
	
	$field .= "</ul>";
	
	return $field;
}

function format_error_block($errors, $err_type = 'error') {
	switch ($err_type) {
		case 'error' :
			$class = 'error';
			$smclass = 'smerror';
			break;
		
		case 'smerror' :
			$class = 'smerror';
			$smclass = 'smerror';
			break;
		
		case 'warning' : // If it becomes necessary, new CSS style classes will be introduced.
		case 'information' :
		default :
			$class = 'smsuccess';
			$smclass = 'footer';
	}
	
	if (! is_array ( $errors )) {
		if (strlen ( trim ( $errors ) ) == 0)
			return NULL;
		else
			$error_rs [] = array (
					'error' => $errors,
					'detail' => '' );
	} else if (isset ( $errors ['error'] ))
		$error_rs [] = $errors;
	else
		$error_rs = $errors;
	
	$error_entries = NULL;
	foreach ($error_rs as $error) {
		if (is_not_empty_array ( $error )) {
			$error_entry = $error ['error'];
			
			if (! is_array ( $error ['detail'] ) && strlen ( $error ['detail'] ) > 0)
				$detail_rs [] = $error ['detail'];
			else if (is_array ( $error ['detail'] ))
				$detail_rs = $error ['detail'];
			
			if (is_not_empty_array ( $detail_rs ?? "")) {
				$details = "";
				foreach ( $detail_rs as $detail ) {
					$details .= "\n<li class=\"$smclass\">" . $detail . "</li>";
				}
				
				if (strlen ( $details ) > 0)
					$error_entry .= "\n<ul>" . $details . "</ul>";
			}
		} else {
			$error_entry = $error;
		}
		
		$error_entries [] = $error_entry;
	}
	
	if (count ( $error_entries ) > 1) {
		$error_block = "\n<ul>";
		foreach ( $error_entries as $error_entry ) {
			$error_block .= "\n<li class=\"$class\">$error_entry</li>";
		}
		$error_block .= "</ul>";
		
		return $error_block;
	} else if (count ( $error_entries ) == 1) {
		return "\n<p class=\"$class\">" . $error_entries [0] . "</p>";
	} else {
		return NULL;
	}
}

function get_item_image($s_item_type, $item_id = NULL) {
	if (strlen ( $s_item_type ) > 0 || ($s_item_type = fetch_item_type ( $item_id ))) {
		$item_type_r = fetch_item_type_r ( $s_item_type );
		if (is_array ( $item_type_r )) {
			// default
			$imagetext = $s_item_type;
			
			// Get image block.
			if (strlen ( $item_type_r ['image'] ) > 0) {
				if (strlen ( $item_type_r ['description'] ) > 0)
					$title_text = htmlspecialchars ( $item_type_r ['description'] );
				else
					$title_text = NULL;
				
				$imagetext = theme_image ( $item_type_r ['image'], $title_text, 's_item_type' );
			}
			
			return $imagetext;
		}
	}
	
	return FALSE;
}
?>
