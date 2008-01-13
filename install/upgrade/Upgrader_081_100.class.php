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

include_once("./functions/OpenDbUpgrader.class.php");

function get_rebuilt_function($function_name, $arguments_r)
{
    if(!is_array($arguments_r))
    {
        if($function_name !== NULL)
        {
            if(strlen($arguments_r)==0)
                return $function_name;
            else
                return $function_name.'('.$arguments_r.')';
        }
        else 
            return $arguments_r;
    }
    else
    {
        $argsText = '';

        @reset($arguments_r);
        while(list(,$argument) = @each($arguments_r))
        {
            // If argument includes a comma, or is whitespace (For a delimiter for example),
            // it must be enclosed in quotes.
            if( (strlen($argument)>0 && strlen(trim($argument))==0) || strpos($argument, ',') !== FALSE)
                $argument = "\"".str_replace("\"", "\\\"", $argument)."\"";
            else if(strpos($argument, '"')!==FALSE)
                $argument = str_replace("\"", "\\\"", $argument);
                
            if(strlen($argsText)==0)
                $argsText .= $argument;
            else
                $argsText .= ', '.$argument;
        }

        if($function_name !== NULL)
        {
            if(strlen($argsText)==0)
                return $function_name;
            else
                return $function_name.'('.$argsText.')';
        }
        else 
            return $argsText;
    }
}

