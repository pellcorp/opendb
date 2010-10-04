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

if(!defined('OPENDB_ADMIN_TOOLS'))
{
	die('Admin tools not accessible directly');
}

include_once("./functions/datetime.php");
include_once("./functions/filecache.php");
include_once("./functions/item_attribute.php");
include_once("./functions/listutils.php");
include_once("./functions/HTML_Listing.class.inc");

@set_time_limit(600);

$HTTP_VARS['cache_type'] = 'ITEM';

if($HTTP_VARS['op'] == 'job')
{
	echo("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</p>");
	
	if($HTTP_VARS['job'] == 'update')
		echo("\n<h3>Update Item Cache</h3>");
	else if($HTTP_VARS['job'] == 'refresh')
		echo("\n<h3>Refresh Item Cache files</h3>");
	else if($HTTP_VARS['job'] == 'refresh_thumbnails')
		echo("\n<h3>Refresh Item Cache Thumbnail files</h3>");

	$jobObj->printJobProgressBar();
}
else if($HTTP_VARS['op'] == 'delete')
{
	if($HTTP_VARS['confirmed'] == 'true')
	{
		// this function is really slow, and rather pointless anyway.
		//file_cache_delete_orphan_item_cache();
		file_cache_delete_orphans($HTTP_VARS['cache_type']);
		
		$HTTP_VARS['op'] = '';
	}
	else if($HTTP_VARS['confirmed'] != 'false')
	{
		echo("\n<h3>Delete Orphaned Cache files</h3>");
		echo(get_op_confirm_form($PHP_SELF, 
				"Are you sure you want to delete cache files?", 
				array('type'=>$ADMIN_TYPE, 'op'=>'delete')));
	}
	else
	{
		$HTTP_VARS['op'] = '';
	}
}

if($HTTP_VARS['op'] == '')
{
	echo("<p>");
	
	if(fetch_file_cache_new_item_attribute_cnt() > 0)
	{
		echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=update\">Update</a>] ");
	}
	
	if(fetch_file_cache_refresh_cnt('ITEM') > 0)
	{
		echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=refresh\">Refresh</a>] ");
	}
	
	if(fetch_file_cache_missing_thumbs_cnt('ITEM') > 0)
	{
		echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=refresh_thumbnails\">Refresh Thumbnails</a>] ");
	}
	
	// the item attribute orphan count is really slow, so do not use it.
	if(fetch_file_cache_missing_file_cnt('ITEM') > 0)// ||  
					//fetch_file_cache_item_attribute_orphans_cnt() > 0)
	{
		echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=delete\">Delete Orphans</a>] ");
	}
	
	echo("</p>");
			
	if(strlen($HTTP_VARS['order_by'])==0)
		$HTTP_VARS['order_by'] = 'cache_date';

	$listingObject =& new HTML_Listing($PHP_SELF, $HTTP_VARS);
	$listingObject->setNoRowsMessage(get_opendb_lang_var('no_items_found'));
	
	$listingObject->startListing();
	
	//$listingObject->addHeaderColumn('Sequence. No.', 'sequence_number');
	$listingObject->addHeaderColumn('URL', 'url');
	$listingObject->addHeaderColumn('Thumbnail', 'thumbnail', FALSE);
	$listingObject->addHeaderColumn('Cached', 'cache_date');
	$listingObject->addHeaderColumn('Expires', 'expire_date');

	if(is_numeric($listingObject->getItemsPerPage()))
	{		
		$listingObject->setTotalItems(fetch_file_cache_cnt($HTTP_VARS['cache_type']));
	}
	
	$results = fetch_file_cache_rs(
				$HTTP_VARS['cache_type'],
				$listingObject->getCurrentOrderBy(), 
				$listingObject->getCurrentSortOrder(), 
				$listingObject->getStartIndex(),
				$listingObject->getItemsPerPage());
	if($results)
	{
		while($file_cache_r = db_fetch_assoc($results))
		{
			$listingObject->startRow();
			
			if(file_cache_get_cache_file($file_cache_r))
			{
				$hrefUrl = "url.php?id=".$file_cache_r['sequence_number'];
				
				if(!is_url_absolute($file_cache_r['url']))
				{
					$url = get_item_input_file_upload_url($file_cache_r['url']);
					if($url!==FALSE)
						$hrefUrl = $url;
				}
				
				$listingObject->addColumn(
					"<a href=\"".$hrefUrl."\" target=\"_new\">".
					get_overflow_tooltip_column($file_cache_r['url'], 100).
					"</a>");
			}
			else
			{
				$listingObject->addColumn(get_overflow_tooltip_column($file_cache_r['url'], 100));
			}
			
			if(file_cache_get_cache_file_thumbnail($file_cache_r))
			{
				$listingObject->addThemeImageColumn('tick.gif');
			}
			else
			{
				$listingObject->addThemeImageColumn('cross.gif');
			}
			
			$listingObject->addColumn(get_localised_timestamp(get_opendb_config_var('http', 'datetime_mask'), $file_cache_r['cache_date']));
			
			$column = '';
			if($file_cache_r['expired_ind']=='Y')
			{
				$column .= "<span class=\"error\">";
			}
			
			if($file_cache_r['expire_date']!=NULL)
			{
				$column .= get_localised_timestamp(get_opendb_config_var('http', 'datetime_mask'), $file_cache_r['expire_date']);
			}
			else
			{
				$column .= "NA";
			}
			
			if($file_cache_r['expired_ind']=='Y')
			{
				$column .= "</span>";
			}	
			$listingObject->addColumn($column);
			
			$listingObject->endRow();
		}//while
		db_free_result($results);
	}
	
	$listingObject->endListing();
	unset($listingObject);
}
?>