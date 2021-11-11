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
include_once("./lib/http.php");
include_once("./lib/utils.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/item.php");
include_once("./lib/datetime.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item_type.php");
include_once("./lib/widgets.php");
include_once("./lib/review.php");
include_once("./lib/listutils.php");
include_once("./lib/status_type.php");
include_once("./lib/HTML_Listing.class.php");
include_once("./lib/TitleMask.class.php");

/**
 * @param $borrowed_item_rs Items that this action will be performed against.  It may actually
 * 						be an array of 'sequence_number' values, in which case the borrow
 * 						record for the sequence_number will be fetched.
 * @param $HTTP_VARS
 * */
function more_information_form($op, $borrowed_item_rs, $HTTP_VARS, $email_notification = TRUE) {
	global $PHP_SELF;

	$duration_attr_type = NULL;
	$default_borrow_duration = NULL;

	echo ("\n<form action=\"$PHP_SELF\" method=\"POST\">");
	// In case no detail is required.
	echo ("\n<input type=\"hidden\" name=\"more_info_requested\" value=\"true\">");

	// Pass all http variables onto next instance...
	// Includes empty fields...
	echo get_url_fields($HTTP_VARS, NULL, NULL);

	// Display the items to be operated on.
	if (is_not_empty_array($borrowed_item_rs)) {
		echo ("<div id=\"moreInfoListing\">");

		// no pagination.
		$HTTP_VARS['items_per_page'] = '';

		$listingObject = new HTML_Listing($PHP_SELF, $HTTP_VARS);

		$listingObject->setIncludeHrefLinks(TRUE);
		$listingObject->setIncludeFooter(FALSE);

		$listingObject->startListing();

		$listingObject->addHeaderColumn(get_opendb_lang_var('type'));
		$listingObject->addHeaderColumn(get_opendb_lang_var('title'));

		if ($op == 'reserve' || $op == 'cancel_reserve' || $op == 'quick_check_out') {
			$listingObject->addHeaderColumn(get_opendb_lang_var('owner'));
		} else if ($op == 'check_in') {
			$listingObject->addHeaderColumn(get_opendb_lang_var('borrower'));
		}

		if (get_opendb_config_var('borrow', 'duration_support')) {
			if ($op == 'check_out' || $op == 'quick_check_out') {
				$listingObject->addHeaderColumn(get_opendb_lang_var('borrow_duration'));
			}
		}

		//initialise
		$max_overdue_duration = NULL;
		$default_borrow_duration = NULL;

		reset($borrowed_item_rs);
		foreach ($borrowed_item_rs as $borrowed_item_r) {
			$listingObject->startRow();

			// If only a sequence_number, we need to fetch the borrow record.
			if (!is_array($borrowed_item_r) && is_numeric($borrowed_item_r)) {
				$borrowed_item_r = fetch_borrowed_item_r($borrowed_item_r);
			}

			// TODO - add borrowed item history this to be displayed as readonly.
			//$results = fetch_borrowed_item_hist_rs($borrowed_item_r['sequence_number']);

			$item_r = fetch_item_instance_r($borrowed_item_r['item_id'], $borrowed_item_r['instance_no']);

			$listingObject->addItemTypeImageColumn($item_r['s_item_type']);
			$listingObject->addTitleColumn($item_r);

			if ($op == 'reserve' || $op == 'cancel_reserve' || $op == 'quick_check_out') {
				$listingObject->addUserNameColumn($item_r['owner_id'], array('bi_sequence_number' => $borrowed_item_r['sequence_number']));
			} else if ($op == 'check_in') {
				$listingObject->addUserNameColumn($borrowed_item_r['borrower_id'], array('bi_sequence_number' => $borrowed_item_r['sequence_number']));
			}

			if (get_opendb_config_var('borrow', 'duration_support')) {
				if ($op == 'check_out' || $op == 'quick_check_out') {
					if (is_numeric($item_r['borrow_duration'])) {
						// todo - change
						$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r($item_r['s_item_type'], 'DURATION');
						$listingObject->addDisplayColumn($duration_attr_type_r['s_attribute_type'], NULL, // prompt
								$duration_attr_type_r['display_type'], $item_r['borrow_duration']);
					} else {
						$listingObject->addColumn(get_opendb_lang_var('undefined'));
					}
				}
			}

			// While we are here, we are going to calculate the default duration value.
			// We want to choose the least duration value.  If any of the items use a
			// different s_attribute_type for duration, then we should not try to get
			// a default value.  This is indicated by the $duration_attr_type===FALSE
			// if we have encountered a difference.
			if (get_opendb_config_var('borrow', 'duration_support') && $duration_attr_type !== FALSE) {
				$new_duration_attr_type = fetch_sfieldtype_item_attribute_type($item_r['s_item_type'], 'DURATION');
				if ($duration_attr_type == NULL)
					$duration_attr_type = $new_duration_attr_type;
				else if ($duration_attr_type !== $new_duration_attr_type) {
					// Different s_attribute_type's for DURATION, so cannot display Duration chooser.
					$duration_attr_type = FALSE;
				}

				if ($duration_attr_type !== FALSE) {
					if ($op == 'check_out' || $op == 'quick_check_out') {
						// The default borrow duration should be the least amount of days or undefined
						// if no records have a borrow duration.
						if ($default_borrow_duration === NULL) {
							$default_borrow_duration = $item_r['borrow_duration'];
						} else if ($default_borrow_duration !== '') { // Undefined empty value.
 							if (is_numeric($default_borrow_duration) && is_numeric($item_r['borrow_duration']) && $item_r['borrow_duration'] < $default_borrow_duration) {
								$default_borrow_duration = $item_r['borrow_duration'];
							}
						}
					} else if ($op == 'extension') {
						if ($borrowed_item_r['total_duration'] > $borrowed_item_r['borrow_duration']) {
							$tmp_overdue_duration = $borrowed_item_r['total_duration'] - $borrowed_item_r['borrow_duration'];
						}

						// We want to get the max overdue duration, so we can give the User granting the extension a
						// default, that will bring all selected items back into non-overdue status.
						if (!is_numeric($max_overdue_duration) || $max_overdue_duration < $tmp_overdue_duration) {
							$max_overdue_duration = $tmp_overdue_duration;
						}
					}
				}
			}//if(get_opendb_config_var('borrow', 'duration_support') && $duration_attr_type!==FALSE)

			$listingObject->endRow();
		}

		$listingObject->endListing();

		echo ("</div>");
	}

	echo ("<table class=\"moreInfo\">");

	// Do not display this more information section unless email is enabled.
	if (is_valid_opendb_mailer() && $email_notification !== FALSE) {
		echo get_input_field("more_information", NULL, //s_attribute_type
		get_opendb_lang_var('more_information'), "textarea(50,10)", "N", //compulsory
		NULL, TRUE);
	}

	// Include a Borrower ID select, minus the current user.
	if ($op == 'quick_check_out') {
		if (strlen($HTTP_VARS['borrower_id']) == 0 || !is_user_granted_permission(PERM_USER_BORROWER, $HTTP_VARS['borrower_id'])) {
			$current_user_mode = EXCLUDE_CURRENT_USER;
			if (get_opendb_config_var('borrow', 'owner_self_checkout') !== FALSE) {
				$current_user_mode = INCLUDE_CURRENT_USER;
			}

			$results = fetch_user_rs(PERM_USER_BORROWER, INCLUDE_ROLE_PERMISSIONS, $current_user_mode, EXCLUDE_DEACTIVATED_USER, 'fullname', 'ASC');
			if ($results) {
				echo (format_field(get_opendb_lang_var('borrower'), custom_select('borrower_id', $results, '%fullname% (%user_id%)', 1, NULL, 'user_id')));
			} else {
				echo (format_field(get_opendb_lang_var('borrower'), get_opendb_lang_var('no_records_found')));
			}
		}
	}

	//Only for check_out/quick_check_out operations - makes no sense otherwise!
	if (get_opendb_config_var('borrow', 'duration_support') !== FALSE && ($op == 'check_out' || $op == 'quick_check_out' || $op == 'extension')) {
		// Display default borrow duration.
		if (strlen($duration_attr_type) > 0) {
			$duration_attr_type_r = fetch_attribute_type_r($duration_attr_type);

			// We have to find the matching DURATION lookup value, which is at least
			// as many days as the max_overdue value, or the highest possible
			// duration value, if none found as large as the $max_overdue_duration
			if ($op == 'extension') {
				$default_borrow_duration = NULL;
				$result = fetch_attribute_type_lookup_rs($duration_attr_type_r['s_attribute_type'], 'order_no, value ASC');
				if ($result) {
					while ($lookup_r = db_fetch_assoc($result)) {
						if (is_numeric($lookup_r['value']) && (!is_numeric($max_overdue_duration) || (is_numeric($max_overdue_duration) && $max_overdue_duration <= $lookup_r['value']))) {
							$default_borrow_duration = $lookup_r['value'];
							break;
						}

						// backup, in case we need to use outside while loop
						$lookup_r2 = $lookup_r;
					}
					db_free_result($result);

					// If still null, then set to the largest option
					if ($default_borrow_duration == NULL) {
						$default_borrow_duration = $lookup_r2['value'];
					}
				}
			}

			if ($op != 'extension' && strlen(get_opendb_lang_var('default_borrow_duration')) > 0 && is_array($borrowed_item_rs))
				$duration_attr_type_r['prompt'] = get_opendb_lang_var('default_borrow_duration');

			$duration_attr_type_r['compulsory_ind'] = 'N';

			echo (get_item_input_field("default_borrow_duration", $duration_attr_type_r, NULL, //$item_r
			($op != 'quick_check_out') ? $default_borrow_duration : NULL));

			// Not appropriate for extension operation
			if ($op == 'check_out' || $op == 'quick_check_out') {
				echo (get_input_field("override_item_duration", NULL, get_opendb_lang_var('override_item_duration'), "simple_checkbox(" . ($default_borrow_duration === NULL ? "CHECKED" : "") . ")", "N", "Y", TRUE));
			}
		} else { //otherwise tell checkout to use item_instance borrow duration instead.
 			if ($op == 'check_out' || $op == 'quick_check_out') {
				echo ("\n<input type=\"hidden\" name=\"override_item_duration\" value=\"N\">");
			}
		}
	}

	echo ("</table>");

	echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">");
	echo ("</form>");

	echo format_help_block(get_opendb_lang_var('more_information_help'));
}

