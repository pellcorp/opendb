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
// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/logging.php");

include_once("./functions/item_attribute.php");
include_once("./functions/widgets.php");
include_once("./functions/utils.php");
include_once("./functions/item_type.php");
include_once("./functions/item.php");
include_once("./functions/user.php");
include_once("./functions/import.php");
include_once("./functions/importcache.php");
include_once("./functions/parseutils.php");
include_once("./functions/scripts.php");
include_once("./functions/ItemImportHandler.class.inc");
include_once("./functions/HTML_Listing.class.inc");

function get_item_id_range($item_id_r)
{
	$item_id_range = '';
	$start_item_id = NULL;
	$last_item_id = NULL;
	if(is_array($item_id_r))
	{
		for($i=0; $i<count($item_id_r); $i++)
		{
			$new_id = $item_id_r[$i];
			
			if($last_item_id !== NULL)
			{
				// If the new_id, has jumped a number, we need to close range, and start again
				if( ($last_item_id+1) < $new_id)
				{
					// If we actually have a range, of at least one.
					if($start_item_id < $last_item_id)
					{
						$item_id_range .= $start_item_id.'-'.$last_item_id.',';
					}
					else if(is_numeric($start_item_id))
					{
						$item_id_range .= $start_item_id.',';
					}
					$start_item_id = $new_id;
				}
				$last_item_id = $new_id;
			}
			else
			{
				$start_item_id = $new_id;
				$last_item_id = $new_id;
			}
		}
	}
	
	// Do final 
	if($start_item_id < $last_item_id)
	{
		$item_id_range .= $start_item_id.'-'.$last_item_id;
	}
	else if(is_numeric($start_item_id))
	{
		$item_id_range .= $start_item_id;
	}
	
	return $item_id_range;
}

/**
*/
function get_column_select_block($fieldname, $lookup_array, $selectedindex)
{
	// So we can access the language variables.
	
	$var="\n<select name=\"$fieldname\">";
	$var.="\n<option value=\"\">-- ".get_opendb_lang_var('none')." --";
	for ($i=0; $i<count($lookup_array); $i++)
	{
		$var .= "\n<option ".(is_numeric($selectedindex) && $selectedindex==$i?"SELECTED":"")." value=\"$i\">".$lookup_array[$i];
	}
	$var.="\n</select>";
	return $var;
}

/**
	We are using this instead of the particular input types, so
	that users can clearly see what the %value% and %display%
	columns are for the lookups, so they can match them in
	their import file.
*/
function build_select($name, $lookup_results, $value, $include_none_option=TRUE)
{	
	// If at least one $lookup_r['value'] is different from $lookup_r['display'] set this variable to true.
	$display_used=FALSE;
	
	$var="\n<select name=\"$name\">";
	if($include_none_option)
		$var.="\n<option value=\"\">-- ".get_opendb_lang_var('none')." --";
		
	while($lookup_r = db_fetch_assoc($lookup_results))
	{
		if($lookup_r['value'] === $lookup_r['display'])
			$display = $lookup_r['value'];
		else{
			$display = $lookup_r['value']." - ".$lookup_r['display'];
			if(!$display_used)
				$display_used=TRUE;
		}

		$var .= "\n<option value=\"".$lookup_r['value']."\"";

		// Only support checked_ind, where we are not displaying a None option.  If None option, it should
		// be chosen by default.		
		if( ($include_none_option!==TRUE && strlen($value)==0 && $lookup_r['checked_ind']=="Y") || 
					(strlen($value)>0 && strcasecmp($value,$lookup_r['value'])===0))
		{
			$var .= " SELECTED";
		}
		
		$var .= ">$display";
	}
	db_free_result($lookup_results);
	
	$var.="\n</select>";

	if($display_used)
		$var.="<br>(%value% - %display%)";
	else
		$var.="<br>(%value%)";
	return $var;
}

