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

include_once("./functions/item_attribute.php");
include_once("./functions/item_type.php");
include_once("./functions/item.php");
include_once("./functions/http.php");
include_once("./functions/fileutils.php");
include_once("./functions/utils.php");
include_once("./functions/parseutils.php");
include_once("./functions/datetime.php");
include_once("./functions/email.php");
include_once("./functions/status_type.php");
include_once("./functions/theme.php");
include_once("./functions/file_type.php");
include_once("./functions/TitleMask.class.php");

/*
	Supported input widgets:
    	
  	hidden										Display a hidden field
	readonly									Display a hidden field and a readonly text field
  	text(length,maxlength,field_mask)			Display a text field
	email(length,maxlength,field_mask)			Display a email field.  Will validate that email
												address is valid.
	filtered(length,maxlength,legalchars,field_mask)
												A filtered text field.  Only characters specified
												in legalchars will be allowed, any others will be
												removed when onChange event fires.  You can specify
												?-? ranges, and include '-', using '\-'.
	date(mask) [NOT IMPLEMENTED]				This field will validate an entered date against
												a specified mask.
	format(mask)[NOT IMPLEMENTED]				This field will validate input against a mask.  Where
												the mask values are: 9 = numbers; X = uppercase letters
												x = lowercase letters.  No other numeral or number is 
												allowed.  Any punctuation, such as '.', '-' will be
												included in the final value sent to the database.

  	simple_checkbox(CHECKED,field_mask)			A really simple little input field, that does not do much
												of use.  Used to be known as 'checkbox', but we added a 
												much more powerful field of the same name with different
												parameters.  Not used in OpenDb anymore - kept for backwards
												compatibility, although this is hardly necessary.
												
	checkbox(checked-val,					    This is a new much more powerful checkbox solution.
			unchecked-val,					    This one will be used when the checkbox is called with
			field_mask)							more than two parameters.  You can still use this version
												without specifying the display_mask, by including an extra
												','.  This will only work from 0.50-dev26 onwards as the
												prc_function_spec function has been updated to support this.

	textarea(cols,rows,field_mask)				Displays a textarea with specified 
  	number(length,field_mask)					Display a text field, which can only have numeric input.
  												Numeric fields also have a maxlength exactly the same as 
  												their length.

	url(length,maxlength,"ext,ext2,etc")

	**** Special case - only used in item_review.php
	review_options(display_mask, orientation)	Displays a list of options, with stars beside.  Replaces
												the logic in item_review.php.

	Note: For all the above orientation corresponds to HORIZONTAL or VERTICAL

	radio_grid(display_mask,orientation)		Display radio group in a grid of columns wide.
	checkbox_grid(display_mask,orientation)		Display checkboxes in a grid of columns wide.

  	single_select(display_mask, length)			A single select list
	multi_select(display_mask, length, size)	A multi select list

	value_select(values, size)	A select widget which will generate an lov based on the comma
												delimited list of values specified for the first argument.
												In order to get this to work, you will need to enclose the
												values argument in double quotes.  If $size>1, a MULTIPLE
												select object will be generated.  The fieldname will be
												modified so that it returns an array via HTTP.

	Note: The s_attribute_type is for generating the lookup records.

	@param $dowrap 			Specify whether the field should be wrapped using format_field before
							returning.  Fields of type hidden will ignore this variable even if true.
	@param $promp_mask		The %prompt% variable will be replaced with the actual prompt.
							This parameter will be ignored if $dowrap is FALSE.
   	@param $onchange_event	Specify extra javascript for onchange event handler.
	 						The onchange event is passed to widgets:
								'textarea', 'text', 'url', 'filtered', 'number', 'email',
								'single_select', 'multi_select','value_select'

							For compatibility with older browsers the widgets:
								'simple_checkbox', 'checkbox' use the 'onclick' event instead.

	The compulsory indicator is only of use for straight text input fields.  The lookups already enforce
	entering a value, by allowing the selection of a default in the s_attribute_type_lookup table.

	Note:
	-----
		The display_mask argument for single_select and multi_select only supports the %value% and %display% specifiers.

		The other functions support all three %img%, %value% and %display%.  If the %img% column has no value, then
		the %value% is used instead.  However if the %img% has a value of "none", then the image tag will be replaced
		with an empty string.

@param $item_attribute_type_r can be an array, which indicates that we are actually being passed the entire $item_attribute,
s_attribute_type, s_item_attribute_type entry!
*/

/**
stub for non item specific functionality
*/
function get_input_field(
		$fieldname,
		$s_attribute_type,
		$prompt,
		$input_type,
		$compulsory_ind='N',
		$value=NULL,
		$dowrap=TRUE,
		$prompt_mask=NULL,
		$onchange_event=NULL,
		$disabled = FALSE)
{
	$input_type_def = prc_function_spec($input_type);
	
	return get_item_input_field(
		$fieldname,
		array( // $item_attribute_type_r
			's_attribute_type'=>$s_attribute_type,
			'order_no'=>NULL,
			'prompt'=>$prompt,
			'input_type'=>$input_type_def['type'],
			'input_type_arg1'=>$input_type_def['args'][0],
			'input_type_arg2'=>$input_type_def['args'][1],
			'input_type_arg3'=>$input_type_def['args'][2],
			'input_type_arg4'=>$input_type_def['args'][3],
			'input_type_arg5'=>$input_type_def['args'][4],
			'compulsory_ind'=>$compulsory_ind),
		NULL, // $item_r
		$value,
		$dowrap,
		$prompt_mask,
		$onchange_event,
		$disabled);
}

