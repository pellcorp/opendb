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

include_once("./functions/utils.php");
include_once("./functions/datetime.php");
include_once("./functions/http.php");
include_once("./functions/user.php");
include_once("./functions/review.php");
include_once("./functions/borrowed_item.php");
include_once("./functions/item.php");
include_once("./functions/widgets.php");
include_once("./functions/item_type.php");
include_once("./functions/listutils.php");
include_once("./functions/status_type.php");
include_once("./functions/export.php");
include_once("./functions/scripts.php");
include_once("./functions/item_attribute.php");
include_once("./functions/TitleMask.class.php");
include_once("./functions/item_display.php");
include_once("./functions/site_plugin.php");

/**
	@selected will be currently selected record.

	$borrow_duration is the item_instance.borrow_duration value in all cases, the rest of the values to do
	with borrow duration will be calculated.
*/
function get_item_status_row($class, $item_r, $listing_link, $selected)
{
	global $HTTP_VARS;
	global $PHP_SELF;
	global $titleMaskCfg;

	$rowcontents = "\n<tr class=\"$class\"><td>";
	
	if($selected)
	{
		$rowcontents .= $item_r['instance_no'];
		$rowcontents .= " "._theme_image("tick.gif", NULL, get_opendb_lang_var('current_item_instance'));
	}
	else
	{
		$rowcontents .= "<a href=\"$PHP_SELF?item_id=${item_r['item_id']}&instance_no=${item_r['instance_no']}\">".$item_r['instance_no']."</a>";
	}
	$rowcontents .= "\n</td>";
	
	$page_title = $titleMaskCfg->expand_item_title($item_r);
	
	$page_title = remove_enclosing_quotes($page_title);
	
	$rowcontents .= "<td>".
			get_list_username($item_r['owner_id'], $HTTP_VARS['mode'], $page_title, get_opendb_lang_var('back_to_item'), 'item_display.php?item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].(strlen($listing_link)>0?'&listing_link='.$listing_link:'')).
			"</td>";
	
	$status_type_r = fetch_status_type_r($item_r['s_status_type']);		
	
	// ---------------------- Borrow,Reserve,Cancel,Edit,Delete,etc operations here.
	$action_links_rs = NULL;
	
	if(get_opendb_session_var('user_id') === $item_r['owner_id'] || is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
		$action_links_rs[] = array(url=>'item_input.php?op=edit&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'edit.gif',text=>get_opendb_lang_var('edit'));
								
		// Checks if any legal site plugins defined for $item_r['s_item_type']
		if(is_item_legal_site_type($item_r['s_item_type']))
		{
			$action_links_rs[] = array(url=>'item_input.php?op=site-refresh&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y'.(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'refresh.gif',text=>get_opendb_lang_var('refresh'));
		}
		
		if($status_type_r['delete_ind'] == 'Y' && !is_item_reserved_or_borrowed($item_r['item_id'], $item_r['instance_no']))
		{
			$action_links_rs[] = array(url=>'item_input.php?op=delete&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'delete.gif',text=>get_opendb_lang_var('delete'));		
		}
		
		// Quick checkout NOT available to Admin user, unless they are also explicitly the owner.
		if(get_opendb_config_var('borrow', 'enable')!==FALSE && 
					get_opendb_session_var('user_id') === $item_r['owner_id'] && 
					get_opendb_config_var('borrow', 'quick_checkout')!==FALSE && 
					($status_type_r['borrow_ind'] == 'Y' || $status_type_r['borrow_ind'] == 'N'))
		{
			// Cannot quick checkout an item already borrowed.
			if(!is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
			{
				$action_links_rs[] = array(url=>'item_borrow.php?op=quick_check_out&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y'.(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'quick_check_out.gif',text=>get_opendb_lang_var('quick_check_out'));
			}						
		}
	}

	if(get_opendb_session_var('user_id') !== $item_r['owner_id'] && 
				is_user_allowed_to_borrow(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) && 
				(strlen($status_type_r['min_display_user_type'])==0 || in_array($status_type_r['min_display_user_type'], get_min_user_type_r(get_opendb_session_var('user_type'))))
			)
	{
		if(get_opendb_config_var('borrow', 'enable')!==FALSE)
		{
			// Check if already in reservation session variable.
			if(get_opendb_config_var('borrow', 'reserve_basket')!==FALSE && is_item_in_reserve_basket($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id')))
			{
				$action_links_rs[] = array(url=>'borrow.php?op=delete_from_my_reserve_basket&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y&listing_link='.$listing_link,img=>'delete_reserve_basket.gif',text=>get_opendb_lang_var('delete_from_reserve_list'));		
			}
			else if(is_item_reserved_or_borrowed($item_r['item_id'], $item_r['instance_no']))
			{
				if(is_item_reserved_by_user($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id')))
				{
					$action_links_rs[] = array(url=>'item_borrow.php?op=cancel_reserve&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y&listing_link='.$listing_link,img=>'cancel_reserve.gif',text=>get_opendb_lang_var('cancel_reservation'));
				}
				else if(!is_item_borrowed_by_user($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id')))
				{
					if($status_type_r['borrow_ind'] == 'Y' &&
								(get_opendb_config_var('borrow', 'allow_reserve_if_borrowed')!==FALSE || !is_item_borrowed($item_r['item_id'], $item_r['instance_no'])) &&
								(get_opendb_config_var('borrow', 'allow_multi_reserve')!==FALSE || !is_item_reserved($item_r['item_id'], $item_r['instance_no'])) )
					{
						if(get_opendb_config_var('borrow', 'reserve_basket')!==FALSE)
						{
							$action_links_rs[] = array(url=>'borrow.php?op=update_my_reserve_basket&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y'.(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'add_reserve_basket.gif',text=>get_opendb_lang_var('add_to_reserve_list'));
						}
						else
						{
							$action_links_rs[] = array(url=>'item_borrow.php?op=reserve&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y'.(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'reserve_item.gif',text=>get_opendb_lang_var('reserve_item'));
						}
					}
				}
			}
			else
			{   
				if($status_type_r['borrow_ind'] == 'Y')
				{
					if(get_opendb_config_var('borrow', 'reserve_basket')!==FALSE)
					{
						$action_links_rs[] = array(url=>'borrow.php?op=update_my_reserve_basket&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y'.(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'add_reserve_basket.gif',text=>get_opendb_lang_var('add_to_reserve_list'));
					}
					else
					{
						$action_links_rs[] = array(url=>'item_borrow.php?op=reserve&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&item_link=y'.(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'reserve_item.gif',text=>get_opendb_lang_var('reserve_item'));
					}
				}
			}
		}			
	} // else -- guest user
	
	if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) || $item_r['owner_id'] == get_opendb_session_var('user_id'))
	{
		if(is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
		{
			$action_links_rs[] = array(url=>'item_borrow.php?op=check_in&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'check_in_item.gif',text=>get_opendb_lang_var('check_in_item'));
		}
		
		if(is_exists_item_instance_history_borrowed_item($item_r['item_id'], $item_r['instance_no']))
		{
			$action_links_rs[] = array(url=>'borrow.php?op=my_item_history&item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].(strlen($listing_link)>0?'&listing_link='.$listing_link:''),img=>'item_history.gif',text=>get_opendb_lang_var('item_history'));
		}
	}

	$rowcontents .= "\n<td>";
	$rowcontents .= ifempty(format_action_links($action_links_rs),get_opendb_lang_var('not_applicable'));
	$rowcontents .= "\n</td>";
	
	// Item Status Image.
	$rowcontents .= "\n<td>";
	$rowcontents .= _theme_image($status_type_r['img'], $status_type_r['description'], NULL, NULL, "borrowed_item");
	$rowcontents .= "\n</td>";
	
	// If a comment is allowed and defined, add it in.
	$rowcontents .= "\n<td>";
	if($status_type_r['status_comment_ind'] == 'Y' || ($status_type_r['status_comment_ind'] == 'H' && 
			(get_opendb_session_var('user_id') === $item_r['owner_id'] || 
			is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))))
	{
		$rowcontents .= ifempty(nl2br($item_r['status_comment']),"&nbsp;"); // support newlines in this field
	}
	else
	{
		$rowcontents .= get_opendb_lang_var('not_applicable');
	}
	$rowcontents .= "\n</td>";
	
	if(get_opendb_config_var('borrow', 'enable')!==FALSE)
	{
		if(get_opendb_config_var('borrow', 'include_borrower_column')!==FALSE)
		{
			$rowcontents .= "\n<td>";
			if(is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
				$rowcontents .= get_list_username(fetch_item_borrower($item_r['item_id'], $item_r['instance_no']), NULL, $page_title, get_opendb_lang_var('back_to_item'), 'item_display.php?item_id='.$item_r['item_id'].'&instance_no='.$item_r['instance_no'].'&listing_link='.$listing_link);
			else
				$rowcontents .= get_opendb_lang_var('not_applicable');
			$rowcontents .= "\n</td>";
		}
		
		// Borrow Status Image.
		$rowcontents .= "\n<td>";
		if(is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
		{
			$rowcontents .= _theme_image("borrowed.gif", get_opendb_lang_var('borrowed'), NULL, NULL, "borrowed_item");
		}
		else if(($status_type_r['borrow_ind'] == 'Y' || $status_type_r['borrow_ind'] == 'N') && is_item_reserved($item_r['item_id'], $item_r['instance_no']))
		{
			$rowcontents .= _theme_image("reserved.gif", get_opendb_lang_var('reserved'), NULL, NULL, "borrowed_item");
		}
		else
		{
			$rowcontents .= get_opendb_lang_var('not_applicable');
		}
		$rowcontents .= "\n</td>";
		
		if(get_opendb_config_var('borrow', 'duration_support')!==FALSE)
		{
			// 'Due Back' functionality.	
			$rowcontents .= "\n<td>";
			if(is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
			{
				$due_date = fetch_item_duedate_timestamp($item_r['item_id'], $item_r['instance_no']);
				if(strlen($due_date)>0)
					$rowcontents .= get_localised_timestamp(get_opendb_config_var('borrow', 'date_mask'), $due_date);
				else
					$rowcontents .= get_opendb_lang_var('undefined');
			}
			else if($status_type_r['borrow_ind'] != 'Y' && $status_type_r['borrow_ind'] != 'N')// s_status_type CAN be changed to 
				$rowcontents .= get_opendb_lang_var('not_applicable');							// type with borrow_ind=N, even if item checked out / reserved
			else if(is_numeric($item_r['borrow_duration']))
			{
				$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r($item_r['s_item_type'], 'DURATION');
				$rowcontents .= get_display_field($duration_attr_type_r['s_attribute_type'], NULL, $duration_attr_type_r['display_type'], $item_r['borrow_duration'], FALSE);
			}
			else
				$rowcontents .= get_opendb_lang_var('undefined');
			$rowcontents .= "\n</td>";
		}
	}
	
	$rowcontents .= "\n</tr>";
	return $rowcontents;		
} 

/**
	This function will return a complete table of all links valid
	for this item_type.

	This is useful because it allows the use of a site plugin for
	generating links only, by specifying as the default site type.
*/
function get_site_plugin_links($page_title, $item_r)
{
	$pageContents = '';
	
	$results = fetch_site_plugin_rs($item_r['s_item_type']);
	if($results)
	{
	    $titleMaskCfg = new TitleMask();
	    
		$pageContents = "<ul class=\"sitepluginLinks\">";
		
		while($site_plugin_type_r = db_fetch_assoc($results))
		{
			if(is_exists_site_plugin($site_plugin_type_r['site_type']))
			{
				$site_plugin_conf_rs = get_site_plugin_conf_r($site_plugin_type_r['site_type']);
				
				// Get the primary link, which will be extended with details for each individual link.
				if(strlen($site_plugin_type_r['image'])>0)
					$link_text = "<img src=\"./site/images/".$site_plugin_type_r['image']."\" border=\"0\" title=\"".htmlspecialchars($site_plugin_type_r['title'])."\" alt=\"".htmlspecialchars($site_plugin_type_r['title'])."\">";
				else
					$link_text = "[".$site_plugin_type_r['title']."]";
							
				$results2 = fetch_site_plugin_link_rs($site_plugin_type_r['site_type'], $item_r['s_item_type']);
				if($results2)
				{
					while($site_plugin_link_r = db_fetch_assoc($results2))
					{
						$parse_url = NULL;
						
						if(strlen($site_plugin_link_r['url'])>0 && is_exists_site_item_attribute($site_plugin_type_r['site_type'], $item_r['item_id'], $item_r['instance_no']))
							$parse_url = $site_plugin_link_r['url'];
						else if(strlen($site_plugin_link_r['title_url'])>0)
							$parse_url = $site_plugin_link_r['title_url'];
						
						if($parse_url != NULL)
						{
						    $titleMaskCfg->reset();
						    
							// now we want to expand the $parse_url
							$parse_url = $titleMaskCfg->expand_title($item_r, $parse_url, $site_plugin_conf_rs);
						
							$pageContents .= "<li><a href=\"".$parse_url."\" onclick=\"popup('url.php?url=".urlencode($parse_url)."&cache_type=none', 800, 600); return false;\">$link_text";
							$pageContents .= "<br />[".$site_plugin_link_r['description']."]";
							$pageContents .= "</a></li>";
						}
					}//while
					db_free_result($results2);
				}
			}
		}//while
		db_free_result($results);
		
		$pageContents .= "</ul>";
		return $pageContents;
	}
}

if(is_site_enabled())
{
	if (is_opendb_valid_session())
	{
		if(is_numeric($HTTP_VARS['parent_id']) && is_numeric($HTTP_VARS['parent_instance_no']))
			$parent_item_r = fetch_item_instance_r($HTTP_VARS['parent_id'], $HTTP_VARS['parent_instance_no']);

		if(is_not_empty_array($parent_item_r))
			$item_r = fetch_child_item_r($HTTP_VARS['item_id']);
		else if(is_numeric($HTTP_VARS['instance_no']))
			$item_r = fetch_item_instance_r($HTTP_VARS['item_id'], $HTTP_VARS['instance_no']);
	
		if(is_not_empty_array($item_r))
		{
			if(is_not_empty_array($parent_item_r))
				$status_type_r = fetch_status_type_r($parent_item_r['s_status_type']);
			else
				$status_type_r = fetch_status_type_r($item_r['s_status_type']);
			
			if(( (is_not_empty_array($parent_item_r) && $parent_item_r['owner_id'] == get_opendb_session_var('user_id')) || 
						(!is_array($parent_item_r) && $item_r['owner_id'] == get_opendb_session_var('user_id')) ) ||
						(strlen($status_type_r['min_display_user_type'])==0 || in_array($status_type_r['min_display_user_type'], get_min_user_type_r(get_opendb_session_var('user_type')))))
			{
			    $titleMaskCfg = new TitleMask('item_display');

			    $page_title = $titleMaskCfg->expand_item_title($item_r);
				echo _theme_header($page_title, $HTTP_VARS['inc_menu']);
				
				echo(get_popup_javascript());
				echo(get_common_javascript());
				echo(get_tabs_javascript());
				
				echo ("<h2>".$page_title." ".get_item_image($item_r['s_item_type'], $item_r['item_id'], is_numeric($item_r['parent_id']))."</h2>");
                
				// ---------------- Display IMAGE attributes ------------------------------------
				// Will bypass the display of images if the config.php get_opendb_config_var('item_display', 'show_item_image')
				// variable has been explicitly set to FALSE.  This means that if the variable does 
				// not exist, this block should still execute.
				if(get_opendb_config_var('item_display', 'show_item_image')!==FALSE)
				{
					// Here we need to get the image_attribute_type and check if it is set.
					$results = fetch_item_attribute_type_rs($item_r['s_item_type'], 'IMAGE');
					if($results)
					{
						$coverimages_rs = NULL;
						
						while($image_attribute_type_r = db_fetch_assoc($results))
						{
							$imageurl = fetch_attribute_val($item_r['item_id'], $item_r['instance_no'], $image_attribute_type_r['s_attribute_type'], $image_attribute_type_r['order_no']);

							$imageurl = get_file_attribute_url($item_r['item_id'], $item_r['instance_no'], $image_attribute_type_r, $imageurl);
							
							// If an image is specified.
							if(strlen($imageurl)>0)
							{
								$coverimages_rs[] = array(
									'file'=>file_cache_get_image_r($imageurl, 'display'),
									'prompt'=>$image_attribute_type_r['prompt']);
							}//if(strlen($imageurl)>0)
						}
						db_free_result($results);
						
						// provide default if no images
						if($coverimages_rs == NULL)
						{
							$coverimages_rs[] = array('file'=>file_cache_get_image_r(NULL, 'display'));
						}
						
						echo("<ul class=\"coverimages\">");
						while(list(,$coverimage_r) = each($coverimages_rs))
						{
							echo("<li>");
							$file_r = $coverimage_r['file'];
							
							if(strlen($file_r['fullsize']['url'])>0)
							{
								// a dirty hack!
								if(starts_with($file_r['url'], 'file://'))
								{
									$parsed_r = parse_upload_file_url($file_r['url']);
									$file_r['url'] = $parsed_r['filename'];
								}
								
								echo("<a href=\"".$file_r['url']."\" onclick=\"popup('".$file_r['fullsize']['url']."', 400, 300); return false;\">");
							}
							echo("<img src=\"".$file_r['thumbnail']['url']."\" border=0 title=\"".htmlspecialchars($coverimage_r['prompt'])."\" ");
							
							if(is_numeric($file_r['thumbnail']['width']))
								echo(' width="'.$file_r['thumbnail']['width'].'"');
							if(is_numeric($file_r['thumbnail']['height']))
								echo(' height="'.$file_r['thumbnail']['height'].'"');
							
							echo(">");
							if(strlen($file_r['fullsize']['url'])>0)
							{
								echo("</a>");
							}
							echo("</li>");
						}
						echo("</ul>");
					}
				}
				
				$cfgIsTabbedLayout = get_opendb_config_var('item_display', 'tabbed_layout');
				
				$otherTabsClass="tabContent";
				if($cfgIsTabbedLayout!==FALSE)
				{
					$otherTabsClass="tabContentHidden";
				}
				
				echo("<div class=\"tabContainer\">");
				if($cfgIsTabbedLayout!==FALSE)
				{
					echo("<ul class=\"tabMenu\" id=\"tab-menu\">");
					echo("<li id=\"menu-details\" class=\"activeTab\" onclick=\"return activateTab('details', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">".get_opendb_lang_var('details')."</li>");
					echo("<li id=\"menu-instance_info\" onclick=\"return activateTab('instance_info', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">".get_opendb_lang_var('instance_info')."</li>");
					echo("<li id=\"menu-related_items\" onclick=\"return activateTab('related_items', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">".get_opendb_lang_var('related_items(s)')."</li>");
					echo("<li id=\"menu-reviews\" onclick=\"return activateTab('reviews', 'tab-menu', 'tab-content', 'activeTab', 'tabContent')\">".get_opendb_lang_var('review(s)')."</li>");
					echo("</ul>");
				}
								
				echo("<div id=\"tab-content\">");
				
				echo("<div class=\"tabContent\" id=\"details\">");
				
				$average = fetch_review_rating($item_r['item_id']);
				if($average!==FALSE)
				{
					echo("<p class=\"rating\">");
					echo (get_opendb_lang_var('rating').": ");
					$attribute_type_r = fetch_attribute_type_r('S_RATING');
					echo get_display_field(
							$attribute_type_r['s_attribute_type'],
							NULL,
							'review()',
							$average,
							FALSE);
					echo("</p>");
				}
				else
				{
					echo("<p class=\"norating\">".get_opendb_lang_var('no_rating')."</p>");	
				}
				
				// Do all the attributes.  Ignore any attributes that have an input_type of hidden.
				$results = fetch_item_attribute_type_rs($item_r['s_item_type'], 'not_instance_field_types');
				if($results)
				{
					echo("<table>");
					while($item_attribute_type_r = db_fetch_assoc($results))
					{
						// If display_type == '' AND input_type == 'hidden' we set to 'hidden'
						$display_type = trim($item_attribute_type_r['display_type']);
						
						if(($HTTP_VARS['mode'] == 'printable' && $item_attribute_type_r['printable_ind'] != 'Y') ||
								(strlen($display_type)==0 && $item_attribute_type_r['input_type'] == 'hidden'))
						{
							// We allow the get_display_field to handle hidden variable, in case at some stage
							// we might want to change the functionality of 'hidden' to something other than ignore.
							$display_type = 'hidden';
						}
						
						if($item_attribute_type_r['s_field_type'] == 'ITEM_ID')
							$value = $item_r['item_id'];
						else if(is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type']))
							$value = fetch_attribute_val_r($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
						else
							$value = fetch_attribute_val($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'],  $item_attribute_type_r['order_no']);

						// Only show attributes which have a value.
						if(is_not_empty_array($value) || (!is_array($value) && strlen($value)>0))
						{
							$item_attribute_type_r['display_type'] = $display_type;
							
							$field = get_item_display_field(
									$item_r,
									$item_attribute_type_r,
									$value,
									FALSE);

							if(strlen($field)>0)
							{
								echo "\n<tr><th class=\"prompt\" scope=\"row\">".$item_attribute_type_r['prompt'].":</th>".
									"<td class=\"data\">".$field."</td></tr>";
							}
						}
					}
					db_free_result($results);
					
					echo("\n</table>");
				}
				
				// ---------------------Site Link Block -----------------------
				echo(get_site_plugin_links($page_title, $item_r));
				// -------------------------------------------------------------
				
				$detail_info_link_rs = NULL;
				if(is_not_empty_array($parent_item_r))
				{
					$detail_info_link_rs[] = array(url=>"$PHP_SELF?item_id=".$HTTP_VARS['parent_id']."&instance_no=".$HTTP_VARS['parent_instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''),text=>get_opendb_lang_var('back_to_parent'));
				}
					
				if(is_not_empty_array($detail_info_link_rs))
				{
					echo(format_footer_links($detail_info_link_rs));
				}
					
				echo("</div>");
				
				// need to be able to display details about parent instances even if related item is context
				if(is_not_empty_array($parent_item_r))
					$current_item_r = $parent_item_r;
				else
					$current_item_r = $item_r;
						
				echo("<div class=\"$otherTabsClass\" id=\"instance_info\">");
				
				echo("<h3>".get_opendb_lang_var('instance_info')."</h3>");
					
				if(get_opendb_config_var('item_input', 'item_instance_support') !== FALSE)
				{
					$results = fetch_item_instance_rs($current_item_r['item_id'], NULL);
					if($results)
					{
						echo("<table>".
							"\n<tr class=\"navbar\">".
							"\n<th>".get_opendb_lang_var('instance')."</th>".
							"\n<th>".get_opendb_lang_var('owner')."</th>".
							"\n<th>".get_opendb_lang_var('action')."</th>".
							"\n<th>".get_opendb_lang_var('status')."</th>".
							"\n<th>".get_opendb_lang_var('status_comment')."</th>");
						
						if(get_opendb_config_var('borrow', 'enable')!==FALSE)
						{
							if(get_opendb_config_var('borrow', 'include_borrower_column')!==FALSE)
							{
								echo("\n<th>".get_opendb_lang_var('borrower')."</th>");
							}
							
							echo("\n<th>".get_opendb_lang_var('borrow_status')."</th>");
							
							if(get_opendb_config_var('borrow', 'duration_support')!==FALSE)
							{
								echo("\n<th>".get_opendb_lang_var('due_date_or_duration')."</th>");
							}
						}
						echo("\n</tr>");

						$toggle=TRUE;
						$numrows = db_num_rows($results);
						while($item_instance_r = db_fetch_assoc($results))
						{
							if($toggle)
								$color="oddRow";
							else
		 						$color="evenRow";
							$toggle = !$toggle;
							
							echo(get_item_status_row(
									$color,
									array_merge($current_item_r, $item_instance_r),
									$HTTP_VARS['listing_link'], 
									$numrows>1 && $current_item_r['instance_no']===$item_instance_r['instance_no']));
						}
						
						echo("\n</table>");
					}
					else
					{	// No instances found, because user has been deactivated and/or items are hidden.
						echo(get_opendb_lang_var('no_records_found'));
					}
				}
				else//lone instance only.
				{
					$numrows = 1;
					echo(get_item_status_row(
							"oddRow",
							$current_item_r, 
							$HTTP_VARS['listing_link'], 
							FALSE));
				}
				
				if($numrows>1)
				{
					echo(format_help_block(array('img'=>'tick.gif','text'=>get_opendb_lang_var('current_item_instance'))));
				}
				
				$instance_info_links = NULL;
				if(is_user_allowed_to_own(get_opendb_session_var('user_id')))
				{
					if(get_opendb_config_var('item_input', 'item_instance_support') !== FALSE)
					{
						$instance_info_links[] = array(url=>"item_input.php?op=newinstance&item_id=".$current_item_r['item_id']."&instance_no=".$current_item_r['instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''),text=>get_opendb_lang_var('new_item_instance'));
					}
						
					if(get_opendb_config_var('item_input', 'clone_item_support') !== FALSE)
					{
						$instance_info_links[] = array(url=>"item_input.php?op=clone_item&item_id=".$current_item_r['item_id']."&instance_no=".$current_item_r['instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''),text=>get_opendb_lang_var('clone_item'));
					}
				}
				
				if(is_not_empty_array($instance_info_links))
				{
					echo(format_footer_links($instance_info_links));
				}
				
				echo("</div>");
				
				echo("<div class=\"$otherTabsClass\" id=\"related_items\">");

				echo("<h3>".get_opendb_lang_var('related_item(s)')."</h3>");
				if(is_array($parent_item_r))
					echo(get_child_items_table($parent_item_r, $item_r, $HTTP_VARS));
				else
					echo(get_child_items_table($item_r, NULL, $HTTP_VARS));
				echo("</div>");
			
				// -------------------------- REVIEWS ---------------------------------------------------------
				echo("<div class=\"$otherTabsClass\" id=\"reviews\">");
							
				echo("<h3>".get_opendb_lang_var('review(s)')."</h3>");
					
				$result = fetch_review_rs($item_r['item_id']);
				if($result)
				{
					echo("<ul>");
					while($review_r = db_fetch_assoc($result))
					{
						$action_links = NULL;
						
						echo("<li>");
						
						if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) || 
										is_review_author($review_r['sequence_number'], get_opendb_session_var('user_id')))
						{
							$action_links_rs = NULL;
							if(get_opendb_config_var('item_review', 'update_support')!==FALSE)
								$action_links[] = array(url=>"item_review.php?op=edit&sequence_number=".$review_r['sequence_number']."&item_id=".$item_r['item_id']."&instance_no=".$item_r['instance_no']."&parent_id=".$HTTP_VARS['parent_id']."&parent_instance_no=".$HTTP_VARS['parent_instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''), text=>get_opendb_lang_var('edit'));	
							if(get_opendb_config_var('item_review', 'delete_support')!==FALSE)
								$action_links[] = array(url=>"item_review.php?op=delete&sequence_number=".$review_r['sequence_number']."&item_id=".$item_r['item_id']."&instance_no=".$item_r['instance_no']."&parent_id=".$HTTP_VARS['parent_id']."&parent_instance_no=".$HTTP_VARS['parent_instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''), text=>get_opendb_lang_var('delete'));	
							
							echo(format_footer_links($action_links));
						}
						
						echo("<p class=\"author\">");
						echo(
							get_opendb_lang_var('on_date_name_wrote_the_following', 
									array('date'=>get_localised_timestamp(get_opendb_config_var('item_display', 'review_datetime_mask'),$review_r['update_on']),
									'fullname'=>fetch_user_name($review_r['author_id']), 
									'user_id'=>$review_r['author_id'])));
						echo("</p>");
									
						echo("<p class=\"comments\">".nl2br(trim($review_r['comment'])));
						if($review_r['item_id']!=$item_r['item_id'])
						{
							echo("<span class=\"reference\">".get_opendb_lang_var(
									'review_for_item_type_title',
									array('s_item_type'=>$review_r['s_item_type'], 'item_id'=>$review_r['item_id'])).
									"</span>");
						}
						echo("</p>");
						
						$average = $review_r['rating'];
						$attribute_type_r = fetch_attribute_type_r("S_RATING");
						echo("<span class=\"rating\">".get_display_field($attribute_type_r['s_attribute_type'], 
								NULL, 
								'review()', // display_type
								$average,
								FALSE).
							"</span>");
						
						echo("</li>");
					}//while
					
					echo("</ul>");
				}
				else
				{
					echo(get_opendb_lang_var('no_item_reviews'));
				}
				
				$action_links = NULL;
				if(is_user_allowed_to_review(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
				{
					$action_links[] = array(
							url=>"item_review.php?op=add&item_id=".$item_r['item_id']."&instance_no=".$item_r['instance_no']."&parent_id=".$HTTP_VARS['parent_id']."&parent_instance_no=".$HTTP_VARS['parent_instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''),
							text=>get_opendb_lang_var('review'));
												
					echo(format_footer_links($action_links));
				}
				echo("</div>"); // end of review
				
				echo("</div>"); // end of tab content
				echo("</div>");  // end of tabContainer
			}
			else // if(!in_array($status_type_r['min_display_user_type'], get_min_user_type_r(get_opendb_session_var('user_type'))))
			{
				$page_title = get_opendb_lang_var('s_status_type_display_access_disabled_for_usertype', array('usertype'=>get_usertype_prompt(get_opendb_session_var('user_type')),'s_status_type_desc'=>$status_type_r['description']));
				echo _theme_header($page_title);
				echo("<p class=\"error\">".$page_title."</p>");
			}
		}
		else //if(is_not_empty_array($item_r))
		{
			echo _theme_header(get_opendb_lang_var('item_not_found'));
			echo("<p class=\"error\">".get_opendb_lang_var('item_not_found')."</p>");
		}//$item_r found
		
		if(is_export_plugin(get_opendb_config_var('item_display', 'export_link')))
		{
			$footer_links_r[] = array(url=>"export.php?op=export&plugin=".get_opendb_config_var('item_display', 'export_link')."&item_id=".$item_r['item_id']."&instance_no=".$item_r['instance_no']."&send_as_format=attachment",text=>get_opendb_lang_var('type_export_item_record', 'type', get_display_export_type(get_opendb_config_var('item_display', 'export_link'))));
		}
			
		// Include a Back to Listing link.
		if($HTTP_VARS['listing_link'] === 'y' && is_array(get_opendb_session_var('listing_url_vars')))
		{
			$footer_links_r[] = array(url=>"listings.php?".get_url_string(get_opendb_session_var('listing_url_vars')),text=>get_opendb_lang_var('back_to_listing'));
		}
	
		echo(format_footer_links($footer_links_r));
		
		echo _theme_footer();
	}
	else
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
