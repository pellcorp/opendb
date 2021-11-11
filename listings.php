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
include_once("./lib/user.php");
include_once("./lib/interest.php");
include_once("./lib/review.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/item_type.php");
include_once("./lib/item_type_group.php");
include_once("./lib/item.php");
include_once("./lib/item_attribute.php");
include_once("./lib/widgets.php");
include_once("./lib/http.php");
include_once("./lib/parseutils.php");
include_once("./lib/listutils.php");
include_once("./lib/item_listing_conf.php");
include_once("./lib/status_type.php");
include_once("./lib/HTML_Listing.class.php");

include_once("./lib/xajax/xajax_core/xajax.inc.php");

function getListingFiltersBlock() {
	global $PHP_SELF;
	global $HTTP_VARS;

	$buffer = '';

	if (($HTTP_VARS['listings.filters'] ?? '') != 'N' && get_opendb_config_var('listings.filters', 'enable') !== FALSE) {
		$excluded_vars_list = NULL;

		$buffer .= "<div id=\"listing-filters\" class=\"menuContainer toggleContainer\">";
		$buffer .= "<span id=\"listing-filters-toggle\" class=\"menuToggle toggleHidden\" onclick=\"return toggleVisible('listing-filters');\">" . get_opendb_lang_var('listing_filters') . "</span>";
		$buffer .= "<div id=\"listing-filters-content\" class=\"menuContent elementHidden\"\">";
		$buffer .= "<h2 class=\"menu\">" . get_opendb_lang_var('listing_filters') . "</h2>";
		$buffer .= "<form name=\"listing-filters\" action=\"$PHP_SELF\" method=\"GET\">";

		$buffer .= "<ul>";
		if (get_opendb_config_var('listings.filters', 'show_owner_lov') !== FALSE) {
			$excluded_vars_list[] = 'owner_id';

			$buffer .= "<li><label for=\"select-owner_id\">" . get_opendb_lang_var('owner_id') . "</label>
				<select id=\"select-owner_id\" name=\"owner_id\">
				<option value=\"\"></option>" . custom_select('owner_id', fetch_user_rs(PERM_ITEM_OWNER), '%fullname% (%user_id%)', 'NA', $HTTP_VARS['owner_id'], 'user_id') . "\n</select></li>";
		}

		if (get_opendb_config_var('listings.filters', 'show_s_status_type_lov') !== FALSE) {
			if (!is_array($HTTP_VARS['s_status_type']) || ($HTTP_VARS['search_list'] != 'y' && $HTTP_VARS['attribute_list'] != 'y')) {
				$results = fetch_status_type_rs();
				if ($results && db_num_rows($results) > 1) {
					$excluded_vars_list[] = 's_status_type';

					$buffer .= "<li><label for=\"select-s_status_type\">" . get_opendb_lang_var('s_status_type') . "</label>
						<select id=\"select-s_status_type\" name=\"s_status_type\">
						<option value=\"\"></option>" . custom_select('owner_id', $results, '%s_status_type% - %description%', 'NA', $HTTP_VARS['s_status_type'], 's_status_type') . "\n</select></li>";
				}
			}
		}

		if (get_opendb_config_var('listings.filters', 'show_item_type_group_lov') !== FALSE) {
			$v_item_type_groups = get_list_item_type_groups();
			if (is_not_empty_array($v_item_type_groups)) {
				$excluded_vars_list[] = 's_item_type_group';

				$buffer .= "<li><label for=\"select-s_item_type_group\">" . get_opendb_lang_var('s_item_type_group') . "</label>
					<select id=\"select-s_item_type_group\" name=\"s_item_type_group\">
					<option value=\"\"></option>" . custom_select('s_item_type_group', $v_item_type_groups, '%value% - %display%', 'NA', $HTTP_VARS['s_item_type_group'], 'value') . "\n</select></li>";
			}
		}

		if (get_opendb_config_var('listings.filters', 'show_item_type_lov') !== FALSE) {
			$v_item_types = get_list_item_types(NULL);
			if (is_not_empty_array($v_item_type_groups)) {
				$excluded_vars_list[] = 's_item_type';

				$buffer .= "<li><label for=\"select-s_item_type\">" . get_opendb_lang_var('s_item_type') . "</label>
					<select id=\"select-s_item_type\" name=\"s_item_type\">
					<option value=\"\"></option>" . custom_select('s_item_type', $v_item_types, '%value% - %display%', 'NA', $HTTP_VARS['s_item_type'], 'value') . "\n</select></li>";
			}
		}

		if (get_opendb_config_var('listings.filters', 'show_interest') !== FALSE) {
			$buffer .= "<li><label for=\"select-interest\">" . get_opendb_lang_var('interest_only_marked') . "</label>" . "<input type=\"checkbox\" class=\"checkbox\" id=\"select-interest\" name=\"interest_level\" value=\"1\"" . ($HTTP_VARS['interest_level'] >= 1 ? ' CHECKED' : '') . "></li>";

			$excluded_vars_list[] = 'interest_level';
		}

		if ($HTTP_VARS['owner_id'] != get_opendb_session_var('user_id')) {
			$buffer .= "<li><label for=\"exclude-current-user\">" . get_opendb_lang_var('exclude_current_user') . "</label>" . "<input type=\"checkbox\" class=\"checkbox\" id=\"exclude-current-user\" name=\"not_owner_id\" value=\"" . get_opendb_session_var('user_id') . "\""
					. ($HTTP_VARS['not_owner_id'] == get_opendb_session_var('user_id') ? ' CHECKED' : '') . "></li>";

			$excluded_vars_list[] = 'not_owner_id';
		}

		$buffer .= "</ul>";
		$buffer .= get_url_fields($HTTP_VARS, NULL, $excluded_vars_list);
		$buffer .= "<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('submit') . "\">";
		$buffer .= "</form>";
		$buffer .= "</div>";
		$buffer .= "</div>";
	}

	return $buffer;
}

function fetch_cached_attribute_type_r($s_attribute_type) {
	global $_OPENDB_DB_CACHE;

	if (!is_array($_OPENDB_DB_CACHE['s_attribute_type'] ?? '') ||
		!is_array($_OPENDB_DB_CACHE['s_attribute_type'][$s_attribute_type] ?? '')) {
		$_OPENDB_DB_CACHE['s_attribute_type'][$s_attribute_type] = fetch_attribute_type_r($s_attribute_type);
	}

	return $_OPENDB_DB_CACHE['s_attribute_type'][$s_attribute_type];
}

function get_list_item_types($s_item_type_group, $s_item_type = NULL) {
	if (strlen($s_item_type_group) > 0) {
		$results = fetch_item_types_for_group_rs($s_item_type_group);
	} else if (is_not_empty_array($s_item_type)) { //set of s_item_types.
 		$results = fetch_item_type_for_item_types_rs($s_item_type);
	} else {
		$results = fetch_item_type_rs();
	}

	if ($results) {
		while ($itemtype_r = db_fetch_assoc($results)) {
			$types[] = array('value' => $itemtype_r['s_item_type'], 'display' => $itemtype_r['description']);
		}
		db_free_result($results);
	}

	return $types;
}

