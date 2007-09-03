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

include_once("./functions/datetime.php");
include_once("./functions/filecache.php");
include_once("./functions/item_attribute.php");
include_once("./functions/listutils.php");
include_once("./functions/HTML_Listing.class.inc");

/**
*/
function display_job_form($job)
{
	$gsimage = _theme_image_src('gs.gif');
 ?>
	<div id="status" style="{width:300; margin: 4px}">
	<table width=100% border=0>
	<tr>
		<td align=center colspan=10 id="message" class="error">&nbsp;</td>
	</tr>
	<tr>
	<td><img id="status1" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status2" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status3" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status4" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status5" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status6" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status7" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status8" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status9" src="<?php echo $gsimage; ?>"></td>
	<td><img id="status10" src="<?php echo $gsimage; ?>"></td>
	</tr>
	<tr>
		<td align=center colspan=10 id="percentage">0%</td>
	</tr>
	</table>
	
	<form id="progressForm">
		<input type="hidden" name="continue" value="true" />
		<input type="button" id="startButton" value="Start" 
				onclick="this.form['continue'].value='true'; xajax_doJob('<?php echo $job; ?>', 'true', '0', '0'); this.value='Working...'; this.disabled=true; return false;" />
		<input type="button" id="cancelButton" value="Cancel" 
				onclick="this.form['continue'].value='false'; this.disabled=true; " />
	</form>
	</div>
<?php		
}
	
session_start();
if (is_opendb_valid_session())
{ 
	@set_time_limit(600);
	
	if (is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
		$HTTP_VARS['cache_type'] = 'ITEM';
		
		if($HTTP_VARS['op'] == 'job')
		{
			echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main List</a>]</div>");
			
			if($HTTP_VARS['job'] == 'update')
				echo("\n<h3>Update Item Cache</h3>");
			else if($HTTP_VARS['job'] == 'refresh')
				echo("\n<h3>Refresh Item Cache files</h3>");
			else if($HTTP_VARS['job'] == 'refresh_thumbnails')
				echo("\n<h3>Refresh Item Cache Thumbnail files</h3>");

			display_job_form($HTTP_VARS['job']);
		}
		else if($HTTP_VARS['op'] == 'delete')
		{
			if($HTTP_VARS['confirmed'] == 'true')
			{
				file_cache_delete_orphan_item_cache();
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
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=update\">Update</a>]&nbsp;");
			}
			
			if(fetch_file_cache_refresh_cnt('ITEM') > 0)
			{
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=refresh\">Refresh</a>]&nbsp;");
			}
			
			// TODO - these things are slow...

			//if(fetch_file_cache_missing_thumbs_cnt('ITEM') > 0)
			//{
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=refresh_thumbnails\">Refresh Thumbnails</a>]&nbsp;");
			//}
			
			//if(fetch_file_cache_missing_file_cnt('ITEM') > 0 || fetch_file_cache_item_attribute_orphans_cnt() > 0)
			//{
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=delete\">Delete Orphans</a>]&nbsp;");
			//}
			
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
						if($file_cache_r['upload_file_ind'] == 'Y')
						{
							$hrefUrl = "url.php?url=".urlencode($file_cache_r['url']);
						}
						else
						{
							$hrefUrl = $file_cache_r['url'];
						}
						
						$listingObject->addColumn(
							"<a href=\"$hrefUrl\" target=\"_new\">".
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
	}
}//(is_opendb_valid_session())
?>