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

include_once("./functions/review.php");
include_once("./functions/item_type.php");
include_once("./functions/item.php");
include_once("./functions/widgets.php");
include_once("./functions/user.php");
include_once("./functions/TitleMask.class.php");

function get_edit_form($op, $review_r, $HTTP_VARS)
{
	global $PHP_SELF;
		
	$formContents = "";
	
	$formContents .= "<form action=\"$PHP_SELF\" method=\"POST\">";
	
	$formContents .= "<table>";
			
	$formContents .= get_input_field("comment",
				NULL,
				get_opendb_lang_var('review'),
               	"htmlarea(55,10)",
				"Y",
            	$review_r['comment'],//value
				TRUE);

	// We are now able to configure this in the database.
	$attribute_type_r = fetch_attribute_type_r('S_RATING');
	$formContents .= get_item_input_field("rating",
						$attribute_type_r,
						NULL, //$item_r
						$review_r['rating']);

	$formContents .= "</table>";
				
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onclick_event = "if(!checkForm(this.form)){return false;}else{this.form.submit();}";
	else
		$onclick_event = "this.form.submit();";
	
	if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
	{
		$formContents .= format_help_block(array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field')));
	}

	$formContents .= "<input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"".get_opendb_lang_var('save_review')."\">
		<input type=\"hidden\" name=\"op\" value=\"$op\">
		<input type=\"hidden\" name=\"sequence_number\" value=\"".$review_r['sequence_number']."\">
		<input type=\"hidden\" name=\"item_id\" value=\"".$HTTP_VARS['item_id']."\">
		<input type=\"hidden\" name=\"instance_no\" value=\"".$HTTP_VARS['instance_no']."\">
		<input type=\"hidden\" name=\"listing_link\" value=\"".$HTTP_VARS['listing_link']."\">
		</form>";
		
	return $formContents;
}

if(is_site_enabled())
{
	if (is_opendb_valid_session())
	{
		if(is_user_allowed_to_review(get_opendb_session_var('user_id'), get_opendb_session_var('user_type'))) 
		{
			if(is_numeric($HTTP_VARS['item_id']) && ($HTTP_VARS['op'] == 'insert' || $HTTP_VARS['op'] == 'add'))
			{
				$item_r = fetch_item_r($HTTP_VARS['item_id']);
			}
			else if(is_numeric($HTTP_VARS['sequence_number']) && ($HTTP_VARS['op'] == 'update' || $HTTP_VARS['op'] == 'delete' || $HTTP_VARS['op'] == 'edit'))
			{
				$review_r = fetch_review_r($HTTP_VARS['sequence_number']);
				
				// Copy reference only.
				$item_r = $review_r;
			}
			
			if(is_not_empty_array($item_r))
			{
			    $titleMaskCfg = new TitleMask('item_display');
			    $item_r['title'] = $titleMaskCfg->expand_item_title($item_r);
			    
				$page_title = get_opendb_lang_var('review_title', 'display_title', $item_r['title']);
	
				echo _theme_header($page_title);
				echo("<h2>".$page_title." ".get_item_image($item_r['s_item_type'])."</h2>\n");
					
				if($HTTP_VARS['op'] == 'insert')
				{
					$HTTP_VARS['comment'] = filter_input_field('htmlarea(55,10)', $HTTP_VARS['comment']);
					if(strlen($HTTP_VARS['comment'])>0)
					{
						if(insert_review($HTTP_VARS['item_id'], get_opendb_session_var('user_id'), $HTTP_VARS['comment'], $HTTP_VARS['rating']))
							echo ("<p class=\"success\">".get_opendb_lang_var('review_added')."</p>");
						else
							echo("<p class=\"error\">".get_opendb_lang_var('review_not_added')."</p>");
					}
					else
					{
						echo(format_error_block(array(error=>get_opendb_lang_var('prompt_must_be_specified', 'prompt', get_opendb_lang_var('review')))));
						echo(get_edit_form('insert', array(), $HTTP_VARS));
					}
				}
				else if($HTTP_VARS['op'] == 'update')
				{
					if(get_opendb_config_var('item_review', 'update_support')!==FALSE)
					{
						if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) || is_review_author($review_r['sequence_number'], get_opendb_session_var('user_id')))
						{
							$HTTP_VARS['comment'] = filter_input_field('htmlarea(55,10)', $HTTP_VARS['comment']);
							if(strlen($HTTP_VARS['comment'])>0)
							{
								if(update_review($HTTP_VARS['sequence_number'], $HTTP_VARS['comment'], $HTTP_VARS['rating']))
									echo ("<p class=\"success\">".get_opendb_lang_var('review_updated')."</p>");
								else
									echo("<p class=\"error\">".get_opendb_lang_var('review_not_updated')."</p>");
							}
							else
							{
								echo(format_error_block(array(error=>get_opendb_lang_var('prompt_must_be_specified', 'prompt', get_opendb_lang_var('review')))));
								echo(get_edit_form('update', array(), $HTTP_VARS));
							}
						}
						else
						{
							echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
						}
					}
					else
					{
						echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
					}
				}
				else if($HTTP_VARS['op'] == 'delete')
				{
					if(get_opendb_config_var('item_review', 'delete_support')!==FALSE)
					{
						if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) || is_review_author($review_r['sequence_number'], get_opendb_session_var('user_id')))
						{
							if($HTTP_VARS['confirmed'] == 'true')
							{
								if(delete_review($HTTP_VARS['sequence_number']))
									echo ("<p class=\"success\">".get_opendb_lang_var('review_deleted')."</p>");
								else
									echo("<p class=\"error\">".get_opendb_lang_var('review_not_deleted')."</p>");
							}
							else if($HTTP_VARS['confirmed'] == 'false')
							{
								echo ("<p class=\"success\">".get_opendb_lang_var('review_not_deleted')."</p>");
							}
							else
							{
								echo get_op_confirm_form($PHP_SELF, get_opendb_lang_var('confirm_delete_review'), $HTTP_VARS);
							}
						}
						else
						{
							echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
						}
					}
					else
					{
						echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
					}
				}
				else if($HTTP_VARS['op'] == 'edit')
				{
					if(get_opendb_config_var('item_review', 'update_support')!==FALSE)
					{
						if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) || is_review_author($review_r['sequence_number'], get_opendb_session_var('user_id')))
						{
							echo get_edit_form('update', $review_r, $HTTP_VARS);
						}
						else
						{
							echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
						}
					}
					else
					{
						echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
					}
				}
				else if($HTTP_VARS['op'] == 'add')
				{
					echo get_edit_form('insert', array(), $HTTP_VARS);
					
				}
			}
			else
			{
				echo _theme_header(get_opendb_lang_var('item_not_found'));
				echo("<h2>".get_opendb_lang_var('item_not_found')."</h2>");
				echo("<p class=\"error\">".get_opendb_lang_var('item_not_found')."</p>");
			}
			
			$footer_links_r[] = array(url=>"item_display.php?item_id=".$HTTP_VARS['item_id']."&instance_no=".$HTTP_VARS['instance_no']."&listing_link=".$HTTP_VARS['listing_link'],text=>get_opendb_lang_var('back_to_item'));

			// Include a Back to Listing link.
			if($HTTP_VARS['listing_link'] === 'y' && is_array(get_opendb_session_var('listing_url_vars')))
			{
				$footer_links_r[] = array(url=>"listings.php?".get_url_string(get_opendb_session_var('listing_url_vars')),text=>get_opendb_lang_var('back_to_listing'));
			}
			
			echo format_footer_links($footer_links_r);
			echo _theme_footer();
		}
		else if(is_site_public_access_enabled())
		{
			// provide login at this point
			redirect_login($PHP_SELF, $HTTP_VARS);
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
