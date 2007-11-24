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

include_once("./functions/admin.php");
include_once("./functions/http.php");
include_once("./functions/user.php");
include_once("./functions/email.php");
include_once("./functions/language.php");
include_once("./functions/item.php");
include_once("./functions/theme.php");
include_once("./functions/status_type.php");
include_once("./functions/borrowed_item.php");
include_once("./functions/whatsnew.php");
include_once("./functions/announcement.php");
include_once("./functions/SourceforgeVersionCheck.class.php");

function get_lastitems_list_blocks_r($update_on, $user_id)
{
	if(get_opendb_config_var('login.last_items_list', 'enable')!==FALSE)
	{
		$last_items_list_conf_r = get_opendb_config_var('login.last_items_list');
		
		if($last_items_list_conf_r['exclude_current_user']!==TRUE)
			$user_id = NULL;
	
		if($last_items_list_conf_r['restrict_last_login']!==TRUE)
			$update_on = NULL;

			$blocks_r = get_last_item_list(
					$last_items_list_conf_r['total_num_items'],
					NULL, 
					NULL, 
					$update_on, 
					$user_id,
					NULL,
					FALSE);

		return $blocks_r;
	}
	else
	{
		return NULL;
	}
}

function get_admin_announcements_rs()
{
	$announcements_rs = array();
	
	$user_cnt = fetch_user_cnt(NULL, 'X', TRUE);
	if($user_cnt>0)
	{
		$announcements_rs[] = array(
		heading=>get_opendb_lang_var('activate_users'),
		message=>get_opendb_lang_var('there_are_no_of_users_awaiting_activation', array('no_of_users'=>$user_cnt)),
		link=>"user_listing.php?restrict_active_ind=X",
		link_text=>get_opendb_lang_var('activate_users'));
	}
	
	if(validate_user_passwd(get_opendb_session_var('user_id') , 'admin'))
	{
		$announcements_rs[] = array(
			heading=>get_opendb_lang_var('change_admin_user_password'),
			message=>get_opendb_lang_var('change_admin_user_password_msg'),
			link=>"user_admin.php?op=change_password&user_id=".get_opendb_session_var('user_id'),
			link_text=>get_opendb_lang_var('change_my_password'));
	}

	if(fetch_user_email(get_opendb_session_var('user_id') ) == 'opendb@iamvegan.net') 
	{
		$announcements_rs[] = array(
			heading=>get_opendb_lang_var('change_admin_user_email'),
			message=>get_opendb_lang_var('change_admin_user_email_msg'),
			link=>"user_admin.php?op=edit&user_id=".get_opendb_session_var('user_id'),
			link_text=>get_opendb_lang_var('edit_my_info'));
	}
			
	if(!is_exists_any_item_type())
	{
		$admin_type_r = get_system_admin_tools_menu('s_item_type');
		$announcements_rs[] = array(
			heading=>get_opendb_lang_var('no_item_types'),
			message=>get_opendb_lang_var('add_new_item_type_msg'),
			link=>"admin.php?type=s_item_type",
			link_text=>$admin_type_r['link'].' Admin Tool');
	}
			
	if(!is_exists_any_site_plugin())
	{
		$admin_type_r = get_system_admin_tools_menu('s_site_plugin');
		$announcements_rs[] = array(
			heading=>get_opendb_lang_var('no_site_plugins'),
			message=>get_opendb_lang_var('add_new_site_plugin_msg'),
			link=>"admin.php?type=s_site_plugin",
			link_text=>$admin_type_r['link'].' Admin Tool');
	}
    
	/*$sfVersionCheck = new SourceforgeVersionCheck();
	if($sfVersionCheck->isUpdatedVersion(get_opendb_version()))
	{
		$announcements_rs[] = array(
			heading=>get_opendb_lang_var(''),
			message=>get_opendb_lang_var(''),
			link=>'http://opendb.iamvegan.net/wiki/index.php?title=Releases',
			link_text=>get_opendb_lang_var(''));
	}*/
	
	return $announcements_rs;
}


