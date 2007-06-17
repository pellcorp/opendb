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
include_once("./functions/email.php");
include_once("./functions/widgets.php");
include_once("./functions/user.php");
include_once("./functions/HTML_Listing.class.inc");

/*
* This script supports several different operations
* 
* 	$op = 'send_to_all'
* 		This will format an email email to all OpenDb users, except the currently
* 		logged in users.
* 
* 		Applicable variables:
* 			$toname - A title identifying the user group.
* 			$subject - The subject of the email
* 			$message - The message, if bypassing the email form.
* 		
* 		This operation is NOT available to a non-opendb Administrator user, and so 
* 		the $from and $fromname variables will be ignored.
* 
* 	$op = 'send_to_usertype'
* 
* 		Applicable variables:
* 			$usertype - User Type (N=Normal,A=Admin,B=Borrower,G=Guest)
* 				The $from user must be at least the type of user they are sending
* 				email to.  For instance a Normal user cannot send mail to Admin group.
* 			$toname - A title identifying the user group.
* 			$subject - The subject of the email
* 			$message - The message, if bypassing the email form.
* 
* 		This operation is NOT available to a non-opendb user, and so the $from
* 		and $fromname variables will be ignored.
* 			
* 	$op = 'send_to_uids'
* 
* 		Applicable Variables:
* 			$uids_rs - An array of userid's to send mail to.  The user trying to send
* 						email should be at least of the usertype of all the $uid's
* 						in this array variable.
* 			$subject - The subject of the email
* 			$message - The message, if bypassing the email form.
* 
* 		This operation is NOT available to a non-opendb user, and so the $from
* 		and $fromname variables will be ignored.
* 
* 	$op = 'send_to_uid'
* 
* 		Applicable Variables:
* 			$uid	 - A single UID to send email to.  The user trying to send
* 						email should be at least of the usertype of the $uid
* 						in this variable.
* 			$subject - The subject of the email
* 			$message - The message, if bypassing the email form.
* 
* 		This operation is NOT available to a non-opendb user, and so the $from
* 		and $fromname variables will be ignored.
*/

/*
* @param $to		Formatted readonly To: address information.  This will often NOT
* 					be an actual email address, but may be something like a comma
* 					delimited list of Userid's or names.
* @param $toname
* @param $from		A $from email address, which will correspond to the current user
* 					in all cases.  We no longer support using this script for non-opendb
* 					email.
* @param $fromname
* @param $subject
* @param message
* @param $HTTP_VARS - Any variables to include as hidden variables in the form.
*/
function show_email_form($to_userid, $to_fullname, $from_userid, $from_fullname, $subject, $message, $HTTP_VARS)
{
	global $PHP_SELF;
		
	if(strlen($to_userid)>0 && strlen($to_fullname)>0)
		$to = get_opendb_lang_var('user_name', array('fullname'=>$to_fullname, 'user_id'=>$to_userid));
	else if(strlen($to_fullname)>0)
		$to = $to_fullname;
	else if(strlen($to_userid)>0)
		$to = $to_userid;
	
	// format from
	if(strlen($from_userid)>0 && strlen($from_fullname)>0)
		$from = get_opendb_lang_var('current_user', array('fullname'=>$from_fullname, 'user_id'=>$from_userid));
	else if(strlen($from_fullname)>0)
		$from = $from_fullname;
	else if(strlen($from_userid)>0)
		$from = $from_userid;

	// Only if we have already been in this form.
	if($HTTP_VARS['no_message'] == 'true')
	{
		if(strlen($subject)==0)
			$error[] = array('error'=>get_opendb_lang_var('invalid_subject'));
		echo format_error_block($error);
	}
			
	// Indicate that we have been in this form, and have purposely left the message textarea blank.
	$HTTP_VARS['no_message'] = 'true';
	
	echo("\n<form action=\"$PHP_SELF\" method=\"POST\">");
	
	echo get_url_fields($HTTP_VARS, NULL, array('subject', 'message'));
	
	echo("\n<table class=\"emailForm\">");
	echo format_field(get_opendb_lang_var('to'),NULL,$to);
	
	// Will have supplied a value for both $from and $fromname, if coming from one of the
	// operations where the user has to be logged in.
	if(strlen($from)>0)
		echo format_field(get_opendb_lang_var('from'),NULL,$from);
	else
	{
		echo format_field(get_opendb_lang_var('from').(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE?_theme_image("compulsory.gif", NULL, get_opendb_lang_var('compulsory_field'), 'top'):""),
						NULL,
						"<input type=\"text\" name=\"from\" size=50 value=\"".htmlspecialchars($HTTP_VARS['from'])."\">");
	}
	
	echo format_field(get_opendb_lang_var('subject').(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE?_theme_image("compulsory.gif", NULL, get_opendb_lang_var('compulsory_field'), 'top'):""),
						NULL,
						"<input type=\"text\" name=\"subject\" size=50 value=\"".htmlspecialchars($subject)."\">");

	echo get_input_field("message",
				NULL, // s_attribute_type
				get_opendb_lang_var('message'), 
                "textarea(50,10)", //input type.
                "N", //compulsory!
                $message,
				TRUE);
	
	echo("</table>");
	
	if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
	{
		$help_block_r[] = array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field'));
	}
	
	if(is_array($help_block_r))
	{
		echo format_help_block($help_block_r);
	}
		
	echo("<input type=submit value=\"".get_opendb_lang_var('send_email')."\">");
	echo("\n</form>");
	
}

