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
include_once("./lib/item_attribute.php");
include_once("./lib/item_type.php");
include_once("./lib/item.php");
include_once("./lib/http.php");
include_once("./lib/fileutils.php");
include_once("./lib/utils.php");
include_once("./lib/parseutils.php");
include_once("./lib/datetime.php");
include_once("./lib/email.php");
include_once("./lib/status_type.php");
include_once("./lib/theme.php");
include_once("./lib/file_type.php");
include_once("./lib/widgets.php");
include_once("./lib/javascript.php");

/**
stub for non item specific functionality
*/
function get_input_field($fieldname, $s_attribute_type, $prompt, $input_type, $compulsory_ind = 'N', $value = NULL, $dowrap = TRUE, $prompt_mask = NULL, $onchange_event = NULL, $disabled = FALSE) {
	$input_type_def = prc_function_spec ( $input_type );
	
	return get_item_input_field ( $fieldname, array ( // $item_attribute_type_r
			's_attribute_type' => $s_attribute_type,
			'order_no' => NULL,
			'prompt' => $prompt,
			'input_type' => $input_type_def ['type'],
			'input_type_arg1' => $input_type_def ['args'] [0] ?? "",
			'input_type_arg2' => $input_type_def ['args'] [1] ?? "",
			'input_type_arg3' => $input_type_def ['args'] [2] ?? "",
			'input_type_arg4' => $input_type_def ['args'] [3] ?? "",
			'input_type_arg5' => $input_type_def ['args'] [4] ?? "",
			'compulsory_ind' => $compulsory_ind ), NULL, 	// $item_r
$value, $dowrap, $prompt_mask, $onchange_event, $disabled );
}