function get_item_input_field(
		$fieldname,
		$item_attribute_type_r,
		$item_r,
		$value=NULL,
		$dowrap=TRUE,
		$prompt_mask=NULL,
		$onchange_event=NULL,
		$disabled = FALSE)
{
	if(is_array($item_attribute_type_r))
	{
		$s_attribute_type = $item_attribute_type_r['s_attribute_type'];
		$order_no = $item_attribute_type_r['order_no'];
		$prompt = $item_attribute_type_r['prompt'];
		$input_type = $item_attribute_type_r['input_type'];
		$compulsory_ind = $item_attribute_type_r['compulsory_ind'];
		
		$widget['type'] = $item_attribute_type_r['input_type'];
		$widget['args'][0] = $item_attribute_type_r['input_type_arg1'];
		$widget['args'][1] = $item_attribute_type_r['input_type_arg2'];
		$widget['args'][2] = $item_attribute_type_r['input_type_arg3'];
		$widget['args'][3] = $item_attribute_type_r['input_type_arg4'];
		$widget['args'][4] = $item_attribute_type_r['input_type_arg5'];
	}

	// an array will be a lookup value
	if(!is_array($value))
	{
		// Escape all html entities so they are displayed correctly!
		if(strlen($value)>0)
		{
			$value = htmlspecialchars($value);
		}
	}

	$field = NULL;
	$field_mask = NULL;

	// Now we have to work out how to parse the input_type
	if($item_attribute_type_r['input_type'] == 'hidden')
	{
		return hidden_field($fieldname, $value);
	}
	else if($item_attribute_type_r['input_type'] == 'readonly')// arg[0] = field_mask
	{
		$field_mask = $widget['args']['0'];
		$field = readonly_field($fieldname, $value);
	}
	else if($item_attribute_type_r['input_type'] == 'textarea' || $item_attribute_type_r['input_type'] == 'htmlarea') // arg[0] = rows, arg[1] = cols, arg[2] = length, arg[3] = field_mask
	{
        $field_mask = $widget['args']['3'];
		$field = textarea_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['1'], $widget['widget']['2'], $compulsory_ind, $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'text') // arg[0] = length of field, arg[1] = maxlength of field, arg[2] = field_mask
	{
        $field_mask = $widget['args']['2'];
        if($item_attribute_type_r['multi_attribute_ind'] == 'Y')
        	$field = multivalue_text_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['1'], $compulsory_ind, $value, $onchange_event, $disabled);
        else
			$field = text_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['1'], $compulsory_ind, $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'password') // arg[0] = length of field, arg[1] = maxlength of field, arg[2] = field_mask
	{
        $field_mask = $widget['args']['2'];
		$field = password_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['1'], $compulsory_ind, $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'email') // arg[0] = length of field, arg[1] = maxlength of field, arg[2] = field_mask
	{
        $field_mask = $widget['args']['2'];
		$field = email_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['1'], $compulsory_ind, $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'filtered') // arg[0] = length of field, arg[1] = maxlength of field, arg[2] = legalChars, arg[3] = field_mask
	{
        $field_mask = $widget['args']['3'];
		$field = filtered_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['1'], $widget['args']['2'], $compulsory_ind, $value, $onchange_event, $disabled);
    }
	else if($item_attribute_type_r['input_type'] == 'datetime') // arg[0] = datetime mask, arg[1] = auto_datetime, arg[2] = field_mask
	{
        $field_mask = $widget['args']['2'];
		$field = datetime_field($fieldname, $prompt, ifempty($widget['args']['0'],'DD/MM/YYYY'), $widget['args']['1'], $compulsory_ind, $value, $onchange_event, $disabled);
    }
	else if($item_attribute_type_r['input_type'] == 'number') // arg[0] = length of field, arg[0] = maxlength of field, arg[1] = field_mask
	{
        $field_mask = $widget['args']['1'];
		$field = number_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['0'], $compulsory_ind, $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'simple_checkbox') // arg[0] = checked, arg[1] = field_mask
	{
        $field_mask = $widget['args']['1'];
		$field = checkbox_field($fieldname, $prompt, strcasecmp(trim($widget['args']['0']), 'CHECKED')===0, $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'checkbox') // arg[0] = checked, arg[1] = unchecked, arg[2] = field_mask
	{
        $field_mask = $widget['args']['2'];
		$field = enhanced_checkbox_field($fieldname, $prompt, $widget['args']['0'], $widget['args']['1'], $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'checkbox_grid')
	{
		$lookup_results = fetch_attribute_type_lookup_rs($s_attribute_type, 'order_no, '.get_lookup_order_by($widget['args']['0']).' ASC');
		if($lookup_results)//arg[0] = display_mask, arg[1] = orientation
			$field = checkbox_grid($fieldname, $lookup_results, $widget['args']['0'], $widget['args']['1'], $value, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'radio_grid')
	{
		$lookup_results = fetch_attribute_type_lookup_rs($s_attribute_type, 'order_no, '.get_lookup_order_by($widget['args']['0']).' ASC');
		if($lookup_results)//arg[0] = display_mask, arg[1] = orientation
			$field = radio_grid($fieldname, $lookup_results, $widget['args']['0'], $widget['args']['1'], $value, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'value_radio_grid')//arg[0] = "comma delimited list of values"
	{
		$field = value_radio_grid($fieldname, explode(',', $widget['args']['0']), $value, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'single_select')
	{
		$lookup_results = fetch_attribute_type_lookup_rs($s_attribute_type, 'order_no, '.get_lookup_order_by($widget['args']['0']).' ASC');
		if($lookup_results) //arg[0] = display mask, arg[1] = max value length
			$field = single_select($fieldname, $lookup_results, $widget['args']['0'], $widget['args']['1'], $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'multi_select')
	{
		$lookup_results = fetch_attribute_type_lookup_rs($s_attribute_type,'order_no, '.get_lookup_order_by($widget['args']['0']).' ASC');
		if($lookup_results) //arg[0] = display mask, arg[1] = max value length, arg[2] = select box number of visible rows
			$field = multi_select($fieldname, $lookup_results, $widget['args']['0'], $widget['args']['1'], $widget['args']['2'], $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'value_select')//arg[0] = "comma delimited list of values"; arg[1] = number of visible rows (Defaults to single select
	{
		$field = value_select($fieldname, explode(',', $widget['args']['0']), $widget['args']['1'], $value, $onchange_event, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'review_options')//arg[1] = display_mask, arg[1] = orientation
	{
		$lookup_results = fetch_attribute_type_lookup_rs($s_attribute_type, 'value DESC');//We want the rows highest value first.
		if($lookup_results)
			$field = review_options($fieldname, $lookup_results, $widget['args']['0'], $widget['args']['1'], $value, $disabled);
	}
	else if($item_attribute_type_r['input_type'] == 'url')//arg[0] = length of field, arg[1] = maxlength of field, arg[2] = extensions
	{
		$field = url($fieldname, $item_r, $item_attribute_type_r, $prompt, $widget['args']['0'], $widget['args']['1'], $widget['args']['2'], $value, $onchange_event, $disabled);
	}
	else
	{
		$field = ">>> ERROR (input_type = $input_type) <<<";
	}
	
	if($dowrap)
   		return format_item_data_field($item_attribute_type_r, $field, $prompt_mask, $field_mask);
   	else
   		return $field;
}

/*
* A display mask consists of %value%, %display% and %img% mask variables, the
* first of %value% or %display% encountered will effect the order by chosen.
*/
function get_lookup_order_by($display_mask)
{
	// if display mask is empty return default of 'value'
	if(strlen($display_mask)==0)
		return 'value'; //default orderby is 'value'
	else
	{
		$displayPos = strpos($display_mask, '%display%');
		$valuePos = strpos($display_mask, '%value%');
		if($displayPos!==FALSE && ($valuePos===FALSE || $valuePos > $displayPos))
			return 'display';
		else
			return 'value';
	} 
}

function validate_input_field($prompt, $input_type, $compulsory_ind = 'N', $value, &$errors)
{
	$input_type_def = prc_function_spec($input_type);
	
	return validate_item_input_field(
		array( // $item_attribute_type_r
			'prompt'=>$prompt,
			'input_type'=>$input_type_def['type'],
			'input_type_arg1'=>$input_type_def['args'][0],
			'input_type_arg2'=>$input_type_def['args'][1],
			'input_type_arg3'=>$input_type_def['args'][2],
			'input_type_arg4'=>$input_type_def['args'][3],
			'input_type_arg5'=>$input_type_def['args'][4],
			'compulsory_ind'=>$compulsory_ind),
		$value,
		$errors);
}

/*
* Validate all input fields and return error(s) to caller, if failed.
*
* This is a basic attempt to prevent users bypassing javascript validation, and causing integrity
* problems in the database.  In future releases this function will be further augmented for other
* widget types.
*/
function validate_item_input_field($item_attribute_type_r, $value, &$errors)
{
	// cater for multivalue fields here!
	if(!is_array($value) && strlen(trim($value))>0)
	{
		$tmpval = trim($value);
		unset($value);
		
		$value[] = $tmpval;
	}
	
	if($item_attribute_type_r['compulsory_ind'] == 'Y')
	{
		// at this point, $value will always be an array because of the block above.
		if(is_empty_or_not_array($value))
		{
			$error = array('error'=>get_opendb_lang_var('prompt_must_be_specified', 'prompt', $item_attribute_type_r['prompt']),'detail'=>'');
			if(is_array($errors))
				$errors[] = $error;
			else
				$errors = $error;
			return FALSE;
		}
	}
	
	if(is_not_empty_array($value) && $item_attribute_type_r['lookup_attribute_ind'] != 'Y')
	{
		switch($item_attribute_type_r['input_type'])
		{
			case 'hidden':
			case 'readonly':
			case 'textarea':
			case 'htmlarea':
			case 'text':
			case 'password':
			case 'simple_checkbox':
			case 'checkbox':
			case 'check_boxes': // deprecated
			case 'vertical_check_boxes': // deprecated
			case 'horizontal_check_boxes': // deprecated
			case 'radio_group': // deprecated
			case 'vertical_radio_group': // deprecated
			case 'horizontal_radio_group': // deprecated
			case 'radio_grid':
			case 'value_radio_grid':
			case 'checkbox_grid':
			case 'single_select':
			case 'multi_select':
			case 'value_select':
				return TRUE;
				break;
				
			case 'url':

				// will be an array of content groups
				if(strlen($item_attribute_type_r['input_type_arg3'])>0)
				{
					$content_group_r = prc_args($item_attribute_type_r['input_type_arg3']);
					$extensions_r = fetch_file_type_extensions_r($content_group_r);
	
					// it might just be a list of extensions
					if(!is_not_empty_array($extensions_r))
					{
						$extensions_r = $content_group_r;
					}
	
					for($i=0; $i<count($value); $i++)
					{
						if(!in_array(strtolower(get_file_ext($value[$i])), $extensions_r))
						{
							$error = array('error'=>get_opendb_lang_var('url_is_not_valid', array('prompt'=>$item_attribute_type_r['prompt'], 'extensions'=>implode(', ', $extensions_r))),'detail'=>'');
							if(is_array($errors))
								$errors[] = $error;
							else
								$errors = $error;
							return FALSE;
						}
					}
				}
				
				//else
				return TRUE;
				
			case 'email':
				for($i=0; $i<count($value); $i++)
				{
					if(!is_valid_email_addr($value[$i]))
					{
						$error = array('error'=>get_opendb_lang_var('email_is_not_valid', 'prompt', $item_attribute_type_r['prompt']),'detail'=>'');
						if(is_array($errors))
							$errors[] = $error;
						else
							$errors = $error;					
						return FALSE;
					}
				}
			
				//else
				return TRUE;
					
			case 'datetime':
				for($i=0; $i<count($value); $i++)
				{
					$timestamp = get_timestamp_for_datetime($value[$i], $item_attribute_type_r['input_type_arg1']);
					if($timestamp===FALSE)
					{
						//else perhaps it is a timestamp value already.
						$timestamp = get_timestamp_for_datetime($value[$i], 'YYYYMMDDHH24MISS');
						if($timestamp===FALSE)
						{
							$error = array('error'=>get_opendb_lang_var('datetime_is_not_valid', array('prompt'=>$item_attribute_type_r['prompt'], 'format_mask'=>$item_attribute_type_r['input_type_arg1'])),'detail'=>'');
							if(is_array($errors))
								$errors[] = $error;
							else
								$errors = $error;
							return FALSE;
						}
					}
				}
				
				//else
				return TRUE;
			
			case 'filtered':
				$legalChars = expand_chars_exp($item_attribute_type_r['input_type_arg3']);
				
				for($i=0; $i<count($value); $i++)
				{
					$value[$i] = trim($value[$i]);
					
					for($j=0; $j<strlen($value[$i]); $j++)
					{
						if(strstr($legalChars, substr($value[$i],$j,1)) === FALSE)
						{
							$error = array('error'=>get_opendb_lang_var('prompt_must_be_format', array('prompt'=>$item_attribute_type_r['prompt'], 'format'=>'['.$item_attribute_type_r['input_type_arg3'].']')),'detail'=>'');
							if(is_array($errors))
								$errors[] = $error;
							else
								$errors = $error;
							return FALSE;
						}
					}
				}
				
				//else
				return TRUE;
					
			case 'number':
				for($i=0; $i<count($value); $i++)
				{
					if(!is_numeric($value[$i]))
					{
						$error = array('error'=>get_opendb_lang_var('prompt_must_be_format', array('prompt'=>$item_attribute_type_r['prompt'], 'format'=>'[0-9]')),'detail'=>'');
						if(is_array($errors))
							$errors[] = $error;
						else
							$errors = $error;
						return FALSE;
					}
				}
				
				//else
				return TRUE;
											
			default:
				return TRUE;
				break;
		}
	}
	else
	{
		return TRUE;
	}
}

function filter_input_field($input_type, $value)
{
	$input_type_def = prc_function_spec($input_type);
	
	return filter_item_input_field(
		array( // $item_attribute_type_r
			'input_type'=>$input_type_def['type'],
			'input_type_arg1'=>$input_type_def['args'][0],
			'input_type_arg2'=>$input_type_def['args'][1],
			'input_type_arg3'=>$input_type_def['args'][2],
			'input_type_arg4'=>$input_type_def['args'][3],
			'input_type_arg5'=>$input_type_def['args'][4]),
		$value);
}

/*
* Will filter input field according to the input_type widget.  In some cases there will be no filtering
* performed.  This filter will also do things like remove HTML, and replace windows/mac newlines with
* unix ones.
*/
function filter_item_input_field($item_attribute_type_r, $value)
{
	// FALSE is not understood as a value, but it means it is not found, so
	// set to NULL which is pretty much the same thing.
	if($value === FALSE)
	{
		return NULL;
	}
	
	if(!is_array($value))
	{
		$tmpval = trim($value);
		unset($value);
		
		if(strlen($tmpval)>0)
		{
			// only support text type for now
			if($item_attribute_type_r['input_type'] == 'text' && $item_attribute_type_r['multi_attribute_ind'] == 'Y')
			{
				$value = explode("\n", replace_newlines($tmpval));
			}
			else
			{
				$value[] = $tmpval;
			}
		}
		else
		{
			return NULL;	
		}
	}
	    	
	for($i=0; $i<count($value); $i++)
	{
		$value[$i] = trim(replace_newlines($value[$i]));
		
		if($item_attribute_type_r['lookup_attribute_ind'] != 'Y' && strlen($value[$i])>0)
		{
			// Now we have to work out how to parse the input_type
			switch($item_attribute_type_r['input_type'])
			{
				case 'hidden':
				case 'readonly':
				case 'text':
				case 'password':
				case 'textarea':
					$value[$i] = strip_tags($value[$i]);
					break;
					
				case 'htmlarea':
					$value[$i] = strip_tags($value[$i], '<'.implode('><', get_opendb_config_var('widgets', 'legal_html_tags')).'>');
					break;
					
				case 'check_boxes':// deprecated
				case 'vertical_check_boxes':// deprecated
				case 'horizontal_check_boxes':// deprecated
				case 'radio_group':// deprecated
				case 'vertical_radio_group':// deprecated
				case 'horizontal_radio_group':// deprecated
				case 'simple_checkbox':
				case 'checkbox':
				case 'radio_grid':
				case 'checkbox_grid':
				case 'single_select':
				case 'multi_select':
				case 'value_radio_grid':
				case 'value_select':
					// do nothing
					break;
					
				case 'url':
					// do nothing
					break;
			
				case 'email':
					// do nothing
					break;
					
				case 'datetime':
					$components = get_timestamp_components_for_datetime($value[$i], $item_attribute_type_r['input_type_arg1']);
					if($components !== FALSE)
					{
						// This is the 'YYYYMMDDHH24MISS' mask.
						$value[$i] = 
							str_pad($components['year'],4,'0', STR_PAD_LEFT)
							.str_pad($components['month'],2,'0', STR_PAD_LEFT)
							.str_pad($components['day'],2,'0', STR_PAD_LEFT)
							.str_pad($components['hour'],2,'0', STR_PAD_LEFT)
							.str_pad($components['minute'],2,'0', STR_PAD_LEFT)
							.str_pad($components['second'],2,'0', STR_PAD_LEFT);
					}
					break;
					
				case 'number':
					$value[$i] = remove_illegal_chars($value[$i], expand_chars_exp('0-9'));
					break;
					
				case 'filtered':
					$value[$i] = remove_illegal_chars($value[$i], expand_chars_exp($item_attribute_type_r['input_type_arg3']));
					break;
				default:
					// do nothing
					break;
			}
		}//if($attribute_type_r['lookup_attribute_ind'] != 'Y')
	}
	
	if($item_attribute_type_r['lookup_attribute_ind'] == 'Y' || $item_attribute_type_r['multi_attribute_ind'] == 'Y')
		return $value;
	else
		return $value[0];
}

/*
 Will format onchange event check if $compulsory_ind == 'Y'
*/
function compulsory_ind_check($prompt, $compulsory_ind)
{
	if($compulsory_ind == 'Y')
		return "if(this.value.length==0){alert('".get_opendb_lang_var('prompt_must_be_specified', 'prompt', $prompt)."'); this.focus(); return false;} ";
	else
		return false;		
}

/**
*/
function hidden_field($name, $value)
{
	return "\n<input type=\"hidden\" name=\"$name\" value=\"$value\">";
}

/**
*/
function readonly_field($name, $value)
{
	return 	$value.
			"\n<input type=\"hidden\" name=\"$name\" value=\"$value\">";
}

/**
  If compulsory_ind = 'Y', we need to provide an onchange event to check
  for this.
*/
function text_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event=NULL, $disabled = FALSE)
{
	// Default size.
	$size = $length; 
	if(!is_numeric($size) || $size <= 0)
		$size = 50;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onchange = "onchange=\"".compulsory_ind_check($prompt, $compulsory_ind)." $onchange_event return true;\"";
	}
	else
	{
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	return "\n<input type=\"text\" class=\"text\" name=\"".$name."\" $onchange size=\"".$size."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":"")." value=\"".$value."\"".($disabled?' DISABLED':'').">";
}

/**
	The basic idea is that a multivalue text field is a textarea, where each row in the text area, represents a single element of the array
	of elements.
*/
function multivalue_text_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event=NULL, $disabled = FALSE)
{
	// Default size.
	$size = $length; 
	if(!is_numeric($size) || $size <= 0)
		$size = 50;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onchange = "onchange=\"".compulsory_ind_check($prompt, $compulsory_ind)." $onchange_event return true;\"";
	}
	else
	{
		$onchange = "onchange=\"$onchange_event\"";
	}
	$buffer .= "\n<div class=\"multiValue\">";
	$buffer .= "<ul class=\"multiValue\" id=\"${name}-multi_value_list\">";
	if(count($value)>0)
	{
		for($i=0; $i<count($value); $i++)
		{
			$buffer .= "\n<li><input type=\"text\" class=\"text\" name=\"".$name."[]\" size=\"".$size."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":"")." value=\"".$value[$i]."\"></li>";
		}
	}
	else
	{
		$buffer .= "\n<li><input type=\"text\" class=\"text\" name=\"".$name."[]\" size=\"".$size."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":"")." value=\"\"></li>";
	}
	
	$buffer .= "</ul>";
	
	$buffer .= "<a href=\"#\" onclick=\"addInputField('${name}-multi_value_list', '$name', '$length'".(is_numeric($maxlength)?", '$maxlength'":"")."); return false;\">".get_opendb_lang_var('add')."</a>";
	
	$buffer .= "</div>";
	return $buffer;
}

/**
  If compulsory_ind = 'Y', we need to provide an onchange event to check
  for this.
*/
function password_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event=NULL, $disabled = FALSE)
{
	// Default size.
	$size = $length; 
	if(!is_numeric($size) || $size <= 0)
		$size = 50;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onchange = "onchange=\"".compulsory_ind_check($prompt, $compulsory_ind)." $onchange_event return true;\"";
	}
	else
		$onchange = "onchange=\"$onchange_event\"";

	return "\n<input type=\"password\" class=\"password\" name=\"".$name."\" $onchange size=\"".$size."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":"")." value=\"".$value."\"".($disabled?' DISABLED':'').">";
}