function get_announcements_block()
{
	$buffer = '';
	
    // display activate users block
	if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
        // include a login warning if user password and email are still the defaults
		if(get_opendb_session_var('user_id') == 'admin')
		{ 
			$announcements_rs = get_admin_announcements_rs();
			while(list(,$announcement_r) = each($announcements_rs))
			{
				$buffer .= "<li><h4>".$announcement_r['heading']."</h4>
					<p class=\"content\">".
						$announcement_r['message'].
					"<a class=\"adminLink\" href=\"".$announcement_r['link']."\">".
						$announcement_r['link_text'].
					"</a></p>";
			}
		}
	}

    if(get_opendb_config_var('login.announcements', 'enable')!==FALSE)
	{
		$results = fetch_announcement_rs(get_opendb_session_var('user_type'), NULL, 'DESC', 0, get_opendb_config_var('login.announcements','display_count'), 'Y', 'Y');
		if($results)
		{
			while($announcement_r = db_fetch_assoc($results))
			{
				$buffer .= "<li><h4>".$announcement_r['title']."</h4>";
				$buffer .= "<small class=\"submitDate\">".get_localised_timestamp(get_opendb_config_var('login.announcements', 'datetime_mask'),$announcement_r['submit_on'])."</small>";
				$buffer .= "<p class=\"content\">".nl2br($announcement_r['content'])."</p></li>";
			}
			db_free_result($results);
		}
	}
	
	if(strlen($buffer)>0)
	{
		return "\n<div id=\"announcements\">".
				"<h3>".get_opendb_lang_var('announcements')."</h3>".
				"\n<ul>".
				$buffer.
				"\n</ul></div>";
	}
	else
	{
		return NULL;
	}
}
/**
simple function so the last items lists and co can be called from more than one
place, without having to rewrite the page logic.
*/
function display_last_login_block($userid, $usertype, $lastvisit)
{
	if(get_opendb_config_var('login.whats_new', 'enable')!==FALSE)
	{
		$buffer .= "\n<div id=\"whatsnew\">";
		$buffer .= "\n<h3>".get_opendb_lang_var('whats_new')."</h3>";
		
		$whats_new_rs = get_whats_new_details($lastvisit, $userid);
		if(is_array($whats_new_rs))
		{
			$buffer .= "\n<ul>";
			while(list(,$whats_new_r) = each($whats_new_rs))
			{
				if(is_array($whats_new_r['items']))
				{
					$buffer .= "\n<li><ul class=\"block\">";
					$buffer .= "\n<h4>".$whats_new_r['heading']."</h4>";
					
					reset($whats_new_r['items']);
					while(list(,$item_rs) = each($whats_new_r['items']))
					{
						$buffer .= "\n<li class=\"".$item_rs['class']."\">".$item_rs['content']."</li>";
					}
					$buffer .= "\n</ul></li>";
				}
			}
			$buffer .= "\n</ul>";
		}
		$buffer .= "\n</div>";
	}

	if(get_opendb_config_var('login.last_items_list', 'enable')!==FALSE)
	{
		$lastitemlist_blocks_r = get_lastitems_list_blocks_r($lastvisit, $userid);
		if(is_array($lastitemlist_blocks_r))
		{
			$buffer .= "\n<div id=\"lastitemslist\">";
			$buffer .= '<h3>'.get_opendb_lang_var('last_items_list').'</h3>';
			
			$buffer .= get_last_item_list_table($lastitemlist_blocks_r);
						
			$buffer .= "\n</div>";
		}
	}
	
	$buffer .= get_announcements_block();
	
	return $buffer;
}

