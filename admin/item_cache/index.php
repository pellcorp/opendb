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

This script produces a table, one row per HTTP cache file, sorted by
descending expiry date (freshest file at the top, oldest at the bottom).

TODO: only print "delete all stale entries" if there are stale entries.
Tricky (ugly) to do without putting the link after the list, which would
force users to scroll to the bottom to find it.
*/
include_once("./functions/datetime.php");
include_once("./functions/filecache.php");
include_once("./functions/item_attribute.php");
include_once("./functions/listutils.php");
include_once("./functions/HTML_Listing.class.inc");

// todo - provide a bit more info when the process fails, at least log details
// to log file!!!

/**
	Repeatedly call the AJAX URL until the remainder job/result/remainder == 0
	or status of job returned is FAILURE
*/
function display_job_form($job)
{
	global $ADMIN_TYPE;
	
	$url = 'admin.php?type='.$ADMIN_TYPE.'&mode=job&op=job&job='.$job;
	
	$gsimage = _theme_image_src('gs.gif');
	$rsimage = _theme_image_src('rs.gif');
	
 ?>
	<script src="./scripts/prototype/prototype.js" language="JavaScript" type="text/javascript"></script>
	<script src="./admin/item_cache/ajaxjobs.js" language="JavaScript" type="text/javascript"></script>

	<script language="JavaScript">
	var updateFunc = function(job)
	{
		var percentage = document.getElementById("percentage");
		var messsage = document.getElementById("message");
	
		var element = document.getElementById("status");
		if(!job.cancelled)
		{
			var images = element.getElementsByTagName("img");
			var count = job.outOfTen();
	
			for(var i=0; i<count; i++)
			{
				images[i].src = '<?php echo $rsimage; ?>';
			}
			
			percentage.innerHTML = job.percentage()+"%";
			
			if(job.finished)
			{
				message.innerHTML = 'Complete...';
				document.getElementById("cancelButton").disabled = true;	
			}
		}
		else if(job.exception)
		{
			messsage.innerHTML = "<div class=\"error\">"+job.exception+"</div>";
			
			document.getElementById("startButton").disabled = false;
			document.getElementById("cancelButton").disabled = true;	
		}
	}
	
	function resetStatus()
	{
		percentage.innerHTML = '0%';
		message.innerHTML = 'Working...';
		
		var element = document.getElementById("status");
		var images = element.getElementsByTagName("img");
		for(var i=0; i<10; i++)
		{
			images[i].src = '<?php echo $gsimage; ?>';
		}
	}
	
	function cancelStatus()
	{
		message.innerHTML = 'Stopping, Please wait.';
	}
	</script>
	
	<br>
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
	
	<form>
	<input type="button" id="startButton" name="start" onclick="this.disabled=true; this.form.cancel.disabled=false; resetStatus(); executeAdminJob(new Job(1, '<?php echo $url ?>', updateFunc));" value="Start" />
	<input type="button" id="cancelButton" name="cancel" onclick="this.disabled=true; cancelStatus(); getJobById(1).cancel('Stopped');" value="Cancel" DISABLED="DISABLED" />
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
			if($HTTP_VARS['mode'] == 'job')
			{
				header("Cache-control: no-store");
				header("Pragma: no-store");
				header("Expires: 0");
				header("Content-disposition: inline");
				header("Content-type: application/xml");
				
				//todo - this is messy, but for now with only a few jobs it will do!
				if($HTTP_VARS['job'] == 'update')
					echo perform_update_cache_batch();
				else if($HTTP_VARS['job'] == 'refresh')
					echo perform_refresh_cache_batch();
				else if($HTTP_VARS['job'] == 'refresh_thumbnails')
					echo perform_refresh_thumbnails_batch();
			}
			else
			{
				echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main List</a>]</div>");
				
				//todo - this is messy, but for now with only a few jobs it will do!
				if($HTTP_VARS['job'] == 'update')
					echo("\n<h3>Update Item Cache</h3>");
				else if($HTTP_VARS['job'] == 'refresh')
					echo("\n<h3>Refresh Item Cache files</h3>");
				else if($HTTP_VARS['job'] == 'refresh_thumbnails')
					echo("\n<h3>Refresh Item Cache Thumbnail files</h3>");
	
				display_job_form($HTTP_VARS['job']);
			}
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
			echo get_popup_javascript();
			
			echo("<p>");
			
			if(fetch_file_cache_new_item_attribute_cnt() > 0)
			{
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=update\">Update</a>]&nbsp;");
			}
			
			if(fetch_file_cache_refresh_cnt('ITEM') > 0)
			{
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=refresh\">Refresh</a>]&nbsp;");
			}
			
			if(fetch_file_cache_missing_thumbs_cnt('ITEM') > 0)
			{
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=refresh_thumbnails\">Refresh Thumbnails</a>]&nbsp;");
			}
			
			if(fetch_file_cache_missing_file_cnt('ITEM') > 0 || fetch_file_cache_item_attribute_orphans_cnt() > 0)
			{
				echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=delete\">Delete Orphans</a>]&nbsp;");
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
					
					//$listingObject->addColumn($file_cache_r['sequence_number']);
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
							"<a href=\"$hrefUrl\" onclick=\"popup('url.php?url=".urlencode($file_cache_r['url'])."&cache_type=${HTTP_VARS['cache_type']}', 400, 300); return false;\">".
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