/**
  If compulsory_ind = 'Y', we need to provide an onchange event to check
  for this.
*/
function email_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event=NULL, $disabled = FALSE)
{
	// Default size.
	$size = $length; 
	if(!is_numeric($size) || $size <= 0)
		$size = 50;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onchange = "onchange=\"".compulsory_ind_check($prompt, $compulsory_ind)."if(!checkEmail(this.value)){alert('".get_opendb_lang_var('email_is_not_valid', 'prompt', $prompt)."');return false;} $onchange_event return true;\"";
	else
		$onchange = "onchange=\"$onchange_event\"";
	
	return "\n<input type=\"text\" class=\"text\" name=\"".$name."\" $onchange size=\"".$size."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":""). " value=\"".$value."\"".($disabled?' DISABLED':'').">";
}

/**
  If compulsory_ind = 'Y', we need to provide an onchange event to check
  for this.
  
  A special field, which  
*/
function filtered_field($name, $prompt, $length, $maxlength, $legalCharsExp, $compulsory_ind, $value, $onchange_event=NULL, $disabled = FALSE)
{
	// Default size.
	$size = $length; 
	if(!is_numeric($size) || $size <= 0)
		$size = 50;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		// Get list of legal characters.
		$legalChars = expand_chars_exp($legalCharsExp);
		if(strlen($legalChars)==0)//Default if not defined.
		{
			$legalChars	= expand_chars_exp('a-zA-Z0-9_.');
		}
			
		$onchange = "onchange=\"this.value=legalCharFilter(this.value, '".$legalChars."'); ".compulsory_ind_check($prompt, $compulsory_ind)." $onchange_event return true;\"";
	}
	else
		$onchange = "onchange=\"$onchange_event\"";
		
	return "\n<input type=\"text\" class=\"text\" name=\"".$name."\" $onchange size=\"".$size."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":""). " value=\"".$value."\"".($disabled?' DISABLED':'').">";
}

/**
  If compulsory_ind = 'Y', we need to provide an onchange event to check
  for this.

  The @param display_mask, supports one format specifier of %field% which specifies where the
  &lt;input ...&gt; field is going, the rest is text that will be included in the returned field
  verbatim.  If display_mask is empty, then format will will a default of the input fiel by itself.
*/
function number_field($name, $prompt, $length, $maxlength, $compulsory_ind, $value, $onchange_event=NULL, $disabled = FALSE)
{
	// Default size.
	$size = $length; 
	if(!is_numeric($size) || $size <= 0)
		$size = 50;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onchange = "onchange=\"this.value=numericFilter(this.value);".compulsory_ind_check($prompt, $compulsory_ind)." $onchange_event return true;\"";
	}
	else
		$onchange = "onchange=\"$onchange_event\"";

	return "\n<input type=\"text\" class=\"text\" name=\"".$name."\" $onchange size=\"".$size."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":""). " value=\"".$value."\"".($disabled?' DISABLED':'').">";
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
function datetime_field($fieldname, $prompt, $format_mask, $auto_datetime, $compulsory_ind, $value, $onchange_event, $disabled = FALSE)
{
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onchange = "onchange=\"".compulsory_ind_check($prompt, $compulsory_ind)."if(this.value.length > 0 && !is_datetime(this.value, '".$format_mask."')){alert('".get_opendb_lang_var('datetime_is_not_valid', array('prompt'=>$prompt,'format_mask'=>$format_mask))."');return false;} $onchange_event return true;\"";
	}
	else
		$onchange = "onchange=\"$onchange_event\"";

	if(strlen($value)>0)
	{
		// the timestamp is stored in the database with the format YYYYMMDDHH24MISS
		$timestamp = get_timestamp_for_datetime($value, 'YYYYMMDDHH24MISS');
		if($timestamp !== FALSE)
		{
			if(strlen($format_mask)==0)
				$format_mask = 'DD/MM/YYYY';

			$datetime = get_localised_timestamp($format_mask, $timestamp);
			if($datetime === FALSE)
			{
				$datetime = $value; // as a last resort
			}
		}
		else
		{
			$datetime = $value; // as a last resort
		}
	}
	else
	{
		if($value === NULL && strcasecmp($auto_datetime, 'Y') === 0)
		{
			$datetime = get_localised_timestamp($format_mask); // current date
		}
		else
		{
			$datetime = '';
		}
	}
	return "\n<input type=\"text\" class=\"text\" name=\"".$fieldname."\" value=\"".$datetime."\" ".$onchange."".($disabled?' DISABLED':'').">";
}

/*
	If compulsory_ind = 'Y', we need to provide an onchange event to check
	for this.
*/
function textarea_field($name, $prompt, $cols, $rows, $length, $compulsory_ind, $value, $onchange_event=NULL, $disabled = FALSE)
{
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onchange = "onchange=\"".compulsory_ind_check($prompt, $compulsory_ind)." $onchange_event return true;\"";
	}
	else
		$onchange = "onchange=\"$onchange_event\"";

	return "\n<textarea name=\"$name\" wrap=virtual $onchange cols=\"$cols\" rows=\"$rows\"".($disabled?' DISABLED':'').">".
			$value.
			"</textarea>";
}

/**
*/
function checkbox_field($name, $prompt, $checked, $value, $onclick_event=NULL, $disabled = FALSE)
{
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onclick = "onclick=\"$onclick_event return true;\"";
	}
	else
		$onchange = "onchange=\"$onclick_event\"";

	return "\n<input type=\"checkbox\" class=\"checkbox\" name=\"$name\" value=\"$value\" $onclick ".($checked?"CHECKED":"")."".($disabled?' DISABLED':'').">";
}