function get_item_input_field($fieldname, $item_attribute_type_r, $item_r, $value = NULL, $dowrap = TRUE, $prompt_mask = NULL, $onchange_event = NULL, $disabled = FALSE) {
	if (is_array ( $item_attribute_type_r )) {
		$s_attribute_type = $item_attribute_type_r ['s_attribute_type'];
		$order_no = $item_attribute_type_r ['order_no'];
		$prompt = $item_attribute_type_r ['prompt'];
		$input_type = $item_attribute_type_r ['input_type'];
		$compulsory_ind = $item_attribute_type_r ['compulsory_ind'];
		
		$widget ['type'] = $item_attribute_type_r ['input_type'];
		$widget ['args'] [0] = $item_attribute_type_r ['input_type_arg1'];
		$widget ['args'] [1] = $item_attribute_type_r ['input_type_arg2'];
		$widget ['args'] [2] = $item_attribute_type_r ['input_type_arg3'];
		$widget ['args'] [3] = $item_attribute_type_r ['input_type_arg4'];
		$widget ['args'] [4] = $item_attribute_type_r ['input_type_arg5'];
	}
	
	if (($item_attribute_type_r['multi_attribute_ind'] ?? '') == 'Y') {
		$multi_value = TRUE;
		
		if (! is_array ( $value )) {
			$old_value = ifempty ( $value, "" );
			unset ( $value );
			$value [] = $old_value;
		}
	} else {
		$multi_value = FALSE;
		
		// an array will be a lookup value
		if (! is_array ( $value )) {
			// Escape all html entities so they are displayed correctly!
			if (strlen ( $value ) > 0) {
				$value = htmlspecialchars ( $value );
			}
		}
	}
	
	$field = NULL;
	$field_mask = NULL;
	
	// Now we have to work out how to parse the input_type
	if ($item_attribute_type_r ['input_type'] == 'hidden') {
		return hidden_field ( $fieldname, $value );
	} else if ($item_attribute_type_r ['input_type'] == 'readonly') {
		$field = readonly_field ( $fieldname, $value );
	} else if ($item_attribute_type_r ['input_type'] == 'textarea' || $item_attribute_type_r ['input_type'] == 'htmlarea') { 	// arg[0] = rows, arg[1] = cols, arg[2] = length
		$field = textarea_field ( $fieldname, $prompt, $widget ['args'] ['0'], $widget ['args'] ['1'], $widget ['args'] ['2'], $compulsory_ind, $value, $onchange_event, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'text') {	// arg[0] = length of field, arg[1] = maxlength of field
		$field = text_field ( $fieldname, $prompt, $widget ['args'] ['0'], $widget ['args'] ['1'], $compulsory_ind, $value, $onchange_event, $disabled, $multi_value );
	} else if ($item_attribute_type_r ['input_type'] == 'password') { 	// arg[0] = length of field, arg[1] = maxlength of field
		$field = password_field ( $fieldname, $prompt, $widget ['args'] ['0'], $widget ['args'] ['1'], $compulsory_ind, $value, $onchange_event, $disabled, $multi_value );
	} else if ($item_attribute_type_r ['input_type'] == 'email') {	// arg[0] = length of field, arg[1] = maxlength of field
		$field = email_field ( $fieldname, $prompt, $widget ['args'] ['0'], $widget ['args'] ['1'], $compulsory_ind, $value, $onchange_event, $disabled, $multi_value );
	} else if ($item_attribute_type_r ['input_type'] == 'filtered') { 	// arg[0] = length of field, arg[1] = maxlength of field, arg[2] = legalChars
		$field = filtered_field ( $fieldname, $prompt, $widget ['args'] ['0'], $widget ['args'] ['1'], $widget ['args'] ['2'], $compulsory_ind, $value, $onchange_event, $disabled, $multi_value );
	} else if ($item_attribute_type_r ['input_type'] == 'datetime') {	// arg[0] = datetime mask, arg[1] = auto_datetime
		$field = datetime_field ( $fieldname, $prompt, ifempty ( $widget ['args'] ['0'], 'DD/MM/YYYY' ), $widget ['args'] ['1'], $compulsory_ind, $value, $onchange_event, $disabled, $multi_value );
	} else if ($item_attribute_type_r ['input_type'] == 'number') { 	// arg[0] = length of field, arg[0] = maxlength of field
		$field = number_field ( $fieldname, $prompt, $widget ['args'] ['0'], $widget ['args'] ['0'], $compulsory_ind, $value, $onchange_event, $disabled, $multi_value );
	} else if ($item_attribute_type_r ['input_type'] == 'simple_checkbox') {	// arg[0] = checked
		$field = checkbox_field ( $fieldname, $prompt, strcasecmp ( trim ( $widget ['args'] ['0'] ), 'CHECKED' ) === 0, $value, $onchange_event, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'checkbox') {	// arg[0] = checked, arg[1] = unchecked
		$field = enhanced_checkbox_field ( $fieldname, $prompt, $widget ['args'] ['0'], $widget ['args'] ['1'], $value, $onchange_event, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'checkbox_grid') {
		$lookup_results = fetch_attribute_type_lookup_rs ( $s_attribute_type, 'order_no, ' . get_lookup_order_by ( $widget ['args'] ['0'] ) . ' ASC' );
		if ($lookup_results) //arg[0] = display_mask, arg[1] = orientation
			$field = checkbox_grid ( $fieldname, $lookup_results, $widget ['args'] ['0'], $widget ['args'] ['1'], $value, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'radio_grid') {
		$lookup_results = fetch_attribute_type_lookup_rs ( $s_attribute_type, 'order_no, ' . get_lookup_order_by ( $widget ['args'] ['0'] ) . ' ASC' );
		if ($lookup_results) //arg[0] = display_mask, arg[1] = orientation
			$field = radio_grid ( $fieldname, $lookup_results, $widget ['args'] ['0'], $widget ['args'] ['1'], $value, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'value_radio_grid') {	//arg[0] = "comma delimited list of values"
		$field = value_radio_grid ( $fieldname, explode ( ',', $widget ['args'] ['0'] ), $value, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'single_select') {
		$lookup_results = fetch_attribute_type_lookup_rs ( $s_attribute_type, 'order_no, ' . get_lookup_order_by ( $widget ['args'] ['0'] ) . ' ASC' );
		if ($lookup_results) //arg[0] = display mask, arg[1] = max value length
			$field = single_select ( $fieldname, $lookup_results, $widget ['args'] ['0'], $widget ['args'] ['1'], $value, $onchange_event, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'multi_select') {
		$lookup_results = fetch_attribute_type_lookup_rs ( $s_attribute_type, 'order_no, ' . get_lookup_order_by ( $widget ['args'] ['0'] ) . ' ASC' );
		if ($lookup_results) //arg[0] = display mask, arg[1] = max value length, arg[2] = select box number of visible rows
			$field = multi_select ( $fieldname, $lookup_results, $widget ['args'] ['0'], $widget ['args'] ['1'], $widget ['args'] ['2'], $value, $onchange_event, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'value_select') { 	//arg[0] = "comma delimited list of values"; arg[1] = number of visible rows (Defaults to single select
		$field = value_select ( $fieldname, explode ( ',', $widget ['args'] ['0'] ), $widget ['args'] ['1'], $value, $onchange_event, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'review_options') {	//arg[1] = display_mask, arg[1] = orientation
		$lookup_results = fetch_attribute_type_lookup_rs ( $s_attribute_type, 'value DESC' ); //We want the rows highest value first.
		if ($lookup_results)
			$field = review_options ( $fieldname, $lookup_results, $widget ['args'] ['0'], $widget ['args'] ['1'], $value, $disabled );
	} else if ($item_attribute_type_r ['input_type'] == 'url') {	//arg[0] = length of field, arg[1] = maxlength of field, arg[2] = extensions
		$field = url ( $fieldname, $item_r, $item_attribute_type_r, $prompt, $widget ['args'] ['0'], $widget ['args'] ['1'], $widget ['args'] ['2'], $value, $onchange_event, $disabled, $multi_value );
	} else {
		$field = ">>> ERROR (input_type = $input_type) <<<";
	}
	
	if ($dowrap)
		return format_item_data_field ( $item_attribute_type_r, $field, $prompt_mask );
	else
		return $field;
}

function validate_input_field($prompt, $input_type, $compulsory_ind = 'N', $value, &$errors) {
	$input_type_def = prc_function_spec ( $input_type );
	
	return validate_item_input_field ( array ( // $item_attribute_type_r
			'prompt' => $prompt,
			'input_type' => $input_type_def ['type'],
			'input_type_arg1' => $input_type_def ['args'] [0],
			'input_type_arg2' => $input_type_def ['args'] [1],
			'input_type_arg3' => $input_type_def ['args'] [2],
			'input_type_arg4' => $input_type_def ['args'] [3],
			'input_type_arg5' => $input_type_def ['args'] [4],
			'compulsory_ind' => $compulsory_ind ), $value, $errors );
}

/*
 * Validate all input fields and return error(s) to caller, if failed.
*
* This is a basic attempt to prevent users bypassing javascript validation, and causing integrity
* problems in the database.  In future releases this function will be further augmented for other
* widget types.
*/
function validate_item_input_field($item_attribute_type_r, $value, &$errors) {
	// cater for multivalue fields here!
	if (! is_array ( $value ) && strlen ( trim ( $value ) ) > 0) {
		$tmpval = trim ( $value );
		unset ( $value );
		
		$value [] = $tmpval;
	}
	
	if ($item_attribute_type_r ['compulsory_ind'] == 'Y') {
		// at this point, $value will always be an array because of the block above.
		if (is_empty_or_not_array ( $value )) {
			$error = array (
					'error' => get_opendb_lang_var ( 'prompt_must_be_specified', 'prompt', $item_attribute_type_r ['prompt'] ),
					'detail' => '' );
			if (is_array ( $errors ))
				$errors [] = $error;
			else
				$errors = $error;
			return FALSE;
		}
	}

	if (is_not_empty_array ( $value ) && $item_attribute_type_r ['lookup_attribute_ind'] != 'Y') {
		switch ($item_attribute_type_r ['input_type']) {
			case 'hidden' :
			case 'readonly' :
			case 'textarea' :
			case 'htmlarea' :
			case 'text' :
			case 'password' :
			case 'simple_checkbox' :
			case 'checkbox' :
			case 'check_boxes' : // deprecated
			case 'vertical_check_boxes' : // deprecated
			case 'horizontal_check_boxes' : // deprecated
			case 'radio_group' : // deprecated
			case 'vertical_radio_group' : // deprecated
			case 'horizontal_radio_group' : // deprecated
			case 'radio_grid' :
			case 'value_radio_grid' :
			case 'checkbox_grid' :
			case 'single_select' :
			case 'multi_select' :
			case 'value_select' :
				return TRUE;
				break;
			
			case 'url' :
				
				// will be an array of content groups
				if (strlen ( $item_attribute_type_r ['input_type_arg3'] ) > 0) {
					$content_group_r = prc_args ( $item_attribute_type_r ['input_type_arg3'] );
					$extensions_r = fetch_file_type_extensions_r ( $content_group_r );
					
					// it might just be a list of extensions
					if (! is_not_empty_array ( $extensions_r )) {
						$extensions_r = $content_group_r;
					}
					
					for($i = 0; $i < count ( $value ); $i ++) {
						if (! in_array ( strtolower ( get_file_ext ( $value [$i] ) ), $extensions_r )) {
							$error = array (
									'error' => get_opendb_lang_var ( 'url_is_not_valid', array (
											'prompt' => $item_attribute_type_r ['prompt'],
											'extensions' => implode ( ', ', $extensions_r ) ) ),
									'detail' => '' );
							if (is_array ( $errors ))
								$errors [] = $error;
							else
								$errors = $error;
							return FALSE;
						}
					}
				}
				
				//else
				return TRUE;
			
			case 'email' :
				for($i = 0; $i < count ( $value ); $i ++) {
					if (! is_valid_email_addr ( $value [$i] ) && ($item_attribute_type_r ['compulsory_ind'] == 'Y' && strlen ( trim ( $value [$i] ) ) > 0)) {
						$error = array (
								'error' => get_opendb_lang_var ( 'email_is_not_valid', 'prompt', $item_attribute_type_r ['prompt'] ),
								'detail' => '' );
						if (is_array ( $errors ))
							$errors [] = $error;
						else
							$errors = $error;
						return FALSE;
					}
				}
				
				//else
				return TRUE;
			
			case 'datetime' :
				for($i = 0; $i < count ( $value ); $i ++) {
					if ($item_attribute_type_r ['compulsory_ind'] == 'Y' || strlen ( trim ( $value [$i] ) ) > 0) {
						$timestamp = get_timestamp_for_datetime ( $value [$i], $item_attribute_type_r ['input_type_arg1'] );
						if ($timestamp === FALSE) {
							//else perhaps it is a timestamp value already.
							$timestamp = get_timestamp_for_datetime ( $value [$i], 'YYYYMMDDHH24MISS' );
							if ($timestamp === FALSE) {
								$error = array (
										'error' => get_opendb_lang_var ( 'datetime_is_not_valid', array (
												'prompt' => $item_attribute_type_r ['prompt'],
												'format_mask' => $item_attribute_type_r ['input_type_arg1'] ) ),
										'detail' => '' );
								if (is_array ( $errors ))
									$errors [] = $error;
								else
									$errors = $error;
								return FALSE;
							}
						}
					}
				}
				
				//else
				return TRUE;
			
			case 'filtered' :
				$legalChars = expand_chars_exp ( $item_attribute_type_r ['input_type_arg3'] );
				
				for($i = 0; $i < count ( $value ); $i ++) {
					$value [$i] = trim ( $value [$i] );
					
					for($j = 0; $j < strlen ( $value [$i] ); $j ++) {
						if (strstr ( $legalChars, substr ( $value [$i], $j, 1 ) ) === FALSE) {
							$error = array (
									'error' => get_opendb_lang_var ( 'prompt_must_be_format', array (
											'prompt' => $item_attribute_type_r ['prompt'],
											'format' => '[' . $item_attribute_type_r ['input_type_arg3'] . ']' ) ),
									'detail' => '' );
							if (is_array ( $errors ))
								$errors [] = $error;
							else
								$errors = $error;
							return FALSE;
						}
					}
				}
				
				return TRUE;
			
			case 'number' :
				for($i = 0; $i < count ( $value ); $i ++) {
					if (! is_numeric ( $value [$i] ) && ($item_attribute_type_r ['compulsory_ind'] == 'Y' && strlen ( trim ( $value [$i] ) ) > 0)) {
						$error = array (
								'error' => get_opendb_lang_var ( 'prompt_must_be_format', array (
										'prompt' => $item_attribute_type_r ['prompt'],
										'format' => '[0-9]' ) ),
								'detail' => '' );
						if (is_array ( $errors ))
							$errors [] = $error;
						else
							$errors = $error;
						return FALSE;
					}
				}
				
				return TRUE;
			
			default :
				return TRUE;
				break;
		}
	} else {
		return TRUE;
	}
}

function filter_input_field($input_type, $value) {
	$input_type_def = prc_function_spec ( $input_type );
	
	return filter_item_input_field ( array ( // $item_attribute_type_r
			'input_type' => $input_type_def ['type'],
			'input_type_arg1' => $input_type_def ['args'] [0],
			'input_type_arg2' => $input_type_def ['args'] [1],
			'input_type_arg3' => $input_type_def ['args'] [2],
			'input_type_arg4' => $input_type_def ['args'] [3],
			'input_type_arg5' => $input_type_def ['args'] [4] ), $value );
}

function filter_item_input_field($item_attribute_type_r, $value) {
	// FALSE is not understood as a value, but it means it is not found, so
	// set to NULL which is pretty much the same thing.
	if ($value === FALSE) {
		return NULL;
	}
	
	if (! is_array ( $value )) {
		$tmpval = trim ( $value );
		unset ( $value );
		
		if (strlen ( $tmpval ) > 0) {
			// only support text type for now
			if ($item_attribute_type_r ['input_type'] == 'text' && $item_attribute_type_r ['multi_attribute_ind'] == 'Y') {
				$value = explode ( "\n", replace_newlines ( $tmpval ) );
			} else {
				$value [] = $tmpval;
			}
		} else {
			return NULL;
		}
	}
	
	for($i = 0; $i < count ( $value ); $i ++) {
		$value [$i] = trim ( replace_newlines ( $value [$i] ) );
		
		if ($item_attribute_type_r ['lookup_attribute_ind'] != 'Y' && strlen ( $value [$i] ) > 0) {
			// Now we have to work out how to parse the input_type
			switch ($item_attribute_type_r ['input_type']) {
				case 'hidden' :
				case 'readonly' :
				case 'text' :
				case 'password' :
				case 'textarea' :
					$value [$i] = strip_tags ( $value [$i] );
					break;
				
				case 'htmlarea' :
					$value [$i] = strip_tags ( $value [$i], '<' . implode ( '><', get_opendb_config_var ( 'widgets', 'legal_html_tags' ) ) . '>' );
					break;
				
				case 'check_boxes' : // deprecated
				case 'vertical_check_boxes' : // deprecated
				case 'horizontal_check_boxes' : // deprecated
				case 'radio_group' : // deprecated
				case 'vertical_radio_group' : // deprecated
				case 'horizontal_radio_group' : // deprecated
				case 'simple_checkbox' :
				case 'checkbox' :
				case 'radio_grid' :
				case 'checkbox_grid' :
				case 'single_select' :
				case 'multi_select' :
				case 'value_radio_grid' :
				case 'value_select' :
					// do nothing
					break;
				
				case 'url' :
					// do nothing
					break;
				
				case 'email' :
					// do nothing
					break;
				
				case 'datetime' :
					$components = get_timestamp_components_for_datetime ( $value [$i], $item_attribute_type_r ['input_type_arg1'] );
					if ($components !== FALSE) {
						// This is the 'YYYYMMDDHH24MISS' mask.
						$value [$i] = str_pad ( $components ['year'], 4, '0', STR_PAD_LEFT ) . str_pad ( $components ['month'], 2, '0', STR_PAD_LEFT ) . str_pad ( $components ['day'], 2, '0', STR_PAD_LEFT ) . str_pad ( $components ['hour'], 2, '0', STR_PAD_LEFT ) . str_pad ( $components ['minute'], 2, '0', STR_PAD_LEFT ) . str_pad ( $components ['second'], 2, '0', STR_PAD_LEFT );
					}
					break;
				
				case 'number' :
					$value [$i] = remove_illegal_chars ( $value [$i], expand_chars_exp ( '0-9' ) );
					break;
				
				case 'filtered' :
					$value [$i] = remove_illegal_chars ( $value [$i], expand_chars_exp ( $item_attribute_type_r ['input_type_arg3'] ) );
					break;
				default :
					// do nothing
					break;
			}
		}
	}
	
	if ($item_attribute_type_r ['lookup_attribute_ind'] == 'Y' || $item_attribute_type_r ['multi_attribute_ind'] == 'Y')
		return $value;
	else
		return $value [0];
}

function compulsory_ind_check($prompt, $compulsory_ind) {
	if ($compulsory_ind == 'Y')
		return "if(this.value.length==0){alert('" . get_opendb_lang_var ( 'prompt_must_be_specified', 'prompt', $prompt ) . "'); this.focus(); return false;} ";
	else
		return false;
}

function hidden_field($name, $value) {
	return "\n<input type=\"hidden\" name=\"$name\" value=\"$value\">";
}

function readonly_field($name, $value) {
	return $value . "\n<input type=\"hidden\" name=\"$name\" value=\"$value\">";
}

/**
 * Generic function to be used by all 'text' fields
 */
function multivalue_text_field($class, $name, $size, $maxlength, $onchange, $value) {
	$buffer = "\n<div class=\"multiValue\">";
	$buffer .= "<ul class=\"multiValue\" id=\"${name}-multi_value_list\">";
	for($i = 0; $i < count ( $value ); $i ++) {
		$buffer .= "\n<li><input type=\"text\" class=\"text\" name=\"" . $name . "[]\" $onchange size=\"" . $size . "\" " . (is_numeric ( $maxlength ) ? "maxlength=\"" . $maxlength . "\"" : "") . " value=\"" . $value [$i] . "\"></li>";
	}
	$buffer .= "</ul>";
	$buffer .= "<a href=\"#\" onclick=\"addInputField('${name}-multi_value_list', '$name', '$size'" . (is_numeric ( $maxlength ) ? ", '$maxlength'" : "") . "); return false;\">" . get_opendb_lang_var ( 'add' ) . "</a>";
	$buffer .= "</div>";
	return $buffer;
}

function singlevalue_text_field($class, $name, $size, $maxlength, $onchange, $value, $disabled = FALSE) {
	return "\n<input type=\"text\" class=\"text\" name=\"" . $name . "\" $onchange size=\"" . $size . "\" " . (is_numeric ( $maxlength ) ? "maxlength=\"" . $maxlength . "\"" : "") . " value=\"" . $value . "\"" . ($disabled ? ' DISABLED' : '') . ">";
}

function text_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event = NULL, $disabled = FALSE, $multi_value = FALSE) {
	// Default size.
	$size = $length;
	if (! is_numeric ( $size ) || $size <= 0)
		$size = 50;
	
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onchange = "onchange=\"" . compulsory_ind_check ( $prompt, $compulsory_ind ) . " $onchange_event return true;\"";
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	if ($multi_value) {
		return multivalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value );
	} else {
		return singlevalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value, $disabled );
	}
}

function password_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event = NULL, $disabled = FALSE, $multi_value = FALSE) {
	// Default size.
	$size = $length;
	if (! is_numeric ( $size ) || $size <= 0)
		$size = 50;
	
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onchange = "onchange=\"" . compulsory_ind_check ( $prompt, $compulsory_ind ) . " $onchange_event return true;\"";
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	if ($multi_value) {
		return multivalue_text_field ( 'password', $name, $size, $maxlength, $onchange, $value );
	} else {
		return singlevalue_text_field ( 'password', $name, $size, $maxlength, $onchange, $value, $disabled );
	}
}

function email_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event = NULL, $disabled = FALSE, $multi_value = FALSE) {
	// Default size.
	$size = $length;
	if (! is_numeric ( $size ) || $size <= 0)
		$size = 50;
	
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onchange = "onchange=\"" . compulsory_ind_check ( $prompt, $compulsory_ind ) . "if(!checkEmail(this.value)){alert('" . get_opendb_lang_var ( 'email_is_not_valid', 'prompt', $prompt ) . "');return false;} $onchange_event return true;\"";
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	if ($multi_value) {
		return multivalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value );
	} else {
		return singlevalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value, $disabled );
	}
}

function filtered_field($name, $prompt, $length, $maxlength, $legalCharsExp, $compulsory_ind, $value, $onchange_event = NULL, $disabled = FALSE, $multi_value = FALSE) {
	// Default size.
	$size = $length;
	if (! is_numeric ( $size ) || $size <= 0)
		$size = 50;
	
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		// Get list of legal characters.
		$legalChars = expand_chars_exp ( $legalCharsExp );
		if (strlen ( $legalChars ) == 0) { //Default if not defined.
			$legalChars = expand_chars_exp ( 'a-zA-Z0-9_.' );
		}
		
		$onchange = "onchange=\"this.value=legalCharFilter(this.value, '" . $legalChars . "'); " . compulsory_ind_check ( $prompt, $compulsory_ind ) . " $onchange_event return true;\"";
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	if ($multi_value) {
		return multivalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value );
	} else {
		return singlevalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value, $disabled );
	}
}

