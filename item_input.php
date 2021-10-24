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

include_once("./lib/utils.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/item.php");
include_once("./lib/http.php");
include_once("./lib/fileutils.php");
include_once("./lib/user.php");
include_once("./lib/review.php");
include_once("./lib/item_attribute.php");
include_once("./lib/item_type.php");
include_once("./lib/widgets.php");
include_once("./lib/parseutils.php");
include_once("./lib/site_plugin.php");
include_once("./lib/item_input.php");
include_once("./lib/item_display.php");
include_once("./lib/status_type.php");
include_once("./lib/TitleMask.class.php");
include_once("./lib/HTML_Listing.class.php");

function get_related_item_search_script() {
	$script = "$(document).ready(function() {
    $('#parent_item_loading').hide();

    $('#parent_item_filter').change(function() {
        $('#parent_item').prop('disabled', true);
        $('#parent_item_loading').show();

        var requestData = 'ajax_op=possible-parents&item_id=' + $('form [name=\"item_id\"]').val() + '&parent_item_filter=' + $('#parent_item_filter').val();

        $.ajax({
            type: 'post',
            url: 'item_input.php',
            dataType: 'json',
            data: requestData
        })
        .done(function(data) {
            $('#parent_item').html(data.select);
        })
        .always(function() {
            $('#parent_item').prop('disabled', false);
            $('#parent_item_loading').hide();
        });
    });
});
";
	return "\n<script language=\"JavaScript\">\n<!-- // hide from stupid browsers\n" . $script . "\n// -->\n</script>\n";
}

function format_item_parents_select($HTTP_VARS, $item_r, $filter = null) {
	$titleMaskCfg = new TitleMask ( 'item_listing' );
	
	$possible_parents = fetch_available_item_parents($HTTP_VARS, $item_r, $filter, false);
	
	// options will be in format ITEM_ID.INSTANCE_NO, so do not clash with existing parent_item_id / parent_instance_no stuff.
	$parent_item_list = '<select name="parent_item" id="parent_item">';

	if (!is_null($filter) && $filter != '%parent_only%') {
		if (count($possible_parents) > 0) {
			$parent_item_list .= '<option value="0">' . get_opendb_lang_var('none') . '</option>';
		} else {
			$parent_item_list .= '<option value="0">' . get_opendb_lang_var('nothing_found') . '</option>';
		}
	} else {
		$parent_item_list .= '<option value="0">' . get_opendb_lang_var('none') . '</option>';
	}

	foreach ($possible_parents as $parent) {
		if (!isset($parent['current_parent'])) {
			$parent ['title'] = $titleMaskCfg->expand_item_title ( $parent );
			$parent_item_list .= '<option value="' . $parent['item_id'] . '_' . $parent['instance_no'] . '">'
					. utf8_encode($parent['title']) . '</option>';
		}
	}

	$parent_item_list .= '</select> <span id="parent_item_loading">' . get_opendb_lang_var('loading') . '...</span>';

	return $parent_item_list;
}

/**
 * Will test the old against the new value.
 * 
 * Assumes that filter_item_input_field(...) has already
 * been called for new_value.
 */