/**
	@param The $value parameter is never actually used to set the value of the parameter,
	but only to work out which of the $checked and $unchecked values to use.

	Note: CASE INSENSITIVE match performed.
*/
function enhanced_checkbox_field($name, $prompt, $checked_value, $unchecked_value, $value, $onclick_event=NULL, $disabled = FALSE)
{
	// Work out whether checked or not...
	if($value!==NULL && strcasecmp($value, $checked_value)===0)
		$is_checked = TRUE;
	else
		$is_checked = FALSE;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		$onclick = "onclick=\"$onclick_event return true;\"";
	}
	else
		$onchange = "onchange=\"$onclick_event\"";

	return "\n<input type=\"hidden\" name=\"$name\" value=\"".($is_checked?$checked_value:$unchecked_value)."\">"
			."\n\t<input type=\"checkbox\" class=\"checkbox\" name=\"".$name."_cbox\" onclick=\"if (this.checked){this.form['$name'].value='$checked_value';}else{this.form['$name'].value='$unchecked_value';}\" $onclick ".($is_checked?"CHECKED":"")."".($disabled?' DISABLED':'').">";
}

/**
	@param $item_r where provided will give the item_id / instance_no, where not provided is safe to assume that this
	is a new item insert field and this information is not relevant.
*/
function url($name, $item_r, $item_attribute_type_r, $prompt, $length, $maxlength, $content_groups, $value, $onchange_event, $disabled = FALSE)
{
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		if(strlen(trim($content_groups))>0)
		{
			// might be an array of content groups
			$content_group_r = prc_args($content_groups);
			
			$extensions_r = fetch_file_type_extensions_r($content_group_r);
			if(is_not_empty_array($extensions_r))
			{
				$extensions = implode(', ', $extensions_r);
			}
			else // else just list of extensions otherwise
			{
				$extensions = $content_groups;
				$extensions_r = $content_group_r;
			}

			$url_is_not_valid_message = addslashes(get_opendb_lang_var('url_is_not_valid', array('prompt'=>$prompt,'extensions'=>$extensions)));
			$onchange = "onchange=\"if(!isValidExtension(this.value, ".encode_javascript_array($extensions_r).")){alert('".$url_is_not_valid_message."'); this.focus(); return false;} $onchange_event return true;\"";
		}
	}
	else
	{
		$onchange = "onchange=\"$onchange_event\"";
	}
	
	if($item_attribute_type_r['file_attribute_ind'] == 'Y')
	{
		$field .= "<ul class=\"urlOptionsMenu\" id=\"${name}-tab-menu\" class=\"file-upload-menu\">";
		$field .= "<li id=\"menu-${name}_saveurl\" class=\"activeTab\" onclick=\"return activateTab('${name}_saveurl', '${name}-tab-menu', '${name}-tab-content', 'activeTab', 'fieldContent');\">URL</li>";
		if(is_file_upload_enabled())
		{
			$field .= "<li id=\"menu-${name}_upload\" onclick=\"return activateTab('${name}_upload', '${name}-tab-menu', '${name}-tab-content', 'activeTab', 'fieldContent');\">Upload File</li>";
		}
		$field .= "</ul>";

		$field .= "<div class=\"urlOptionsContainer\" id=\"${name}-tab-content\">";

		$field .= "<div class=\"fieldContent\" id=\"${name}_saveurl\">";
		$field .= "<input type=\"text\" class=\"text\" name=\"$name\" value=\"$value\" $onchange size=\"".$length."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":"").">";
		$field .= "<input type=\"button\" class=\"button\" onclick=\"if(this.form['$name'].value.length>0){popup('url.php?url='+escape(this.form['$name'].value),'400','300');}else{alert('".get_opendb_lang_var('prompt_must_be_specified', 'prompt', $prompt)."');}\" value=\"".get_opendb_lang_var('view')."\"".($disabled?' DISABLED':'').">";
		$field .= "</div>";

		if(is_file_upload_enabled())
		{
	       	// Default size.
			$size = $length; 
			if(!is_numeric($size) || $size <= 0)
				$size = 50;
	
			$field .= "<div class=\"fieldContentHidden\" id=\"${name}_upload\">";
			$field .= "<input type=\"file\" class=\"file\" name=\"${name}_upload\" $onchange size=\"".$size."\"".($disabled?' DISABLED':'').">";
			$field .= "</div>";
		}
	
		$field .= '</div>';
	}
	else
	{
		$field .= "<input type=\"text\" class=\"text\" name=\"$name\" value=\"$value\" $onchange size=\"".$length."\" ".(is_numeric($maxlength)?"maxlength=\"".$maxlength."\"":"").">";
	}
	return $field;
}

/**
* @param $lookup_rs - array of values
*/
function value_radio_grid($name, $lookup_rs, $value, $disabled = FALSE)
{
	$field = "<ul class=\"radioGridOptionsVertical\">";

	$is_checked = FALSE;
	while(list(,$val) = each($lookup_rs))
	{	
		if((strlen($value)>0 && strcasecmp(trim($value), $val)===0) || (strlen($value)==0 && !$is_checked))
		{
			$field .= "\n<li><input type=\"radio\" class=\"radio\" name=\"$name\" value=\"$val\" CHECKED".($disabled?' DISABLED':'').">$val</li>";
			$is_checked=TRUE;
		}
		else
			$field .= "\n<li><input type=\"radio\" class=\"radio\" name=\"$name\" value=\"$val\"".($disabled?' DISABLED':'').">$val</li>";
	}

	$field .= "</ul>";

	return $field;
}

/**
	Special function, only used by item_review script.
*/
function review_options($name, $lookup_results, $mask, $orientation, $value, $disabled = FALSE)
{
	if(isset($orientation))
		$orientation=trim(strtolower($orientation));
	else
		$orientation="horizontal";
		
	$total_count = 0;
	$is_first_value=TRUE;
	$value_found=FALSE;

	$value = trim($value);
	
	if(strcasecmp($orientation, 'VERTICAL')==0)
		$field = "<ul class=\"reviewOptionsVertical\">";
	else
		$field = "<ul class=\"reviewOptions\">";
		
	while($lookup_r = db_fetch_assoc($lookup_results))
	{
		if($is_first_value === TRUE)
		{
			$is_first_value = FALSE;
			$total_count = (int)$lookup_r['value'];
		}

		$field .= "<li>";
		
		if($value===NULL && $lookup_r['checked_ind']=='Y')
			$field .= "\n<input type=\"radio\" class=\"radio\" name=\"$name\" value=\"".$lookup_r['value']."\" CHECKED".($disabled?' DISABLED':'').">";
		else
		{
			// Case insensitive!
			if($value!==NULL && strcasecmp($value, $lookup_r['value'])===0)
			{
				$value_found=TRUE;
				$field .= "\n<input type=\"radio\" class=\"radio\" name=\"$name\" value=\"".$lookup_r['value']."\" CHECKED".($disabled?' DISABLED':'').">";
			}
			else
			{
				$field .= "\n<input type=\"radio\" class=\"radio\" name=\"$name\" value=\"".$lookup_r['value']."\"".($disabled?' DISABLED':'').">";
			}
		}

		$field .= '<span class="starRating">';
		// Now display the images.
		for ($i=0; $i<(int)$lookup_r['value']; $i++)
			$field .= _theme_image("rs.gif");
		for ($i=0; $i<(int)($total_count-(int)$lookup_r['value']); $i++)
			$field .= _theme_image("gs.gif");
		$field .= '</span>';
		
		// now the display value.
		$field .= format_display_value($mask, $lookup_r['img'], $lookup_r['value'], $lookup_r['display']);
		$field .= "</li>";
	}
	db_free_result($lookup_results);
	
	$field .= "</ul>";
	
	return $field;
}

function process_lookup_results($lookup_results, $value)
{
	if(is_array($value) && count($value)>0)
		$values_r = $value;
	else if(!is_array($value) && $value !== NULL)// if a single string value, convert to single element array.
		$values_r[] = $value;
	else // is_empty_array!
		$values_r = NULL;

	$lookup_rs = array();
	while($lookup_r = db_fetch_assoc($lookup_results))
	{
		$lookup_rs[] = $lookup_r;
	}
	db_free_result($lookup_results);
	
	$value_found = FALSE;
	while(list(,$lookup_r) = each($lookup_rs))
	{
		if(is_array($values_r) && ($lookup_key = array_search2($lookup_r['value'], $values_r, TRUE)) !== FALSE)
		{
			$value_found = TRUE;
			break;
		}
	}
	
	$lookup_val_rs = array();
	
	reset($lookup_rs);
	while(list(,$lookup_r) = each($lookup_rs))
	{
		if($value_found) {
			$lookup_r['checked_ind'] = 'N';
		}
		
		if(is_array($values_r) && ($lookup_key = array_search2($lookup_r['value'], $values_r, TRUE)) !== FALSE)
		{
			$lookup_r['checked_ind'] = 'Y';
			
			// Remove the matched element
			array_splice($values_r, $lookup_key, 1);
		}
		
		$lookup_val_rs[] = $lookup_r;
	}

	if(is_array($values_r))
	{
		// Add the value to the list of options and select it.
		reset($values_r);
		while(list(,$value) = each($values_r))
		{
			if(strlen($value)>0)
			{
				$lookup_val_rs[] = array(value=>$value, checked_ind=>'Y');
			}
		}
	}
	
	return $lookup_val_rs;
}

/*
	Will format a ul list with the number of specified columns.
 
	@param $columns 1 for VERTICAL, * for HORIZONTAL one row, otherwise a numeric column value
	 		will build a table.

	@param $value - will not be array.
*/
function radio_grid($name, $lookup_results, $mask, $orientation, $value, $disabled = FALSE)
{
	$lookup_val_rs = process_lookup_results($lookup_results, $value);
	
	if(strcasecmp($orientation, 'VERTICAL')==0)
		$class = 'radioGridOptionsVertical';
	else
		$class = 'radioGridOptions';
		
	$buffer = "<ul class=\"$class\">";
	
	reset($lookup_val_rs);
	while(list(,$lookup_val_r) = each($lookup_val_rs))
	{
		$buffer .= "\n<li><input type=\"radio\" class=\"radio\" name=\"$name\" value=\"".$lookup_val_r['value']."\"".($lookup_val_r['checked_ind'] == 'Y'?' CHECKED':'').($disabled?' DISABLED':'').">".
				format_display_value($mask, $lookup_val_r['img'], $lookup_val_r['value'], $lookup_val_r['display'])."</li>";
	}
	
	$buffer .= "</ul>";
	
	return $buffer;
}


/*
	@param $value - array of values.
*/
function checkbox_grid($name, $lookup_results, $mask, $orientation, $value, $disabled = FALSE)
{
	$lookup_val_rs = process_lookup_results($lookup_results, $value);
	
	if(strcasecmp($orientation, 'VERTICAL')==0)
		$class = 'checkboxGridOptionsVertical';
	else
		$class = 'checkboxGridOptions';

	$buffer = "<ul class=\"$class\">";
	
	reset($lookup_val_rs);
	while(list(,$lookup_val_r) = each($lookup_val_rs))	
	{
		$buffer .= "<li><input type=\"checkbox\" class=\"checkbox\" name=\"".$name."[]\" value=\"".$lookup_val_r['value']."\"".($lookup_val_r['checked_ind'] == 'Y'?' CHECKED':'').($disabled?' DISABLED':'').">".
				format_display_value($mask, $lookup_val_r['img'], $lookup_val_r['value'], $lookup_val_r['display'])."</li>";
	}

	$buffer .= "</ul>";

	return $buffer;
}

