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
include_once("./functions/email.php");

/**
 * Is current user able to see UID address 
 *
 * @param unknown_type $HTTP_VARS
 * @param unknown_type $address_type_r
 * @return unknown
 */
function is_user_address_visible($HTTP_VARS, $address_type_r)
{
	if($address_type_r['public_address_ind'] == 'Y')
		return TRUE;
	else if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
		return TRUE;
	else if($address_type_r['borrow_address_ind'] == 'Y' && 
		(is_owner_and_borrower(get_opendb_session_var('user_id'), $HTTP_VARS['uid'])) ||
				is_owner_and_borrower($HTTP_VARS['uid'], get_opendb_session_var('user_id')))
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

if(is_site_enabled())
{
	if(is_opendb_valid_session())
	{
		if(!is_user_guest(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
		{
			$user_r = fetch_user_r($HTTP_VARS['uid']);
			if(is_array($user_r))
			{
				$page_title = get_opendb_lang_var('user_profile_for_user_name', array('user_id'=>$HTTP_VARS['uid'], 'fullname'=>fetch_user_name($HTTP_VARS['uid'])));
				echo(_theme_header($page_title));
				echo('<h2>'.$page_title.'</h2>');
	
				echo("<table>");
	
				echo(format_field(
						get_opendb_lang_var('userid'),
						$user_r['user_id']));
	
				echo(format_field(
						get_opendb_lang_var('user_type'),
						get_usertype_prompt(ifempty($HTTP_VARS['user_type'],$user_r['type']))));
	
				echo(format_field(
						get_opendb_lang_var('fullname'),
						$user_r['fullname']));
	
				if($HTTP_VARS['user_id'] === get_opendb_session_var('user_id') || 
						is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
				{
					echo(format_field(
						get_opendb_lang_var('email'),
						$user_r['email_addr']));
				}
						
				echo("\n</table>");
	
				$address_header_displayed = FALSE;
				$addr_results = fetch_user_address_type_rs($HTTP_VARS['uid'], NULL, TRUE);
				if($addr_results)
				{
					while($address_type_r = db_fetch_assoc($addr_results))
					{
						if(is_user_address_visible($HTTP_VARS, $address_type_r))
						{
							$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], get_opendb_session_var('user_type'), 'query', TRUE);
							if($attr_results)
							{
								echo('<h3>'.$address_type_r['description'].'</h3>');
								echo("<table>");
								while($addr_attribute_type_r = db_fetch_assoc($attr_results))
								{
									// If display_type == '' AND input_type == 'hidden' we set to 'hidden'
									if(strlen(trim($addr_attribute_type_r['display_type']))==0 && $addr_attribute_type_r['input_type'] == 'hidden')
									{
										// We allow the get_display_field to handle hidden variable, in case at some stage
										// we might want to change the functionality of 'hidden' to something other than ignore.
										$addr_attribute_type_r['display_type'] = 'hidden';
									}
									
									$value = NULL;
									if(is_lookup_attribute_type($addr_attribute_type_r['s_attribute_type']))
									{
										$value = fetch_user_address_lookup_attribute_val(
																	$address_type_r['sequence_number'],
																	$addr_attribute_type_r['s_attribute_type'],
																	$addr_attribute_type_r['order_no']);
									}
									else
									{
										$value = fetch_user_address_attribute_val(
																	$address_type_r['sequence_number'],
																	$addr_attribute_type_r['s_attribute_type'],
																	$addr_attribute_type_r['order_no']);
									}
	
									if(strlen($value)>0)
									{
										echo(get_item_display_field(
												NULL,
												$addr_attribute_type_r,
												$value));
									}
								}
								db_free_result($attr_results);
								echo("\n</table>");
							}
						}
					}
					db_free_result($addr_results);
				}
				
				if(is_valid_opendb_mailer() && strlen($user_r['email_addr'])>0)
				{
					$url = 'email.php?'.
							get_url_string(Array(
									'op'=>'send_to_uid',
									'uid'=>$HTTP_VARS['uid'],
									'inc_menu'=>'N',
									'subject'=>ifempty($HTTP_VARS['subject'], get_opendb_lang_var('no_subject'))));
		
					$footer_links_r[] = array(url=>$url, target=>'popup(640,480)', text=>get_opendb_lang_var('send_email'));
				}
				
				$footer_links_r[] = array(url=>"listings.php?owner_id=".$HTTP_VARS['uid'], text=>get_opendb_lang_var('list_user_items'));
				
				if($HTTP_VARS['listing_link'] === 'y' && is_array(get_opendb_session_var('user_listing_url_vars')))
				{
					$footer_links_r[] = array(url=>"user_listing.php?".get_url_string(get_opendb_session_var('user_listing_url_vars')),text=>get_opendb_lang_var('back_to_user_listing'));
				}
		
				echo format_footer_links($footer_links_r);
			}
			else
			{
				$message = get_opendb_lang_var('user_not_found', array('user_id'=>$HTTP_VARS['uid']));
				
				echo _theme_header($message);
				echo("<p class=\"error\">".$message."</p>");
				echo(_theme_footer());
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