function number_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event = NULL, $disabled = FALSE, $multi_value = FALSE) {
	// Default size.
	$size = $length;
	if (! is_numeric ( $size ) || $size <= 0)
		$size = 50;
	
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onchange = "onchange=\"this.value=numericFilter(this.value);" . compulsory_ind_check ( $prompt, $compulsory_ind ) . " $onchange_event return true;\"";
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	if ($multi_value) {
		return multivalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value );
	} else {
		return singlevalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value, $disabled );
	}
}

function get_datetime_value($value, $format_mask = "", $auto_datetime = "") {
	if (strlen ( $value ) > 0) {
		// the timestamp is stored in the database with the format YYYYMMDDHH24MISS
		$timestamp = get_timestamp_for_datetime ( $value, 'YYYYMMDDHH24MISS' );
		if ($timestamp !== FALSE) {
			if (strlen ( $format_mask ) == 0) {
				$format_mask = 'DD/MM/YYYY';
			}
			
			$datetime = get_localised_timestamp ( $format_mask, $timestamp );
			if ($datetime === FALSE) {
				$datetime = $value; // as a last resort
			}
		} else {
			$datetime = $value; // as a last resort
		}
	} else {
		if ($value === NULL && strcasecmp ( $auto_datetime, 'Y' ) === 0) {
			$datetime = get_localised_timestamp ( $format_mask ); // current date
		} else {
			$datetime = '';
		}
	}
	return $datetime;
}