/**
	$length restricts the value part to a specified length.  If $length===FALSE or 0 (zero) then no restriction is placed,
	damn those who pass $length as a negative number, because you get what you deserve.
*/
function single_select($name, $lookup_results, $mask, $length, $value, $onchange_event=NULL, $disabled = FALSE, $id = NULL)
{
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onchange = "onchange=\"$onchange_event\"";
	else
		$onchange = "onchange=\"$onchange_event\"";

	$lookup_val_rs = process_lookup_results($lookup_results, $value);
	
	$var = "\n<select ".($id!=NULL?"id=\"$id\"":"")." name=\"$name\" $onchange".($disabled?' DISABLED':'').">";
			
	reset($lookup_val_rs);
	while(list(,$lookup_val_r) = each($lookup_val_rs))	
	{
        // Now get the display value.
		$display = format_display_value($mask, NULL, $lookup_val_r['value'], $lookup_val_r['display']);
        
		// Ensure any length restriction is enforced.
		if($length>0 && strlen($display)>$length)
		{
			$display = substr($display, 0, $length);
		}

		$var .= "\n<option value=\"".$lookup_val_r['value']."\"".($lookup_val_r['checked_ind']=='Y'?' SELECTED':'').">$display";
	}
	
	$var.="\n</select>";
	return $var;
}

/**
	Will generate a SELECT option that allows for multiple selection, the values will be passed back to the server
	as an array matching the $name.
*/
function multi_select($name, $lookup_results, $mask, $length, $size, $value, $onchange_event=NULL, $disabled = FALSE)
{
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onchange = "onchange=\"$onchange_event\"";
	else
		$onchange = "onchange=\"$onchange_event\"";
	
	if(is_numeric($size) && $size>1)
		$var="\n<select multiple name=\"".$name."[]\" size=\"$size\" $onchange".($disabled?' DISABLED':'').">";
	else
		$var = "\n<select name=\"$name\" $onchange".($disabled?' DISABLED':'').">";

	$lookup_val_rs = process_lookup_results($lookup_results, $value);
	
	reset($lookup_val_rs);
	while(list(,$lookup_val_r) = each($lookup_val_rs))	
	{
        // Now get the display value.
		$display = format_display_value($mask, NULL, $lookup_val_r['value'], $lookup_val_r['display']);
		
		// Ensure any length restriction is enforced.
		if($length>0 && strlen($display)>$length)
		{
			$display = substr($display, 0, $length);
		}
		
		$var .= "\n<option value=\"".$lookup_val_r['value']."\"".($lookup_val_r['checked_ind']=='Y'?' SELECTED':'').">$display";
	}

	$var.="\n</select>";
	return $var;
}

/**
*/
function value_select($name, $values_r, $size, $value, $onchange_event=NULL, $disabled = FALSE)
{
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onchange = "onchange=\"$onchange_event\"";
	else
		$onchange = "onchange=\"$onchange_event\"";
			
	if(is_numeric($size) && $size>1)
		$var="\n<select multiple name=\"".$name."[]\" size=\"$size\" $onchange".($disabled?' DISABLED':'').">";
	else
		$var = "\n<select name=\"$name\" $onchange".($disabled?' DISABLED':'').">";

	while(list(,$val) = @each($values_r))
	{
		if($value!==NULL && strcasecmp(trim($value), $val)===0)
			$var .= "\n<option value=\"$val\" SELECTED>$val";
		else
			$var .= "\n<option value=\"$val\">$val";
	}

	$var .= "\n</select>";

	return $var;
}

/**
*/
function next_array_record(&$results)
{
	if(is_not_empty_array($results))
	{
		//ignore any error.
		return @each($results);
	}
	else
	{
		//ignore any error.
		return @db_fetch_assoc($results);
	}
}

/*
* Work out where in the $lookup_r the value for the $value_column
* is actually located.
* 
* NO SUPPORT FOR NUMERIC COLUMNS.  WILL NAVIGATE ARRAYS RETURNED
* FROM each() 
*/
function get_array_variable_value($lookup_r, $value_column)
{
	if(is_array($lookup_r))
	{
		// Work out what to return, based on value_column specifier.
		if($value_column == 'key')
			return $lookup_r['key'];
		else if($value_column == 'valkey')// key is actual value, but not if numeric.
		{
			// Use value, if 'key' column is auto generated numeric index.
			if(!is_array($lookup_r['value']) && is_numeric($lookup_r['key']))
				return $lookup_r['value'];
			else
				return $lookup_r['key'];
		}
		else if(!is_array($lookup_r['value']) && $value_column == 'value')
			return $lookup_r['value'];
		else if(is_array($lookup_r['value']) && isset($lookup_r['value'][$value_column]))
			return $lookup_r['value'][$value_column];
		else if(isset($lookup_r[$value_column]))
			return $lookup_r[$value_column];
	}
	
	//else
	return ''; // no value found
}

/**
	This is a simple mask processor (Used by widgets.php::custom_select(...)).  It 
	is not as advanced as the parse_title_mask functionality, because it does not 
	support mask functions (if, ifdef, elsedef), or the special mask options '.img', etc.

	@param $display_mask	The display mask with variables delimited by $variable_char.
							The variable_name must exist as a keyname in $values_r.
	@param $values_r
	@param $variable_char
*/
function expand_display_mask($display_mask, $values_r, $variable_char="%")
{
	$i = 0;
	$inside_variable = FALSE;
	$variable="";
	$value = $display_mask;

	for ($i=0; $i<strlen($display_mask); $i++)
	{
		if($inside_variable)
		{
			// If closing bracket
			if($display_mask[$i] == $variable_char && ($i==0 || $display_mask[$i-1]!= '\\'))
			{
				// Indicate close of reference.
				$inside_variable = FALSE;

				if(strlen($variable)>0)
				{
					$replace = get_array_variable_value($values_r, $variable);
					$value = str_replace($variable_char.$variable.$variable_char, $replace, $value);
					$variable = '';
				}
			}
			else
			{
				$variable .= $display_mask[$i];
			}
		}
		else if ($display_mask[$i] == $variable_char && ($i==0 || $display_mask[$i-1]!= '\\'))
		{
			$inside_variable = TRUE;	
		}
	}

	if($value!=NULL)
		return trim($value);
	else
		return NULL;
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
function custom_select(
			$name, 
			$lookup_results, 
			$display_mask, 
			$size=1, 
			$value=NULL, 
			$value_column='value', 
			$include_ind_func=NULL, 
			$checked_ind='', 
			$onchange_event='', 
			$disabled = FALSE,
			$id = NULL)
{
	// allows function to be called with an array of args, instead of individual arguments.
	if(is_array($name))
	{
		extract($name);
	}
	
	if($size !== 'NA')
	{
		if(is_numeric($size) && $size>1)
			$var = "\n<select ".($id!=NULL?"id=\"$id\"":"")." name=\"".$name."[]\" size=\"$size\" onchange=\"$onchange_event\"".($disabled?' DISABLED':'')." MULTIPLE>";
		else
			$var = "\n<select ".($id!=NULL?"id=\"$id\"":"")." name=\"$name\" onchange=\"$onchange_event\"".($disabled?' DISABLED':'').">";
	}
	else
	{
		$var = '';
	}
	
	// Must reset, otherwise 'each' will fail.
	if(is_array($lookup_results))
		reset($lookup_results);

	$empty_display_mask = expand_display_mask($display_mask, NULL, '%');
	
	$value_found=FALSE;
	while($lookup_r = next_array_record($lookup_results))
	{
		// Check if this record should be included in list of values.
		if(!function_exists($include_ind_func) || $include_ind_func($lookup_r))
		{
			$lookup_value = get_array_variable_value($lookup_r, $value_column);
			
			$display = expand_display_mask($display_mask, $lookup_r, '%');
			
			// if all variables were replaced with nothing, then assume empty option
			if(strlen($lookup_value) == 0 && $display == $empty_display_mask)
			{			
				$display = '';
			}

            if(is_array($value))
			{
				if(in_array($lookup_value, $value)!==FALSE)
					$var .= "\n<option value=\"".$lookup_value."\" SELECTED>$display";
				else
                    $var .= "\n<option value=\"".$lookup_value."\">$display";
			}
			else
			{
				if(!$value_found && $value==NULL && $lookup_r[$checked_ind]=='Y')
					$var .= "\n<option value=\"".$lookup_value."\" SELECTED>$display";
				else
				{
					if(strcasecmp(trim($value), $lookup_value) === 0)
					{
						$value_found=TRUE;
						$var .= "\n<option value=\"".$lookup_value."\" SELECTED>$display";
					}
					else
					{
						$var .= "\n<option value=\"".$lookup_value."\">$display";
					}
				}
			}
		}
	}

	if($lookup_results && !is_array($lookup_results))
		db_free_result($lookup_results);
	
	if($size !== 'NA')
	{
		$var.="\n</select>";
	}
	return $var;	
}

function format_data($field, $field_mask = NULL)
{
	if(strlen($field_mask)>0 && strpos($field_mask,"%field%")!==FALSE)
		$field = str_replace("%field%", $field, $field_mask);
	
	if(strlen($field)==0)
		$field = "&nbsp;";

	return "<td class=\"data\">$field</td>";
}

function format_prompt($prompt, $prompt_mask = NULL)
{
	if(strlen($prompt_mask)>0 && strpos($prompt_mask,"%prompt%")!==FALSE)
		$prompt = str_replace("%prompt%", $prompt, $prompt_mask);

	return "<th class=\"prompt\" scope=\"row\">$prompt:</th>";
}

function format_field($prompt, $field, $prompt_mask=NULL, $field_mask = NULL)
{
	return "\n<tr>".format_prompt($prompt, $prompt_mask).format_data($field, $field_mask)."</tr>";
}

function format_item_data_field($attribute_type_r, $field, $prompt_mask=NULL, $field_mask=NULL)
{
	$prompt = $attribute_type_r['prompt'];
	if(strlen($prompt_mask)>0 && strpos($prompt_mask, "%prompt%")!==FALSE)
		$prompt = str_replace("%prompt%", $prompt, $prompt_mask);
		
	if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE && 
			$attribute_type_r['compulsory_ind'] == 'Y')
	{
		if(strlen($prompt_mask)==0)
			$prompt_mask = '%prompt%';
			
		$prompt .=	_theme_image("compulsory.gif", get_opendb_lang_var('compulsory_field'));
	}
	
	if(strlen($field_mask)>0 && strpos($field_mask,"%field%")!==FALSE)
		$field = str_replace("%field%", $field, $field_mask);
	
	$fieldid = strtolower(str_replace('_', '-', $attribute_type_r['s_attribute_type']).'-'.$attribute_type_r['order_no']);
	$fieldclass = strtolower(str_replace('_', NULL, $attribute_type_r['s_attribute_type']));
	
	return "\n<tr id=\"$fieldid\">".
				"<th class=\"prompt\" scope=\"row\">$prompt:</th>".
				"<td class=\"data $fieldclass\">".ifempty($field,'&nbsp;')."</td>".
			"</tr>";
}