/**
*/
function get_row_column_mappings_table($s_item_type, $owner_id, $header_row, $field_column_r, $field_default_r, $field_initcap_r)
{
	$buffer = "\n<table>";
	$buffer .= "\n<tr class=\"navbar\"><th></th>".
				"<th>".get_opendb_lang_var('column')."</th>".
				"<th>".get_opendb_lang_var('default')."</th>".
				"<th> ".get_opendb_lang_var('initcap')." </th></tr>";
	
	// If the prompt for this field, matches any of the $tokens, then select that token.
	if(!is_numeric($field_column_r['s_item_type']))
	{
		for($i=0; $i<count($header_row); $i++)
		{
			if($header_row[$i] == 's_item_type' || $header_row[$i] == get_opendb_lang_var('s_item_type'))
			{
				$field_column_r['s_item_type'] = $i;
				break;
			}
		}
	}
							
	// If your data includes the item_type, then specify the column, so we
	// can ignore records that are not of the chosen item_type.
	$buffer .= "\n<tr>"
		.format_prompt(get_opendb_lang_var('s_item_type'))
		.format_data(NULL, get_column_select_block("field_column[s_item_type]", $header_row, $field_column_r['s_item_type']))
		.format_data(NULL, NULL)
		.format_data(NULL, NULL, "center")
		."</tr>";
									
	// --------- Now the main input fields start....
	$results = fetch_item_attribute_type_rs($s_item_type);
	if($results)
	{
		while($item_attribute_type_r = db_fetch_assoc($results))
		{
			// purely readonly
			if($item_attribute_type_r['s_field_type'] !== 'ITEM_ID')
			{
				$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
	
				if($item_attribute_type_r['s_field_type'] == 'STATUSTYPE')
				{
					$lookup_results = fetch_newitem_status_type_rs($owner_id);
					$default_field = build_select("field_default[$fieldname]", $lookup_results, $field_default_r[$fieldname], FALSE); // do not include 'None' option
					$initcap_field = NULL;// No initcap for these types of fields.
				}
				else if($item_attribute_type_r['input_type'] !== "textarea" && $item_attribute_type_r['input_type'] !== "url")
				{
					// The url widgets can not work as default fields! 
					// Also because of their nature, it does not make any sense to have a default field
					// for textarea widgets either!

					// Check if any lookup values for this field.
					$lookup_results = fetch_attribute_type_lookup_rs($item_attribute_type_r['s_attribute_type'], 'order_no, value ASC');
					if($lookup_results)
					{
						$default_field = build_select("field_default[$fieldname]", $lookup_results, $field_default_r[$fieldname]);
						$initcap_field = NULL;// No initcap for these types of fields.
					}
					else //use normal input field.
					{
						if($item_attribute_type_r['multi_attribute_ind'] == 'Y')
						{
							$item_attribute_type_r['multi_attribute_ind'] = 'N';
						}
						
						$default_field = 
									get_item_input_field(
											"field_default[$fieldname]",
											$item_attribute_type_r, 
											NULL, //$item_r
											$field_default_r[$fieldname],
											FALSE);

						if($item_attribute_type_r['input_type'] === 'text')
						{
							$initcap_field = 
									get_input_field(
											"field_initcap[$fieldname]",
											NULL, 
											NULL, 
											"checkbox(true,false,)",
											"N", 
											ifempty($field_initcap_r[$fieldname], (get_opendb_config_var('import', 'row_import_default_initcap_checked')?"true":"false")),
											FALSE);
						}
						else //Only for text fields.
						{
							$initcap_field = NULL;
						}
					}
				}
				else
				{
					$field = NULL;
					$default_field = NULL;
					$initcap_field = NULL;
				}
										
				// If the prompt for this field, matches any of the $tokens, then select that token.
				if(!is_numeric($field_column_r[$fieldname]))
				{
					for($i=0; $i<count($header_row); $i++)
					{
						// A direct export from CSV Export, so lets match the columns for you.
						if($header_row[$i] == $fieldname || 
								$header_row[$i] == $item_attribute_type_r['prompt'] ||
								// hack for export which still exports 'Genre' instead of correct specific header, will
								// fix but this allows old row based files to still be matched correctly.
								($item_attribute_type_r['s_field_type'] == 'CATEGORY' && $header_row[$i] == 'Genre'))
						{
							$field_column_r[$fieldname] = $i;
							break;
						}
					}
				}
				
				$buffer .= "\n<tr>"
					.format_prompt($item_attribute_type_r['prompt'])
					.format_data(NULL, get_column_select_block("field_column[$fieldname]", $header_row, $field_column_r[$fieldname]))
					.format_data(NULL, $default_field)
					.format_data(NULL, $initcap_field, "center")
					."</tr>";
					
			}//if($item_attribute_type_r['s_field_type'] !== 'ITEM_ID')
		}//while
		db_free_result($results);
	}//if($results)
	
	$buffer .= "\n</table>";
	
	return $buffer;
}