function display_html_success_borrow_results($section_intro, $results_rs) {
	echo ("<p class=\"success\">$section_intro</p>");
	echo ("<dl class=\"userItemList\">");

	reset($results_rs);
	foreach ($results_rs as $results_r) {
		echo ("<dt>" . $results_r['user_name'] . "</dt>");
		echo ("<dd>");

		echo ("<ul>");
		foreach ($results_r['display_titles'] as $display_title) {
			echo ("<li>");
			echo ($display_title);
			echo ("</li>");
		}
		echo ("</ul></dd>");

		if (isset($results_r['email_result'])) {
			echo ("<dd>");
			if ($results_r['email_result'] !== TRUE) {
				echo ("<span class=\"error\">" . get_opendb_lang_var('notication_email_not_sent') . "</span>");
				if (is_not_empty_array($results_r['email_errors'])) {
					echo (format_error_block($results_r['email_errors']));
				}
			} else {
				echo ("<span class=\"success\">" . get_opendb_lang_var('notication_email_sent') . "</span>");
			}
			echo ("</dd>");
		}
	}
	echo ("</dl>");
}

function display_html_failure_borrow_results($section_intro, $results_rs) {
	echo ("<p class=\"error\">$section_intro</p>");
	echo ("<ul class=\"failureItems\">");
	reset($results_rs);
	foreach ($results_rs as $results_r) {
		echo ("<li>");
		echo ($results_r['display_title']);
		if (is_not_empty_array($results_r['errors'])) {
			echo (format_error_block($results_r['errors']));
		}
		echo ("</li>");
	}
	echo ("</ul>");
}

