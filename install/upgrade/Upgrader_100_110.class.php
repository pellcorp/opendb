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
include_once("./functions/filecache.php");

class Upgrader_100_110 extends OpenDbUpgrader
{
	function Upgrader_100_110()
	{
		parent::OpenDbUpgrader(
						'1.0',
						'1.1.0',
						array(
							array('description'=>'New tables and system data changes'),
							array('description'=>'New Related Status Type'),
							array('description'=>'Transfer Linked Items'),
							array('description'=>'Transfer Email Addresses'),
							array('description'=>'Cleanup Email address system data'),
							array('description'=>'Transfer uploaded files from File Cache'),
							array('description'=>'Finalise upgrade')
						)
					);
	}
	
	function executeStep2($stepPart)
	{
		return exec_install_sql_file("./admin/s_status_type/sql/R-Related.sql", $errors);
	}
	
	/**
	 * Create a item_instance for every item that has a parent_id set.  Then create
	 * a item_instance_relationship to link it to all the parent item instances.  Finally
	 * drop the parent_id column.
	 */
	function executeStep3($stepPart)
	{
		$results = db_query(
					"SELECT ii.item_id, ii.instance_no, ii.owner_id, i.id AS related_item_id
					FROM	item i,
							item_instance ii 
					WHERE	i.parent_id = ii.item_id AND 
							i.parent_id IS NOT NULL");
		if($results)
		{
			while($item_instance_r = db_fetch_assoc($results))
			{
				if(!is_exists_item_instance($item_instance_r['related_item_id'], 1))
				{
					insert_item_instance(
							$item_instance_r['related_item_id'], 
							1, //$instance_no, 
							'R', //$s_status_type, 
							NULL, //$status_comment, 
							NULL, //$borrow_duration, 
							$item_instance_r['owner_id']);
				}
					
				insert_item_instance_relationship(
							$item_instance_r['item_id'], 
							$item_instance_r['instance_no'], 
							$item_instance_r['related_item_id'], 
							1);
			}
			db_free_result($results);
		}
		
		return TRUE;
	}
	
	function executeStep4($stepPart)
	{
		$results = db_query(
					"SELECT ua.user_id, uaa.attribute_val
					FROM user_address ua, user_address_attribute uaa
					WHERE ua.sequence_number = uaa.ua_sequence_number AND 
					ua.s_address_type = 'EMAIL' AND
					ua.start_dt <= NOW() AND (ua.end_dt IS NULL OR ua.end_dt < NOW())");
		if($results)
		{
			$result = TRUE;

			while($addr_attr_r = db_fetch_assoc($results))
			{
				if(is_valid_email_addr($addr_attr_r['attribute_val'])) {
					if(db_query("UPDATE user SET email_addr = '".$addr_attr_r['attribute_val']."'
								WHERE user_id = '".$addr_attr_r['user_id']."'") === FALSE )
					{
						$this->addError(
								'User '.$addr_attr_r['user_id'].' email address ('.$addr_attr_r['attribute_val'].') not transferred',
								db_error());
								
						$result = FALSE;
					}
				}
			}
			
			return $result;
		}
		
		return TRUE;
	}
	
	/**
	 * @param unknown_type $stepPart
	 */
	function executeStep6($stepPart)
	{
		// need to copy all uploaded records from file_cache into upload directory creating using a 
		// unique filename
		$uploadDir = get_item_input_file_upload_directory();
		if(!is_writable($uploadDir))
		{
			$this->addError('Upload directory is not writable', $uploadDir);
			return FALSE;
		}
		
		$query = "SELECT fc.sequence_number, 
						fc.cache_type,
						fc.cache_date,
						fc.expire_date,
						fc.url,
						fc.location,
						fc.upload_file_ind,
						fc.content_type,
						fc.content_length,
						fc.cache_file, 
						fc.cache_file_thumb, 
						ia.item_id, 
						ia.instance_no, 
						ia.s_attribute_type, 
						ia.order_no, 
						ia.attribute_val, 
						ia.attribute_no
						FROM file_cache fc,
						item_attribute ia
						WHERE fc.upload_file_ind = 'Y' AND fc.cache_type = 'ITEM' AND
						fc.url = CONCAT( 'file://opendb/upload/', ia.item_id, '/', ia.instance_no, '/', ia.s_attribute_type, '/', ia.order_no, '/', ia.attribute_no, '/', ia.attribute_val )";

		$items_per_page = 50;
		$start_index = 0; //$stepPart > 0 ? ($stepPart * $items_per_page) : 0;
		$query .= ' LIMIT ' .$start_index. ', ' .($items_per_page + 1);
		$count = 0;
		
		$fc_attrib_rs = NULL;
		$results = db_query($query);
		if($results)
		{
			while($fc_attrib_r = db_fetch_assoc($results))
			{
				if($count < $items_per_page)
				{
					$fc_attrib_rs[] = $fc_attrib_r;
					$count++;
				}
			}
			db_free_result($results);
		}
		
		if(is_array($fc_attrib_rs))
		{
			$directory = file_cache_get_cache_type_directory('ITEM');
			while(list(,$fc_attrib_r) = each($fc_attrib_rs))
			{
				$cacheFile = $directory.'/'.$fc_attrib_r['cache_file'];
				if(file_exists($cacheFile))
				{
					// todo - how to get unique filename
					$filename = $fc_attrib_r['attribute_val'];
					
					if($filename != $fc_attrib_r['attribute_val'])
					{
						if(!update_item_attribute($fc_attrib_r['item_id'],
										$fc_attrib_r['instance_no'],
										$fc_attrib_r['s_attribute_type'],
										$fc_attrib_r['order_no'],
										$fc_attrib_r['attribute_no'],
										NULL,
										$filename))
						{
							$this->addError('Failed to update attribute', 
											'item_id='.$fc_attrib_r['item_id'].
											'; s_attribute_type='.$fc_attrib_r['s_attribute_type'].
											'; order_no='.$fc_attrib_r['order_no'].
											'; $filename='.$fc_attrib_r['$filename']
										);
						}
					}				
					
					$uploadFile = $uploadDir.'/'.$filename;
					if(copy($cacheFile, $uploadFile) && is_file($uploadFile)) // call me paranoid!!!
					{
						// fake it, so that the delete_file_cache function goes to the right spot.
						$fc_attrib_r['upload_file_ind'] = 'N';
				
						delete_file_cache($fc_attrib_r);
					}
					else
					{
						$this->addError('Failed to copy upload file', 
									'cacheFile='.$cacheFile.
									'; uploadFile='.$uploadFile);
					}
				}
				else
				{
					delete_file_cache($fc_attrib_r);
				}
			}
		}
		
		if($count == $items_per_page)// still at least one result left
			return -1; // unfinished
		else		
			return TRUE;
	}
	
	function executeStep7($stepPart)
	{
		db_query("ALTER TABLE item DROP parent_id");
		return TRUE;
	}
}
?>