function get_list_item_type_groups() {
	$results = fetch_item_type_group_rs();
	if ($results) {
		$item_type_group_rs = NULL;

		while ($item_type_group_r = db_fetch_assoc($results)) {
			if (is_exists_item_type_group_rltshp($item_type_group_r['s_item_type_group'])) {
				$item_type_group_rs[] = array('value' => $item_type_group_r['s_item_type_group'], 'display' => $item_type_group_r['description']);
			}
		}
		db_free_result($results);

		return $item_type_group_rs;
	} else {
		return FALSE;
	}
}

function is_item_type_in_item_type_r($v_item_types, $s_item_type) {
	reset($v_item_types);
	foreach ( $v_item_types as $item_type_r ) {
		if ($item_type_r['value'] == $s_item_type) {
			return TRUE;
		}
	}

	//else
	return FALSE;
}

function find_field_type_column_config($s_field_type, $display_column_config) {
	$idx_of_element = -1;
	for ($k = 0; $k < count($display_column_config); $k++) {
		if ($display_column_config[$k]['column_type'] == 's_field_type' && $display_column_config[$k]['s_field_type'] == $s_field_type) {
			$idx_of_element = $k;
			break;
		}
	}

	return $idx_of_element;
}

function find_attribute_type_column_config($s_attribute_type, $display_column_config) {
	$idx_of_element = -1;
	for ($k = 0; $k < count($display_column_config); $k++) {
		if ($display_column_config[$k]['column_type'] == 's_attribute_type' && $display_column_config[$k]['s_attribute_type'] == $s_attribute_type) {
			$idx_of_element = $k;
			break;
		}
	}

	return $idx_of_element;
}

/**
 * Designed to merge display_attribute_type_rs arrays of the form:
 * 	array(s_attribute_type=>'BOXSET', order_no=>'1')
 * with search_attribute_type_rs arrays of the form:
 * 	array(s_attribute_type=>'BOXSET', value=>'first', match=>'word', include_in_list=>'y')
 */
function &merge_display_column_config_arrays($display_column_config, $search_column_config) {
	if (is_array($display_column_config) && is_array($search_column_config)) {
		// for each display column config, see if the search array has any more info, and merge it in. 
		for ($i = 0; $i < count($display_column_config); $i++) {
			if ($display_column_config[$i]['column_type'] == 's_attribute_type') {
				if (strlen($display_column_config[$i]['s_attribute_type']) > 0) {
					if (($idx_of_element = find_attribute_type_column_config($display_column_config[$i]['s_attribute_type'], $search_column_config)) != -1) {
						if (strlen($search_column_config[$idx_of_element]['lookup_attribute_val']) > 0 || strlen($search_column_config[$idx_of_element]['attribute_val']) > 0 || strlen($search_column_config[$idx_of_element]['attr_update_on']) > 0
								|| strlen($search_column_config[$idx_of_element]['attr_update_on_days']) > 0) {
							$display_column_config[$i] = array_merge($display_column_config[$i], $search_column_config[$idx_of_element]);

							// override orderby for search attributes.
							if ($display_column_config[$i]['orderby_support_ind'] !== 'Y') {
								$display_column_config[$i]['orderby_support_ind'] = 'Y';
							}
						}
					}
				}
			}
		}

		// first of all, lets find the title column if it exists
		$tmp_display_column_config = array();

		$index_of_title = find_field_type_column_config('TITLE', $display_column_config);

		// add all columns before the title, and including the title.
		for ($i = 0; $i <= $index_of_title; $i++) {
			$tmp_display_column_config[] = $display_column_config[$i];
		}

		// Any search columns not in listing column definition need to be added, and will be added to the
		// listing, as columns after the title column if found, otherwise will be added last.
		for ($i = 0; $i < count($search_column_config); $i++) {
			if ($search_column_config[$i]['column_type'] == 's_attribute_type') {
				if (strlen($search_column_config[$i]['s_attribute_type']) > 0
						&& (strlen($search_column_config[$i]['lookup_attribute_val']) > 0 || strlen($search_column_config[$i]['attribute_val']) > 0 || strlen($search_column_config[$i]['attr_update_on']) > 0 || strlen($search_column_config[$i]['attr_update_on_days']) > 0)) {
					if (find_attribute_type_column_config($search_column_config[$i]['s_attribute_type'], $display_column_config) == -1) {
						$tmp_display_column_config[] = $search_column_config[$i];
					}
				}
			} else if ($search_column_config[$i]['column_type'] == 's_field_type' && $search_column_config[$i]['s_field_type'] == 'RATING') {
				if (find_field_type_column_config('RATING', $display_column_config) == -1) {
					// Now we want to add it after 'title' element.
					$tmp_display_column_config[] = $search_column_config[$i];
				}
			}
		}

		if ($index_of_title != -1) {
			// now add the rest of the columns.
			for ($i = $index_of_title + 1; $i < count($display_column_config); $i++) {
				$tmp_display_column_config[] = $display_column_config[$i];

				if ($display_column_config[$i]['column_type'] == 's_field_type' && $display_column_config[$i]['s_field_type'] == 'STATUSTYPE') {
					// where we have a status type column def and a search on status comment has been initiated, we want to
					// add the status comment field, where it does not already exist.
					if (($idx_of_element = find_field_type_column_config('STATUSCMNT', $search_column_config)) != -1) {
						if (find_field_type_column_config('STATUSCMNT', $display_column_config) == -1) {
							$tmp_display_column_config[] = $search_column_config[$idx_of_element];
						}
					}
				}
			}
		}

		return $tmp_display_column_config;
	} else {
		return $display_column_config;
	}
}

function &filter_for_printable_list($column_display_config_rs) {
	$new_column_display_config_rs = array();
	for ($i = 0; $i < count($column_display_config_rs); $i++) {
		if ($column_display_config_rs[$i]['printable_support_ind'] === 'Y') {
			$new_column_display_config_rs[] = &$column_display_config_rs[$i];
		}
	}
	return $new_column_display_config_rs;
}