/**
 * Format mask should correspond to a mask using the following components:
 *
 * 	Mask components supported are:
 *		DD - Days (01 - 31)
 *		MM - Months (01 -12)
 *		YYYY - Years
 *		HH24 - Hours (00 - 23)
 *		HH - Hours (01 - 12)
 *		MI - Minutes (00 - 59)
 *		SS - Seconds (00 - 59)
*/
function datetime_field($name, $prompt, $format_mask, $auto_datetime, $compulsory_ind, $value, $onchange_event, $disabled = FALSE, $multi_value = FALSE) {
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onchange = "onchange=\"" . compulsory_ind_check ( $prompt, $compulsory_ind ) . "if(this.value.length > 0 && !is_datetime(this.value, '" . $format_mask . "')){alert('" . get_opendb_lang_var ( 'datetime_is_not_valid', array (
				'prompt' => $prompt,
				'format_mask' => $format_mask ) ) . "');return false;} $onchange_event return true;\"";
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}

    $size = strlen($format_mask);
    $maxlength = $size + 2;
	
	if ($multi_value) {
		for($i = 0; $i < count ( $value ); $i ++) {
			$value [$i] = get_datetime_value ( $value [$i], $format_mask, $auto_datetime );
		}
		return multivalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value );
	} else {
		$value = get_datetime_value ( $value, $format_mask, $auto_datetime );
		return singlevalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value, $disabled );
	}
}