/**
	This script will contain all the functions associated with the item attributes
	This will include accessors for the s_item_type, s_attribute_type, s_attribute_type_lookup
	and s_item_attribute_type tables.
	* 
	This function will also support specifying a $order_no of FALSE, so we can get a fieldname
	without the _$order_no
*/
function get_field_name($s_attribute_type, $order_no=NULL)
{
	if(is_numeric($order_no))
		return strtolower($s_attribute_type)."_".$order_no;
	else
		return strtolower($s_attribute_type);
}

/**
	Given a fieldname, it will return an associative array containing
	type=>
	order_no=>
*/
function get_attribute_and_order_no($fieldname)
{
	// We have to look for the last "_", and check if the value
    // after this is a number.  For now we will not bother checking that it
    // is a valid order_no, but just that it is a number.
    $idx = strrpos($fieldname, "_");
    if($idx !== FALSE)
    {
    	$type = substr($fieldname, 0, $idx);
        $order_no = substr($fieldname, $idx+1, strlen($fieldname)-$idx);
	    if(!is_numeric($order_no))
        	$order_no = null;
    }
    else
    	$type = $fieldname;
    
    // Return array now.
    return array("type"=>$type, "order_no"=>$order_no);
}

/**
	Convert $args array into equivalent Javascript array statement.
*/
function encode_javascript_array($args)
{
	$buf="";
	for ($i=0; $i<count($args); $i++)
	{
		if(strlen($buf)>0)
			$buf .= ", '".addslashes($args[$i])."'";
		else
			$buf = "'".addslashes($args[$i])."'";
	}
	
	return "new Array($buf)";			
}



/**
	$display_type can have the following values:
		list(list_type [,delimiter])
						Will split text according to delimiter (or newline of not specified)
						and format based on list_type.  If the 'list-link' is specified,
						then will add a s_attribute_type listing link to each item.

		datetime(datetime_mask)
		 				Format the value entered via the 'datetime' input field according to the
		 				display mask, which supports the following mask elements:
							Month - Month name
					 		Mon - Abbreviated month, Initcap.
 					 		MON - Abreviated month UPPERCASE
			 				Day	- Weekday name
			 				DDth - Day of the month with English suffix (1st, 2nd, 3rd)
			 				DD - Days (01 - 31)
			 				MM - Months (01 -12)
			 				YYYY - Years
			 				HH24 - Hours (00 - 23)
			 				HH - Hours (01 - 12)
			 				MI - Minutes (00 - 59)
			 				SS - Seconds (00 - 59)
			 				AM/PM - Meridian indicator  (Will be replaced with the actual context meridian value!)		

		format_mins(display_mask)		
						Expects a int value of total minutes, and will format it according to 
						the specified mask.  The default mask is: "%h %H %m %M"
				
							%h - hour value only
						    %H - text "hour" or "hours"
						    %m - minute value only
						    %M - text "minute" or "minutes"
	
						In the case where an hour value is not available, because the total 
						minutes is less than 60, then everything in the mask before the %m
						or %M will be ignored.
		
		display(display_mask)
						This is a new function to allow the inclusion of a list-link for what would
						have originally been specified as a %display% or %value% type mask, without
						functional reference.  Now you can specify the display mask and an optional
						list-link argument.  This is the preferred means of specifying a display
						type for a field, even if the list-link argument is not used.

		category(display_mask)
						Special 'CATEGORY' display_type.  Will split the category values according to 
						matches in s_attribute_type_lookup table and display as 'category / category' etc.

		review([display_mask])
						Display a single Review value, which will consist of a set of stars, and any
						s_attribute_type_lookup columns for the matching value.  This option is not
						available for use with s_attribute_type's
		 
		hidden			Allows users to disable display of fields that were, either previously automatically
						populated by a site plugin, or were originally display attributes that are now
						no longer required.

		For display_mask:
			Options are %img%, %value%, %display% variables.

		Options for list_type are:
				plain
				nl2br
				ordered
				unordered
				ticks
*/

//stub
function get_display_field($s_attribute_type, $prompt, $display_type, $value, $dowrap=TRUE, $prompt_mask=NULL)
{
	$display_type_def = prc_function_spec($display_type);
	
	return get_item_display_field(
		NULL, // item_r
		array(
			's_attribute_type'=>$s_attribute_type, 
			'prompt'=>$prompt, 
			'display_type'=>$display_type_def['type'],
			'display_type_arg1'=>$display_type_def['args'][0],
			'display_type_arg2'=>$display_type_def['args'][1],
			'display_type_arg3'=>$display_type_def['args'][2],
			'display_type_arg4'=>$display_type_def['args'][3],
			'display_type_arg5'=>$display_type_def['args'][4]),
		$value,
		$dowrap,
		$prompt_mask);
}

function get_item_display_field(
		$item_r,
		$item_attribute_type_r,
		$value=NULL,
		$dowrap=TRUE,
		$prompt_mask=NULL)
{
	if($item_attribute_type_r['display_type'] == 'hidden')
	{
		// Do nothing.
		return '';
	}
	else if($item_attribute_type_r['display_type'] == 'fileviewer')
	{
		$value = trim($value);

		$format_mask = ifempty($item_attribute_type_r['display_type_arg1'], '%value%');
		$width = ifempty($item_attribute_type_r['display_type_arg2'], '400');
		$height = ifempty($item_attribute_type_r['display_type_arg3'], '300');

		if(strpos($format_mask, '%img%')!==FALSE)
		{
			$file_type_r = fetch_file_type_r(fetch_file_type_for_extension(get_file_ext($value)));
			
			if(strlen($file_type_r['image'])>0 && ($image_src = _theme_image_src($file_type_r['image']))!==FALSE)
				$img = '<img src="'.$image_src.'" title="'.$value.'">';
			else
				$img = '';				
		
			$format_mask = str_replace('%img%', $img, $format_mask);
		}

		if(strpos($format_mask, '%value%')!==FALSE)
		{
			$format_mask = str_replace('%value%', $value, $format_mask);
		}

//		if(is_url_absolute($value))
//		{
			$field = "<a href=\"".$value."\" onclick=\"popup('url.php?url=".urlencode($value)."' ,'".($width+20)."', '".($height+25)."'); return false;\" title=\"".$item_attribute_type_r['prompt']."\" class=\"popuplink\">$format_mask</a>";
			
			if($dowrap)
				return format_field($item_attribute_type_r['prompt'], $field, $prompt_mask);
			else
				return $field;
//		}
//		else
//		{
//			return $value;
//		}
	}
	else if($item_attribute_type_r['display_type'] == 'list')	//list(list_type [,delimiter])
	{
		if(is_array($value))
		{
			$values = $value;
			$attr_match = 'exact';
		}
		else
		{
			$value = trim($value);

			if(strlen($item_attribute_type_r['display_type_arg2'])==0) // Use newline!
			{
				$values = explode_lines($value);
				$attr_match = 'partial';
			}
			else
			{
				$values = explode($item_attribute_type_r['display_type_arg2'], $value);

				if(strlen(trim($item_attribute_type_r['display_type_arg2']))===0)
					$attr_match = 'word';
				else
					$attr_match = 'partial';
			}
		}

		$field = format_list_from_array($values, $item_attribute_type_r, $item_attribute_type_r['listing_link_ind']=='Y'?$attr_match:FALSE);
		if($dowrap)
			return format_field($item_attribute_type_r['prompt'], $field, $prompt_mask);
		else
			return $field;
	}
	else if($item_attribute_type_r['display_type'] == 'datetime')
	{
		$value = trim($value);

		$timestamp = get_timestamp_for_datetime($value, 'YYYYMMDDHH24MISS');
		if($timestamp !== FALSE)
		{
			if(strlen($item_attribute_type_r['display_type_arg1'])==0)
				$item_attribute_type_r['display_type_arg1'] = 'DD/MM/YYYY';

			$datetime = get_localised_timestamp($item_attribute_type_r['display_type_arg1'], $timestamp);
			if($datetime!==FALSE)
				$field = $datetime;
			else
				$field = $value;
		}
		else
		{
			$field = $value;
		}
		
		if($dowrap)
			return format_field($item_attribute_type_r['prompt'], $field, $prompt_mask);
		else
			return $field;
	}
	else if($item_attribute_type_r['display_type'] == 'format_mins')
	{
		$time_value=trim($value);// time display
		if( is_numeric($time_value) )
		{
			// Ensure we have a mask to work with.
			$display_mask = $item_attribute_type_r['display_type_arg1'];
			if(strlen($display_mask)==0)
				$display_mask = '%h %H %m %M';

			$hrs = floor($time_value/60); // hours
			$mins = $time_value%60;	// minutes

			// Process display_mask and remove any bits that are not needed because the hour/minute is zero.
			if($mins == 0 && $hrs > 0) // only get rid of minutes if $hrs is a value.
			{
				$index = strpos($display_mask, '%H');
				if($index !== FALSE)
					$display_mask = substr($display_mask, 0, $index+2);//include the %H
				else
				{
					$index = strpos($display_mask, '%m');
					if($index!=FALSE)
						$display_mask = substr($display_mask, 0, $index);//include the %H
				}
			}
			else if($hrs == 0)
			{
				$index = strpos($display_mask, '%m');
				if($index!=FALSE)
					$display_mask = substr($display_mask, $index);//include the %H
			}

			// Unfortunately we need to do $mins>0 and $hrs>0 if's twice, because otherwise once we
			// replace the %h and %H the test for $mins>0 would not be able to cut the display_mask,
			// based on the %h/%H...
			if($hrs>0)
			{
				// Now do all replacements.
				$display_mask = str_replace('%h',$hrs,$display_mask);
				if($hrs!=1)
					$display_mask = str_replace('%H',get_opendb_lang_var('hours'),$display_mask);
				else
					$display_mask = str_replace('%H',get_opendb_lang_var('hour'),$display_mask);
			}

			if($mins>=0 || ($hrs===0 && $mins===0))
			{
				// Now do minute replacements only.
				$display_mask = str_replace('%m',$mins,$display_mask);
				if($mins!=1)
					$display_mask = str_replace('%M',get_opendb_lang_var('minutes'),$display_mask);
				else
					$display_mask = str_replace('%M',get_opendb_lang_var('minute'),$display_mask);
			}

			// Now return mask with parts of value inserted.
			if($dowrap)
				return format_field($item_attribute_type_r['prompt'], $display_mask, $prompt_mask);
			else
				return $display_mask;
		}
		else
		{
			// what else can we do here?!
			if($dowrap)
				return format_field($item_attribute_type_r['prompt'], $time_value, $prompt_mask);
			else
				return $time_value;
		}
	}
	else if($item_attribute_type_r['display_type'] == 'review')
	{
		$total_count = fetch_attribute_type_cnt('S_RATING');
		if(is_numeric($total_count))
		{
			$value = trim($value);
			if(!is_numeric($value))
			{
				$value = 0;
			}
			$field = '';
			$j = $value;
			for($i=0;$i<$total_count;++$i)
			{
				if($j >= 0.75)
					$field .= _theme_image('rs.gif');
				else if ($j >=0.25)
					$field .= _theme_image('rgs.gif');
				else
					$field .= _theme_image('gs.gif');
				$j = $j - 1;
			}
			
			// If a mask is defined, format the display value.
			if(strlen($item_attribute_type_r['display_type_arg1'])>0)
			{
				$lookup_r = fetch_attribute_type_lookup_r('S_RATING', $value);
				if(is_not_empty_array($lookup_r))
				{
					$field .= format_display_value(
								$item_attribute_type_r['display_type_arg1'], 
								$lookup_r['img'],
								$lookup_r['value'],
								$lookup_r['display']);
				}
			}
			return $field; // this is only used in a few places.
		}
	}
	else if($item_attribute_type_r['display_type'] == 'star_rating') // arg[0] = rating range
	{
		$value = trim($value);

		// no point unless numeric
		if(is_numeric($value))
		{
			$total_count = $item_attribute_type_r['display_type_arg1'];
			if(is_numeric($total_count))
			{
				$field = '';
				$j = $value;
				for($i=0;$i<$total_count;++$i)
				{
					if($j >= 0.75)
						$field .= _theme_image('rs.gif');
					else if ($j >=0.25)
						$field .= _theme_image('rgs.gif');
					else
						$field .= _theme_image('gs.gif');
					$j = $j - 1;
				}

				$ratingmask = $item_attribute_type_r['display_type_arg2'];
				if(strlen($ratingmask)>0)
				{
					$ratingmask = str_replace('%value%', $value, $ratingmask);
					$ratingmask = str_replace('%maxrange%', $total_count, $ratingmask);
					$field = str_replace('%starrating%', $field, $ratingmask);
				}

				if($item_attribute_type_r['listing_link_ind'] == 'Y')
				{
					$field = format_listing_link($value, $field, $item_attribute_type_r, NULL);
				}

				if($dowrap)
					return format_field($item_attribute_type_r['prompt'], $field, $prompt_mask);
				else
					return $field;
			}
		}
		else
		{
			return ''; // nothing to do!
		}
	}
	else if(!is_array($value) && $item_attribute_type_r['display_type'] == 'display' && 
									ifempty($item_attribute_type_r['display_type_arg1'], '%value%') == '%value%')
	{
		// Support newline formatting by default.
		$value = nl2br(trim($value));
		
		if($item_attribute_type_r['listing_link_ind'] == 'Y')
			$field = format_listing_links($value, $item_attribute_type_r, 'word');
		else
			$field = $value;
		
		if($dowrap)
			return format_field($item_attribute_type_r['prompt'], $field, $prompt_mask);
		else
			return $field;
	}
	else if($item_attribute_type_r['display_type'] == 'category' || $item_attribute_type_r['display_type'] == 'display')
	{	
		$field = '';
		
		if(is_array($value))
			$value_array = $value;
		else
		    $value_array[] = $value;
		
		$attribute_value_rs = array();
		
		if($item_attribute_type_r['lookup_attribute_ind'] == 'Y')
		{
			$results = fetch_value_match_attribute_type_lookup_rs($item_attribute_type_r['s_attribute_type'], $value_array, get_lookup_order_by($item_attribute_type_r['display_type_arg1']), 'asc');
			if($results)
			{
				while($lookup_r = db_fetch_assoc($results))
				{
					$lookup_key = array_search2($lookup_r['value'], $value_array, TRUE);
					if($lookup_key !== FALSE)
					{
						// Remove the matched element
						array_splice($value_array, $lookup_key, 1);
						
						$attribute_value_rs[] = array(value=>$lookup_r['value'], display=>$lookup_r['display'], img=>$lookup_r['img']);
					}						
				}
				db_free_result($results);
			}
		}
		
		// where extra items that do not have a matching lookup value.
		if(is_not_empty_array($value_array))
		{
			reset($value_array);
			while(list(,$value) = each($value_array))
			{
				if(strlen(trim($value))>0) // In case there are extra spaces
				{
					$attribute_value_rs[] = array(value=>$value, display=>$value);
				}
			}
		}
		
		if(is_not_empty_array($attribute_value_rs))
		{
			$field = format_lookup_display_block($item_attribute_type_r, $attribute_value_rs);
			if(strlen($field)>0)
			{
				// $var would be empty, if we had not been inside while and inner if!
				if($dowrap)
					return format_field($item_attribute_type_r['prompt'], $field, $prompt_mask);
				else
					return $field;
			}
			else
			{
				return NULL;				
			}
		}
	}

   	//else -- no display type match.
   	if($dowrap)
		return format_field($item_attribute_type_r['prompt'], nl2br($value), $prompt_mask);
	else
		return nl2br($value);
}