/*
* Will not check whether $user_id_rs contains the current users user_id.  It is expected
* that this should have already been done.
*/
function send_email_to_userids($user_id_rs, $fromemail, $fromname, $subject, $message)
{
	$errors = NULL;
	
	reset($user_id_rs);
	while (list(,$user_id) = each($user_id_rs))
	{
		$touser_r = fetch_user_r($user_id);
		if(is_not_empty_array($touser_r))
		{
			$email_address = fetch_user_email($touser_r['user_id']);
			if(opendb_email($email_address, $touser_r['fullname'], $fromemail, $fromname, $subject, $message, $errors))
			{
				$success[] = $touser_r['fullname']." (".$user_id.")";
			}
			else
			{
				$failures[] = array(user=>$touser_r['fullname']." (".$user_id.")", error=>$errors);
			}
			$errors = NULL;
		}
	}

	if(is_not_empty_array($success))
	{
		echo ("<p class=\"success\">".get_opendb_lang_var('message_sent_to').": <ul>");
		while (list(,$touser) = each($success))
		{
			echo("<li class=\"smsuccess\">".$touser."</li>");
		}
		echo("</ul></p>");
	}

	if(is_not_empty_array($failures))
	{
		echo ("<p class=\"error\">".get_opendb_lang_var('message_not_sent_to').": <ul>");
		while (list(,$failure_r) = each($failures))
		{
			echo("<li class=\"smerror\">".$failure_r['user'].
				format_error_block($failure_r['error'])."</li>");
		}
		echo("</ul></p>");
	}
}

function get_user_id_rs($user_type_rs=NULL, $exclude_current_user = FALSE)
{
	$user_id_rs = NULL;
	$result = fetch_user_rs($user_type_rs, NULL, 'user_id', 'ASC');
	if($result)
	{
		while ($user_r = db_fetch_assoc($result))
		{
			if(!$exclude_current_user || $user_r['user_id'] != get_opendb_session_var('user_id'))
			{
				$user_id_rs[] = $user_r['user_id'];
			}
		}
		db_free_result($result);
	}

	return $user_id_rs;
}

/**
*/
function get_user_ids_tovalue($user_id_rs)
{
	$to = "";
	if(is_not_empty_array($user_id_rs))
	{
		reset($user_id_rs);
		while(list(,$user_id) = each($user_id_rs))
		{
			if(strlen($to)==0)
				$to = $user_id;
			else
				$to .= ", ".$user_id;
		}
	}
	return $to;
}