function get_column_display_config(&$HTTP_VARS, $show_owner_column, $show_action_column, $show_interest_column) {
	$v_column_display_config_rs = get_s_item_listing_column_conf_rs($HTTP_VARS['s_item_type_group'] ?? '', $HTTP_VARS['s_item_type'] ?? '');

	if ($HTTP_VARS['mode'] == 'printable') {
		$v_column_display_config_rs = &filter_for_printable_list($v_column_display_config_rs);
	}

	if (($HTTP_VARS['attr_match'] ?? '') != 'category' && strlen($HTTP_VARS['attribute_type'] ?? '') > 0) {
		// Now we have to merge in search terms, and add them after the 'title' column_id
		$v_column_display_config_rs = &merge_display_column_config_arrays(
			$v_column_display_config_rs,
			array(
				array('column_type' => 's_attribute_type',
					  's_attribute_type' => $HTTP_VARS['attribute_type'],
					  'attribute_val' => $HTTP_VARS['attribute_val'],
					  'lookup_attribute_val' => $HTTP_VARS['lookup_attribute_val'],
					  'attr_match' => $HTTP_VARS['attr_match'],
					  'attr_update_on' => $HTTP_VARS['attr_update_on'],
					  'attr_update_on_days' => $HTTP_VARS['attr_update_on_days'],
					  'orderby_support_ind' => 'Y',
					  'search_attribute_ind' => ifempty($HTTP_VARS['search_list'], $HTTP_VARS['attribute_list']))));
	}

	// need to add status_comment to listing if search for status comment enabled.							
	if (strlen($HTTP_VARS['status_comment'] ?? '') > 0) {
		$v_column_display_config_rs = &merge_display_column_config_arrays(
			$v_column_display_config_rs,
			array(
				array(
					'column_type' => 's_field_type',
					's_field_type' => 'STATUSCMNT')));
	}

	if (strlen($HTTP_VARS['rating'] ?? '') > 0) {
		$v_column_display_config_rs = &merge_display_column_config_arrays(
			$v_column_display_config_rs,
			array(
				array(
					'column_type' => 's_field_type',
					's_field_type' => 'RATING')));
	}

	for ($i = 0; $i < count($v_column_display_config_rs); $i++) {
		$v_column_display_config_rs[$i]['include_in_listing'] = TRUE;

		if ($v_column_display_config_rs[$i]['column_type'] == 's_attribute_type') {
			$v_attribute_type_r = fetch_cached_attribute_type_r($v_column_display_config_rs[$i]['s_attribute_type']);
			if (is_array($v_attribute_type_r)) {
				if (strlen($HTTP_VARS['s_item_type'] ?? '') > 0) {
					$v_column_display_config_rs[$i]['prompt'] = ifempty(fetch_s_item_type_attr_prompt($HTTP_VARS['s_item_type'], $v_column_display_config_rs[$i]['s_attribute_type']), $v_attribute_type_r['prompt']);
				} else {
					$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], $v_attribute_type_r['prompt']);
				}

				// record whether the s_attribute_type is a lookup attribute_type for use while generating the page.
				if ($v_attribute_type_r['lookup_attribute_ind'] == 'Y') {
					$v_column_display_config_rs[$i]['lookup_attribute_ind'] = 'Y';

					if (strlen($v_column_display_config_rs[$i]['lookup_attribute_val'] ?? '') == 0 &&
						strlen($v_column_display_config_rs[$i]['attribute_val'] ?? '') > 0)
					{
						$v_column_display_config_rs[$i]['lookup_attribute_val'] = $v_column_display_config_rs[$i]['attribute_val'];
						$v_column_display_config_rs[$i]['attribute_val'] = NULL;
					}
				} else if ($v_attribute_type_r['multi_attribute_ind'] == 'Y') {
					$v_column_display_config_rs[$i]['multi_attribute_ind'] = 'Y';
				}

				if ($v_column_display_config_rs[$i]['orderby_support_ind'] === 'Y') {
					if ($v_column_display_config_rs[$i]['orderby_datatype'] != 'numeric' && $v_attribute_type_r['input_type'] == 'number') {
						$v_column_display_config_rs[$i]['orderby_datatype'] = 'numeric';
					}
				}

				$v_column_display_config_rs[$i]['fieldname'] = get_field_name($v_column_display_config_rs[$i]['s_attribute_type']);

				// by default we won't include, unless the following is true
				$v_column_display_config_rs[$i]['include_in_listing'] = FALSE;

				// TODO - revise to get rid of this reverse logic!!!! 
				if (($v_column_display_config_rs[$i]['search_attribute_ind'] ?? '') != 'y' ||
					$v_column_display_config_rs[$i]['item_listing_conf_ind'] == 'Y' ||
					$v_column_display_config_rs[$i]['attr_match'] != 'exact' ||
					get_opendb_config_var('listings', 'show_exact_match_search_columns') !== FALSE)
				{
					$v_column_display_config_rs[$i]['include_in_listing'] = TRUE;
				}
			} else {
				$v_column_display_config_rs[$i]['include_in_listing'] = FALSE;
			}
		} else if ($v_column_display_config_rs[$i]['column_type'] == 's_field_type') {
			if ($v_column_display_config_rs[$i]['s_field_type'] == 'RATING') {
				$v_column_display_config_rs[$i]['s_attribute_type'] = 'S_RATING';

				$v_attribute_type_r = fetch_cached_attribute_type_r($v_column_display_config_rs[$i]['s_attribute_type']);

				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], $v_attribute_type_r['prompt']);

				$v_column_display_config_rs[$i]['fieldname'] = 'rating';
				$v_column_display_config_rs[$i]['orderby_support_ind'] = 'N';
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'ITEM_ID') {
				$v_column_display_config_rs[$i]['s_attribute_type'] = 'S_ITEM_ID';

				$v_attribute_type_r = fetch_cached_attribute_type_r($v_column_display_config_rs[$i]['s_attribute_type']);

				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], $v_attribute_type_r['prompt']);

				$v_column_display_config_rs[$i]['fieldname'] = 'item_id';
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'CATEGORY') {
				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('category'));

				$v_column_display_config_rs[$i]['fieldname'] = 'category';
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'STATUSTYPE') {
				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('status'));

				$v_column_display_config_rs[$i]['fieldname'] = 's_status_type';
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'STATUSCMNT') {
				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('status_comment'));

				$v_column_display_config_rs[$i]['fieldname'] = 'statuscmnt';
				$v_column_display_config_rs[$i]['orderby_support_ind'] = 'N';
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'TITLE') {
				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('title'));

				$v_column_display_config_rs[$i]['fieldname'] = 'title';
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'ITEMTYPE') {
				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('type'));

				$v_column_display_config_rs[$i]['fieldname'] = 's_item_type';
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'OWNER') {
				$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('owner'));
				$v_column_display_config_rs[$i]['fieldname'] = 'owner_id';

				if (!$show_owner_column) {
					$v_column_display_config_rs[$i]['include_in_listing'] = FALSE;
				}
			} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'INTEREST') {
				if ($show_interest_column) {
					$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('interest'));
					$v_column_display_config_rs[$i]['fieldname'] = 'interest';
				}
			}
		} else if ($v_column_display_config_rs[$i]['column_type'] == 'action_links') {
			$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('action'));

			$v_column_display_config_rs[$i]['fieldname'] = 'action_links';
			$v_column_display_config_rs[$i]['orderby_support_ind'] = 'N';

			if (!$show_action_column) {
				$v_column_display_config_rs[$i]['include_in_listing'] = FALSE;
			}
		} else if (get_opendb_config_var('borrow', 'enable') !== FALSE && $v_column_display_config_rs[$i]['column_type'] == 'borrow_status') {
			$v_column_display_config_rs[$i]['prompt'] = ifempty($v_column_display_config_rs[$i]['override_prompt'], get_opendb_lang_var('borrow_status'));
			$v_column_display_config_rs[$i]['fieldname'] = 'borrow_status';
			$v_column_display_config_rs[$i]['orderby_support_ind'] = 'N';
		}
	}

	return $v_column_display_config_rs;
}

