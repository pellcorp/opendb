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

include_once("./lib/user.php");
include_once("./lib/widgets.php");
include_once("./lib/http.php");
include_once("./lib/email.php");
include_once("./lib/datetime.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/item.php");
include_once("./lib/review.php");
include_once("./lib/item_input.php");
include_once("./lib/address_type.php");
include_once("./lib/secretimage.php");

function is_user_granted_update_permission($HTTP_VARS) {
	if ($HTTP_VARS['user_id'] === get_opendb_session_var('user_id') && is_user_granted_permission(PERM_EDIT_USER_PROFILE))
		return TRUE;
	else if (is_user_granted_permission(PERM_ADMIN_USER_PROFILE))
		return TRUE;
	else
		return FALSE;
}

function is_user_granted_change_password($HTTP_VARS) {
	if ($HTTP_VARS['user_id'] === get_opendb_session_var('user_id') && is_user_granted_permission(PERM_CHANGE_PASSWORD) && get_opendb_config_var('user_admin', 'user_passwd_change_allowed') !== FALSE) {
		return TRUE;
	} else if (is_user_granted_permission(PERM_ADMIN_CHANGE_PASSWORD)) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function perform_changeuser($HTTP_VARS) {
	// save existing user_id so we can restore it.
	register_opendb_session_var('admin_user_id', get_opendb_session_var('user_id'));

	$user_r = fetch_user_r($HTTP_VARS['uid']);
	register_opendb_session_var('user_id', $HTTP_VARS['uid']);

	opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Administrator changed user');
}

function show_changeuser_form() {
	echo ("<h2>" . get_opendb_lang_var('change_user') . "</h2>");

	echo ("\n<form action=\"user_admin.php\" method=\"GET\">");
	echo ("\n<input type=\"hidden\" name=\"op\" value=\"change_user\">");

	echo ("\n<table class=\"changeUserForm\">");
	$results = fetch_user_rs(PERM_ADMIN_CHANGE_USER, EXCLUDE_ROLE_PERMISSIONS, EXCLUDE_CURRENT_USER, EXCLUDE_DEACTIVATED_USER, 'fullname', 'ASC');
	if ($results) {
		echo (format_field(get_opendb_lang_var('user'), custom_select('uid', $results, '%fullname% (%user_id%)', 1, NULL, 'user_id')));
	} else {
		echo (format_field(get_opendb_lang_var('user'), get_opendb_lang_var('no_records_found')));
	}
	echo ("</table>");

	echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">");
	echo ("</form>");
}

/**
 * @param $op is 'edit' or 'new'
 */
