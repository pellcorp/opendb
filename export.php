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
include_once("./lib/item_type.php");
include_once("./lib/item.php");
include_once("./lib/item_attribute.php");
include_once("./lib/user.php");
include_once("./lib/widgets.php");
include_once("./lib/utils.php");
include_once("./lib/export.php");
include_once("./lib/TitleMask.class.php");

/*
 * Export Type 
 * 
 * @param $item_id - if provided will restrict export to this single item.instance combo
 * @param $instance_no 
 * @param $restrict_status_type_r - an array of status_type's to restrict to.
 */
function export_type_items(&$exportPlugin, $page_title, $s_item_type, $item_id, $instance_no, $owner_id, $restrict_status_type_r = NULL) {

	// the $restrict_status_type_r is ignored for a single item
	if (is_numeric($item_id) && is_numeric($instance_no)) {
		send_header($exportPlugin, $page_title);

		$item_r = fetch_item_instance_r($item_id, $instance_no);
		if ($item_r['owner_id'] == get_opendb_session_var('user_id') || is_user_granted_permission(PERM_VIEW_ITEM_DISPLAY)) {
			send_data(get_export_type_item($exportPlugin, $item_id, $instance_no, $item_r['s_item_type'], $item_r['title'], $owner_id));
		}

		send_footer($exportPlugin);
		return TRUE;
	} else {
		$itemresults = fetch_export_item_rs($s_item_type, $owner_id, $restrict_status_type_r);
		if ($itemresults) {
			send_header($exportPlugin, $page_title);
			while ($item_r = db_fetch_assoc($itemresults)) {
				send_data(get_export_type_item($exportPlugin, $item_r['item_id'], NULL, $item_r['s_item_type'], $item_r['title'], $owner_id, $include_parent_related_item));
			}
			db_free_result($itemresults);

			send_footer($exportPlugin);
			return TRUE;
		}
	}

	//else
	return FALSE;
}

function get_export_type_item(&$exportPlugin, $item_id, $instance_no, $s_item_type, $title, $owner_id) {
	$buffer = '';

	$buffer .= $exportPlugin->start_item($item_id, $s_item_type, $title);

	// export item (non instance level) attributes.
	$buffer .= export_type_item_attributes($exportPlugin, $item_id, NULL, $s_item_type);

	// $instance_no will have been provided if a single item.instance is being exported, otherwise
	// we are exporting all instances for this particular item.
	if (is_numeric($instance_no)) {
		$item_instance_r = fetch_item_instance_r($item_id, $instance_no);
		if (is_not_empty_array($item_instance_r)) {
			$buffer .= $exportPlugin->start_item_instance($item_instance_r['item_id'], $item_instance_r['instance_no'], $item_instance_r['owner_id'], $item_instance_r['borrow_duration'], $item_instance_r['s_status_type'], $item_instance_r['status_comment'], $item_instance_r['update_on']);
			$buffer .= export_type_item_attributes($exportPlugin, $item_id, $item_instance_r['instance_no'], $s_item_type);
			$buffer .= $exportPlugin->end_item_instance();
		}
	} else {
		$iiresults = fetch_item_instance_rs($item_id, $owner_id);
		if ($iiresults) {
			while ($item_instance_r = db_fetch_assoc($iiresults)) {
				$buffer .= $exportPlugin->start_item_instance($item_instance_r['item_id'], $item_instance_r['instance_no'], $item_instance_r['owner_id'], $item_instance_r['borrow_duration'], $item_instance_r['s_status_type'], $item_instance_r['status_comment'], $item_instance_r['update_on']);
				$buffer .= export_type_item_attributes($exportPlugin, $item_id, $item_instance_r['instance_no'], $s_item_type);
				$buffer .= $exportPlugin->end_item_instance();
			}
			db_free_result($iiresults);
		}
	}

	$buffer .= $exportPlugin->end_item();
	return $buffer;
}