function textarea_field($name, $prompt, $cols, $rows, $length, $compulsory_ind, $value, $onchange_event = NULL, $disabled = FALSE) {
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onchange = "onchange=\"" . compulsory_ind_check ( $prompt, $compulsory_ind ) . " $onchange_event return true;\"";
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	return "\n<textarea name=\"$name\" wrap=virtual $onchange cols=\"$cols\" rows=\"$rows\"" . ($disabled ? ' DISABLED' : '') . ">" . $value . "</textarea>";
}

function checkbox_field($name, $prompt, $checked, $value, $onclick_event = NULL, $disabled = FALSE) {
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onclick = "onclick=\"$onclick_event return true;\"";
	} else {
		$onchange = "onchange=\"$onclick_event\"";
	}
	
	return "\n<input type=\"checkbox\" class=\"checkbox\" name=\"$name\" value=\"$value\" $onclick " . ($checked ? "CHECKED" : "") . "" . ($disabled ? ' DISABLED' : '') . ">";
}

function enhanced_checkbox_field($name, $prompt, $checked_value, $unchecked_value, $value, $onclick_event = NULL, $disabled = FALSE) {
	// Work out whether checked or not...
	if ($value !== NULL && strcasecmp ( $value, $checked_value ) === 0)
		$is_checked = TRUE;
	else
		$is_checked = FALSE;
	
	$onclick = "if (this.checked){this.form['$name'].value='$checked_value';}else{this.form['$name'].value='$unchecked_value';}";
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		$onclick = "onclick=\"$onclick; $onclick_event return true;\"";
	} else {
		$onclick = "onclick=\"$onclick;\"";
		$onchange = "onchange=\"$onclick_event\"";
	}
	
	return "\n<input type=\"hidden\" name=\"$name\" value=\"" . ($is_checked ? $checked_value : $unchecked_value) . "\">" 
			. "\n\t<input type=\"checkbox\" class=\"checkbox\" name=\"" . $name . "_cbox\" $onclick " . ($is_checked ? "CHECKED" : "") . "" . ($disabled ? ' DISABLED' : '') . ">";
}

