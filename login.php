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

/**
    Assumes op=login and login params have been provided

    if(strlen($HTTP_VARS['uid'])>0 && strlen($HTTP_VARS['passwd'])>0)

    returns:
        SITE_IS_DISABLED - if user is not admin and site is diabled
        FALSE - if login failure
        TRUE - if login successful

    Does not perform any redirects
 */
function perform_login($HTTP_VARS) {
	$HTTP_VARS['uid'] = strtolower($HTTP_VARS['uid']);// make lowercase
	if (is_user_active($HTTP_VARS['uid']) && validate_user_passwd($HTTP_VARS['uid'], $HTTP_VARS['passwd'])) {
		if (get_opendb_config_var('site', 'enable') !== FALSE || is_user_granted_permission(PERM_ADMIN_LOGIN)) {
			register_user_login($HTTP_VARS['uid'], $HTTP_VARS['remember'] == 'true');

			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User logged in', array($HTTP_VARS['uid']));

			return TRUE;
		} else {
			opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User tried to log in while site is disabled', array($HTTP_VARS['uid']));

			return "SITE_IS_DISABLED";
		}
	} else { //if(is_user_active($HTTP_VARS['uid']) && validate_user_passwd($HTTP_VARS['uid'], $HTTP_VARS['passwd']))
 		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User failed to login', array($HTTP_VARS['uid']));

		return FALSE;
	}
}

function show_login_form($HTTP_VARS, $errors = NULL) {
	global $PHP_SELF;

	echo _theme_header(get_opendb_lang_var('login'), is_show_login_menu_enabled());

	echo ("<h2>" . get_opendb_lang_var('login') . "</h2>");

	if (is_not_empty_array($errors))
		echo format_error_block($errors);

	echo ("<div id=\"loginFormDiv\">");

	if ($HTTP_VARS['rememberMeLogin'] == 'true') {
		echo ("<p class=\"rememberMeLoginMessage\">" . get_opendb_lang_var('not_authorized_to_page_login') . "</p>");
	}
	
	if (strlen($HTTP_VARS['redirect']) > 0) {
		echo ("<p class=\"redirectMessage\">" . get_opendb_lang_var('login_redirect_message', array('pageid' => get_page_id($HTTP_VARS['redirect']))) . "</p>");
	}
	
	echo ("<form id=\"loginForm\" action=\"$PHP_SELF\" method=\"POST\" name=\"login\">");

	// The user tried to go straight to a menu item with an invalid session.
	// Set a "redirect" variable here so that after we give them a full session
	// we can redirect them back to the page they really wanted.
	if (strlen($HTTP_VARS['redirect']) > 0) {
		echo ("<input type=\"hidden\" name=\"redirect\" value=\"" . $HTTP_VARS['redirect'] . "\">");
	}

	echo ("<input type=\"hidden\" name=\"op\" value=\"login\">");

	echo ("\n<ul>" . 
			"\n<li><label class=\"label\" for=\"uid\">" . get_opendb_lang_var('userid') . "</label>" . 
			"<input type=\"text\" class=\"text\" id=\"uid\" name=\"uid\" value=\"" . $HTTP_VARS['uid'] . "\"></li>" . 
			
			"\n<li><label class=\"label\" for=\"password\">"
			. get_opendb_lang_var('password') . "</label>" . 
			"<input type=\"password\" class=\"password\" id=\"passwd\" name=\"passwd\"></li>");

	echo("\n<li><label class=\"label\" for=\"remember\">"
				. get_opendb_lang_var('remember_me') . "</label>" .
				"<input type=\"checkbox\" class=\"remember\" id=\"remember\" name=\"remember\" value=\"true\"></li>"
	);
	
	echo (		"</ul>" . 
			
			"\n<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('login') . "\">");

	echo ("</form>");

	// force uid field focus for login
	echo ("\n<script type=\"text/javascript\">
		document.forms['login']['uid'].focus();
	</script>");

	$footer_links_r = array();
	if (is_site_enabled() && is_valid_opendb_mailer()) {
		if (strlen($HTTP_VARS['uid']) > 0 && get_opendb_config_var('login', 'enable_new_pwd_gen') !== FALSE && is_user_granted_permission(PERM_CHANGE_PASSWORD, $HTTP_VARS['uid'])) {
			$footer_links_r[] = array('url' => $PHP_SELF . "?op=newpassword&uid=" . urlencode($HTTP_VARS['uid']), 'text' => get_opendb_lang_var('forgot_your_pwd'));
		}

		// no point if site disabled, email is not available
		if (get_opendb_config_var('email', 'send_to_site_admin') !== FALSE) {
			$footer_links_r[] = array('text' => get_opendb_lang_var('email_administrator'), 'target' => "popup(640,480)", 'url' => "email.php?op=send_to_site_admin&inc_menu=N");
		}
	}

	// Indicate we should show the signup link.
	if (get_opendb_config_var('login.signup', 'enable') !== FALSE) {
		$footer_links_r[] = array('url' => "user_admin.php?op=signup", 'text' => get_opendb_lang_var('sign_me_up'));
	}

	echo format_footer_links($footer_links_r);

	echo ("</div>");

	echo (_theme_footer());
}

function perform_newpassword($HTTP_VARS, &$errors) {
	if (!is_user_valid($HTTP_VARS['uid'])) {
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: User does not exist', array($HTTP_VARS['uid']));

		// make user look successful to prevent mining for valid userids
		return TRUE;
	} else if (!is_user_active($HTTP_VARS['uid'])) { // Do not allow new password operation for 'deactivated' user.
 		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: User is not active', array($HTTP_VARS['uid']));
		return FALSE;
	} else if (!is_user_granted_permission(PERM_CHANGE_PASSWORD, $HTTP_VARS['uid'])) {
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: User does not have permission to change password', array($HTTP_VARS['uid']));
		return FALSE;
	} else if (get_opendb_config_var('user_admin', 'user_passwd_change_allowed') === FALSE && !is_user_granted_permission(PERM_ADMIN_CHANGE_PASSWORD)) {
		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'New password request failure: Password change is disabled', array($HTTP_VARS['uid']));
		return FALSE;
	} else {
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'User requested to be emailed a new password', array($HTTP_VARS['uid']));

		$user_r = fetch_user_r($HTTP_VARS['uid']);

		$user_passwd = generate_password(8);

		// only send if valid user (email)
		if (strlen($user_r['email_addr']) > 0) {
			$pass_result = update_user_passwd($HTTP_VARS['uid'], $user_passwd);
			if ($pass_result === TRUE) {
				$subject = get_opendb_lang_var('lost_password');
				$message = get_opendb_lang_var('to_user_email_intro', 'fullname', $user_r['fullname']) . "\n\n" . get_opendb_lang_var('new_passwd_email') . "\n\n" . get_opendb_lang_var('userid') . ": " . $HTTP_VARS['uid'] . "\n" . get_opendb_lang_var('password') . ": " . $user_passwd;

				if (opendb_user_email($user_r['user_id'], NULL, $subject, $message, $errors)) {
					return TRUE;
				} else {
					return "EMAIL_NOT_SENT";
				}
			}
		} else {
			$errors[] = "User '" . $HTTP_VARS['uid'] . "' does not have a valid email address.";
			return FALSE;
		}
	}
}