/*
* Used exclusively by get_display_field
*/
function format_lookup_display_block($item_attribute_type_r, $attribute_value_rs)
{
	$block = '';
	
	$first = TRUE;
	while(list(,$attribute_value_r) = each($attribute_value_rs))
	{
		$display_value = format_lookup_display_field($item_attribute_type_r, $attribute_value_r);
		
		$block .= '<li'.($first?' class="first"':'').'>'.$display_value.'</li>';
		
		if($first)$first = FALSE;
	}

	if(strlen($block)>0)
	{
		$class = '';
		
		if($item_attribute_type_r['display_type'] == 'category')
		{
			$class = 'category';
		}
		
		if(count($attribute_value_rs)==1)
		{
			if(strlen($class)>0)
				$class .= ' ';
				
			$class .= 'single';
		}
			
		return '<ul'.(strlen($class)>0?' class="'.$class.'"':'').'>'.$block.'</ul>';
	}
	else
	{
		return NULL;
	}
}

function format_lookup_display_field($item_attribute_type_r, $attribute_value_r)
{
	$display_value = format_display_value($item_attribute_type_r['display_type_arg1'], $attribute_value_r['img'], $attribute_value_r['value'], $attribute_value_r['display']);

	// Add listings.php link if required.
	if($item_attribute_type_r['listing_link_ind'] == 'Y')
	{
		$display_value = format_listing_link($attribute_value_r['value'], $display_value, $item_attribute_type_r, $item_attribute_type_r['display_type']=='category'?'category':'exact');
	}
	return $display_value;	
}

/**
	Will return an array of links.

	@value Can be an array or a single value.
*/
function format_listing_links($value, $item_attribute_type_r, $attr_match)
{
	if(is_array($value))
		$tokens = $value;
	else
		$tokens[] = $value;
		
	while(list(,$token) = @each($tokens))
	{
		$token = trim($token);
		$lines[] = format_listing_link($token, $token, $item_attribute_type_r, $attr_match);
	}
	
	// If no array passed in, then pass back normal string!
	if(is_array($value))
		return $lines;
	else
		return $lines[0];
}

/**
	$attr_match
		word		A '= $value match' OR 'LIKE % $value% ' OR 'LIKE '%$value ' OR 'LIKE '% $value%'
		exact		A '= "$value match"'
		partial		A 'LIKE %$value%' match
		category	listings will handle this special type, by linking against item.category instead
		of the item_attribute.attribute_val...
*/
function format_listing_link($value, $display, $item_attribute_type_r, $attr_match)
{
	// The % cannot exist in a database column, whereas the '_' can.  This is
	// why we only need to escape the _.  We escape it by specifying it twice!
	$value = trim(str_replace("_", "\\_", $value));
	
	// If any whitespace, then enclose with quotes, otherwise will be treated by boolean parser as 
	// separate words, which is not desirable.
	if($attr_match!='exact' && strpos($value, " ")!==FALSE)
		$value = urlencode("\"".$value."\"");
	else
		$value = urlencode($value);
	
	return "<a href=\"listings.php?attribute_list=y&attr_match=$attr_match&attribute_type=".$item_attribute_type_r['s_attribute_type']."&s_status_type=ALL&attribute_val=".$value."&order_by=title&sortorder=ASC\" title=\"".get_opendb_lang_var('list_items_with_same_prompt', 'prompt', $item_attribute_type_r['prompt'])."\" class=\"listlink\">$display</a>";
}

/**
	@param $tokens	Expects $tokens to be an array otherwise will return empty string.

	@param $attr_match
					Specified $attr_match type for list-link.  If FALSE, then no list-link
					to be added.
					
	$args[0] = type
	$args[1] ... $args[3] = list args					
*/
function format_list_from_array($tokens, $item_attribute_type_r, $attr_match=FALSE)
{
	if(is_not_empty_array($tokens))
	{
		$first = TRUE;
		$value = '';
		while(list(, $token) = each($tokens))
		{
			if($attr_match!==FALSE)		
				$token = format_listing_link($token, $token, $item_attribute_type_r, $attr_match);
			
			$value .= '<li';
			
			if($first)
			{
				$value .= ' class="'.($first?'first':'').'"';
			}
			$value .= '>'.$token.'</li>';

			if($first)$first = FALSE;
		}	
		
		$list_type = $item_attribute_type_r['display_type_arg1'];
		
		$class = $list_type;
		
		if(count($tokens)==1)
		{
			$class .= ' single';	
		}
		
		if($list_type == 'ordered')
		{
			return '<ol class="'.$class.'">'.$value.'</ol>';
		}
		else // plain, unordered, nl2br, ticks, names
		{
			return '<ul class="'.$class.'">'.$value.'</ul>';
		}
	}
}

/**
	Based on the $display_type mask, will format the display text for 
    the combination of $img, $value and $display columns.
    
    This can also be used for input fields where a display component
    is required to be custom.  for fields such as single_select, do
    not pass the $img, and it will work as expected. 
    
    If $img === "none" then we will replace %img% with empty string.  This is
	in contrast to the default functionality of replacing the %img% with %value%
	if a particular record from s_attribute_type_lookup does not specify an
	img value.
	
	If a img value is specified, but it cannot be resolved to a theme_image,
	the display will be used.  This is ac
*/
function format_display_value($mask, $img, $value, $display, $theme_image_type=NULL)
{
   	// The default.
   	if(strlen($mask)==0)
       	$mask = "%display%";

	// Note: We are only modifying local copy of $mask for return.
	if(strlen(trim($img))>0 && $img!=="none")
	{
		$image = _theme_image($img, $display, $theme_image_type);
		if(strlen($image)>0)
			$mask = str_replace("%img%", $image, $mask);
		else if(strlen($display)>0)
			$mask = str_replace("%img%", $display, $mask);
		else
			$mask = str_replace("%img%", $value, $mask);
	}
	else if($img === "none") // A image value with "none" indicates we should replace image with empty string.
	{
		$mask = str_replace("%img%", "", $mask);
	}
	else
	{
		// If no %display% mask variable, replace missing image with display field instead.
		if(strpos($mask, '%display%') === FALSE)
		{
			$mask = str_replace("%img%", $display, $mask);
		}
		else if(strpos($mask, '%value%') === FALSE && strcmp($value, $display) !== 0) // but only if display is NOT the same as value
		{
			$mask = str_replace("%img%", $value, $mask);
		}
		else
		{
			$mask = str_replace("%img%", "", $mask);
		}
	}

	$mask = str_replace("%display%", $display, $mask);
	$mask = str_replace("%value%", $value, $mask);
    
    return $mask;       
}