function export_type_item_attributes($exportPlugin, $item_id, $instance_no, $s_item_type) {
	$buffer = '';

	$attresults = fetch_item_attribute_type_rs($s_item_type, is_numeric($instance_no) ? 'instance_attribute_ind' : 'item_attribute_ind', FALSE);
	if ($attresults) {
		while ($item_attribute_type_r = db_fetch_assoc($attresults)) {
			// Only attribute specific s_field_type's should be exported as XML.
			if (strlen($item_attribute_type_r['s_field_type']) == 0
					|| ($item_attribute_type_r['s_field_type'] != 'TITLE' && $item_attribute_type_r['s_field_type'] != 'DURATION' && $item_attribute_type_r['s_field_type'] != 'STATUSTYPE' && $item_attribute_type_r['s_field_type'] != 'STATUSCMNT'
							&& $item_attribute_type_r['s_field_type'] != 'ITEM_ID')) {
				$item_attribute_val_r = fetch_attribute_val_r($item_id, $instance_no, $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
				if (is_not_empty_array($item_attribute_val_r)) {
					for ($i = 0; $i < count($item_attribute_val_r); $i++) {
						$buffer .= $exportPlugin->item_attribute($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no'], $item_attribute_val_r[$i]);
					}
				}
			}
		}
		db_free_result($attresults);
	}

	return $buffer;
}

/**
 * Row Export
 */
function export_row_items(&$exportPlugin, $page_title, $include_header, $export_columns, $s_item_type, $owner_id) {
	$iiresults = fetch_export_item_instance_rs($s_item_type, $owner_id);
	if ($iiresults) {
		send_header($exportPlugin, $page_title);

		if ($include_header == 'Y') {
			if (method_exists($exportPlugin, 'prompt_header')) {
				$row = get_header_row('prompt', $export_columns, $s_item_type);
				if (is_not_empty_array($row)) {
					send_data($exportPlugin->prompt_header($row));
				}
			} else if (method_exists($exportPlugin, 'data_header')) {
				$row = get_header_row('data', $export_columns, $s_item_type);
				if (is_not_empty_array($row)) {
					send_data($exportPlugin->data_header());
				}
			}
		}

		$item_instance_r2 = NULL;
		while ($item_instance_r = db_fetch_assoc($iiresults)) {
			$row = get_item_row($export_columns, strlen($s_item_type) == 0, $item_instance_r['item_id'], $item_instance_r['instance_no'], $item_instance_r['owner_id'], $item_instance_r['s_item_type'], $item_instance_r['title'], $item_instance_r['borrow_duration'], $item_instance_r['s_status_type'],
					$item_instance_r['status_comment']);

			if (is_not_empty_array($row)) {
				send_data($exportPlugin->item_row($row));
			}

			$item_instance_r2 = $item_instance_r;
		}

		db_free_result($iiresults);

		if (method_exists($exportPlugin, 'close')) {
			send_data($exportPlugin->close());
		}

		send_footer($exportPlugin);
		return TRUE;
	} else {
		return FALSE;
	}
}

function get_item_row($export_columns, $is_all_item_types, $item_id, $instance_no, $owner_id, $s_item_type, $title, $borrow_duration, $s_status_type, $status_comment) {
	if (!is_array($export_columns) || $export_columns['item_id'] == 'Y') {
		$row[] = $item_id;
	}

	if (!is_array($export_columns) || $export_columns['instance_no'] == 'Y') {
		$row[] = $instance_no;
	}

	if (!is_array($export_columns) || $export_columns['owner_id'] == 'Y') {
		$row[] = $owner_id;
	}

	if (!is_array($export_columns) || $export_columns['s_item_type'] == 'Y') {
		$row[] = $s_item_type;
	}

	if ($is_all_item_types !== TRUE) {
		$results = fetch_item_attribute_type_rs($s_item_type);
		if ($results) {
			while ($item_attribute_type_r = db_fetch_assoc($results)) {
				$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
				if (!is_array($export_columns) || $export_columns[$fieldname] == 'Y') {
					if ($item_attribute_type_r['s_field_type'] != 'ITEM_ID') {
						if ($item_attribute_type_r['s_field_type'] == 'TITLE') {
							$row[] = $title;
						} else if ($item_attribute_type_r['s_field_type'] == 'DURATION') {
							$row[] = $borrow_duration;
						} else if ($item_attribute_type_r['s_field_type'] == 'STATUSTYPE') {
							$row[] = $s_status_type;
						} else if ($item_attribute_type_r['s_field_type'] == 'STATUSCMNT') {
							$row[] = $status_comment;
						} else {
							$item_attribute_val_r = fetch_attribute_val_r($item_id, $instance_no, $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
							if (is_not_empty_array($item_attribute_val_r)) {
								// the plugins will have to handle a array, and format it appropriately.
								$row[] = $item_attribute_val_r;
							} else {
								$row[] = ''; // nothing.
							}
						}
					}
				}
			}
			db_free_result($results);
		}
	} else {
		if (!is_array($export_columns) || $export_columns['title'] == 'Y') {
			$row[] = $title;
		}
	}

	return $row;
}

function get_header_row($header_type, $export_columns, $s_item_type) {
	if (!is_array($export_columns) || $export_columns['item_id'] == 'Y') {
		if ($header_type == 'data')
			$headings[] = 'item_id';
		else
			$headings[] = get_opendb_lang_var('item_id');
	}

	if (!is_array($export_columns) || $export_columns['instance_no'] == 'Y') {
		if ($header_type == 'data')
			$headings[] = 'instance_no';
		else
			$headings[] = get_opendb_lang_var('instance_no');
	}

	if (!is_array($export_columns) || $export_columns['owner_id'] == 'Y') {
		if ($header_type == 'data')
			$headings[] = 'owner_id';
		else
			$headings[] = get_opendb_lang_var('owner_id');
	}

	if (!is_array($export_columns) || $export_columns['s_item_type'] == 'Y') {
		if ($header_type == 'data')
			$headings[] = 's_item_type';
		else
			$headings[] = get_opendb_lang_var('s_item_type');
	}

	if (strlen($s_item_type) > 0) {
		// Get the item_attribute headings.
		$results = fetch_item_attribute_type_rs($s_item_type);
		if ($results) {
			while ($item_attribute_type_r = db_fetch_assoc($results)) {
				// Only legal s_field_type's - ignore ITEM_ID!!!
				if (strlen($item_attribute_type_r['s_field_type']) == 0 || $item_attribute_type_r['s_field_type'] == 'CATEGORY' || $item_attribute_type_r['s_field_type'] == 'TITLE' || $item_attribute_type_r['s_field_type'] == 'DURATION' || $item_attribute_type_r['s_field_type'] == 'STATUSTYPE'
						|| $item_attribute_type_r['s_field_type'] == 'STATUSCMNT' || $item_attribute_type_r['s_field_type'] == 'IMAGE') {
					$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
					if (!is_array($export_columns) || $export_columns[$fieldname] == 'Y') {
						if ($header_type == 'data') {
							$headings[] = $fieldname;
						} else { //if($header_type == 'prompt')
 							$headings[] = $item_attribute_type_r['prompt'];
						}
					}
				}
			}
			db_free_result($results);
		}
	} else {
		if (!is_array($export_columns) || $export_columns['title'] == 'Y') {
			if ($header_type == 'data')
				$headings[] = 'title';
			else
				$headings[] = get_opendb_lang_var('title');
		}
	}
	return $headings;
}

function send_data($buffer) {
	if (strlen($buffer)) {
		echo ($buffer);

		// do explicit flush
		flush();
	}
}

function send_header(&$exportPlugin, $page_title) {
	$filename_prefix = 'export';

	if (method_exists($exportPlugin, 'get_file_name')) {
		$filename = $exportPlugin->get_file_name();
	} else if (method_exists($exportPlugin, 'get_file_extension')) {
		$filename = $filename_prefix . '.' . $exportPlugin->get_file_extension();
	} else {
		$filename = $filename_prefix . '.txt';
	}

	if (method_exists($exportPlugin, 'get_file_content_type')) {
		$content_type = $exportPlugin->get_file_content_type();
	} else {
		$content_type = 'text/plain';
	}

	if (method_exists($exportPlugin, 'http_header')) {
		$exportPlugin->http_header($filename, $content_type);
	} else {
		header("Cache-control: no-store");
		header("Pragma: no-store");
		header("Expires: 0");
		header("Content-disposition: attachment; filename=$filename");
		header("Content-type: $content_type");
	}

	if (method_exists($exportPlugin, 'file_header')) {
		send_data($exportPlugin->file_header($page_title));
	}
}

function send_footer(&$exportPlugin) {
	if (method_exists($exportPlugin, 'file_footer')) {
		send_data($exportPlugin->file_footer());
	}
}

function get_row_export_column_form(&$exportPlugin, $HTTP_VARS) {
	global $PHP_SELF;

	$buffer .= "\n<form method=\"POST\" action=\"$PHP_SELF\">";
	$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"export\">";
	$buffer .= "\n<input type=\"hidden\" name=\"owner_id\" value=\"" . $HTTP_VARS['owner_id'] . "\">";
	$buffer .= "\n<input type=\"hidden\" name=\"s_item_type\" value=\"" . $HTTP_VARS['s_item_type'] . "\">";
	$buffer .= "\n<input type=\"hidden\" name=\"plugin\" value=\"" . $HTTP_VARS['plugin'] . "\">";

	$buffer .= "\n<ul class=\"checkboxGridOptionsVertical\">";

	$buffer .= "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"export_columns[item_id]\" value=\"Y\">" . get_opendb_lang_var('item_id') . "</li>";
	$buffer .= "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"export_columns[instance_no]\" value=\"Y\">" . get_opendb_lang_var('instance_no') . "</li>";
	$buffer .= "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"export_columns[owner_id]\" value=\"Y\">" . get_opendb_lang_var('owner_id') . "</li>";
	$buffer .= "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"export_columns[item_id]\" value=\"Y\"" . (strlen($HTTP_VARS['owner_id']) == 0 ? ' CHECKED' : '') . ">" . get_opendb_lang_var('item_id') . "</li>";
	$buffer .= "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"export_columns[s_item_type]\" value=\"Y\">" . get_opendb_lang_var('s_item_type') . "</li>";

	if (strlen($HTTP_VARS['s_item_type']) > 0) {
		$results = fetch_item_attribute_type_rs($HTTP_VARS['s_item_type']);
		if ($results) {
			while ($item_attribute_type_r = db_fetch_assoc($results)) {
				// Only legal s_field_type's - ignore ITEM_ID!!!
				if (strlen($item_attribute_type_r['s_field_type']) == 0 || $item_attribute_type_r['s_field_type'] == 'CATEGORY' || $item_attribute_type_r['s_field_type'] == 'TITLE' || $item_attribute_type_r['s_field_type'] == 'DURATION' || $item_attribute_type_r['s_field_type'] == 'STATUSTYPE'
						|| $item_attribute_type_r['s_field_type'] == 'STATUSCMNT' || $item_attribute_type_r['s_field_type'] == 'IMAGE') {
					$buffer .= '<li><input type="checkbox" class="checkbox" name="export_columns[' . get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']) . ']" value="Y"';

					// work out what columns to have checked by default.
					if ($item_attribute_type_r['s_field_type'] == 'TITLE' || $item_attribute_type_r['s_field_type'] == 'IMAGE'
							|| (strlen($item_attribute_type_r['s_field_type']) == 0 && strcasecmp(ifempty($item_attribute_type_r['display_type'], $item_attribute_type_r['input_type']), "hidden") !== 0)) {
						$buffer .= ' CHECKED';
					}

					$buffer .= '>' . $item_attribute_type_r['prompt'] . "</li>";
				}
			}
			db_free_result($results);
		}
	} else {
		$buffer .= "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"export_columns[title]\" value=\"Y\">" . get_opendb_lang_var('title') . "</li>";
	}

	if (method_exists($exportPlugin, 'prompt_header') || method_exists($exportPlugin, 'data_header')) {
		$buffer .= "\n<li id=\"include-header\"><input type=\"checkbox\" class=\"checkbox\" name=\"include_header\" value=\"Y\" CHECKED>" . get_opendb_lang_var('include_header') . "</li>";
	}

	$buffer .= "</ul>";

	$buffer .= "\n<ul class=\"actionButtons\">";
	$buffer .= "\n<li><input type=\"button\" class=\"button\" value=\"" . get_opendb_lang_var('check_all') . "\" onClick=\"setCheckboxes(this.form, 'export_columns', true);\"></li>" . "\n<li><input type=\"button\" class=\"button\" value=\"" . get_opendb_lang_var('uncheck_all')
			. "\" onClick=\"setCheckboxes(this.form, 'export_columns', false);\"></li>" . "\n<li><input type=\"reset\" class=\"reset\" value=\"" . get_opendb_lang_var('reset') . "\"></li>" . "\n<li class=\"submitButton\"><input type=\"submit\" class=\"submit\" value=\""
			. get_opendb_lang_var('export_items') . "\"></li>" . "</ul>";

	$buffer .= '</form>';

	return $buffer;
}

if (is_site_enabled()) {
	if (is_opendb_valid_session()) {
		if (is_user_granted_permission(PERM_ADMIN_EXPORT) || is_user_granted_permission(PERM_USER_EXPORT)) {
			if (!is_numeric($HTTP_VARS['item_id']) && !is_user_granted_permission(PERM_ADMIN_EXPORT)) {
				$HTTP_VARS['owner_id'] = get_opendb_session_var('user_id');
			}

			if ($HTTP_VARS['op'] == 'export') {
				$exportPlugin = &get_export_plugin($HTTP_VARS['plugin']);
				if ($exportPlugin !== NULL) {
					if (strlen($HTTP_VARS['s_item_type']) == 0 || is_valid_item_type_structure($HTTP_VARS['s_item_type'])) {
						if ($exportPlugin->get_plugin_type() == 'row') {
							// Work out page title.
							if (strlen($HTTP_VARS['owner_id']) > 0)
								$page_title = get_opendb_lang_var('type_export_for_name_item_type', array('description' => $exportPlugin->get_display_name(), 'fullname' => fetch_user_name($HTTP_VARS['owner_id']), 'user_id' => $HTTP_VARS['owner_id'], 's_item_type' => $HTTP_VARS['s_item_type']));
							else if (strlen($HTTP_VARS['s_item_type']) > 0)
								$page_title = get_opendb_lang_var('type_export_for_item_type', array('description' => $exportPlugin->get_display_name(), 's_item_type' => $HTTP_VARS['s_item_type']));
							else
								$page_title = get_opendb_lang_var('type_export', array('description' => $exportPlugin->get_display_name()));

							if (is_not_empty_array($HTTP_VARS['export_columns'])) {
								@set_time_limit(600);
								if (!export_row_items($exportPlugin, $page_title, $HTTP_VARS['include_header'], $HTTP_VARS['export_columns'], $HTTP_VARS['s_item_type'], $HTTP_VARS['owner_id'])) {
									echo _theme_header($page_title);
									echo ("<h2>" . $page_title . "</h2>");
									echo format_error_block(array('error' => get_opendb_lang_var('no_records_found'), 'detail' => ''));
									echo _theme_footer();
								}
							} else {
								echo _theme_header($page_title);
								echo ("<h2>" . $page_title . "</h2>");

								echo ("<h3>" . get_opendb_lang_var('choose_export_fields') . "</h3>");

								echo (get_row_export_column_form($exportPlugin, $HTTP_VARS));

								echo _theme_footer();
							}
						} else if ($exportPlugin->get_plugin_type() == 'item') {
							$titleMaskCfg = new TitleMask('item_display');

							// Work out page title.
							if (strlen($HTTP_VARS['owner_id']) > 0 || is_numeric($HTTP_VARS['item_id'])) {
								if (strlen($HTTP_VARS['owner_id']) > 0 && strlen($HTTP_VARS['s_item_type']) > 0)
									$page_title = get_opendb_lang_var('type_export_for_name_item_type', array('description' => $exportPlugin->get_display_name(), 'fullname' => fetch_user_name($HTTP_VARS['owner_id']), 'user_id' => $HTTP_VARS['owner_id'], 's_item_type' => $HTTP_VARS['s_item_type']));
								else if (strlen($HTTP_VARS['s_item_type']) > 0)
									$page_title = get_opendb_lang_var('type_export_for_item_type', array('description' => $exportPlugin->get_display_name(), 's_item_type' => $HTTP_VARS['s_item_type']));
								else if (is_numeric($HTTP_VARS['item_id']) && is_numeric($HTTP_VARS['instance_no'])) {
									$item_r = fetch_item_instance_r($HTTP_VARS['item_id'], $HTTP_VARS['instance_no']);
									$page_title = get_opendb_lang_var('type_export_for_item_instance', array('description' => $exportPlugin->get_display_name(), 'item_id' => $HTTP_VARS['item_id'], 'instance_no' => $HTTP_VARS['instance_no'], 'title' => $titleMaskCfg->expand_item_title($item_r)));
								} else if (is_numeric($HTTP_VARS['item_id'])) {
									// Not really a child item, but we are not interested in the instance, so use this.  It still
									// returns the right data anyway.
									$item_r = fetch_child_item_r($HTTP_VARS['item_id']);
									$page_title = get_opendb_lang_var('type_export_for_item', array('description' => $exportPlugin->get_display_name(), 'item_id' => $HTTP_VARS['item_id'], 'title' => $titleMaskCfg->expand_item_title($item_r)));
								} else {
									$page_title = get_opendb_lang_var('type_export_for_name', array('description' => $exportPlugin->get_display_name(), 'fullname' => fetch_user_name($HTTP_VARS['owner_id']), 'user_id' => $HTTP_VARS['owner_id']));
								}
							} else { //if(strlen($HTTP_VARS['owner_id'])>0 || is_numeric($HTTP_VARS['item_id']))
 								$page_title = get_opendb_lang_var('type_export', array('description' => $exportPlugin->get_display_name()));
							}

							@set_time_limit(600);
							if (!export_type_items($exportPlugin, $page_title, $HTTP_VARS['s_item_type'], $HTTP_VARS['item_id'], $HTTP_VARS['instance_no'], $HTTP_VARS['owner_id'], $HTTP_VARS['s_status_type'])) {
								echo _theme_header($page_title);
								echo ("<h2>" . $page_title . "</h2>");
								echo format_error_block(array('error' => get_opendb_lang_var('no_records_found'), 'detail' => ''));
								echo _theme_footer();
							}
						} else { //get_plugin_type() not supported.
 							echo _theme_header(get_opendb_lang_var('undefined_error'));
							echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
							echo _theme_footer();
						}
					} else { //if(strlen($HTTP_VARS['s_item_type'])==0 || is_valid_item_type_structure($HTTP_VARS['s_item_type']))
 						$page_title = get_opendb_lang_var('type_export', array('description' => $exportPlugin->get_display_name()));
						echo _theme_header($page_title);
						echo ("<h2>" . $page_title . "</h2>");
						echo (format_error_block(array('error' => get_opendb_lang_var('invalid_item_type_structure', 's_item_type', $HTTP_VARS['s_item_type']), 'detail' => '')));
						echo _theme_footer();
					}
				} else { //if($importPlugin !== NULL)
 					echo _theme_header(get_opendb_lang_var('undefined_error'));
					echo ("<p class=\"error\">" . get_opendb_lang_var('undefined_error') . "</p>");
					echo _theme_footer();
				}
			} else {
				// A custom title.
				if (is_user_granted_permission(PERM_ADMIN_EXPORT) && strlen($HTTP_VARS['owner_id']) == 0)
					$page_title = get_opendb_lang_var('export_items');
				else if ($HTTP_VARS['owner_id'] == get_opendb_session_var('user_id') || strlen($HTTP_VARS['owner_id']) == 0)
					$page_title = get_opendb_lang_var('export_my_items');

				echo _theme_header($page_title);
				echo ("<h2>" . $page_title . "</h2>");

				echo ("<form method=\"GET\" action=\"$PHP_SELF\">");
				echo ("\n<input type=\"hidden\" name=\"op\" value=\"export\">");

				echo ("<table>");

				$field = "\n<select name=\"plugin\">\n";
				$plugin_list_r = get_export_plugin_list_r();
				if (is_array($plugin_list_r)) {
					foreach ($plugin_list_r as  $plugin_r) {
						$field .= '<option value="' . $plugin_r['name'] . '"';

						if ($plugin_r['name'] == 'OpenDbExportPlugin') {
							$field .= ' SELECTED';
						}

						$field .= '>' . $plugin_r['description'] . "\n";
					}
				}
				$field .= "</select>";

				echo format_field(get_opendb_lang_var('type'), $field);

				if (is_user_granted_permission(PERM_ADMIN_EXPORT)) {
					echo format_field(get_opendb_lang_var('owner'),
							"\n<select name=\"owner_id\">" . "\n<option value=\"\">-------------- " . get_opendb_lang_var('all') . " --------------"
									. custom_select('owner_id', fetch_user_rs(PERM_USER_EXPORT), '%fullname% (%user_id%)', 'NA', ifempty($HTTP_VARS['owner_id'], get_opendb_session_var('user_id')), 'user_id') . "\n</select>");
				} else {
					echo ("<input type=\"hidden\" name=\"owner_id\" value=\"" . $HTTP_VARS['owner_id'] . "\">");
				}

				echo format_field(get_opendb_lang_var('item_type'),
						"<select name=\"s_item_type\">" . "\n<option value=\"\">-------------- " . get_opendb_lang_var('all') . " --------------"
								. custom_select('s_item_type', fetch_item_type_rs(), '%s_item_type% - %description%', 'NA', $HTTP_VARS['s_item_type'], 's_item_type') . "\n</select>");

				$lookup_results = fetch_status_type_rs(TRUE);
				if ($lookup_results && db_num_rows($lookup_results) > 1) {
					echo format_field(get_opendb_lang_var('s_status_type'), checkbox_grid('s_status_type', $lookup_results, '%img%', // mask
					'VERTICAL', NULL)); // value
				}

				echo ("</table>");

				echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">");

				echo ("</form>");

				echo _theme_footer();
			}
		} else { //no guests or borrowers allowed!
 			opendb_not_authorised_page(array(PERM_USER_EXPORT, PERM_ADMIN_EXPORT));
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
