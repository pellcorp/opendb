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

include_once("./functions/borrowed_item.php");
include_once("./functions/widgets.php");
include_once("./functions/user.php");
include_once("./functions/item.php");
include_once("./functions/review.php");
include_once("./functions/chart.php");
include_once("./functions/item_type.php");
include_once("./functions/item_attribute.php");
include_once("./functions/item.php");

function build_owner_item_chart_info($s_item_type)
{
	$result = fetch_user_rs(PERM_ITEM_OWNER);
	if($result)
	{
		while ($user_r = db_fetch_assoc($result))
		{
			$num_total = fetch_owner_item_cnt($user_r['user_id'], $s_item_type);
			if($num_total>0)
			{
				$info[$user_r['fullname']] = $num_total;
			}
		}
		db_free_result($result);
	}
	return $info;
}

function build_item_category_chart_info($s_item_type)
{
	$category_attribute_type = fetch_sfieldtype_item_attribute_type($s_item_type, 'CATEGORY');
	if($category_attribute_type)
	{
		$results = fetch_attribute_type_lookup_rs($category_attribute_type, 'order_no, value ASC');
		if($results)
		{
			while($attribute_type_r = db_fetch_assoc($results)) // next category...
			{
				$num_total = fetch_category_item_cnt($attribute_type_r['value'], $s_item_type);
				if($num_total > 0)
				{
					$key = $attribute_type_r['display'];
					$info[$key] = $num_total;
				}
			}
			db_free_result($results);
		}
	}

	return $info;
}

function build_item_types_chart_info()
{
	$results = fetch_item_type_rs();
	while( $item_type_r = db_fetch_assoc($results) )
	{
		$num_total = fetch_item_instance_cnt($item_type_r['s_item_type']);
		if($num_total>0)
		{
			$info[$item_type_r['s_item_type']] = $num_total; // caption
		}
	}
	db_free_result($results);

	return $info;
}

function build_item_ownership_chart_info()
{
	$results = fetch_status_type_rs();
	if($results)
	{
		while ($status_type_r = db_fetch_assoc($results))
		{
			$status_type_rs[] = $status_type_r;
		} 
		db_free_result($results);
	}
		
	$results = fetch_user_rs(PERM_ITEM_OWNER);
	if($results)
	{
		while ($user_r = db_fetch_assoc($results))
		{
			$num_total = 0;
			if(is_not_empty_array($status_type_rs))
			{
				reset($status_type_rs);
	    		while(list($key, $status_type_r) = each($status_type_rs))
				{
					$status_total = fetch_owner_s_status_type_item_cnt($user_r['user_id'], $status_type_r['s_status_type']);
					$num_total += $status_total;
				}
			}

			// pie chart data
			if($num_total>0)
			{
				$info[$user_r['fullname']] = $num_total;
			}
		}//while ($user_r = db_fetch_assoc($result))
		db_free_result($results);
	}
	return $info;
}

