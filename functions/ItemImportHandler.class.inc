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
include_once("./functions/item.php");
include_once("./functions/item_type.php");
include_once("./functions/item_input.php");
include_once("./functions/utils.php");
include_once("./functions/status_type.php");
include_once("./functions/widgets.php");

class Item
{
	var $_item_type = NULL;
	var $_title = NULL;
	var $_attribute_rs = NULL;

	function Item()
	{
	}

	function setItemType($s_item_type)
	{
		$this->_item_type = $s_item_type;
	}
	
	function setTitle($title)
	{
		$this->_title = $title;
	}
	
    function addAttribute($attribute_type, $order_no, $value)
	{
		// where value is array, recursively call the addAttribute function,
		// so rest of function can assume string $value.
		if(is_array($value))
		{
			for($i=0; $i<count($value); $i++)
			{
				$this->addAttribute($attribute_type, $order_no, $value[$i]);
			}
		}
		else
		{
			// trim the value before anything else.
			$value = trim($value);

			if(strlen($value)>0)
			{
				if(!is_array($this->_attribute_rs))
					$this->_attribute_rs = array();

				if(isset($this->_attribute_rs[$attribute_type]))
				{
					if(!is_array($this->_attribute_rs[$attribute_type]))
					{
						// do not add duplicates
						if($this->_attribute_rs[$attribute_type] != $value)
						{
							$tmpvalue = $this->_attribute_rs[$attribute_type];

							$this->_attribute_rs[$attribute_type] = array();
							$this->_attribute_rs[$attribute_type][] = $tmpvalue;

							// add new value to array
							$this->_attribute_rs[$attribute_type][] = $value;
						}
					}
					else
					{
						// do not add duplicates
						if(array_search2($value, $this->_attribute_rs[$attribute_type]) === FALSE)
						{
							$this->_attribute_rs[$attribute_type][] = $value;
						}
					}
				}
				else // otherwise single value only
				{
					$this->_attribute_rs[$attribute_type] = $value;
				}
			}//if(strlen($value)>0)
		}
	}
	
	function getItemType()
	{
		return $this->_item_type;
	}
	
	function getTitle()
	{
		return $this->_title;
	}
	
	function getAttributes()
	{
		return $this->_attribute_rs;
	}
}

class ItemInstance extends Item
{
	var $_owner_id;
    var $_status_type;
	var $_status_comment;
	var $_borrow_duration;
	var $_instance_no;
    
    function ItemInstance(&$parentItemObj, $owner_id, $status_type, $status_comment, $borrow_duration)
    {
        parent::Item();
        
        $this->setItemType($parentItemObj->getItemType());
        $this->setTitle($parentItemObj->getTitle());
        
        $this->_owner_id = $owner_id;
        $this->_status_type = $status_type;
        $this->_status_comment = $status_comment;
        $this->_borrow_duration = $borrow_duration;
	}
	
	function setInstanceNo($instance_no)
	{
	    $this->_instance_no = $instance_no;
	}
	
	function getInstanceNo()
	{
		return $this->_instance_no;
	}
	
	function getOwnerID()
	{
		return $this->_owner_id;
	}
	
	function getStatusType()
	{
		return $this->_status_type;
	}
	
	function getStatusComment()
	{
		return $this->_status_comment;
	}
	
	function getBorrowDuration()
	{
		return $this->_borrow_duration;
	}
}
  
class ItemImportHandler
{
	var $_errors;

	var $_success_row_count = 0;
	var $_failure_row_count = 0;
	
	// keep track of all items added to the database, just item_id's will do
	var $_item_id_list_r;
	
	var $_item_obj;
	var $_instance_item_obj_rs;

    // indicator of whether we are operating on a instance context or not.  If
	// so, then the last Item in the $_instance_item_obj_rs array will be used.
    var $_is_item_instance;
    
	// state info - what context are we inserting for!	
	var $_owner_id;
	
	var $_listingsObject;
	
	// Once final endItem is called, this is set to TRUE.  Until clear() is called
	// no operations against this item are allowed.
	var $_is_item_finished;

    // save the s_attribute_type/order_no structure once in an array, so we
	// do not have to continually query the database.
	var $_item_type_structure_rs;

	var $_cfg_is_trial_run;
	var $_cfg_ignore_duplicate_titles;
    var $_cfg_override_status_type;
    var $_cfg_default_status_type_r;
	