function get_search_query_matrix($HTTP_VARS) {
	function get_match_type($match) {
		if ($match == 'word')
			return get_opendb_lang_var('word_match');
		else if ($match == 'partial')
			return get_opendb_lang_var('partial_match');
		else if ($match == 'exact')
			return get_opendb_lang_var('exact_match');
		else
			return NULL;
	}

	$searches = array();

	if (strlen($HTTP_VARS['title']) > 0) {
		// Default title match is exact match.
		$HTTP_VARS['title_match'] = ifempty($HTTP_VARS['title_match'], 'exact');

		if ($HTTP_VARS['title_match'] == 'word' || $HTTP_VARS['title_match'] == 'partial') {
			if (!isset($HTTP_VARS['title_case']))
				$searches[] = array('prompt' => get_opendb_lang_var('title') . ' (<em>' . get_match_type($HTTP_VARS['title_match']) . '</em>)', 'field' => $HTTP_VARS['title']);
			else
				$searches[] = array('prompt' => get_opendb_lang_var('title') . ' (<em>' . get_match_type($HTTP_VARS['title_match']) . ', ' . get_opendb_lang_var('case_sensitive') . '</em>)', 'field' => $HTTP_VARS['title']);
		} else {
			$searches[] = array('prompt' => get_opendb_lang_var('title'), 'field' => $HTTP_VARS['title']);
		}
	}

	if (strlen($HTTP_VARS['category'] ?? '') > 0) {
		// If s_item_type defined, we can get at the s_attribute_type of the category value.
		if (strlen($HTTP_VARS['s_item_type']) > 0) {
			$attribute_type_r = fetch_sfieldtype_item_attribute_type_r($HTTP_VARS['s_item_type'], 'CATEGORY');
			$searches[] = array('prompt' => get_opendb_lang_var('category'), 'field' => get_item_display_field(NULL, $attribute_type_r, $HTTP_VARS['category'], FALSE));
		} else {
			$searches[] = array('prompt' => get_opendb_lang_var('category'), 'field' => $HTTP_VARS['category']);
		}
	}

	if (strlen($HTTP_VARS['owner_id'] ?? '') > 0) {
		$username = fetch_user_name($HTTP_VARS['owner_id']);
		if (strlen($username) > 0)
			$searches[] = array('prompt' => get_opendb_lang_var('owner'), 'field' => $username . ' (' . $HTTP_VARS['owner_id'] . ')');
	}

	if (strlen($HTTP_VARS['s_item_type_group'] ?? '') > 0) {
		$searches[] = array('prompt' => get_opendb_lang_var('s_item_type_group'), 'field' => $HTTP_VARS['s_item_type_group']);
	}

	if (is_array($HTTP_VARS['s_item_type'] ?? '')) {
		$field = '';
		for ($i = 0; $i < count($HTTP_VARS['s_item_type']); $i++) {
			$item_type_r = fetch_item_type_r($HTTP_VARS['s_item_type'][$i]);
			$field .= theme_image($item_type_r['image'], $item_type_r['description'], 's_item_type');
		}

		$searches[] = array('prompt' => get_opendb_lang_var('s_item_type'), 'field' => $field);

	} else if (strlen($HTTP_VARS['s_item_type'] ?? '') > 0) {
		$item_type_r = fetch_item_type_r($HTTP_VARS['s_item_type']);
		$searches[] = array('prompt' => get_opendb_lang_var('s_item_type'), 'field' => theme_image($item_type_r['image'], $item_type_r['description'], 's_item_type'));
	}

	if (is_numeric($HTTP_VARS['rating'] ?? '')) {
		$attribute_type_r = fetch_cached_attribute_type_r('S_RATING');

		$searches[] = array('prompt' => $attribute_type_r['prompt'], 'field' => get_display_field($attribute_type_r['s_attribute_type'], NULL, 'review()', $HTTP_VARS['rating'], FALSE));
	}

	$attribute_type_r = NULL;
	if (strlen($HTTP_VARS['attribute_type'] ?? '') > 0) {
		$attribute_type_r = fetch_cached_attribute_type_r($HTTP_VARS['attribute_type']);
		if (is_not_empty_array($attribute_type_r)) {
			$attribute_type_r['listing_link_ind'] = 'N';

			// Default title match is exact match.
			$HTTP_VARS['attr_match'] = ifempty($HTTP_VARS['attr_match'], 'exact');

			// Special category search, but ignore if category variable actually specified.
			if (strlen($HTTP_VARS['category']) == 0 && strlen($HTTP_VARS['attribute_val']) > 0 && $HTTP_VARS['attr_match'] == 'category') {
				// We do not want the Listing Link to be added to this display field
				$searches[] = array('prompt' => $attribute_type_r['prompt'], 'field' => get_item_display_field(NULL, $attribute_type_r, stripslashes($HTTP_VARS['attribute_val']), FALSE));
			} else {
				if (strlen($HTTP_VARS['attribute_val']) > 0) {
					$HTTP_VARS['attribute_val'] = stripslashes($HTTP_VARS['attribute_val']);
					if (starts_with($HTTP_VARS['attribute_val'], '"') && ends_with($HTTP_VARS['attribute_val'], '"')) {
						$HTTP_VARS['attribute_val'] = substr($HTTP_VARS['attribute_val'], 1, -1);
					}

					$search = ifempty( // using ifempty in case a display type does not return anything
					get_item_display_field(NULL, $attribute_type_r, $HTTP_VARS['attribute_val'], FALSE), $HTTP_VARS['attribute_val']);
				} else if (strlen($HTTP_VARS['lookup_attribute_val']) > 0) {
					$search = get_item_display_field(NULL, $attribute_type_r, stripslashes($HTTP_VARS['lookup_attribute_val']), FALSE);
				}

				if (!is_lookup_attribute_type($HTTP_VARS['attribute_type']) && $HTTP_VARS['attr_match'] != 'exact')
					$searches[] = array('prompt' => $attribute_type_r['prompt'] . ' (<em>' . get_match_type($HTTP_VARS['attr_match']) . '</em>)', 'field' => $search);
				else
					$searches[] = array('prompt' => $attribute_type_r['prompt'], 'field' => $search);
			}
		}//if(is_not_empty_array($attribute_type_r))
	} else {
		if (strlen($HTTP_VARS['attribute_val'] ?? '') > 0) { // specified a search term without attribute type, this is a global search.
 			if (!isset($HTTP_VARS['attr_case'])) {
				$searches[] = array('prompt' => get_opendb_lang_var('attribute_val') . ' (<em>' . get_match_type(ifempty($HTTP_VARS['attr_match'], 'exact')) . '</em>)', 'field' => stripslashes($HTTP_VARS['attribute_val']));
			} else {
				$searches[] = array('prompt' => get_opendb_lang_var('attribute_val') . ' (<em>' . get_match_type(ifempty($HTTP_VARS['attr_match'], 'exact')) . ', ' . get_opendb_lang_var('case_sensitive') . '</em>)', 'field' => stripslashes($HTTP_VARS['attribute_val']));
			}
		}
	}

	// add another search field if update_on value also specified.	
	if (strlen($HTTP_VARS['attr_update_on'] ?? '') > 0) {
		if (is_not_empty_array($attribute_type_r))
			$prompt = get_opendb_lang_var('attribute_prompt_updated', array('s_attribute_type' => $attribute_type_r['s_attribute_type'], 'prompt' => $attribute_type_r['prompt']));
		else
			$prompt = get_opendb_lang_var('attributes_updated');

		if (strlen($HTTP_VARS['datetimemask']) > 0)
			$searches[] = array('prompt' => $prompt, 'field' => $HTTP_VARS['attr_update_on'] . ' (' . $HTTP_VARS['datetimemask'] . ')');
		else
			$searches[] = array('prompt' => $prompt, 'field' => $HTTP_VARS['attr_update_on']);
	} else if (is_numeric($HTTP_VARS['attr_update_on_days'] ?? '')) {
		if (is_not_empty_array($attribute_type_r))
			$prompt = get_opendb_lang_var('attribute_prompt_updated', array('s_attribute_type' => $attribute_type_r['s_attribute_type'], 'prompt' => $attribute_type_r['prompt']));
		else
			$prompt = get_opendb_lang_var('attributes_updated');

		if ($HTTP_VARS['attr_update_on_days'] == '1')
			$field = get_opendb_lang_var('one_day_ago');
		else if ($HTTP_VARS['attr_update_on_days'] == '7')
			$field = get_opendb_lang_var('one_week_ago');
		else if ($HTTP_VARS['attr_update_on_days'] == '28')
			$field = get_opendb_lang_var('one_month_ago');
		else if ($HTTP_VARS['attr_update_on_days'] == '365')
			$field = get_opendb_lang_var('one_year_ago');

		$searches[] = array('prompt' => $prompt, 'field' => $field);
	}

	if (strlen($HTTP_VARS['update_on'] ?? '') > 0) {
		if (strlen($HTTP_VARS['datetimemask']) > 0)
			$searches[] = array('prompt' => get_opendb_lang_var('updated'), 'field' => $HTTP_VARS['update_on'] . ' (' . $HTTP_VARS['datetimemask'] . ')');
		else
			$searches[] = array('prompt' => get_opendb_lang_var('updated'), 'field' => $HTTP_VARS['update_on']);
	} else if (is_numeric($HTTP_VARS['update_on_days'] ?? '')) {
		if ($HTTP_VARS['update_on_days'] == '1')
			$field = get_opendb_lang_var('one_day_ago');
		else if ($HTTP_VARS['update_on_days'] == '7')
			$field = get_opendb_lang_var('one_week_ago');
		else if ($HTTP_VARS['update_on_days'] == '28')
			$field = get_opendb_lang_var('one_month_ago');
		else if ($HTTP_VARS['update_on_days'] == '365')
			$field = get_opendb_lang_var('one_year_ago');

		$searches[] = array('prompt' => get_opendb_lang_var('updated'), 'field' => $field);
	}

	if (is_not_empty_array($HTTP_VARS['s_status_type'] ?? NULL) > 0) {
		$search = '';
		for ($i = 0; $i < count($HTTP_VARS['s_status_type']); $i++) {
			if (strlen($search) > 0) {
				$search .= ' ';
			}

			$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type'][$i]);
			if (is_not_empty_array($status_type_r)) {
				$search .= format_display_value('%img%', $status_type_r['img'], 'Y', $status_type_r['description'], 's_status_type');
			}
		}

		if (strlen($search) > 0) {
			$searches[] = array('prompt' => get_opendb_lang_var('s_status_type'), 'field' => $search);
		}
	}

	if (strlen($HTTP_VARS['status_comment'] ?? NULL) > 0) {
		// Default status_comment match is exact match.
		$HTTP_VARS['status_comment_match'] = ifempty($HTTP_VARS['status_comment_match'], 'exact');

		if ($HTTP_VARS['status_comment_match'] == 'word' || $HTTP_VARS['status_comment_match'] == 'partial' || $HTTP_VARS['status_comment_match'] == 'exact') {
			if (!isset($HTTP_VARS['status_comment_case']))
				$searches[] = array('prompt' => get_opendb_lang_var('status_comment') . ' (<em>' . get_match_type($HTTP_VARS['status_comment_match']) . '</em>)', 'field' => $HTTP_VARS['status_comment']);
			else
				$searches[] = array('prompt' => get_opendb_lang_var('status_comment') . ' (<em>' . get_match_type($HTTP_VARS['status_comment_match']) . ', ' . get_opendb_lang_var('case_sensitive') . '</em>)', 'field' => $HTTP_VARS['status_comment']);
		} else {
			$searches[] = array('prompt' => get_opendb_lang_var('status_comment'), 'field' => $HTTP_VARS['status_comment']);
		}
	}
	if (is_numeric($HTTP_VARS['interest_level'] ?? NULL) && $HTTP_VARS['interest_level'] > 0) {
		$searches[] = array('prompt' => get_opendb_lang_var('interest'), 'field' => theme_image("interest_1.gif", get_opendb_lang_var('interest'), 's_item_type'));
	}

	return $searches;
}//function get_search_query_matrix($HTTP_VARS)