function do_stats_graph($HTTP_VARS)
{
	// Load GD Library if not already loaded - todo is this still required
	// Thanks to Laurent CHASTEL (lchastel)
	if (!@extension_loaded('gd'))
	{
		if((boolean)@ini_get('enable_dl'))// is dynamic load enabled
		{ 
		    $gd_library = get_opendb_config_var('site.gd', 'library');
			if(strlen($gd_library)>0)
			{
				@dl($gd_library);
			}
		}
	}
	
	$graphCfg = _theme_graph_config();
	
	// Defines the image type to use.  'png', 'jpeg'/'jpg' supported!
	$imgType = strlen(get_opendb_config_var('stats', 'image_type'))>0? get_opendb_config_var('stats', 'image_type') : "png";
	
	// standard threshold.
	$chartOptions['threshold'] = 3;

	// Default pie chart options.		
	if(strcasecmp(get_opendb_config_var('stats', 'piechart_sort'),"asc")===0 || strcasecmp(get_opendb_config_var('stats', 'piechart_sort'),"desc")===0)
		$sortorder = strtolower(get_opendb_config_var('stats', 'piechart_sort'));
			
	if(get_opendb_config_var('stats', 'piechart_12oclock') === TRUE)
		$chartOptions['12oclock'] = TRUE;
	else
		$chartOptions['12oclock'] = FALSE;

	if(get_opendb_config_var('stats', 'piechart_striped') === TRUE)
		$chartOptions['striped'] = TRUE;
	else
		$chartOptions['striped'] = FALSE;

	$total_items = fetch_item_instance_cnt();
	
	switch($HTTP_VARS['graphtype'])
	{
		case 'item_ownership':
			build_and_send_graph(
				$total_items,
				build_item_ownership_chart_info(), 
				$sortorder, 
				'piechart', //chartype
				$chartOptions, 
				$graphCfg, 
				$imgType);
			break;
		
		case 'item_types':
			build_and_send_graph(
				$total_items,
				build_item_types_chart_info(), 
				$sortorder, 
				'barchart', //chartype
				$chartOptions, 
				$graphCfg, 
				$imgType);
			break;
		
		case 'item_type_ownership':
			build_and_send_graph(
				$total_items,
				build_owner_item_chart_info($HTTP_VARS['s_item_type']), 
				$sortorder, 
				'piechart', //chartype
				$chartOptions, 
				$graphCfg, 
				$imgType);
			break;
		
		case 'item_type_category':
			if(get_opendb_config_var('stats', 'category_barchart') === TRUE)
			{
				// Is the barchart being sorted.
				if(strcasecmp(get_opendb_config_var('stats', 'barchart_sort'),"asc")===0 || strcasecmp(get_opendb_config_var('stats', 'barchart_sort'),"desc")===0)
					$sortorder = strtolower(get_opendb_config_var('stats', 'barchart_sort'));
			
				// These options are currently ignored for barcharts
				$chartOptions['striped'] = FALSE;
				$chartOptions['12oclock'] = FALSE;
				
				build_and_send_graph(
					$total_items,
					build_item_category_chart_info($HTTP_VARS['s_item_type']),
					$sortorder, 
					'barchart', //chartype
					$chartOptions, 
					$graphCfg, 
					$imgType);
			}
			else
			{
				build_and_send_graph(
					$total_items,
					build_item_category_chart_info($HTTP_VARS['s_item_type']), 
					$sortorder, 
					'piechart', //chartype
					$chartOptions, 
					$graphCfg, 
					$imgType);
			}
			break;
		
		default:
			// what to do here!
	}
}