function get_op_confirm_form($PHP_SELF, $confirm_message, $HTTP_VARS)
{
	$formContents = "\n<form class=\"confirmForm\" action=\"$PHP_SELF\" method=\"POST\">";
		
	$formContents .= 
		"<p>".$confirm_message."</p>".
		get_url_fields($HTTP_VARS, NULL, array('confirmed')). // Pass all http variables
		"<fieldset>".
		"<label for=\"confirm_yes\">".get_opendb_lang_var('yes')."</label>".
		"<input type=\"radio\" class=\"radio\" name=\"confirmed\" id=\"confirm_yes\" value=\"true\">".
		"<label for=\"confirm_no\">".get_opendb_lang_var('no')."</label>".
		"<input type=\"radio\" class=\"radio\" name=\"confirmed\" id=\"confirm_no\" value=\"false\" CHECKED>".
		"</fieldset>".
		"<input type=\"submit\" class=\"submit\" value=\"".get_opendb_lang_var('submit')."\">".
	"</form>\n";

	return $formContents;
}

/**
	Displays the footer links on a page.
*/
function format_footer_links($links_rs)
{
	$field = NULL;
	
	if(is_array($links_rs) && isset($links_rs['url']))
		$footer_links_rs[] = $links_rs;
	else
		$footer_links_rs =& $links_rs;
		
	if(is_array($footer_links_rs))
	{
		$field = "<ul class=\"footer-links\">";
		
		$first = TRUE;
		while(list(,$footer_links_r) = @each($footer_links_rs))
		{
			$field .= "<li";
			if($first)
			{
				$first=FALSE;
				$field .= " class=\"first\"";
			}
			
			$field .= "><a";
			if(strlen($footer_links_r['url'])>0)
			{
				$field .= " href=\"".$footer_links_r['url']."\"";
			}
			
			if(strlen($footer_links_r['onclick'])>0)
			{
				$field .= " onclick=\"".$footer_links_r['onclick']."\" ";	
			}
			
			if(starts_with($footer_links_r['target'], 'popup'))
			{
				$spec = prc_function_spec($footer_links_r['target']);
	
				if(!is_array($spec['args']))
				{
					$spec['args'][0] = '800';
					$spec['args'][1] = '600';
				}
	
				$field .= " onclick=\"popup('".$footer_links_r['url']."','".$spec['args'][0]."','".$spec['args'][1]."'); return false;\">";
			}
			else 
			{
				$field .= (strlen($footer_links_r['target'])>0?"target=\"".$footer_links_r['target']."\"":"").">";
			}
			
			if(strlen($footer_links_r['img'])>0)
			{
				$field .= '<img src="'.$footer_links_r['img'].'" title="'.$footer_links_r['text'].'">';
			}
	
			// don't allow wrapping of link
			$field .= $footer_links_r['text']."</a>";
	
			$field .= "</li>";
		}
		
		$field .= "</ul>";
	}
	return $field;
}

/*
* 	Format of a help entry:
*
* 		array('Text', 'Text', 'Text'
* 	Or:
* 		array('img'=>image, 'text'=>'Text')
* 	Or:
* 		array('text'=>array('text', 'text', 'text'))
*	Or:
*		Text
* 
*	Expects the 'img' element to be an unexpanded theme image.
*/
function _format_help_entry($help_entry_r)
{
	if(is_array($help_entry_r))
	{
		if(isset($help_entry_r['img']))
			$entry .= _theme_image($help_entry_r['img'], $help_entry_r['text'])." ";
		$entry .= $help_entry_r['text'];
		
		return $entry;
	}
	else
	{
		return $help_entry_r;
	}
}

function _format_help_list($help_entry)
{
	$field = '';
	
	if(is_array($help_entry) && !isset($help_entry['text']))
	{
		while(list($key,$entry) = each($help_entry))
		{
			$field .= _format_help_list($entry);
		}
	}
	else
	{
		$field .= "\n<li>"._format_help_entry($help_entry)."</li>";
	}
	
	return $field;
}

function format_help_block($help_entries_rs)
{
	if(!is_array($help_entries_rs) && strlen($help_entries_rs)>0)
		$entries[] = array( array('text'=>$help_entries_rs) );
	else if(is_array($help_entries_rs) && isset( $help_entries_rs['text'] ))
		$entries[] = array($help_entries_rs);
	else if(is_array($help_entries_rs))
		$entries[] =& $help_entries_rs;
		
	if(is_array($entries))
	{
		return "\n<ul class=\"help\">".
			_format_help_list($entries).
			"</ul>\n";
	}
	else
	{
		return NULL;
	}
}

/*
* If _theme_image returns NULL / FALSE, this indicates that the default
* Action links functionality should be used.  Otherwise a valid Image link
* will be returned for each action.  The situation where some images
* are handled and others are not, will be handled as well, although
* it is not an ideal situation.
* 
* @param wrap_mask - If not null, the action links will be inserted into
* the $wrap_mask, before being returned.  If a %nowrap% variable is present,
* and all action links were handled with _theme_image, then the word 'nowrap'
* will replace %nowrap%, otherwise an empty string will be used.
* 
* @param ifempty - If FALSE, then return NULL.  This also means that the $wrap_mask
* will be ignored in this case as well.  However if $ifempty has a non-FALSE value,
* (even NULL), the $wrap_mask will still be expanded. 
*/
function format_action_links($action_links_rs)
{
	$field = '';
	$first = TRUE;
	while(list(,$action_link_r) = @each($action_links_rs))
	{
		if(strlen($action_link_r['img'])>0)
			$action_image = _theme_image('action_'.$action_link_r['img'], $action_link_r['text'], "action");
		else
			$action_image = FALSE;
		
		$field .= "<li class=\"".($first?'first':'')."\"><a href=\"".$action_link_r['url']."\">";
		if($first)$first = FALSE;
		
		// Either strlen($action_link_r['img'])==0 or theme specific theme_image does not want images for actions, and
		// returned NULL as a result.
		if($action_image!==FALSE && strlen($action_image)>0)
		{
			$field .= $action_image;
		}
		else
		{
			$field .= $action_link_r['text'];
		}
		
		$field .= '</a></li>';
	}
	
	if(strlen($field)>0)
	{
		return "<ul class=\"action-links\">".$field."</ul>";
	}
	else
	{
		return NULL;
	}
}

/**
 * @param unknown_type $cbname
 * @param unknown_type $not_checked_message
 * @param unknown_type $action_links_rs
 * @return unknown
 */
function format_checkbox_action_links($cbname, $not_checked_message, $action_links_rs)
{
	$field = "<ul class=\"checkbox-action-links\">";
	
	$first = TRUE;
	
	@reset($action_links_rs);
	while(list(,$action_links_r) = @each($action_links_rs))
	{
		$field .= "<li class=\"".($first?'first':'')."\">";
		
		if($action_links_r['checked']!==FALSE)
		{
			$field .= "<a href=\"#\" onclick=\"if(isChecked(document.forms['".$cbname."'], '".$cbname."[]')){doFormSubmit(document.forms['".$cbname."'], '".$action_links_r['action']."', '".$action_links_r['op']."');}else{alert('".$not_checked_message."');} return false;\">".$action_links_r['link']."</a>";
		}
		else
		{
			$field .= "<a href=\"".$action_links_r['action']."?op=".$action_links_r['op']."\">".$action_links_r['link']."</a>";
		}
		$field .= "</li>";
		
		if($first)$first = FALSE;
	}
	
	$field .= "</ul>";

	return $field;
}

/*
* @param $errors an array of errors.  The format of
* each error entry is:
* 	error=>'Main error'
* 	detail=>'Details of error, db_error, etc'
* @param $msg_type Indicates the type of error, which might be one
* 		of:
* 			error
* 			warning
* 			information
*/
function format_error_block($errors, $err_type = 'error')
{
	switch($err_type)
	{
		case 'error':
			$class = 'error';
			$smclass = 'smerror';
			break;
		
		case 'smerror':
			$class = 'smerror';
			$smclass = 'smerror';
			break;
			
		case 'warning': // If it becomes necessary, new CSS style classes will be introduced.
		case 'information':
		default:
			$class = 'smsuccess';
			$smclass = 'footer';
	}
	
	if(!is_array($errors))
	{
		if(strlen(trim($errors))==0)
			return NULL;
		else				
			$error_rs[] = array('error'=>$errors,'detail'=>'');
	}
	else if(isset($errors['error']))
		$error_rs[] = $errors;
	else
		$error_rs = $errors;
	
	$error_entries = NULL;
	while(list(,$error) = each($error_rs))
	{
		if(is_not_empty_array($error))
		{
			$error_entry = $error['error'];
		
			if(!is_array($error['detail']) && strlen($error['detail'])>0)
				$detail_rs[] = $error['detail'];
			else if(is_array($error['detail']))
				$detail_rs = $error['detail'];
		
			if(is_not_empty_array($detail_rs))
			{
				$details = "";
				while(list(,$detail) = each($detail_rs))
				{
					$details .= "\n<li class=\"$smclass\">".$detail."</li>";
				}
			
				if(strlen($details)>0)
					$error_entry .= "\n<ul>".$details."</ul>";
			}
		}
		else
		{
			$error_entry = $error;
		}
		
		$error_entries[] = $error_entry;
	}
	
	if(count($error_entries)>1)
	{
		$error_block = "\n<ul>";
		while(list(,$error_entry) = each($error_entries))
		{
			$error_block .= "\n<li class=\"$class\">$error_entry</li>";
		}
		$error_block .= "</ul>";
		
		return $error_block;
	}
	else if(count($error_entries)==1)
	{
		return "\n<p class=\"$class\">".$error_entries[0]."</p>";
	}
	else
	{
		return NULL;
	}
}

/**
	Will fetch the image block for a particular s_item_type.  Will
	use $s_item_type if specified, otherwise will get s_item_type
	for $item_id first.
*/
function get_item_image($s_item_type, $item_id = NULL)
{
	if(strlen($s_item_type)>0 || ($s_item_type = fetch_item_type($item_id)))
	{
		$item_type_r = fetch_item_type_r($s_item_type);
		if(is_array($item_type_r))
		{
			// default
			$imagetext = $s_item_type;

			// Get image block.
			if(strlen($item_type_r['image'])>0)
			{
				if(strlen($item_type_r['description'])>0)
					$title_text = htmlspecialchars($item_type_r['description']);
				else
					$title_text = NULL;

				$imagetext = _theme_image($item_type_r['image'], $title_text, 's_item_type');
			}

			return $imagetext;
		}
	}
	
	//else
	return FALSE;
}
?>