function get_user_input_form($user_r, $HTTP_VARS) {
	global $PHP_SELF;

	$buffer .= "<form action=\"$PHP_SELF\" method=\"POST\">";

	$buffer .= "<table class=\"userInputForm\">";
	if (is_not_empty_array($user_r)) {
		$buffer .= get_input_field("user_id", NULL, // s_attribute_type
			get_opendb_lang_var('userid'), "readonly", //input type.
			"", //compulsory!
			$user_r['user_id'], TRUE);
	} else {
		$buffer .= get_input_field("user_id", NULL, // s_attribute_type
		get_opendb_lang_var('userid'), "filtered(20,20,a-zA-Z0-9_.)", //input type.
		"Y", //compulsory!
		$HTTP_VARS['user_id'], TRUE);
	}

	if (is_not_empty_array($user_r) && !is_user_granted_permission(PERM_ADMIN_USER_PROFILE)) {
		$role_r = fetch_role_r($user_r['user_role']);

		$buffer .= get_input_field("user_role", NULL, // s_attribute_type
			get_opendb_lang_var('user_role'), "readonly", //input type.
			"", //compulsory!
			$role_r['description'], TRUE);
	} else {
		$buffer .= format_field(get_opendb_lang_var('user_role'),
				custom_select('user_role', fetch_user_role_rs($HTTP_VARS['op'] == 'signup' ? EXCLUDE_SIGNUP_UNAVAILABLE_USER : INCLUDE_SIGNUP_UNAVAILABLE_USER), "%description%", '1', ifempty($user_r['user_role'], $HTTP_VARS['user_role']), 'role_name'));
	}

	$buffer .= get_input_field("fullname", NULL, // s_attribute_type
				get_opendb_lang_var('fullname'), "text(30,100)", //input type.
				"Y", //compulsory!
				ifempty($HTTP_VARS['fullname'], $user_r['fullname']), TRUE);

	$buffer .= get_input_field("email_addr", NULL, // s_attribute_type
				get_opendb_lang_var('email'), "email(30,100)", //input type.
				"Y", //compulsory!
				ifempty($HTTP_VARS['email_addr'], $user_r['email_addr']), TRUE);

	if (get_opendb_config_var('user_admin', 'user_themes_support') !== FALSE) {
		$uid_theme = ifempty($HTTP_VARS['uid_theme'], $user_r['theme']);
		$buffer .= format_field(get_opendb_lang_var('user_theme'), custom_select("uid_theme", get_user_theme_r(), "%value%", 1, is_exists_theme($uid_theme) ? $uid_theme : get_opendb_config_var('site', 'theme')));// If theme no longer exists, then set to default!
	}

	if (get_opendb_config_var('user_admin', 'user_language_support') !== FALSE) {
		// Do not bother with language input field if only one language pack available.
		if (fetch_language_cnt() > 1) {
			$uid_language = ifempty($HTTP_VARS['uid_language'], $user_r['language']);

			$buffer .= format_field(get_opendb_lang_var('user_language'),
					custom_select('uid_language', fetch_language_rs(), "%language%", 1, is_exists_language($uid_language) ? $uid_language : get_opendb_config_var('site', 'language'), 'language', NULL, 'default_ind'));// If language no longer exists, then set to default!
		}
	}

	$buffer .= "</table>";

	// Now do the addresses
	if (is_not_empty_array($user_r)) {
		$addr_results = fetch_user_address_type_rs($user_r['user_id'], TRUE);
	} else {
		$addr_results = fetch_address_type_rs(TRUE);
	}

	if ($addr_results) {
		while ($address_type_r = db_fetch_assoc($addr_results)) {
			$v_address_type = strtolower($address_type_r['s_address_type']);

			if (is_not_empty_array($user_r)) {
				$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], 'update', TRUE);
			} else {
				$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], 'update', TRUE);
			}

			if ($attr_results) {
				$buffer .= '<h3>' . $address_type_r['description'] . '</h3>';

				$buffer .= "<ul class=\"addressIndicators\">";
				$buffer .= '<li><input type="checkbox" class="checkbox" name="' . $v_address_type . '[public_address_ind]" value="Y"' . (ifempty($address_type_r['public_address_ind'], $HTTP_VARS[$v_address_type]['public_address_ind']) == 'Y' ? ' CHECKED' : '') . '">'
						. get_opendb_lang_var('public_address_indicator') . '</li>';
				$buffer .= '<li><input type="checkbox" class="checkbox" name="' . $v_address_type . '[borrow_address_ind]" value="Y"' . (ifempty($address_type_r['borrow_address_ind'], $HTTP_VARS[$v_address_type]['borrow_address_ind']) == 'Y' ? ' CHECKED' : '') . '">'
						. get_opendb_lang_var('borrow_address_indicator') . '</li>';
				$buffer .= "</ul>";

				$buffer .= "<table class=\"addressInputForm\">";
				while ($addr_attribute_type_r = db_fetch_assoc($attr_results)) {
					$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);

					$value = NULL;
					if ($address_type_r['sequence_number'] !== NULL) {
						if (is_lookup_attribute_type($addr_attribute_type_r['s_attribute_type'])) {
							$value = fetch_user_address_lookup_attribute_val($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);
						} else {

							$value = fetch_user_address_attribute_val($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);
						}

						$value = ifempty(filter_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname]), $value);
					} else {
						$value = filter_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname]);
					}

					// If this is an edit operation - the value must be NOT NULL
					// for some widgets to work properly.
					if ($address_type_r['sequence_number'] !== NULL && $value === NULL) {
						$value = '';
					}

					$buffer .= get_item_input_field($v_address_type . '[' . $fieldname . ']', $addr_attribute_type_r, NULL, //$item_r
					$value);
				}//while
				db_free_result($attr_results);
				$buffer .= "</table>";
			}//if($attr_results)
		}//while
		db_free_result($addr_results);
	}//if($addr_results)

	$buffer .= format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory'));

	if ($HTTP_VARS['op'] == 'new_user') {
		$buffer .= "<h3>" . get_opendb_lang_var('password') . "</h3>";

		if (get_opendb_config_var('user_admin', 'user_passwd_change_allowed') !== FALSE || is_user_granted_permission(PERM_ADMIN_CHANGE_PASSWORD)) {
			$buffer .= "<table class=\"changePasswordForm\">";

			if (is_valid_opendb_mailer())
				$compulsory_ind = 'N';
			else
				$compulsory_ind = 'Y';

			$buffer .= get_input_field("pwd", NULL, // s_attribute_type
					get_opendb_lang_var('new_passwd'), "password(30,40)", //input type.
					$compulsory_ind, //compulsory!
					"", TRUE);

			$buffer .= get_input_field("confirmpwd", NULL, // s_attribute_type
					get_opendb_lang_var('confirm_passwd'), "password(30,40)", //input type.
					$compulsory_ind, //compulsory!
					"", TRUE, NULL,
					get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE ? "if( (this.form.pwd.value.length!=0 || this.form.confirmpwd.value.length!=0) && this.form.pwd.value!=this.form.confirmpwd.value){alert('" . get_opendb_lang_var('passwds_do_not_match')
									. "'); this.focus(); return false;}" : "");

			$buffer .= "\n</table>";

			if ($compulsory_ind == 'N') {
				$buffer .= format_help_block(get_opendb_lang_var('new_passwd_will_be_autogenerated_if_not_specified'));
			}
		}
	}

	if ($HTTP_VARS['op'] == 'signup' && get_opendb_config_var('login.signup', 'disable_captcha') !== TRUE) {
		$buffer .= render_secret_image_form_field();
	}

	if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
		$onclick_event = "if(!checkForm(this.form)){return false;}else{this.form.submit();}";
	else
		$onclick_event = "this.form.submit();";

	if (is_not_empty_array($user_r)) {
		$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"update\">";

		if ($HTTP_VARS['user_id'] != get_opendb_session_var('user_id')) {
			$buffer .= "\n<input type=\"button\" class=\"button\" onclick=\"this.form.op.value='update'; $onclick_event\" value=\"" . get_opendb_lang_var('update_user') . "\">";

			if (is_user_not_activated($HTTP_VARS['user_id'])) {
				$buffer .= "\n<input type=\"button\" class=\"button\" onclick=\"this.form.op.value='delete'; this.form.submit();\" value=\"" . get_opendb_lang_var('delete_user') . "\">";
			} else if (is_user_active($HTTP_VARS['user_id'])) {
				$buffer .= "\n<input type=\"button\" class=\"button\" onclick=\"this.form.op.value='deactivate'; this.form.submit();\" value=\"" . get_opendb_lang_var('deactivate_user') . "\">";
			}

			if (!is_user_active($HTTP_VARS['user_id'])) {
				$buffer .= "\n<input type=\"button\" class=\"button\" onclick=\"this.form.op.value='activate'; this.form.submit();\" value=\"" . get_opendb_lang_var('activate_user') . "\">";
			}
		} else {
			$buffer .= "\n<input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"" . get_opendb_lang_var('update_details') . "\">";
		}
	} else {
		if ($HTTP_VARS['op'] != 'signup') {
			if (is_valid_opendb_mailer()) {
				if ($HTTP_VARS['op'] == 'new_user') {
					if ($HTTP_VARS['email_user'] == 'Y')
						$checked = "CHECKED";
					else
						$checked = "";
				} else
					$checked = "CHECKED";

				$buffer .= "<p><input type=\"checkbox\" class=\"checkbox\" id=\"email_user\" name=\"email_user\" value=\"Y\" $checked>" . get_opendb_lang_var('send_welcome_email') . "</p>";
			}

			$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"insert\">" . "\n<input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"" . get_opendb_lang_var('add_user') . "\">";

		} else {
			$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"signup\">" . "<input type=\"hidden\" name=\"op2\" value=\"send_info\">" . "<input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"" . get_opendb_lang_var('submit') . "\">";
		}
	}

	$buffer .= "\n</form>";

	return $buffer;
}