	/*
	* @param $owner_id - Will be used for item_instance insert, as well as validation
	* 					of any owner insert restrictions.
	*/
	function ItemImportHandler($owner_id, $cfg_is_trial_run, $cfg_ignore_duplicate_title, $cfg_override_status_type, $cfg_default_status_type_r, &$listingsObject)
	{
        $this->_owner_id = $owner_id;
		$this->_cfg_ignore_duplicate_title = $cfg_ignore_duplicate_title;
		$this->_cfg_is_trial_run = $cfg_is_trial_run;
		$this->_cfg_override_status_type = $cfg_override_status_type;
		$this->_cfg_default_status_type_r = $cfg_default_status_type_r;
		
		$this->_listingsObject =& $listingsObject;
		
		$this->clear();
	}
	
	function addError($method, $error, $dberror = NULL)
	{
		$this->_errors[] = array('method'=>$method, 'error'=>$error, 'dbdetails'=>$dberror);
	}
	
	function getErrors()
	{
		if(is_not_empty_array($this->_errors))
		{
			for($i=0; $i<count($this->_errors); $i++)
			{
				$errors[] = $this->_errors[$i]['method'].': '.$this->_errors[$i]['error'].' '.( (strlen($this->_errors[$i]['dbdetails'])>0)?' ['.$this->_errors[$i]['dbdetails'].']':'');
			}
			
			return $errors;
		}
		else
		{
			return NULL;
		}
	}
	
	function getRawErrors()
	{
		return $this->_errors;
	}
	
	function isError()
	{
		return is_not_empty_array($this->_errors);
	}
	
	function getSuccessRowCount()
	{
		return $this->_success_row_count;
	}
	
	function getFailureRowCount()
	{
		return $this->_failure_row_count;
	}
	
	/*
	* Reset Error condition, and internal parser structures.
	*/
	function clear()
	{
		$this->_errors = NULL;
		$this->_item_obj = NULL;
		$this->_instance_item_obj_rs = NULL;
		
		$this->_is_item_instance = FALSE;
		$this->_is_item_finished = FALSE;
	}
	
	function getItemIDList()
	{
		return $this->_item_id_list_r;
	}
	
	function getOwner()
	{
		return $this->_owner_id;
	}
	
	function &getItem()
	{
		return $this->_item_obj;
	}
	
	function &getInstanceItems()
	{
		return $this->_instance_item_obj_rs;
	}
	
	function startItem($s_item_type, $title = NULL)
	{
		if($this->_is_item_finished)
		{
			// clear parser structures for new item.					
			$this->clear();
		}
		
		// reset instance indicator
		$this->_is_item_instance = FALSE;
           
		$this->_item_obj =& new Item();
		$this->_item_obj->setItemType($s_item_type);
		$this->_item_obj->setTitle($title);
		return TRUE;
	}
	
