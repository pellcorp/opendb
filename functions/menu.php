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

include_once("./functions/user.php");
include_once("./functions/status_type.php");
include_once("./functions/utils.php");
include_once("./functions/email.php");
include_once("./functions/borrowed_item.php");
include_once("./functions/announcement.php");

function get_printable_link_r($pageid)
{
	global $PHP_SELF;
	global $HTTP_VARS;
	
	if($pageid == 'listings' || $pageid == 'borrow')
	{
		return array(url=>"$PHP_SELF?".get_url_string($HTTP_VARS, array('mode'=>'printable'), array('listing_link')),
					title=>get_opendb_lang_var('printable_version'),
					image=>_theme_image_src("printable.gif"));
	}
	else if($pageid == 'item_display')
	{
		return array(url=>"$PHP_SELF?".get_url_string($HTTP_VARS, array('mode'=>'printable')),
					title=>get_opendb_lang_var('printable_version'),
					image=>_theme_image_src("printable.gif"));
	}
}

/**
	TODO - based on $HTTP_VARS determine currently selected link

	You can use this menu options function, with standard image names
	to automatically generate a menu with images, instead of text.

	The image names should match the s_language_var id for the 'link'
	element.
*/
function get_menu_options($user_id, $user_type)
{
	global $PHP_SELF;
	global $HTTP_VARS;
	
	$menu_options = array();
	
	// Authenticated user
	if(strlen($user_id)>0)
	{
		if(is_user_allowed_to_own($user_id, $user_type))
		{
			$menu_options['item'][] = array(link=>get_opendb_lang_var('add_new_item'), url=>"item_input.php?op=site-add&owner_id=$user_id");
			$menu_options['item'][] = array(link=>get_opendb_lang_var('list_my_items'), url=>"listings.php?owner_id=$user_id");
		}
		
		$menu_options['item'][] = array(link=>get_opendb_lang_var('list_all_items'), url=>"listings.php");
						
		if(is_user_allowed_to_own($user_id, $user_type))
		{
			if(is_file_upload_enabled())
			{
				$menu_options['item'][] = array(link=>get_opendb_lang_var('import_my_items'), url=>"import.php?owner_id=$user_id");
			}
			$menu_options['item'][] = array(link=>get_opendb_lang_var('export_my_items'), url=>"export.php?owner_id=$user_id");
		}
		
		// Is borrow functionality enabled?
		if(get_opendb_config_var('borrow', 'enable')!==FALSE && is_user_allowed_to_borrow($user_id, $user_type))
		{
			if((get_opendb_config_var('borrow', 'list_all_borrowed')!==FALSE || is_user_admin($user_id, $user_type)) && is_exists_borrowed())
			{
				$menu_options['borrow'][] = array(link=>get_opendb_lang_var('items_borrowed'), url=>"borrow.php?op=all_borrowed");
			}
				
			if((get_opendb_config_var('borrow', 'list_all_reserved')!==FALSE || is_user_admin($user_id, $user_type)) && is_exists_reserved())
			{
				$menu_options['borrow'][] = array(link=>get_opendb_lang_var('items_reserved'), url=>"borrow.php?op=all_reserved");
			}
			
			if(is_exists_borrower_history($user_id))
			{
				$menu_options['borrow'][] = array(link=>get_opendb_lang_var('my_history'), url=>"borrow.php?op=my_history");
			}
			
			if(is_exists_borrower_borrowed($user_id))
			{
				$menu_options['borrow'][] = array(link=>get_opendb_lang_var('my_borrowed_items'), url=>"borrow.php?op=my_borrowed");
			}
			
			if(is_exists_borrower_reserved($user_id))
			{
				$menu_options['borrow'][] = array(link=>get_opendb_lang_var('my_reserved_items'), url=>"borrow.php?op=my_reserved");
			}
				
			if(get_opendb_config_var('borrow', 'reserve_basket')!==FALSE && is_exists_my_reserve_basket($user_id))
			{
				$menu_options['borrow'][] = array(link=>get_opendb_lang_var('item_reserve_list'), url=>"borrow.php?op=my_reserve_basket&order_by=title&sortorder=ASC");
			}

			if(is_user_allowed_to_own($user_id, $user_type))
			{
				$include_spacer=FALSE;
				if(is_exists_owner_reserved($user_id))
				{
					$menu_options['borrow'][] = array(link=>get_opendb_lang_var('check_out_item(s)'), url=>"borrow.php?op=owner_reserved");
				}
				
				if(is_exists_owner_borrowed($user_id))
				{
					$menu_options['borrow'][] = array(link=>get_opendb_lang_var('check_in_item(s)'), url=>"borrow.php?op=owner_borrowed");
				}
			}
		} //if(is_user_allowed_to_borrow($user_id, $type))

		$menu_options['misc'][] = array(link=>get_opendb_lang_var('advanced_search'), url=>"search.php");
		$menu_options['misc'][] = array(link=>get_opendb_lang_var('statistics'), url=>"stats.php");
		$menu_options['misc'][] = array(link=>get_opendb_lang_var('rss_feeds'), url=>"rss.php");
		
		if(is_user_admin($user_id, $user_type) || is_user_normal($user_id, $user_type) || is_user_borrower($user_id, $user_type))
		{
			$menu_options['user'][] = array(link=>get_opendb_lang_var('edit_my_info'), url=>"user_admin.php?op=edit&user_id=$user_id");
			if(get_opendb_config_var('user_admin', 'user_passwd_change_allowed')!==FALSE || is_user_admin($user_id, $user_type))
			{
				$menu_options['user'][] = array(link=>get_opendb_lang_var('change_my_password'), url=>"user_admin.php?op=change_password&user_id=$user_id");
			}
		}
	
		if(is_user_admin($user_id, $user_type))
		{
			if(is_exist_users_not_activated())
		    {
		    	$menu_options['admin'][] = array(link=>get_opendb_lang_var('activate_user_list'), url=>"user_listing.php?restrict_active_ind=X&order_by=fullname&sortorder=ASC");
			}
			$menu_options['admin'][] = array(link=>get_opendb_lang_var('user_list'), url=>"user_listing.php?order_by=fullname&sortorder=ASC");
			$menu_options['admin'][] = array(link=>get_opendb_lang_var('add_new_user'), url=>"user_admin.php?op=new_user");
			$menu_options['admin'][] = array(link=>get_opendb_lang_var('change_user'), url=>"login.php?op=change-user");
	
			// no point providing this option if email disabled.
			if(is_valid_opendb_mailer())
			{
				$menu_options['admin'][] = array(link=>get_opendb_lang_var('email_users'), url=>"email.php?op=send_to_all");
			}
	
			// Is borrow functionality enabled?
			if(get_opendb_config_var('borrow', 'enable')!==FALSE)
			{
				if(is_exists_history())
				{
					$menu_options['admin'][] = array(link=>get_opendb_lang_var('borrower_history'), url=>"borrow.php?op=admin_history");
				}
				$menu_options['admin'][] = array(link=>get_opendb_lang_var('quick_check_out'), url=>"item_borrow.php?op=admin_quick_check_out");
			}
	
			if(is_file_upload_enabled())
			{
				$menu_options['admin'][] = array(link=>get_opendb_lang_var('import_items'), url=>"import.php");
			}
			$menu_options['admin'][] = array(link=>get_opendb_lang_var('export_items'), url=>"export.php");
			
			$menu_options['admin'][] = array(link=>get_opendb_lang_var('system_admin_tools'), url=>"admin.php", target=>"_new");
		}
	}
	else if(is_site_public_access_enabled())
	{
		$menu_options['item'][] = array(link=>get_opendb_lang_var('list_all_items'), url=>"listings.php");
		$menu_options['misc'][] = array(link=>get_opendb_lang_var('statistics'), url=>"stats.php");
		$menu_options['misc'][] = array(link=>get_opendb_lang_var('rss_feeds'), url=>"rss.php");
	}
	
	//if(!is_site_public_access_enabled())
	//{
	//	$menu_options['login'][] = array(link=>get_opendb_lang_var('logout'), url=>"logout.php");
	//}
	//else
	//{
	//	$menu_options['login'][] = array(link=>get_opendb_lang_var('login'), url=>"login.php?op=login");
	//}
	
	return $menu_options;
}

