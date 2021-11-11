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
include_once("./lib/item_attribute.php");
include_once("./lib/widgets.php");
include_once("./lib/item.php");
include_once("./lib/parseutils.php");
include_once("./lib/user.php");
include_once("./lib/status_type.php");

function encode_search_javascript_arrays(&$item_type_rs, &$arrayOfUniqueCategories, &$item_attribute_type_rs) {
	$buffer = "";

	$arrayOfCategoryTypes = array();
	$arrayOfUniqueAttributes = array();

	$itemTypeBlock = "";
	$attrTypeSelect = "";

	$jsArrayOfAttributes = "";
	$jsArrayOfAttributesCount = 0;

	$itemresults = fetch_item_type_rs();
	while ($item_type_r = db_fetch_assoc($itemresults)) {
		$results = fetch_item_attribute_type_rs($item_type_r['s_item_type'], NULL, 'prompt');
		// For the case where we have a s_item_type with no attributes associated with it!
		if ($results) {
			while ($item_attribute_type_r = db_fetch_assoc($results)) {
				// Do not include any attributes which do not exist as item attributes
				if ($item_attribute_type_r['s_field_type'] != 'TITLE' && $item_attribute_type_r['s_field_type'] != 'DURATION' && $item_attribute_type_r['s_field_type'] != 'STATUSTYPE' && $item_attribute_type_r['s_field_type'] != 'STATUSCMNT' && $item_attribute_type_r['s_field_type'] != 'ITEM_ID') {
					// Only unique attributes should be added to attribute options object.
					if (!is_array($arrayOfUniqueAttributes) || !in_array($item_attribute_type_r['s_attribute_type'], $arrayOfUniqueAttributes)) {
						// This array, is only so we can make sure that we have unique attributes only!
						$arrayOfUniqueAttributes[] = $item_attribute_type_r['s_attribute_type'];

#						$jsArrayOfUniqueAttributes .= "\narrayOfAttributes[$jsArrayOfAttributesCount] = new LookupAttribute(\"\",\"" . $item_attribute_type_r['s_attribute_type'] . "\",\"" . $item_attribute_type_r['s_attribute_type'] . " - " . $item_attribute_type_r['description'] . "\");";

						$item_attribute_type_rs[] = $item_attribute_type_r;
					}

					$jsArrayOfAttributes .= "\narrayOfAttributes[$jsArrayOfAttributesCount] = new LookupAttribute(\"" . $item_type_r['s_item_type'] . "\",\"" . $item_attribute_type_r['s_attribute_type'] . "\",\"" . $item_attribute_type_r['s_attribute_type'] . " - "
							. $item_attribute_type_r['description'] . "\");";
					$jsArrayOfAttributesCount++;
				}
			}
		}

		// Get the category_attribute_type for this item_type
		$category_attribute_type = fetch_sfieldtype_item_attribute_type($item_type_r['s_item_type'], 'CATEGORY');

		// We need this list further down, to work out which of the lookup items are actually categories.
		$arrayOfCategoryTypes[] = $category_attribute_type;

		$item_type_rs[] = $item_type_r;
	}

	// Give us the whole s_attribute_type_lookup table, whoo baby...
	$jsArrayOfLookupValues = "";
	$jsArrayOfLookupValuesCount = 0;

	$attresults = fetch_attribute_type_lookup_rs(NULL, 's_attribute_type ASC');
	while ($attribute_type_r = db_fetch_assoc($attresults)) {
		if ($attribute_type_r['s_field_type'] != 'ADDRESS' && $attribute_type_r['s_field_type'] != 'S_RATING') {
			if (in_array($attribute_type_r['s_attribute_type'], $arrayOfCategoryTypes) && (!is_array($arrayOfUniqueCategories) || !in_array($attribute_type_r['value'], $arrayOfUniqueCategories))) {
				$arrayOfUniqueCategories[$attribute_type_r['value']] = $attribute_type_r['display'];
			}

			$jsArrayOfLookupValues .= "\narrayOfLookupValues[$jsArrayOfLookupValuesCount] = new LookupAttribute(\"" . $attribute_type_r['s_attribute_type'] . "\",\"" . $attribute_type_r['value'] . "\",\"" . $attribute_type_r['display'] . "\");";
			$jsArrayOfLookupValuesCount++;
		}
	}

	$buffer .= "\n\narrayOfLookupValues = new Array($jsArrayOfLookupValuesCount);";
	$buffer .= $jsArrayOfLookupValues;

	$buffer .= "\n\narrayOfAttributes = new Array($jsArrayOfAttributesCount);";
	$buffer .= $jsArrayOfAttributes;

	$arrayOfUniqueCatValues = "";
	$arrayOfUniqueCatValuesCount = 0;

	// Now sort all values into alphabetical order!
	if (is_array($arrayOfUniqueCategories)) {
		asort($arrayOfUniqueCategories);
		reset($arrayOfUniqueCategories);
		foreach ($arrayOfUniqueCategories as $value => $display) {
			$arrayOfUniqueCatValues .= "\narrayOfUniqueCatValues[$arrayOfUniqueCatValuesCount] = new LookupAttribute('',\"$value\",\"$display\");";
			$arrayOfUniqueCatValuesCount++;
		}
	}

	// Now wrap and return
	return "\n<script language=\"JavaScript\">\n<!-- // hide from stupid browsers\n" . $buffer . "\n// -->\n</script>\n";
}