function get_user_password_change_form($user_r, $HTTP_VARS) {
	global $PHP_SELF;

	$buffer .= "<form action=\"$PHP_SELF\" method=\"POST\">";

	$buffer .= "<table class=\"changePasswordForm\">";
	$buffer .= get_input_field("user_id", NULL, // s_attribute_type
			get_opendb_lang_var('userid'), "readonly", //input type.
			"", //compulsory!
			$user_r['user_id'], TRUE);

	$buffer .= get_input_field("pwd", NULL, // s_attribute_type
			get_opendb_lang_var('new_passwd'), "password(30,40)", //input type.
			'Y', //compulsory!
			"", TRUE);

	$buffer .= get_input_field("confirmpwd", NULL, // s_attribute_type
			get_opendb_lang_var('confirm_passwd'), "password(30,40)", //input type.
			'Y', //compulsory!
			"", TRUE, NULL,
			get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE ? "if( (this.form.pwd.value.length!=0 || this.form.confirmpwd.value.length!=0) && this.form.pwd.value!=this.form.confirmpwd.value){alert('" . get_opendb_lang_var('passwds_do_not_match')
							. "'); this.focus(); return false;}" : "");

	$buffer .= "</table>";

	$buffer .= format_help_block(array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory'));

	if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
		$onclick_event = "if(!checkForm(this.form)){return false;}else{this.form.submit();}";
	else
		$onclick_event = "this.form.submit();";

	$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"update_password\">" . "\n<input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"" . get_opendb_lang_var('change_password') . "\">";

	$buffer .= "\n</form>";

	return $buffer;
}

function is_empty_attribute($s_attribute_type, $attribute_val) {
	if (is_lookup_attribute_type($s_attribute_type)) {
		if (is_array($attribute_val)) {
			if (count($attribute_val) > 0)
				return TRUE;
			else
				return FALSE;
		} else {
			if (strlen($attribute_val) > 0)
				return TRUE;
			else
				return FALSE;
		}
	} else {
		if (strlen($attribute_val) > 0)
			return TRUE;
		else
			return FALSE;
	}
}

/**
    Send notification to user that account is active
    
    @param - $user_r - a single record from user table
 */
function send_newuser_email($user_r, $passwd, &$errors) {
	$from_user_r = fetch_user_r(get_opendb_session_var('user_id'));

	$subject = get_opendb_lang_var('new_site_account', 'site', get_opendb_config_var('site', 'title'));
	$message = get_opendb_lang_var('to_user_email_intro', 'fullname', $user_r['fullname']) . "\n\n" . get_opendb_lang_var('welcome_email', 'site', get_opendb_config_var('site', 'title')) . "\n\n" . get_opendb_lang_var('userid') . ": " . $user_r['user_id'] . "\n" . get_opendb_lang_var('new_passwd')
			. ": " . $passwd;

	if (is_user_granted_permission(PERM_EDIT_USER_PROFILE)) {
		// Provide a link to open User Info form in edit mode.
		$message .= "\n\n" . get_opendb_lang_var('edit_my_info') . ":\n" . get_site_url() . "user_admin.php?op=edit&user_id=" . urlencode($user_r['user_id']);
	}

	if (is_valid_email_addr($user_r['email_addr'])) {
		return opendb_user_email($user_r['user_id'], $from_user_r['user_id'], $subject, $message, $errors, FALSE);
	}
}

function validate_user_info($user_r, &$HTTP_VARS, &$address_provided_r, &$errors) {
	$address_attribs_provided = NULL;
	$is_address_validated = TRUE;

	// cannot change your role unless you have the permissions
	if (is_array($user_r) && !is_user_granted_permission(PERM_ADMIN_USER_PROFILE)) {
		$HTTP_VARS['user_role'] = $user_r['user_role'];
	} else if ($HTTP_VARS['op'] == 'signup' && !is_valid_signup_role($HTTP_VARS['user_role'])) {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid Signup User Role specified', $HTTP_VARS);
		return FALSE;
	}

	$role_r = fetch_role_r($HTTP_VARS['user_role']);
	if (!is_array($role_r)) {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid User Role specified', $HTTP_VARS);
		return FALSE;
	}

	$HTTP_VARS['fullname'] = filter_input_field("text(30,100)", $HTTP_VARS['fullname']);
	$HTTP_VARS['email_addr'] = filter_input_field("email(30,100)", $HTTP_VARS['email_addr']);

	if (!validate_input_field(get_opendb_lang_var('fullname'), "text(30,100)", "Y", $HTTP_VARS['fullname'], $errors) || !validate_input_field(get_opendb_lang_var('email'), "email(30,100)", "Y", $HTTP_VARS['email_addr'], $errors)) {
		return FALSE;
	}

	if (get_opendb_config_var('user_admin', 'user_themes_support') === FALSE || !is_exists_theme($HTTP_VARS['uid_theme'])) {
		$HTTP_VARS['uid_theme'] = FALSE; // Do not update theme!
	}

	// Do not allow update with illegal language.
	if (get_opendb_config_var('user_admin', 'user_language_support') === FALSE || !is_exists_language($HTTP_VARS['uid_language'])) {
		$HTTP_VARS['uid_language'] = NULL;
	}

	$addr_results = fetch_address_type_rs(TRUE);
	if ($addr_results) {
		while ($address_type_r = db_fetch_assoc($addr_results)) {
			$v_address_type = strtolower($address_type_r['s_address_type']);

			$address_provided_r[$v_address_type] = FALSE;

			$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], 'update', TRUE);
			if ($attr_results) {
				while ($addr_attribute_type_r = db_fetch_assoc($attr_results)) {
					$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);

					$HTTP_VARS[$v_address_type][$fieldname] = filter_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname]);

					if (is_empty_attribute($addr_attribute_type_r['s_attribute_type'], $HTTP_VARS[$v_address_type][$fieldname]) !== FALSE) {
						$address_provided_r[$v_address_type] = TRUE;

						if (!validate_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname], $errors)) {
							$is_address_validated = FALSE;
						}
					}
				}
				db_free_result($attr_results);
			}//if($addr_results)
		}
		db_free_result($addr_results);
	}//if($addr_results)

	return $is_address_validated;
}