function display_job_success_borrow_results($section_intro, $results_rs) {
	echo ("\n$section_intro\n");

	reset($results_rs);
	foreach ($results_rs as $results_r) {
		echo ("\n\t" . $results_r['user_name']);

		foreach ($results_r['display_titles'] as $display_title) {
			echo ("\n\t\t * $display_title");
		}

		if (isset($results_r['email_result'])) {
			echo ("\n\t\t");
			if ($results_r['email_result'] !== TRUE) {
				echo (get_opendb_lang_var('notication_email_not_sent'));
				if (is_not_empty_array($results_r['email_errors'])) {
					foreach ($results_r['email_errors'] as $error) {
						echo ("\n\t\t$error");
					}
				}
			} else {
				echo (get_opendb_lang_var('notication_email_sent'));
			}
		}
		echo ("\n\n");
	}
}

function display_job_failure_borrow_results($section_intro, $results_rs) {
	echo ("\n$section_intro\n");

	reset($results_rs);
	foreach ($results_rs as $results_r) {
		echo ("\n\t* ");
		echo ($results_r['display_title']);
		if (is_not_empty_array($results_r['errors'])) {
			foreach ($results_r['errors'] as $error) {
				echo ("\n\t$error");
			}
		}
		echo ("\n");
	}
	echo ("\n");
}

/**
 */
function process_borrow_results($op, $mode, $heading, $success_intro, $failure_intro, $more_information, $success_item_rs, $failure_item_rs, $email_notification = TRUE) {
	$titleMaskCfg = new TitleMask(array('item_borrow', 'item_display'));

	if (is_not_empty_array($success_item_rs)) {
		// Sort the items by user, so we can send emails for multiple 
		// items, instead of individually.
		$borrowed_item_user_r = array();
		foreach ($success_item_rs as $borrowed_item_r) {
			$item_r = fetch_item_instance_r($borrowed_item_r['item_id'], $borrowed_item_r['instance_no']);
			$item_r['title'] = $titleMaskCfg->expand_item_title($item_r);

			$item_entry_r['display_title'] = get_opendb_lang_var('borrow_item_title_listing', array('display_title' => $item_r['title'], 'item_id' => $item_r['item_id'], 'instance_no' => $item_r['instance_no']));

			// A array of item_entries.
			//$item_entry_r['item'] = $item_r;
			$item_entry_r['detail'] = get_borrow_details($op, $item_r, $borrowed_item_r);

			// When reserving or cancelling and the current user is the borrower, we want to
			// send the email to the owner, in all other cases the email should go to the
			// borrower.
			if (($op == 'reserve' || $op == 'cancel_reserve') && get_opendb_session_var('user_id') == $borrowed_item_r['borrower_id']) {
				$to_user = $item_r['owner_id'];
			} else {
				$to_user = $borrowed_item_r['borrower_id'];
			}

			// Now add an entry to this user array.
			$borrowed_item_user_r[$to_user][] = $item_entry_r;
		}

		$success_results = array();
		foreach ($borrowed_item_user_r as $to_user => $item_entry_rs) {
			$errors = NULL;

			if (is_valid_opendb_mailer() && $email_notification !== FALSE) {
				// How can the from user be anything but the currently logged in user!
				$email_result = send_notification_email($to_user, get_opendb_session_var('user_id'), $heading, $success_intro, $more_information, $item_entry_rs, $errors);
			}

			$display_title_r = NULL;
			reset($item_entry_rs);
			foreach ($item_entry_rs as $item_entry_r) {
				$display_title_r[] = $item_entry_r['display_title'];
			}

			$user_name = get_opendb_lang_var('user_name', array('fullname' => fetch_user_name($to_user), 'user_id' => $to_user));

			$success_results_rs[] = array('user_name' => $user_name, 'display_titles' => $display_title_r, 'email_result' => $email_result, 'email_errors' => $errors);
		}

		if (is_not_empty_array($success_results_rs)) {
			if ($mode == 'job')
				display_job_success_borrow_results($success_intro, $success_results_rs);
			else
				display_html_success_borrow_results($success_intro, $success_results_rs);
		}
	}

	if (is_not_empty_array($failure_item_rs)) {
		$failure_results = array();
		foreach ($failure_item_rs as $borrowed_item_r) {
			$item_r = fetch_item_instance_r($borrowed_item_r['item_id'], $borrowed_item_r['instance_no']);

			// Expand title mask.
			$item_r['title'] = $titleMaskCfg->expand_item_title($item_r);

			$display_title = get_opendb_lang_var('borrow_item_title_listing', array('display_title' => $item_r['title'], 'item_id' => $item_r['item_id'], 'instance_no' => $item_r['instance_no']));

			// Now display any errors if present.
			if (strlen($borrowed_item_r['errors']) > 0) {
				$borrow_error_details = get_opendb_lang_var('borrow_error_detail', 'error', $borrowed_item_r['errors']);
			}

			$failure_results[] = array('display_title' => $display_title, 'errors' => array($borrow_error_details));
		}

		if (is_not_empty_array($failure_results)) {
			if ($mode == 'job')
				display_job_failure_borrow_results($failure_intro, $failure_results);
			else
				display_html_failure_borrow_results($failure_intro, $failure_results);
		}
	}
}