	function setTitle($title)
	{
		if($this->_is_item_finished !== TRUE)
		{
			if($this->_item_obj != NULL)
			{
				$this->_item_obj->setTitle($title);
			}
			else
			{
				$this->addError('setTitle', get_opendb_lang_var('undefined_error'));
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	*/
	function startItemInstance($s_status_type = NULL, $status_comment = NULL, $borrow_duration = NULL)
	{
		if($this->_is_item_finished !== TRUE)
		{
			if($this->_item_obj != NULL)
			{
				$this->_instance_item_obj_rs[] = new ItemInstance($this->_item_obj, $this->_owner_id, $s_status_type, $status_comment, $borrow_duration);
				$this->_is_item_instance = TRUE;
			}
			else
			{
				$this->addError('startItemInstance', get_opendb_lang_var('undefined_error'));
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	This function is only required to be called where there are multiple instances per item, otherwise calling
	endItem is enough.
	*/
	function endItemInstance()
	{
        if($this->_is_item_finished !== TRUE)
		{
        	$this->_is_item_instance = FALSE;
		}
		else // if($this->isError() !== TRUE)
		{
			return FALSE;
		}
	}
	
	/*
	* 
	*/
	function itemAttribute($s_attribute_type, $order_no, $attribute_val)
	{
		if($this->_is_item_finished !== TRUE)
		{
			if($this->_item_obj != NULL)
			{
				if($this->_is_item_instance)
                    $tmpItem =& $this->_instance_item_obj_rs[count($this->_instance_item_obj_rs) - 1];
				else
					$tmpItem =& $this->_item_obj;
				
                $tmpItem->addAttribute($s_attribute_type, $order_no, $attribute_val);
			}//if($this->_item_obj != NULL)
			else
			{
				$this->addError('itemAttribute', get_opendb_lang_var('undefined_error'));
				return FALSE;
			}
		}
		else // if($this->isError() !== TRUE)
		{
			return FALSE;
		}
	}

	/*
	*/
	function endItem()
	{
		if($this->_is_item_finished !== TRUE)
		{
			if($this->_item_obj != NULL)
			{
				// instance was not closed, close it now!
				if($this->_is_item_instance)
				    $this->_is_item_instance = FALSE;

				// if not item instance, create one
				if(is_empty_array($this->_instance_item_obj_rs))
				{
				    $this->startItemInstance();
				    $this->endItemInstance();
				}
					
				// The item is finished, no more additions are allowed, until the
				// startItem method is called again.
				$this->_is_item_finished = TRUE;

				$item_vars = $this->getItemHTTPVars($this->_item_obj);

				$item_vars['trial_run'] = $this->_cfg_is_trial_run?'true':'false';
				$item_vars['confirmed'] = $this->_cfg_ignore_duplicate_title?'true':'false';

				$item_r = array(
						s_item_type=>$this->_item_obj->getItemType(),
						owner_id=>$this->_owner_id
				);
				
				$instance_valid = FALSE;
				
				$dummy_POST_FILES = NULL;
				$errors = NULL;
				
				$return_val = handle_item_insert($item_r, $item_vars, $dummy_POST_FILES, $errors);
				if($return_val !== FALSE)
				{
					// store item id for later use
					if($this->_cfg_is_trial_run!==TRUE && is_numeric($item_r['item_id']))
						$this->_item_id_list_r[] = $item_r['item_id'];

					for($i=0; $i<count($this->_instance_item_obj_rs); $i++)
					{
					    $instanceObj =& $this->_instance_item_obj_rs[$i];
						
                           // if status type is to be overriden, do it here!
						if($this->_cfg_override_status_type)
							$status_type_r = $this->_cfg_default_status_type_r;
						else
						{
						    $status_type_r = fetch_status_type_r($instanceObj->getStatusType());

							// if illegal type, then override by default.
							if($status_type_r['closed_ind'] == 'Y' || $status_type_r['insert_ind'] != 'Y')
							{
							    $status_type_r = $this->_cfg_default_status_type_r;
							}
						}

						$item_r['owner_id'] = $instanceObj->getOwnerID();
						$item_r['s_status_type'] = $status_type_r['s_status_type'];
						
				    	$instance_vars = array_merge($item_vars, $this->getItemHTTPVars($instanceObj));
				    	
				    	$return_val = handle_item_instance_insert($item_r, $status_type_r, $instance_vars, $errors);
				    	if($return_val !== FALSE)
				    	{
				    	    $instanceObj->setInstanceNo($this->_cfg_is_trial_run?$i+1:$item_r['instance_no']);
							$this->listing_item_import_result_row($instanceObj, $status_type_r, $instance_vars, NULL);
							
							// indicates at least one instance inserted.
							$instance_valid = TRUE;
				    	}
				    	else
				    	{
				    	    $instanceObj->setInstanceNo($this->_cfg_is_trial_run?$i+1:$item_r['instance_no']);
							$this->listing_item_import_result_row($instanceObj, $status_type_r, $instance_vars, $errors);
				    	}
					}
				}
				else
				{
				    $this->listing_item_import_result_row($this->_item_obj, NULL, $item_vars, $errors);
				}
                   
				$this->_item_obj = NULL;
                   
				// end of parent item.
				return TRUE;
			}
			else
			{
			    $this->_item_obj = NULL;
			    
				$this->addError('endItem', get_opendb_lang_var('undefined_error'));
				return FALSE;
			}
		}
		else // if($this->_is_item_finished !== TRUE)
		{
		    return FALSE;
		}
	}

	function listing_item_import_result_row($itemObj, $status_type_r, $attribute_rs, $errorMsg = NULL)
	{
		$this->_listingsObject->startRow();

		if(is_not_empty_array($errorMsg) || strlen($errorMsg) > 0)
		{
			$this->_listingsObject->addThemeImageColumn("cross.gif");
			$this_failure_row_count++;
		}
		else
		{
			$this->_listingsObject->addThemeImageColumn("tick.gif");
			$this->_success_row_count++;
		}
		
		$this->_listingsObject->addItemTypeImageColumn(
				$itemObj->getItemType());

		if(strcasecmp(get_class($itemObj),'ItemInstance')===0)
	        $instance_no = $itemObj->getInstanceNo();
        
		// We have to include the title, instance, etc.
		$this->_listingsObject->addColumn(
				$itemObj->getTitle().(is_numeric($instance_no) && $instance_no>1?'&nbsp;#'.$instance_no:''));

		if(is_not_empty_array($errorMsg) || strlen($errorMsg) > 0)
		{
			$this->_listingsObject->addColumn(format_error_block($errorMsg, 'smerror'));
			$this->_listingsObject->addColumn();
		}
		else
		{
		    // if override status type, then we are not showing this column
		    if($this->_cfg_override_status_type!==TRUE)
		    {
				if(is_array($status_type_r))
				{
					$this->_listingsObject->addThemeImageColumn(
								$status_type_r['img'],
								$status_type_r['description'],
								$status_type_r['description'], //title
								's_status_type');
				}
				else
				{
					$this->_listingsObject->addColumn('', 1);
				}
			}
			
			$column = '';
			if(is_array($attribute_rs))
			{
				$column .= '<table>';
				reset($attribute_rs);
				while(list($key,$attribute_val) = each($attribute_rs))
				{
				    $attribute_type_r = NULL;
				    for($j=0; $j<count($this->_item_type_structure_rs[$itemObj->getItemType()]); $j++)
				    {
				        if($key == get_field_name($this->_item_type_structure_rs[$itemObj->getItemType()][$j]['s_attribute_type'], $this->_item_type_structure_rs[$itemObj->getItemType()][$j]['order_no']))
				        {
				            $attribute_type_r = $this->_item_type_structure_rs[$itemObj->getItemType()][$j];
				            break;
				        }
				    }
				    
					if(is_array($attribute_type_r))
				    {
				    	if($attribute_type_r['display_type'] == 'hidden' ||
									($attribute_type_r['display_type']==0 && $attribute_type_r['input_type'] == 'hidden'))
						{
							$attribute_type_r['display_type'] = 'display';
							$attribute_type_r['display_type_arg1'] = '%value%';
						}

						$column .= get_item_display_field(NULL, $attribute_type_r, $attribute_val);
					}
				}
				$column .= '</table>';
			}
			$this->_listingsObject->addColumn($column);
		}
		
		$this->_listingsObject->endRow();
	}

	/**
	*/
	function getItemHTTPVars($itemObj)
	{
        if($this->_save_item_type_attrib_details($itemObj->getItemType()))
        {
			// this will be set if array encountered, but not lookup value.
			$processing_s_attribute_type = FALSE;

            $new_attributes_rs = $itemObj->getAttributes();
            
            reset($this->_item_type_structure_rs[$itemObj->getItemType()]);
			while(list(,$attribute_type_r) = each($this->_item_type_structure_rs[$itemObj->getItemType()]))
			{
                if($attribute_type_r['s_field_type']!='DURATION' &&
						$attribute_type_r['s_field_type']!='TITLE' &&
						$attribute_type_r['s_field_type']!='STATUSTYPE' &&
						$attribute_type_r['s_field_type']!='STATUSCMNT' &&
						$attribute_type_r['s_field_type']!='ITEM_ID')
                {
                	$fieldname = get_field_name($attribute_type_r['s_attribute_type'], $attribute_type_r['order_no']);
                	
					if(isset($new_attributes_rs[$attribute_type_r['s_attribute_type']]))
					{
						// TODO: Consider adding values not found in the lookup table to the s_attribute_type_lookup.
						if($attribute_type_r['lookup_attribute_ind'] == 'Y')
						{
						    // reset
						    $value_r = NULL;
						    
							// here is where we want some sanity checking of the options
							if(is_not_empty_array($new_attributes_rs[$attribute_type_r['s_attribute_type']]))
								$value_r = $new_attributes_rs[$attribute_type_r['s_attribute_type']];
							else
							    $value_r[] = $new_attributes_rs[$attribute_type_r['s_attribute_type']];

							$lookup_value_r = array();
							for($i=0; $i<count($value_r); $i++)
							{
								$raw_value = trim($value_r[$i]);
								if(strlen($raw_value)>0)
								{
									$value = fetch_attribute_type_lookup_value($attribute_type_r['s_attribute_type'], $raw_value);
									if($value !== FALSE)
										$lookup_value_r[] = $value;
									else
										$lookup_value_r[] = $raw_value;
								}
							}
							$item_attributes_rs[$fieldname] = $lookup_value_r;
						}
						else
						{
						    if(is_not_empty_array($new_attributes_rs[$attribute_type_r['s_attribute_type']]))
						    {
								// This indicates we have a repeated s_attribute_type, and so should act appropriately.
								if($processing_s_attribute_type!=NULL && $attribute_type_r['s_attribute_type'] == $processing_s_attribute_type)
								{
									$item_attributes_rs[$fieldname] = $new_attributes_rs[$attribute_type_r['s_attribute_type']][0];
									array_splice( $new_attributes_rs[$attribute_type_r['s_attribute_type']], 0, 1);
								}
								else if(count($new_attributes_rs[$attribute_type_r['s_attribute_type']])>1)
								{
									// this is the first occurence of the s_attribute_type, so lets see if its repeated at least once.
									if(is_numeric(fetch_s_item_attribute_type_next_order_no($itemObj->getItemType(), $attribute_type_r['s_attribute_type'], $attribute_type_r['order_no'])))
									{
										$item_attributes_rs[$fieldname] = $new_attributes_rs[$attribute_type_r['s_attribute_type']][0];

										array_splice( $new_attributes_rs[$attribute_type_r['s_attribute_type']], 0, 1);

										$processing_s_attribute_type = $attribute_type_r['s_attribute_type'];
									}
									else
									{
										// otherwise just copy the whole thing.
										$item_attributes_rs[$fieldname] = $new_attributes_rs[$attribute_type_r['s_attribute_type']];
									}
								}
								else
								{
									$item_attributes_rs[$fieldname] = $new_attributes_rs[$attribute_type_r['s_attribute_type']][0];
								}
							}
							else if(!is_array($new_attributes_rs[$attribute_type_r['s_attribute_type']]))
							{
								$item_attributes_rs[$fieldname] = $new_attributes_rs[$attribute_type_r['s_attribute_type']];
							}
						}
					}
				}
				else
				{
                    // instance class
					if(strcasecmp(get_class($this->_item_obj),'ItemInstance')===0)
					{
					    if($attribute_type_r['s_field_type'] == 'DURATION' && is_numeric($itemObj->getBorrowDuration()))
					        $item_attributes_rs['borrow_duration'] = $itemObj->getBorrowDuration();
						else if($attribute_type_r['s_field_type'] == 'STATUSTYPE')
						    $item_attributes_rs['s_status_type'] = $itemObj->getStatusType();
						else if($attribute_type_r['s_field_type'] == 'STATUSCMNT' && strlen($itemObj->getStatusComment())>0)
						    $item_attributes_rs['status_comment'] = $itemObj->getStatusComment();
					}
					else
					{
					    if($attribute_type_r['s_field_type'] == 'TITLE')
					    {
					        $item_attributes_rs['title'] = $itemObj->getTitle();
					    }
					}
	   			}
			}//while

			return $item_attributes_rs;
		}//if($this->_save_item_type_attrib_details($s_item_type))
		else
		{
			return FALSE;
		}
	}//function

    // return FALSE if item does not exist.
	function _save_item_type_attrib_details($s_item_type)
	{
		// save the structure of the item type once, including whether s_attribute_type is a lookup or not.
		if(is_empty_or_not_array($this->_item_type_structure_rs[$s_item_type]))
		{
			if(is_exists_item_type($s_item_type))
			{
				$results = fetch_item_attribute_type_rs($s_item_type, NULL, 's_attribute_type');
				if($results)
				{
					while($item_attribute_type_r = db_fetch_assoc($results))
					{
						// save s_item_attribute_type structure.
						$this->_item_type_structure_rs[$s_item_type][] = $item_attribute_type_r;
					}
					db_free_result($results);
				}

				return TRUE;
			}
			else
			{
				// s_item_type does not exist.
				return FALSE;
			}
		}
		else
		{
			return TRUE;
		}
	}
}
?>