function update_user_addresses($user_r, $address_provided_r, $HTTP_VARS, &$errors) {
	// No errors recorded at this stage.
	$errors = NULL;

	$address_creation_success = TRUE;
	$address_type_sequence_number_r = NULL;

	$addr_results = fetch_user_address_type_rs($user_r['user_id'], TRUE);
	if ($addr_results) {
		while ($address_type_r = db_fetch_assoc($addr_results)) {
			$v_address_type = strtolower($address_type_r['s_address_type']);

			$address_creation_success = TRUE;

			// address does not currently exist, so create it.
			if ($address_type_r['sequence_number'] === NULL) {
				if ($address_provided_r[$v_address_type] !== FALSE) {
					$new_sequence_number = insert_user_address($user_r['user_id'], $address_type_r['s_address_type'], $HTTP_VARS[$v_address_type]['public_address_ind'], $HTTP_VARS[$v_address_type]['borrow_address_ind']);
					if ($new_sequence_number !== FALSE) {
						$address_type_r['sequence_number'] = $new_sequence_number;
					} else {
						$address_creation_success = FALSE;
					}
				}
			} else {
				$new_sequence_number = update_user_address($address_type_r['sequence_number'], $HTTP_VARS[$v_address_type]['public_address_ind'], $HTTP_VARS[$v_address_type]['borrow_address_ind']);
			}

			if ($address_creation_success !== FALSE) {
				if ($address_provided_r[$v_address_type] !== FALSE) {
					$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], 'update', TRUE);
					if ($attr_results) {
						while ($addr_attribute_type_r = db_fetch_assoc($attr_results)) {
							$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);

							if (is_lookup_attribute_type($addr_attribute_type_r['s_attribute_type'])) {
								$lookup_value_r = NULL;
								if (is_array($HTTP_VARS[$v_address_type][$fieldname]))
									$lookup_value_r = &$HTTP_VARS[$v_address_type][$fieldname];
								else if (strlen(trim($HTTP_VARS[$v_address_type][$fieldname])) > 0)
									$lookup_value_r[] = $HTTP_VARS[$v_address_type][$fieldname];

								$user_addr_attr_lookup_val_r = fetch_user_address_lookup_attribute_val($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);

								if ($user_addr_attr_lookup_val_r !== FALSE) {
									if (is_not_empty_array($lookup_value_r)) { // insert/update mode
 										if (!update_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $lookup_value_r)) {
											$db_error = db_error();
											$errors[] = array('error' => get_opendb_lang_var('user_address_not_updated'), 'detail' => $db_error);
											$address_creation_success = FALSE;
										}
									} else {
										if (!delete_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'])) {
											$db_error = db_error();
											$errors[] = array('error' => get_opendb_lang_var('user_address_not_updated'), 'detail' => $db_error);
											$address_creation_success = FALSE;
										}
									}
								} else if (is_not_empty_array($lookup_value_r)) {
									if (!insert_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $lookup_value_r)) {
										$db_error = db_error();
										$errors[] = array('error' => get_opendb_lang_var('user_address_not_updated'), 'detail' => $db_error);
										$address_creation_success = FALSE;
									}
								}
							} else {
								$attribute_val = fetch_user_address_attribute_val($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);

								if ($attribute_val !== FALSE) {
									if (strlen($HTTP_VARS[$v_address_type][$fieldname]) > 0) {
										if (!update_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $HTTP_VARS[$v_address_type][$fieldname])) {
											$db_error = db_error();
											$errors[] = array('error' => get_opendb_lang_var('user_address_not_updated'), 'detail' => $db_error);
											$address_creation_success = FALSE;
										}
									} else {
										if (!delete_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'])) {
											$db_error = db_error();
											$errors[] = array('error' => get_opendb_lang_var('user_address_not_updated'), 'detail' => $db_error);
											$address_creation_success = FALSE;
										}
									}
								} else {
									if (strlen($HTTP_VARS[$v_address_type][$fieldname]) > 0) {
										if (!insert_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $HTTP_VARS[$v_address_type][$fieldname])) {
											$db_error = db_error();
											$errors[] = array('error' => get_opendb_lang_var('user_address_not_updated'), 'detail' => $db_error);
											$address_creation_success = FALSE;
										}
									}
								}
							}
						}
						db_free_result($attr_results);
					}
				} else {
					// existing address, we want to get rid of it here
					if ($address_type_r['sequence_number'] !== NULL) {
						if (delete_user_address_attributes($address_type_r['sequence_number'])) {
							delete_user_address($address_type_r['sequence_number']);
						}
					}
				}
			}
		}
		db_free_result($addr_results);
	}

	return $address_creation_success;
}

function handle_user_delete($user_id, $HTTP_VARS, &$errors) {
	if (is_user_valid($user_id) && is_user_not_activated($user_id)) {
		// If already confirmed operation.
		if ($HTTP_VARS['confirmed'] == 'true') {
			// ignore failure to delete user addresses - will be logged.
			delete_user_addresses($user_id);

			if (!delete_user($user_id)) {
				$db_error = db_error();
				$errors = array('error' => get_opendb_lang_var('user_not_deleted'), 'detail' => $db_error);
				return FALSE;
			} else {
				return TRUE;
			}
		} else if ($HTTP_VARS['confirmed'] != 'false') {// confirmation required.
			return "__CONFIRM__";
		} else {
			return "__ABORTED__";
		}
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Attempt to delete a user which is activated or previously activated', $user_id);
		$errors = array('error' => get_opendb_lang_var('operation_not_available'), 'detail' => '');
		return FALSE;
	}
}

function handle_user_insert(&$HTTP_VARS, &$errors) {
	if (!is_user_valid($HTTP_VARS['user_id'])) {
		$HTTP_VARS['user_id'] = strtolower(filter_input_field("filtered(20,20,a-zA-Z0-9_.)", $HTTP_VARS['user_id']));
		if (!validate_input_field(get_opendb_lang_var('userid'), "filtered(20,20,a-zA-Z0-9_.)", "Y", $HTTP_VARS['user_id'], $errors)) {
			return FALSE;
		}

		if (validate_user_info(NULL, $HTTP_VARS, $address_provided_r, $errors)) {
			if ($HTTP_VARS['op'] == 'signup') { // no password saved when signing up, as user still must be activated
 				$active_ind = 'X';

				// Will be reset when user activated
				$HTTP_VARS['pwd'] = NULL;
			} else {
				$active_ind = 'Y';

				if (strlen($HTTP_VARS['pwd']) == 0) {
					if (is_valid_opendb_mailer()) {
						$HTTP_VARS['pwd'] = generate_password(8);
					} else {
						$errors[] = array('error' => get_opendb_lang_var('passwd_not_specified'));
						return FALSE;
					}
				} else if ($HTTP_VARS['pwd'] != $HTTP_VARS['confirmpwd']) {
					$errors[] = array('error' => get_opendb_lang_var('passwds_do_not_match'));
					return FALSE;
				}
			}

			// We want to validate and perform inserts even in signup mode
			if (insert_user($HTTP_VARS['user_id'], $HTTP_VARS['fullname'], $HTTP_VARS['pwd'], $HTTP_VARS['user_role'], $HTTP_VARS['uid_language'], $HTTP_VARS['uid_theme'], $HTTP_VARS['email_addr'], $active_ind)) {
				$user_r = fetch_user_r($HTTP_VARS['user_id']);

				return update_user_addresses($user_r, $address_provided_r, $HTTP_VARS, $errors);
			} else {
				$db_error = db_error();
				$errors[] = array('error' => get_opendb_lang_var('user_not_added', 'user_id', $HTTP_VARS['user_id']), 'detail' => $db_error);
				return FALSE;
			}
		} else {
			return FALSE;
		}
	} else {
		$errors[] = array('error' => get_opendb_lang_var('user_exists', 'user_id', $HTTP_VARS['user_id']), 'detail' => '');
		return FALSE;
	}
}

