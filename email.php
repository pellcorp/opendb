<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001,2013 by Jason Pell

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

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");
include_once("./lib/email.php");
include_once("./lib/widgets.php");
include_once("./lib/user.php");
include_once("./lib/HTML_Listing.class.php");

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
function show_email_form($to_userid, $to_fullname, $from_userid, $from_fullname, $subject, $message, $HTTP_VARS, $errors) {
	global $PHP_SELF;

	if (strlen($to_userid) > 0 && strlen($to_fullname) > 0)
		$to = get_opendb_lang_var('user_name', array('fullname' => $to_fullname, 'user_id' => $to_userid));
	else if (strlen($to_fullname) > 0)
		$to = $to_fullname;
	else if (strlen($to_userid) > 0)
		$to = $to_userid;

	$isFromReadonly = FALSE;

	if (strlen($from_userid) > 0 && strlen($from_fullname) > 0) {
		$from = get_opendb_lang_var('current_user', array('fullname' => $from_fullname, 'user_id' => $from_userid));
		$isFromReadonly = TRUE;
	} else if (strlen($from_fullname) > 0) {
		$from = $from_fullname; // this is an email address
	} else if (strlen($from_userid) > 0) {
		$from = $from_userid; // this is an email address
	}

	if (is_array($errors)) {
		echo format_error_block($errors);
	}

	echo ("\n<form action=\"$PHP_SELF\" method=\"POST\">");

	echo get_url_fields($HTTP_VARS, array('op2' => 'send'), array('subject', 'message'));

	echo ("\n<table class=\"emailForm\">");
	echo format_field(get_opendb_lang_var('to'), $to);

	echo get_input_field("from", NULL, // s_attribute_type
	get_opendb_lang_var('from'), $isFromReadonly ? "readonly" : "email(50,100)", //input type.
	"Y", //compulsory!
	$from, TRUE);

	echo get_input_field("subject", NULL, // s_attribute_type
	get_opendb_lang_var('subject'), "text(50,100)", //input type.
	"Y", //compulsory!
	$subject, TRUE);

	echo get_input_field("message", NULL, // s_attribute_type
	get_opendb_lang_var('message'), "textarea(50,10)", //input type.
	"N", //compulsory!
	$message, TRUE);

	echo ("</table>");

	$help_block_r[] = array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'),
                            'id' => 'compulsory');

	echo format_help_block($help_block_r);

	echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">");
	echo ("\n</form>");

}

/*
 * Will not check whether $user_id_rs contains the current users user_id.  It is expected
 * that this should have already been done.
 */
function send_email_to_userids($user_id_rs, $from_userid, $subject, $message, &$errors) {
	if (strlen($subject) == 0) {
		$errors[] = get_opendb_lang_var('invalid_subject');
		return FALSE;
	}

	reset($user_id_rs);
	foreach( $user_id_rs as $user_id) {
		$touser_r = fetch_user_r($user_id);
		if (is_not_empty_array($touser_r)) {
			if (opendb_user_email($touser_r['user_id'], $from_userid, $subject, $message, $errors)) {
				$success[] = $touser_r['fullname'] . " (" . $user_id . ")";
			} else {
				$failures[] = array('user' => $touser_r['fullname'] . " (" . $user_id . ")",
                                    'error' => $errors);
			}
			$errors = NULL;
		}
	}

	if (is_not_empty_array($success)) {
		echo ("<p class=\"success\">" . get_opendb_lang_var('message_sent_to') . ": <ul>");
		foreach ( $success as $touser) {
			echo ("<li class=\"smsuccess\">" . $touser . "</li>");
		}
		echo ("</ul></p>");
	}

	if (is_not_empty_array($failures)) {
		echo ("<p class=\"error\">" . get_opendb_lang_var('message_not_sent_to') . ": <ul>");
		foreach ( $failures as $failure_r) {
			echo ("<li class=\smerror\">" . $failure_r['user'] . format_error_block($failure_r['error']) . "</li>");
		}
		echo ("</ul></p>");
	}

	return TRUE;
}

function get_user_id_rs() {
	$user_id_rs = NULL;
	$result = fetch_user_rs(PERM_RECEIVE_EMAIL, INCLUDE_ROLE_PERMISSIONS, INCLUDE_CURRENT_USER, EXCLUDE_DEACTIVATED_USER, TRUE, 'user_id', 'ASC');
	if ($result) {
		while ($user_r = db_fetch_assoc($result)) {
			$user_id_rs[] = $user_r['user_id'];
		}
		db_free_result($result);
	}
	return $user_id_rs;
}

/**
 */
function get_user_ids_tovalue($user_id_rs) {
	$to = "";
	if (is_not_empty_array($user_id_rs)) {
		reset($user_id_rs);
		foreach ( $user_id_rs as $user_id) {
			if (strlen($to) == 0)
				$to = $user_id;
			else
				$to .= ", " . $user_id;
		}
	}
	return $to;
}