/**
    This will format a status line for the borrowed_item_r passed as parameter.
 */
function get_borrow_details($op, $item_r, $borrowed_item_r) {
	if ($op == 'check_out' || $op == 'quick_check_out' || $op == 'reminder' || $op == 'admin_send_reminders' || $op == 'extension') {
		if ($borrowed_item_r['due_date'] > 0 && is_numeric($borrowed_item_r['borrow_duration']) && $borrowed_item_r['borrow_duration'] > 0) {
			$details_r[] = get_opendb_lang_var('due_duration_detail', 'borrow_duration', $borrowed_item_r['borrow_duration']);
			$details_r[] = get_opendb_lang_var('due_date_detail', 'date', get_localised_timestamp(get_opendb_config_var('borrow', 'date_mask'), $borrowed_item_r['due_date']));
		}
	}

	if ($op == 'check_in' || $op == 'reminder' || $op == 'admin_send_reminders') {
		if ($borrowed_item_r['total_duration'] > 0 && is_numeric($borrowed_item_r['borrow_duration']) && $borrowed_item_r['borrow_duration'] > 0) {
			$details_r[] = get_opendb_lang_var('total_duration_detail', 'total_duration', $borrowed_item_r['total_duration']);

			if ($borrowed_item_r['total_duration'] > $borrowed_item_r['borrow_duration']) {
				$overdue_duration = $borrowed_item_r['total_duration'] - $borrowed_item_r['borrow_duration'];
				$details_r[] = get_opendb_lang_var('overdue_duration_detail', 'overdue_duration', $overdue_duration);
			}
		}
	}

	return $details_r;
}

/**
    The $borrow_item_r array format:
        $item_r
        $details
 */
function send_notification_email($to_user, $from_user, $heading, $introduction, $more_information, $item_entry_rs, &$errors) {
	// Format the entire message.
	$message = get_opendb_lang_var('to_user_email_intro', 'fullname', fetch_user_name($to_user)) . "\n\n" . $introduction;

	foreach ($item_entry_rs as $item_entry_r) {
		$message .= "\n*    " . $item_entry_r['display_title'];

		// Add any item Borrow (overdue,due,reminder,etc) details here.
		foreach ($item_entry_r['detail'] as $detail) {
			$message .= "\n     - " . $detail;
		}
	}

	if (strlen($more_information) > 0) {
		$message .= "\n\n\n" . $more_information . "\n";
	}

	// Send the mail!
	return opendb_user_email($to_user, $from_user, $heading, $message, $errors);
}

function add_errors_to_borrowed_item_r($borrowed_item_r, $errors) {
	$borrowed_item_r['errors'] = $errors;
	return $borrowed_item_r;
}