if (is_site_enabled()) {
	if (is_opendb_valid_session() || is_site_public_access()) {
		if (is_user_granted_permission(PERM_VIEW_ADVANCED_SEARCH)) {
			$page_title = get_opendb_lang_var('advanced_search');
			echo _theme_header($page_title);

			echo (encode_search_javascript_arrays($item_type_rs, $category_type_rs, $item_attribute_type_rs));

			echo ("<h2>" . $page_title . "</h2>");

			echo ("\n<form name=\"search\" method=\"GET\" action=\"listings.php\">");
			echo ("\n<input type=\"hidden\" name=\"datetimemask\" value=\"" . get_opendb_config_var('search', 'datetime_mask') . "\">");
			echo ("\n<input type=\"hidden\" name=\"search_list\" value=\"y\">");

			echo ("<table class=\"searchForm\">");

			echo format_field(get_opendb_lang_var('title'),
					"\n<input type=\"text\" class=\"text\" id=\"search-title\" size=\"50\" name=\"title\">" . "\n<ul class=\"searchInputOptions\">" . "\n<li><input type=\"radio\" class=\"radio\" name=\"title_match\" value=\"word\">" . get_opendb_lang_var('word_match') . "</li>"
							. "\n<li><input type=\"radio\" class=\"radio\" name=\"title_match\" value=\"partial\" CHECKED>" . get_opendb_lang_var('partial_match') . "</li>" . "\n<li><input type=\"radio\" class=\"radio\" name=\"title_match\" value=\"exact\">" . get_opendb_lang_var('exact_match')
							. "</li>" . "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"title_case\" value=\"case_sensitive\">" . get_opendb_lang_var('case_sensitive') . "</li>" . "\n</ul>");

			if (@count($category_type_rs) > 1) {
				$catTypeSelect = "<select name=\"category\" id=\"search-category\">" . "\n<option value=\"\">-------------- " . get_opendb_lang_var('all') . " --------------";

				reset($category_type_rs);
				foreach ($category_type_rs as $value => $display) {
					$catTypeSelect .= "\n<option value=\"$value\">$display";
				}
				$catTypeSelect .= "</select>";

				echo format_field(get_opendb_lang_var('category'), $catTypeSelect);
			}

			if (get_opendb_config_var('item_review', 'enable') !== FALSE) {
				$attribute_type_r = fetch_attribute_type_r("S_RATING");
				$attribute_type_r['compulsory_ind'] = 'N';
				echo get_item_input_field("rating", $attribute_type_r, NULL, // $item_r
				NULL); //value
			}

			if (@count($item_type_rs) > 1) {
				$itemTypeSelect = "<select name=\"s_item_type\" id=\"search-itemtype\" onChange=\"populateList(this.options[this.options.selectedIndex].value, this.form.attribute_type, arrayOfAttributes, true, '------------- " . get_opendb_lang_var('all') . " -------------', false);\">"
						. "\n<option value=\"\">-------------- " . get_opendb_lang_var('all') . " --------------";

				reset($item_type_rs);
				foreach ($item_type_rs as $item_type_r) {
					$itemTypeSelect .= "\n<option value=\"" . $item_type_r['s_item_type'] . "\" >" . $item_type_r['s_item_type'] . " - " . $item_type_r['description'];
				}
				$itemTypeSelect .= "</select>";

				echo format_field(get_opendb_lang_var('item_type'), $itemTypeSelect);
			}

			$attrTypeSelect = "<select name=\"attribute_type\" id=\"search-attributetype\" onChange=\"populateList(this.options[this.options.selectedIndex].value, this.form['lookup_attribute_val'], arrayOfLookupValues, false, '" . get_opendb_lang_var('use_the_value_field') . " ---->', true);\">"
					. "\n<option value=\"\">-------------- " . get_opendb_lang_var('all') . " --------------";

			@reset($item_attribute_type_rs);
			foreach ($item_attribute_type_rs as  $item_attribute_type_r) {
                if (has_role_permission($item_attribute_type_r['view_perm'])) {
				    $attrTypeSelect .= "\n<option value=\"" . $item_attribute_type_r['s_attribute_type'] . "\">" . $item_attribute_type_r['s_attribute_type'] . " - " . $item_attribute_type_r['description'];
                }
			}
			$attrTypeSelect .= "</select>";

			echo format_field(get_opendb_lang_var('s_attribute_type'), $attrTypeSelect);

			echo format_field(get_opendb_lang_var('s_attribute_type_lookup'),
					"\n<select name=\"lookup_attribute_val\" id=\"search-lookupattributeval\" onChange=\"if(this.options[this.options.selectedIndex].value.length>0){this.form['attribute_val'].disabled=true;}else{this.form['attribute_val'].disabled=false;}\">" . "\n<option value=\"\">"
							. get_opendb_lang_var('use_the_value_field') . " ---->" . "\n</select>");

			echo format_field(get_opendb_lang_var('attribute_val'),
					"<input type=\"text\" class=\"text\" name=\"attribute_val\" id=\"search-attributeval\" size=\"50\" value=\"\">" . "\n<ul class=\"searchInputOptions\">" . "\n<li><input type=\"radio\" class=\"radio\" name=\"attr_match\" value=\"word\">" . get_opendb_lang_var('word_match')
							. "</li>" . "\n<li><input type=\"radio\" class=\"radio\" name=\"attr_match\" value=\"partial\" CHECKED>" . get_opendb_lang_var('partial_match') . "</li>" . "\n<li><input type=\"radio\" class=\"radio\" name=\"attr_match\" value=\"exact\">"
							. get_opendb_lang_var('exact_match') . "</li>" . "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"attr_case\" value=\"case_sensitive\">" . get_opendb_lang_var('case_sensitive') . "</li>" . "\n</ul>");

			if (strlen($HTTP_VARS['not_owner_id']) > 0) {
				echo ("\n<input type=\"hidden\" name=\"not_owner_id\" value=\"" . $HTTP_VARS['not_owner_id'] . "\">");
			}

			echo (format_field(get_opendb_lang_var('owner'),
					"\n<select name=\"owner_id\" id=\"search-owner\">" . "\n<option value=\"\">-------------- " . get_opendb_lang_var('all') . " --------------"
							. custom_select('owner_id', fetch_user_rs(PERM_ITEM_OWNER), '%fullname% (%user_id%)', 'NA', NULL, 'user_id') . "\n</select>"));

			$lookup_results = fetch_status_type_rs(TRUE);

			if ($lookup_results && db_num_rows($lookup_results) > 1) {
				echo format_field(get_opendb_lang_var('s_status_type'), checkbox_grid('s_status_type', $lookup_results, '%img%', // mask
				'VERTICAL', array())); // value
			}

			echo format_field(get_opendb_lang_var('status_comment'),
					"\n<input type=\"text\" class=\"text\" name=\"status_comment\" id=\"search-statuscomment\" size=\"50\">" . "\n<ul class=\"searchInputOptions\">" . "\n<li><input type=\"radio\" class=\"radio\" name=\"status_comment_match\" value=\"word\">" . get_opendb_lang_var('word_match')
							. "</li>" . "\n<li><input type=\"radio\" class=\"radio\" name=\"status_comment_match\" value=\"partial\" CHECKED>" . get_opendb_lang_var('partial_match') . "</li>" . "\n<li><input type=\"radio\" class=\"radio\" name=\"status_comment_match\" value=\"exact\">"
							. get_opendb_lang_var('exact_match') . "</li>" . "\n<li><input type=\"checkbox\" class=\"checkbox\" name=\"status_comment_case\" value=\"case_sensitive\">" . get_opendb_lang_var('case_sensitive') . "</li>" . "\n</ul>");

			echo format_field(get_opendb_lang_var('updated'),
					"\n<select name=\"update_on_days\" id=\"search-updateondays\" onChange=\"if(this.options[this.options.selectedIndex].value.length>0){this.form['update_on'].disabled=true;}else{this.form['update_on'].disabled=false;}\">" . "\n<option value=\"\">"
							. get_opendb_lang_var('specify_datetime') . " ---->" . "\n<option value=\"1\">" . get_opendb_lang_var('one_day_ago') . "\n<option value=\"7\">" . get_opendb_lang_var('one_week_ago') . "\n<option value=\"28\">" . get_opendb_lang_var('one_month_ago')
							. "\n<option value=\"365\">" . get_opendb_lang_var('one_year_ago') . "\n</select>" . get_input_field("update_on", NULL, NULL, "datetime(" . get_opendb_config_var('search', 'datetime_mask') . ")", "N", NULL, FALSE));

			echo format_field(get_opendb_lang_var('order_by'),
					"\n<select name=\"order_by\"  id=\"search-orderby\">" . "\n<option value=\"title\" SELECTED>" . get_opendb_lang_var('title') . "\n<option value=\"owner_id\">" . get_opendb_lang_var('owner') . "\n<option value=\"category\">" . get_opendb_lang_var('category')
							. "\n<option value=\"s_item_type\">" . get_opendb_lang_var('item_type') . "\n<option value=\"s_status_type\">" . get_opendb_lang_var('s_status_type') . "\n<option value=\"update_on\">" . get_opendb_lang_var('update_date') . "\n</select>"
							. "\n<input type=\"radio\" class=\"radio\" name=\"sortorder\" value=\"ASC\" CHECKED>" . get_opendb_lang_var('asc') . "\n<input type=\"radio\" class=\"radio\" name=\"sortorder\" value=\"DESC\">" . get_opendb_lang_var('desc'));

			echo ("</table>");

			echo ("\n<input type=\"submit\" class=\"submit\" value=\"" . get_opendb_lang_var('search') . "\">");
			echo ("</form>");

			echo _theme_footer();
		} else {
			opendb_not_authorised_page(PERM_VIEW_ADVANCED_SEARCH, $HTTP_VARS);
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