if (is_site_enabled()) {
	if (is_opendb_valid_session() || 
			($HTTP_VARS['op'] == 'send_to_site_admin' 
					&& get_opendb_config_var('email', 'send_to_site_admin') !== FALSE)) {

		// no email functionality is available unless a valid mailer is configured.
		if (is_valid_opendb_mailer()) {
			// Avoid any attempts to foil required validation checks.
			$HTTP_VARS['subject'] = trim(strip_tags($HTTP_VARS['subject']));
			$HTTP_VARS['message'] = trim(strip_tags($HTTP_VARS['message']));

			if ($HTTP_VARS['op'] == 'send_to_site_admin') {
				// Avoid any attempts to foil required validation checks.
				$HTTP_VARS['from'] = trim(strip_tags($HTTP_VARS['from']));

				if ($HTTP_VARS['op2'] == 'send' && send_email_to_site_admins(PERM_ADMIN_SEND_EMAIL, $HTTP_VARS['from'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $errors)) {
					echo _theme_header(get_opendb_lang_var('send_email'), $HTTP_VARS['inc_menu']);
					echo ("<h2>" . get_opendb_lang_var('send_email') . "</h2>");
					
					echo ("<p class=\"success\">" . get_opendb_lang_var('message_sent_to') . " " . get_opendb_lang_var('site_administrator', 'site', get_opendb_config_var('site', 'title')) . "</p>");
					echo _theme_footer();
				} else {
					echo _theme_header(get_opendb_lang_var('send_email'), $HTTP_VARS['inc_menu']);
					echo ("<h2>" . get_opendb_lang_var('send_email') . "</h2>");
					
					show_email_form(NULL, // email 
					get_opendb_lang_var('site_administrator', 'site', get_opendb_config_var('site', 'title')), $HTTP_VARS['from'], // from_userid
					NULL, // from_fullname
					$HTTP_VARS['subject'], $HTTP_VARS['message'], $HTTP_VARS, $errors);
					echo _theme_footer();
				}
			} else if (($HTTP_VARS['op'] == 'send_to_all' || $HTTP_VARS['op'] == 'send_to_uids')) {
				if (is_user_granted_permission(PERM_ADMIN_SEND_EMAIL)) {
					echo _theme_header(get_opendb_lang_var('send_email'), $HTTP_VARS['inc_menu']);
					echo ("<h2>" . get_opendb_lang_var('send_email') . "</h2>");
					
					$from_user_r = fetch_user_r(get_opendb_session_var('user_id'));
					$HTTP_VARS['toname'] = trim(strip_tags($HTTP_VARS['toname']));
	
					if ($HTTP_VARS['op'] == 'send_to_all') {
						// Default toname for bulk email.
						if (strlen($HTTP_VARS['toname']) == 0) {
							$HTTP_VARS['toname'] = get_opendb_lang_var('site_users', 'user_desc', get_opendb_config_var('site', 'title'));
						}
	
						$user_id_r = get_user_id_rs();
						if (is_not_empty_array($user_id_r)) {
							if ($HTTP_VARS['op2'] == 'send' && send_email_to_userids($user_id_r, $from_user_r['user_id'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $errors)) {
								// do nothing 
							} else {
								show_email_form(get_user_ids_tovalue($user_id_r), $HTTP_VARS['toname'], $from_user_r['user_id'], $from_user_r['fullname'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $HTTP_VARS, $errors);
							}
						} else {
							echo ("<p class=\"error\">" . get_opendb_lang_var('no_users_found') . "</p>");
						}
					} else if ($HTTP_VARS['op'] == 'send_to_uids' && (is_not_empty_array($HTTP_VARS['user_id_rs']) || strlen(trim($HTTP_VARS['checked_user_id_rs_list'])) > 0)) {
						if ($HTTP_VARS['op2'] == 'send' && send_email_to_userids($HTTP_VARS['user_id_rs'], $from_user_r['user_id'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $errors)) {
							// do nothing
						} else {
							show_email_form(get_user_ids_tovalue($HTTP_VARS['user_id_rs']), get_opendb_lang_var('site_users', 'user_desc', get_opendb_config_var('site', 'title')), $from_user_r['user_id'], $from_user_r['fullname'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $HTTP_VARS, $errors);
						}
					}
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_SEND_EMAIL, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'send_to_uid' && is_user_permitted_to_receive_email($HTTP_VARS['uid'])) {
				if (is_user_granted_permission(PERM_SEND_EMAIL)) {
					echo _theme_header(get_opendb_lang_var('send_email'), $HTTP_VARS['inc_menu']);
					echo ("<h2>" . get_opendb_lang_var('send_email') . "</h2>");
					$from_user_r = fetch_user_r(get_opendb_session_var('user_id'));
					$HTTP_VARS['toname'] = trim(strip_tags($HTTP_VARS['toname']));
	
					if ($HTTP_VARS['op2'] == 'send' && send_email_to_userids(array($HTTP_VARS['uid']), $from_user_r['user_id'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $errors)) {
						// do nothing
					} else {
						show_email_form($HTTP_VARS['uid'], fetch_user_name($HTTP_VARS['uid']), $from_user_r['user_id'], $from_user_r['fullname'], $HTTP_VARS['subject'], $HTTP_VARS['message'], $HTTP_VARS, $errors);
					}
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_SEND_EMAIL, $HTTP_VARS);
				}
			} else {
				opendb_operation_not_available();
			}
		} else {
			opendb_operation_not_available();
		}
	} else {
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
}//if(is_site_enabled())
 else {
	opendb_site_disabled();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
