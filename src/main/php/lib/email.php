<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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

include_once("lib/logging.php");
include_once("lib/user.php");
include_once("lib/utils.php");
include_once("lib/http.php");
include_once("lib/opendbmailer.class.php");

function is_valid_opendb_mailer() {
	$mailer = get_opendb_config_var('email', 'mailer');
	return ($mailer == 'mail' || $mailer == 'smtp');
}

/**
    A simple email validation function.  Used by the main 'email' sending
    routine in this script, which allows calling programs to test their
    email addresses with the same test.
    
    Also used in lib/widgets.php 
 */
function is_valid_email_addr($email_addr) {
	if (strlen($email_addr) == 0 || !ereg("^.+@.+\\..+$", $email_addr) || strpos($email_addr, ">") !== FALSE)
		return FALSE;
	else
		return TRUE;
}

function get_email_footer() {
	if (strlen(get_opendb_lang_var('email_footer')) > 0) {
		$site_url = get_site_url();

		$footer_text = get_opendb_lang_var('email_footer', array('site' => get_opendb_config_var('site', 'title'), 'version' => get_opendb_version(), 'site_url' => $site_url));

		return "\n\n" . "--" . "\n" . $footer_text;
	}

	return "";
}

function send_email_to_site_admins($user_role_permissions, $from_email_addr, $subject, $message, &$errors) {
	$success = TRUE;

	if (!is_valid_email_addr($from_email_addr)) {
		$errors[] = get_opendb_lang_var('invalid_from_address');
		$success = FALSE;
	}

	if (strlen($subject) == 0) {
		$errors[] = get_opendb_lang_var('invalid_subject');
		$success = FALSE;
	}

	if ($success) {
		$success = FALSE;

		$results = fetch_user_rs($user_role_permissions);
		while ($user_r = db_fetch_assoc($results)) {
			if (opendb_user_email($user_r['user_id'], $from_email_addr, $subject, $message, $errors)) {
				$success = TRUE;
			}
		}
	}

	return $success;
}

/**
 * Email to be sent from one OpenDb user to another
 * 
 * @from_userid can be null, and in this case, the from address will be the configured no-reply address for
 * the psuedo administrator.
 */
function opendb_user_email($to_userid, $from_userid, $subject, $message, &$errors, $append_site_to_subject = TRUE) {
	$to_userid = trim($to_userid);
	if (is_user_permitted_to_receive_email($to_userid)) {
		$to_user_r = fetch_user_r($to_userid);
		$to_email_addr = trim($to_user_r['email_addr']);
		$to_name = trim($to_user_r['fullname']);

		$from_userid = trim($from_userid);
		if (is_user_valid($from_userid)) {
			$from_user_r = fetch_user_r($from_userid);
			$from_email_addr = trim($from_user_r['email_addr']);
			$from_name = trim($from_user_r['fullname']);
		} else if (strlen($from_userid) == 0) {
			$from_email_addr = trim(get_opendb_config_var('email', 'noreply_address'));
			$from_name = trim(get_opendb_lang_var('noreply'));
		} else //if(is_valid_email_addr($from_userid))
 {
			$from_email_addr = $from_userid;
		}

		if (!is_valid_email_addr($to_email_addr)) {
			$errors[] = get_opendb_lang_var('invalid_to_address');
			return FALSE;
		}

		if (!is_valid_email_addr($from_email_addr)) {
			$errors[] = get_opendb_lang_var('invalid_from_address');
			return FALSE;
		}

		$subject = trim(stripslashes($subject));
		if (strlen($subject) == 0) {
			$errors[] = get_opendb_lang_var('invalid_subject');
			return FALSE;
		}

		if ($append_site_to_subject) {
			$subject .= " [" . get_opendb_config_var('site', 'title') . "]";
		}

		$message = trim(stripslashes($message));

		$message .= get_email_footer();

		if (sendEmail($to_email_addr, $to_name, $from_email_addr, $from_name, $subject, $message, $errors)) {

			insert_email($to_userid, $from_userid != $from_email_addr ? $from_userid : NULL, $from_email_addr, // insert email function will set this to NULL if from user provided!
			$subject, $message);

			return TRUE;
		}
	}

	//else
	return FALSE;
}

/**
    @param to
    @param toname
    @param from
    @param fromname
    @param subject
    @param message

    @returns TRUE on success, or array of errors on failure.
 */
function sendEmail($to, $toname, $from, $fromname, $subject, $message, &$errors) {
	$mailer = new OpenDbMailer(ifempty(get_opendb_config_var('email', 'mailer'), 'mail'));

	$mailer->From = $from;
	$mailer->FromName = $fromname;

	$mailer->AddAddress($to, $toname);
	$mailer->Subject = $subject;
	$mailer->Body = $message;

	if ($mailer->Send()) {
		// No errors returned indicates correct execution.
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Email sent', array($to, $toname, $from, $fromname, $subject));
		return TRUE;
	} else {
		// No errors returned indicates correct execution.
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, $mailer->ErrorInfo, array($to, $toname, $from, $fromname, $subject));

		$errors[] = $mailer->ErrorInfo;
		return FALSE;
	}
}

/**
 * The table structure could be more sophisticated where a message is sent to multiple
 * addresses, but since the email function does not provide this, I see no reason to
 * do anything more complicated.
 *
 * @param unknown_type $item_id
 * @param unknown_type $author_id
 * @param unknown_type $comment
 * @param unknown_type $rating
 * @return unknown
 */
function insert_email($to_user_id, $from_user_id, $from_email_addr, $subject, $message) {
	$to_user_id = trim($to_user_id);
	$from_user_id = trim($from_user_id);
	$from_email_addr = trim($from_email_addr);

	if (!is_user_valid($to_user_id)) {

		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid To User', array($to_user_id, $from_user_id, $from_email_addr, $subject));
		return FALSE;

	} else if (strlen($from_user_id) > 0 && !is_user_valid($from_user_id)) {

		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid From User', array($to_user_id, $from_user_id, $from_email_addr, $subject));
		return FALSE;

	} else if (strlen($from_user_id) == 0 && (strlen($from_email_addr) == 0 || !is_valid_email_addr($from_email_addr))) {

		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid From Email', array($to_user_id, $from_user_id, $from_email_addr, $subject));
		return FALSE;
	}

	if (strlen($from_user_id) > 0) {
		$from_email_addr = NULL;
	} else {
		$from_email_addr = addslashes($from_email_addr);
	}

	$subject = addslashes(trim($subject));
	$message = addslashes(replace_newlines(trim($message)));

	$query = "INSERT INTO mailbox (to_user_id,from_user_id,from_email_addr,subject,message)" . "VALUES ('$to_user_id'," . (strlen($from_user_id) > 0 ? "'$from_user_id'" : "NULL") . "," . (strlen($from_email_addr) > 0 ? "'$from_email_addr'" : "NULL") . ", '$subject','$message')";

	$insert = db_query($query);
	if ($insert && db_affected_rows() > 0) {
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($to_user_id, $from_user_id, $from_email_addr, $subject));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($to_user_id, $from_user_id, $from_email_addr, $subject));
		return FALSE;
	}
}
?>