if (is_site_enabled()) {
	if (is_opendb_valid_session()) {
		if (get_opendb_config_var('borrow', 'enable') !== FALSE) {
			if ($HTTP_VARS['op'] == 'admin_send_reminders' && $HTTP_VARS['mode'] == 'job') {
				@set_time_limit(600);

				header("Content-Type: text/plain");
				echo get_opendb_lang_var('check_in_reminder');

				for ($i = 0; $i < strlen(get_opendb_lang_var('check_in_reminder')); $i++)
					$dashed_line .= "-";

				echo ("\n$dashed_line\n\n");

				if (is_user_granted_permission(PERM_ADMIN_BORROWER)) {
					if (is_valid_opendb_mailer()) {
						$results = fetch_reminder_borrowed_item_rs(get_opendb_config_var('borrow.reminder', 'duration_range'));
						if ($results) {
							while ($borrowed_item_r = db_fetch_assoc($results)) {
								if (handle_reminder($borrowed_item_r['sequence_number'], $errors))
									$success_items_rs[] = $borrowed_item_r;
								else {
									$borrowed_item_r['errors'] = $errors;
									$failure_items_rs[] = $borrowed_item_r;
								}
							}
							db_free_result($results);

							process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('check_in_reminder'), get_opendb_lang_var('check_in_reminder_for_items'), get_opendb_lang_var('check_in_reminder_not_for_items'), NULL, //$HTTP_VARS['more_information'],
							$success_items_rs, $failure_items_rs, TRUE);
						} else {
							echo get_opendb_lang_var('no_records_found');
						}
					} else {
						echo get_opendb_lang_var('operation_not_available');
					}
				} else {
					echo get_opendb_lang_var('not_authorized_to_page');
				}
			} else if (is_user_granted_permission(array(PERM_ADMIN_BORROWER, PERM_USER_BORROWER))) {
				$errors = NULL;

				if ($HTTP_VARS['op'] == 'reserve_all' || $HTTP_VARS['op'] == 'reserve') {
					echo _theme_header(get_opendb_lang_var('reserve_item(s)'));
					echo ("<h2>" . get_opendb_lang_var('reserve_item(s)') . "</h2>");

					if ($HTTP_VARS['op'] == 'reserve_all') {
						$results = fetch_borrowed_item_pk_rs(get_opendb_session_var('user_id'), 'T');
						if ($results) {
							while ($borrowed_item_r = db_fetch_assoc($results)) {
								$HTTP_VARS['sequence_number'][] = $borrowed_item_r['sequence_number'];
								$reserve_item_rs[] = $borrowed_item_r;
							}
							db_free_result($results);
						}

						$HTTP_VARS['op'] = 'reserve';
					} else if (is_not_empty_array($HTTP_VARS['sequence_number'])) { // from reserve basket
 						reset($HTTP_VARS['sequence_number']);
						foreach ($HTTP_VARS['sequence_number'] as $sequence_number) {
							$reserve_item_rs[] = fetch_borrowed_item_pk_r($sequence_number);
						}
					} else if (is_not_empty_array($HTTP_VARS['item_id_instance_no'])) { // direct from listings
 						// Format of $item_id_instance_no {item_id}_{instance_no}
						foreach ($HTTP_VARS['item_id_instance_no'] as $item_id_instance_no) {
							$item_id_instance_no_r = get_item_id_and_instance_no($item_id_instance_no);
							if (is_not_empty_array($item_id_instance_no_r))
								$reserve_item_rs[] = $item_id_instance_no_r;
						}
					} else if (is_numeric($HTTP_VARS['item_id']) && is_numeric($HTTP_VARS['instance_no'])) {
						if (is_exists_item_instance($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'])) {
							$reserve_item_rs[] = array('item_id' => $HTTP_VARS['item_id'], 'instance_no' => $HTTP_VARS['instance_no']);
						}
					}

					if (is_array($reserve_item_rs)) {
						// There is no point in providing a More Information form, unless we either have use of php email,
						// or we are in checkout mode.
						if (get_opendb_config_var('borrow', 'reserve_more_information') && $HTTP_VARS['more_info_requested'] != 'true') {
							more_information_form('reserve', $reserve_item_rs, $HTTP_VARS, get_opendb_config_var('borrow', 'reserve_email_notification'));
						} else {
							foreach ($reserve_item_rs as $reserve_item_r) {
								// In case someone is trying to pass invalid item_id/instance_no combo's
								if (is_exists_item_instance($reserve_item_r['item_id'], $reserve_item_r['instance_no'])) {
									if (($new_borrowed_item_id = handle_reserve($reserve_item_r['item_id'], $reserve_item_r['instance_no'], get_opendb_session_var('user_id'), $HTTP_VARS['more_information'], $errors)) !== FALSE)// This allows reserve to support calls from borrow.php, item_display.php or listings.php
										$success_items_rs[] = array('item_id' => $reserve_item_r['item_id'], 'instance_no' => $reserve_item_r['instance_no'], 'borrower_id' => get_opendb_session_var('user_id'));
									else
										$failure_items_rs[] = array('item_id' => $reserve_item_r['item_id'], 'instance_no' => $reserve_item_r['instance_no'], 'borrower_id' => get_opendb_session_var('user_id'), 'errors' => $errors);
								}
							}

							process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('reserve_item(s)'), get_opendb_lang_var('items_have_been_reserved'), get_opendb_lang_var('items_have_not_been_reserved'), $HTTP_VARS['more_information'], $success_items_rs,
									$failure_items_rs, get_opendb_config_var('borrow', 'reserve_email_notification'));
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
					}
				} else if ($HTTP_VARS['op'] == 'cancel_reserve') {
					echo _theme_header(get_opendb_lang_var('item_cancel_reservation'));
					echo ("<h2>" . get_opendb_lang_var('item_cancel_reservation') . "</h2>");

					// So we can process only sequence_numbers
					if (is_not_empty_array($HTTP_VARS['sequence_number'])) {
						$sequence_number_r = $HTTP_VARS['sequence_number'];
					} else if (is_numeric($HTTP_VARS['sequence_number'])) {
						$sequence_number_r[] = $HTTP_VARS['sequence_number']; //convert to array here.
					} else if (is_numeric($HTTP_VARS['item_id']) && is_numeric($HTTP_VARS['instance_no'])) { //In this case the $borrower_id has to be the current user, no one else can cancel using this function!
						$sequence_number = fetch_borrowed_item_seq_no($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'], 'R', get_opendb_session_var('user_id'));
						if ($sequence_number !== FALSE) {
							$sequence_number_r[] = $sequence_number;
						}
					}

					if (is_array($sequence_number_r)) {
						// There is no point in providing a More Information form, unless we either have use of php email,
						// or we are in checkout mode.
						if (get_opendb_config_var('borrow', 'cancel_more_information') && $HTTP_VARS['more_info_requested'] != 'true') {
							more_information_form('cancel_reserve', $sequence_number_r, $HTTP_VARS, get_opendb_config_var('borrow', 'cancel_email_notification'));
						} else {
							foreach ($sequence_number_r as $sequence_number) {
								// This allows cancel-reserve to support calls from borrow.php, item_display.php or listings.php
								if (handle_cancelreserve($sequence_number, $HTTP_VARS['more_information'], $errors))
									$success_items_rs[] = fetch_borrowed_item_r($sequence_number);
								else
									$failure_items_rs[] = add_errors_to_borrowed_item_r(fetch_borrowed_item_pk_r($sequence_number), $errors);
							}

							process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('item_cancel_reservation'), get_opendb_lang_var('reserve_items_have_been_cancelled'), get_opendb_lang_var('reserve_items_have_not_been_cancelled'), $HTTP_VARS['more_information'],
									$success_items_rs, $failure_items_rs, get_opendb_config_var('borrow', 'cancel_email_notification'));
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
					}
				} else if ($HTTP_VARS['op'] == 'check_out') {
					echo _theme_header(get_opendb_lang_var('check_out_item(s)'));
					echo ("<h2>" . get_opendb_lang_var('check_out_item(s)') . "</h2>");

					// It is easier to assume an array in all cases.	
					if (is_not_empty_array($HTTP_VARS['sequence_number'])) {
						$sequence_number_r = $HTTP_VARS['sequence_number'];
					} else if (is_numeric($HTTP_VARS['sequence_number'])) {
						$sequence_number_r[] = $HTTP_VARS['sequence_number']; //convert to array here.
					}

					if (is_array($sequence_number_r)) {
						// There is no point in providing a More Information form, unless we either have use of php email,
						// or we are in checkout mode.
						if (get_opendb_config_var('borrow', 'checkout_more_information') && $HTTP_VARS['more_info_requested'] != 'true') {
							more_information_form('check_out', $sequence_number_r, $HTTP_VARS, get_opendb_config_var('borrow', 'checkout_email_notification'));
						} else {
							foreach ($sequence_number_r as $sequence_number) {
								$borrow_duration = NULL;

								// The More Information form was not presented
								// So we need to get the default duration from the item table.
								if ($HTTP_VARS['more_info_requested'] != 'true') {
									$borrowed_item_r = fetch_borrowed_item_pk_r($sequence_number);
									if (is_not_empty_array($borrowed_item_r)) {
										$item_r = fetch_item_instance_r($borrowed_item_r['item_id'], $borrowed_item_r['instance_no']);
										if (is_not_empty_array($item_r)) {
											$borrow_duration = $item_r['borrow_duration'];
										}
									}
								} else { // else more information form presented, so we have to factor in overriding borrow duration.
 									// In this case a duration of '' is supported
									if ($HTTP_VARS['override_item_duration'] != 'Y') {
										$borrowed_item_r = fetch_borrowed_item_pk_r($sequence_number);
										if (is_not_empty_array($borrowed_item_r)) {
											$item_r = fetch_item_instance_r($borrowed_item_r['item_id'], $borrowed_item_r['instance_no']);
											if (is_not_empty_array($item_r)) {
												$borrow_duration = $item_r['borrow_duration'];
											}
										}
										$borrow_duration = $item_r['borrow_duration'];
									}

									if (!is_numeric($borrow_duration)) {
										$borrow_duration = $HTTP_VARS['default_borrow_duration'];
									}
								}

								if (handle_checkout($sequence_number, $borrow_duration, $HTTP_VARS['more_information'], $errors))
									$success_items_rs[] = fetch_borrowed_item_r($sequence_number, TRUE);
								else
									$failure_items_rs[] = add_errors_to_borrowed_item_r(fetch_borrowed_item_pk_r($sequence_number), $errors);
							}

							process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('check_out_item(s)'), get_opendb_lang_var('items_have_been_checked_out'), get_opendb_lang_var('items_have_not_been_checked_out'), $HTTP_VARS['more_information'], $success_items_rs,
									$failure_items_rs, get_opendb_config_var('borrow', 'checkout_email_notification'));
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
					}
				} else if ($HTTP_VARS['op'] == 'quick_check_out') {
					if (strlen($HTTP_VARS['borrower_id']) == 0 || !is_user_granted_permission(PERM_USER_BORROWER, $HTTP_VARS['borrower_id'])) {
						echo _theme_header(get_opendb_lang_var('quick_check_out'));
						echo ("<h2>" . get_opendb_lang_var('quick_check_out') . "</h2>");
					} else {
						$page_title = get_opendb_lang_var('quick_check_out_for_fullname', array('user_id' => $HTTP_VARS['borrower_id'], 'fullname' => fetch_user_name($HTTP_VARS['borrower_id'])));
						echo _theme_header($page_title);
						echo ("<h2>" . $page_title . "</h2>");
					}

					if (is_not_empty_array($HTTP_VARS['checkout_item_instance_rs'])) {
						foreach ($HTTP_VARS['checkout_item_instance_rs'] as $item_id_and_instance_no) {
							if (strlen($item_id_and_instance_no) > 0) {
								$item_id_and_instance_no_r = get_item_id_and_instance_no($item_id_and_instance_no);
								if (is_not_empty_array($item_id_and_instance_no_r)) {
									$checkout_item_r = fetch_item_instance_r($item_id_and_instance_no_r['item_id'], $item_id_and_instance_no_r['instance_no']);
									if (is_array($checkout_item_r)) {
										$checkout_item_rs[] = $checkout_item_r;
									}
								}
							}
						}
					} else if (is_numeric($HTTP_VARS['item_id']) && is_numeric($HTTP_VARS['instance_no'])) {
						if (is_exists_item_instance($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'])) {
							$checkout_item_rs[] = fetch_item_instance_r($HTTP_VARS['item_id'], $HTTP_VARS['instance_no']);
							;
						}
					}

					if (is_array($checkout_item_rs)) {
						// In the case of quick check, we always want a more information form, because this is
						// where the 'borrower_id' is selected.
						if ($HTTP_VARS['more_info_requested'] != 'true') {
							more_information_form('quick_check_out', $checkout_item_rs, $HTTP_VARS, get_opendb_config_var('borrow', 'quick_checkout_email_notification'));
						} else {
							foreach ($checkout_item_rs as $checkout_item_r) {
								// In case someone is trying to pass invalid item_id/instance_no combo's
								if (is_exists_item_instance($checkout_item_r['item_id'], $checkout_item_r['instance_no'])) {
									$borrow_duration = NULL;
									if ($HTTP_VARS['override_item_duration'] != 'Y' && is_numeric($checkout_item_r['borrow_duration']))
										$borrow_duration = $checkout_item_r['borrow_duration'];
									else
										$borrow_duration = $HTTP_VARS['default_borrow_duration'];

									// This allows reserve to support calls from borrow.php, item_display.php or listings.php
									$new_borrowed_item_id = handle_quick_checkout($checkout_item_r['item_id'], $checkout_item_r['instance_no'], $HTTP_VARS['borrower_id'], $borrow_duration, $HTTP_VARS['more_information'], $errors);
									if ($new_borrowed_item_id !== FALSE)
										$success_items_rs[] = fetch_borrowed_item_r($new_borrowed_item_id, TRUE);
									else
										$failure_items_rs[] = array('item_id' => $checkout_item_r['item_id'], 'instance_no' => $checkout_item_r['instance_no'], 'borrower_id' => $HTTP_VARS['borrower_id'], 'errors' => $errors);
								}
							}

							process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('quick_check_out'), get_opendb_lang_var('items_have_been_checked_out'), get_opendb_lang_var('items_have_not_been_checked_out'), $HTTP_VARS['more_information'], $success_items_rs,
									$failure_items_rs, get_opendb_config_var('borrow', 'quick_checkout_email_notification'));
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('no_items_found') . "</p>");
					}
				} else if ($HTTP_VARS['op'] == 'check_in') {
					echo _theme_header(get_opendb_lang_var('check_in_item(s)'));
					echo ("<h2>" . get_opendb_lang_var('check_in_item(s)') . "</h2>");

					// It is easier to assume an array in all cases.	
					if (is_not_empty_array($HTTP_VARS['sequence_number'])) {
						$sequence_number_r = $HTTP_VARS['sequence_number'];
					} else if (is_numeric($HTTP_VARS['sequence_number'])) { //is_numeric
 						$sequence_number_r[] = $HTTP_VARS['sequence_number']; //convert to array here.
					} else if (is_numeric($HTTP_VARS['item_id']) && is_numeric($HTTP_VARS['instance_no'])) {
						if (is_exists_item_instance($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'])) {
							// there should only be one instance of a borrowed item for this item
							$sequence_number = fetch_borrowed_item_seq_no($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'], 'B');
							if ($sequence_number !== FALSE) {
								$sequence_number_r[] = $sequence_number;
							}
						}
					}

					if (is_array($sequence_number_r)) {
						// There is no point in providing a More Information form, unless we either have use of php email,
						// or we are in checkout mode.
						if (get_opendb_config_var('borrow', 'checkin_more_information') && $HTTP_VARS['more_info_requested'] != 'true') {
							more_information_form('check_in', $sequence_number_r, $HTTP_VARS, get_opendb_config_var('borrow', 'checkin_email_notification'));
						} else {
							foreach ($sequence_number_r as $sequence_number) {
								if (handle_checkin($sequence_number, $HTTP_VARS['more_information'], $errors))
									$success_items_rs[] = fetch_borrowed_item_r($sequence_number);
								else
									$failure_items_rs[] = add_errors_to_borrowed_item_r(fetch_borrowed_item_pk_r($sequence_number), $errors);
							}

							process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('check_in_item(s)'), get_opendb_lang_var('items_have_been_checked_in'), get_opendb_lang_var('items_have_not_been_checked_in'), $HTTP_VARS['more_information'], $success_items_rs,
									$failure_items_rs, get_opendb_config_var('borrow', 'checkin_email_notification'));
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
					}
				} else if ($HTTP_VARS['op'] == 'reminder') {
					echo _theme_header(get_opendb_lang_var('check_in_reminder'));
					echo ("<h2>" . get_opendb_lang_var('check_in_reminder') . "</h2>");

					if (is_valid_opendb_mailer()) {
						// It is easier to assume an array in all cases.	
						if (is_not_empty_array($HTTP_VARS['sequence_number'])) {
							$sequence_number_r = $HTTP_VARS['sequence_number'];
						} else if (is_numeric($HTTP_VARS['sequence_number'])) { //is_numeric
 							$sequence_number_r[] = $HTTP_VARS['sequence_number']; //convert to array here.
						}

						if (is_array($sequence_number_r)) {
							// If we are providing for custom email to go along with the message then we need to take care of it here.
							if (get_opendb_config_var('borrow', 'reminder_more_information') && $HTTP_VARS['more_info_requested'] != 'true') {
								more_information_form('reminder', $sequence_number_r, $HTTP_VARS, TRUE);
							} else {
								foreach ($sequence_number_r as $sequence_number) {
									if (handle_reminder($sequence_number, $errors))
										$success_items_rs[] = fetch_borrowed_item_r($sequence_number, TRUE);
									else
										$failure_items_rs[] = add_errors_to_borrowed_item_r(fetch_borrowed_item_pk_r($sequence_number), $errors);
								}

								process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('check_in_reminder'), get_opendb_lang_var('check_in_reminder_for_items'), get_opendb_lang_var('check_in_reminder_not_for_items'), $HTTP_VARS['more_information'], $success_items_rs,
										$failure_items_rs, TRUE);
							}
						} else {
							echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</p>");
					}
				} else if ($HTTP_VARS['op'] == 'extension') {
					echo _theme_header(get_opendb_lang_var('borrow_duration_extension(s)'));
					echo ("<h2>" . get_opendb_lang_var('borrow_duration_extension(s)') . "</h2>");

					if (get_opendb_config_var('borrow', 'duration_support') !== FALSE) {
						// It is easier to assume an array in all cases.	
						if (is_not_empty_array($HTTP_VARS['sequence_number'])) {
							$sequence_number_r = $HTTP_VARS['sequence_number'];
						} else if (is_numeric($HTTP_VARS['sequence_number'])) { //is_numeric
 							$sequence_number_r[] = $HTTP_VARS['sequence_number']; //convert to array here.
						}

						if (is_array($sequence_number_r)) {
							// If we are providing for custom email to go along with the message then we need to take care of it here.
							if (!is_numeric($HTTP_VARS['default_borrow_duration']) || $HTTP_VARS['more_info_requested'] != 'true') {
								// If more info has already been requested, then the 
								// default_borrow_duration must have not been specified
								if ($HTTP_VARS['more_info_requested'] == 'true') {
									echo format_error_block(get_opendb_lang_var('borrow_duration_extension_must_be_specified'));
								}
								more_information_form('extension', $sequence_number_r, $HTTP_VARS, TRUE);
							} else {
								foreach ($sequence_number_r as $sequence_number) {
									if (handle_extension($sequence_number, $HTTP_VARS['default_borrow_duration'], $HTTP_VARS['more_information'], $errors))
										$success_items_rs[] = fetch_borrowed_item_r($sequence_number, TRUE);
									else
										$failure_items_rs[] = add_errors_to_borrowed_item_r(fetch_borrowed_item_pk_r($sequence_number), $errors);
								}

								process_borrow_results($HTTP_VARS['op'], $HTTP_VARS['mode'], get_opendb_lang_var('borrow_duration_extension(s)'), get_opendb_lang_var('borrow_duration_extension_for_items'), get_opendb_lang_var('borrow_duration_extension_not_for_items'),
										$HTTP_VARS['more_information'], $success_items_rs, $failure_items_rs, TRUE);
							}
						} else {
							echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
						}
					} else {
						echo ("<p class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</p>");
					}
				} else {
					echo _theme_header(get_opendb_lang_var('operation_not_available'));
					echo ("<p class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</p>");
				}

				// Include a link no matter what, because they might have initiated the action by accident.
				if (is_numeric($HTTP_VARS['item_id']) && is_numeric($HTTP_VARS['instance_no'])) {
					$footer_links_r[] = array('url' => "item_display.php?item_id=" . $HTTP_VARS['item_id'] . "&instance_no=" . $HTTP_VARS['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
				}
				if (is_opendb_session_var('listing_url_vars')) {
					$footer_links_r[] = array('url' => "listings.php?" . get_url_string(get_opendb_session_var('listing_url_vars')), 'text' => get_opendb_lang_var('back_to_listing'));
				}

				echo format_footer_links($footer_links_r);
				echo _theme_footer();
			} else {
				opendb_not_authorised_page(array(PERM_ADMIN_BORROWER, PERM_USER_BORROWER));
			}
		} else { //borrow functionality disabled.
 			echo _theme_header(get_opendb_lang_var('borrow_not_supported'));
			echo ("<p class=\"error\">" . get_opendb_lang_var('borrow_not_supported') . "</p>");
			echo _theme_footer();
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
