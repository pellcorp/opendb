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

/**
@param $filename - assumes its been basenamed
*/
function get_upload_file_url($item_id, $instance_no, $s_attribute_type, $order_no, $attribute_no, $filename)
{
	return "file://opendb/upload/${item_id}/".(is_numeric($instance_no)?$instance_no:0)."/${s_attribute_type}/${order_no}/${attribute_no}/$filename";
}

/**
	Example:
		file://opendb/upload/123/2/IMAGEURL/1/1/JasonPell.doc
*/
function parse_upload_file_url($url)
{
	if(preg_match("!file://opendb/upload/([\d]+)/([\d]+)/([^/]+)/([\d]+)/([\d]+)/([^\$]+)!", $url, $matches))
	{
		return array(
			'item_id'=>$matches[1],
			'instance_no'=>$matches[2],
			's_attribute_type'=>$matches[3],
			'order_no'=>$matches[4],
			'attribute_no'=>$matches[5],
			'filename'=>$matches[6]);
	}
	else
	{
		return NULL;
	}
}

/**
 * Optionally expands URL for file upload cached files.
 */
function fetch_10_upload_file_cache_r($item_attrib_r)
{
	if(!is_url_absolute($item_attrib_r['attribute_val']) && !file_exists($item_attrib_r['attribute_val']))
	{
		$url = get_upload_file_url(
					$item_attrib_r['item_id'],
					$item_attrib_r['instance_attribute_ind']?$item_attrib_r['instance_no']:"0",
					$item_attrib_r['s_attribute_type'],
					$item_attrib_r['order_no'],
					'1', // attribute_no - file attributes cannot be multivalue - so this will always be 1
					$item_attrib_r['attribute_val']);
	
		return fetch_url_file_cache_r($url, 'ITEM', INCLUDE_EXPIRED);
	}
	else
	{	
		return FALSE;
	}
}

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
		$uploadDir = get_item_input_file_upload_directory();
		if(!is_writable($uploadDir))
		{
			$this->addError('Upload directory is not writable', $uploadDir);
			return FALSE;
		}
		
		$query = "SELECT DISTINCT ia.item_id, 
				ia.instance_no, 
				ia.s_attribute_type,
				ia.order_no, 
				ia.attribute_val, 
				ia.attribute_no,
				siat.instance_attribute_ind
		FROM 	item_attribute ia,
				s_attribute_type sat,
				s_item_attribute_type siat
		WHERE	sat.s_attribute_type = siat.s_attribute_type AND
				siat.s_attribute_type = ia.s_attribute_type AND
				siat.order_no = siat.order_no AND
				ia.s_attribute_type = sat.s_attribute_type AND
				sat.file_attribute_ind = 'Y' AND
				ia.attribute_val NOT LIKE '%://%'
		ORDER BY ia.item_id, ia.instance_no, ia.order_no, ia.attribute_no";
						
		$item_attrib_rs = NULL;
		$results = db_query($query);
		if($results)
		{
			while($item_attrib_r = db_fetch_assoc($results))
			{
				$fc_entry_r = fetch_10_upload_file_cache_r($item_attrib_r);
				if($fc_entry_r!==FALSE) {
					$fc_entry_rs[] = array_merge($fc_entry_r, $item_attrib_r);
				} else {
					// no uploaded file so ignore?
				}
			}
			db_free_result($results);
		}
		
		if(is_array($fc_entry_rs))
		{
			$previous_filename_r = array();
			
			$directory = file_cache_get_cache_type_directory('ITEM');
			while(list(,$fc_entry_r) = each($fc_entry_rs))
			{
				$cacheFile = $directory.'/'.$fc_entry_r['cache_file'];
				if(file_exists($cacheFile))
				{
					if(in_array($fc_entry_r['attribute_val'], $previous_filename_r))
					{
						$file_r = get_root_filename($fc_entry_r['attribute_val']);
						$filename = generate_unique_filename($file_r, $previous_filename_r);
		
						if($filename != $fc_entry_r['attribute_val'])
						{
							opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, "Upload file already exists - generating a unique filename", array($fc_entry_r['attribute_val'], $filename));
							
							if(!update_item_attribute($fc_entry_r['item_id'],
											$fc_entry_r['instance_no'],
											$fc_entry_r['s_attribute_type'],
											$fc_entry_r['order_no'],
											$fc_entry_r['attribute_no'],
											NULL,
											$filename))
							{
								$this->addError('Failed to update attribute', 
												'item_id='.$fc_entry_r['item_id'].
												'; s_attribute_type='.$fc_entry_r['s_attribute_type'].
												'; order_no='.$fc_entry_r['order_no'].
												'; $filename='.$fc_entry_r['$filename']
											);
							}
						}
					}
					else
					{
						$filename = $fc_entry_r['attribute_val'];
					}

					$previous_filename_r[] = $filename;
					
					// NOTE - we are not going to delete the cache files they can be removed later on manually
					$uploadFile = $uploadDir.'/'.$filename;
					if(!copy($cacheFile, $uploadFile) && is_file($uploadFile)) // call me paranoid!!!
					{
						$this->addError('Failed to copy upload file', 
									'cacheFile='.$cacheFile.
									'; uploadFile='.$uploadFile);
					}
				}
			}
		}
		
		return TRUE;
	}
	
	function executeStep7($stepPart)
	{
		db_query("ALTER TABLE item DROP parent_id");
		return TRUE;
	}
}
?>