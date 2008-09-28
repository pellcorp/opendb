<?php
/* 	
	OpenDb Media Collector Database
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
include_once("./functions/database.php");
include_once("./functions/logging.php");
include_once("./functions/utils.php");
include_once("./functions/borrowed_item.php");
include_once("./functions/item.php");
include_once("./functions/http.php");
include_once("./functions/fileutils.php");
include_once("./functions/user.php");
include_once("./functions/review.php");
include_once("./functions/item_attribute.php");
include_once("./functions/item_type.php");
include_once("./functions/widgets.php");
include_once("./functions/parseutils.php");
include_once("./functions/status_type.php");

/*
* 	This assumes a certain amount of input validation has been performed before calling this
*	function.
* 
* 	NOTE: Assumes the validate_item_attributes(...) has been called before this, to put the
* 		actual values into the $HTTP_VARS[$fieldname] value.
*
*   If $item_r['instance_no'] is defined, then this function will ONLY do instance level
*   attributes, so be sure to set this to empty, if you want to do everything, but be aware
*   that even then, you will have to call this twice to insert any item_instance specific
*   attributes.
*/
function handle_item_attributes($op, $item_r, $HTTP_VARS, $_FILES, &$errors)
{
	// for these operations, no instance_no context is possible
	// item instance attributes will be handled in a separate call to this function, as a updateinstance op
	if($op == 'insert' || $op == 'update')
	    $item_r['instance_no'] = NULL;

	$attr_results = fetch_item_attribute_type_rs($item_r['s_item_type'], is_numeric($item_r['instance_no'])?'instance_attribute_ind':'item_attribute_ind', FALSE);
	if($attr_results)
	{
		$attributes_updated = 0;
		while($item_attribute_type_r = db_fetch_assoc($attr_results))
		{
			// For all operations the {DURATION,TITLE,STATUSTYPE,STATUSCMNT,ITEM_ID} cannot be 
			// updated because they exist at item/item_instance level.
			if(	$item_attribute_type_r['s_field_type']!='DURATION' && 
						$item_attribute_type_r['s_field_type']!='TITLE' && 
						$item_attribute_type_r['s_field_type']!='STATUSTYPE' &&
						$item_attribute_type_r['s_field_type']!='STATUSCMNT' && 
						$item_attribute_type_r['s_field_type']!='ITEM_ID')
			{
				$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);

				// save it in case we are in refresh mode.
				$orig_fieldname = $fieldname;

				if(!is_array($HTTP_VARS[$fieldname]))
				{
					if(preg_match("/new([0-9]+)/", $HTTP_VARS[$fieldname], $matches) && isset($HTTP_VARS[$fieldname.'_'.$matches[0]]))
					{
						$fieldname = $fieldname.'_'.$matches[0];
					}
					else if($HTTP_VARS[$fieldname] == 'old')
					{
						// make sure this is a refresh value and not just a field with the value 'old'
						if(isset($HTTP_VARS[$fieldname.'_new1']))
						{
							$fieldname = $fieldname.'_old';
						}
					}
				}

				if(is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type']))
				{
					$value_r = NULL;
					if(is_array($HTTP_VARS[$fieldname]))
						$value_r = $HTTP_VARS[$fieldname];
					else if(isset($HTTP_VARS[$fieldname]))
						$value_r[] = $HTTP_VARS[$fieldname];

					if(is_item_attribute_set($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'],  $item_attribute_type_r['order_no']))
					{
						if(update_item_attributes($item_r['item_id'], $item_r['instance_no'], $item_r['s_item_type'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no'], $value_r))
						{
							$attributes_updated++;
						}
					}
					else if(is_not_empty_array($value_r))
					{
						if(insert_item_attributes($item_r['item_id'], $item_r['instance_no'], $item_r['s_item_type'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no'], $value_r))
						{
							$attributes_updated++;
						}
					}
				}
				else
				{
					$file_r = NULL;
					
					if(is_array($_FILES) && 
							is_array($_FILES[$fieldname.'_upload']) && 
							is_uploaded_file($_FILES[$fieldname.'_upload']['tmp_name']))
					{
						$value = basename($_FILES[$fieldname.'_upload']['name']);
						$file_r = $_FILES[$fieldname.'_upload'];
					}
					else // normal field
					{
						$value = $HTTP_VARS[$fieldname];
					}
					
					// If attribute value found - an existing attribute, so do an update.
					if(is_item_attribute_set($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'],  $item_attribute_type_r['order_no']))
					{
						if(update_item_attributes($item_r['item_id'], $item_r['instance_no'], $item_r['s_item_type'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no'], $value, $file_r))
						{
							$attributes_updated++;
						}
					}
					else if(strlen($value)>0)
					{
						if(insert_item_attributes($item_r['item_id'], $item_r['instance_no'], $item_r['s_item_type'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no'], $value, $file_r))
						{
   			   			 	$attributes_updated++;
						}
					}
				}
			}
		}//while
		db_free_result($attr_results);
			
		// Indicate how many attributes updated.
		return $attributes_updated;
	}
	else
	{
		return FALSE;
	}
}

/*
* Validate item_attributes will actually update the $HTTP_VARS variable
* with the final filtered value
*/
function validate_item_attributes($op, $s_item_type, &$HTTP_VARS, $_FILES, &$errors)
{
	$errors = NULL;
	$all_fields_validated=TRUE;
	
	$attr_results = fetch_item_attribute_type_rs($s_item_type, 'not_instance_field_types');
	if($attr_results)
	{
		while($item_attribute_type_r = db_fetch_assoc($attr_results))
		{
			// Item_ID is purely a read-only attribute.
			if($item_attribute_type_r['s_field_type'] != 'ITEM_ID')
			{
				// Force compulsory_ind for several s_field_type attributes, in case of bad data.
				if($item_attribute_type_r['s_field_type'] == 'TITLE')
				{
					$item_attribute_type_r['compulsory_ind'] = 'Y';
					$fieldname = 'title';
				}
				else
				{
					$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);				
				}
				// save it in case we are in refresh mode.
				$orig_fieldname = $fieldname;
				
				if(!is_array($HTTP_VARS[$fieldname]))
				{
					if(preg_match("/new([0-9]+)/", $HTTP_VARS[$fieldname], $matches) && isset($HTTP_VARS[$fieldname.'_'.$matches[0]]))
					{
						$fieldname = $fieldname.'_'.$matches[0];
					}
					else if($HTTP_VARS[$fieldname] == 'old')
					{
						// make sure this is a refresh value and not just a field with the value 'old'
						if(isset($HTTP_VARS[$fieldname.'_new1']))
						{
							$fieldname = $fieldname.'_old';
						}
					}
				}
				
				// Is it an upload operation
				if(is_array($_FILES) && 
						is_array($_FILES[$fieldname.'_upload']) && 
						is_uploaded_file($_FILES[$fieldname.'_upload']['tmp_name']))
				{
					$HTTP_VARS[$fieldname] = $_FILES[$fieldname.'_upload']['name'];
				}
				else // normal field
				{
					$HTTP_VARS[$fieldname] = filter_item_input_field($item_attribute_type_r, $HTTP_VARS[$fieldname]);
				}
				
				// Indicate at least one field failed validation.
				if(!validate_item_input_field($item_attribute_type_r, $HTTP_VARS[$fieldname], $errors))
				{
					$all_fields_validated = FALSE;
				}
				else
				{
					// So we have the filtered version for the handle_update / handle_insert functions.
					if(!is_array($HTTP_VARS[$orig_fieldname]))
					{
						if(preg_match("/new([0-9]+)/", $HTTP_VARS[$orig_fieldname], $matches) && isset($HTTP_VARS[$orig_fieldname.'_'.$matches[0]]))
						{
							$HTTP_VARS[$fieldname.'_'.$matches[0]] = $HTTP_VARS[$orig_fieldname];
						}
					}
				}
			}				
		}
		db_free_result($attr_results);
		
		if(!$all_fields_validated)
			return FALSE;
		else
			return TRUE;
	}
	else
	{
		//else - what else can I do here?
		$errors[] = array('error'=>get_opendb_lang_var('undefined_error'),'detail'=>'');
		return FALSE;
	}
}

/*
 * Returns:
 * 	TRUE  				- Successful execution
 *  FALSE 	 			- Failed execution
 *  "__CONFIRM__" 		- Operation requires confirmation
 *  "__ABORTED__"		- Operation was aborted
 * "__INVALID_DATA__" 	- indicates that the data entered was not validated
 */
function handle_item_insert(&$item_r, $HTTP_VARS, $_FILES, &$errors)
{
	if( (is_user_granted_permission(PERM_ITEM_OWNER) &&
				$item_r['owner_id'] == get_opendb_session_var('user_id')) || 
				is_user_granted_permission(PERM_ITEM_ADMIN) )
	{
		// Before trying to insert items into this structure, first ensure it is valid.
		if(is_valid_item_type_structure($item_r['s_item_type']))
		{
			// We need to get the title	
			if(validate_item_attributes('insert', $item_r['s_item_type'], $HTTP_VARS, $_FILES, $errors))
			{
				$fieldname = 'title';
				if(!is_array($HTTP_VARS[$fieldname]))
				{
					if(preg_match("/new([0-9]+)/", $HTTP_VARS[$fieldname], $matches) && isset($HTTP_VARS[$fieldname.'_'.$matches[0]]))
					{
						$fieldname = $fieldname.'_'.$matches[0];
					}
				}
				$item_r['title'] = $HTTP_VARS[$fieldname];

				$is_exists_owner_title = FALSE;
				$is_exists_title = FALSE;
				
				// Check owner context
				if(get_opendb_config_var('item_input', 'duplicate_title_support')!==TRUE || 
					($HTTP_VARS['confirmed'] != 'true' && 
						get_opendb_config_var('item_input', 'confirm_duplicate_owner_insert')!==FALSE))
				{
					$is_exists_owner_title = is_exists_title($item_r['title'], $item_r['s_item_type'], $item_r['owner_id']);
				}
					
				// Only check title/s_item_type context								
				if(get_opendb_config_var('item_input', 'duplicate_title_support')!==TRUE || 
					($HTTP_VARS['confirmed'] != 'true' && 
						get_opendb_config_var('item_input', 'confirm_duplicate_insert')!==FALSE))
				{
					$is_exists_title = is_exists_title($item_r['title'], $item_r['s_item_type']);
				}
				
				// Unless duplicate title support is allowed we cannot continue.
				if((!$is_exists_title && !$is_exists_owner_title) || 
						get_opendb_config_var('item_input', 'duplicate_title_support')!==FALSE)
				{
					if(!$is_exists_title && !$is_exists_owner_title)
					{
					    if($HTTP_VARS['trial_run'] != 'true')
					    {
							$new_item_id = insert_item($item_r['s_item_type'], $item_r['title']);
							if($new_item_id !== FALSE)
							{
								$item_r['item_id'] = $new_item_id;
	
		                        // insert any item level attributes
	    	                    handle_item_attributes('insert', $item_r, $HTTP_VARS, $_FILES, $errors);
	                        
								return TRUE;
							}
							else
							{
								$db_error = db_error();
								$errors = array('error'=>get_opendb_lang_var('item_not_added'),'detail'=>$db_error);
								return FALSE;
							}
						}//if($HTTP_VARS['trial_run'] != 'true')
						else
						{
						    return TRUE;
						}
					}
					else if($HTTP_VARS['confirmed'] != 'false')// if explicitly false, then we are aborting insert.
					{
						if($is_exists_owner_title)
						{
						    $errors = array('error'=>get_opendb_lang_var('title_same_type_and_owner_exists', array('title'=>$item_r['title'],'s_item_type'=>$item_r['s_item_type'])),'detail'=>'');
							return "__CONFIRM_EXISTS_OWNER_TITLE__";
						}
						else if($is_exists_title)
						{
						    $errors = array('error'=>get_opendb_lang_var('title_same_type_exists', array('title'=>$item_r['title'],'s_item_type'=>$item_r['s_item_type'])),'detail'=>'');
							return "__CONFIRM_EXISTS_TITLE__";
						}
					}
					else //insert aborted.
					{
						return "__ABORTED__";
					}
				}
				else // cannot insert duplicate.
				{
					if($is_exists_owner_title)
						$errors = array('error'=>get_opendb_lang_var('title_same_type_and_owner_exists', array('title'=>$item_r['title'],'s_item_type'=>$item_r['s_item_type'])),'detail'=>'');
					else if($is_exists_title)
						$errors = array('error'=>get_opendb_lang_var('title_same_type_exists', array('title'=>$item_r['title'],'s_item_type'=>$item_r['s_item_type'])),'detail'=>'');
					
					return FALSE;
				}
			}
			else //if(validate_item_attributes("insert", $item_r['s_item_type'], $errors))
			{
				return "__INVALID_DATA__";
			}
		}
		else // if(is_valid_item_type_structure($item_r['s_item_type']))
		{
			$errors = array('error'=>get_opendb_lang_var('invalid_item_type_structure', 's_item_type', $item_r['s_item_type']),'detail'=>'');
		
			// An error like this is a big problem, and should be dealt with quickly, but there is no sense in alarming the
			// user by sending back an error.
			return FALSE;
		}
	}// non-admin user attempting to insert item for someone else.
	else
	{
		$errors = array('error'=>get_opendb_lang_var('operation_not_available'));
		
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attemped to insert an item for another user', $item_r);
		return FALSE;
	}
}

/*
 No assumptions are made about whether the op is an update or delete.  In fact we
 assume 'refresh' functionality even if a normal update, which simplifies our
 task considerably.
 *
 * Return "__INVALID_DATA__" - indicates that the data entered was not validated
 */
function handle_item_update(&$item_r, $HTTP_VARS, $_FILES, &$errors)
{
	if($item_r['owner_id'] == get_opendb_session_var('user_id') || is_user_granted_permission(PERM_ITEM_ADMIN))
	{
		if(validate_item_attributes('update', $item_r['s_item_type'], $HTTP_VARS, $_FILES, $errors))
		{
			$fieldname = 'title';
			if(!is_array($HTTP_VARS[$fieldname]))
			{
				if(preg_match("/new([0-9]+)/", $HTTP_VARS[$fieldname], $matches) && isset($HTTP_VARS[$fieldname.'_'.$matches[0]]))
				{
					$fieldname = $fieldname.'_'.$matches[0];
				}
				else if($HTTP_VARS[$fieldname] == 'old')
				{
					// make sure this is a refresh value and not just a field with the value 'old'
					if(isset($HTTP_VARS[$fieldname.'_new1']))
					{
						$fieldname = $fieldname.'_old';
					}
				}
			}
			
			// this is technically unecessary, because we enforce title as a required field.
			$item_r['title'] = ifempty($HTTP_VARS[$fieldname], $item_r['title']);
			
			if(update_item($item_r['item_id'], $item_r['title']))
			{
				handle_item_attributes('update', $item_r, $HTTP_VARS, $_FILES, $errors);
				return TRUE;
			}
			else //if(update_item($item_r['item_id'], ifempty($title_val, $item_r['title'])))
			{
				$db_error = db_error();
				$errors = array('error'=>get_opendb_lang_var('item_not_updated'),'detail'=>$db_error);
				return FALSE;
			}
		}
		else
		{
			return "__INVALID_DATA__";
		}
	}
	else
	{
		$errors = array('error'=>get_opendb_lang_var('cannot_update_item_not_owned'),'detail'=>'');
			
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to update an item they do not own', $item_r);
		return FALSE;
	}
}

function handle_item_instance_insert(&$item_r, $status_type_r, $HTTP_VARS, &$errors)
{
	$owner_id = ifempty($HTTP_VARS['owner_id'], get_opendb_session_var('user_id'));
	
	if( (is_user_granted_permission(PERM_ITEM_OWNER) &&
				$owner_id == get_opendb_session_var('user_id')) || 
				is_user_granted_permission(PERM_ITEM_ADMIN) )
	{
		$status_type = ifempty($HTTP_VARS['s_status_type'], $item_r['s_status_type']);
		$status_type_r = fetch_status_type_r($status_type);
		
		if(is_newinstance_status_type_valid($item_r['item_id'], $owner_id, $status_type_r, $errors))
		{
			// assume validation attribute function converts borrow duration to a single element array.
			if(is_array($HTTP_VARS['borrow_duration']) && count($HTTP_VARS['borrow_duration']) == 1)
				$borrow_duration = $HTTP_VARS['borrow_duration'][0];
			else
				$borrow_duration = $HTTP_VARS['borrow_duration'];
			
			$status_comment = $HTTP_VARS['status_comment'];
			
			if($HTTP_VARS['trial_run'] != 'true')
			{
				$new_instance_no = insert_item_instance($item_r['item_id'], NULL, $status_type, $status_comment, $borrow_duration, $owner_id);
				if($new_instance_no !== FALSE)
				{
					// Now $item_r represents new instance of item
					$item_r['instance_no'] = $new_instance_no;
					$item_r['s_status_type'] = $status_type;
					$item_r['status_comment'] = $status_comment;
					$item_r['borrow_duration'] = $borrow_duration;
					$item_r['owner_id'] = $owner_id;

   					copy_related_item_instance_relationships($item_r['item_id'], $item_r['instance_no']);
    				
				    handle_item_attributes('updateinstance', $item_r, $HTTP_VARS, $_FILES, $errors);

					return TRUE;
				}
				else//if($new_instance_no !== FALSE)
				{
					$db_error = db_error();
					$errors = array('error'=>get_opendb_lang_var('item_instance_not_added'),'detail'=>$db_error);
					return FALSE;
				}
			}
			else
			{
			    return TRUE;
			}
		}
		else//if(is_status_type_insert_valid($item_r['item_id'], $owner_id, $new_status_type_r, $errors))
		{
			return FALSE;
		}
	}// non-admin user attempting to insert item for someone else.
	else
	{
		$errors = array('error'=>get_opendb_lang_var('not_authorized_to_page'));
		
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to insert an item instance for another user');
		return FALSE;
	}
}

/*
* Update s_status_type,borrow_duration,status_comment values only.  However
* the calling process will be passing the new s_status_type value, so there
* is no need for clever logic in this function.
* 
* Return values:
* 
* 	__ABORTED__
* 	__CONFIRM__
*/
function handle_item_instance_update($item_r, $status_type_r, $HTTP_VARS, &$errors)
{
	if(is_not_empty_array($item_r))
	{
		if($item_r['owner_id'] == get_opendb_session_var('user_id') ||
				is_user_granted_permission(PERM_ITEM_ADMIN))
		{
			$update_status_type = $HTTP_VARS['s_status_type'];

			// Do not allow use of default s_status_type, it needs to be explicitly specified.
			if(strlen($update_status_type)>0 && $item_r['s_status_type'] != $update_status_type)
				$update_status_type_r = fetch_status_type_r($update_status_type);
			else // Otherwise s_status_type is not being udated
			{
				$update_status_type = $status_type_r['s_status_type'];
				$update_status_type_r = $status_type_r; // current s_status_type
			}

			// If $update_status_type_r not defined, then we are not updating the s_status_type, only the status_comment
			if($item_r['s_status_type'] == $update_status_type ||
					is_update_status_type_valid($item_r['item_id'], $item_r['instance_no'], $item_r['owner_id'], $status_type_r, $update_status_type_r, $errors))
			{
				// assume validation attribute function converts borrow duration to a single element array.
				if(is_array($HTTP_VARS['borrow_duration']) && count($HTTP_VARS['borrow_duration']) == 1)
					$borrow_duration = $HTTP_VARS['borrow_duration'][0];
				else
					$borrow_duration = $HTTP_VARS['borrow_duration'];

				$borrow_duration = ifempty($borrow_duration, $item_r['borrow_duration']);
				if(!is_numeric($borrow_duration))
				{
					$borrow_duration = FALSE; // Not defined, so do not update
				}
				
				$status_comment = $HTTP_VARS['status_comment'];
				
				// trigger change owner processing at this point.
				if(strlen($HTTP_VARS['owner_id']) && $HTTP_VARS['owner_id'] != $item_r['owner_id'])
				{
					if($status_type_r['change_owner_ind'] == 'Y')
					{
						if(!is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
						{
							if(!update_item_instance_owner($item_r['item_id'], $item_r['instance_no'], $item_r['owner_id'], $HTTP_VARS['owner_id']))
							{
								$errors = array('error'=>get_opendb_lang_var('item_instance_owner_not_changed'));	
							}
						}
						else
						{
							$errors = array('error'=>get_opendb_lang_var('item_instance_owner_not_changed'));
						}
					}
					else
					{
						$errors = array('error'=>get_opendb_lang_var('operation_not_avail_change_owner'),'detail'=>'');
					}
				}
				
				if(update_item_instance($item_r['item_id'], $item_r['instance_no'], $update_status_type, $status_comment, $borrow_duration))
				{
				    handle_item_attributes('updateinstance', $item_r, $HTTP_VARS, $_FILES, $errors);
					return TRUE;
				}
				else
				{
					$db_error = db_error();
					$errors = array('error'=>get_opendb_lang_var('item_instance_not_updated'),'detail'=>$db_error);
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			$errors = array('error'=>get_opendb_lang_var('cannot_update_item_not_owned'),detail=>'');
			
			opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to update item instance they do not own', $item_r);
			return FALSE;
		}
	}
	else//if(is_not_empty_array($item_r))
	{
		$errors = array('error'=>get_opendb_lang_var('item_not_found'),'detail'=>'');
		return FALSE;
	}
}

function copy_item_attributes($old_item_type, $old_item_id, $old_instance_no, $new_item_type, $new_item_id, $new_instance_no)
{
	$results = fetch_item_attribute_type_rs($old_item_type, 'not_instance_field_types');
	if($results)
	{
		$attr_order_no_r = array();
		
		while($item_attribute_type_r = db_fetch_assoc($results))
		{
			$last_order_no = NULL;
			if(is_numeric($attr_order_no_r[$item_attribute_type_r['s_attribute_type']]))
				$last_order_no = $attr_order_no_r[$item_attribute_type_r['s_attribute_type']];
			
			$order_no = fetch_s_item_attribute_type_next_order_no($new_item_type, $item_attribute_type_r['s_attribute_type'], $last_order_no);
			if($order_no!==FALSE)
			{
				// update with latest order no
				$attr_order_no_r[$item_attribute_type_r['s_attribute_type']] = $order_no;
				
				$attribute_val_r = fetch_attribute_val_r($old_item_id, $old_instance_no, $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
				if(is_not_empty_array($attribute_val_r))
				{
					insert_item_attributes(
									$new_item_id,
									$new_instance_no, // instance_no
                                    $new_item_type,
									$item_attribute_type_r['s_attribute_type'],
									$order_no,
									$attribute_val_r);
				}
			}//if($order_no!==FALSE)
		}
		db_free_result($results);
	}
}

/**
 * 
 */
function copy_item_to_http_vars($old_item_r, $new_item_type)
{
	$HTTP_VARS = array();
	
	$results = fetch_item_attribute_type_rs($old_item_r['s_item_type'], 'not_instance_field_types');
	if($results)
	{
		$attr_order_no_r = array();
		
		while($item_attribute_type_r = db_fetch_assoc($results))
		{
			if($item_attribute_type_r['s_field_type'] == 'TITLE')
			{
				$order_no = fetch_s_item_attribute_type_next_order_no($new_item_type, $item_attribute_type_r['s_attribute_type']);
					
				$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $order_no);
				$HTTP_VARS[$fieldname] = $old_item_r['title'];
			}
			else
			{
				$last_order_no = NULL;
				if(is_numeric($attr_order_no_r[$item_attribute_type_r['s_attribute_type']]))
					$last_order_no = $attr_order_no_r[$item_attribute_type_r['s_attribute_type']];
				
				$order_no = fetch_s_item_attribute_type_next_order_no($new_item_type, $item_attribute_type_r['s_attribute_type'], $last_order_no);
				if($order_no!==FALSE)
				{
					$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $order_no);
					
					// update with latest order no
					$attr_order_no_r[$item_attribute_type_r['s_attribute_type']] = $order_no;
					
					if($item_attribute_type_r['lookup_attribute_ind'] == 'Y' || $item_attribute_type_r['multi_attribute_ind'] == 'Y')
					{
						$attribute_val_r = fetch_attribute_val_r($old_item_r['item_id'], $old_item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
						if(is_not_empty_array($attribute_val_r))
						{
							$HTTP_VARS[$fieldname] = $attribute_val_r;
						}
					}
					else
					{
						$attribute_val = fetch_attribute_val($old_item_r['item_id'], $old_item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
						if(strlen($attribute_val)>0)
						{
							$HTTP_VARS[$fieldname] = $attribute_val;
						}
					}
				}
			}
		}
		db_free_result($results);
	}
	
	return $HTTP_VARS;
}

/*
 * This function assumes, that the delete confirm functionality has already
 * been processed, before being called.  It does not confirm delete checking
 * as a result.
 * 
 * Returns:
 * 	TRUE  			-	Successful execution
 *  FALSE 	 		-	Failed execution
 *  "__CONFIRM__" 	-	Operation requires confirmation
 *  "__ABORTED__"	-	Operation was aborted
 */
function handle_item_delete($item_r, $status_type_r, $HTTP_VARS, &$errors, $delete_with_closed_borrow_records = NULL)
{
	if($item_r['owner_id'] != get_opendb_session_var('user_id') && !is_user_granted_permission(PERM_ITEM_ADMIN))
	{
		$errors = array('error'=>get_opendb_lang_var('cannot_delete_item_not_owned'),'detail'=>'');
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User to delete item instance they do not own', $item_r);
		return FALSE;
	}
	
	if($status_type_r['delete_ind'] != 'Y')
	{
		$errors = array('error'=>get_opendb_lang_var('operation_not_avail_s_status_type', 's_status_type_desc', $status_type_r['description']),'detail'=>'');
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'Attempted to delete item instance with a status that prevents the item being deleted', $item_r);
		return FALSE;
	}

	if(is_exists_item_instance_relationship($item_r['item_id'], $item_r['instance_no']))
	{
		$errors = array('error'=>get_opendb_lang_var('item_not_deleted'), 'detail'=>get_opendb_lang_var('item_related_to_other_items'));
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to to delete item with related items', $item_r);
		return FALSE;
	}
	
	if(is_item_reserved_or_borrowed($item_r['item_id'], $item_r['instance_no']))
	{
		$errors = array('error'=>get_opendb_lang_var('item_not_deleted'), 'detail'=>get_opendb_lang_var('item_reserved_or_borrowed'));
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to to delete item with active reservation(s)', $item_r);
		return FALSE;
	}
	
	$inactive_borrowed_items_exist = FALSE;
	if(is_exists_item_instance_borrowed_item($item_r['item_id'], $item_r['instance_no']))
	{
		if(get_opendb_config_var('item_input', 'allow_delete_with_closed_or_cancelled_borrow_records')!==TRUE) 
		{
			$errors = array('error'=>get_opendb_lang_var('item_not_deleted'), 'detail'=>get_opendb_lang_var('item_has_inactive_borrowed_item'));
			opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to to delete item with inactive reservation(s)', $item_r);
			return FALSE;
		}
		else
		{
			$inactive_borrowed_items_exist = TRUE;
		}
	}
			
	if($HTTP_VARS['confirmed'] == 'true' || 
				($inactive_borrowed_items_exist!==TRUE && 
				get_opendb_config_var('item_input', 'confirm_item_delete')===FALSE) )
	{
		if($inactive_borrowed_items_exist)
		{
			if(!delete_item_instance_inactive_borrowed_items($item_r['item_id'], $item_r['instance_no']))
			{
				$db_error = db_error();
				$errors = array('error'=>get_opendb_lang_var('undefined_error'),'detail'=>$db_error);
				return FALSE;
			}
		}
			
		if(!is_exists_item_instance_borrowed_item($item_r['item_id'], $item_r['instance_no']))
		{
			delete_related_item_instance_relationship($item_r['item_id'], $item_r['instance_no'], $HTTP_VARS['parent_item_id'], $HTTP_VARS['parent_instance_no']);
			
			if(!is_exists_related_item_instance_relationship($item_r['item_id'], $item_r['instance_no'], $HTTP_VARS['parent_item_id'], $HTTP_VARS['parent_instance_no']))
			{
				if(!delete_item_instance($item_r['item_id'], $item_r['instance_no']))
				{
					$db_error = db_error();
					$errors = array('error'=>get_opendb_lang_var('item_not_deleted'),'detail'=>$db_error);
					return FALSE;
				}
			}
			else
			{	
				$errors = array('error'=>get_opendb_lang_var('item_not_deleted'), 'detail'=>get_opendb_lang_var('item_related_to_other_items'));
				opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to to delete item with attached item instance relationship record(s)', $item_r);
				return FALSE;
			}
		}
		else
		{
			if(is_item_reserved_or_borrowed($item_r['item_id'], $item_r['instance_no']))
				$errors = array('error'=>get_opendb_lang_var('item_not_deleted'),'detail'=>get_opendb_lang_var('item_reserved_or_borrowed'));
			else
				$errors = array('error'=>get_opendb_lang_var('item_not_deleted'),'detail'=>get_opendb_lang_var('item_has_inactive_borrowed_item'));
			return FALSE;
		}

		// If child and no more instance left, 
		// proceed with item and item_attribute delete.
		if(!is_exists_item_instance($item_r['item_id'], NULL))
		{
			// Get rid of all reviews.
			if(is_item_reviewed($item_r['item_id']))
			{
				delete_reviews($item_r['item_id']);
			}
			
			delete_item_attributes($item_r['item_id'], $item_r['instance_no']);
			
			if(!delete_item($item_r['item_id']))
			{
				$db_error = db_error();
				$errors = array('error'=>get_opendb_lang_var('item_not_deleted'),'detail'=>$db_error);
				return FALSE;
			}
			
			// As long as delete_item has worked, we do not care about anything else.
			return TRUE;
		}
	}
	else if($HTTP_VARS['confirmed'] != 'false')
	{
		if($inactive_borrowed_items_exist)
			return "__CONFIRM_INACTIVE_BORROW__";
		else
			return "__CONFIRM__";
	}
	else // confirmation required.
	{
		return "__ABORTED__";				
	}
}
?>