function get_menu_options_list($options)
{
	$buffer = '';
	if(is_not_empty_array($options))
	{
		$active_found = FALSE;
		
		while (list($id,$option_rs) = @each($options))
		{
			$buffer .= "\n<ul class=\"menu\" id=\"${id}-menu\">";
			while (list(,$option_r) = @each($option_rs))
			{
				$class = '';
				if(!$active_found && is_menu_option_active($option_r))
				{
					$class = ' class="active"';
					$active_found = TRUE;
				}
	
				$buffer .= "<li$class>".get_menu_option($option_r)."</li>";
			}
			$buffer .= "\n</ul>";
	}
	}
	return $buffer;
}

function get_menu_option($option_r)
{
	$buffer = "\n<a href=\"".$option_r['url']."\" title=\"".ifempty($option_r['alt'],$option_r['link'])."\"";
			
	if($option_r['target'] == '_new')
	{
		$buffer .= ' target="_new"';
	}
	
	$buffer .= ">".$option_r['link']."</a>";
	
	return $buffer;
}

// TODO - this is a bit of a hack, it should be revisited at some later time
function is_menu_option_active($option_r)
{
	global $HTTP_VARS;
	global $PHP_SELF;
	
	$active = FALSE;
	
	$php_self = basename($PHP_SELF);
	
	$parse_r = parse_url($option_r['url']);
	if($parse_r['path'] == $php_self)
	{
		$active = TRUE;
		
		parse_str($parse_r['query'], $query_r);	
		if(is_array($query_r))
		{
			while(list($name, $value) = each($query_r))
			{
				if(!isset($HTTP_VARS[$name]) || $HTTP_VARS[$name] != $value)
				{
					$active = FALSE;
				}
			}
		}
	}
	return $active;
}
?>