/**
	@param $item_r where provided will give the item_id / instance_no, where not provided is safe to assume that this
	is a new item insert field and this information is not relevant.
*/
function url($name, $item_r, $item_attribute_type_r, $prompt, $length, $maxlength, $content_groups, $value, $onchange_event, $disabled = FALSE, $multi_value = FALSE) {
	// Default size.
	$size = $length;
	if (! is_numeric ( $size ) || $size <= 0) {
		$size = 50;
	}
	
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE) {
		if (strlen ( trim ( $content_groups ) ) > 0) {
			// might be an array of content groups
			$content_group_r = prc_args ( $content_groups );
			
			$extensions_r = fetch_file_type_extensions_r ( $content_group_r );
			if (is_not_empty_array ( $extensions_r )) {
				$extensions = implode ( ', ', $extensions_r );
			} else { // else just list of extensions otherwise
				$extensions = $content_groups;
				$extensions_r = $content_group_r;
			}
			
			$url_is_not_valid_message = addslashes ( get_opendb_lang_var ( 'url_is_not_valid', array (
					'prompt' => $prompt,
					'extensions' => $extensions ) ) );
			$onchange = "onchange=\"if(!isValidExtension(this.value, " . encode_javascript_array ( $extensions_r ) . ")){alert('" . $url_is_not_valid_message . "'); this.focus(); return false;} $onchange_event return true;\"";
		}
	} else {
		$onchange = "onchange=\"$onchange_event\"";
	}

    $field = '';
	if (($item_attribute_type_r ['file_attribute_ind'] ?? '') == 'Y') {
		$field .= "\n<ul class=\"urlOptionsMenu\" id=\"${name}-tab-menu\" class=\"file-upload-menu\">";
		$field .= "<li id=\"menu-${name}_saveurl\" class=\"activeTab\" onclick=\"return activateTab('${name}_saveurl', '${name}-tab-menu', '${name}-tab-content', 'activeTab', 'fieldContent');\">URL</li>";
		if (is_file_upload_enabled ()) {
			$field .= "<li id=\"menu-${name}_upload\" onclick=\"return activateTab('${name}_upload', '${name}-tab-menu', '${name}-tab-content', 'activeTab', 'fieldContent');\">Upload File</li>";
		}
		$field .= "</ul>";
		
		$field .= "<div class=\"urlOptionsContainer\" id=\"${name}-tab-content\">";
		
		$field .= "\n<div class=\"fieldContent\" id=\"${name}_saveurl\">";
		$field .= "<input type=\"text\" class=\"text\" name=\"$name\" value=\"$value\" $onchange size=\"" . $length . "\" " . (is_numeric ( $maxlength ) ? "maxlength=\"" . $maxlength . "\"" : "") . ">";
		$field .= "<input type=\"button\" class=\"button\" onclick=\"if(this.form['$name'].value.length>0){popup(this.form['$name'].value,'400','300');}else{alert('" . get_opendb_lang_var ( 'prompt_must_be_specified', 'prompt', $prompt ) . "');}\" value=\"" . get_opendb_lang_var ( 'view' ) . "\"" . ($disabled ? ' DISABLED' : '') . ">";
		$field .= "</div>";
		
		if (is_file_upload_enabled ()) {
			$field .= "<div class=\"fieldContentHidden\" id=\"${name}_upload\">";
			$field .= "<input type=\"file\" class=\"file\" name=\"${name}_upload\" $onchange size=\"" . $size . "\"" . ($disabled ? ' DISABLED' : '') . ">";
			$field .= "</div>";
		}
		
		$field .= '</div>';
	} else {
		if ($multi_value) {
			return multivalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value );
		} else {
			return singlevalue_text_field ( 'text', $name, $size, $maxlength, $onchange, $value, $disabled );
		}
	}
	return $field;
}