function get_import_options_table(&$importPlugin, $HTTP_VARS)
{
	$buffer = "<ul class=\"importOptions\">";
	
	if($importPlugin->get_plugin_type() == 'row' && $importPlugin->is_header_row())
	{
		$buffer .= "<li><label for=\"include_first_line_cbox\">".get_opendb_lang_var('include_first_line')."</label>".
				"<input id=\"include_first_line_cbox\" type=checkbox name=\"include_header_row\" value=\"Y\"".(strcmp($HTTP_VARS['include_header_row'],'Y')===0?' CHECKED':'')."></li>";
	}
	
	$buffer .= 	"<li><label for=\"ignore_duplicate_title_cbox\">".get_opendb_lang_var('ignore_duplicate_title')."</label>".
				"<input id=\"ignore_duplicate_title_cbox\" type=checkbox name=\"ignore_duplicate_title\" value=\"Y\"".(strcmp($HTTP_VARS['ignore_duplicate_title'],'Y')===0?' CHECKED':'')."></li>";
	
	if(strcasecmp(get_class($importPlugin),'PreviewImportPlugin')!==0)
	{
		$buffer .= "<li><label for=\"trial_run_cbox\">".get_opendb_lang_var('trial_run')."</label>".
			"<input id=\"trial_run_cbox\" type=checkbox name=\"trial_run\" value=\"Y\" CHECKED></li>";
	}
	
	$buffer .= "</ul>";
	
	return $buffer;
}

function get_import_choices_table(&$importPlugin, $cfg_include_header_row, $cfg_ignore_duplicate_title, $cfg_is_trial_run, $cfg_override_status_type, $cfg_default_status_type_r)
{
	$buffer = "<dl class=\"importOptions\">";

	if($importPlugin->get_plugin_type() == 'row' && $importPlugin->is_header_row())
	{
		$buffer .= "<dt>".get_opendb_lang_var('include_first_line')."</dt>".
				"<dd>".($cfg_include_header_row?_theme_image('tick.gif'):_theme_image('cross.gif'))."</dd>";
	}

	$buffer .= 	"<dt>".get_opendb_lang_var('ignore_duplicate_title')."</dt>".
				"<dd>".($cfg_ignore_duplicate_title?_theme_image('tick.gif'):_theme_image('cross.gif'))."</dd>";
				
	if(strcasecmp(get_class($importPlugin),'PreviewImportPlugin')!==0)
	{
		$buffer .= "<dt>".get_opendb_lang_var('trial_run')."</dt>".
			"<dd>".($cfg_is_trial_run?_theme_image('tick.gif'):_theme_image('cross.gif'))."</dd>";
	}

	if($cfg_override_status_type)
	{
		if(is_not_empty_array($cfg_default_status_type_r))
		{
			$buffer .= "<dt>".get_opendb_lang_var('override_status_type')."</dt>".
						"<dd>".format_display_value('%img%',
									$cfg_default_status_type_r['img'],
									'Y',
									$cfg_default_status_type_r['description'],
									"borrowed_item")."</dd>";
		}
	}

	$buffer .= "</dl>";

	return $buffer;
}