/**
	Assumes op=login and login params have been provided

	if(strlen($HTTP_VARS['uid'])>0 && strlen($HTTP_VARS['passwd'])>0)

	returns:
		SITE_IS_DISABLED - if user is not admin and site is diabled
		FALSE - if login failure
		TRUE - if login successful

	Does not perform any redirects
*/
function perform_login($HTTP_VARS)
{
	$HTTP_VARS['uid'] = strtolower($HTTP_VARS['uid']);// make lowercase
	if(is_user_active($HTTP_VARS['uid']) && validate_user_passwd($HTTP_VARS['uid'], $HTTP_VARS['passwd']))
	{
		if(get_opendb_config_var('site', 'enable')!==FALSE || is_user_admin($HTTP_VARS['uid']))
		{
			$time = time();
            register_opendb_session_var('login_time', $time);
            register_opendb_session_var('last_access_time', $time);

			$user_r = fetch_user_r($HTTP_VARS['uid']);

            register_opendb_session_var('user_id', $HTTP_VARS['uid']);
            register_opendb_session_var('user_type', $user_r['type']);

            // Now register security hash, so we can compare.
            register_opendb_session_var('hash_check', get_opendb_config_var('site', 'security_hash'));

			// Get the previous last visit so we can use in whats new page.
            register_opendb_session_var('login_lastvisit', fetch_user_lastvisit($HTTP_VARS['uid']));

			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User logged in', array($HTTP_VARS['uid']));

			// Not much we can do if it does not update.
			update_user_lastvisit($HTTP_VARS['uid']);

			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User tried to log in while site is disabled', array($HTTP_VARS['uid']));

			return "SITE_IS_DISABLED";
		}
	}//if(is_user_active($HTTP_VARS['uid']) && validate_user_passwd($HTTP_VARS['uid'], $HTTP_VARS['passwd']))
	else
	{
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User failed to login', array($HTTP_VARS['uid']));

		return FALSE;
	}
}