if (is_site_enabled()) {
	if (is_opendb_valid_session() || is_site_public_access()) {
		if (is_user_granted_permission(PERM_VIEW_LISTINGS)) {
			$v_item_types = get_list_item_types($HTTP_VARS['s_item_type_group'] ?? "", $HTTP_VARS['s_item_type'] ?? NULL);
			if (!is_array($HTTP_VARS['s_item_type'] ?? "") && strlen($HTTP_VARS['s_item_type'] ?? "") > 0 && is_not_empty_array($v_item_types)) {
				if (!is_item_type_in_item_type_r($v_item_types, $HTTP_VARS['s_item_type'] ?? NULL)) {
					unset($HTTP_VARS['s_item_type']);
				}
			}

			$show_interest_column = FALSE;
			if (is_user_granted_permission(PERM_USER_INTEREST)) {
				$show_interest_column = TRUE;

				//@TODO This should be moved to HTML_listing class.
				$xajax = new xajax();
				$xajax->configure('responseType', 'XML');
				$xajax->configure('javascript URI', 'lib/xajax/');
				$xajax->configure('debug', false);
				$xajax->configure('statusMessages', true);
				$xajax->configure('waitCursor', true);
				$xajax->register(XAJAX_FUNCTION, "ajax_update_interest_level");
				$xajax->register(XAJAX_FUNCTION, "ajax_remove_all_interest_level");
				$xajax->processRequest();
			}

			if (strlen($HTTP_VARS['owner_id'] ?? '') > 0)
				$show_owner_column = FALSE;
			else
				$show_owner_column = TRUE;

			// Work out whether Item action checkboxes should be displayed.
			$show_checkbox_column = FALSE;
			if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('listings.multi_borrow', 'enable') !== FALSE
					&& (get_opendb_config_var('listings.multi_borrow', 'reserve_action') !== FALSE
							|| (get_opendb_config_var('borrow', 'reserve_basket') !== FALSE && get_opendb_config_var('listings.multi_borrow', 'basket_action') === TRUE
									&& (get_opendb_config_var('listings.multi_borrow', 'basket_action_if_not_empty_only') !== TRUE || is_exists_my_reserve_basket(get_opendb_session_var('user_id')))))) {
				// Only users who can borrow should see checkboxes and their own listings.
				if (is_user_granted_permission(PERM_USER_BORROWER)) {
					if ($HTTP_VARS['owner_id'] !== get_opendb_session_var('user_id') || get_opendb_config_var('borrow', 'owner_self_checkout') !== FALSE) {
						$show_checkbox_column = TRUE;
					}
				}
			}

			if (get_opendb_config_var('borrow', 'enable') !== FALSE && is_user_granted_permission(array(PERM_USER_BORROWER, PERM_ADMIN_BORROWER)) && (get_opendb_config_var('listings.borrow', 'quick_checkout_action') !== FALSE || get_opendb_config_var('listings.borrow', 'enable') !== FALSE)) {
				$show_action_column = TRUE;
			}

			if (get_opendb_config_var('listings', 'show_input_actions') !== FALSE) {
				if (is_user_granted_permission(PERM_ITEM_OWNER) && (strlen($HTTP_VARS['not_owner_id'] ?? "") == 0 || $HTTP_VARS['not_owner_id'] != get_opendb_session_var('user_id')) && (strlen($HTTP_VARS['owner_id'] ?? '') == 0 || $HTTP_VARS['owner_id'] == get_opendb_session_var('user_id'))) {
					$show_action_column = TRUE;
				}
			}

			$v_column_display_config_rs = get_column_display_config($HTTP_VARS, $show_owner_column, $show_action_column, $show_interest_column);

			$page_title = NULL;
			if ($HTTP_VARS['search_list'] == 'y' || $HTTP_VARS['attribute_list'] == 'y') {
				if (strlen($HTTP_VARS['override_page_title'] ?? '') > 0)
					$page_title = $HTTP_VARS['override_page_title'];
				else
					$page_title = get_opendb_lang_var('search_results');
			} else if (is_valid_s_status_type($HTTP_VARS['s_status_type'])) {
				$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);

				if ($HTTP_VARS['owner_id'] == get_opendb_session_var('user_id'))
					$page_title = get_opendb_lang_var('my_s_status_type_item_listing', 's_status_type_desc', $status_type_r['description']);
				else if (strlen($HTTP_VARS['owner_id']) > 0)
					$page_title = get_opendb_lang_var('s_status_type_item_listing_for_name', array('s_status_type_desc' => $status_type_r['description'], 'fullname' => fetch_user_name($HTTP_VARS['owner_id']), 'user_id' => $HTTP_VARS['owner_id']));
				else if (strlen($HTTP_VARS['not_owner_id']) > 0)
					$page_title = get_opendb_lang_var('other_s_status_type_item_listing', 's_status_type_desc', $status_type_r['description']);
				else
					$page_title = get_opendb_lang_var('all_s_status_type_item_listing', 's_status_type_desc', $status_type_r['description']);
			} else {
				if ($HTTP_VARS['owner_id'] == get_opendb_session_var('user_id'))
					$page_title = get_opendb_lang_var('my_item_listing');
				else if (strlen($HTTP_VARS['owner_id']) > 0)
					$page_title = get_opendb_lang_var('item_listing_for_name', array('fullname' => fetch_user_name($HTTP_VARS['owner_id']), 'user_id' => $HTTP_VARS['owner_id']));
				else if (strlen($HTTP_VARS['not_owner_id']) > 0)
					$page_title = get_opendb_lang_var('other_item_listing');
				else
					$page_title = get_opendb_lang_var('all_item_listing');
			}

			echo (_theme_header($page_title, $HTTP_VARS['inc_menu'] ?? NULL));
			echo ('<h2>' . $page_title . '</h2>');

			if ($HTTP_VARS['search_list'] == 'y' || $HTTP_VARS['attribute_list'] == 'y') {
				// default search term if attribute_type specified, but no value provided.
				if (strlen($HTTP_VARS['attribute_type'] ?? '') > 0 &&
					strlen($HTTP_VARS['attribute_val'] ?? '') == 0 &&
					strlen($HTTP_VARS['lookup_attribute_val'] ?? '') == 0)
				{
					$HTTP_VARS['attribute_val'] = '%';
				}

				if (($HTTP_VARS['show_search_query_matrix'] ?? '') != 'N') {
					$matrix_rs = get_search_query_matrix($HTTP_VARS);
					if (is_not_empty_array($matrix_rs)) {
						echo ('<div class="search-query"><dl>');

						foreach ( $matrix_rs as $matrix_r ) {
							echo ('<dt>' . $matrix_r['prompt'] . '</dt>' . '<dd>' . $matrix_r['field'] . '</dd>');
						}
						echo ('</dl></div>');
					}
				}
			}

			echo (getListingFiltersBlock());
			echo (getAlphaListBlock($PHP_SELF, $HTTP_VARS));

			if ($show_interest_column) {
				echo ($xajax->printJavascript());
			}

			$listingObject = new HTML_Listing($PHP_SELF, $HTTP_VARS);

			$listingObject->startListing($page_title);

			if ($show_checkbox_column) {
				$listingObject->addHeaderColumn(NULL, 'item_id_instance_no', FALSE, 'checkbox');
			}

			for ($i = 0; $i < count($v_column_display_config_rs); $i++) {
				if ($v_column_display_config_rs[$i]['include_in_listing'] !== FALSE) {
					$listingObject->addHeaderColumn($v_column_display_config_rs[$i]['prompt'], $v_column_display_config_rs[$i]['fieldname'], $v_column_display_config_rs[$i]['orderby_support_ind'] === 'Y');
				}
			}

			// If no items Per Page - we are listing everything.
			if (is_numeric($listingObject->getItemsPerPage())) {
				$listingObject->setTotalItems(fetch_item_listing_cnt($HTTP_VARS, $v_column_display_config_rs));
			}

			// -------------------------------------- Process the Query here -----------------------------------------------------------------
			if (($result = fetch_item_listing_rs($HTTP_VARS, 
						$v_column_display_config_rs, // calculated above
						$listingObject->getCurrentOrderBy(), 
						$listingObject->getCurrentSortOrder(), 
						$listingObject->getStartIndex(), 
						$listingObject->getItemsPerPage()))) {
				// ----------------------------------------------------------------------------
				// Save current url string, so we can return to last listings page if required.
				// ----------------------------------------------------------------------------
				// The Listing class has already removed any $HTTP_VARS which should not
				// be passed onto the next request.
				$v_listing_url_vars = $HTTP_VARS;

				$v_listing_url_vars['mode'] = NULL;

				// These are listing specific - we do not want to save them.
				$v_listing_url_vars['item_id_instance_no'] = NULL;
				$v_listing_url_vars['checked_item_id_instance_no'] = NULL;
				$v_listing_url_vars['checked_item_id_instance_no_list'] = NULL;

				register_opendb_session_var('listing_url_vars', $v_listing_url_vars);

				while ($item_r = db_fetch_assoc($result)) {
					$listingObject->startRow();

					// Get the Status Type config for the current item_instance.s_status_type, but grab it once.
					if (!is_array($status_type_rs[$item_r['s_status_type']])) {
						$status_type_rs[$item_r['s_status_type']] = fetch_status_type_r($item_r['s_status_type']);
					}

					if ($show_checkbox_column) {
						if (!is_item_in_reserve_basket($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id'))) {
							if ($status_type_rs[$item_r['s_status_type']]['borrow_ind'] == 'Y' && ($item_r['owner_id'] !== get_opendb_session_var('user_id') || get_opendb_config_var('borrow', 'owner_self_checkout') !== FALSE) && is_user_granted_permission(PERM_USER_BORROWER)
									&& !is_item_reserved_or_borrowed_by_user($item_r['item_id'], $item_r['instance_no']) && (get_opendb_config_var('borrow', 'allow_reserve_if_borrowed') !== FALSE || !is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
									&& (get_opendb_config_var('borrow', 'allow_multi_reserve') !== FALSE || !is_item_reserved($item_r['item_id'], $item_r['instance_no']))) {
								$listingObject->addCheckboxColumn($item_r['item_id'] . "_" . $item_r['instance_no'], FALSE);
							} else {
								$listingObject->addColumn();
							}
						} else {
							$listingObject->addColumn();
						}
					}

					for ($i = 0; $i < count($v_column_display_config_rs); $i++) {
						if ($v_column_display_config_rs[$i]['include_in_listing'] !== FALSE) {
							if ($v_column_display_config_rs[$i]['column_type'] == 's_attribute_type') {
								if ($v_column_display_config_rs[$i]['search_attribute_ind'] == 'y') {
									$attribute_val = $item_r[$v_column_display_config_rs[$i]['fieldname']];
								} else if ($v_column_display_config_rs[$i]['multi_attribute_ind'] == 'Y' || $v_column_display_config_rs[$i]['lookup_attribute_ind'] == 'Y') {
									$attribute_val = fetch_attribute_val_r($item_r['item_id'], $item_r['instance_no'], $v_column_display_config_rs[$i]['s_attribute_type']);
								} else {
									$attribute_val = fetch_attribute_val($item_r['item_id'], $item_r['instance_no'], $v_column_display_config_rs[$i]['s_attribute_type']);
								}

								if ($attribute_val !== FALSE && $attribute_val !== NULL) {
									$listingObject->addAttrDisplayColumn($item_r, fetch_cached_attribute_type_r($v_column_display_config_rs[$i]['s_attribute_type']), $attribute_val);
								} else {
									$listingObject->addColumn();
								}
							} else if ($v_column_display_config_rs[$i]['column_type'] == 's_field_type') {
								if ($v_column_display_config_rs[$i]['s_field_type'] == 'RATING') {
									$rating = fetch_review_rating($item_r['item_id']);
									if ($rating !== FALSE) {
										$attribute_type_r = fetch_cached_attribute_type_r($v_column_display_config_rs[$i]['s_attribute_type']);
										if (strlen($attribute_type_r['display_type']) == 0 || $attribute_type_r['display_type'] == 'hidden') {
											$attribute_type_r['display_type'] = 'review';
										}
										$listingObject->addAttrDisplayColumn($item_r, $attribute_type_r, fetch_review_rating($item_r['item_id']));
									} else {
										$listingObject->addColumn();
									}
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'ITEM_ID') {
									$attribute_type_r = fetch_cached_attribute_type_r($v_column_display_config_rs[$i]['s_attribute_type']);
									if (strlen($attribute_type_r['display_type']) == 0 || $attribute_type_r['display_type'] == 'hidden') {
										$attribute_type_r['display_type'] = 'display';
										$attribute_type_r['display_type_arg1'] = '%value%';
									}

									$listingObject->addAttrDisplayColumn($item_r, $attribute_type_r, $item_r['item_id']);
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'CATEGORY') {
									$listingObject
											->addAttrDisplayColumn($item_r, fetch_cached_attribute_type_r($item_r['catia_s_attribute_type']), fetch_attribute_val_r($item_r['item_id'], $item_r['instance_no'], $item_r['catia_s_attribute_type'], $item_r['catia_order_no']));
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'STATUSTYPE') {
									$listingObject
											->addThemeImageColumn($status_type_rs[$item_r['s_status_type']]['img'], $status_type_rs[$item_r['s_status_type']]['description'], $status_type_rs[$item_r['s_status_type']]['description'], //title
											's_status_type');//type
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'STATUSCMNT') {
									// If a comment is allowed and defined, add it in.
									if ($status_type_rs[$item_r['s_status_type']]['status_comment_ind'] == 'Y' || get_opendb_session_var('user_id') === $item_r['owner_id'] || is_user_granted_permission(PERM_ITEM_ADMIN)) {
										// support newlines in this field
										$listingObject->addColumn(nl2br($item_r['status_comment']));
									} else {
										$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
									}
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'ITEMTYPE') {
									$listingObject->addItemTypeImageColumn($item_r['s_item_type']);
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'TITLE') {
									$listingObject->addTitleColumn($item_r);
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'OWNER') {
									$listingObject->addUserNameColumn($item_r['owner_id']);
								} else if ($v_column_display_config_rs[$i]['s_field_type'] == 'INTEREST') {
									$listingObject->addInterestColumn($item_r['item_id'], $item_r['instance_no'], get_opendb_session_var('user_id'), $item_r['interest_level']);
								}
							} else if ($v_column_display_config_rs[$i]['column_type'] == 'action_links') {
								$action_links_rs = NULL;

								// Administrator and Owner actions here.
								if ($item_r['owner_id'] == get_opendb_session_var('user_id') || is_user_granted_permission(PERM_ITEM_ADMIN)) {
									// The option of having only Quick Checkout links should be provided.
									if (get_opendb_config_var('listings', 'show_input_actions')) {
										if (get_opendb_config_var('listings', 'show_input_actions')) {
											$action_links_rs[] = array('url' => 'item_input.php?op=edit&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'edit.gif', 'text' => get_opendb_lang_var('edit'));

											// So we only have to check the 'is_site_plugin' once!
											if (strlen($item_types_rs[$item_r['s_item_type']]['legal_site_type']) == 0) {
												$item_types_rs[$item_r['s_item_type']]['legal_site_type'] = is_item_legal_site_type($item_r['s_item_type']);
											}

											// Only site types which are considered legal can be allowed for refresh operation.
											if (get_opendb_config_var('listings', 'show_refresh_actions') && $item_types_rs[$item_r['s_item_type']]['legal_site_type']) {
												$action_links_rs[] = array('url' => 'item_input.php?op=site-refresh&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'refresh.gif', 'text' => get_opendb_lang_var('refresh'));
											}

											if ($status_type_rs[$item_r['s_status_type']]['delete_ind'] == 'Y' && !is_item_reserved_or_borrowed($item_r['item_id'], $item_r['instance_no'])) {
												$action_links_rs[] = array('url' => 'item_input.php?op=delete&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'delete.gif', 'text' => get_opendb_lang_var('delete'));
											}
										}
									}
								}

								if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('listings.borrow', 'enable') !== FALSE) {
									if (is_item_borrowed($item_r['item_id'], $item_r['instance_no'])) {
										if (is_user_allowed_to_checkin_item($item_r['item_id'], $item_r['instance_no'])) {
											$action_links_rs[] = array('url' => 'item_borrow.php?op=check_in&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'check_in_item.gif', 'text' => get_opendb_lang_var('check_in_item'));
										}
									} else {
										if (get_opendb_config_var('borrow', 'quick_checkout') !== FALSE && get_opendb_config_var('listings.borrow', 'quick_checkout_action') !== FALSE && $status_type_rs[$item_r['s_status_type']]['borrow_ind'] == 'Y'
												&& is_user_allowed_to_checkout_item($item_r['item_id'], $item_r['instance_no'])) {
											$action_links_rs[] = array('url' => 'item_borrow.php?op=quick_check_out&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'quick_check_out.gif', 'text' => get_opendb_lang_var('quick_check_out'));
										}
									}
								}

								if ($item_r['owner_id'] != get_opendb_session_var('user_id')) {
									// Reservation/Cancel Information.
									if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('listings.borrow', 'enable') !== FALSE) {
										if (is_user_granted_permission(PERM_USER_BORROWER) && $status_type_rs[$item_r['s_status_type']]['borrow_ind'] == 'Y') {
											if (is_item_reserved_or_borrowed($item_r['item_id'], $item_r['instance_no'])) {
												if (is_item_reserved_by_user($item_r['item_id'], $item_r['instance_no'])) {
													$action_links_rs[] = array('url' => 'item_borrow.php?op=cancel_reserve&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'cancel_reserve.gif', 'text' => get_opendb_lang_var('cancel'));
												} else if (!is_item_borrowed_by_user($item_r['item_id'], $item_r['instance_no'])) {
													if ((get_opendb_config_var('borrow', 'allow_reserve_if_borrowed') !== FALSE || !is_item_borrowed($item_r['item_id'], $item_r['instance_no']))
															&& (get_opendb_config_var('borrow', 'allow_multi_reserve') !== FALSE || !is_item_reserved($item_r['item_id'], $item_r['instance_no']))) {
														if (get_opendb_config_var('borrow', 'reserve_basket') !== FALSE && get_opendb_config_var('listings.borrow', 'basket_action') !== FALSE) {
															$action_links_rs[] = array('url' => 'borrow.php?op=update_my_reserve_basket&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'add_reserve_basket.gif', 'text' => get_opendb_lang_var('add_to_reserve_list'));
														}

														if (get_opendb_config_var('listings.borrow', 'reserve_action') !== FALSE) {
															$action_links_rs[] = array('url' => 'item_borrow.php?op=reserve&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'reserve_item.gif', 'text' => get_opendb_lang_var('reserve'));
														}
													}
												}
											} else {
												if (get_opendb_config_var('borrow', 'reserve_basket') !== FALSE && get_opendb_config_var('listings.borrow', 'basket_action') !== FALSE) {
													$action_links_rs[] = array('url' => 'borrow.php?op=update_my_reserve_basket&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'add_reserve_basket.gif', 'text' => get_opendb_lang_var('add_to_reserve_list'));
												}

												if (get_opendb_config_var('listings.borrow', 'reserve_action') !== FALSE) {
													$action_links_rs[] = array('url' => 'item_borrow.php?op=reserve&item_id=' . $item_r['item_id'] . '&instance_no=' . $item_r['instance_no'], 'img' => 'reserve_item.gif', 'text' => get_opendb_lang_var('reserve'));
												}
											}
										}
									}
								}

								$listingObject->addActionColumn($action_links_rs);
							} else if ($v_column_display_config_rs[$i]['column_type'] == 'borrow_status') {
								if (get_opendb_config_var('borrow', 'enable') !== FALSE) {
									if (is_item_borrowed($item_r['item_id'], $item_r['instance_no'])) {
										$listingObject->addThemeImageColumn('borrowed.gif', get_opendb_lang_var('borrowed'), get_opendb_lang_var('borrowed'), //title
												'borrowed_item');
									} else if (is_item_reserved($item_r['item_id'], $item_r['instance_no'])) {
										$listingObject->addThemeImageColumn('reserved.gif', get_opendb_lang_var('reserved'), get_opendb_lang_var('borrowed'), //title
												'borrowed_item');
									} else {
										$listingObject->addColumn(get_opendb_lang_var('not_applicable'));
									}
								}
							}
						}//if($v_column_display_config_rs[$i]['include_in_listing']!==FALSE)
					}

					$listingObject->endRow();
				}//end of while
				db_free_result($result);
				// ---------------------------------------------------------------------------------------------------------------
			}//end of if($result)

			$listingObject->endListing();

			if ($listingObject->isCheckboxColumns() > 0) {
				if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('listings.multi_borrow', 'enable') !== FALSE) {
					if (get_opendb_config_var('listings.multi_borrow', 'reserve_action') !== FALSE) {
						$checkbox_action_rs[] = array('action' => 'item_borrow.php', 'op' => 'reserve', 'link' => get_opendb_lang_var('reserve_item(s)'));
					}

					if (get_opendb_config_var('borrow', 'reserve_basket') !== FALSE
							&& (get_opendb_config_var('listings.multi_borrow', 'basket_action') === TRUE && (get_opendb_config_var('listings.multi_borrow', 'basket_action_if_not_empty_only') !== TRUE || is_exists_my_reserve_basket(get_opendb_session_var('user_id'))))) {
						$checkbox_action_rs[] = array('action' => 'borrow.php', 'op' => 'update_my_reserve_basket', 'link' => get_opendb_lang_var('add_to_reserve_list'));
					}
				}

				echo (format_checkbox_action_links('item_id_instance_no', get_opendb_lang_var('no_items_checked'), $checkbox_action_rs));
			}

			echo (format_help_block($listingObject->getHelpEntries()));

			echo ("<ul class=\"listingControls\">");
			if (get_opendb_config_var('listings', 'allow_override_show_item_image') !== FALSE) {
				echo ("<li>" . getToggleControl($PHP_SELF, $HTTP_VARS, get_opendb_lang_var('show_item_image'), 'show_item_image', ifempty($HTTP_VARS['show_item_image'] ?? "", get_opendb_config_var('listings', 'show_item_image') == TRUE ? 'Y' : 'N')) . "</li>");
			}
			echo ("<li>" . getItemsPerPageControl($PHP_SELF, $HTTP_VARS) . "</li>");
			echo ("</ul>");

			echo ("<p class=\"listingDate\">" . get_opendb_lang_var('listing_generated', 'datetime', get_localised_timestamp(get_opendb_config_var('listings', 'print_listing_datetime_mask'))) . "</p>");

			echo (_theme_footer());
		} else {
			opendb_not_authorised_page(PERM_VIEW_LISTINGS, $HTTP_VARS);
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
