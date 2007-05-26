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

/**
	Return a very simple XML fragment which indicates success or failure of update
*/
function perform_update_cache_batch()
{
	$buffer = '';
	
	// todo - make this configurable but not by the administrator
	$limit = 10;
	
	$processed = 0;
	$unprocessed = 0;
		
	$new_item_cnt = fetch_file_cache_new_item_attribute_cnt();
	
	$results = fetch_file_cache_new_item_attribute_rs();
	if($results)
	{
		while($item_attribute_r = db_fetch_assoc($results))
		{
			// if URL happens to have been inserted by someone else before we get to the current
			// row, then this function will do nothing, and thats ok.
			if(file_cache_insert_file($item_attribute_r['attribute_val'], NULL, NULL, NULL, 'ITEM', FALSE))
			{
				$processed++;
			}
			else
			{
				$unprocessed++;
			}
			
			// don't process anymore this time around.
			if($processed >= $limit)
			{
				break;
			}
		}
		db_free_result($results);
	}
		
	$new_item_cnt = fetch_file_cache_new_item_attribute_cnt();
	
	return '<job>
				<name>Update Item Cache</name>
				<params>
					<batchsize>'.$limit.'</batchsize>
				</params>
				<result>
					<status>'.($processed==0 && $unprocessed > 0?'FAILURE':'SUCCESS').'</status>
					<failures>'.$unprocessed.'</failures>
					<processed>'.$processed.'</processed>
					<unprocessed>'.$new_item_cnt.'</unprocessed>
				</result>
			</job>';
}

function perform_refresh_cache_batch()
{
	$buffer = '';
	
	$limit = 10;
				
	$processed = 0;
	$unprocessed = 0;	
	
	$results = fetch_file_cache_refresh_rs('ITEM');
	if($results)
	{
		while($file_cache_r = db_fetch_assoc($results))
		{
			// in this case we want to refresh the URL, so TRUE as last parameter idicates overwrite
			if(file_cache_insert_file($file_cache_r['url'], NULL, NULL, NULL, 'ITEM', TRUE))
			{
				$processed++;
			}
			else
			{
				$unprocessed++;
			}
			
			// don't process anymore this time around.
			if($processed >= $limit)
			{
				break;
			}
		}
		db_free_result($results);
	}
		
	$refresh_item_cnt = fetch_file_cache_refresh_cnt('ITEM');
		
	return '<job>
				<name>Refresh Item Cache</name>
				<params>
					<batchsize>'.$limit.'</batchsize>
				</params>
				<result>
					<status>'.($processed==0 && $unprocessed > 0?'FAILURE':'SUCCESS').'</status>
					<failures>'.$unprocessed.'</failures>
					<processed>'.$processed.'</processed>
					<unprocessed>'.$refresh_item_cnt.'</unprocessed>
				</result>
			</job>';
}

function perform_refresh_thumbnails_batch()
{
	$buffer = '';
	
	$limit = 10;
				
	$processed = 0;
	$unprocessed = 0;
	
	$results = fetch_file_cache_rs('ITEM');
	if($results)
	{
		while($file_cache_r = db_fetch_assoc($results))
		{
			// its not a case of only a thumbnail, if not even the source exists
			if(file_cache_get_cache_file($file_cache_r)!==FALSE && 
					file_cache_get_cache_file_thumbnail($file_cache_r)===FALSE)
			{
				// in this case we want to refresh the URL, so TRUE as last parameter idicates overwrite
				if(file_cache_save_thumbnail_file($file_cache_r, $errors))
				{
					$processed++;
				}
				else
				{
					$unprocessed++;
				}
				
				// don't process anymore this time around.
				if($processed >= $limit)
				{
					break;
				}
			}
		}
		db_free_result($results);
	}
		
	$missing_thumbs_cnt = fetch_file_cache_missing_thumbs_cnt('ITEM');
	
	return '<job>
				<name>Refresh Item Thumbnail Cache</name>
				<params>
					<batchsize>'.$limit.'</batchsize>
				</params>
				<result>
					<status>'.($processed==0 && $unprocessed > 0?'FAILURE':'SUCCESS').'</status>
					<failures>'.$unprocessed.'</failures>
					<processed>'.$processed.'</processed>
					<unprocessed>'.$missing_thumbs_cnt.'</unprocessed>
				</result>
			</job>';
}
?>