if(is_site_enabled())
{
	if(is_opendb_valid_session() || 
					($HTTP_VARS['op'] == 'send_to_site_admin' && get_opendb_config_var('email', 'send_to_site_admin')!==FALSE))
	{
		echo _theme_header(get_opendb_lang_var('send_email'), $HTTP_VARS['inc_menu']);
		echo("<h2>".get_opendb_lang_var('send_email')."</h2>");
		
		// no email functionality is available unless a valid mailer is configured.
		if(is_valid_opendb_mailer())
		{
			if($HTTP_VARS['op'] != 'send_to_site_admin')
			{
				$from_user_r = fetch_user_r(get_opendb_session_var('user_id'));
				$HTTP_VARS['toname'] = trim(strip_tags($HTTP_VARS['toname']));
			}
			
			// Avoid any attempts to foil required validation checks.
			$HTTP_VARS['subject'] = trim(strip_tags($HTTP_VARS['subject']));
			$HTTP_VARS['message'] = trim(strip_tags($HTTP_VARS['message']));
			
			if($HTTP_VARS['op'] == 'send_to_all' && is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
			{
				// Default toname for bulk email.
				if(strlen($HTTP_VARS['toname'])==0)
				{
					$HTTP_VARS['toname'] = get_opendb_lang_var('users', 'user_desc', get_opendb_config_var('site', 'title'));
				}
			
				$user_id_r = get_user_id_rs(get_email_user_types_r(), TRUE);
				if(is_not_empty_array($user_id_r))
				{
					// If everything is provided, we can send email.
					if(strlen($HTTP_VARS['subject'])>0 && (strlen($HTTP_VARS['message'])>0 || $HTTP_VARS['no_message']=='true'))
					{
						send_email_to_userids(
								$user_id_r, 
								fetch_user_email($from_user_r['user_id']), 
								$from_user_r['fullname'],
								$HTTP_VARS['subject'], 
								$HTTP_VARS['message']);
					}
					else
					{
						show_email_form(
								get_user_ids_tovalue($user_id_r),
								$HTTP_VARS['toname'],
								$from_user_r['user_id'],
								$from_user_r['fullname'],
								$HTTP_VARS['subject'],
								$HTTP_VARS['message'],
								$HTTP_VARS);
					}
				}
				else
				{
					echo("<p class=\"error\">".get_opendb_lang_var('no_users_found')."</p>");
				}
			}
			else if($HTTP_VARS['op'] == 'send_to_usertype' && 
							is_usertype_valid($HTTP_VARS['usertype']) && 
							in_array(ifempty($HTTP_VARS['usertype'],'N'), get_min_user_type_r(get_opendb_session_var('user_type'))))
			{
				// Default toname for bulk email.
				if(strlen($HTTP_VARS['toname'])==0)
					$HTTP_VARS['toname'] = get_opendb_lang_var('users', 'user_desc', get_usertype_prompt($HTTP_VARS['usertype']));
					
				if(strlen($HTTP_VARS['subject'])>0 && (strlen($HTTP_VARS['message'])>0 || $HTTP_VARS['no_message']=='true'))
				{
					send_email_to_userids(
							get_user_id_rs(array($HTTP_VARS['usertype'])), 
							fetch_user_email($from_user_r['user_id']), 
							$from_user_r['fullname'], 
							$HTTP_VARS['subject'], 
							$HTTP_VARS['message']);
				}
				else
				{
					show_email_form(
							get_user_ids_tovalue(get_user_id_rs(array($HTTP_VARS['usertype']), TRUE)),
							$HTTP_VARS['toname'],
							$from_user_r['user_id'],
							$from_user_r['fullname'],
							$HTTP_VARS['subject'], 
							$HTTP_VARS['message'],
							$HTTP_VARS);
				}
			}
			else if($HTTP_VARS['op'] == 'send_to_uids' && 
						(is_not_empty_array($HTTP_VARS['user_id_rs']) || 
						strlen(trim($HTTP_VARS['checked_user_id_rs_list']))>0))
			{
				// Remove $user_id's that are above the user type of the current user.
				$filtered_user_id_rs = NULL;
				
				reset($HTTP_VARS['user_id_rs']);
				while(list(,$uid) = each($HTTP_VARS['user_id_rs']))
				{
					if(in_array(fetch_user_type($uid), get_min_user_type_r(get_opendb_session_var('user_type'))))
					{
						$filtered_user_id_rs[] = $uid;
					}
				}
				
				if(strlen($HTTP_VARS['subject'])>0 && (strlen($HTTP_VARS['message'])>0 || $HTTP_VARS['no_message']=='true'))
				{
					send_email_to_userids(
							$filtered_user_id_rs, 
							fetch_user_email($from_user_r['user_id']), 
							$from_user_r['fullname'], 
							$HTTP_VARS['subject'], 
							$HTTP_VARS['message']);
				}
				else
				{
					show_email_form(
							get_user_ids_tovalue($filtered_user_id_rs),
							get_opendb_lang_var('users', 'user_desc', get_opendb_config_var('site', 'title')),
							$from_user_r['user_id'],
							$from_user_r['fullname'],
							$HTTP_VARS['subject'],
							$HTTP_VARS['message'],
							$HTTP_VARS);
				}
			}
			else if($HTTP_VARS['op'] == 'send_to_uid' && 
					is_user_valid($HTTP_VARS['uid']) && 
					!is_user_guest(fetch_user_type($HTTP_VARS['uid']), get_opendb_session_var('user_type')))
			{
				if(strlen($HTTP_VARS['subject'])>0 && (strlen($HTTP_VARS['message'])>0 || $HTTP_VARS['no_message']=='true'))
				{
					send_email_to_userids(
							array($HTTP_VARS['uid']), 
							fetch_user_email($from_user_r['user_id']), 
							$from_user_r['fullname'], 
							$HTTP_VARS['subject'], 
							$HTTP_VARS['message']);
				}
				else
				{
					show_email_form(
							$HTTP_VARS['uid'],
							fetch_user_name($HTTP_VARS['uid']),
							$from_user_r['user_id'],
							$from_user_r['fullname'],
							$HTTP_VARS['subject'],
							$HTTP_VARS['message'],
							$HTTP_VARS);
				}
			}
			else if($HTTP_VARS['op'] == 'send_to_site_admin')
			{
				// Avoid any attempts to foil required validation checks.
				$HTTP_VARS['from'] = trim(strip_tags($HTTP_VARS['from']));
				
				$success = FALSE;
				
				// Only try to email if all the info is there, or we have been to the email form.  In the latter case we
				// are also getting the opendb_email function to test the from and subject values for us.
				if(strlen($HTTP_VARS['from'])>0 && strlen($HTTP_VARS['subject'])>0 && (strlen($HTTP_VARS['message'])>0 || $HTTP_VARS['no_message'] == 'true'))
				{
					if(send_email_to_site_admins($HTTP_VARS['from'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $errors))
					{
						echo("<p class=\"success\">".get_opendb_lang_var('message_sent_to')." ".get_opendb_lang_var('site_administrator', 'site', get_opendb_config_var('site', 'title'))."</p>");
						$success = TRUE;
					}
				}

				// Else first time into the email form.
				if(!$success)
				{
					echo format_error_block($errors);
					
					show_email_form(
							NULL,// email 
							get_opendb_lang_var('site_administrator', 'site', get_opendb_config_var('site', 'title')),
							NULL, // from_userid
							NULL, // from_fullname
							$HTTP_VARS['subject'],
							$HTTP_VARS['message'],
							$HTTP_VARS);
				}
			}
		}
		else
		{
			echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
		}

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