/*
* This page is displayed, when file is uploaded, and various
* options are available to indicate what the next step should
* be in the upload process.  The uploaded page will also be
* loaded when a user chooses to go 'Back' during a trial
* run of the import process.
*/
function get_uploaded_form(&$importPlugin, $header_row, $HTTP_VARS)
{
	global $PHP_SELF;
		
	$buffer = "\n<form action=\"$PHP_SELF\" method=\"POST\">";
	$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"import\">";
	$buffer .= "\n<input type=\"hidden\" name=\"owner_id\" value=\"".$HTTP_VARS['owner_id']."\">";
	$buffer .= "\n<input type=\"hidden\" name=\"s_item_type\" value=\"".$HTTP_VARS['s_item_type']."\">";
	$buffer .= "\n<input type=\"hidden\" name=\"ic_sequence_number\" value=\"".$HTTP_VARS['ic_sequence_number']."\">";
	
	$buffer .= get_import_options_table($importPlugin, $HTTP_VARS);

	if($importPlugin->get_plugin_type() == 'row')
	{
		$buffer .= get_row_column_mappings_table(
							$HTTP_VARS['s_item_type'], 
							$HTTP_VARS['owner_id'], 
							$header_row, 
							$HTTP_VARS['field_column'],
							$HTTP_VARS['field_default'],
							$HTTP_VARS['field_initcap']);
		
		if(strcasecmp(get_class($importPlugin),'PreviewImportPlugin')!==0)
		{
	        $buffer .= "\n<input type=submit value=\"".get_opendb_lang_var('import_items')."\">";
		}
	}
	else if($importPlugin->get_plugin_type() == 'xml')
	{
		// Include a Status Type LOV and a checkbox to indicate whether the s_status_type should be used for all imports
		// ignoring any s_status_type which may be included in the data.
		$buffer .= '<table>';
		
		$results = fetch_newitem_status_type_rs($HTTP_VARS['owner_id']);
		if($results && db_num_rows($results)>1)
		{
			$buffer .= format_field(get_opendb_lang_var('s_status_type'), NULL, status_type_input_field("s_status_type", $results, $HTTP_VARS['s_status_type']));
			$buffer .= format_field(get_opendb_lang_var('override_status_type'), NULL, "<input type=checkbox name=\"override_status_type\" value=\"Y\"".(strcmp($HTTP_VARS['override_status_type'],'Y')===0?' CHECKED':'').">");
		}
		else if(db_num_rows($results)>0) // handle single status silently
		{
			$status_type_r = db_fetch_assoc($results);
			$buffer .= "<input type=\"hidden\" name=\"s_status_type\" value=\"".$status_type_r['s_status_type']."\">";
			db_free_result($results);
		}			
		$buffer .= '</table>';
		
        if(strcasecmp(get_class($importPlugin),'PreviewImportPlugin')!==0)
        {
	        $buffer .= "<input type=submit value=\"".get_opendb_lang_var('import_items')."\">";
        }
	}
	
	$buffer .= '</form>';
	
	return $buffer;
}

function get_upload_form($HTTP_VARS)
{
	global $PHP_SELF;
		
	$buffer = get_validation_javascript();
	
	$buffer .= "\n<form name=\"main\" action=\"$PHP_SELF\" method=\"POST\" enctype=\"multipart/form-data\">";
	$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"upload\">";
	
	$buffer .= "\n<table>";
	// We have already checked that the current user is an administrator if owner_id not provided.	
	if(strlen($HTTP_VARS['owner_id'])==0)
	{
		$results = fetch_user_rs(get_owner_user_types_r());
		$buffer .= format_field(get_opendb_lang_var('owner'), NULL, custom_select('owner_id', $results, '%fullname% (%user_id%)', 1, get_opendb_session_var('user_id'), 'user_id'));
	}
	else
	{
		$buffer .= "\n<input type=\"hidden\" name=\"owner_id\" value=\"".$HTTP_VARS['owner_id']."\">";
	}
							
	$buffer .= format_field(get_opendb_lang_var('item_type'), NULL, single_select("s_item_type", fetch_item_type_rs(TRUE), "%value% - %display%", NULL, $HTTP_VARS['s_item_type']));
	$buffer .= format_field(get_opendb_lang_var('file'), NULL, "<input type=\"file\" size=\"25\" name=\"uploadfile\">");
	
	$buffer .= "\n</table>";
	
	$buffer .= "\n<input type=submit value=\"".get_opendb_lang_var('import_items')."\">";
	$buffer .= "\n</form>";
	
	return $buffer;
}