function is_exists_081_item_attribute_table()
{
	$result = db_query("SELECT 'x' FROM item_attribute_old LIMIT 0,1");
	if($result)
	{
		db_free_result($result);
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function fetch_lowest_item_attribute_type_order_no($item_type, $attribute_type)
{
	$query = "SELECT MIN(order_no) as min_order_no FROM s_item_attribute_type 
					WHERE s_item_type = '$item_type' AND s_attribute_type = '$attribute_type'";
	
	$result = db_query($query);

	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		if ($found)
		{
			db_free_result($result);
			return $found['min_order_no'];
		}
	}

	return FALSE;
}

function fetch_all_item_attributes($item_id, $s_attribute_type)
{
	$query = "SELECT item_id, order_no, attribute_val FROM item_attribute_old 
			WHERE item_id = $item_id AND s_attribute_type = '$s_attribute_type' ORDER BY order_no ASC";
	
	$result = db_query($query);

	$attribute_type_rs = array();
	if($result && db_num_rows($result)>0)
	{
		while($attribute_type_r = db_fetch_assoc($result))
		{
			$attribute_type_rs[] = $attribute_type_r['attribute_val'];
		}
		db_free_result($result);
	}
	
	return $attribute_type_rs;		
}

function is_exists_10_item_attribute_table()
{
	$result = db_query("SELECT attribute_no FROM item_attribute LIMIT 0,1");
	if($result)
	{
		db_free_result($result);
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function is_exists_081_item_table()
{
	$result = db_query("SELECT category FROM item LIMIT 0,1");
	if($result)
	{
		db_free_result($result);
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function is_exists_081_user_address_attribute_table()
{
	$result = db_query("SELECT 'x' FROM user_address_attribute_old LIMIT 0,1");
	if($result)
	{
		db_free_result($result);
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function is_exists_10_user_address_attribute_table()
{
	$result = db_query("SELECT attribute_no FROM user_address_attribute LIMIT 0,1");
	if($result)
	{
		db_free_result($result);
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function create_new_item_attribute_table(&$errors)
{
	if(db_query("RENAME TABLE item_attribute TO item_attribute_old"))
	{
		// delete invalid record first.
		//db_query("DELETE FROM item_attribute_old WHERE 
		//		(attribute_val IS NULL OR LENGTH( attribute_val ) = 0) AND
		//		(lookup_attribute_val IS NULL OR LENGTH( lookup_attribute_val ) = 0)");

		if(db_query("CREATE TABLE item_attribute (
		  		item_id				INTEGER(10) UNSIGNED NOT NULL,
  				instance_no			SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
		  		s_attribute_type		VARCHAR(10) NOT NULL,
  				order_no				TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
		  		attribute_no 			TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
  				lookup_attribute_val		VARCHAR(50),
		  		attribute_val			TEXT,
  				update_on				TIMESTAMP(14) NOT NULL,
		  	PRIMARY KEY ( item_id, instance_no, s_attribute_type, order_no, attribute_no )
			) TYPE=MyISAM COMMENT='Item Attribute table'"))
		{
			return TRUE;
		}
		else
		{
			$errors[] = array('error'=>'Did not update item attribute table','detail'=>db_error());
			return FALSE;
		}
	}
	else
	{
		$errors[] = array('error'=>'Did not create new item attribute table','detail'=>db_error());
		return FALSE;
	}
}

function create_new_user_address_attribute_table(&$errors)
{
	if(db_query("RENAME TABLE user_address_attribute TO user_address_attribute_old"))
	{
		if(db_query("CREATE TABLE user_address_attribute (
				  ua_sequence_number	INTEGER(10) unsigned NOT NULL,
				  s_attribute_type		VARCHAR(10) NOT NULL,
				  order_no				TINYINT(3) unsigned NOT NULL,
				  attribute_no 			TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
				  lookup_attribute_val 	VARCHAR(50),
				  attribute_val			TEXT,
				  update_on				TIMESTAMP(14) NOT NULL,
				  PRIMARY KEY ( ua_sequence_number, s_attribute_type, order_no, attribute_no )
				) TYPE=MyISAM COMMENT='User address attribute';"))
		{
			return TRUE;
		}
		else
		{
			$errors[] = array('error'=>'Did not update user address attribute table','detail'=>db_error());
			return FALSE;
		}
	}
	else
	{
		$errors[] = array('error'=>'Did not update user address attribute table','detail'=>db_error());
		return FALSE;
	}
}

function update_s_attribute_type_lookup_ind(&$errors)
{
    $errors = NULL;

	$lookup_attribute_type_r = array();
	$results = db_query("SELECT DISTINCT s_attribute_type FROM s_attribute_type_lookup");
	if($results)
	{
		while($attribute_type_r = db_fetch_assoc($results))
		{
            if(!db_query("UPDATE s_attribute_type SET lookup_attribute_ind = 'Y' WHERE s_attribute_type = '".$attribute_type_r['s_attribute_type']."'"))
            {
				$errors[] = array('error'=>'Did not update lookup_attribute_ind','detail'=>db_error());
            }
		}
		db_free_result($results);
	}

	if(is_array($errors))
	    return FALSE;
	else
		return TRUE;
}

function transfer_user_address_attributes(&$errors)
{
    $errors = NULL;

	$results = db_query(
		"SELECT uaao.ua_sequence_number, 
				uaao.s_attribute_type, 
				uaao.order_no, 
				uaao.lookup_attribute_val, 
				uaao.attribute_val, 
				uaao.update_on 
		FROM	user_address_attribute_old uaao
		ORDER BY uaao.ua_sequence_number, uaao.order_no, uaao.lookup_attribute_val");
		
	if($results)
	{
		$curr_attribute_type_r = NULL;
		$attribute_val_r = NULL;
		
		while($ua_attribute_type_r = db_fetch_assoc($results))
		{
		    if($curr_attribute_type_r == NULL)
		    {
		        $curr_attribute_type_r = $ua_attribute_type_r;
		    }
			else if($curr_attribute_type_r['ua_sequence_number'] != $ua_attribute_type_r['ua_sequence_number'] ||
					$curr_attribute_type_r['s_attribute_type'] != $ua_attribute_type_r['s_attribute_type'] ||
					$curr_attribute_type_r['order_no'] != $ua_attribute_type_r['order_no'])
			{
				if(is_array($attribute_val_r))
				{
					if(!insert_user_address_attributes(
							$curr_attribute_type_r['ua_sequence_number'],
							$curr_attribute_type_r['s_attribute_type'], 
							$curr_attribute_type_r['order_no'], 
							$attribute_val_r))
					{
					    $errors[] = array('error'=>'User Address (sequence number = '.$curr_attribute_type_r['s_attribute_type'].') Attribute(s) ('.$curr_attribute_type_r['s_attribute_type'].') not transferred','detail'=>db_error());
					}
				}
				
				$curr_attribute_type_r = $ua_attribute_type_r;
				$attribute_val_r = NULL;
			}
			
			if(strlen($ua_attribute_type_r['lookup_attribute_val'])>0)
			{
			    $attribute_val_r[] = $ua_attribute_type_r['lookup_attribute_val'];
			}
			else
			{
			    $attribute_val_r[] = trim($ua_attribute_type_r['attribute_val']);
			}
		}
		db_free_result($results);
		
		if(is_array($attribute_val_r))
		{
			if(!insert_user_address_attributes(
					$curr_attribute_type_r['ua_sequence_number'], 
					$curr_attribute_type_r['s_attribute_type'], 
					$curr_attribute_type_r['order_no'], 
					$attribute_val_r))
			{
			    $errors[] = array('error'=>'User Address (sequence number = '.$curr_attribute_type_r['s_attribute_type'].') Attribute(s) ('.$curr_attribute_type_r['s_attribute_type'].') not transferred','detail'=>db_error());
			}
		}
	}

	if(is_array($errors))
	    return FALSE;
	else
		return TRUE;
}

/**
*/
function transfer_item_category($stepPart, &$errors)
{
	$query = "SELECT i.id AS item_id, i.category, siat.s_item_type, siat.s_attribute_type, siat.order_no 
			FROM 	item i, s_item_attribute_type siat, s_attribute_type sat 
			WHERE 	i.s_item_type = siat.s_item_type AND 
					siat.s_attribute_type = sat.s_attribute_type AND 
					sat.s_field_type = 'CATEGORY' ";
	
	$items_per_page = 1000;
	$start_index = $stepPart > 0 ? ($stepPart * $items_per_page) : 0;
	$query .= ' LIMIT ' .$start_index. ', ' .($items_per_page + 1);
	$count = 0;

	$results = db_query($query);
	if($results)
	{
	    while($item_attr_r = db_fetch_assoc($results))
		{
			if($count < $items_per_page)
			{
				$count++;

			    if(strlen(trim($item_attr_r['category']))>0)
			    {
		    		$attribute_val_r = trim_explode(' ', trim($item_attr_r['category']));
		    		if(!insert_item_attributes($item_attr_r['item_id'], NULL, $item_attr_r['s_item_type'], $item_attr_r['s_attribute_type'], $item_attr_r['order_no'], $attribute_val_r))
					{
		    			$errors[] = array('error'=>'Attribute(s) ('.$item_attr_r['s_attribute_type'].') for item '.$item_attr_r['item_id'].' not transferred','detail'=>db_error());
					}
				}
			}
	    }
	    db_free_result($results);
	}

    if(is_array($errors))
	    return FALSE;
	else if($count == $items_per_page)// still at least one result left
		return -1; // unfinished
	else		
		return TRUE;
}

/**
 * except for CDTRACK
 *
 * @param unknown_type $stepPart
 * @param unknown_type $errors
 * @return unknown
 */
function transfer_item_attributes($stepPart, &$errors)
{
    $errors = NULL;

	$query = "SELECT iao.item_id, i.s_item_type, iao.s_attribute_type, iao.order_no, iao.lookup_attribute_val, iao.attribute_val, iao.update_on 
			FROM item_attribute_old iao, item i 
			WHERE i.id = iao.item_id AND s_attribute_type NOT IN ('CDTRACK', 'ACTORS', 'DIRECTOR', 'AUTHOR', 'ARTIST')
			ORDER BY iao.item_id, iao.order_no, iao.lookup_attribute_val";
	
	$items_per_page = 1000;
	$start_index = $stepPart > 0 ? ($stepPart * $items_per_page) : 0;
	$query .= ' LIMIT ' .$start_index. ', ' .($items_per_page + 1);
	$count = 0;
	
	// disable file cache functionality, otherwise item attribute migration process will take far too long.
	set_opendb_config_ovrd_var('http.item.cache', 'enable', FALSE);
	
	$results = db_query($query);
	if($results)
	{
		$num_results = db_num_rows($results);
		
		$curr_attribute_type_r = NULL;
		$attribute_val_r = NULL;
		
		while($item_attribute_type_r = db_fetch_assoc($results))
		{
			if($count < $items_per_page)
			{
				$count++;
				
			    if($curr_attribute_type_r == NULL)
			    {
			        $curr_attribute_type_r = $item_attribute_type_r;
			    }
			
				// if last record or change of attribute 
				if($count == $num_results ||
						$curr_attribute_type_r['item_id'] != $item_attribute_type_r['item_id'] ||
						$curr_attribute_type_r['s_attribute_type'] != $item_attribute_type_r['s_attribute_type'] ||
						$curr_attribute_type_r['order_no'] != $item_attribute_type_r['order_no'])
				{
					if(is_not_empty_array($attribute_val_r))
					{
						if(!insert_item_attributes($curr_attribute_type_r['item_id'], NULL, $curr_attribute_type_r['s_item_type'], $curr_attribute_type_r['s_attribute_type'], $curr_attribute_type_r['order_no'], $attribute_val_r))
						{
						    $errors[] = array('error'=>'Attribute(s) ('.$curr_attribute_type_r['s_attribute_type'].') for item '.$curr_attribute_type_r['item_id'].' not transferred','detail'=>db_error());
						}
					}
					
					$curr_attribute_type_r = $item_attribute_type_r;
					$attribute_val_r = NULL;
				}
				
				if(strlen(trim($item_attribute_type_r['lookup_attribute_val']))>0)
				{
				    $attribute_val_r[] = $item_attribute_type_r['lookup_attribute_val'];
				}
				else if(strlen(trim($item_attribute_type_r['attribute_val']))>0)
				{
				    $attribute_val_r[] = trim($item_attribute_type_r['attribute_val']);
				}
			}
		}
		db_free_result($results);
	}

    if(is_array($errors))
	    return FALSE;
	else if($count == $items_per_page)// still at least one result left
		return -1; // unfinished
	else		
		return TRUE;
}

function transfer_actors_directors_artists_authors_item_attributes($stepPart, &$errors)
{
    $query = "SELECT s_attribute_type, item_id, order_no, attribute_val 
			FROM item_attribute_old ia, item i 
			WHERE i.id = ia.item_id AND 
			ia.s_attribute_type IN('ACTORS', 'DIRECTOR', 'AUTHOR', 'ARTIST')";
	
	$items_per_page = 1000;
	$start_index = $stepPart > 0 ? ($stepPart * $items_per_page) : 0;
	$query .= ' LIMIT ' .$start_index. ', ' .($items_per_page + 1);
	$count = 0;
	
	$results = db_query($query);
	if($results)
	{
	    while($item_attribute_r = db_fetch_assoc($results))
		{
			if($count < $items_per_page)
			{
				$count++;
				
	    		$attribute_val_r = trim_explode(',', trim($item_attribute_r['attribute_val']));
	    		
	    		if(is_not_empty_array($attribute_val_r) && strlen($attribute_val_r[0])>0)
				{
	    			if(!insert_item_attributes($item_attribute_r['item_id'], NULL, $item_attribute_r['s_item_type'], $item_attribute_r['s_attribute_type'], $item_attribute_r['order_no'], $attribute_val_r))
					{
	    				$errors[] = array('error'=>'Attribute(s) ('.$item_attribute_r['s_attribute_type'].') for item '.$item_attribute_r['item_id'].' not transferred', 'detail'=>db_error());
					}
				}
			}
			else
			{
				break;
			}
		}
		db_free_result($results);
	}

    if(is_array($errors))
	    return FALSE;
	else if($count == $items_per_page)// still at least one result left
		return -1; // unfinished
	else		
		return TRUE;
}

function convert_s_attribute_widget_types()
{
	$item_attribute_type_rs = NULL;
	$query = "SELECT s_attribute_type, display_type, input_type FROM s_attribute_type";
	$results = db_query($query);
	if($results)
	{
	    while($item_attribute_r = db_fetch_assoc($results))
		{
			$item_attribute_type_rs[] = $item_attribute_r;
		}
		db_free_result($results);
	}
	
	while(list(,$item_attribute_type_r) = each($item_attribute_type_rs))
	{
		if($item_attribute_type_r['display_type'] == '%display%')
			$display_widget = prc_function_spec('display(%display%)');	
		else
			$display_widget = prc_function_spec($item_attribute_type_r['display_type']);
			
		$input_widget = prc_function_spec($item_attribute_type_r['input_type']);
		
		$query = "UPDATE s_attribute_type SET display_type = '".$display_widget['type']."', 
				input_type = '".$input_widget['type']."' ";

		for($i=0; $i<count($input_widget['args']); $i++)
		{
			$query .= ', input_type_arg'.($i+1)." = '".$input_widget['args'][$i]."'";
		}

		$new_display_args = array();
		// lets get rid of the list-link argument first
		for($i=0; $i<count($display_widget['args']); $i++)
		{
			if($display_widget['args'][$i] == 'list-link')
			{
				$query .= ", listing_link_ind = 'Y'";
			}
			else
			{
				$new_display_args[] = $display_widget['args'][$i];
			}
		}
		
		for($i=0; $i<count($new_display_args); $i++)
		{
			$query .= ', display_type_arg'.($i+1)." = '".$new_display_args[$i]."'";
		}
		
		$query .= " WHERE s_attribute_type = '".$item_attribute_type_r['s_attribute_type']."'";

		if(!db_query($query))
		{
			return FALSE;
		}
	}
	return TRUE;
}

/**
 * Find all item attribute type relationships where more than 1 cd track instance is attached,
 * 		record the item type and the order_no of the first CDTRACK relationship
 * Query all items of the specific type, for each item query all CDTRACKS
 */
function convert_cdtracks_item_attributes($stepPart, &$errors)
{
	$query = "SELECT DISTINCT ia.item_id, i.s_item_type, ia.s_attribute_type
					FROM item_attribute_old ia, item i
					WHERE i.id = ia.item_id
					AND ia.s_attribute_type = 'CDTRACK'";
	
	$items_per_page = 75;
	$start_index = $stepPart > 0 ? ($stepPart * $items_per_page) : 0;
	$query .= ' LIMIT ' .$start_index. ', ' .($items_per_page + 1);
	$count = 0;
	
	$results = db_query($query);
	
	// for a particular s_item_type, we want to find the minimum order_no and insert multiple attributes into it, ignore the lowest
	// value of the item_attribute because there might be some invalid data.
	if($results && db_num_rows($results)>0)
	{
		$min_order_no_by_item_type = array();
		
   		while($item_attribute_r = db_fetch_assoc($results))
		{
			if($count < $items_per_page)
			{
				$count++;
				
				if(!is_numeric($min_order_no_by_item_type[$item_attribute_r['item_type']]))
				{
					$min_order_no_by_item_type[$item_attribute_r['item_type']] = 
						fetch_lowest_item_attribute_type_order_no($item_attribute_r['s_item_type'], $item_attribute_r['s_attribute_type']);
				}
				
				$min_order_no = $min_order_no_by_item_type[$item_attribute_r['item_type']];
				
				$attribute_val_r = fetch_all_item_attributes($item_attribute_r['item_id'], $item_attribute_r['s_attribute_type']);
				if(is_not_empty_array($attribute_val_r) && strlen($attribute_val_r[0])>0)
				{
					if(!insert_item_attributes($item_attribute_r['item_id'], NULL, $item_attribute_r['s_item_type'], $item_attribute_r['s_attribute_type'], $min_order_no, $attribute_val_r))
					{
			    		$errors[] = array('error'=>'Attribute(s) ('.$item_attribute_r['s_attribute_type'].') for item '.$item_attribute_r['item_id'].' not transferred', 'detail'=>db_error());
					}
				}
			}
			else
			{
				break; 
			}
		}
		db_free_result($results);
	}
	
	if(is_array($errors))
	    return FALSE;
	else if($count == $items_per_page)// still at least one result left
		return -1; // unfinished
	else		
		return TRUE;
}

function remove_cdtrack_s_item_attribute_types()
{
	$results = db_query("SELECT s_item_type FROM s_item_type");
	if($results)
	{
		while($item_type_r = db_fetch_assoc($results))
		{
			$min_order_no = fetch_lowest_item_attribute_type_order_no($item_type_r['s_item_type'], 'CDTRACK');
			
			db_query("DELETE from s_item_attribute_type 
					WHERE s_item_type = '".$item_type_r['s_item_type']."' AND 
					s_attribute_type = 'CDTRACK' AND 
					order_no <> ".$min_order_no);
		}
		
		db_free_result($results);
	}
}

/**
	This step will do quite a lot of processing, as follows:

	Convert item attribute display type column for:
		urlpopup, urlpopup2 - replace first argument with %img% value% and set to fileviewer

	Convert item attribute input type column for:
		upload, saveurl, upload_or_saveurl, url to a simpler url widget with simpler arguments:
			url(length[, maxlength][, content_group|extension list])
*/
function transfer_url_attribute_types(&$errors)
{
    $results = db_query("SELECT s_attribute_type, input_type, display_type FROM s_attribute_type WHERE
    			input_type LIKE 'saveurl%' OR
    			input_type LIKE 'upload%' OR
    			input_type LIKE 'url%' OR
    			display_type LIKE 'urlpopup%'");

	if($results)
	{
		$attribute_type_rs = NULL;
	    while($attribute_type_r = db_fetch_assoc($results))
		{
			$attribute_type_rs[] = $attribute_type_r;
		}
		db_free_result($results);

		while(list(,$attribute_type_r) = each($attribute_type_rs))
		{
			$updated = FALSE;
	
	        if(starts_with($attribute_type_r['input_type'], "saveurl") ||
	         		starts_with($attribute_type_r['input_type'], "upload") ||
					starts_with($attribute_type_r['input_type'], "url"))
			{
				$widget = prc_function_spec($attribute_type_r['input_type']);
	
				// only want length, maxlength, content_group or extensions list
				// TODO - consider auto-converting extensions list to a content group
				$arguments_r = array($widget['args']['0'], $widget['args']['1'], $widget['args']['2']);
				$attribute_type_r['input_type'] = get_rebuilt_function('url', $arguments_r);
	
				$updated = TRUE;
			}
	
			if(starts_with($attribute_type_r['display_type'], "urlpopup"))
			{
				$widget = prc_function_spec($attribute_type_r['display_type']);
	
				$arguments_r = array('%img% %value%', $widget['args']['1'], $widget['args']['2']);
				$attribute_type_r['display_type'] = get_rebuilt_function('fileviewer', $arguments_r);
	
				$updated = TRUE;
			}
	
			if($updated)
			{
		    	if(!db_query("UPDATE s_attribute_type
					SET input_type = '".addslashes($attribute_type_r['input_type'])."',
					display_type = '".addslashes($attribute_type_r['display_type'])."'
					WHERE s_attribute_type = '".$attribute_type_r['s_attribute_type']."'"))
				{
					$this->addErrors(array('error'=>'URL attribute(s) ('.$attribute_type_r['s_attribute_type'].') not converted','detail'=>db_error()));
	        		return FALSE;
				}
			}
		}
	}
	
	return TRUE;
}

class Upgrader_081_100 extends OpenDbUpgrader
{
	function Upgrader_081_100()
	{
		parent::OpenDbUpgrader(
						'0.81',
						'1.0b8',
						array(
							array('description'=>'Database changes'),
							array('description'=>'Configure Lookup Attributes Types'),
							array('description'=>'Item Attribute Table update'),
							array('description'=>'Transfer Item Category'),
							array('description'=>'Convert url, saveurl, upload, urlpopup, urlpopup2, etc'),
							array('description'=>'Transfer Actors, Directors, Artists, Authors as multi-value'),
							array('description'=>'Attribute type input and display type changes'),
							array('description'=>'Transfer CDTRACK attributes as multi-value'),
							array('description'=>'Convert User Address Attributes'),
							array('description'=>'Default Language Pack'),
							array('description'=>'Finalise upgrade')
						)
					);
	}
	
	function executeStep2($stepPart)
	{
	    if(update_s_attribute_type_lookup_ind($errors))
	    {
			return TRUE;
		}
		else
		{
			$this->addErrors($errors);
			return FALSE;
		}
	}

	function executeStep3($stepPart)
	{
		if(!is_exists_10_item_attribute_table())
		{
			if(!create_new_item_attribute_table($errors))
			{
				$this->addErrors($errors);
				return FALSE;
			}
		}
		
		// only perform upgrade if old table exists, otherwise already been converted
		if(is_exists_081_item_attribute_table() && is_exists_10_item_attribute_table())
		{
			$result = transfer_item_attributes($stepPart, $errors);
	        $this->addErrors($errors);
			return $result;
			
		}
		else
		{
			return TRUE;
		}
	}
	
	function executeStep4($stepPart)
	{
		if(is_exists_081_item_table())
		{
			$result = transfer_item_category($stepPart, $errors);
	        $this->addErrors($errors);
			return $result;
		}
		else
		{
			return TRUE;
		}
	}

	function executeStep5($stepPart)
	{
		if(transfer_url_attribute_types($errors))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function executeStep6($stepPart)
	{
		if($stepPart == 0)
		{
			db_query("UPDATE s_attribute_type
				SET display_type = 'list(names, \",\", list-link)', multi_attribute_ind = 'Y'
				WHERE s_attribute_type IN('ACTORS', 'DIRECTOR', 'AUTHOR', 'ARTIST')");
		}
		
		$result = transfer_actors_directors_artists_authors_item_attributes($stepPart, $errors);
		$this->addErrors($errors);
		return $result;
	}

	function executeStep7($stepPart)
	{
		if(convert_s_attribute_widget_types())
	    {
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function executeStep8($stepPart)
	{
		db_query("UPDATE s_attribute_type SET multi_attribute_ind = 'Y' WHERE s_attribute_type = 'CDTRACK'");
		
		$result = convert_cdtracks_item_attributes($stepPart, $errors);
		
		if($result === TRUE)
		{
			remove_cdtrack_s_item_attribute_types();
			db_query("UPDATE s_item_attribute_type SET prompt = NULL WHERE s_attribute_type = 'CDTRACK'");
			
			db_query("UPDATE s_attribute_type 
					SET display_type = 'list', 
						display_type_arg1 = 'ordered',
						display_type_arg2 = NULL,
						display_type_arg3 = NULL,
						display_type_arg4 = NULL,
						display_type_arg5 = NULL 
					WHERE s_attribute_type = 'CDTRACK'");
		}
		
		$this->addErrors($errors);
		return $result;
	}
	
	function executeStep9($stepPart)
	{
		if(!is_exists_10_user_address_attribute_table())
		{
			if(create_new_user_address_attribute_table($errors))
			{
        		if(transfer_user_address_attributes($errors))
	    		{
	        		return TRUE;
				}
				else
				{
					$this->addErrors($errors);
					return FALSE;
				}
			}
			else
			{
				$this->addErrors($errors);
				return FALSE;
			}
		}
	    else
	    {
			return TRUE;
	    }
	}

	function executeStep10($stepPart)
	{
		if(exec_install_sql_file("./admin/s_language/sql/english.sql", $errors))
		{
			return TRUE;
		}
		else
		{
			$this->addErrors($errors);
			return FALSE;
		}
	}

	function executeStep11($stepPart)
	{
		db_query("DROP TABLE item_attribute_old");
		db_query("DROP TABLE user_address_attribute_old");
		db_query("ALTER TABLE item DROP category");
		db_query("ALTER TABLE s_attribute_type CHANGE input_type input_type VARCHAR(20)");
		db_query("ALTER TABLE s_attribute_type CHANGE display_type display_type VARCHAR(20)");
		
		// a hack to get this fix in
		db_query("UPDATE s_attribute_type SET input_type_arg3 = '0-9 \\-+' WHERE s_attribute_type = 'PHONE_NO'");
		
		return TRUE;
	}
}
?>