function handle_user_update(&$HTTP_VARS, &$errors) {
	$user_r = fetch_user_r($HTTP_VARS['user_id']);
	if (is_not_empty_array($user_r)) {
		if (validate_user_info($user_r, $HTTP_VARS, $address_attribs_provided, $errors)) {
			if (update_user($HTTP_VARS['user_id'], $HTTP_VARS['fullname'], $HTTP_VARS['uid_language'], $HTTP_VARS['uid_theme'], $HTTP_VARS['email_addr'], $HTTP_VARS['user_role'])) {
				return update_user_addresses($user_r, $address_provided_r, $HTTP_VARS, $errors);
			} else {
				$db_error = db_error();
				$errors[] = array('error' => get_opendb_lang_var('user_not_updated', 'user_id', $HTTP_VARS['user_id']), 'detail' => $db_error);
				return FALSE;
			}
		} else {
			return FALSE;
		}
	} else {
		$errors[] = array('error' => get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']));
		return FALSE;
	}
}

function handle_user_password_change($user_id, $HTTP_VARS, &$errors) {
	$user_r = fetch_user_r($user_id);
	if (is_not_empty_array($user_r)) {
		// If at least one password specified, we will try to perform update.
		if (strlen($HTTP_VARS['pwd']) > 0 || strlen($HTTP_VARS['confirmpwd']) > 0) {
			if (get_opendb_config_var('user_admin', 'user_passwd_change_allowed') !== FALSE || is_user_granted_permission(PERM_ADMIN_CHANGE_PASSWORD)) {
				if ($HTTP_VARS['pwd'] != $HTTP_VARS['confirmpwd']) {
					$error = get_opendb_lang_var('passwds_do_not_match');
				} else if (strlen($HTTP_VARS['pwd']) == 0) {
					$error = get_opendb_lang_var('passwd_not_specified');
				} else {
					if (update_user_passwd($user_id, $HTTP_VARS['pwd'])) {
						return TRUE;
					} else {
						$error = db_error();
						return FALSE;
					}
				}
			} else {
				return FALSE;
			}
		} else {
			$error = get_opendb_lang_var('passwd_not_specified');
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/*
 * The Deactivate process will not delete any records.  All pending reservations
 * for the users items, and made by the user will be cancelled, but thats it.
 */
function handle_user_deactivate($user_id, $HTTP_VARS, &$errors) {
	if ($user_id == get_opendb_session_var('user_id')) {
		$errors[] = array('error' => get_opendb_lang_var('cannot_deactivate_yourself'), 'detail' => '');
		return FALSE;
	} else if (fetch_my_borrowed_item_cnt($user_id) > 0) {
		$errors[] = array('error' => get_opendb_lang_var('user_with_borrows_not_deactivated'), 'detail' => '');
		return FALSE;
	} else if (fetch_owner_borrowed_item_cnt($user_id) > 0) {
		$errors[] = array('error' => get_opendb_lang_var('user_with_owner_borrows_not_deactivated'), 'detail' => '');
		return FALSE;
	} else if ($HTTP_VARS['confirmed'] == 'true') {
		// Cancel all reservations.
		$results = fetch_owner_reserved_item_rs($user_id);
		if ($results) {
			while ($borrowed_item_r = db_fetch_assoc($results)) {
				cancel_reserve_item($borrowed_item_r['sequence_number']);
			}
			db_free_result($results);
		}

		$results = fetch_my_reserved_item_rs($user_id);
		if ($results) {
			while ($borrowed_item_r = db_fetch_assoc($results)) {
				cancel_reserve_item($borrowed_item_r['sequence_number']);
			}
			db_free_result($results);
		}

		// deactivate user.
		if (deactivate_user($user_id))
			return TRUE;
		else
			return FALSE;
	} else if ($HTTP_VARS['confirmed'] != 'false') { // confirmation required.
 		return "__CONFIRM__";
	} else {
		return "__ABORTED__";
	}
}

function handle_user_activate($user_id, $HTTP_VARS, &$errors) {
	if ($HTTP_VARS['confirmed'] == 'true') {
		if (activate_user($user_id))
			return TRUE;
		else
			return FALSE;
	} else if ($HTTP_VARS['confirmed'] != 'false') { // confirmation required.
 		return "__CONFIRM__";
	} else {
		return "__ABORTED__";
	}
}

function send_signup_info_to_admin($HTTP_VARS, &$errors) {
	global $PHP_SELF;

	$role_r = fetch_role_r($HTTP_VARS['user_role']);

	$user_info_lines = get_opendb_lang_var('userid') . ": " . $HTTP_VARS['user_id'] . "\n" . get_opendb_lang_var('fullname') . ": " . $HTTP_VARS['fullname'] . "\n" . get_opendb_lang_var('user_role') . ": " . $role_r['description'] . "\n" . get_opendb_lang_var('user_theme') . ": "
			. $HTTP_VARS['uid_theme'] . "\n" . get_opendb_lang_var('email') . ": " . $HTTP_VARS['email_addr'];

	$addr_results = fetch_address_type_rs(TRUE);
	if ($addr_results) {
		while ($address_type_r = db_fetch_assoc($addr_results)) {
			$address_type = strtolower($address_type_r['s_address_type']);
			$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], 'update', TRUE);
			if ($attr_results) {
				while ($addr_attribute_type_r = db_fetch_assoc($attr_results)) {
					$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);

					// may have to change this if statement, if fieldname will contain array, instead of scalar value
					if (is_not_empty_array($HTTP_VARS[$address_type][$fieldname]) || (!is_array($HTTP_VARS[$address_type][$fieldname]) && strlen($HTTP_VARS[$address_type][$fieldname]) > 0)) {
						if (is_not_empty_array($HTTP_VARS[$address_type][$fieldname])) {
							$value = '';
							for ($i = 0; $i < count($HTTP_VARS[$address_type][$fieldname]); $i++) {
								if (strlen($value) > 0)
									$value .= ',';

								$value .= $HTTP_VARS[$address_type][$fieldname][$i];
							}
						} else {
							$value = $HTTP_VARS[$address_type][$fieldname];
						}
						$user_info_lines .= "\n" . $addr_attribute_type_r['prompt'] . ": " . $value;
					}
				}
				db_free_result($attr_results);
			}//if($attr_results)
		}
		db_free_result($addr_results);
	}//if($addr_results)

	$activate_url = get_site_url() . 'user_admin.php?op=activate&user_id=' . $HTTP_VARS['user_id'];
	$delete_url = get_site_url() . 'user_admin.php?op=delete&user_id=' . $HTTP_VARS['user_id'];

	$message = get_opendb_lang_var('new_account_email',
			array('admin_name' => get_opendb_lang_var('site_administrator', 'site', get_opendb_config_var('site', 'title')), 'user_info' => $user_info_lines, 'site' => get_opendb_config_var('site', 'title'), 'activate_url' => $activate_url, 'delete_url' => $delete_url));

	return send_email_to_site_admins(PERM_ADMIN_CREATE_USER, $HTTP_VARS['email_addr'], get_opendb_lang_var('new_account'), $message, $errors);
}

if (is_site_enabled()) {
	if (is_opendb_valid_session() || $HTTP_VARS['op'] == 'signup') {
		if ($HTTP_VARS['op'] == 'gfx_code_check' && is_numeric($HTTP_VARS['gfx_random_number'])) {
			secretimage($HTTP_VARS['gfx_random_number']);
		} else {
			if (is_array(get_opendb_session_var('user_listing_url_vars'))) {
				$footer_links_r[] = array('url' => "user_listing.php?" . get_url_string(get_opendb_session_var('user_listing_url_vars')), 'text' => get_opendb_lang_var('back_to_user_listing'));
			}
			
			if ($HTTP_VARS['op'] == 'new_user') {
				if(is_user_granted_permission(PERM_ADMIN_CREATE_USER)) {
					echo _theme_header(get_opendb_lang_var('add_new_user'));
					echo ("<h2>" . get_opendb_lang_var('add_new_user') . "</h2>");
	
					echo (get_user_input_form(NULL, $HTTP_VARS));
					
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_CREATE_USER, $HTTP_VARS, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'edit') {
				if (is_user_granted_update_permission($HTTP_VARS)) {
					if ($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
						$page_title = get_opendb_lang_var('my_info');
					else
						$page_title = get_opendb_lang_var('user_info');
	
					echo _theme_header($page_title);
					echo ("<h2>" . $page_title . "</h2>");
	
					$user_r = fetch_user_r($HTTP_VARS['user_id']);
					if (is_not_empty_array($user_r)) {
						echo (get_user_input_form($user_r, $HTTP_VARS));
					} else { //user not found.
	 					echo ("<p class=\"error\">" . get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']) . "</p>");
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_USER_PROFILE, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'change_password') {
				if (is_user_granted_change_password($HTTP_VARS)) {
					if ($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
						$page_title = get_opendb_lang_var('change_my_password');
					else
						$page_title = get_opendb_lang_var('change_user_password');
	
					echo _theme_header($page_title);
					echo ("<h2>" . $page_title . "</h2>");
	
					$user_r = fetch_user_r($HTTP_VARS['user_id']);
					if (is_not_empty_array($user_r)) {
						echo (get_user_password_change_form($user_r, $HTTP_VARS));
					} else { //user not found.
	 					echo ("<p class=\"error\">" . get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']) . "</p>");
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_CHANGE_PASSWORD, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'update_password') {
				if (is_user_granted_change_password($HTTP_VARS)) {
					if ($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
						$page_title = get_opendb_lang_var('change_my_password');
					else
						$page_title = get_opendb_lang_var('change_user_password');
	
					echo _theme_header($page_title);
					echo ("<h2>" . $page_title . "</h2>");
	
					if (handle_user_password_change($HTTP_VARS['user_id'], $HTTP_VARS, $error)) {
						echo ("<p class=\"success\">" . get_opendb_lang_var('passwd_changed') . "</p>");
					} else {
						echo (format_error_block(array('error' => get_opendb_lang_var('passwd_not_changed'), 'details' => $error)));
	
						$user_r = fetch_user_r($HTTP_VARS['user_id']);
						if (is_not_empty_array($user_r)) {
							$HTTP_VARS['op'] = 'change_password';
							echo get_user_password_change_form($user_r, $HTTP_VARS);
						} else { //user not found.
	 						echo ("<p class=\"error\">" . get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']) . "</p>");
						}
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_CHANGE_PASSWORD, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'change_user' && get_opendb_config_var('user_admin.change_user', 'enable') !== FALSE) {
				if(is_user_granted_permission(PERM_ADMIN_CHANGE_USER)) {
					if (strlen($HTTP_VARS['uid']) > 0 && is_user_active($HTTP_VARS['uid'])) {
						perform_changeuser($HTTP_VARS);
						opendb_redirect('welcome.php');
						return;
					} else {
						echo _theme_header(get_opendb_lang_var('change_user'));
						show_changeuser_form();
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_CHANGE_USER, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'insert') {
				if (is_user_granted_permission(PERM_ADMIN_CREATE_USER)) {
					echo _theme_header(get_opendb_lang_var('add_new_user'));
					echo ("<h2>" . get_opendb_lang_var('add_new_user') . "</h2>");
	
					$return_val = handle_user_insert($HTTP_VARS, $errors);
					if ($return_val !== FALSE) {
						echo ("\n<p class=\"success\">" . get_opendb_lang_var('user_added', 'user_id', $HTTP_VARS['user_id']) . "</p>");
	
						if ($HTTP_VARS['email_user'] == 'Y') {
							$user_r = fetch_user_r($HTTP_VARS['user_id']);
							if (is_valid_opendb_mailer()) {
								if (send_newuser_email($user_r, $HTTP_VARS['pwd'], $errors)) {
									echo ("<p class=\"success\">" . get_opendb_lang_var('welcome_email_sent', $user_r) . "</p>");
								} else {
									echo ("<p class=\"error\">" . get_opendb_lang_var('welcome_email_error', $user_r) . "</p>");
									echo format_error_block($errors);
								}
							}
						}
	
						$footer_links_r[] = array('url' => "$PHP_SELF?op=edit&user_id=" . $HTTP_VARS['user_id'], 'text' => get_opendb_lang_var('edit_user_info'));
					} else { // $return_val === FALSE
	 					echo format_error_block($errors);
						$HTTP_VARS['op'] = 'new_user';
						echo (get_user_input_form(NULL, $HTTP_VARS));
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_CREATE_USER, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'update') {
				if (is_user_granted_update_permission($HTTP_VARS)) {
					if ($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
						$page_title = get_opendb_lang_var('my_info');
					else
						$page_title = get_opendb_lang_var('user_info');
	
					echo _theme_header($page_title);
					echo ("<h2>" . $page_title . "</h2>");
	
					if (handle_user_update($HTTP_VARS, $errors)) {
						// Any warnings that should be displayed.
						if ($errors !== NULL)
							echo format_error_block($errors);
					}
	
					echo format_error_block($errors);
	
					$user_r = fetch_user_r($HTTP_VARS['user_id']);
					if (is_not_empty_array($user_r)) {
						$HTTP_VARS['op'] = 'edit';
						echo get_user_input_form($user_r, $HTTP_VARS);
					} else { //user not found.
	 					echo ("<p class=\"error\">" . get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']) . "</p>");
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_USER_PROFILE, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'deactivate') {
				if (is_user_granted_permission(PERM_ADMIN_USER_PROFILE)) {
					echo _theme_header(get_opendb_lang_var('deactivate_user'));
					echo ("<h2>" . get_opendb_lang_var('deactivate_user') . "</h2>");
	
					if (is_user_valid($HTTP_VARS['user_id'])) {
						// user has to be currently active for a deactivation process to succeed
						if (is_user_active($HTTP_VARS['user_id'])) {
							$return_val = handle_user_deactivate($HTTP_VARS['user_id'], $HTTP_VARS, $errors);
							if ($return_val === "__CONFIRM__") {
								echo get_op_confirm_form($PHP_SELF, get_opendb_lang_var('confirm_user_deactivate', array('fullname' => fetch_user_name($HTTP_VARS['user_id']), 'user_id' => $HTTP_VARS['user_id'])), $HTTP_VARS);
							} else if ($return_val === "__ABORTED__") {
								echo ("<p class=\"success\">" . get_opendb_lang_var('user_not_deactivated') . "</p>");
								$footer_links_r[] = array('url' => "$PHP_SELF?op=edit&user_id=" . $HTTP_VARS['user_id'], 'text' => get_opendb_lang_var('edit_user_info'));
							} else if ($return_val === TRUE) {
								echo ("<p class=\"success\">" . get_opendb_lang_var('user_deactivated') . "</p>");
							} else { //if($return_val === FALSE)
	 							echo format_error_block($errors);
								$footer_links_r[] = array('url' => "$PHP_SELF?op=edit&user_id=" . $HTTP_VARS['user_id'], 'text' => get_opendb_lang_var('edit_user_info'));
							}
						} else { //if(is_user_active($HTTP_VARS['user_id']))
	 						echo format_error_block(get_opendb_lang_var('operation_not_available'));
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']) . "</p>");
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_USER_PROFILE, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'activate') {
				if (is_user_granted_permission(PERM_ADMIN_USER_PROFILE)) {
					echo _theme_header(get_opendb_lang_var('activate_user'));
					echo ("<h2>" . get_opendb_lang_var('activate_user') . "</h2>");
	
					if (is_user_valid($HTTP_VARS['user_id'])) {
						// user must be deactivated in order for this process to continue.
						if (!is_user_active($HTTP_VARS['user_id'])) {
							// if newly activated user, then we want to reset password and
							// send notification email.
							$new_activated_user = is_user_not_activated($HTTP_VARS['user_id']);
	
							$return_val = handle_user_activate($HTTP_VARS['user_id'], $HTTP_VARS, $errors);
							if ($return_val === '__CONFIRM__') {
								echo get_op_confirm_form($PHP_SELF, get_opendb_lang_var('confirm_user_activate', array('fullname' => fetch_user_name($HTTP_VARS['user_id']), 'user_id' => $HTTP_VARS['user_id'])), $HTTP_VARS);
							} else if ($return_val === '__ABORTED__') {
								echo ("<p class=\"success\">" . get_opendb_lang_var('user_not_activated') . "</p>");
							} else if ($return_val === TRUE) {
								echo ("<p class=\"success\">" . get_opendb_lang_var('user_activated') . "</p>");
	
								// reset password and send email
								if ($new_activated_user) {
									$user_passwd = generate_password(8);
									$pass_result = update_user_passwd($HTTP_VARS['user_id'], $user_passwd);
									if ($pass_result === TRUE) {
										$user_r = fetch_user_r($HTTP_VARS['user_id']);
										if (is_valid_opendb_mailer()) {
											if (send_newuser_email($user_r, $user_passwd, $errors)) {
												echo ("\n<p class=\"success\">" . get_opendb_lang_var('welcome_email_sent', $user_r) . "</p>");
											} else {
												echo ("<p class=\"error\">" . get_opendb_lang_var('welcome_email_error', $user_r) . "</p>");
												echo format_error_block($errors);
											}
										}
									}
								}
	
								$footer_links_r[] = array('url' => "$PHP_SELF?op=edit&user_id=" . $HTTP_VARS['user_id'], 'text' => get_opendb_lang_var('edit_user_info'));
							} else {
								echo format_error_block($errors);
								$footer_links_r[] = array('url' => "$PHP_SELF?op=edit&user_id=" . $HTTP_VARS['user_id'], 'text' => get_opendb_lang_var('edit_user_info'));
							}
						} else { //if(!is_user_active($HTTP_VARS['user_id']))
	 						echo format_error_block(get_opendb_lang_var('operation_not_available'));
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']) . "</p>");
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_USER_PROFILE, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'activate_users') {
				if (is_user_granted_permission(PERM_ADMIN_USER_PROFILE)) {
					echo _theme_header(get_opendb_lang_var('activate_users'));
					echo ("<h2>" . get_opendb_lang_var('activate_users') . "</h2>");

					// handle activate of single user in the same way
					if (!is_array($HTTP_VARS['user_id_rs']) && is_user_valid($HTTP_VARS['user_id'])) {
						$HTTP_VARS['user_id_rs'][] = $HTTP_VARS['user_id'];
						unset($HTTP_VARS['user_id']);
					}

					if (is_not_empty_array($HTTP_VARS['user_id_rs'])) {
						// do not display confirm screen
						$HTTP_VARS['confirmed'] = 'true';

						$success_userid_rs = NULL;
						$failure_userid_rs = NULL;

						foreach ($HTTP_VARS['user_id_rs'] as $userid) {
							// if newly activated user, then we want to reset password and send notification email.
							$new_activated_user = is_user_not_activated($userid);
	
							$user_r = fetch_user_r($userid);
	
							$errors = NULL;
	
							if (handle_user_activate($userid, $HTTP_VARS, $errors)) {
								// reset password and send email
								if ($new_activated_user) {
									$user_passwd = generate_password(8);
									$pass_result = update_user_passwd($userid, $user_passwd);
									if ($pass_result === TRUE) {
										if (is_valid_opendb_mailer()) {
											if (send_newuser_email($user_r, $user_passwd, $errors)) {
												$user_r['_send_email_result'] = TRUE;
											} else {
												$user_r['_send_email_result'] = FALSE;
												$user_r['_send_email_errors'] = $errors;
											}
										}
									}
								}
	
								$success_userid_rs[] = $user_r;
							} else {
								$failure_userid_rs[] = $user_r;
							}
						}
	
						if (is_array($success_userid_rs)) {
							echo ("<p class=\"success\">" . get_opendb_lang_var('users_activated') . "</p>");
							echo ("<ul>");
							for ($i = 0; $i < count($success_userid_rs); $i++) {
								echo ("<li class=\"smsuccess\">" . get_opendb_lang_var('user_activated_detail', $success_userid_rs[$i]));
	
								if ($success_userid_rs[$i]['_send_email_result'] !== FALSE) {
									echo ("<ul><li class=\"smsuccess\">" . get_opendb_lang_var('welcome_email_sent', $success_userid_rs[$i]) . "</li></ul>");
								} else {
									echo format_error_block(array('error' => get_opendb_lang_var('welcome_email_error', $success_userid_rs[$i]), 'detail' => $errors));
								}
	
								echo ("</li>");
							}
							echo ("</ul>");
						}
	
						if (is_array($failure_userid_rs)) {
							echo ("<p class=\"error\">" . get_opendb_lang_var('users_not_activated') . "</p>");
							echo ("<ul>");
							for ($i = 0; $i < count($failure_userid_rs); $i++) {
								echo ("<li class=\"smerror\">\"" . get_opendb_lang_var('user_activated_detail', $failure_userid_rs[$i]) . "</li>");
							}
							echo ("</ul>");
						}
					} else { //if(!is_user_active($HTTP_VARS['user_id']))
	 					echo format_error_block(get_opendb_lang_var('operation_not_available'));
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_USER_PROFILE, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'delete') {
				if (is_user_granted_permission(PERM_ADMIN_USER_PROFILE)) {
					echo _theme_header(get_opendb_lang_var('delete_user'));
					echo ("<h2>" . get_opendb_lang_var('delete_user') . "</h2>");
	
					$return_val = handle_user_delete($HTTP_VARS['user_id'], $HTTP_VARS, $errors);
					if ($return_val === '__CONFIRM__') {
						echo get_op_confirm_form($PHP_SELF, get_opendb_lang_var('confirm_user_delete', array('fullname' => fetch_user_name($HTTP_VARS['user_id']), 'user_id' => $HTTP_VARS['user_id'])), $HTTP_VARS);
					} else if ($return_val === '__ABORTED__') {
						echo ("<p class=\"success\">" . get_opendb_lang_var('user_not_deleted') . "</p>");
						$footer_links_r[] = array('url' => "$PHP_SELF?op=edit&user_id=" . $HTTP_VARS['user_id'], 'text' => ($HTTP_VARS['user_id'] == get_opendb_session_var('user_id') ? get_opendb_lang_var('edit_my_info') : get_opendb_lang_var('edit_user_info')));
					} else if ($return_val === TRUE) {
						echo ("<p class=\"success\">" . get_opendb_lang_var('user_deleted') . "</p>");
					} else { //if($return_val === FALSE)
	 					echo format_error_block($errors);
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					opendb_not_authorised_page(PERM_ADMIN_USER_PROFILE, $HTTP_VARS);
				}
			} else if ($HTTP_VARS['op'] == 'signup' && get_opendb_config_var('login.signup', 'enable') !== FALSE) {
				if ($HTTP_VARS['op2'] == 'send_info') {
					$page_title = get_opendb_lang_var('new_account');
					echo (_theme_header($page_title, is_show_login_menu_enabled()));
					echo ("<h2>" . $page_title . "</h2>");

					if (get_opendb_config_var('login.signup', 'disable_captcha') === TRUE || is_secret_image_code_valid($HTTP_VARS['gfx_code_check'], $HTTP_VARS['gfx_random_number'])) {
						$return_val = handle_user_insert($HTTP_VARS, $errors);
						if ($return_val !== FALSE) {
							echo ("\n<p class=\"success\">" . get_opendb_lang_var('new_account_reply', 'site', get_opendb_config_var('site', 'title')) . "</p>");
							if (send_signup_info_to_admin($HTTP_VARS, $errors)) {
								echo ("\n<p class=\"smsuccess\">" . get_opendb_lang_var('new_account_admin_email_sent', 'site', get_opendb_config_var('site', 'title')) . "</p>");
							} else {
								echo (format_error_block($errors));
							}
						} else { // $return_val === FALSE
 							echo (format_error_block($errors));
							echo (get_user_input_form(NULL, $HTTP_VARS));
						}
					} else { //is_secretimage_code_valid
 						echo (format_error_block(get_opendb_lang_var('invalid_verify_code')));
						echo (get_user_input_form(NULL, $HTTP_VARS));
					}
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				} else {
					$page_title = get_opendb_lang_var('new_account');
					echo (_theme_header($page_title, is_show_login_menu_enabled()));
					echo ("\n<h2>" . $page_title . "</h2>");
					echo (get_user_input_form(NULL, $HTTP_VARS));
					echo format_footer_links($footer_links_r);
					echo _theme_footer();
				}
			} else { //End of $HTTP_VARS['op'] checks
 				opendb_operation_not_available();
			}
		}
	} else {
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
} else { //if(is_site_enabled())
	opendb_site_disabled();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