if (is_opendb_valid_session() && $HTTP_VARS['op'] != 'login' && $HTTP_VARS['op'] != 'newpassword') {
	if (strlen($HTTP_VARS['redirect']) > 0) { // Redirect to requested page, as already logged in.
 		//TODO: This does not work very well with a login page in middle of an item update!
		opendb_redirect(urldecode($HTTP_VARS['redirect']));
	} else { // refresh of login page
 		opendb_redirect('welcome.php');
	}
} else { // invalid session - go to login
	if ($HTTP_VARS['op'] == 'newpassword') {
		if (strlen($HTTP_VARS['uid']) > 0 && get_opendb_config_var('login', 'enable_new_pwd_gen') !== FALSE) {
			echo _theme_header(get_opendb_lang_var('login'), is_show_login_menu_enabled());

			echo ("<h2>" . get_opendb_lang_var('lost_password') . "</h2>");

			$result = perform_newpassword($HTTP_VARS, $errors);
			if ($result === FALSE) {
				echo ("<p class=\"error\">" . get_opendb_lang_var('error_updating_pwd') . "</p>");
				echo ("<p class=\"error\">" . get_opendb_lang_var('if_problem_persists_contact_your_administrator', array('site' => get_opendb_config_var('site', 'title'))) . "</p>");
			} else if ($result === "EMAIL_NOT_SENT") {
				echo ("<p class=\"error\">" . get_opendb_lang_var('error_sending_email') . "</p>");
			} else {
				echo ("<p class=\"success\">" . get_opendb_lang_var('new_passwd_sent') . "</p>");
			}

			// no point if site disabled, email is not available
			if (is_site_enabled() && is_valid_opendb_mailer() && get_opendb_config_var('email', 'send_to_site_admin') !== FALSE) {
				$footer_links_r[] = array('text' => get_opendb_lang_var('email_administrator'), 'target' => "popup(640,480)", 'url' => "email.php?op=send_to_site_admin&inc_menu=N&subject=" . get_opendb_lang_var('lost_password'));
			}

			echo format_footer_links($footer_links_r);
		} else {
			opendb_redirect('welcome.php');
			return;
		}
	} else { //if($HTTP_VARS['op'] == 'login')
		if (strlen($HTTP_VARS['uid'] ?? "") > 0 && strlen($HTTP_VARS['passwd']) > 0) {
			$result = perform_login($HTTP_VARS);
			if ($result === TRUE) {
				if (strlen($HTTP_VARS['redirect']) > 0) {
					// User tried to get in with an invalid session.
					// We've just given her a valid one, so log it
					// appropriately and send a redirect to where she
					// really wanted to go.
					opendb_redirect(urldecode($HTTP_VARS['redirect']));
					return;
				} else {
					opendb_redirect('welcome.php');
					return;
				}
			} else if ($result === "SITE_IS_DISABLED") {
				opendb_site_disabled(get_opendb_config_var('login', 'show_menu') !== FALSE);
			} else { // $result === FALSE
				show_login_form($HTTP_VARS, array('error' => get_opendb_lang_var('login_failure'), 'details' => get_opendb_lang_var('double_check_info')));
			}
		} else {
			show_login_form($HTTP_VARS);
		}
	}
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