function is_value_refreshed($s_attribute_type, $new_value, $old_value) {
	// Do the simplest check first!
	if (!is_array($old_value) && strlen($old_value) == 0 && !is_array($new_value) && strlen($new_value) > 0) {
		return TRUE;
	} else {
		// they should both be arrays at this point.
		if (is_multivalue_attribute_type($s_attribute_type)) {
			if (is_not_empty_array($old_value) && is_not_empty_array($new_value) && count($old_value) == count($new_value)) {
				for ($i = 0; $i < count($old_value); $i++) {
					// case insensitive search
					if (array_search2($old_value[$i], $new_value, TRUE) === FALSE) {
						return TRUE;
					}
				}

				//else
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			if (is_array($new_value)) { // multi-value option automatically means a refreshed field
 				foreach ($new_value as $val) {
					if (strcmp($val, $old_value) !== 0) {
						return TRUE;
					}
				}
				return FALSE;
			}
			if (strcmp($new_value, $old_value) !== 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
}

/**
 * This function is responsible for grouping input fields that
 * should appear grouped together. Any hidden fields, will be
 * grouped together, so the array that gets returned will look like:
 * 
 * array(hidden_fields=>array(
 * 			$input_field_r, $input_field_r, ...),
 * 		 display_fields=>array(
 * 				array(prompt=>'field group prompt',
 * 					fieldset=>array(
 * 						$input_field_r, $input_field_r, ....)
 * 					)
 *		)
 * )
 */
function get_site_type_input_fields($HTTP_VARS, $site_plugin_r, $item_r) {
	global $titleMaskCfg;

	$input_fields_rs = NULL;

	$sifresults = fetch_site_plugin_input_field_rs($site_plugin_r['site_type']);
	if ($sifresults) {
		$display_field_r = NULL;
		$inrow = FALSE;
		while ($input_field_r = db_fetch_assoc($sifresults)) {
			// Only if we are are refreshing an item.
			if (is_not_empty_array($item_r)) {
				$titleMaskCfg->reset();
				$value = $titleMaskCfg->expand_title($item_r, $input_field_r['refresh_mask']);
			} else if ($input_field_r['field_type'] != 'hidden') {
				$titleMaskCfg->reset();

				// a kludge to support feature request #816240 - failure when adding an item should return previous search
				$value = ifempty($titleMaskCfg->expand_title($HTTP_VARS, $input_field_r['refresh_mask']), $input_field_r['default_value']);
			} else {
				$value = $input_field_r['default_value'];
			}

			switch ($input_field_r['field_type']) {
			case 'hidden':
				$input_fields_rs[] = array('name' => $input_field_r['field'], 'type' => 'hidden', 'value' => $value);
				break;

			default:
				if (strlen($input_field_r['prompt']) > 0) {
					if ($inrow) {
						$input_fields_rs[] = $display_field_r;
						$display_field_r = NULL;
						$inrow = FALSE;
					}

					$display_field_r['prompt'] = $input_field_r['prompt'];
					$display_field_r['type'] = 'fieldset';
					$display_field_r['fieldset'][] = array('name' => $input_field_r['field'], 'type' => 'text', 'value' => $value);

					$inrow = TRUE;
				} else {
					if (!$inrow) {
						$newrow = TRUE;
						$input_fields_rs['fieldset'][] = $input_field_r;
					}
				}
				break;
			}//switch
		}
		db_free_result($sifresults);

		if ($display_field_r != NULL) {
			$input_fields_rs[] = $display_field_r;
		}
	}

	return $input_fields_rs;
}

function get_site_plugin_rs($HTTP_VARS, $item_r = NULL) {
	$site_plugin_rs = NULL;

	// if $HTTP_VARS['s_item_type'] is null, all site plugins will be returned.
	$results = fetch_site_plugin_rs($HTTP_VARS['s_item_type'] ?? NULL);
	if ($results) {
		$ischecked = FALSE;

		while ($site_plugin_r = db_fetch_assoc($results)) {
			if (is_exists_site_plugin($site_plugin_r['site_type'])) {
				$input_field_rs = get_site_type_input_fields($HTTP_VARS, $site_plugin_r, $item_r);
				if (is_array($input_field_rs)) {
					$site_plugin_r['input_fields'] = $input_field_rs;

					if (strlen($HTTP_VARS['s_item_type'] ?? "") == 0) {
						$site_plugin_r['s_item_type'] = fetch_site_item_type_r($site_plugin_r['site_type']);
					} else {
						$site_plugin_r['s_item_type'][] = $HTTP_VARS['s_item_type'];
					}
					if (is_array($site_plugin_r['s_item_type'])) {
						if (!$ischecked) {
							$site_plugin_r['checked_ind'] = 'Y';
							$ischecked = TRUE;
						} else {
							$site_plugin_r['checked_ind'] = 'N';
						}
						$site_plugin_rs[] = $site_plugin_r;
					}
				}
			}
		}
		db_free_result($results);
	}

	return $site_plugin_rs;
}

function display_site_plugin_blocks($HTTP_VARS, $item_r = NULL) {
	global $titleMaskCfg;

	$site_plugin_rs = get_site_plugin_rs($HTTP_VARS, $item_r);
	if (is_array($site_plugin_rs)) {
		echo ("<div id=\"site-add-container\">");

		echo ("<ul id=\"site-add-menu\">");
		reset($site_plugin_rs);
		$first = TRUE;
		foreach ($site_plugin_rs as $site_plugin_r) {
			echo ("<li" . ($first ? ' class="first activeTab"' : ' class=""') . " id=\"menu-" . $site_plugin_r['site_type'] . "\" onClick=\"return activateTab('" . $site_plugin_r['site_type'] . "', 'site-add-menu', 'site-add-content', 'activeTab', 'sitePlugin'); return false;\">"
					. $site_plugin_r['title'] . "</li>");
			if ($first)
				$first = FALSE;
		}
		echo ("</ul>");

		echo ("\n<div id=\"site-add-content\">");
		reset($site_plugin_rs);
		foreach ($site_plugin_rs as $site_plugin_r) {
			echo ("\n<div class=\"sitePlugin" . ($site_plugin_r['checked_ind'] != 'Y' ? "Hidden" : "") . "\" id=\"" . $site_plugin_r['site_type'] . "\">");

			$title = "<img src=\"./images/site/" . $site_plugin_r['image'] . "\" title=\"" . strip_tags($site_plugin_r['description']) . "\" alt=\"" . strip_tags($site_plugin_r['description']) . "\">";

			echo ("<h3>" . $site_plugin_r['title'] . "</h3>");

			echo theme_image("images/site/" . $site_plugin_r['image'], strip_tags($site_plugin_r['description']), "siteLogo");

			echo ("<h4>" . $site_plugin_r['description'] . "</h4>");

			start_item_input_form($HTTP_VARS, "site-search");
			echo ("<input type=hidden name=\"site_type\" value=\"" . $site_plugin_r['site_type'] . "\">\n");

			if (isset($HTTP_VARS['s_item_type']) && is_exists_item_type($HTTP_VARS['s_item_type'])) {
				echo ("\n<input type=\"hidden\" name=\"s_item_type\" value=\"" . $HTTP_VARS['s_item_type'] . "\">");
			} else {
				echo ("\n<label for=\"" . $site_plugin_r['site_type'] . "-s_item_type\">" . get_opendb_lang_var('item_type') . "</label>");
				echo (single_select("s_item_type", fetch_item_type_for_item_types_rs($site_plugin_r['s_item_type'], TRUE), "%value% - %display%", NULL, NULL, NULL, FALSE, $site_plugin_r['site_type'] . "-s_item_type"));
			}

			if (is_array($site_plugin_r['input_fields'])) {
				foreach ($site_plugin_r['input_fields'] as $input_field_r) {
					if ($input_field_r['type'] == 'hidden') {
						echo ("<input type=hidden name=\"" . $input_field_r['name'] . "\" value=\"" . htmlspecialchars($input_field_r['value']) . "\">");
					} else {
						foreach ($input_field_r['fieldset'] as $field_r) {
							echo ("<label for=\"" . $site_plugin_r['site_type'] . "-" . $field_r['name'] . "\">" . $input_field_r['prompt'] . "</label>");
							echo ("<input id=\"" . $site_plugin_r['site_type'] . "-" . $field_r['name'] . "\" class=\"text\" type=\"text\" name=\"" . $field_r['name'] . "\" value=\"" . htmlspecialchars($field_r['value']) . "\">\n");
						}
					}
				}
			}

			echo ("<input class=\"submit\" type=submit value=\"" . get_opendb_lang_var('site_search', 'site', $site_plugin_r['title']) . "\">");

			echo ("</form>");

			echo ("\n</div>\n");
		}
		echo ("</div>");
	}//if($results)

	// add manual entry record
	if (!is_array($item_r)) {
		echo ("<div id=\"site-add-manual\">");

		echo ("<h3>" . get_opendb_lang_var('manual_entry') . "</h3>");

		start_item_input_form($HTTP_VARS, "new");

		if (isset($HTTP_VARS['s_item_type']) && is_exists_item_type($HTTP_VARS['s_item_type'])) {
			echo ("\n<input type=\"hidden\" name=\"s_item_type\" value=\"" . $HTTP_VARS['s_item_type'] . "\">");
		} else {
			echo ("\n<label for=\"manual-s_item_type\">" . get_opendb_lang_var('item_type') . "</label>");
			echo (single_select("s_item_type", fetch_item_type_rs(TRUE), "%value% - %display%", NULL, NULL, NULL, FALSE, 'manual-s_item_type'));
		}

		echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">");

		echo ("</form>");

		echo ("</div>");
	}

	return (is_array($site_plugin_rs) || !is_array($item_r));
}

function handle_site_add_or_refresh($item_r, $status_type_r, &$HTTP_VARS, &$footer_links_r) {
	global $PHP_SELF;
	global $titleMaskCfg;

	if (!is_array($item_r)) {
		if (($HTTP_VARS['confirmed'] ?? "") == 'false' || strlen($HTTP_VARS['owner_id']) == 0) {
			$page_title = get_opendb_lang_var('add_new_item');
			echo _theme_header($page_title);

			if (isset($HTTP_VARS['s_item_type']) && is_exists_item_type($HTTP_VARS['s_item_type']))
				echo ("<h2>" . $page_title . " " . get_item_image($HTTP_VARS['s_item_type']) . "</h2>");
			else
				echo ("<h2>" . $page_title . "</h2>");

			echo ("<form action=\"$PHP_SELF\" method=\"GET\">" . get_url_fields($HTTP_VARS, array('confirmed' => 'true')));

			$HTTP_VARS['owner_id'] = ifempty($HTTP_VARS['owner_id'], get_opendb_session_var('user_id'));

			echo ("<h3>" . get_opendb_lang_var('what_owner_add_new_item') . "</h3>");

			echo ("\n<select name=\"owner_id\">" . custom_select('owner_id', fetch_user_rs(PERM_ITEM_OWNER), '%fullname% (%user_id%)', 'NA', $HTTP_VARS['owner_id'], 'user_id') . "\n</select>");

			echo ("<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('add_new_item') . "\">");
			echo ("</form>");
		} else { //if($show_owner_field || $show_status_type_field)
 			if ($HTTP_VARS['owner_id'] != get_opendb_session_var('user_id'))
				$page_title = get_opendb_lang_var('add_new_item_for_name', array('user_id' => $HTTP_VARS['owner_id'], 'fullname' => fetch_user_name($HTTP_VARS['owner_id'])));
			else
				$page_title = get_opendb_lang_var('add_new_item');

			echo _theme_header($page_title);

			if (isset($HTTP_VARS['s_item_type']) && is_exists_item_type($HTTP_VARS['s_item_type']))
				echo ("<h2>" . $page_title . " " . get_item_image($HTTP_VARS['s_item_type']) . "</h2>");
			else
				echo ("<h2>" . $page_title . "</h2>");

			if (!display_site_plugin_blocks($HTTP_VARS, $item_r)) {
				echo ("<p class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</p>");
			}
		}
	} else if (is_not_empty_array($item_r)) {
		$HTTP_VARS['s_item_type'] = $item_r['s_item_type'];

		$page_title = get_opendb_lang_var('refresh_title', array('display_title' => $titleMaskCfg->expand_item_title($item_r)));
		echo _theme_header($page_title);
		echo ("<h2>" . $page_title . " " . get_item_image($item_r['s_item_type']) . "</h2>\n");

		if (!display_site_plugin_blocks($HTTP_VARS, $item_r)) {
			echo ("<p class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</p>");
		}
	}

	if (is_not_empty_array($item_r)) {
		$footer_links_r[] = array('url' => "item_display.php?item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
	}
}

function start_item_input_form($HTTP_VARS, $op) {
	echo ("\n<form action=\"item_input.php\" method=\"GET\">");

	if (isset($HTTP_VARS['owner_id']))
		echo ("<input type=hidden name=\"owner_id\" value=\"" . $HTTP_VARS['owner_id'] . "\">\n");
	if (isset($HTTP_VARS['s_status_type']))
		echo ("<input type=hidden name=\"s_status_type\" value=\"" . $HTTP_VARS['s_status_type'] . "\">\n");
	if (isset($HTTP_VARS['item_id']))
		echo ("<input type=hidden name=\"item_id\" value=\"" . $HTTP_VARS['item_id'] . "\">\n");
	if (isset($HTTP_VARS['instance_no']))
		echo ("<input type=hidden name=\"instance_no\" value=\"" . $HTTP_VARS['instance_no'] . "\">\n");
	if (isset($HTTP_VARS['parent_item_id']))
		echo ("<input type=hidden name=\"parent_item_id\" value=\"" . $HTTP_VARS['parent_item_id'] . "\">\n");
	if (isset($HTTP_VARS['parent_instance_no']))
		echo ("<input type=hidden name=\"parent_instance_no\" value=\"" . $HTTP_VARS['parent_instance_no'] . "\">\n");
	echo ("<input type=hidden  name=\"op\" value=\"" . $op . "\">\n");
}

/**
    Bypass get_edit_form, by choosing defaults for all data provided by site plugin, supports site / refresh operations
 */
function get_site_item_input_data($op, $item_r, $HTTP_VARS) {
	$results = fetch_item_attribute_type_rs($item_r['s_item_type'], 'not_instance_field_types');
	if ($results) {
		while ($item_attribute_type_r = db_fetch_assoc($results)) {
			$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
			if (isset($HTTP_VARS[$fieldname])) {
				if (is_array($HTTP_VARS[$fieldname])) {
					if (is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type'])) {
						$value = $HTTP_VARS[$fieldname];
					} else {
						$value = $HTTP_VARS[$fieldname][0];
					}
				} else {
					$value = $HTTP_VARS[$fieldname];
				}

				unset($HTTP_VARS[$fieldname]);

				if ($item_attribute_type_r['s_field_type'] == 'TITLE') {
					$fieldname = 'title';
				}

				$HTTP_VARS[$fieldname] = $value;
			} else if ($op == 'refresh') {
				if ($item_attribute_type_r['s_field_type'] == 'TITLE') {
					$fieldname = 'title';
					$HTTP_VARS[$fieldname] = $item_r['title'];
				} else if (is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type']))
					$HTTP_VARS[$fieldname] = fetch_attribute_val_r($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
				else
					$HTTP_VARS[$fieldname] = fetch_attribute_val($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
			}
		}
		db_free_result($results);
	}

	return $HTTP_VARS;
}

// This function will calculate a field input_field value,
// based on $HTTP_VARS, $op and $indexes (Used for site operations)
function get_field_value($op, $item_r, $s_attribute_type, $order_no, $s_field_type, $attribute_val, &$HTTP_VARS) {
	if (is_not_empty_array($HTTP_VARS)) { // Lets try to get field value, from HTTP
 		$fieldname = get_field_name($s_attribute_type, $order_no);

		// refresh operation!
		if (isset($HTTP_VARS[$fieldname]) && !is_array($HTTP_VARS[$fieldname])) {
			if (preg_match("/new([0-9]+)/", $HTTP_VARS[$fieldname], $matches) && isset($HTTP_VARS[$fieldname . '_' . $matches[0]])) {
				$HTTP_VARS[$fieldname] = $HTTP_VARS[$fieldname . '_' . $matches[0]];
			} else if ($HTTP_VARS[$fieldname] == 'old') {
				// make sure this is a refresh value and not just a field with the value 'old'
				if (isset($HTTP_VARS[$fieldname . '_new0'])) {
					// Using $item_r value instead.
					unset($HTTP_VARS[$fieldname]);
				}
			}
		}

		// this is a kludge for when a new fails
		if (!isset($HTTP_VARS[$fieldname]) && $s_field_type == 'TITLE') {
			$fieldname = 'title';
		}

		// If $HTTP_VARS[$fieldname] is set, we have probably come back to
		// edit form, after a failed insert or update.
		if (isset($HTTP_VARS[$fieldname])) {
			// Is it an upload operation - There is not much we can do in the case of $_FILES, as they
			// cannot be cached and passed into next request.  A user would have to re-upload the file.
			if (is_array($_FILES) && is_array($_FILES[$fieldname]))
				return NULL;
			else
				// normal field
				return $HTTP_VARS[$fieldname];
		} else if ($op == 'new') {
            return get_field_default_value($s_attribute_type);
		} else if ($op == 'site' && !isset($HTTP_VARS[$fieldname])) {
            return get_field_default_value($s_attribute_type);
        }
	}

	return get_old_field_value($item_r, $s_field_type, $attribute_val);
}

function get_old_field_value($item_r, $s_field_type, $attribute_val) {
	if ($s_field_type == 'STATUSTYPE') {
		return $item_r['s_status_type'];
	} else if ($s_field_type == 'STATUSCMNT') {
		return $item_r['status_comment'];
	} else if ($s_field_type == 'DURATION') {
		return $item_r['borrow_duration'];
	} else if ($s_field_type == 'TITLE') {
		return $item_r['title'];
	} else {
		return $attribute_val;
	}
}

function get_field_default_value($s_attribute_type) {
    $attribute_type_r = fetch_attribute_type_r($s_attribute_type);
    $value = NULL;

    switch ($attribute_type_r['input_type']) {
        case 'text':
            $value = $attribute_type_r['input_type_arg3'];
            break;
        case 'number':
            $value = $attribute_type_r['input_type_arg2'];
            break;
        case 'filtered':
            $value = $attribute_type_r['input_type_arg4'];
            break;
        case 'checkbox':
            if ($attribute_type_r['input_type_arg3'] == $attribute_type_r['input_type_arg1']
                || $attribute_type_r['input_type_arg3'] == $attribute_type_r['input_type_arg2']) {
                $value = $attribute_type_r['input_type_arg3'];
            }
            break;
    }

    return $value;
}

// If $old_value !== FALSE, then we will assume a refresh operation and display a
// special refresh row, if the $new_value is different from the $old_value.
function get_item_form_row($op, $item_r, $item_attribute_type_r, $old_value, $new_value) {
	if ($item_attribute_type_r['s_field_type'] == 'TITLE') {
		$fieldname = 'title';
	} else {
		$fieldname = get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
	}

	$is_multi_value = is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type']);

	$refresh_field = FALSE;
	// Hidden cannot be involved in a refresh operation directly, but refreshed hidden fields, will still be updated.
	if (strcasecmp($item_attribute_type_r['input_type'], 'hidden') !== 0 && $old_value !== FALSE && (is_not_empty_array($new_value) || (!is_array($new_value) && strlen($new_value) > 0)) && is_value_refreshed($item_attribute_type_r['s_attribute_type'], $new_value, $old_value)
			&& 
			// Do not display 'Old' & 'New' options, if there was NO previous Old value (but $old_field is not FALSE!!!)
			(is_not_empty_array($old_value) || (!is_array($old_value) && strlen($old_value) > 0))) {

		$refresh_field = TRUE;

		$new_value = get_array_for_value($new_value);
	}

	if (($refresh_field && count($new_value) > 0) || (is_array($new_value) && !$is_multi_value)) {
		// -------------
		// REFRESH FIELD
		// -------------
		// If we are doing a complete refresh block, and display_type is set to 
		// hidden, overwrite to display it.
		if ($item_attribute_type_r['display_type'] == 'hidden') {
			$item_attribute_type_r['display_type'] = 'display(%value%)';
			$item_attribute_type_r['display_type_arg1'] = '%value%';
		}

		$field .= "<ul class=\"tabMenu\" id=\"${fieldname}-tab-menu\">";

		if (!$is_multi_value) {
			$new_value = deduplicate_array($new_value, $old_value);
			$count = count($new_value) - 1;
		} else {
			$count = 0;
		}

		$key = array_keys($new_value);
		for ($i = 0; $i <= $count; $i++) {
			is_numeric($key[$i]) ? $label_description = "" : $label_description = " : ${key[$i]}";
			$field .= "<li id=\"menu-${fieldname}_new${i}\"" . ($i == 0 ? " class=\"first activeTab\"" : "") . ">
					<label for=\"menu-${fieldname}_new${i}-cbox\">" . $item_attribute_type_r['prompt'] . "$label_description</label>" . "<input type=\"radio\" class=\"radio\" name=\"" . $fieldname
					. "\" id=\"menu-${fieldname}_new${i}-cbox\" value=\"new${i}\" onclick=\"return activateTab('${fieldname}_new${i}', '${fieldname}-tab-menu', '${fieldname}-tab-content');\"" . ($i == 0 ? " CHECKED" : "") . "></li>";
		}

		if ($refresh_field) {
			$field .= "<li id=\"menu-${fieldname}_old\" >
				<label for=\"menu-${fieldname}_old-cbox\">" . get_opendb_lang_var('old_prompt', 'prompt', $item_attribute_type_r['prompt']) . "</label>" . "<input type=\"radio\" class=\"radio\" id=\"menu-${fieldname}_old-cbox\" name=\"" . $fieldname
					. "\" value=\"old\" onclick=\"return activateTab('${fieldname}_old', '${fieldname}-tab-menu', '${fieldname}-tab-content');\"></li>";
		}
		$field .= "</ul>";

		$field .= "<div class=\"tabContentContainer\" id=\"${fieldname}-tab-content\">";

		for ($i = 0; $i <= $count; $i++) {
			if ($is_multi_value) {
				$value = $new_value;
			} else {
				$value = $new_value[$key[$i]];
			}

			$field .= "<div class=\"tabContent" . ($i > 0 ? "Hidden" : "") . "\" id=\"${fieldname}_new${i}\">" . get_item_input_field($fieldname . "_new${i}", $item_attribute_type_r, $item_r, $value, FALSE) . "</div>";
		}

		if ($refresh_field) {
			$field .= "<div class=\"tabContentHidden\" id=\"${fieldname}_old\">" . get_item_input_field($fieldname . "_old", $item_attribute_type_r, $item_r, $old_value, FALSE) . "</div>";
		}

		$field .= "</div>";

		$prompt_mask = NULL;
		if ($refresh_field) {
			$prompt_mask = theme_image('rs.gif', get_opendb_lang_var('refreshed')) . "%prompt%";
		}

		return format_item_data_field($item_attribute_type_r, $field, $prompt_mask, NULL); // field mask

	} else { // not a choose attribute
 		if (is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type'])) {
			// if new_value is empty!
			if (is_empty_or_not_array($new_value)) {
				if ($old_value !== FALSE)
					$value = $old_value;
				else
					$value = NULL;
			} else {
				$value = &$new_value;
			}

			return get_item_input_field($fieldname, $item_attribute_type_r, $item_r, $value);
		} else {
			$value = ifempty($new_value, $old_value === FALSE ? NULL : $old_value);

			// If this is an edit operation - the value must be NOT NULL
			// for some widgets to work properly.
			if ($op != 'new' && $op != 'site' && $value === NULL) {
				$value = '';
			}

			return get_item_input_field($fieldname, $item_attribute_type_r, $item_r, $value);
		}
	}
}

function get_edit_item_form($op, $item_r, $HTTP_VARS, &$upload_file_fields) {
	// is at least one field a compulsory field?
	$compulsory_fields = FALSE;
	$upload_file_fields = FALSE;

	$results = fetch_item_attribute_type_rs($item_r['s_item_type'], 'not_instance_field_types');
	if ($results) {
		$formContents = "\n<table>";
		while ($item_attribute_type_r = db_fetch_assoc($results)) {
			if ($item_attribute_type_r['s_field_type'] == 'ITEM_ID') {
				continue;
			}

			// need to provide a proper encoded form if upload encountered, assume upload where file attribute encountered
			if ($item_attribute_type_r['file_attribute_ind'] == 'Y') {
				$upload_file_fields = TRUE;
			}

			if ($op == 'refresh' || $op == 'edit' || $op == 'newinstance') {
				if (is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type']))
					$item_attribute_type_r['attribute_val'] = fetch_attribute_val_r($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
				else
					$item_attribute_type_r['attribute_val'] = fetch_attribute_val($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
			}

			if ($op == 'refresh') {
				$old_value = get_old_field_value($item_r, $item_attribute_type_r['s_field_type'], $item_attribute_type_r['attribute_val']);
				$new_value = get_field_value($op, $item_r, $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no'], $item_attribute_type_r['s_field_type'], $item_attribute_type_r['attribute_val'], $HTTP_VARS);

				// this represents multiple selections for a single value, lookup / multi value attributes will
				// be dealt with together.
				if (is_not_empty_array($new_value) && !is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type'])) {
					foreach ($new_value as $key => $val) {
						$new_value[$key] = filter_item_input_field($item_attribute_type_r, $val);
					}
				} else {
					$new_value = filter_item_input_field($item_attribute_type_r, $new_value);
					// If no HTTP value, especially where FILE UPLOAD is being concerned, attempt to get from database again.
					if (($op == 'edit' || $op == 'newinstance') && $new_value === NULL) {
						$new_value = get_old_field_value($item_r, $item_attribute_type_r['s_field_type'], $item_attribute_type_r['attribute_val']);
					}
				}
			} else { //if($op == 'refresh')
 				$old_value = FALSE;
				$new_value = get_field_value($op, $item_r, $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no'], $item_attribute_type_r['s_field_type'], $item_attribute_type_r['attribute_val'], $HTTP_VARS);

				if (is_not_empty_array($new_value) && !is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type'])) {
					foreach ($new_value as $key => $val) {
						$new_value[$key] = filter_item_input_field($item_attribute_type_r, $val);
					}
				} else {
					$new_value = filter_item_input_field($item_attribute_type_r, $new_value);

					// If no HTTP value, especially where FILE UPLOAD is being concerned, attempt to get from database again.
					if (($op == 'edit' || $op == 'newinstance') && $new_value === NULL) {
						$new_value = get_old_field_value($item_r, $item_attribute_type_r['s_field_type'], $item_attribute_type_r['attribute_val']);
					}
				}
			}

			// Enforce compulsory indicator for TITLE.
			if ($item_attribute_type_r['s_field_type'] == 'TITLE') {
				$item_attribute_type_r['compulsory_ind'] = 'Y';

				$override_title_articles_r = get_opendb_config_var('item_input', 'title_articles');
				if (is_not_empty_array($override_title_articles_r)) {
					if (is_not_empty_array($new_value)) {
						foreach ($new_value as $key => $val) {
							$new_value[$key] = trim(format_title_grammar_article($val, $override_title_articles_r));
						}
					} else {
						$new_value = trim(format_title_grammar_article($new_value, $override_title_articles_r));
					}
				}
			}

			$formContents .= get_item_form_row($op, $item_r, $item_attribute_type_r, $old_value, $new_value);

			if ($item_attribute_type_r['compulsory_ind'] == 'Y') {
				$compulsory_fields = TRUE;
			}
		}//while
		db_free_result($results);

		$formContents .= "\n</table>";

		$help_block_r = NULL;
		if ($op == 'refresh') {
			$help_block_r[] = array('img' => 'rs.gif', 'text' => get_opendb_lang_var('refreshed'));
		}

		$help_block_r[] = array('img' => 'compulsory.gif', 'text' => get_opendb_lang_var('compulsory_field'), 'id' => 'compulsory');

		$formContents .= format_help_block($help_block_r);

		return $formContents;
	} else {
		return FALSE;
	}
}

function get_edit_item_instance_form($op, $item_r, $status_type_r, $HTTP_VARS) {
	$formContents = "<div class=\"tabContentHidden\" id=\"instance_info\">";
	
	$formContents .= get_related_item_search_script();
	
	$results = fetch_item_attribute_type_rs($item_r['s_item_type'], 'instance_field_types');
	if ($results) {
		$formContents .= "<h3>" . get_opendb_lang_var('instance_info') . "</h3>";

		$formContents .= "\n<table>";

		if (($op == 'edit' || $op == 'refresh') && $status_type_r['change_owner_ind'] == 'Y') {
			$formContents .= format_field(get_opendb_lang_var('owner'),
					"\n<select name=\"owner_id\">"
							. custom_select('owner_id', fetch_user_rs(PERM_ITEM_OWNER, INCLUDE_ROLE_PERMISSIONS, INCLUDE_CURRENT_USER, EXCLUDE_DEACTIVATED_USER, 'user_id', 'ASC'), '%fullname% (%user_id%)', 'NA', ifempty($HTTP_VARS['owner_id'], $item_r['owner_id']), 'user_id') . "\n</select>");
		}

		if ($op == 'newinstance' || $op == 'clone_item') {
			$item_r['s_status_type'] = NULL;
			$item_r['status_comment'] = NULL;
			$item_r['borrow_duration'] = NULL;
		}

		while ($item_attribute_type_r = db_fetch_assoc($results)) {
			if ($item_attribute_type_r['s_field_type'] == 'STATUSTYPE') {
				$status_type = ifempty(filter_item_input_field($item_attribute_type_r, $HTTP_VARS['s_status_type'] ?? FALSE), $item_r['s_status_type']);

				if ($op == 'new' || $op == 'site' || $op == 'newinstance' || $op == 'clone_item') {
					$lookup_results = fetch_newitem_status_type_rs();
				} else {
					// If item has borrowed records, then no s_status_type with borrow_ind == 'X' should be included.
					$lookup_results = fetch_update_status_type_rs($status_type);
				}

				if ($lookup_results && db_num_rows($lookup_results) > 0) {
					$formContents .= format_field($item_attribute_type_r['prompt'], radio_grid('s_status_type', $lookup_results, '%img%', // mask
					'VERTICAL', $status_type)); // value
				}
			} else if ($item_attribute_type_r['s_field_type'] == 'STATUSCMNT') {
				$status_comment = ifempty(filter_item_input_field($item_attribute_type_r, $HTTP_VARS['status_comment'] ?? FALSE), $item_r['status_comment']);

				$formContents .= get_item_input_field('status_comment', $item_attribute_type_r, NULL, //$item_r
				$status_comment);
			} else if ($item_attribute_type_r['s_field_type'] == 'DURATION') {
				$borrow_duration = ifempty(filter_item_input_field($item_attribute_type_r, $HTTP_VARS['borrow_duration'] ?? FALSE), $item_r['borrow_duration']);

				// The S_DURATION lookup list will most likely include an 'Undefined' option, that equates
				// to an empty string.  So for Updates, we want to allow for a match, by forcing any NULL
				// value to a empty string.  The reason why we do this, is because the Borrow Duration was
				// probably set to 'Undefined', but because this equated to an empty string, the field was
				// never updated.
				if ($op != 'new' && $op != 'site') {
					if ($borrow_duration === NULL)
						$borrow_duration = '';
				}

				$formContents .= get_item_input_field('borrow_duration', $item_attribute_type_r, NULL, //$item_r
				$borrow_duration);
			}
		}//while
		db_free_result($results);
	
		$formContents .= "\n</table>";
	}
		
	if (get_opendb_config_var('item_input', 'related_item_support') !== FALSE) {
		$formContents .= "<h3>" . get_opendb_lang_var ( 'add_related_parent' ) . "</h3>";
		
       	$formContents .= "\n<table>";
		$formContents .= format_field(get_opendb_lang_var('parent_item_filter'), '<input type="text" name="parent_item_filter" id="parent_item_filter">');
		$formContents .= format_field(get_opendb_lang_var('parent_item'), format_item_parents_select($HTTP_VARS, $item_r, '%parent_only%'));
		$formContents .= "\n</table>";
		
		$relatedItems = get_related_items_listing ( $item_r, $HTTP_VARS, RELATED_PARENTS_MODE );
		if ($relatedItems != NULL) {
			$formContents .= "<h3>" . get_opendb_lang_var('related_parent_item(s)') . "</h3>";
			$formContents .= $relatedItems;
		}
	}
	
	$formContents .= "</div>";

	return $formContents;
}

function get_edit_form($op, $item_r, $status_type_r, $HTTP_VARS) {
	global $PHP_SELF;

	// Work out $op value to submit.
	if ($op == 'edit' || $op == 'refresh' || $op == 'newinstance')
		$op2 = 'update';
	else if ($op == 'new' || $op == 'site' || $op == 'clone_item')
		$op2 = 'insert';
	else
		$op2 = $op; // last resort!

	$formContents = get_edit_item_form($op, $item_r, $HTTP_VARS, $upload_file_fields);
	if ($formContents !== FALSE) {
		$pageContents = '';

		$pageContents .= "<div class=\"tabContainer\">";

		if ($upload_file_fields && is_file_upload_enabled()) {
			$pageContents .= "\n<form name=\"itemInput\" action=\"$PHP_SELF\" method=\"POST\" enctype=\"multipart/form-data\">";
		} else {
			$pageContents .= "\n<form action=\"$PHP_SELF\" method=\"POST\">";
		}

		if (get_opendb_config_var('widgets', 'enable_javascript_validation') !== FALSE)
			$onclick_event = "if(!checkForm(this.form)){return false;}else{this.form.submit();}";
		else
			$onclick_event = "this.form.submit();";

		$pageContents .= "<ul class=\"tabMenu\" id=\"tab-menu\">";
		$pageContents .= "<li id=\"menu-details\" class=\"first activeTab\" onclick=\"return activateTab('details');\">" . get_opendb_lang_var('details') . "</li>";
		$pageContents .= "<li id=\"menu-instance_info\" onclick=\"return activateTab('instance_info');\">" . get_opendb_lang_var('instance_info') . "</li>";
		$pageContents .= "</ul>";

		$pageContents .= "<div id=\"tab-content\">";

		$pageContents .= "<ul class=\"saveButtons\">
				<li><input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"" . get_opendb_lang_var('save_item') . "\"></li>
			</ul>";

		$pageContents .= "<div class=\"tabContent\" id=\"details\">";

		$pageContents .= "\n<input type=\"hidden\" name=\"op\" value=\"$op2\">";
		$pageContents .= "\n<input type=\"hidden\" name=\"start-op\" value=\"$op\">";
		$pageContents .= "\n<input type=\"hidden\" name=\"s_item_type\" value=\"" . $item_r['s_item_type'] . "\">";

		if (strlen( $HTTP_VARS['parent_item_id'] ?? '') > 0)
			$pageContents .= "\n<input type=\"hidden\" name=\"parent_item_id\" value=\"" . $HTTP_VARS['parent_item_id'] . "\">";
		if (strlen( $HTTP_VARS['parent_instance_no'] ?? '') > 0)
			$pageContents .= "\n<input type=\"hidden\" name=\"parent_instance_no\" value=\"" . $HTTP_VARS['parent_instance_no'] . "\">";

		if ($op == 'clone_item' || is_not_empty_array($item_r)) {
			if (is_numeric($item_r['item_id']))
				$pageContents .= "\n<input type=\"hidden\" name=\"item_id\" value=\"" . $item_r['item_id'] . "\">";
			if (is_numeric($item_r['instance_no']))
				$pageContents .= "\n<input type=\"hidden\" name=\"instance_no\" value=\"" . $item_r['instance_no'] . "\">";
		}

		if (strlen($HTTP_VARS['owner_id'] ?? '') > 0)
			$pageContents .= "\n<input type=\"hidden\" name=\"owner_id\" value=\"" . $HTTP_VARS['owner_id'] . "\">";

		$pageContents .= $formContents;

		$action_links_rs = NULL;

		$pageContents .= "<ul class=\"saveButtons\">
		<li><input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"" . get_opendb_lang_var('save_item') . "\"></li>
			</ul>";
		
		if (is_not_empty_array($action_links_rs)) {
			$pageContents .= format_footer_links($action_links_rs);
		}

		$pageContents .= "</div>";

		$pageContents .= get_edit_item_instance_form($op, $item_r, $status_type_r, $HTTP_VARS);

		$pageContents .= "</div>";

		$pageContents .= "</form>";

		$pageContents .= "</div>";

		return $pageContents;
	} else {
		return FALSE;
	}
}

function handle_edit_or_refresh($op, $item_r, $status_type_r, $HTTP_VARS, &$errors) {
	if ((is_user_granted_permission(PERM_ITEM_OWNER) && $item_r['owner_id'] == get_opendb_session_var('user_id')) || is_user_granted_permission(PERM_ITEM_ADMIN)) {
		$formContents = get_edit_form($op, $item_r, $status_type_r, $HTTP_VARS);
		if ($formContents != FALSE)
			return $formContents;
		else {
			$errors = array('error' => get_opendb_lang_var('undefined_error'), 'detail' => '');
			return FALSE;
		}
	} else {
		$errors = array('error' => get_opendb_lang_var('cannot_edit_item_not_owned'), 'detail' => '');

		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to edit item instance they do not own', $item_r);
		return FALSE;
	}
}

function handle_new_or_site($op, $item_r, $status_type_r, $HTTP_VARS, &$errors) {
	if ((is_user_granted_permission(PERM_ITEM_OWNER) && $item_r['owner_id'] == get_opendb_session_var('user_id')) || is_user_granted_permission(PERM_ITEM_ADMIN)) {
		if (is_valid_item_type_structure($item_r['s_item_type'])) {
			$formContents = get_edit_form($op, $item_r, $status_type_r, $HTTP_VARS);
			if ($formContents != FALSE)
				return $formContents;
			else {
				$errors = array('error' => get_opendb_lang_var('undefined_error'), 'detail' => '');
				return FALSE;
			}
		} else {
			$errors = array('error' => get_opendb_lang_var('invalid_item_type_structure', 's_item_type', $item_r['s_item_type']), 'detail' => '');
			return FALSE;
		}
	} else {
		$errors = array('error' => get_opendb_lang_var('operation_not_available'));

		opendb_logger(OPENDB_LOG_WARN, __FILE__, __FUNCTION__, 'User attempted to insert an item for another user', $item_r);
		return FALSE;
	}
}

function handle_site_search(&$sitePlugin, $HTTP_VARS, &$errors, &$footer_links_r) {
	global $PHP_SELF;

	$HTTP_VARS['op'] = 'site';

	$formContents = '<div id="site-search">';

	if ($sitePlugin->_queryListing($HTTP_VARS) !== FALSE) {
		$searchQuery = $sitePlugin->getSearchQuery();
		if (is_not_empty_array($searchQuery)) {
			$formContents .= "<h3>" . get_opendb_lang_var('site_search_results', array('site_title' => $sitePlugin->getTitle())) . "</h3>";

			$formContents .= '<div class="search-query"><dl>';
			for ($i = 0; $i < count($searchQuery); $i++) {
				if ($searchQuery[$i]['field_type'] != 'hidden') {
					$formContents .= '<dt>' . $searchQuery[$i]['prompt'] . "</dt>";
					$formContents .= '<dd>' . htmlspecialchars($searchQuery[$i]['value']) . '</dd>';
				}
			}
			$formContents .= '</dl></div>';
		}

		if ($sitePlugin->getRowCount() > 0) {
			// exact title match.
			if ($sitePlugin->getRowCount() == 1 && $sitePlugin->isPreviousPage() === FALSE) {
				// the site plugin process will have already queried for the itemData
				// based on the single row returned.
				return "__EXACT_TITLE_MATCH__";
			} else {
				$formContents .= "\n<table class=\"listing-table\">";
				$class = 'oddRow';

				for ($i = 0; $i < $sitePlugin->getRowCount(); $i++) {
					$formContents .= "\n<tr class=\"$class\">";

					$row_data_r = $sitePlugin->getRowData($i);

					$file_r = file_cache_get_image_r($row_data_r['cover_image_url'], 'site-add');
					if (is_not_empty_array($file_r)) {
						$imageblock = "<img src=\"" . $file_r['thumbnail']['url'] . "\" ";

						if (is_numeric($file_r['thumbnail']['width']))
							$imageblock .= ' width="' . $file_r['thumbnail']['width'] . '"';
						if (is_numeric($file_r['thumbnail']['height']))
							$imageblock .= ' height="' . $file_r['thumbnail']['height'] . '"';

						$imageblock .= ">";
					}

					$formContents .= "\n<td class=\"image\">" . $imageblock . "</td>";

					$formContents .= "\n<td class=\"title\">";

					$formContents .= "\n<a href=\"" . "item_input.php?" . $row_data_r['opendb_link_url'] . "\">" . $row_data_r['title'] . "</a>";

					if (strlen($row_data_r['comments']) > 0) {
						$formContents .= '<p class=\"comments\">' . nl2br($row_data_r['comments']) . '</p>';
					}
					$formContents .= "\n</td>";

					if (strlen($row_data_r['more_info_url']) > 0) {
						$formContents .= "\n<td class=\"moreInfo\"><a href=\"" . $row_data_r['more_info_url'] . "\" target=\"_new\">" . get_opendb_lang_var('more_info') . "</a></td>";
					}

					$formContents .= "\n</tr>";

					$class = ($class == 'oddRow' ? 'evenRow' : 'oddRow');
				}
				$formContents .= "\n</table>";

				unset($HTTP_VARS['page_no']);

				if ($sitePlugin->isPreviousPage() || $sitePlugin->isNextPage()) {
					if ($sitePlugin->isPreviousPage() || $sitePlugin->isNextPage()) {
						$HTTP_VARS['op'] = 'site-search';
						$page_nav_url = get_url_string($HTTP_VARS);

						$formContents .= "<ul class=\"listingPager\">";

						if ($sitePlugin->isPreviousPage())
							$formContents .= "<li class=\"previousPage\"><a href=\"item_input.php?$page_nav_url&page_no=" . ($sitePlugin->getPageNo() - 1) . "\">" . get_opendb_lang_var('previous_page') . "</a></li>";
						else
							$formContents .= "<li class=\"previousPage disabled\">" . get_opendb_lang_var('previous_page') . "</a></li>";

						if ($sitePlugin->isNextPage())
							$formContents .= "<li class=\"nextPage\"><a href=\"item_input.php?$page_nav_url&page_no=" . ($sitePlugin->getPageNo() + 1) . "\">" . get_opendb_lang_var('next_page') . "</a></li>";
						else
							$formContents .= "<li class=\"nextPage disabled\">" . get_opendb_lang_var('next_page') . "</a></li>";

						$formContents .= "</ul>";
					}
				}
			}
		} else {
			$formContents .= "<p class=\"error\">" . get_opendb_lang_var('no_matches_found') . "</p>";
		}

		$http_url_vars = get_url_string($HTTP_VARS, NULL, array('op', 'site_type', 's_item_type'));

		// either site-add / site-refresh
		if (is_exists_item_instance($HTTP_VARS['item_id'], $HTTP_VARS['instance']))
			$footer_links_r[] = array('url' => "item_input.php?op=site-refresh" . (strlen($http_url_vars) > 0 ? "&" . $http_url_vars : ""), 'text' => get_opendb_lang_var('new_search'));
		else
			$footer_links_r[] = array('url' => "item_input.php?op=site-add" . (strlen($http_url_vars) > 0 ? "&" . $http_url_vars : ""), 'text' => get_opendb_lang_var('new_search'));

		$formContents .= "</div>";

		return $formContents;

	} else {
		$errors = $sitePlugin->getErrors();

		if ($errors === FALSE)
			$errors = get_opendb_lang_var('undefined_error');

		return FALSE;
	}
}

/**
 * Will work out based on the $op what the title should be.  It will
 * return a complete heading, including calling the _theme_header
 * and everything.
 */
function do_op_title($item_r, $status_type_r, $op) {
	global $titleMaskCfg;
	global $HTTP_VARS; // hack

	if ($op == 'new' || $op == 'site' || $op == 'site-search' || $op == 'insert') {
		if ($item_r['owner_id'] != get_opendb_session_var('user_id'))
			$item_title = get_opendb_lang_var('add_new_item_for_name', array('user_id' => $item_r['owner_id'], 'fullname' => fetch_user_name($item_r['owner_id'])));
		else
			$item_title = get_opendb_lang_var('add_new_item');
	} else if ($op == 'update' || $op == 'delete' || $op == 'delete_related') {
		$item_title = get_opendb_lang_var($op . '_item');
	} else if ($op == 'refresh' || $op == 'edit' || $op == 'clone_item') {
		if ($op == 'clone_item')
			$op = 'clone';

		$item_title = get_opendb_lang_var($op . '_title', array('display_title' => $titleMaskCfg->expand_item_title($item_r)));
	} else if ($op == 'newinstance') {
		// temporarily remove instance_no so that title renders correctly.
		$item_r['instance_no'] = NULL;

		$item_title = get_opendb_lang_var('new_item_instance_title', array('display_title' => $titleMaskCfg->expand_item_title($item_r)));
	}

	echo _theme_header($item_title, $HTTP_VARS['inc_menu'] ?? NULL);
	echo ("<h2>" . $item_title . " " . get_item_image($item_r['s_item_type']) . "</h2>\n");
}

function perform_insert_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
	global $PHP_SELF;

	$errors = NULL;

	$return_val = TRUE;

	$op = NULL;

	$return_val = handle_item_insert($item_r, $HTTP_VARS, $errors);
	if ($return_val === TRUE) {
		$return_val = handle_item_instance_insert($item_r, $status_type_r, $HTTP_VARS, $errors);
		if ($return_val !== FALSE) {
			if (get_opendb_config_var('item_input', 'related_item_support') !== FALSE) {
				if (is_numeric($HTTP_VARS['parent_item_id']) && is_numeric($HTTP_VARS['parent_instance_no']) && is_exists_item_instance($HTTP_VARS['parent_item_id'], $HTTP_VARS['parent_instance_no'])) {
					// fixme, this creates a relationship for all item instances, is that what we want?
					insert_item_instance_relationships($HTTP_VARS['parent_item_id'], $item_r['item_id'], $item_r['instance_no']);
				} elseif (!empty($HTTP_VARS['parent_item'])) {
                    $parent_item_r = get_item_id_and_instance_no($HTTP_VARS['parent_item']);

                    if (is_exists_item_instance($parent_item_r['item_id'], $parent_item_r['instance_no'])) {
                        insert_item_instance_relationship($parent_item_r['item_id'], $parent_item_r['instance_no'],
                            $item_r['item_id'], $item_r['instance_no']);
                    }
                }
			}
		}
	}

	// this will only occur for new item insert
	if ($return_val === "__CONFIRM_EXISTS_TITLE__" || $return_val === "__CONFIRM_EXISTS_OWNER_TITLE__") {
		if ($return_val === "__CONFIRM_EXISTS_OWNER_TITLE__") {
			$message_lang_var = 'confirm_title_same_type_and_owner_insert';
			$footer_links_r[] = array('url' => "listings.php?search_list=y&inc_menu=N&owner_id=" . $item_r['owner_id'] . "&title=" . urlencode($item_r['title']) . "&title_match=exact&s_item_type=" . $item_r['s_item_type'], 'target' => '_new', 'text' => get_opendb_lang_var('list_duplicate_title(s)'));
		} else if ($return_val === "__CONFIRM_EXISTS_TITLE__") {
			$message_lang_var = 'confirm_title_same_type_insert';
			$footer_links_r[] = array('url' => "listings.php?search_list=y&inc_menu=N&title=" . urlencode($item_r['title']) . "&title_match=exact&s_item_type=" . $item_r['s_item_type'], 'target' => '_new', 'text' => get_opendb_lang_var('list_duplicate_title(s)'));
		}

		do_op_title($item_r, $status_type_r, $HTTP_VARS['start-op'] == 'clone_item' ? 'clone_item' : 'insert');

		echo get_op_confirm_form($PHP_SELF, get_opendb_lang_var($message_lang_var, array('title' => $item_r['title'], 's_item_type' => $item_r['s_item_type'])), $HTTP_VARS);
	} else {
		if ($return_val === "__INVALID_DATA__") {
			do_op_title($item_r, $status_type_r, $HTTP_VARS['start-op'] == 'clone_item' ? 'clone_item' : 'insert');

			echo format_error_block($errors);

			$HTTP_VARS['op'] = 'new';
			$formContents = handle_new_or_site($HTTP_VARS['op'], $item_r, $status_type_r, $HTTP_VARS, $errors);
			if ($formContents !== FALSE)
				echo $formContents;
			else
				echo format_error_block($errors);
		} else if ($return_val === "__ABORTED__") {
			if ($HTTP_VARS['start-op'] == 'clone_item') {
				do_op_title($item_r, $status_type_r, 'clone_item');
				echo ("<p class=\"success\">" . get_opendb_lang_var('item_not_cloned') . "</p>");
			} else {
				do_op_title($item_r, $status_type_r, 'insert');
				echo ("<p class=\"success\">" . get_opendb_lang_var('item_not_added') . "</p>");
			}
		} else if ($return_val === TRUE) {
			if ($HTTP_VARS['start-op'] == 'clone_item') {
				do_op_title($item_r, $status_type_r, 'clone_item');
				echo ("<p class=\"success\">" . get_opendb_lang_var('item_cloned') . "</p>");
			} else {
				do_op_title($item_r, $status_type_r, 'insert');
				echo ("<p class=\"success\">" . get_opendb_lang_var('item_added') . "</p>");
			}

			echo format_error_block($errors, 'warning');

			$footer_links_r[] = array('url' => "item_display.php?item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
			$footer_links_r[] = array('url' => "item_input.php?op=site-add&owner_id=" . $item_r['owner_id'], 'text' => get_opendb_lang_var('add_new_item'));
		} else {
			do_op_title($item_r, $status_type_r, $HTTP_VARS['start-op'] == 'clone_item' ? 'clone_item' : 'insert');
			echo format_error_block($errors);
		}
	}
}

function perform_update_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
	global $PHP_SELF;

	do_op_title($item_r, $status_type_r, $HTTP_VARS['start-op'] == 'newinstance' ? 'newinstance' : 'update');

	$errors = NULL;

	$return_val = FALSE;

	if ($HTTP_VARS['start-op'] == 'newinstance') {
		$item_r['instance_no'] = NULL;
		$return_val = handle_item_instance_insert($item_r, $status_type_r, $HTTP_VARS, $errors);
	} else {
		$return_val = handle_item_instance_update($item_r, $status_type_r, $HTTP_VARS, $errors);
	}

	if ($return_val === TRUE) {
		$return_val = handle_item_update($item_r, $HTTP_VARS, $errors);

        if (get_opendb_config_var('item_input', 'related_item_support') !== FALSE) {
        	if (!empty($HTTP_VARS['parent_item'])) {
        		$parent_item_r = get_item_id_and_instance_no($HTTP_VARS['parent_item']);
        		
        		if (is_exists_item_instance($parent_item_r['item_id'], $parent_item_r['instance_no'])
						&& !is_exists_related_item_instance_relationship($item_r['item_id'], $item_r['instance_no'], 
								$parent_item_r['item_id'], $parent_item_r['instance_no'])) {

        			insert_item_instance_relationship($parent_item_r['item_id'], $parent_item_r['instance_no'], 
        								$item_r['item_id'], $item_r['instance_no']);
        		}
        	}
        }
	}

	if ($return_val === "__INVALID_DATA__") {
		echo format_error_block($errors);

		$HTTP_VARS['op'] = 'edit';

		$formContents = handle_edit_or_refresh($HTTP_VARS['op'], $item_r, $status_type_r, $HTTP_VARS, $errors);
		if ($formContents !== FALSE) {
			echo $formContents;
		} else {
			echo format_error_block($errors);
		}
	} else if ($return_val === TRUE) {
		if ($HTTP_VARS['start-op'] == 'newinstance')
			echo ("<p class=\"success\">" . get_opendb_lang_var('item_instance_added') . "</p>");
		else
			echo ("<p class=\"success\">" . get_opendb_lang_var('item_updated') . "</p>");

		echo format_error_block($errors, 'warning');

		$footer_links_r[] = array('url' => "item_display.php?item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
	} else {
		echo format_error_block($errors);
	}
}

function perform_cloneitem_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
	global $PHP_SELF;

	if (!is_exists_item_type($HTTP_VARS['s_item_type'])) {
		do_op_title($item_r, $status_type_r, 'clone_item');

		echo ('<form action="' . $PHP_SELF . '">');
		echo ('<input type="hidden" name="op" value="clone_item">');
		echo ('<input type="hidden" name="item_id" value="' . $item_r['item_id'] . '">');
		echo ('<input type="hidden" name="instance_no" value="' . $item_r['instance_no'] . '">');

		echo ('<table class="cloneItemForm">');
		echo ('<tr><th class="prompt">' . get_opendb_lang_var('item_type') . '</th>' . '<td class="data">'
				. custom_select('s_item_type', fetch_item_type_rs(), '%s_item_type% - %description%', '1', $item_r['s_item_type'], 's_item_type', NULL, NULL, NULL, FALSE, 'clone-s_item_type') . '</td></tr>');

		echo ('</table>');
		echo ('<input type="submit" class="submit" value="' . get_opendb_lang_var('submit') . '">');

		echo ('</form>');

		echo format_help_block(get_opendb_lang_var('clone_item_help'));

		$footer_links_r[] = array('url' => "item_display.php?item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
	} else {
		// at this point we need to populate $HTTP_VARS with all data corresponding to a mapping between the old and possible new item type
		$HTTP_VARS = array_merge($HTTP_VARS, copy_item_to_http_vars($item_r, $HTTP_VARS['s_item_type']));

		// insert item for item type as specified in previous dialog
		$item_r['s_item_type'] = $HTTP_VARS['s_item_type'];

		do_op_title($item_r, $status_type_r, 'clone_item');

		$item_r['title'] = NULL;

		// fix for clone reverting to source item on insert.
		$item_r['item_id'] = NULL;
		$item_r['instance_no'] = NULL;

		$errors = NULL;
		$formContents = handle_new_or_site($HTTP_VARS['op'], $item_r, $status_type_r, $HTTP_VARS, $errors);
		if ($formContents !== FALSE)
			echo $formContents;
		else {
			echo format_error_block($errors);
		}
	}
}

function perform_delete_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
	global $PHP_SELF;
	global $titleMaskCfg;

	do_op_title($item_r, $status_type_r, 'delete');

	$errors = NULL;
	$return_val = handle_item_delete($item_r, $status_type_r, $HTTP_VARS, $errors);
	if ($return_val === "__CONFIRM__") {
		echo (get_op_confirm_form($PHP_SELF, get_opendb_lang_var('confirm_delete_title', 'display_title', $titleMaskCfg->expand_item_title($item_r)), $HTTP_VARS));
	} else if ($return_val === "__CONFIRM_INACTIVE_BORROW__") {
		echo (get_op_confirm_form($PHP_SELF, get_opendb_lang_var('confirm_delete_inactive_borrowed_items_and_title', 'display_title', $titleMaskCfg->expand_item_title($item_r)), $HTTP_VARS));
	} else {
		if ($return_val === "__ABORTED__") {
			echo ("<p class=\"success\">" . get_opendb_lang_var('item_not_deleted') . "</p>");
			$footer_links_r[] = array('url' => "item_display.php?item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
		} else if ($return_val === FALSE) {
			echo format_error_block($errors);
			$footer_links_r[] = array('url' => "item_display.php?item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
		} else {
			echo ("<p class=\"success\">" . get_opendb_lang_var('item_deleted') . "</p>");
		}
	}
}

function perform_new_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
	global $PHP_SELF;

	do_op_title($item_r, $status_type_r, $HTTP_VARS['op'] == 'clone_item' ? 'clone_item' : 'new');

	$errors = NULL;
	$formContents = handle_new_or_site($HTTP_VARS['op'], $item_r, $status_type_r, $HTTP_VARS, $errors);
	if ($formContents !== FALSE) {
		echo $formContents;
	} else {
		echo format_error_block($errors);
	}
}

function perform_edit_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
	global $PHP_SELF;

	do_op_title($item_r, $status_type_r, $HTTP_VARS['op'] == 'newinstance' ? 'newinstance' : 'edit');

	$errors = NULL;
	$formContents = handle_edit_or_refresh($HTTP_VARS['op'], $item_r, $status_type_r, $HTTP_VARS, $errors);
	if ($formContents !== FALSE) {
		echo $formContents;
	} else {
		echo format_error_block($errors);
	}
}

function perform_delete_relation_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
    global $PHP_SELF;
    global $titleMaskCfg;

    $parent_item_r = fetch_item_r($HTTP_VARS['parent_item_id']);
    do_op_title($parent_item_r, $status_type_r, 'delete_related');

    $errors = null;
    $return_val = handle_item_relation_delete($item_r, $status_type_r, $HTTP_VARS, $errors);
    if ($return_val === "__CONFIRM__") {
        echo (get_op_confirm_form($PHP_SELF, get_opendb_lang_var('confirm_delete_relation_title', 'display_title', $titleMaskCfg->expand_item_title($parent_item_r)), $HTTP_VARS));
    } else {
        if ($return_val == "__ABORTED__") {
            echo ("<p class=\"success\">" . get_opendb_lang_var('item_relation_not_deleted') . "</p>");
            $footer_links_r[] = array('url' => "item_input.php?op=edit&item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
        } else if ($return_val === FALSE) {
            echo format_error_block($errors);
            $footer_links_r[] = array('url' => "item_input.php?op=edit&item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
        } else {
            echo ("<p class=\"success\">" . get_opendb_lang_var('item_relation_deleted') . "</p>");
            $footer_links_r[] = array('url' => "item_input.php?op=edit&item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('back_to_item'));
        }
    }
}

function perform_site_process(&$item_r, &$status_type_r, &$HTTP_VARS, &$footer_links_r) {
	$sitePlugin = &get_site_plugin_instance($HTTP_VARS['site_type']);
	if ($sitePlugin !== FALSE) {
		if ($HTTP_VARS['op'] == 'site-search') {
			$return_val = handle_site_search($sitePlugin, $HTTP_VARS, $errors, $footer_links_r);

			if ($return_val === "__EXACT_TITLE_MATCH__") {
				// do nothing - we have an exact match, so shall fall down
				// to do site / refresh operation instead.
			} else if ($return_val !== FALSE) {
				// display search page and break out.
				do_op_title($item_r, $status_type_r, 'site-search');

				echo $return_val;

				if (get_opendb_config_var('item_input.site', 'debug') === TRUE) {
					echo $sitePlugin->getDebugItemDataAsHtml();
				}

				return;
			} else { // $return_val === FALSE)
 				do_op_title($item_r, $status_type_r, 'site-search');

				echo format_error_block($errors);

				if (get_opendb_config_var('item_input.site', 'debug') === TRUE) {
					echo $sitePlugin->getDebugItemDataAsHtml();
				}

				return;
			}
		} else { //if($HTTP_VARS['op'] == 'site-search')
 			if ($sitePlugin->_queryItem($HTTP_VARS) !== TRUE) {
				// display error
				do_op_title($item_r, $status_type_r, 'new');

				$errors = $sitePlugin->getErrors();

				// we need to provide at least some indication of why there was a problem.
				if ($errors === FALSE)
					$errors = get_opendb_lang_var('undefined_error');

				echo format_error_block($errors);

				if (get_opendb_config_var('item_input.site', 'debug') === TRUE) {
					echo $sitePlugin->getDebugItemDataAsHtml();
				}

				return;
			}
		}

		// at this point we have an exact match!
		$site_item_attributes_r = $sitePlugin->getItemData($item_r['s_item_type']);
		if (is_not_empty_array($site_item_attributes_r)) {
			$HTTP_VARS = array_merge($HTTP_VARS, $site_item_attributes_r);
			if (is_exists_item($item_r['item_id'])) {
				$HTTP_VARS['op'] = 'refresh';

				$titleArticlesConfig = $sitePlugin->getConfigValue('item_input.title_articles');
				if (is_not_empty_array($titleArticlesConfig))
					set_opendb_config_ovrd_var('item_input', 'title_articles', $titleArticlesConfig);

				if (get_opendb_config_var('item_input', 'auto_site_update') === TRUE) {
					// expand $HTTP_VARS to bypass edit form.
					$HTTP_VARS = get_site_item_input_data($HTTP_VARS['op'], $item_r, $HTTP_VARS);

					perform_update_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
				} else {
					perform_edit_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
				}
			} else { //if(is_exists_item($item_r['item_id']))
 				$HTTP_VARS['op'] = 'site';

				if (get_opendb_config_var('item_input', 'auto_site_insert') === TRUE) {
					$item_r['s_status_type'] = fetch_default_status_type();

					// expand $HTTP_VARS to bypass edit form.
					$HTTP_VARS = get_site_item_input_data($HTTP_VARS['op'], $item_r, $HTTP_VARS);

					perform_insert_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
				} else {
					perform_new_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
				}
			}

			if (get_opendb_config_var('item_input.site', 'debug') === TRUE) {
				echo $sitePlugin->getDebugItemDataAsHtml();
			}
		} else {
			// no info found - drop down to new operation.
			$HTTP_VARS['op'] = 'new';
			perform_new_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
		}
	} else { //if($sitePlugin !== FALSE)
 		do_op_title($item_r, $status_type_r, $HTTP_VARS['op']);

		echo format_error_block(get_opendb_lang_var('undefined_error'));

		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Site plugin class not found', $HTTP_VARS);
	}
}

// *****************************************************************************
// MAIN PROCESS
// *****************************************************************************
if (is_site_enabled()) {
	@set_time_limit(600);

	if (is_opendb_valid_session()) {
		if (is_user_granted_permission(PERM_ITEM_OWNER) || is_user_granted_permission(PERM_ITEM_ADMIN)) {
			if (empty($HTTP_VARS['ajax_op'])) {
				// For a $op == ('site' OR 'site-search' OR 'site-add') where an item is actually defined,
				// it is really a 'refresh' site operation.
				if ( $HTTP_VARS['op'] == 'new' ||
					 $HTTP_VARS['op'] == 'insert' ||
					 ( ( $HTTP_VARS['op'] == 'site-search' ||
						 $HTTP_VARS['op'] == 'site-add' ||
						 $HTTP_VARS['op'] == 'site-refresh' ||
						 $HTTP_VARS['op'] == 'site') &&
					   !is_exists_item($HTTP_VARS['item_id'] ?? NULL))) {

					if (strlen($HTTP_VARS['s_status_type'] ?? "") == 0) {
						$status_attr_type_r = fetch_sfieldtype_item_attribute_type_r($HTTP_VARS['s_item_type'] ?? "", 'STATUSTYPE');
						$HTTP_VARS['s_status_type'] = $HTTP_VARS[get_field_name($status_attr_type_r['s_attribute_type'], $status_attr_type_r['order_no'])] ?? "";
					}
	
					if (strlen($HTTP_VARS['s_status_type']) > 0) {
						$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);
					} else {
						// Dummy array entry, as the s_status_type will be chosen in the edit form.
						$status_type_r = array('borrow_ind' => 'Y', 's_status_type' => '');
					}
	
					// where we are making a copy of an existing item
					if (is_exists_item($HTTP_VARS['item_id'] ?? NULL)) {
						$item_r = fetch_item_r($HTTP_VARS['item_id']);
					} else {
						$item_r = array('item_id' => NULL, 'title' => NULL, 's_item_type' => trim($HTTP_VARS['s_item_type'] ?? ""));
					}
	
					$item_r['instance_no'] = NULL; // if a new copy / clone let insert process work out next instance no
					$item_r['owner_id'] = ifempty($HTTP_VARS['owner_id'], get_opendb_session_var('user_id'));
					$item_r['s_status_type'] = $status_type_r['s_status_type'];
					$item_r['status_comment'] = NULL;

				} else { //otherwise either a site refresh operation or an edit/update/delete
	 				$item_r = fetch_item_instance_r($HTTP_VARS['item_id'], $HTTP_VARS['instance_no']);
	
					// a new copy should be for the current user
					if ($HTTP_VARS['op'] == 'newinstance')
						$item_r['owner_id'] = get_opendb_session_var('user_id');
	
					$status_type_r = fetch_status_type_r($item_r['s_status_type']);
				}
	
				// Includes 'new' because we artificially construct an $item_r array.
				if (is_not_empty_array($item_r)) {
					// We need a valid $status_type_r as well at this point, and should not continue without it.
					if (is_not_empty_array($status_type_r)) {
						$footer_links_r = NULL;
	
						// construct single instance of this object to use throughout the script.
						$titleMaskCfg = new TitleMask('item_display');
	
						switch ($HTTP_VARS['op']) {
						case 'insert':
							perform_insert_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'delete':
							perform_delete_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'update':
							perform_update_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'newinstance':
							perform_edit_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'clone_item':
							perform_cloneitem_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'new':
							perform_new_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'edit':
							perform_edit_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
	                    case 'delete-relation':
	                        perform_delete_relation_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
	                        break;
	
						case 'site-add':
							handle_site_add_or_refresh(NULL, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'site-refresh':
							handle_site_add_or_refresh($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						case 'site-search':
						case 'site':
							perform_site_process($item_r, $status_type_r, $HTTP_VARS, $footer_links_r);
							break;
	
						default:
							echo _theme_header(get_opendb_lang_var('operation_not_available'));
							echo format_error_block(get_opendb_lang_var('operation_not_available'));
						}
					} else { //if(is_not_empty_array($status_type_r))
	 					$page_title = get_opendb_lang_var('invalid_s_status_type', 's_status_type', ifempty($item_r['s_status_type'], $HTTP_VARS['s_status_type']));
						echo _theme_header($page_title);
						echo format_error_block($page_title);
					}
				} else { //if(is_not_empty_array($item_r))
	 				echo _theme_header(get_opendb_lang_var('item_not_found'));
					echo format_error_block(get_opendb_lang_var('item_not_found'));
				}
	
				if (is_opendb_session_var('listing_url_vars')) {
					$footer_links_r[] = array('url' => "listings.php?" . get_url_string(get_opendb_session_var('listing_url_vars')), 'text' => get_opendb_lang_var('back_to_listing'));
				}
	
				echo format_footer_links($footer_links_r);
				echo _theme_footer();
			} else {
				switch ($HTTP_VARS['ajax_op']) {
					case 'possible-parents':
						// Get HTML select list of possible item parents.
						if (is_user_granted_permission(PERM_ITEM_OWNER) || is_user_granted_permission(PERM_ITEM_ADMIN)) {
							echo json_encode(
									array('select' => format_item_parents_select(
											$HTTP_VARS, fetch_item_r($HTTP_VARS['item_id']), $HTTP_VARS['parent_item_filter'])));
						}
						break;
					default:
						// invalid operation.
						echo json_encode(array('error' => get_opendb_lang_var('operation_not_available')));
						break;
				}
			}
		} else {
			if (empty($HTTP_VARS['ajax_op'])) {
				opendb_not_authorised_page(array(PERM_ITEM_OWNER, PERM_ITEM_ADMIN), $HTTP_VARS);
			} else {
				echo json_encode(array('error' => get_opendb_lang_var('not_authorized_to_page')));
			}
		}
	} else {
		if (empty($HTTP_VARS['ajax_op'])) {
			// invalid login, so login instead.
			redirect_login($PHP_SELF, $HTTP_VARS);
		} else {
			echo json_encode(array('error' => get_opendb_lang_var('login_failure')));
		}
	}
} else { //if(is_site_enabled())
	if (empty($HTTP_VARS['ajax_op'])) {
		opendb_site_disabled();
	} else {
		echo json_encode(array('error' => get_opendb_lang_var('site_is_disabled')));
	}
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