if(is_site_enabled())
{
	if(is_opendb_valid_session())
	{
		// Only admin user or owner user allowed to access this.
		if( ($HTTP_VARS['owner_id'] == get_opendb_session_var('user_id') && is_user_normal(get_opendb_session_var('user_id'),get_opendb_session_var('user_type')) ) || is_user_admin(get_opendb_session_var('user_id'),get_opendb_session_var('user_type')))
		{
			if(is_file_upload_enabled())
			{
				@set_time_limit(600);
				
				if($HTTP_VARS['op'] == 'upload')
				{
					if(is_uploaded_file($_FILES['uploadfile']['tmp_name']))
					{
						if($_FILES['uploadfile']['size']>0)
						{
							$importPlugin =& get_import_plugin($_FILES['uploadfile'], $error);
							if($importPlugin !== NULL)
							{
								$sequence_number = import_cache_insert($HTTP_VARS['owner_id'], get_class($importPlugin), $_FILES['uploadfile']['tmp_name']);
								if($sequence_number!==FALSE)
								{
									// pass this onto the next call!
									$HTTP_VARS['ic_sequence_number'] = $sequence_number;
									
									if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
										$page_title = get_opendb_lang_var('type_import', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name()));
									else
										$page_title = get_opendb_lang_var('type_import_items_for_name', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name(), 'fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));

									echo(_theme_header($page_title));
									
									if($importPlugin->get_plugin_type() == 'row')
										echo ("<h2>".$page_title." ".get_item_image($HTTP_VARS['s_item_type'])."</h2>\n");
									else
										echo("<h2>".$page_title."</h2>");
									
									$inFile = import_cache_fetch_file($HTTP_VARS['ic_sequence_number']);
									if($inFile)
									{
										$fileHandler =& new WrapperFileHandler($inFile);
										echo(get_uploaded_form(
													$importPlugin, 
													($importPlugin->get_plugin_type()=='row')? $importPlugin->read_header($fileHandler, $error) : NULL,
													$HTTP_VARS));
										
										unset($fileHandler);
										@fclose($inFile);
										
										echo _theme_footer();
									}
									else
									{
										echo _theme_header(get_opendb_lang_var('undefined_error'));
										echo("<p class=\"error\">".get_opendb_lang_var('undefined_error')."</p>");
										echo _theme_footer();
									}
								}//if($sequence_number!==FALSE)
								else
								{
									if(strlen($HTTP_VARS['owner_id'])>0)
									{
										if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
											$page_title = get_opendb_lang_var('import_my_items');
										else
											$page_title = get_opendb_lang_var('import_items_for_name', array('fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
									}
									else
									{
										$page_title = get_opendb_lang_var('import_items');
									}
									echo(_theme_header($page_title));
									echo("<h2>".$page_title."</h2>");
									echo(format_error_block(get_opendb_lang_var('file_upload_error', 'prompt', strtoupper(get_file_ext($_FILES['uploadfile']['name'])))));
									echo(_theme_footer());
								}
							}//if($importPlugin !== NULL)
							else
							{
								if(strlen($HTTP_VARS['owner_id'])>0)
								{
									if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
										$page_title = get_opendb_lang_var('import_my_items');
									else
										$page_title = get_opendb_lang_var('import_items_for_name', array('fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
								}
								else
								{
									$page_title = get_opendb_lang_var('import_items');
								}
								echo(_theme_header($page_title));
								echo("<h2>".$page_title."</h2>");
								echo(format_error_block($error));
								echo(_theme_footer());
							}
						}//if($_FILES['uploadfile']['size']>0)
						else
						{
							if(strlen($HTTP_VARS['owner_id'])>0)
							{
								if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
									$page_title = get_opendb_lang_var('import_my_items');
								else
									$page_title = get_opendb_lang_var('import_items_for_name', array('fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
							}
							else
							{
								$page_title = get_opendb_lang_var('import_items');
							}
							echo(_theme_header($page_title));
							echo("<h2>".$page_title."</h2>");
							echo(format_error_block(get_opendb_lang_var('file_upload_empty', 'prompt', strtoupper(get_file_ext($_FILES['uploadfile']['name'])))));
							echo(_theme_footer());
						}
					}
					else
					{
						$importPlugin = new PreviewImportPlugin();
						
						if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
							$page_title = get_opendb_lang_var('type_import', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name()));
						else
							$page_title = get_opendb_lang_var('type_import_items_for_name', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name(), 'fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
		
						echo(_theme_header($page_title));
						echo ("<h2>".$page_title." ".get_item_image($HTTP_VARS['s_item_type'])."</h2>\n");
										
						// no upload file provided - so preview mode.
						echo(get_uploaded_form(
										$importPlugin, 
										NULL, 
										$HTTP_VARS));
		
						echo _theme_footer();
					}
				}
				else if($HTTP_VARS['op'] == 'uploaded')
				{
					$import_cache_r = fetch_import_cache_r($HTTP_VARS['ic_sequence_number'], $HTTP_VARS['owner_id']);
					if(is_not_empty_array($import_cache_r))
					{
						if(is_import_plugin($import_cache_r['plugin_name']))
						{
							$pluginRef = $import_cache_r['plugin_name'];
							
							include_once("./import/".$pluginRef.".php");
							$importPlugin = new $pluginRef();
							if($importPlugin !== NULL)
							{
								$inFile = import_cache_fetch_file($HTTP_VARS['ic_sequence_number']);
								if($inFile)
								{
									$fileHandler =& new WrapperFileHandler($inFile);
									
									if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
										$page_title = get_opendb_lang_var('type_import', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name()));
									else
										$page_title = get_opendb_lang_var('type_import_items_for_name', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name(), 'fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
		
									echo(_theme_header($page_title));
										
									if($importPlugin->get_plugin_type() == 'row')
										echo ("<h2>".$page_title." ".get_item_image($HTTP_VARS['s_item_type'])."</h2>\n");
									else
										echo("<h2>".$page_title."</h2>");
									
									echo(get_uploaded_form(
												$importPlugin, 
												($importPlugin->get_plugin_type()=='row')? $importPlugin->read_header($fileHandler, $error) : NULL,
												$HTTP_VARS));
									
									unset($fileHandler);
									@fclose($inFile);
									
									echo _theme_footer();
								}//if(strlen($content)>0)
								else
								{
									echo _theme_header(get_opendb_lang_var('undefined_error'));
									echo("<p class=\"error\">".get_opendb_lang_var('undefined_error')."</p>");
									echo _theme_footer();
								}
							}//if($importPlugin !== NULL)
							else
							{
								echo _theme_header(get_opendb_lang_var('undefined_error'));
								echo("<p class=\"error\">".get_opendb_lang_var('undefined_error')."</p>");
								echo _theme_footer();
							}
						}//if(is_import_plugin($import_cache_r['plugin_name']))
						else
						{
							echo _theme_header(get_opendb_lang_var('undefined_error'));
							echo("<p class=\"error\">".get_opendb_lang_var('undefined_error')."</p>");
							echo _theme_footer();
						}
					}//if(is_not_empty_array($import_cache_r))
					else
					{
						echo _theme_header(get_opendb_lang_var('import_cache_file_not_found'));
						echo("<p class=\"error\">".get_opendb_lang_var('import_cache_file_not_found')."</p>");
						echo _theme_footer();
					}
				}
				else if($HTTP_VARS['op'] == 'import')
				{
					$import_cache_r = fetch_import_cache_r($HTTP_VARS['ic_sequence_number'], $HTTP_VARS['owner_id']);
					if(is_not_empty_array($import_cache_r))
					{
						if(is_import_plugin($import_cache_r['plugin_name']))
						{
							$pluginRef = $import_cache_r['plugin_name'];
							
							include_once("./import/".$pluginRef.".php");
							$importPlugin =& new $pluginRef();
							if($importPlugin !== NULL)
							{
								$inFile = import_cache_fetch_file($HTTP_VARS['ic_sequence_number']);
								if($inFile)
								{
									$fileHandler =& new WrapperFileHandler($inFile);

									// we want to display all items - no pagination.
									$HTTP_VARS['items_per_page'] = '';
									
									$listingObject =& new HTML_Listing($PHP_SELF, $HTTP_VARS);
								
									$listingObject->setNoRowsMessage(get_opendb_lang_var('no_items_found'));
									
									$cfg_include_header_row = ( strcmp($HTTP_VARS['include_header_row'],'Y')===0? TRUE : FALSE );
									$cfg_ignore_duplicate_title = ( strcmp($HTTP_VARS['ignore_duplicate_title'],'Y')===0? TRUE : FALSE );
									$cfg_is_trial_run = ( strcmp($HTTP_VARS['trial_run'],'Y')===0? TRUE : FALSE );
									$cfg_override_status_type = ( strcmp($HTTP_VARS['override_status_type'],'Y')===0? TRUE : FALSE );
									
									// force disable of duplicate titles.
									set_opendb_config_ovrd_var('item_input', 'duplicate_title_support', $cfg_ignore_duplicate_title);
									//set_opendb_config_ovrd_var('item_input', 'confirm_duplicate_insert', TRUE);
	
	                                if(is_valid_s_status_type($HTTP_VARS['s_status_type']))
										$cfg_default_status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);
									else
										$cfg_default_status_type_r = fetch_status_type_r(fetch_default_status_type_for_owner($HTTP_VARS['owner_id']));

									$itemImportHandler = new ItemImportHandler(
															$HTTP_VARS['owner_id'],
															$cfg_is_trial_run,
															$cfg_ignore_duplicate_title,
															$cfg_override_status_type,
															$cfg_default_status_type_r,
															$listingObject);
										
									if($importPlugin->get_plugin_type() == 'row')
									{
										if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
											$page_title = get_opendb_lang_var('type_import', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name()));
										else
											$page_title = get_opendb_lang_var('type_import_items_for_name', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name(), 'fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
										
										echo(_theme_header($page_title));
										echo ("<h2>".$page_title." ".get_item_image($HTTP_VARS['s_item_type'])."</h2>\n");
									}
									else if($importPlugin->get_plugin_type() == 'xml')
									{
										//
										// The XML callback functions
										//
										function import_add_error($method, $error)
										{
											global $itemImportHandler;
											$itemImportHandler->addError($method, $error);
										}
										
										function import_start_item($s_item_type, $title = NULL)
										{
											global $itemImportHandler;
											$itemImportHandler->startItem($s_item_type, $title);
										}
										
										function import_set_title($title)
										{
											global $itemImportHandler;
											$itemImportHandler->setTitle($title);
										}
										
										function import_start_item_instance($s_status_type = NULL, $status_comment = NULL, $borrow_duration = NULL)
										{
											global $itemImportHandler;
											$itemImportHandler->startItemInstance($s_status_type, $status_comment, $borrow_duration);
										}
										
										function import_end_item_instance()
										{
											global $itemImportHandler;
											$itemImportHandler->endItemInstance();
										}
										
										// convenience function for plugins that do not import instance level attributes
										function import_item_instance($s_status_type = NULL, $status_comment = NULL, $borrow_duration = NULL)
										{
											global $itemImportHandler;
											$itemImportHandler->startItemInstance($s_status_type, $status_comment, $borrow_duration);
											$itemImportHandler->endItemInstance();
										}
										
										function import_item_attribute($s_attribute_type, $order_no, $attribute_val)
										{
											global $itemImportHandler;
											$itemImportHandler->itemAttribute($s_attribute_type, $order_no, $attribute_val);
										}
										
										function import_end_item()
										{
											global $itemImportHandler;
											$itemImportHandler->endItem();
										}
										
										if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
											$page_title = get_opendb_lang_var('type_import', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name()));
										else
											$page_title = get_opendb_lang_var('type_import_items_for_name', array('type'=>get_display_import_type(get_class($importPlugin)), 'description'=>$importPlugin->get_display_name(), 'fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
										
										echo(_theme_header($page_title));
										echo ("<h2>".$page_title."</h2>\n");
									}
							
									echo(get_import_choices_table(
										$importPlugin,
										$cfg_include_header_row,
										$cfg_ignore_duplicate_title,
										$cfg_is_trial_run,
										$cfg_override_status_type,
										$cfg_default_status_type_r));
									
									$listingObject->startListing();
							
									$listingObject->addHeaderColumn(''); // Success or Failure column
									$listingObject->addHeaderColumn(get_opendb_lang_var('type'));
									$listingObject->addHeaderColumn(get_opendb_lang_var('title'));
									//$listingObject->addHeaderColumn(get_opendb_lang_var('owner'));
									if($cfg_override_status_type!==TRUE)
										$listingObject->addHeaderColumn(get_opendb_lang_var('s_status_type'));
									$listingObject->addHeaderColumn(get_opendb_lang_var('attributes'));
							
									if($importPlugin->get_plugin_type() == 'row')
									{
										$rowHandler = new RowImportPluginHandler($itemImportHandler, $importPlugin, $fileHandler, $HTTP_VARS['field_column'], $HTTP_VARS['field_default'], $HTTP_VARS['field_initcap']);
										if( ($resultOfImport = $rowHandler->handleImport($cfg_include_header_row, $HTTP_VARS['s_item_type']))!==TRUE)
										{
											$importError = $xmlHandler->getError();
										}
									}
									else if($importPlugin->get_plugin_type() == 'xml')
									{
										$xmlHandler = new XMLImportPluginHandler($itemImportHandler, $importPlugin, $fileHandler);
										if( ($resultOfImport = $xmlHandler->handleImport())!==TRUE)
										{
											$importError = $xmlHandler->getError();
										}
									}
							
									// Close file.
									unset($fileHandler);
									@fclose($inFile);
									
									$listingObject->endListing();
									
									if($resultOfImport !== TRUE)
									{
										$listingObject->setNoRowsMessage($importError);
									}
									else
									{
										if($cfg_is_trial_run)
										{
											echo("<form action=\"$PHP_SELF\" method=\"POST\">");
											echo(get_url_fields($HTTP_VARS, array('op'), array('op2')));
											echo("<input type=\"button\" onclick=\"this.form.op.value='uploaded'; this.form.submit();\" value=\"".get_opendb_lang_var('back')."\">");
											echo("<input type=\"button\" onclick=\"this.form.trial_run.value='N'; this.form.op.value='import'; this.form.submit();\" value=\"".get_opendb_lang_var('import_items')."\">");
											echo("</form>");
										}
										else 
										{
											if(is_not_empty_array($itemImportHandler->getItemIDList()))
											{
												$footer_links_r[] = array(
													url=>'listings.php?item_id_range='.urlencode(get_item_id_range($itemImportHandler->getItemIDList())), 
													text=>get_opendb_lang_var('list_imported_items', 'count', count($itemImportHandler->getItemIDList())));
											}
											
											//Get rid of the file now!
											import_cache_delete($HTTP_VARS['ic_sequence_number']);
											
											echo format_footer_links($footer_links_r);
										}
									}
									
									
									// don't need it anymore.
									unset($listingObject);
									
									echo _theme_footer();
								}//if(strlen($content)>0)
								else
								{
									echo _theme_header(get_opendb_lang_var('undefined_error'));
									echo("<p class=\"error\">".get_opendb_lang_var('undefined_error')."</p>");
									echo _theme_footer();
								}
							}//if($importPlugin !== NULL)
							else
							{
								echo _theme_header(get_opendb_lang_var('undefined_error'));
								echo("<p class=\"error\">".get_opendb_lang_var('undefined_error')."</p>");
								echo _theme_footer();
							}
						}//if(is_import_plugin($import_cache_r['plugin_name']))
						else
						{
							echo _theme_header(get_opendb_lang_var('undefined_error'));
							echo("<p class=\"error\">".get_opendb_lang_var('undefined_error')."</p>");
							echo _theme_footer();
						}
					}//if(is_not_empty_array($import_cache_r))
					else
					{
						echo _theme_header(get_opendb_lang_var('import_cache_file_not_found'));
						echo("<p class=\"error\">".get_opendb_lang_var('import_cache_file_not_found')."</p>");
						echo _theme_footer();
					}
				}
				else
				{
					if(strlen($HTTP_VARS['owner_id'])>0)
					{
						if(strcmp($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')) === 0)
							$page_title = get_opendb_lang_var('import_my_items');
						else
							$page_title = get_opendb_lang_var('import_items_for_name', array('fullname'=>fetch_user_name($HTTP_VARS['owner_id']),'user_id'=>$HTTP_VARS['owner_id']));
					}
					else
					{
						$page_title = get_opendb_lang_var('import_items');
					}
					
					echo(_theme_header($page_title));
					echo("<h2>".$page_title."</h2>");
					
					echo(get_upload_form($HTTP_VARS));
	
					echo _theme_footer();
				}
			}
			else// if(is_file_upload_enabled() && import_check_is_installed())
			{
				if(is_file_upload_enabled())
				{
					echo _theme_header(get_opendb_lang_var('import_not_available'));
					echo("<p class=\"error\">".get_opendb_lang_var('import_not_available')."</p>");
					
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Import cache table not installed.');
				}
				else
				{
					echo _theme_header(get_opendb_lang_var('file_upload_not_available'));
					echo("<p class=\"error\">".get_opendb_lang_var('file_upload_not_available')."</p>");
				}
			}
		}
		else if(is_site_public_access_enabled())
		{
			// provide login at this point
			redirect_login($PHP_SELF, $HTTP_VARS);
		}
		else//not an administrator or own user.
		{
			echo _theme_header(get_opendb_lang_var('not_authorized_to_page'));
			echo("<p class=\"error\">".get_opendb_lang_var('not_authorized_to_page')."</p>");
			echo _theme_footer();
		}
	}
	else//invalid session
	{
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
}//if(is_site_enabled())
else
{
	echo _theme_header(get_opendb_lang_var('site_is_disabled'), FALSE);
	echo("<p class=\"error\">".get_opendb_lang_var('site_is_disabled')."</p>");
	echo _theme_footer();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>