function show_login_form($HTTP_VARS, $errors = NULL)
{
	global $PHP_SELF;

	echo _theme_header(
			get_opendb_lang_var('login'),
			is_show_login_menu_enabled());

	echo("<h2>".get_opendb_lang_var('login')."</h2>");

    if(is_not_empty_array($errors))
		echo format_error_block($errors);

	echo("<form id=\"loginForm\" action=\"$PHP_SELF\" method=\"POST\" name=\"login\">");
	
	// The user tried to go straight to a menu item with an invalid session.
	// Set a "redirect" variable here so that after we give them a full session
	// we can redirect them back to the page they really wanted.
	if(strlen($HTTP_VARS['redirect'])>0)
	{
		echo("<input type=\"hidden\" name=\"redirect\" value=\"".$HTTP_VARS['redirect']."\">");
	}
	
	echo("<input type=\"hidden\" name=\"op\" value=\"login\">");
	
	echo("\n<ul>".
		"\n<li><label for=\"uid\">".get_opendb_lang_var('userid')."</label>".
		"<input type=\"text\" class=\"text\" id=\"uid\" name=\"uid\" value=\"".$HTTP_VARS['uid']."\"></li>".
		"\n<li><label for=\"password\">".get_opendb_lang_var('password')."</label>".
		"<input type=\"password\" class=\"password\" id=\"passwd\" name=\"passwd\"></li>".
		"</ul>".
		"\n<input type=\"submit\" class=\"submit\" value=\"".get_opendb_lang_var('login')."\">");

   echo("</form>");
	
	// force uid field focus for login
	echo("\n<script type=\"text/javascript\">
		document.forms['login']['uid'].focus();
	</script>");
	
	// if configuration permits PHP email, allow email new password
	if(is_site_enabled() && is_valid_opendb_mailer())
	{
		if(strlen($HTTP_VARS['uid'])>0 && get_opendb_config_var('login', 'enable_new_pwd_gen')!==FALSE)
		{
			$footer_links_r[] = array(url=>$PHP_SELF."?op=newpassword&uid=".urlencode($HTTP_VARS['uid']), text=>get_opendb_lang_var('forgot_your_pwd'));
		}
		
		// no point if site disabled, email is not available
		if(get_opendb_config_var('email', 'send_to_site_admin')!==FALSE)
		{
			$footer_links_r[] = array(
						text=>get_opendb_lang_var('email_administrator'),
						target=>"popup(640,480)",
						url=>"email.php?op=send_to_site_admin&inc_menu=N");
		}
	}
	
	// Indicate we should show the signup link.
	if(get_opendb_config_var('login.signup', 'enable')!==FALSE)
	{
		$footer_links_r[] = array(url=>"user_admin.php?op=signup", text=>get_opendb_lang_var('sign_me_up'));
	}
	
	echo format_footer_links($footer_links_r);
	
	echo(_theme_footer());
}//show_login_form($HTTP_VARS, $errors = NULL)

function perform_changeuser($HTTP_VARS)
{
	// save existing user_id so we can restore it.
    register_opendb_session_var('admin_user_id', get_opendb_session_var('user_id'));

	$user_r = fetch_user_r($HTTP_VARS['uid']);
    register_opendb_session_var('user_id', $HTTP_VARS['uid']);
    register_opendb_session_var('user_type', $user_r['type']);

	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Administrator changed user');
}

function show_changeuser_form()
{
	// display owner_id input field.
	echo _theme_header(get_opendb_lang_var('change_user'));

	echo("<h2>".get_opendb_lang_var('change_user')."</h2>");

	echo("\n<form action=\"login.php\" method=\"GET\">");
	echo("\n<input type=\"hidden\" name=\"op\" value=\"change-user\">");

	echo("\n<table class=\"changeUserForm\">");
	$results = fetch_user_rs(get_changeuser_user_types_r(), NULL, "fullname", "ASC", FALSE, get_opendb_session_var('user_id'));
	if($results)
	{
		echo(
			format_field(get_opendb_lang_var('user'),
						custom_select('uid', $results, '%fullname% (%user_id%)', 1, NULL, 'user_id')
					)
			);
	}
	else
	{
		echo(
			format_field(
					get_opendb_lang_var('user'),
					get_opendb_lang_var('no_records_found'))
		);
	}
	echo("</table>");
	
	echo("<input type=\"submit\" class=\"submit\" value=\"".get_opendb_lang_var('submit')."\">");
	echo("</form>");
	
	echo(_theme_footer());
}

function perform_newpassword($HTTP_VARS, &$errors)
{
    if(!is_user_valid($HTTP_VARS['uid']))
	{
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: User does not exist.', array($HTTP_VARS['uid']));

		// make user look successful to prevent mining for valid userids
		return TRUE;
	}
	else if(!is_user_active($HTTP_VARS['uid'])) // Do not allow new password operation for 'deactivated' user.
	{
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: User is not active.', array($HTTP_VARS['uid']));
		return FALSE;
	}
	else if(is_user_guest($HTTP_VARS['uid']))
	{
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: User is a guest.', array($HTTP_VARS['uid']));
		return FALSE;
	}
	else if(get_opendb_config_var('user_admin', 'user_passwd_change_allowed')===FALSE && !is_user_admin($HTTP_VARS['uid']))
	{
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: Password change is disabled', array($HTTP_VARS['uid']));
		return FALSE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User requested to be emailed a new password', array($HTTP_VARS['uid']));

		$user_r = fetch_user_r($HTTP_VARS['uid']);
		
		$user_passwd  = generate_password(8);

		// only send if valid user (email)
		if(strlen($user_r['email_addr'])>0)
		{
			$pass_result = update_user_passwd($HTTP_VARS['uid'], $user_passwd);
			if($pass_result===TRUE)
			{
				$subject    = get_opendb_lang_var('lost_password');
				$message    = get_opendb_lang_var('to_user_email_intro', 'fullname', $user_r['fullname']).
							"\n\n".
							get_opendb_lang_var('new_passwd_email').
							  "\n\n".
			        	      get_opendb_lang_var('userid').": ".$HTTP_VARS['uid']."\n".
		    	        	  get_opendb_lang_var('password').": ".$user_passwd;

		    	if(opendb_user_email($user_r['user_id'], NULL, $subject, $message, $errors))
				{
					return TRUE;
				}
				else
				{
					return "EMAIL_NOT_SENT";
				}
			}
		}
		else
		{
			$errors[] = "User '".$HTTP_VARS['uid']."' does not have a valid email address.";
			return FALSE;
		}
	}
}

// For a valid session, only refresh of page or change-user is allowed
// A login operation is allowed, where public access is enabled
if(is_opendb_valid_session() && $HTTP_VARS['op'] != 'login' && $HTTP_VARS['op'] != 'newpassword')
{
	if(strlen($HTTP_VARS['redirect'])>0)// Redirect to requested page, as already logged in.
	{
		//TODO: This does not work very well with a login page in middle of an item update!
		http_redirect(urldecode($HTTP_VARS['redirect']));
	}
	else if($HTTP_VARS['op'] == 'change-user')
	{
		// only allow changeuser if configuration allows
        if(get_opendb_config_var('login', 'enable_change_user')!==FALSE && is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
		{
            if(strlen($HTTP_VARS['uid'])>0 &&
					is_user_active($HTTP_VARS['uid']) &&
					is_user_changeuser($HTTP_VARS['uid']))
			{
                perform_changeuser($HTTP_VARS);
				http_redirect('login.php');
				return;
			}
			else
			{
				show_changeuser_form();
			}
		}
		else
		{
			http_redirect('login.php');
			return;
		}
	}
	else // refresh of login page
	{
    	echo _theme_header(get_opendb_lang_var('login'));

		echo(display_last_login_block(
			get_opendb_session_var('user_id'),
			get_opendb_session_var('user_type'),
			get_opendb_session_var('login_lastvisit')));

		echo(_theme_footer());
	}
}
else  // invalid session - go to login
{
	if($HTTP_VARS['op'] == 'newpassword')
	{
		if(strlen($HTTP_VARS['uid'])>0 && get_opendb_config_var('login', 'enable_new_pwd_gen')!==FALSE)
		{
			echo _theme_header(
					get_opendb_lang_var('login'),
					is_show_login_menu_enabled());

            echo("<h2>".get_opendb_lang_var('lost_password')."</h2>");

            $result = perform_newpassword($HTTP_VARS, $errors);
			if($result === FALSE)
			{
				echo("<p class=\"error\">".get_opendb_lang_var('error_updating_pwd')."</p>");
				echo("<p class=\"error\">".get_opendb_lang_var('if_problem_persists_contact_your_administrator', array('site'=>get_opendb_config_var('site', 'title')))."</p>");
			}
            else if($result === "EMAIL_NOT_SENT")
            {
            	echo("<p class=\"error\">".get_opendb_lang_var('error_sending_email')."</p>");
			}
			else
			{
				echo("<p class=\"success\">".get_opendb_lang_var('new_passwd_sent')."</p>");
			}

			// no point if site disabled, email is not available
			if(is_site_enabled() && is_valid_opendb_mailer() && get_opendb_config_var('email', 'send_to_site_admin')!==FALSE)
			{
				$footer_links_r[] = array(
					text=>get_opendb_lang_var('email_administrator'),
					target=>"popup(640,480)",
					url=>"email.php?op=send_to_site_admin&inc_menu=N&subject=".get_opendb_lang_var('lost_password'));
			}
			
			$footer_links_r[] = array(url=>"login.php",text=>get_opendb_lang_var('return_to_login_page'));
			echo format_footer_links($footer_links_r);
		}
		else
		{
            http_redirect('login.php');
			return;
		}
	}
	else //if($HTTP_VARS['op'] == 'login')
	{
        if(strlen($HTTP_VARS['uid'])>0 && strlen($HTTP_VARS['passwd'])>0)
        {
			$result = perform_login($HTTP_VARS);
            if($result === TRUE)
			{
				if(strlen($HTTP_VARS['redirect'])>0)
				{
					// User tried to get in with an invalid session.
					// We've just given her a valid one, so log it
					// appropriately and send a redirect to where she
					// really wanted to go.
					http_redirect(urldecode($HTTP_VARS['redirect']));
					return;
				}
				else
				{
					http_redirect('login.php');
					return;
				}
			}
			else if($result === "SITE_IS_DISABLED")
			{
				echo _theme_header(
					get_opendb_lang_var('site_is_disabled'),
					get_opendb_config_var('login', 'show_menu')!==FALSE);

				echo("<p class=\"error\">".get_opendb_lang_var('site_is_disabled')."</p>");
				echo(_theme_footer());
			}
			else // $result === FALSE
			{
				show_login_form($HTTP_VARS, array('error'=>get_opendb_lang_var('login_failure'), details=>get_opendb_lang_var('double_check_info')));
			}
		}
		else
		{
			show_login_form($HTTP_VARS);
		}
	}
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>