function value_radio_grid($name, $lookup_rs, $value, $disabled = FALSE) {
	$field = "<ul class=\"radioGridOptionsVertical\">";
	
	$is_checked = FALSE;
	foreach ( $lookup_rs as $val ) {
		if ((strlen ( $value ) > 0 && strcasecmp ( trim ( $value ), $val ) === 0) || (strlen ( $value ) == 0 && ! $is_checked)) {
			$field .= "\n<li><input type=\"radio\" class=\"radio\" name=\"$name\" value=\"$val\" CHECKED" . ($disabled ? ' DISABLED' : '') . ">$val</li>";
			$is_checked = TRUE;
		} else
			$field .= "\n<li><input type=\"radio\" class=\"radio\" name=\"$name\" value=\"$val\"" . ($disabled ? ' DISABLED' : '') . ">$val</li>";
	}
	
	$field .= "</ul>";
	
	return $field;
}

function review_options($name, $lookup_results, $mask, $orientation, $value, $disabled = FALSE) {
	if (isset ( $orientation ))
		$orientation = trim ( strtolower ( $orientation ) );
	else
		$orientation = "horizontal";
	
	$total_count = 0;
	$is_first_value = TRUE;
	$value_found = FALSE;
	
	$value = trim ( $value );
	
	if (strcasecmp ( $orientation, 'VERTICAL' ) == 0)
		$field = "<ul class=\"reviewOptionsVertical\">";
	else
		$field = "<ul class=\"reviewOptions\">";
	
	while ( $lookup_r = db_fetch_assoc ( $lookup_results ) ) {
		if ($is_first_value === TRUE) {
			$is_first_value = FALSE;
			$total_count = ( int ) $lookup_r ['value'];
		}
		
		$field .= "<li>";
		
		if ($value === NULL && $lookup_r ['checked_ind'] == 'Y')
			$field .= "\n<input type=\"radio\" class=\"radio\" name=\"$name\" value=\"" . $lookup_r ['value'] . "\" CHECKED" . ($disabled ? ' DISABLED' : '') . ">";
		else {
			// Case insensitive!
			if ($value !== NULL && strcasecmp ( $value, $lookup_r ['value'] ) === 0) {
				$value_found = TRUE;
				$field .= "\n<input type=\"radio\" class=\"radio\" name=\"$name\" value=\"" . $lookup_r ['value'] . "\" CHECKED" . ($disabled ? ' DISABLED' : '') . ">";
			} else {
				$field .= "\n<input type=\"radio\" class=\"radio\" name=\"$name\" value=\"" . $lookup_r ['value'] . "\"" . ($disabled ? ' DISABLED' : '') . ">";
			}
		}
		
		$field .= '<span class="starRating">';
		// Now display the images.
		for($i = 0; $i < ( int ) $lookup_r ['value']; $i ++)
			$field .= theme_image ( "rs.gif" );
		for($i = 0; $i < ( int ) ($total_count - ( int ) $lookup_r ['value']); $i ++)
			$field .= theme_image ( "gs.gif" );
		$field .= '</span>';
		
		// now the display value.
		$field .= format_display_value ( $mask, $lookup_r ['img'], $lookup_r ['value'], $lookup_r ['display'] );
		$field .= "</li>";
	}
	db_free_result ( $lookup_results );
	
	$field .= "</ul>";
	
	return $field;
}

function radio_grid($name, $lookup_results, $mask, $orientation, $value, $disabled = FALSE) {
	$lookup_val_rs = process_lookup_results ( $lookup_results, $value );
	
	if (strcasecmp ( $orientation, 'VERTICAL' ) == 0)
		$class = 'radioGridOptionsVertical';
	else
		$class = 'radioGridOptions';
	
	$buffer = "<ul class=\"$class\">";
	
	reset ( $lookup_val_rs );
	foreach ( $lookup_val_rs as $lookup_val_r ) {
		$buffer .= "\n<li><input type=\"radio\" class=\"radio\" name=\"$name\" value=\"" . $lookup_val_r ['value'] . "\"" . ($lookup_val_r ['checked_ind'] == 'Y' ? ' CHECKED' : '') . ($disabled ? ' DISABLED' : '') . ">" . format_display_value ( $mask, $lookup_val_r ['img'], $lookup_val_r ['value'], $lookup_val_r ['display'] ) . "</li>";
	}
	
	$buffer .= "</ul>";
	
	return $buffer;
}

function checkbox_grid($name, $lookup_results, $mask, $orientation, $value, $disabled = FALSE) {
	$lookup_val_rs = process_lookup_results ( $lookup_results, $value );
	
	if (strcasecmp ( $orientation, 'VERTICAL' ) == 0)
		$class = 'checkboxGridOptionsVertical';
	else
		$class = 'checkboxGridOptions';
	
	$buffer = "<ul class=\"$class\">";
	
	reset ( $lookup_val_rs );
	foreach ( $lookup_val_rs as $lookup_val_r ) {
		$buffer .= "<li><input type=\"checkbox\" class=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $lookup_val_r ['value'] . "\"" . ($lookup_val_r ['checked_ind'] == 'Y' ? ' CHECKED' : '') . ($disabled ? ' DISABLED' : '') . ">" . format_display_value ( $mask, $lookup_val_r ['img'], $lookup_val_r ['value'], $lookup_val_r ['display'] ) . "</li>";
	}
	
	$buffer .= "</ul>";
	
	return $buffer;
}