if(is_site_enabled())
{
	if (is_opendb_valid_session() || is_site_public_access())
	{
		if(is_user_granted_permission(PERM_VIEW_STATS))
		{
			if($HTTP_VARS['op'] == 'graph')
			{
				do_stats_graph($HTTP_VARS);
			}
			else
			{
				echo _theme_header(get_opendb_lang_var('statistics'));
				echo("<h2>".get_opendb_lang_var('statistics')."</h2>");
		
				echo("<h3>".get_opendb_lang_var('general_stats')."</h3>");
				
				echo("<dl class=\"generalStats\">");
				echo("<dt>".get_opendb_lang_var('owner(s)')."</dt>");
				$num_users = fetch_user_cnt(PERM_ITEM_OWNER);//only users who can own
				echo("<dd>".$num_users."</dd>");
				
				echo("<dt>".get_opendb_lang_var('item(s)')."</dt>");
				// This count should not include owner's items, where the owner has been DEACTIVATED.
				$total_items = fetch_item_instance_cnt();
				echo("<dd>".$total_items."</dd>");
		
				$avgrate = fetch_review_rating();
				if($avgrate>0)
				{
					$num_review = fetch_review_cnt();
					
					echo("<dt>".get_opendb_lang_var('review(s)')."</dt>");
					echo("<dd>".$num_review."</dd>");
				
					echo("<dt>".get_opendb_lang_var('average_rating')."</dt>");
					$attribute_type_r = fetch_attribute_type_r("S_RATING");
					echo("<dd>".get_display_field($attribute_type_r['s_attribute_type'], 
									NULL, 
									'review()',
									$avgrate,
									FALSE)."</dd>");
				}
				echo("</dl>");
				
				// Get the list of valid status_types, which we can display
			    // in this statistics page.
			    $results = fetch_status_type_rs();
			    if ($results)
				{
			        while ($status_type_r = db_fetch_assoc($results))
					{
						$status_type_r['total'] = 0;
			            $status_type_rs[] = $status_type_r;
			        } 
			        db_free_result($results);
			    }
		
			    echo("<h3>".get_opendb_lang_var('item_stats')."</h3>");
			    
				echo("<table class=\"itemStats\">");
				
				echo("<tr class=\"navbar\">");
				echo("<th>".get_opendb_lang_var('owner')."</th>");
				
				if(is_not_empty_array($status_type_rs))
				{
				    reset($status_type_rs);
				    while(list(, $status_type_r) = each($status_type_rs))
					{
						echo("<th>".
							_theme_image($status_type_r['img'], $status_type_r['description'], "s_status_type").
							"</th>");
					}
				}
		
		        echo("<th>".get_opendb_lang_var('total')."</th>");
		        
				echo("</tr>");
				
				$result = fetch_user_rs(PERM_ITEM_OWNER);
				if($result)
				{
					$toggle=TRUE;
		
					// Totals.
					$sum_loaned = 0;
					$sum_reserved = 0;
		
					while ($user_r = db_fetch_assoc($result))
					{
						$user_name = get_opendb_lang_var('user_name', array('fullname'=>$user_r['fullname'], 'user_id'=>$user_r['user_id']));
	
						echo("<tr class=\"data\"><th>");
						if(is_user_granted_permission(PERM_VIEW_USER_PROFILE))
						{
							echo("<a href=\"user_profile.php?uid=".$user_r['user_id']."\">".$user_name."</a>");
						}
						else
						{
							echo($user_name);
						}
						echo("</th>");
						
						$num_total = 0;
						if(is_not_empty_array($status_type_rs))
						{
							reset($status_type_rs);
				    		while(list($key, $status_type_r) = each($status_type_rs))
							{
								$status_total = fetch_owner_s_status_type_item_cnt($user_r['user_id'], $status_type_r['s_status_type']);
								$status_type_rs[$key]['total']  += $status_total;
						
								echo("\n<td>");
								if($status_total>0)
									echo("<a href=\"listings.php?owner_id=".$user_r['user_id']."&s_status_type=".$status_type_r['s_status_type']."&order_by=title&sortorder=ASC\">$status_total</a>");
								else
									echo("-");
								echo("</td>");
						
								$num_total += $status_total;
							}
							$sum_total += $num_total;
						
							echo("\n<td>".$num_total."</td>");
						}
					
						echo("</tr>");
					}//while ($user_r = db_fetch_assoc($result))
					db_free_result($result);
				
					echo("<tr class=\"data totals\"><th>".get_opendb_lang_var('totals')."</th>");
	
					if(is_not_empty_array($status_type_rs))
					{
						reset($status_type_rs);
			    		while(list(, $status_type_r) = each($status_type_rs))
						{
							echo("<td>".$status_type_r['total']."</td>");
						}
						echo("<td>".$sum_total."</td>");
					}
					
					echo("</tr>");
				}
				echo("</table>");
				
				if(get_opendb_config_var('borrow', 'enable') !== FALSE)
				{
					echo("<h3>".get_opendb_lang_var('borrow_stats')."</h3>");
				    
					echo("<table class=\"itemStats\">");
					
					echo("<tr class=\"navbar\">");
					echo("<th>".get_opendb_lang_var('owner')."</th>");
					echo("<th>"._theme_image('reserved.gif', get_opendb_lang_var('reserved'), "borrowed_item")."</th>");
					echo("<th>"._theme_image('borrowed.gif', get_opendb_lang_var('borrowed'), "borrowed_item")."</th>");
					echo("</tr>");
					
					$result = fetch_user_rs(PERM_ITEM_OWNER);//only ACTIVE owner users!
					if($result)
					{
						$toggle=TRUE;
			
						// Totals.
						$sum_loaned = 0;
						$sum_reserved = 0;
			
						while ($user_r = db_fetch_assoc($result))
						{
							$user_name = get_opendb_lang_var('user_name', array('fullname'=>$user_r['fullname'], 'user_id'=>$user_r['user_id']));
		
							echo("<tr class=\"data\"><th>");
							if(is_user_granted_permission(PERM_VIEW_USER_PROFILE))
							{
								echo("<a href=\"user_profile.php?uid=".$user_r['user_id']."\">".$user_name."</a>");
							}
							else
							{
								echo($user_name);
							}
							echo("</th>");
							
							$reserved_total = fetch_owner_reserved_item_cnt($user_r['user_id']);
							$sum_reserved += $reserved_total;
		
							echo("\n<td>");
							if($reserved_total>0)
								echo($reserved_total);
							else
								echo("-");
							echo("</td>");
		
							$loan_total = fetch_owner_borrowed_item_cnt($user_r['user_id']);
							$sum_loaned += $loan_total;
		
							echo("\n<td>");
							if($loan_total>0)
								echo($loan_total);
							else
								echo("-");
							echo("</td>");
							
							echo("</tr>");
						}//while ($user_r = db_fetch_assoc($result))
						db_free_result($result);
						
						echo("<tr class=\"data totals\"><th>".get_opendb_lang_var('totals')."</th>");
						
						// sum loaned.
						if(get_opendb_config_var('borrow', 'enable') !== FALSE)
						{
							echo("<td>".$sum_reserved."</td>");
							echo("<td>".$sum_loaned."</td>");
						}
						
						echo("</tr>");
					}
					echo("</table>");
				}
				
				$itemresults = fetch_item_type_rs();
				if($itemresults)
				{
					$graphConfigURLParams = get_url_string(_theme_graph_config());
					
					echo("<ul class=\"itemCharts\">");
					
					echo("<li><h3>".get_opendb_lang_var('item_ownership')."</h3>");
					echo("<ul class=\"graph\">");
					echo("<li><img src=\"stats.php?op=graph&graphtype=item_ownership&$graphConfigURLParams\" alt=\"".get_opendb_lang_var('database_ownership_chart')."\"></li>");
					echo("<li><img src=\"stats.php?op=graph&graphtype=item_types&$graphConfigURLParams\" alt=\"".get_opendb_lang_var('database_itemtype_chart')."\"></li>");
					echo("</ul>");
					echo("</li>");
				
					while($item_type_r = db_fetch_assoc($itemresults) )
					{
						$type_total_items = fetch_item_instance_cnt($item_type_r['s_item_type']);
						if($type_total_items > 0) 
						{
			        	    echo("<li><h3>".get_opendb_lang_var('itemtype_breakdown', array('desc'=>$item_type_r['description'],'s_item_type'=>$item_type_r['s_item_type'], 'total'=>$type_total_items))."</h3>");
							echo("<ul class=\"graph\">");
							echo("<li><img src=\"stats.php?op=graph&graphtype=item_type_ownership&s_item_type=".urlencode($item_type_r['s_item_type'])."&$graphConfigURLParams\" alt=\"".get_opendb_lang_var('itemtype_ownership_chart', 's_item_type', $item_type_r['s_item_type'])."\"></li>");
							echo("<li><img src=\"stats.php?op=graph&graphtype=item_type_category&s_item_type=".urlencode($item_type_r['s_item_type'])."&$graphConfigURLParams\" alt=\"".get_opendb_lang_var('itemtype_category_chart', 's_item_type', $item_type_r['s_item_type'])."\"></li>");
							echo("</ul>");
							echo("</li>");
						}
					}
					db_free_result($itemresults);
					
				}
				echo("</ul>");
				
				echo _theme_footer();
			}
		}
		else
		{
			echo _theme_header(get_opendb_lang_var('not_authorized_to_page'));
			echo("<p class=\"error\">".get_opendb_lang_var('not_authorized_to_page')."</p>");
			echo _theme_footer();
		}
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