function single_select($name, $lookup_results, $mask, $length, $value, $onchange_event = NULL, $disabled = FALSE, $id = NULL) {
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE)
		$onchange = "onchange=\"$onchange_event\"";
	else
		$onchange = "onchange=\"$onchange_event\"";
	
	$lookup_val_rs = process_lookup_results ( $lookup_results, $value );
	
	$var = "\n<select " . ($id != NULL ? "id=\"$id\"" : "") . " name=\"$name\" $onchange" . ($disabled ? ' DISABLED' : '') . ">";
	
	reset ( $lookup_val_rs );
	foreach ( $lookup_val_rs as $lookup_val_r ) {
		// Now get the display value.
		$display = format_display_value ( $mask, NULL, $lookup_val_r ['value'], $lookup_val_r ['display'] );
		
		// Ensure any length restriction is enforced.
		if ($length > 0 && strlen ( $display ) > $length) {
			$display = substr ( $display, 0, $length );
		}
		
		$var .= "\n<option value=\"" . $lookup_val_r['value'] . "\"" . (($lookup_val_r['checked_ind'] ?? '') == 'Y' ? ' SELECTED' : '') . ">$display";
	}
	
	$var .= "\n</select>";
	return $var;
}

function multi_select($name, $lookup_results, $mask, $length, $size, $value, $onchange_event = NULL, $disabled = FALSE) {
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE)
		$onchange = "onchange=\"$onchange_event\"";
	else
		$onchange = "onchange=\"$onchange_event\"";
	
	if (is_numeric ( $size ) && $size > 1)
		$var = "\n<select multiple name=\"" . $name . "[]\" size=\"$size\" $onchange" . ($disabled ? ' DISABLED' : '') . ">";
	else
		$var = "\n<select name=\"$name\" $onchange" . ($disabled ? ' DISABLED' : '') . ">";
	
	$lookup_val_rs = process_lookup_results ( $lookup_results, $value );
	
	reset ( $lookup_val_rs );
	foreach ( $lookup_val_rs as $lookup_val_r ) {
		// Now get the display value.
		$display = format_display_value ( $mask, NULL, $lookup_val_r ['value'], $lookup_val_r ['display'] );
		
		// Ensure any length restriction is enforced.
		if ($length > 0 && strlen ( $display ) > $length) {
			$display = substr ( $display, 0, $length );
		}
		
		$var .= "\n<option value=\"" . $lookup_val_r ['value'] . "\"" . ($lookup_val_r ['checked_ind'] == 'Y' ? ' SELECTED' : '') . ">$display";
	}
	
	$var .= "\n</select>";
	return $var;
}

function value_select($name, $values_r, $size, $value, $onchange_event = NULL, $disabled = FALSE) {
	if (get_opendb_config_var ( 'widgets', 'enable_javascript_validation' ) !== FALSE)
		$onchange = "onchange=\"$onchange_event\"";
	else
		$onchange = "onchange=\"$onchange_event\"";
	
	if (is_numeric ( $size ) && $size > 1)
		$var = "\n<select multiple name=\"" . $name . "[]\" size=\"$size\" $onchange" . ($disabled ? ' DISABLED' : '') . ">";
	else
		$var = "\n<select name=\"$name\" $onchange" . ($disabled ? ' DISABLED' : '') . ">";

	foreach ( $values_r as $val ) {
		if ($value !== NULL && strcasecmp ( trim ( $value ), $val ) === 0)
			$var .= "\n<option value=\"$val\" SELECTED>$val";
		else
			$var .= "\n<option value=\"$val\">$val";
	}
	
	$var .= "\n</select>";
	
	return $var;
}

function process_lookup_results($lookup_results, $value) {
	if (is_array ( $value ) && count ( $value ) > 0)
		$values_r = $value;
	else if (! is_array ( $value ) && $value !== NULL) // if a single string value, convert to single element array.
		$values_r [] = $value;
	else // is_empty_array!
		$values_r = NULL;
	
	$lookup_rs = fetch_results_array ( $lookup_results );
	
	$value_found = FALSE;
	foreach ( $lookup_rs as $lookup_r ) {
		if (is_array ( $values_r ) && ($lookup_key = array_search2 ( $lookup_r ['value'], $values_r, TRUE )) !== FALSE) {
			$value_found = TRUE;
			break;
		}
	}
	
	$lookup_val_rs = array ();
	
	reset ( $lookup_rs );
	foreach ( $lookup_rs as $lookup_r ) {
		if ($value_found) {
			$lookup_r ['checked_ind'] = 'N';
		}
		
		if (is_array ( $values_r ) && ($lookup_key = array_search2 ( $lookup_r ['value'], $values_r, TRUE )) !== FALSE) {
			$lookup_r ['checked_ind'] = 'Y';
			
			// Remove the matched element
			array_splice ( $values_r, $lookup_key, 1 );
		}
		
		$lookup_val_rs [] = $lookup_r;
	}
	
	if (is_array ( $values_r )) {
		// Add the value to the list of options and select it.
		reset ( $values_r );
		foreach ( $values_r as $value ) {
			if (strlen ( $value ) > 0) {
				$lookup_val_rs [] = array (
						'value' => $value,
						'checked_ind' => 'Y' );
			}
		}
	}
	
	return $lookup_val_rs;
}
?>
